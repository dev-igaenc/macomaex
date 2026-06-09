<?php
include_once('./_common.php');
include_once(G5_PATH . '/ko/helper/company_list_ai_helper.php');

$board['bo_10'] = '3';
$board['bo_9'] = '1';

$company_list_ai_css_ver = @filemtime(G5_PATH . '/ko/css/company_list_ai.css');
if (!$company_list_ai_css_ver) $company_list_ai_css_ver = G5_CSS_VER;
$company_card_css_ver = @filemtime(G5_PATH . '/ko/css/company-card.css');
if (!$company_card_css_ver) $company_card_css_ver = G5_CSS_VER;
$company_list_ai_js_ver = @filemtime(G5_PATH . '/ko/js/company_list_ai.js');
if (!$company_list_ai_js_ver) $company_list_ai_js_ver = defined('G5_JS_VER') ? G5_JS_VER : G5_CSS_VER;

add_stylesheet('<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/variable/pretendardvariable-dynamic-subset.min.css">', 180);
add_stylesheet('<link rel="stylesheet" href="' . G5_URL . '/ko/css/design-tokens.css?ver=' . G5_CSS_VER . '">', 185);
add_stylesheet('<link rel="stylesheet" href="' . G5_URL . '/ko/css/header-core.css?ver=' . G5_CSS_VER . '">', 186);
add_stylesheet('<link rel="stylesheet" href="' . G5_URL . '/ko/css/header-search.css?ver=' . G5_CSS_VER . '">', 187);
add_stylesheet('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">', 188);
add_stylesheet('<link rel="stylesheet" href="' . G5_URL . '/ko/css/badges.css?ver=' . G5_CSS_VER . '">', 190);
add_stylesheet('<link rel="stylesheet" href="' . G5_URL . '/ko/css/company-card.css?ver=' . (int)$company_card_css_ver . '">', 200);
add_stylesheet('<link rel="stylesheet" href="' . G5_URL . '/ko/css/favorite.css?ver=' . G5_CSS_VER . '">', 205);
add_stylesheet('<link rel="stylesheet" href="' . G5_URL . '/ko/css/section-header.css?ver=' . G5_CSS_VER . '">', 210);
add_stylesheet('<link rel="stylesheet" href="' . G5_URL . '/ko/css/verify-modal.css?ver=' . G5_CSS_VER . '">', 215);
add_stylesheet('<link rel="stylesheet" href="' . G5_URL . '/ko/css/ai-loading.css?ver=' . G5_CSS_VER . '">', 216);
add_stylesheet('<link rel="stylesheet" href="' . G5_URL . '/ko/css/company_list_ai.css?ver=' . (int)$company_list_ai_css_ver . '">', 220);

$g5['body_script'] = ' class="page-company-list-ai"';
include_once(G5_THEME_PATH . '/head.php');

echo '<style>#pageTop,#sVisual{display:none !important;}</style>';

function company_list_ai_translate_radar($radar)
{
    if (!is_array($radar)) return array();

    // EC2/구버전 키를 공통 표준 키로 정규화
    $key_alias = array(
        'supply_chain_old' => 'supply_chain',
        'industry' => 'supply_chain',
        'network' => 'network_overlap',
        'similarity' => 'network_overlap',
        'financial' => 'financial_health',
        'finance' => 'financial_health',
        'activity' => 'growth_trend',
        'trust' => 'growth_trend',
        'scale_fit' => 'scale_match',
        'scale' => 'scale_match',
        'region' => 'accessibility',
    );

    $out = array();
    foreach ($radar as $key => $val) {
        $k = strtolower(trim((string)$key));
        if ($k === '') continue;
        if (isset($key_alias[$k])) $k = $key_alias[$k];

        $score = (float)$val;
        if ($score < 0) $score = 0;
        if ($score > 100) $score = 100;
        $out[$k] = $score;
    }
    return $out;
}

function company_list_ai_normalize_tags($tags)
{
    if (!is_array($tags) || empty($tags)) return array();

    $normalized = array();
    foreach ($tags as $tag_raw) {
        $tag = trim((string)$tag_raw);
        if ($tag === '' || in_array($tag, $normalized, true)) continue;
        $normalized[] = $tag;
    }

    return $normalized;
}

function company_list_ai_parse_keywords_json($keywords_json)
{
    $keywords_json = trim((string)$keywords_json);
    if ($keywords_json === '') return array();

    $decoded = json_decode($keywords_json, true);
    if (!is_array($decoded)) return array();

    return company_list_ai_normalize_tags($decoded);
}

function company_list_ai_resolve_tag_total_count($keyword_count, $tags)
{
    $count = (int)$keyword_count;
    if ($count > 0) return $count;
    return count(is_array($tags) ? $tags : array());
}

function company_list_ai_normalize_kedcd($kedcd)
{
    $k = str_pad(preg_replace('/\D/', '', (string)$kedcd), 10, '0', STR_PAD_LEFT);
    if (strlen($k) !== 10 || preg_match('/^0+$/', $k)) return '';
    return $k;
}

function company_list_ai_fetch_unlocked_kedcd_map_for_member($member_id)
{
    $member_id = trim((string)$member_id);
    if ($member_id === '') return array();

    $res = sql_query("
        SELECT target_kedcd
        FROM trade_request
        WHERE request_mb_id = '" . sql_escape_string($member_id) . "'
          AND credit_used = 1
          AND target_kedcd != ''
    ", false);
    if (!$res) return array();

    $map = array();
    while ($row = sql_fetch_array($res)) {
        $kedcd = company_list_ai_normalize_kedcd($row['target_kedcd'] ?? '');
        if ($kedcd !== '') $map[$kedcd] = true;
    }
    return $map;
}

function company_list_ai_apply_unlock_state($cards, $unlocked_kedcd_map)
{
    if (!is_array($cards) || empty($cards)) return array();
    if (!is_array($unlocked_kedcd_map)) $unlocked_kedcd_map = array();

    foreach ($cards as $idx => $card) {
        if (!is_array($card)) continue;
        $kedcd = company_list_ai_normalize_kedcd($card['kedcd'] ?? '');
        $cards[$idx]['is_unlocked'] = ($kedcd !== '' && !empty($unlocked_kedcd_map[$kedcd]));
    }
    return $cards;
}

function company_list_ai_fetch_company_keyword_map($kedcd_list)
{
    if (!is_array($kedcd_list) || empty($kedcd_list)) return array();

    $clean = array();
    foreach ($kedcd_list as $kedcd) {
        $k = str_pad(preg_replace('/\D/', '', (string)$kedcd), 10, '0', STR_PAD_LEFT);
        if (strlen($k) !== 10 || preg_match('/^0+$/', $k)) continue;
        $clean[$k] = true;
    }
    $keys = array_keys($clean);
    if (empty($keys)) return array();

    $map = array();
    foreach (array_chunk($keys, 200) as $chunk) {
        $quoted = array();
        foreach ($chunk as $k) {
            $quoted[] = "'" . sql_real_escape_string($k) . "'";
        }
        if (empty($quoted)) continue;

        $sql = "
            SELECT KEDCD, keywords_json, keyword_count
            FROM company
            WHERE KEDCD IN (" . implode(',', $quoted) . ")
        ";
        $res = sql_query($sql, false);
        if (!$res) continue;

        while ($row = sql_fetch_array($res)) {
            $k = str_pad(preg_replace('/\D/', '', (string)($row['KEDCD'] ?? '')), 10, '0', STR_PAD_LEFT);
            if (strlen($k) !== 10) continue;
            $keywords_json = trim((string)($row['keywords_json'] ?? ''));
            $tags = company_list_ai_parse_keywords_json($keywords_json);
            $keyword_count = max(0, (int)($row['keyword_count'] ?? 0));
            $map[$k] = array(
                'keywords_json' => $keywords_json,
                'tags' => $tags,
                'keyword_count' => $keyword_count,
            );
        }
    }

    return $map;
}

function company_list_ai_format_badge_percent($value)
{
    if ($value === null || $value === '') return '';
    $str = trim((string)$value);
    if ($str === '') return '';
    if (!is_numeric($str)) return $str;

    $num = (float)$str;
    if ($num == (int)$num) return (string)((int)$num);
    return rtrim(rtrim(number_format($num, 2, '.', ''), '0'), '.');
}

function company_list_ai_build_badges_from_fields($badge_growth_raw, $badge_stability_raw, $venture_raw)
{
    $badges = array();

    $badge_growth = company_list_ai_format_badge_percent($badge_growth_raw);
    $badge_stability = company_list_ai_format_badge_percent($badge_stability_raw);
    $venture = strtoupper(trim((string)$venture_raw));

    if ($badge_growth !== '') $badges[] = '성장TOP' . $badge_growth . '%';
    if ($badge_stability !== '') $badges[] = '재무안정TOP' . $badge_stability . '%';
    if ($venture === 'Y') $badges[] = '벤처인증';

    return array(
        'badges' => $badges,
        'badge_growth' => $badge_growth,
        'badge_stability' => $badge_stability,
        'badge_venture' => ($venture === 'Y') ? 'Y' : 'N',
    );
}

function company_list_ai_fetch_company_badge_map($kedcd_list)
{
    if (!is_array($kedcd_list) || empty($kedcd_list)) return array();

    $clean = array();
    foreach ($kedcd_list as $kedcd) {
        $k = str_pad(preg_replace('/\D/', '', (string)$kedcd), 10, '0', STR_PAD_LEFT);
        if (strlen($k) !== 10 || preg_match('/^0+$/', $k)) continue;
        $clean[$k] = true;
    }
    $keys = array_keys($clean);
    if (empty($keys)) return array();

    $map = array();
    foreach (array_chunk($keys, 200) as $chunk) {
        $quoted = array();
        foreach ($chunk as $k) {
            $quoted[] = "'" . sql_real_escape_string($k) . "'";
        }
        if (empty($quoted)) continue;

        $sql = "
            SELECT KEDCD, badge_growth, badge_stability, badge_venture
            FROM company_score_final_dedup
            WHERE KEDCD IN (" . implode(',', $quoted) . ")
        ";
        $res = sql_query($sql, false);
        if (!$res) continue;

        while ($row = sql_fetch_array($res)) {
            $k = str_pad(preg_replace('/\D/', '', (string)($row['KEDCD'] ?? '')), 10, '0', STR_PAD_LEFT);
            if (strlen($k) !== 10) continue;
            $map[$k] = array(
                'badge_growth' => $row['badge_growth'] ?? '',
                'badge_stability' => $row['badge_stability'] ?? '',
                'badge_venture' => strtoupper(trim((string)($row['badge_venture'] ?? 'N'))),
            );
        }
    }

    return $map;
}

function company_list_ai_get_item_tags_by_industry($bzc_nm, $bzc_cd, $kedcd = '')
{
    $industry_item_map = array(
        array('kw' => array('화학', '케미칼', '도료', '도장', '수지', '플라스틱'), 'items' => array('화학원료', '정밀화학', '첨가제')),
        array('kw' => array('철강', '금속', '주물', '단조', '압연', '알루미늄', '스테인리스'), 'items' => array('철강재', '금속부품', '합금소재')),
        array('kw' => array('기계', '장비', '설비', '압축기', '펌프', '밸브', '유압'), 'items' => array('산업기계', '유압부품', '제조설비')),
        array('kw' => array('전자', '전기', '반도체', 'pcb', '디스플레이', '배터리', 'led'), 'items' => array('전자부품', '회로기판', '전기자재')),
        array('kw' => array('자동차', '차량', '모빌리티', '타이어', '브레이크', '시트'), 'items' => array('자동차부품', '차체소재', '조립모듈')),
        array('kw' => array('식품', '음료', '제과', '제빵', '농산', '수산', '축산', '가공'), 'items' => array('식자재', '가공식품', '원물')),
        array('kw' => array('건설', '건축', '토목', '인테리어', '단열', '방수', '콘크리트'), 'items' => array('건설자재', '구조재', '시공자재')),
        array('kw' => array('섬유', '의류', '봉제', '원단', '실', '직물', '편물'), 'items' => array('원단', '의류소재', '봉제부자재')),
        array('kw' => array('종이', '인쇄', '포장', '골판지', '박스', '완충재'), 'items' => array('포장재', '인쇄물', '산업용지')),
        array('kw' => array('물류', '운송', '화물', '배송', '택배', '냉동', '냉장'), 'items' => array('물류서비스', '운송장비', '보관용역')),
        array('kw' => array('소프트웨어', 'it', '솔루션', '시스템', 'erp', 'saas'), 'items' => array('IT솔루션', 'SW라이선스', '시스템구축')),
        array('kw' => array('의료', '바이오', '제약', '의약', '헬스', '의료기기'), 'items' => array('의료기기', '의약원료', '바이오소재')),
        array('kw' => array('농업', '비료', '농약', '종자', '원예', '작물'), 'items' => array('농자재', '비료', '식물원료')),
        array('kw' => array('고무', '실리콘', '씰', '가스켓', '방진', '방음'), 'items' => array('고무부품', '실리콘제품', '씰링재')),
        array('kw' => array('목재', '가구', '합판', 'mdf', '목공'), 'items' => array('목재소재', '가구부자재', '합판')),
        array('kw' => array('유리', '세라믹', '도자', '내화'), 'items' => array('유리제품', '세라믹소재', '내화재')),
        array('kw' => array('환경', '폐기물', '재활용', '수처리', '정수'), 'items' => array('환경설비', '재활용원료', '처리약품')),
        array('kw' => array('에너지', '태양광', '풍력', '연료', '가스', 'lng'), 'items' => array('에너지설비', '연료소재', '발전부품')),
    );

    $name = trim((string)$bzc_nm);
    $name_lc = function_exists('mb_strtolower') ? mb_strtolower($name, 'UTF-8') : strtolower($name);

    foreach ($industry_item_map as $row) {
        foreach ($row['kw'] as $kw) {
            $kw_lc = function_exists('mb_strtolower') ? mb_strtolower((string)$kw, 'UTF-8') : strtolower((string)$kw);
            if ($kw_lc !== '' && strpos($name_lc, $kw_lc) !== false) {
                return $row['items'];
            }
        }
    }

    $cd = strtoupper(trim((string)$bzc_cd));
    if ($cd !== '' && substr($cd, 0, 1) === 'C') {
        return array('제조부품', '산업소재', '납품용역');
    }

    return array('납품용역', 'B2B소재', '산업부품');
}

function company_list_ai_ml_item_to_card($item)
{
    if (!is_array($item)) return null;

    $kedcd = str_pad(preg_replace('/\D/', '', (string)($item['kedcd'] ?? '')), 10, '0', STR_PAD_LEFT);
    if (strlen($kedcd) !== 10 || preg_match('/^0+$/', $kedcd)) return null;

    $title = trim((string)($item['enp_nm'] ?? ''));
    if ($title === '') $title = '기업명 준비중';

    $raw_score = isset($item['display_score']) ? (float)$item['display_score'] : (float)($item['score'] ?? 0) * 100.0;
    $score = (int)round($raw_score);
    if ($score < 0) $score = 0;
    if ($score > 100) $score = 100;

    $bzc_cd = trim((string)($item['bzc_cd'] ?? ''));
    $bzc_nm = trim((string)($item['bzc_nm'] ?? ''));
    $radar_raw = (isset($item['radar']) && is_array($item['radar'])) ? $item['radar'] : array();

    $badge_fields = company_list_ai_build_badges_from_fields(
        $item['badge_growth'] ?? null,
        $item['badge_stability'] ?? null,
        ($item['badge_venture'] ?? ($item['venp_yn'] ?? ($item['venture_yn'] ?? 'N')))
    );
    $badges = $badge_fields['badges'];

    $tags = array();
    if (isset($item['keywords_json'])) {
        if (is_array($item['keywords_json'])) {
            $tags = company_list_ai_normalize_tags($item['keywords_json']);
        } else {
            $tags = company_list_ai_parse_keywords_json((string)$item['keywords_json']);
        }
    }
    if (empty($tags) && isset($item['tags']) && is_array($item['tags'])) {
        $tags = company_list_ai_normalize_tags($item['tags']);
    } else if (empty($tags) && isset($item['tags']) && is_string($item['tags']) && trim($item['tags']) !== '') {
        $tags_from_json = company_list_ai_parse_keywords_json((string)$item['tags']);
        if (!empty($tags_from_json)) {
            $tags = $tags_from_json;
        } else {
            $parts = preg_split('/\s*,\s*/u', trim((string)$item['tags']));
            foreach ($parts as $tag) {
                $t = trim((string)$tag);
                if ($t === '' || in_array($t, $tags, true)) continue;
                $tags[] = $t;
            }
        }
    }
    if (empty($tags)) {
        $biz_category = trim((string)($item['biz_category'] ?? ''));
        if ($biz_category !== '') {
            $tags[] = $biz_category;
        } else {
            $tags = company_list_ai_get_item_tags_by_industry($bzc_nm, $bzc_cd, $kedcd);
        }
    }
    if (empty($tags)) $tags[] = 'AI추천';
    $tag_total_count = company_list_ai_resolve_tag_total_count($item['keyword_count'] ?? 0, $tags);

    return array(
        'kedcd' => $kedcd,
        'title' => $title,
        // 상세 LLM 보고서에 넘길 추천 기업 메타
        'candidate_type' => trim((string)($item['candidate_type'] ?? 'chain2')),
        'industry' => $bzc_cd,
        'industry_nm' => $bzc_nm,
        'region' => trim((string)($item['sido'] ?? '')),
        'sze' => (int)($item['sze'] ?? 0),
        'company_age' => max(0, (int)($item['company_age'] ?? 0)),
        'sales_growth_pct' => (isset($item['sales_growth_pct']) && $item['sales_growth_pct'] !== '' && $item['sales_growth_pct'] !== null)
            ? (float)$item['sales_growth_pct']
            : null,
        'debt_ratio_pct' => (isset($item['debt_ratio_pct']) && $item['debt_ratio_pct'] !== '' && $item['debt_ratio_pct'] !== null)
            ? (float)$item['debt_ratio_pct']
            : null,
        'consec_profit_2y' => (isset($item['consec_profit_2y']) && $item['consec_profit_2y'] !== '' && $item['consec_profit_2y'] !== null)
            ? ((int)$item['consec_profit_2y'] ? 1 : 0)
            : null,
        'capital_negative' => (isset($item['capital_negative']) && $item['capital_negative'] !== '' && $item['capital_negative'] !== null)
            ? ((int)$item['capital_negative'] ? 1 : 0)
            : null,
        'one_line' => trim((string)($item['one_line'] ?? '')),
        'score' => $score,
        'badges' => $badges,
        'badge_growth' => $badge_fields['badge_growth'],
        'badge_stability' => $badge_fields['badge_stability'],
        'badge_venture' => $badge_fields['badge_venture'],
        'tags' => $tags,
        'tag_total_count' => $tag_total_count,
        'radar' => company_list_ai_translate_radar(isset($item['radar']) ? $item['radar'] : array()),
    );
}

function company_list_ai_summarize_cards_for_log($cards)
{
    $summary = array();
    foreach ((array)$cards as $card) {
        if (!is_array($card)) continue;
        $summary[] = array(
            'kedcd' => (string)($card['kedcd'] ?? ''),
            'title' => (string)($card['title'] ?? ''),
            'industry' => (string)($card['industry'] ?? ''),
            'industry_nm' => (string)($card['industry_nm'] ?? ''),
            'score' => (int)($card['score'] ?? 0),
            'tags' => array_values((array)($card['tags'] ?? array())),
            'badges' => array_values((array)($card['badges'] ?? array())),
        );
    }
    return $summary;
}

function company_list_ai_render_badges($badges)
{
    if (!is_array($badges) || !$badges) {
        return '<span class="mCompanyCard_badge mCompanyCard_badge_placeholder">뱃지 준비중</span>';
    }

    $html = '';
    foreach ($badges as $badge) {
        $class = 'mCompanyCard_badge';
        if (strpos($badge, '성장TOP') !== false || strpos($badge, '상위TOP') !== false) $class .= ' mCompanyCard_badge_top_growth';
        else if (strpos($badge, '재무안정TOP') !== false) $class .= ' mCompanyCard_badge_top_stability';
        else if (strpos($badge, '벤처') !== false) $class .= ' mCompanyCard_badge_venture';
        $html .= '<span class="' . $class . '">' . htmlspecialchars($badge, ENT_QUOTES, 'UTF-8') . '</span>';
    }
    return $html;
}

function company_list_ai_mask_name($name)
{
    $raw = trim((string)$name);
    if ($raw === '') return '기업 상세';

    $m = array();
    preg_match('/^(\(주\)|\(유\)|\(사\))?(.*)$/u', $raw, $m);
    $prefix = isset($m[1]) ? (string)$m[1] : '';
    $body = isset($m[2]) ? (string)$m[2] : $raw;
    $visible = ($prefix !== '')
        ? mb_substr($body, 0, 1, 'UTF-8')
        : mb_substr($body, 0, 2, 'UTF-8');

    return $prefix . $visible . '****';
}

function company_list_ai_build_radar_axes($radar)
{
    $radar = is_array($radar) ? $radar : array();

    // 시안과 동일한 레이더 라벨 체계
    $label_map = array(
        'supply_chain' => '공급망 경로',
        'network_overlap' => '거래처 중첩도',
        'financial_health' => '재무 건전성',
        'market_power' => '시장 거래력',
        'trade_diversity' => '거래 다각화',
        'scale_match' => '규모 적합도',
        'company_size' => '기업 역량',
        'accessibility' => '지역 접근성',
        'industry_fit' => '업종 연관성',
        'trade_loyalty' => '거래 지속성',
        'product_fit' => '품목 보완성',
        'growth_trend' => '성장 추세',
    );

    // 시안 위치 고정: 0(상단)=supply, 1(우상)=network, 5(좌상)=financial
    // 단, network_overlap 점수가 0 이하이면 고정축에서 제외 (문서 규칙 반영)
    $fixed = array('supply_chain', 'network_overlap', 'financial_health');
    $all_axes = array_keys($radar);
    $fixed_axes = array_values(array_filter($fixed, function ($k) use ($radar) {
        if (!array_key_exists($k, $radar)) return false;
        if ($k === 'network_overlap' && (float)$radar[$k] <= 0) return false;
        return true;
    }));

    $dyn_axes = array_values(array_filter($all_axes, function ($k) use ($fixed_axes) {
        return !in_array($k, $fixed_axes, true);
    }));
    $axis_order = array_flip($all_axes);
    usort($dyn_axes, function ($a, $b) use ($radar, $axis_order) {
        $sa = (float)($radar[$a] ?? 0);
        $sb = (float)($radar[$b] ?? 0);
        if ($sa === $sb) {
            return ($axis_order[$a] ?? 0) <=> ($axis_order[$b] ?? 0);
        }
        return ($sb <=> $sa);
    });

    $slots = array(
        isset($fixed_axes[0]) ? $fixed_axes[0] : null,
        isset($fixed_axes[1]) ? $fixed_axes[1] : null,
        isset($dyn_axes[0]) ? $dyn_axes[0] : null,
        isset($dyn_axes[1]) ? $dyn_axes[1] : null,
        isset($dyn_axes[2]) ? $dyn_axes[2] : null,
        isset($fixed_axes[2]) ? $fixed_axes[2] : null,
    );

    $slots_filled = array_values(array_filter($slots, function ($k) {
        return $k !== null && $k !== '';
    }));
    $spare = array_values(array_filter($all_axes, function ($k) use ($slots_filled) {
        return !in_array($k, $slots_filled, true);
    }));

    $ordered = array();
    $si = 0;
    foreach ($slots as $k) {
        if ($k === null || $k === '') {
            $k = isset($spare[$si]) ? $spare[$si] : null;
            $si++;
        }
        if ($k !== null && $k !== '' && !in_array($k, $ordered, true)) $ordered[] = $k;
    }

    // 축이 부족하면 시안 기본 축 순서로 0점 폴백
    $fallback_order = array(
        'supply_chain',
        'network_overlap',
        'market_power',
        'accessibility',
        'scale_match',
        'financial_health',
        'trade_diversity',
        'company_size',
        'industry_fit',
        'trade_loyalty',
        'product_fit',
        'growth_trend',
    );
    foreach ($fallback_order as $k) {
        if (count($ordered) >= 6) break;
        if (!in_array($k, $ordered, true)) $ordered[] = $k;
    }

    $axes = array();
    foreach (array_slice($ordered, 0, 6) as $k) {
        $score = isset($radar[$k]) ? (float)$radar[$k] : 0;
        if ($score < 0) $score = 0;
        if ($score > 100) $score = 100;
        $axes[] = array(
            'key' => $k,
            'label' => isset($label_map[$k]) ? $label_map[$k] : $k,
            'score' => (int)round($score),
        );
    }

    return $axes;
}

function company_list_ai_radar_point($cx, $cy, $radius, $angle_deg)
{
    $rad = deg2rad($angle_deg);
    return [
        'x' => $cx + ($radius * cos($rad)),
        'y' => $cy + ($radius * sin($rad)),
    ];
}

function company_list_ai_points_to_str($points)
{
    $chunks = [];
    foreach ($points as $pt) {
        $chunks[] = number_format($pt['x'], 1, '.', '') . ',' . number_format($pt['y'], 1, '.', '');
    }
    return implode(' ', $chunks);
}

function company_list_ai_render_radar_svg($radar)
{
    $axes = company_list_ai_build_radar_axes($radar);
    $cx = 150.0;
    $cy = 110.0;
    $outer_r = 60.0;
    $angles = [-90, -30, 30, 90, 150, 210];

    $ring_scales = [0.25, 0.5, 0.75, 1.0];
    $svg = '';

    foreach ($ring_scales as $scale) {
        $ring_points = [];
        foreach ($angles as $angle) {
            $ring_points[] = company_list_ai_radar_point($cx, $cy, $outer_r * $scale, $angle);
        }
        $svg .= '<polygon points="' . company_list_ai_points_to_str($ring_points) . '" fill="none" stroke="#e5e7eb" stroke-width="0.8"/>';
    }

    foreach ($angles as $angle) {
        $edge = company_list_ai_radar_point($cx, $cy, $outer_r, $angle);
        $svg .= '<line x1="' . number_format($cx, 1, '.', '') . '" y1="' . number_format($cy, 1, '.', '') . '" x2="' . number_format($edge['x'], 1, '.', '') . '" y2="' . number_format($edge['y'], 1, '.', '') . '" stroke="#e5e7eb" stroke-width="0.6"/>';
    }

    // 레이더 폴리곤은 최소 25 + 25단위 스냅으로 시각 보정
    $snap_score = function ($v) {
        $v = (float)$v;
        if ($v < 25) return 25;
        return max(25, min(100, round($v / 25) * 25));
    };

    $shape_points = [];
    foreach ($axes as $idx => $axis) {
        $ratio = $snap_score((int)$axis['score']) / 100.0;
        $shape_points[] = company_list_ai_radar_point($cx, $cy, $outer_r * $ratio, $angles[$idx]);
    }
    $svg .= '<polygon points="' . company_list_ai_points_to_str($shape_points) . '" fill="rgba(109,40,217,0.12)" stroke="rgba(109,40,217,0.6)" stroke-width="1.8"/>';
    foreach ($shape_points as $pt) {
        $svg .= '<circle cx="' . number_format($pt['x'], 1, '.', '') . '" cy="' . number_format($pt['y'], 1, '.', '') . '" r="3" fill="rgba(109,40,217,0.8)"/>';
    }

    $label_radius = 76.0;
    foreach ($axes as $idx => $axis) {
        $label_pt = company_list_ai_radar_point($cx, $cy, $label_radius, $angles[$idx]);
        $anchor = 'middle';
        if ($idx === 1 || $idx === 2) $anchor = 'start';
        if ($idx === 4 || $idx === 5) $anchor = 'end';
        if ($idx === 0) $label_pt['y'] -= 2;
        if ($idx === 3) $label_pt['y'] += 6;

        $svg .= '<text x="' . number_format($label_pt['x'], 1, '.', '') . '" y="' . number_format($label_pt['y'], 1, '.', '') . '" text-anchor="' . $anchor . '" font-size="15" font-weight="500" fill="#444">' . htmlspecialchars($axis['label'], ENT_QUOTES, 'UTF-8') . '</text>';
    }

    return $svg;
}

function company_list_ai_radar_tag_meta($label)
{
    $label = trim((string)$label);
    $lower = strtolower($label);

    if (strpos($label, '공급망') !== false || strpos($label, '업종') !== false || strpos($lower, 'supply') !== false) {
        return ['같은 업종', 'tag-industry'];
    }
    if (strpos($label, '유사') !== false || strpos($label, '중첩') !== false || strpos($label, '네트워크') !== false || strpos($lower, 'network') !== false) {
        return ['인기 거래처', 'tag-network'];
    }
    if (strpos($label, '활성') !== false || strpos($label, '신뢰') !== false || strpos($lower, 'activity') !== false) {
        return ['높은 신뢰도', 'tag-trust'];
    }
    if (strpos($label, '재무') !== false || strpos($lower, 'financial') !== false) {
        return ['재무 안정', 'tag-finance'];
    }
    if (strpos($label, '지역') !== false || strpos($label, '접근') !== false || strpos($lower, 'access') !== false) {
        return ['같은 지역', 'tag-region'];
    }
    if (strpos($label, '규모') !== false || strpos($lower, 'scale') !== false) {
        return ['규모 유사', 'tag-scale'];
    }

    return ['추천 지표', 'tag-dimmed'];
}

function company_list_ai_render_radar_tags($radar)
{
    $axes = company_list_ai_build_radar_axes($radar);
    usort($axes, function ($a, $b) {
        return ((int)$b['score']) <=> ((int)$a['score']);
    });

    $used = [];
    $html = '';
    foreach ($axes as $axis) {
        list($text, $class) = company_list_ai_radar_tag_meta($axis['label']);
        $key = $text . '|' . $class;
        if (isset($used[$key])) continue;
        $used[$key] = true;
        $html .= '<span class="radar-tag ' . $class . '">' . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '</span>';
        if (count($used) >= 4) break;
    }

    return $html;
}

function company_list_ai_render_radar_rows($radar)
{
    $axes = company_list_ai_build_radar_axes($radar);
    $rows = '';
    foreach ($axes as $idx => $axis) {
        $score = (int)$axis['score'];
        $label = $axis['label'];
        $fill = 'rgba(140,120,210,0.50)';
        if (strpos($label, '공급망') !== false || strpos($label, '업종') !== false) $fill = 'rgba(59,108,181,0.65)';
        else if (strpos($label, '거래처') !== false || strpos($label, '유사') !== false || strpos($label, '중첩') !== false) $fill = 'rgba(230,120,100,0.55)';
        else if (strpos($label, '재무') !== false) $fill = 'rgba(100,160,220,0.60)';
        else if (strpos($label, '시장') !== false || strpos($label, '활성') !== false || strpos($label, '신뢰') !== false) $fill = 'rgba(59,150,180,0.60)';
        else if (strpos($label, '지역') !== false || strpos($label, '접근') !== false) $fill = 'rgba(100,170,200,0.55)';
        else if (strpos($label, '규모') !== false) $fill = 'rgba(140,120,210,0.50)';
        $rows .= '<div class="radar-card_bar">';
        $rows .= '<span class="radar-card_bar_label">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span>';
        $rows .= '<div class="radar-card_bar_track"><div class="radar-card_bar_fill" style="width:' . $score . '%;background:' . $fill . ';"></div></div>';
        $rows .= '<span class="radar-card_bar_value">' . $score . '</span>';
        $rows .= '</div>';
    }
    return $rows;
}

function company_list_ai_render_rank_medal($rank)
{
    $rank = (int)$rank;
    if ($rank < 1 || $rank > 3) return '';

    static $rank_uid = 0;
    $rank_uid++;

    $gradient_id = 'rg' . $rank . '_' . $rank_uid;
    $cfg = [
        1 => ['top' => '#f0d060', 'bottom' => '#c8a030', 'shadow' => '#c8a030', 'side' => '#d4b040', 'spark' => '#fff3b0'],
        2 => ['top' => '#c8c8cc', 'bottom' => '#9a9aa0', 'shadow' => '#9a9aa0', 'side' => '#b0b0b8', 'spark' => '#e8e8f0'],
        3 => ['top' => '#d4956a', 'bottom' => '#a06838', 'shadow' => '#a06838', 'side' => '#c07e50', 'spark' => '#f0d0b0'],
    ];
    $c = $cfg[$rank];

    return '<span class="ai-top3_rank" data-rank="' . $rank . '">' .
        '<span class="rank-medal">' .
            '<svg class="hex-bg" viewBox="0 0 80 88" fill="none">' .
                '<defs><linearGradient id="' . $gradient_id . '" x1="0" y1="0" x2="0" y2="1">' .
                    '<stop offset="0%" stop-color="' . $c['top'] . '"/>' .
                    '<stop offset="100%" stop-color="' . $c['bottom'] . '"/>' .
                '</linearGradient></defs>' .
                '<polygon points="40,6 72,24 72,60 40,78 8,60 8,24" fill="' . $c['shadow'] . '" opacity="0.3" transform="translate(0,3)"/>' .
                '<polygon points="40,6 72,24 72,60 40,78 8,60 8,24" fill="url(#' . $gradient_id . ')"/>' .
                '<polygon points="40,6 72,24 40,38 8,24" fill="#fff" opacity="0.12"/>' .
                '<polygon points="8,24 40,38 40,78 8,60" fill="' . $c['side'] . '" opacity="0.3"/>' .
            '</svg>' .
            '<span class="rank-label">No.</span>' .
            '<span class="rank-num">' . $rank . '</span>' .
        '</span>' .
        '<span class="sparkle-dot"><svg viewBox="0 0 20 20"><path d="M10 0 L12 8 L20 10 L12 12 L10 20 L8 12 L0 10 L8 8 Z" fill="' . $c['spark'] . '" opacity="0.8"/></svg></span>' .
        '<span class="sparkle-dot"><svg viewBox="0 0 20 20"><path d="M10 0 L12 8 L20 10 L12 12 L10 20 L8 12 L0 10 L8 8 Z" fill="' . $c['spark'] . '" opacity="0.6"/></svg></span>' .
        '<span class="sparkle-dot"><svg viewBox="0 0 20 20"><path d="M10 0 L12 8 L20 10 L12 12 L10 20 L8 12 L0 10 L8 8 Z" fill="' . $c['spark'] . '" opacity="0.4"/></svg></span>' .
        '<span class="sparkle-dot"><svg viewBox="0 0 20 20"><path d="M10 0 L12 8 L20 10 L12 12 L10 20 L8 12 L0 10 L8 8 Z" fill="' . $c['spark'] . '" opacity="0.3"/></svg></span>' .
    '</span>';
}

function company_list_ai_render_card($card, $trade_type, $rank = 0, $src_kedcd = '')
{
    $kedcd = htmlspecialchars($card['kedcd'], ENT_QUOTES, 'UTF-8');
    // 상세에서 동일 추천 컨텍스트를 복원할 수 있도록 기준기업(src) kedcd를 함께 전달
    $src_kedcd_raw = str_pad(preg_replace('/\D/', '', (string)$src_kedcd), 10, '0', STR_PAD_LEFT);
    if (strlen($src_kedcd_raw) !== 10 || preg_match('/^0+$/', $src_kedcd_raw)) {
        $src_kedcd_raw = '';
    }
    $src_kedcd_attr = htmlspecialchars($src_kedcd_raw, ENT_QUOTES, 'UTF-8');
    $title_raw = trim((string)($card['title'] ?? ''));
    $title_text = !empty($card['is_unlocked'])
        ? ($title_raw !== '' ? $title_raw : '기업 상세')
        : company_list_ai_mask_name($title_raw);
    $title = htmlspecialchars($title_text, ENT_QUOTES, 'UTF-8');
    $score = (int)$card['score'];
    // 상세 LLM 보고서 payload 생성을 위해 추천 메타를 data-*로 함께 전달
    $candidate_type = htmlspecialchars((string)($card['candidate_type'] ?? 'chain2'), ENT_QUOTES, 'UTF-8');
    $industry = htmlspecialchars((string)($card['industry'] ?? ''), ENT_QUOTES, 'UTF-8');
    $industry_nm = htmlspecialchars((string)($card['industry_nm'] ?? ''), ENT_QUOTES, 'UTF-8');
    $region = htmlspecialchars((string)($card['region'] ?? ''), ENT_QUOTES, 'UTF-8');
    $sze_attr = (int)($card['sze'] ?? 0);
    $company_age_attr = (int)($card['company_age'] ?? 0);
    $sales_growth_attr = (isset($card['sales_growth_pct']) && $card['sales_growth_pct'] !== null && $card['sales_growth_pct'] !== '') ? (string)$card['sales_growth_pct'] : '';
    $debt_ratio_attr = (isset($card['debt_ratio_pct']) && $card['debt_ratio_pct'] !== null && $card['debt_ratio_pct'] !== '') ? (string)$card['debt_ratio_pct'] : '';
    $consec_profit_attr = (isset($card['consec_profit_2y']) && $card['consec_profit_2y'] !== null && $card['consec_profit_2y'] !== '') ? ((int)$card['consec_profit_2y'] ? '1' : '0') : '';
    $capital_negative_attr = (isset($card['capital_negative']) && $card['capital_negative'] !== null && $card['capital_negative'] !== '') ? ((int)$card['capital_negative'] ? '1' : '0') : '';
    $one_line_attr = htmlspecialchars((string)($card['one_line'] ?? ''), ENT_QUOTES, 'UTF-8');
    $trade_q = (int)$trade_type;
    $card_cd = simple_crypt((string)$card['kedcd'], 'e');
    $url = '/recommendations/detail?id=' . rawurlencode((string)$card_cd) . '&trade_type=' . $trade_q;
    if ($src_kedcd_raw !== '') {
        $src_cd = simple_crypt((string)$src_kedcd_raw, 'e');
        $url .= '&src_cd=' . rawurlencode((string)$src_cd);
    }
    $is_top3 = ($rank > 0);

    $tags_html = '';
    $all_tags = isset($card['tags']) && is_array($card['tags']) ? $card['tags'] : [];
    $tags = array_slice($all_tags, 0, 3);
    $total_tag_count = isset($card['tag_total_count']) && (int)$card['tag_total_count'] > 0
        ? (int)$card['tag_total_count']
        : count($all_tags);
    foreach ($tags as $tag) {
        $tags_html .= '<span class="mCompanyCard_desc_tag">' . htmlspecialchars($tag, ENT_QUOTES, 'UTF-8') . '</span>';
    }
    if ($tags_html === '') {
        $tags_html = '<span class="mCompanyCard_desc_tag">정보 준비중</span>';
    }
    $hidden_count = max(0, $total_tag_count - count($tags));
    if ($hidden_count > 0) {
        $tags_html .= '<span class="mCompanyCard_desc_more">+' . $hidden_count . '</span>';
    }

    $badge_html = company_list_ai_render_badges($card['badges'] ?? []);
    $radar_rows = company_list_ai_render_radar_rows($card['radar'] ?? []);
    $radar_svg = company_list_ai_render_radar_svg($card['radar'] ?? []);
    $radar_tags = company_list_ai_render_radar_tags($card['radar'] ?? []);
    $html = '<div class="swiper-slide">';
    if ($is_top3) $html .= company_list_ai_render_rank_medal($rank);
    $html .= '<a href="' . $url . '" class="company-ai-link" data-kedcd="' . $kedcd . '" data-src-kedcd="' . $src_kedcd_attr . '" data-trade-type="' . $trade_q . '" data-score="' . $score . '" data-candidate-type="' . $candidate_type . '" data-bzc-cd="' . $industry . '" data-bzc-nm="' . $industry_nm . '" data-sido="' . $region . '" data-sze="' . $sze_attr . '" data-company-age="' . $company_age_attr . '" data-sales-growth-pct="' . htmlspecialchars($sales_growth_attr, ENT_QUOTES, 'UTF-8') . '" data-debt-ratio-pct="' . htmlspecialchars($debt_ratio_attr, ENT_QUOTES, 'UTF-8') . '" data-consec-profit-2y="' . $consec_profit_attr . '" data-capital-negative="' . $capital_negative_attr . '" data-one-line="' . $one_line_attr . '" style="text-decoration:none;color:inherit;">';
    $html .= '<div class="mCompanyCard company-ai-card" style="position:relative;">';
    $html .= '<div class="mCompanyCard_content">';
    $html .= '<div class="mCompanyCard_ai_match"><span class="mCompanyCard_ai_match_label">AI 추천점수</span><span><span class="mCompanyCard_ai_match_value">' . $score . '</span><span class="mCompanyCard_ai_match_unit">    점</span></span></div>';
    $html .= '<div class="mCompanyCard_badges">' . $badge_html . '</div>';
    $html .= '<h3 class="mCompanyCard_title">' . $title . '</h3>';
    $html .= '<p class="mCompanyCard_desc">' . $tags_html . '</p>';
    $html .= '<div class="radar-card company-ai-radar radar-card--hex">';
    $html .= '<div class="radar-card_chart"><svg viewBox="0 15 300 195" class="radar-svg">' . $radar_svg . '</svg></div>';
    $html .= '<div class="radar-card_scores">' . $radar_rows . '</div>';
    $html .= '<div class="radar-card_tags">' . $radar_tags . '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</a>';
    $html .= '</div>';

    return $html;
}

function company_list_ai_build_slide_sets($cards, $trade_type = 1, $src_kedcd = '')
{
    $top3 = array();
    $rest = array();
    foreach ($cards as $idx => $card) {
        if ($idx < 3) $top3[] = company_list_ai_render_card($card, $trade_type, $idx + 1, $src_kedcd);
        else $rest[] = company_list_ai_render_card($card, $trade_type, 0, $src_kedcd);
    }
    return array('top3' => $top3, 'rest' => $rest);
}

function company_list_ai_parse_partner_bzc_filters($value)
{
    $filters = array();
    $parts = preg_split('/\s*,\s*/', (string)$value);
    foreach ($parts as $part) {
        $code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string)$part), 0, 10));
        if ($code === '' || in_array($code, $filters, true)) continue;
        $filters[] = $code;
    }
    return $filters;
}

function company_list_ai_parse_partner_cd_filters($value)
{
    $filters = array();
    $parts = preg_split('/\s*,\s*/', (string)$value);
    foreach ($parts as $part) {
        $token = trim((string)$part);
        if ($token === '') continue;
        $decoded = simple_crypt($token, 'd');
        if (!is_string($decoded) || $decoded === '') continue;
        $code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $decoded), 0, 10));
        if ($code === '' || in_array($code, $filters, true)) continue;
        $filters[] = $code;
    }
    return $filters;
}

function company_list_ai_parse_bizno_query($value)
{
    $raw = trim((string)$value);
    if ($raw === '') return '';

    if (preg_match('/^[0-9]{10}$/', $raw)) return $raw;

    $decoded = simple_crypt($raw, 'd');
    if (!is_string($decoded) || $decoded === '') return '';

    $decoded_digits = preg_replace('/\D/', '', $decoded);
    return strlen($decoded_digits) === 10 ? $decoded_digits : '';
}

function company_list_ai_filter_results_by_partner_bzc($results, $partner_bzc_filters)
{
    if (!is_array($results)) return array();
    if (empty($partner_bzc_filters)) return $results;

    $filtered = array();
    foreach ($results as $item) {
        if (!is_array($item)) continue;
        $bzc_cd = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string)($item['bzc_cd'] ?? ($item['bzcCd'] ?? ''))), 0, 10));
        if ($bzc_cd === '') continue;
        if (in_array($bzc_cd, $partner_bzc_filters, true)) {
            $filtered[] = $item;
        }
    }

    return $filtered;
}

function company_list_ai_add_partner_bzc_filter_payload($payload, $partner_bzc_filters, $partner_bzc_weights = array())
{
    if (!is_array($payload) || empty($partner_bzc_filters)) return $payload;

    $filter_bzc_cds = array();
    $filter_bzc_weights = array();
    foreach ($partner_bzc_filters as $code) {
        $normalized = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string)$code), 0, 10));
        if ($normalized === '' || in_array($normalized, $filter_bzc_cds, true)) continue;
        $filter_bzc_cds[] = $normalized;
        $weight_idx = count($filter_bzc_cds) - 1;
        $filter_bzc_weights[] = max(1, (int)($partner_bzc_weights[$weight_idx] ?? 1));
    }

    if (!empty($filter_bzc_cds)) {
        $payload['filter_bzc_cds'] = $filter_bzc_cds;
        $payload['filter_bzc_weights'] = $filter_bzc_weights;
    }

    return $payload;
}

$buyer_cards = array();
$seller_cards = array();
$buy_total_stat = count($buyer_cards);
$sell_total_stat = count($seller_cards);
$buy_live_ok = false;
$sell_live_ok = false;
$buy_result_source = 'not_requested';
$sell_result_source = 'not_requested';
$buy_call = array('ok' => false, 'error' => '', 'http_code' => 0);
$sell_call = array('ok' => false, 'error' => '', 'http_code' => 0);
$unlocked_kedcd_map = company_list_ai_fetch_unlocked_kedcd_map_for_member($member['mb_id'] ?? '');

$kedcd_query = str_pad(preg_replace('/\D/', '', (string)($_GET['kedcd'] ?? '')), 10, '0', STR_PAD_LEFT);
$has_kedcd_query = isset($_GET['kedcd']) && trim((string)$_GET['kedcd']) !== '';
$has_kedcd_query_invalid = $has_kedcd_query && (strlen($kedcd_query) !== 10 || preg_match('/^0+$/', $kedcd_query));
$bizno_query_raw = trim((string)($_GET['bizno'] ?? ($_GET['comnum'] ?? '')));
$bizno_query = company_list_ai_parse_bizno_query($bizno_query_raw);
$has_bizno_query = ($bizno_query_raw !== '');
$has_valid_bizno_query = (strlen($bizno_query) === 10);
$has_bizno_query_invalid = $has_bizno_query && !$has_valid_bizno_query;
$has_valid_source_input = (($has_kedcd_query && !$has_kedcd_query_invalid) || $has_valid_bizno_query);
$partner_bzc_filters = company_list_ai_parse_partner_cd_filters($_GET['partner_cd'] ?? '');
if (empty($partner_bzc_filters)) {
    $partner_bzc_filters = company_list_ai_parse_partner_bzc_filters($_GET['partner_bzc'] ?? '');
}
// [추천 방향 확정]
// 구매처 찾기와 판매처 찾기가 섞이지 않도록 목록 진입 시 trade_type을 반드시 1 또는 2로 확정한다.
// 기존 링크처럼 trade_type이 없으면 type=seller만 판매처(2), 나머지는 구매처(1)로 보정한다.
$requested_type = trim((string)($_GET['type'] ?? ''));
$partner_filter_trade_type = (int)($_GET['trade_type'] ?? 0);
if (!in_array($partner_filter_trade_type, array(1, 2), true)) {
    // 전체 추천처럼 trade_type이 없는 기존 링크도 type을 기준으로 반드시 한 방향만 선택한다.
    $partner_filter_trade_type = ($requested_type === 'seller') ? 2 : 1;
}
$requested_type = ($partner_filter_trade_type === 2) ? 'seller' : 'buyer';
$partner_bzc_weights = array_map('intval', preg_split('/\s*,\s*/', trim((string)($_GET['partner_weight'] ?? '')), -1, PREG_SPLIT_NO_EMPTY));
$partner_bzc_weights = array_slice($partner_bzc_weights, 0, count($partner_bzc_filters));
while (count($partner_bzc_weights) < count($partner_bzc_filters)) $partner_bzc_weights[] = 1;
$selected_keywords = array_values(array_filter(array_map('trim', preg_split('/\s*,\s*/u', (string)($_GET['selected_keyword'] ?? '')))));
$keyword_source_bzc_cds = company_list_ai_parse_partner_bzc_filters($_GET['keyword_source_bzc'] ?? '');
// [추천 흐름 추적 ID]
// 키워드 조회부터 캐시 조회, EC2 요청/응답, 카드 출력까지 동일한 trace_id로 연결한다.
$trace_id = company_list_ai_normalize_trace_id($_GET['trace_id'] ?? '');
$has_partner_bzc_filters = !empty($partner_bzc_filters);
$use_buy_partner_filter = $has_partner_bzc_filters && $partner_filter_trade_type === 1;
$use_sell_partner_filter = $has_partner_bzc_filters && $partner_filter_trade_type === 2;
$resolved_base_payload = array();
$input_error_notice = '';
$resolve_error_notice = '';

if (!$has_valid_source_input && ($has_kedcd_query_invalid || $has_bizno_query_invalid)) {
    $input_error_notice = '입력값이 올바르지 않습니다. kedcd/bizno는 10자리 숫자여야 합니다.';
}
// 상세 링크에 실어 보낼 "추천의 기준 기업" kedcd
$source_kedcd_for_detail = '';

// 진입 파라미터는 kedcd 직접 입력 또는 bizno(comnum) 둘 다 지원
if (($has_kedcd_query && strlen($kedcd_query) === 10 && !preg_match('/^0+$/', $kedcd_query)) || $has_valid_bizno_query) {
    include_once(G5_PATH . '/ko/helper/company_list_ai_helper.php');

    if ($has_kedcd_query && strlen($kedcd_query) === 10 && !preg_match('/^0+$/', $kedcd_query)) {
        $resolved_base_payload = company_list_ai_build_base_payload($_GET);
        $resolved_base_payload['kedcd'] = $kedcd_query;
    } else if ($has_valid_bizno_query) {
        $resolved = company_list_ai_resolve_base_payload_by_bizno($bizno_query, true, array(
            'trace_id' => $trace_id,
            'trade_type' => $partner_filter_trade_type,
        ));
        if (!empty($resolved['ok']) && !empty($resolved['base_payload']) && is_array($resolved['base_payload'])) {
            $resolved_base_payload = $resolved['base_payload'];
            $query_override = company_list_ai_build_base_payload(array_merge($_GET, array(
                'kedcd' => isset($resolved_base_payload['kedcd']) ? $resolved_base_payload['kedcd'] : ''
            )));
            $resolved_base_payload = array_merge($resolved_base_payload, $query_override);
        } else {
            $resolve_error_notice = trim((string)($resolved['error'] ?? ''));
            if ($resolve_error_notice === '') {
                $resolve_error_notice = '기준 기업 정보를 불러오지 못했습니다.';
            }
        }
    }
}

if (!empty($resolved_base_payload['kedcd'])) {
    $source_kedcd_for_detail = str_pad(preg_replace('/\D/', '', (string)$resolved_base_payload['kedcd']), 10, '0', STR_PAD_LEFT);
    if (strlen($source_kedcd_for_detail) !== 10 || preg_match('/^0+$/', $source_kedcd_for_detail)) {
        $source_kedcd_for_detail = '';
    }

    // [EC2 요청 payload 구성]
    // 구매처 찾기는 trade_type=1 payload만, 판매처 찾기는 trade_type=2 payload만 실제 조회한다.
    // 키워드 추천이면 사용자가 고른 상대 업종코드와 선택 횟수 기반 가중치를 해당 방향 payload에 추가한다.
    $base_payload = $resolved_base_payload;
    $buy_payload = $base_payload;
    $buy_payload['trade_type'] = 1;
    if ($use_buy_partner_filter) {
        $buy_payload = company_list_ai_add_partner_bzc_filter_payload($buy_payload, $partner_bzc_filters, $partner_bzc_weights);
    }

    $sell_payload = $base_payload;
    $sell_payload['trade_type'] = 2;
    if ($use_sell_partner_filter) {
        $sell_payload = company_list_ai_add_partner_bzc_filter_payload($sell_payload, $partner_bzc_filters, $partner_bzc_weights);
    }

    // [한 달 추천 캐시 조건 구성]
    // 전체/키워드 추천 모두 한 달 이내 company_ml_log를 우선 사용한다.
    // 키워드 추천은 업종코드+가중치 조합이 완전히 같은 캐시만 복원하여 다른 선택 조건과 섞이지 않게 한다.
    $ml_log_bizno = $has_valid_bizno_query ? $bizno_query : '';
    $ml_log_kedcd = isset($resolved_base_payload['kedcd']) ? (string)$resolved_base_payload['kedcd'] : '';
    $recommendation_mode = $has_partner_bzc_filters ? 'keyword' : 'full';
    $partner_bzc_cds_for_log = implode(',', $partner_bzc_filters);
    $buy_cache_context = company_list_ai_normalize_ml_cache_context($use_buy_partner_filter ? array(
        'filter_bzc_cds' => $partner_bzc_filters,
        'filter_bzc_weights' => $partner_bzc_weights,
    ) : array());
    $sell_cache_context = company_list_ai_normalize_ml_cache_context($use_sell_partner_filter ? array(
        'filter_bzc_cds' => $partner_bzc_filters,
        'filter_bzc_weights' => $partner_bzc_weights,
    ) : array());
    $buy_cache_policy = $buy_cache_context['recommendation_mode'] . '_allow_1month_ml_log';
    $sell_cache_policy = $sell_cache_context['recommendation_mode'] . '_allow_1month_ml_log';
    $cache_policy_for_log = ($buy_cache_policy === $sell_cache_policy)
        ? $buy_cache_policy
        : 'mixed_allow_1month_ml_log';
    $trace_context = array(
        'trace_id' => $trace_id,
        'keywords' => implode(',', $selected_keywords),
        'match_type' => $has_partner_bzc_filters ? 'selected_keyword' : 'full',
    );

    // [추천 진입 로그]
    // 기준 기업, 선택 방향, 키워드, 상대 업종코드와 가중치를 최초 스냅샷으로 기록한다.
    company_list_ai_write_event_log('recommend_entry', 'success', array(
        'action' => 'list_enter',
        'trace_id' => $trace_id,
        'stage' => 'entry',
        'source' => 'company_list_ai',
        'recommendation_mode' => $recommendation_mode,
        'requested_type' => $requested_type,
        'trade_type' => $partner_filter_trade_type,
        'bizno' => $ml_log_bizno,
        'kedcd' => $ml_log_kedcd,
        'selected_trade_type' => $partner_filter_trade_type,
        'source_bzc_cd' => (string)($resolved_base_payload['bzc_cd'] ?? ''),
        'partner_bzc_count' => count($partner_bzc_filters),
        'partner_bzc_cds' => $partner_bzc_cds_for_log,
        'filter_bzc_count' => count($partner_bzc_filters),
        'filter_bzc_cds' => $partner_bzc_cds_for_log,
        'filter_bzc_weights' => $has_partner_bzc_filters ? implode(',', $partner_bzc_weights) : '',
        'cache_policy' => $cache_policy_for_log,
        'keywords' => implode(',', $selected_keywords),
        'matched_source_bzc_cds' => implode(',', $keyword_source_bzc_cds),
        'request_data' => array(
            'resolved_base_payload' => $resolved_base_payload,
            'selected_keywords' => $selected_keywords,
            'keyword_source_bzc_cds' => $keyword_source_bzc_cds,
            'filter_bzc_cds' => $partner_bzc_filters,
            'filter_bzc_weights' => $partner_bzc_weights,
        ),
    ));

    if ($partner_filter_trade_type === 1) {
        // [구매처 추천]
        // 구매처 한 달 캐시를 먼저 조회하고, 캐시 미스일 때만 EC2에 trade_type=1로 요청한다.
        $buy_call = null;
        $buy_cached_call = null;
        company_list_ai_write_event_log('recommend_request', 'success', array(
        'trace_id' => $trace_id,
        'stage' => 'request',
        'action' => 'cache_lookup',
        'source' => 'company_ml_log',
        'recommendation_mode' => $buy_cache_context['recommendation_mode'],
        'bizno' => $ml_log_bizno,
        'kedcd' => $ml_log_kedcd,
        'trade_type' => 1,
        'source_bzc_cd' => (string)($resolved_base_payload['bzc_cd'] ?? ''),
        'matched_source_bzc_cds' => implode(',', $keyword_source_bzc_cds),
        'keywords' => implode(',', $selected_keywords),
        'filter_bzc_cds' => implode(',', $buy_cache_context['filter_bzc_cds']),
        'filter_bzc_weights' => implode(',', $buy_cache_context['filter_bzc_weights']),
        'cache_policy' => $buy_cache_policy,
        'request_data' => $buy_payload,
        ), '추천 요청 조건 및 한 달 캐시 조회');
        $buy_cached_row = company_list_ai_pick_ml_log($ml_log_bizno, $ml_log_kedcd, 1, false, 1, $buy_cache_context);
        $buy_cached_call = company_list_ai_restore_call_from_ml_log($buy_cached_row);
        if (is_array($buy_cached_call)) {
            $buy_call = $buy_cached_call;
            $buy_result_source = 'ml_cache_1m';
            company_list_ai_write_event_log('recommend_response', 'success', array(
            'trace_id' => $trace_id,
            'stage' => 'response',
            'source' => 'company_ml_log',
            'recommendation_mode' => $buy_cache_context['recommendation_mode'],
            'bizno' => $ml_log_bizno,
            'kedcd' => $ml_log_kedcd,
            'trade_type' => 1,
            'keywords' => implode(',', $selected_keywords),
            'filter_bzc_cds' => implode(',', $buy_cache_context['filter_bzc_cds']),
            'filter_bzc_weights' => implode(',', $buy_cache_context['filter_bzc_weights']),
            'from_cache' => 1,
            'result_count' => count((array)($buy_call['data']['results'] ?? array())),
            'response_data' => company_list_ai_summarize_recommend_results($buy_call['data']),
            ), '한 달 추천 캐시 응답 사용');
        } else {
            $buy_call = company_list_ai_call_recommend($buy_payload, 8, $trace_context);
            $buy_result_source = !empty($buy_call['ok']) ? 'ec2' : 'error';
        }
    }

    if ($partner_filter_trade_type === 2) {
        // [판매처 추천]
        // 판매처 한 달 캐시를 먼저 조회하고, 캐시 미스일 때만 EC2에 trade_type=2로 요청한다.
        $sell_call = null;
        $sell_cached_call = null;
        company_list_ai_write_event_log('recommend_request', 'success', array(
        'trace_id' => $trace_id,
        'stage' => 'request',
        'action' => 'cache_lookup',
        'source' => 'company_ml_log',
        'recommendation_mode' => $sell_cache_context['recommendation_mode'],
        'bizno' => $ml_log_bizno,
        'kedcd' => $ml_log_kedcd,
        'trade_type' => 2,
        'source_bzc_cd' => (string)($resolved_base_payload['bzc_cd'] ?? ''),
        'matched_source_bzc_cds' => implode(',', $keyword_source_bzc_cds),
        'keywords' => implode(',', $selected_keywords),
        'filter_bzc_cds' => implode(',', $sell_cache_context['filter_bzc_cds']),
        'filter_bzc_weights' => implode(',', $sell_cache_context['filter_bzc_weights']),
        'cache_policy' => $sell_cache_policy,
        'request_data' => $sell_payload,
        ), '추천 요청 조건 및 한 달 캐시 조회');
        $sell_cached_row = company_list_ai_pick_ml_log($ml_log_bizno, $ml_log_kedcd, 2, false, 1, $sell_cache_context);
        $sell_cached_call = company_list_ai_restore_call_from_ml_log($sell_cached_row);
        if (is_array($sell_cached_call)) {
            $sell_call = $sell_cached_call;
            $sell_result_source = 'ml_cache_1m';
            company_list_ai_write_event_log('recommend_response', 'success', array(
            'trace_id' => $trace_id,
            'stage' => 'response',
            'source' => 'company_ml_log',
            'recommendation_mode' => $sell_cache_context['recommendation_mode'],
            'bizno' => $ml_log_bizno,
            'kedcd' => $ml_log_kedcd,
            'trade_type' => 2,
            'keywords' => implode(',', $selected_keywords),
            'filter_bzc_cds' => implode(',', $sell_cache_context['filter_bzc_cds']),
            'filter_bzc_weights' => implode(',', $sell_cache_context['filter_bzc_weights']),
            'from_cache' => 1,
            'result_count' => count((array)($sell_call['data']['results'] ?? array())),
            'response_data' => company_list_ai_summarize_recommend_results($sell_call['data']),
            ), '한 달 추천 캐시 응답 사용');
        } else {
            $sell_call = company_list_ai_call_recommend($sell_payload, 8, $trace_context);
            $sell_result_source = !empty($sell_call['ok']) ? 'ec2' : 'error';
        }
    }

    if ($partner_filter_trade_type === 1) company_list_ai_write_event_log('recommend_result_source', !empty($buy_call['ok']) ? 'success' : 'fail', array(
        'action' => 'result_source',
        'trace_id' => $trace_id,
        'stage' => 'response',
        'source' => $buy_result_source,
        'recommendation_mode' => $buy_cache_context['recommendation_mode'],
        'bizno' => $ml_log_bizno,
        'kedcd' => $ml_log_kedcd,
        'trade_type' => 1,
        'source_bzc_cd' => (string)($resolved_base_payload['bzc_cd'] ?? ''),
        'filter_bzc_count' => count($buy_cache_context['filter_bzc_cds']),
        'filter_bzc_cds' => implode(',', $buy_cache_context['filter_bzc_cds']),
        'filter_bzc_weights' => implode(',', $buy_cache_context['filter_bzc_weights']),
        'from_cache' => !empty($buy_call['from_cache']),
        'result_count' => (isset($buy_call['data']['results']) && is_array($buy_call['data']['results'])) ? count($buy_call['data']['results']) : 0,
    ));
    if ($partner_filter_trade_type === 2) company_list_ai_write_event_log('recommend_result_source', !empty($sell_call['ok']) ? 'success' : 'fail', array(
        'action' => 'result_source',
        'trace_id' => $trace_id,
        'stage' => 'response',
        'source' => $sell_result_source,
        'recommendation_mode' => $sell_cache_context['recommendation_mode'],
        'bizno' => $ml_log_bizno,
        'kedcd' => $ml_log_kedcd,
        'trade_type' => 2,
        'source_bzc_cd' => (string)($resolved_base_payload['bzc_cd'] ?? ''),
        'filter_bzc_count' => count($sell_cache_context['filter_bzc_cds']),
        'filter_bzc_cds' => implode(',', $sell_cache_context['filter_bzc_cds']),
        'filter_bzc_weights' => implode(',', $sell_cache_context['filter_bzc_weights']),
        'from_cache' => !empty($sell_call['from_cache']),
        'result_count' => (isset($sell_call['data']['results']) && is_array($sell_call['data']['results'])) ? count($sell_call['data']['results']) : 0,
    ));

    // [추천 응답 캐시 저장]
    // 캐시 미스 후 EC2 실호출이 성공한 경우에만 응답과 필터 조합을 company_ml_log에 적재한다.
    if ($partner_filter_trade_type === 1 && !empty($buy_call['ok']) && empty($buy_call['from_cache'])) {
        company_list_ai_save_ml_log($ml_log_bizno, $ml_log_kedcd, 1, $buy_call, array_merge($buy_cache_context, array('trace_id' => $trace_id)));
    }
    if ($partner_filter_trade_type === 2 && !empty($sell_call['ok']) && empty($sell_call['from_cache'])) {
        company_list_ai_save_ml_log($ml_log_bizno, $ml_log_kedcd, 2, $sell_call, array_merge($sell_cache_context, array('trace_id' => $trace_id)));
    }

    // [추천 응답 -> 화면 카드 변환]
    // 선택 방향의 EC2 결과만 업종 필터링하고, 기업 키워드/배지 정보를 합쳐 화면 카드 데이터로 변환한다.
    if ($partner_filter_trade_type === 1 && !empty($buy_call['ok']) && !empty($buy_call['data']['results']) && is_array($buy_call['data']['results'])) {
        $buy_live_ok = true;
        $buy_results = company_list_ai_filter_results_by_partner_bzc($buy_call['data']['results'], $use_buy_partner_filter ? $partner_bzc_filters : array());
        $buy_bucket = company_list_ai_build_bucket($buy_results, 45);
        $buy_items = array_merge($buy_bucket['top3'], $buy_bucket['list']);
        $buy_keyword_map = company_list_ai_fetch_company_keyword_map(array_column($buy_items, 'kedcd'));
        $buy_badge_map = company_list_ai_fetch_company_badge_map(array_column($buy_items, 'kedcd'));
        $mapped_buy = array();
        foreach ($buy_items as $bi) {
            $item_kedcd = str_pad(preg_replace('/\D/', '', (string)($bi['kedcd'] ?? '')), 10, '0', STR_PAD_LEFT);
            if (isset($buy_keyword_map[$item_kedcd]) && is_array($buy_keyword_map[$item_kedcd])) {
                $kw_meta = $buy_keyword_map[$item_kedcd];
                if (!empty($kw_meta['keywords_json'])) $bi['keywords_json'] = $kw_meta['keywords_json'];
                if (!empty($kw_meta['tags']) && is_array($kw_meta['tags'])) $bi['tags'] = $kw_meta['tags'];
                if (isset($kw_meta['keyword_count'])) $bi['keyword_count'] = (int)$kw_meta['keyword_count'];
            }
            if (isset($buy_badge_map[$item_kedcd]) && is_array($buy_badge_map[$item_kedcd])) {
                $bg_meta = $buy_badge_map[$item_kedcd];
                $bi['badge_growth'] = $bg_meta['badge_growth'] ?? '';
                $bi['badge_stability'] = $bg_meta['badge_stability'] ?? '';
                $bi['badge_venture'] = $bg_meta['badge_venture'] ?? 'N';
            }
            $card = company_list_ai_ml_item_to_card($bi);
            if (is_array($card)) $mapped_buy[] = $card;
        }
        $buyer_cards = $mapped_buy;
        $buy_total_stat = isset($buy_bucket['total']) ? (int)$buy_bucket['total'] : count($mapped_buy);
    } else if ($partner_filter_trade_type === 1 && !empty($buy_call['ok'])) {
        // 실호출 성공 + 0건도 실제 결과로 취급
        $buy_live_ok = true;
        $buyer_cards = array();
        $buy_total_stat = 0;
    }

    if ($partner_filter_trade_type === 2 && !empty($sell_call['ok']) && !empty($sell_call['data']['results']) && is_array($sell_call['data']['results'])) {
        $sell_live_ok = true;
        $sell_results = company_list_ai_filter_results_by_partner_bzc($sell_call['data']['results'], $use_sell_partner_filter ? $partner_bzc_filters : array());
        $sell_bucket = company_list_ai_build_bucket($sell_results, 45);
        $sell_items = array_merge($sell_bucket['top3'], $sell_bucket['list']);
        $sell_keyword_map = company_list_ai_fetch_company_keyword_map(array_column($sell_items, 'kedcd'));
        $sell_badge_map = company_list_ai_fetch_company_badge_map(array_column($sell_items, 'kedcd'));
        $mapped_sell = array();
        foreach ($sell_items as $si) {
            $item_kedcd = str_pad(preg_replace('/\D/', '', (string)($si['kedcd'] ?? '')), 10, '0', STR_PAD_LEFT);
            if (isset($sell_keyword_map[$item_kedcd]) && is_array($sell_keyword_map[$item_kedcd])) {
                $kw_meta = $sell_keyword_map[$item_kedcd];
                if (!empty($kw_meta['keywords_json'])) $si['keywords_json'] = $kw_meta['keywords_json'];
                if (!empty($kw_meta['tags']) && is_array($kw_meta['tags'])) $si['tags'] = $kw_meta['tags'];
                if (isset($kw_meta['keyword_count'])) $si['keyword_count'] = (int)$kw_meta['keyword_count'];
            }
            if (isset($sell_badge_map[$item_kedcd]) && is_array($sell_badge_map[$item_kedcd])) {
                $bg_meta = $sell_badge_map[$item_kedcd];
                $si['badge_growth'] = $bg_meta['badge_growth'] ?? '';
                $si['badge_stability'] = $bg_meta['badge_stability'] ?? '';
                $si['badge_venture'] = $bg_meta['badge_venture'] ?? 'N';
            }
            $card = company_list_ai_ml_item_to_card($si);
            if (is_array($card)) $mapped_sell[] = $card;
        }
        $seller_cards = $mapped_sell;
        $sell_total_stat = isset($sell_bucket['total']) ? (int)$sell_bucket['total'] : count($mapped_sell);
    } else if ($partner_filter_trade_type === 2 && !empty($sell_call['ok'])) {
        // 실호출 성공 + 0건도 실제 결과로 취급
        $sell_live_ok = true;
        $seller_cards = array();
        $sell_total_stat = 0;
    }

    // [최종 카드 출력 로그]
    // 사용자에게 실제 출력된 카드만 선택 방향별로 기록하여 요청 결과와 화면 결과를 대조할 수 있게 한다.
    if ($partner_filter_trade_type === 1) company_list_ai_write_event_log('recommend_card_output', !empty($buy_call['ok']) ? 'success' : 'fail', array(
        'trace_id' => $trace_id,
        'stage' => 'card_output',
        'source' => 'company_list_ai',
        'recommendation_mode' => $buy_cache_context['recommendation_mode'],
        'bizno' => $ml_log_bizno,
        'kedcd' => $ml_log_kedcd,
        'trade_type' => 1,
        'source_bzc_cd' => (string)($resolved_base_payload['bzc_cd'] ?? ''),
        'keywords' => implode(',', $selected_keywords),
        'filter_bzc_cds' => implode(',', $buy_cache_context['filter_bzc_cds']),
        'filter_bzc_weights' => implode(',', $buy_cache_context['filter_bzc_weights']),
        'from_cache' => !empty($buy_call['from_cache']),
        'result_count' => count($buyer_cards),
        'error_code' => !empty($buy_call['ok']) ? '' : 'recommend_failed',
        'output_data' => company_list_ai_summarize_cards_for_log($buyer_cards),
    ), !empty($buy_call['ok']) ? '구매처 추천 카드 출력 완료' : '구매처 추천 실패로 카드 출력 불가');
    if ($partner_filter_trade_type === 2) company_list_ai_write_event_log('recommend_card_output', !empty($sell_call['ok']) ? 'success' : 'fail', array(
        'trace_id' => $trace_id,
        'stage' => 'card_output',
        'source' => 'company_list_ai',
        'recommendation_mode' => $sell_cache_context['recommendation_mode'],
        'bizno' => $ml_log_bizno,
        'kedcd' => $ml_log_kedcd,
        'trade_type' => 2,
        'source_bzc_cd' => (string)($resolved_base_payload['bzc_cd'] ?? ''),
        'keywords' => implode(',', $selected_keywords),
        'filter_bzc_cds' => implode(',', $sell_cache_context['filter_bzc_cds']),
        'filter_bzc_weights' => implode(',', $sell_cache_context['filter_bzc_weights']),
        'from_cache' => !empty($sell_call['from_cache']),
        'result_count' => count($seller_cards),
        'error_code' => !empty($sell_call['ok']) ? '' : 'recommend_failed',
        'output_data' => company_list_ai_summarize_cards_for_log($seller_cards),
    ), !empty($sell_call['ok']) ? '판매처 추천 카드 출력 완료' : '판매처 추천 실패로 카드 출력 불가');
}

if ($source_kedcd_for_detail === '' && $has_kedcd_query && strlen($kedcd_query) === 10 && !preg_match('/^0+$/', $kedcd_query)) {
    $source_kedcd_for_detail = $kedcd_query;
}

$buy_pool = $buy_live_ok ? $buyer_cards : array();
$sell_pool = $sell_live_ok ? $seller_cards : array();

// company_detail.php와 동일한 해제 기준으로 목록 카드 마스킹 상태를 서버에서 확정한다.
$buy_pool = company_list_ai_apply_unlock_state($buy_pool, $unlocked_kedcd_map);
$sell_pool = company_list_ai_apply_unlock_state($sell_pool, $unlocked_kedcd_map);

$buy_slides = company_list_ai_build_slide_sets($buy_pool, 1, $source_kedcd_for_detail);
$sell_slides = company_list_ai_build_slide_sets($sell_pool, 2, $source_kedcd_for_detail);

$buy_top3 = isset($buy_slides['top3']) ? $buy_slides['top3'] : array();
$buy_rest_pool = isset($buy_slides['rest']) ? $buy_slides['rest'] : array();
$buy_rest_first = array_slice($buy_rest_pool, 0, 9);
$sell_top3 = isset($sell_slides['top3']) ? $sell_slides['top3'] : array();
$sell_rest_pool = isset($sell_slides['rest']) ? $sell_slides['rest'] : array();

$buy_top3_notice = '';
$buy_rest_notice = '';
$sell_top3_notice = '';
$sell_rest_notice = '';

if ($input_error_notice !== '') {
    $buy_top3_notice = $input_error_notice;
    $buy_rest_notice = $input_error_notice;
    $sell_top3_notice = $input_error_notice;
    $sell_rest_notice = $input_error_notice;
} else {
    if (empty($buy_top3)) $buy_top3_notice = !empty($resolve_error_notice) ? $resolve_error_notice : (!empty($buy_call['error']) ? '구매 추천 데이터를 불러오지 못했습니다.' : '구매 추천 결과가 없습니다.');
    if (empty($buy_rest_pool)) $buy_rest_notice = !empty($resolve_error_notice) ? $resolve_error_notice : '추가 구매 추천 결과가 없습니다.';
    if (empty($sell_top3)) $sell_top3_notice = !empty($resolve_error_notice) ? $resolve_error_notice : (!empty($sell_call['error']) ? '판매 추천 데이터를 불러오지 못했습니다.' : '판매 추천 결과가 없습니다.');
    if (empty($sell_rest_pool)) $sell_rest_notice = !empty($resolve_error_notice) ? $resolve_error_notice : '추가 판매 추천 결과가 없습니다.';
}

$active_top3 = ($partner_filter_trade_type === 2) ? $sell_top3 : $buy_top3;
$active_rest_pool = ($partner_filter_trade_type === 2) ? $sell_rest_pool : $buy_rest_pool;
$active_rest_first = array_slice($active_rest_pool, 0, 9);
$active_top3_notice = ($partner_filter_trade_type === 2) ? $sell_top3_notice : $buy_top3_notice;
$active_rest_notice = ($partner_filter_trade_type === 2) ? $sell_rest_notice : $buy_rest_notice;

// 시안과 동일하게 배너 통계는 고정 노출
$banner_buy_stat = 12;
$banner_sell_stat = 12;

?>

<div id="listWrap" class="company-list-ai-real">
    <div class="ai-loading_overlay is_open company-ai-loading-overlay" id="companyAiLoadingOverlay" aria-live="polite" aria-busy="true">
        <div class="ai-loading_card company-ai-loading-card" role="status">
            <div class="ai-loading_spinner" aria-hidden="true"></div>
            <h3 class="ai-loading_title">AI가 거래처를 분석하고 있습니다</h3>
            <div class="ai-loading_steps" id="companyAiLoadingSteps">
                <div class="ai-loading_step" data-step="0">
                    <span class="ai-loading_step_icon"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"></path></svg></span>
                    <span class="ai-loading_step_text" data-pending="공급망 근접도 분석 중..." data-done="공급망 근접도 분석 완료 ✓">공급망 근접도 분석 중...</span>
                </div>
                <div class="ai-loading_step" data-step="1">
                    <span class="ai-loading_step_icon"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"></path></svg></span>
                    <span class="ai-loading_step_text" data-pending="거래처 유사성 분석 중..." data-done="거래처 유사성 분석 완료 ✓">거래처 유사성 분석 중...</span>
                </div>
                <div class="ai-loading_step" data-step="2">
                    <span class="ai-loading_step_icon"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"></path></svg></span>
                    <span class="ai-loading_step_text" data-pending="거래 활성도 분석 중..." data-done="거래 활성도 분석 완료 ✓">거래 활성도 분석 중...</span>
                </div>
                <div class="ai-loading_step" data-step="3">
                    <span class="ai-loading_step_icon"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"></path></svg></span>
                    <span class="ai-loading_step_text" data-pending="재무 안정성 분석 중..." data-done="재무 안정성 분석 완료 ✓">재무 안정성 분석 중...</span>
                </div>
                <div class="ai-loading_step" data-step="4">
                    <span class="ai-loading_step_icon"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"></path></svg></span>
                    <span class="ai-loading_step_text" data-pending="지역 접근성 분석 중..." data-done="지역 접근성 분석 완료 ✓">지역 접근성 분석 중...</span>
                </div>
                <div class="ai-loading_step" data-step="5">
                    <span class="ai-loading_step_icon"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"></path></svg></span>
                    <span class="ai-loading_step_text" data-pending="규모 적합도 분석 중..." data-done="규모 적합도 분석 완료 ✓">규모 적합도 분석 중...</span>
                </div>
            </div>
            <div class="ai-loading_bar" aria-hidden="true">
                <div class="ai-loading_bar_fill company-ai-loading-progress-fill" id="companyAiLoadingProgressFill"></div>
            </div>
            <p class="company-ai-loading-desc" id="companyAiLoadingDesc" hidden>거래 데이터를 분석해 맞춤 추천을 불러오는 중입니다.</p>
            <p class="company-ai-loading-help" id="companyAiLoadingHelp" hidden>응답이 지연되면 잠시 후 자동으로 안내 메시지가 표시됩니다.</p>
            <div class="company-ai-loading-actions" id="companyAiLoadingActions" hidden>
                <button type="button" class="company-ai-loading-btn company-ai-loading-btn-primary" id="companyAiLoadingRetryBtn">다시 시도</button>
                <button type="button" class="company-ai-loading-btn company-ai-loading-btn-ghost" id="companyAiLoadingBackBtn">이전으로</button>
            </div>
        </div>
    </div>

    <div class="ai-banner" id="aiBanner">
        <div class="ai-banner_card">
            <div class="ai-banner_top">
                <div class="ai-banner_icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M12 2L13.8 7.2L19.2 7.5L14.6 10.8L16.2 16L12 12.9L7.8 16L9.4 10.8L4.8 7.5L10.2 7.2Z"/></svg>
                </div>
                <span class="ai-banner_label">AI Recommendation</span>
            </div>
            <h2 class="ai-banner_title">귀사에 적합한 거래처를 분석했습니다</h2>
            <p class="ai-banner_desc">업종·거래 데이터 기반 머신러닝 분석을 통해 최적의 거래처를 추천합니다.</p>
            <div class="ai-banner_stats">
                <div class="ai-banner_stat">
                    <span class="ai-banner_stat_value">200만+</span>
                    <span class="ai-banner_stat_label">거래 데이터</span>
                </div>
                <div class="ai-banner_stat">
                    <span class="ai-banner_stat_value"><?php echo (int)$banner_buy_stat; ?></span>
                    <span class="ai-banner_stat_label">구매처</span>
                </div>
                <div class="ai-banner_stat">
                    <span class="ai-banner_stat_value"><?php echo (int)$banner_sell_stat; ?></span>
                    <span class="ai-banner_stat_label">판매처</span>
                </div>
            </div>
        </div>
    </div>

    <div class="list-body">
        <div class="inner">
            <main class="list-main">
                <div class="list-top">
                    <div class="list-type-wrap">
                        <?php /*
                        구매처/판매처 찾기 진입 방향과 다른 목록으로 전환되지 않도록 탭 UI를 임시 비활성화한다.
                        <div class="list-type-switch" role="group" aria-label="구매하기/판매하기">
                            <span class="list-type-slider" aria-hidden="true"></span>
                            <button type="button" class="list-type-btn on" data-type="buyer">구매하기</button>
                            <button type="button" class="list-type-btn" data-type="seller">판매하기</button>
                        </div>
                        */ ?>
                    </div>
                </div>

                <div class="ai-list-panel is-active" data-type="<?php echo htmlspecialchars($requested_type, ENT_QUOTES, 'UTF-8'); ?>">
                    <section class="ai-top3-section-top">
                        <div class="ai-top3">
                            <div class="mHookSection_header">
                                <div class="mHookSection_label mHookSection_label--ai-top3">
                                    <span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></span>
                                    <span>TOP 3</span>
                                </div>
                                <div class="mHookSection_titleRow">
                                    <h2 class="mHookSection_title">AI가 선정한 TOP 3 거래처</h2>
                                </div>
                                <p class="mHookSection_subtitle">귀사의 업종·거래 데이터를 분석해 가장 적합한 거래처를 추천합니다</p>
                            </div>
                            <div class="ai-top3-swiper swiper">
                                <div class="swiper-wrapper">
                                <?php if (!empty($active_top3)): ?>
                                    <?php foreach ($active_top3 as $slide_html): ?>
                                        <?php echo $slide_html; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="ai-empty-state">
                                        <p class="ai-empty-state_text"><?php echo htmlspecialchars($active_top3_notice, ENT_QUOTES, 'UTF-8'); ?></p>
                                    </div>
                                <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="ai-rest-section">
                        <div class="mHookSection_header ai-rest_header">
                            <div class="mHookSection_label mHookSection_label--ai-rest">
                                <span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></span>
                                <span>ALL</span>
                            </div>
                            <div class="mHookSection_titleRow">
                                <h2 class="mHookSection_title">추천 거래처 전체 목록</h2>
                            </div>
                            <p class="mHookSection_subtitle">AI가 분석한 전체 추천 거래처를 확인해 보세요</p>
                        </div>
                        <div class="ai-rest-swiper swiper">
                            <div class="swiper-wrapper">
                                <?php if (!empty($active_rest_first)): ?>
                                    <?php foreach ($active_rest_first as $slide_html): ?>
                                        <?php echo $slide_html; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="ai-empty-state">
                                        <p class="ai-empty-state_text"><?php echo htmlspecialchars($active_rest_notice, ENT_QUOTES, 'UTF-8'); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="swiper-pagination"></div>
                        </div>
                        <div class="list-grid_more_wrap" id="loadMoreWrap">
                            <button type="button" class="list-grid_more_btn" id="btnLoadMore">&#8635; 다른 추천 보기</button>
                        </div>
                    </section>
                </div>
            </main>
        </div>
    </div>
</div>

<script>
window.__COMPANY_LIST_AI_REQUESTED_TYPE__ = <?php echo json_encode($requested_type, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
window.__COMPANY_LIST_AI_DATA__ = {
    buyer: {
        top3: <?php echo json_encode(array_values($buy_top3), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
        rest: <?php echo json_encode(array_values($buy_rest_pool), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
    },
    seller: {
        top3: <?php echo json_encode(array_values($sell_top3), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
        rest: <?php echo json_encode(array_values($sell_rest_pool), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
    }
};
window.__COMPANY_LIST_AI_META__ = {
    buyer: {
        result_source: <?php echo json_encode($buy_result_source, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
        from_cache: <?php echo !empty($buy_call['from_cache']) ? 'true' : 'false'; ?>,
        top3_notice: <?php echo json_encode($buy_top3_notice, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
        rest_notice: <?php echo json_encode($buy_rest_notice, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
    },
    seller: {
        result_source: <?php echo json_encode($sell_result_source, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
        from_cache: <?php echo !empty($sell_call['from_cache']) ? 'true' : 'false'; ?>,
        top3_notice: <?php echo json_encode($sell_top3_notice, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
        rest_notice: <?php echo json_encode($sell_rest_notice, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
    }
};
window.__COMPANY_LIST_AI_STATS__ = {
    buyer_total: <?php echo (int)$buy_total_stat; ?>,
    seller_total: <?php echo (int)$sell_total_stat; ?>
};
</script>
<script src="<?php echo G5_URL; ?>/ko/js/company_list_ai.js?ver=<?php echo (int)$company_list_ai_js_ver; ?>"></script>

<?php include_once(G5_THEME_PATH . '/tail.php'); ?>
