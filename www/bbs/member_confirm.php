<?php
include_once('./_common.php');

$requested_target_url = isset($_GET['url']) ? clean_xss_tags($_GET['url']) : '';

if ($is_guest) {
    $login_return_url = isset($_SERVER['REQUEST_URI']) ? (string)$_SERVER['REQUEST_URI'] : (G5_BBS_URL . '/member_confirm.php');
    $login_url = function_exists('macomaex_login_guard_build_login_url')
        ? macomaex_login_guard_build_login_url($login_return_url)
        : (G5_BBS_URL . '/login.php?url=' . urlencode($login_return_url));
    $login_policy_key = '';
    $login_message = '회원 전용 페이지입니다. 로그인 후 이용하실 수 있습니다.';

    if (strpos((string)$requested_target_url, 'register_form.php') !== false) {
        $login_policy_key = 'profile_edit';
        $login_message = macomaex_login_guard_policy_message($login_policy_key);
    } else if (strpos((string)$requested_target_url, 'member_leave.php') !== false) {
        $login_policy_key = 'member_leave';
        $login_message = macomaex_login_guard_policy_message($login_policy_key);
    }

    if (function_exists('login_required_modal')) {
        login_required_modal($login_message, $login_url, $login_policy_key);
    }

    alert('로그인 한 회원만 접근하실 수 있습니다.', G5_BBS_URL.'/login.php');
}

$board['bo_10']="6"; // 1뎁스
$board['bo_9']="4"; // 2뎁스

/*
if ($url)
    $urlencode = urlencode($url);
else
    $urlencode = urlencode($_SERVER[REQUEST_URI]);
*/

$url = $requested_target_url;

//소셜 로그인 한 경우
if( function_exists('social_member_comfirm_redirect') && (! $url || $url === 'register_form.php' || (function_exists('social_is_edit_page') && social_is_edit_page($url) ) ) ){    
    social_member_comfirm_redirect();
}

$g5['title'] = '회원 비밀번호 확인';
include_once('./_head.sub.php');

// url 체크
check_url_host($url, '', G5_URL, true);

if($url){
    $url = preg_replace('#^/\\\{1,}#', '/', $url);

    if( preg_match('#^/{3,}#', $url) ){
        $url = preg_replace('#^/{3,}#', '/', $url);
    }
}


$url = get_text($url);

include_once($member_skin_path.'/member_confirm.skin.php');

include_once('./_tail.sub.php');
