<?php
// prompts.php — Gemini 보고서 프롬프트 3단계 라이브러리
defined('GEMINI_API_KEY') or exit; // 직접 HTTP 접근 차단

function get_prompt(string $tier, array $d): array {
    return match($tier) {
        'enhanced' => _prompt_enhanced($d),
        'premium'  => _prompt_premium($d),
        'trade'    => _prompt_trade($d),
        default    => _prompt_basic($d),
    };
}

// ── 공통 헬퍼 ─────────────────────────────────────────────

function _mask_name(string $nm): string {
    if (!$nm) return $nm;
    return '해당 기업';
}

function _candidate_type_desc(string $type): string {
    return match(true) {
        str_starts_with($type, 'chain')   => "공통 거래처 네트워크 연결",
        str_starts_with($type, 'partner') => "동종 기업 실거래 검증 파트너",
        $type === 'industry'              => "동일 업종 공급망 파트너",
        $type === 'neighbor'              => "지역 기반 근접 파트너",
        default                           => "거래 연결",
    };
}

function _axis_desc(): array {
    return [
        // ── EC2 app.py 실제 레이더 12축 ──────────────────
        'supply_chain'       => '공급망 연결력',
        'financial_health'   => '재무 건전성',
        'network_overlap'    => '거래망 중복도',
        'market_power'       => '시장 영향력',
        'trade_diversity'    => '거래 다양성',
        'company_size'       => '기업 규모',
        'growth_trend'       => '성장 궤도',
        'trade_loyalty'      => '거래 충성도',
        'scale_match'        => '거래 규모 적합도',
        'accessibility'      => '지역 접근성',
        'industry_fit'       => '업종 적합도',
        'product_fit'        => '상품 적합도',
        // ── features.py 구버전 호환 ───────────────────────
        'similarity'         => '거래 유사도',
        'activity'           => '거래 활성도',
        // ── 예비 ─────────────────────────────────────────
        'geographic_fit'     => '지역 근접성',
        'stability_score'    => '거래 안정성',
        'network_centrality' => '네트워크 중심성',
        'market_position'    => '시장 포지션',
        'synergy_potential'  => '시너지 잠재력',
    ];
}

function _radar_highlights(array $radar): array {
    $desc = _axis_desc();
    arsort($radar);
    $top = array_slice($radar, 0, 3, true);
    return array_map(fn($k, $v) => ($desc[$k] ?? $k) . " {$v}/100", array_keys($top), $top);
}

function _rec_block_basic(?array $rec): string {
    if (!$rec) return '없음';
    $nm     = $rec['enp_nm'] ?? $rec['kedcd'] ?? '-';
    $sc     = $rec['display_score'] ?? $rec['score'] ?? '-';
    $ct     = _candidate_type_desc($rec['candidate_type'] ?? '-');
    $hl     = !empty($rec['radar']) ? implode(', ', _radar_highlights($rec['radar'])) : '없음';
    $age    = ($rec['company_age'] ?? 0) ? $rec['company_age'] . '년' : '-';
    $growth = isset($rec['sales_growth_pct']) ? (($rec['sales_growth_pct'] >= 0 ? '+' : '') . $rec['sales_growth_pct'] . '%') : '-';
    $p2y    = isset($rec['consec_profit_2y']) ? ($rec['consec_profit_2y'] ? '2년 연속 흑자' : '미확인') : '-';
    return "- 기업명: {$nm}\n- ML 추천점수: {$sc}\n- 연결방식: {$ct}\n- 업력: {$age}\n- 매출성장률: {$growth}\n- 연속흑자: {$p2y}\n- 레이더 상위 3축: {$hl}";
}

function _rec_block_table(?array $rec): string {
    if (!$rec) return '| 데이터 없음 | — |';
    $nm     = $rec['enp_nm'] ?? $rec['kedcd'] ?? '-';
    $sc     = $rec['display_score'] ?? $rec['score'] ?? '-';
    $ct     = _candidate_type_desc($rec['candidate_type'] ?? '-');
    $hl     = !empty($rec['radar']) ? implode(', ', _radar_highlights($rec['radar'])) : '없음';
    $age    = ($rec['company_age'] ?? 0) ? $rec['company_age'] . '년' : '-';
    $growth = isset($rec['sales_growth_pct']) ? (($rec['sales_growth_pct'] >= 0 ? '+' : '') . $rec['sales_growth_pct'] . '%') : '-';
    $p2y    = isset($rec['consec_profit_2y']) ? ($rec['consec_profit_2y'] ? '2년 연속 흑자' : '미확인') : '-';
    return "| 기업명 | {$nm} |\n| 추천점수 | {$sc} |\n| 연결방식 | {$ct} |\n| 업력 | {$age} |\n| 매출성장률 | {$growth} |\n| 2년 연속흑자 | {$p2y} |\n| 레이더 상위 3축 | {$hl} |";
}

function _rec_radar_full(?array $rec, array $axisDesc): string {
    if (!$rec || empty($rec['radar'])) return '레이더 데이터 없음';
    $sorted = $rec['radar'];
    arsort($sorted);
    $lines = [];
    foreach ($sorted as $axis => $val) {
        $desc  = $axisDesc[$axis] ?? $axis;
        $mark  = $val >= 70 ? '★' : ($val >= 50 ? '◎' : '○');
        $lines[] = "- {$mark} {$desc}: {$val}/100";
    }
    return implode("\n", $lines);
}

function _get_rec_and_label(array $d): array {
    $tradeType = (int)($d['trade_type'] ?? 1);
    $isSell = ($tradeType === 2);
    $rec = $isSell ? ($d['sell_top1'] ?? null) : ($d['buy_top1'] ?? null);
    if (empty($rec)) {
        $rec = $isSell ? ($d['buy_top1'] ?? null) : ($d['sell_top1'] ?? null);
        $isSell = !$isSell;
    }
    return [
        'rec'         => is_array($rec) ? $rec : [],
        'trade_label' => $isSell ? '판매처' : '구매처',
        'is_sell'     => $isSell,
    ];
}

function _trade_exact_items($value): array {
    $rawItems = is_array($value) ? $value : [$value];
    $out = [];
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

// ── Tier 1: Basic ──────────────────────────────────────────

function _prompt_basic(array $d): array {
    $rl         = _get_rec_and_label($d);
    $rec        = $rl['rec'];
    $tradeLabel = $rl['trade_label'];
    $isSell     = $rl['is_sell'];
    $tradeDir   = $isSell
        ? '의뢰사가 해당 기업에 판매할 수 있는 품목·서비스'
        : '의뢰사가 해당 기업으로부터 구매할 수 있는 품목·서비스';

    $srcNm   = $d['source_enp_nm'] ?? '(검색 기업)';
    $srcBzc  = ($d['source_bzc_nm'] ?? '-') . ' (' . ($d['source_bzc_cd'] ?? '-') . ')';
    $srcSido = $d['source_sido'] ?? '-';
    $srcSze  = $d['source_sze']  ?? '-';
    $srcMpd  = (!empty($d['source_mpd']) && $d['source_mpd'] !== '-') ? "- 주력상품: {$d['source_mpd']}" : '';
    $srcCust = !empty($d['source_cust_names']) ? implode(', ', $d['source_cust_names']) : '';
    $srcSupp = !empty($d['source_supp_names']) ? implode(', ', $d['source_supp_names']) : '';
    $srcCustLine = $srcCust ? "- 기존 매입처: {$srcCust}" : '';
    $srcSuppLine = $srcSupp ? "- 기존 매출처: {$srcSupp}" : '';

    $recBlock    = _rec_block_basic($rec ?: null);
    $recBzc      = $rec['bzc_nm'] ?? '-';
    $recSido     = $rec['sido']   ?? '-';
    $recKeywords = !empty($rec['keywords']) ? '- 주요 품목·키워드: ' . implode(', ', $rec['keywords']) : '';

    return [
        'system_instruction' => <<<SYS
당신은 B2B 거래처 연결 전문 컨설턴트입니다.
보고서는 반드시 아래 순서로 작성합니다:
① 핵심 추천 사유 요약 (거래 언어 중심, 3줄)
② 제공된 데이터 기반 추천 기업 현황 소개
③ {$tradeLabel} 추천 이유 및 의뢰사와의 시너지
④ 거래 시 감안할 사항

[데이터 경계 원칙 — 최우선 준수]
1. 보고서에 사용 가능한 정보는 아래 '제공 데이터' 항목뿐입니다. 그 외 출처는 없습니다.
2. '-' 또는 '미확인'으로 표시된 항목은 해당 내용을 서술하지 않습니다.
3. 업종코드·업종명에서 해당 업종의 일반적 거래 품목과 공급망상 역할을 해석하는 것은 허용합니다. 단, 해당 기업의 특정 제품명·주요 고객사·인증·대표자 등 기업 고유 정보는 추론하여 작성하지 않습니다.
4. 사전 학습 지식으로 알고 있는 해당 기업의 구체적 정보(제품, 뉴스, 거래처, 대표자 등)를 기재하지 않습니다.
5. 모든 서술은 제공된 특정 데이터 항목에 직접 근거해야 합니다. 근거 없는 문장은 작성하지 않습니다.

[거래 관점 서술 원칙]
이 보고서는 {$tradeLabel} 추천입니다. {$tradeDir}를 중심으로 설명합니다.
업종명은 거래 언어로 표현합니다 (예: '자동차 임대업' → '차량 조달·임대 공급 기업', '응용 소프트웨어 개발 및 공급업' → '업무용 소프트웨어 개발·공급 기업').

레이더 지표는 각 축의 비즈니스적 의미를 구체적으로 해석하세요.
모든 문장은 반드시 '~입니다', '~습니다' 형식의 존댓말로 작성하세요.
보고서 본문에서 업종명을 언급할 때는 반드시 **업종명** 형식으로 볼드 처리하세요. 그 외 볼드 마크다운(**)은 사용하지 마세요.
추천 기업의 실명(기업명)을 그대로 사용하세요.
SYS,
        'user_content' => <<<USR
## ※ 사용 가능한 데이터 (이 목록 외 정보는 존재하지 않습니다)

## 의뢰사 정보
- 기업명: {$srcNm}
- 업종: {$srcBzc}
- 지역: {$srcSido}, 규모: {$srcSze}
{$srcMpd}
{$srcCustLine}
{$srcSuppLine}

## ML 추천 {$tradeLabel} 1위
- 업종: {$recBzc}
- 지역: {$recSido}
{$recKeywords}
{$recBlock}

---

## 보고서 작성 지침

[핵심 추천 사유] 보고서 최상단에 3줄 요약:
- {$tradeLabel} 관점: {$tradeDir} (업종명을 거래 언어로 표현)
- 공급망 연결: 두 기업이 어떻게 공급망으로 이어지는지
- 선정 근거: 업종 적합성·규모·재무 건전성 핵심

[1단계] 위 제공 데이터를 바탕으로 추천 기업 현황을 상세히 서술합니다:
- 업종을 거래 언어로 표현하고 공급망 내 역할 설명
- 지역 거점의 비즈니스적 의미 (물류·접근성·산업 집적 등)
- 규모(sze) 기반 기업 포지션 분석
- 레이더 상위 지표 각각의 비즈니스 의미를 2~3문장으로 구체적으로 해석

[2단계] {$tradeLabel} 추천 이유와 구체적 시너지 기회:
- {$tradeDir} 중심으로 연결 스토리 서술
- 의뢰사와 추천 기업이 함께 만들어낼 수 있는 협력 시나리오 2~3가지

[3단계] 거래 시 감안할 사항을 간략히 마무리합니다.

---

## 출력 형식

## 핵심 추천 사유
- (거래 관점: {$tradeDir} — 업종명을 거래 언어로 표현)
- (공급망 연결: 두 기업이 어떻게 공급망으로 이어지는지)
- (선정 근거: 업종·규모·재무 적합성)

---

## 1. 해당 기업 현황
*제공 데이터 기반 분석입니다.*

### 기업 기본 정보
(업종을 거래 언어로 표현·공급망 역할, 지역 거점 의미, 규모 분석 — 각 항목 2~3문장)

### ML 적합도 분석
(추천 점수 해석 + 레이더 상위 지표 각각의 비즈니스 의미를 구체적으로 서술)

## 2. 추천 이유 및 의뢰사와의 시너지

### 거래 연결 배경
({$tradeLabel} 관점: {$tradeDir} — 왜 이 기업이 의뢰사의 {$tradeLabel}로 선택되었는지 상세히)

### 시너지 기회
(구체적인 협력 시나리오 2~3가지, 각 시나리오별 기대 효과 포함)

## 3. 감안할 사항
(1~2가지)
USR,
        'temperature' => 0.3,
    ];
}

// ── Tier 2: Enhanced ───────────────────────────────────────

function _prompt_enhanced(array $d): array {
    $rl         = _get_rec_and_label($d);
    $rec        = $rl['rec'];
    $tradeLabel = $rl['trade_label'];
    $isSell     = $rl['is_sell'];
    $tradeDir   = $isSell
        ? '의뢰사가 해당 기업에 판매할 수 있는 품목·서비스'
        : '의뢰사가 해당 기업으로부터 구매할 수 있는 품목·서비스';

    $srcNm   = $d['source_enp_nm'] ?? '(검색 기업)';
    $srcBzc  = ($d['source_bzc_nm'] ?? '-') . ' (' . ($d['source_bzc_cd'] ?? '-') . ')';
    $srcSido = $d['source_sido'] ?? '-';
    $srcSze  = $d['source_sze']  ?? '-';
    $custList = !empty($d['source_cust_names']) ? implode(', ', $d['source_cust_names']) : '미공개';
    $suppList = !empty($d['source_supp_names']) ? implode(', ', $d['source_supp_names']) : '미공개';
    $srcMpdRow = (!empty($d['source_mpd']) && $d['source_mpd'] !== '-')
                 ? "| 주력상품 | {$d['source_mpd']} |" : '';

    $recBlock    = _rec_block_table($rec ?: null);
    $recBzc      = $rec['bzc_nm'] ?? '-';
    $recSido     = $rec['sido']   ?? '-';
    $recSze      = $rec['sze']    ?? '-';
    $recKeywords = !empty($rec['keywords']) ? implode(', ', $rec['keywords']) : '-';

    return [
        'system_instruction' => <<<SYS
당신은 한국 중소기업 B2B 시장 전문 파트너십 컨설턴트입니다.
보고서는 반드시 아래 흐름으로 작성합니다:

[Step 0] 핵심 추천 사유 요약 (거래 언어 중심, 3줄)
[Step 1] 제공 데이터 기반 추천 기업 현황 파악
[Step 2] {$tradeLabel} 추천 분석 → 추천 배경 + 시너지
[Step 3] 감안할 사항 (전체 분량의 10% 이내)

[데이터 경계 원칙 — 최우선 준수]
1. 보고서에 사용 가능한 정보는 아래 '제공 데이터' 항목뿐입니다. 그 외 출처는 없습니다.
2. '-' 또는 '미확인'으로 표시된 항목은 해당 내용을 서술하지 않습니다.
3. 업종코드·업종명에서 해당 업종의 일반적 거래 품목과 공급망상 역할을 해석하는 것은 허용합니다. 단, 해당 기업의 특정 제품명·주요 고객사·인증·대표자 등 기업 고유 정보는 추론하여 작성하지 않습니다.
4. 사전 학습 지식으로 알고 있는 해당 기업의 구체적 정보(제품, 뉴스, 거래처, 대표자 등)를 기재하지 않습니다.
5. 모든 서술은 제공된 특정 데이터 항목에 직접 근거해야 합니다. 근거 없는 문장은 작성하지 않습니다.

[거래 관점 서술 원칙]
이 보고서는 {$tradeLabel} 추천입니다. {$tradeDir}를 중심으로 설명합니다.
업종명은 거래 언어로 표현합니다 (예: '자동차 임대업' → '차량 조달·임대 공급 기업', '시스템 소프트웨어 개발 및 공급업' → '시스템 소프트웨어 구축·공급 기업').

전문 한국어, 기회 중심의 문체로 작성합니다.
모든 문장은 반드시 '~입니다', '~습니다' 형식의 존댓말로 작성하세요.
보고서 본문에서 업종명을 언급할 때는 반드시 **업종명** 형식으로 볼드 처리하세요. 그 외 볼드 마크다운(**)은 사용하지 마세요.
추천 기업의 실명(기업명)을 그대로 사용하세요.
SYS,
        'user_content' => <<<USR
## ※ 사용 가능한 데이터 (이 목록 외 정보는 존재하지 않습니다)

## 의뢰사 정보

| 항목 | 값 |
|------|-----|
| 기업명 | {$srcNm} |
| 업종 | {$srcBzc} |
| 지역 | {$srcSido} |
| 규모 | {$srcSze} (1=소기업 ↔ 5=대기업) |
{$srcMpdRow}
| 기존 매입처 | {$custList} |
| 기존 매출처 | {$suppList} |

---

## ML 추천 {$tradeLabel} 1위

| 항목 | 값 |
|------|-----|
| 업종 | {$recBzc} |
| 지역 | {$recSido} |
| 규모 | {$recSze} (1=소기업 ↔ 5=대기업) |
| 주요 품목·키워드 | {$recKeywords} |
{$recBlock}

---

## 보고서 작성 순서 (반드시 준수)

### Step 0 — 핵심 추천 사유 (최상단 3줄)
- {$tradeLabel} 관점: {$tradeDir} (업종명을 거래 언어로 표현)
- 공급망 연결: 두 기업이 어떻게 공급망으로 이어지는지
- 선정 근거: 업종 적합성·규모·재무 건전성

### Step 1 — 추천 기업 현황
위 제공 데이터를 기반으로 아래 내용을 충분한 분량으로 작성합니다:
- 업종을 거래 언어로 표현하고 공급망 내 역할 설명
- 지역 거점의 비즈니스적 의미 (물류·접근성·산업 집적 등)
- 규모(sze) 기반 기업 포지션 분석
- ★ 표시 레이더 강점 지표 각각에 대해 비즈니스적 의미와 거래 활용 방안을 2~3문장씩 서술

### Step 2 — {$tradeLabel} 추천 분석
- {$tradeDir} 중심으로 거래 연결 스토리 서술
- 거래 연결 유형이 말해주는 연결 배경
- 의뢰사와 추천 기업이 각각 얻는 구체적 이익
- 협력 시나리오 2~3가지 (각 시나리오별 기대 효과 포함)

### Step 3 — 감안할 사항
- 간략한 리스크 또는 주의점 1~2개

---

## 출력 형식

# 해당 기업 {$tradeLabel} 분석 보고서

---

## 핵심 추천 사유
- (거래 관점: {$tradeDir} — 업종명을 거래 언어로 표현)
- (공급망 연결: 두 기업이 어떻게 공급망으로 이어지는지)
- (선정 근거: 업종·규모·재무 적합성)

---

## 1. 기업 현황 (제공 데이터 기반)

### 기업 기본 정보
(업종을 거래 언어로 표현·공급망 특성과 역할, 지역 거점 의미, 규모 포지션 — 각 항목 2~3문장)

### 핵심 역량 (레이더 지표 기반)
(★ 강점 지표 각각의 비즈니스 의미와 거래 맥락에서의 활용 방안 — 지표별 2~3문장)

---

## 2. {$tradeLabel} 추천 배경 및 시너지

### 추천 배경
({$tradeLabel} 관점: {$tradeDir} — 거래 연결 유형 기반 구체적 연결 스토리)

### 의뢰사와의 시너지 기회

| 시너지 영역 | 기대 효과 | 근거 |
|-----------|---------|------|
| (업종·공급망 연계) | ... | ... |
| (네트워크 연결) | ... | ... |
| (규모 적합성) | ... | ... |

### 협력 시나리오
(단기·중장기 협력 방향 및 첫 번째 실행 액션 제안)

---

## 3. 감안할 사항
- (1~2가지, 간략히)
USR,
        'temperature' => 0.2,
    ];
}

// ── Tier 3: Premium ────────────────────────────────────────

function _prompt_premium(array $d): array {
    $axisDesc = _axis_desc();

    $rl         = _get_rec_and_label($d);
    $rec        = $rl['rec'];
    $tradeLabel = $rl['trade_label'];
    $isSell     = $rl['is_sell'];
    $tradeDir   = $isSell
        ? '의뢰사가 해당 기업에 판매할 수 있는 품목·서비스'
        : '의뢰사가 해당 기업으로부터 구매할 수 있는 품목·서비스';

    $srcNm    = $d['source_enp_nm']  ?? '(검색 기업)';
    $srcBzc   = ($d['source_bzc_nm'] ?? '-') . ' (' . ($d['source_bzc_cd'] ?? '-') . ')';
    $srcSido  = $d['source_sido']    ?? '-';
    $srcSze   = $d['source_sze']     ?? '-';
    $srcMpd   = $d['source_mpd']     ?? '미공개';
    $custList = !empty($d['source_cust_names']) ? implode(', ', $d['source_cust_names']) : '미공개';
    $suppList = !empty($d['source_supp_names']) ? implode(', ', $d['source_supp_names']) : '미공개';
    $srcSales = ($d['source_sales'] ?? 0) ? number_format($d['source_sales']) . '원' : '미공개';

    $recScore   = $rec ? ($rec['display_score'] ?? $rec['score'] ?? '-') : '-';
    $recType    = $rec ? _candidate_type_desc($rec['candidate_type'] ?? '-') : '-';
    $recRadar   = _rec_radar_full($rec ?: null, $axisDesc);
    $recBzcFull = $rec ? (($rec['bzc_nm'] ?? '') ? "{$rec['bzc_nm']} ({$rec['bzc_cd']})" : ($rec['bzc_cd'] ?? '-')) : '-';
    $recSze     = $rec ? ($rec['sze']  ?? '-') : '-';
    $recSido    = $rec ? ($rec['sido'] ?? '-') : '-';
    $recAge     = ($rec && ($rec['company_age'] ?? 0)) ? $rec['company_age'] . '년' : '-';
    $recGrowth  = isset($rec['sales_growth_pct']) ? (($rec['sales_growth_pct'] >= 0 ? '+' : '') . $rec['sales_growth_pct'] . '%') : '-';
    $recDebtPct = isset($rec['debt_ratio_pct'])   ? $rec['debt_ratio_pct'] . '%' : '-';
    $recP2y     = isset($rec['consec_profit_2y'])  ? ($rec['consec_profit_2y'] ? '2년 연속 흑자' : '미확인') : '-';
    $recKeywords = !empty($rec['keywords']) ? implode(', ', $rec['keywords']) : '';
    $srcKeywords = !empty($d['src_keywords']) ? implode(', ', $d['src_keywords']) : '';

    return [
        'system_instruction' => <<<SYS
당신은 한국 B2B 시장 15년 경력의 선임 비즈니스 파트너십 컨설턴트입니다.
운영팀이 실제 거래 의사결정에 활용할 수 있는 심층 보고서를 작성하는 것이 임무입니다.

[데이터 경계 원칙 — 모든 작성 원칙보다 최우선]
1. 보고서에 사용 가능한 정보는 아래 '제공 데이터' 항목뿐입니다. 그 외 출처는 존재하지 않습니다.
2. '-' 또는 '미확인'으로 표시된 항목은 해당 내용을 서술하지 않습니다.
3. 업종코드·업종명에서 해당 업종의 일반적 거래 품목과 공급망상 역할을 해석하는 것은 허용합니다. 단, 해당 기업의 특정 제품명·주요 고객사·인증·대표자 등 기업 고유 정보는 추론하여 작성하지 않습니다.
4. 사전 학습 지식으로 알고 있는 해당 기업의 구체적 정보(제품, 뉴스, 거래처, 대표자, 수상 등)를 기재하지 않습니다.
5. 모든 서술은 제공된 특정 데이터 항목에 직접 근거해야 합니다. 어떤 항목에도 근거하지 않는 문장은 작성하지 않습니다.

[거래 관점 서술 원칙]
이 보고서는 {$tradeLabel} 추천입니다. {$tradeDir}를 보고서 전반에 걸쳐 명확히 서술합니다.
업종명은 거래 언어로 표현합니다 (예: '자동차 임대업' → '차량 조달·임대 공급 기업', '응용 소프트웨어 개발 및 공급업' → '업무용 소프트웨어 개발·공급 기업').

[필수 작성 흐름 — 순서 변경 불가]

■ Phase 0: 핵심 추천 사유 (보고서 최상단, 3줄)
  · {$tradeLabel} 관점: {$tradeDir} (업종명을 거래 언어로 표현)
  · 공급망 연결성: 두 기업이 어떻게 이어지는지
  · 선정 근거: 업종·규모·재무 핵심 요약

■ Phase 1: 기업 현황 (제공 데이터 기반)
  · 기본 정보: 업종을 거래 언어로 표현하고 공급망 내 역할 설명, 지역 거점 의미, 규모·업력 분석
  · 재무 경향: 매출성장률(방향과 폭), 부채비율(재무 안정성), 2년 연속흑자(수익 지속성) 해석

■ Phase 2: 거래처 추천 분석
  · {$tradeLabel} 관점에서 거래 가능한 구체적 품목·소재·서비스를 열거 (형태: ① 주요품목·원소재 ② 가공·공정 연계 ③ 서비스·납품 형태). '품목 키워드'가 있으면 그것을 우선 근거로 사용하고, 없으면 업종코드로 추론합니다.
  · {$tradeDir} — 두 기업이 공급망에서 어느 단계에 위치하는지, 어떤 공정·거래 흐름으로 연결 가능한지
  · 거래 연결 유형이 말해주는 의뢰사와 추천 기업 간 연결 스토리
  · ★(70점 이상) 레이더 축을 추천 기업의 핵심 경쟁력으로 해석
  · 의뢰사의 기존 거래처 네트워크와 추천 기업의 위치 관계 분석

■ Phase 3: 시너지 및 실행 제안
  · 거래 성사 시 의뢰사와 추천 기업이 각각 얻는 구체적 이익
  · 단기·중장기 협력 시나리오 및 첫 번째 실행 액션

■ Phase 4: 감안할 사항 (전체 분량의 10% 이내)

기회와 시너지 중심, 부정적 내용은 Phase 4에만 집약합니다.
한국어 전용, 전문 비즈니스 문체로 작성합니다.
모든 문장은 반드시 '~입니다', '~습니다' 형식의 존댓말로 작성하세요.
보고서 본문에서 업종명을 언급할 때는 반드시 **업종명** 형식으로 볼드 처리하세요. 그 외 볼드 마크다운(**)은 사용하지 마세요.
추천 기업의 실명은 보고서 어디서도 직접 기재하지 말고 반드시 '해당 기업'으로 표기하세요.
SYS,
        'user_content' => <<<USR
## ※ 사용 가능한 데이터 (이 목록 외 정보는 존재하지 않습니다)

---
## [의뢰사: {$srcNm}]

| 항목 | 값 |
|------|-----|
| 업종 | {$srcBzc} |
| 지역 | {$srcSido} |
| 규모(sze) | {$srcSze} (1=소기업 ↔ 5=대기업) |
| 주력상품 | {$srcMpd} |
| 매출(당기) | {$srcSales} |
| 품목 키워드 | {$srcKeywords} |
| 기존 매입거래처 | {$custList} |
| 기존 매출거래처 | {$suppList} |

---
## [해당 기업 — ML 추천 {$tradeLabel} 1위]

| 항목 | 값 |
|------|-----|
| 업종 | {$recBzcFull} |
| 지역 | {$recSido} |
| 규모(sze) | {$recSze} (1=소기업 ↔ 5=대기업) |
| 업력 | {$recAge} |
| 매출성장률 | {$recGrowth} |
| 부채비율 | {$recDebtPct} |
| 2년 연속흑자 | {$recP2y} |
| 추천 점수 | {$recScore} |
| 연결 방식 | {$recType} |
| 품목 키워드 | {$recKeywords} |

레이더 지표 (★ 강점 · ◎ 보통 · ○ 참고)
{$recRadar}

---
## [출력 형식 — 반드시 이 순서로]

# 해당 기업 {$tradeLabel} 심층 분석 보고서

> 추천 유형: {$tradeLabel} | 추천 점수: {$recScore} | 연결 방식: {$recType}

---

## 핵심 추천 사유
- (거래 관점: {$tradeDir} — 업종명을 거래 언어로 표현)
- (공급망 연결: 두 기업이 어떻게 공급망으로 이어지는지)
- (선정 근거: 업종·규모·재무 측면 적합성)

---

## 1. 기업 현황 (제공 데이터 기반)

### 1-1. 기업 기본 정보
(업종을 거래 언어로 표현·공급망 내 역할과 특성, 지역 거점 비즈니스적 의미, 기업형태·규모 포지션, 업력 해석 — 각 항목 2~3문장)

### 1-2. 재무 경향 분석
(매출성장률 방향·폭 해석, 부채비율 기반 재무 안정성 평가, 2년 연속흑자 여부의 수익 지속성 의미)

---

## 2. 거래처 추천 배경

### 2-1. 거래 가능 품목·서비스
({$tradeLabel} 관점에서 업종코드 기반 거래 가능한 구체적 품목·소재·서비스를 열거:
① [주요 품목·원소재] ② [가공·공정 연계] ③ [서비스·납품 형태])

### 2-2. 거래 연결 스토리
({$tradeLabel} 관점: {$tradeDir} — 두 기업이 공급망에서 어떻게 만나는지, 거래 연결 유형 기반 연결 스토리)

### 2-3. 핵심 경쟁력
(레이더 상위 지표를 중심으로 한 실질적 강점 해석)

---

## 3. 의뢰사와의 시너지 및 실행 제안

### 3-1. 시너지 분석

| 시너지 영역 | 의뢰사가 얻는 것 | 추천사가 얻는 것 | 근거 |
|-----------|--------------|-----------------|------|
| (업종·공급망) | ... | ... | ... |
| (네트워크·거래처) | ... | ... | ... |
| (재무·규모) | ... | ... | ... |

### 3-2. 파트너십 가치 요약
> (이 거래가 가져올 비즈니스 임팩트를 2~3문장으로)

### 3-3. 실행 제안

| 단계 | 액션 | 기대 효과 |
|------|------|---------|
| 단기 (1~3개월) | ... | ... |
| 중기 (3~12개월) | ... | ... |

---

## 4. 감안할 사항
- (리스크·주의점·보완 항목 1~3개, 간략히)
USR,
        'temperature' => 0.2,
    ];
}

// ── Tier Trade: 거래중심 A/B 테스트용 Prompt B ─────────────

function _prompt_trade(array $d): array {
    $axisDesc = _axis_desc();

    $rl         = _get_rec_and_label($d);
    $rec        = $rl['rec'];
    $tradeLabel = $rl['trade_label'];
    $isSell     = $rl['is_sell'];
    $tradeDir   = $isSell
        ? '의뢰사가 해당 기업에 판매할 수 있는 품목·서비스'
        : '의뢰사가 해당 기업으로부터 구매할 수 있는 품목·서비스';
    $tradeAction = $isSell ? '어떤 제품·서비스를 판매할 수 있는지' : '어떤 품목·소재·서비스를 구매할 수 있는지';

    $srcNm    = $d['source_enp_nm']  ?? '(검색 기업)';
    $srcBzc   = ($d['source_bzc_nm'] ?? '-') . ' (' . ($d['source_bzc_cd'] ?? '-') . ')';
    $srcSido  = $d['source_sido']    ?? '-';
    $srcSze   = $d['source_sze']     ?? '-';
    $srcMpd   = $d['source_mpd']     ?? '미공개';
    $custList = !empty($d['source_cust_names']) ? implode(', ', $d['source_cust_names']) : '미공개';
    $suppList = !empty($d['source_supp_names']) ? implode(', ', $d['source_supp_names']) : '미공개';
    $srcSales = ($d['source_sales'] ?? 0) ? number_format($d['source_sales']) . '원' : '미공개';

    $recScore   = $rec ? ($rec['display_score'] ?? $rec['score'] ?? '-') : '-';
    $recType    = $rec ? _candidate_type_desc($rec['candidate_type'] ?? '-') : '-';
    $recRadar   = _rec_radar_full($rec ?: null, $axisDesc);
    $recBzcFull = $rec ? (($rec['bzc_nm'] ?? '') ? "{$rec['bzc_nm']} ({$rec['bzc_cd']})" : ($rec['bzc_cd'] ?? '-')) : '-';
    $recSze     = $rec ? ($rec['sze']  ?? '-') : '-';
    $recSido    = $rec ? ($rec['sido'] ?? '-') : '-';
    $recAge     = ($rec && ($rec['company_age'] ?? 0)) ? $rec['company_age'] . '년' : '-';
    $recGrowth  = isset($rec['sales_growth_pct']) ? (($rec['sales_growth_pct'] >= 0 ? '+' : '') . $rec['sales_growth_pct'] . '%') : '-';
    $recDebtPct = isset($rec['debt_ratio_pct'])   ? $rec['debt_ratio_pct'] . '%' : '-';
    $recP2y     = isset($rec['consec_profit_2y'])  ? ($rec['consec_profit_2y'] ? '2년 연속 흑자' : '미확인') : '-';
    $recKeywords = !empty($rec['keywords']) ? implode(', ', $rec['keywords']) : '';
    $srcKeywords = !empty($d['src_keywords']) ? implode(', ', $d['src_keywords']) : '';
    $srcExactItems = array_values(array_unique(array_merge(
        _trade_exact_items($d['src_keywords'] ?? []),
        _trade_exact_items($d['source_mpd'] ?? '')
    )));
    $recExactItems = _trade_exact_items($rec['keywords'] ?? []);
    $buyExactItems = $isSell ? $srcExactItems : $recExactItems;
    $sellExactItems = $isSell ? $recExactItems : $srcExactItems;
    $buyItemHint = !empty($buyExactItems) ? implode(', ', $buyExactItems) : '없음';
    $sellItemHint = !empty($sellExactItems) ? implode(', ', $sellExactItems) : '없음';

    return [
        'system_instruction' => <<<SYS
당신은 B2B 영업 현장 전문가입니다. 보고서 독자는 실제 구매·영업 담당자이며, "이 회사와 왜 거래가 되는가"를 직관적으로 이해하고 싶어합니다.

[데이터 경계 원칙 — 최우선 준수]
1. 보고서에 사용 가능한 정보는 아래 '제공 데이터' 항목뿐입니다.
2. '-' 또는 '미확인' 항목은 서술하지 않습니다.
3. 업종코드·업종명에서 해당 업종의 일반적 거래 품목과 공급망 역할 해석은 허용합니다. 단, 해당 기업의 특정 제품명·고객사·대표자 등 기업 고유 정보는 추론 작성 금지입니다.
4. 사전 학습 지식으로 알고 있는 해당 기업의 구체적 정보를 기재하지 않습니다.
5. 모든 서술은 제공된 데이터에 직접 근거해야 합니다.

[업종명 거래 언어 변환 — 필수]
업종명을 반드시 실제 거래 언어로 변환하여 표현합니다.
예) '그 외 기타 1차 철강 제조업' → '산업용 철강 원소재 및 가공소재 공급 기업'
    '응용 소프트웨어 개발 및 공급업' → '업무용 소프트웨어 개발·공급 기업'
    '화장품 제조업' → '화장품 OEM·ODM 및 완제품 공급 기업'
    '자동차 부품 제조업' → '자동차 부품·조립 가공 공급 기업'
    '생산설비 관련 제조업' → '생산설비·자동화 장비 공급 기업'
    '포장재 제조업' → '포장재·용기 공급 기업'

[거래 중심 서술 원칙]
이 보고서는 {$tradeLabel} 추천입니다.
핵심 질문: "{$tradeAction}"을 담당자가 즉시 이해할 수 있도록 설명합니다.
- {$tradeDir}를 보고서 전반에 걸쳐 구체적으로 서술합니다.
- '품목 키워드'가 제공된 경우 해당 키워드를 거래 가능 품목 서술의 우선 근거로 사용합니다. 없으면 업종코드로 추론합니다.
- 추천 사유를 업종명 중심으로 쓰지 말고, 실제 거래 품목 중심으로 씁니다.
- `buy_items`, `sell_items`에는 아래 제공된 '구매 가능 품목 원본값', '판매 가능 품목 원본값'에 있는 표현만 그대로 사용합니다.
- `buy_items`, `sell_items`에서 새로운 품목명이나 서비스명을 추론해서 만들지 않습니다.
- 원본값이 없으면 해당 배열은 비워 둘 수 있으며, 없는 품목을 억지로 만들지 않습니다.
- 구매 가능 품목은 이 거래에서 실제로 구매 수요가 맞닿는 품목·서비스로 정리하고, 판매 가능 품목은 실제로 공급·제공 가능한 품목·서비스로 정리합니다.
- '핵심 추천 사유' 첫 번째 항목은 반드시 구체적 품목명 또는 서비스명으로 시작합니다. 업종 설명으로 시작하지 않습니다.
- '핵심 추천 사유', '거래 연결 배경', '공급망·공정 연결성', '거래 시나리오'에서 품목명 또는 서비스명이 필요할 때도 가능한 한 위 원본값 표현을 그대로 재사용합니다.
- 'IT 시스템 구축 서비스', '솔루션 제공', '운영 지원'처럼 너무 넓은 표현만 단독으로 쓰지 말고, 가능한 한 서버 구축, 네트워크 설계, 보안 시스템 구축, 유지보수 계약, 금속구조물, 배전반 외함처럼 실제 거래 단위를 씁니다.
- 각 섹션은 충분한 분량(2~4문장 이상)으로 구체적으로 서술합니다. 한 줄 요약으로 끝내지 않습니다.
- 재무·업력·규모는 섹션 3에서 별도로 다루되, 거래 신뢰도 관점에서 항목별로 해석합니다.
- 업종명은 항상 **거래언어변환 표현** 형식으로 볼드 처리합니다. 그 외 볼드(**)는 사용하지 않습니다.
- 모든 문장은 '~입니다', '~습니다' 존댓말로 작성합니다.
- 추천 기업의 실명은 보고서 어디서도 직접 기재하지 말고 반드시 '해당 기업'으로 표기하세요.

[출력 규칙 — 최우선]
- 마크다운 보고서를 쓰지 말고 JSON만 출력합니다.
- 코드펜스 없이 순수 JSON 객체만 반환합니다.
- 키 이름을 바꾸거나 생략하지 않습니다.
- `buy_items`, `sell_items`는 위 원본값 표현만 그대로 사용합니다.
- 원본값에 없는 새 품목명은 절대 만들지 않습니다.
SYS,
        'user_content' => <<<USR
## ※ 사용 가능한 데이터 (이 목록 외 정보는 존재하지 않습니다)

---
## [의뢰사: {$srcNm}]

| 항목 | 값 |
|------|-----|
| 업종 | {$srcBzc} |
| 지역 | {$srcSido} |
| 규모(sze) | {$srcSze} (1=소기업 ↔ 5=대기업) |
| 주력상품 | {$srcMpd} |
| 매출(당기) | {$srcSales} |
| 품목 키워드 | {$srcKeywords} |
| 기존 매입거래처 | {$custList} |
| 기존 매출거래처 | {$suppList} |

---
## [해당 기업 — ML 추천 {$tradeLabel} 1위]

| 항목 | 값 |
|------|-----|
| 업종 | {$recBzcFull} |
| 지역 | {$recSido} |
| 규모(sze) | {$recSze} (1=소기업 ↔ 5=대기업) |
| 업력 | {$recAge} |
| 매출성장률 | {$recGrowth} |
| 부채비율 | {$recDebtPct} |
| 2년 연속흑자 | {$recP2y} |
| 추천 점수 | {$recScore} |
| 연결 방식 | {$recType} |
| 품목 키워드 | {$recKeywords} |
| 구매 가능 품목 참고 | {$buyItemHint} |
| 판매 가능 품목 참고 | {$sellItemHint} |
| 구매 가능 품목 원본값 | {$buyItemHint} |
| 판매 가능 품목 원본값 | {$sellItemHint} |

레이더 지표 (★ 강점 · ◎ 보통 · ○ 참고)
{$recRadar}

---
## [기존 보고서 형식 — 내용 흐름은 아래 형식을 유지]

# {$tradeLabel} 거래 가능성 분석

> 추천 유형: {$tradeLabel} | 점수: {$recScore} | 연결: {$recType}

---

## 거래 품목 요약

### 구매 가능 품목
(구매 가능 품목 원본값을 우선 사용하여 1줄 요약)

### 판매 가능 품목
(판매 가능 품목 원본값을 우선 사용하여 1줄 요약)

---

## 핵심 추천 사유
- ({$tradeAction} — 업종명을 거래 언어로 변환하여 구체적으로)
- (두 기업이 공급망·공정에서 연결되는 이유)
- (ML 연결 방식이 말해주는 실거래 연결 근거)

---

## 1. 어떤 {$tradeLabel}인가

### 1-1. 거래 언어로 본 기업 포지션
(업종명을 거래 언어로 변환: "이 기업은 **[거래언어변환]** 기업으로, [공급망 내 역할]을 담당합니다" 형태. 지역 거점의 물류·접근성 의미, 규모 기반 포지션 분석 포함. 3~4문장)

### 1-2. {$tradeAction}
({$tradeLabel} 관점에서 거래 가능한 구체적 품목·소재·서비스·공정을 품목 키워드·업종코드 기반으로 열거.
형태: "① [품목·소재] ② [가공·공정 연계] ③ [서비스·납품 형태]"
각 항목을 1~2문장으로 구체적으로 설명합니다.)

---

## 2. 왜 우리 회사와 연결되는가

### 2-1. 거래 연결 배경
(ML 연결 방식({$recType})이 말해주는 두 기업의 관계. 의뢰사 업종과 추천 기업 업종이 공급망에서 어떻게 만나는지 3~4문장으로 구체적으로 서술)

### 2-2. 공급망·공정 연결성
(두 기업이 실제 공급망에서 어느 단계에 위치하는지, 어떤 공정·거래 흐름으로 연결 가능한지 구체적으로 서술.
★ 레이더 강점 지표를 거래 연결 신뢰도 관점에서 항목별로 해석. 각 지표 1~2문장)

### 2-3. 거래 시나리오
(이 거래가 실제로 성사되었을 때의 구체적인 거래 흐름 2~3가지를 서술.
의뢰사가 얻는 실질적 이익과 추천 기업이 얻는 이익을 각각 명시)

---

## 3. 거래 신뢰도 분석

### 3-1. 거래 안정성
(업력·규모를 거래 파트너로서의 안정성 관점에서 해석. 2~3문장)

### 3-2. 재무 건전성
(매출성장률·부채비율·2년 연속흑자를 거래 리스크 관점에서 해석. 항목별로 2~3문장)

---

## 4. 실행 제안 및 감안할 사항

### 4-1. 첫 거래 접점
(구체적인 첫 접촉 방법·시작 포인트 2~3가지를 단계별로 제시)

### 4-2. 감안할 사항
(거래 시 주의해야 할 리스크 또는 보완 사항 2~3가지)

---
## [추가 요청 반영 규칙 — 위 기존 형식에 아래 내용을 추가 반영]

- 보고서 상단 또는 추천사유 영역에서 사용자가 바로 이해할 수 있도록 '구매 가능 품목', '판매 가능 품목'을 별도 항목으로 노출합니다.
- 추천사유에 업종명만 쓰지 말고 구매 품목과 판매 품목을 함께 반영합니다.
- 예: "귀사가 필요로 하는 알루미늄 판넬 및 배전반 외함을 공급할 수 있는 기업으로 분석되었습니다." 같은 방식으로 작성합니다.
- 목적은 사용자가 "왜 이 기업이 추천됐는지"를 업종이 아니라 실제 거래 품목 기준으로 바로 이해하게 만드는 것입니다.

---
## [JSON 출력 스키마 — 이 키 이름 그대로만 출력]

{
  "title": "{$tradeLabel} 거래 가능성 분석",
  "trade_label": "{$tradeLabel}",
  "score": "{$recScore}",
  "connection_type": "{$recType}",
  "buy_items": ["원본값1", "원본값2"],
  "sell_items": ["원본값1", "원본값2"],
  "reasons": [
    "귀사가 필요로 하는 [품목A], [품목B] 등을 공급/제공할 수 있는 기업으로 분석되었습니다.",
    "두 기업이 공급망·공정에서 어떻게 연결되는지 품목 중심으로 설명합니다.",
    "ML 연결 방식이 실거래 가능성과 어떻게 이어지는지 품목 또는 납품 형태를 포함해 설명합니다."
  ],
  "positioning": "거래 언어로 본 기업 포지션을 3~4문장으로 작성합니다.",
  "tradable_items_detail": "거래 가능한 구체적 품목·소재·서비스·공정을 3개 이상 포함해 설명합니다.",
  "connection_background": "거래 연결 배경을 실제 품목명 또는 서비스명을 포함해 3~4문장으로 설명합니다.",
  "supply_chain_fit": "공급망·공정 연결성과 레이더 강점 지표를 거래 연결 신뢰도 관점에서 설명합니다.",
  "trade_scenarios": [
    "거래 흐름 시나리오 1",
    "거래 흐름 시나리오 2"
  ],
  "stability_analysis": "업력·규모 기반 거래 안정성을 2~3문장으로 설명합니다.",
  "financial_analysis": "매출성장률·부채비율·2년 연속흑자를 거래 리스크 관점에서 2~3문장으로 설명합니다.",
  "first_touch_actions": [
    "첫 접촉 방법 1",
    "첫 접촉 방법 2"
  ],
  "considerations": [
    "감안할 사항 1",
    "감안할 사항 2"
  ]
}
USR,
        'temperature' => 0.1,
    ];
}
