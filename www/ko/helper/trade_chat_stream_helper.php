<?php
if (!defined('_GNUBOARD_')) exit;

/*
 * GetStream SDK 래퍼 헬퍼.
 * Phase 1에서는 안전한 스켈레톤만 제공하고, 실제 API 호출은 Phase 2부터 연결한다.
 */

include_once(__DIR__ . '/trade_chat_config_helper.php');

function trade_chat_stream_autoload_path() {
	return dirname(dirname(__DIR__)) . '/vendor/autoload.php';
}

function trade_chat_stream_sdk_available() {
	$autoload = trade_chat_stream_autoload_path();
	return file_exists($autoload);
}

function trade_chat_stream_require_sdk() {
	$autoload = trade_chat_stream_autoload_path();
	if (!file_exists($autoload)) {
		return false;
	}

	require_once($autoload);
	return true;
}

function trade_chat_stream_client() {
	if (!trade_chat_is_configured()) {
		return null;
	}

	if (!trade_chat_stream_require_sdk()) {
		return null;
	}

	/*
	 * 실제 SDK 인스턴스 생성은 패키지 설치 후 Phase 2에서 연결한다.
	 * 여기서는 외부 호출부가 공통 진입점을 가질 수 있도록 placeholder만 둔다.
	 */
	return null;
}

function trade_chat_stream_upsert_user($mbId, $memberProfile = array()) {
	$mbId = trim((string)$mbId);
	if ($mbId === '') {
		return array(
			'success' => false,
			'message' => '회원 ID가 비어 있습니다.',
		);
	}

	return array(
		'success' => false,
		'message' => 'Phase 1 스켈레톤 상태입니다.',
		'stream_user_id' => 'member:' . $mbId,
		'profile' => $memberProfile,
	);
}

function trade_chat_stream_get_or_create_channel($channelType, $channelId, $data = array()) {
	$channelType = trim((string)$channelType);
	$channelId = trim((string)$channelId);

	if ($channelType === '' || $channelId === '') {
		return array(
			'success' => false,
			'message' => '채널 식별자가 올바르지 않습니다.',
		);
	}

	return array(
		'success' => false,
		'message' => 'Phase 1 스켈레톤 상태입니다.',
		'channel_type' => $channelType,
		'channel_id' => $channelId,
		'channel_data' => $data,
	);
}

function trade_chat_stream_add_members($channelType, $channelId, $streamUserIds = array()) {
	return array(
		'success' => false,
		'message' => 'Phase 1 스켈레톤 상태입니다.',
		'channel_type' => trim((string)$channelType),
		'channel_id' => trim((string)$channelId),
		'members' => array_values((array)$streamUserIds),
	);
}

function trade_chat_stream_create_token($streamUserId) {
	$streamUserId = trim((string)$streamUserId);
	if ($streamUserId === '') {
		return array(
			'success' => false,
			'message' => 'Stream user id가 비어 있습니다.',
		);
	}

	return array(
		'success' => false,
		'message' => 'Phase 1 스켈레톤 상태입니다.',
		'stream_user_id' => $streamUserId,
		'token' => '',
	);
}

