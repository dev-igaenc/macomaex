<?php
$sub_menu = "500100";
include_once('./_common.php');

$w = $_REQUEST['w'];

if ($w == 'u' || $w == 'd')
    check_demo();

auth_check_menu($auth, $sub_menu, 'w');

check_admin_token();

$check_keys = array(
'no',
'month',
'title',
'title_en',
'price_origin',
'price_sale',
'sale',
'vat',
'pay_type',
'desc',
'open_cnt',
'matching_cnt',
'regdate'
);

foreach( $_POST as $key=>$value ){
    if( empty($value) ) continue;

    if( in_array($key, $check_keys) ) {
        $_POST[$key] = strip_tags(clean_xss_attributes($value));
    }
}

if ($w == '')
{
    $sql = " insert g5_matching  set
					month='{$month}',
					title='{$title}',
					title_en='{$title_en}',
					regdate='".G5_TIME_YMD."'";
    //sql_query($sql);

    $po_id = sql_insert_id();
}
else if ($w == 'u')
{
    $sql = " update g5_matching  set
					month='{$month}',
					title='{$title}',
					title_en='{$title_en}',
					where no = '{$_REQUEST['no']}' ";
    //sql_query($sql);
}
else if ($w == 'd')
{
	$sql = " update g5_matching set del='D' where no = '{$_REQUEST['no']}' ";
    sql_query($sql);
}

if ($w == 'd')
    goto_url('./matching_success.list.php?'.$qstr);
else
    goto_url('./matching_success.list.php?'.$qstr);