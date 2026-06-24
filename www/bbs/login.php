<?php
include_once('./_common.php');

if( function_exists('social_check_login_before') ){
    $social_login_html = social_check_login_before();
}
$refer = isset($_SERVER['HTTP_REFERER']) ? (string)$_SERVER['HTTP_REFERER'] : '';
$board['bo_10']="10";
$board['bo_9']="1";
$board['bo_5'] = "멤버쉽";
$board['bo_subject'] = "로그인";
$board['bo_4'] = $config['cf_title']."의 혜택을 확인하세요.";
$g5['title'] = '로그인';
$hide_theme_svisual = true;
$hide_theme_pagetop = true;
$theme_wrap_inline_style = 'min-height:0;height:auto;display:block;overflow:visible;';
$theme_wrapper_inline_style = 'flex:0 0 auto;';
$theme_container_wr_inline_style = 'height:auto;min-height:0;';
$theme_container_inline_style = 'min-height:0;height:auto;padding-bottom:8rem;';

if(!strpos($refer,"/backoffice/")) include_once(G5_THEME_PATH.'/head.php');

include_once('./_head.sub.php');

$url = isset($_GET['url']) ? strip_tags($_GET['url']) : '';

// url 체크
check_url_host($url);

// 이미 로그인 중이라면
if ($is_member) {
    if ($url)
        goto_url($url);
    else
        goto_url(G5_URL);
}

$login_url        = login_url($url);
$login_action_url = G5_HTTPS_BBS_URL."/login_check.php";

// 로그인 스킨이 없는 경우 관리자 페이지 접속이 안되는 것을 막기 위하여 기본 스킨으로 대체
$login_file = $member_skin_path.'/login.skin.php';
if (!file_exists($login_file))
    $member_skin_path   = G5_SKIN_PATH.'/member/basic';

if(strpos($refer,"/backoffice/"))  include_once($member_skin_path.'/admin_login.skin.php');
else include_once($member_skin_path.'/login.skin.php');
run_event('member_login_tail', $login_url, $login_action_url, $member_skin_path, $url);


if(!strpos($refer,"/backoffice/"))  include_once(G5_THEME_PATH.'/tail.php');

include_once('./_tail.sub.php');
?>
