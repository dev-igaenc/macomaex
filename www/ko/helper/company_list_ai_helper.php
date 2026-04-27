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
    function company_list_ai_pick_company_api_log($bizno, $today_only)
    {
        $bizno = preg_replace('/\D/', '', (string)$bizno);
        if (strlen($bizno) !== 10) return null;

        $bizno_sql = sql_real_escape_string($bizno);
        $sql = "select c_bizno, c_kedcd, c_content, c_datetime
                from company_api_log
                where c_bizno='{$bizno_sql}'";
        if ($today_only) {
            $sql .= " and DATE(c_datetime)='" . date('Y-m-d') . "'";
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

if (!function_exists('company_list_ai_resolve_base_payload_by_bizno')) {
    function company_list_ai_resolve_base_payload_by_bizno($bizno, $allow_kodata = true)
    {
        // bizno -> ML 공통 payload 변환: cache-first, miss 시 Kodata
        $bizno = preg_replace('/\D/', '', (string)$bizno);
        if (strlen($bizno) !== 10) {
            return array(
                'ok' => false,
                'status' => 400,
                'error' => 'bizno는 10자리 숫자여야 합니다.',
                'base_payload' => array(),
            );
        }

        // 1) 오늘 캐시
        $today_row = company_list_ai_pick_company_api_log($bizno, true);
        if (is_array($today_row) && !empty($today_row['c_content'])) {
            $parsed = company_list_ai_build_base_payload_from_kodata_json($today_row['c_content'], $today_row['c_kedcd']);
            if (!empty($parsed['ok'])) {
                return array(
                    'ok' => true,
                    'status' => 200,
                    'error' => '',
                    'source' => 'company_api_log_today',
                    'cached_datetime' => (string)$today_row['c_datetime'],
                    'base_payload' => $parsed['base_payload'],
                );
            }
        }

        // 2) 최근 캐시(로컬 개발에서 Kodata IP 제한 시 fallback)
        $latest_row = company_list_ai_pick_company_api_log($bizno, false);
        if (is_array($latest_row) && !empty($latest_row['c_content'])) {
            $parsed = company_list_ai_build_base_payload_from_kodata_json($latest_row['c_content'], $latest_row['c_kedcd']);
            if (!empty($parsed['ok'])) {
                return array(
                    'ok' => true,
                    'status' => 200,
                    'error' => '',
                    'source' => 'company_api_log_latest',
                    'cached_datetime' => (string)$latest_row['c_datetime'],
                    'base_payload' => $parsed['base_payload'],
                );
            }
        }

        // 3) Kodata 직접 호출 (운영/허용 IP에서만 성공 가능)
        if (!$allow_kodata || !function_exists('getKODATA')) {
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
            return array(
                'ok' => false,
                'status' => 502,
                'error' => ($body !== '') ? $body : 'Kodata 조회 실패',
                'base_payload' => array(),
            );
        }

        $parsed = company_list_ai_build_base_payload_from_kodata_json($body, '');
        if (empty($parsed['ok'])) {
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

// company_ml_log 저장
if (!function_exists('company_list_ai_save_ml_log')) {
    function company_list_ai_save_ml_log($bizno, $kedcd, $trade_type, $call_result)
    {
        $trade_type = ((int)$trade_type === 2) ? 2 : 1;

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

        if (!is_string($raw) || trim($raw) === '') {
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

        return (bool)sql_query($sql, false);
    }
}

// company_ml_log 조회 (당일/최신)
if (!function_exists('company_list_ai_pick_ml_log')) {
    function company_list_ai_pick_ml_log($bizno, $kedcd, $trade_type, $today_only = true)
    {
        $trade_type = ((int)$trade_type === 2) ? 2 : 1;

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

        $sql = "select c_id, c_bizno, c_kedcd, c_trade_type, c_content, c_datetime
                from company_ml_log
                where " . implode(' and ', $where) . "
                order by c_datetime desc, c_id desc
                limit 1";

        $row = sql_fetch($sql);
        if (!is_array($row) || empty($row['c_content'])) return null;
        return $row;
    }
}

// company_ml_log row -> recommend call_result 형태로 복원
if (!function_exists('company_list_ai_restore_call_from_ml_log')) {
    function company_list_ai_restore_call_from_ml_log($row)
    {
        if (!is_array($row) || empty($row['c_content'])) {
            return null;
        }

        $raw = (string)$row['c_content'];
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
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

// EC2 /recommend 단건 호출
if (!function_exists('company_list_ai_call_recommend')) {
    function company_list_ai_call_recommend($payload, $timeout_sec)
    {
        // EC2 /recommend 단건 호출 공통 함수
        $base_url = rtrim((string)company_list_ai_env('ML_API_URL', ''), '/');
        $api_key = (string)company_list_ai_env('ML_API_KEY', '');

        if ($base_url === '' || $api_key === '') {
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
        curl_close($ch);

        $elapsed_ms = (int)round((microtime(true) - $start) * 1000);

        if ($curl_errno) {
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

        if ($http_code < 200 || $http_code >= 300 || $raw === false || $raw === '') {
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

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
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

        return array(
            'ok' => true,
            'status' => 200,
            'error' => '',
            'elapsed_ms' => $elapsed_ms,
            'http_code' => $http_code,
            'raw' => $raw,
            'data' => $decoded,
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
        $candidate_type = trim((string)company_list_ai_pick($item, array('candidate_type'), ''));
        $one_line = trim((string)company_list_ai_pick($item, array('one_line', 'reason'), ''));

        return array(
            'rank' => $rank,
            'kedcd' => $kedcd,
            'bzno' => $bzno,
            'enp_nm' => $enp_nm,
            'bzc_cd' => $bzc_cd,
            'score' => $score,
            'display_score' => $display_score,
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
