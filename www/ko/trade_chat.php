<?php
include_once(__DIR__ . '/_common.php');
include_once(__DIR__ . '/helper/trade_chat_helper.php');

$g5['title'] = '거래 채팅';
$hide_theme_svisual = true;
$hide_theme_pagetop = true;

login_chk();

$roomId = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;
if ($roomId < 1) {
	alert('채팅방 정보가 올바르지 않습니다.', '/ko/my_matching.php');
}

$room = trade_chat_find_room_by_id($roomId);
if (empty($room['room_id'])) {
	alert('채팅방을 찾을 수 없습니다.', '/ko/my_matching.php');
}

$access = trade_chat_assert_member_access($room, $member);
if (empty($access['success'])) {
	alert($access['message'] ?? '채팅방 접근 권한이 없습니다.', '/ko/my_matching.php');
}

$tokenResult = trade_chat_issue_user_token($room, $member);
if (empty($tokenResult['success']) || empty($tokenResult['token'])) {
	alert('채팅 접속 토큰을 발급할 수 없습니다.', '/ko/my_matching.php');
}

$settings = trade_chat_settings();
$chatPageData = array(
	'apiKey' => $settings['api_key'],
	'channelType' => $room['stream_channel_type'] ?? 'messaging',
	'channelId' => $room['stream_channel_id'] ?? '',
	'channelCid' => $room['stream_channel_cid'] ?? '',
	'roomId' => (int)$room['room_id'],
	'user' => array(
		'id' => $access['stream_user_id'],
		'name' => $member['mb_id'] ?? $access['stream_user_id'],
	),
	'token' => $tokenResult['token'],
);

$trade_chat_css_ver = file_exists(__DIR__ . '/css/trade_chat.css') ? filemtime(__DIR__ . '/css/trade_chat.css') : time();
$stream_chat_vendor_js_ver = file_exists(__DIR__ . '/js/vendor/stream-chat.browser.full-bundle.min.js') ? filemtime(__DIR__ . '/js/vendor/stream-chat.browser.full-bundle.min.js') : time();
$trade_chat_js_ver = file_exists(__DIR__ . '/js/trade_chat.js') ? filemtime(__DIR__ . '/js/trade_chat.js') : time();

add_stylesheet('<link rel="stylesheet" href="/ko/css/trade_chat.css?ver=' . $trade_chat_css_ver . '" />', 200);
add_javascript('<script src="/ko/js/vendor/stream-chat.browser.full-bundle.min.js?ver=' . $stream_chat_vendor_js_ver . '"></script>', 200);
add_javascript('<script src="/ko/js/trade_chat.js?ver=' . $trade_chat_js_ver . '"></script>', 210);

include_once(G5_THEME_PATH . '/head.php');
?>

<script>
window.__TRADE_CHAT_PAGE_DATA = <?php echo json_encode($chatPageData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
</script>

<main class="trade-chat-page">
	<section class="trade-chat-shell">
		<header class="trade-chat-header">
			<div>
				<p class="trade-chat-kicker">GetStream 연결 테스트</p>
				<h1>거래 채팅방</h1>
				<p>Room #<?php echo (int)$room['room_id']; ?> · <?php echo get_text($room['stream_channel_cid'] ?: $room['stream_channel_type'] . ':' . $room['stream_channel_id']); ?></p>
			</div>
			<a class="trade-chat-back" href="/ko/my_matching.php">거래 히스토리로 돌아가기</a>
		</header>

		<div id="tradeChatStatus" class="trade-chat-status">채팅 서버에 연결 중입니다.</div>
		<div id="tradeChatMessages" class="trade-chat-messages" aria-live="polite"></div>

		<form id="tradeChatForm" class="trade-chat-form" autocomplete="off">
			<textarea id="tradeChatInput" rows="2" placeholder="메시지를 입력하세요." disabled></textarea>
			<button type="submit" disabled>전송</button>
		</form>
	</section>
</main>

<?php
include_once(G5_THEME_PATH . '/tail.php');
?>
