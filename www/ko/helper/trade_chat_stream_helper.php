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

	$settings = trade_chat_settings();

	try {
		return new \GetStream\ChatClient(
			$settings['api_key'],
			$settings['api_secret'],
			$settings['base_url']
		);
	} catch (\Throwable $e) {
		return null;
	}
}

function trade_chat_stream_upsert_user($mbId, $memberProfile = array()) {
	$mbId = trim((string)$mbId);
	$streamUserId = function_exists('trade_chat_make_stream_user_id')
		? trade_chat_make_stream_user_id($mbId)
		: 'member_' . preg_replace('/[^a-z0-9@_. -]/i', '_', $mbId);
	if ($mbId === '') {
		return array(
			'success' => false,
			'message' => '회원 ID가 비어 있습니다.',
		);
	}

	$client = trade_chat_stream_client();
	if (!$client) {
		return array(
			'success' => false,
			'message' => 'GetStream client를 초기화할 수 없습니다.',
			'stream_user_id' => $streamUserId,
		);
	}

	$name = trim((string)($memberProfile['name'] ?? $mbId));
	if ($name === '') $name = $mbId;

	try {
		$response = $client->updateUsers(new \GetStream\GeneratedModels\UpdateUsersRequest(
			users: array(
				$streamUserId => new \GetStream\GeneratedModels\UserRequest(
					id: $streamUserId,
					name: $name,
					custom: (object)array(
						'mb_id' => $mbId,
						'company_bzno' => (string)($memberProfile['company_bzno'] ?? ''),
						'company_kedcd' => (string)($memberProfile['company_kedcd'] ?? ''),
					),
				),
			),
		));

		return array(
			'success' => $response->isSuccessful(),
			'status_code' => $response->getStatusCode(),
			'stream_user_id' => $streamUserId,
		);
	} catch (\Throwable $e) {
		return array(
			'success' => false,
			'message' => $e->getMessage(),
			'stream_user_id' => $streamUserId,
		);
	}
}

function trade_chat_stream_get_or_create_channel($channelType, $channelId, $data = array()) {
	$channelType = function_exists('trade_chat_normalize_channel_type')
		? trade_chat_normalize_channel_type($channelType)
		: trim((string)$channelType);
	$channelId = trim((string)$channelId);

	if ($channelType === '' || $channelId === '') {
		return array(
			'success' => false,
			'message' => '채널 식별자가 올바르지 않습니다.',
		);
	}

	if (function_exists('trade_chat_is_valid_channel_id') && !trade_chat_is_valid_channel_id($channelId)) {
		return array(
			'success' => false,
			'message' => '채널 ID 형식이 올바르지 않습니다.',
			'channel_type' => $channelType,
			'channel_id' => $channelId,
		);
	}

	$client = trade_chat_stream_client();
	if (!$client) {
		return array(
			'success' => false,
			'message' => 'GetStream client를 초기화할 수 없습니다.',
			'channel_type' => $channelType,
			'channel_id' => $channelId,
		);
	}

	$createdById = trim((string)($data['created_by_id'] ?? ''));
	$members = array_values(array_filter((array)($data['members'] ?? array())));
	if ($createdById === '' && !empty($members)) {
		$createdById = (string)$members[0];
	}

	if ($createdById === '') {
		return array(
			'success' => false,
			'message' => '채널 생성자 정보가 없습니다.',
			'channel_type' => $channelType,
			'channel_id' => $channelId,
		);
	}

	$memberRequests = array();
	foreach ($members as $streamUserId) {
		$streamUserId = trim((string)$streamUserId);
		if ($streamUserId === '') continue;
		$memberRequests[] = new \GetStream\GeneratedModels\ChannelMemberRequest(userID: $streamUserId);
	}

	try {
		$response = $client->getOrCreateChannel(
			$channelType,
			$channelId,
			new \GetStream\GeneratedModels\ChannelGetOrCreateRequest(
				data: new \GetStream\GeneratedModels\ChannelInput(
					createdByID: $createdById,
					members: $memberRequests,
					custom: (object)array(
						'room_id' => (string)($data['room_id'] ?? ''),
						'company_pair_key' => (string)($data['company_pair_key'] ?? ''),
						'source_tr_no' => (string)($data['source_tr_no'] ?? ''),
						'latest_tr_no' => (string)($data['latest_tr_no'] ?? ''),
					),
				),
			),
		);

		$responseData = $response->getData();
		$channel = $responseData->channel ?? null;

		return array(
			'success' => $response->isSuccessful(),
			'status_code' => $response->getStatusCode(),
			'channel_type' => $channelType,
			'channel_id' => $channelId,
			'channel_cid' => $channel->cid ?? ($channelType . ':' . $channelId),
			'member_count' => $channel->memberCount ?? count($memberRequests),
		);
	} catch (\Throwable $e) {
		return array(
			'success' => false,
			'message' => $e->getMessage(),
			'channel_type' => $channelType,
			'channel_id' => $channelId,
		);
	}
}

function trade_chat_stream_add_members($channelType, $channelId, $streamUserIds = array()) {
	return trade_chat_stream_get_or_create_channel($channelType, $channelId, array(
		'members' => array_values((array)$streamUserIds),
	));
}

function trade_chat_stream_create_token($streamUserId) {
	$streamUserId = trim((string)$streamUserId);
	if ($streamUserId === '') {
		return array(
			'success' => false,
			'message' => 'Stream user id가 비어 있습니다.',
		);
	}

	$client = trade_chat_stream_client();
	if (!$client) {
		return array(
			'success' => false,
			'message' => 'GetStream client를 초기화할 수 없습니다.',
			'stream_user_id' => $streamUserId,
			'token' => '',
		);
	}

	try {
		return array(
			'success' => true,
			'stream_user_id' => $streamUserId,
			'token' => $client->createUserToken($streamUserId, array(), 3600),
			'expires_in' => 3600,
		);
	} catch (\Throwable $e) {
		return array(
			'success' => false,
			'message' => $e->getMessage(),
			'stream_user_id' => $streamUserId,
			'token' => '',
		);
	}
}

function trade_chat_stream_get_latest_message_preview($channelType, $channelId, $excludeStreamUserId = '') {
	$channelType = function_exists('trade_chat_normalize_channel_type')
		? trade_chat_normalize_channel_type($channelType)
		: trim((string)$channelType);
	$channelId = trim((string)$channelId);
	$excludeStreamUserId = trim((string)$excludeStreamUserId);

	if ($channelType === '' || $channelId === '') {
		return '';
	}

	$client = trade_chat_stream_client();
	if (!$client) {
		return '';
	}

	try {
		$response = $client->queryChannels(
			new \GetStream\GeneratedModels\QueryChannelsRequest(
				filterConditions: (object)array(
					'id' => $channelId,
					'type' => $channelType,
				),
				state: true,
				messageLimit: 10,
				memberLimit: 0,
				limit: 1,
			)
		);

		$data = $response->getData();
		$channels = $data->channels ?? array();
		if (empty($channels[0])) {
			return '';
		}

		$messages = $channels[0]->messages ?? array();
		if (empty($messages)) {
			return '';
		}

		$candidateText = '';
		for ($i = count($messages) - 1; $i >= 0; $i--) {
			$message = $messages[$i];
			$messageUserId = trim((string)($message->user->id ?? ''));
			$text = trim((string)($message->text ?? ''));
			if ($text === '') {
				$attachments = $message->attachments ?? array();
				if (!empty($attachments)) {
					$text = '첨부파일이 있습니다.';
				}
			}

			if ($text === '') {
				continue;
			}

			if ($excludeStreamUserId !== '' && $messageUserId === $excludeStreamUserId) {
				if ($candidateText === '') {
					$candidateText = $text;
				}
				continue;
			}

			$candidateText = $text;
			break;
		}

		$candidateText = preg_replace('/\s+/u', ' ', $candidateText);
		return trim((string)$candidateText);
	} catch (\Throwable $e) {
		return '';
	}
}
