<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/register.lib.php');
include_once(G5_LIB_PATH.'/mailer.lib.php');

if( ! $config['cf_social_login_use'] ){
    alert('소셜 로그인을 사용하지 않습니다.', G5_URL);
}

if( $is_member ){
    alert('이미 회원가입 하였습니다.', G5_URL);
}

$provider_name = social_get_request_provider();
$user_profile = social_session_exists_check();
if( ! $user_profile ){
    alert("소셜로그인을 하신 분만 접근할 수 있습니다.", G5_URL);
}

// 소셜 가입된 내역이 있는지 확인합니다.
$is_exists_social_account = social_before_join_check(G5_URL);

// 소셜 프로필 공통 추출값(이름/휴대폰)입니다.
$social_contact = social_get_profile_contact_data($user_profile);

$email = ($user_profile->emailVerified) ? $user_profile->emailVerified : $user_profile->email;
$email = get_email_address($email);
if( !$email ){
    alert("소셜 계정 이메일을 확인할 수 없습니다.", G5_URL);
}

$mb_id = strtolower(trim((string)$email));
$mb_5 = $mb_id;

// 요청 정책: 자동가입 계정의 기본 id/name/nick은 동일한 이메일 값으로 저장합니다.
$mb_nick = exist_mb_nick_recursive($mb_id);
$mb_name = $mb_id;

$mb_6 = '';
if( !empty($social_contact['name']) ){
    $mb_6 = clean_xss_tags(trim(strip_tags($social_contact['name'])));
}
$mb_6 = $mb_6 ? $mb_6 : $mb_id;
$mb_8 = !empty($social_contact['phone']) ? social_normalize_phone_number($social_contact['phone']) : '';

$mb_password = md5(pack('V*', rand(), rand(), rand(), rand()));
$mb_email_certify = G5_TIME_YMDHIS;

// 메일인증을 사용한다면 인증 전 상태로 저장합니다.
if( defined('G5_SOCIAL_CERTIFY_MAIL') && G5_SOCIAL_CERTIFY_MAIL && $config['cf_use_email_certify'] ){
    $mb_email_certify = '';
}

if ($msg = exist_mb_id($mb_id)) alert($msg, G5_BBS_URL.'/login.php');
if ($msg = exist_mb_nick($mb_nick, $mb_id)) alert($msg, G5_BBS_URL.'/login.php');
if ($msg = exist_mb_email($email, $mb_id)) alert($msg, G5_BBS_URL.'/login.php');

$sql = " insert into {$g5['member_table']}
            set mb_id = '{$mb_id}',
                mb_password = '".get_encrypt_string($mb_password)."',
                mb_name = '{$mb_name}',
                mb_nick = '{$mb_nick}',
                mb_nick_date = '".G5_TIME_YMD."',
                mb_email = '{$email}',
                mb_email_certify = '".$mb_email_certify."',
                mb_today_login = '".G5_TIME_YMDHIS."',
                mb_datetime = '".G5_TIME_YMDHIS."',
                mb_ip = '{$_SERVER['REMOTE_ADDR']}',
                mb_level = '{$config['cf_register_level']}',
                mb_login_ip = '{$_SERVER['REMOTE_ADDR']}',
                mb_mailling = '0',
                mb_sms = '0',
                mb_open = '0',
                mb_open_date = '".G5_TIME_YMD."',
                mb_5 = '".sql_real_escape_string($mb_5)."',
                mb_6 = '".sql_real_escape_string($mb_6)."',
                mb_8 = '".sql_real_escape_string($mb_8)."' ";
$result = sql_query($sql, false);

if( !$result ){
    social_login_session_clear(1);
    alert('소셜 회원 자동가입 중 오류가 발생했습니다. 다시 로그인해 주세요.', G5_BBS_URL.'/login.php');
}

$mb = get_member($mb_id);

// 소셜 프로필 매핑 테이블을 갱신합니다.
if( function_exists('social_login_success_after') ){
    social_login_success_after($mb, '', 'register');
}

// 회원가입 포인트 부여
insert_point($mb_id, $config['cf_register_point'], '회원가입 축하', '@member', $mb_id, '회원가입');

// 기본 가입 크레딧 지급 (일반 회원가입 경로와 동일 정책)
$signup_bonus = (defined('_new_member_signup_credit') && (int)_new_member_signup_credit > 0)
    ? (int)_new_member_signup_credit
    : 30;
if ($signup_bonus > 0 && function_exists('credit_ledger_get_latest_row') && function_exists('credit_lot_register_charge')) {
    sql_query("START TRANSACTION");
    $ledger_last = credit_ledger_get_latest_row($mb_id, true);
    $current_credit = isset($ledger_last['credit_after']) ? (int)$ledger_last['credit_after'] : 0;
    $signup_bonus_after = $current_credit + $signup_bonus;

    $bonus_memo = addslashes('신규 가입 무료 크레딧 지급');
    $bonus_history_ok = sql_query("insert into credit_history set
        uid='{$mb_id}',
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
        $bonus_history_id = (int)sql_insert_id();
        if ($bonus_history_id > 0) {
            $bonus_lot_order_no = 'BNS_' . $bonus_history_id;
            $bonus_lot = credit_lot_register_charge($mb_id, $bonus_history_id, $bonus_lot_order_no, $signup_bonus);
            $bonus_lot_ok = !empty($bonus_lot['ok']);
        }
    }

    if ($bonus_history_ok && $bonus_lot_ok) {
        sql_query("COMMIT");
    } else {
        sql_query("ROLLBACK");
        error_log('[credit_bonus] social signup bonus rollback mb_id=' . $mb_id . ' history_ok=' . ($bonus_history_ok ? 'Y' : 'N') . ' lot_ok=' . ($bonus_lot_ok ? 'Y' : 'N') . ' history_id=' . $bonus_history_id);
    }
}

// 가입 결과 페이지 진입을 위한 세션값을 저장합니다.
set_session('ss_mb_reg', $mb['mb_id']);

// 메일인증이 필요 없으면 즉시 로그인 처리합니다.
if( $mb_email_certify ){
    set_session('ss_mb_id', $mb['mb_id']);
}

goto_url(G5_HTTP_BBS_URL.'/register_result.php');
