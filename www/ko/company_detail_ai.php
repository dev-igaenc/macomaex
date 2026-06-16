<?php
include_once('./_common.php');
include_once(__DIR__ . '/helper/trade_request_helper.php');
include_once(__DIR__ . '/helper/company_detail_helper.php');
include_once(__DIR__ . '/helper/llm_report_trade_helper.php');

$should_open_trade_request_modal = false;
if (isset($_GET['trade_request_login']) && $_GET['trade_request_login'] === '1') {
    login_chk();
    $should_open_trade_request_modal = !empty($member['mb_id']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'trade_request_verify_bzno') {
        company_detail_handle_trade_request_verify_bzno($member, $_POST);
    }
    if ($_POST['action'] === 'trade_request_insert') {
        company_detail_handle_trade_request_insert($member, $_POST);
    }
}

// TOP 배지 퍼센트 표기를 화면용 문자열로 정규화
function company_detail_ai_format_badge_percent($value)
{
    if ($value === null || $value === '') return '';
    $str = trim((string)$value);
    if ($str === '') return '';
    if (!is_numeric($str)) return $str;
    $num = (float)$str;
    if ($num == (int)$num) return (string)((int)$num);
    return rtrim(rtrim(number_format($num, 2, '.', ''), '0'), '.');
}

// keywords_json(JSON 배열)을 태그 배열로 변환
function company_detail_ai_parse_keywords_json($keywords_json)
{
    $keywords_json = trim((string)$keywords_json);
    if ($keywords_json === '') return array();

    $decoded = json_decode($keywords_json, true);
    if (!is_array($decoded)) return array();

    $tags = array();
    foreach ($decoded as $part) {
        $tag = trim((string)$part);
        if ($tag === '') continue;
        if ($tag === '' || in_array($tag, $tags, true)) continue;
        $tags[] = $tag;
    }
    return $tags;
}

function company_detail_ai_resolve_tag_total_count($keyword_count, $tags)
{
    $count = (int)$keyword_count;
    if ($count > 0) return $count;
    return count(is_array($tags) ? $tags : array());
}

function company_detail_ai_display_value($value)
{
    $value = trim((string)$value);
    return ($value !== '') ? $value : '정보 준비중';
}

function company_detail_ai_extract_report_from_llm_log_content($raw_content)
{
    $raw_content = trim((string)$raw_content);
    if ($raw_content === '') return '';
    $decoded = json_decode($raw_content, true);
    if (!is_array($decoded)) return '';
    $status = strtoupper(trim((string)($decoded['status'] ?? '')));
    $report = trim((string)($decoded['report'] ?? ''));
    // 성공 로그의 report 본문만 즉시표시 대상으로 사용
    if ($status === 'SUCCESS' && $report !== '') return $report;
    return '';
}

// 상세 대상 기업 식별자(kedcd)는 일반 상세와 동일하게 cd(암호화 기업코드)를 우선 사용한다.
$cd = isset($_GET['cd']) ? trim((string)$_GET['cd']) : '';
$kedcd = $cd ? simple_crypt($cd, 'd') : '';

// 하위호환: 기존 kedcd 또는 id(10자리 숫자) 파라미터도 허용한다.
if (!preg_match('/^\d{10}$/', $kedcd)) {
    $kedcd = isset($_GET['kedcd']) ? preg_replace('/\D/', '', (string)$_GET['kedcd']) : '';
    if ($kedcd === '' && isset($_GET['id'])) {
        $id = preg_replace('/\D/', '', (string)$_GET['id']);
        if (strlen($id) === 10) $kedcd = $id;
    }
}

// 잘못된 식별자로 직접 진입한 경우 목록으로 되돌린다.
if (strlen($kedcd) !== 10 || preg_match('/^0+$/', $kedcd)) {
    alert('잘못된 접근입니다. AI 추천 목록에서 다시 선택해 주세요.', '/recommendations');
}
// 상세 진입 trade_type(1=구매처, 2=판매처)에 따라 상단 라벨을 분기한다.
$detail_trade_type = isset($_GET['trade_type']) ? (int)$_GET['trade_type'] : 1;
if ($detail_trade_type !== 2) $detail_trade_type = 1;
$detail_trade_type_label = ($detail_trade_type === 2) ? '판매처 추천' : '구매처 추천';
$detail_selected_target_keywords = array_values(array_filter(array_map('trim', preg_split('/\s*,\s*/u', (string)($_GET['selected_keyword'] ?? ''), -1, PREG_SPLIT_NO_EMPTY))));
$detail_selected_partner_bzc_cds = array_values(array_filter(array_map(function ($value) {
    return strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string)$value), 0, 10));
}, preg_split('/\s*,\s*/', (string)($_GET['partner_bzc'] ?? ''), -1, PREG_SPLIT_NO_EMPTY))));
$detail_recommendation_mode = trim((string)($_GET['recommendation_mode'] ?? 'full'));
if (!in_array($detail_recommendation_mode, array('keyword', 'full'), true)) $detail_recommendation_mode = 'full';
$detail_trace_id = function_exists('company_list_ai_normalize_trace_id')
    ? company_list_ai_normalize_trace_id($_GET['trace_id'] ?? '')
    : substr(preg_replace('/[^A-Za-z0-9_-]/', '', (string)($_GET['trace_id'] ?? '')), 0, 64);

$company = array();
// 상세 기본 정보 조회
$company = sql_fetch("\n    SELECT KEDCD, ENP_NM, BZNO, keywords_json, keyword_count, BZC_CD, ENP_TYP, LABORER_SUM, ESTB_DT, LOC_ADDRA, LOC_ADDRB, HPAGE_URL\n    FROM company\n    WHERE KEDCD = '" . sql_escape_string($kedcd) . "'\n    LIMIT 1\n");
if (empty($company['KEDCD'])) {
    alert('기업 정보를 찾을 수 없습니다.', '/recommendations');
}

$is_favorite_company = false;
if (!empty($member['mb_id'])) {
    $favorite_row = sql_fetch("
        SELECT ms_id
        FROM g5_favorite
        WHERE mb_id = '" . sql_escape_string((string)$member['mb_id']) . "'
          AND KEDCD = '" . sql_escape_string($kedcd) . "'
        LIMIT 1
    ");
    $is_favorite_company = !empty($favorite_row['ms_id']);
}

$company_name = trim((string)($company['ENP_NM'] ?? ''));
$g5['title'] = 'AI 추천 기업 상세';

$design_tokens_ver = file_exists(__DIR__ . '/css/design-tokens.css') ? filemtime(__DIR__ . '/css/design-tokens.css') : time();
$badges_css_ver = file_exists(__DIR__ . '/css/badges.css') ? filemtime(__DIR__ . '/css/badges.css') : time();
$favorite_css_ver = file_exists(__DIR__ . '/css/favorite.css') ? filemtime(__DIR__ . '/css/favorite.css') : time();
$confirm_modal_css_ver = file_exists(__DIR__ . '/css/confirm-modal.css') ? filemtime(__DIR__ . '/css/confirm-modal.css') : time();
$share_css_ver = file_exists(__DIR__ . '/css/share.css') ? filemtime(__DIR__ . '/css/share.css') : time();
$ai_loading_css_ver = file_exists(__DIR__ . '/css/ai-loading.css') ? filemtime(__DIR__ . '/css/ai-loading.css') : time();
$auth_toggle_css_ver = file_exists(__DIR__ . '/css/auth-toggle.css') ? filemtime(__DIR__ . '/css/auth-toggle.css') : time();
$company_detail_left_css_ver = file_exists(__DIR__ . '/css/company-detail-left.css') ? filemtime(__DIR__ . '/css/company-detail-left.css') : time();
$company_detail_ai_css_ver = file_exists(__DIR__ . '/css/company_detail_ai.css') ? filemtime(__DIR__ . '/css/company_detail_ai.css') : time();
$match_request_modal_css_ver = file_exists(__DIR__ . '/css/match-request-modal.css') ? filemtime(__DIR__ . '/css/match-request-modal.css') : time();
$top_nav_css_ver = file_exists(__DIR__ . '/css/top-nav.css') ? filemtime(__DIR__ . '/css/top-nav.css') : time();

$badges_js_ver = file_exists(__DIR__ . '/js/badges.js') ? filemtime(__DIR__ . '/js/badges.js') : time();
$company_detail_left_js_ver = file_exists(__DIR__ . '/js/company-detail-left.js') ? filemtime(__DIR__ . '/js/company-detail-left.js') : time();
$company_detail_ai_js_ver = file_exists(__DIR__ . '/js/company_detail_ai.js') ? filemtime(__DIR__ . '/js/company_detail_ai.js') : time();
$favorite_js_ver = file_exists(__DIR__ . '/js/favorite.js') ? filemtime(__DIR__ . '/js/favorite.js') : time();
$share_js_ver = file_exists(__DIR__ . '/js/share.js') ? filemtime(__DIR__ . '/js/share.js') : time();
$confirm_modal_js_ver = file_exists(__DIR__ . '/js/confirm-modal.js') ? filemtime(__DIR__ . '/js/confirm-modal.js') : time();
$ai_loading_js_ver = file_exists(__DIR__ . '/js/ai-loading.js') ? filemtime(__DIR__ . '/js/ai-loading.js') : time();
$match_request_modal_js_ver = file_exists(__DIR__ . '/js/match-request-modal.js') ? filemtime(__DIR__ . '/js/match-request-modal.js') : time();

$default_message = trade_request_default_message();
$founded_year = '';
if (!empty($company['ESTB_DT']) && strlen((string)$company['ESTB_DT']) >= 4) {
    $founded_year = substr((string)$company['ESTB_DT'], 0, 4) . '년';
}
$score = array();
$industry_name = '';
$company_type = '법인기업';
$keywords_json = trim((string)($company['keywords_json'] ?? ''));
$seed_tags = company_detail_ai_parse_keywords_json($keywords_json);
$seed_tag_total_count = company_detail_ai_resolve_tag_total_count($company['keyword_count'] ?? 0, $seed_tags);
$seed_product_line = implode(', ', $seed_tags);

if (strlen($kedcd) === 10) {
    $kedcd_sql = sql_escape_string($kedcd);
    $score = sql_fetch("
        SELECT
            KEDCD,
            total_grade_22,
            growth_score_5,
            profit_score_5,
            stability_score_5,
            badge_growth,
            badge_stability,
            badge_venture,
            final_total_score_5
        FROM company_score_final_dedup
        WHERE KEDCD = '{$kedcd_sql}'
    ");

    if (!empty($company['BZC_CD'])) {
        $bzc_cd_sql = sql_escape_string((string)$company['BZC_CD']);
        $industry_row = sql_fetch("
            SELECT k_code_name
            FROM kssc
            WHERE k_code = '{$bzc_cd_sql}'
            LIMIT 1
        ");
        $industry_name = trim((string)($industry_row['k_code_name'] ?? ''));
    }
}

if (!empty($company['ENP_TYP']) && isset($enp_typt[$company['ENP_TYP']])) {
    $company_type = $enp_typt[$company['ENP_TYP']];
}

$preloaded_ai_report_markdown = '';
$preloaded_ai_once_key = '';
$preloaded_ai_source_context = array();
$src_cd = isset($_GET['src_cd']) ? trim((string)$_GET['src_cd']) : '';
$preload_src_kedcd = $src_cd ? preg_replace('/\D/', '', (string)simple_crypt($src_cd, 'd')) : '';
if (strlen($preload_src_kedcd) !== 10) {
    $preload_src_kedcd = isset($_GET['src_kedcd']) ? preg_replace('/\D/', '', (string)$_GET['src_kedcd']) : '';
}
if (!empty($member['mb_id'])) {
    $preload_trade_type = $detail_trade_type;

    if (strlen($preload_src_kedcd) !== 10) {
        $preload_src_kedcd = preg_replace('/\D/', '', (string)($member['mb_company'] ?? ''));
    }

    if (strlen($preload_src_kedcd) === 10) {
        // 상세 재진입 시에도 AI 리포트 입력값울 복원할 수 있게 의뢰사 컨텍스트 준비
        $preload_src_sql = sql_escape_string($preload_src_kedcd);
        $preload_source_company = sql_fetch("
            SELECT KEDCD, ENP_NM, BZC_CD, ENP_SZE, PD_NM, LOC_ADDRA, keywords_json, keyword_count
            FROM company
            WHERE KEDCD = '{$preload_src_sql}'
            LIMIT 1
        ");
        if (!empty($preload_source_company['KEDCD'])) {
            $preload_source_industry = '';
            if (!empty($preload_source_company['BZC_CD'])) {
                $preload_source_industry_row = sql_fetch("
                    SELECT k_code_name
                    FROM kssc
                    WHERE k_code = '" . sql_escape_string((string)$preload_source_company['BZC_CD']) . "'
                    LIMIT 1
                ");
                $preload_source_industry = trim((string)($preload_source_industry_row['k_code_name'] ?? ''));
            }
            $preload_source_keywords = company_detail_ai_parse_keywords_json((string)($preload_source_company['keywords_json'] ?? ''));
            $preloaded_ai_source_context = array(
                'source_kedcd' => $preload_src_kedcd,
                'source_enp_nm' => trim((string)($preload_source_company['ENP_NM'] ?? '')),
                'source_bzc_cd' => trim((string)($preload_source_company['BZC_CD'] ?? '')),
                'source_bzc_nm' => $preload_source_industry,
                'source_sido' => trim((string)mb_substr((string)($preload_source_company['LOC_ADDRA'] ?? ''), 0, 3)),
                'source_sze' => trim((string)($preload_source_company['ENP_SZE'] ?? '3')),
                'source_mpd' => trim((string)($preload_source_company['PD_NM'] ?? '')),
                'source_sales' => 0,
                'source_cust_names' => array(),
                'source_supp_names' => array(),
                'src_keywords' => $preload_source_keywords,
                'selected_target_keywords' => $detail_selected_target_keywords,
                'selected_partner_bzc_cds' => $detail_selected_partner_bzc_cds,
                'recommendation_mode' => $detail_recommendation_mode,
                'trace_id' => $detail_trace_id,
            );
            if ($preloaded_ai_source_context['source_mpd'] === '' && !empty($preload_source_keywords)) {
                $preloaded_ai_source_context['source_mpd'] = implode(', ', $preload_source_keywords);
            }
        }
    }

    if (strlen($preload_src_kedcd) === 10) {
        // API와 동일 키: 동일 분석 이력에서 최근 성공 리포트 재사용
        $preloaded_ai_once_key = hash('sha256', 'AI_COMPANY_REPORT:' . (string)$member['mb_id'] . ':' . $preload_src_kedcd . ':' . $kedcd . ':' . $preload_trade_type);
        $once_key_sql = sql_escape_string($preloaded_ai_once_key);
        $result_log = sql_query("
            SELECT c_content
            FROM company_llm_log
            WHERE c_once_key = '{$once_key_sql}'
              AND c_trade_type = '{$preload_trade_type}'
              AND c_datetime >= date_sub(now(), interval 1 month)
            ORDER BY c_id DESC
            LIMIT 10
        ", false);

        if ($result_log) {
            // 최근 로그부터 순회하며 즉시표시 가능한 성공 리포트 1건을 선택
            while ($row_log = sql_fetch_array($result_log)) {
                $report_md = company_detail_ai_extract_report_from_llm_log_content($row_log['c_content'] ?? '');
                if ($report_md !== '') {
                    $preloaded_ai_report_markdown = $report_md;
                    break;
                }
            }
        }
    }

    if ($preloaded_ai_report_markdown !== '' && !empty($preloaded_ai_source_context)) {
        // 캐시된 리포트 최신 품목 통제 규칙으로 정규화
        $preloaded_ai_report_markdown = llm_report_trade_sanitize_report_json(
            $preloaded_ai_report_markdown,
            $preloaded_ai_source_context,
            array(
                'bzc_cd' => trim((string)($company['BZC_CD'] ?? '')),
                'bzc_nm' => $industry_name,
                'sido' => trim((string)mb_substr((string)($company['LOC_ADDRA'] ?? ''), 0, 3)),
                'keywords' => $seed_tags,
            ),
            ((int)$preload_trade_type === 2)
        );
    }
}

$is_detail_unlocked = false;
if (!empty($member['mb_id']) && strlen($kedcd) === 10) {
    $unlock_row = sql_fetch("
        SELECT tr_no
        FROM trade_request
        WHERE request_mb_id = '" . sql_escape_string($member['mb_id']) . "'
          AND target_kedcd = '" . sql_escape_string($kedcd) . "'
          AND credit_used = 1
        ORDER BY tr_no DESC
        LIMIT 1
    ");
    $is_detail_unlocked = !empty($unlock_row['tr_no']);
}

// 상세 초기 렌더/JS seed로 함께 쓰는 기본 데이터
$company_seed = array(
    'id' => $kedcd,
    'title' => $company_name,
    'title_display' => company_detail_ai_display_value($company_name),
    'sub' => $industry_name,
    'tags' => $seed_tags,
    'tag_total_count' => $seed_tag_total_count,
    'score' => (isset($score['final_total_score_5']) && $score['final_total_score_5'] !== '') ? (float)$score['final_total_score_5'] : null,
    'grow' => (isset($score['growth_score_5']) && $score['growth_score_5'] !== '') ? (float)$score['growth_score_5'] : null,
    'profit' => (isset($score['profit_score_5']) && $score['profit_score_5'] !== '') ? (float)$score['profit_score_5'] : null,
    'stable' => (isset($score['stability_score_5']) && $score['stability_score_5'] !== '') ? (float)$score['stability_score_5'] : null,
    'badge_growth' => company_detail_ai_format_badge_percent($score['badge_growth'] ?? null),
    'badge_stability' => company_detail_ai_format_badge_percent($score['badge_stability'] ?? null),
    'badge_venture' => strtoupper(trim((string)($score['badge_venture'] ?? 'N'))),
    'founded' => $founded_year,
    'employees' => !empty($company['LABORER_SUM']) ? number_format((float)$company['LABORER_SUM']) . '명' : '정보 준비중',
    'biz_type' => $company_type,
    'credit' => trim((string)($score['total_grade_22'] ?? '')) !== '' ? (string)$score['total_grade_22'] : '',
    'corp_type' => $company_type,
    'address' => trim((string)($company['LOC_ADDRA'] ?? '') . ' ' . (string)($company['LOC_ADDRB'] ?? '')),
    'address_display' => company_detail_ai_display_value(trim((string)($company['LOC_ADDRA'] ?? '') . ' ' . (string)($company['LOC_ADDRB'] ?? ''))),
    'homepage' => trim((string)($company['HPAGE_URL'] ?? '')),
    'homepage_display' => company_detail_ai_display_value(trim((string)($company['HPAGE_URL'] ?? ''))),
    'product_line' => $seed_product_line,
    'is_unlocked' => $is_detail_unlocked,
    'region_insensitive' => false,
    'clients' => array(),
    'ai_analysis_html' => ''
);

// 비로그인 사용자는 우측 AI 추천점수 예측에 실데이터가 노출되지 않도록 더미 payload만 내려준다.
if (empty($member['mb_id'])) {
    $company_seed['score'] = null;
    $company_seed['grow'] = null;
    $company_seed['profit'] = null;
    $company_seed['stable'] = null;
    $company_seed['badge_growth'] = '';
    $company_seed['badge_stability'] = '';
    $company_seed['badge_venture'] = 'N';
    $company_seed['tags'] = array();
    $company_seed['tag_total_count'] = 0;
    $company_seed['product_line'] = '';
    $company_seed['address'] = '';
    $company_seed['address_display'] = '정보 준비중';
    $company_seed['homepage'] = '';
    $company_seed['homepage_display'] = '정보 준비중';
    $company_seed['clients'] = array();
    $company_seed['ai_analysis_html'] = '';
}

$current_request_uri = isset($_SERVER['REQUEST_URI']) ? (string)$_SERVER['REQUEST_URI'] : '/ko/company_detail_ai.php';
$detail_login_url = '/bbs/login.php?url=' . urlencode($current_request_uri);

$trade_request_login_params = $_GET;
$trade_request_detail_id = isset($trade_request_login_params['id'])
    ? trim((string)$trade_request_login_params['id'])
    : (isset($trade_request_login_params['cd']) ? trim((string)$trade_request_login_params['cd']) : '');
unset($trade_request_login_params['id'], $trade_request_login_params['cd']);
$trade_request_login_params['trade_request_login'] = '1';
$trade_request_return_query = array_merge(array('id' => $trade_request_detail_id), $trade_request_login_params);
$trade_request_return_url = '/recommendations/detail?' . http_build_query($trade_request_return_query);
$trade_request_login_url = '/bbs/login.php?url=' . urlencode($trade_request_return_url);

$requester_company_data = !empty($member['mb_id'])
    ? trade_request_member_company_data($member['mb_4'] ?? '')
    : array();
$requester_profile_state = !empty($member['mb_id'])
    ? company_detail_trade_request_profile_state($member)
    : array();

$hide_theme_svisual = true;
$hide_theme_pagetop = true;
$g5['body_script'] = ' class="page-company-detail-ai"';

add_stylesheet('<link href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/variable/pretendardvariable-dynamic-subset.min.css" rel="stylesheet" />', 180);
add_stylesheet('<link rel="stylesheet" href="/ko/css/design-tokens.css?ver=' . $design_tokens_ver . '" />', 190);
add_stylesheet('<link rel="stylesheet" href="/ko/css/badges.css?ver=' . $badges_css_ver . '" />', 200);
add_stylesheet('<link rel="stylesheet" href="/ko/css/favorite.css?ver=' . $favorite_css_ver . '" />', 210);
add_stylesheet('<link rel="stylesheet" href="/ko/css/confirm-modal.css?ver=' . $confirm_modal_css_ver . '" />', 220);
add_stylesheet('<link rel="stylesheet" href="/ko/css/share.css?ver=' . $share_css_ver . '" />', 230);
add_stylesheet('<link rel="stylesheet" href="/ko/css/ai-loading.css?ver=' . $ai_loading_css_ver . '" />', 250);
add_stylesheet('<link rel="stylesheet" href="/ko/css/auth-toggle.css?ver=' . $auth_toggle_css_ver . '" />', 255);
add_stylesheet('<link rel="stylesheet" href="/ko/css/top-nav.css?ver=' . $top_nav_css_ver . '" />', 260);
add_stylesheet('<link rel="stylesheet" href="/ko/css/company-detail-left.css?ver=' . $company_detail_left_css_ver . '" />', 270);
add_stylesheet('<link rel="stylesheet" href="/ko/css/match-request-modal.css?ver=' . $match_request_modal_css_ver . '" />', 280);
add_stylesheet('<link rel="stylesheet" href="/ko/css/company_detail_ai.css?ver=' . $company_detail_ai_css_ver . '" />', 290);

include_once(G5_THEME_PATH . '/head.php');
?>
<style>#pageTop,#sVisual{display:none !important;}</style>
<div id="detailWrap">
<div class="page-wrap">
	<nav class="top-nav">
		<a class="btn-back" href="javascript:history.back();"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg> 돌아가기</a>
		<div class="top-nav-actions">
			<button type="button" class="btn-match-cta-top" id="openTypeModal2">거래 요청하기</button>
			<button type="button" class="btn-favorite<?php echo $is_favorite_company ? ' is-active' : ''; ?>" data-favorite data-kedcd="<?php echo htmlspecialchars($kedcd, ENT_QUOTES, 'UTF-8'); ?>" title="관심기업 등록" aria-label="관심기업 등록">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
			</button>
			<button type="button" class="btn-topnav-icon" data-share title="공유하기" aria-label="공유하기">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.59 13.51l6.83 3.98M15.41 6.51l-6.82 3.98"/></svg>
			</button>
		</div>
	</nav>

	<div class="layout">
		<div class="card" id="leftCard"></div>
		<div class="right-stack">
			<div class="card match-box" id="rightCard"></div>
		</div>
	</div>

	<div class="ai-analyze-full">
			<div class="card section-card ai-analyze-card">
				<div class="ai-analyze-head">
					<h3>AI 기업 분석</h3>
					<p class="ai-cta-trade-type" id="aiCtaTradeTypeText"><?php echo htmlspecialchars($detail_trade_type_label, ENT_QUOTES, 'UTF-8'); ?></p>
				</div>
			<div class="ai-blur-bg" id="aiBlurBg" aria-hidden="true">
				<div class="ai-blur-content ai-analysis-result">
					<h3>1. 거래 밸류에이션 요약</h3>
					<p>양사 간의 결합은 <strong>데이터 기반 전략적 파트너십</strong>으로 정의됩니다.</p>
						<table class="ai-kpi-table"><thead><tr><th>핵심 지표</th><th>점수</th></tr></thead><tbody>
					<tr><td>공급망 근접도</td><td>--</td></tr>
					<tr><td>거래처 유사성</td><td>--</td></tr>
					<tr><td>거래 활성도</td><td>--</td></tr>
					<tr><td>재무 안정성</td><td>--</td></tr>
					<tr><td>지역 접근성</td><td>--</td></tr>
					<tr><td>규모 적합도</td><td>--</td></tr>
					</tbody></table>
					<ul>
						<li>실제 점수는 최신 EC2 분석 결과로 동적으로 반영됩니다.</li>
						<li>분석 완료 후 거래 리스크 및 협업 적합도를 확인할 수 있습니다.</li>
						<li>업종/네트워크 기반 핵심 지표를 종합해 추천 근거를 제공합니다.</li>
					</ul>
					<hr>
					<h3>2. 상세 분석 및 시너지 도출</h3>
					<p>전문 제조 기업으로 국내외 다수 고객사에 안정적인 공급 실적을 보유하고 있습니다.</p>
					<h4>강점</h4>
					<p>지역 접근성과 규모 적합성을 바탕으로 신속한 기술 대응 및 운영 효율성 확보가 가능합니다.</p>
					<h4>유의사항</h4>
					<p>거래처 유사성 점수가 상대적으로 낮아 초기 파일럿 거래를 통한 신뢰 구축이 권장됩니다.</p>
				</div>
				</div>
				<div class="ai-cta-overlay" id="aiCtaOverlay">
					<button type="button" class="btn-ai-analyze-cta" id="btnAiAnalyze">
						<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 3l1.5 4.5L18 9l-4.5 1.5L12 15l-1.5-4.5L6 9l4.5-1.5L12 3z"/></svg>
						분석하기
				    </button>
				    <p class="ai-cta-benefit" id="aiCtaBenefitText">두 기업 간 시너지, 거래 적합도, 협력 전략까지<br>AI가 심층 분석한 맞춤 리포트를 받아보세요.</p>
			    </div>
				<div class="ai-analysis-body" id="aiAnalysisBody" role="region" aria-live="polite" aria-label="AI 분석 결과" style="display:none;"></div>
				<!-- LLM 보고서 생성 단계 로딩 템플릿 -->
				<template id="reportLoadingTpl">
					<div class="report-loading" id="reportLoadingUi">
						<div class="report-loading_spinner"></div>
						<div class="report-loading_title">AI 심층 분석 보고서 생성 중…</div>
						<ul class="report-loading_steps">
							<li class="report-loading_step" data-step="0">
								<span class="report-loading_step_icon">①</span>
								<span>의뢰사 프로필 및 업종 확인</span>
							</li>
							<li class="report-loading_step" data-step="1">
								<span class="report-loading_step_icon">②</span>
								<span>추천 기업 ML 데이터 분석</span>
							</li>
							<li class="report-loading_step" data-step="2">
								<span class="report-loading_step_icon">③</span>
								<span>업종·공급망 구조 파악</span>
							</li>
							<li class="report-loading_step" data-step="3">
								<span class="report-loading_step_icon">④</span>
								<span>재무 경향 및 안정성 평가</span>
							</li>
							<li class="report-loading_step" data-step="4">
								<span class="report-loading_step_icon">⑤</span>
								<span>거래 네트워크·시너지 분석</span>
							</li>
							<li class="report-loading_step" data-step="5">
								<span class="report-loading_step_icon">⑥</span>
								<span>협력 전략 및 실행 계획 수립</span>
							</li>
						</ul>
						<div class="report-loading_bar">
							<div class="report-loading_bar_fill" id="reportLoadingBarFill"></div>
						</div>
					</div>
				</template>
			</div>
		</div>
	</div>

<?php include __DIR__ . '/modal/match_request_modal.php'; ?>

<div class="ai-loading_overlay" id="aiLoadingOverlay">
	<div class="ai-loading_card">
		<div class="ai-loading_spinner"></div>
		<h3 class="ai-loading_title">AI가 거래처를 분석하고 있습니다</h3>
		<div class="ai-loading_steps">
			<div class="ai-loading_step" data-step="0"><span class="ai-loading_step_icon"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></span><span>업종 연관성 분석 중...</span></div>
			<div class="ai-loading_step" data-step="1"><span class="ai-loading_step_icon"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></span><span>거래 네트워크 분석 중...</span></div>
			<div class="ai-loading_step" data-step="2"><span class="ai-loading_step_icon"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></span><span>지역 근접성 분석 중...</span></div>
			<div class="ai-loading_step" data-step="3"><span class="ai-loading_step_icon"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></span><span>규모 적합도 분석 중...</span></div>
			<div class="ai-loading_step" data-step="4"><span class="ai-loading_step_icon"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></span><span>재무 건전성 분석 중...</span></div>
			<div class="ai-loading_step" data-step="5"><span class="ai-loading_step_icon"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></span><span>기업 신뢰도 분석 중...</span></div>
		</div>
		<div class="ai-loading_bar"><div class="ai-loading_bar_fill" id="aiLoadingBarFill"></div></div>
	</div>
</div>
</div>

<script>
// company_detail_ai.js에서 상세 화면 초기 상태를 구성할 때 사용
window.COMPANY_DETAIL_DATA = <?php echo json_encode($company_seed, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
window.MATCH_REQUEST_CONTEXT = {
	endpoint: "/ko/company_detail_ai.php",
	verifyEndpoint: "/ko/trade_request_verify_bzno.php",
	sourcePage: "company_detail_ai",
	isLoggedIn: <?php echo !empty($member['mb_id']) ? 'true' : 'false'; ?>,
	loginCheckUrl: <?php echo json_encode($detail_login_url, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
	tradeRequestLoginUrl: <?php echo json_encode($trade_request_login_url, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
	tradeRequestReturnUrl: <?php echo json_encode($trade_request_return_url, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
	autoOpenOnLoad: <?php echo $should_open_trade_request_modal ? 'true' : 'false'; ?>,
	defaultRequestMessage: <?php echo json_encode($default_message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
	requesterCompany: <?php echo json_encode($requester_company_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
	requesterProfile: <?php echo json_encode($requester_profile_state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
	target: {
		kedcd: <?php echo json_encode($kedcd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
		company: <?php echo json_encode($company_name, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
		bzno: <?php echo json_encode(licenseNum($company['BZNO'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
		founded: <?php echo json_encode($founded_year, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
		employees: <?php echo json_encode(!empty($company['LABORER_SUM']) ? number_format((float)$company['LABORER_SUM']) . '명' : '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
		corpType: <?php echo json_encode('법인기업', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
		industry: <?php echo json_encode($company['BZC_CD'] ?? '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
		product: <?php echo json_encode($company_seed['product_line'] ?? '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
	},
	sourceKedcd: <?php echo json_encode($preload_src_kedcd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
	recommendationContext: {
		selected_target_keywords: <?php echo json_encode($detail_selected_target_keywords, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
		selected_partner_bzc_cds: <?php echo json_encode($detail_selected_partner_bzc_cds, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
		recommendation_mode: <?php echo json_encode($detail_recommendation_mode, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
		trace_id: <?php echo json_encode($detail_trace_id, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
	},
	seedCompany: <?php echo json_encode($company_seed, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
};
window.PRELOADED_AI_REPORT = {
	markdown: <?php echo json_encode($preloaded_ai_report_markdown, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
	once_key: <?php echo json_encode($preloaded_ai_once_key, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
};
window.PRELOADED_AI_SOURCE_CONTEXT = <?php echo json_encode($preloaded_ai_source_context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
</script>
<script src="/ko/js/badges.js?ver=<?php echo $badges_js_ver; ?>"></script>
<script src="/ko/js/company-detail-left.js?ver=<?php echo $company_detail_left_js_ver; ?>"></script>
<script src="/ko/js/favorite.js?ver=<?php echo $favorite_js_ver; ?>"></script>
<script src="/ko/js/share.js?ver=<?php echo $share_js_ver; ?>"></script>
<script src="/ko/js/confirm-modal.js?ver=<?php echo $confirm_modal_js_ver; ?>"></script>
<script src="/ko/js/match-request-modal.js?ver=<?php echo $match_request_modal_js_ver; ?>"></script>
<script src="/ko/js/ai-loading.js?ver=<?php echo $ai_loading_js_ver; ?>"></script>
<!-- LLM 보고서 markdown 렌더링 -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<!-- LLM markdown 렌더 결과 XSS 방지를 위한 sanitize -->
<script src="https://cdn.jsdelivr.net/npm/dompurify@3.2.6/dist/purify.min.js"></script>
<script src="/ko/js/company_detail_ai.js?ver=<?php echo $company_detail_ai_js_ver; ?>"></script>
<?php include_once(G5_THEME_PATH . '/tail.php'); ?>
