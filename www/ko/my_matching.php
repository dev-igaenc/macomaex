<?php
include_once(__DIR__ . '/_common.php');

$g5['title'] = '거래 히스토리';
$board['bo_10'] = '6';
$board['bo_9'] = '2';
$defaultMessage = '담당자님, 안녕하세요. 귀사를 확인하고 연락드립니다. 신규 거래처를 검토 중이며, 귀사와 적합한 경우 거래 진행을 희망합니다. 상세 조건은 협의를 통해 논의하고자 합니다.';

$design_tokens_ver = file_exists(__DIR__ . '/css/design-tokens.css') ? filemtime(__DIR__ . '/css/design-tokens.css') : time();
$match_request_modal_ver = file_exists(__DIR__ . '/css/match-request-modal.css') ? filemtime(__DIR__ . '/css/match-request-modal.css') : time();
$my_matching_css_ver = file_exists(__DIR__ . '/css/my_matching.css') ? filemtime(__DIR__ . '/css/my_matching.css') : time();
$favorite_css_ver = file_exists(__DIR__ . '/css/favorite.css') ? filemtime(__DIR__ . '/css/favorite.css') : time();
$ai_loading_css_ver = file_exists(__DIR__ . '/css/ai-loading.css') ? filemtime(__DIR__ . '/css/ai-loading.css') : time();

$favorite_js_ver = file_exists(__DIR__ . '/js/favorite.js') ? filemtime(__DIR__ . '/js/favorite.js') : time();
$ai_loading_js_ver = file_exists(__DIR__ . '/js/ai-loading.js') ? filemtime(__DIR__ . '/js/ai-loading.js') : time();
$match_request_modal_js_ver = file_exists(__DIR__ . '/js/match-request-modal.js') ? filemtime(__DIR__ . '/js/match-request-modal.js') : time();
$my_matching_js_ver = file_exists(__DIR__ . '/js/my_matching.js') ? filemtime(__DIR__ . '/js/my_matching.js') : time();

// 테마 헤더 숨김, 페지 전용 스타일 범위 body class 고정
$hide_theme_svisual = true;
$hide_theme_pagetop = true;
$g5['body_script'] = ' class="page-my-matching"';

add_stylesheet('<link href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/variable/pretendardvariable-dynamic-subset.min.css" rel="stylesheet" />', 180);
add_stylesheet('<link rel="stylesheet" href="/ko/css/design-tokens.css?ver=' . $design_tokens_ver . '" />', 190);
add_stylesheet('<link rel="stylesheet" href="/ko/css/match-request-modal.css?ver=' . $match_request_modal_ver . '" />', 200);
add_stylesheet('<link rel="stylesheet" href="/ko/css/my_matching.css?ver=' . $my_matching_css_ver . '" />', 210);
add_stylesheet('<link rel="stylesheet" href="/ko/css/favorite.css?ver=' . $favorite_css_ver . '" />', 220);
add_stylesheet('<link rel="stylesheet" href="/ko/css/ai-loading.css?ver=' . $ai_loading_css_ver . '" />', 240);

add_javascript('<script src="/ko/js/favorite.js?ver=' . $favorite_js_ver . '"></script>', 200);
add_javascript('<script src="/ko/js/ai-loading.js?ver=' . $ai_loading_js_ver . '"></script>', 210);
add_javascript('<script src="/ko/js/match-request-modal.js?ver=' . $match_request_modal_js_ver . '"></script>', 220);
add_javascript('<script src="/ko/js/my_matching.js?ver=' . $my_matching_js_ver . '"></script>', 240);

login_chk();

include_once(G5_THEME_PATH . '/head.php');

function my_matching_status_code($approvalStatus) {
	$status = (int)$approvalStatus;
	if ($status === 1) return 'Y';
	if ($status === 2) return 'R';
	return 'N';
}

function my_matching_trade_type_label($tradeType) {
	$type = strtoupper(trim((string)$tradeType));
	if ($type === 'TM') return 'TM 서포트';
	return '일반';
}

function my_matching_trade_direction_label($tradeDirection) {
	$direction = strtoupper(trim((string)$tradeDirection));
	if ($direction === 'P') return '구매';
	if ($direction === 'S') return '판매';
	if ($direction === 'C') return '협업';
	return '';
}

// trade_purpose 컬럼 JSON 배열 칩 라벨 배열로 변환
function my_matching_trade_purpose_labels($json) {
	$purposeLabels = array(
		'BUY_PRODUCT' => '제품·상품 매입',
		'SOURCE_MATERIAL_PART' => '원자재·부품 조달',
		'REQUEST_OEM_ODM' => 'OEM·ODM 요청',
		'REQUEST_IMPORT_PROCUREMENT' => '수입·조달 요청',
		'REQUEST_REGULAR_SUPPLY' => '정기 납품 요청',
		'OFFER_PRODUCT_SALE' => '제품·상품 판매 제안',
		'OFFER_OEM_ODM' => 'OEM·ODM 제공',
		'OFFER_DISTRIBUTION_SALES' => '유통·판매 대행 제안',
		'OFFER_EXPORT_AGENCY' => '수출 대행 제안',
		'OFFER_REGULAR_SALE' => '정기 판매 제안',
		'COLLAB_NEW_PRODUCT_DEVELOPMENT' => '신제품 공동 개발',
		'COLLAB_MARKETING_PROMOTION' => '공동 마케팅·판촉',
		'COLLAB_TECH_ALLIANCE_LICENSING' => '기술 제휴·라이선싱',
	);

	$decoded = json_decode((string)$json, true);
	if (!is_array($decoded)) return array();

	$labels = array();
	for ($i = 0; $i < count($decoded); $i++) {
		$key = trim((string)$decoded[$i]);
		if ($key === '') continue;
		$labels[] = isset($purposeLabels[$key]) ? $purposeLabels[$key] : $key;
	}

	return array_values(array_unique($labels));
}

// 기업 상세보기 URL
function my_matching_company_detail_url($kedcd) {
	$kedcd = trim((string)$kedcd);
	if (!preg_match('/^\d{10}$/', $kedcd)) return '';
	return 'company_detail.php?cd=' . urlencode(simple_crypt($kedcd, 'e')) . '&nocta=1';
}

// 프론트 렌더링에 필요한 상세 페이지 데이터 구조를 한 번에 정리해 JS로 내려준다.
function my_matching_build_item($row, $starred = false) {
	$companyName = trim((string)($row['company_name'] ?? ''));
	if ($companyName === '') {
		$companyName = trim((string)($row['fallback_company_name'] ?? ''));
	}
	if ($companyName === '') {
		$companyName = '정보 준비중';
	}

	$address = trim((string)($row['LOC_ADDRA'] ?? '') . ' ' . (string)($row['LOC_ADDRB'] ?? ''));
	if ($address === '') $address = '정보 준비중';

	$industry = trim((string)($row['industry_name'] ?? ''));
	if ($industry === '') $industry = '정보 준비중';

	$messageContent = trim((string)($row['message_content'] ?? ''));
	if ($messageContent === '') $messageContent = '요청 메시지가 없습니다.';

	return array(
		'id' => (int)$row['tr_no'],
		'tradeNo' => (int)$row['tr_no'],
		'company' => $companyName,
		'bzno' => trim((string)($row['company_bzno'] ?? '')),
		'address' => $address,
		'industry' => $industry,
		'manager' => trim((string)($row['manager_name'] ?? '')),
		'phone' => '',
		'email' => trim((string)($row['manager_email'] ?? '')),
		'status' => my_matching_status_code($row['approval_status'] ?? 0),
		'starred' => (bool)$starred,
		'kedcd' => trim((string)($row['company_kedcd'] ?? '')),
		'detailUrl' => my_matching_company_detail_url($row['company_kedcd'] ?? ''),
		'requestBzno' => trim((string)($row['request_bzno'] ?? '')),
		'targetBzno' => trim((string)($row['target_bzno'] ?? '')),
		'approvalStatus' => (int)($row['approval_status'] ?? 0),
		'message' => array(
			'matchType' => my_matching_trade_type_label($row['trade_type'] ?? ''),
			'direction' => my_matching_trade_direction_label($row['trade_direction'] ?? ''),
			'types' => my_matching_trade_purpose_labels($row['trade_purpose'] ?? ''),
			'item' => trim((string)($row['desired_item'] ?? '')),
			'extra' => $messageContent,
		),
	);
}

function my_matching_requester_company_data($memberBzno) {
	$memberBzno = preg_replace('/[^0-9]/', '', (string)$memberBzno);
	if ($memberBzno === '') return array();

	$company = sql_fetch("
		SELECT
			KEDCD,
			ENP_NM,
			BZNO,
			BZC_CD,
			ESTB_DT,
			LABORER_SUM,
			ENP_TYP,
			PD_NM,
			HPAGE_URL
		FROM company
		WHERE BZNO = '" . sql_escape_string($memberBzno) . "'
		LIMIT 1
	");
	if (empty($company['KEDCD'])) return array();

	$industry = sql_fetch("
		SELECT k_code_name
		FROM kssc
		WHERE k_code = '" . sql_escape_string($company['BZC_CD']) . "'
		LIMIT 1
	");

	$founded = '';
	if (!empty($company['ESTB_DT']) && strlen($company['ESTB_DT']) >= 4) {
		$founded = substr($company['ESTB_DT'], 0, 4) . '년';
	}

	$corpType = '';
	if (!empty($company['ENP_TYP']) && isset($GLOBALS['enp_typt'][$company['ENP_TYP']])) {
		$corpType = $GLOBALS['enp_typt'][$company['ENP_TYP']];
	}

	return array(
		'company' => trim((string)($company['ENP_NM'] ?? '')),
		'bzno' => !empty($company['BZNO']) ? licenseNum($company['BZNO']) : '',
		'founded' => $founded,
		'employees' => !empty($company['LABORER_SUM']) ? number_format((float)$company['LABORER_SUM']) . '명' : '',
		'corpType' => $corpType,
		'industry' => trim((string)($industry['k_code_name'] ?? '')),
		'product' => trim((string)($company['PD_NM'] ?? '')),
		'homepage' => trim((string)($company['HPAGE_URL'] ?? '')),
	);
}

$memberBzno = preg_replace('/[^0-9]/', '', (string)($member['mb_4'] ?? ''));
if ($memberBzno === '') {
	alert('회원 사업자번호 정보가 없어 거래 히스토리를 확인할 수 없습니다.', '/');
}

$requesterCompanyData = my_matching_requester_company_data($memberBzno);

$memberBznoSql = sql_escape_string($memberBzno);

$favoriteKedcdMap = array();
$favoriteResult = sql_query("SELECT KEDCD FROM g5_favorite WHERE mb_id = '" . sql_escape_string($member['mb_id']) . "'", false);
if ($favoriteResult) {
	for ($i = 0; $fav = sql_fetch_array($favoriteResult); $i++) {
		$favKedcd = trim((string)($fav['KEDCD'] ?? ''));
		if ($favKedcd !== '') {
			$favoriteKedcdMap[$favKedcd] = true;
		}
	}
}

$receivedRows = array();
$receivedSql = "
	SELECT
		TR.tr_no,
		TR.request_company_name AS fallback_company_name,
		TR.request_bzno,
		TR.target_bzno,
		TR.trade_direction,
		TR.trade_purpose,
		TR.desired_item,
		TR.message_content,
		TR.trade_type,
		TR.approval_status,
		C.BZNO AS company_bzno,
		C.KEDCD AS company_kedcd,
		C.ENP_NM AS company_name,
		C.LOC_ADDRA,
		C.LOC_ADDRB,
		(SELECT k_code_name FROM kssc WHERE k_code = C.BZC_CD LIMIT 1) AS industry_name,
		M.mb_6 AS manager_name,
		M.mb_5 AS manager_email
	FROM trade_request TR
	LEFT JOIN company C ON C.BZNO = TR.request_bzno
	LEFT JOIN g5_member M ON M.mb_company = C.KEDCD
	WHERE TR.target_bzno = '{$memberBznoSql}'
	ORDER BY TR.tr_no DESC
";
// 받은요청 조회
$receivedResult = sql_query($receivedSql, false);
if ($receivedResult) {
	for ($i = 0; $row = sql_fetch_array($receivedResult); $i++) {
		$kedcd = trim((string)($row['company_kedcd'] ?? ''));
		$receivedRows[] = my_matching_build_item($row, isset($favoriteKedcdMap[$kedcd]));
	}
}

$sentRows = array();
$sentSql = "
	SELECT
		TR.tr_no,
		TR.target_company_name AS fallback_company_name,
		TR.request_bzno,
		TR.target_bzno,
		TR.trade_direction,
		TR.trade_purpose,
		TR.desired_item,
		TR.message_content,
		TR.trade_type,
		TR.approval_status,
		C.BZNO AS company_bzno,
		C.KEDCD AS company_kedcd,
		C.ENP_NM AS company_name,
		C.LOC_ADDRA,
		C.LOC_ADDRB,
		(SELECT k_code_name FROM kssc WHERE k_code = C.BZC_CD LIMIT 1) AS industry_name,
		M.mb_6 AS manager_name,
		M.mb_5 AS manager_email
	FROM trade_request TR
	LEFT JOIN company C ON C.KEDCD = TR.target_kedcd
	LEFT JOIN g5_member M ON M.mb_company = C.KEDCD
	WHERE TR.request_bzno = '{$memberBznoSql}'
	ORDER BY TR.tr_no DESC
";

// 보낸요청 조회
$sentResult = sql_query($sentSql, false);
if ($sentResult) {
	for ($i = 0; $row = sql_fetch_array($sentResult); $i++) {
		$kedcd = trim((string)($row['company_kedcd'] ?? ''));
		$sentRows[] = my_matching_build_item($row, isset($favoriteKedcdMap[$kedcd]));
	}
}

$receivedStatusByKedcd = array();
for ($i = 0; $i < count($receivedRows); $i++) {
	$kedcd = trim((string)($receivedRows[$i]['kedcd'] ?? ''));
	if ($kedcd === '') continue;
	$receivedStatusByKedcd[$kedcd] = 'received-' . (string)$receivedRows[$i]['status'];
}

$sentStatusByKedcd = array();
for ($i = 0; $i < count($sentRows); $i++) {
	$kedcd = trim((string)($sentRows[$i]['kedcd'] ?? ''));
	if ($kedcd === '') continue;
	$sentStatusByKedcd[$kedcd] = 'sent-' . (string)$sentRows[$i]['status'];
}

$interestedRows = array();
$interestedSql = "
	SELECT
		F.KEDCD AS company_kedcd,
		C.BZNO AS company_bzno,
		C.ENP_NM AS company_name,
		C.LOC_ADDRA,
		C.LOC_ADDRB,
		(SELECT k_code_name FROM kssc WHERE k_code = C.BZC_CD LIMIT 1) AS industry_name
	FROM g5_favorite F
	LEFT JOIN company C ON C.KEDCD = F.KEDCD
	WHERE F.mb_id = '" . sql_escape_string($member['mb_id']) . "'
	ORDER BY F.ms_id DESC
";

// 관심기업 조회
$interestedResult = sql_query($interestedSql, false);
if ($interestedResult) {
	for ($i = 0; $row = sql_fetch_array($interestedResult); $i++) {
		$kedcd = trim((string)($row['company_kedcd'] ?? ''));
		$matchStatus = null;
		if (isset($receivedStatusByKedcd[$kedcd])) {
			$matchStatus = $receivedStatusByKedcd[$kedcd];
		} else if (isset($sentStatusByKedcd[$kedcd])) {
			$matchStatus = $sentStatusByKedcd[$kedcd];
		}

		$interestedRows[] = array(
			'id' => 'fav_' . $kedcd,
			'company' => trim((string)($row['company_name'] ?? '')) ?: '정보 준비중',
			'bzno' => trim((string)($row['company_bzno'] ?? '')),
			'address' => trim((string)($row['LOC_ADDRA'] ?? '') . ' ' . (string)($row['LOC_ADDRB'] ?? '')) ?: '정보 준비중',
			'industry' => trim((string)($row['industry_name'] ?? '')) ?: '정보 준비중',
			'matchStatus' => $matchStatus,
			'starred' => true,
			'kedcd' => $kedcd,
			'detailUrl' => my_matching_company_detail_url($row['company_kedcd'] ?? ''),
		);
	}
}

// 프런트 데이터 패키징, JSON 직렬화 -> window.. 형태로 JS 주입
$matchingPageData = array(
	'received' => $receivedRows,
	'sent' => $sentRows,
	'interested' => $interestedRows,
);

$matchingPageDataJson = json_encode(
	$matchingPageData,
	JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);
if ($matchingPageDataJson === false) {
	$matchingPageDataJson = '{"received":[],"sent":[],"interested":[]}';
}

add_javascript('<script>window.__MATCHING_PAGE_DATA = ' . $matchingPageDataJson . ';</script>', 239);
add_javascript('<script>window.MATCH_REQUEST_CONTEXT = { defaultRequestMessage: ' . json_encode($defaultMessage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ', endpoint: "/ko/company_detail.php", sourcePage: "my_matching", requesterCompany: ' . json_encode($requesterCompanyData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ', target: null };</script>', 238);
?>

<div id="myMatchingPage">

<!-- SVG icon definitions -->
<svg id="svg-defs" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <symbol id="icon-magic-wand" viewBox="0 0 24 24">
      <path d="M12 2L9.19 8.63 2 9.24l5.46 4.73L5.82 21 12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61z"/>
    </symbol>
  </defs>
</svg>

<div id="matchingContent">
<div class="matching-main-tabs" id="mainTabs">
    <div class="matching-main-tabs_inner">
        <a href="#received" data-tab="received" class="matching-main-tab active">받은요청</a>
        <a href="#sent" data-tab="sent" class="matching-main-tab">보낸요청</a>
        <a href="#interested" data-tab="interested" class="matching-main-tab">관심기업</a>
        <div class="tab-indicator" id="tabIndicator"></div>
    </div>
</div>

<!-- Sub-filter Tabs (received) -->
<div class="matching-tab" d2 id="subTabsReceived">
    <a href="#" data-filter="all" class="active">전체<span id="rcvCountAll">7</span></a>
    <a href="#" data-filter="N">연결중<span id="rcvCountN">4</span></a>
    <a href="#" data-filter="Y">연결성사<span id="rcvCountY">2</span></a>
</div>

<!-- Sub-filter Tabs (sent) -->
<div class="matching-tab" d2 id="subTabsSent" style="display:none;">
    <a href="#" data-filter="all" class="active">전체<span id="sntCountAll">6</span></a>
    <a href="#" data-filter="N">연결중<span id="sntCountN">3</span></a>
    <a href="#" data-filter="Y">연결성사<span id="sntCountY">2</span></a>
</div>

<!-- ===== Tab Panels ===== -->

<!-- Received Panel -->
<div class="tab-panel active" id="panel-received">
    <div class="matching-list" id="receivedList"></div>
</div>

<!-- Sent Panel -->
<div class="tab-panel" id="panel-sent">
    <div class="matching-list" id="sentList"></div>
</div>

<!-- Interested Panel -->
<div class="tab-panel" id="panel-interested">
    <div class="matching-list" id="interestedList"></div>
</div>


<!-- Confirm Modal -->
<!-- 상세보기 팝업 -->
<div class="detail-popup-overlay" id="detailPopupOverlay">
    <div class="detail-popup-box">
        <div class="detail-popup-header">
            <span class="detail-popup-title" id="detailPopupTitle">기업 상세보기</span>
            <button type="button" class="detail-popup-close" id="detailPopupClose" aria-label="닫기">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <iframe class="detail-popup-iframe" id="detailPopupIframe" src="" title="기업 상세정보"></iframe>
    </div>
</div>

<div class="confirm-overlay" id="confirmOverlay">
    <div class="confirm-box">
        <h4 id="confirmTitle">알림</h4>
        <p id="confirmMsg"></p>
        <div class="confirm-actions">
            <button type="button" id="confirmCancel">취소</button>
            <button type="button" class="confirm-ok" id="confirmOk">확인</button>
        </div>
    </div>
</div>

<!-- AI Loading Overlay -->
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

<?php include __DIR__ . '/modal/match_request_modal.php'; ?>

<?php
include_once(G5_THEME_PATH . '/tail.php');
?>
