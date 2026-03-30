<?php
$sub_menu = "700200";
include_once('./_common.php');

check_demo();

if ($is_admin != 'super')
    alert('최고관리자만 접근 가능합니다.');

check_admin_token();

$check_keys = array(
'no'
);

foreach( $_REQUEST as $key=>$value ){
    if( empty($value) ) continue;

    if( in_array($key, $check_keys) ) {
        $_REQUEST[$key] = strip_tags(clean_xss_attributes($value));
    }
}

if (!isset($_REQUEST['no'])) {
	ALERT("정상적으로 이용하세요.","/");
}

if ($w == '')
{
    $sql = "";
    sql_query($sql);

    $po_id = sql_insert_id();
}
else if ($w == 'u')
{
    $sql = "";
    sql_query($sql);
}
else if ($w == 'd')
{
    $sql = " delete from g5_membership_payment where no = '{$_REQUEST['no']}' ";
    sql_query($sql);
}

if ($w == 'd')
    goto_url('./payment.list.php?'.$qstr);
else
    goto_url('./payment.list.php?'.$qstr);