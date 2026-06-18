<?php
// company_list_ai_api용 공통 헬퍼
// - env 로딩
// - payload 구성
// - EC2 호출
// - 응답 정규화
if (!defined('_GNUBOARD_')) exit;

// .env 파일을 1회만 읽어 프로세스 환경변수로 주입
if (!function_exists('company_list_ai_load_env_file')) {
    function company_list_ai_load_env_file($env_path)
    {
        static $loaded = array();

        if (isset($loaded[$env_path])) {
            return;
        }
        $loaded[$env_path] = true;

        if (!is_file($env_path) || !is_readable($env_path)) {
            return;
        }

        $scanner = defined('INI_SCANNER_RAW') ? INI_SCANNER_RAW : INI_SCANNER_NORMAL;
        $values = @parse_ini_file($env_path, false, $scanner);
        if (!is_array($values)) {
            return;
        }

        foreach ($values as $name => $value) {
            if (getenv($name) !== false) {
                continue;
            }
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
            @putenv($name . '=' . $value);
        }
    }
}

if (!function_exists('company_list_ai_bootstrap_env')) {
    function company_list_ai_bootstrap_env()
    {
        static $bootstrapped = false;
        if ($bootstrapped) {
            return;
        }
        $bootstrapped = true;

        $root_env_path = dirname(dirname(dirname(__DIR__))) . '/.env';
        company_list_ai_load_env_file($root_env_path);
    }
}

if (!function_exists('company_list_ai_env')) {
    function company_list_ai_env($name, $default = '')
    {
        company_list_ai_bootstrap_env();

        if (function_exists('macomaex_env')) {
            return macomaex_env($name, $default);
        }

        $value = getenv($name);
        if ($value !== false && $value !== '') {
            return $value;
        }

        return $default;
    }
}

if (!function_exists('company_list_ai_compact_log_message')) {
    function company_list_ai_compact_log_message($message, $max_length = 255)
    {
        $message = trim((string)$message);
        if ($message === '') return '';

        $message = preg_replace('/\s+/u', ' ', $message);
        $max_length = (int)$max_length;
        if ($max_length < 1) return $message;

        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            if (mb_strlen($message, 'UTF-8') > $max_length) {
                return mb_substr($message, 0, $max_length - 1, 'UTF-8') . '…';
            }
            return $message;
        }

        if (strlen($message) > $max_length) {
            return substr($message, 0, $max_length - 3) . '...';
        }
        return $message;
    }
}

if (!function_exists('company_list_ai_normalize_trace_id')) {
    function company_list_ai_normalize_trace_id($trace_id = '')
    {
        // [추적 ID 정규화]
        // 외부에서 전달된 ID는 허용 문자와 길이를 제한하고, 없으면 조회하기 쉬운 짧은 실행 ID를 만든다.
        $trace_id = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$trace_id);
        if ($trace_id !== '') return substr($trace_id, 0, 64);

        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $suffix = '';
        try {
            $bytes = random_bytes(4);
            for ($i = 0; $i < 4; $i++) {
                $suffix .= $alphabet[ord($bytes[$i]) % strlen($alphabet)];
            }
        } catch (Exception $e) {
            for ($i = 0; $i < 4; $i++) {
                $suffix .= $alphabet[mt_rand(0, strlen($alphabet) - 1)];
            }
        }
        return 'R-' . date('ymd-His') . '-' . $suffix;
    }
}

if (!function_exists('company_list_ai_log_json')) {
    function company_list_ai_log_json($value, $max_length = 500000)
    {
        // [로그 JSON 정규화]
        // 요청/응답/출력 데이터는 유효한 JSON 문자열로 저장하며, 제한을 넘으면 원문 길이와 미리보기만 남긴다.
        if ($value === null || $value === '') return '';
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) $value = $decoded;
        }
        if (!is_array($value) && !is_object($value)) $value = array('value' => $value);
        $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($json)) return '';
        $max_length = max(1, (int)$max_length);
        if (strlen($json) <= $max_length) return $json;

        // 원문을 단순 절단하면 JSON이 깨지므로, 제한 초과 사실과 미리보기를 유효한 JSON으로 남긴다.
        $truncated = json_encode(array(
            'truncated' => true,
            'original_length' => strlen($json),
            'preview' => substr($json, 0, min(2000, max(1, $max_length - 100))),
        ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (is_string($truncated) && strlen($truncated) <= $max_length) return $truncated;

        $minimal = json_encode(array('truncated' => true, 'original_length' => strlen($json)));
        return (is_string($minimal) && strlen($minimal) <= $max_length) ? $minimal : '';
    }
}

if (!function_exists('company_list_ai_ensure_recommend_log_table')) {
    function company_list_ai_ensure_recommend_log_table()
    {
        static $ready = null;
        if ($ready !== null) return $ready;

        // 자주 조회하는 식별 필드만 컬럼으로 두고, 이벤트별 상세는 c_data JSON에 모은다.
        $sql = "CREATE TABLE IF NOT EXISTS company_recommend_log (
            c_id bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '로그 행 식별자',
            c_trace_id varchar(64) NOT NULL DEFAULT '' COMMENT '한 번의 추천 실행에서 발생한 이벤트를 묶는 실행 ID',
            c_event_type varchar(50) NOT NULL DEFAULT '' COMMENT 'request_received/external_request/external_response/cache_response/cache_save/output 등 이벤트 유형',
            c_event_status varchar(10) NOT NULL DEFAULT '' COMMENT 'success/fail',
            c_source varchar(50) NOT NULL DEFAULT '' COMMENT 'company_list_ai/company_list_ai_api/ec2/company_ml_log 등 이벤트 발생 주체',
            c_recommendation_mode varchar(20) NOT NULL DEFAULT '' COMMENT 'full=전체 추천, keyword=키워드 선택 추천',
            c_bizno varchar(10) NOT NULL DEFAULT '' COMMENT '기준 기업 사업자번호',
            c_kedcd varchar(10) NOT NULL DEFAULT '' COMMENT '기준 기업 KED 기업코드',
            c_trade_type tinyint unsigned NOT NULL DEFAULT 0 COMMENT '0=방향 없음, 1=구매처 추천, 2=판매처 추천',
            c_keywords text NOT NULL COMMENT '추천 버튼 클릭 시 선택되어 전달된 키워드 목록',
            c_error_code varchar(100) NOT NULL DEFAULT '' COMMENT '실패 사유 식별 코드',
            c_message varchar(500) NOT NULL DEFAULT '' COMMENT '사람이 읽는 로그 설명',
            c_data mediumtext NOT NULL COMMENT '이벤트별 요청, 응답, 출력 및 부가정보 JSON',
            c_datetime datetime NOT NULL COMMENT '로그 기록 시각',
            c_ip varchar(45) NOT NULL DEFAULT '' COMMENT '요청자 IPv4 또는 IPv6',
            PRIMARY KEY (c_id),
            KEY idx_trace_trade_event (c_trace_id, c_trade_type, c_event_type, c_id),
            KEY idx_kedcd_trade_dt (c_kedcd, c_trade_type, c_datetime),
            KEY idx_event_status_dt (c_event_type, c_event_status, c_datetime),
            KEY idx_error_dt (c_error_code, c_datetime)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
          COMMENT='거래처 추천 실행의 핵심 성공 이벤트와 단계별 실패 추적 로그'";

        $ready = (bool)sql_query($sql, false);
        return $ready;
    }
}

if (!function_exists('company_list_ai_normalize_event_type')) {
    function company_list_ai_normalize_event_type($event_type, $payload = array())
    {
        $source = trim((string)($payload['source'] ?? ''));
        $map = array(
            'recommend_entry' => 'request_received',
            'recommend_card_output' => 'output',
            'ml_log_save' => 'cache_save',
            'ml_cache_restore' => 'cache_response',
        );
        if ($event_type === 'recommend_request' && $source === 'ec2') return 'external_request';
        if ($event_type === 'recommend_response' && $source === 'ec2') return 'external_response';
        if ($event_type === 'recommend_response' && $source === 'company_ml_log') return 'cache_response';
        return isset($map[$event_type]) ? $map[$event_type] : $event_type;
    }
}

if (!function_exists('company_list_ai_build_event_data')) {
    function company_list_ai_build_event_data($payload)
    {
        $payload = is_array($payload) ? $payload : array('value' => $payload);
        $excluded = array(
            'trace_id', 'source', 'recommendation_mode', 'bizno', 'kedcd', 'trade_type',
            'keywords', 'error_code', 'client_ip', 'user_agent', 'stage',
        );
        $renamed = array(
            'request_data' => 'request',
            'response_data' => 'response',
            'output_data' => 'output',
        );
        $data = array();
        foreach ($payload as $key => $value) {
            if (in_array($key, $excluded, true)) continue;
            if ($value === '' || $value === null) continue;
            $data[isset($renamed[$key]) ? $renamed[$key] : $key] = $value;
        }
        $data['request_uri'] = trim((string)($_SERVER['REQUEST_URI'] ?? ''));
        $data['user_agent'] = company_list_ai_compact_log_message((string)($payload['user_agent'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? '')), 300);
        return company_list_ai_log_json($data);
    }
}

if (!function_exists('company_list_ai_write_db_event_log')) {
    function company_list_ai_write_db_event_log($event_type, $status, $payload, $message)
    {
        // [DB 추적 로그 저장]
        // 조회에 자주 쓰는 식별 필드는 컬럼에, 이벤트별 상세는 c_data JSON에 저장한다.
        if (!company_list_ai_ensure_recommend_log_table()) return false;
        $payload = is_array($payload) ? $payload : array();

        $values = array(
            'c_trace_id' => company_list_ai_normalize_trace_id($payload['trace_id'] ?? ''),
            'c_event_type' => (string)$event_type,
            'c_event_status' => (string)$status,
            'c_source' => (string)($payload['source'] ?? ''),
            'c_recommendation_mode' => (string)($payload['recommendation_mode'] ?? ''),
            'c_bizno' => preg_replace('/\D/', '', (string)($payload['bizno'] ?? '')),
            'c_kedcd' => preg_replace('/\D/', '', (string)($payload['kedcd'] ?? '')),
            'c_trade_type' => (int)($payload['trade_type'] ?? 0),
            'c_keywords' => company_list_ai_compact_log_message((string)($payload['keywords'] ?? ''), 2000),
            'c_error_code' => (string)($payload['error_code'] ?? ''),
            'c_message' => company_list_ai_compact_log_message($message, 500),
            'c_data' => company_list_ai_build_event_data($payload),
            'c_datetime' => date('Y-m-d H:i:s'),
            'c_ip' => (string)($payload['client_ip'] ?? ($_SERVER['REMOTE_ADDR'] ?? '')),
        );

        $sets = array();
        foreach ($values as $key => $value) {
            $sets[] = $key . "='" . sql_real_escape_string((string)$value) . "'";
        }
        return (bool)sql_query("insert company_recommend_log set " . implode(',', $sets), false);
    }
}

if (!function_exists('company_list_ai_log_directory_path')) {
    function company_list_ai_log_directory_path()
    {
        return dirname(dirname(__DIR__)) . '/logs/company_list_ai';
    }
}

if (!function_exists('company_list_ai_log_file_path')) {
    function company_list_ai_log_file_path($date_ymd = '')
    {
        $date_ymd = preg_replace('/[^0-9]/', '', (string)$date_ymd);
        if ($date_ymd === '') $date_ymd = date('Ymd');
        return company_list_ai_log_directory_path() . '/company_list_ai_log_' . $date_ymd . '.php';
    }
}

if (!function_exists('company_list_ai_log_default_message')) {
    function company_list_ai_log_default_message($event_type, $status, $payload = array())
    {
        $event_type = trim((string)$event_type);
        $status = trim((string)$status);
        $source = trim((string)($payload['source'] ?? ''));

        if ($status === 'success') {
            if ($event_type === 'request_received') return '추천 요청 접수 완료';
            if ($event_type === 'bizno_resolve') return '사업자번호 기준 기업정보 해석 완료';
            if ($event_type === 'external_request') return 'EC2 추천 요청 호출 시작';
            if ($event_type === 'external_response') return 'EC2 추천 응답 수신 완료';
            if ($event_type === 'cache_response') return '추천 캐시 응답 사용';
            if ($event_type === 'cache_save') return '추천 응답 캐시 저장 완료';
            if ($event_type === 'output') return '추천 카드 출력 완료';
            if ($event_type === 'keyword_lookup') return '업종코드 기반 추천 키워드 조회 완료';
            return 'AI 추천 작업이 완료되었습니다.';
        }

        return 'AI 추천 작업 중 오류가 발생했습니다.';
    }
}

if (!function_exists('company_list_ai_should_write_event_log')) {
    function company_list_ai_should_write_event_log($event_type, $status, $payload = array())
    {
        // 실패는 실제 발생 단계에만 남긴다. EC2 실패 뒤 이어지는 출력 불가 로그는 같은 오류의 중복이므로 제외한다.
        if ($status === 'fail') {
            if ($event_type === 'output' && (string)($payload['error_code'] ?? '') === 'recommend_failed') return false;
            return true;
        }
        if ($status !== 'success') return false;

        return in_array($event_type, array(
            'request_received', 'external_request', 'external_response',
            'cache_response', 'cache_save', 'output',
            'keyword_lookup', 'custom_keyword_search',
        ), true);
    }
}

if (!function_exists('company_list_ai_write_event_log')) {
    function company_list_ai_write_event_log($event_type, $status, $payload = array(), $message = '')
    {
        // [추천 핵심 이벤트 DB + 파일 이중 로그]
        // 추천 버튼 진입 이후 핵심 성공 이벤트와 실제 실패만 company_recommend_log와 일자별 파일에 동일하게 기록한다.
        $event_type = trim((string)$event_type);
        $status = trim((string)$status);
        if ($event_type === '' || $status === '') return false;
        // 상태 분류는 성공/실패만 허용한다. skip 같은 흐름 구분값을 실패로 오인해 적재하지 않는다.
        if (!in_array($status, array('success', 'fail'), true)) return false;
        $payload = is_array($payload) ? $payload : array('value' => $payload);
        $event_type = company_list_ai_normalize_event_type($event_type, $payload);
        if (!preg_match('/^[a-z0-9_.:-]{1,50}$/i', $event_type)) $event_type = 'invalid_event_type';
        if (!company_list_ai_should_write_event_log($event_type, $status, $payload)) return true;
        $payload['trace_id'] = company_list_ai_normalize_trace_id($payload['trace_id'] ?? '');
        $dir = company_list_ai_log_directory_path();
        // 파일과 DB는 독립 저장한다. 한쪽 장애가 다른 쪽 로그 기록까지 막아서는 안 된다.
        $file_ready = is_dir($dir) || @mkdir($dir, 0775, true) || is_dir($dir);
        $file_path = company_list_ai_log_file_path();
        $is_new_file = $file_ready && !file_exists($file_path);
        $logged_at = date('Y-m-d H:i:s');

        $message = company_list_ai_compact_log_message($message);
        if ($message === '') {
            $message = company_list_ai_log_default_message($event_type, $status, $payload);
        }

        $fields = array(
            'event_type' => $event_type,
            'event_status' => $status,
            'trace_id' => $payload['trace_id'],
            'source' => trim((string)($payload['source'] ?? '')),
            'recommendation_mode' => trim((string)($payload['recommendation_mode'] ?? '')),
            'bizno' => preg_replace('/\D/', '', (string)($payload['bizno'] ?? '')),
            'kedcd' => preg_replace('/\D/', '', (string)($payload['kedcd'] ?? '')),
            'trade_type' => (int)($payload['trade_type'] ?? 0),
            'keywords' => company_list_ai_compact_log_message((string)($payload['keywords'] ?? ''), 2000),
            'error_code' => trim((string)($payload['error_code'] ?? '')),
            'message' => $message,
            'data' => company_list_ai_build_event_data($payload),
            'client_ip' => trim((string)($payload['client_ip'] ?? ($_SERVER['REMOTE_ADDR'] ?? ''))),
        );

        $allow_zero_keys = array(
            'trade_type',
        );

        $lines = array();
        if ($is_new_file) {
            $lines[] = "<?php exit; ?>";
            $lines[] = '';
        }
        $lines[] = '[' . $logged_at . ']';
        foreach ($fields as $key => $value) {
            if ($value === '') continue;
            if ($value === 0 && !in_array($key, $allow_zero_keys, true)) continue;
            $lines[] = $key . ': ' . $value;
        }
        $lines[] = '---';
        $lines[] = '';

        $body = implode(PHP_EOL, $lines);
        $file_ok = $file_ready && @file_put_contents($file_path, $body, FILE_APPEND | LOCK_EX) !== false;
        $db_ok = company_list_ai_write_db_event_log($event_type, $status, $payload, $message);
        return $file_ok && $db_ok;
    }
}

if (!function_exists('company_list_ai_pick')) {
    function company_list_ai_pick($row, $keys, $default = '')
    {
        if (!is_array($row) || !is_array($keys)) {
            return $default;
        }

        foreach ($keys as $key) {
            if (!array_key_exists($key, $row)) {
                continue;
            }
            $value = $row[$key];
            if ($value === null) {
                continue;
            }
            if (is_string($value) && trim($value) === '') {
                continue;
            }
            return $value;
        }

        return $default;
    }
}

if (!function_exists('company_list_ai_is_real_company')) {
    function company_list_ai_is_real_company($name)
    {
        if (!is_string($name)) {
            return false;
        }

        $name = trim($name);
        if ($name === '' || mb_strlen($name) < 2) {
            return false;
        }

        static $bad = array(
            '신용카드', '현금영수증', '수취분', '발행분', '간이과세', '면세사업',
            '소계', '합계', '직접수출', '기타', '불명', '해당없음', '개인', '자가공급'
        );

        foreach ($bad as $word) {
            if (mb_strpos($name, $word) !== false) {
                return false;
            }
        }

        return true;
    }
}

if (!function_exists('company_list_ai_parse_name_list')) {
    function company_list_ai_parse_name_list($raw)
    {
        if (!is_string($raw) || trim($raw) === '') {
            return array();
        }

        $parts = explode('|', $raw);
        $names = array();

        foreach ($parts as $part) {
            $name = trim($part);
            if (!company_list_ai_is_real_company($name)) {
                continue;
            }
            $names[] = $name;
            if (count($names) >= 10) {
                break;
            }
        }

        return $names;
    }
}

// 프론트 요청값을 EC2 recommend 공통 payload로 정리
if (!function_exists('company_list_ai_build_base_payload')) {
    function company_list_ai_build_base_payload($source)
    {
        $kedcd_raw = isset($source['kedcd']) ? (string)$source['kedcd'] : '';
        $kedcd = str_pad(preg_replace('/\D/', '', $kedcd_raw), 10, '0', STR_PAD_LEFT);

        $payload = array(
            'kedcd' => $kedcd,
            'top_n' => 100,
        );

        $bzc_cd = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string)company_list_ai_pick($source, array('bzc_cd'), '')), 0, 10));
        $sido = trim((string)company_list_ai_pick($source, array('sido'), ''));
        $mpd = trim((string)company_list_ai_pick($source, array('mpd'), ''));
        $estb_dt = substr(preg_replace('/\D/', '', (string)company_list_ai_pick($source, array('estb_dt'), '')), 0, 8);

        $sze = (int)company_list_ai_pick($source, array('sze'), 0);
        if ($sze < 1 || $sze > 5) {
            $sze = 0;
        }

        $sales = max(0, (int)company_list_ai_pick($source, array('sales'), 0));
        $prev_sales = max(0, (int)company_list_ai_pick($source, array('prev_sales'), 0));
        $asset = max(0, (int)company_list_ai_pick($source, array('asset'), 0));
        $debt = max(0, (int)company_list_ai_pick($source, array('debt'), 0));
        $bad_cnt = max(0, (int)company_list_ai_pick($source, array('bad_cnt'), 0));

        if ($bzc_cd !== '') $payload['bzc_cd'] = $bzc_cd;
        if ($sido !== '') $payload['sido'] = $sido;
        if ($mpd !== '') $payload['mpd'] = $mpd;
        if ($estb_dt !== '') $payload['estb_dt'] = $estb_dt;
        if ($sze > 0) $payload['sze'] = $sze;
        if ($sales > 0) $payload['sales'] = $sales;
        if ($prev_sales > 0) $payload['prev_sales'] = $prev_sales;
        if ($asset > 0) $payload['asset'] = $asset;
        if ($debt > 0) $payload['debt'] = $debt;
        if ($bad_cnt > 0) $payload['bad_cnt'] = $bad_cnt;

        $cust_names = company_list_ai_parse_name_list((string)company_list_ai_pick($source, array('cust_names'), ''));
        $supp_names = company_list_ai_parse_name_list((string)company_list_ai_pick($source, array('supp_names'), ''));

        if (!empty($cust_names) || !empty($supp_names)) {
            $payload['cust_names'] = $cust_names;
            $payload['supp_names'] = $supp_names;
        }

        return $payload;
    }
}

if (!function_exists('company_list_ai_xml_val')) {
    function company_list_ai_xml_val($value)
    {
        if ($value === null || is_array($value) || is_object($value)) {
            return '';
        }
        return trim((string)$value);
    }
}

if (!function_exists('company_list_ai_xml_int')) {
    function company_list_ai_xml_int($value)
    {
        $s = company_list_ai_xml_val($value);
        return ($s !== '') ? (int)$s : 0;
    }
}

if (!function_exists('company_list_ai_collect_partner_names')) {
    function company_list_ai_collect_partner_names($section, $prefix)
    {
        $names = array();
        if (!is_array($section)) return $names;

        for ($i = 1; $i <= 10; $i++) {
            $k = $prefix . $i;
            $name = company_list_ai_xml_val(isset($section[$k]) ? $section[$k] : '');
            if (!company_list_ai_is_real_company($name)) continue;
            $names[] = $name;
        }

        return array_slice($names, 0, 10);
    }
}

if (!function_exists('company_list_ai_pick_company_api_log')) {
    function company_list_ai_pick_company_api_log($bizno, $today_only, $max_age_months = 0)
    {
        $bizno = preg_replace('/\D/', '', (string)$bizno);
        if (strlen($bizno) !== 10) return null;
        $max_age_months = (int)$max_age_months;
        if ($max_age_months < 0) $max_age_months = 0;

        $bizno_sql = sql_real_escape_string($bizno);
        $sql = "select c_bizno, c_kedcd, c_content, c_datetime
                from company_api_log
                where c_bizno='{$bizno_sql}'";
        if ($today_only) {
            $sql .= " and DATE(c_datetime)='" . date('Y-m-d') . "'";
        } else if ($max_age_months > 0) {
            $sql .= " and c_datetime >= date_sub(now(), interval {$max_age_months} month)";
        }
        $sql .= " order by c_datetime desc limit 1";

        $row = sql_fetch($sql);
        if (!is_array($row) || empty($row['c_content'])) return null;
        return $row;
    }
}

if (!function_exists('company_list_ai_build_base_payload_from_kodata_json')) {
    function company_list_ai_build_base_payload_from_kodata_json($json_text, $fallback_kedcd = '')
    {
        $decoded = json_decode((string)$json_text, true);
        if (!is_array($decoded)) {
            return array(
                'ok' => false,
                'error' => 'Kodata JSON 파싱 실패',
                'base_payload' => array(),
            );
        }

        $contents = (isset($decoded['CONTENTS']) && is_array($decoded['CONTENTS'])) ? $decoded['CONTENTS'] : array();
        $enp  = (isset($contents['ENPintr0000']) && is_array($contents['ENPintr0000'])) ? $contents['ENPintr0000'] : array();
        $cust = (isset($contents['ENPcust0001']) && is_array($contents['ENPcust0001'])) ? $contents['ENPcust0001'] : array();
        $supp = (isset($contents['ENPsupp0001']) && is_array($contents['ENPsupp0001'])) ? $contents['ENPsupp0001'] : array();
        $fsdt = (isset($contents['ENPfsdt0001']) && is_array($contents['ENPfsdt0001'])) ? $contents['ENPfsdt0001'] : array();
        $kbad = (isset($contents['ENPkbad0000']) && is_array($contents['ENPkbad0000'])) ? $contents['ENPkbad0000'] : array();

        $kedcd = preg_replace('/\D/', '', company_list_ai_xml_val(isset($enp['kedcd']) ? $enp['kedcd'] : ''));
        if (strlen($kedcd) !== 10) {
            $kedcd = preg_replace('/\D/', '', (string)$fallback_kedcd);
        }
        $kedcd = str_pad($kedcd, 10, '0', STR_PAD_LEFT);
        if (strlen($kedcd) !== 10 || preg_match('/^0+$/', $kedcd)) {
            return array(
                'ok' => false,
                'error' => 'Kodata 응답에 kedcd가 없습니다.',
                'base_payload' => array(),
            );
        }

        $cust_names = company_list_ai_collect_partner_names($cust, 'cust_nm');
        $supp_names = company_list_ai_collect_partner_names($supp, 'supp_nm');
        $loc_addra = company_list_ai_xml_val(isset($enp['loc_addra']) ? $enp['loc_addra'] : '');

        $source = array(
            'kedcd'      => $kedcd,
            'bzc_cd'     => company_list_ai_xml_val(isset($enp['bzc_cd']) ? $enp['bzc_cd'] : ''),
            'sido'       => ($loc_addra !== '') ? mb_substr($loc_addra, 0, 3) : '',
            'sze'        => company_list_ai_xml_val(isset($enp['enp_sze']) ? $enp['enp_sze'] : ''),
            'estb_dt'    => company_list_ai_xml_val(isset($enp['estb_dt']) ? $enp['estb_dt'] : ''),
            'mpd'        => company_list_ai_xml_val(isset($enp['mpd']) ? $enp['mpd'] : ''),
            'sales'      => company_list_ai_xml_int(isset($fsdt['fs_val9']) ? $fsdt['fs_val9'] : 0),
            'prev_sales' => company_list_ai_xml_int(isset($fsdt['fs1_val9']) ? $fsdt['fs1_val9'] : 0),
            'asset'      => company_list_ai_xml_int(isset($fsdt['fs_val3']) ? $fsdt['fs_val3'] : 0),
            'debt'       => company_list_ai_xml_int(isset($fsdt['fs_val6']) ? $fsdt['fs_val6'] : 0),
            'bad_cnt'    => company_list_ai_xml_int(isset($kbad['kfb_bad_cnt']) ? $kbad['kfb_bad_cnt'] : 0),
            'cust_names' => implode('|', $cust_names),
            'supp_names' => implode('|', $supp_names),
        );

        $payload = company_list_ai_build_base_payload($source);
        $payload['kedcd'] = $kedcd;

        return array(
            'ok' => true,
            'error' => '',
            'base_payload' => $payload,
        );
	}
}

if (!function_exists('company_list_ai_normalize_bzc_cd')) {
	function company_list_ai_normalize_bzc_cd($value) {
		return strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string)$value), 0, 10));
	}
}

if (!function_exists('company_list_ai_fetch_company_bzc_by_bizno')) {
	function company_list_ai_fetch_company_bzc_by_bizno($bizno) {
		$bizno = preg_replace('/\D/', '', (string)$bizno);
		if (strlen($bizno) !== 10) return '';
		$row = sql_fetch("select BZC_CD from company where BZNO='" . sql_real_escape_string($bizno) . "' and BZC_CD<>'' limit 1");
		return company_list_ai_normalize_bzc_cd($row['BZC_CD'] ?? '');
	}
}

if (!function_exists('company_list_ai_apply_bzc_resolution_meta')) {
	function company_list_ai_apply_bzc_resolution_meta($base_payload, $bizno, $source = '') {
		$base_payload = is_array($base_payload) ? $base_payload : array();
		$original_bzc = company_list_ai_normalize_bzc_cd($base_payload['bzc_cd'] ?? '');
		$final_bzc = $original_bzc;
		$missing = ($original_bzc === '');
		$resolved = false;
		$resolve_source = $original_bzc !== '' ? (string)$source : '';
		$fallback_reason = '';

		if ($missing) {
			$company_bzc = company_list_ai_fetch_company_bzc_by_bizno($bizno);
			if ($company_bzc !== '') {
				$final_bzc = $company_bzc;
				$base_payload['bzc_cd'] = $company_bzc;
				$resolved = true;
				$resolve_source = 'company';
				$fallback_reason = 'company_bzc_found';
			} else {
				unset($base_payload['bzc_cd']);
				$resolve_source = 'none';
				$fallback_reason = 'missing_bzc_cd';
			}
		}

		$base_payload['_bzc_meta'] = array(
			'original_source_bzc_cd' => $original_bzc,
			'source_bzc_cd' => $final_bzc,
			'source_bzc_missing' => $missing ? 1 : 0,
			'source_bzc_resolved' => $resolved ? 1 : 0,
			'source_bzc_resolve_source' => $resolve_source,
			'keyword_scope' => ($final_bzc === '') ? 'all' : 'related',
			'match_type' => ($final_bzc === '') ? 'global_all' : '',
			'fallback_reason' => $fallback_reason,
		);

		return $base_payload;
	}
}

if (!function_exists('company_list_ai_resolve_base_payload_by_bizno')) {
    function company_list_ai_resolve_base_payload_by_bizno($bizno, $allow_kodata = true, $trace_context = array())
    {
        // bizno -> ML 공통 payload 변환: cache-first, miss 시 Kodata
        $trace_context = is_array($trace_context) ? $trace_context : array();
        $resolve_trace_id = company_list_ai_normalize_trace_id($trace_context['trace_id'] ?? '');
        $resolve_trade_type = in_array((int)($trace_context['trade_type'] ?? 0), array(1, 2), true)
            ? (int)$trace_context['trade_type']
            : 0;
        $bizno = preg_replace('/\D/', '', (string)$bizno);
        if (strlen($bizno) !== 10) {
            company_list_ai_write_event_log('bizno_resolve', 'fail', array(
                'trace_id' => $resolve_trace_id,
                'trade_type' => $resolve_trade_type,
                'action' => 'resolve_base_payload',
                'bizno' => $bizno,
                'reason' => 'invalid_bizno',
                'error_code' => 'invalid_bizno',
            ), 'bizno는 10자리 숫자여야 합니다.');
            return array(
                'ok' => false,
                'status' => 400,
                'error' => 'bizno는 10자리 숫자여야 합니다.',
                'base_payload' => array(),
            );
        }

        $common_resolved = macomaex_kodata_resolve_company($bizno, array(
            'persist_company' => false,
            'allow_live' => $allow_kodata,
            'context' => 'company_list_ai',
        ));
        if (empty($common_resolved['success'])) {
            company_list_ai_write_event_log('bizno_resolve', 'fail', array(
                'trace_id' => $resolve_trace_id,
                'trade_type' => $resolve_trade_type,
                'action' => 'resolve_base_payload',
                'bizno' => $bizno,
                'reason' => $common_resolved['error_code'] ?? 'all_fallbacks_exhausted',
                'source' => 'none',
                'error_code' => $common_resolved['error_code'] ?? 'all_fallbacks_exhausted',
            ), $common_resolved['message'] ?? 'Kodata 및 저장된 기업정보 조회에 실패했습니다.');
            return array(
                'ok' => false,
                'status' => 502,
                'error' => $common_resolved['message'] ?? 'Kodata 및 저장된 기업정보 조회에 실패했습니다.',
                'base_payload' => array(),
            );
        }

        $common_source = (string)($common_resolved['source'] ?? '');
        if (!empty($common_resolved['json'])) {
            $parsed = company_list_ai_build_base_payload_from_kodata_json($common_resolved['json'], $common_resolved['company']['KEDCD'] ?? '');
        } else {
            $company_row = is_array($common_resolved['company'] ?? null) ? $common_resolved['company'] : array();
            $parsed = array(
                'ok' => !empty($company_row['KEDCD']),
                'error' => empty($company_row['KEDCD']) ? 'company fallback payload 생성 실패' : '',
                'base_payload' => company_list_ai_build_base_payload(array(
                    'kedcd' => $company_row['KEDCD'] ?? '',
                    'bzc_cd' => $company_row['BZC_CD'] ?? '',
                    'sido' => $company_row['LOC_ADDRA'] ?? '',
                    'sze' => $company_row['ENP_SZE'] ?? 0,
                    'estb_dt' => $company_row['ESTB_DT'] ?? '',
                    'mpd' => $company_row['PD_NM'] ?? '',
                    'sales' => $company_row['SALES'] ?? 0,
                )),
            );
        }
        if (empty($parsed['ok'])) {
            return array(
                'ok' => false,
                'status' => 502,
                'error' => $parsed['error'] ?? '기업정보 payload 생성 실패',
                'base_payload' => array(),
            );
        }

        $base_payload = company_list_ai_apply_bzc_resolution_meta($parsed['base_payload'], $bizno, $common_source);
        $bzc_meta = is_array($base_payload['_bzc_meta'] ?? null) ? $base_payload['_bzc_meta'] : array();

        company_list_ai_write_event_log('bizno_resolve', 'success', array(
            'trace_id' => $resolve_trace_id,
            'trade_type' => $resolve_trade_type,
            'action' => 'resolve_base_payload',
            'bizno' => $bizno,
            'kedcd' => (string)($base_payload['kedcd'] ?? ''),
            'source' => $common_source,
            'from_cache' => ($common_source === 'kodata_live') ? 0 : 1,
            'source_bzc_cd' => (string)($bzc_meta['source_bzc_cd'] ?? ''),
            'original_source_bzc_cd' => (string)($bzc_meta['original_source_bzc_cd'] ?? ''),
            'source_bzc_missing' => (int)($bzc_meta['source_bzc_missing'] ?? 0),
            'source_bzc_resolved' => (int)($bzc_meta['source_bzc_resolved'] ?? 0),
            'source_bzc_resolve_source' => (string)($bzc_meta['source_bzc_resolve_source'] ?? ''),
            'keyword_scope' => (string)($bzc_meta['keyword_scope'] ?? 'related'),
            'fallback_reason' => (string)($bzc_meta['fallback_reason'] ?? ''),
        ));
        return array(
            'ok' => true,
            'status' => 200,
            'error' => '',
            'source' => $common_source,
            'base_payload' => $base_payload,
        );

        // 1) 최근 1개월 캐시
        $recent_row = company_list_ai_pick_company_api_log($bizno, false, 1);
        if (is_array($recent_row) && !empty($recent_row['c_content'])) {
            $parsed = company_list_ai_build_base_payload_from_kodata_json($recent_row['c_content'], $recent_row['c_kedcd']);
            if (!empty($parsed['ok'])) {
                company_list_ai_write_event_log('bizno_resolve', 'success', array(
                    'trace_id' => $resolve_trace_id,
                    'trade_type' => $resolve_trade_type,
                    'action' => 'resolve_base_payload',
                    'bizno' => $bizno,
                    'kedcd' => (string)($parsed['base_payload']['kedcd'] ?? ''),
                    'source' => 'company_api_log_1m',
                    'cache_scope' => '1month',
                    'cache_datetime' => (string)$recent_row['c_datetime'],
                    'from_cache' => 1,
                ));
                return array(
                    'ok' => true,
                    'status' => 200,
                    'error' => '',
                    'source' => 'company_api_log_1m',
                    'cached_datetime' => (string)$recent_row['c_datetime'],
                    'base_payload' => $parsed['base_payload'],
                );
            }
        }

        // 2) 한 달 이내 캐시가 없으면 KODATA를 직접 호출한다.
        if (!$allow_kodata || !function_exists('getKODATA')) {
            company_list_ai_write_event_log('bizno_resolve', 'fail', array(
                'trace_id' => $resolve_trace_id,
                'trade_type' => $resolve_trade_type,
                'action' => 'resolve_base_payload',
                'bizno' => $bizno,
                'reason' => 'kodata_not_available',
                'source' => 'none',
                'error_code' => 'kodata_not_available',
            ), 'Kodata 캐시 없음(로컬 IP 제한 가능). company_api_log 캐시를 먼저 확보하세요.');
            return array(
                'ok' => false,
                'status' => 502,
                'error' => 'Kodata 캐시 없음(로컬 IP 제한 가능). company_api_log 캐시를 먼저 확보하세요.',
                'base_payload' => array(),
            );
        }

        $kodata_raw = (string)getKODATA($bizno);
        $parts = explode('|', $kodata_raw, 2);
        $code = isset($parts[0]) ? trim((string)$parts[0]) : '';
        $body = isset($parts[1]) ? (string)$parts[1] : '';

        if ($code !== '1000' || $body === '') {
            company_list_ai_write_event_log('bizno_resolve', 'fail', array(
                'trace_id' => $resolve_trace_id,
                'trade_type' => $resolve_trade_type,
                'action' => 'resolve_base_payload',
                'bizno' => $bizno,
                'reason' => 'kodata_call_failed',
                'source' => 'kodata_live',
                'error_code' => 'kodata_call_failed',
            ), ($body !== '') ? $body : 'Kodata 조회 실패');
            return array(
                'ok' => false,
                'status' => 502,
                'error' => ($body !== '') ? $body : 'Kodata 조회 실패',
                'base_payload' => array(),
            );
        }

        $parsed = company_list_ai_build_base_payload_from_kodata_json($body, '');
        if (empty($parsed['ok'])) {
            company_list_ai_write_event_log('bizno_resolve', 'fail', array(
                'trace_id' => $resolve_trace_id,
                'trade_type' => $resolve_trade_type,
                'action' => 'resolve_base_payload',
                'bizno' => $bizno,
                'reason' => 'kodata_parse_failed',
                'source' => 'kodata_live',
                'error_code' => 'kodata_parse_failed',
            ), isset($parsed['error']) ? $parsed['error'] : 'Kodata 파싱 실패');
            return array(
                'ok' => false,
                'status' => 502,
                'error' => isset($parsed['error']) ? $parsed['error'] : 'Kodata 파싱 실패',
                'base_payload' => array(),
            );
        }

        // Kodata 성공 응답은 company_api_log 에 즉시 기록해 재호출 절감
        $bizno_sql = sql_real_escape_string($bizno);
        $kedcd_sql = sql_real_escape_string((string)$parsed['base_payload']['kedcd']);
        $json_sql = sql_real_escape_string($body);
        $ip_sql = sql_real_escape_string((string)(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : ''));
        sql_query("insert company_api_log set c_bizno='{$bizno_sql}', c_kedcd='{$kedcd_sql}', c_content='{$json_sql}', c_datetime=now(), c_ip='{$ip_sql}'");
        company_list_ai_write_event_log('bizno_resolve', 'success', array(
            'trace_id' => $resolve_trace_id,
            'trade_type' => $resolve_trade_type,
            'action' => 'resolve_base_payload',
            'bizno' => $bizno,
            'kedcd' => (string)($parsed['base_payload']['kedcd'] ?? ''),
            'source' => 'kodata_live',
            'cache_scope' => 'live',
            'cache_datetime' => date('Y-m-d H:i:s'),
            'from_cache' => 0,
        ));

        return array(
            'ok' => true,
            'status' => 200,
            'error' => '',
            'source' => 'kodata_live',
            'cached_datetime' => date('Y-m-d H:i:s'),
            'base_payload' => $parsed['base_payload'],
        );
    }
}

if (!function_exists('company_list_ai_normalize_ml_cache_context')) {
    function company_list_ai_normalize_ml_cache_context($context = array())
    {
        // [캐시 식별 조건 정규화]
        // 상대 업종코드를 정렬하고 대응 가중치와 묶어, 선택 순서가 달라도 같은 조건이면 같은 캐시 키가 되게 한다.
        $context = is_array($context) ? $context : array();
        $codes = isset($context['filter_bzc_cds']) && is_array($context['filter_bzc_cds'])
            ? $context['filter_bzc_cds']
            : array();
        $weights = isset($context['filter_bzc_weights']) && is_array($context['filter_bzc_weights'])
            ? $context['filter_bzc_weights']
            : array();

        $pairs = array();
        foreach ($codes as $idx => $code) {
            $normalized = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string)$code), 0, 10));
            if ($normalized === '') continue;
            $pairs[$normalized] = max(1, (int)($weights[$idx] ?? 1));
        }
        ksort($pairs, SORT_STRING);

        $mode = empty($pairs) ? 'full' : 'keyword';
        $normalized_codes = array_keys($pairs);
        $normalized_weights = array_values($pairs);
        $signature = $mode . '|' . implode(',', $normalized_codes) . '|' . implode(',', $normalized_weights);

        return array(
            'recommendation_mode' => $mode,
            'filter_bzc_cds' => $normalized_codes,
            'filter_bzc_weights' => $normalized_weights,
            'cache_key' => hash('sha256', $signature),
        );
    }
}

if (!function_exists('company_list_ai_ml_cache_content_matches')) {
    function company_list_ai_ml_cache_content_matches($content, $cache_context)
    {
        // [캐시 조건 일치 확인]
        // 현재 요청의 캐시 키와 저장 응답의 _cache_meta 키가 같은 경우에만 해당 추천 응답을 재사용한다.
        $expected = company_list_ai_normalize_ml_cache_context($cache_context);
        $decoded = json_decode((string)$content, true);
        if (!is_array($decoded)) return false;

        // 메타데이터가 없는 기존 행은 전체 추천 캐시로 취급한다.
        $meta = isset($decoded['_cache_meta']) && is_array($decoded['_cache_meta'])
            ? company_list_ai_normalize_ml_cache_context($decoded['_cache_meta'])
            : company_list_ai_normalize_ml_cache_context();

        return hash_equals($expected['cache_key'], $meta['cache_key']);
    }
}

// [EC2 추천 응답 캐시 저장]
// EC2의 원본 응답 구조를 유지하면서 _cache_meta를 추가해 전체/키워드 추천 조건을 구분한다.
if (!function_exists('company_list_ai_save_ml_log')) {
    function company_list_ai_save_ml_log($bizno, $kedcd, $trade_type, $call_result, $cache_context = array())
    {
        $trade_type = ((int)$trade_type === 2) ? 2 : 1;
        $trace_id = company_list_ai_normalize_trace_id($cache_context['trace_id'] ?? '');
        $keywords_for_log = (string)($cache_context['keywords'] ?? '');
        $cache_context = company_list_ai_normalize_ml_cache_context($cache_context);

        $bizno = preg_replace('/\D/', '', (string)$bizno);
        if (strlen($bizno) !== 10) $bizno = '';

        $kedcd = preg_replace('/\D/', '', (string)$kedcd);
        $kedcd = str_pad($kedcd, 10, '0', STR_PAD_LEFT);
        if (strlen($kedcd) !== 10 || preg_match('/^0+$/', $kedcd)) $kedcd = '';

        $raw = '';
        if (is_array($call_result)) {
            if (isset($call_result['raw']) && is_string($call_result['raw']) && $call_result['raw'] !== '') {
                $raw = $call_result['raw'];
            } else if (isset($call_result['data']) && is_array($call_result['data'])) {
                $json = json_encode($call_result['data'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                if (is_string($json)) $raw = $json;
            }
        } else if (is_string($call_result)) {
            $raw = $call_result;
        }

        $decoded_raw = json_decode((string)$raw, true);
        if (is_array($decoded_raw)) {
            // 기존 results 응답 구조를 유지하면서 동일한 키워드 필터 조합만 복원한다.
            $decoded_raw['_cache_meta'] = $cache_context;
            $encoded_raw = json_encode($decoded_raw, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if (is_string($encoded_raw)) $raw = $encoded_raw;
        }

        if (!is_string($raw) || trim($raw) === '') {
            company_list_ai_write_event_log('cache_save', 'fail', array(
                'trace_id' => $trace_id,
                'stage' => 'cache_save',
                'source' => 'company_ml_log',
                'bizno' => $bizno,
                'kedcd' => $kedcd,
                'trade_type' => $trade_type,
                'reason' => 'empty_call_result',
                'error_code' => 'empty_call_result',
            ), 'ML 저장 대상 응답이 비어 있습니다.');
            return false;
        }

        // MEDIUMTEXT(16MB) 안전 여유
        if (strlen($raw) > 16000000) {
            $raw = substr($raw, 0, 16000000);
        }

        $bizno_sql = sql_real_escape_string($bizno);
        $kedcd_sql = sql_real_escape_string($kedcd);
        $content_sql = sql_real_escape_string($raw);
        $ip_sql = sql_real_escape_string((string)(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : ''));

        $sql = "insert company_ml_log
                set c_bizno='{$bizno_sql}',
                    c_kedcd='{$kedcd_sql}',
                    c_trade_type={$trade_type},
                    c_content='{$content_sql}',
                    c_datetime=now(),
                    c_ip='{$ip_sql}'";

        $ok = (bool)sql_query($sql, false);
        company_list_ai_write_event_log('cache_save', $ok ? 'success' : 'fail', array(
            'trace_id' => $trace_id,
            'stage' => 'cache_save',
            'bizno' => $bizno,
            'kedcd' => $kedcd,
            'trade_type' => $trade_type,
            'source' => 'company_ml_log',
            'recommendation_mode' => $cache_context['recommendation_mode'],
            'keywords' => $keywords_for_log,
            'filter_bzc_count' => count($cache_context['filter_bzc_cds']),
            'filter_bzc_cds' => implode(',', $cache_context['filter_bzc_cds']),
            'filter_bzc_weights' => implode(',', $cache_context['filter_bzc_weights']),
            'reason' => $ok ? 'saved' : 'insert_failed',
            'error_code' => $ok ? '' : 'insert_failed',
        ), $ok ? 'ML 결과 DB 저장 완료' : 'ML 결과 DB 저장 실패');
        return $ok;
    }
}

// [EC2 추천 응답 캐시 조회]
// 사업자번호/KED 코드, 거래 방향, 보존 기간, 필터 조합이 모두 맞는 최신 응답만 선택한다.
// - $today_only=true  : 당일 캐시만 조회
// - $today_only=false : 최신 캐시 조회 + 필요 시 $max_age_months 기간 제한 적용
if (!function_exists('company_list_ai_pick_ml_log')) {
    function company_list_ai_pick_ml_log($bizno, $kedcd, $trade_type, $today_only = true, $max_age_months = 0, $cache_context = array())
    {
        $trade_type = ((int)$trade_type === 2) ? 2 : 1;
        $cache_context = company_list_ai_normalize_ml_cache_context($cache_context);
        $max_age_months = (int)$max_age_months;
        if ($max_age_months < 0) $max_age_months = 0;

        $bizno = preg_replace('/\D/', '', (string)$bizno);
        if (strlen($bizno) !== 10) $bizno = '';

        $kedcd = preg_replace('/\D/', '', (string)$kedcd);
        $kedcd = str_pad($kedcd, 10, '0', STR_PAD_LEFT);
        if (strlen($kedcd) !== 10 || preg_match('/^0+$/', $kedcd)) $kedcd = '';

        if ($bizno === '' && $kedcd === '') {
            return null;
        }

        $where = array("c_trade_type={$trade_type}");
        if ($bizno !== '') $where[] = "c_bizno='" . sql_real_escape_string($bizno) . "'";
        if ($kedcd !== '') $where[] = "c_kedcd='" . sql_real_escape_string($kedcd) . "'";
        if ($today_only) $where[] = "DATE(c_datetime)='" . date('Y-m-d') . "'";
        // 무제한 latest 조회를 막기 위해 기간 제한이 있으면 추가 필터링
        else if ($max_age_months > 0) $where[] = "c_datetime >= date_sub(now(), interval {$max_age_months} month)";

        $sql = "select c_id, c_bizno, c_kedcd, c_trade_type, c_content, c_datetime
                from company_ml_log
                where " . implode(' and ', $where) . "
                order by c_datetime desc, c_id desc";

        $result = sql_query($sql, false);
        if (!$result) return null;
        while ($row = sql_fetch_array($result)) {
            if (empty($row['c_content'])) continue;
            if (company_list_ai_ml_cache_content_matches($row['c_content'], $cache_context)) return $row;
        }
        return null;
    }
}

// [캐시 응답 복원]
// company_ml_log의 JSON을 실시간 EC2 호출 결과와 동일한 call_result 형태로 복원한다.
if (!function_exists('company_list_ai_restore_call_from_ml_log')) {
    function company_list_ai_restore_call_from_ml_log($row, $trace_context = array())
    {
        if (!is_array($row) || empty($row['c_content'])) {
            return null;
        }

        $raw = (string)$row['c_content'];
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            company_list_ai_write_event_log('cache_response', 'fail', array(
                'trace_id' => (string)($trace_context['trace_id'] ?? ''),
                'bizno' => (string)($row['c_bizno'] ?? ''),
                'kedcd' => (string)($row['c_kedcd'] ?? ''),
                'trade_type' => (int)($row['c_trade_type'] ?? 0),
                'source' => 'company_ml_log',
                'recommendation_mode' => (string)($trace_context['recommendation_mode'] ?? ''),
                'keywords' => (string)($trace_context['keywords'] ?? ''),
                'reason' => 'json_decode_failed',
                'cache_datetime' => (string)($row['c_datetime'] ?? ''),
                'error_code' => 'json_decode_failed',
            ), 'ML 캐시 JSON 파싱 실패');
            return null;
        }

        return array(
            'ok' => true,
            'status' => 200,
            'error' => '',
            'elapsed_ms' => 0,
            'http_code' => 200,
            'raw' => $raw,
            'data' => $decoded,
            'from_cache' => true,
            'cached_datetime' => isset($row['c_datetime']) ? (string)$row['c_datetime'] : '',
        );
    }
}

if (!function_exists('company_list_ai_summarize_recommend_results')) {
    function company_list_ai_summarize_recommend_results($data, $limit = 100)
    {
        $results = isset($data['results']) && is_array($data['results']) ? $data['results'] : array();
        $summary = array();
        foreach (array_slice($results, 0, max(1, (int)$limit)) as $row) {
            if (!is_array($row)) continue;
            $summary[] = array(
                'kedcd' => (string)($row['kedcd'] ?? ''),
                'enp_nm' => (string)($row['enp_nm'] ?? ''),
                'bzc_cd' => (string)($row['bzc_cd'] ?? ''),
                'bzc_nm' => (string)($row['bzc_nm'] ?? ''),
                'score' => $row['display_score'] ?? ($row['score'] ?? null),
            );
        }
        return array(
            'result_count' => count($results),
            'results' => $summary,
        );
    }
}

if (!function_exists('company_list_ai_ec2_curl_error_code')) {
    function company_list_ai_ec2_curl_error_code($curl_errno, $connect_time_ms)
    {
        $curl_errno = (int)$curl_errno;
        if ($curl_errno === CURLE_COULDNT_RESOLVE_HOST) return 'dns_resolution_failed';
        if ($curl_errno === CURLE_COULDNT_CONNECT) return 'connection_refused';
        if ($curl_errno === CURLE_OPERATION_TIMEDOUT) {
            return (int)$connect_time_ms <= 0 ? 'connect_timeout' : 'response_timeout';
        }
        if (in_array($curl_errno, array(CURLE_RECV_ERROR, CURLE_SEND_ERROR, CURLE_PARTIAL_FILE), true)) {
            return 'connection_reset';
        }

        $ssl_errors = array(CURLE_SSL_CONNECT_ERROR);
        if (defined('CURLE_PEER_FAILED_VERIFICATION')) $ssl_errors[] = constant('CURLE_PEER_FAILED_VERIFICATION');
        if (defined('CURLE_SSL_CACERT')) $ssl_errors[] = constant('CURLE_SSL_CACERT');
        if (in_array($curl_errno, array_unique($ssl_errors), true)) return 'ssl_verification_failed';
        return 'curl_error';
    }
}

// [EC2 /recommend 단건 호출]
// 전송 직전에 요청 로그를 남기고, 설정 누락/cURL/HTTP/JSON 오류 및 정상 응답을 각각 응답 로그로 기록한다.
if (!function_exists('company_list_ai_call_recommend')) {
    function company_list_ai_call_recommend($payload, $timeout_sec, $trace_context = array())
    {
        // EC2 /recommend 단건 호출 공통 함수
        $base_url = rtrim((string)company_list_ai_env('ML_API_URL', ''), '/');
        $api_key = (string)company_list_ai_env('ML_API_KEY', '');
        $kedcd_for_log = str_pad(preg_replace('/\D/', '', (string)($payload['kedcd'] ?? '')), 10, '0', STR_PAD_LEFT);
        if (strlen($kedcd_for_log) !== 10 || preg_match('/^0+$/', $kedcd_for_log)) $kedcd_for_log = '';
        $bizno_for_log = preg_replace('/\D/', '', (string)($payload['bizno'] ?? ($payload['bzno'] ?? ($trace_context['bizno'] ?? ''))));
        if (strlen($bizno_for_log) !== 10) $bizno_for_log = '';
        $trade_type_for_log = ((int)($payload['trade_type'] ?? 1) === 2) ? 2 : 1;
        $filter_bzc_cds_for_log = array();
        if (isset($payload['filter_bzc_cds']) && is_array($payload['filter_bzc_cds'])) {
            foreach ($payload['filter_bzc_cds'] as $code) {
                $normalized_code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string)$code), 0, 10));
                if ($normalized_code === '' || in_array($normalized_code, $filter_bzc_cds_for_log, true)) continue;
                $filter_bzc_cds_for_log[] = $normalized_code;
            }
        }
        $filter_bzc_weights_for_log = array();
        if (isset($payload['filter_bzc_weights']) && is_array($payload['filter_bzc_weights'])) {
            foreach ($payload['filter_bzc_weights'] as $weight) {
                $filter_bzc_weights_for_log[] = max(1, (int)$weight);
            }
        }
        $log_payload = array(
            'action' => 'recommend',
            'trace_id' => company_list_ai_normalize_trace_id($trace_context['trace_id'] ?? ''),
            'stage' => 'response',
            'source' => 'ec2',
            'recommendation_mode' => !empty($filter_bzc_cds_for_log) ? 'keyword' : 'full',
            'kedcd' => $kedcd_for_log,
            'bizno' => $bizno_for_log,
            'trade_type' => $trade_type_for_log,
            'source_bzc_cd' => strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string)($payload['bzc_cd'] ?? '')), 0, 10)),
            'original_source_bzc_cd' => (string)($trace_context['original_source_bzc_cd'] ?? ''),
            'source_bzc_missing' => (int)($trace_context['source_bzc_missing'] ?? 0),
            'source_bzc_resolved' => (int)($trace_context['source_bzc_resolved'] ?? 0),
            'source_bzc_resolve_source' => (string)($trace_context['source_bzc_resolve_source'] ?? ''),
            'keyword_scope' => (string)($trace_context['keyword_scope'] ?? ''),
            'fallback_reason' => (string)($trace_context['fallback_reason'] ?? ''),
            'filter_bzc_count' => count($filter_bzc_cds_for_log),
            'filter_bzc_cds' => implode(',', $filter_bzc_cds_for_log),
            'filter_bzc_weights' => implode(',', $filter_bzc_weights_for_log),
            'top_n' => (int)($payload['top_n'] ?? 0),
            'cust_name_count' => (isset($payload['cust_names']) && is_array($payload['cust_names'])) ? count($payload['cust_names']) : 0,
            'supp_name_count' => (isset($payload['supp_names']) && is_array($payload['supp_names'])) ? count($payload['supp_names']) : 0,
            'keywords' => (string)($trace_context['keywords'] ?? ''),
            'match_type' => (string)($trace_context['match_type'] ?? ''),
        );

        if ($base_url === '' || $api_key === '') {
            company_list_ai_write_event_log('external_response', 'fail', array_merge($log_payload, array(
                'reason' => 'missing_ml_config',
                'error_code' => 'missing_ml_config',
                'http_code' => 0,
                'elapsed_ms' => 0,
            )), 'ML_API_URL 또는 ML_API_KEY가 설정되지 않았습니다.');
            return array(
                'ok' => false,
                'status' => 500,
                'error' => 'ML_API_URL 또는 ML_API_KEY가 설정되지 않았습니다.',
                'elapsed_ms' => 0,
                'http_code' => 0,
                'raw' => '',
                'data' => null,
            );
        }

        $url = $base_url . '/recommend';
        $json_payload = json_encode($payload, JSON_UNESCAPED_UNICODE);
        if ($json_payload === false) {
            company_list_ai_write_event_log('external_response', 'fail', array_merge($log_payload, array(
                'reason' => 'json_encode_failed',
                'error_code' => 'json_encode_failed',
                'http_code' => 0,
                'elapsed_ms' => 0,
            )), '요청 JSON 인코딩 실패');
            return array(
                'ok' => false,
                'status' => 500,
                'error' => '요청 JSON 인코딩 실패',
                'elapsed_ms' => 0,
                'http_code' => 0,
                'raw' => '',
                'data' => null,
            );
        }

        // 설정과 JSON 검증이 끝난 뒤 실제 EC2 호출 직전에만 요청 성공 이벤트를 기록한다.
        company_list_ai_write_event_log('external_request', 'success', array_merge($log_payload, array(
            'stage' => 'request',
            'reason' => 'call_started',
            'request_data' => $payload,
        )), 'EC2 추천 요청 호출 시작');

        $start = microtime(true);

        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json_payload,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'X-API-Key: ' . $api_key,
            ),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => (int)$timeout_sec,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ));

        $raw = curl_exec($ch);
        $curl_errno = curl_errno($ch);
        $curl_error = curl_error($ch);
        $http_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $connect_time_ms = (int)round((float)curl_getinfo($ch, CURLINFO_CONNECT_TIME) * 1000);
        curl_close($ch);

        $elapsed_ms = (int)round((microtime(true) - $start) * 1000);

        if ($curl_errno) {
            $error_code = company_list_ai_ec2_curl_error_code($curl_errno, $connect_time_ms);
            company_list_ai_write_event_log('external_response', 'fail', array_merge($log_payload, array(
                'reason' => $error_code,
                'error_code' => $error_code,
                'http_code' => $http_code,
                'elapsed_ms' => $elapsed_ms,
                'connect_time_ms' => $connect_time_ms,
                'response_data' => array('curl_errno' => $curl_errno, 'curl_error' => $curl_error),
            )), $curl_error !== '' ? $curl_error : 'cURL 오류');
            return array(
                'ok' => false,
                'status' => 502,
                'error' => $curl_error !== '' ? $curl_error : 'cURL 오류',
                'elapsed_ms' => $elapsed_ms,
                'http_code' => $http_code,
                'raw' => is_string($raw) ? $raw : '',
                'data' => null,
            );
        }

        if ($http_code < 200 || $http_code >= 300) {
            $error_code = $http_code > 0 ? 'http_' . $http_code : 'http_error';
            company_list_ai_write_event_log('external_response', 'fail', array_merge($log_payload, array(
                'reason' => $error_code,
                'error_code' => $error_code,
                'http_code' => $http_code,
                'elapsed_ms' => $elapsed_ms,
                'response_data' => array('raw' => is_string($raw) ? $raw : ''),
            )), 'EC2 응답 오류');
            return array(
                'ok' => false,
                'status' => 502,
                'error' => 'EC2 응답 오류',
                'elapsed_ms' => $elapsed_ms,
                'http_code' => $http_code,
                'raw' => is_string($raw) ? $raw : '',
                'data' => null,
            );
        }

        if ($raw === false || $raw === '') {
            company_list_ai_write_event_log('external_response', 'fail', array_merge($log_payload, array(
                'reason' => 'empty_response',
                'error_code' => 'empty_response',
                'http_code' => $http_code,
                'elapsed_ms' => $elapsed_ms,
            )), 'EC2 응답 본문이 비어 있습니다.');
            return array(
                'ok' => false,
                'status' => 502,
                'error' => 'EC2 응답 본문이 비어 있습니다.',
                'elapsed_ms' => $elapsed_ms,
                'http_code' => $http_code,
                'raw' => '',
                'data' => null,
            );
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            company_list_ai_write_event_log('external_response', 'fail', array_merge($log_payload, array(
                'reason' => 'json_decode_failed',
                'error_code' => 'json_decode_failed',
                'http_code' => $http_code,
                'elapsed_ms' => $elapsed_ms,
                'response_data' => array('raw' => is_string($raw) ? $raw : ''),
            )), 'EC2 JSON 파싱 실패');
            return array(
                'ok' => false,
                'status' => 502,
                'error' => 'EC2 JSON 파싱 실패',
                'elapsed_ms' => $elapsed_ms,
                'http_code' => $http_code,
                'raw' => is_string($raw) ? $raw : '',
                'data' => null,
            );
        }

        if (!isset($decoded['results']) || !is_array($decoded['results'])) {
            company_list_ai_write_event_log('external_response', 'fail', array_merge($log_payload, array(
                'reason' => 'invalid_response_schema',
                'error_code' => 'invalid_response_schema',
                'http_code' => $http_code,
                'elapsed_ms' => $elapsed_ms,
                'response_data' => array('response_keys' => array_keys($decoded)),
            )), 'EC2 응답에 results 배열이 없습니다.');
            return array(
                'ok' => false,
                'status' => 502,
                'error' => 'EC2 응답 형식이 올바르지 않습니다.',
                'elapsed_ms' => $elapsed_ms,
                'http_code' => $http_code,
                'raw' => $raw,
                'data' => null,
            );
        }

        $invalid_result_count = 0;
        foreach ($decoded['results'] as $result_item) {
            $result_kedcd = is_array($result_item)
                ? str_pad(preg_replace('/\D/', '', (string)($result_item['kedcd'] ?? '')), 10, '0', STR_PAD_LEFT)
                : '';
            if (!is_array($result_item) || strlen($result_kedcd) !== 10 || preg_match('/^0+$/', $result_kedcd)) {
                $invalid_result_count++;
            }
        }
        if (!empty($decoded['results']) && $invalid_result_count === count($decoded['results'])) {
            company_list_ai_write_event_log('external_response', 'fail', array_merge($log_payload, array(
                'reason' => 'all_results_invalid',
                'error_code' => 'all_results_invalid',
                'http_code' => $http_code,
                'elapsed_ms' => $elapsed_ms,
                'result_count' => count($decoded['results']),
                'invalid_result_count' => $invalid_result_count,
            )), 'EC2 추천 결과에 유효한 KED 기업코드가 없습니다.');
            return array(
                'ok' => false,
                'status' => 502,
                'error' => 'EC2 추천 결과가 올바르지 않습니다.',
                'elapsed_ms' => $elapsed_ms,
                'http_code' => $http_code,
                'raw' => $raw,
                'data' => null,
            );
        }

        company_list_ai_write_event_log('external_response', 'success', array_merge($log_payload, array(
            'reason' => 'ok',
            'http_code' => $http_code,
            'elapsed_ms' => $elapsed_ms,
            'result_count' => count($decoded['results']),
            'invalid_result_count' => $invalid_result_count,
            'from_cache' => 0,
            'response_data' => company_list_ai_summarize_recommend_results($decoded),
        )));

        return array(
            'ok' => true,
            'status' => 200,
            'error' => '',
            'elapsed_ms' => $elapsed_ms,
            'http_code' => $http_code,
            'raw' => $raw,
            'data' => $decoded,
            'trace_id' => $log_payload['trace_id'],
        );
    }
}

if (!function_exists('company_list_ai_normalize_radar')) {
    function company_list_ai_normalize_radar($radar)
    {
        if (!is_array($radar)) {
            return array();
        }

        $normalized = array();
        foreach ($radar as $label => $value) {
            if (count($normalized) >= 6) {
                break;
            }

            $label = trim((string)$label);
            if ($label === '') {
                $label = '지표';
            }

            $score = (float)$value;
            if ($score < 0) $score = 0;
            if ($score > 100) $score = 100;

            $normalized[$label] = (float)round($score, 1);
        }

        return $normalized;
    }
}

if (!function_exists('company_list_ai_normalize_item')) {
    function company_list_ai_normalize_item($item, $index)
    {
        if (!is_array($item)) {
            return null;
        }

        $rank = (int)company_list_ai_pick($item, array('rank'), $index + 1);
        if ($rank < 1) {
            $rank = $index + 1;
        }

        $score = (float)company_list_ai_pick($item, array('score'), 0);
        $display_score = company_list_ai_pick($item, array('display_score'), null);
        if ($display_score === null || $display_score === '') {
            $display_score = $score <= 1 ? ($score * 100) : $score;
        }
        $display_score = round((float)$display_score, 1);

        $kedcd = str_pad(preg_replace('/\D/', '', (string)company_list_ai_pick($item, array('kedcd'), '')), 10, '0', STR_PAD_LEFT);
        $bzno = preg_replace('/\D/', '', (string)company_list_ai_pick($item, array('bzno', 'bizno', 'biz_no'), ''));
        $enp_nm = trim((string)company_list_ai_pick($item, array('enp_nm', 'enpNm', 'company_name', 'name'), ''));
        $bzc_cd = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string)company_list_ai_pick($item, array('bzc_cd', 'bzcCd'), '')), 0, 10));
        // 추천 상세/LLM 보고서에서 쓰는 업종/재무 메타 필드 정규화
        $bzc_nm = trim((string)company_list_ai_pick($item, array('bzc_nm', 'bzcNm'), ''));
        $sido = trim((string)company_list_ai_pick($item, array('sido', 'region'), ''));
        $sze = (int)company_list_ai_pick($item, array('sze'), 0);
        if ($sze < 0) $sze = 0;
        $company_age = (int)company_list_ai_pick($item, array('company_age'), 0);
        if ($company_age < 0) $company_age = 0;
        $sales_growth_pct = company_list_ai_pick($item, array('sales_growth_pct'), null);
        if ($sales_growth_pct !== null && $sales_growth_pct !== '' && is_numeric($sales_growth_pct)) {
            $sales_growth_pct = (float)$sales_growth_pct;
        } else {
            $sales_growth_pct = null;
        }
        $debt_ratio_pct = company_list_ai_pick($item, array('debt_ratio_pct'), null);
        if ($debt_ratio_pct !== null && $debt_ratio_pct !== '' && is_numeric($debt_ratio_pct)) {
            $debt_ratio_pct = (float)$debt_ratio_pct;
        } else {
            $debt_ratio_pct = null;
        }
        $consec_profit_2y = company_list_ai_pick($item, array('consec_profit_2y'), null);
        if ($consec_profit_2y !== null && $consec_profit_2y !== '') {
            $consec_profit_2y = (int)$consec_profit_2y ? 1 : 0;
        } else {
            $consec_profit_2y = null;
        }
        $capital_negative = company_list_ai_pick($item, array('capital_negative'), null);
        if ($capital_negative !== null && $capital_negative !== '') {
            $capital_negative = (int)$capital_negative ? 1 : 0;
        } else {
            $capital_negative = null;
        }
        $candidate_type = trim((string)company_list_ai_pick($item, array('candidate_type'), ''));
        $one_line = trim((string)company_list_ai_pick($item, array('one_line', 'reason'), ''));

        return array(
            'rank' => $rank,
            'kedcd' => $kedcd,
            'bzno' => $bzno,
            'enp_nm' => $enp_nm,
            'bzc_cd' => $bzc_cd,
            'bzc_nm' => $bzc_nm,
            'sido' => $sido,
            'sze' => $sze,
            'score' => $score,
            'display_score' => $display_score,
            'company_age' => $company_age,
            'sales_growth_pct' => $sales_growth_pct,
            'debt_ratio_pct' => $debt_ratio_pct,
            'consec_profit_2y' => $consec_profit_2y,
            'capital_negative' => $capital_negative,
            'candidate_type' => $candidate_type,
            'radar' => company_list_ai_normalize_radar(company_list_ai_pick($item, array('radar'), array())),
            'one_line' => $one_line,
        );
    }
}

if (!function_exists('company_list_ai_empty_bucket')) {
    function company_list_ai_empty_bucket()
    {
        return array(
            'top3' => array(),
            'list' => array(),
            'total' => 0,
            'refresh_remaining' => 5,
        );
    }
}

// 결과를 top3/list/total 구조로 변환
if (!function_exists('company_list_ai_build_bucket')) {
    function company_list_ai_build_bucket($results, $list_limit)
    {
        if (!is_array($results)) {
            return company_list_ai_empty_bucket();
        }

        $normalized = array();
        foreach ($results as $index => $row) {
            $item = company_list_ai_normalize_item($row, $index);
            if (is_array($item)) {
                $normalized[] = $item;
            }
        }

        $top3 = array_slice($normalized, 0, 3);
        $rest = array_slice($normalized, 3);
        if (count($rest) > 1) {
            shuffle($rest);
        }

        return array(
            'top3' => $top3,
            'list' => array_slice($rest, 0, max(0, (int)$list_limit)),
            'total' => count($normalized),
            'refresh_remaining' => 5,
        );
    }
}
