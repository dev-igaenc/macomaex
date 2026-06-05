<?php
if (!defined('G5_SKIP_SESSION_ACTIVITY_UPDATE')) define('G5_SKIP_SESSION_ACTIVITY_UPDATE', true);
include_once('./_common.php');

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$is_logged_in = !empty($member['mb_id']);

echo json_encode(array(
    'success' => true,
    'logged_in' => $is_logged_in,
), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit;
