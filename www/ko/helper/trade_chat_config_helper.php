<?php
if (!defined('_GNUBOARD_')) exit;

/*
 * GetStream 채팅 연동 설정 헬퍼.
 * 실제 secret 값은 코드에 두지 않고 환경 변수 또는 서버 설정에서 읽는다.
 */

function trade_chat_config($key, $default = null) {
	$value = getenv($key);
	if ($value === false || $value === null || $value === '') {
		return $default;
	}

	return $value;
}

function trade_chat_settings() {
	return array(
		'api_key' => (string)trade_chat_config('GETSTREAM_API_KEY', ''),
		'api_secret' => (string)trade_chat_config('GETSTREAM_API_SECRET', ''),
		'app_id' => (string)trade_chat_config('GETSTREAM_APP_ID', ''),
		'channel_type' => (string)trade_chat_config('GETSTREAM_CHANNEL_TYPE', 'messaging'),
	);
}

function trade_chat_is_configured() {
	$settings = trade_chat_settings();
	return $settings['api_key'] !== '' && $settings['api_secret'] !== '';
}

