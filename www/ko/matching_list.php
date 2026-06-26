<?php
// ============================================================
// matching_list.php
// ============================================================

include_once("./_common.php");
$is_matching_list_api = defined('MATCHING_LIST_API_MODE') || (isset($_GET['ajax']) && (string)$_GET['ajax'] === '1');

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
add_stylesheet('<link rel="stylesheet" href="'.G5_URL.'/ko/css/badges.css?ver='.G5_CSS_VER.'">', 210);
add_stylesheet('<link rel="stylesheet" href="'.G5_URL.'/ko/css/company-card.css?ver='.G5_CSS_VER.'">', 211);
add_stylesheet('<link rel="stylesheet" href="'.G5_URL.'/ko/css/favorite.css?ver='.G5_CSS_VER.'">', 212);
add_stylesheet('<link rel="stylesheet" href="'.G5_URL.'/ko/css/section-header.css?ver='.G5_CSS_VER.'">', 213);
add_stylesheet('<link rel="stylesheet" href="'.G5_URL.'/ko/css/list-sidebar.css?ver='.G5_CSS_VER.'">', 214);
add_stylesheet('<link rel="stylesheet" href="'.G5_URL.'/ko/css/trade_chat_modal.css?ver='.G5_CSS_VER.'">', 215);
add_stylesheet('<link rel="stylesheet" href="'.G5_URL.'/ko/css/matching_list.css?ver='.$matching_list_css_ver.'">', 220);
if (!$is_matching_list_api) {
    include_once(G5_THEME_PATH.'/head.php');
}

// ----------------------------------------------------------
// 3. 카테고리 정의
// ----------------------------------------------------------
$list_categories = include __DIR__ . '/matching_categories.php';

// ----------------------------------------------------------
// 4. 요청 파라미터 처리
// ----------------------------------------------------------
$current_cat  = isset($_GET['category']) ? $_GET['category'] : (isset($_GET['cat']) ? $_GET['cat'] : 'all');
$current_sort = isset($_GET['sort']) ? $_GET['sort'] : 'rank';
if (!in_array($current_sort, ['rank', 'grow', 'profit', 'stable'], true)) $current_sort = 'rank';
$mq = '';
if (isset($_GET['section'])) $mq .= 'section=' . urlencode($_GET['section']) . '&';
$mq .= 'sort=' . urlencode($current_sort) . '&';
$mq_cat = '';
if (isset($_GET['section'])) $mq_cat .= 'section=' . urlencode($_GET['section']) . '&';
$mq_cat .= 'sort=rank&';

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

$free_limit   = 12;
// 기본 렌더/더보기 단위 크기
$default_page_size = 12;
$max_page_size = 100;
// 더보기 권한: 비회원은 기존 제한 유지, 로그인 회원은 멤버십/결제 여부와 무관하게 허용
$can_load_more = !empty($member['mb_id']);
$page_size_req = isset($_GET['page_size']) ? (int)$_GET['page_size'] : $default_page_size;
if ($page_size_req < $default_page_size) $page_size_req = $default_page_size;
if ($page_size_req > $max_page_size) $page_size_req = $max_page_size;
$page_size = $page_size_req;
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

function matching_list_render_badges($company_row) {
    $badge_growth = format_badge_percent($company_row['badge_growth'] ?? null);
    $badge_stability = format_badge_percent($company_row['badge_stability'] ?? null);
    $badge_venture = strtoupper(trim((string)($company_row['badge_venture'] ?? '')));

    $parts = array();
    if ($badge_growth !== '') {
        $parts[] = '<span class="mCompanyCard_badge mCompanyCard_badge_top_growth">성장TOP' . htmlspecialchars($badge_growth, ENT_QUOTES, 'UTF-8') . '%</span>';
    }
    if ($badge_stability !== '') {
        $parts[] = '<span class="mCompanyCard_badge mCompanyCard_badge_top_stability">재무안정TOP' . htmlspecialchars($badge_stability, ENT_QUOTES, 'UTF-8') . '%</span>';
    }
    if ($badge_venture === 'Y') {
        $parts[] = '<span class="mCompanyCard_badge mCompanyCard_badge_venture">벤처인증</span>';
    }

    if (!$parts) {
        return '<span class="mCompanyCard_badge mCompanyCard_badge_placeholder">뱃지 준비중</span>';
    }

    return implode('', $parts);
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

// 사업자번호 비교를 위해 숫자만 남긴다.
function normalize_bzno_for_unlock($bzno) {
    return preg_replace('/[^0-9]/', '', (string)$bzno);
}

// 로그인 회원이 크레딧 사용 완료한 거래요청의 target_bzno 목록을 조회한다.
function fetch_unlocked_bzno_map_for_member($memberId) {
    $memberId = trim((string)$memberId);
    if ($memberId === '') return [];

    $map = [];
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

// 해제 목록 여부에 따라 회사명을 서버에서 확정한다(마스킹 대상은 원본 미노출).
function build_company_title_for_list($rawName, $bzno, $unlocked_bzno_map) {
    $rawName = trim((string)$rawName);
    if ($rawName === '') return '';

    $normalizedBzno = normalize_bzno_for_unlock($bzno);
    if ($normalizedBzno !== '' && !empty($unlocked_bzno_map[$normalizedBzno])) {
        return $rawName;
    }

    return mask_company_name_like_mock($rawName);
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

// keywords_json(JSON 배열)에서 태그 목록 추출
function parse_keywords_json_tags($keywords_json) {
    $keywords_json = trim((string)$keywords_json);
    if ($keywords_json === '') return [];

    $decoded = json_decode($keywords_json, true);
    if (!is_array($decoded)) return [];

    $result = [];
    foreach ($decoded as $kw) {
        $trimmed = trim((string)$kw);
        if ($trimmed === '') continue;
        $result[] = $trimmed;
    }
    return array_values(array_unique($result));
}

// keyword_count가 있으면 우선 사용, 없으면 파싱된 태그 개수 사용
function resolve_keyword_tag_total_count($keyword_count, $tags) {
    $count = (int)$keyword_count;
    if ($count > 0) return $count;
    return count(is_array($tags) ? $tags : []);
}

// 현재 카테고리의 prefix/exclude 규칙 찾기
function get_target_prefix_rules_by_category($list_categories, $current_cat) {
    if (empty($current_cat) || $current_cat === 'all') return [];
    $rules = [];
    foreach ($list_categories as $lg) {
        if ($lg['slug'] === $current_cat) {
            foreach ($lg['sub'] as $sub) {
                $prefixes = isset($sub['prefixes']) && is_array($sub['prefixes']) ? $sub['prefixes'] : [];
                if (!empty($sub['code'])) $prefixes[] = $sub['code']; // backward compatibility
                if ($prefixes) {
                    $rules[] = [
                        'prefixes' => $prefixes,
                        'excludes' => isset($sub['excludes']) && is_array($sub['excludes']) ? $sub['excludes'] : [],
                    ];
                }
            }
            break;
        }
        foreach ($lg['sub'] as $sub) {
            if ($sub['slug'] === $current_cat) {
                $prefixes = isset($sub['prefixes']) && is_array($sub['prefixes']) ? $sub['prefixes'] : [];
                if (!empty($sub['code'])) $prefixes[] = $sub['code']; // backward compatibility
                if ($prefixes) {
                    $rules[] = [
                        'prefixes' => $prefixes,
                        'excludes' => isset($sub['excludes']) && is_array($sub['excludes']) ? $sub['excludes'] : [],
                    ];
                }
                break 2;
            }
        }
    }
    return $rules;
}

// company 조회 WHERE 만들기
function build_company_where_clause($target_rules, $restrict_cg_prefix = false) {
    $where_clause = "WHERE A.KEDCD != '' AND A.ENP_NM != ''";
    $where_clause .= " AND A.ENP_SCD = '01'";
    $where_clause .= " AND A.ENP_SZE IN ('02', '03', '06')";
    if ($restrict_cg_prefix) {
        $where_clause .= " AND (A.BZC_CD LIKE 'C%' OR A.BZC_CD LIKE 'G%')";
    }
    if (!empty($target_rules)) {
        $rule_conds = [];
        foreach ($target_rules as $rule) {
            $prefixes = isset($rule['prefixes']) && is_array($rule['prefixes']) ? $rule['prefixes'] : [];
            $excludes = isset($rule['excludes']) && is_array($rule['excludes']) ? $rule['excludes'] : [];
            $prefix_conds = [];
            foreach ($prefixes as $tc) {
                $tc = trim((string)$tc);
                if ($tc === '') continue;
                $prefix_conds[] = "A.BZC_CD LIKE '" . sql_real_escape_string($tc) . "%'";
            }
            if (!$prefix_conds) continue;

            $cond = "(" . implode(" OR ", $prefix_conds) . ")";
            foreach ($excludes as $ex) {
                $ex = trim((string)$ex);
                if ($ex === '') continue;
                $cond .= " AND A.BZC_CD NOT LIKE '" . sql_real_escape_string($ex) . "%'";
            }
            $rule_conds[] = "(" . $cond . ")";
        }
        if ($rule_conds) {
            $where_clause .= " AND (" . implode(" OR ", $rule_conds) . ")";
        }
    }
    return $where_clause;
}

// 정렬 기준에 맞는 ORDER BY 만들기
function build_company_order_clause($sort_key) {
    switch ($sort_key) {
        case 'grow':
            return "
                (S.growth_score_5 IS NULL) ASC,
                S.growth_score_5 DESC,
                (S.final_total_score_5 IS NULL) ASC,
                S.final_total_score_5 DESC,
                A.ENP_NM ASC
            ";
        case 'stable':
            return "
                (S.stability_score_5 IS NULL) ASC,
                S.stability_score_5 DESC,
                (S.final_total_score_5 IS NULL) ASC,
                S.final_total_score_5 DESC,
                A.ENP_NM ASC
            ";
        case 'rank':
        default:
            return "
                (S.final_total_score_5 IS NULL) ASC,
                S.final_total_score_5 DESC,
                A.ENP_NM ASC
            ";
    }
}

function matching_list_count_cache_get($cache_key, $ttl_sec = 45) {
    $cache_dir = G5_DATA_PATH . '/cache/matching_list';
    if (!is_dir($cache_dir)) {
        @mkdir($cache_dir, 0777, true);
    }
    $cache_file = $cache_dir . '/count_' . md5($cache_key) . '.php';
    if (!is_file($cache_file)) return null;
    if ((time() - @filemtime($cache_file)) > (int)$ttl_sec) return null;

    $raw = @file_get_contents($cache_file);
    if ($raw === false) return null;
    $payload = @json_decode($raw, true);
    if (!is_array($payload) || !isset($payload['cnt'])) return null;
    return (int)$payload['cnt'];
}

function matching_list_count_cache_set($cache_key, $count) {
    $cache_dir = G5_DATA_PATH . '/cache/matching_list';
    if (!is_dir($cache_dir)) {
        @mkdir($cache_dir, 0777, true);
    }
    $cache_file = $cache_dir . '/count_' . md5($cache_key) . '.php';
    $payload = json_encode(['cnt' => (int)$count], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    @file_put_contents($cache_file, $payload, LOCK_EX);
}

function matching_list_payload_cache_get($cache_key, $ttl_sec = 20) {
    $cache_dir = G5_DATA_PATH . '/cache/matching_list';
    if (!is_dir($cache_dir)) {
        @mkdir($cache_dir, 0777, true);
    }
    $cache_file = $cache_dir . '/payload_' . md5($cache_key) . '.json';
    if (!is_file($cache_file)) return null;
    if ((time() - @filemtime($cache_file)) > (int)$ttl_sec) return null;

    $raw = @file_get_contents($cache_file);
    if ($raw === false || $raw === '') return null;
    $payload = @json_decode($raw, true);
    if (!is_array($payload) || empty($payload['ok'])) return null;
    return $payload;
}

function matching_list_payload_cache_set($cache_key, $payload) {
    if (!is_array($payload) || empty($payload['ok'])) return;
    $cache_dir = G5_DATA_PATH . '/cache/matching_list';
    if (!is_dir($cache_dir)) {
        @mkdir($cache_dir, 0777, true);
    }
    $cache_file = $cache_dir . '/payload_' . md5($cache_key) . '.json';
    $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) return;
    @file_put_contents($cache_file, $json, LOCK_EX);
}

// 일반 리스트 카드와 총 개수 조회
function fetch_general_cards_by_where($where_clause, $query_type, $show_count_req, $sort_key = 'rank', $offset = 0, $unlocked_bzno_map = []) {
    $cards = [];
    $display_cap = 10000;
    $requested_limit = max(1, (int)$show_count_req);
    $requested_offset = max(0, (int)$offset);

    $count_cache_key = $query_type . '|' . $where_clause;
    $cards_total_count = matching_list_count_cache_get($count_cache_key, 45);
    if ($cards_total_count === null) {
        $count_row = sql_fetch("
            SELECT COUNT(*) AS cnt
            FROM company A
            {$where_clause}
        ");
        $cards_total_count = (int)($count_row['cnt'] ?? 0);
        matching_list_count_cache_set($count_cache_key, $cards_total_count);
    }

    $order_clause = build_company_order_clause($sort_key);

    $sql_list_base = "
        SELECT A.KEDCD, A.BZNO, A.ENP_NM, A.LOC_ADDRA, A.BZC_CD, A.keywords_json AS keywords_json, A.keyword_count AS keyword_count, A.VENP_YN, B.k_code_name,
            S.growth_score_5, S.profit_score_5, S.stability_score_5, S.age_score_5, S.emp_score_5, S.final_total_score_5,
            S.badge_growth, S.badge_stability, S.badge_venture
        FROM   company A
        LEFT JOIN kssc_11 B ON A.BZC_CD = B.k_code
        LEFT JOIN company_score_final_dedup S ON A.KEDCD = S.KEDCD
        {$where_clause}
        ORDER BY {$order_clause}
    ";

    $append_rows = function ($sql) use (&$cards, $query_type, $unlocked_bzno_map) {
        $res_list = sql_query($sql);
        while ($row = sql_fetch_array($res_list)) {
            $title = build_company_title_for_list($row['ENP_NM'] ?? '', $row['BZNO'] ?? '', $unlocked_bzno_map);
            $tags = parse_keywords_json_tags($row['keywords_json'] ?? '');
            $cards[] = [
                'type'    => $query_type,
                'title'   => $title,
                'tags'    => $tags,
                'tag_total_count' => resolve_keyword_tag_total_count($row['keyword_count'] ?? 0, $tags),
                'score'   => calc_score_avg($row['final_total_score_5']),
                'grow'    => $row['growth_score_5'],
                'profit'  => $row['profit_score_5'],
                'stable'  => $row['stability_score_5'],
                'age_score' => $row['age_score_5'],
                'emp_score' => $row['emp_score_5'],
                'company' => $title,
                'kedcd'   => $row['KEDCD'],
                'venp_yn' => $row['VENP_YN'],
                'badge_growth' => $row['badge_growth'],
                'badge_stability' => $row['badge_stability'],
                'badge_venture' => $row['badge_venture'],
            ];
        }
    };

    // 1차: 성능 우선 구간(상위 10,000건)에서 먼저 로드
    if ($requested_offset < $display_cap) {
        $first_limit = min($requested_limit, $display_cap - $requested_offset);
        if ($first_limit > 0) {
            $append_rows($sql_list_base . " LIMIT {$requested_offset}, {$first_limit}");
        }

        // 2차: 10,000건 구간이 소진되면 잔여분을 이후 구간에서 이어서 로드
        $remain = $requested_limit - count($cards);
        if ($remain > 0 && $cards_total_count > $display_cap) {
            $next_offset = max($display_cap, $requested_offset + count($cards));
            $append_rows($sql_list_base . " LIMIT {$next_offset}, {$remain}");
        }
    } else {
        // 10,000건 이후 구간 직접 조회
        $append_rows($sql_list_base . " LIMIT {$requested_offset}, {$requested_limit}");
    }

    return [
        'cards' => $cards,
        'total_count' => $cards_total_count,
    ];
}

function matching_list_render_cards_grid_html($cards) {
    if (empty($cards) || !is_array($cards)) {
        return '<div style="grid-column: 1/-1; padding: 100px 0; text-align: center; color: #888;">해당 카테고리에 등록된 기업이 없습니다.</div>';
    }

    ob_start();
    foreach ($cards as $idx => $c):
        $scoreValue = isset($c['score']) && is_numeric($c['score']) ? (float)$c['score'] : 0.0;
        $growScore = isset($c['grow']) && is_numeric($c['grow']) ? (float)$c['grow'] : 0.0;
        $profitScore = isset($c['profit']) && is_numeric($c['profit']) ? (float)$c['profit'] : 0.0;
        $stableScore = isset($c['stable']) && is_numeric($c['stable']) ? (float)$c['stable'] : 0.0;

        $careerScore = isset($c['age_score']) && is_numeric($c['age_score']) ? (float)$c['age_score'] : 0.0;
        $employScore = isset($c['emp_score']) && is_numeric($c['emp_score']) ? (float)$c['emp_score'] : 0.0;
        $radarScores = [$growScore, $profitScore, $stableScore, $careerScore, $employScore];
        $radarSvg = build_radar_svg_5($radarScores);
        $starsHtml = render_stars_html($scoreValue);

        $allTags = is_array($c['tags'] ?? null) ? $c['tags'] : [];
        $showNum = 3;
        $displayTags = array_slice($allTags, 0, $showNum);
        $totalCount = isset($c['tag_total_count']) && is_numeric($c['tag_total_count'])
            ? max(0, (int)$c['tag_total_count'])
            : count($allTags);
        $restCount = max($totalCount - count($displayTags), 0);
        ?>
        <a href="/companies/detail?id=<?php echo urlencode(simple_crypt($c['kedcd'] ?? '')); ?>"
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
                    <?php echo matching_list_render_badges($c); ?>
                </div>
                <h3 class="mCompanyCard_title"><?php echo htmlspecialchars($c['title'] ?? ''); ?></h3>

                <p class="mCompanyCard_desc">
                    <?php if ($totalCount > 0): ?>
                        <?php foreach ($displayTags as $t): ?>
                            <span class="mCompanyCard_desc_tag"><?php echo htmlspecialchars($t); ?></span>
                        <?php endforeach; ?>
                        <?php if ($restCount > 0): ?>
                            <span class="mCompanyCard_desc_tag">+<?php echo $restCount; ?></span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="mCompanyCard_desc_tag">정보 준비중</span>
                    <?php endif; ?>
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
        <?php
    endforeach;

    return ob_get_clean();
}

$unlocked_bzno_map = fetch_unlocked_bzno_map_for_member($member['mb_id'] ?? '');

$cards = [];
$cards_total_count = 0;

$should_use_api_payload_cache = (
    $is_matching_list_api
    && !$has_comnum
    && $offset_req === 0
);
$api_payload_cache_key = '';
if ($should_use_api_payload_cache) {
    $api_payload_cache_key = implode('|', array(
        'v2',
        'matching_list_api',
        'member:' . (string)($member['mb_id'] ?? ''),
        'cat:' . (string)$current_cat,
        'sort:' . (string)$current_sort,
        'page_size:' . (int)$page_size
    ));
    $cached_payload = matching_list_payload_cache_get($api_payload_cache_key, 20);
    if (is_array($cached_payload)) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($cached_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return;
    }
}

if (!$has_comnum) {
    // 7-1. 사업자번호 미입력: 카테고리 기준 일반 기업 조회
    $query_type = 'company';
    $buyer_rules = get_target_prefix_rules_by_category($list_categories, $current_cat);
    $where_clause = build_company_where_clause($buyer_rules, false);
    $general_result = fetch_general_cards_by_where($where_clause, $query_type, $show_count_req, $current_sort, $offset_req, $unlocked_bzno_map);
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

    $resolved_company = macomaex_kodata_resolve_company($comnum, array(
        'persist_company' => false,
        'context' => 'matching_list',
    ));
    if (!empty($resolved_company['success']) && !empty($resolved_company['json'])) {
        $cominfo = $resolved_company['json'];
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
                    SELECT A.*, B.BZNO, B.BZC_CD, B.LOC_ADDRA, B.keywords_json AS keywords_json, B.keyword_count AS keyword_count, B.VENP_YN, C.k_code_name,
                           S.growth_score_5, S.profit_score_5, S.stability_score_5, S.age_score_5, S.emp_score_5, S.final_total_score_5,
                           MAX(S.badge_growth) AS badge_growth,
                           MAX(S.badge_stability) AS badge_stability,
                           MAX(S.badge_venture) AS badge_venture
                    FROM   company_client A
                    LEFT JOIN company B ON A.TXPL_KEDCD = B.KEDCD
                    LEFT JOIN kssc_11 C ON B.BZC_CD = C.k_code
                    LEFT JOIN company_score_final_dedup S ON A.TXPL_KEDCD = S.KEDCD
                    WHERE  A.KEDCD IN ('{$REC}')
                      AND  A.TXPL_KEDCD != ''
                    GROUP BY A.TXPL_KEDCD, S.growth_score_5, S.profit_score_5, S.stability_score_5, S.age_score_5, S.emp_score_5, S.final_total_score_5
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
    $map_cards = function($rows, $type) use ($unlocked_bzno_map) {
        $out = [];
        foreach ($rows as $row) {
            if (empty($row['TXPL_KEDCD']) || empty($row['TXPL_NM'])) continue;
            $title = build_company_title_for_list($row['TXPL_NM'] ?? '', $row['BZNO'] ?? '', $unlocked_bzno_map);
            $tags = parse_keywords_json_tags($row['keywords_json'] ?? '');
            $out[] = [
                'type'    => $type,
                'title'   => $title,
                'tags'    => $tags,
                'tag_total_count' => resolve_keyword_tag_total_count($row['keyword_count'] ?? 0, $tags),
                'score'   => calc_score_avg($row['final_total_score_5']),
                'grow'    => $row['growth_score_5'],
                'profit'  => $row['profit_score_5'],
                'stable'  => $row['stability_score_5'],
                'age_score' => $row['age_score_5'],
                'emp_score' => $row['emp_score_5'],
                'company' => $title,
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
    $buyer_total_count = $cards_total_count;
    $seller_total_count = 0;
}
$list_total_count = !$has_comnum ? $cards_total_count : count($cards);

if ($is_matching_list_api) {
    $grid_html = matching_list_render_cards_grid_html($cards);
    $api_payload = [
        'ok' => true,
        'gridHtml' => $grid_html,
        'totalByType' => [
            'buyer' => (int)$buyer_total_count,
            'seller' => (int)$seller_total_count,
        ],
        'totalCount' => (int)$list_total_count,
        'currentSort' => $current_sort,
        'pageSize' => (int)$page_size,
    ];
    if ($should_use_api_payload_cache && $api_payload_cache_key !== '') {
        matching_list_payload_cache_set($api_payload_cache_key, $api_payload);
    }
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($api_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return;
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

$ai_recommend_href = '/recommendations/demo';
if ($has_comnum) {
    $ai_recommend_href = '/recommendations?comnum=' . urlencode($input_comnum);
}

$current_category_label = '전체 업종';
$current_category_group_slug = '';
$current_category_group_label = '';
foreach ($list_categories as $lg) {
    if ($current_cat === $lg['slug']) {
        $current_category_label = $lg['label'];
        $current_category_group_slug = $lg['slug'];
        $current_category_group_label = $lg['label'];
        break;
    }

    foreach ($lg['sub'] as $sub) {
        if ($current_cat === $sub['slug']) {
            $current_category_label = $sub['label'];
            $current_category_group_slug = $lg['slug'];
            $current_category_group_label = $lg['label'];
            break 2;
        }
    }
}
?>

<div id="listWrap">
<!-- =========================================================
     모바일 전용: 업종별 카테고리 드롭다운/탭
     ========================================================= -->
<div class="list-cat-tab-wrap">
    <div class="list-cat-dropdown" id="catDropdown">
        <button type="button"
                class="list-cat-dropdown_btn"
                id="catDropdownBtn"
                aria-haspopup="true"
                aria-expanded="false">
            <span class="list-cat-dropdown_label">
                <span class="list-cat-dropdown_overline"><?php echo htmlspecialchars($current_category_group_label ?: '업종별 탐색'); ?></span>
                <strong><?php echo htmlspecialchars($current_category_label); ?></strong>
            </span>
            <svg class="list-cat-dropdown_chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <polyline points="6 9 12 15 18 9"/>
            </svg>
        </button>
        <div class="list-cat-dropdown_panel" id="catDropdownPanel">
            <a href="/companies?<?php echo $mq_cat; ?>category=all"
               class="list-cat-dropdown_all <?php echo ($current_cat === 'all' || $current_cat === '') ? 'on' : ''; ?>">
                전체 업종
            </a>
            <div class="list-cat-dropdown_groups">
                <?php foreach ($list_categories as $lg): ?>
                <div class="list-cat-dropdown_group <?php echo ($current_category_group_slug === $lg['slug']) ? 'is-active' : ''; ?>">
                    <a href="/companies?<?php echo $mq_cat; ?>category=<?php echo urlencode($lg['slug']); ?>"
                       class="list-cat-dropdown_group_name <?php echo ($current_cat === $lg['slug']) ? 'on' : ''; ?>">
                        <?php echo htmlspecialchars($lg['label']); ?>
                    </a>
                    <div class="list-cat-dropdown_subs">
                        <?php foreach ($lg['sub'] as $sub): ?>
                        <a href="/companies?<?php echo $mq_cat; ?>category=<?php echo urlencode($sub['slug']); ?>"
                           class="<?php echo ($current_cat === $sub['slug']) ? 'on' : ''; ?>">
                            <?php echo htmlspecialchars($sub['label']); ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
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
                <a href="<?php echo htmlspecialchars($ai_recommend_href, ENT_QUOTES, 'UTF-8'); ?>" class="list-sidebar_ai <?php echo $has_comnum ? 'on' : ''; ?>">
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
                <a href="/companies?<?php echo $mq_cat; ?>category=all"
                   class="list-sidebar_all <?php echo ($current_cat === 'all' || $current_cat === '') ? 'on' : ''; ?>">
                    전체 업종
                </a>
                <?php foreach ($list_categories as $lg):
                    // 수정된 로직: 현재 카테고리가 본인이거나 하위 카테고리인 경우 그룹을 엽니다.
                    $is_open          = ($open_group_slug === $lg['slug']);
                    $is_current_group = ($current_cat === $lg['slug']);
                    $group_icon = !empty($lg['icon']) ? $lg['icon'] : (isset($sidebar_icon_map[$lg['slug']]) ? $sidebar_icon_map[$lg['slug']] : 'cat-iso-all');
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
                            <a href="/companies?<?php echo $mq_cat; ?>category=<?php echo urlencode($lg['slug']); ?>"
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
                            <a href="/companies?<?php echo $mq_cat; ?>category=<?php echo urlencode($sub['slug']); ?>"
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
                    <p class="list-count">
                        <?php if (!empty($_GET['comnum'])): ?>
                        <span class="list-search-query">
                            사업자번호 <?php echo htmlspecialchars($_GET['comnum']); ?> 검색 ·&nbsp;
                        </span>
                        <?php endif; ?>
                        <strong><?php echo (int)$list_total_count; ?></strong>개의 기업
                    </p>
                </div>
                <div class="list-sort">
                    <select id="sortSelect" aria-label="정렬 기준">
                        <option value="rank" <?php echo $current_sort === 'rank' ? 'selected' : ''; ?>>랭킹순</option>
                        <option value="grow" <?php echo $current_sort === 'grow' ? 'selected' : ''; ?>>성장성순</option>
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
                            <li class="sort-dropdown-item<?php echo $current_sort === 'stable' ? ' selected' : ''; ?>" role="option" data-val="stable">안정성순</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="list-grid">
                <?php echo matching_list_render_cards_grid_html($cards); ?>
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
        'icon' => isset($lg['icon']) ? $lg['icon'] : '',
        'sub' => $subs,
    ];
}

$matching_list_js_config = [
    'freeLimit' => (int)$free_limit,
    'pageSize' => (int)$page_size,
    'canLoadMore' => (bool)$can_load_more,
    'isServerPaged' => !$has_comnum,
    'totalByType' => [
        'buyer' => (int)$buyer_total_count,
        'seller' => (int)$seller_total_count,
    ],
    'totalCount' => (int)$list_total_count,
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
