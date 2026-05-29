<?php
include_once("./_common.php");
include_once(__DIR__ . '/helper/trade_request_helper.php');
include_once(__DIR__ . '/helper/company_detail_helper.php');

$g5['title'] = '기업 상세';
$shouldOpenTradeRequestModal = false;

if (isset($_GET['trade_request_login']) && $_GET['trade_request_login'] === '1') {
	login_chk();
	$shouldOpenTradeRequestModal = !empty($member['mb_id']);
}

$badges_css_ver = file_exists(__DIR__ . '/css/badges.css') ? filemtime(__DIR__ . '/css/badges.css') : time();
$favorite_css_ver = file_exists(__DIR__ . '/css/favorite.css') ? filemtime(__DIR__ . '/css/favorite.css') : time();
$confirm_modal_css_ver = file_exists(__DIR__ . '/css/confirm-modal.css') ? filemtime(__DIR__ . '/css/confirm-modal.css') : time();
$share_css_ver = file_exists(__DIR__ . '/css/share.css') ? filemtime(__DIR__ . '/css/share.css') : time();
$ai_loading_css_ver = file_exists(__DIR__ . '/css/ai-loading.css') ? filemtime(__DIR__ . '/css/ai-loading.css') : time();
$company_detail_left_css_ver = file_exists(__DIR__ . '/css/company-detail-left.css') ? filemtime(__DIR__ . '/css/company-detail-left.css') : time();
$company_detail_css_ver = file_exists(__DIR__ . '/css/company-detail.css') ? filemtime(__DIR__ . '/css/company-detail.css') : time();
$match_request_modal_css_ver = file_exists(__DIR__ . '/css/match-request-modal.css') ? filemtime(__DIR__ . '/css/match-request-modal.css') : time();
$top_nav_css_ver = file_exists(__DIR__ . '/css/top-nav.css') ? filemtime(__DIR__ . '/css/top-nav.css') : time();

$favorite_js_ver = file_exists(__DIR__ . '/js/favorite.js') ? filemtime(__DIR__ . '/js/favorite.js') : time();
$badges_js_ver = file_exists(__DIR__ . '/js/badges.js') ? filemtime(__DIR__ . '/js/badges.js') : time();
$company_detail_left_js_ver = file_exists(__DIR__ . '/js/company-detail-left.js') ? filemtime(__DIR__ . '/js/company-detail-left.js') : time();
$company_detail_js_ver = file_exists(__DIR__ . '/js/company-detail.js') ? filemtime(__DIR__ . '/js/company-detail.js') : time();
$ai_loading_js_ver = file_exists(__DIR__ . '/js/ai-loading.js') ? filemtime(__DIR__ . '/js/ai-loading.js') : time();
$match_request_modal_js_ver = file_exists(__DIR__ . '/js/match-request-modal.js') ? filemtime(__DIR__ . '/js/match-request-modal.js') : time();
$share_js_ver = file_exists(__DIR__ . '/js/share.js') ? filemtime(__DIR__ . '/js/share.js') : time();
$confirm_modal_js_ver = file_exists(__DIR__ . '/js/confirm-modal.js') ? filemtime(__DIR__ . '/js/confirm-modal.js') : time();
$defaultMessage = trade_request_default_message();

// badge 퍼센트 포맷 변환
function format_badge_percent_detail($value) {
	if ($value === null || $value === '') return '';
	$str = trim((string)$value);
	if ($str === '') return '';
	if (!is_numeric($str)) return $str;

	$num = (float)$str;
	if ($num == (int)$num) return (string)((int)$num);
	return rtrim(rtrim(number_format($num, 2, '.', ''), '0'), '.');
}

// 상세 페이지 해제 상태에서 값이 비어 있으면 공통 대체 문구로 보여준다.
function trade_request_display_value($value) {
	$value = trim((string)$value);
	return ($value !== '') ? $value : '정보 준비중';
}

function parse_keywords_json_tags_detail($keywords_json) {
	$keywords_json = trim((string)$keywords_json);
	if ($keywords_json === '') return array();

	$decoded = json_decode($keywords_json, true);
	if (!is_array($decoded)) return array();

	$result = array();
	foreach ($decoded as $kw) {
		$trimmed = trim((string)$kw);
		if ($trimmed === '') continue;
		$result[] = $trimmed;
	}

	return array_values(array_unique($result));
}

function resolve_keyword_tag_total_count_detail($keyword_count, $tags) {
	$count = (int)$keyword_count;
	if ($count > 0) return $count;
	return is_array($tags) ? count($tags) : 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
	if ($_POST['action'] === 'trade_request_verify_bzno') {
		company_detail_handle_trade_request_verify_bzno($member, $_POST);
	}
	if ($_POST['action'] === 'trade_request_insert') {
		company_detail_handle_trade_request_insert($member, $_POST);
	}
}

// 진입 시 암호화 기업코드 복호화 후 조회하기 위함
$cd = isset($_GET['cd']) ? trim((string)$_GET['cd']) : '';
$kedcd = $cd ? simple_crypt($cd, 'd') : '';

// 기업코드 값 최소 검증
if (!preg_match('/^\d{10}$/', $kedcd)) {
	alert('잘못된 접근입니다.', '/ko/matching_list.php');
}

$kedcdSql = sql_escape_string($kedcd);

// 대상 기업 기본 정보 조회
$company = sql_fetch("
	SELECT
		KEDCD,
		ENP_NM,
		BZNO,
		BZC_CD,
		ESTB_DT,
		LABORER_SUM,
		ENP_TYP,
		keywords_json,
		keyword_count,
		LOC_ADDRA,
		LOC_ADDRB,
		HPAGE_URL
	FROM company
	WHERE KEDCD = '{$kedcdSql}'
");

if (empty($company['KEDCD'])) {
	alert('기업 정보를 찾을 수 없습니다.', '/ko/matching_list.php');
}

$favorite_company = array();
if (!empty($member['mb_id'])) {
	$favorite_company = sql_fetch("
		SELECT ms_id
		FROM g5_favorite
		WHERE mb_id = '" . sql_escape_string($member['mb_id']) . "'
		  AND KEDCD = '" . sql_escape_string($company['KEDCD']) . "'
	");
}

// 기업의 업종코드(BZC_CD) 기준 업종명 조회
$industry = array();
if (!empty($company['BZC_CD'])) {
	$companyBzcCdSql = sql_escape_string($company['BZC_CD']);
	$industry = sql_fetch("
		SELECT k_code, k_code_name
		FROM kssc
		WHERE k_code = '{$companyBzcCdSql}'
	");
}

// 해당 기업코드 기준 성장성 ~ 재무상태 조회
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
		age_score_5,
		emp_score_5,
		final_total_score_5
	FROM company_score_final_dedup
	WHERE KEDCD = '{$kedcdSql}'
");

// 설립일자(ESTB_DT) : YYYYMMDD -> YYYY년 변환
$foundedYear = '';
if (!empty($company['ESTB_DT']) && strlen($company['ESTB_DT']) >= 4) {
	$foundedYear = substr($company['ESTB_DT'], 0, 4) . '년';
}

// extend/user.config 의 $enp_typt 활용 -> 기업유형
$companyType = '';
if (!empty($company['ENP_TYP']) && isset($enp_typt[$company['ENP_TYP']])) {
	$companyType = $enp_typt[$company['ENP_TYP']];
}

// 현재 로그인 회원이 이 기업에 대해 크레딧 사용 완료한 거래 요청이 있으면 마스킹 해제
$isDetailUnlocked = false;
if (!empty($member['mb_id'])) {
	$unlockRow = sql_fetch("
		SELECT tr_no
		FROM trade_request
		WHERE request_mb_id = '" . sql_escape_string($member['mb_id']) . "'
		  AND target_kedcd = '" . sql_escape_string($company['KEDCD']) . "'
		  AND credit_used = 1
		ORDER BY tr_no DESC
		LIMIT 1
	");

	$isDetailUnlocked = !empty($unlockRow['tr_no']);
}

$companyTitleDisplay = trade_request_display_value($company['ENP_NM'] ?? '');
$companyAddressDisplay = trade_request_display_value(trim(($company['LOC_ADDRA'] ?? '') . ' ' . ($company['LOC_ADDRB'] ?? '')));
$companyHomepageDisplay = trade_request_display_value($company['HPAGE_URL'] ?? '');
$companyKeywordTags = parse_keywords_json_tags_detail($company['keywords_json'] ?? '');
$companyKeywordTotalCount = resolve_keyword_tag_total_count_detail($company['keyword_count'] ?? 0, $companyKeywordTags);
$companyKeywordDisplayTags = array_slice($companyKeywordTags, 0, 3);
$companyKeywordRestCount = max($companyKeywordTotalCount - count($companyKeywordDisplayTags), 0);
if ($companyKeywordRestCount > 0) {
	$companyKeywordDisplayTags[] = '+' . $companyKeywordRestCount;
}
$companyProductLineText = implode(' · ', $companyKeywordTags);

// 모달에서 쓰는 "우리 기업 정보"는 로그인 회원의 사업자번호 기준 회사 정보로 맞춘다.
$requesterCompanyData = !empty($member['mb_id'])
	? trade_request_member_company_data($member['mb_4'] ?? '')
	: array();
$requesterProfileState = !empty($member['mb_id'])
	? company_detail_trade_request_profile_state($member)
	: array();

$tradeRequestLoginParams = $_GET;
$tradeRequestLoginParams['trade_request_login'] = '1';
$tradeRequestReturnUrl = '/ko/company_detail.php?' . http_build_query($tradeRequestLoginParams);
$tradeRequestLoginUrl = '/bbs/login.php?url=' . urlencode($tradeRequestReturnUrl);

// 프론트 렌더링과 마스킹 분기에 필요한 상세 페이지 데이터 구조를 한 번에 정리해 JS로 내려준다.
$companyDetailData = array(
	'kedcd' => $company['KEDCD'] ?? '',
	'title' => $company['ENP_NM'] ?? '',
	'title_display' => $companyTitleDisplay,
	'sub' => $industry['k_code_name'] ?? '',
	'tags' => $companyKeywordDisplayTags,
	'founded' => $foundedYear,
	'employees' => !empty($company['LABORER_SUM']) ? number_format((float)$company['LABORER_SUM']) . '명' : '',
	'corp_type' => $companyType,
	'product_line' => $companyProductLineText,
	'credit' => $score['total_grade_22'] ?? '',
	'bzno' => $company['BZNO'] ?? '',
	'defaultRequestMessage' => $defaultMessage,
	'address' => trim(($company['LOC_ADDRA'] ?? '') . ' ' . ($company['LOC_ADDRB'] ?? '')),
	'address_display' => $companyAddressDisplay,
	'homepage' => $company['HPAGE_URL'] ?? '',
	'homepage_display' => $companyHomepageDisplay,
	'is_unlocked' => $isDetailUnlocked,
	'badge_growth' => format_badge_percent_detail($score['badge_growth'] ?? null),
	'badge_stability' => format_badge_percent_detail($score['badge_stability'] ?? null),
	'badge_venture' => strtoupper(trim((string)($score['badge_venture'] ?? ''))),
	'grow' => isset($score['growth_score_5']) ? (float)$score['growth_score_5'] : 0,
	'profit' => isset($score['profit_score_5']) ? (float)$score['profit_score_5'] : 0,
	'stable' => isset($score['stability_score_5']) ? (float)$score['stability_score_5'] : 0,
	'ageScore' => isset($score['age_score_5']) ? (float)$score['age_score_5'] : 0,
	'empScore' => isset($score['emp_score_5']) ? (float)$score['emp_score_5'] : 0,
	'score' => isset($score['final_total_score_5']) ? (float)$score['final_total_score_5'] : 0,
);

// 테마 헤더 사용, 상단 비주얼/페이지탑은 상세 레이아웃과 겹치지 않도록 숨김
$hide_theme_svisual = true;
$hide_theme_pagetop = true;
$g5['body_script'] = ' class="page-company-detail"';

add_stylesheet('<link href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/variable/pretendardvariable-dynamic-subset.min.css" rel="stylesheet" />', 180);
add_stylesheet('<link rel="stylesheet" href="/ko/css/badges.css?ver=' . $badges_css_ver . '" />', 200);
add_stylesheet('<link rel="stylesheet" href="/ko/css/favorite.css?ver=' . $favorite_css_ver . '" />', 210);
add_stylesheet('<link rel="stylesheet" href="/ko/css/confirm-modal.css?ver=' . $confirm_modal_css_ver . '" />', 220);
add_stylesheet('<link rel="stylesheet" href="/ko/css/share.css?ver=' . $share_css_ver . '" />', 230);
add_stylesheet('<link rel="stylesheet" href="/ko/css/ai-loading.css?ver=' . $ai_loading_css_ver . '" />', 250);
add_stylesheet('<link rel="stylesheet" href="/ko/css/company-detail-left.css?ver=' . $company_detail_left_css_ver . '" />', 260);
add_stylesheet('<link rel="stylesheet" href="/ko/css/company-detail.css?ver=' . $company_detail_css_ver . '" />', 270);
add_stylesheet('<link rel="stylesheet" href="/ko/css/match-request-modal.css?ver=' . $match_request_modal_css_ver . '" />', 280);
add_stylesheet('<link rel="stylesheet" href="/ko/css/top-nav.css?ver=' . $top_nav_css_ver . '" />', 290);

include_once(G5_THEME_PATH . '/head.php');
?>
<script>try { if (window.self !== window.top) document.documentElement.classList.add('in-iframe'); } catch(e) {}</script>
<div id="detailWrap">
	<script>
	if (document.documentElement.classList.contains('in-iframe')) {
		document.body.style.paddingTop = '0';
		document.documentElement.style.setProperty('--header-h', '0px');
		if (new URLSearchParams(window.location.search).get('nocta') === '1') {
			document.documentElement.classList.add('no-cta');
		}
	}
	</script>


	<div class="page-wrap">
		<nav class="top-nav">
			<a class="btn-back" href="javascript:history.back();"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg> 돌아가기</a>
			<div class="top-nav-actions">
				<button type="button" class="btn-match-cta-top" id="openTypeModal2">거래 요청하기</button>
				<button type="button" class="btn-favorite<?php echo !empty($favorite_company['ms_id']) ? ' is-active' : ''; ?>" data-favorite data-kedcd="<?php echo htmlspecialchars($company['KEDCD'], ENT_QUOTES, 'UTF-8'); ?>" title="관심기업 등록" aria-label="관심기업 등록">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
				</button>
				<button type="button" class="btn-topnav-icon" data-share title="공유하기" aria-label="공유하기">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.59 13.51l6.83 3.98M15.41 6.51l-6.82 3.98"/></svg>
				</button>
			</div>
		</nav>

		<div class="layout">
			<!-- Left column: 기업 핵심 정보 -->
			<div class="card" id="leftCard"></div>

			<!-- Right column: 5각형 레이더 차트 -->
			<div class="right-stack" id="rightStack">
				<div class="right-section-card">
					<div class="right-section-title">기업 분석</div>
					<div class="detail-radar-card" id="detailRadarCard"></div>
				</div>
			</div>
		</div>
	</div>

	<?php include __DIR__ . '/modal/match_request_modal.php'; ?>

	<!-- floating bar removed -->

	<!-- AI loading overlay (inline from ai_loading.php) -->
	<div class="ai-loading_overlay" id="aiLoadingOverlay">
		<div class="ai-loading_card">
			<div class="ai-loading_spinner"></div>
			<h3 class="ai-loading_title">AI가 거래처를 분석하고 있습니다</h3>
			<div class="ai-loading_steps">
				<div class="ai-loading_step" data-step="0">
					<span class="ai-loading_step_icon"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></span>
					<span>업종 연관성 분석 중...</span>
				</div>
				<div class="ai-loading_step" data-step="1">
					<span class="ai-loading_step_icon"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></span>
					<span>거래 네트워크 분석 중...</span>
				</div>
				<div class="ai-loading_step" data-step="2">
					<span class="ai-loading_step_icon"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></span>
					<span>지역 근접성 분석 중...</span>
				</div>
				<div class="ai-loading_step" data-step="3">
					<span class="ai-loading_step_icon"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></span>
					<span>규모 적합도 분석 중...</span>
				</div>
				<div class="ai-loading_step" data-step="4">
					<span class="ai-loading_step_icon"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></span>
					<span>재무 건전성 분석 중...</span>
				</div>
				<div class="ai-loading_step" data-step="5">
					<span class="ai-loading_step_icon"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></span>
					<span>기업 신뢰도 분석 중...</span>
				</div>
			</div>
			<div class="ai-loading_bar"><div class="ai-loading_bar_fill" id="aiLoadingBarFill"></div></div>
		</div>
	</div>
</div>

<!-- SVG icon definitions -->
<svg xmlns="http://www.w3.org/2000/svg" style="display:none">
	<symbol id="icon-magic-wand" viewBox="0 0 24 24"><path fill="currentColor" d="M16.8 2.2l1.1 2.3 2.3 1.1-2.3 1.1-1.1 2.3-1.1-2.3-2.3-1.1 2.3-1.1 1.1-2.3zm3.2 2.8.6 1.1 1.1.6-1.1.6-.6 1.1-.6-1.1-1.1-.6 1.1-.6.6-1.1zM7.4 17.4 16.1 8.7l2.6 2.6-8.7 8.7-2.6-2.6zm-1 2.4 1.3 1.3-3.1 1 .8-2.9z"/></symbol>
</svg>

<script>
window.COMPANY_DETAIL_DATA = <?php echo json_encode($companyDetailData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
window.MATCH_REQUEST_CONTEXT = {
	endpoint: "/ko/company_detail.php",
	sourcePage: "company_detail",
	isLoggedIn: <?php echo !empty($member['mb_id']) ? 'true' : 'false'; ?>,
	loginCheckUrl: <?php echo json_encode($tradeRequestLoginUrl, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
	tradeRequestReturnUrl: <?php echo json_encode($tradeRequestReturnUrl, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
	autoOpenOnLoad: <?php echo $shouldOpenTradeRequestModal ? 'true' : 'false'; ?>,
	defaultRequestMessage: <?php echo json_encode($defaultMessage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
	requesterCompany: <?php echo json_encode($requesterCompanyData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
	requesterProfile: <?php echo json_encode($requesterProfileState, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
	target: {
		kedcd: <?php echo json_encode($company['KEDCD'] ?? '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
		company: <?php echo json_encode($company['ENP_NM'] ?? '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
		bzno: <?php echo json_encode(licenseNum($company['BZNO'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
		founded: <?php echo json_encode($foundedYear, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
		employees: <?php echo json_encode(!empty($company['LABORER_SUM']) ? number_format((float)$company['LABORER_SUM']) . '명' : '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
		corpType: <?php echo json_encode($companyType, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
		industry: <?php echo json_encode($industry['k_code_name'] ?? '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
		product: <?php echo json_encode($companyDetailData['product_line'] ?? '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
	}
};
</script>
<script src="/ko/js/badges.js?ver=<?php echo $badges_js_ver; ?>"></script>
<script src="/ko/js/company-detail-left.js?ver=<?php echo $company_detail_left_js_ver; ?>"></script>
<script src="/ko/js/company-detail.js?ver=<?php echo $company_detail_js_ver; ?>"></script>
<script src="/ko/js/ai-loading.js?ver=<?php echo $ai_loading_js_ver; ?>"></script>
<script src="/ko/js/match-request-modal.js?ver=<?php echo $match_request_modal_js_ver; ?>"></script>
<script src="/ko/js/favorite.js?ver=<?php echo $favorite_js_ver; ?>"></script>
<script src="/ko/js/share.js?ver=<?php echo $share_js_ver; ?>"></script>
<script src="/ko/js/confirm-modal.js?ver=<?php echo $confirm_modal_js_ver; ?>"></script>
<?php include_once(G5_THEME_PATH . '/tail.php'); ?>
