<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('system_error_env')) {
    function system_error_env($key, $default = '')
    {
        $key = trim((string)$key);
        if ($key === '') {
            return $default;
        }

        if (function_exists('macomaex_env')) {
            return macomaex_env($key, $default);
        }

        $value = getenv($key);
        if ($value !== false && $value !== null) {
            return $value;
        }

        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }

        if (isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }

        return $default;
    }
}

if (!function_exists('system_error_env_bool')) {
    function system_error_env_bool($key, $default = null)
    {
        $value = system_error_env($key, null);
        if ($value === null || $value === '') {
            return $default;
        }

        $value = strtolower(trim((string)$value));
        if (in_array($value, array('1', 'true', 'y', 'yes', 'on'), true)) {
            return true;
        }
        if (in_array($value, array('0', 'false', 'n', 'no', 'off'), true)) {
            return false;
        }

        return $default;
    }
}

if (!function_exists('system_error_monitor_enabled')) {
    function system_error_monitor_enabled()
    {
        if (defined('G5_SYSTEM_ERROR_MONITOR_USE') && !G5_SYSTEM_ERROR_MONITOR_USE) {
            return false;
        }

        return true;
    }
}

if (!function_exists('system_error_log_cut')) {
    function system_error_log_cut($text, $max_len)
    {
        $text = trim((string)$text);
        $max_len = (int)$max_len;
        if ($max_len < 1) {
            return $text;
        }

        if (function_exists('mb_substr')) {
            return mb_substr($text, 0, $max_len, 'UTF-8');
        }

        return substr($text, 0, $max_len);
    }
}

if (!function_exists('system_error_log_table_name')) {
    function system_error_log_table_name()
    {
        $prefix = defined('G5_TABLE_PREFIX') ? G5_TABLE_PREFIX : 'g5_';
        return $prefix . 'system_error_log';
    }
}

if (!function_exists('system_error_log_table_exists')) {
    function system_error_log_table_exists()
    {
        static $table_exists = null;
        if ($table_exists !== null) {
            return $table_exists;
        }

        if (!function_exists('sql_query')) {
            $table_exists = false;
            return $table_exists;
        }

        $table = system_error_log_table_name();
        $table_exists = (bool)@sql_query(" DESCRIBE {$table} ", false);

        return $table_exists;
    }
}

if (!function_exists('system_error_level_name')) {
    function system_error_level_name($severity)
    {
        $severity = (int)$severity;
        $map = array(
            E_ERROR => 'ERROR',
            E_WARNING => 'WARNING',
            E_PARSE => 'PARSE',
            E_NOTICE => 'NOTICE',
            E_CORE_ERROR => 'CORE_ERROR',
            E_CORE_WARNING => 'CORE_WARNING',
            E_COMPILE_ERROR => 'COMPILE_ERROR',
            E_COMPILE_WARNING => 'COMPILE_WARNING',
            E_USER_ERROR => 'USER_ERROR',
            E_USER_WARNING => 'USER_WARNING',
            E_USER_NOTICE => 'USER_NOTICE'
        );

        if (defined('E_RECOVERABLE_ERROR')) {
            $map[E_RECOVERABLE_ERROR] = 'RECOVERABLE_ERROR';
        }
        if (defined('E_DEPRECATED')) {
            $map[E_DEPRECATED] = 'DEPRECATED';
        }
        if (defined('E_USER_DEPRECATED')) {
            $map[E_USER_DEPRECATED] = 'USER_DEPRECATED';
        }

        return isset($map[$severity]) ? $map[$severity] : 'PHP_' . $severity;
    }
}

if (!function_exists('system_error_mask_key')) {
    function system_error_mask_key($key)
    {
        $key = strtolower((string)$key);
        if ($key === '') {
            return false;
        }

        return (bool)preg_match('/pass|pwd|token|secret|auth|cookie|key/', $key);
    }
}

if (!function_exists('system_error_mask_value')) {
    function system_error_mask_value($value, $key_name = '')
    {
        if (system_error_mask_key($key_name)) {
            return '[MASKED]';
        }

        if (is_array($value)) {
            $new_value = array();
            foreach ($value as $k => $v) {
                $new_value[$k] = system_error_mask_value($v, $k);
            }
            return $new_value;
        }

        if (is_object($value)) {
            return '[OBJECT:' . get_class($value) . ']';
        }

        if (is_string($value)) {
            return system_error_log_cut($value, 300);
        }

        return $value;
    }
}

if (!function_exists('system_error_fallback_file_log')) {
    function system_error_fallback_file_log($line)
    {
        $log_dir = G5_DATA_PATH . '/log';
        $log_file = $log_dir . '/system_error.log';

        if (!is_dir($log_dir)) {
            @mkdir($log_dir, G5_DIR_PERMISSION, true);
        }

        @error_log($line . PHP_EOL, 3, $log_file);
    }
}

if (!function_exists('system_error_slack_enabled')) {
    function system_error_slack_enabled()
    {
        $env_use = system_error_env_bool('SYSTEM_ERROR_SLACK_USE', null);
        if ($env_use === false) {
            return false;
        }
        if ($env_use === null && defined('G5_SYSTEM_ERROR_SLACK_USE') && !G5_SYSTEM_ERROR_SLACK_USE) {
            return false;
        }

        $webhook = system_error_slack_webhook();

        return ($webhook !== '');
    }
}

if (!function_exists('system_error_slack_webhook')) {
    function system_error_slack_webhook()
    {
        $webhook = trim((string)system_error_env('SYSTEM_ERROR_SLACK_WEBHOOK', ''));
        if ($webhook !== '') {
            return $webhook;
        }
        if (defined('_system_error_slack_webhook')) {
            return trim((string)_system_error_slack_webhook);
        }

        $webhook = trim((string)system_error_env('TM_OVERDUE_SLACK_WEBHOOK', ''));
        if ($webhook !== '') {
            return $webhook;
        }
        if (defined('_tm_overdue_slack_webhook')) {
            return trim((string)_tm_overdue_slack_webhook);
        }

        return '';
    }
}

if (!function_exists('system_error_slack_min_interval')) {
    function system_error_slack_min_interval()
    {
        $interval = 300;
        $env_interval = system_error_env('SYSTEM_ERROR_SLACK_MIN_INTERVAL', '');
        if ($env_interval !== '') {
            $interval = (int)$env_interval;
        } else if (defined('_system_error_slack_min_interval')) {
            $interval = (int)_system_error_slack_min_interval;
        }
        if ($interval < 0) {
            $interval = 0;
        }

        return $interval;
    }
}

if (!function_exists('system_error_db_dedupe_seconds')) {
    function system_error_db_dedupe_seconds()
    {
        $seconds = 60;
        if (defined('_system_error_db_dedupe_seconds')) {
            $seconds = (int)_system_error_db_dedupe_seconds;
        }
        if ($seconds < 0) {
            $seconds = 0;
        }

        return $seconds;
    }
}

if (!function_exists('system_error_slack_should_alert')) {
    function system_error_slack_should_alert($type, $level, $message)
    {
        $type = trim((string)$type);
        if ($type !== 'php_fatal' && $type !== 'php_exception') {
            return false;
        }

        if (strpos((string)$message, 'SYSTEM_ERROR_MONITOR_TEST_') !== false) {
            return false;
        }

        return true;
    }
}

if (!function_exists('system_error_slack_dedupe_file')) {
    function system_error_slack_dedupe_file()
    {
        return G5_DATA_PATH . '/cache/system_error_slack_dedupe.json';
    }
}

if (!function_exists('system_error_slack_allow_send')) {
    function system_error_slack_allow_send($fingerprint)
    {
        $fingerprint = trim((string)$fingerprint);
        if ($fingerprint === '') {
            return true;
        }

        $interval = system_error_slack_min_interval();
        if ($interval === 0) {
            return true;
        }

        $file = system_error_slack_dedupe_file();
        $dir = dirname($file);
        if (!is_dir($dir)) {
            @mkdir($dir, G5_DIR_PERMISSION, true);
        }

        $map = array();
        if (is_file($file)) {
            $raw = @file_get_contents($file);
            if ($raw !== false && $raw !== '') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $map = $decoded;
                }
            }
        }

        $now = time();
        if (isset($map[$fingerprint]) && ($now - (int)$map[$fingerprint]) < $interval) {
            return false;
        }

        $map[$fingerprint] = $now;

        $keep_seconds = max(86400, $interval * 20);
        foreach ($map as $k => $ts) {
            if (($now - (int)$ts) > $keep_seconds) {
                unset($map[$k]);
            }
        }

        @file_put_contents($file, json_encode($map), LOCK_EX);
        return true;
    }
}

if (!function_exists('system_error_slack_send')) {
    function system_error_slack_send($event)
    {
        if (!system_error_slack_enabled()) {
            return false;
        }

        if (!is_array($event)) {
            return false;
        }

        $fingerprint = isset($event['fingerprint']) ? trim((string)$event['fingerprint']) : '';
        if (!system_error_slack_allow_send($fingerprint)) {
            return false;
        }

        $webhook = system_error_slack_webhook();
        if ($webhook === '') {
            return false;
        }

        $type = isset($event['type']) ? (string)$event['type'] : '';
        $level = isset($event['level']) ? (string)$event['level'] : '';
        $message = isset($event['message']) ? (string)$event['message'] : '';
        $file = isset($event['file']) ? (string)$event['file'] : '';
        $line = isset($event['line']) ? (int)$event['line'] : 0;
        $request_uri = isset($event['request_uri']) ? (string)$event['request_uri'] : '';
        $mb_id = isset($event['mb_id']) ? (string)$event['mb_id'] : '';
        $ip = isset($event['ip']) ? (string)$event['ip'] : '';

        $host = isset($_SERVER['HTTP_HOST']) ? (string)$_SERVER['HTTP_HOST'] : 'CLI';
        $text = "[SYSTEM ERROR ALERT]"
            . "\n- site: " . system_error_log_cut($host, 100)
            . "\n- type: " . system_error_log_cut($type, 30)
            . "\n- level: " . system_error_log_cut($level, 20)
            . "\n- message: " . system_error_log_cut($message, 300)
            . "\n- file: " . system_error_log_cut($file, 160) . ':' . $line
            . "\n- uri: " . system_error_log_cut($request_uri, 220)
            . "\n- admin: " . system_error_log_cut($mb_id, 20)
            . "\n- ip: " . system_error_log_cut($ip, 45)
            . "\n- at: " . date('Y-m-d H:i:s')
            . "\n- fp: " . system_error_log_cut($fingerprint, 40);

        $json_flags = 0;
        if (defined('JSON_UNESCAPED_UNICODE')) {
            $json_flags |= JSON_UNESCAPED_UNICODE;
        }
        if (defined('JSON_UNESCAPED_SLASHES')) {
            $json_flags |= JSON_UNESCAPED_SLASHES;
        }
        $payload = json_encode(array('text' => $text), $json_flags);
        if ($payload === false) {
            return false;
        }

        if (!function_exists('curl_init')) {
            system_error_fallback_file_log('[SLACK_FAIL] curl_init not exists');
            return false;
        }

        $ch = curl_init($webhook);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 6);
        $response = curl_exec($ch);
        $http_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($response === false || $http_code < 200 || $http_code >= 300) {
            system_error_fallback_file_log('[SLACK_FAIL] http=' . $http_code . ' err=' . $curl_error);
            return false;
        }

        return true;
    }
}

if (!function_exists('system_error_log_write')) {
    function system_error_log_write($type, $level, $message, $file, $line, $extra = array())
    {
        global $member;

        if (!system_error_monitor_enabled()) {
            return false;
        }

        static $is_writing = false;
        if ($is_writing) {
            return false;
        }
        $is_writing = true;

        $type = system_error_log_cut($type, 30);
        $level = system_error_log_cut($level, 20);
        $message = system_error_log_cut($message, 500);
        $file = system_error_log_cut($file, 255);
        $line = (int)$line;

        $request_method = isset($_SERVER['REQUEST_METHOD']) ? system_error_log_cut($_SERVER['REQUEST_METHOD'], 10) : '';
        $request_uri = isset($_SERVER['REQUEST_URI']) ? system_error_log_cut($_SERVER['REQUEST_URI'], 255) : '';
        $ip = isset($_SERVER['REMOTE_ADDR']) ? system_error_log_cut($_SERVER['REMOTE_ADDR'], 45) : '';
        $mb_id = isset($member['mb_id']) ? system_error_log_cut($member['mb_id'], 20) : '';

        $safe_request = array();
        if (isset($_REQUEST) && is_array($_REQUEST)) {
            $safe_request = system_error_mask_value($_REQUEST);
        }
        $safe_extra = system_error_mask_value($extra);

        $payload_data = array(
            'request' => $safe_request,
            'extra' => $safe_extra
        );

        $json_flags = 0;
        if (defined('JSON_UNESCAPED_UNICODE')) {
            $json_flags |= JSON_UNESCAPED_UNICODE;
        }
        if (defined('JSON_UNESCAPED_SLASHES')) {
            $json_flags |= JSON_UNESCAPED_SLASHES;
        }

        $payload = json_encode($payload_data, $json_flags);
        if ($payload === false) {
            $payload = print_r($payload_data, true);
        }
        $payload = system_error_log_cut($payload, 10000);

        $fingerprint = sha1($type . '|' . $level . '|' . $message . '|' . $file . '|' . $line);

        static $request_seen = array();
        $request_key = $fingerprint . '|' . $request_method . '|' . $request_uri;
        if (isset($request_seen[$request_key])) {
            $is_writing = false;
            return true;
        }
        $request_seen[$request_key] = 1;

        $insert_ok = false;
        if (function_exists('sql_real_escape_string') && function_exists('sql_query') && system_error_log_table_exists()) {
            $table = system_error_log_table_name();
            $esc_fingerprint = sql_real_escape_string($fingerprint);
            $esc_request_method = sql_real_escape_string($request_method);
            $esc_request_uri = sql_real_escape_string($request_uri);

            $dedupe_seconds = system_error_db_dedupe_seconds();
            if ($dedupe_seconds > 0) {
                $dup_sql = " select el_id
                               from {$table}
                              where el_fingerprint = '{$esc_fingerprint}'
                                and el_request_method = '{$esc_request_method}'
                                and el_request_uri = '{$esc_request_uri}'
                                and el_datetime >= DATE_SUB(NOW(), INTERVAL {$dedupe_seconds} SECOND)
                              order by el_id desc
                              limit 1 ";
                $dup_row = @sql_fetch($dup_sql, false);
                if (!empty($dup_row['el_id'])) {
                    $is_writing = false;
                    return true;
                }
            }

            $sql = " insert into {$table} set
                        el_datetime = '" . G5_TIME_YMDHIS . "',
                        el_type = '" . sql_real_escape_string($type) . "',
                        el_level = '" . sql_real_escape_string($level) . "',
                        el_message = '" . sql_real_escape_string($message) . "',
                        el_file = '" . sql_real_escape_string($file) . "',
                        el_line = '{$line}',
                        el_request_method = '{$esc_request_method}',
                        el_request_uri = '{$esc_request_uri}',
                        el_mb_id = '" . sql_real_escape_string($mb_id) . "',
                        el_ip = '" . sql_real_escape_string($ip) . "',
                        el_fingerprint = '{$esc_fingerprint}',
                        el_payload = '" . sql_real_escape_string($payload) . "' ";
            $insert_ok = (bool)@sql_query($sql, false);
        }

        if (!$insert_ok) {
            $line_text = '[' . date('Y-m-d H:i:s') . ']'
                . ' [' . $level . ']'
                . ' [' . $type . '] '
                . $message . ' (' . $file . ':' . $line . ')'
                . ' uri=' . $request_uri;
            system_error_fallback_file_log($line_text);
        }

        if (system_error_slack_should_alert($type, $level, $message)) {
            system_error_slack_send(array(
                'type' => $type,
                'level' => $level,
                'message' => $message,
                'file' => $file,
                'line' => $line,
                'request_uri' => $request_uri,
                'mb_id' => $mb_id,
                'ip' => $ip,
                'fingerprint' => $fingerprint
            ));
        }

        $is_writing = false;
        return $insert_ok;
    }
}

if (!function_exists('system_error_monitor_error_handler')) {
    function system_error_monitor_error_handler($severity, $message, $file, $line)
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        $level = system_error_level_name($severity);
        system_error_log_write('php_error', $level, $message, $file, $line);

        return false;
    }
}

if (!function_exists('system_error_monitor_exception_handler')) {
    function system_error_monitor_exception_handler($exception)
    {
        $message = 'Unknown exception';
        $file = '';
        $line = 0;
        $trace = '';

        if (is_object($exception)) {
            if (method_exists($exception, 'getMessage')) {
                $message = $exception->getMessage();
            }
            if (method_exists($exception, 'getFile')) {
                $file = $exception->getFile();
            }
            if (method_exists($exception, 'getLine')) {
                $line = (int)$exception->getLine();
            }
            if (method_exists($exception, 'getTraceAsString')) {
                $trace = $exception->getTraceAsString();
            }
        }

        system_error_log_write('php_exception', 'EXCEPTION', $message, $file, $line, array('trace' => $trace));

        if (!headers_sent()) {
            if (function_exists('http_response_code')) {
                http_response_code(500);
            } else {
                header('HTTP/1.1 500 Internal Server Error');
            }
            header('Content-Type: text/html; charset=utf-8');
        }

        if (defined('G5_DEBUG') && G5_DEBUG) {
            echo '<h3>Unhandled Exception</h3>';
            echo '<pre>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</pre>';
        } else {
            echo '시스템 오류가 발생했습니다.';
        }
        exit;
    }
}

if (!function_exists('system_error_monitor_shutdown_handler')) {
    function system_error_monitor_shutdown_handler()
    {
        $error = error_get_last();
        if (!$error || !is_array($error)) {
            return;
        }

        $fatal_types = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR);
        if (defined('E_RECOVERABLE_ERROR')) {
            $fatal_types[] = E_RECOVERABLE_ERROR;
        }

        if (!in_array((int)$error['type'], $fatal_types)) {
            return;
        }

        $level = system_error_level_name((int)$error['type']);
        system_error_log_write('php_fatal', $level, $error['message'], $error['file'], $error['line']);
    }
}

if (!defined('G5_SYSTEM_ERROR_MONITOR_REGISTERED')) {
    define('G5_SYSTEM_ERROR_MONITOR_REGISTERED', true);

    set_error_handler('system_error_monitor_error_handler');
    set_exception_handler('system_error_monitor_exception_handler');
    register_shutdown_function('system_error_monitor_shutdown_handler');
}
