<?php
if (!defined('_GNUBOARD_')) exit;

/*
 * my_matching.php 전용 서버 헬퍼.
 * 받은 요청 승인/거절 처리와 보낸 요청 상태 조회를 페이지 밖으로 분리해 관리한다.
 */

function my_matching_json_response($payload) {
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	exit;
}

function my_matching_text_response($text) {
	header('Content-Type: text/plain; charset=utf-8');
	echo (string)$text;
	exit;
}

function my_matching_member_bzno($member) {
	return preg_replace('/[^0-9]/', '', (string)($member['mb_4'] ?? ''));
}

function my_matching_handle_trade_request_approval($member, $request) {
	if (empty($member['mb_id'])) {
		my_matching_text_response('501');
	}

	$idx = isset($request['idx']) ? (int)$request['idx'] : 0;
	$typ = isset($request['typ']) ? trim((string)$request['typ']) : '';
	$memberBzno = my_matching_member_bzno($member);
	$nextStatus = '';

	if ($typ === 'accept') $nextStatus = '1';
	if ($typ === 'reject') $nextStatus = '2';

	if ($idx < 1 || $memberBzno === '' || $nextStatus === '') {
		my_matching_text_response('500');
	}

	$memberBznoSql = sql_escape_string($memberBzno);
	$targetRequest = sql_fetch("
		select tr_no, approval_status
		from trade_request
		where tr_no='{$idx}'
		  and target_bzno='{$memberBznoSql}'
		limit 1
	");

	if (empty($targetRequest['tr_no'])) {
		my_matching_text_response('500');
	}

	if ($nextStatus === '1') {
		$updateSql = "
			update trade_request
			set approval_status='1',
				approved_at=now(),
				rejected_at=NULL
			where tr_no='{$idx}'
			  and target_bzno='{$memberBznoSql}'
		";
	} else {
		$updateSql = "
			update trade_request
			set approval_status='2',
				rejected_at=now(),
				approved_at=NULL
			where tr_no='{$idx}'
			  and target_bzno='{$memberBznoSql}'
		";
	}

	$updated = sql_query($updateSql, false);
	if (!$updated) {
		my_matching_text_response('500');
	}

	my_matching_text_response('1000|' . $nextStatus);
}

function my_matching_handle_sent_status($member) {
	if (empty($member['mb_id'])) {
		my_matching_json_response(array(
			'success' => false,
			'login' => false,
			'items' => array(),
		));
	}

	$memberBzno = my_matching_member_bzno($member);
	if ($memberBzno === '') {
		my_matching_json_response(array(
			'success' => false,
			'items' => array(),
		));
	}

	$memberBznoSql = sql_escape_string($memberBzno);
	$statusItems = array();
	$statusResult = sql_query("
		select tr_no, approval_status
		from trade_request
		where request_bzno='{$memberBznoSql}'
		order by tr_no desc
	", false);

	if ($statusResult) {
		for ($i = 0; $row = sql_fetch_array($statusResult); $i++) {
			$statusItems[] = array(
				'id' => (int)$row['tr_no'],
				'approval_status' => (int)$row['approval_status'],
			);
		}
	}

	my_matching_json_response(array(
		'success' => true,
		'items' => $statusItems,
	));
}
