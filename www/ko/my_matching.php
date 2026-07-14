<?php
include_once(__DIR__ . '/_common.php');

// 비로그인 접근은 어떤 후속 로직보다 먼저 로그인 유도 모달로 분기한다.
login_chk();

include_once(__DIR__ . '/helper/trade_request_helper.php');
include_once(__DIR__ . '/helper/my_matching_helper.php');

$g5['title'] = '거래 히스토리';
$board['bo_10'] = '6';
$board['bo_9'] = '2';
$defaultMessage = trade_request_default_message();

$match_request_modal_ver = file_exists(__DIR__ . '/css/match-request-modal.css') ? filemtime(__DIR__ . '/css/match-request-modal.css') : time();
$my_matching_css_ver = file_exists(__DIR__ . '/css/my_matching.css') ? filemtime(__DIR__ . '/css/my_matching.css') : time();
$favorite_css_ver = file_exists(__DIR__ . '/css/favorite.css') ? filemtime(__DIR__ . '/css/favorite.css') : time();
$ai_loading_css_ver = file_exists(__DIR__ . '/css/ai-loading.css') ? filemtime(__DIR__ . '/css/ai-loading.css') : time();
$trade_chat_modal_css_ver = file_exists(__DIR__ . '/css/trade_chat_modal.css') ? filemtime(__DIR__ . '/css/trade_chat_modal.css') : time();

$favorite_js_ver = file_exists(__DIR__ . '/js/favorite.js') ? filemtime(__DIR__ . '/js/favorite.js') : time();
$ai_loading_js_ver = file_exists(__DIR__ . '/js/ai-loading.js') ? filemtime(__DIR__ . '/js/ai-loading.js') : time();
$match_request_modal_js_ver = file_exists(__DIR__ . '/js/match-request-modal.js') ? filemtime(__DIR__ . '/js/match-request-modal.js') : time();
$my_matching_js_ver = file_exists(__DIR__ . '/js/my_matching.js') ? filemtime(__DIR__ . '/js/my_matching.js') : time();
$stream_chat_vendor_js_ver = file_exists(__DIR__ . '/js/vendor/stream-chat.browser.full-bundle.min.js') ? filemtime(__DIR__ . '/js/vendor/stream-chat.browser.full-bundle.min.js') : time();
$trade_chat_modal_js_ver = file_exists(__DIR__ . '/js/trade_chat_modal.js') ? filemtime(__DIR__ . '/js/trade_chat_modal.js') : time();

// 테마 헤더 숨김, 페지 전용 스타일 범위 body class 고정
$hide_theme_svisual = true;
$hide_theme_pagetop = true;
$g5['body_script'] = ' class="page-my-matching"';

add_stylesheet('<link href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/variable/pretendardvariable-dynamic-subset.min.css" rel="stylesheet" />', 180);
add_stylesheet('<link rel="stylesheet" href="/ko/css/match-request-modal.css?ver=' . $match_request_modal_ver . '" />', 200);
add_stylesheet('<link rel="stylesheet" href="/ko/css/my_matching.css?ver=' . $my_matching_css_ver . '" />', 210);
add_stylesheet('<link rel="stylesheet" href="/ko/css/favorite.css?ver=' . $favorite_css_ver . '" />', 220);
add_stylesheet('<link rel="stylesheet" href="/ko/css/ai-loading.css?ver=' . $ai_loading_css_ver . '" />', 240);
add_stylesheet('<link rel="stylesheet" href="/ko/css/trade_chat_modal.css?ver=' . $trade_chat_modal_css_ver . '" />', 250);

add_javascript('<script src="/ko/js/favorite.js?ver=' . $favorite_js_ver . '"></script>', 200);
add_javascript('<script src="/ko/js/ai-loading.js?ver=' . $ai_loading_js_ver . '"></script>', 210);
add_javascript('<script src="/ko/js/match-request-modal.js?ver=' . $match_request_modal_js_ver . '"></script>', 220);
add_javascript('<script src="/ko/js/my_matching.js?ver=' . $my_matching_js_ver . '"></script>', 240);
add_javascript('<script src="/ko/js/vendor/stream-chat.browser.full-bundle.min.js?ver=' . $stream_chat_vendor_js_ver . '"></script>', 245);
add_javascript('<script src="/ko/js/trade_chat_modal.js?ver=' . $trade_chat_modal_js_ver . '"></script>', 250);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
	$action = trim((string)$_POST['action']);

	if ($action === 'trade_request_approval') {
		my_matching_handle_trade_request_approval($member, $_POST);
	}
	if ($action === 'trade_request_recipients') {
		my_matching_handle_trade_request_recipients($member, $_POST);
	}

	if ($action === 'my_matching_sent_status') {
		my_matching_handle_sent_status($member);
	}

	if ($action === 'trade_chat_retry') {
		my_matching_handle_trade_chat_retry($member, $_POST);
	}
}

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
		'creditUsed' => (int)($row['credit_used'] ?? 0),
		'chatRoomId' => (int)($row['chat_room_id'] ?? 0),
		'chatEnabled' => ((int)($row['approval_status'] ?? 0) === 1 && (int)($row['chat_room_id'] ?? 0) > 0 && ($row['chat_room_status'] ?? '') === 'active'),
		'chatRoomStatus' => trim((string)($row['chat_room_status'] ?? '')),
		'streamChannelCid' => trim((string)($row['stream_channel_cid'] ?? '')),
		'message' => array(
			'matchType' => my_matching_trade_type_label($row['trade_type'] ?? ''),
			'direction' => my_matching_trade_direction_label($row['trade_direction'] ?? ''),
			'types' => my_matching_trade_purpose_labels($row['trade_purpose'] ?? ''),
			'item' => trim((string)($row['desired_item'] ?? '')),
			'extra' => $messageContent,
		),
	);
}

$memberCompanyCode = trim((string)($member['mb_company'] ?? ''));
$linkedCompany = array();
if ($memberCompanyCode !== '' && !preg_match('/^M[0-9]{9}$/', $memberCompanyCode)) {
	$linkedCompany = sql_fetch("SELECT KEDCD, BZNO FROM company WHERE KEDCD='" . sql_escape_string($memberCompanyCode) . "' LIMIT 1");
}
$memberBzno = preg_replace('/[^0-9]/', '', (string)($linkedCompany['BZNO'] ?? ($member['mb_4'] ?? '')));
$memberHasBzno = ($memberBzno !== '');
$requiresCompanyConnection = empty($linkedCompany['KEDCD']) || !$memberHasBzno;
$companyConnectUrl = '/account/company?return_url=' . urlencode('/account/trades');

// 관심기업 탭 공통 모달에서도 같은 "우리 기업 정보"를 쓰도록 로그인 기업 데이터를 미리 준비한다.
$requesterCompanyData = $memberHasBzno ? trade_request_member_company_data($memberBzno) : array();

$memberIdSql = sql_escape_string($member['mb_id']);
$memberBznoSql = $memberHasBzno ? sql_escape_string($memberBzno) : '';

$favoriteKedcdMap = array();
if ($memberHasBzno) {
	$favoriteResult = sql_query("SELECT KEDCD FROM g5_favorite WHERE mb_id = '{$memberIdSql}'", false);
	if ($favoriteResult) {
		for ($i = 0; $fav = sql_fetch_array($favoriteResult); $i++) {
			$favKedcd = trim((string)($fav['KEDCD'] ?? ''));
			if ($favKedcd !== '') {
				$favoriteKedcdMap[$favKedcd] = true;
			}
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
		TR.credit_used,
		TR.approval_status,
		TR.chat_room_id,
		TCR.room_status AS chat_room_status,
		TCR.stream_channel_cid,
		C.BZNO AS company_bzno,
		C.KEDCD AS company_kedcd,
		C.ENP_NM AS company_name,
		C.LOC_ADDRA,
		C.LOC_ADDRB,
		(SELECT k_code_name FROM kssc_11 WHERE k_code = C.BZC_CD LIMIT 1) AS industry_name,
		COALESCE(NULLIF(TR.request_member_name, ''), NULLIF(M.mb_6, ''), NULLIF(M.mb_name, '')) AS manager_name,
		M.mb_5 AS manager_email
	FROM trade_request TR
	LEFT JOIN trade_chat_room TCR ON TCR.room_id = TR.chat_room_id
	LEFT JOIN company C ON C.BZNO = TR.request_bzno
	LEFT JOIN g5_member M ON M.mb_id = TR.request_mb_id
	WHERE TR.target_bzno = '{$memberBznoSql}'
	ORDER BY TR.tr_no DESC
";
// 받은 요청은 요청 기업 기준으로 회사/담당자 정보를 매칭한다.
if ($memberHasBzno) {
	$receivedResult = sql_query($receivedSql, false);
	if ($receivedResult) {
		for ($i = 0; $row = sql_fetch_array($receivedResult); $i++) {
			$kedcd = trim((string)($row['company_kedcd'] ?? ''));
			$receivedRows[] = my_matching_build_item($row, isset($favoriteKedcdMap[$kedcd]));
		}
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
		TR.credit_used,
		TR.approval_status,
		TR.chat_room_id,
		TCR.room_status AS chat_room_status,
		TCR.stream_channel_cid,
		C.BZNO AS company_bzno,
		C.KEDCD AS company_kedcd,
		C.ENP_NM AS company_name,
		C.LOC_ADDRA,
		C.LOC_ADDRB,
		(SELECT k_code_name FROM kssc_11 WHERE k_code = C.BZC_CD LIMIT 1) AS industry_name,
		COALESCE(NULLIF(TR.target_member_name, ''), NULLIF(M.mb_6, ''), NULLIF(M.mb_name, '')) AS manager_name,
		M.mb_5 AS manager_email
	FROM trade_request TR
	LEFT JOIN trade_chat_room TCR ON TCR.room_id = TR.chat_room_id
	LEFT JOIN company C ON C.KEDCD = TR.target_kedcd
	LEFT JOIN g5_member M ON M.mb_id = TR.target_mb_id
	WHERE TR.request_bzno = '{$memberBznoSql}'
	ORDER BY TR.tr_no DESC
";

// 보낸 요청은 대상 기업 기준으로 회사 정보를 매칭한다.
if ($memberHasBzno) {
	$sentResult = sql_query($sentSql, false);
	if ($sentResult) {
		for ($i = 0; $row = sql_fetch_array($sentResult); $i++) {
			$kedcd = trim((string)($row['company_kedcd'] ?? ''));
			$sentRows[] = my_matching_build_item($row, isset($favoriteKedcdMap[$kedcd]));
		}
	}
}

$receivedStatusByKedcd = array();
$receivedChatByKedcd = array();
for ($i = 0; $i < count($receivedRows); $i++) {
	$kedcd = trim((string)($receivedRows[$i]['kedcd'] ?? ''));
	if ($kedcd === '') continue;
	$receivedStatusByKedcd[$kedcd] = 'received-' . (string)$receivedRows[$i]['status'];
	$receivedChatByKedcd[$kedcd] = array(
		'chatRoomId' => (int)($receivedRows[$i]['chatRoomId'] ?? 0),
		'chatEnabled' => !empty($receivedRows[$i]['chatEnabled']),
		'chatRoomStatus' => trim((string)($receivedRows[$i]['chatRoomStatus'] ?? '')),
		'creditUsed' => (int)($receivedRows[$i]['creditUsed'] ?? 0),
	);
}

$sentStatusByKedcd = array();
$sentChatByKedcd = array();
for ($i = 0; $i < count($sentRows); $i++) {
	$kedcd = trim((string)($sentRows[$i]['kedcd'] ?? ''));
	if ($kedcd === '') continue;
	$sentStatusByKedcd[$kedcd] = 'sent-' . (string)$sentRows[$i]['status'];
	$sentChatByKedcd[$kedcd] = array(
		'chatRoomId' => (int)($sentRows[$i]['chatRoomId'] ?? 0),
		'chatEnabled' => !empty($sentRows[$i]['chatEnabled']),
		'chatRoomStatus' => trim((string)($sentRows[$i]['chatRoomStatus'] ?? '')),
		'creditUsed' => (int)($sentRows[$i]['creditUsed'] ?? 0),
	);
}

$interestedRows = array();
$interestedSql = "
	SELECT
		F.KEDCD AS company_kedcd,
		C.BZNO AS company_bzno,
		C.ENP_NM AS company_name,
		C.LOC_ADDRA,
		C.LOC_ADDRB,
		(SELECT k_code_name FROM kssc_11 WHERE k_code = C.BZC_CD LIMIT 1) AS industry_name
	FROM g5_favorite F
	LEFT JOIN company C ON C.KEDCD = F.KEDCD
	WHERE F.mb_id = '{$memberIdSql}'
	ORDER BY F.ms_id DESC
";

// 관심기업은 기존 요청 상태를 함께 계산해 버튼 상태를 결정한다.
if ($memberHasBzno) {
	$interestedResult = sql_query($interestedSql, false);
	if ($interestedResult) {
		for ($i = 0; $row = sql_fetch_array($interestedResult); $i++) {
			$kedcd = trim((string)($row['company_kedcd'] ?? ''));
			$matchStatus = null;
			$chatMeta = array();
			if (isset($receivedStatusByKedcd[$kedcd])) {
				$matchStatus = $receivedStatusByKedcd[$kedcd];
				$chatMeta = $receivedChatByKedcd[$kedcd] ?? array();
			} else if (isset($sentStatusByKedcd[$kedcd])) {
				$matchStatus = $sentStatusByKedcd[$kedcd];
				$chatMeta = $sentChatByKedcd[$kedcd] ?? array();
			}

			$interestedRows[] = array(
				'id' => 'fav_' . $kedcd,
				'company' => trim((string)($row['company_name'] ?? '')) ?: '정보 준비중',
				'bzno' => trim((string)($row['company_bzno'] ?? '')),
				'address' => trim((string)($row['LOC_ADDRA'] ?? '') . ' ' . (string)($row['LOC_ADDRB'] ?? '')) ?: '정보 준비중',
				'industry' => trim((string)($row['industry_name'] ?? '')) ?: '정보 준비중',
				'matchStatus' => $matchStatus,
				'chatRoomId' => (int)($chatMeta['chatRoomId'] ?? 0),
				'chatEnabled' => !empty($chatMeta['chatEnabled']),
				'chatRoomStatus' => trim((string)($chatMeta['chatRoomStatus'] ?? '')),
				'creditUsed' => (int)($chatMeta['creditUsed'] ?? 0),
				'starred' => true,
				'kedcd' => $kedcd,
				'detailUrl' => my_matching_company_detail_url($row['company_kedcd'] ?? ''),
			);
		}
	}
}

$csrfToken = my_matching_create_csrf_token();

// 프런트 데이터 패키징, JSON 직렬화 -> window.. 형태로 JS 주입
$matchingPageData = array(
	'received' => $receivedRows,
	'sent' => $sentRows,
	'interested' => $interestedRows,
	'csrfToken' => $csrfToken,
	'requiresCompanyConnection' => $requiresCompanyConnection,
	'companyConnectUrl' => $companyConnectUrl,
);

$matchingPageDataJson = json_encode(
	$matchingPageData,
	JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
);
if ($matchingPageDataJson === false) {
	$matchingPageDataJson = '{"received":[],"sent":[],"interested":[],"csrfToken":""}';
}

$defaultMessageJson = json_encode($defaultMessage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
if ($defaultMessageJson === false) {
	$defaultMessageJson = '""';
}

$requesterCompanyDataJson = json_encode($requesterCompanyData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
if ($requesterCompanyDataJson === false) {
	$requesterCompanyDataJson = '{}';
}

// 공통 모달은 이 컨텍스트만 보고 company_detail 저장 로직을 공통으로 호출한다.
add_javascript('<script>window.__MATCHING_PAGE_DATA = ' . $matchingPageDataJson . ';</script>', 239);
add_javascript('<script>window.MATCH_REQUEST_CONTEXT = { defaultRequestMessage: ' . $defaultMessageJson . ', endpoint: "/ko/company_detail.php", verifyEndpoint: "/ko/trade_request_verify_bzno.php", sourcePage: "my_matching", isLoggedIn: true, loginCheckUrl: "", autoOpenOnLoad: false, requesterCompany: ' . $requesterCompanyDataJson . ', target: null };</script>', 238);
add_javascript('<script>window.__TRADE_CHAT_MODAL_CONTEXT = { isLoggedIn: true, loginUrl: "/bbs/login.php?url=' . urlencode('/account/trades') . '", bootstrapUrl: "/ko/trade_chat_modal_data.php", uiEventUrl: "/ko/trade_chat_ui_event.php" };</script>', 237);
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

<div class="confirm-overlay" id="recipientAssignOverlay">
    <div class="confirm-box">
        <h4>수신 담당자 지정</h4>
        <p>수락 후 요청자와 1:1 채팅을 진행할 담당자를 선택해 주세요.</p>
        <div class="matching-recipient-picker" id="recipientAssignPicker">
            <input type="hidden" id="recipientAssignSelect" value="">
            <button type="button" class="matching-recipient-select" id="recipientAssignTrigger" aria-expanded="false" disabled>담당자 정보를 불러오는 중입니다.</button>
            <div class="matching-recipient-options" id="recipientAssignOptions" hidden></div>
        </div>
        <div class="confirm-actions">
            <button type="button" id="recipientAssignCancel">취소</button>
            <button type="button" class="confirm-ok" id="recipientAssignOk" disabled>수락하기</button>
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

</div>

<?php include __DIR__ . '/modal/match_request_modal.php'; ?>
<?php include __DIR__ . '/modal/trade_chat_modal.php'; ?>

<?php
include_once(G5_THEME_PATH . '/tail.php');
?>
