<?php
$sub_menu = '810200';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "w");

check_admin_token();

$_POST = array_map('trim', $_POST);

$check_sanitize_keys = array(
'cp_subject',       // 쿠폰이름
'cp_method',        // 쿠폰종류
'cp_target',        // 적용상품
'mb_id',            // 회원아이디
'cp_start',         // 사용시작일
'cp_end',           // 사용종료일
'cp_type',          // 쿠폰타입
'cp_price',         // 할인금액
'cp_type',          // 할인금액타입
'cp_trunc',         // 절사금액
'cp_minimum',       // 최소주문금액
'cp_maximum',       // 최대할인금액
'chk_all_mb'        // 전체회원 체크
);

foreach( $check_sanitize_keys as $key ){
    $$key = $_POST[$key] = isset($_POST[$key]) ? strip_tags(clean_xss_attributes($_POST[$key])) : '';
}

if(!$_POST['cp_subject'])
    alert('쿠폰이름을 입력해 주십시오.');


if(!$_POST['cp_start'] || !$_POST['cp_end'])
    alert('사용 시작일과 종료일을 입력해 주십시오.');

if($_POST['cp_start'] > $_POST['cp_end'])
    alert('사용 시작일은 종료일 이전으로 입력해 주십시오.');



if($w == '') {
	for($i=0;$i<$cp_copy;$i++){
		$j = 0;
		do {
			$cp_id = get_newcoupon_id();

			$sql3 = " select count(*) as cnt from {$g5['g5_shop_coupon_log_table']} where cp_id = '$cp_id' ";
			$row3 = sql_fetch($sql3);

			if(!$row3['cnt'])
				break;
			else {
				if($j > 20)
					die('Coupon ID Error');
			}

			$j++;

		} while(1);

		$sql = " INSERT INTO {$g5['g5_shop_coupon_log_table']}
					( cp_id, mb_id, cp_subject, cp_start, cp_end, cp_type, cp_trunc, cl_datetime )
				VALUES
					( '$cp_id', '$mb_id', '$cp_subject', '$cp_start', '$cp_end', '$cp_type', '$cp_trunc', '".G5_TIME_YMDHIS."' ) ";

		sql_query($sql);
	}
} else if($w == 'u') {
    $sql = " select * from {$g5['g5_shop_coupon_log_table']} where cl_id = '$cl_id' ";
    $cp = sql_fetch($sql);

    if(!$cp['cl_id'])
        alert('쿠폰정보가 존재하지 않습니다.', './couponloglist.php');

    $sql = " update {$g5['g5_shop_coupon_log_table']}
                set cp_subject  = '$cp_subject',
					cp_id       = '$cp_id',
                    mb_id       = '$mb_id',
                    cp_start    = '$cp_start',
                    cp_end      = '$cp_end',
                    cp_type     = '$cp_type',
                    cp_trunc    = '$cp_trunc',
					cl_datetime    = '$cl_datetime'
                where cl_id = '$cl_id' ";
    sql_query($sql);
}


goto_url('./couponloglist.php');