<?php
$sub_menu = '990400';
include_once('./_common.php');
$gw_msg = new NCP_SENS_AT();

check_demo();

auth_check_menu($auth, $sub_menu, 'd');

check_admin_token();

$count = count($_POST['chk']);
if(!$count)
    alert('선택취소 하실 항목을 하나 이상 체크하세요.');

$gw_msg->reserved_canceled($_POST);

goto_url('./nc_alimtalk_reserved_list.php?'.$qstr);
