<?php
$sub_menu = "999999";
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = '알림톡&친구톡 가이드';

include_once (G5_ADMIN_PATH.'/admin.head.php');

// 스크립트 & 스타일시트
add_stylesheet('<link rel="stylesheet" href="'.G5_ADMIN_URL.'/gw_alimtalk_admin/css/style.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/remodal/remodal.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/remodal/remodal-default-theme.css">', 0);
add_javascript('<script src="'.G5_JS_URL.'/remodal/remodal.js"></script>', 10).PHP_EOL;
?>

<div class="service_wrap">
    <div class="local_desc01 local_desc">
        <p>카카오톡에서 제공하는 <strong>알림톡</strong>과 <strong>친구톡</strong>의 차이에 대해 알려드립니다.</p>
        <p><a href="https://business.kakao.com/info/bizmessage" target="_blank" rel="noopener"><strong>카카오톡 비즈메시지 홈페이지</strong></a>에서 보다 자세한 내용을 확인하실 수 있습니다.</p>
    </div>


    <div class="service_2">
        <div class="svc_ri svc_sms">
            <div class="svc_a">
                <h3>소개서&가이드</h3>
                <p>비즈메시지 운영에 필요한 가이드와 자료를 다운받아 활용할 수 있습니다.</p>
            </div>
            <div class="svc_btn3">
                <a href="https://kakaobusiness.gitbook.io/main/ad/bizmessage?x-content-disposition=inline" target="_blank">
                    <span class="file_info">
                        <span class="txt_num">알림톡/친구톡/상담톡 상품소개서 [바로가기]</span>
                    </span>
                </a>
            </div>
        </div>
    </div>

    <div class="compare_wrap">
        <section id="nc_alimtalk_config" class="compare_left">
            <h2 class="h2_frm">1. 정보성 메시지를 발송하는 알림톡</h2>
            <div class="local_desc01 local_desc">
                <ol>
                    <li>알림톡 템플릿이라는 형태로 발송내용에 대해 카카오의 사전 검수를 받아야 합니다.</li>
                    <li>알림톡은 정보성 메시지(한국인터넷진흥원 가이드상 ‘광고성 정보의 예외’ 중 이용자 보호 차원에서 서비스에 적합하다고 보는 일부)에 한해 발송이 가능합니다.</li>
                    <li>비영리조직이 활용할 수 있는 정보성 메시지 사례는 다음과 같습니다: 약정 신청 감사, 후원금 납입 감사, 후원금 미납 안내, 연차보고서 전달, 기부금영수증 발급을 위한 정보 입력 요청, 후원의 밤 등 행사 N일 전 리마인드</li>
                    <li>후원을 요청하는 메시지는 광고성 정보로 판정되어 알림톡을 발송할 수 없습니다. '후원하기', '기부하기' 등의 단어가 포함되어 있으면 알림톡 승인이 거절됩니다.</li>
                    <li>알림톡은 플러스친구와 친구를 맺지 않은 카카오톡 사용자에게도 발송이 가능합니다.</li>
                </ol>
            </div>
            <p class="wysiwyg-text-align-center"><img src="<?php echo G5_ADMIN_URL ?>/gw_alimtalk_admin/img/alimtalk_info.png" alt="alimtalk_info.png"></p>
        </section>

        <section id="nc_alimtalk_config" class="compare_right">
            <h2 class="h2_frm">2. 개인화된 메시지를 발송하는 친구톡</h2>
            <div class="local_desc01 local_desc">
                <ol>
                    <li>친구톡은 플러스친구와 친구를 맺은 카카오톡 사용자에게 메시지를 발송하는 기능이며 카카오의 검수가 필요 없고 이미지 삽입과 광고성 메시지 발송이 가능합니다.</li>
                    <li>친구톡은 친구 전체에게 메시지가 발송되는 일반적인 플러스친구 메시지 발송 방식과 다르게 구성원의 휴대전화번호를 활용해 친구를 맺은 구성원 중 특정 구성원에게만 개인화된 메시지를 발송할 수 있습니다.</li>
                    <li>광고성 메시지를 발송하는 경우에는 정보통신망이용법상 메시지 내용 제일 앞에 (광고)라는 문구를 표기해야 합니다. 미표기 시 최대 3,000만 원의 과태료가 부가될 수 있습니다.</li>
                    <li>비영리조직에서 공익 목적으로 발송하는 메시지는 '광고성 정보의 예외'로 인정 받을 수 있습니다. 구성원과 후원자에게 활동소식을 알리는 메시지 등이 공익 목적으로 발송하는 메시지 입니다. 이 경우에는 (광고) 문구를 표기하지 않아도 괜찮습니다.</li>
                    <li>친구톡은 카카오의 광고성 메시지 발송금지 시간대인 19:30~08:30 사이에 발송이 제한됩니다.</li>
                </ol>
            </div>
            <p class="wysiwyg-text-align-center"><img src="<?php echo G5_ADMIN_URL ?>/gw_alimtalk_admin/img/friendtalk_info.png" alt="alimtalk_info.png"></p>
        </section>
    </div>
</div>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
