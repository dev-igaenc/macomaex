<?php
$sub_menu = "999200";
include_once('./_common.php');
/*******************************************************
관리자화면에서 결과보기를 클릭했을때 결과를 받아오는 Modal 입니다.
********************************************************/

auth_check_menu($auth, $sub_menu, 'r');

$message_id = trim($_POST['message_id']);

if (!$message_id) {
	alert('잘못된 요청! message_id 값이 없습니다.');
}

$gw_msg = new NCP_SENS_AT();

$res = $gw_msg->get_result($message_id);
$res = json_decode($res, true);

$tmp_str = '';
foreach($res as $key => $val ){

    // api의 Sms Service Id 값이 노출되는 것을 우려하여 해당 부분 출력 무시.
    if ($key == 'failoverSmsServiceId') continue;

    $tmp_str .= "<ul><li>{$key}</li><li>{$val}</li></ul>";

    /*
    // api의 sms키 값이 노출되는 것을 우려하여 주석처리. (추후 결과값을 보고 싶다면 오픈 가능)
    if ($key == 'failover' && $res['failover']) {
        foreach($res['failover'] as $key2 => $val2 ){
            $tmp_str .= "<ul><li>{$key2}</li><li>{$val2}</li></ul>";
        }
    }
    */
}

$res['html'] = $tmp_str;

die ( json_encode($res) );
