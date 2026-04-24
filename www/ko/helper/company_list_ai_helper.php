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

// EC2 /recommend 단건 호출
if (!function_exists('company_list_ai_call_recommend')) {
    function company_list_ai_call_recommend($payload, $timeout_sec)
    {
        $base_url = rtrim((string)company_list_ai_env('ML_API_URL', ''), '/');
        $api_key = (string)company_list_ai_env('ML_API_KEY', '');

        if ($base_url === '' || $api_key === '') {
            return array(
                'ok' => false,
                'status' => 500,
                'error' => 'ML_API_URL 또는 ML_API_KEY가 설정되지 않았습니다.',
                'elapsed_ms' => 0,
                'http_code' => 0,
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
                'data' => null,
            );
        }

        return array(
            'ok' => true,
            'status' => 200,
            'error' => '',
            'elapsed_ms' => $elapsed_ms,
            'http_code' => $http_code,
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
