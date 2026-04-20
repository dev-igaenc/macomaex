<?php
// ============================================================
// matching_list.php
// ============================================================

include_once("./_common.php");

// ----------------------------------------------------------
// 1. 게시판 설정
// ----------------------------------------------------------
$board['bo_10'] = "3"; // 1뎁스
$board['bo_9']  = "1"; // 2뎁스
// $board['bo_8']       = "1"; // 3뎁스
// $board['bo_7']       = "1"; // 4뎁스
// $board['bo_5']       = "";  // 1뎁스 제목
// $board['bo_6']       = "";  // 1뎁스 설명
// $board['bo_subject'] = "";  // 2뎁스 제목
// $board['bo_4']       = $config['cf_title']."의 혜택을 확인하세요."; // 2뎁스 설명

// ----------------------------------------------------------
// 2. CSS / 헤더
// ----------------------------------------------------------
$matching_list_css_ver = @filemtime(G5_PATH.'/ko/css/matching_list.css');
$matching_list_js_ver  = @filemtime(G5_PATH.'/ko/js/matching_list.js');
if (!$matching_list_css_ver) $matching_list_css_ver = G5_CSS_VER;
if (!$matching_list_js_ver)  $matching_list_js_ver  = G5_JS_VER;

add_stylesheet('<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/variable/pretendardvariable-dynamic-subset.min.css">', 180);
add_stylesheet('<link rel="stylesheet" href="'.G5_URL.'/ko/css/design-tokens.css?ver='.G5_CSS_VER.'">', 185);
add_stylesheet('<link rel="stylesheet" href="'.G5_URL.'/ko/css/badges.css?ver='.G5_CSS_VER.'">', 210);
add_stylesheet('<link rel="stylesheet" href="'.G5_URL.'/ko/css/company-card.css?ver='.G5_CSS_VER.'">', 211);
add_stylesheet('<link rel="stylesheet" href="'.G5_URL.'/ko/css/favorite.css?ver='.G5_CSS_VER.'">', 212);
add_stylesheet('<link rel="stylesheet" href="'.G5_URL.'/ko/css/section-header.css?ver='.G5_CSS_VER.'">', 213);
add_stylesheet('<link rel="stylesheet" href="'.G5_URL.'/ko/css/list-sidebar.css?ver='.G5_CSS_VER.'">', 214);
add_stylesheet('<link rel="stylesheet" href="'.G5_URL.'/ko/css/matching_list.css?ver='.$matching_list_css_ver.'">', 220);
include_once(G5_THEME_PATH.'/head.php');

// ----------------------------------------------------------
// 3. 카테고리 정의
// ----------------------------------------------------------
$list_categories = [
    ['slug' => 'metal', 'label' => '금속·소재', 'large_category_cd' => 1, 'sub' => [
        ['slug' => 'metal_steel',      'label' => '1차 철강·비철',   'code' => 'C241'],
        ['slug' => 'metal_nonferrous', 'label' => '1차 비철금속',   'code' => 'C242'],
        ['slug' => 'metal_casting',    'label' => '금속 주조',      'code' => 'C243'],
        ['slug' => 'metal_structural', 'label' => '구조용 금속',    'code' => 'C251'],
        ['slug' => 'metal_machining',  'label' => '금속 가공·부품',  'code' => 'C259'],
    ]],
    ['slug' => 'chemical', 'label' => '화학·케미컬·원료', 'large_category_cd' => 2, 'sub' => [
        ['slug' => 'chem_basic',       'label' => '기초 화학원료',        'code' => 'C201'],
        ['slug' => 'chem_plastic_raw', 'label' => '합성수지·플라스틱 원료', 'code' => 'C202'],
        ['slug' => 'chem_agri',        'label' => '농업용 화학제품',      'code' => 'C203'],
        ['slug' => 'chem_indus',       'label' => '산업용 화학제품',      'code' => 'C204'],
        ['slug' => 'chem_fiber',       'label' => '화학섬유',           'code' => 'C205'],
        ['slug' => 'chem_rubber',      'label' => '고무제품',           'code' => 'C221'],
        ['slug' => 'chem_plastic',     'label' => '플라스틱 제품',       'code' => 'C222'],
    ]],
    ['slug' => 'electronics', 'label' => '전자·전기·반도체', 'large_category_cd' => 3, 'sub' => [
        ['slug' => 'elec_semi',      'label' => '반도체',         'code' => 'C261'],
        ['slug' => 'elec_parts',     'label' => '전자부품',       'code' => 'C262'],
        ['slug' => 'elec_it',        'label' => '컴퓨터·IT기기',   'code' => 'C263'],
        ['slug' => 'elec_comm',      'label' => '통신·방송장비',   'code' => 'C264'],
        ['slug' => 'elec_av',        'label' => '영상·음향기기',   'code' => 'C265'],
        ['slug' => 'elec_measure',   'label' => '정밀측정·제어기기', 'code' => 'C272'],
        ['slug' => 'elec_imaging',   'label' => '광학·이미징기기',  'code' => 'C273'],
    ]],
    ['slug' => 'machinery', 'label' => '기계·설비·산업장비', 'large_category_cd' => 4, 'sub' => [
        ['slug' => 'mach_control',   'label' => '산업용 전동·제어장치', 'code' => 'C281'],
        ['slug' => 'mach_battery',   'label' => '배터리·축전지',      'code' => 'C282'],
        ['slug' => 'mach_cable',     'label' => '전선·케이블',        'code' => 'C283'],
        ['slug' => 'mach_light',     'label' => '조명·광원장치',      'code' => 'C284'],
        ['slug' => 'mach_appliance', 'label' => '가전·생활기기',      'code' => 'C285'],
        ['slug' => 'mach_electric',  'label' => '산업용 전기장비',     'code' => 'C289'],
        ['slug' => 'mach_general',   'label' => '일반 산업기계',      'code' => 'C291'],
        ['slug' => 'mach_special',   'label' => '특수 산업기계',      'code' => 'C292'],
    ]],
    ['slug' => 'auto', 'label' => '자동차·모빌리티', 'large_category_cd' => 5, 'sub' => [
        ['slug' => 'auto_finish',    'label' => '완성차·엔진',      'code' => 'C301'],
        ['slug' => 'auto_body',      'label' => '차체·특장차',      'code' => 'C302'],
        ['slug' => 'auto_parts',     'label' => '자동차 부품',      'code' => 'C303'],
        ['slug' => 'auto_reman',     'label' => '재생·재제조 부품',  'code' => 'C304'],
    ]],
    ['slug' => 'food', 'label' => '식자재·식품 B2B', 'large_category_cd' => 6, 'sub' => [
        ['slug' => 'food_meat',      'label' => '육류 가공',      'code' => 'C101'],
        ['slug' => 'food_sea',       'label' => '수산물 가공',    'code' => 'C102'],
        ['slug' => 'food_agri',      'label' => '농산물 가공',    'code' => 'C103'],
        ['slug' => 'food_oil',       'label' => '유지·식용유',    'code' => 'C104'],
        ['slug' => 'food_dairy',     'label' => '낙농·빙과',      'code' => 'C105'],
        ['slug' => 'food_grain',     'label' => '곡물·전분',      'code' => 'C106'],
        ['slug' => 'food_processed', 'label' => '가공식품',       'code' => 'C107'],
        ['slug' => 'food_feed',      'label' => '사료·동물식품',   'code' => 'C108'],
    ]],
    ['slug' => 'medical', 'label' => '의료·헬스케어', 'large_category_cd' => 7, 'sub' => [
        ['slug' => 'med_material',   'label' => '의약 원료',     'code' => 'C211'],
        ['slug' => 'med_pharma',     'label' => '의약품',       'code' => 'C212'],
        ['slug' => 'med_consumable', 'label' => '의료 소모품',    'code' => 'C213'],
        ['slug' => 'med_device',     'label' => '의료기기',      'code' => 'C271'],
    ]],
    ['slug' => 'packaging', 'label' => '포장재·소모성 자재', 'large_category_cd' => 8, 'sub' => [
        ['slug' => 'pack_pulp',      'label' => '펄프·종이 원지',  'code' => 'C171'],
        ['slug' => 'pack_box',       'label' => '골판지·포장재',   'code' => 'C172'],
        ['slug' => 'pack_paper',     'label' => '종이 가공제품',   'code' => 'C179'],
    ]],
];

// ----------------------------------------------------------
// 4. 요청 파라미터 처리
// ----------------------------------------------------------
$current_cat  = isset($_GET['cat'])     ? $_GET['cat']     : 'all';
$current_type = (isset($_GET['type']) && $_GET['type'] === 'seller') ? 'seller' : 'buyer';
$current_sort = isset($_GET['sort']) ? $_GET['sort'] : 'rank';
if (!in_array($current_sort, ['rank', 'grow', 'profit', 'stable'], true)) $current_sort = 'rank';
$mq = '';
if (isset($_GET['section'])) $mq .= 'section=' . urlencode($_GET['section']) . '&';
$mq .= 'type=' . urlencode($current_type) . '&';
$mq .= 'sort=' . urlencode($current_sort) . '&';

// 사업자번호 처리
$input_comnum = !empty($_POST['comnum']) ? $_POST['comnum'] : (!empty($_GET['comnum']) ? $_GET['comnum'] : '');
$input_comnum = preg_replace('/[^0-9]/', '', $input_comnum);
$has_comnum   = (strlen($input_comnum) === 10);
$bzno         = $input_comnum;

// ----------------------------------------------------------
// 5. 멤버십 정보 조회
// ----------------------------------------------------------
$today  = date("Y-m-d");
$msinfo = [];

if ($member['mb_id']) {
    $msinfo_row = getUserMembership();
    // 멤버십 기본값 처리
    if (is_array($msinfo_row) && isset($msinfo_row[0]) && is_array($msinfo_row[0]) && !empty($msinfo_row[0])) {
        $msinfo = $msinfo_row[0];
    } else {
        $free_membership_no = (is_array($msinfo_row) && isset($msinfo_row[1]['no'])) ? $msinfo_row[1]['no'] : 1;
        $msinfo = ['membership_parent_no' => $free_membership_no];
    }
}

$free_limit   = 9;
// 더보기 권한: 비회원은 기존 제한 유지, 로그인 회원은 멤버십/결제 여부와 무관하게 허용
$can_load_more = !empty($member['mb_id']);
$page_size = $free_limit;
$offset_req = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
if ($offset_req < 0) $offset_req = 0;
$show_count_req = $page_size;

// ----------------------------------------------------------
// 7. 카드 데이터 빌드
// ----------------------------------------------------------

// 점수 표시값 만들기
function calc_score_avg($val) {
    return ($val !== null && $val !== '') ? number_format($val, 1) : '-';
}

// 뱃지 퍼센트 글자 정리 (10.00 -> 10)
function format_badge_percent($value) {
    if ($value === null || $value === '') return '';
    $str = trim((string)$value);
    if ($str === '') return '';
    if (!is_numeric($str)) return $str;

    $num = (float)$str;
    if ($num == (int)$num) return (string)((int)$num);
    return rtrim(rtrim(number_format($num, 2, '.', ''), '0'), '.');
}

// 시안대로 회사명 마스킹
function mask_company_name_like_mock($name) {
    $source = (string)$name;
    if (preg_match('/^(\(주\)|\(유\)|\(사\))?(.*)$/u', $source, $m)) {
        $prefix = isset($m[1]) ? $m[1] : '';
        $body   = isset($m[2]) ? $m[2] : '';
    } else {
        $prefix = '';
        $body   = $source;
    }

    $keep_len = ($prefix !== '') ? 1 : 2;
    $visible  = function_exists('mb_substr')
        ? mb_substr($body, 0, $keep_len, 'UTF-8')
        : substr($body, 0, $keep_len);

    return $prefix . $visible . '****';
}

// 레이더 점 좌표 계산
function radar_polar_point($cx, $cy, $r, $index, $num_axis) {
    $angle = (M_PI * 2 * $index / $num_axis) - (M_PI / 2);
    return [
        'x' => $cx + $r * cos($angle),
        'y' => $cy + $r * sin($angle),
    ];
}

// 5축 레이더 SVG 만들기
function build_radar_svg_5($scores) {
    $labels = ['성장성', '수익성', '재무안정', '업력', '고용규모'];
    $num_axis = 5;
    $cx = 105;
    $cy = 100;
    $max_r = 48;
    $label_r = 68;
    $grid_levels = [0.2, 0.4, 0.6, 0.8, 1.0];

    $safe_scores = [];
    for ($i = 0; $i < $num_axis; $i++) {
        $safe_scores[] = isset($scores[$i]) && is_numeric($scores[$i]) ? (float)$scores[$i] : 0.0;
    }

    $svg = '';

    foreach ($grid_levels as $lv) {
        $pts = [];
        for ($i = 0; $i < $num_axis; $i++) {
            $p = radar_polar_point($cx, $cy, $max_r * $lv, $i, $num_axis);
            $pts[] = round($p['x'], 2) . ',' . round($p['y'], 2);
        }
        $svg .= '<polygon points="' . implode(' ', $pts) . '" class="radar-grid"/>';
    }

    for ($i = 0; $i < $num_axis; $i++) {
        $p = radar_polar_point($cx, $cy, $max_r, $i, $num_axis);
        $svg .= '<line x1="' . $cx . '" y1="' . $cy . '" x2="' . round($p['x'], 2) . '" y2="' . round($p['y'], 2) . '" stroke="#E8EBF0" stroke-width="0.5"/>';
    }

    $shape_points = [];
    for ($i = 0; $i < $num_axis; $i++) {
        $p = radar_polar_point($cx, $cy, $max_r * ($safe_scores[$i] / 5), $i, $num_axis);
        $shape_points[] = round($p['x'], 2) . ',' . round($p['y'], 2);
    }
    $svg .= '<polygon points="' . implode(' ', $shape_points) . '" class="radar-shape"/>';

    for ($i = 0; $i < $num_axis; $i++) {
        $p = radar_polar_point($cx, $cy, $max_r * ($safe_scores[$i] / 5), $i, $num_axis);
        $svg .= '<circle cx="' . round($p['x'], 2) . '" cy="' . round($p['y'], 2) . '" class="radar-dot"/>';
    }

    for ($i = 0; $i < $num_axis; $i++) {
        $p = radar_polar_point($cx, $cy, $label_r, $i, $num_axis);
        $x = $p['x'];
        $y = $p['y'];
        $anchor = 'middle';
        $dx = 0;
        if ($x < $cx - 5) {
            $anchor = 'end';
            $dx = -2;
        } elseif ($x > $cx + 5) {
            $anchor = 'start';
            $dx = 2;
        }

        $dy = 0;
        if ($y < $cy - 10) $dy = -3;
        elseif ($y > $cy + 10) $dy = 8;
        else $dy = 3;

        $svg .= '<text x="' . round($x + $dx, 2) . '" y="' . round($y + $dy, 2) . '" text-anchor="' . $anchor . '" class="radar-label">' . htmlspecialchars($labels[$i]) . '</text>';
    }

    return $svg;
}

// 평점을 별 모양으로 변환
function render_stars_html($score) {
    $score = isset($score) && is_numeric($score) ? (float)$score : 0.0;
    $full = (int)floor($score);
    $part = $score - $full;
    $star = '<svg viewBox="0 0 24 24" width="14" height="14" fill="#ffc107"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
    return str_repeat($star, $full) . (($part >= 0.3) ? $star : '');
}

// 품목 문자열을 태그 배열로 변경
function parse_pd_nm($pd_nm) {
    if (empty($pd_nm)) return [];
    $tags = explode(',', $pd_nm);
    $result = [];
    foreach ($tags as $t) {
        $trimmed = trim($t);
        if ($trimmed) {
            $result[] = str_replace(' 외', '', $trimmed);
        }
    }
    return $result;
}

// 현재 카테고리의 구매 코드 찾기
function get_target_codes_by_category($list_categories, $current_cat) {
    if (empty($current_cat) || $current_cat === 'all') return [];
    $target_codes = [];
    foreach ($list_categories as $lg) {
        if ($lg['slug'] === $current_cat) {
            foreach ($lg['sub'] as $sub) {
                if (!empty($sub['code'])) $target_codes[] = $sub['code'];
            }
            break;
        }
        foreach ($lg['sub'] as $sub) {
            if ($sub['slug'] === $current_cat) {
                if (!empty($sub['code'])) $target_codes[] = $sub['code'];
                break 2;
            }
        }
    }
    return $target_codes;
}

// company 조회 WHERE 만들기
function build_company_where_clause($target_codes, $restrict_cg_prefix = false) {
    $where_clause = "WHERE A.KEDCD != '' AND A.ENP_NM != ''";
    if ($restrict_cg_prefix) {
        $where_clause .= " AND (A.BZC_CD LIKE 'C%' OR A.BZC_CD LIKE 'G%')";
    }
    if (!empty($target_codes)) {
        $code_conds = [];
        foreach ($target_codes as $tc) {
            $code_conds[] = "A.BZC_CD LIKE '" . sql_real_escape_string($tc) . "%'";
        }
        $where_clause .= " AND (" . implode(" OR ", $code_conds) . ")";
    }
    return $where_clause;
}

// 정렬 기준에 맞는 ORDER BY 만들기
function build_company_order_clause($sort_key) {
    switch ($sort_key) {
        case 'grow':
            return "
                CASE WHEN S.growth_score_5 IS NULL OR S.growth_score_5 = '' THEN 1 ELSE 0 END ASC,
                CAST(S.growth_score_5 AS DECIMAL(10,4)) DESC,
                CASE WHEN S.final_total_score_5 IS NULL OR S.final_total_score_5 = '' THEN 1 ELSE 0 END ASC,
                CAST(S.final_total_score_5 AS DECIMAL(10,4)) DESC,
                A.ENP_NM ASC
            ";
        case 'profit':
            return "
                CASE WHEN S.profit_score_5 IS NULL OR S.profit_score_5 = '' THEN 1 ELSE 0 END ASC,
                CAST(S.profit_score_5 AS DECIMAL(10,4)) DESC,
                CASE WHEN S.final_total_score_5 IS NULL OR S.final_total_score_5 = '' THEN 1 ELSE 0 END ASC,
                CAST(S.final_total_score_5 AS DECIMAL(10,4)) DESC,
                A.ENP_NM ASC
            ";
        case 'stable':
            return "
                CASE WHEN S.stability_score_5 IS NULL OR S.stability_score_5 = '' THEN 1 ELSE 0 END ASC,
                CAST(S.stability_score_5 AS DECIMAL(10,4)) DESC,
                CASE WHEN S.final_total_score_5 IS NULL OR S.final_total_score_5 = '' THEN 1 ELSE 0 END ASC,
                CAST(S.final_total_score_5 AS DECIMAL(10,4)) DESC,
                A.ENP_NM ASC
            ";
        case 'rank':
        default:
            return "
                CASE WHEN S.final_total_score_5 IS NULL OR S.final_total_score_5 = '' THEN 1 ELSE 0 END ASC,
                CAST(S.final_total_score_5 AS DECIMAL(10,4)) DESC,
                A.ENP_NM ASC
            ";
    }
}

// 현재 카테고리의 판매 코드 찾기
function get_seller_codes_by_category($list_categories, $current_cat, $seller_category_code_map) {
    // 전체면 모든 판매 코드 사용
    if (empty($current_cat) || $current_cat === 'all') {
        $all = [];
        foreach ($seller_category_code_map as $codes) {
            $all = array_merge($all, $codes);
        }
        return array_values(array_unique($all));
    }

    // 소분류 직접 선택
    if (isset($seller_category_code_map[$current_cat])) {
        return array_values(array_unique($seller_category_code_map[$current_cat]));
    }

    // 대분류면 하위 코드 합치기
    foreach ($list_categories as $lg) {
        if ($lg['slug'] === $current_cat) {
            $merged = [];
            foreach ($lg['sub'] as $sub) {
                $sub_slug = $sub['slug'] ?? '';
                if ($sub_slug && isset($seller_category_code_map[$sub_slug])) {
                    $merged = array_merge($merged, $seller_category_code_map[$sub_slug]);
                }
            }
            return array_values(array_unique($merged));
        }
    }

    return [];
}

// buyer/seller에 맞는 WHERE 결정
function resolve_where_clause_for_general_list($query_type, $current_cat, $list_categories, $seller_category_code_map) {
    if ($query_type === 'seller') {
        $seller_codes = get_seller_codes_by_category($list_categories, $current_cat, $seller_category_code_map);
        if (empty($seller_codes)) return "WHERE 1=0";
        return build_company_where_clause($seller_codes, false);
    }

    $buyer_codes = get_target_codes_by_category($list_categories, $current_cat);
    return build_company_where_clause($buyer_codes, false);
}

// 일반 리스트 카드와 총 개수 조회
function fetch_general_cards_by_where($where_clause, $query_type, $show_count_req, $sort_key = 'rank', $offset = 0) {
    $cards = [];

    $count_row = sql_fetch("
        SELECT COUNT(*) AS cnt
        FROM company A
        {$where_clause}
    ");
    $cards_total_count = (int)($count_row['cnt'] ?? 0);

    $order_clause = build_company_order_clause($sort_key);

    $sql_list = "
        SELECT A.KEDCD, A.ENP_NM, A.LOC_ADDRA, A.BZC_CD, A.PD_NM, A.VENP_YN, B.k_code_name,
            S.growth_score_5, S.profit_score_5, S.stability_score_5, S.final_total_score_5,
            S.badge_growth, S.badge_stability, S.badge_venture
        FROM   company A
        LEFT JOIN kssc B ON A.BZC_CD = B.k_code
        LEFT JOIN company_score_final_dedup S ON A.KEDCD = S.KEDCD
        {$where_clause}
        ORDER BY {$order_clause}
        LIMIT " . (int)$offset . ", " . (int)$show_count_req . "
    ";

    $res_list = sql_query($sql_list);
    while ($row = sql_fetch_array($res_list)) {
        $cards[] = [
            'type'    => $query_type,
            'title'   => $row['ENP_NM'],
            'tags'    => parse_pd_nm($row['PD_NM']),
            'score'   => calc_score_avg($row['final_total_score_5']),
            'grow'    => $row['growth_score_5'],
            'profit'  => $row['profit_score_5'],
            'stable'  => $row['stability_score_5'],
            'company' => $row['ENP_NM'],
            'kedcd'   => $row['KEDCD'],
            'venp_yn' => $row['VENP_YN'],
            'badge_growth' => $row['badge_growth'],
            'badge_stability' => $row['badge_stability'],
            'badge_venture' => $row['badge_venture'],
        ];
    }

    return [
        'cards' => $cards,
        'total_count' => $cards_total_count,
    ];
}

$seller_category_code_map = include __DIR__ . "/matching_seller_category_codes.php";

$cards = [];
$cards_total_count = 0;

if (!$has_comnum) {
    // 7-1. 사업자번호 미입력: 탭/카테고리 기준 기업 조회
    $query_type = ($current_type === 'seller') ? 'seller' : 'buyer';
    $where_clause = resolve_where_clause_for_general_list($query_type, $current_cat, $list_categories, $seller_category_code_map);
    $general_result = fetch_general_cards_by_where($where_clause, $query_type, $show_count_req, $current_sort, $offset_req);
    $cards = $general_result['cards'];
    $cards_total_count = (int)$general_result['total_count'];
} else {
    // 7-2. 사업자번호 입력: 매칭 추천 기업 조회
    $tmp_cnt       = 9999999;
    $cust_sub_rank = [];
    $supp_sub_rank = [];
    $REC_KEDCD     = [];

    // 7-2-1. 사업자번호로 기업 API 조회 (당일 로그 캐시 우선)
    $comnum = str_replace('-', '', $bzno);
    $cominfo = '';

    $log_chk = sql_fetch("
        SELECT * FROM company_api_log
        WHERE  c_bizno = '{$comnum}'
          AND  DATE(c_datetime) = '" . date("Y-m-d") . "'
    ");

    if (empty($log_chk['c_content'])) {
        // 외부 API 호출
        $TOKEN_URL = "https://218.55.37.214:6059/enpinfo?userid=igaenconl&process=S&bzno={$comnum}&pidagryn=Y&jmno=ig001&format=XML&Length=10000";
        $cookie_nm = './cookie.txt';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,            $TOKEN_URL);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSLVERSION,     1);
        curl_setopt($ch, CURLOPT_HEADER,         0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_COOKIEJAR,      $cookie_nm);
        curl_setopt($ch, CURLOPT_COOKIEFILE,     $cookie_nm);
        curl_setopt($ch, CURLOPT_USERAGENT,      'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_TIMEOUT,        30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $html_ori = curl_exec($ch);
        curl_close($ch);

        if ($html_ori) {
            $xml = @simplexml_load_string($html_ori, null, LIBXML_NOCDATA);
            if ($xml && (string)$xml->HEADER->errcd === "00") {
                $json  = json_encode($xml, JSON_UNESCAPED_UNICODE);
                $kedcd = '';
                foreach ($xml->CONTENTS as $row) {
                    $kedcd = (string)$row->ENPintr0000->kedcd;
                }
                sql_query("
                    INSERT INTO company_api_log
                    SET c_bizno    = '{$comnum}',
                        c_kedcd    = '{$kedcd}',
                        c_content  = '" . addslashes($json) . "',
                        c_datetime = NOW(),
                        c_ip       = '{$_SERVER['REMOTE_ADDR']}'
                ");
                $cominfo = $json;
            }
        }
    } else {
        $cominfo = $log_chk['c_content'];
    }

    // 7-2-2. company 테이블 기본 정보 조회 (없으면 API 결과로 보강)
    $com = sql_fetch("SELECT * FROM company WHERE BZNO = '{$bzno}'");

    if (empty($com['BZC_CD']) && !empty($cominfo)) {
        $arr = json_decode($cominfo, true);
        if (!empty($arr['CONTENTS'])) {
            $contents = $arr['CONTENTS'];
            $node     = isset($contents['ENPintr0000']) ? $contents['ENPintr0000'] : ($contents[0]['ENPintr0000'] ?? []);
            if (!empty($node['bzc_cd']))  $com['BZC_CD'] = $node['bzc_cd'];
            if (!empty($node['kedcd']))   $com['KEDCD']  = $node['kedcd'];
        }
    }

    // 7-2-3. 동종업계 매출 랭크 기반 추천 기업 조회
    if ($com['BZC_CD']) {
        // 내 회사의 동종업계 순위 확인
        $rank_row = sql_fetch("
            SELECT KEDCD, ENP_NM, SALES, rank
            FROM (
                SELECT
                    KEDCD, ENP_NM, SALES,
                    @rank := IF(@prev = SALES, @rank, @rank + 1) AS rank,
                    @prev := SALES
                FROM company, (SELECT @rank := 0, @prev := NULL) AS vars
                WHERE BZC_CD = '{$com['BZC_CD']}'
                ORDER BY SALES DESC
            ) AS ranked
            WHERE KEDCD = '{$com['KEDCD']}'
        ");

        if ($rank_row['rank']) {
            $top30 = ROUND($rank_row['rank'] * 0.3);
            $top50 = ROUND($rank_row['rank'] * 0.5);

            // 상위 30~50% 구간이 너무 좁으면 상위 1~20위로 대체
            if (($top50 - $top30) < 6) {
                $sql_rec = "
                    SELECT KEDCD, ENP_NM, SALES, rank
                    FROM (
                        SELECT
                            KEDCD, ENP_NM, SALES,
                            @rank := IF(@prev = SALES, @rank, @rank + 1) AS rank,
                            @prev := SALES
                        FROM company, (SELECT @rank := 0, @prev := NULL) AS vars
                        WHERE BZC_CD = '{$com['BZC_CD']}'
                        ORDER BY SALES DESC
                    ) AS ranked
                    WHERE rank != {$rank_row['rank']} AND rank BETWEEN 1 AND 20
                    ORDER BY RAND()
                    LIMIT 15
                ";
            } else {
                $sql_rec = "
                    SELECT KEDCD, ENP_NM, SALES, rank
                    FROM (
                        SELECT
                            KEDCD, ENP_NM, SALES,
                            @rank := IF(@prev = SALES, @rank, @rank + 1) AS rank,
                            @prev := SALES
                        FROM company, (SELECT @rank := 0, @prev := NULL) AS vars
                        WHERE BZC_CD = '{$com['BZC_CD']}'
                        ORDER BY SALES DESC
                    ) AS ranked
                    WHERE rank BETWEEN {$top30} AND {$top50}
                    ORDER BY RAND()
                    LIMIT 15
                ";
            }

            $rec_res = sql_query($sql_rec);
            while ($r = sql_fetch_array($rec_res)) {
                $REC_KEDCD[] = $r['KEDCD'];
            }

            // 7-2-4. 추천 기업의 거래처(구매처/공급처) 조회 및 업종 내 랭크 계산
            if (!empty($REC_KEDCD)) {
                $REC        = implode("','", $REC_KEDCD);
                $client_res = sql_query("
                    SELECT A.*, B.BZC_CD, B.LOC_ADDRA, B.PD_NM, B.VENP_YN, C.k_code_name,
                           S.growth_score_5, S.profit_score_5, S.stability_score_5, S.final_total_score_5,
                           MAX(S.badge_growth) AS badge_growth,
                           MAX(S.badge_stability) AS badge_stability,
                           MAX(S.badge_venture) AS badge_venture
                    FROM   company_client A
                    LEFT JOIN company B ON A.TXPL_KEDCD = B.KEDCD
                    LEFT JOIN kssc C    ON B.BZC_CD = C.k_code
                    LEFT JOIN company_score_final_dedup S ON A.TXPL_KEDCD = S.KEDCD
                    WHERE  A.KEDCD IN ('{$REC}')
                      AND  A.TXPL_KEDCD != ''
                    GROUP BY A.TXPL_KEDCD, S.growth_score_5, S.profit_score_5, S.stability_score_5, S.final_total_score_5
                    ORDER BY B.BZC_CD DESC
                ");

                while ($row = sql_fetch_array($client_res)) {
                    // 거래처의 업종 내 매출 순위 계산
                    if ($row['BZC_CD'] && $row['TXPL_KEDCD']) {
                        $tx_rank = sql_fetch("
                            SELECT KEDCD, ENP_NM, SALES, rank
                            FROM (
                                SELECT
                                    KEDCD, ENP_NM, SALES,
                                    @vRank := IF(@group = BZC_CD,
                                                 IF(@prev_value = SALES, @vRank, @vRank + 1),
                                                 1) AS rank,
                                    @prev_value := SALES,
                                    @group := BZC_CD
                                FROM company, (SELECT @vRank := 0, @prev_value := NULL, @group := NULL) AS vars
                                WHERE BZC_CD = '{$row['BZC_CD']}'
                                ORDER BY BZC_CD, SALES DESC
                            ) AS ranked
                            WHERE KEDCD = '{$row['TXPL_KEDCD']}'
                        ");
                        $row['TXPL_RANK'] = $tx_rank['rank'];
                    }

                    // 구매처 (TXPL_CCD=1) / 공급처 (TXPL_CCD=2) 분류
                    if ($row['TXPL_CCD'] == 1) {
                        if (!$row['TXPL_RANK']) { $row['TXPL_RANK'] = $tmp_cnt++; }
                        if ($row['LOC_ADDRA'])  { $cust_sub_rank[] = $row; }
                    }
                    if ($row['TXPL_CCD'] == 2) {
                        if (!$row['TXPL_RANK']) { $row['TXPL_RANK'] = $tmp_cnt++; }
                        if ($row['LOC_ADDRA'])  { $supp_sub_rank[] = $row; }
                    }
                }
            }
        }
    }

    // 7-2-5. 거래처 배열 → 카드 배열로 변환
    $map_cards = function($rows, $type) {
        $out = [];
        foreach ($rows as $row) {
            if (empty($row['TXPL_KEDCD']) || empty($row['TXPL_NM'])) continue;
            $out[] = [
                'type'    => $type,
                'title'   => $row['TXPL_NM'],
                'tags'    => parse_pd_nm($row['PD_NM']),
                'score'   => calc_score_avg($row['final_total_score_5']),
                'grow'    => $row['growth_score_5'],
                'profit'  => $row['profit_score_5'],
                'stable'  => $row['stability_score_5'],
                'company' => $row['TXPL_NM'],
                'kedcd'   => $row['TXPL_KEDCD'],
                'venp_yn' => $row['VENP_YN'],
                'badge_growth' => $row['badge_growth'],
                'badge_stability' => $row['badge_stability'],
                'badge_venture' => $row['badge_venture'],
            ];
        }
        return $out;
    };

    $dynamic_cards = array_merge(
        $map_cards($cust_sub_rank, 'buyer'),
        $map_cards($supp_sub_rank, 'seller')
    );
    if (!empty($dynamic_cards)) {
        $cards = $dynamic_cards;
    }
    $cards_total_count = count($cards);
}

$buyer_loaded_count = 0;
$seller_loaded_count = 0;
foreach ($cards as $card_item) {
    if (($card_item['type'] ?? 'buyer') === 'seller') $seller_loaded_count++;
    else $buyer_loaded_count++;
}

$buyer_total_count = $buyer_loaded_count;
$seller_total_count = $seller_loaded_count;
if (!$has_comnum) {
    if ($current_type === 'seller') {
        $buyer_total_count = 0;
        $seller_total_count = $cards_total_count;
    } else {
        $buyer_total_count = $cards_total_count;
        $seller_total_count = 0;
    }
}

// ----------------------------------------------------------
// 8. 사이드바: 현재 열린 그룹 탐색
// ----------------------------------------------------------
$open_group_slug = null;

foreach ($list_categories as $lg) {
    // 8-1. 현재 카테고리가 대분류 슬러그와 일치하는 경우
    if ($current_cat === $lg['slug']) {
        $open_group_slug = $lg['slug'];
        break;
    }
    
    // 8-2. 현재 카테고리가 중분류(sub) 슬러그 중 하나와 일치하는 경우
    foreach ($lg['sub'] as $sub) {
        if ($current_cat === $sub['slug']) {
            $open_group_slug = $lg['slug']; // 부모 대분류의 슬러그를 저장
            break 2;
        }
    }
}

// 만약 아무것도 선택되지 않은 'all' 상태라면 첫 번째 그룹을 열어두거나 null 유지
if (!$open_group_slug && $current_cat === 'all') {
}

$sort_labels = [
    'rank' => '랭킹순',
    'grow' => '성장성순',
    'profit' => '수익성순',
    'stable' => '안정성순',
];
$current_sort_label = isset($sort_labels[$current_sort]) ? $sort_labels[$current_sort] : $sort_labels['rank'];

$sidebar_icon_map = [
    'all' => 'cat-iso-all',
    'metal' => 'cat-iso-7',
    'chemical' => 'cat-iso-5',
    'electronics' => 'cat-iso-10',
    'machinery' => 'cat-iso-6',
    'auto' => 'cat-iso-11',
    'food' => 'cat-iso-4',
    'medical' => 'cat-iso-17',
    'packaging' => 'cat-iso-12',
];
?>

<div id="listWrap">
<!-- =========================================================
     모바일 전용: 업종별 카테고리 탭
     ========================================================= -->
<div class="list-cat-tab-wrap">
    <div class="list-cat-tabs" id="catTabs"></div>
    <div class="list-cat-sub" id="catSubSwiper"></div>
</div>

<!-- =========================================================
     본문 레이아웃
     ========================================================= -->
<div class="list-body">
    <div class="inner">

        <aside class="list-sidebar">

            <div class="list-sidebar_ai_section">
                <a href="?cat=all&type=ai" class="list-sidebar_ai <?php echo $has_comnum ? 'on' : ''; ?>">
                    <span class="list-sidebar_ai_icon"><span class="ai-magic-wand"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16.8 2.2l1.1 2.3 2.3 1.1-2.3 1.1-1.1 2.3-1.1-2.3-2.3-1.1 2.3-1.1 1.1-2.3zm3.2 2.8.6 1.1 1.1.6-1.1.6-.6 1.1-.6-1.1-1.1-.6 1.1-.6.6-1.1zM7.4 17.4 16.1 8.7l2.6 2.6-8.7 8.7-2.6-2.6zm-1 2.4 1.3 1.3-3.1 1 .8-2.9z"/></svg></span></span>
                    <div class="list-sidebar_ai_body">
                        <span class="list-sidebar_ai_label">AI 추천받기</span>
                        <span class="list-sidebar_ai_sub">실거래 기반 맞춤 추천</span>
                    </div>
                    <span class="list-sidebar_ai_arrow" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </span>
                </a>
            </div>

            <div class="list-sidebar_or"><span>업종별 탐색</span></div>

            <nav class="list-sidebar_nav">
                <?php foreach ($list_categories as $lg):
                    // 수정된 로직: 현재 카테고리가 본인이거나 하위 카테고리인 경우 그룹을 엽니다.
                    $is_open          = ($open_group_slug === $lg['slug']);
                    $is_current_group = ($current_cat === $lg['slug']);
                    $group_icon = isset($sidebar_icon_map[$lg['slug']]) ? $sidebar_icon_map[$lg['slug']] : 'cat-iso-all';
                ?>
                <div class="list-sidebar_group <?php echo $is_open ? 'is-open' : ''; ?>"
                     data-group="<?php echo htmlspecialchars($lg['slug']); ?>">

                    <div class="list-sidebar_group_header <?php echo $is_current_group ? 'on' : ''; ?>"
                         role="button"
                         tabindex="0"
                         aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>"
                         aria-controls="sidebar-body-<?php echo htmlspecialchars($lg['slug']); ?>">
                        <div class="list-sidebar_group_left">
                            <span class="list-sidebar_group_icon">
                                <svg viewBox="0 0 48 48" width="26" height="26" aria-hidden="true">
                                    <use href="#<?php echo htmlspecialchars($group_icon); ?>"></use>
                                </svg>
                            </span>
                            <a href="?<?php echo $mq; ?>cat=<?php echo urlencode($lg['slug']); ?>"
                               class="list-sidebar_group_name list-sidebar_group_title_link">
                                <?php echo htmlspecialchars($lg['label']); ?>
                            </a>
                        </div>
                        <span class="list-sidebar_group_toggle" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 18l6-6-6-6"/>
                            </svg>
                        </span>
                    </div>

                    <div class="list-sidebar_group_body"
                         id="sidebar-body-<?php echo htmlspecialchars($lg['slug']); ?>">
                        <div class="list-sidebar_sub_wrap">
                            <?php foreach ($lg['sub'] as $sub): ?>
                            <a href="?<?php echo $mq; ?>cat=<?php echo urlencode($sub['slug']); ?>"
                               class="list-sidebar_sub <?php echo ($current_cat === $sub['slug']) ? 'on' : ''; ?>">
                                <?php echo htmlspecialchars($sub['label']); ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </nav>
        </aside>

        <main class="list-main">

            <div class="list-top">
                <div class="list-type-wrap">
                    <div class="list-type-switch" role="group" aria-label="구매하기/판매하기">
                        <span class="list-type-slider" aria-hidden="true"></span>
                        <button type="button" class="list-type-btn on" data-type="buyer">구매하기</button>
                        <button type="button" class="list-type-btn"    data-type="seller">판매하기</button>
                    </div>
                    <p class="list-count">
                        <?php if (!empty($_GET['comnum'])): ?>
                        <span class="list-search-query">
                            사업자번호 <?php echo htmlspecialchars($_GET['comnum']); ?> 검색 ·&nbsp;
                        </span>
                        <?php endif; ?>
                        <strong><?php echo (int)($current_type === 'seller' ? $seller_total_count : $buyer_total_count); ?></strong>개의 기업
                    </p>
                </div>
                <div class="list-sort">
                    <select id="sortSelect" aria-label="정렬 기준">
                        <option value="rank" <?php echo $current_sort === 'rank' ? 'selected' : ''; ?>>랭킹순</option>
                        <option value="grow" <?php echo $current_sort === 'grow' ? 'selected' : ''; ?>>성장성순</option>
                        <option value="profit" <?php echo $current_sort === 'profit' ? 'selected' : ''; ?>>수익성순</option>
                        <option value="stable" <?php echo $current_sort === 'stable' ? 'selected' : ''; ?>>안정성순</option>
                    </select>
                    <div class="sort-dropdown" id="sortDropdown">
                        <button type="button"
                                class="sort-dropdown-btn"
                                id="sortDropdownBtn"
                                aria-haspopup="listbox"
                                aria-expanded="false">
                            <span id="sortDropdownLabel"><?php echo htmlspecialchars($current_sort_label); ?></span>
                            <svg class="sort-dropdown-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"/>
                            </svg>
                        </button>
                        <ul class="sort-dropdown-menu" id="sortDropdownMenu" role="listbox">
                            <li class="sort-dropdown-item<?php echo $current_sort === 'rank' ? ' selected' : ''; ?>" role="option" data-val="rank">랭킹순</li>
                            <li class="sort-dropdown-item<?php echo $current_sort === 'grow' ? ' selected' : ''; ?>" role="option" data-val="grow">성장성순</li>
                            <li class="sort-dropdown-item<?php echo $current_sort === 'profit' ? ' selected' : ''; ?>" role="option" data-val="profit">수익성순</li>
                            <li class="sort-dropdown-item<?php echo $current_sort === 'stable' ? ' selected' : ''; ?>" role="option" data-val="stable">안정성순</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="list-grid">
                <?php if (empty($cards)): ?>
                    <div style="grid-column: 1/-1; padding: 100px 0; text-align: center; color: #888;">
                        해당 카테고리에 등록된 기업이 없습니다.
                    </div>
                <?php else: ?>
                    <?php foreach ($cards as $idx => $c):
                        $scoreValue = isset($c['score']) && is_numeric($c['score']) ? (float)$c['score'] : 0.0;
                        $growScore = isset($c['grow']) && is_numeric($c['grow']) ? (float)$c['grow'] : 0.0;
                        $profitScore = isset($c['profit']) && is_numeric($c['profit']) ? (float)$c['profit'] : 0.0;
                        $stableScore = isset($c['stable']) && is_numeric($c['stable']) ? (float)$c['stable'] : 0.0;

                        $careerScore = min(5, max(2.5, $stableScore * 0.9 + (($idx % 3) * 0.3)));
                        $employScore = min(5, max(2.5, $growScore * 0.8 + (($idx % 4) * 0.25)));
                        $radarScores = [$growScore, $profitScore, $stableScore, $careerScore, $employScore];
                        $radarSvg = build_radar_svg_5($radarScores);
                        $starsHtml = render_stars_html($scoreValue);

                        $allTags = is_array($c['tags'] ?? null) ? $c['tags'] : [];
                        $showNum = 3;
                        $displayTags = array_slice($allTags, 0, $showNum);
                    ?>
                    <a href="/ko/company_detail.php?cd=<?php echo urlencode(simple_crypt($c['kedcd'] ?? '')); ?>"
                    class="mCompanyCard detail_view"
                    data-type="<?php echo htmlspecialchars($c['type'] ?? 'buyer'); ?>"
                    data-rank="<?php echo (int)$idx; ?>"
                    data-total="<?php echo $scoreValue; ?>"
                    data-grow="<?php echo ($c['grow'] !== null && $c['grow'] !== '') ? (float)$c['grow'] : ''; ?>"
                    data-profit="<?php echo ($c['profit'] !== null && $c['profit'] !== '') ? (float)$c['profit'] : ''; ?>"
                    data-stable="<?php echo ($c['stable'] !== null && $c['stable'] !== '') ? (float)$c['stable'] : ''; ?>"
                    kedcd="<?php echo htmlspecialchars($c['kedcd'] ?? ''); ?>"
                    style="text-decoration:none;color:inherit;">

                        <div class="mCompanyCard_content">
                            <div class="mCompanyCard_badges">
                                <?php
                                $has_badge = false;
                                $badge_growth = format_badge_percent($c['badge_growth'] ?? null);
                                $badge_stability = format_badge_percent($c['badge_stability'] ?? null);
                                $badge_venture = strtoupper(trim((string)($c['badge_venture'] ?? '')));

                                if ($badge_growth !== '') {
                                    $has_badge = true;
                                    echo '<span class="mCompanyCard_badge mCompanyCard_badge_top_growth">성장TOP' . htmlspecialchars($badge_growth) . '%</span>';
                                }
                                if ($badge_stability !== '') {
                                    $has_badge = true;
                                    echo '<span class="mCompanyCard_badge mCompanyCard_badge_top_stability">재무안정TOP' . htmlspecialchars($badge_stability) . '%</span>';
                                }
                                if ($badge_venture === 'Y') {
                                    $has_badge = true;
                                    echo '<span class="mCompanyCard_badge mCompanyCard_badge_venture">벤처인증</span>';
                                }
                                if (!$has_badge) {
                                    echo '<span class="mCompanyCard_badge mCompanyCard_badge_placeholder">뱃지 준비중</span>';
                                }
                                ?>
                            </div>
                            <h3 class="mCompanyCard_title"><?php echo htmlspecialchars(mask_company_name_like_mock($c['title'] ?? '')); ?></h3>

                            <p class="mCompanyCard_desc">
                                <?php foreach ($displayTags as $t): ?>
                                    <span class="mCompanyCard_desc_tag"><?php echo htmlspecialchars($t); ?></span>
                                <?php endforeach; ?>
                            </p>

                            <div class="radar-card">
                                <div class="radar-card_chart">
                                    <svg viewBox="0 0 210 200" class="radar-svg"><?php echo $radarSvg; ?></svg>
                                </div>
                            </div>

                            <div class="mCompanyCard_footer_stars">
                                <span class="mCompanyCard_footer_stars_wrap"><?php echo $starsHtml; ?></span>
                                <span class="mCompanyCard_footer_score"><?php echo number_format($scoreValue, 1); ?></span>
                                <span class="mCompanyCard_footer_max">/ 5.0</span>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="list-grid_more_wrap">
                <button type="button" id="listMoreBtn" class="list-grid_more_btn">더보기</button>
            </div>
        </main>
    </div>
</div>
</div>


<!-- =========================================================
     레이어: 기업 상세보기
     ========================================================= -->
<div id="company-view-lay" class="layer-base layer-main company-view">
    <div class="layer-in new"></div>
</div>

<!-- =========================================================
     팝업: 매칭 요청
     ========================================================= -->
<?php
$company_detail_popup_variant = 'advanced';
include_once(G5_THEME_PATH . '/partials/company-detail-matching-popup.php');
?>

<?php
$list_categories_for_js = [];
foreach ($list_categories as $lg) {
    $subs = [];
    foreach ($lg['sub'] as $sub) {
        $subs[] = [
            'slug' => $sub['slug'],
            'label' => $sub['label'],
        ];
    }

    $list_categories_for_js[] = [
        'slug' => $lg['slug'],
        'label' => $lg['label'],
        'sub' => $subs,
    ];
}

$matching_list_js_config = [
    'freeLimit' => (int)$free_limit,
    'canLoadMore' => (bool)$can_load_more,
    'isServerPaged' => !$has_comnum,
    'totalByType' => [
        'buyer' => (int)$buyer_total_count,
        'seller' => (int)$seller_total_count,
    ],
    'currentType' => ($current_type === 'seller') ? 'seller' : 'buyer',
    'currentCat' => $current_cat,
    'currentSort' => $current_sort,
    'mq' => $mq,
    'listCategories' => $list_categories_for_js,
];
?>
<script>
window.MATCHING_LIST_CONFIG = <?php echo json_encode($matching_list_js_config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
</script>
<script src="<?php echo G5_URL; ?>/ko/js/matching_list.js?ver=<?php echo (int)$matching_list_js_ver; ?>"></script>

<?php include_once(G5_THEME_PATH.'/tail.php'); ?>
