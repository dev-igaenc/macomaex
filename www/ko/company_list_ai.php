<?php
include_once('./_common.php');

$board['bo_10'] = '3';
$board['bo_9'] = '1';

$company_list_ai_css_ver = @filemtime(G5_PATH . '/ko/css/company_list_ai.css');
if (!$company_list_ai_css_ver) $company_list_ai_css_ver = G5_CSS_VER;
$company_list_ai_js_ver = @filemtime(G5_PATH . '/ko/js/company_list_ai.js');
if (!$company_list_ai_js_ver) $company_list_ai_js_ver = defined('G5_JS_VER') ? G5_JS_VER : G5_CSS_VER;

add_stylesheet('<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/variable/pretendardvariable-dynamic-subset.min.css">', 180);
add_stylesheet('<link rel="stylesheet" href="' . G5_URL . '/ko/css/design-tokens.css?ver=' . G5_CSS_VER . '">', 185);
add_stylesheet('<link rel="stylesheet" href="' . G5_URL . '/ko/css/header-core.css?ver=' . G5_CSS_VER . '">', 186);
add_stylesheet('<link rel="stylesheet" href="' . G5_URL . '/ko/css/header-search.css?ver=' . G5_CSS_VER . '">', 187);
add_stylesheet('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">', 188);
add_stylesheet('<link rel="stylesheet" href="' . G5_URL . '/ko/css/badges.css?ver=' . G5_CSS_VER . '">', 190);
add_stylesheet('<link rel="stylesheet" href="' . G5_URL . '/ko/css/company-card.css?ver=' . G5_CSS_VER . '">', 200);
add_stylesheet('<link rel="stylesheet" href="' . G5_URL . '/ko/css/favorite.css?ver=' . G5_CSS_VER . '">', 205);
add_stylesheet('<link rel="stylesheet" href="' . G5_URL . '/ko/css/section-header.css?ver=' . G5_CSS_VER . '">', 210);
add_stylesheet('<link rel="stylesheet" href="' . G5_URL . '/ko/css/verify-modal.css?ver=' . G5_CSS_VER . '">', 215);
add_stylesheet('<link rel="stylesheet" href="' . G5_URL . '/ko/css/ai-loading.css?ver=' . G5_CSS_VER . '">', 216);
add_stylesheet('<link rel="stylesheet" href="' . G5_URL . '/ko/css/company_list_ai.css?ver=' . (int)$company_list_ai_css_ver . '">', 220);

include_once(G5_THEME_PATH . '/head.php');

echo '<style>#pageTop,#sVisual{display:none !important;}</style>';

function company_list_ai_cards()
{
    return [
        'buyer' => [],
        'seller' => [],
    ];
}

function company_list_ai_translate_radar($radar)
{
    if (!is_array($radar)) return array();

    $label_map = array(
        'supply_chain' => '공급망 경로',
        'similarity' => '거래처 유사성',
        'network_overlap' => '거래처 유사성',
        'activity' => '거래 활성도',
        'growth_trend' => '거래 활성도',
        'financial' => '재무 건전성',
        'financial_health' => '재무 건전성',
        'accessibility' => '지역 접근성',
        'scale_fit' => '규모 적합도',
        'scale_match' => '규모 적합도',
        'trade_diversity' => '거래 다변성',
        'market_power' => '시장 경쟁력',
        'company_size' => '기업 규모',
        'trade_loyalty' => '거래 안정성',
    );

    $out = array();
    foreach ($radar as $key => $val) {
        $k = strtolower(trim((string)$key));
        $label = isset($label_map[$k]) ? $label_map[$k] : trim((string)$key);
        if ($label === '') $label = '지표';
        $out[$label] = (float)$val;
    }
    return $out;
}

function company_list_ai_format_bizno($bizno)
{
    $digits = preg_replace('/\D/', '', (string)$bizno);
    if (strlen($digits) !== 10) return '';
    return substr($digits, 0, 3) . '-' . substr($digits, 3, 2) . '-' . substr($digits, 5, 5);
}

function company_list_ai_candidate_label($candidate_type)
{
    $key = strtolower(trim((string)$candidate_type));
    if ($key === '') return '';

    $map = array(
        'chain1' => '1차 연결',
        'chain2' => '2차 연결',
        'similar' => '유사 거래처',
        'neighbor' => '인접 업종',
    );

    return isset($map[$key]) ? $map[$key] : strtoupper($key);
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

    $candidate_type = trim((string)($item['candidate_type'] ?? ''));
    $candidate_label = company_list_ai_candidate_label($candidate_type);
    $bzc_cd = trim((string)($item['bzc_cd'] ?? ''));
    $bzno = preg_replace('/\D/', '', (string)($item['bzno'] ?? ''));
    $bzno_fmt = company_list_ai_format_bizno($bzno);

    $badges = array();
    if ($score >= 90) $badges[] = '상위TOP5%';
    else if ($score >= 75) $badges[] = '상위TOP20%';
    if ($candidate_label !== '') $badges[] = $candidate_label;

    $tags = array();
    if ($bzc_cd !== '') $tags[] = $bzc_cd;
    if ($bzno_fmt !== '') $tags[] = $bzno_fmt;
    if (empty($tags)) $tags[] = 'AI추천';

    return array(
        'kedcd' => $kedcd,
        'title' => $title,
        'score' => $score,
        'badges' => $badges,
        'tags' => $tags,
        'one_line' => trim((string)($item['one_line'] ?? '')),
        'radar' => company_list_ai_translate_radar(isset($item['radar']) ? $item['radar'] : array()),
    );
}

function company_list_ai_render_badges($badges)
{
    if (!is_array($badges) || !$badges) return '';

    $html = '';
    foreach ($badges as $badge) {
        $class = 'mCompanyCard_badge';
        if (strpos($badge, '성장TOP') !== false) $class .= ' mCompanyCard_badge_top_growth';
        else if (strpos($badge, '재무안정TOP') !== false) $class .= ' mCompanyCard_badge_top_stability';
        else if (strpos($badge, '벤처') !== false) $class .= ' mCompanyCard_badge_venture';
        $html .= '<span class="' . $class . '">' . htmlspecialchars($badge, ENT_QUOTES, 'UTF-8') . '</span>';
    }
    return $html;
}

function company_list_ai_mask_name($name)
{
    $name = trim((string)$name);
    if ($name === '') return '';

    if (preg_match('/^(\(주\)|\(유\)|\(사\))(.*)$/u', $name, $m)) {
        $prefix = $m[1];
        $body = trim($m[2]);
        $first = mb_substr($body, 0, 1, 'UTF-8');
        return $prefix . $first . '****';
    }

    $first_two = mb_substr($name, 0, 2, 'UTF-8');
    return $first_two . '****';
}

function company_list_ai_build_radar_axes($radar)
{
    $fallback_labels = ['공급망 근접도', '거래처 유사성', '거래 활성도', '재무 안정성', '지역 접근성', '규모 적합도'];
    $axes = [];

    if (is_array($radar)) {
        foreach ($radar as $label => $score) {
            if (count($axes) >= 6) break;
            $label = trim((string)$label);
            if ($label === '') $label = '지표';
            $score = (float)$score;
            if ($score < 0) $score = 0;
            if ($score > 100) $score = 100;
            $axes[] = ['label' => $label, 'score' => (int)round($score)];
        }
    }

    foreach ($fallback_labels as $label) {
        if (count($axes) >= 6) break;
        $exists = false;
        foreach ($axes as $axis) {
            if ($axis['label'] === $label) {
                $exists = true;
                break;
            }
        }
        if (!$exists) $axes[] = ['label' => $label, 'score' => 0];
    }

    return array_slice($axes, 0, 6);
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

    $shape_points = [];
    foreach ($axes as $idx => $axis) {
        $ratio = ((int)$axis['score']) / 100.0;
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
    if (strpos($label, '유사') !== false || strpos($label, '네트워크') !== false || strpos($lower, 'network') !== false) {
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

function company_list_ai_render_card($card, $trade_type, $rank = 0)
{
    $kedcd = htmlspecialchars($card['kedcd'], ENT_QUOTES, 'UTF-8');
    $title_masked = company_list_ai_mask_name((string)($card['title'] ?? ''));
    $title = htmlspecialchars($title_masked, ENT_QUOTES, 'UTF-8');
    $score = (int)$card['score'];
    $trade_q = (int)$trade_type;
    $url = '/ko/company_detail_ai.php?kedcd=' . rawurlencode($card['kedcd']) . '&trade_type=' . $trade_q;
    $is_top3 = ($rank > 0);

    $tags_html = '';
    $all_tags = isset($card['tags']) && is_array($card['tags']) ? $card['tags'] : [];
    $tags = array_slice($all_tags, 0, 2);
    foreach ($tags as $tag) {
        $tags_html .= '<span class="mCompanyCard_desc_tag">' . htmlspecialchars($tag, ENT_QUOTES, 'UTF-8') . '</span>';
    }
    $hidden_count = max(0, count($all_tags) - count($tags));
    if ($hidden_count > 0) {
        $tags_html .= '<span class="mCompanyCard_desc_more">+' . $hidden_count . '</span>';
    }

    $badge_html = company_list_ai_render_badges($card['badges'] ?? []);
    $radar_rows = company_list_ai_render_radar_rows($card['radar'] ?? []);
    $radar_svg = company_list_ai_render_radar_svg($card['radar'] ?? []);
    $radar_tags = company_list_ai_render_radar_tags($card['radar'] ?? []);
    $one_line = trim((string)($card['one_line'] ?? ''));
    $one_line_attr = $one_line !== '' ? ' title="' . htmlspecialchars($one_line, ENT_QUOTES, 'UTF-8') . '"' : '';

    $html = '<div class="swiper-slide">';
    if ($is_top3) $html .= company_list_ai_render_rank_medal($rank);
    $html .= '<a href="' . $url . '" class="company-ai-link" data-kedcd="' . $kedcd . '" data-trade-type="' . $trade_q . '" data-score="' . $score . '"' . $one_line_attr . ' style="text-decoration:none;color:inherit;">';
    $html .= '<div class="mCompanyCard company-ai-card" style="position:relative;">';
    $html .= '<div class="mCompanyCard_content">';
    $html .= '<div class="mCompanyCard_ai_match"><span class="mCompanyCard_ai_match_label">AI 적합도</span><span><span class="mCompanyCard_ai_match_value">' . $score . '</span><span class="mCompanyCard_ai_match_unit">%</span></span></div>';
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

function company_list_ai_expand_cards($source_cards, $target_count = 48)
{
    if (!is_array($source_cards) || !$source_cards) return array();
    $source_cards = array_values($source_cards);
    $out = array();
    $idx = 0;
    while (count($out) < $target_count) {
        $out[] = $source_cards[$idx % count($source_cards)];
        $idx++;
    }
    return $out;
}

function company_list_ai_build_slide_sets($cards, $trade_type = 1)
{
    $top3 = array();
    $rest = array();
    foreach ($cards as $idx => $card) {
        if ($idx < 3) $top3[] = company_list_ai_render_card($card, $trade_type, $idx + 1);
        else $rest[] = company_list_ai_render_card($card, $trade_type, 0);
    }
    return array('top3' => $top3, 'rest' => $rest);
}

$cards = company_list_ai_cards();
$buyer_cards = isset($cards['buyer']) && is_array($cards['buyer']) ? $cards['buyer'] : array();
$seller_cards = isset($cards['seller']) && is_array($cards['seller']) ? $cards['seller'] : array();
$buy_total_stat = count($buyer_cards);
$sell_total_stat = count($seller_cards);
$buy_live_ok = false;
$sell_live_ok = false;
$buy_call = array('ok' => false, 'error' => '', 'http_code' => 0);
$sell_call = array('ok' => false, 'error' => '', 'http_code' => 0);

$kedcd_query = str_pad(preg_replace('/\D/', '', (string)($_GET['kedcd'] ?? '')), 10, '0', STR_PAD_LEFT);
$has_kedcd_query = isset($_GET['kedcd']) && trim((string)$_GET['kedcd']) !== '';
$bizno_query = preg_replace('/\D/', '', (string)($_GET['bizno'] ?? ($_GET['comnum'] ?? '')));
$has_valid_bizno_query = (strlen($bizno_query) === 10);
$resolved_base_payload = array();

// 진입 파라미터는 kedcd 직접 입력 또는 bizno(comnum) 둘 다 지원
if (($has_kedcd_query && strlen($kedcd_query) === 10 && !preg_match('/^0+$/', $kedcd_query)) || $has_valid_bizno_query) {
    include_once(G5_PATH . '/ko/helper/company_list_ai_helper.php');

    if ($has_kedcd_query && strlen($kedcd_query) === 10 && !preg_match('/^0+$/', $kedcd_query)) {
        $resolved_base_payload = company_list_ai_build_base_payload($_GET);
        $resolved_base_payload['kedcd'] = $kedcd_query;
    } else if ($has_valid_bizno_query) {
        $resolved = company_list_ai_resolve_base_payload_by_bizno($bizno_query, true);
        if (!empty($resolved['ok']) && !empty($resolved['base_payload']) && is_array($resolved['base_payload'])) {
            $resolved_base_payload = $resolved['base_payload'];
            $query_override = company_list_ai_build_base_payload(array_merge($_GET, array(
                'kedcd' => isset($resolved_base_payload['kedcd']) ? $resolved_base_payload['kedcd'] : ''
            )));
            $resolved_base_payload = array_merge($resolved_base_payload, $query_override);
        }
    }
}

if (!empty($resolved_base_payload['kedcd'])) {
    // 화면 렌더 전에 buy/sell을 모두 받아 탭 전환 시 즉시 표시
    $base_payload = $resolved_base_payload;
    $buy_payload = $base_payload;
    $buy_payload['trade_type'] = 1;

    $sell_payload = $base_payload;
    $sell_payload['trade_type'] = 2;

    $buy_call = company_list_ai_call_recommend($buy_payload, 8);
    $sell_call = company_list_ai_call_recommend($sell_payload, 8);

    if (!empty($buy_call['ok']) && !empty($buy_call['data']['results']) && is_array($buy_call['data']['results'])) {
        $buy_live_ok = true;
        $buy_bucket = company_list_ai_build_bucket($buy_call['data']['results'], 45);
        $buy_items = array_merge($buy_bucket['top3'], $buy_bucket['list']);
        $mapped_buy = array();
        foreach ($buy_items as $bi) {
            $card = company_list_ai_ml_item_to_card($bi);
            if (is_array($card)) $mapped_buy[] = $card;
        }
        $buyer_cards = $mapped_buy;
        $buy_total_stat = isset($buy_bucket['total']) ? (int)$buy_bucket['total'] : count($mapped_buy);
    } else if (!empty($buy_call['ok'])) {
        // 실호출 성공 + 0건도 실제 결과로 취급
        $buy_live_ok = true;
        $buyer_cards = array();
        $buy_total_stat = 0;
    }

    if (!empty($sell_call['ok']) && !empty($sell_call['data']['results']) && is_array($sell_call['data']['results'])) {
        $sell_live_ok = true;
        $sell_bucket = company_list_ai_build_bucket($sell_call['data']['results'], 45);
        $sell_items = array_merge($sell_bucket['top3'], $sell_bucket['list']);
        $mapped_sell = array();
        foreach ($sell_items as $si) {
            $card = company_list_ai_ml_item_to_card($si);
            if (is_array($card)) $mapped_sell[] = $card;
        }
        $seller_cards = $mapped_sell;
        $sell_total_stat = isset($sell_bucket['total']) ? (int)$sell_bucket['total'] : count($mapped_sell);
    } else if (!empty($sell_call['ok'])) {
        // 실호출 성공 + 0건도 실제 결과로 취급
        $sell_live_ok = true;
        $seller_cards = array();
        $sell_total_stat = 0;
    }
}

$use_live_pool = ($buy_live_ok || $sell_live_ok);
if ($use_live_pool) {
    $buy_pool = $buy_live_ok ? $buyer_cards : array();
    $sell_pool = $sell_live_ok ? $seller_cards : array();
} else {
    $buy_pool = company_list_ai_expand_cards($buyer_cards, 48);
    $sell_source = !empty($seller_cards) ? $seller_cards : $buyer_cards;
    $sell_pool = company_list_ai_expand_cards($sell_source, 48);
}

$buy_slides = company_list_ai_build_slide_sets($buy_pool, 1);
$sell_slides = company_list_ai_build_slide_sets($sell_pool, 2);

$buy_top3 = isset($buy_slides['top3']) ? $buy_slides['top3'] : array();
$buy_rest_pool = isset($buy_slides['rest']) ? $buy_slides['rest'] : array();
$buy_rest_first = array_slice($buy_rest_pool, 0, 9);
$sell_top3 = isset($sell_slides['top3']) ? $sell_slides['top3'] : array();
$sell_rest_pool = isset($sell_slides['rest']) ? $sell_slides['rest'] : array();

$buy_top3_notice = '';
$buy_rest_notice = '';
$sell_top3_notice = '';
$sell_rest_notice = '';

if (empty($buy_top3)) $buy_top3_notice = !empty($buy_call['error']) ? '구매 추천 데이터를 불러오지 못했습니다.' : '구매 추천 결과가 없습니다.';
if (empty($buy_rest_pool)) $buy_rest_notice = '추가 구매 추천 결과가 없습니다.';
if (empty($sell_top3)) $sell_top3_notice = !empty($sell_call['error']) ? '판매 추천 데이터를 불러오지 못했습니다.' : '판매 추천 결과가 없습니다.';
if (empty($sell_rest_pool)) $sell_rest_notice = '추가 판매 추천 결과가 없습니다.';

?>

<div id="listWrap" class="company-list-ai-real">
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
                    <span class="ai-banner_stat_value"><?php echo (int)$buy_total_stat; ?></span>
                    <span class="ai-banner_stat_label">구매처</span>
                </div>
                <div class="ai-banner_stat">
                    <span class="ai-banner_stat_value"><?php echo (int)$sell_total_stat; ?></span>
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
                        <div class="list-type-switch" role="group" aria-label="구매하기/판매하기">
                            <span class="list-type-slider" aria-hidden="true"></span>
                            <button type="button" class="list-type-btn on" data-type="buyer">구매하기</button>
                            <button type="button" class="list-type-btn" data-type="seller">판매하기</button>
                        </div>
                    </div>
                </div>

                <div class="ai-list-panel is-active" data-type="buyer">
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
                                <?php if (!empty($buy_top3)): ?>
                                    <?php foreach ($buy_top3 as $slide_html): ?>
                                        <?php echo $slide_html; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="ai-empty-state">
                                        <p class="ai-empty-state_text"><?php echo htmlspecialchars($buy_top3_notice, ENT_QUOTES, 'UTF-8'); ?></p>
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
                                <?php if (!empty($buy_rest_first)): ?>
                                    <?php foreach ($buy_rest_first as $slide_html): ?>
                                        <?php echo $slide_html; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="ai-empty-state">
                                        <p class="ai-empty-state_text"><?php echo htmlspecialchars($buy_rest_notice, ENT_QUOTES, 'UTF-8'); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
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
        top3_notice: <?php echo json_encode($buy_top3_notice, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
        rest_notice: <?php echo json_encode($buy_rest_notice, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
    },
    seller: {
        top3_notice: <?php echo json_encode($sell_top3_notice, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
        rest_notice: <?php echo json_encode($sell_rest_notice, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
    }
};
</script>
<script src="<?php echo G5_URL; ?>/ko/js/company_list_ai.js?ver=<?php echo (int)$company_list_ai_js_ver; ?>"></script>

<?php include_once(G5_THEME_PATH . '/tail.php'); ?>
