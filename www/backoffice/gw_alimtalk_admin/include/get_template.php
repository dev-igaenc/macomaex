<?php
include_once('./_common.php');

$gw_msg = new NCP_SENS_AT();

$channelId = '';
$templateCode = $_POST['templateCode'];
$template_content = '';
$field_arr = array();

if (!$templateCode) {
    $msg = "현재 네이버 클라우드 플랫폼과의 통신이 원할하지 않습니다.\n\n잠시 후 다시 시도해주세요.";
}

// 템플릿을 선택하면 해당 템플릿을 가져옴.
if ($templateCode) {
    $template = $gw_msg->get_templates($templateCode);
    $template = $template[0];

    $templateCode = $template['templateCode']; // 템플릿 내용
    $template_content = $template['content']; // 템플릿 내용
    $channelId = $template['channelId']; // 카카오채널

    // 템플릿 내용이 있다면 치환 변수 체크
    if ($template['content']) {
        for ($i=1; $i<=10; $i++) {
            if (strpos($template['content'], '{필드'.$i.'}')) {
                $field_arr[$i] = '#{필드'.$i.'}';
            }
        }
    } else {
        $template_content = "현재 네이버 클라우드 플랫폼과의 통신이 원할하지 않습니다.\n\n잠시 후 다시 시도해주세요.";
    }
}

die(json_encode(array('error'=>$msg, 'channelId'=>$channelId, 'templateCode'=>$templateCode, 'template_content'=>$template_content, 'field_arr'=>$field_arr)));


