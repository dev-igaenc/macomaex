<?php
if (!defined('_GNUBOARD_')) exit;

include_once(G5_PHPMAILER_PATH.'/PHPMailerAutoload.php');

if (!function_exists('mailer_log_table_name')) {
    function mailer_log_table_name()
    {
        global $g5;

        if (isset($g5['mail_log_table']) && $g5['mail_log_table']) {
            return $g5['mail_log_table'];
        }

        if (defined('G5_TABLE_PREFIX')) {
            return G5_TABLE_PREFIX.'mail_log';
        }

        return 'g5_mail_log';
    }
}

if (!function_exists('mailer_log_table_exists')) {
    function mailer_log_table_exists()
    {
        static $table_exists = null;

        if ($table_exists !== null) {
            return $table_exists;
        }

        if (!function_exists('sql_query')) {
            $table_exists = false;
            return $table_exists;
        }

        $table = mailer_log_table_name();
        $table_exists = (bool) sql_query(" DESC `{$table}` ", false);

        return $table_exists;
    }
}

if (!function_exists('mailer_log_column_exists')) {
    function mailer_log_column_exists($column, $refresh = false)
    {
        static $columns = null;

        $column = trim((string) $column);
        if ($column === '') {
            return false;
        }

        if (!mailer_log_table_exists()) {
            return false;
        }

        if ($refresh || $columns === null) {
            $columns = array();
            $table = mailer_log_table_name();
            $result = sql_query(" SHOW COLUMNS FROM `{$table}` ", false);

            if ($result) {
                while ($row = sql_fetch_array($result)) {
                    if (!empty($row['Field'])) {
                        $columns[$row['Field']] = true;
                    }
                }
            }
        }

        return isset($columns[$column]);
    }
}

if (!function_exists('mailer_log_ensure_open_tracking_schema')) {
    function mailer_log_ensure_open_tracking_schema()
    {
        static $checked = false;
        static $ready = false;

        if ($checked) {
            return $ready;
        }

        $checked = true;

        if (!mailer_log_table_exists()) {
            return false;
        }

        $table = mailer_log_table_name();
        $id_column = sql_fetch(" SHOW COLUMNS FROM `{$table}` LIKE 'id' ", false);
        $primary_key = sql_fetch(" SHOW INDEX FROM `{$table}` WHERE Key_name = 'PRIMARY' ", false);

        $ready = $id_column
            && strtoupper((string) $id_column['Null']) === 'NO'
            && strtoupper((string) $id_column['Key']) === 'PRI'
            && stripos((string) $id_column['Extra'], 'auto_increment') !== false
            && (bool) $primary_key
            && mailer_log_column_exists('track_token', true)
            && mailer_log_column_exists('first_opened_at')
            && mailer_log_column_exists('last_opened_at')
            && mailer_log_column_exists('open_count')
            && mailer_log_column_exists('last_open_ip')
            && mailer_log_column_exists('last_open_user_agent');

        return $ready;
    }
}

if (!function_exists('mailer_open_tracking_enabled')) {
    function mailer_open_tracking_enabled($type = 0)
    {
        $type = (int) $type;

        if ($type !== 1 && $type !== 2) {
            return false;
        }

        if (!function_exists('https_url')) {
            return false;
        }

        return mailer_log_ensure_open_tracking_schema();
    }
}

if (!function_exists('mailer_generate_track_token')) {
    function mailer_generate_track_token()
    {
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes(16));
        }

        return sha1(uniqid(mt_rand(), true));
    }
}

if (!function_exists('mailer_tracking_base_url')) {
    function mailer_tracking_base_url()
    {
        if (!function_exists('https_url')) {
            return '';
        }

        return rtrim((string) https_url('', true), '/');
    }
}

if (!function_exists('mailer_prepare_tracking_context')) {
    function mailer_prepare_tracking_context($type, $log_context = array())
    {
        $type = (int) $type;

        if (!is_array($log_context)) {
            $log_context = array();
        }

        if ($type !== 1 && $type !== 2) {
            return $log_context;
        }

        if (mailer_tracking_base_url() === '' || !mailer_log_column_exists('track_token', true)) {
            return $log_context;
        }

        $track_token = isset($log_context['track_token']) ? trim((string) $log_context['track_token']) : '';
        if ($track_token === '') {
            $log_context['track_token'] = mailer_generate_track_token();
        }

        return $log_context;
    }
}

if (!function_exists('mailer_tracking_pixel_url')) {
    function mailer_tracking_pixel_url($token)
    {
        $token = trim((string) $token);
        if ($token === '') {
            return '';
        }

        $base_url = mailer_tracking_base_url();
        if ($base_url === '') {
            return '';
        }

        return $base_url . '/mail_open.php?token=' . rawurlencode($token);
    }
}

if (!function_exists('mailer_tracking_resolve_target_url')) {
    function mailer_tracking_resolve_target_url($target_url)
    {
        $target_url = html_entity_decode(trim((string) $target_url), ENT_QUOTES, 'UTF-8');
        if ($target_url === '') {
            return '';
        }

        if (strpos($target_url, '//') === 0 || stripos($target_url, 'javascript:') === 0) {
            return '';
        }

        if (strpos($target_url, '/') === 0) {
            return $target_url;
        }

        $target_parts = parse_url($target_url);
        if (!is_array($target_parts) || empty($target_parts['host'])) {
            return '';
        }

        $allowed_urls = array_unique(array_filter(array(
            mailer_tracking_base_url(),
            defined('G5_URL') ? rtrim((string) G5_URL, '/') : '',
            defined('G5_BBS_URL') ? rtrim((string) G5_BBS_URL, '/') : ''
        )));

        foreach ($allowed_urls as $allowed_url) {
            $allowed_parts = parse_url($allowed_url);
            if (!is_array($allowed_parts) || empty($allowed_parts['host'])) {
                continue;
            }

            $same_host = strcasecmp((string) $target_parts['host'], (string) $allowed_parts['host']) === 0;
            $same_port = (int) (isset($target_parts['port']) ? $target_parts['port'] : 0) === (int) (isset($allowed_parts['port']) ? $allowed_parts['port'] : 0);

            if ($same_host && $same_port) {
                return $target_url;
            }
        }

        return '';
    }
}

if (!function_exists('mailer_tracking_click_url')) {
    function mailer_tracking_click_url($track_token, $target_url)
    {
        $track_token = trim((string) $track_token);
        $resolved_target = mailer_tracking_resolve_target_url($target_url);

        if ($track_token === '' || $resolved_target === '' || !function_exists('mailer_log_click_tracking_ready') || !mailer_log_click_tracking_ready()) {
            return $target_url;
        }

        $base_url = mailer_tracking_base_url();
        if ($base_url === '') {
            return $target_url;
        }

        return $base_url . '/mail_click.php?token=' . rawurlencode($track_token) . '&target=' . rawurlencode($resolved_target);
    }
}

if (!function_exists('mailer_append_tracking_pixel')) {
    function mailer_append_tracking_pixel($content, $pixel_url)
    {
        $pixel_url = trim((string) $pixel_url);
        if ($pixel_url === '') {
            return $content;
        }

        $pixel_html = '<img src="' . $pixel_url . '" width="1" height="1" style="display:none !important;width:1px !important;height:1px !important;border:0 !important;overflow:hidden !important;" alt="">';

        if (stripos((string) $content, '</body>') !== false) {
            return preg_replace('/<\/body>/i', $pixel_html . '</body>', (string) $content, 1);
        }

        return (string) $content . $pixel_html;
    }
}

if (!function_exists('mailer_prepare_open_tracking')) {
    function mailer_prepare_open_tracking($type, $content, $log_context = array())
    {
        $log_context = mailer_prepare_tracking_context($type, $log_context);

        if (!mailer_open_tracking_enabled($type)) {
            return array($content, $log_context);
        }

        $track_token = isset($log_context['track_token']) ? trim((string) $log_context['track_token']) : '';
        if ($track_token === '') {
            $track_token = mailer_generate_track_token();
            $log_context['track_token'] = $track_token;
        }

        $pixel_url = mailer_tracking_pixel_url($track_token);
        if ($pixel_url !== '') {
            $content = mailer_append_tracking_pixel($content, $pixel_url);
        }

        return array($content, $log_context);
    }
}

if (!function_exists('mailer_log_mark_opened')) {
    function mailer_log_mark_opened($track_token)
    {
        $track_token = preg_replace('/[^a-zA-Z0-9]/', '', (string) $track_token);
        if ($track_token === '') {
            return false;
        }

        if (!mailer_log_ensure_open_tracking_schema()) {
            return false;
        }

        $table = mailer_log_table_name();
        $ip = isset($_SERVER['REMOTE_ADDR']) ? substr((string) $_SERVER['REMOTE_ADDR'], 0, 45) : '';
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? substr((string) $_SERVER['HTTP_USER_AGENT'], 0, 255) : '';

        $sql = " update `{$table}` set
                    `first_opened_at` = IF(`first_opened_at` IS NULL, NOW(), `first_opened_at`),
                    `last_opened_at` = NOW(),
                    `open_count` = IFNULL(`open_count`, 0) + 1,
                    `last_open_ip` = '" . sql_real_escape_string($ip) . "',
                    `last_open_user_agent` = '" . sql_real_escape_string($user_agent) . "'
                  where `track_token` = '" . sql_real_escape_string($track_token) . "'
                    and `status` = 'SUCCESS' ";

        return (bool) sql_query($sql, false);
    }
}

if (!function_exists('mailer_log_click_tracking_ready')) {
    function mailer_log_click_tracking_ready()
    {
        static $ready = null;

        if ($ready !== null) {
            return $ready;
        }

        $ready = mailer_log_table_exists()
            && mailer_log_column_exists('track_token', true)
            && mailer_log_column_exists('first_clicked_at')
            && mailer_log_column_exists('last_clicked_at')
            && mailer_log_column_exists('click_count');

        return $ready;
    }
}

if (!function_exists('mailer_log_mark_clicked')) {
    function mailer_log_mark_clicked($track_token)
    {
        $track_token = preg_replace('/[^a-zA-Z0-9]/', '', (string) $track_token);
        if ($track_token === '' || !mailer_log_click_tracking_ready()) {
            return false;
        }

        $table = mailer_log_table_name();
        $sql = " update `{$table}` set
                    `first_clicked_at` = IF(`first_clicked_at` IS NULL, NOW(), `first_clicked_at`),
                    `last_clicked_at` = NOW(),
                    `click_count` = IFNULL(`click_count`, 0) + 1
                  where `track_token` = '" . sql_real_escape_string($track_token) . "'
                    and `status` = 'SUCCESS' ";

        return (bool) sql_query($sql, false);
    }
}

if (!function_exists('mailer_log_write')) {
    function mailer_log_write($log_context, $recipient_email, $subject, $status, $error_message = '')
    {
        $table = mailer_log_table_name();
        $mail_type = '';
        $related_id = '';
        $track_token = '';
        $recipient_route = '';
        $skip_reason = '';

        if (is_array($log_context)) {
            $mail_type = isset($log_context['mail_type']) ? trim((string) $log_context['mail_type']) : '';
            $related_id = isset($log_context['related_id']) ? trim((string) $log_context['related_id']) : '';
            $track_token = isset($log_context['track_token']) ? trim((string) $log_context['track_token']) : '';
            $recipient_route = isset($log_context['recipient_route']) ? trim((string) $log_context['recipient_route']) : '';
            $skip_reason = isset($log_context['skip_reason']) ? trim((string) $log_context['skip_reason']) : '';
        }

        if ($mail_type === '') {
            $mail_type = 'general';
        }

        $recipient_email = trim((string) $recipient_email);
        $subject = trim((string) $subject);
        $status = trim((string) $status);
        $error_message = trim((string) $error_message);
        $created_at = defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s');
        $sql_extra = '';

        if ($track_token !== '' && mailer_log_column_exists('track_token')) {
            $sql_extra .= ",
                    track_token = '" . sql_real_escape_string($track_token) . "'";
        }

        if ($recipient_route !== '' && mailer_log_column_exists('recipient_route')) {
            $sql_extra .= ",
                    recipient_route = '" . sql_real_escape_string($recipient_route) . "'";
        }

        if ($skip_reason !== '' && mailer_log_column_exists('skip_reason')) {
            $sql_extra .= ",
                    skip_reason = '" . sql_real_escape_string($skip_reason) . "'";
        }

        $sql = " insert into `{$table}` set
                    mail_type = '".sql_real_escape_string($mail_type)."',
                    related_id = '".sql_real_escape_string($related_id)."',
                    recipient_email = '".sql_real_escape_string($recipient_email)."',
                    subject = '".sql_real_escape_string($subject)."',
                    status = '".sql_real_escape_string($status)."',
                    error_message = '".sql_real_escape_string($error_message)."'"
                    . $sql_extra . ",
                    created_at = '".sql_real_escape_string($created_at)."' ";

        $result = sql_query($sql, false);
        if (!$result) {
            error_log('[mailer_log] insert failed: '.sql_error_info());
        }

        return $result;
    }
}

// 메일 보내기 (파일 여러개 첨부 가능)
// type : text=0, html=1, text+html=2
function mailer($fname, $fmail, $to, $subject, $content, $type=0, $file="", $cc="", $bcc="", $log_context=array())
{
    global $config;
    global $g5;

    // 메일발송 사용을 하지 않는다면
    if (!$config['cf_email_use']) return;

    if ($type != 1)
        $content = nl2br($content);

    list($content, $log_context) = mailer_prepare_open_tracking($type, $content, $log_context);

    $result = run_replace('mailer', $fname, $fmail, $to, $subject, $content, $type, $file, $cc, $bcc);
    
    if( is_array($result) && isset($result['return']) ){
        $hook_mail_send_result = (bool) $result['return'];
        $hook_error_message = $hook_mail_send_result ? '' : 'run_replace mailer returned false';
        mailer_log_write($log_context, $to, $subject, $hook_mail_send_result ? 'SUCCESS' : 'FAIL', $hook_error_message);
        return $result['return'];
    }

    $mail_send_result = false;
    $mail = null;
    $mail_error_message = '';

    try {
        $get_mailer_env = function($name, $default = '') {
            if (function_exists('macomaex_env')) {
                return macomaex_env($name, $default);
            }

            $value = getenv($name);
            if ($value !== false) {
                return $value;
            }

            return $default;
        };

        $smtp_host = '';
        $smtp_port = '';
        $smtp_user = '';
        $smtp_password = '';
        $smtp_secure = '';
        $smtp_authtype = '';
        $smtp_useauth = false;
        $smtp_auto_tls = true;
        $from_mail = $fmail;
        $from_name = $fname;

        $env_smtp_host = trim($get_mailer_env('SMTP_HOST', ''));
        if ($env_smtp_host !== '') {
            $smtp_host = $env_smtp_host;
        } else if (defined('SMTP') && SMTP) {
            $smtp_host = SMTP;
        } else if (defined('G5_SMTP') && G5_SMTP) {
            $smtp_host = G5_SMTP;
        }

        $env_smtp_port = trim($get_mailer_env('SMTP_PORT', ''));
        if ($env_smtp_port !== '') {
            $smtp_port = $env_smtp_port;
        } else if (defined('SMTP_PORT') && SMTP_PORT) {
            $smtp_port = SMTP_PORT;
        } else if (defined('G5_SMTP_PORT') && G5_SMTP_PORT) {
            $smtp_port = G5_SMTP_PORT;
        }

        $env_smtp_user = trim($get_mailer_env('SMTP_USER', ''));
        if ($env_smtp_user !== '') {
            $smtp_user = $env_smtp_user;
        } else if (defined('SMTP_USER') && SMTP_USER) {
            $smtp_user = SMTP_USER;
        } else if (defined('G5_Username') && G5_Username) {
            $smtp_user = G5_Username;
        }

        $env_smtp_password = $get_mailer_env('SMTP_PW', '');
        if ($env_smtp_password !== '') {
            $smtp_password = $env_smtp_password;
        } else if (defined('SMTP_PW') && SMTP_PW) {
            $smtp_password = SMTP_PW;
        } else if (defined('G5_Password') && G5_Password) {
            $smtp_password = G5_Password;
        }

        $env_smtp_secure = trim($get_mailer_env('SMTP_SECURE', ''));
        if ($env_smtp_secure !== '') {
            $smtp_secure = strtolower($env_smtp_secure);
        } else if (defined('SMTP_SECURE') && SMTP_SECURE) {
            $smtp_secure = strtolower(trim(SMTP_SECURE));
        }

        $env_smtp_auto_tls = strtolower(trim($get_mailer_env('SMTP_AUTO_TLS', '')));
        if ($env_smtp_auto_tls !== '') {
            $smtp_auto_tls = !in_array($env_smtp_auto_tls, array('0', 'false', 'off', 'no'), true);
        }

        $env_smtp_authtype = trim($get_mailer_env('SMTP_AUTHTYPE', ''));
        if ($env_smtp_authtype !== '') {
            $smtp_authtype = $env_smtp_authtype;
        } else if (defined('SMTP_AUTHTYPE') && SMTP_AUTHTYPE) {
            $smtp_authtype = SMTP_AUTHTYPE;
        }

        if (defined('SMTP_USEAUTH')) {
            $smtp_useauth = (bool) SMTP_USEAUTH;
        } else if ($smtp_user !== '' || $smtp_password !== '') {
            $smtp_useauth = true;
        }

        $env_from_mail = trim($get_mailer_env('SMTP_FROM_EMAIL', ''));
        if ($env_from_mail !== '') {
            $from_mail = $env_from_mail;
        }

        $env_from_name = trim($get_mailer_env('SMTP_FROM_NAME', ''));
        if ($env_from_name !== '') {
            $from_name = $env_from_name;
        }

        $mail = new PHPMailer(); // defaults to using php "mail()"
        if ($smtp_host) {
            $mail->IsSMTP(); // telling the class to use SMTP
            $mail->Host = $smtp_host; // SMTP server

            if ($smtp_port) {
                $mail->Port = (int) $smtp_port;
            }

            if (in_array($smtp_secure, array('none', 'off', 'false', 'disable', 'disabled'), true)) {
                $smtp_secure = '';
                $smtp_auto_tls = false;
            }

            if ($smtp_secure && $smtp_secure !== 'auto') {
                $mail->SMTPSecure = $smtp_secure;
            }

            if (property_exists($mail, 'SMTPAutoTLS')) {
                $mail->SMTPAutoTLS = $smtp_auto_tls;
            }

            if ($smtp_useauth && $smtp_user !== '' && $smtp_password !== '') {
                $mail->SMTPAuth = true; // SMTP Auth
                if ($smtp_authtype) {
                    $mail->AuthType = $smtp_authtype;
                }
                $mail->Username = $smtp_user; // SMTP Auth id
                $mail->Password = $smtp_password; // SMTP Auth password
            }
        }
        $mail->CharSet = 'UTF-8';
        $mail->From = $from_mail;
        $mail->FromName = $from_name;
        $mail->Subject = $subject;
        $mail->AltBody = ""; // optional, comment out and test
        $mail->msgHTML($content);
        $mail->addAddress($to);
        if ($cc)
            $mail->addCC($cc);
        if ($bcc)
            $mail->addBCC($bcc);
        //print_r2($file); exit;
        if ($file != "") {
            foreach ($file as $f) {
                $mail->addAttachment($f['path'], $f['name']);
            }
        }

        $mail = run_replace('mail_options', $mail, $fname, $fmail, $to, $subject, $content, $type, $file, $cc, $bcc);

        $mail_send_result = $mail->send();
        if (!$mail_send_result) {
            $mail_error_message = (string) $mail->ErrorInfo;
            error_log('[mailer] send failed to '.$to.': '.$mail_error_message);
        }

    } catch (Exception $e) {
        $mail_error_message = $e->getMessage();
        error_log('[mailer] exception while sending to '.$to.': '.$mail_error_message);
    }

    if (!$mail_send_result && $mail_error_message === '') {
        $mail_error_message = 'Unknown mail send failure';
    }

    mailer_log_write($log_context, $to, $subject, $mail_send_result ? 'SUCCESS' : 'FAIL', $mail_error_message);
    run_event('mail_send_result', $mail_send_result, $mail, $to, $cc, $bcc);

    return $mail_send_result;
}

// 파일을 첨부함
function attach_file($filename, $tmp_name)
{
    // 서버에 업로드 되는 파일은 확장자를 주지 않는다. (보안 취약점)
    $dest_file = G5_DATA_PATH.'/tmp/'.str_replace('/', '_', $tmp_name);
    move_uploaded_file($tmp_name, $dest_file);
    $tmpfile = array("name" => $filename, "path" => $dest_file);
    return $tmpfile;
}
