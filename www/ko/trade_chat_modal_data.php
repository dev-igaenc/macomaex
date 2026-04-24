<?php
include_once(__DIR__ . '/_common.php');
include_once(__DIR__ . '/helper/trade_chat_helper.php');

header('Content-Type: application/json; charset=utf-8');

$mode = isset($_REQUEST['mode']) ? trim((string)$_REQUEST['mode']) : 'bootstrap';
$initialRoomId = isset($_REQUEST['initial_room_id']) ? (int)$_REQUEST['initial_room_id'] : 0;
$memberRow = isset($member) && is_array($member) ? $member : array();

if ($mode === 'room') {
	$roomId = isset($_REQUEST['room_id']) ? (int)$_REQUEST['room_id'] : 0;
	$response = trade_chat_build_modal_room_payload($memberRow, $roomId);
} else {
	$response = trade_chat_build_modal_bootstrap($memberRow, $initialRoomId);
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit;
