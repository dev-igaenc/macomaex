<?php
$sub_menu = "990500";
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = '알림톡 발송예제';

include_once (G5_ADMIN_PATH.'/admin.head.php');
include_once (G5_ADMIN_PATH.'/gw_alimtalk_admin/include/format_javascript.php');

// 스크립트 & 스타일시트
add_stylesheet('<link rel="stylesheet" href="'.G5_ADMIN_URL.'/gw_alimtalk_admin/css/style.css">', 0);
?>

<div class="local_desc02 local_desc">
    <ol>
        <li>알림톡 발송을 원하는 페이지에 코드를 삽입하여 알림톡을 발송 할 수 있는 예제입니다.</li>
        <li><strong>템플릿은 반드시 네이버 클라우드 플랫폼에 등록이 되어 있어야 합니다.</strong></li>

        <li><strong>#{필드1} ~ #{필드10}</strong> 형식은 템플릿에 동일하게 등록되어 있어야 자동 치환됩니다.</li>
        <li>아래 샘플 예제를 참고하여 원하는 페이지에 코드를 삽입해 주세요.</li>
    </ol>
</div>

<p class="highlight">
<?php
highlight_string('
<?php
# 알림톡 일반 발송 예제
$gw_msg = new NCP_SENS_AT();
$gw_msg->templateCode       = "sample"; // 템플릿 코드
$gw_msg->to                 = "010-1111-1111"; // 수신자 휴대폰번호
$gw_msg->user_field1        = "필드1 값"; // #{필드1}
$gw_msg->user_field2        = "필드2 값"; // #{필드2}
$gw_msg->user_field3        = "필드3 값"; // #{필드3}
$gw_msg->user_field4        = "필드4 값"; // #{필드4}
$gw_msg->user_field5        = "필드5 값"; // #{필드5}
$gw_msg->user_field6        = "필드6 값"; // #{필드6}
$gw_msg->user_field7        = "필드7 값"; // #{필드7}
$gw_msg->user_field8        = "필드8 값"; // #{필드8}
$gw_msg->user_field9        = "필드9 값"; // #{필드9}
$gw_msg->user_field10       = "필드10 값"; // #{필드10}
$gw_msg->send();
?>

<?php
# 알림톡 예약 발송 예제
$gw_msg = new NCP_SENS_AT();
$gw_msg->templateCode       = "sample"; // 템플릿 코드
$gw_msg->to                 = "010-1111-1111"; // 수신자 휴대폰번호
$gw_msg->reserveTime	    = "2022-01-01 12:00"; // 예약 발송 일시 (Y-m-d H:i)
$gw_msg->user_field1        = "필드1 값"; // #{필드1}
$gw_msg->user_field2        = "필드2 값"; // #{필드2}
$gw_msg->user_field3        = "필드3 값"; // #{필드3}
$gw_msg->user_field4        = "필드4 값"; // #{필드4}
$gw_msg->user_field5        = "필드5 값"; // #{필드5}
$gw_msg->user_field6        = "필드6 값"; // #{필드6}
$gw_msg->user_field7        = "필드7 값"; // #{필드7}
$gw_msg->user_field8        = "필드8 값"; // #{필드8}
$gw_msg->user_field9        = "필드9 값"; // #{필드9}
$gw_msg->user_field10       = "필드10 값"; // #{필드10}
$gw_msg->send();
?>
');
?>
</p>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
