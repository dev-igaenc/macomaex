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

$mb_id_seed = $user_profile->sid ? preg_replace("/[^0-9a-z_]+/i", "", $user_profile->sid) : get_social_convert_id($user_profile->identifier, $provider_name);
$mb_id = exist_mb_id_recursive($mb_id_seed);

$mb_nick_seed = social_relace_nick($user_profile->displayName);
if( !$mb_nick_seed ){
    $tmp = explode('@', $email);
    $mb_nick_seed = isset($tmp[0]) ? $tmp[0] : $mb_id;
}
$mb_nick = exist_mb_nick_recursive($mb_nick_seed);

// 기존 가입 로직과 동일하게 mb_name은 필수이므로 소셜 이름/이메일 기반으로 채웁니다.
$mb_name = '';
if( !empty($social_contact['name']) ){
    $mb_name = clean_xss_tags(trim(strip_tags($social_contact['name'])));
}
if( !$mb_name ){
    $tmp = explode('@', $email);
    $mb_name = isset($tmp[0]) ? $tmp[0] : $mb_nick;
}

$mb_6 = $mb_name;
$mb_8 = !empty($social_contact['phone']) ? trim((string)$social_contact['phone']) : '';

$mb_password = md5(pack('V*', rand(), rand(), rand(), rand()));
$mb_email_certify = G5_TIME_YMDHIS;

// 메일인증을 사용한다면 인증 전 상태로 저장합니다.
if( defined('G5_SOCIAL_CERTIFY_MAIL') && G5_SOCIAL_CERTIFY_MAIL && $config['cf_use_email_certify'] ){
    $mb_email_certify = '';
}

if ($msg = exist_mb_id($mb_id)) alert($msg);
if ($msg = exist_mb_nick($mb_nick, $mb_id)) alert($msg, "", true, true);
if ($msg = exist_mb_email($email, $mb_id)) alert($msg, "", true, true);

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
                mb_6 = '".sql_real_escape_string($mb_6)."',
                mb_8 = '".sql_real_escape_string($mb_8)."' ";
$result = sql_query($sql, false);

if( !$result ){
    alert('소셜 회원 자동가입 중 오류가 발생했습니다.', G5_URL);
}

$mb = get_member($mb_id);

// 소셜 프로필 매핑 테이블을 갱신합니다.
if( function_exists('social_login_success_after') ){
    social_login_success_after($mb, '', 'register');
}

// 가입 결과 페이지 진입을 위한 세션값을 저장합니다.
set_session('ss_mb_reg', $mb['mb_id']);

// 메일인증이 필요 없으면 즉시 로그인 처리합니다.
if( $mb_email_certify ){
    set_session('ss_mb_id', $mb['mb_id']);
}

goto_url(G5_HTTP_BBS_URL.'/register_result.php');

