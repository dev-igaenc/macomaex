<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/mailer.lib.php');

if (!headers_sent()) {
    header('Content-Type: image/gif');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Expires: 0');
}

$track_token = isset($_GET['token']) ? trim((string) $_GET['token']) : '';
if ($track_token !== '' && function_exists('mailer_log_mark_opened')) {
    mailer_log_mark_opened($track_token);
}

echo base64_decode('R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==');
exit;
