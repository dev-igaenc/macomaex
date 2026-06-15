<?php

if (!function_exists('llm_report_trade_exact_items')) {
    function llm_report_trade_exact_items($value) {
        $rawItems = is_array($value) ? $value : array($value);
        $out = array();
        foreach ($rawItems as $raw) {
            $raw = trim((string)$raw);
            if ($raw === '' || $raw === '-' || $raw === '미공개') continue;
            $parts = preg_split('/\s*(?:,|\/|\||;|\n|\r\n|·|ㆍ)\s*/u', $raw);
            foreach ($parts as $part) {
                $part = trim((string)$part);
                if ($part === '' || $part === '-' || $part === '미공개') continue;
                if (!in_array($part, $out, true)) $out[] = $part;
            }
        }
        return $out;
    }
}

if (!function_exists('llm_report_trade_allowed_items')) {
    function llm_report_trade_allowed_items($sourceData, $rec, $isSell) {
        $srcItems = array_values(array_unique(array_merge(
            llm_report_trade_exact_items($sourceData['src_keywords'] ?? array()),
            llm_report_trade_exact_items($sourceData['source_mpd'] ?? '')
        )));
        $recItems = llm_report_trade_exact_items(is_array($rec) ? ($rec['keywords'] ?? array()) : array());
        // 구매처 찾기는 추천 기업의 공급 품목을, 판매처 찾기는 의뢰사의 공급 품목을 사용한다.
        // 반대 방향 배열은 비워 리포트가 거래 주체를 뒤섞지 않도록 한다.
        $buyItems = $isSell ? array() : $recItems;
        $sellItems = $isSell ? $srcItems : array();
        return array('buy_items' => $buyItems, 'sell_items' => $sellItems);
    }
}

if (!function_exists('llm_report_trade_connection_type_desc')) {
    function llm_report_trade_connection_type_desc($type) {
        $type = (string)$type;
        if (strpos($type, 'chain') === 0) return '공통 거래처 네트워크 연결';
        if (strpos($type, 'partner') === 0) return '동종 기업 실거래 검증 파트너';
        if ($type === 'industry') return '동일 업종 공급망 파트너';
        if ($type === 'neighbor') return '지역 기반 근접 파트너';
        return '거래 연결';
    }
}

if (!function_exists('llm_report_trade_join_items')) {
    function llm_report_trade_join_items($items, $emptyLabel = '원본 품목 정보') {
        $items = array_values(array_filter(array_map('trim', (array)$items), function ($v) { return $v !== ''; }));
        if (empty($items)) return $emptyLabel;
        return implode(', ', array_slice($items, 0, 3));
    }
}

if (!function_exists('llm_report_trade_primary_meta')) {
    function llm_report_trade_primary_meta($buyItems, $sellItems, $tradeLabel, $emptyLabel = '원본 품목 정보') {
        $isSellTrade = ($tradeLabel === '판매처');
        return array(
            'is_sell_trade' => $isSellTrade,
            'items' => $isSellTrade ? $sellItems : $buyItems,
            'list' => llm_report_trade_join_items($isSellTrade ? $sellItems : $buyItems, $emptyLabel),
            'label' => $isSellTrade ? '판매 가능 품목' : '구매 가능 품목',
        );
    }
}

if (!function_exists('llm_report_trade_reason_line')) {
    function llm_report_trade_reason_line($buyItems, $sellItems, $tradeLabel) {
        $primary = llm_report_trade_primary_meta($buyItems, $sellItems, $tradeLabel, '');
        if ($primary['list'] !== '') {
            if ($primary['is_sell_trade']) {
                return "귀사가 제공할 수 있는 {$primary['list']} 관련 거래 가능성을 우선 검토할 수 있는 기업으로 분석되었습니다.";
            }
            return "귀사가 필요로 하는 {$primary['list']} 관련 거래 가능성을 우선 검토할 수 있는 기업으로 분석되었습니다.";
        }
        return "원본 품목 정보가 부족하여 품목 요약은 생략하고 거래 가능성 중심으로 분석했습니다.";
    }
}

if (!function_exists('llm_report_trade_decode_json_object')) {
    function llm_report_trade_decode_json_object($rawText) {
        $rawText = trim((string)$rawText);
        if ($rawText === '') return null;

        $decoded = json_decode($rawText, true);
        if (is_array($decoded)) return $decoded;

        $cleaned = preg_replace('/^```json\s*/iu', '', $rawText);
        $cleaned = preg_replace('/^```\s*/u', '', (string)$cleaned);
        $cleaned = preg_replace('/\s*```$/u', '', (string)$cleaned);
        $cleaned = trim((string)$cleaned);
        $decoded = json_decode($cleaned, true);
        if (is_array($decoded)) return $decoded;

        $start = strpos($cleaned, '{');
        $end = strrpos($cleaned, '}');
        if ($start === false || $end === false || $end <= $start) return null;

        $candidate = substr($cleaned, $start, $end - $start + 1);
        $decoded = json_decode($candidate, true);
        return is_array($decoded) ? $decoded : null;
    }
}

if (!function_exists('llm_report_trade_build_reasons')) {
    function llm_report_trade_build_reasons($buyItems, $sellItems, $tradeLabel, $connectionType) {
        $primary = llm_report_trade_primary_meta($buyItems, $sellItems, $tradeLabel);
        $connectionType = trim((string)$connectionType);
        $focusLine = $primary['is_sell_trade']
            ? "원본 품목 기준으로 보면 {$primary['list']} 중심으로 실제 공급 가능 범위를 검토할 수 있어 거래 제안의 구체성을 높일 수 있습니다."
            : "원본 품목 기준으로 보면 {$primary['list']} 중심으로 실제 구매 수요를 구체화할 수 있어 거래 검토의 우선순위를 빠르게 정리할 수 있습니다.";
        return array(
            llm_report_trade_reason_line($buyItems, $sellItems, $tradeLabel),
            $focusLine,
            $connectionType !== ''
                ? "연결 방식은 {$connectionType}로 제시되어 있어, 초기 접점도 원본 품목 범위를 기준으로 좁혀서 검토하는 것이 적절합니다."
                : "초기 접점은 원본 품목 범위를 기준으로 좁혀서 검토하는 것이 적절합니다.",
        );
    }
}

if (!function_exists('llm_report_trade_build_positioning')) {
    function llm_report_trade_build_positioning($sourceData, $rec, $tradeLabel) {
        $recIndustry = trim((string)($rec['bzc_nm'] ?? $rec['bzc_cd'] ?? '해당 업종'));
        $recRegion = trim((string)($rec['sido'] ?? ''));
        $recScale = trim((string)($rec['sze'] ?? ''));
        $sourceIndustry = trim((string)($sourceData['source_bzc_nm'] ?? $sourceData['source_bzc_cd'] ?? '의뢰사 업종'));
        $regionText = ($recRegion !== '' && $recRegion !== '-') ? ($recRegion . ' 거점을 바탕으로 ') : '';
        $scaleText = ($recScale !== '' && $recScale !== '-') ? ('규모는 ' . $recScale . ' 수준으로 파악되며, ') : '';
        return "해당 기업은 **{$recIndustry}** 성격의 기업으로 해석할 수 있습니다. {$regionText}{$tradeLabel} 검토 범위를 넓힐 수 있는 후보입니다. {$scaleText}{$sourceIndustry}과의 거래 접점을 품목 단위로 확인하는 접근이 적절합니다.";
    }
}

if (!function_exists('llm_report_trade_build_detail')) {
    function llm_report_trade_build_detail($buyItems, $sellItems, $tradeLabel) {
        $primary = llm_report_trade_primary_meta($buyItems, $sellItems, $tradeLabel);
        if ($primary['is_sell_trade']) {
            if ($primary['list'] === '원본 품목 정보') {
                return "판매처 관점에서 우선 확인할 판매 가능 품목 원본 정보가 부족합니다. 따라서 초기 논의에서는 실제 공급 가능 범위와 납기 조건부터 우선 확인하는 것이 적절합니다.";
            }
            return "판매처 관점에서 우선 확인할 판매 가능 품목은 {$primary['list']}입니다. 따라서 초기 논의는 위 원본 품목의 공급 가능 범위, 발주 단위, 납기 조건을 중심으로 구체화하는 것이 적절합니다.";
        }

        if ($primary['list'] === '원본 품목 정보') {
            return "구매처 관점에서 우선 확인할 구매 가능 품목 원본 정보가 부족합니다. 따라서 초기 논의에서는 실제 수요 품목과 발주 조건부터 우선 확인하는 것이 적절합니다.";
        }
        return "구매처 관점에서 우선 확인할 구매 가능 품목은 {$primary['list']}입니다. 따라서 초기 논의는 위 원본 품목의 발주 단위, 납기 조건, 공급 가능 범위를 중심으로 구체화하는 것이 적절합니다.";
    }
}

if (!function_exists('llm_report_trade_build_connection_background')) {
    function llm_report_trade_build_connection_background($sourceData, $rec, $buyItems, $sellItems, $connectionType, $tradeLabel) {
        $sourceIndustry = trim((string)($sourceData['source_bzc_nm'] ?? $sourceData['source_bzc_cd'] ?? '의뢰사 업종'));
        $recIndustry = trim((string)($rec['bzc_nm'] ?? $rec['bzc_cd'] ?? '해당 업종'));
        $primary = llm_report_trade_primary_meta($buyItems, $sellItems, $tradeLabel);
        $connectionText = trim((string)$connectionType);
        $sentence = "의뢰사는 {$sourceIndustry}, 해당 기업은 {$recIndustry} 업종 기반으로 해석할 수 있습니다. 원본 품목 기준으로 보면 {$primary['list']}를 중심으로 거래 접점을 좁혀볼 수 있습니다.";
        if ($connectionText !== '') {
            $sentence .= " 연결 방식이 {$connectionText}로 제시된 만큼, 실제 검토도 위 원본 품목 범위 안에서 시작하는 것이 적절합니다.";
        } else {
            $sentence .= " 실제 검토도 위 원본 품목 범위 안에서 시작하는 것이 적절합니다.";
        }
        return $sentence;
    }
}

if (!function_exists('llm_report_trade_build_supply_chain_fit')) {
    function llm_report_trade_build_supply_chain_fit($rec, $buyItems, $sellItems, $tradeLabel) {
        $primary = llm_report_trade_primary_meta($buyItems, $sellItems, $tradeLabel);
        $radarSummary = '';
        if (!empty($rec['radar']) && is_array($rec['radar'])) {
            $axisMap = array(
                'supply_chain' => '공급망 연결력',
                'financial_health' => '재무 건전성',
                'network_overlap' => '거래망 중복도',
                'market_power' => '시장 영향력',
                'trade_diversity' => '거래 다양성',
                'company_size' => '기업 규모',
                'growth_trend' => '성장 궤도',
                'trade_loyalty' => '거래 충성도',
                'scale_match' => '거래 규모 적합도',
                'accessibility' => '지역 접근성',
                'industry_fit' => '업종 적합도',
                'product_fit' => '상품 적합도',
                'similarity' => '거래 유사도',
                'activity' => '거래 활성도',
                'geographic_fit' => '지역 근접성',
                'stability_score' => '거래 안정성',
                'network_centrality' => '네트워크 중심성',
            );
            $sorted = $rec['radar'];
            arsort($sorted);
            $topAxes = array_slice(array_keys($sorted), 0, 2);
            $topLabels = array();
            foreach ($topAxes as $axis) {
                $topLabels[] = $axisMap[$axis] ?? $axis;
            }
            if (!empty($topLabels)) {
                $radarSummary = implode(', ', $topLabels);
            }
        }
        $sentence = $primary['is_sell_trade']
            ? "원본 품목 기준으로 보면 {$primary['list']} 공급 가능 범위가 실제 제안 가능한 거래 단위와 연결됩니다."
            : "원본 품목 기준으로 보면 {$primary['list']} 수요 범위가 실제 검토 대상 거래 단위와 연결됩니다.";
        if ($radarSummary !== '') {
            $sentence .= " 레이더에서는 {$radarSummary} 축이 상대적으로 높게 나타나, 거래 연결성과 실행 가능성을 검토하는 근거로 활용할 수 있습니다.";
        }
        $sentence .= " 초기 협의에서는 품목 범위, 발주 단위, 납기 가능 여부를 먼저 맞추는 것이 적절합니다.";
        return $sentence;
    }
}

if (!function_exists('llm_report_trade_build_scenarios')) {
    function llm_report_trade_build_scenarios($buyItems, $sellItems, $tradeLabel) {
        $primary = llm_report_trade_primary_meta($buyItems, $sellItems, $tradeLabel);
        if ($primary['is_sell_trade']) {
            return array(
                "{$primary['list']} 중심으로 소량 견적 또는 샘플 제안부터 시작해 실제 공급 조건을 확인할 수 있습니다.",
                "초기 검증이 끝나면 {$primary['list']}의 정기 공급 가능성과 납기 안정성을 검토해 지속 거래 여부를 판단할 수 있습니다.",
            );
        }
        return array(
            "{$primary['list']} 중심으로 소량 견적 문의 또는 샘플 검토부터 시작해 실제 구매 조건을 확인할 수 있습니다.",
            "초기 검증이 끝나면 {$primary['list']}의 반복 발주 가능성과 납기 안정성을 검토해 지속 거래 여부를 판단할 수 있습니다.",
        );
    }
}

if (!function_exists('llm_report_trade_build_first_touch_actions')) {
    function llm_report_trade_build_first_touch_actions($buyItems, $sellItems, $tradeLabel) {
        $primary = llm_report_trade_primary_meta($buyItems, $sellItems, $tradeLabel);
        if ($primary['is_sell_trade']) {
            return array(
                "첫 연락에서는 {$primary['list']} 중 실제 제안할 품목을 1~2개로 먼저 좁혀 문의하는 것이 좋습니다.",
                "견적, 최소 발주 수량, 납기 가능 일정, 품질 기준을 함께 안내해 초기 검토 범위를 명확히 하는 것이 좋습니다.",
            );
        }
        return array(
            "첫 연락에서는 {$primary['list']} 중 실제 검토할 품목을 1~2개로 먼저 좁혀 문의하는 것이 좋습니다.",
            "견적, 최소 발주 수량, 납기 가능 일정, 품질 기준을 함께 요청해 초기 검토 범위를 명확히 하는 것이 좋습니다.",
        );
    }
}

if (!function_exists('llm_report_trade_build_considerations')) {
    function llm_report_trade_build_considerations() {
        return array(
            "원본 품목 범위를 벗어난 확장 제안은 초기 접점에서 제외하고, 현재 확인된 품목부터 검토하는 것이 좋습니다.",
            "발주 단위, 납기, 결제 조건, 품질 기준은 거래 전 단계에서 반드시 명확히 확인해야 합니다.",
        );
    }
}

if (!function_exists('llm_report_trade_sanitize_report_json')) {
    function llm_report_trade_sanitize_report_json($reportText, $sourceData, $rec, $isSell) {
        $decoded = llm_report_trade_decode_json_object($reportText);
        if (!is_array($decoded)) return $reportText;

        // 품목 배열은 항상 서버가 계산한 허용 원본값으로 다시 고정
        $tradeLabel = $isSell ? '판매처' : '구매처';
        $allowed = llm_report_trade_allowed_items($sourceData, $rec, $isSell);
        $connectionType = trim((string)($decoded['connection_type'] ?? ''));
        if ($connectionType === '') {
            $connectionType = llm_report_trade_connection_type_desc($rec['candidate_type'] ?? '');
        }

        $decoded['trade_label'] = trim((string)($decoded['trade_label'] ?? '')) ?: $tradeLabel;
        $decoded['title'] = trim((string)($decoded['title'] ?? '')) ?: ($decoded['trade_label'] . ' 거래 가능성 분석');
        $decoded['connection_type'] = $connectionType;
        $decoded['buy_items'] = $allowed['buy_items'];
        $decoded['sell_items'] = $allowed['sell_items'];
        if ($decoded['trade_label'] === '판매처') {
            $decoded['buy_items'] = array();
        } else {
            $decoded['sell_items'] = array();
        }
        $decoded['reasons'] = llm_report_trade_build_reasons($decoded['buy_items'], $decoded['sell_items'], $decoded['trade_label'], $connectionType);
        $decoded['positioning'] = llm_report_trade_build_positioning($sourceData, $rec, $decoded['trade_label']);
        $decoded['tradable_items_detail'] = llm_report_trade_build_detail($decoded['buy_items'], $decoded['sell_items'], $decoded['trade_label']);
        $decoded['connection_background'] = llm_report_trade_build_connection_background($sourceData, $rec, $decoded['buy_items'], $decoded['sell_items'], $connectionType, $decoded['trade_label']);
        $decoded['supply_chain_fit'] = llm_report_trade_build_supply_chain_fit($rec, $decoded['buy_items'], $decoded['sell_items'], $decoded['trade_label']);
        $decoded['trade_scenarios'] = llm_report_trade_build_scenarios($decoded['buy_items'], $decoded['sell_items'], $decoded['trade_label']);
        $decoded['first_touch_actions'] = llm_report_trade_build_first_touch_actions($decoded['buy_items'], $decoded['sell_items'], $decoded['trade_label']);
        $decoded['considerations'] = llm_report_trade_build_considerations();

        $encoded = json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return ($encoded === false) ? $reportText : $encoded;
    }
}
