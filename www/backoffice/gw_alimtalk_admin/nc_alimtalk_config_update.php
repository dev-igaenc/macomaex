<?php
$sub_menu = "999100";
include_once('./_common.php');

check_demo();

auth_check_menu($auth, $sub_menu, 'w');

check_admin_token();

$_POST['nc_biz_msg_admin_hp'] = hyphen_hp_number($_POST['nc_biz_msg_admin_hp']);
$_POST['nc_sms_admin_hp'] = hyphen_hp_number($_POST['nc_sms_admin_hp']);

$sql = " update {$g5['nc_alimtalk_config']}
            set nc_plus_friend_id = '{$_POST['nc_plus_friend_id']}',
                nc_biz_msg_service_id = '{$_POST['nc_biz_msg_service_id']}',
                nc_sub_access_key_id = '{$_POST['nc_sub_access_key_id']}',
                nc_sub_secret_key = '{$_POST['nc_sub_secret_key']}',
                nc_biz_msg_admin_hp = '{$_POST['nc_biz_msg_admin_hp']}',
				nc_sms = '{$_POST['nc_sms']}',
				nc_sms_service_id = '{$_POST['nc_sms_service_id']}',
                nc_sms_admin_hp = '{$_POST['nc_sms_admin_hp']}' ";
sql_query($sql);

goto_url('./nc_alimtalk_config.php', false);
