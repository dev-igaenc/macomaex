<?php
if (!defined('STDIN')) {
	header('Content-Type: text/plain; charset=utf-8');
}

function trade_chat_summary_parse_entry_lines($lines) {
	$entry = array();
	foreach ($lines as $line) {
		$line = trim((string)$line);
		if ($line === '' || $line === '---') continue;
		if ($line[0] === '[' && substr($line, -1) === ']') {
			$entry['logged_at'] = substr($line, 1, -1);
			continue;
		}
		$pos = strpos($line, ':');
		if ($pos === false) continue;
		$key = trim(substr($line, 0, $pos));
		$value = trim(substr($line, $pos + 1));
		if ($key !== '') {
			$entry[$key] = $value;
		}
	}
	return $entry;
}

function trade_chat_summary_read_entries($filePath) {
	$raw = @file($filePath, FILE_IGNORE_NEW_LINES);
	if (!is_array($raw)) return array();

	$entries = array();
	$bucket = array();
	foreach ($raw as $line) {
		$line = rtrim((string)$line, "\r\n");
		if ($line === '<?php exit; ?>') continue;
		if (trim($line) === '---') {
			$entry = trade_chat_summary_parse_entry_lines($bucket);
			if (!empty($entry)) $entries[] = $entry;
			$bucket = array();
			continue;
		}
		$bucket[] = $line;
	}
	if (!empty($bucket)) {
		$entry = trade_chat_summary_parse_entry_lines($bucket);
		if (!empty($entry)) $entries[] = $entry;
	}
	return $entries;
}

function trade_chat_summary_inc(&$map, $key) {
	$key = trim((string)$key);
	if ($key === '') $key = '(empty)';
	if (!isset($map[$key])) $map[$key] = 0;
	$map[$key]++;
}

function trade_chat_summary_print_map($title, $map) {
	echo $title . PHP_EOL;
	if (empty($map)) {
		echo "- (none)" . PHP_EOL . PHP_EOL;
		return;
	}
	arsort($map);
	foreach ($map as $key => $count) {
		echo "- {$key}: {$count}" . PHP_EOL;
	}
	echo PHP_EOL;
}

$inputDate = '';
if (PHP_SAPI === 'cli') {
	$inputDate = isset($argv[1]) ? trim((string)$argv[1]) : '';
} else {
	$inputDate = trim((string)($_GET['date'] ?? ''));
}

$dateYmd = preg_replace('/[^0-9]/', '', $inputDate);
if ($dateYmd === '') $dateYmd = date('Ymd');
if (!preg_match('/^[0-9]{8}$/', $dateYmd)) {
	echo "invalid date. use YYYYMMDD" . PHP_EOL;
	exit(1);
}

$logFile = dirname(__DIR__) . '/logs/chat/chat_log_' . $dateYmd . '.php';
if (!is_file($logFile)) {
	echo "log file not found: {$logFile}" . PHP_EOL;
	exit(1);
}

$entries = trade_chat_summary_read_entries($logFile);
$scanEntries = array();
foreach ($entries as $entry) {
	$eventType = trim((string)($entry['event_type'] ?? ''));
	if (strpos($eventType, 'file_scan_') !== 0) continue;
	$scanEntries[] = $entry;
}

$byTypeStatus = array();
$byReason = array();
$byActionError = array();
$byDeleteStatus = array();
$byNoticeStatus = array();
$failedRows = array();

foreach ($scanEntries as $entry) {
	$type = trim((string)($entry['event_type'] ?? ''));
	$status = trim((string)($entry['event_status'] ?? ''));
	$reason = trim((string)($entry['reason'] ?? ''));
	$actionError = trim((string)($entry['action_error_code'] ?? ''));
	$deleteStatus = trim((string)($entry['delete_status'] ?? ''));
	$noticeStatus = trim((string)($entry['notice_status'] ?? ''));

	trade_chat_summary_inc($byTypeStatus, $type . ' / ' . ($status === '' ? '(empty)' : $status));
	trade_chat_summary_inc($byReason, $reason);
	if ($actionError !== '') trade_chat_summary_inc($byActionError, $actionError);
	if ($deleteStatus !== '') trade_chat_summary_inc($byDeleteStatus, $deleteStatus);
	if ($noticeStatus !== '') trade_chat_summary_inc($byNoticeStatus, $noticeStatus);

	if ($status === 'fail') {
		$failedRows[] = array(
			'logged_at' => (string)($entry['logged_at'] ?? ''),
			'event_type' => $type,
			'reason' => $reason,
			'room_id' => (string)($entry['room_id'] ?? ''),
			'message_id' => (string)($entry['message_id'] ?? ''),
			'message' => (string)($entry['message'] ?? ''),
		);
	}
}

echo "trade chat file-scan summary" . PHP_EOL;
echo "date: {$dateYmd}" . PHP_EOL;
echo "file: {$logFile}" . PHP_EOL;
echo "all_entries: " . count($entries) . PHP_EOL;
echo "file_scan_entries: " . count($scanEntries) . PHP_EOL . PHP_EOL;

trade_chat_summary_print_map("counts by event_type/status", $byTypeStatus);
trade_chat_summary_print_map("counts by reason", $byReason);
trade_chat_summary_print_map("counts by action_error_code", $byActionError);
trade_chat_summary_print_map("counts by delete_status", $byDeleteStatus);
trade_chat_summary_print_map("counts by notice_status", $byNoticeStatus);

echo "latest fail rows (up to 10)" . PHP_EOL;
if (empty($failedRows)) {
	echo "- (none)" . PHP_EOL;
	exit(0);
}

$failedRows = array_slice(array_reverse($failedRows), 0, 10);
foreach ($failedRows as $row) {
	echo "- [" . $row['logged_at'] . "] "
		. $row['event_type']
		. " reason=" . ($row['reason'] === '' ? '(empty)' : $row['reason'])
		. " room_id=" . ($row['room_id'] === '' ? '-' : $row['room_id'])
		. " message_id=" . ($row['message_id'] === '' ? '-' : $row['message_id'])
		. " msg=" . $row['message']
		. PHP_EOL;
}
