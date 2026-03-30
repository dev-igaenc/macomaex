<?php
$sub_menu = "700400";
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
    $sql = " insert g5_membership_manage  set
					month='{$month}',
					title='{$title}',
					title_en='{$title_en}',
					price_origin='{$price_origin}',
					price_sale='{$price_sale}',
					sale='{$sale}',
					vat='{$vat}',
					pay_type='{$pay_type}',
					`desc`='{$desc}',
					open_cnt='{$open_cnt}',
					matching_cnt='{$matching_cnt}',
					selected_cnt='{$selected_cnt}',
					regdate='".G5_TIME_YMD."'";
    sql_query($sql);

    $po_id = sql_insert_id();
}
else if ($w == 'u')
{
    $sql = " update g5_membership_manage  set
					month='{$month}',
					title='{$title}',
					title_en='{$title_en}',
					price_origin='{$price_origin}',
					price_sale='{$price_sale}',
					sale='{$sale}',
					vat='{$vat}',
					pay_type='{$pay_type}',
					`desc`='{$desc}',
					open_cnt='{$open_cnt}',
					matching_cnt='{$matching_cnt}',
					selected_cnt='{$selected_cnt}',
					regdate='".G5_TIME_YMD."'
					where no = '{$_REQUEST['no']}' ";
    sql_query($sql);
}
else if ($w == 'd')
{
    $sql = " delete from g5_membership_manage where no = '{$_REQUEST['no']}' ";
    sql_query($sql);
}

if ($w == 'd')
    goto_url('./membership_list.php?'.$qstr);
else
    goto_url('./membership_list.php?'.$qstr);