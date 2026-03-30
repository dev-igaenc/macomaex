<?php
$sub_menu = "999200";
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'r');

$gw_msg = new NCP_SENS_AT();

/*
nc_alimtalk_history_list.php 페이지에서 넘겨 받는 변수 값
g5_nc_alimtalk_history 테이블 참조

$nc_id			= 고유 번호 auto_increment
$nc_request_id	= 발송 요청 아이디 필드 requestId
$nc_message_id	= 발송 메시지 아이디 필드 messageId
*/

$res	= $gw_msg->get_request($_REQUEST['nc_request_id']);
$res	= json_decode($res, true);

$return = $res['messages'][0];
$return['statusName'] = $res['statusName'];

$sql = " select * from {$g5['nc_alimtalk_history']} where nc_request_id = '{$_REQUEST['nc_request_id']}' " ;
$row = sql_fetch($sql);

$return['templateCode'] = $row['nc_template_code']; // 템플릿 코드
$return['to'] = $row['nc_to']; // 수신번호

// 예약상태
switch ($row['nc_reserved']){
	case '1':
		$return['statusName'] = "reserved";
		$return['messageStatusName'] = "";
		$return['completeTime'] = $row['nc_reserved_datetime'];
		break;
	case '2':
		$return['statusName'] = "success";
		$return['messageStatusName'] = "success";
		break;
	case '3':
		$return['statusName'] = "canceled";
		$return['messageStatusName'] = "";
		$return['completeTime'] = "-";
		break;
}

// 요청상태 한글로 변환
switch ($return['statusName']){
	case 'success':
		$return['statusName_ko'] = "<span class='request_state_box'><i class='request_state success'></i>성공</span>";
		break;
	case 'processing':
		$return['statusName_ko'] = "<span class='request_state_box'><i class='request_state processing'></i>발송중</span>";
		break;
	case 'reserved':
		$return['statusName_ko'] = "<span class='request_state_box'><i class='request_state reserved'></i>예약중</span>";
		break;
	case 'fail':
		$return['statusName_ko'] = "<span class='request_state_box'><i class='request_state fail'></i>실패</br>(".$return['requestStatusDesc'].")</span>";
		break;
	case 'canceled': // 임의의 상태값을 추가 함
		$return['statusName_ko'] = "<span class='request_state_box'><i class='request_state canceled'></i>취소</span>";
		break;
}

// 발송상태 한글로 변환 (발송 후 요청 단계에서 실패라면 요청 상태를 출력함.)
if ($return['messageStatusName']) {
    switch ($return['messageStatusName']){
        case 'success':
            $return['messageStatusName_ko'] = "<span class='request_state_box'><i class='request_state success'></i>성공</span>";
            break;
        case 'fail':
            $return['messageStatusName_ko'] = "<span class='request_state_box'><i class='request_state fail'></i>실패</br>(".$return['messageStatusDesc'].")</span>";
            break;
        case 'processing':
            $return['messageStatusName_ko'] = "<span class='request_state_box'><i class='request_state processing'></i>처리중</span>";
            break;
        default:
            $return['messageStatusName_ko'] = "-";
    }
} else {
    switch ($return['requestStatusName']){
        case 'success':
            $return['messageStatusName_ko'] = "<span class='request_state_box'><i class='request_state success'></i>성공</span>";
            break;
        case 'fail':
            $return['messageStatusName_ko'] = "<span class='request_state_box'><i class='request_state fail'></i>실패</br>(".$return['requestStatusDesc'].")</span>";
            break;
        case 'processing':
            $return['messageStatusName_ko'] = "<span class='request_state_box'><i class='request_state processing'></i>처리중</span>";
            break;
        default:
            $return['messageStatusName_ko'] = "-";
    }
}

// 대체문자 발송건 이라면
$return['failoverStatusName_ko'] = "-";
if ($return['useSmsFailover'] == 1) {
    $res	= $gw_msg->get_result($return['messageId']);
    $res	= json_decode($res, true);
    $res    = $res['failover'];

    // 발송상태 한글로 변환
    switch ($res['messageStatusName']){
        case 'success':
            $return['failoverStatusName_ko'] = "<span class='request_state_box'><i class='request_state success'></i>성공</span>";
            break;
        case 'fail':
            $return['failoverStatusName_ko'] = "<span class='request_state_box'><i class='request_state fail'></i>실패</br>(".$return['statusMessage'].")</span>";
            break;
        case 'processing':
            $return['failoverStatusName_ko'] = "<span class='request_state_box'><i class='request_state processing'></i>처리중</span>";
            break;
    }
}

// 대체문자 발송 여부
switch ($return['useSmsFailover']){
    case '1':
        $return['useSmsFailover'] = "<span class='request_state_box'><i class='request_state success'></i>사용</span>";
        break;
    default:
        $return['useSmsFailover'] = "<span class='request_state_box'><i class='request_state fail'></i>미사용</span>";
        break;
}

$return['requestTime']	= $row['nc_datetime']; // 요청일시 가공
$return['completeTime']	= date('Y-m-d H:i:s', strtotime($return['completeTime'])); // 완료일시 가공

echo json_encode($return);
