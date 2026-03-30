<?php
include_once('./_common.php');

$gw_msg = new NCP_SENS_FT();

$res = $gw_msg->images_upload($_FILES['image_file'], $_POST['wide']);

die(json_encode($res));

