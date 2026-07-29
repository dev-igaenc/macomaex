<?php
if (!defined('_INDEX_')) define('_INDEX_', true);
if (!defined('_GNUBOARD_')) exit;

include_once(G5_THEME_PATH . '/head.php');
?>
<?php
$index_css_ver = file_exists(G5_THEME_PATH . '/css/index.css') ? filemtime(G5_THEME_PATH . '/css/index.css') : G5_CSS_VER;
$index_js_ver = file_exists(G5_THEME_PATH . '/js/index.js') ? filemtime(G5_THEME_PATH . '/js/index.js') : G5_JS_VER;
$stream_chat_vendor_js_ver = file_exists(G5_PATH . '/ko/js/vendor/stream-chat.browser.full-bundle.min.js') ? filemtime(G5_PATH . '/ko/js/vendor/stream-chat.browser.full-bundle.min.js') : G5_JS_VER;
?>
<link href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/variable/pretendardvariable-dynamic-subset.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Serif+KR:wght@400;500;600&display=swap">
<link rel="stylesheet" href="<?php echo $prix_root_asset_url('ko/css/company-card.css', G5_CSS_VER); ?>">
<link rel="stylesheet" href="<?php echo $prix_root_asset_url('ko/css/section-header.css', G5_CSS_VER); ?>">
<link rel="stylesheet" href="<?php echo $prix_root_asset_url('ko/css/auth-toggle.css', G5_CSS_VER); ?>">
<link rel="stylesheet" href="<?php echo $prix_root_asset_url('ko/css/ai-loading.css', G5_CSS_VER); ?>">
<link rel="stylesheet" href="<?php echo $prix_root_asset_url('ko/css/trade_chat_modal.css', G5_CSS_VER); ?>">
<link rel="stylesheet" href="<?php echo $prix_theme_asset_url('css/index.css', $index_css_ver); ?>">
<?php

$main_categories = include G5_PATH . '/ko/matching_categories.php';

function render_main_category_item($category, $wrap_slide = false) {
    $slug = isset($category['slug']) ? (string)$category['slug'] : '';
    $label = isset($category['label']) ? (string)$category['label'] : '';
    $icon = !empty($category['icon']) ? (string)$category['icon'] : 'cat-iso-all';
    if ($slug === '' || $label === '') return '';

    $href = '/companies?category=' . rawurlencode($slug);
    $item = '<a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '" class="mCategories_item" title="' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '">'
        . '<div class="cat_inner">'
        . '<div class="cat_icon"><svg viewBox="0 0 48 48" aria-hidden="true"><use href="#' . htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') . '"></use></svg></div>'
        . '<span class="cat_label">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span>'
        . '</div>'
        . '</a>';

    if ($wrap_slide) {
        return '<div class="swiper-slide">' . $item . '</div>';
    }

    return $item;
}

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

    // 상단 카드 중복 노출 방지를 위해 limit보다 넉넉히 가져온 뒤 PHP에서 중복 제거
    $fetch_limit = max($limit * 30, 30);

    $sql = "SELECT a.KEDCD, a.BZNO, a.ENP_NM, a.ENP_NM_TRD, a.keywords_json, a.keyword_count, a.VENP_YN,
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
            LIMIT {$fetch_limit}";

    $result = sql_query($sql);
    $list = array();
    $seen_company_keys = array();
    $seen_name_keys = array();

    while($row = sql_fetch_array($result)) {
        $rawName = trim((string)$row['ENP_NM_TRD']) !== '' ? $row['ENP_NM_TRD'] : $row['ENP_NM'];
        $normalizedBzno = normalize_bzno_for_unlock($row['BZNO'] ?? '');
        $isUnlocked = ($normalizedBzno !== '' && !empty($unlocked_bzno_map[$normalizedBzno]));
        $row['display_name'] = $isUnlocked ? $rawName : mask_company_name_for_list($rawName);

        // 같은 기업 중복 제거: BZNO 우선, 없으면 KEDCD 기준
        $kedcd = trim((string)($row['KEDCD'] ?? ''));
        $companyKey = '';
        if ($normalizedBzno !== '') {
            $companyKey = 'BZNO:' . $normalizedBzno;
        } else if ($kedcd !== '') {
            $companyKey = 'KEDCD:' . $kedcd;
        }
        if ($companyKey !== '' && isset($seen_company_keys[$companyKey])) continue;

        // 식별키가 비어있는 경우에만 회사명(원본명)으로 보조 중복 제거
        $nameKey = '';
        if ($companyKey === '') {
            $nameBase = (string)$rawName;
            $nameBase = function_exists('mb_strtolower') ? mb_strtolower($nameBase, 'UTF-8') : strtolower($nameBase);
            $nameKey = preg_replace('/\s+/u', '', $nameBase);
            if ($nameKey !== '' && isset($seen_name_keys[$nameKey])) continue;
        }

        $row['growth_score_5'] = clamp_score_5($row['growth_score_5']);
        $row['profit_score_5'] = clamp_score_5($row['profit_score_5']);
        $row['stability_score_5'] = clamp_score_5($row['stability_score_5']);
        $row['age_score_5'] = clamp_score_5($row['age_score_5']);
        $row['emp_score_5'] = clamp_score_5($row['emp_score_5']);
        $row['final_score_num'] = clamp_score_5($row['final_total_score_5']);
        $row['final_score'] = number_format($row['final_score_num'], 1);

        // 카드 태그는 PD_NM 대신 keywords_json(JSON 배열) 기준으로 구성
        $tags = array();
        $keywords_json_raw = trim((string)($row['keywords_json'] ?? ''));
        if ($keywords_json_raw !== '') {
            $decoded = json_decode($keywords_json_raw, true);
            if (is_array($decoded)) {
                foreach ($decoded as $kw) {
                    $trimmed = trim((string)$kw);
                    if ($trimmed === '') continue;
                    $tags[] = $trimmed;
                }
            }
        }
        $row['KEYWORD_TAGS'] = array_values(array_unique($tags));
        $row['keyword_count'] = max(0, (int)($row['keyword_count'] ?? 0));

        if ($companyKey !== '') $seen_company_keys[$companyKey] = true;
        if ($nameKey !== '') $seen_name_keys[$nameKey] = true;
        $list[] = $row;
        if (count($list) >= $limit) break;
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
            <h1 class="mVisual_heading">AI가 우리 회사와<br><span class="mVisual_heading_point">거래 가능성이 높은 기업</span>을<br>추천합니다.</h1>
            <span class="mVisual_topbanner">사업자등록번호를 입력하면 <span class="mVisual_topbanner_point">AI가 실거래 데이터를 분석해</span><br class="mVisual_subBr">추천 이유와 함께 결과를 제공합니다.</span>
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
            <div class="mVisual_quick">
                <a href="/account/trades#received" class="mVisual_quick_item mVisual_quick_item--request">
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
        </div>
        <div class="mVisual_right">
            <div class="mVisual_interview" id="heroInterview">
                <div class="mHeroSlides">
                    <div class="mHeroSlide mHeroSlide--video active">
                        <video class="mVisual_video_thumb" id="mHeroVideo" src="<?php echo G5_THEME_URL; ?>/video/hero-guide.mp4" poster="<?php echo G5_THEME_URL; ?>/img/main/hero-video-thumb.png" muted loop playsinline autoplay preload="metadata" aria-label="매커멕스 사용방법 영상"></video>
                        <button type="button" class="mVisual_video_play" id="mHeroVideoToggle" aria-label="사용방법 영상 크게 보기" data-video-src="<?php echo G5_THEME_URL; ?>/video/hero-guide.mp4">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"></path></svg>
                            <span class="mVisual_video_play_tip">크게 보기</span>
                        </button>
                        <div class="mVisual_video_overlay">
                            <h3 class="mVisual_video_title">매커멕스 100% 활용법</h3>
                            <p class="mVisual_video_cap">3분이면 끝! 거래처 찾기부터 AI 추천 활용법까지</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mVideoModal" id="mVideoModal" aria-hidden="true">
    <div class="mVideoModal_backdrop" data-video-close></div>
    <div class="mVideoModal_dialog" role="dialog" aria-modal="true" aria-label="매커멕스 사용방법 영상">
        <button type="button" class="mVideoModal_close" data-video-close aria-label="닫기">&times;</button>
        <div class="mVideoModal_frame">
            <video id="mVideoPlayer" controls playsinline preload="metadata"></video>
        </div>
        <p class="mVideoModal_note">매커멕스 사용방법 안내 영상입니다.</p>
    </div>
</div>

<div id="mCategories" class="mSection">
    <div class="inner">
        <div class="mCategories_row">
            <div class="mCategories_primary">
                <div class="mCategories_grid">
                    <?php foreach ($main_categories as $main_category): ?>
                    <?php echo render_main_category_item($main_category); ?>
                    <?php endforeach; ?>
                </div>
                <div class="mCategories_carousel_wrap">
                    <div class="mCategories_carousel_card">
                        <div class="mCategories_swiper swiper">
                            <div class="swiper-wrapper">
                                <?php foreach ($main_categories as $main_category): ?>
                                <?php echo render_main_category_item($main_category, true); ?>
                                <?php endforeach; ?>
                            </div>
                            <div class="swiper-pagination mCategories_carousel_pagination"></div>
                        </div>
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
                    <a href="/companies?sort=grow" class="mHookSection_more">더보기</a>
                </div>
                <p class="mHookSection_subtitle">매출과 고용이 꾸준히 성장하고 있는 검증된 기업들</p>
            </div>
            <div class="card-grid" id="hkGrowthGrid">
                <?php foreach ($company_list as $index => $com): 
                    $allTags = $com['KEYWORD_TAGS'] ?? [];
                    $showNum = 3;
                    $displayTags = array_slice($allTags, 0, $showNum);
                    $totalCount = isset($com['keyword_count']) && (int)$com['keyword_count'] > 0
                        ? (int)$com['keyword_count']
                        : count($allTags);
                    $restCount = max($totalCount - count($displayTags), 0);
                    $growthTop = (int)round($com['growth_score_5']);
                    $detailUrl = '/companies/detail?id=' . urlencode(simple_crypt($com['KEDCD']));
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
                    <a href="/companies?sort=stable" class="mHookSection_more">더보기</a>
                </div>
                <p class="mHookSection_subtitle">지역 접근성과 업력이 탄탄한 신뢰할 수 있는 파트너</p>
            </div>
            <div class="card-grid" id="hkStableGrid">
                <?php foreach ($stable_list as $idx => $com): 
                    $allTags = $com['KEYWORD_TAGS'] ?? [];
                    $showNum = 3;
                    $displayTags = array_slice($allTags, 0, $showNum);
                    $totalCount = isset($com['keyword_count']) && (int)$com['keyword_count'] > 0
                        ? (int)$com['keyword_count']
                        : count($allTags);
                    $restCount = max($totalCount - count($displayTags), 0);
                    $stableTop = (int)round($com['stability_score_5']);
                    $detailUrl = '/companies/detail?id=' . urlencode(simple_crypt($com['KEDCD']));
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

<script>
window.__INDEX_LOGIN_GUARD_CONTEXT = {
    isLoggedIn: <?php echo !empty($member['mb_id']) ? 'true' : 'false'; ?>,
    returnUrl: "/account/trades#received",
    loginUrl: "/bbs/login.php?url=<?php echo urlencode('/account/trades#received'); ?>"
};

window.__TRADE_CHAT_MODAL_CONTEXT = {
    isLoggedIn: <?php echo !empty($member['mb_id']) ? 'true' : 'false'; ?>,
    loginUrl: "/bbs/login.php?url=<?php echo urlencode('/'); ?>",
    bootstrapUrl: "/ko/trade_chat_modal_data.php"
};
</script>
<script src="<?php echo G5_URL; ?>/ko/js/vendor/stream-chat.browser.full-bundle.min.js?ver=<?php echo $stream_chat_vendor_js_ver; ?>"></script>
<script src="<?php echo $prix_theme_asset_url('js/index.js', $index_js_ver); ?>"></script>
<script src="<?php echo $prix_root_asset_url('ko/js/trade_chat_modal.js', G5_JS_VER); ?>"></script>

	</div>
<?php
include_once(G5_THEME_PATH.'/tail.php');
?>
