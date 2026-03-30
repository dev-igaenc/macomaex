<?php
include_once('./_common.php');

$gw_msg = new NCP_SENS_AT();

// 100건씩 맥스 전송 수
$array_count = 100;
$data_array = array_chunk($_POST['messages'], $array_count);

// 100건씩 나누어 보낸다.
for ($i=0; $i<count($data_array); $i++) {
    $data = array(
        'plusFriendId'      => $_POST['plusFriendId'],
        'templateCode'      => $_POST['templateCode'],
        'messages'          => $data_array[$i],
        'reserveTime'       => $_POST['reserveTime'],
    );

    $res = $gw_msg->multi_send($data);
    unset($data);
}

switch ($res) {
    case true:
        $return_arr = "알림톡 발송 완료";
        break;
    case false:
        $return_arr = "알림톡 발송 실패";
        break;
}

echo json_encode($return_arr);
