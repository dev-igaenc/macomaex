<?php
if (!defined('_GNUBOARD_')) exit;

function empty_mb_id($reg_mb_id)
{
    if (trim($reg_mb_id)=='')
        return "회원아이디를 입력해 주십시오.";
    else
        return "";
}

function valid_mb_id($reg_mb_id)
{
    if (preg_match("/[^0-9a-z_]+/i", $reg_mb_id))
        return "회원아이디는 영문자, 숫자, _ 만 입력하세요.";
    else
        return "";
}

function count_mb_id($reg_mb_id)
{
    if (strlen($reg_mb_id) < 3)
        return "회원아이디는 최소 3글자 이상 입력하세요.";
    else
        return "";
}

function exist_mb_id($reg_mb_id)
{
    global $g5;

    $reg_mb_id = trim($reg_mb_id);
    if ($reg_mb_id == "") return "";

    $sql = " select count(*) as cnt from `{$g5['member_table']}` where mb_id = '$reg_mb_id' ";
    $row = sql_fetch($sql);
    if ($row['cnt'])
        return "이미 사용중인 회원아이디 입니다.";
    else
        return "";
}

function reserve_mb_id($reg_mb_id)
{
    global $config;
    if (preg_match("/[\,]?{$reg_mb_id}/i", $config['cf_prohibit_id']))
        return "이미 예약된 단어로 사용할 수 없는 회원아이디 입니다.";
    else
        return "";
}

function empty_mb_nick($reg_mb_nick)
{
    if (!trim($reg_mb_nick))
        return "닉네임을 입력해 주십시오.";
    else
        return "";
}

function valid_mb_nick($reg_mb_nick)
{
    if (!check_string($reg_mb_nick, G5_HANGUL + G5_ALPHABETIC + G5_NUMERIC))
        return "닉네임은 공백없이 한글, 영문, 숫자만 입력 가능합니다.";
    else
        return "";
}

function count_mb_nick($reg_mb_nick)
{
    if (strlen($reg_mb_nick) < 4)
        return "닉네임은 한글 2글자, 영문 4글자 이상 입력 가능합니다.";
    else
        return "";
}

function exist_mb_nick($reg_mb_nick, $reg_mb_id)
{
    global $g5;
    $row = sql_fetch(" select count(*) as cnt from {$g5['member_table']} where mb_nick = '$reg_mb_nick' and mb_id <> '$reg_mb_id' ");
    if ($row['cnt'])
        return "이미 존재하는 닉네임입니다.";
    else
        return "";
}

function reserve_mb_nick($reg_mb_nick)
{
    global $config;
    if (preg_match("/[\,]?".preg_quote($reg_mb_nick)."/i", $config['cf_prohibit_id']))
        return "이미 예약된 단어로 사용할 수 없는 닉네임 입니다.";
    else
        return "";
}

function empty_mb_email($reg_mb_email)
{
    if (!trim($reg_mb_email))
        return "E-mail 주소를 입력해 주십시오.";
    else
        return "";
}

function valid_mb_email($reg_mb_email)
{
    if (!preg_match("/([0-9a-zA-Z_-]+)@([0-9a-zA-Z_-]+)\.([0-9a-zA-Z_-]+)/", $reg_mb_email))
        return "E-mail 주소가 형식에 맞지 않습니다.";
    else
        return "";
}

// 금지 메일 도메인 검사
function prohibit_mb_email($reg_mb_email)
{
    global $config;

    list($id, $domain) = explode("@", $reg_mb_email);
    $email_domains = explode("\n", trim($config['cf_prohibit_email']));
    $email_domains = array_map('trim', $email_domains);
    $email_domains = array_map('strtolower', $email_domains);
    $email_domain = strtolower($domain);

    if (in_array($email_domain, $email_domains))
        return "$domain 메일은 사용할 수 없습니다.";

    return "";
}

function exist_mb_email($reg_mb_email, $reg_mb_id)
{
    global $g5;
    $row = sql_fetch(" select count(*) as cnt from `{$g5['member_table']}` where mb_email = '$reg_mb_email' and mb_id <> '$reg_mb_id' ");
    if ($row['cnt'])
        return "이미 사용중인 E-mail 주소입니다.";
    else
        return "";
}

function empty_mb_name($reg_mb_name)
{
    if (!trim($reg_mb_name))
        return "이름을 입력해 주십시오.";
    else
        return "";
}

function valid_mb_name($mb_name)
{
    if (!check_string($mb_name, G5_HANGUL))
        return "이름은 공백없이 한글만 입력 가능합니다.";
    else
        return "";
}

function valid_mb_hp($reg_mb_hp)
{
    $reg_mb_hp = preg_replace("/[^0-9]/", "", $reg_mb_hp);
    if(!$reg_mb_hp)
        return "휴대폰번호를 입력해 주십시오.";
    else {
        if(preg_match("/^01[0-9]{8,9}$/", $reg_mb_hp))
            return "";
        else
            return "휴대폰번호를 올바르게 입력해 주십시오.";
    }
}

function exist_mb_hp($reg_mb_hp, $reg_mb_id)
{
    global $g5;

    if (!trim($reg_mb_hp)) return "";

    $reg_mb_hp = hyphen_hp_number($reg_mb_hp);

    $sql = "select count(*) as cnt from {$g5['member_table']} where mb_hp = '$reg_mb_hp' and mb_id <> '$reg_mb_id' ";
    $row = sql_fetch($sql);

    if($row['cnt'])
        return " 이미 사용 중인 휴대폰번호입니다. ".$reg_mb_hp;
    else
        return "";
}

function macomaex_aligo_config()
{
    $user_id = function_exists('macomaex_env') ? trim((string) macomaex_env('ALIGO_USER_ID', '')) : '';
    $api_key = function_exists('macomaex_env') ? trim((string) macomaex_env('ALIGO_API_KEY', '')) : '';
    $sender = function_exists('macomaex_env') ? trim((string) macomaex_env('ALIGO_SENDER', '')) : '';
    $sender = preg_replace('/[^0-9]/', '', $sender);

    return array(
        'user_id' => $user_id,
        'api_key' => $api_key,
        'sender' => $sender,
        'ready' => ($user_id !== '' && $api_key !== '' && $sender !== ''),
    );
}

function macomaex_aligo_send_sms($receiver, $message)
{
    $config = macomaex_aligo_config();
    $receiver = preg_replace('/[^0-9]/', '', (string) $receiver);
    $message = trim((string) $message);

    if (!$config['ready']) {
        return array(
            'ok' => false,
            'message' => '알리고 설정값이 누락되었습니다.',
            'debug' => array('reason' => 'missing_config'),
        );
    }

    if ($receiver === '') {
        return array(
            'ok' => false,
            'message' => '수신번호를 입력해 주세요.',
            'debug' => array('reason' => 'empty_receiver'),
        );
    }

    if ($message === '') {
        return array(
            'ok' => false,
            'message' => '문자 내용을 입력해 주세요.',
            'debug' => array('reason' => 'empty_message'),
        );
    }

    if (!function_exists('curl_init')) {
        return array(
            'ok' => false,
            'message' => '문자 발송 모듈을 사용할 수 없습니다.',
            'debug' => array('reason' => 'curl_missing'),
        );
    }

    $data = array(
        'key' => $config['api_key'],
        'user_id' => $config['user_id'],
        'sender' => $config['sender'],
        'receiver' => $receiver,
        'msg' => $message,
        'msg_type' => 'SMS',
    );

    $ch = curl_init('https://apis.aligo.in/send/');
    curl_setopt_array($ch, array(
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_TIMEOUT => 10,
    ));

    $response_body = curl_exec($ch);
    $curl_error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $decoded = json_decode((string) $response_body, true);
    $result_code = isset($decoded['result_code']) ? (int) $decoded['result_code'] : 0;
    $ok = ($curl_error === '' && $http_code >= 200 && $http_code < 300 && $result_code > 0);

    $debug = array(
        'http_code' => $http_code,
        'curl_error' => $curl_error,
        'result_code' => isset($decoded['result_code']) ? $decoded['result_code'] : null,
        'aligo_message' => isset($decoded['message']) ? $decoded['message'] : '',
    );

    if (!$ok) {
        $log_debug = $debug;
        $log_debug['raw'] = $response_body;
        error_log('[aligo_sms] send failed ' . json_encode($log_debug, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    return array(
        'ok' => $ok,
        'message' => $ok ? '문자를 발송했습니다.' : '문자 발송에 실패했습니다.',
        'debug' => $debug,
    );
}

function macomaex_signup_phone_otp_normalize($phone)
{
    return hyphen_hp_number($phone);
}

function macomaex_signup_phone_otp_hash($phone, $code)
{
    return hash('sha256', macomaex_signup_phone_otp_normalize($phone).'|'.trim((string) $code).'|'.session_id());
}

function macomaex_signup_phone_otp_clear()
{
    set_session('ss_signup_phone_otp_hp', '');
    set_session('ss_signup_phone_otp_code_hash', '');
    set_session('ss_signup_phone_otp_expires', '');
    set_session('ss_signup_phone_otp_verified', '');
    set_session('ss_signup_phone_otp_verified_hp', '');
}

function macomaex_signup_phone_otp_send($phone)
{
    $phone = macomaex_signup_phone_otp_normalize($phone);

    if ($msg = valid_mb_hp($phone)) {
        return array('ok' => false, 'message' => $msg, 'debug' => array('reason' => 'invalid_phone'));
    }

    if (function_exists('random_int')) {
        try {
            $code = (string) random_int(100000, 999999);
        } catch (Exception $e) {
            $code = (string) mt_rand(100000, 999999);
        }
    } else {
        $code = (string) mt_rand(100000, 999999);
    }

    $message = '[매커멕스] 인증번호 ['.$code.']를 입력해 주세요.';
    $result = macomaex_aligo_send_sms($phone, $message);

    if (empty($result['ok'])) {
        macomaex_signup_phone_otp_clear();
        return $result;
    }

    set_session('ss_signup_phone_otp_hp', $phone);
    set_session('ss_signup_phone_otp_code_hash', macomaex_signup_phone_otp_hash($phone, $code));
    set_session('ss_signup_phone_otp_expires', G5_SERVER_TIME + 300);
    set_session('ss_signup_phone_otp_verified', '');
    set_session('ss_signup_phone_otp_verified_hp', '');

    return array(
        'ok' => true,
        'message' => '인증번호를 발송했습니다. 5분 안에 입력해 주세요.',
        'debug' => isset($result['debug']) ? $result['debug'] : array(),
    );
}

function macomaex_signup_phone_otp_verify($phone, $code)
{
    $phone = macomaex_signup_phone_otp_normalize($phone);
    $code = preg_replace('/[^0-9]/', '', (string) $code);

    if ($msg = valid_mb_hp($phone)) {
        return array('ok' => false, 'message' => $msg);
    }

    if ($code === '' || strlen($code) !== 6) {
        return array('ok' => false, 'message' => '인증번호 6자리를 입력해 주세요.');
    }

    $session_phone = macomaex_signup_phone_otp_normalize(get_session('ss_signup_phone_otp_hp'));
    $session_hash = (string) get_session('ss_signup_phone_otp_code_hash');
    $session_expires = (int) get_session('ss_signup_phone_otp_expires');

    if ($session_phone === '' || $session_hash === '' || $session_expires <= 0) {
        return array('ok' => false, 'message' => '인증번호 발송 후 다시 시도해 주세요.');
    }

    if ($session_phone !== $phone) {
        return array('ok' => false, 'message' => '인증번호를 받은 휴대폰 번호와 일치하지 않습니다.');
    }

    if ($session_expires < G5_SERVER_TIME) {
        macomaex_signup_phone_otp_clear();
        return array('ok' => false, 'message' => '인증번호가 만료되었습니다. 다시 발송해 주세요.');
    }

    $expected_hash = macomaex_signup_phone_otp_hash($phone, $code);
    $hash_matched = function_exists('hash_equals') ? hash_equals($session_hash, $expected_hash) : ($session_hash === $expected_hash);

    if (!$hash_matched) {
        return array('ok' => false, 'message' => '인증번호가 일치하지 않습니다.');
    }

    set_session('ss_signup_phone_otp_verified', '1');
    set_session('ss_signup_phone_otp_verified_hp', $phone);

    return array('ok' => true, 'message' => '휴대폰 인증이 완료되었습니다.');
}

function macomaex_signup_phone_otp_is_verified($phone)
{
    $phone = macomaex_signup_phone_otp_normalize($phone);
    $verified = get_session('ss_signup_phone_otp_verified');
    $verified_phone = macomaex_signup_phone_otp_normalize(get_session('ss_signup_phone_otp_verified_hp'));

    return ($verified === '1' && $phone !== '' && $verified_phone === $phone);
}

function macomaex_signup_sms_debug_allowed()
{
    $ip = isset($_SERVER['REMOTE_ADDR']) ? trim((string) $_SERVER['REMOTE_ADDR']) : '';

    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && trim((string) $_SERVER['HTTP_X_FORWARDED_FOR']) !== '') {
        $parts = explode(',', (string) $_SERVER['HTTP_X_FORWARDED_FOR']);
        $forwarded_ip = trim((string) $parts[0]);
        if ($forwarded_ip !== '') {
            $ip = $forwarded_ip;
        }
    }

    $allowed_ips = array(
        '1.226.85.230',
        '222.107.227.177',
        '222.122.39.49',
        '127.0.0.1',
        '::1',
    );

    return in_array($ip, $allowed_ips, true);
}

function macomaex_signup_phone_certification($mb_name, $mb_hp, $post_cert_no = '')
{
    global $config, $g5, $member;

    $mb_name = trim((string) $mb_name);
    $mb_hp = hyphen_hp_number($mb_hp);
    $post_cert_no = trim((string) $post_cert_no);

    if (!$config['cf_cert_use']) {
        return array('ok' => false, 'message' => '회원가입을 위해서는 휴대폰 본인확인 설정이 필요합니다.');
    }

    if ($msg = empty_mb_name($mb_name)) {
        return array('ok' => false, 'message' => $msg);
    }

    if ($msg = valid_mb_hp($mb_hp)) {
        return array('ok' => false, 'message' => $msg);
    }

    $md5_cert_no = get_session('ss_cert_no');
    $cert_type = get_session('ss_cert_type');
    $cert_birth = get_session('ss_cert_birth');
    $cert_hash = get_session('ss_cert_hash');

    if (!$md5_cert_no || !$cert_type || !$cert_birth || !$cert_hash) {
        return array('ok' => false, 'message' => '회원가입을 위해서는 휴대폰 본인확인을 완료해 주셔야 합니다.');
    }

    if ($post_cert_no === '' || $post_cert_no !== $md5_cert_no) {
        return array('ok' => false, 'message' => '회원가입을 위해서는 휴대폰 본인확인을 완료해 주셔야 합니다.');
    }

    if ($cert_type === 'ipin') {
        return array('ok' => false, 'message' => '휴대폰 본인확인으로 인증을 완료해 주셔야 합니다.');
    }

    $expected_hash = md5($mb_name.$cert_type.$cert_birth.$mb_hp.$md5_cert_no);
    if ($cert_hash !== $expected_hash) {
        return array('ok' => false, 'message' => '본인인증된 정보와 입력된 회원정보가 일치하지 않습니다. 다시 시도해 주세요.');
    }

    $dupinfo = get_session('ss_cert_dupinfo');
    if ($dupinfo) {
        $exclude_mb_id = isset($member['mb_id']) ? $member['mb_id'] : '';
        $sql = " select mb_id from {$g5['member_table']} where mb_id <> '".sql_real_escape_string($exclude_mb_id)."' and mb_dupinfo = '".sql_real_escape_string($dupinfo)."' ";
        $row = sql_fetch($sql);
        if (!empty($row['mb_id'])) {
            return array('ok' => false, 'message' => '입력하신 본인확인 정보로 가입된 내역이 존재합니다.');
        }
    }

    $sql_certify = " , mb_hp = '".sql_real_escape_string($mb_hp)."' ";
    $sql_certify .= " , mb_certify = '".sql_real_escape_string($cert_type)."' ";
    $sql_certify .= " , mb_adult = '".sql_real_escape_string(get_session('ss_cert_adult'))."' ";
    $sql_certify .= " , mb_birth = '".sql_real_escape_string($cert_birth)."' ";
    $sql_certify .= " , mb_sex = '".sql_real_escape_string(get_session('ss_cert_sex'))."' ";
    $sql_certify .= " , mb_dupinfo = '".sql_real_escape_string($dupinfo)."' ";

    return array(
        'ok' => true,
        'message' => '',
        'mb_hp' => $mb_hp,
        'cert_type' => $cert_type,
        'cert_no' => $md5_cert_no,
        'sql_certify' => $sql_certify,
    );
}

function macomaex_grant_signup_credit_bonus($mb_id, $log_prefix = 'signup')
{
    $mb_id = trim((string) $mb_id);
    if ($mb_id === '') {
        return array('ok' => false, 'message' => 'empty_member_id');
    }

    $signup_bonus = (defined('_new_member_signup_credit') && (int) _new_member_signup_credit > 0)
        ? (int) _new_member_signup_credit
        : 30;

    if ($signup_bonus <= 0) {
        return array('ok' => true, 'skipped' => true, 'message' => 'bonus_disabled');
    }

    if (!function_exists('credit_ledger_get_latest_row') || !function_exists('credit_lot_register_charge')) {
        return array('ok' => false, 'message' => 'credit_functions_missing');
    }

    $mb_id_sql = sql_real_escape_string($mb_id);
    sql_query("START TRANSACTION");

    $already = sql_fetch("select id from credit_history where uid='{$mb_id_sql}' and tx_type='BONUS' and service_code='SIGNUP_BONUS' limit 1 for update");
    if (!empty($already['id'])) {
        sql_query("COMMIT");
        return array('ok' => true, 'skipped' => true, 'message' => 'already_granted', 'history_id' => (int) $already['id']);
    }

    $ledger_last = credit_ledger_get_latest_row($mb_id, true);
    $current_credit = isset($ledger_last['credit_after']) ? (int) $ledger_last['credit_after'] : 0;
    $signup_bonus_after = $current_credit + $signup_bonus;

    $bonus_memo = addslashes('신규 가입 무료 크레딧 지급');
    $bonus_history_ok = sql_query("insert into credit_history set
        uid='{$mb_id_sql}',
        tx_type='BONUS',
        service_code='SIGNUP_BONUS',
        credit_delta='{$signup_bonus}',
        credit_after='{$signup_bonus_after}',
        order_no=NULL,
        tr_no='',
        memo='{$bonus_memo}',
        created_at=now()", false);

    $bonus_lot_ok = false;
    $bonus_history_id = 0;
    if ($bonus_history_ok) {
        $bonus_history_id = (int) sql_insert_id();
        if ($bonus_history_id > 0) {
            $bonus_lot_order_no = 'BNS_' . $bonus_history_id;
            $bonus_lot = credit_lot_register_charge($mb_id, $bonus_history_id, $bonus_lot_order_no, $signup_bonus);
            $bonus_lot_ok = !empty($bonus_lot['ok']);
        }
    }

    if ($bonus_history_ok && $bonus_lot_ok) {
        sql_query("COMMIT");
        return array('ok' => true, 'skipped' => false, 'history_id' => $bonus_history_id, 'credit_delta' => $signup_bonus);
    }

    sql_query("ROLLBACK");
    error_log('[credit_bonus] '.$log_prefix.' signup bonus rollback mb_id=' . $mb_id . ' history_ok=' . ($bonus_history_ok ? 'Y' : 'N') . ' lot_ok=' . ($bonus_lot_ok ? 'Y' : 'N') . ' history_id=' . $bonus_history_id);

    return array('ok' => false, 'message' => 'bonus_insert_failed', 'history_id' => $bonus_history_id);
}
