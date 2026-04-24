<?php
if (!defined('_INDEX_')) define('_INDEX_', true);
if (!defined('_GNUBOARD_')) exit;
// 모바일은 모바일 전용 인덱스 사용
if (G5_IS_MOBILE) {
    include_once(G5_THEME_MOBILE_PATH . '/index.php');
    return;
}

include_once(G5_THEME_PATH . '/head.php');
?>
<link href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/variable/pretendardvariable-dynamic-subset.min.css" rel="stylesheet" />
<link href="<?php echo G5_THEME_URL; ?>/font/SUIT-Variable.css" rel="stylesheet">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Serif+KR:wght@400;500;600&display=swap">
<link href="<?php echo G5_THEME_URL; ?>/mont/font.css" rel="stylesheet">
<link rel="stylesheet" href="<?php echo G5_URL; ?>/ko/css/design-tokens.css?ver=<?php echo G5_CSS_VER; ?>">
<link rel="stylesheet" href="<?php echo G5_URL; ?>/ko/css/company-card.css?ver=<?php echo G5_CSS_VER; ?>">
<link rel="stylesheet" href="<?php echo G5_URL; ?>/ko/css/section-header.css?ver=<?php echo G5_CSS_VER; ?>">
<link rel="stylesheet" href="<?php echo G5_URL; ?>/ko/css/auth-toggle.css?ver=<?php echo G5_CSS_VER; ?>">
<link rel="stylesheet" href="<?php echo G5_URL; ?>/ko/css/verify-modal.css?ver=<?php echo G5_CSS_VER; ?>">
<link rel="stylesheet" href="<?php echo G5_URL; ?>/ko/css/ai-loading.css?ver=<?php echo G5_CSS_VER; ?>">
<link rel="stylesheet" href="<?php echo G5_URL; ?>/ko/css/trade_chat_modal.css?ver=<?php echo G5_CSS_VER; ?>">
<link rel="stylesheet" href="<?php echo G5_THEME_URL; ?>/css/index.css?ver=<?php echo G5_CSS_VER; ?>&rev=20260417-1610">
<?php

// 점수를 0~5 범위로 보정
function clamp_score_5($value) {
    $num = (float)$value;
    if ($num < 0) return 0.0;
    if ($num > 5) return 5.0;
    return $num;
}

// 레이더 차트 점 좌표 계산
function radar_point($cx, $cy, $r, $idx, $count = 5) {
    $angle = (M_PI * 2 * $idx / $count) - (M_PI / 2);
    return array(
        $cx + ($r * cos($angle)),
        $cy + ($r * sin($angle))
    );
}

// 카드용 레이더 SVG 생성
function render_company_radar_svg($scores) {
    $labels = array('성장성', '수익성', '재무안정', '업력', '고용규모');
    $num = 5;
    $cx = 105;
    $cy = 100;
    $maxR = 48;
    $labelR = 62;
    $gridLevels = array(0.2, 0.4, 0.6, 0.8, 1.0);

    $html = '';

    foreach ($gridLevels as $lv) {
        $pts = array();
        for ($i = 0; $i < $num; $i++) {
            list($x, $y) = radar_point($cx, $cy, $maxR * $lv, $i, $num);
            $pts[] = round($x, 2) . ',' . round($y, 2);
        }
        $html .= '<polygon points="' . implode(' ', $pts) . '" class="radar-grid"></polygon>';
    }

    for ($i = 0; $i < $num; $i++) {
        list($x, $y) = radar_point($cx, $cy, $maxR, $i, $num);
        $html .= '<line x1="' . $cx . '" y1="' . $cy . '" x2="' . round($x, 2) . '" y2="' . round($y, 2) . '" stroke="#E8EBF0" stroke-width="0.5"></line>';
    }

    $dataPts = array();
    for ($i = 0; $i < $num; $i++) {
        $score = isset($scores[$i]) ? clamp_score_5($scores[$i]) : 0.0;
        $norm = $score / 5.0;
        list($x, $y) = radar_point($cx, $cy, $maxR * $norm, $i, $num);
        $dataPts[] = round($x, 2) . ',' . round($y, 2);
    }
    $html .= '<polygon points="' . implode(' ', $dataPts) . '" class="radar-shape"></polygon>';

    for ($i = 0; $i < $num; $i++) {
        $score = isset($scores[$i]) ? clamp_score_5($scores[$i]) : 0.0;
        $norm = $score / 5.0;
        list($x, $y) = radar_point($cx, $cy, $maxR * $norm, $i, $num);
        $html .= '<circle cx="' . round($x, 2) . '" cy="' . round($y, 2) . '" class="radar-dot"></circle>';
    }

    for ($i = 0; $i < $num; $i++) {
        list($x, $y) = radar_point($cx, $cy, $labelR, $i, $num);
        $anchor = 'middle';
        $dx = 0;
        if ($x < $cx - 5) {
            $anchor = 'end';
            $dx = -2;
        } else if ($x > $cx + 5) {
            $anchor = 'start';
            $dx = 2;
        }
        $dy = 0;
        if ($y < $cy - 10) $dy = -3;
        else if ($y > $cy + 10) $dy = 8;
        else $dy = 3;
        $html .= '<text x="' . round($x + $dx, 2) . '" y="' . round($y + $dy, 2) . '" text-anchor="' . $anchor . '" class="radar-label">' . $labels[$i] . '</text>';
    }

    return $html;
}

// 평점을 별 아이콘 문자열로 변환
function render_score_stars($score) {
    $score = clamp_score_5($score);
    $full = (int)floor($score);
    $part = $score - $full;
    $count = $full + ($part >= 0.3 ? 1 : 0);
    if ($count < 1) $count = 1;
    if ($count > 5) $count = 5;

    $star = '<svg viewBox="0 0 24 24" width="14" height="14" fill="#ffc107"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>';
    return str_repeat($star, $count);
}

// 리스트 노출용 회사명 마스킹
function mask_company_name_for_list($name) {
    $name = trim((string)$name);
    if ($name === '') return '';

    // 이미 마스킹된 값이면 그대로 사용
    if (preg_match('/\*{2,}/u', $name)) {
        return $name;
    }

    $prefix = '';
    $base = $name;
    if (preg_match('/^(\(주\)|\(유\)|\(사\))(.*)$/u', $name, $m)) {
        $prefix = $m[1];
        $base = trim($m[2]);
    }

    if ($base === '') {
        return $prefix . '****';
    }

    // 법인 접두사가 있으면 1글자, 없으면 2글자 노출
    $visibleLen = ($prefix !== '') ? 1 : 2;
    $visible = function_exists('mb_substr')
        ? mb_substr($base, 0, $visibleLen, 'UTF-8')
        : substr($base, 0, $visibleLen);

    return $prefix . $visible . '****';
}

// 사업자번호 비교를 위해 숫자만 남긴다.
function normalize_bzno_for_unlock($bzno) {
    return preg_replace('/[^0-9]/', '', (string)$bzno);
}

// 로그인 회원이 크레딧 사용 완료한 거래요청의 target_bzno 목록을 조회한다.
function fetch_unlocked_bzno_map_for_member($memberId) {
    $memberId = trim((string)$memberId);
    if ($memberId === '') return array();

    $map = array();
    $res = sql_query("
        SELECT target_bzno
        FROM trade_request
        WHERE request_mb_id = '" . sql_escape_string($memberId) . "'
          AND credit_used = 1
          AND target_bzno != ''
    ");

    while ($row = sql_fetch_array($res)) {
        $normalized = normalize_bzno_for_unlock($row['target_bzno'] ?? '');
        if ($normalized !== '') {
            $map[$normalized] = true;
        }
    }

    return $map;
}

// 상단 카드에 쓸 기업 데이터 조회/가공
function get_processed_companies($order_by_col, $limit = 3, $unlocked_bzno_map = array()) {
    $allowed_order_cols = array('growth_score_5', 'stability_score_5');
    if (!in_array($order_by_col, $allowed_order_cols, true)) {
        $order_by_col = 'growth_score_5';
    }

    $limit = (int)$limit;
    if ($limit < 1) $limit = 3;

    $sql = "SELECT a.KEDCD, a.BZNO, a.ENP_NM, a.ENP_NM_TRD, a.PD_NM, a.VENP_YN,
                   COALESCE(b.growth_score_5, 0) AS growth_score_5,
                   COALESCE(b.profit_score_5, 0) AS profit_score_5,
                   COALESCE(b.stability_score_5, 0) AS stability_score_5,
                   COALESCE(b.age_score_5, 0) AS age_score_5,
                   COALESCE(b.emp_score_5, 0) AS emp_score_5,
                   COALESCE(b.final_total_score_5, 0) AS final_total_score_5
            FROM company a
            LEFT JOIN company_score_final_dedup b ON a.KEDCD = b.KEDCD
            WHERE b.{$order_by_col} IS NOT NULL
            ORDER BY b.{$order_by_col} DESC, b.final_total_score_5 DESC
            LIMIT {$limit}";

    $result = sql_query($sql);
    $list = array();

    while($row = sql_fetch_array($result)) {
        $rawName = trim((string)$row['ENP_NM_TRD']) !== '' ? $row['ENP_NM_TRD'] : $row['ENP_NM'];
        $normalizedBzno = normalize_bzno_for_unlock($row['BZNO'] ?? '');
        $isUnlocked = ($normalizedBzno !== '' && !empty($unlocked_bzno_map[$normalizedBzno]));
        $row['display_name'] = $isUnlocked ? $rawName : mask_company_name_for_list($rawName);
        $row['growth_score_5'] = clamp_score_5($row['growth_score_5']);
        $row['profit_score_5'] = clamp_score_5($row['profit_score_5']);
        $row['stability_score_5'] = clamp_score_5($row['stability_score_5']);
        $row['age_score_5'] = clamp_score_5($row['age_score_5']);
        $row['emp_score_5'] = clamp_score_5($row['emp_score_5']);
        $row['final_score_num'] = clamp_score_5($row['final_total_score_5']);
        $row['final_score'] = number_format($row['final_score_num'], 1);

        $tags = array();
        if (!empty($row['PD_NM'])) {
            $raw_tags = explode(',', $row['PD_NM']);
            foreach($raw_tags as $rt) {
                $trimmed = trim($rt);
                if($trimmed) {
                    $tags[] = str_replace(' 외', '', $trimmed);
                }
            }
        }
        $row['PD_NM_TAGS'] = array_values(array_unique($tags));

        $list[] = $row;
    }
    return $list;
}

// 상단 카드 데이터(성장/안정) 준비
$unlocked_bzno_map = fetch_unlocked_bzno_map_for_member($member['mb_id'] ?? '');
$company_list = get_processed_companies('growth_score_5', 3, $unlocked_bzno_map);
$stable_list = get_processed_companies('stability_score_5', 3, $unlocked_bzno_map);
?>


<div id="container" class="full">

<div id="mVisual">
    <div class="mVisual_main">
        <div class="mVisual_left">
            <span class="mVisual_topbanner">실거래 데이터 기반으로, 거래 확률 UP</span>
            <h1 class="mVisual_heading">연결되는 거래에는<br>성공 공식이 있다</h1>
            <div class="mVisual_searchWrap bxShadow">
                <form action="/ko/matching_list.php" method="POST" id="match_index">
                    <input type="hidden" name="cominfo" id="cominfo_index">
                    <input type="text" name="comnum" id="comnum_index" placeholder="사업자등록번호를 입력해보세요" class="comnum_chk" maxlength="12" autocomplete="off">
                    <button type="submit" id="main_submit_index" class="search_btn" aria-label="AI 추천받기">
                        <span class="sparkle"><svg viewBox="0 0 24 24"><use href="#icon-magic-wand"></use></svg></span>
                        <span>AI 추천받기</span>
                    </button>
                </form>
            </div>
        </div>
        <div class="mVisual_right">
            <div class="mVisual_quick">
                <a href="/ko/my_matching.php#received" class="mVisual_quick_item mVisual_quick_item--request">
                    <div class="mVisual_quick_icon">
                        <svg viewBox="0 0 48 48">
                            <path fill="#3b82f6" d="M8 26 L40 26 L36 22 L4 22 Z"></path>
                            <path fill="#1e40af" d="M40 26 L40 40 L36 37 L36 22 Z"></path>
                            <path fill="#2563eb" d="M8 26 L40 26 L40 40 L8 40 Z"></path>
                            <path fill="#1e3a8a" d="M8 26 L8 40 L4 37 L4 22 Z"></path>
                            <path fill="#1d4ed8" d="M12 29 L36 29 L36 37 L12 37 Z"></path>
                            <path fill="#bfdbfe" d="M18 7 L30 7 L30 22 L18 22 Z"></path>
                            <path fill="#93c5fd" d="M26 7 L30 7 L30 11 Z"></path>
                            <path fill="#60a5fa" d="M20 13 L28 13 L28 14.5 L20 14.5 Z"></path>
                            <path fill="#60a5fa" d="M20 16 L26 16 L26 17.5 L20 17.5 Z"></path>
                            <path fill="#60a5fa" d="M21 22 L27 22 L27 29 L21 29 Z"></path>
                            <path fill="#3b82f6" d="M16 27 L24 35 L32 27 Z"></path>
                        </svg>
                    </div>
                    <div class="mVisual_quick_body">
                        <strong>받은 요청</strong>
                        <span>수락 · 거절</span>
                    </div>
                    <svg class="mVisual_quick_arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </a>
                <button type="button" class="mVisual_quick_item mVisual_quick_item--chat" id="openChatDemo">
                    <div class="mVisual_quick_icon">
                        <svg viewBox="0 0 48 48">
                            <path fill="#a78bfa" d="M8 10 L40 10 L36 6 L4 6 Z"></path>
                            <path fill="#5b21b6" d="M40 10 L40 30 L36 27 L36 6 Z"></path>
                            <path fill="#7c3aed" d="M8 10 L40 10 L40 30 L8 30 Z"></path>
                            <path fill="#4c1d95" d="M8 10 L8 30 L4 27 L4 6 Z"></path>
                            <path fill="#6d28d9" d="M10 30 L18 40 L22 30 Z"></path>
                            <path fill="#4c1d95" d="M10 30 L12 32 L18 40 Z"></path>
                            <circle fill="#ddd6fe" cx="17" cy="20" r="3"></circle>
                            <circle fill="#ede9fe" cx="24" cy="20" r="3"></circle>
                            <circle fill="#ddd6fe" cx="31" cy="20" r="3"></circle>
                        </svg>
                    </div>
                    <div class="mVisual_quick_body">
                        <strong>채팅창</strong>
                        <span>담당자 대화</span>
                    </div>
                    <svg class="mVisual_quick_arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </button>
            </div>

            <div class="mVisual_preview">
                <div class="mVisual_preview_cards" id="previewBanner">
                    <div class="mVisual_preview_slide active">
                        <div class="mVisual_preview_card">
                            <div class="mSlide_header">
                                <span class="mSlide_label">AI 적합도 예측</span>
                                <span class="mSlide_sub">A기업 ⇄ B기업</span>
                                <span class="mSlide_score">87<span class="mSlide_score_unit">%</span></span>
                            </div>
                            <div class="mSlide_body mSlide_body--center">
                                <svg viewBox="0 0 300 270" class="detail-radar-svg"><polygon points="150,115 163,122.5 163,137.5 150,145 137,137.5 137,122.5" fill="none" stroke="#e5e7eb" stroke-width="0.8"></polygon><polygon points="150,100 176,115 176,145 150,160 124,145 124,115" fill="none" stroke="#e5e7eb" stroke-width="0.8"></polygon><polygon points="150,85 189,107.5 189,152.5 150,175 111,152.5 111,107.5" fill="none" stroke="#e5e7eb" stroke-width="0.8"></polygon><polygon points="150,70 202,100 202,160 150,190 98,160 98,100" fill="none" stroke="#e5e7eb" stroke-width="0.8"></polygon><line x1="150" y1="130" x2="150" y2="70" stroke="#e5e7eb" stroke-width="0.6"></line><line x1="150" y1="130" x2="202" y2="100" stroke="#e5e7eb" stroke-width="0.6"></line><line x1="150" y1="130" x2="202" y2="160" stroke="#e5e7eb" stroke-width="0.6"></line><line x1="150" y1="130" x2="150" y2="190" stroke="#e5e7eb" stroke-width="0.6"></line><line x1="150" y1="130" x2="98" y2="160" stroke="#e5e7eb" stroke-width="0.6"></line><line x1="150" y1="130" x2="98" y2="100" stroke="#e5e7eb" stroke-width="0.6"></line><polygon points="150,74.8 194.2,104.5 190.5,153.4 150,184 107.4,154.6 100.6,101.5" fill="rgba(109,40,217,0.12)" stroke="rgba(109,40,217,0.6)" stroke-width="1.8"></polygon><circle cx="150" cy="74.8" r="3.5" fill="rgba(109,40,217,0.8)"></circle><circle cx="194.2" cy="104.5" r="3.5" fill="rgba(109,40,217,0.8)"></circle><circle cx="190.5" cy="153.4" r="3.5" fill="rgba(109,40,217,0.8)"></circle><circle cx="150" cy="184" r="3.5" fill="rgba(109,40,217,0.8)"></circle><circle cx="107.4" cy="154.6" r="3.5" fill="rgba(109,40,217,0.8)"></circle><circle cx="100.6" cy="101.5" r="3.5" fill="rgba(109,40,217,0.8)"></circle><text x="150" y="43" text-anchor="middle" font-size="19" font-weight="700" fill="#333">공급망 근접도</text><text x="229.7" y="89" text-anchor="start" font-size="19" font-weight="700" fill="#333">거래처 유사성</text><text x="229.7" y="181" text-anchor="start" font-size="19" font-weight="700" fill="#333">거래 활성도</text><text x="150" y="227" text-anchor="middle" font-size="19" font-weight="700" fill="#333">재무 안정성</text><text x="70.3" y="181" text-anchor="end" font-size="19" font-weight="700" fill="#333">지역 접근성</text><text x="70.3" y="89" text-anchor="end" font-size="19" font-weight="700" fill="#333">규모 적합도</text></svg>
                                <p class="mSlide_caption">6가지 지표 기반 AI 거래 적합도 분석 결과</p>
                            </div>
                        </div>
                    </div>

                    <div class="mVisual_preview_slide">
                        <div class="mVisual_preview_card">
                            <div class="mSlide_header">
                                <span class="mSlide_label">6가지 분석 지표</span>
                                <span class="mSlide_sub">A기업 ⇄ B기업</span>
                                <span class="mSlide_score">87<span class="mSlide_score_unit">%</span></span>
                            </div>
                            <div class="mSlide_body">
                                <div class="mSlide2_list">
                                    <div class="mSlide2_item"><span class="mSlide2_label">공급망 근접도</span><div class="mSlide2_track"><div class="mSlide2_fill" style="width:92%"></div></div><span class="mSlide2_score">92</span></div>
                                    <div class="mSlide2_item"><span class="mSlide2_label">거래처 유사성</span><div class="mSlide2_track"><div class="mSlide2_fill" style="width:85%"></div></div><span class="mSlide2_score">85</span></div>
                                    <div class="mSlide2_item"><span class="mSlide2_label">거래 활성도</span><div class="mSlide2_track"><div class="mSlide2_fill" style="width:78%"></div></div><span class="mSlide2_score">78</span></div>
                                    <div class="mSlide2_item"><span class="mSlide2_label">재무 안정성</span><div class="mSlide2_track"><div class="mSlide2_fill" style="width:90%"></div></div><span class="mSlide2_score">90</span></div>
                                    <div class="mSlide2_item"><span class="mSlide2_label">지역 접근성</span><div class="mSlide2_track"><div class="mSlide2_fill" style="width:82%"></div></div><span class="mSlide2_score">82</span></div>
                                    <div class="mSlide2_item"><span class="mSlide2_label">규모 적합도</span><div class="mSlide2_track"><div class="mSlide2_fill" style="width:95%"></div></div><span class="mSlide2_score">95</span></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mVisual_preview_slide">
                        <div class="mVisual_preview_card">
                            <div class="mSlide_header">
                                <span class="mSlide_label">AI 분석 보고서</span>
                                <span class="mSlide_sub">A기업 ⇄ B기업</span>
                                <span class="mSlide_score">87<span class="mSlide_score_unit">%</span></span>
                            </div>
                            <div class="mSlide_body">
                                <div class="mVisual_report_card">
                                    <div class="mVisual_report_oneline">
                                        <span class="mVisual_report_oneline_label">한줄 요약</span>
                                        <p>기존 거래망과 유사한 구조의 협력 후보입니다.</p>
                                    </div>
                                    <div class="mVisual_report_divider"></div>
                                    <div class="mVisual_report_reason_row">
                                        <div class="mVisual_report_reason_item">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                            <span>공급망 근접도가 높아 거래 전환 가능성 우수</span>
                                        </div>
                                        <div class="mVisual_report_reason_item">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                            <span>공동 거래처 네트워크 72% 중복</span>
                                        </div>
                                        <div class="mVisual_report_reason_item">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                            <span>최근 3년 연평균 매출 18% 성장</span>
                                        </div>
                                    </div>
                                    <div class="mVisual_report_divider"></div>
                                    <div class="mVisual_report_more">
                                        <div class="mVisual_report_more_items">
                                            <span>거래처 유사성 분석</span>
                                            <span>재무 안정성 평가</span>
                                            <span>지역·물류 시너지</span>
                                        </div>
                                        <p class="mVisual_report_more_hint">외 총 6개 항목의 상세분석이 포함됩니다.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mVisual_preview_indicator">
                        <button class="mVisual_preview_dot active" data-slide="0"></button>
                        <button class="mVisual_preview_dot" data-slide="1"></button>
                        <button class="mVisual_preview_dot" data-slide="2"></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="mCategories" class="mSection">
    <div class="inner">
        <div class="mCategories_row">
            <div class="mCategories_primary">
        <!-- 데스크탑/태블릿: 그리드 (대분류 8개) -->
        <div class="mCategories_grid">
            <a href="/ko/matching_list.php?cat=metal" class="mCategories_item" title="금속·소재"><div class="cat_inner"><div class="cat_icon"><svg viewBox="0 0 48 48"><use href="#cat-iso-7"/></svg></div><span class="cat_label">금속·소재</span></div></a>
            <a href="/ko/matching_list.php?cat=chemical" class="mCategories_item" title="화학·케미컬·원료"><div class="cat_inner"><div class="cat_icon"><svg viewBox="0 0 48 48"><use href="#cat-iso-5"/></svg></div><span class="cat_label">화학·케미컬·원료</span></div></a>
            <a href="/ko/matching_list.php?cat=electronics" class="mCategories_item" title="전자·전기·반도체"><div class="cat_inner"><div class="cat_icon"><svg viewBox="0 0 48 48"><use href="#cat-iso-10"/></svg></div><span class="cat_label">전자·전기·반도체</span></div></a>
            <a href="/ko/matching_list.php?cat=machinery" class="mCategories_item" title="기계·설비·산업장비"><div class="cat_inner"><div class="cat_icon"><svg viewBox="0 0 48 48"><use href="#cat-iso-6"/></svg></div><span class="cat_label">기계·설비·산업장비</span></div></a>
            <a href="/ko/matching_list.php?cat=auto" class="mCategories_item" title="자동차·모빌리티"><div class="cat_inner"><div class="cat_icon"><svg viewBox="0 0 48 48"><use href="#cat-iso-11"/></svg></div><span class="cat_label">자동차·모빌리티</span></div></a>
            <a href="/ko/matching_list.php?cat=food" class="mCategories_item" title="식자재·식품 B2B"><div class="cat_inner"><div class="cat_icon"><svg viewBox="0 0 48 48"><use href="#cat-iso-4"/></svg></div><span class="cat_label">식자재·식품 B2B</span></div></a>
            <a href="/ko/matching_list.php?cat=medical" class="mCategories_item" title="의료·헬스케어"><div class="cat_inner"><div class="cat_icon"><svg viewBox="0 0 48 48"><use href="#cat-iso-17"/></svg></div><span class="cat_label">의료·헬스케어</span></div></a>
            <a href="/ko/matching_list.php?cat=packaging" class="mCategories_item" title="포장재·소모성 자재"><div class="cat_inner"><div class="cat_icon"><svg viewBox="0 0 48 48"><use href="#cat-iso-12"/></svg></div><span class="cat_label">포장재·소모성 자재</span></div></a>
        </div>
        <!-- 모바일 전용: 대분류 8개 2줄 스와이프 캐러셀 -->
        <div class="mCategories_carousel_wrap">
            <div class="mCategories_carousel_card">
            <div class="mCategories_swiper swiper">
                <div class="swiper-wrapper">
                    <div class="swiper-slide"><a href="/ko/matching_list.php?cat=metal" class="mCategories_item" title="금속·소재"><div class="cat_inner"><div class="cat_icon"><svg viewBox="0 0 48 48"><use href="#cat-iso-7"/></svg></div><span class="cat_label">금속·소재</span></div></a></div>
                    <div class="swiper-slide"><a href="/ko/matching_list.php?cat=chemical" class="mCategories_item" title="화학·케미컬·원료"><div class="cat_inner"><div class="cat_icon"><svg viewBox="0 0 48 48"><use href="#cat-iso-5"/></svg></div><span class="cat_label">화학·케미컬·원료</span></div></a></div>
                    <div class="swiper-slide"><a href="/ko/matching_list.php?cat=electronics" class="mCategories_item" title="전자·전기·반도체"><div class="cat_inner"><div class="cat_icon"><svg viewBox="0 0 48 48"><use href="#cat-iso-10"/></svg></div><span class="cat_label">전자·전기·반도체</span></div></a></div>
                    <div class="swiper-slide"><a href="/ko/matching_list.php?cat=machinery" class="mCategories_item" title="기계·설비·산업장비"><div class="cat_inner"><div class="cat_icon"><svg viewBox="0 0 48 48"><use href="#cat-iso-6"/></svg></div><span class="cat_label">기계·설비·산업장비</span></div></a></div>
                    <div class="swiper-slide"><a href="/ko/matching_list.php?cat=auto" class="mCategories_item" title="자동차·모빌리티"><div class="cat_inner"><div class="cat_icon"><svg viewBox="0 0 48 48"><use href="#cat-iso-11"/></svg></div><span class="cat_label">자동차·모빌리티</span></div></a></div>
                    <div class="swiper-slide"><a href="/ko/matching_list.php?cat=food" class="mCategories_item" title="식자재·식품 B2B"><div class="cat_inner"><div class="cat_icon"><svg viewBox="0 0 48 48"><use href="#cat-iso-4"/></svg></div><span class="cat_label">식자재·식품 B2B</span></div></a></div>
                    <div class="swiper-slide"><a href="/ko/matching_list.php?cat=medical" class="mCategories_item" title="의료·헬스케어"><div class="cat_inner"><div class="cat_icon"><svg viewBox="0 0 48 48"><use href="#cat-iso-17"/></svg></div><span class="cat_label">의료·헬스케어</span></div></a></div>
                    <div class="swiper-slide"><a href="/ko/matching_list.php?cat=packaging" class="mCategories_item" title="포장재·소모성 자재"><div class="cat_inner"><div class="cat_icon"><svg viewBox="0 0 48 48"><use href="#cat-iso-12"/></svg></div><span class="cat_label">포장재·소모성 자재</span></div></a></div>
                </div>
                <div class="swiper-pagination mCategories_carousel_pagination"></div>
            </div>
            </div>
        </div>
            </div>
            <div class="mVisual_banner_wrap mVisual_banner_compact">
                <div class="mVisual_banner_swiper swiper">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <aside class="mVisual_banner mVisual_banner_card">
                                <div class="mVisual_banner_inner">
                                    <span class="mVisual_banner_status">테스트 운영 중 · 4월 말 런칭</span>
                                    <p class="mVisual_banner_main">거래 빈도가 높은<br>핵심 산업 집중</p>
                                    <p class="mVisual_banner_sub">제조·유통 기반 추천 모델 고도화</p>
                                </div>
                            </aside>
                        </div>
                        <div class="swiper-slide">
                            <aside class="mVisual_banner mVisual_banner_card">
                                <div class="mVisual_banner_inner">
                                    <span class="mVisual_banner_status">오픈 기념 · 크레딧 즉시 지급</span>
                                    <p class="mVisual_banner_main">가입만 해도<br>무료 크레딧 제공</p>
                                    <p class="mVisual_banner_sub">지금 가입하고 거래를 시작하세요</p>
                                </div>
                            </aside>
                        </div>
                    </div>
                <div class="mVisual_banner_pagination" aria-hidden="true">
                    <span class="mVisual_banner_page_current">1</span>/<span class="mVisual_banner_page_total">2</span>
                    <button type="button" class="mVisual_banner_next" aria-label="다음 배너"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg></button>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="mHookSections">
    <div class="mHookSection" id="hkGrowth">
        <div class="section-inner">
            <div class="mHookSection_header">
                <div class="mHookSection_label mHookSection_label--growth">
                    <span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                            <polyline points="17 6 23 6 23 12"></polyline>
                        </svg>
                    </span>
                    <span>GROWTH</span>
                </div>
                <div class="mHookSection_titleRow">
                    <h2 class="mHookSection_title">3년 연속 성장 기업</h2>
                    <a href="/ko/matching_list.php?sort=grow" class="mHookSection_more">더보기</a>
                </div>
                <p class="mHookSection_subtitle">매출과 고용이 꾸준히 성장하고 있는 검증된 기업들</p>
            </div>
            <div class="card-grid" id="hkGrowthGrid">
                <?php foreach ($company_list as $index => $com): 
                    $allTags = $com['PD_NM_TAGS'] ?? [];
                    $totalCount = count($allTags);
                    $showNum = 2;
                    $displayTags = array_slice($allTags, 0, $showNum);
                    $restCount = max($totalCount - $showNum, 0);
                    $growthTop = (int)round($com['growth_score_5'] * 20);
                    $detailUrl = '/ko/company_detail.php?cd=' . urlencode(simple_crypt($com['KEDCD']));
                ?>
                <div class="mCompanyCard detail_view" kedcd="<?= $com['KEDCD'] ?>" data-kedcd="<?= $com['KEDCD'] ?>" data-detail-url="<?= htmlspecialchars($detailUrl, ENT_QUOTES) ?>">
                    <div class="mCompanyCard_content">
                        <div class="hk-badges">
                            <span class="hk-badge hk-badge--growth">성장TOP<?= $growthTop ?>%</span>
                            <?php if (($com['VENP_YN'] ?? 'N') === 'Y'): ?>
                            <span class="hk-badge hk-badge--venture">벤처인증</span>
                            <?php endif; ?>
                        </div>

                        <h3 class="mCompanyCard_title"><?= htmlspecialchars($com['display_name']) ?></h3>

                        <p class="mCompanyCard_desc">
                            <?php if ($totalCount > 0): ?>
                                <?php foreach ($displayTags as $tag): ?>
                                    <span class="mCompanyCard_desc_tag"><?= htmlspecialchars($tag) ?></span>
                                <?php endforeach; ?>
                                <?php if ($restCount > 0): ?>
                                    <span class="mCompanyCard_desc_more">+<?= $restCount ?></span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </p>

                        <div class="radar-card">
                            <div class="radar-card_chart">
                                <svg viewBox="0 0 210 200" class="radar-svg">
                                    <?= render_company_radar_svg(array(
                                        $com['growth_score_5'],
                                        $com['profit_score_5'],
                                        $com['stability_score_5'],
                                        $com['age_score_5'],
                                        $com['emp_score_5']
                                    )) ?>
                                </svg>
                            </div>
                        </div>

                        <div class="mCompanyCard_footer_stars">
                            <span class="mCompanyCard_footer_stars_wrap"><?= render_score_stars($com['final_score_num']) ?></span>
                            <span class="mCompanyCard_footer_score"><?= $com['final_score'] ?></span>
                            <span class="mCompanyCard_footer_max">/ 5.0</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="mHookSection mHookSection_stable" id="hkStable">
        <div class="section-inner">
            <div class="mHookSection_header">
                <div class="mHookSection_label mHookSection_label--stable">
                    <span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                    </span>
                    <span>STABILITY</span>
                </div>
                <div class="mHookSection_titleRow">
                    <h2 class="mHookSection_title">안정성이 높은 기업</h2>
                    <a href="/ko/matching_list.php?sort=stable" class="mHookSection_more">더보기</a>
                </div>
                <p class="mHookSection_subtitle">지역 접근성과 업력이 탄탄한 신뢰할 수 있는 파트너</p>
            </div>
            <div class="card-grid" id="hkStableGrid">
                <?php foreach ($stable_list as $idx => $com): 
                    $allTags = $com['PD_NM_TAGS'] ?? [];
                    $totalCount = count($allTags);
                    $showNum = 2;
                    $displayTags = array_slice($allTags, 0, $showNum);
                    $restCount = max($totalCount - $showNum, 0);
                    $stableTop = (int)round($com['stability_score_5'] * 20);
                    $detailUrl = '/ko/company_detail.php?cd=' . urlencode(simple_crypt($com['KEDCD']));
                ?>
                <div class="mCompanyCard detail_view" kedcd="<?= $com['KEDCD'] ?>" data-kedcd="<?= $com['KEDCD'] ?>" data-detail-url="<?= htmlspecialchars($detailUrl, ENT_QUOTES) ?>">
                    <div class="mCompanyCard_content">
                        <div class="hk-badges">
                            <span class="hk-badge hk-badge--stable">재무안정TOP<?= $stableTop ?>%</span>
                            <?php if (($com['VENP_YN'] ?? 'N') === 'Y'): ?>
                            <span class="hk-badge hk-badge--venture">벤처인증</span>
                            <?php endif; ?>
                        </div>
                        
                        <h3 class="mCompanyCard_title"><?= htmlspecialchars($com['display_name']) ?></h3>
                        
                        <p class="mCompanyCard_desc">
                            <?php if ($totalCount > 0): ?>
                                <?php foreach ($displayTags as $tag): ?>
                                    <span class="mCompanyCard_desc_tag"><?= htmlspecialchars($tag) ?></span>
                                <?php endforeach; ?>
                                <?php if ($restCount > 0): ?>
                                    <span class="mCompanyCard_desc_more">+<?= $restCount ?></span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </p>

                        <div class="radar-card">
                            <div class="radar-card_chart">
                                <svg viewBox="0 0 210 200" class="radar-svg">
                                    <?= render_company_radar_svg(array(
                                        $com['growth_score_5'],
                                        $com['profit_score_5'],
                                        $com['stability_score_5'],
                                        $com['age_score_5'],
                                        $com['emp_score_5']
                                    )) ?>
                                </svg>
                            </div>
                        </div>
                        
                        <div class="mCompanyCard_footer_stars">
                            <span class="mCompanyCard_footer_stars_wrap"><?= render_score_stars($com['final_score_num']) ?></span>
                            <span class="mCompanyCard_footer_score"><?= $com['final_score'] ?></span>
                            <span class="mCompanyCard_footer_max">/ 5.0</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

    <div id="company-view-lay" class="layer-base layer-main company-view">
        <div class="layer-in new"></div>
    </div>  

    <?php
    $company_detail_popup_variant = 'advanced';
    include_once(G5_THEME_PATH . '/partials/company-detail-matching-popup.php');
    include_once(G5_PATH . '/ko/modal/trade_chat_modal.php');
    ?>


<script src="<?php echo G5_THEME_JS_URL; ?>/index.js?ver=<?php echo G5_JS_VER; ?>&rev=20260417-1550"></script>
<script src="<?php echo G5_URL; ?>/ko/js/trade_chat_modal.js?ver=<?php echo G5_JS_VER; ?>"></script>

	</div>
<?php
include_once(G5_THEME_PATH.'/tail.php');
?>
