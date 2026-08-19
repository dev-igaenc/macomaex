<?php
define('G5_CERT_IN_PROG', true);
include_once('./_common.php');

$logout_mb_id = trim((string) get_session('ss_mb_id'));
$logout_login_token = trim((string) get_session('ss_mb_login_token'));
$duplicate_login = !empty($_REQUEST['duplicate_login']);

if(function_exists('social_provider_logout')){
    social_provider_logout();
}

if ($logout_mb_id !== '' && $logout_login_token !== '') {
    $logout_mb_id_sql = sql_real_escape_string($logout_mb_id);
    $logout_login_token_sql = sql_real_escape_string($logout_login_token);
    sql_query(" update {$g5['member_table']} set mb_1 = '' where mb_id = '{$logout_mb_id_sql}' and mb_1 = '{$logout_login_token_sql}' ");
}

// 이호경님 제안 코드
session_unset(); // 모든 세션변수를 언레지스터 시켜줌
session_destroy(); // 세션해제함

// 자동로그인 해제 --------------------------------
set_cookie('ck_mb_id', '', 0);
set_cookie('ck_auto', '', 0);
set_cookie('ck_login_token', '', 0);
// 자동로그인 해제 end --------------------------------

if ($url) {
    if ( substr($url, 0, 2) == '//' )
        $url = 'http:' . $url;

    $p = @parse_url(urldecode($url));
    /*
        // OpenRediect 취약점관련, PHP 5.3 이하버전에서는 parse_url 버그가 있음 ( Safflower 님 제보 ) 아래 url 예제
        // http://localhost/bbs/logout.php?url=http://sir.kr%23@/
    */
    if (preg_match('/^https?:\/\//i', $url) || $p['scheme'] || $p['host']) {
        alert('url에 도메인을 지정할 수 없습니다.', G5_URL);
    }

    if($url == 'shop')
        $link = G5_SHOP_URL;
    else
        $link = $url;
} else if ($bo_table) {
    $link = get_pretty_url($bo_table);
} else {
    $link = G5_URL;
}

run_event('member_logout', $link);

if ($duplicate_login) {
    alert('다른 기기에서 로그인되어 로그아웃되었습니다.', $link);
}

goto_url($link);
?>
