<?php
$sub_menu = '810000';
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

if($_POST['cp_end'] < G5_TIME_YMD)
    alert('종료일은 오늘('.G5_TIME_YMD.')이후로 입력해 주십시오.');



if($w == '') {
	for($i=0;$i<$cp_copy;$i++){
		$j = 0;
		do {
			$cp_id = get_newcoupon_id();

			$sql3 = " select count(*) as cnt from {$g5['g5_shop_coupon_table']} where cp_id = '$cp_id' ";
			$row3 = sql_fetch($sql3);

			if(!$row3['cnt'])
				break;
			else {
				if($j > 20)
					die('Coupon ID Error');
			}

			$j++;

		} while(1);

		$sql = " INSERT INTO {$g5['g5_shop_coupon_table']}
					( cp_id, cp_subject, cp_method, cp_target, mb_id, cp_start, cp_end, cp_type, cp_price, cp_trunc, cp_minimum, cp_maximum, cp_datetime )
				VALUES
					( '$cp_id', '$cp_subject', '$cp_method', '$cp_target', '$mb_id', '$cp_start', '$cp_end', '$cp_type', '$cp_price', '$cp_trunc', '$cp_minimum', '$cp_maximum', '".G5_TIME_YMDHIS."' ) ";

		sql_query($sql);
	}
} else if($w == 'u') {
    $sql = " select * from {$g5['g5_shop_coupon_table']} where cp_id = '$cp_id' ";
    $cp = sql_fetch($sql);

    if(!$cp['cp_id'])
        alert('쿠폰정보가 존재하지 않습니다.', './couponlist.php');

    if($_POST['chk_all_mb']) {
        $mb_id = '전체회원';
    }

    $sql = " update {$g5['g5_shop_coupon_table']}
                set cp_subject  = '$cp_subject',
                    cp_method   = '$cp_method',
                    cp_target   = '$cp_target',
                    mb_id       = '$mb_id',
                    cp_start    = '$cp_start',
                    cp_end      = '$cp_end',
                    cp_type     = '$cp_type',
                    cp_price    = '$cp_price',
                    cp_trunc    = '$cp_trunc',
                    cp_maximum  = '$cp_maximum',
                    cp_minimum  = '$cp_minimum'
                where cp_id = '$cp_id' ";
    sql_query($sql);
}

// 쿠폰생성알림 발송
if($w == '' && ($_POST['cp_sms_send'] || $_POST['cp_email_send'])) {
    include_once(G5_LIB_PATH.'/mailer.lib.php');

    $sms_count = 0;
    $arr_send_list = array();
    $sms_messages = array();

    if($_POST['chk_all_mb']) {
        $sql = " select mb_id, mb_name, mb_hp, mb_email, mb_mailling, mb_sms
                    from {$g5['member_table']}
                    where mb_leave_date = ''
                      and mb_intercept_date = ''
                      and ( mb_mailling = '1' or mb_sms = '1' )
                      and mb_id <> '{$config['cf_admin']}' ";
    } else {
        $sql = " select mb_id, mb_name, mb_hp, mb_email, mb_mailling, mb_sms
                    from {$g5['member_table']}
                    where mb_id = '$mb_id' ";
    }

    $result = sql_query($sql);

    for($i=0; $row = sql_fetch_array($result); $i++) {
        $arr_send_list[] = $row;
    }

    $count = count($arr_send_list);

    for($i=0; $i<$count; $i++) {
        if(!$arr_send_list[$i]['mb_id'])
            continue;

        // SMS
        if($config['cf_sms_use'] == 'icode' && $_POST['cp_sms_send'] && $arr_send_list[$i]['mb_hp'] && $arr_send_list[$i]['mb_sms']) {
            $sms_contents = $cp_subject.' 쿠폰이 '.get_text($arr_send_list[$i]['mb_name']).'님께 발행됐습니다. 쿠폰만료 : '.$cp_end.' '.str_replace('http://', '', G5_URL);

            if($sms_contents) {
                $receive_number = preg_replace("/[^0-9]/", "", $arr_send_list[$i]['mb_hp']);   // 수신자번호
                $send_number = preg_replace("/[^0-9]/", "", $default['de_admin_company_tel']); // 발신자번호

                if($receive_number)
                    $sms_messages[] = array('recv' => $receive_number, 'send' => $send_number, 'cont' => $sms_contents);
            }
        }

    }


}

goto_url('./couponlist.php');
