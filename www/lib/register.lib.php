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
