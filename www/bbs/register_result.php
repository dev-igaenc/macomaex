<?php
include_once('./_common.php');

if (isset($_SESSION['ss_mb_reg']))
    $mb = get_member($_SESSION['ss_mb_reg']);

// 회원정보가 없다면 초기 페이지로 이동

if (!$mb['mb_id'])
    goto_url(G5_URL);

$board['bo_10']="10";
$board['bo_9']="2";
$board['bo_5'] = "멤버쉽";
$board['bo_subject'] = "회원가입 완료";
//$board['bo_4'] = $config['cf_title']."의 혜택을 확인하세요.";
$g5['title'] = '회원가입 완료';
include_once('./_head.php');
include_once($member_skin_path.'/register_result.skin.php');
include_once('./_tail.php');