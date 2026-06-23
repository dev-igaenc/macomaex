<?php
if (!defined('_GNUBOARD_')) exit;

if (!function_exists('channel_talk_env')) {
    function channel_talk_env($key, $default = '')
    {
        $key = trim((string)$key);
        if ($key === '') return (string)$default;

        if (function_exists('macomaex_env')) {
            return trim((string)macomaex_env($key, $default));
        }

        $value = getenv($key);
        if ($value === false || $value === null || $value === '') {
            return (string)$default;
        }

        return trim((string)$value);
    }
}

if (!function_exists('channel_talk_plugin_key')) {
    function channel_talk_plugin_key()
    {
        $pluginKey = channel_talk_env('CHANNEL_TALK_PLUGIN_KEY', '');
        if ($pluginKey !== '') return $pluginKey;

        return '296accc8-9450-4f33-a7e5-8a61d905164d';
    }
}

if (!function_exists('channel_talk_guest_member_id')) {
    function channel_talk_guest_member_id()
    {
        $sessionId = session_id();
        if ($sessionId === '') {
            @session_start();
            $sessionId = session_id();
        }

        $sessionId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$sessionId);
        if ($sessionId === '') {
            $sessionId = substr(hash('sha256', uniqid('guest_', true)), 0, 12);
        }

        return 'guest_' . $sessionId;
    }
}

if (!function_exists('channel_talk_member_id')) {
    function channel_talk_member_id()
    {
        global $member, $is_member;

        if (!empty($is_member)) {
            $mbId = trim((string)($member['mb_id'] ?? ''));
            if ($mbId !== '') {
                return $mbId;
            }
        }

        return channel_talk_guest_member_id();
    }
}

if (!function_exists('channel_talk_boot_payload')) {
    function channel_talk_boot_payload($extra = array())
    {
        $memberId = channel_talk_member_id();
        $isGuest = channel_talk_string_contains($memberId, 'guest_');
        $payload = array(
            'pluginKey' => channel_talk_plugin_key(),
            'memberId' => $memberId,
            'profile' => array(
                'name' => $memberId,
                'member_id' => $memberId,
                'guest_session_id' => $isGuest ? $memberId : '',
            ),
        );

        if (is_array($extra) && !empty($extra)) {
            $payload = array_replace_recursive($payload, $extra);
        }

        return $payload;
    }
}

if (!function_exists('channel_talk_json_encode')) {
    function channel_talk_json_encode($value)
    {
        $flags = 0;
        if (defined('JSON_UNESCAPED_UNICODE')) $flags |= JSON_UNESCAPED_UNICODE;
        if (defined('JSON_UNESCAPED_SLASHES')) $flags |= JSON_UNESCAPED_SLASHES;

        $json = json_encode($value, $flags);
        if ($json === false) {
            return '{}';
        }

        return $json;
    }
}

if (!function_exists('channel_talk_boot_script')) {
    function channel_talk_boot_script($extra = array())
    {
        $payload = channel_talk_json_encode(channel_talk_boot_payload($extra));

        return <<<HTML
<script>
(function(){var w=window;if(w.ChannelIO){return w.console.error("ChannelIO script included twice.");}var ch=function(){ch.c(arguments);};ch.q=[];ch.c=function(args){ch.q.push(args);};w.ChannelIO=ch;function l(){if(w.ChannelIOInitialized){return;}w.ChannelIOInitialized=true;var s=document.createElement("script");s.type="text/javascript";s.async=true;s.src="https://cdn.channel.io/plugin/ch-plugin-web.js";var x=document.getElementsByTagName("script")[0];if(x.parentNode){x.parentNode.insertBefore(s,x);}}if(document.readyState==="complete"){l();}else{w.addEventListener("DOMContentLoaded",l);w.addEventListener("load",l);}})();
ChannelIO('boot', {$payload});
</script>
HTML;
    }
}

if (!function_exists('channel_talk_compact_log_message')) {
    function channel_talk_compact_log_message($message, $maxLength = 255)
    {
        $message = trim((string)$message);
        if ($message === '') return '';

        $message = preg_replace('/\s+/u', ' ', $message);
        $maxLength = (int)$maxLength;
        if ($maxLength < 1) return $message;

        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            if (mb_strlen($message, 'UTF-8') > $maxLength) {
                return mb_substr($message, 0, $maxLength - 1, 'UTF-8') . '…';
            }
            return $message;
        }

        if (strlen($message) > $maxLength) {
            return substr($message, 0, $maxLength - 3) . '...';
        }

        return $message;
    }
}

if (!function_exists('channel_talk_json_response')) {
    function channel_talk_json_response($success, $message = '', $statusCode = 200, $extra = array())
    {
        http_response_code((int)$statusCode);
        header('Content-Type: application/json; charset=utf-8');

        $payload = array(
            'success' => (bool)$success,
            'message' => (string)$message,
        );

        if (is_array($extra) && !empty($extra)) {
            $payload = array_merge($payload, $extra);
        }

        echo channel_talk_json_encode($payload);
        exit;
    }
}

if (!function_exists('channel_talk_request_ip')) {
    function channel_talk_request_ip()
    {
        return trim((string)($_SERVER['REMOTE_ADDR'] ?? ''));
    }
}

if (!function_exists('channel_talk_request_user_agent')) {
    function channel_talk_request_user_agent()
    {
        return trim((string)($_SERVER['HTTP_USER_AGENT'] ?? ''));
    }
}

if (!function_exists('channel_talk_request_uri')) {
    function channel_talk_request_uri()
    {
        return trim((string)($_SERVER['REQUEST_URI'] ?? ''));
    }
}

if (!function_exists('channel_talk_string_contains')) {
    function channel_talk_string_contains($haystack, $needle)
    {
        $haystack = (string)$haystack;
        $needle = (string)$needle;
        if ($haystack === '' || $needle === '') return false;

        if (function_exists('mb_stripos')) {
            return mb_stripos($haystack, $needle, 0, 'UTF-8') !== false;
        }

        return stripos($haystack, $needle) !== false;
    }
}

if (!function_exists('channel_talk_db_table_name')) {
    function channel_talk_db_table_name()
    {
        return 'channel_talk_log';
    }
}

if (!function_exists('channel_talk_log_directory_path')) {
    function channel_talk_log_directory_path()
    {
        return dirname(__DIR__) . '/logs/channel_talk';
    }
}

if (!function_exists('channel_talk_log_file_path')) {
    function channel_talk_log_file_path($dateYmd = '')
    {
        $dateYmd = preg_replace('/[^0-9]/', '', (string)$dateYmd);
        if ($dateYmd === '') {
            $dateYmd = date('Ymd');
        }

        return channel_talk_log_directory_path() . '/channel_talk_log_' . $dateYmd . '.php';
    }
}

if (!function_exists('channel_talk_log_default_message')) {
    function channel_talk_log_default_message($eventType, $status)
    {
        $eventType = trim((string)$eventType);
        $status = trim((string)$status);

        if ($status === 'success') {
            $map = array(
                'question_received' => '채널톡 사용자 질문 로그를 저장했습니다.',
                'answer_updated' => '채널톡 AI 답변 로그를 반영했습니다.',
                'slack_alert_sent' => '슬랙 알림을 발송했습니다.',
                'slack_alert_failed' => '슬랙 알림 발송에 실패했습니다.',
            );

            return $map[$eventType] ?? '채널톡 로그를 기록했습니다.';
        }

        if ($eventType === 'slack_alert_failed') {
            return '슬랙 알림 발송에 실패했습니다.';
        }

        return '채널톡 로그 처리 중 확인이 필요합니다.';
    }
}

if (!function_exists('channel_talk_write_daily_file_log')) {
    function channel_talk_write_daily_file_log($eventType, $status, $payload = array())
    {
        $directory = channel_talk_log_directory_path();
        if (!is_dir($directory)) {
            @mkdir($directory, 0777, true);
        }
        if (!is_dir($directory) || !is_writable($directory)) {
            return false;
        }

        $filePath = channel_talk_log_file_path();
        $isNewFile = !file_exists($filePath);
        $loggedAt = date('Y-m-d H:i:s');
        $message = trim((string)($payload['message'] ?? ''));
        if ($message === '') {
            $message = channel_talk_log_default_message($eventType, $status);
        }

        $fields = array(
            'event_type' => trim((string)$eventType),
            'event_status' => trim((string)$status),
            'log_id' => isset($payload['log_id']) ? (int)$payload['log_id'] : '',
            'talk_id' => trim((string)($payload['talk_id'] ?? '')),
            'person_type' => trim((string)($payload['person_type'] ?? '')),
            'route_type' => trim((string)($payload['route_type'] ?? '')),
            'topic_type' => trim((string)($payload['topic_type'] ?? '')),
            'risk_level' => trim((string)($payload['risk_level'] ?? '')),
            'customer_inquiry_yn' => trim((string)($payload['customer_inquiry_yn'] ?? '')),
            'slack_alert_yn' => trim((string)($payload['slack_alert_yn'] ?? '')),
            'slack_alert_reason' => trim((string)($payload['slack_alert_reason'] ?? '')),
            'page_url' => trim((string)($payload['page_url'] ?? '')),
            'request_uri' => trim((string)($payload['request_uri'] ?? channel_talk_request_uri())),
            'client_ip' => trim((string)($payload['ip_address'] ?? channel_talk_request_ip())),
            'user_agent' => channel_talk_compact_log_message((string)($payload['user_agent'] ?? channel_talk_request_user_agent()), 300),
            'question' => channel_talk_compact_log_message((string)($payload['question'] ?? ''), 500),
            'answer' => channel_talk_compact_log_message((string)($payload['answer'] ?? ''), 500),
            'message' => $message,
        );

        $allowZeroKeys = array('log_id');
        $lines = array();
        if ($isNewFile) {
            $lines[] = "<?php exit; ?>";
            $lines[] = '';
        }
        $lines[] = '[' . $loggedAt . ']';
        foreach ($fields as $key => $value) {
            if ($value === '') continue;
            if ($value === 0 && !in_array($key, $allowZeroKeys, true)) continue;
            $lines[] = $key . ': ' . $value;
        }
        $lines[] = '---';
        $lines[] = '';

        $body = implode(PHP_EOL, $lines);
        return @file_put_contents($filePath, $body, FILE_APPEND | LOCK_EX) !== false;
    }
}

if (!function_exists('channel_talk_slack_webhook')) {
    function channel_talk_slack_webhook()
    {
        $webhook = channel_talk_env('CHANNEL_TALK_SLACK_WEBHOOK', '');
        if ($webhook !== '') return $webhook;

        $webhook = channel_talk_env('SYSTEM_ERROR_SLACK_WEBHOOK', '');
        if ($webhook !== '') return $webhook;

        $webhook = channel_talk_env('TM_OVERDUE_SLACK_WEBHOOK', '');
        if ($webhook !== '') return $webhook;

        if (defined('_system_error_slack_webhook')) {
            $webhook = trim((string)_system_error_slack_webhook);
            if ($webhook !== '') return $webhook;
        }
        if (defined('_tm_overdue_slack_webhook')) {
            $webhook = trim((string)_tm_overdue_slack_webhook);
            if ($webhook !== '') return $webhook;
        }

        return '';
    }
}

if (!function_exists('channel_talk_detect_topic_type')) {
    function channel_talk_detect_topic_type($question)
    {
        $question = trim((string)$question);
        if ($question === '') return 'service_basic';

        $map = array(
            'security' => array('exe', 'apk', 'bat', 'cmd', 'powershell', 'shell', 'script', 'malware', 'virus', '해킹', '권한 우회', 'sql'),
            'refund' => array('환불'),
            'credit_payment' => array('결제', '중복 결제', '승인 실패', '크레딧', '요금제', '결제 오류'),
            'member_account' => array('로그인', '회원가입', '계정', '비밀번호', '인증', '회사 정보'),
            'trade_request' => array('거래 요청', '채팅', '담당자 연락처', '거래 성사', '상대 기업'),
            'ai_recommend' => array('ai 추천', '추천 결과', '사업자등록번호', '추천 실패'),
            'data_issue' => array('데이터가 잘못', '정보 수정', '기업 데이터', '항목이 이상'),
            'error' => array('오류', '에러', '장애', '문제가 발생'),
        );

        foreach ($map as $topicType => $keywords) {
            foreach ($keywords as $keyword) {
                if (channel_talk_string_contains($question, $keyword)) {
                    return $topicType;
                }
            }
        }

        return 'service_basic';
    }
}

if (!function_exists('channel_talk_detect_route_bundle')) {
    function channel_talk_detect_route_bundle($question)
    {
        $question = trim((string)$question);
        $topicType = channel_talk_detect_topic_type($question);
        $routeType = 'ai';
        $riskLevel = 'low';
        $customerInquiryYn = 'N';
        $slackAlertYn = 'N';
        $slackAlertReason = 'not_required';

        $customerInquiryTopics = array('refund', 'credit_payment', 'member_account', 'error', 'data_issue');
        if (in_array($topicType, $customerInquiryTopics, true)) {
            $routeType = 'customer_inquiry';
            $customerInquiryYn = 'Y';
        }

        $slackPatterns = array(
            'security' => array('실행 파일', 'exe', 'apk', 'bat', 'cmd', 'powershell', 'shell script', 'sql injection', '해킹'),
            'payment_issue' => array('중복 결제', '결제 오류', '승인 실패', '환불'),
            'service_error' => array('반복 오류', '계속 실패', '장애', '기록 누락', '상태 표시가 이상'),
            'data_issue' => array('데이터가 잘못', '정보가 잘못', '기업 데이터 오류'),
        );

        foreach ($slackPatterns as $reason => $keywords) {
            foreach ($keywords as $keyword) {
                if (channel_talk_string_contains($question, $keyword)) {
                    $routeType = 'slack_alert';
                    $riskLevel = ($reason === 'security') ? 'high' : 'medium';
                    $customerInquiryYn = 'Y';
                    $slackAlertYn = 'Y';
                    $slackAlertReason = $reason;
                    break 2;
                }
            }
        }

        return array(
            'topic_type' => $topicType,
            'route_type' => $routeType,
            'risk_level' => $riskLevel,
            'customer_inquiry_yn' => $customerInquiryYn,
            'slack_alert_yn' => $slackAlertYn,
            'slack_alert_reason' => $slackAlertReason,
        );
    }
}

if (!function_exists('channel_talk_recursive_find_first')) {
    function channel_talk_recursive_find_first($payload, $keys)
    {
        if (!is_array($payload) || empty($keys)) return '';

        foreach ($keys as $key) {
            if (isset($payload[$key]) && !is_array($payload[$key])) {
                $value = trim((string)$payload[$key]);
                if ($value !== '') return $value;
            }
        }

        foreach ($payload as $value) {
            if (is_array($value)) {
                $found = channel_talk_recursive_find_first($value, $keys);
                if ($found !== '') return $found;
            }
        }

        return '';
    }
}

if (!function_exists('channel_talk_extract_talk_id_from_webhook')) {
    function channel_talk_extract_talk_id_from_webhook($payload)
    {
        $candidateKeys = array(
            'member_id',
            'memberId',
            'guest_session_id',
            'guestSessionId',
            'guest_id',
            'externalId',
            'external_id',
            'referenceId',
            'reference_id',
        );

        $priorityCandidates = array();
        foreach ($candidateKeys as $key) {
            $found = channel_talk_recursive_find_first($payload, array($key));
            if ($found !== '') {
                $priorityCandidates[] = $found;
            }
        }

        $profileCandidates = array();
        $profileKeys = array('name', 'member', 'memberCode', 'member_code');
        foreach ($profileKeys as $key) {
            $found = channel_talk_recursive_find_first($payload, array($key));
            if ($found !== '') {
                $profileCandidates[] = $found;
            }
        }

        $allCandidates = array_merge($priorityCandidates, $profileCandidates);
        foreach ($allCandidates as $candidate) {
            $normalized = channel_talk_normalize_member_id_candidate($candidate);
            if ($normalized !== '') return $normalized;
        }

        $channelLeadId = channel_talk_extract_channel_lead_id($payload);
        if ($channelLeadId !== '') return $channelLeadId;

        return 'talk_unknown';
    }
}

if (!function_exists('channel_talk_normalize_member_id_candidate')) {
    function channel_talk_normalize_member_id_candidate($candidate)
    {
        $candidate = trim((string)$candidate);
        if ($candidate === '') return '';

        if (channel_talk_string_contains($candidate, 'guest_')) {
            return $candidate;
        }

        if (preg_match('/^[a-f0-9-]{36}$/i', $candidate)) {
            return '';
        }

        if (preg_match('/^[a-zA-Z0-9._-]+$/', $candidate)) {
            return $candidate;
        }

        return '';
    }
}

if (!function_exists('channel_talk_extract_channel_lead_id')) {
    function channel_talk_extract_channel_lead_id($payload)
    {
        if (!is_array($payload)) return '';

        $user = $payload['refers']['user'] ?? array();
        if (is_array($user)) {
            $memberId = trim((string)($user['memberId'] ?? ''));
            if ($memberId !== '') return $memberId;

            $veilId = trim((string)($user['veilId'] ?? ''));
            if ($veilId !== '') return $veilId;

            $userId = trim((string)($user['id'] ?? ''));
            if ($userId !== '') return 'channel_user_' . $userId;
        }

        $userChat = $payload['refers']['userChat'] ?? array();
        if (is_array($userChat)) {
            $userId = trim((string)($userChat['userId'] ?? ''));
            if ($userId !== '') return 'channel_user_' . $userId;
        }

        return '';
    }
}

if (!function_exists('channel_talk_extract_message_text')) {
    function channel_talk_extract_message_text($entity)
    {
        $text = channel_talk_recursive_find_first($entity, array('plainText', 'text', 'message'));
        return trim((string)$text);
    }
}

if (!function_exists('channel_talk_extract_page_url_from_webhook')) {
    function channel_talk_extract_page_url_from_webhook($payload)
    {
        $pageUrl = trim((string)channel_talk_recursive_find_first($payload, array(
            'pageUrl',
            'page_url',
            'currentPageUrl',
            'current_page_url',
        )));

        if ($pageUrl !== '') return $pageUrl;

        $sourcePage = trim((string)($payload['refers']['userChat']['source']['page'] ?? ''));
        if ($sourcePage !== '') return $sourcePage;

        return '';
    }
}
if (!function_exists('channel_talk_extract_person_type')) {
    function channel_talk_extract_person_type($entity)
    {
        return trim((string)channel_talk_recursive_find_first($entity, array('personType')));
    }
}

if (!function_exists('channel_talk_slack_notify')) {
    function channel_talk_slack_notify($logRow)
    {
        if (!is_array($logRow)) return false;
        if (($logRow['slack_alert_yn'] ?? 'N') !== 'Y') return false;

        $webhook = channel_talk_slack_webhook();
        if ($webhook === '' || !function_exists('curl_init')) return false;

        $text = "[채널톡 알림]\n"
            . "- 시각: " . date('Y-m-d H:i:s') . "\n"
            . "- talk_id: " . (($logRow['talk_id'] ?? '') !== '' ? $logRow['talk_id'] : '-') . "\n"
            . "- 유형: " . (($logRow['topic_type'] ?? '') !== '' ? $logRow['topic_type'] : '-') . "\n"
            . "- 분기: " . (($logRow['route_type'] ?? '') !== '' ? $logRow['route_type'] : '-') . "\n"
            . "- 위험도: " . (($logRow['risk_level'] ?? '') !== '' ? $logRow['risk_level'] : '-') . "\n"
            . "- 사유: " . (($logRow['slack_alert_reason'] ?? '') !== '' ? $logRow['slack_alert_reason'] : '-') . "\n"
            . "- 질문: " . channel_talk_compact_log_message((string)($logRow['question'] ?? '-'), 500) . "\n"
            . "- 답변: " . channel_talk_compact_log_message((string)($logRow['answer'] ?? '-'), 500) . "\n"
            . "- 페이지: " . (trim((string)($logRow['page_url'] ?? '')) !== '' ? trim((string)($logRow['page_url'] ?? '')) : '-');

        $body = channel_talk_json_encode(array('text' => $text));
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
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $success = !($response === false || $httpCode < 200 || $httpCode >= 300);
        channel_talk_write_daily_file_log($success ? 'slack_alert_sent' : 'slack_alert_failed', $success ? 'success' : 'fail', array(
            'log_id' => isset($logRow['id']) ? (int)$logRow['id'] : '',
            'talk_id' => trim((string)($logRow['talk_id'] ?? '')),
            'route_type' => trim((string)($logRow['route_type'] ?? '')),
            'topic_type' => trim((string)($logRow['topic_type'] ?? '')),
            'risk_level' => trim((string)($logRow['risk_level'] ?? '')),
            'customer_inquiry_yn' => trim((string)($logRow['customer_inquiry_yn'] ?? '')),
            'slack_alert_yn' => trim((string)($logRow['slack_alert_yn'] ?? '')),
            'slack_alert_reason' => trim((string)($logRow['slack_alert_reason'] ?? '')),
            'page_url' => trim((string)($logRow['page_url'] ?? '')),
            'question' => trim((string)($logRow['question'] ?? '')),
            'answer' => trim((string)($logRow['answer'] ?? '')),
            'message' => $success ? '슬랙 알림을 발송했습니다.' : '슬랙 알림 발송에 실패했습니다.',
        ));

        return $success;
    }
}

if (!function_exists('channel_talk_insert_question_log')) {
    function channel_talk_insert_question_log($talkId, $question, $meta = array())
    {
        $table = channel_talk_db_table_name();
        $talkId = trim((string)$talkId);
        $question = trim((string)$question);
        if ($talkId === '' || $question === '') return 0;

        $bundle = channel_talk_detect_route_bundle($question);
        $pageUrl = trim((string)($meta['page_url'] ?? ''));
        $ipAddress = trim((string)($meta['ip_address'] ?? channel_talk_request_ip()));
        $userAgent = trim((string)($meta['user_agent'] ?? channel_talk_request_user_agent()));

        sql_query("
            INSERT INTO `{$table}`
            SET talk_id='" . sql_escape_string($talkId) . "',
                question='" . sql_escape_string($question) . "',
                answer='',
                route_type='" . sql_escape_string($bundle['route_type']) . "',
                topic_type='" . sql_escape_string($bundle['topic_type']) . "',
                risk_level='" . sql_escape_string($bundle['risk_level']) . "',
                customer_inquiry_yn='" . sql_escape_string($bundle['customer_inquiry_yn']) . "',
                slack_alert_yn='" . sql_escape_string($bundle['slack_alert_yn']) . "',
                slack_alert_reason=" . ($bundle['slack_alert_reason'] ? "'" . sql_escape_string($bundle['slack_alert_reason']) . "'" : "NULL") . ",
                page_url=" . ($pageUrl !== '' ? "'" . sql_escape_string($pageUrl) . "'" : "NULL") . ",
                ip_address=" . ($ipAddress !== '' ? "'" . sql_escape_string($ipAddress) . "'" : "NULL") . ",
                user_agent=" . ($userAgent !== '' ? "'" . sql_escape_string($userAgent) . "'" : "NULL") . ",
                created_at=NOW(),
                updated_at=NOW()
        ", false);

        $idRow = sql_fetch("SELECT LAST_INSERT_ID() AS id");
        $logId = (int)($idRow['id'] ?? 0);

        channel_talk_write_daily_file_log('question_received', 'success', array(
            'log_id' => $logId,
            'talk_id' => $talkId,
            'person_type' => 'user',
            'route_type' => $bundle['route_type'],
            'topic_type' => $bundle['topic_type'],
            'risk_level' => $bundle['risk_level'],
            'customer_inquiry_yn' => $bundle['customer_inquiry_yn'],
            'slack_alert_yn' => $bundle['slack_alert_yn'],
            'slack_alert_reason' => $bundle['slack_alert_reason'],
            'page_url' => $pageUrl,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'question' => $question,
            'answer' => '',
        ));

        return $logId;
    }
}

if (!function_exists('channel_talk_update_latest_answer_log')) {
    function channel_talk_update_latest_answer_log($talkId, $answer)
    {
        $table = channel_talk_db_table_name();
        $talkId = trim((string)$talkId);
        $answer = trim((string)$answer);
        if ($talkId === '' || $answer === '') return false;

        $row = sql_fetch("
            SELECT id, question, route_type, topic_type, risk_level, customer_inquiry_yn, slack_alert_yn, slack_alert_reason, page_url
            FROM `{$table}`
            WHERE talk_id='" . sql_escape_string($talkId) . "'
              AND answer=''
            ORDER BY id DESC
            LIMIT 1
        ");

        if (empty($row['id'])) return false;

        $updated = sql_query("
            UPDATE `{$table}`
            SET answer='" . sql_escape_string($answer) . "',
                updated_at=NOW()
            WHERE id='" . (int)$row['id'] . "'
            LIMIT 1
        ", false);

        if ($updated) {
            channel_talk_write_daily_file_log('answer_updated', 'success', array(
                'log_id' => (int)$row['id'],
                'talk_id' => $talkId,
                'person_type' => 'bot',
                'route_type' => trim((string)($row['route_type'] ?? '')),
                'topic_type' => trim((string)($row['topic_type'] ?? '')),
                'risk_level' => trim((string)($row['risk_level'] ?? '')),
                'customer_inquiry_yn' => trim((string)($row['customer_inquiry_yn'] ?? '')),
                'slack_alert_yn' => trim((string)($row['slack_alert_yn'] ?? '')),
                'slack_alert_reason' => trim((string)($row['slack_alert_reason'] ?? '')),
                'page_url' => trim((string)($row['page_url'] ?? '')),
                'question' => trim((string)($row['question'] ?? '')),
                'answer' => $answer,
            ));

            if (trim((string)($row['slack_alert_yn'] ?? 'N')) === 'Y') {
                channel_talk_slack_notify(array(
                    'id' => (int)$row['id'],
                    'talk_id' => $talkId,
                    'question' => trim((string)($row['question'] ?? '')),
                    'answer' => $answer,
                    'route_type' => trim((string)($row['route_type'] ?? '')),
                    'topic_type' => trim((string)($row['topic_type'] ?? '')),
                    'risk_level' => trim((string)($row['risk_level'] ?? '')),
                    'customer_inquiry_yn' => trim((string)($row['customer_inquiry_yn'] ?? '')),
                    'slack_alert_yn' => trim((string)($row['slack_alert_yn'] ?? '')),
                    'slack_alert_reason' => trim((string)($row['slack_alert_reason'] ?? '')),
                    'page_url' => trim((string)($row['page_url'] ?? '')),
                ));
            }
        }

        return (bool)$updated;
    }
}
