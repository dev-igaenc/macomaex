<?php
include_once('./_common.php');

// 로그인중인 경우 회원가입 할 수 없습니다.
if ($is_member) {
    goto_url(G5_URL);
}

// 세션을 지웁니다.
set_session("ss_mb_reg", "");

$board['bo_10']="10";
$board['bo_9']="2";
$board['bo_5'] = "멤버쉽";
$board['bo_subject'] = "회원가입";
//$board['bo_4'] = $config['cf_title']."의 혜택을 확인하세요.";
$g5['title'] = '회원 가입';

include_once('./_head.php');

$register_action_url = G5_BBS_URL.'/register_form_update.php';
include_once($member_skin_path.'/register.skin.php');
include_once('./_tail.php');
