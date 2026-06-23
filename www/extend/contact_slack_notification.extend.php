<?php
if (!defined('_GNUBOARD_')) exit;

if (!function_exists('contact_slack_env')) {
    function contact_slack_env($key, $default = '') {
        $key = trim((string)$key);
        if ($key === '') return (string)$default;

        if (function_exists('macomaex_env')) {
            return trim((string)macomaex_env($key, $default));
        }

        $value = getenv($key);
        if ($value === false || $value === null) {
            return (string)$default;
        }

        return trim((string)$value);
    }
}

if (!function_exists('contact_slack_webhook')) {
    function contact_slack_webhook() {
        $keys = array(
            'CONTACT_SLACK_WEBHOOK',
            'CUSTOMER_INQUIRY_SLACK_WEBHOOK',
            'TM_OVERDUE_SLACK_WEBHOOK',
        );

        foreach ($keys as $key) {
            $webhook = contact_slack_env($key, '');
            if ($webhook !== '') {
                return $webhook;
            }
        }

        if (defined('_contact_slack_webhook')) {
            $webhook = trim((string)constant('_contact_slack_webhook'));
            if ($webhook !== '') {
                return $webhook;
            }
        }

        if (defined('_tm_overdue_slack_webhook')) {
            $webhook = trim((string)constant('_tm_overdue_slack_webhook'));
            if ($webhook !== '') {
                return $webhook;
            }
        }

        return '';
    }
}

if (!function_exists('contact_slack_join_value')) {
    function contact_slack_join_value($value, $glue = '') {
        if (is_array($value)) {
            $parts = array();
            foreach ($value as $item) {
                $item = trim((string)$item);
                if ($item === '') {
                    continue;
                }
                $parts[] = $item;
            }
            return implode($glue, $parts);
        }

        return trim((string)$value);
    }
}

if (!function_exists('contact_slack_excerpt')) {
    function contact_slack_excerpt($text, $limit = 300) {
        $text = trim((string)$text);
        if ($text === '') {
            return '';
        }

        $text = html_entity_decode(strip_tags($text), ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text);
        $text = trim((string)$text);

        if ($text === '') {
            return '';
        }

        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            if (mb_strlen($text, 'UTF-8') > $limit) {
                return mb_substr($text, 0, $limit, 'UTF-8') . '...';
            }
            return $text;
        }

        if (strlen($text) > $limit) {
            return substr($text, 0, $limit) . '...';
        }

        return $text;
    }
}

if (!function_exists('contact_slack_notify')) {
    function contact_slack_notify($payload) {
        if (!is_array($payload)) return false;

        $webhook = contact_slack_webhook();
        if ($webhook === '' || !function_exists('curl_init')) {
            return false;
        }

        $board_subject = trim((string)(isset($payload['board_subject']) ? $payload['board_subject'] : '문의'));
        $subject = trim((string)(isset($payload['subject']) ? $payload['subject'] : ''));
        $name = trim((string)(isset($payload['name']) ? $payload['name'] : ''));
        $company = trim((string)(isset($payload['company']) ? $payload['company'] : ''));
        $position = trim((string)(isset($payload['position']) ? $payload['position'] : ''));
        $email = trim((string)(isset($payload['email']) ? $payload['email'] : ''));
        $phone = trim((string)(isset($payload['phone']) ? $payload['phone'] : ''));
        $content = contact_slack_excerpt(isset($payload['content']) ? $payload['content'] : '', 300);
        $created_at = trim((string)(isset($payload['created_at']) ? $payload['created_at'] : ''));

        $lines = array();
        $lines[] = '[고객문의 등록]';
        $lines[] = '- 게시판: ' . ($board_subject !== '' ? $board_subject : '문의');
        $lines[] = '- 등록시각: ' . ($created_at !== '' ? $created_at : date('Y-m-d H:i:s'));
        $lines[] = '- 제목: ' . ($subject !== '' ? $subject : '-');
        $lines[] = '- 작성자: ' . ($name !== '' ? $name : '-');
        $lines[] = '- 기업명: ' . ($company !== '' ? $company : '-');
        $lines[] = '- 직책: ' . ($position !== '' ? $position : '-');
        $lines[] = '- 이메일: ' . ($email !== '' ? $email : '-');
        $lines[] = '- 연락처: ' . ($phone !== '' ? $phone : '-');
        if ($content !== '') {
            $lines[] = '- 문의내용: ' . $content;
        }
        $json_flags = 0;
        if (defined('JSON_UNESCAPED_UNICODE')) $json_flags |= JSON_UNESCAPED_UNICODE;
        if (defined('JSON_UNESCAPED_SLASHES')) $json_flags |= JSON_UNESCAPED_SLASHES;

        $body = json_encode(array('text' => implode("\n", $lines)), $json_flags);
        if ($body === false) {
            return false;
        }

        $ch = curl_init($webhook);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 6);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        $response = curl_exec($ch);
        $http_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($response === false || $http_code < 200 || $http_code >= 300) {
            error_log('[contact_slack_notify] send failed: http=' . $http_code . ' error=' . $curl_error);
            return false;
        }

        return true;
    }
}
