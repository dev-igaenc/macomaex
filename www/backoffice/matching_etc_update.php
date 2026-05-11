<?php
$sub_menu = "500400";
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
	if(!$_REQUEST['no']) alert('제대로 된 값이 넘어오지 않았습니다.');
	$mb_info=sql_query("seletct * from {$g5['member_table']} where mb_no='{$_REQUEST['no']}'");
	//회원삭제
	sql_query("delete from {$g5['member_table']} where mb_no='{$_REQUEST['no']}'");
	//매칭정보 삭제??
	//즐겨찾기 정보 삭제??
	//맴버쉽 결제 정보 삭제??
	//고객 결제 정보 삭제??
	//수동 입력데이터라면 
	if(strpos($mb_info['mb_company'],"M")>-1) {
		//회사정보 삭제 처리 
		sql_query("delete from company where KEDCD='{$mb_info['mb_company']}'");
		//회사신용정보 삭제 처리 
		sql_query("delete from company_credit where KEDCD='{$mb_info['mb_company']}'");
	}
}

if ($w == 'd')
    goto_url('./matching_etc.list.php?'.$qstr);
else
    goto_url('./matching_etc.list.php?'.$qstr);