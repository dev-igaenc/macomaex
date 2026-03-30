<?php
include_once('./_common.php');

$gw_msg = new NCP_SENS_FT();

$success = 0; // 성공
$fail = 0; // 실패

foreach ($_POST['image_id'] as $key => $val) {
    $res = $gw_msg->delete_image($val);

    if ($res == true) {
        $success++;
    } else {
        $fail++;
    }
}

$return_arr = array("success" => $success, "fail" => $fail);

die(json_encode($return_arr));
