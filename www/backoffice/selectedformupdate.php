<?php
$sub_menu = '200250';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "w");

check_admin_token();

$_POST = array_map('trim', $_POST);

$check_sanitize_keys = array(
'mb_id',        // 회원아이디
's_subject',        // 회사명
'KEDCD',            // 회사코드
's_membership',         // 이용상품
's_start',           // 이용 시작일
's_end',          // 이용 종료일
's_cnt',         // 셀렉티드 제공 개수
's_cnt_limit',          // 셀렉티드 가능 개수
's_modi',         // 셀렉티드 제공일
's_status',       // 상태 (S:이용중,  E:종료(기간만료), C:취소)
's_datetime' // 셀렉티드 생성일
);

foreach( $check_sanitize_keys as $key ){
    $$key = $_POST[$key] = isset($_POST[$key]) ? strip_tags(clean_xss_attributes($_POST[$key])) : '';
}

if(!$_POST['s_no'])
    alert('사용방법이 올바르지 않습니다.');



if($w == '') {

} else if($w == 'u') {

	$sql = " update g5_selected set ";
	if($s_subject) $sql .= " s_subject  = '$s_subject', ";
	if($KEDCD) $sql .= "KEDCD   = '$KEDCD', ";
	if($s_membership) $sql .= "s_membership   = '$s_membership', ";
	if($mb_id) $sql .= "mb_id       = '$mb_id', ";
	if($s_start) $sql .= "s_start    = '$s_start', ";
	if($s_end) $sql .= "s_end      = '$s_end', ";
	if($s_cnt) $sql .= "s_cnt     = '$s_cnt', ";
	if($s_cnt_limit) $sql .= "s_cnt_limit    = '$s_cnt_limit', ";
	if($s_modi) $sql .= "s_modi    = '$s_modi', ";
	if($s_status) $sql .= "s_status  = '$s_status', ";
	if($s_datetime) $sql .= "s_datetime  = '$s_datetime' ";
	$sql .= "where s_no = '$s_no' ";
	sql_query($sql);

}


goto_url('./selected_list.php');