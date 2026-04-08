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
add_stylesheet('<link rel="stylesheet" href="'.G5_THEME_URL.'/css/pages/matching_list.css?ver='.G5_CSS_VER.'">', 200);
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

// 점수 계산 헬퍼 (NULL 방어 포함)
function calc_score_avg($val) {
    return ($val !== null && $val !== '') ? number_format($val, 1) : '-';
}

function format_badge_percent($value) {
    if ($value === null || $value === '') return '';
    $str = trim((string)$value);
    if ($str === '') return '';
    if (!is_numeric($str)) return $str;

    $num = (float)$str;
    if ($num == (int)$num) return (string)((int)$num);
    return rtrim(rtrim(number_format($num, 2, '.', ''), '0'), '.');
}

// PD_NM 문자열을 태그 배열로 변환
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

function get_seller_codes_by_category($list_categories, $current_cat, $seller_category_code_map) {
    // 전체보기: 현재 등록된 판매 코드셋 전체를 합산
    if (empty($current_cat) || $current_cat === 'all') {
        $all = [];
        foreach ($seller_category_code_map as $codes) {
            $all = array_merge($all, $codes);
        }
        return array_values(array_unique($all));
    }

    // 하위 카테고리 직접 선택
    if (isset($seller_category_code_map[$current_cat])) {
        return array_values(array_unique($seller_category_code_map[$current_cat]));
    }

    // 대분류 선택: 하위 카테고리 판매 코드셋을 합산
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

function resolve_where_clause_for_general_list($query_type, $current_cat, $list_categories, $seller_category_code_map) {
    if ($query_type === 'seller') {
        $seller_codes = get_seller_codes_by_category($list_categories, $current_cat, $seller_category_code_map);
        if (empty($seller_codes)) return "WHERE 1=0";
        return build_company_where_clause($seller_codes, false);
    }

    $buyer_codes = get_target_codes_by_category($list_categories, $current_cat);
    return build_company_where_clause($buyer_codes, false);
}

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
?>

<!-- =========================================================
     모바일 전용: 업종별 카테고리 Swiper (대분류)
     ========================================================= -->
<div class="list-cat-swiper-wrap">
    <div class="list-cat-swiper swiper">
        <div class="swiper-wrapper">
            <div class="swiper-slide">
                <a href="?<?php echo $mq; ?>cat=all"
                   class="<?php echo ($current_cat === 'all' || $current_cat === '') ? 'on' : ''; ?>">
                    전체
                </a>
            </div>
            <?php foreach ($list_categories as $lg): ?>
            <div class="swiper-slide">
                <a href="?<?php echo $mq; ?>cat=<?php echo urlencode($lg['slug']); ?>"
                   class="<?php echo ($current_cat === $lg['slug']) ? 'on' : ''; ?>">
                    <?php echo htmlspecialchars($lg['label']); ?>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- =========================================================
     본문 레이아웃
     ========================================================= -->
     <div class="list-body">
    <div class="inner">

        <aside class="list-sidebar">

            <a href="?cat=all&type=ai" class="list-sidebar_ai <?php echo $has_comnum ? 'on' : ''; ?>">
                <span class="list-sidebar_ai_icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 2L13.8 7.2L19.2 7.5L14.6 10.8L16.2 16L12 12.9L7.8 16L9.4 10.8L4.8 7.5L10.2 7.2Z" fill="currentColor"/>
                    </svg>
                </span>
                <span>AI 추천받기</span>
            </a>

            <h2 class="list-sidebar_title">업종별</h2>

            <nav class="list-sidebar_nav">
                <a href="?<?php echo $mq; ?>cat=all"
                   class="<?php echo ($current_cat === 'all' || $current_cat === '') ? 'on' : ''; ?>">
                    전체보기
                </a>

                <?php foreach ($list_categories as $lg):
                    // 수정된 로직: 현재 카테고리가 본인이거나 하위 카테고리인 경우 그룹을 엽니다.
                    $is_open          = ($open_group_slug === $lg['slug']);
                    $is_current_group = ($current_cat    === $lg['slug']);
                ?>
                <div class="list-sidebar_group <?php echo $is_open ? 'is-open' : ''; ?>"
                     data-group="<?php echo htmlspecialchars($lg['slug']); ?>">

                    <div class="list-sidebar_group_header <?php echo $is_current_group ? 'on' : ''; ?>"
                         role="button"
                         tabindex="0"
                         aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>"
                         aria-controls="sidebar-body-<?php echo htmlspecialchars($lg['slug']); ?>">
                        <a href="?<?php echo $mq; ?>cat=<?php echo urlencode($lg['slug']); ?>"
                           class="list-sidebar_group_title list-sidebar_group_title_link">
                            <?php echo htmlspecialchars($lg['label']); ?>
                        </a>
                        <span class="list-sidebar_group_toggle" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 18l6-6-6-6"/>
                            </svg>
                        </span>
                    </div>

                    <div class="list-sidebar_group_body"
                         id="sidebar-body-<?php echo htmlspecialchars($lg['slug']); ?>">
                        <?php foreach ($lg['sub'] as $sub): ?>
                        <a href="?<?php echo $mq; ?>cat=<?php echo urlencode($sub['slug']); ?>"
                           class="list-sidebar_sub <?php echo ($current_cat === $sub['slug']) ? 'on' : ''; ?>">
                            <?php echo htmlspecialchars($sub['label']); ?>
                        </a>
                        <?php endforeach; ?>
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
                        <strong><?php echo (int)$cards_total_count; ?></strong>개의 기업
                    </p>
                </div>
                <div class="list-sort">
                    <label for="sortSelect">정렬</label>
                    <select id="sortSelect" aria-label="정렬 기준">
                        <option value="rank" <?php echo $current_sort === 'rank' ? 'selected' : ''; ?>>랭킹순</option>
                        <option value="grow" <?php echo $current_sort === 'grow' ? 'selected' : ''; ?>>성장성순</option>
                        <option value="profit" <?php echo $current_sort === 'profit' ? 'selected' : ''; ?>>수익성순</option>
                        <option value="stable" <?php echo $current_sort === 'stable' ? 'selected' : ''; ?>>재무안정순</option>
                    </select>
                </div>
            </div>

            <div class="list-grid">
                <?php if (empty($cards)): ?>
                    <div style="grid-column: 1/-1; padding: 100px 0; text-align: center; color: #888;">
                        해당 카테고리에 등록된 기업이 없습니다.
                    </div>
                <?php else: ?>
                    <?php foreach ($cards as $idx => $c):
                        $scoreValue = (float)$c['score'];
                        $starFull   = floor($scoreValue);
                        $starPart   = $scoreValue - $starFull;
                    ?>
                    <a href="#"
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
                            <div class="mCompanyCard_venture_slot">
                                <?php
                                $has_badge = false;
                                $badge_growth = format_badge_percent($c['badge_growth'] ?? null);
                                $badge_stability = format_badge_percent($c['badge_stability'] ?? null);
                                $badge_venture = strtoupper(trim((string)($c['badge_venture'] ?? '')));

                                if ($badge_growth !== '') {
                                    $has_badge = true;
                                    echo '<span class="mCompanyCard_badge mCompanyCard_badge_growth_top" style="font-size:11px;font-weight:500;padding:3px 8px;border-radius:4px;background:#dbeafe;color:#1e40af;">성장TOP' . htmlspecialchars($badge_growth) . '%</span>';
                                }
                                if ($badge_stability !== '') {
                                    $has_badge = true;
                                    echo '<span class="mCompanyCard_badge mCompanyCard_badge_stability_top" style="font-size:11px;font-weight:500;padding:3px 8px;border-radius:4px;background:#ede9fe;color:#5b21b6;">재무안정TOP' . htmlspecialchars($badge_stability) . '%</span>';
                                }
                                if ($badge_venture === 'Y') {
                                    $has_badge = true;
                                    echo '<span class="mCompanyCard_badge mCompanyCard_badge_venture" style="font-size:11px;font-weight:500;padding:3px 8px;border-radius:4px;background:#10b981;color:#fff;">벤처인증</span>';
                                }
                                if (!$has_badge) {
                                    echo '<span class="mCompanyCard_badge mCompanyCard_badge_pending" style="font-size:11px;font-weight:500;padding:3px 8px;border-radius:4px;background:#f3f4f6;color:#9ca3af;">뱃지 준비중</span>';
                                }
                                ?>
                            </div>
                            <h3 class="mCompanyCard_title"><?php echo htmlspecialchars($c['title']); ?></h3>

                            <p class="mCompanyCard_desc">
                                <?php 
                                $allTags = $c['tags']; 
                                $totalCount = count($allTags);
                                $showNum = 2; 
                                
                                if ($totalCount > 0):
                                    $displayTags = array_slice($allTags, 0, $showNum);
                                    $restCount = $totalCount - $showNum;

                                    foreach ($displayTags as $t): 
                                ?>
                                    <span class="mCompanyCard_desc_tag"><?php echo htmlspecialchars($t); ?></span>
                                <?php 
                                    endforeach; 

                                    if ($restCount > 0): 
                                ?>
                                    <span class="mCompanyCard_desc_more">+<?php echo $restCount; ?></span>
                                <?php 
                                    endif; 
                                endif; 
                                ?>
                            </p>

                            <div class="mCompanyCard_rating">
                                <div class="mCompanyCard_rating_left">
                                    <span class="mCompanyCard_rating_label">종합</span>
                                    <span class="mCompanyCard_rating_score"><?php echo $c['score']; ?></span>
                                    <div class="mCompanyCard_rating_stars">
                                        <?php for ($i = 1; $i <= 5; $i++):
                                            if ($i <= $starFull): ?>
                                        <svg viewBox="0 0 24 24" fill="#ffc107"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                        <?php elseif ($i == $starFull + 1 && $starPart > 0): ?>
                                        <span class="star-partial">
                                            <svg viewBox="0 0 24 24" fill="#ffc107" style="position:absolute; left:0; top:0; clip-path: inset(0 <?php echo (1 - $starPart) * 100; ?>% 0 0);"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                            <svg viewBox="0 0 24 24" fill="#e0e0e0" style="position:absolute; left:0; top:0;"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                        </span>
                                        <?php else: ?>
                                        <svg viewBox="0 0 24 24" fill="#e0e0e0"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                        <?php endif; endfor; ?>
                                    </div>
                                </div>
                                <div class="mCompanyCard_rating_right">
                                    <?php foreach (['grow' => '성장성', 'profit' => '수익성', 'stable' => '재무안정'] as $key => $label): ?>
                                    <div class="mCompanyCard_rating_bar">
                                        <span class="mCompanyCard_rating_bar_label"><?php echo $label; ?></span>
                                        <div class="mCompanyCard_rating_bar_chart">
                                            <div class="mCompanyCard_rating_bar_fill" style="width:<?php echo ($c[$key] !== null ? ($c[$key] / 5 * 100) : 0); ?>%;"></div>
                                        </div>
                                        <span class="mCompanyCard_rating_bar_count"><?php echo ($c[$key] !== null ? number_format($c[$key], 1) : '-'); ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="list-more-wrap">
                <button type="button" id="listMoreBtn" class="list-more-btn">더보기</button>
            </div>
        </main>
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

<!-- =========================================================
     JavaScript
     ========================================================= -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
// ----------------------------------------------------------
// Swiper: 모바일 카테고리 탭
// ----------------------------------------------------------
(function () {
    var catEl = document.querySelector('.list-cat-swiper');
    if (!catEl) return;
    new Swiper('.list-cat-swiper', {
        slidesPerView: 'auto',
        spaceBetween: 8,
        freeMode: true,
        grabCursor: true,
        resistanceRatio: 0.85
    });
})();

// ----------------------------------------------------------
// 사이드바 대분류 아코디언
// ----------------------------------------------------------
(function () {
    var groups = document.querySelectorAll('.list-sidebar_group');
    if (!groups.length) return;

    function openOnlyThis(targetGroup) {
        groups.forEach(function (g) {
            var isThis = (g === targetGroup);
            g.classList.toggle('is-open', isThis);
            var h = g.querySelector('.list-sidebar_group_header');
            if (h) h.setAttribute('aria-expanded', isThis ? 'true' : 'false');
        });
    }

    groups.forEach(function (group) {
        var header = group.querySelector('.list-sidebar_group_header');
        if (!header) return;

        header.addEventListener('click', function (e) {
            var toggleEl = e.target.closest('.list-sidebar_group_toggle');
            var linkEl = header.querySelector('.list-sidebar_group_title_link');

            if (toggleEl) {
                if (group.classList.contains('is-open')) {
                    group.classList.remove('is-open');
                    header.setAttribute('aria-expanded', 'false');
                } else {
                    openOnlyThis(group);
                }
                return;
            }

            if (linkEl && linkEl.href) {
                location.href = linkEl.href;
            }
        });

        header.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                var linkEl = header.querySelector('.list-sidebar_group_title_link');
                if (linkEl && linkEl.href) {
                    e.preventDefault();
                    location.href = linkEl.href;
                }
            } else if (e.key === ' ') {
                e.preventDefault();
                var toggleEl = header.querySelector('.list-sidebar_group_toggle');
                if (toggleEl) toggleEl.click();
            }
        });
    });
})();

// ----------------------------------------------------------
// 구매하기/판매하기 탭 스위치
// ----------------------------------------------------------
(function () {
    var wrap = document.querySelector('.list-type-switch');
    if (!wrap) return;

    var slider      = wrap.querySelector('.list-type-slider');
    var btns        = wrap.querySelectorAll('.list-type-btn');
    var moreBtn     = document.getElementById('listMoreBtn');
    var sortSelect  = document.getElementById('sortSelect');
    var grid        = document.querySelector('.list-grid');
    var pageSize    = <?php echo (int)$free_limit; ?>;
    var canLoadMore = <?php echo $can_load_more ? 'true' : 'false'; ?>;
    var isServerPaged = <?php echo !$has_comnum ? 'true' : 'false'; ?>;
    var totalByType = {
        buyer: <?php echo (int)$buyer_total_count; ?>,
        seller: <?php echo (int)$seller_total_count; ?>
    };
    var showCount   = { buyer: pageSize, seller: pageSize };
    var currentType = <?php echo json_encode($current_type === 'seller' ? 'seller' : 'buyer'); ?>;
    var isLoadingMore = false;

    function updateSlider() {
        var active = wrap.querySelector('.list-type-btn.on');
        if (!slider || !active) return;
        var wrapRect = wrap.getBoundingClientRect();
        var btnRect  = active.getBoundingClientRect();
        slider.style.width = btnRect.width + 'px';
        slider.style.left  = (btnRect.left - wrapRect.left) + 'px';
    }

    function renderType(type) {
        currentType = type;
        var cards   = document.querySelectorAll('.list-grid .mCompanyCard');
        var matched = [];

        cards.forEach(function (card) {
            if (card.getAttribute('data-type') === type) matched.push(card);
            card.style.display = 'none';
        });

        var visibleCount = isServerPaged ? matched.length : Math.min(showCount[type], matched.length);
        for (var i = 0; i < visibleCount; i++) matched[i].style.display = '';

        var countEl = document.querySelector('.list-count strong');
        if (countEl) {
            var totalCountForType = totalByType[type] || 0;
            countEl.textContent = totalCountForType;
        }

        if (moreBtn) {
            var totalCountForType = totalByType[type] || 0;
            var shouldShowMoreBtn = totalCountForType > visibleCount;
            moreBtn.style.display = shouldShowMoreBtn ? '' : 'none';
        }
    }

    function getScore(card, key) {
        var raw = card.getAttribute('data-' + key);
        if (raw === null || raw === '') return null;
        var n = parseFloat(raw);
        return isNaN(n) ? null : n;
    }

    function sortCards(key) {
        if (!grid) return;

        var cards  = Array.from(grid.querySelectorAll('.mCompanyCard'));
        var buyers = [];
        var sellers = [];

        cards.forEach(function (card) {
            (card.getAttribute('data-type') === 'seller' ? sellers : buyers).push(card);
        });

        var cmp;
        if (key === 'rank') {
            cmp = function (a, b) {
                var av = getScore(a, 'total');
                var bv = getScore(b, 'total');
                if (av === null && bv === null) return (parseInt(a.getAttribute('data-rank'), 10) || 0) - (parseInt(b.getAttribute('data-rank'), 10) || 0);
                if (av === null) return 1;
                if (bv === null) return -1;
                if (bv === av) return (parseInt(a.getAttribute('data-rank'), 10) || 0) - (parseInt(b.getAttribute('data-rank'), 10) || 0);
                return bv - av;
            };
        } else {
            cmp = function (a, b) {
                var av = getScore(a, key);
                var bv = getScore(b, key);
                if (av === null && bv === null) return (parseInt(a.getAttribute('data-rank'), 10) || 0) - (parseInt(b.getAttribute('data-rank'), 10) || 0);
                if (av === null) return 1;
                if (bv === null) return -1;
                if (bv === av) return (parseInt(a.getAttribute('data-rank'), 10) || 0) - (parseInt(b.getAttribute('data-rank'), 10) || 0);
                return bv - av;
            };
        }

        buyers.sort(cmp);
        sellers.sort(cmp);

        grid.innerHTML = '';
        buyers.concat(sellers).forEach(function (card) {
            grid.appendChild(card);
        });
    }

    function fetchAndReplaceGrid(url, preserveScroll) {
        var prevScrollY = window.scrollY || window.pageYOffset || 0;
        isLoadingMore = true;
        if (moreBtn) moreBtn.disabled = true;

        return fetch(url.toString(), { credentials: 'same-origin' })
            .then(function (res) { return res.text(); })
            .then(function (html) {
                var doc = new DOMParser().parseFromString(html, 'text/html');
                var nextGrid = doc.querySelector('.list-grid');
                if (!nextGrid) throw new Error('list-grid not found');

                grid.innerHTML = nextGrid.innerHTML;
                if (!isServerPaged) {
                    sortCards(sortSelect ? (sortSelect.value || 'rank') : 'rank');
                }
                renderType(currentType);
                if (preserveScroll) window.scrollTo(0, prevScrollY);
                history.replaceState({ type: currentType }, '', url.toString());
            })
            .catch(function () {
                location.href = url.toString();
            })
            .finally(function () {
                isLoadingMore = false;
                if (moreBtn) moreBtn.disabled = false;
            });
    }

    function fetchAndAppendGrid(url, preserveScroll) {
        var prevScrollY = window.scrollY || window.pageYOffset || 0;
        isLoadingMore = true;
        if (moreBtn) moreBtn.disabled = true;

        return fetch(url.toString(), { credentials: 'same-origin' })
            .then(function (res) { return res.text(); })
            .then(function (html) {
                var doc = new DOMParser().parseFromString(html, 'text/html');
                var nextGrid = doc.querySelector('.list-grid');
                if (!nextGrid) throw new Error('list-grid not found');

                var nodes = Array.from(nextGrid.querySelectorAll('.mCompanyCard'));
                if (!nodes.length) return;
                nodes.forEach(function (card) { grid.appendChild(card); });

                if (!isServerPaged) {
                    sortCards(sortSelect ? (sortSelect.value || 'rank') : 'rank');
                }
                renderType(currentType);
                if (preserveScroll) window.scrollTo(0, prevScrollY);
                history.replaceState({ type: currentType }, '', url.toString());
            })
            .catch(function () {
                location.href = url.toString();
            })
            .finally(function () {
                isLoadingMore = false;
                if (moreBtn) moreBtn.disabled = false;
            });
    }

    function setType(type) {
        if (isServerPaged && type !== currentType) {
            var moveUrl = new URL(location.href);
            moveUrl.searchParams.set('type', type);
            moveUrl.searchParams.delete('offset');
            location.href = moveUrl.toString();
            return;
        }

        btns.forEach(function (b) {
            var isActive = b.getAttribute('data-type') === type;
            b.classList.toggle('on', isActive);
            b.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
        updateSlider();
        renderType(type);

        var url = new URL(location.href);
        url.searchParams.set('type', type);
        history.replaceState({ type: type }, '', url);
    }

    // 초기화
    btns.forEach(function (btn) {
        btn.setAttribute('aria-pressed', btn.classList.contains('on') ? 'true' : 'false');
        btn.addEventListener('click', function () {
            setType(btn.getAttribute('data-type'));
        });
    });

    var urlType = new URLSearchParams(location.search).get('type');
    setType(urlType === 'seller' ? 'seller' : 'buyer');

    if (sortSelect) {
        sortSelect.addEventListener('change', function () {
            if (isServerPaged) {
                if (isLoadingMore) return;
                var selectedSort = sortSelect.value || 'rank';
                var url = new URL(location.href);
                url.searchParams.set('sort', selectedSort);
                url.searchParams.set('offset', 0);
                fetchAndReplaceGrid(url, true);
                return;
            }
            showCount[currentType] = pageSize;
            sortCards(sortSelect.value || 'rank');
            renderType(currentType);
        });
        if (!isServerPaged) sortCards(sortSelect.value || 'rank');
        renderType(currentType);
    }

    if (moreBtn) {
        moreBtn.addEventListener('click', function () {
            if (!canLoadMore) {
                jAlert("로그인 후 이용하세요.", "알림", function () {
                    location.href = "/bbs/login.php?url=" + encodeURIComponent(location.pathname + location.search);
                });
                return;
            }
            if (isServerPaged) {
                if (isLoadingMore) return;
                var currentShown = document.querySelectorAll('.list-grid .mCompanyCard[data-type="' + currentType + '"]').length;
                if (currentShown >= (totalByType[currentType] || 0)) {
                    renderType(currentType);
                    return;
                }

                var url = new URL(location.href);
                url.searchParams.set('offset', currentShown);
                url.searchParams.set('sort', sortSelect ? (sortSelect.value || 'rank') : 'rank');
                fetchAndAppendGrid(url, true);
                return;
            }
            showCount[currentType] += pageSize;
            renderType(currentType);
        });
    }

    window.addEventListener('resize', updateSlider);
})();

// ----------------------------------------------------------
// 매칭 메시지 템플릿
// ----------------------------------------------------------
var matchingMessageTemplates = {
    distributor: [
        { title: '신규 제품 소싱', body: '판매 가능한 신규 제품을 공급할 제조사를 찾고 있습니다. 테스트 거래 후 지속적인 거래를 희망합니다.' },
        { title: '제조 파트너 발굴', body: '안정적인 제품 공급이 가능한 제조사를 찾고 있습니다. 협의를 통해 장기 거래를 검토하고자 합니다.' },
        { title: 'OEM/ODM 생산 협력', body: '자체 브랜드 제품 생산을 위해 OEM 또는 ODM 협력 가능한 제조사를 찾고 있습니다.' },
        { title: '신규 상품 발굴', body: '판매 경쟁력 있는 신규 제품을 발굴하기 위해 협력 가능한 제조 파트너를 찾고 있습니다.' },
        { title: '안정적인 공급처 확보', body: '지속적으로 제품 공급이 가능한 제조사를 찾고 있습니다. 상호 협의를 통해 거래 조건을 논의하고자 합니다.' }
    ],
    manufacturer: [
        { title: '유통 파트너 발굴', body: '자사 제품의 판매 확대를 위해 유통 및 판매 협력 가능한 기업을 찾고 있습니다. 안정적이고 장기적인 거래 파트너를 희망합니다.' },
        { title: '신규 판매 채널 확보', body: '제품 판매처를 확대하기 위해 협업 가능한 유통사를 찾고 있습니다. 상호 협의를 통해 거래 조건을 논의하고자 합니다.' },
        { title: '수출/해외 유통 협력', body: '자사 제품의 해외 시장 진출을 위해 유통 및 판매 협력 가능한 파트너를 찾고 있습니다.' },
        { title: '신규 브랜드 유통 협력', body: '신제품 출시와 함께 판매 확대가 가능한 유통사를 찾고 있습니다. 초기 협의 후 장기적인 거래를 희망합니다.' },
        { title: '안정적인 판매 파트너 확보', body: '지속적인 제품 공급이 가능한 유통 및 판매 파트너를 찾고 있습니다. 안정적인 거래 관계 구축을 희망합니다.' }
    ]
};

function getCurrentBizRole() {
    var selected = $('input[name="biz_role_ui"]:checked').val();
    return selected === 'manufacturer' ? 'manufacturer' : 'distributor';
}

function renderMatchingTemplateCards() {
    var role = getCurrentBizRole();
    var rows = matchingMessageTemplates[role] || [];
    var html = '';
    for (var i = 0; i < rows.length; i++) {
        var n = i + 1;
        html += ''
            + '<div class="req-tpl-card" data-idx="' + i + '">'
            + '  <div class="req-tpl-head">'
            + '    <input type="checkbox" class="reqTplChk" value="' + i + '" id="reqTplChk_' + i + '">'
            + '    <h6>' + n + '. ' + rows[i].title + '</h6>'
            + '  </div>'
            + '  <p>' + rows[i].body + '</p>'
            + '</div>';
    }
    $('#reqTplGrid').html(html);
}

function updateMatchingMessageContent() {
    var role = getCurrentBizRole();
    var rows = matchingMessageTemplates[role] || [];
    var selectedTexts = [];
    $('.reqTplChk:checked').each(function () {
        var idx = parseInt($(this).val(), 10);
        if (!isNaN(idx) && rows[idx]) selectedTexts.push(rows[idx].body);
    });
    var extra = $.trim($('#match_content_extra').val() || '');
    var merged = selectedTexts.join("\n\n");
    if (extra) merged = merged ? (merged + "\n\n" + extra) : extra;
    $('#match_content').val(merged);
}

// ----------------------------------------------------------
// 요청 방식 선택 (기본/TM)
// ----------------------------------------------------------
$('body').on('click', '.requestTypeBtn', function () {
    var value = $(this).data('value') === 'TM' ? 'TM' : 'BASIC';
    $('#request_type').val(value);

    $('.requestTypeBtn').removeClass('is-active').css({
        borderColor: '#d9dce3',
        background: '#fff',
        color: '#333'
    });
    $(this).addClass('is-active').css({
        borderColor: '#3856ff',
        background: '#3856ff',
        color: '#fff'
    });

    if (value === 'TM') {
        $('#requestTypeDesc').text('TM 요청은 1건당 30크레딧이 차감되며, 24시간 동안 상대 기업 응답이 없으면 운영팀이 오프라인 연락을 지원합니다.');
    } else {
        $('#requestTypeDesc').text('기본 요청은 상대 기업에 매칭 요청만 전달되며 1건당 10크레딧이 차감됩니다.');
    }
});

$('body').on('change', 'input[name="biz_role_ui"]', function () {
    renderMatchingTemplateCards();
    updateMatchingMessageContent();
});

$('body').on('click', '.req-tpl-card', function (e) {
    if ($(e.target).is('input')) return;
    var $chk = $(this).find('.reqTplChk');
    $chk.prop('checked', !$chk.prop('checked')).trigger('change');
});

$('body').on('change', '.reqTplChk', function () {
    $(this).closest('.req-tpl-card').toggleClass('is-on', $(this).is(':checked'));
    updateMatchingMessageContent();
});

$('body').on('input', '#match_content_extra', function () {
    updateMatchingMessageContent();
});

if (window.CompanyDetailActions && typeof window.CompanyDetailActions.init === 'function') {
    window.CompanyDetailActions.init({
        getSetUrl: '/get_set.php',
        popupSelector: '.popup_submit',
        requestTypeSelector: '#request_type',
        receiverNameSelector: '#recv',
        planUrl: '/ko/plan.php',
        loginUrl: function () {
            return '/bbs/login.php?url=' + encodeURIComponent(location.pathname + location.search);
        },
        beforeSubmit: function () {
            updateMatchingMessageContent();
            if (!$.trim($('#match_content').val())) {
                if (typeof jAlert === 'function') jAlert('매칭 메시지 선택(선택) 또는 추가 요청사항(선택) 중 1개 이상 입력해 주세요.', '알림');
                else alert('매칭 메시지 선택(선택) 또는 추가 요청사항(선택) 중 1개 이상 입력해 주세요.');
                return false;
            }
            return true;
        },
        onPopupReady: function (kedcd, parts) {
            $('#match_content_extra').val('');
            renderMatchingTemplateCards();
            updateMatchingMessageContent();
            if (parts && parts[2]) $('#recv').text(parts[2]);
        }
    });
}
</script>

<?php include_once(G5_THEME_PATH.'/tail.php'); ?>
