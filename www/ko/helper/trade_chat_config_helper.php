<?php
if (!defined('_GNUBOARD_')) exit;

/*
 * GetStream 채팅 연동 설정 헬퍼.
 * 실제 secret 값은 코드에 두지 않고 환경 변수 또는 서버 설정에서 읽는다.
 */

function trade_chat_env_paths() {
	return array(
		dirname(__DIR__, 3) . '/.env',
		dirname(__DIR__, 2) . '/.env',
	);
}

function trade_chat_load_env_file() {
	static $loaded = false;
	if ($loaded) return;
	$loaded = true;

	$paths = trade_chat_env_paths();
	foreach ($paths as $path) {
		if (!is_readable($path)) continue;

		$lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		if (!is_array($lines)) continue;

		foreach ($lines as $line) {
			$line = trim((string)$line);
			if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) continue;

			list($key, $value) = explode('=', $line, 2);
			$key = trim($key);
			$value = trim($value);
			if ($key === '' || getenv($key) !== false) continue;

			if (
				(strlen($value) >= 2)
				&& (($value[0] === '"' && substr($value, -1) === '"') || ($value[0] === "'" && substr($value, -1) === "'"))
			) {
				$value = substr($value, 1, -1);
			}

			putenv($key . '=' . $value);
			$_ENV[$key] = $value;
			$_SERVER[$key] = $value;
		}

		return;
	}
}

function trade_chat_config($key, $default = null) {
	trade_chat_load_env_file();

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
		'base_url' => (string)trade_chat_config('GETSTREAM_BASE_URL', 'https://chat.stream-io-api.com'),
	);
}

function trade_chat_is_configured() {
	$settings = trade_chat_settings();
	return $settings['api_key'] !== '' && $settings['api_secret'] !== '';
}
