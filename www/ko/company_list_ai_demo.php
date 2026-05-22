<?php
include_once("./_common.php");

$entry = isset($_GET['entry']) ? trim((string)$_GET['entry']) : '';
if ($entry !== 'matching_ai_demo') {
    alert('잘못된 접근입니다.', '/ko/matching_list.php');
}


$board['bo_10'] = "3";
$board['bo_9'] = "1";

$company_list_ai_demo_css_ver = @filemtime(G5_PATH . '/ko/css/company_list_ai_demo.css');
if (!$company_list_ai_demo_css_ver) $company_list_ai_demo_css_ver = G5_CSS_VER;

add_stylesheet('<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/variable/pretendardvariable-dynamic-subset.min.css">', 180);
add_stylesheet('<link rel="stylesheet" href="' . G5_URL . '/ko/css/design-tokens.css?ver=' . G5_CSS_VER . '">', 185);
add_stylesheet('<link rel="stylesheet" href="' . G5_URL . '/ko/css/header-core.css?ver=' . G5_CSS_VER . '">', 186);
add_stylesheet('<link rel="stylesheet" href="' . G5_URL . '/ko/css/header-search.css?ver=' . G5_CSS_VER . '">', 187);
add_stylesheet('<link rel="stylesheet" href="' . G5_URL . '/ko/css/badges.css?ver=' . G5_CSS_VER . '">', 190);
add_stylesheet('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">', 195);
add_stylesheet('<link rel="stylesheet" href="' . G5_URL . '/ko/css/company-card.css?ver=' . G5_CSS_VER . '">', 200);
add_stylesheet('<link rel="stylesheet" href="' . G5_URL . '/ko/css/section-header.css?ver=' . G5_CSS_VER . '">', 210);
add_stylesheet('<link rel="stylesheet" href="' . G5_URL . '/ko/css/ai-loading.css?ver=' . G5_CSS_VER . '">', 216);
add_stylesheet('<link rel="stylesheet" href="' . G5_URL . '/ko/css/company_list_ai_demo.css?ver=' . (int)$company_list_ai_demo_css_ver . '">', 220);

include_once(G5_THEME_PATH . '/head.php');

echo '<style>
#pageTop,
#sVisual { display: none !important; }
</style>';

function company_list_ai_cards()
{
    return [
        'buyer' => [
            [
                'kedcd' => '0008300204',
                'title' => '(주)한빛머티리얼즈',
                'score' => 97,
                'badges' => ['성장TOP15%', '재무안정TOP10%'],
                'tags' => ['금속소재', '철강·비철금속', '가공소재', '산업자재', 'B2B원자재'],
                'one_line' => '동일 업종 + 지역 접근성이 높아 즉시 협업 가능성이 큽니다.',
                'radar' => ['공급망 경로' => 95, '거래처 중첩도' => 90, '재무 건전성' => 88, '규모 적합도' => 91, '지역 접근성' => 94, '시장 거래력' => 85],
            ],
            [
                'kedcd' => '0004123098',
                'title' => '대성케미텍',
                'score' => 94,
                'badges' => ['벤처인증', '재무안정TOP5%'],
                'tags' => ['화학소재', '합성수지', '도료원료', '산업소재', '기초화학', '중간재'],
                'one_line' => '유사 거래 네트워크가 풍부해 초기 발주 테스트에 유리합니다.',
                'radar' => ['공급망 연결강도' => 88, '거래처 유사성' => 84, '거래 활성도' => 73, '재무 건전성' => 85, '지역 접근성' => 97, '규모 적합도' => 86],
            ],
            [
                'kedcd' => '0007123488',
                'title' => '(주)동서기계',
                'score' => 91,
                'badges' => ['성장TOP20%'],
                'tags' => ['기계설비', '산업장비', '정밀장치', '제조설비', '유지보수'],
                'one_line' => '납품 반응 속도와 규모 적합도가 높아 단기 거래 전환에 적합합니다.',
                'radar' => ['공급망 연결강도' => 86, '거래처 유사성' => 80, '거래 활성도' => 88, '재무 건전성' => 90, '지역 접근성' => 79, '규모 적합도' => 83],
            ],
            [
                'kedcd' => '0005532001',
                'title' => '(주)태영정밀',
                'score' => 88,
                'badges' => ['재무안정TOP5%'],
                'tags' => ['기계부품', '정밀가공'],
                'one_line' => '생산 라인 호환성이 높아 중장기 거래처 후보로 적합합니다.',
                'radar' => ['공급망 근접도' => 84, '거래처 유사성' => 79, '거래 활성도' => 82, '재무 안정성' => 90, '지역 접근성' => 76, '규모 적합도' => 83],
            ],
            [
                'kedcd' => '0009993007',
                'title' => '주식회사 유진로직스',
                'score' => 86,
                'badges' => ['벤처인증'],
                'tags' => ['물류', '유통'],
                'one_line' => '유통·물류 연결성이 좋아 공급 안정성 확보에 도움이 됩니다.',
                'radar' => ['공급망 근접도' => 80, '거래처 유사성' => 78, '거래 활성도' => 88, '재무 안정성' => 81, '지역 접근성' => 84, '규모 적합도' => 77],
            ],
            [
                'kedcd' => '0001112099',
                'title' => '(주)그린소재',
                'score' => 84,
                'badges' => ['성장TOP25%'],
                'tags' => ['친환경소재', '화학'],
                'one_line' => '성장성이 높고 거래처 유사성이 안정적으로 확보된 후보입니다.',
                'radar' => ['공급망 근접도' => 78, '거래처 유사성' => 81, '거래 활성도' => 80, '재무 안정성' => 79, '지역 접근성' => 74, '규모 적합도' => 83],
            ],
        ],
        'seller' => [
            [
                'kedcd' => '0006622001',
                'title' => '(주)넥스트유통',
                'score' => 96,
                'badges' => ['성장TOP10%', '벤처인증'],
                'tags' => ['유통', 'B2B플랫폼'],
                'one_line' => '매출 확장성이 높고 거래 활성도 지표가 우수한 판매 후보입니다.',
                'radar' => ['공급망 근접도' => 90, '거래처 유사성' => 93, '거래 활성도' => 96, '재무 안정성' => 84, '지역 접근성' => 89, '규모 적합도' => 88],
            ],
            [
                'kedcd' => '0004022103',
                'title' => '(주)오성테크',
                'score' => 93,
                'badges' => ['재무안정TOP10%'],
                'tags' => ['전자부품', '산업장비'],
                'one_line' => '유사 구매처가 많아 초기 판매 전환 확률이 높은 편입니다.',
                'radar' => ['공급망 근접도' => 87, '거래처 유사성' => 90, '거래 활성도' => 91, '재무 안정성' => 88, '지역 접근성' => 78, '규모 적합도' => 86],
            ],
            [
                'kedcd' => '0007332012',
                'title' => '(주)현대소싱',
                'score' => 91,
                'badges' => ['성장TOP20%'],
                'tags' => ['소재유통', '제조'],
                'one_line' => '판매 채널 확장에 유리한 네트워크를 보유한 후보입니다.',
                'radar' => ['공급망 근접도' => 85, '거래처 유사성' => 89, '거래 활성도' => 88, '재무 안정성' => 83, '지역 접근성' => 82, '규모 적합도' => 84],
            ],
            [
                'kedcd' => '0002123008',
                'title' => '(주)동아기공',
                'score' => 87,
                'badges' => ['재무안정TOP5%'],
                'tags' => ['기계장비', '정밀부품'],
                'one_line' => '구매력 지표가 안정적이라 반복 매출 기대가 가능합니다.',
                'radar' => ['공급망 근접도' => 81, '거래처 유사성' => 84, '거래 활성도' => 83, '재무 안정성' => 90, '지역 접근성' => 74, '규모 적합도' => 81],
            ],
            [
                'kedcd' => '0005532017',
                'title' => '주식회사 삼우화학',
                'score' => 85,
                'badges' => ['벤처인증'],
                'tags' => ['화학원료', '산업재'],
                'one_line' => '기존 고객군과 제품군이 맞아 교차판매 가능성이 높습니다.',
                'radar' => ['공급망 근접도' => 79, '거래처 유사성' => 82, '거래 활성도' => 86, '재무 안정성' => 78, '지역 접근성' => 80, '규모 적합도' => 79],
            ],
            [
                'kedcd' => '0009944019',
                'title' => '(주)바른패키지',
                'score' => 83,
                'badges' => ['성장TOP25%'],
                'tags' => ['포장솔루션', '소모성자재'],
                'one_line' => '중소 고객군 기반이 넓어 신규 거래 시작 허들이 낮습니다.',
                'radar' => ['공급망 근접도' => 77, '거래처 유사성' => 80, '거래 활성도' => 81, '재무 안정성' => 76, '지역 접근성' => 75, '규모 적합도' => 82],
            ],
        ],
    ];
}

function company_list_ai_render_badges($badges)
{
    if (!is_array($badges) || !$badges) {
        return '<span class="mCompanyCard_badge mCompanyCard_badge_placeholder">뱃지 준비중</span>';
    }

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

    $html = '<div class="swiper-slide">';
    if ($is_top3) $html .= company_list_ai_render_rank_medal($rank);
    $html .= '<div class="company-ai-link" data-kedcd="' . $kedcd . '" data-trade-type="' . $trade_q . '" data-score="' . $score . '" style="color:inherit;">';
    $html .= '<div class="mCompanyCard company-ai-card" style="position:relative;">';
    $html .= '<div class="mCompanyCard_content">';
    $html .= '<div class="mCompanyCard_ai_match"><span class="mCompanyCard_ai_match_label">AI 추천점수</span><span><span class="mCompanyCard_ai_match_value">' . $score . '</span><span class="mCompanyCard_ai_match_unit">%</span></span></div>';
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
    $html .= '</div>';
    $html .= '</div>';

    return $html;
}

$cards = company_list_ai_cards();
?>

<div id="listWrap" class="company-list-ai-page">
    <div class="ai-banner">
        <div class="ai-banner_card">
            <div class="ai-banner_top">
                <div class="ai-banner_icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M12 2L13.8 7.2L19.2 7.5L14.6 10.8L16.2 16L12 12.9L7.8 16L9.4 10.8L4.8 7.5L10.2 7.2Z"/></svg>
                </div>
                <span class="ai-banner_label">AI Recommendation</span>
            </div>
            <h2 class="ai-banner_title">실제 거래 데이터로<br>최적의 거래처를 찾아드립니다</h2>
            <p class="ai-banner_desc">6가지 정밀 지표로 검증합니다.</p>
            <div class="ai-banner_stats">
                <div class="ai-banner_stat">
                    <span class="ai-banner_stat_value">200만+</span>
                    <span class="ai-banner_stat_label">실거래 데이터</span>
                </div>
                <div class="ai-banner_stat">
                    <span class="ai-banner_stat_value">6개</span>
                    <span class="ai-banner_stat_label">검증 지표</span>
                </div>
                <div class="ai-banner_stat">
                    <span class="ai-banner_stat_value">100%</span>
                    <span class="ai-banner_stat_label">실거래 검증 데이터</span>
                </div>
            </div>
        </div>
    </div>

    <div class="list-body">
        <div class="inner">
            <div class="list-top">
                <div class="list-type-wrap">
                    <div class="list-type-switch" role="group" aria-label="구매하기/판매하기">
                        <span class="list-type-slider" aria-hidden="true"></span>
                        <button type="button" class="list-type-btn on" data-type="buyer">구매하기</button>
                        <button type="button" class="list-type-btn" data-type="seller">판매하기</button>
                    </div>
                    <p class="list-count"><strong id="aiCompanyCount"><?php echo count($cards['buyer']); ?></strong>개의 추천 기업</p>
                </div>
            </div>

            <?php foreach (['buyer', 'seller'] as $type):
                $panel_cards = isset($cards[$type]) && is_array($cards[$type]) ? $cards[$type] : [];
                $top3 = array_slice($panel_cards, 0, 3);
                $rest = array_slice($panel_cards, 3);
                $is_active = ($type === 'buyer');
                $trade_type = ($type === 'seller') ? 2 : 1;
                $title_type = ($type === 'seller') ? '판매처' : '구매처';
            ?>
            <div class="ai-list-panel<?php echo $is_active ? ' is-active' : ''; ?>" data-type="<?php echo $type; ?>" data-total="<?php echo count($panel_cards); ?>">
                <section class="ai-top3-section-top">
                    <div class="mHookSection_header" style="margin-bottom:4px;">
                        <div class="mHookSection_label mHookSection_label--ai-top3">
                            <span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></span>
                            <span>TOP 3</span>
                        </div>
                        <div class="mHookSection_titleRow">
                            <h2 class="mHookSection_title">AI 추천 <?php echo $title_type; ?> TOP 3</h2>
                        </div>
                        <p class="mHookSection_subtitle">실거래 데이터 기반으로 선정된 최우선 추천 거래처입니다.</p>
                    </div>
                    <div class="ai-top3">
                        <div class="ai-top3-swiper swiper">
                            <div class="swiper-wrapper">
                            <?php foreach ($top3 as $idx => $card): ?>
                                <?php echo company_list_ai_render_card($card, $trade_type, $idx + 1); ?>
                            <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="ai-rest-section">
                    <div class="mHookSection_header ai-rest_header">
                        <div class="mHookSection_label mHookSection_label--ai-rest"><span>ALL</span></div>
                        <div class="mHookSection_titleRow">
                            <h2 class="mHookSection_title">추천 거래처 전체 목록</h2>
                        </div>
                        <p class="mHookSection_subtitle">추가 후보를 비교해 최적의 거래처를 선택해보세요.</p>
                    </div>
                    <div class="ai-rest-swiper swiper">
                        <div class="swiper-wrapper">
                            <?php foreach ($rest as $card): ?>
                                <?php echo company_list_ai_render_card($card, $trade_type, 0); ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            </div>
            <?php endforeach; ?>

            <section class="ai-report-section">
                <div class="mHookSection_header ai-rest_header ai-report_header">
                    <div class="mHookSection_label mHookSection_label--ai-rest">
                        <span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg></span>
                        <span>REPORT</span>
                    </div>
                    <div class="mHookSection_titleRow">
                        <h2 class="mHookSection_title">AI 기업 분석 보고서</h2>
                    </div>
                    <p class="mHookSection_subtitle">추천 거래처와 귀사 간의 시너지, 적합도, 협력 전략을 AI가 심층 분석합니다.</p>
                </div>

                <div class="ai-two-col">
                    <div class="card section-card ai-fit-card">
                        <div class="fit-header">
                            <div class="fit-header-top">
                                <div class="fit-header-companies"><strong>A 기업</strong> <span class="fit-header-arrow">⇄</span> <strong>B 기업</strong></div>
                                <div class="fit-header-score"><span class="fit-header-score-num">78</span><span class="fit-header-score-unit">%</span></div>
                            </div>
                            <div class="fit-header-label">AI 추천점수 예측</div>
                        </div>

                        <div class="detail-radar">
                            <div class="detail-radar_row">
                                <div class="detail-radar_chart">
                                    <svg viewBox="0 0 300 270" class="detail-radar-svg">
                                        <polygon points="150,117.5 163,122.5 163,137.5 150,145 137,137.5 137,122.5" fill="none" stroke="#e5e7eb" stroke-width="0.8"/>
                                        <polygon points="150,100 176,115 176,145 150,160 124,145 124,115" fill="none" stroke="#e5e7eb" stroke-width="0.8"/>
                                        <polygon points="150,82.5 188.9,107.5 188.9,152.5 150,175 111.1,152.5 111.1,107.5" fill="none" stroke="#e5e7eb" stroke-width="0.8"/>
                                        <polygon points="150,70 201.96,100 201.96,160 150,190 98.04,160 98.04,100" fill="none" stroke="#e5e7eb" stroke-width="0.8"/>

                                        <line x1="150" y1="130" x2="150" y2="70" stroke="#e5e7eb" stroke-width="0.6"/>
                                        <line x1="150" y1="130" x2="201.96" y2="100" stroke="#e5e7eb" stroke-width="0.6"/>
                                        <line x1="150" y1="130" x2="201.96" y2="160" stroke="#e5e7eb" stroke-width="0.6"/>
                                        <line x1="150" y1="130" x2="150" y2="190" stroke="#e5e7eb" stroke-width="0.6"/>
                                        <line x1="150" y1="130" x2="98.04" y2="160" stroke="#e5e7eb" stroke-width="0.6"/>
                                        <line x1="150" y1="130" x2="98.04" y2="100" stroke="#e5e7eb" stroke-width="0.6"/>

                                        <polygon points="150,86.8 194.2,104.5 196.8,157 150,185.2 115.7,149.8 118.3,111.7" fill="rgba(109,40,217,0.12)" stroke="rgba(109,40,217,0.6)" stroke-width="1.8"/>
                                        <circle cx="150" cy="86.8" r="3.5" fill="rgba(109,40,217,0.8)" class="radar-dot" data-axis="0"/>
                                        <circle cx="194.2" cy="104.5" r="3.5" fill="rgba(109,40,217,0.8)" class="radar-dot" data-axis="1"/>
                                        <circle cx="196.8" cy="157" r="3.5" fill="rgba(109,40,217,0.8)" class="radar-dot" data-axis="2"/>
                                        <circle cx="150" cy="185.2" r="3.5" fill="rgba(109,40,217,0.8)" class="radar-dot" data-axis="3"/>
                                        <circle cx="115.7" cy="149.8" r="3.5" fill="rgba(109,40,217,0.8)" class="radar-dot" data-axis="4"/>
                                        <circle cx="118.3" cy="111.7" r="3.5" fill="rgba(109,40,217,0.8)" class="radar-dot" data-axis="5"/>

                                        <text x="150" y="43" text-anchor="middle" font-size="13" font-weight="500" fill="#555" class="radar-label" data-axis="0">공급망 연결강도</text>
                                        <text x="229.7" y="89" text-anchor="start" font-size="13" font-weight="500" fill="#555" class="radar-label" data-axis="1">거래처 유사성</text>
                                        <text x="229.7" y="181" text-anchor="start" font-size="13" font-weight="500" fill="#555" class="radar-label" data-axis="2">거래 활성도</text>
                                        <text x="150" y="227" text-anchor="middle" font-size="13" font-weight="500" fill="#555" class="radar-label" data-axis="3">재무 건전성</text>
                                        <text x="70.3" y="181" text-anchor="end" font-size="13" font-weight="500" fill="#555" class="radar-label" data-axis="4">지역 접근성</text>
                                        <text x="70.3" y="89" text-anchor="end" font-size="13" font-weight="500" fill="#555" class="radar-label" data-axis="5">규모 적합도</text>
                                    </svg>
                                </div>
                                <div class="detail-radar_legend">
                                    <div class="detail-radar_legend_item" data-axis="0">
                                        <div class="detail-radar_legend_text"><strong>공급망 경로</strong><span>기존 거래망으로 접근 가능</span></div>
                                        <span class="detail-radar_legend_score">72</span>
                                    </div>
                                    <div class="detail-radar_legend_item" data-axis="1">
                                        <div class="detail-radar_legend_text"><strong>거래처 중첩도</strong><span>공통 거래처 연결 유사도</span></div>
                                        <span class="detail-radar_legend_score">85</span>
                                    </div>
                                    <div class="detail-radar_legend_item" data-axis="2">
                                        <div class="detail-radar_legend_text"><strong>재무 건전성</strong><span>신용등급 · 부채 안정성</span></div>
                                        <span class="detail-radar_legend_score">90</span>
                                    </div>
                                    <div class="detail-radar_legend_item" data-axis="3">
                                        <div class="detail-radar_legend_text"><strong>시장 거래력</strong><span>업계 거래 규모 · 인지도</span></div>
                                        <span class="detail-radar_legend_score">92</span>
                                    </div>
                                    <div class="detail-radar_legend_item" data-axis="4">
                                        <div class="detail-radar_legend_text"><strong>지역 접근성</strong><span>물류 · 방문 접근 편의</span></div>
                                        <span class="detail-radar_legend_score">66</span>
                                    </div>
                                    <div class="detail-radar_legend_item" data-axis="5">
                                        <div class="detail-radar_legend_text"><strong>규모 적합도</strong><span>매출 · 자산 규모 유사성</span></div>
                                        <span class="detail-radar_legend_score">61</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="ai-right-wrapper">
                        <div id="aiRightCard" class="card section-card ai-analyze-card">
                            <div class="ai-analyze-head" style="position:relative;z-index:3;">
                                <h3>
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 3l1.5 4.5L18 9l-4.5 1.5L12 15l-1.5-4.5L6 9l4.5-1.5L12 3z"/></svg>
                                    AI 기업 분석 보고서
                                </h3>
                            </div>
                            <div class="ai-blur-bg" id="aiBlurBg" aria-hidden="true">
                                <div class="ai-blur-content ai-analysis-result">
                                    <h2>A 기업 ↔ B 기업 협력 방안</h2>
                                    <h3>1. 거래 밸류에이션 요약 (ML Score 기반)</h3>
                                    <p>양사 결합은 <strong>경기 성남 권역 내 실리콘 에멀젼-화장품 제조 원료 직접 연계를 통한 K-뷰티 시장 공동 성장 파트너십</strong>으로 정의됩니다.</p>
                                    <table><thead><tr><th>핵심 지표</th><th>점수</th><th>SHAP 분석 기반 기회 요인</th></tr></thead><tbody>
                                        <tr><td><strong>재무 건전성</strong></td><td><strong>92.0</strong></td><td>최적 협업 체급으로 의사결정 속도·계약 유연성 우수</td></tr>
                                        <tr><td><strong>거래처 유사성</strong></td><td><strong>85.0</strong></td><td>ODM·OEM 브랜드 고객망이 다중 적용처로 연결</td></tr>
                                        <tr><td><strong>공급망 연결강도</strong></td><td><strong>78.0</strong></td><td>실리콘 에멀젼이 화장품 포뮬레이션 핵심 원료로 직접 연결</td></tr>
                                    </tbody></table>
                                    <hr>
                                    <h3>2. 상세 분석 및 시너지 도출</h3>
                                    <h4>A. 사업적 시너지: "K-뷰티 성장 수혜를 원료-제조사 파트너십으로 공유"</h4>
                                    <ul>
                                        <li><strong>화장품 전용 실리콘 에멀젼 공급:</strong> 스킨케어·메이크업 포뮬레이션에 적용하여 사용감·발림성·피부 보호 성능 향상.</li>
                                        <li><strong>맞춤 원료 공동 개발:</strong> 소량 시험 생산에 강점 보유 — 신규 화장품 라인 최적화 레시피 공동 개발.</li>
                                    </ul>
                                    <hr>
                                    <h3>3. 실무형 협력 전략 (Action Plan)</h3>
                                    <h4>전략 1: 화장품용 실리콘 에멀젼 인증 취득</h4>
                                    <ul><li><strong>내용:</strong> 식약처·EU CPNP 적합 인증 취득, 뷰티팩토리가 인증 과정에서 수요처 역할 및 데이터 지원.</li></ul>
                                    <h4>전략 2: 전용 실리콘 에멀젼 라인 공동 개발</h4>
                                    <ul><li><strong>내용:</strong> 주력 제품군에 최적화된 전용 레시피 공동 개발, NDA 기반 독점 공급 계약 체결.</li></ul>
                                    <blockquote>"본 거래은 이가이엔씨의 실리콘 소재 기술력과 뷰티팩토리의 K-뷰티 제조 네트워크를 결합하여 국내 화장품 원료 시장의 신규 성장 기회를 양사가 공유하는 전략적 파트너십입니다."</blockquote>
                                    <p class="ai-analysis-note">※ 본 내용은 샘플 미리보기입니다. 분석하기를 클릭하면 AI가 실제 데이터를 기반으로 맞춤 전략 보고서를 생성합니다.</p>
                                </div>
                            </div>
                            <div class="ai-analysis-body" id="aiAnalysisBody" style="display:none;"></div>
                        </div>
                    </div>

                </div>
            </section>
        </div>
    </div>

    <div class="ai-banner ai-banner-bottom">
        <div class="rec-bottom-banner">
            <div class="rec-bottom-banner_icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M12 2L13.8 7.2L19.2 7.5L14.6 10.8L16.2 16L12 12.9L7.8 16L9.4 10.8L4.8 7.5L10.2 7.2Z"/></svg>
            </div>
            <div class="rec-bottom-banner_body">
                <p class="rec-bottom-banner_title">실제 거래 데이터로 검증된 거래처만 추천합니다</p>
                <p class="rec-bottom-banner_desc">100만 건의 실거래 흐름을 분석한 AI가<br>우리와 유사한 기업들이 선택한 거래처를 기반으로 추천합니다.<br>지금 바로 확인해보세요</p>
            </div>
        </div>
    </div>
</div>

<?php include_once(G5_THEME_PATH . '/tail.php'); ?>
