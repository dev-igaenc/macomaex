<?php
$sub_menu = '999500';
include_once('./_common.php');

check_demo();

auth_check_menu($auth, $sub_menu, 'd');

check_admin_token();

$count = count($_POST['chk']);
if(!$count)
    alert('선택삭제 하실 항목을 하나 이상 체크하세요.');

for ($i=0; $i<$count; $i++)
{
    // 실제 번호를 넘김
    $k = $_POST['chk'][$i];

    // 발송 내역정보
    $sql = " select * from {$g5['nc_friendtalk_history']} where nc_id = '{$_POST['nc_id'][$k]}' ";
    $row = sql_fetch($sql);

    if(!$row['nc_id'])
        continue;

    // 발송 내역삭제
    $sql = " delete from {$g5['nc_friendtalk_history']} where nc_id = '{$_POST['nc_id'][$k]}' ";
    sql_query($sql);
}

goto_url('./nc_friendtalk_history_list.php?'.$qstr);
