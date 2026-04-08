<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/mailer.lib.php');

$track_token = isset($_GET['token']) ? trim((string) $_GET['token']) : '';
$target_url = isset($_GET['target']) ? trim((string) $_GET['target']) : '';
$redirect_url = defined('G5_URL') && G5_URL ? G5_URL : '/';

if (function_exists('mailer_tracking_resolve_target_url')) {
    $resolved_target = mailer_tracking_resolve_target_url($target_url);
    if ($resolved_target !== '') {
        $redirect_url = $resolved_target;
    }
}

if ($track_token !== '' && function_exists('mailer_log_mark_clicked')) {
    mailer_log_mark_clicked($track_token);
}

goto_url($redirect_url);
