<?php
$sub_menu = "990200";
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = '알림톡 수동발송';

include_once (G5_ADMIN_PATH.'/admin.head.php');

$colspan = 4;

$gw_msg = new NCP_SENS_AT();
$templateCode = isset($templateCode) ? trim((string)$templateCode) : '';
$template_content = '';
$channelId = '';
$field_arr = array();
$template = array('buttons' => array());

// 템플릿을 선택하면 해당 템플릿을 가져옴.
if ($templateCode) {
    $template_res = $gw_msg->get_templates($templateCode);
    if (is_array($template_res) && isset($template_res[0]) && is_array($template_res[0])) {
        $template = $template_res[0];
    }

    $templateCode = isset($template['templateCode']) ? (string)$template['templateCode'] : ''; // 템플릿 코드
    $template_content = isset($template['content']) ? (string)$template['content'] : ''; // 템플릿 내용
    $channelId = isset($template['channelId']) ? (string)$template['channelId'] : ''; // 카카오채널

    // 템플릿 내용이 있다면 치환 변수 체크
    if ($template_content !== '') {
        for ($i=1; $i<=10; $i++) {
            if (strpos($template_content, '{필드'.$i.'}') !== false) {
                $field_arr[$i] = '#{필드'.$i.'}';
            }
        }
    } else {
        $template_content = "현재 네이버 클라우드 플랫폼과의 통신이 원할하지 않습니다.\n\n잠시 후 다시 시도해주세요.";
    }
}

add_stylesheet('<link rel="stylesheet" href="http://code.jquery.com/ui/1.11.0/themes/smoothness/jquery-ui.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_ADMIN_URL.'/gw_alimtalk_admin/css/jquery-ui-timepicker-addon.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_ADMIN_URL.'/gw_alimtalk_admin/css/style.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_ADMIN_URL.'/gw_alimtalk_admin/css/alimtalk.css">', 0);
add_stylesheet('<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css">', 0);
add_javascript('<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>', 0);
add_javascript('<script src="'.G5_ADMIN_URL.'/gw_alimtalk_admin/js/jquery-ui-timepicker-addon.js"></script>', 0);
?>

<body onload="groupList();">

<section id="anc_cf_basic">

    <div class="local_desc01 local_desc">
        <ol>
            <li>알림톡 발송은 한/영 구분없이 띄어쓰기 포함 <strong>1,000자 텍스트 + 메시지 하단 링크버튼 최대5개</strong>까지 가능합니다.</li>
            <li>알림톡은 <strong>등록된 발신자(@카카오채널)로만 발송 가능</strong>합니다. 따라서 발신자 등록 후 발송하시기 바랍니다.</li>
            <li>알림톡은 <strong>승인된 템플릿만 발송 가능</strong>합니다. 따라서 승인된 템플릿을 먼저 선택 후, 발송하시기 바랍니다.</li>
            <li>템플릿은 <strong>네이버 클라우드 플랫폼</strong>에서 무제한으로 등록할 수 있습니다.</li>
            <li>네이버 클라우드 플랫폼(Biz Message)에 Failover SMS 서비스를 연동 한 계정은 알림톡 발송 실패시 자동으로 대체문자가 발송됩니다.</li>
            <li>대체발송은 알림톡 발송실패 시 단문(90Byte), 장문(2000Byte)으로 대체하여 발송하는 기능입니다.</li>
        </ol>
    </div>

    <div class="container">
        <div class="row">
            <!-- 알림톡 보내기 -->

            <!-- section -->
            <div id="section">
                <div class="left">
                    <!-- message_write | 메시지 입력 -->
                    <div class="message_write">
                        <!-- ulTem 프로필 선택 및 만들기 -->
                        <ul class="ulTem clearfix">
                            <li>01. 템플릿 선택</li>
                            <li>
                                <div class="upload_g disabled_g">
                                    <div class="box_tf">
                                        <span class="txt_ft tf_g"><?php echo $templateCode ? $templateCode : "템플릿 불러오기" ?></span>
                                    </div>
                                    <div class="upload_btn">
                                        <button type="button" class="btn_g" onclick="tempload()">불러오기</button>
                                    </div>
                                </div>
                            </li>
                        </ul>
                        <!-- //ulTem 프로필 선택 및 만들기 -->

                        <!-- ulReservation 예약발송 날짜 및 시간 선택 -->
                        <ul class="ulReservation">
                            <li>02. 예약발송 날짜 및 시간 선택</li>
                            <li><?php echo help("* 예약발송 완료 후, 취소는 '알림톡 예약취소' 메뉴에서 가능합니다."); ?></li>
                            <li><?php echo help("* 예약 발송의 경우 현재 시간보다 최소 30분 이후로 입력해 주십시오."); ?></li>
                            <li><input type="checkbox" id="check_reserved_use" value="1"><label for="check_reserved_use"> 예약발송</label></li>
                            <li><input type="text" id="reserved_datetime" title="날짜입력" value="" class="frm_input" readonly></li>
                        </ul>
                        <!-- //ulReservation 예약발송 날짜 및 시간 선택 -->

                        <!-- ulAddress 회원등급 선택 -->
                        <ul class="ulAddress clearfix">
                            <li>03. 회원등급 선택</li>
                            <li class="liAddr">
                                <div class="select" style="">
                                    <select name="direct_btn_type" id="selectAddr">
                                        <option value="" selected>회원등급 선택</option>
                                    </select>
                                    <button type="button" onclick="addrList()" title="선택" class="btn_s">선택하기</button>
                                </div>
                            </li>
                            <li class="add_bal">
                                <ul class="ulbalance">
                                    <li>주소선택(중복제거): <span class="txt_orange mgl_6 selectmsgcount"></span>건</li>
                                </ul>
                            </li>
                        </ul>
                        <!-- //ulAddress 회원등급 선택 -->
                    </div>
                    <!-- //message_write | 메시지 입력폼 -->
                </div>
                <!-- //left -->

                <!-- right -->
                <div class="right">
                    <!-- message_view | 알림톡 미리보기 -->
                    <div class="message_view">
                        <div class="talkWindow">
                            <p class="talkTit">알림톡 미리보기</p>
                            <div>
                                <ul class="clearfix">
                                    <li class="liText">
                                        <textarea name="KAKAO_MSG" id="KAKAO_MSG" onkeydown="nopaste()" placeholder="템플릿을 선택해주세요" readonly><?php echo $template_content ?></textarea>
                                    </li>
                                </ul>
                                <p class="pTxtnum">
                                    <script>
                                        $(".pTxtnum").text(($("#KAKAO_MSG").val().length) + "/1000자");
                                    </script>
                                </p>
                            </div>

                            <p class="talkBtn div_button">
                                <a style="cursor:pointer" id="sendButton" title="발송하기" onclick="readySend()">발송하기</a>
                            </p>
                        </div>

                    </div>
                    <!-- //message_view | 메시지 미리보기 -->
                </div>
                <!-- //right -->

                <div class="clearfix"></div>

                <!-- message_add -->
                <div class="message_add">
                    <!-- ulLink 링크버튼 만들기 -->
                    <ul class="ulLink clearfix">
                        <li>04. 템플릿 버튼정보</li>
                        <li>
                            <div class="tbl_head01 tbl_wrap" id="button_table">
                                <table summary="템플릿 버튼정보" class="table">
                                    <colgroup>
                                        <col width="80px">
                                        <col>
                                        <col>
                                        <col>
                                    </colgroup>
                                    <thead>
                                    <tr>
                                        <th class="w80">NO.</th>
                                        <th>버튼타입</th>
                                        <th>버튼명</th>
                                        <th>버튼링크</th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    <?php
                                    for ($i=0; $i<count($template['buttons']); $i++) {
                                        $num = $i+1;

                                        $btn_type = '';
                                        switch ($template['buttons'][$i]['type']){
                                            case 'DS':
                                                $btn_type = "배송조회";
                                                break;
                                            case 'WL':
                                                $btn_type = "웹링크";
                                                break;
                                            case 'AL':
                                                $btn_type = "앱링크";
                                                break;
                                            case 'BK':
                                                $btn_type = "봇키워드";
                                                break;
                                            case 'MD':
                                                $btn_type = "메시지전달";
                                                break;
                                            case 'BC':
                                                $btn_type = "상담톡전환";
                                                break;
                                            case 'BT':
                                                $btn_type = "봇전환";
                                                break;
                                            case 'AC':
                                                $btn_type = "채널추가";
                                                break;
                                        }
                                    ?>
                                        <!--버튼List-->
                                        <tr class="button_list">
                                            <td>
                                                <?php echo $num; ?>
                                                <input type="hidden" name="button_type[]" value="<?php echo $template['buttons'][$i]['type']; ?>">
                                                <input type="hidden" name="button_name[]" value="<?php echo $template['buttons'][$i]['name']; ?>">
                                                <input type="hidden" name="button_linkMobile[]" value="<?php echo $template['buttons'][$i]['linkMobile']; ?>">
                                                <input type="hidden" name="button_linkPc[]" value="<?php echo $template['buttons'][$i]['linkPc']; ?>">
                                                <input type="hidden" name="button_schemeAndroid[]" value="<?php echo $template['buttons'][$i]['schemeAndroid']; ?>">
                                                <input type="hidden" name="button_schemeIos[]" value="<?php echo $template['buttons'][$i]['schemeIos']; ?>">
                                            </td>
                                            <td><?php echo $btn_type; ?></td>
                                            <td><?php echo $template['buttons'][$i]['name']; ?></td>
                                            <td>
                                                <?php
                                                switch ($template['buttons'][$i]['type']) {
                                                    case 'WL':
                                                        echo '<div>'.$template['buttons'][$i]['linkMobile'].'</div>';
                                                        echo '<div>'.$template['buttons'][$i]['linkPc'].'</div>';
                                                        break;
                                                    case 'AL':
                                                        echo '<div>'.$template['buttons'][$i]['schemeAndroid'].'</div>';
                                                        echo '<div>'.$template['buttons'][$i]['schemeIos'].'</div>';
                                                        break;
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <!--//버튼List-->
                                    <?php
                                    }
                                    if ($i == 0)
                                        echo "<tr><td colspan=\"".$colspan."\" class=\"empty_table\">등록된 템플릿이 버튼이 없습니다.</td></tr>";
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                            <!-- //div_table -->
                        </li>
                    </ul>
                    <!-- //ulLink 링크버튼 만들기 -->

                    <!-- ulVar 개별변수 입력하기 -->
                    <ul class="ulVar clearfix">
                        <li>05. 개별 입력 사항</li>
                        <li>
                            <div class="tbl_head01 tbl_wrap" id="sendlist_table">
                                <table summary="개별 변수 입력하기" class="table">
                                    <thead>
                                    <tr>
                                        <th class="checkbox-column w80">
                                            <div class="checker" id="uniform-each_check_all">
                                                <span>
                                                  <input type="checkbox" class="uniform" id="each_check_all" onclick="gochkall(this)">
                                                </span>
                                            </div>
                                        </th>
                                        <th class="w80">순번</th>
                                        <th>전화번호</th>
                                        <?php
                                        if (!empty($field_arr)) {
                                            foreach ($field_arr as $key => $val) {
                                                echo '<th id ="field_1">'.$field_arr[$key].'</th>';
                                            }
                                        }
                                        ?>
                                        <th class="w80">행추가</th>
                                        <th class="w80">행삭제</th>
                                    </tr>
                                    </thead>
                                    <tbody id="Slist">
                                    <!--list -->
                                    <tr id="sendlist_1" class="send_list">
                                        <td>
                                            <div class="checker">
                                                <span>
                                                  <input type="checkbox" class="uniform each_check" name="chk">
                                                </span>
                                            </div>
                                        </td>
                                        <td class="each_id">1</td>
                                        <td>
                                            <input type="text" class="frm_input" name="hp[]" value="">
                                        </td>
                                        <?php
                                        if (!empty($field_arr)) {
                                            foreach ($field_arr as $key => $val) {
                                                echo '<td><input type="text" class="frm_input" name="field_' . $key . '[]" value=""></td>';
                                            }
                                        }
                                        ?>
                                        <td>
                                            <i class="fas fa-plus-circle ulPlus_btn" id="1" onclick="addsend(this)"></i>
                                        </td>
                                        <td>
                                            <i class="fas fa-minus-circle ulMinus_btn" onclick="removesend(this.parentNode.parentNode.id)"></i>
                                        </td>
                                    </tr>
                                    <!--list//-->
                                    </tbody>
                                </table>
                            </div>
                            <!-- //div_table -->
                        </li>
                        <li>
                            <div class="divBtn">
                                <p class="pBtn">
                                    <a onclick="delsend()" class="btn btn_02">선택삭제</a>
                                </p>
                            </div>
                        </li>
                    </ul>
                    <!-- //ulVar 개별변수 입력하기 -->
                </div>
                <!-- message_add -->

            </div>
            <!-- //section -->
        </div>
        <!-- //row -->

        <!-- 템플릿 불러오기 모달 TemplateUpload Modal -->
        <div class="basicModal" id="template_Upload">
            <div class="basicModal_content">
                <h3 class="modal_title">템플릿 선택하기</h3>
                <!-- basicModal_body -->
                <div class="basicModal_body select_body">
                    <div class="widget-content">
                        <div class="tbl_head03 tbl_wrap" id="template_table"></div>
                    </div>

                    <div class="btn_area text_center">
                        <button class="btn btn_02" onclick="tmpcancel()">취소</button>
                        <button class="btn btn_02" onclick="tempload()">새로고침</button>
                        <button class="btn_submit btn" onclick="tempselect()">확인</button>
                    </div>
                </div>
                <!-- //basicModal_body -->
            </div>
            <!-- //basicModal_content -->
        </div>
        <!-- //템플릿 불러오기 모달 TemplateUpload Modal -->

        <!-- modal | 발송확인 -->
        <div id="md_send" class="md_card">
            <!-- Modal content -->
            <div class="modal_content">
                <div class="modal_top">
                    <span>발송정보 확인</span>
                </div>
                <div class="tbl_head01 tbl_wrap">
                    <table class="tableLeft">
                        <thead>
                        <tr>
                            <th scope="col">발송종류</th>
                            <th scope="col">발송상태</th>
                            <th scope="col">발송시간</th>
                            <th scope="col">발송건수</th>
                        </tr>
                        </thead>

                        <tbody>
                        <tr>
                            <td width="35%" class="text-left" id="name">알림톡</td>
                            <td width="35%" class="text-left" ><b><span id="res_state" class="txt_orange"></span></b></td>
                            <td width="35%" class="text-left" id="now_datetime">2021-04-28 10:27</td>
                            <td width="35%" class="text-left"><span id="msgP" class="txt_orange"></span>건</td>
                        </tr>
                        </tbody>
                    </table>

                    <h3 class="Alert txt_red">알림톡 발송 등록을 완료하셨습니까?</h3>
                    <h4 class="Alert">아래 확인 버튼을 클릭하시면 발송이 시작됩니다.<br>*반드시 발송정보를 확인 하시고 발송 하세요!</h4>
                    <div class="divBtn" style="text-align: right">
                        <p class="pBtn">
                            <a onclick="md_send_cancel()" id="md_send_cancel" class="btn_submit btn">취소</a>
                            <a onclick="send_msg()" id="md_send_ok" class="btn btn_02">확인</a>
                        </p>
                        <input type="hidden" value="" id="uid">
                    </div>
                </div>
                <!-- //div_table -->
            </div>
            <!-- //modal_content -->
        </div>
        <!-- //modal | 발송확인 -->


        <!-- modal | 발송진행상태 -->
        <div id="md_sendIng" class="modal_wrap">
            <div class="modalPopup w800">
                <div class="pop_body mgt_30">
                    <div style="" class="loader_overlay cssload_flipping_square">
                        <div class="loader">
                            <div class="cssload_flipper">
                                <div class="cssload_front"></div>
                                <div class="cssload_back"></div>
                            </div>
                        </div>
                        <div class="loader_txt">
                            <h4 class="Alert">5,000건 이상 발송시 1분이상 걸릴 수 있습니다.</h4>
                            <h4 class="Alert">발송중입니다. 잠시만 기다려주세요.</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- //modal | 발송진행상태 -->

        <!-- modal | 발송완료 -->
        <div id="md_sendFinsh" class="modal_wrap">
            <div class="modalPopup w800">
                <i class="far fa-check-circle pop_far"></i>
                <div class="pop_bottom pdt_10">
                    <div class="loader_txt">
                        <h4 class="Alert">발송완료</h4>
                    </div>
                </div>
            </div>
        </div>
        <!-- //modal | 발송완료 -->

    </div>
    <!-- //Container -->
</section>

<script>
var BefoCode = "";
var Lcnt=1;
var msgObj = {messages:[]};
var _json;

var plusFriendId = "<?php echo $channelId ?>";
var templateCode = "<?php echo $templateCode ?>";



$(document).ready(function(){
    $("input#reserved_datetime").prop("disabled", true);
    $("input#reserved_datetime").css("background","#ccc");

    $("#check_reserved_use").change (function () {
        var st = this.checked;
        if (st) {
            $("input#reserved_datetime").prop("disabled",  false);
            $("input#reserved_datetime").css("background","#fff");
            $("select#check_reserved_use").prop("disabled",  false);
            $("select#check_reserved_use").css("background","#fff");
            $("[name='reserved_datetime']").attr("required", true).addClass("required");
        } else {
            $("input#reserved_datetime").prop("disabled", true);
            $("input#reserved_datetime").css("background","#ccc");
            $("select#check_reserved_use").prop("disabled",  true);
            $("select#check_reserved_use").css("background","#ccc");
            $("input#reserved_datetime").val("");
            $("select#check_reserved_use").val("");
            $("[name='reserved_datetime']").removeAttr("required").removeClass("required");
            $("[name='check_reserved_use']").removeAttr("required").removeClass("required");
        }
    });
});

function nopaste() {
    var content_length = $("#KAKAO_MSG").val().length;
    $(".pTxtnum").text(content_length + "/1000자");

    return;
}

function tempload() {
    $.ajax({
        url:"./include/get_template_list.php",
        method:"POST",
        data:{},
        success:function(data){
            $('#template_table').html(data);
            $("#template_Upload").css("display", "block");
        }
    });
}

function tmpcancel() {
    $("#template_Upload").css("display", "none");
}

function md_send_cancel() {
    $("#md_send").css("display", "none");
}

function listck(tcode) {
    BefoCode.bgColor = "#ffffff";
    //alert (tcode.cells.length);
    tcode.bgColor = "#FFE400";
    BefoCode = tcode;
}

function tempselect_test() {

    if (templateCode == undefined) {
        alert("템플릿을 선택해주세요.");
        return false;
    }

    $.ajax({
        type : "POST",
        dataType : "json",
        url : "./include/get_template.php",
        data : {
            templateCode:templateCode,
        },
        success : function(data) {
            plusFriendId = data.channelId;
            $("#KAKAO_MSG").val(data.template_content);
            nopaste();

            $("#template_Upload").css("display", "none");

            if (data.error) {
                alert(data.error);
            }
        },
    });
}

function tempselect() {
    templateCode = BefoCode.id

    if (templateCode == undefined) {
        alert("템플릿을 선택해주세요.");
        return false;
    }

    location.replace("./nc_alimtalk_form.php?templateCode=" + templateCode);
}

//---json전송 ajax-----------단체발송
function readySend(){
    if($("#KAKAO_MSG").val().length<10) {
        alert("템플릿을 선택을 해주세요!");
        tempload();
        return;
    }

    if ($('#sendlist_table').length < 1) {
        alert("발송 목록이 없습니다.\n회원그룹을 선택하거나 개별 발송 목록을 입력하세요.");
        return;
    }

    /*
    if($('#sendlist_table').length < 1 && !$("#selectAddr option:selected").val()) {
        alert("회원등급선택을 해주세요!");
        return;
    }
    */

    $("#res_state").text("즉시발송");
    if ($("#check_reserved_use").is(":checked")) {
        $("#res_state").text("예약발송");
    }

    $("#md_send").css("display", "block");

    // 발송하기 버튼을 클릭한 현재시간을 넣어줌.
    var today = getTimeStamp() ;
    $("#now_datetime").text(today);

    msgCount();
}

//############ kakao알림톡전송하기
function send_msg() {
    $("#md_send").css("display", "none");

    msgObj = {messages:[]};
    //console.log(msgObj);

    set_messages();

    Lcnt=1;
    var messages = msgObj.messages;
    //console.log(messages);

    // 발송 할 목록이 없다면
    if (messages.length == 0) {
        alert("발송 목록이 없습니다.\n회원그룹을 선택하거나 개별 발송 목록을 입력하세요.");
        return false;
    }

    var resurve_use = ($("#check_reserved_use").is(":checked"))? "1" : "0";

    // 예약 메시지라면 검증 후  시간과 타임존을 넣어줌.
    if (resurve_use == 1) {
        var reserveTime = $("#reserved_datetime").val();
        if (resurve_use == 1 && !reserveTime)
        {
            alert("예약발송시 예약 날짜 및 시간을 선택해야만 합니다.");
            return false;
        }
    }

    $.ajax({
        type : "POST",
        dataType : "json",
        url : "./include/send_alimtalk_msg.php",
        data : {
            plusFriendId:plusFriendId,
            templateCode:templateCode,
            messages : messages,
            reserveTime:reserveTime,
        },
        beforeSend: function() {
            $("#md_sendIng").css("display", "block");
        },
        success : function(data) {
            //alert(JSON.stringify(data));
            $("#md_sendIng").css("display", "none");
            $("#md_sendFinsh").css("display", "block");
            setTimeout( 'location.reload()', 2000);
        },
        error: function(xhr, textStatus, errorThrown,res) {
            alert("전송에 실패했습니다."+xhr+"-"+textStatus+":"+errorThrown + ">" +res);
        }
    });
}

//##############################json만들어 보내자(단체발송)#######################
function set_messages() {

    var countryCode = "82";
    var to = "";
    var content = "";
    var buttons_qty = $('#button_table');
    var sendlist_qty = $('#sendlist_table');

    //console.log(_json);
    //보낼갯수(Lcnt)만큼 패킷을 만든다.
    $.each(sendlist_qty.find("tr.send_list"), function() {
        to = $(this).find("input[name='hp[]']").val(); // 전화번호
        if (!to) { // 연락처가 있는 행만 전송
            return;
        }

        var buttons = new Array(); // 버튼 배열을 루프마다 재선언(초기화)
        content = $("#KAKAO_MSG").val(); // 발송 내용

        // 여분필드 치환 작업
        var field_1 = $(this).find("input[name='field_1[]']").val(); // 필드1
        var field_2 = $(this).find("input[name='field_2[]']").val(); // 필드2
        var field_3 = $(this).find("input[name='field_3[]']").val(); // 필드3
        var field_4 = $(this).find("input[name='field_4[]']").val(); // 필드4
        var field_5 = $(this).find("input[name='field_5[]']").val(); // 필드5
        var field_6 = $(this).find("input[name='field_6[]']").val(); // 필드6
        var field_7 = $(this).find("input[name='field_7[]']").val(); // 필드7
        var field_8 = $(this).find("input[name='field_8[]']").val(); // 필드8
        var field_9 = $(this).find("input[name='field_9[]']").val(); // 필드9
        var field_10 = $(this).find("input[name='field_10[]']").val(); // 필드10

        content = content.replace('#{필드1}', field_1);
        content = content.replace('#{필드2}', field_2);
        content = content.replace('#{필드3}', field_3);
        content = content.replace('#{필드4}', field_4);
        content = content.replace('#{필드5}', field_5);
        content = content.replace('#{필드6}', field_6);
        content = content.replace('#{필드7}', field_7);
        content = content.replace('#{필드8}', field_8);
        content = content.replace('#{필드9}', field_9);
        content = content.replace('#{필드10}', field_10);

        // 버튼이 있다면 버튼의 개수만큼 배열을 생성
        buttons_qty.find("tr.button_list").each(function() {
            var type = $(this).find("input[name='button_type[]']").val();
            var name = $(this).find("input[name='button_name[]']").val();
            var linkMobile = $(this).find("input[name='button_linkMobile[]']").val();
            var linkPc = $(this).find("input[name='button_linkPc[]']").val();
            var schemeAndroid = $(this).find("input[name='button_schemeAndroid[]']").val();
            var schemeIos = $(this).find("input[name='button_schemeIos[]']").val();
            buttons.push( {"type":type, "name":name, "linkMobile":linkMobile, "linkPc":linkPc, "schemeAndroid":schemeAndroid, "schemeIos":schemeIos} );
        });
        addjson(countryCode, to, content, buttons);
    });
    //jsonData = JSON.stringify(msgObj);
    //console.log("데이타:"+jsonData);
}

//-------json 구조 만들기
function addjson(countryCode, to, content, buttons){
    var qmsg = new Object();
    qmsg.countryCode = countryCode;
    qmsg.to= to;
    qmsg.content = content;
    qmsg.buttons = buttons;
    msgObj.messages.push(qmsg);
}

function addsend(elmId){
    var str ="";
    var Tcnt = elmId.id;
    Lcnt++;
    str ="<tr id=\"sendlist_"+Lcnt+"\" class=\"send_list\"><td><div class=\"checker\"><span><input type=\"checkbox\"class=\"uniform each_check\" name=\"chk\"></span></div></td>"+
        "<td class=\"each_id\">"+Lcnt+"</td><td><input type=\"text\"class=\"frm_input\" name=\"hp[]\" value=\"\"></td>"+
        <?php
        if (!empty($field_arr)) {
            foreach ($field_arr as $key => $val) {
        ?>
            "<td><input type=\"text\" class=\"frm_input\" name=\"field_<?php echo $key; ?>[]\" value=\"\" ></td>"+
        <?php
            }
        }
        ?>
        "<td><i class=\"fas fa-plus-circle ulPlus_btn\" id=\""+Lcnt+"\" onclick=\"addsend(this)\"></i></td><td><i class=\"fas fa-minus-circle ulMinus_btn\" onclick=\"removesend(this.parentNode.parentNode.id)\"></i></td></tr>";
    $("#sendlist_"+Tcnt).after(str);
    for(i=1;i<=Lcnt;i++){$(".each_id:eq("+i+")").text(i+1);}
    var f = Lcnt -2;
    msgCount();
}

function removesend(elmId){
    removeck(elmId);
    for(i=0;i<Lcnt;i++){$(".each_id:eq("+i+")").text(i+1); }
	//for(i=0;i<Lcnt;i++){console.log($(".each_id:eq(1)").text()); }
    msgCount();
}

function removeck(elm){
    //console.log(elm);
    if(elm.length > 1){
        $("#"+elm).remove();
        console.log(elm+"cn:"+Lcnt);
        Lcnt--;
        if(Lcnt<1){
            str ="<tr id=\"sendlist_1\" class=\"send_list\"><td><div class=\"checker\"><span><input type=\"checkbox\"class=\"uniform each_check\" name=\"chk\"></span></div></td>"+
                "<td class=\"each_id\">1</td><td><input type=\"text\"class=\"frm_input\" name=\"hp[]\" value=\"\"></td>"+
                <?php
                if (!empty($field_arr)) {
                    foreach ($field_arr as $key => $val) {
                ?>
                    "<td><input type=\"text\" class=\"frm_input\" name=\"field_<?php echo $key; ?>[]\" value=\"\" ></td>"+
                <?php
                    }
                }
                ?>
                "<td><i class=\"fas fa-plus-circle ulPlus_btn\" id=\"1\" onclick=\"addsend(this)\"></i></td><td><i class=\"fas fa-minus-circle ulMinus_btn\" onclick=\"removesend(this.parentNode.parentNode.id)\"></i></td></tr>";
            $("#Slist").html(str);Lcnt++;
            msgCount();
        }
        return 1;
    }
    return 0;

}

//##########개별 발송 전체선택(체크처리함)
function gochkall(obj) {
    $("[name=chk]").each(function() {
        this.checked = obj.checked;
    })
}

//##########개별 발송 선택삭제
function delsend() {
    $("[name=chk]").each(function() {
        if(this.checked){
            //console.log( this.id);
            removeck(this.parentNode.parentNode.parentNode.parentNode.id);
            $("input:checkbox[id='each_check_all']").prop("checked", false); // 전체 체크박스 해제

            for(i=0;i<Lcnt;i++){$(".each_id:eq("+i+")").text(i+1); }
            msgCount();
        }
    });
}

$(document).ready(function() {
    $("#clickBtn_1").click(function() {
        $("#clickBtn_2").css({"background-color": "#e9e9e9", "border-bottom": "1px solid #aeaeae"});
        $("#clickBtn_1").css({"background-color": "#fff", "border-bottom": "1px solid #fff"});
        $("#ulExcel").show();
    });

    $("#clickBtn_2").click(function() {
        $("#clickBtn_1").css({"background-color": "#e9e9e9", "border-bottom": "1px solid #aeaeae"});
        $("#clickBtn_2").css({"background-color": "#fff", "border-bottom": "1px solid #fff"});
        $("#ulExcel").hide();
    });
});

//##########그룹정보가져오기(addrlist.php)
function groupList()
{
    var bstr="";
    var cnt=0;
    $.ajax({
        type: "POST",
        url: "./include/get_group_list.php",
        cache: false,
        dataType: "json",
        success: function(data) {
            //console.log(res);
            $("#selectAddr").html("<option value=''>회원등급 선택</option>");
            $.each(data, function(key, val) {
                $("#selectAddr").append("<option value='" + val.mb_level + "'>" + val.group_name +" ["+val.count+ "]명</option>");
            })
            getGroup();
        },
        error: function(xhr, textStatus, errorThrown) {
            alert("전송에 실패했습니다. groupList" + errorThrown);
        }
    });
}
//#########그룹option GET변경
function getGroup()
{
    var grt ='';
    if (grt=="") {
        return;
    }
    $("#selectAddr").val(grt).prop("selected",true);
    addrList();
}
//##########주소정보가져오기(addrlist.php)
function addrList()
{
    if(!$("#selectAddr option:selected").val()){
        return 0;
    }
    var str="";
    var cnt=0;
    Lcnt=1;
    $.ajax({
        type: "POST",
        url: "./include/get_member_list.php", //

        data: {
            mb_level: $("#selectAddr option:selected").val(),
        },
        cache: false,
        dataType: "json",
        success: function(res) {
            //console.log(res);
            _json = res;
            $.each(res, function(i, json) {
                str +="<tr id=\"sendlist_"+i+"\" class=\"send_list\"><td><div class=\"checker\"><span><input type=\"checkbox\"class=\"uniform each_check\" name=\"chk\"></span></div></td>"+
                    "<td class=\"each_id\">"+(i+1)+"</td><td><input type=\"text\"class=\"frm_input\" name=\"hp[]\" value=\""+(json.mb_hp)+"\"></td>"+
                    <?php
                    if (!empty($field_arr)) {
                        foreach ($field_arr as $key => $val) {
                    ?>
                        "<td><input type=\"text\" class=\"frm_input\" name=\"field_<?php echo $key; ?>[]\" value=\"\" ></td>"+
                    <?php
                        }
                    }
                    ?>
                    "<td><i class=\"fas fa-plus-circle ulPlus_btn\" id=\""+i+"\" onclick=\"addsend(this)\"></i></td><td><i class=\"fas fa-minus-circle ulMinus_btn\" onclick=\"removesend(this.parentNode.parentNode.id)\"></i></td></tr>";Lcnt++;
            })
            Lcnt=Lcnt-1;
            $("#Slist").html(str);
            msgCount();
        },
        error: function(xhr, textStatus, errorThrown) {
            alert("전송에 실패했습니다. addrList" + errorThrown);
        }
    });
}

function msgCount()
{
    $(".selectmsgcount").text(number_Format(Lcnt));
    $("#msgP").text(number_Format(Lcnt));
}

function txt_add(text) {
    var msg = $("#SNS_MSG").val();
    var spos = $("#SNS_MSG").prop("selectionStart");
    var epos = $("#SNS_MSG").prop("selectionEnd");
    var msg1 = msg.substring(0, spos);
    var msg2 = msg.substring(epos, msg.length);
    var addText = text;
    $("#SNS_MSG").val(msg1 + addText + msg2);
    spos = spos + addText.length;
    $("#SNS_MSG").prop("selectionEnd", spos).focus();
}

$("#selectAddr").change(function(){
    addrList();
});

function number_Format(inputNumber) {
    return inputNumber.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
function unnumber_Format(inputNumber) {
    return inputNumber.replace(/,/gi, "");
}

function getTimeStamp() {
    var d = new Date();
    var s =
        leadingZeros(d.getFullYear(), 4) + '-' +
        leadingZeros(d.getMonth() + 1, 2) + '-' +
        leadingZeros(d.getDate(), 2) + ' ' +
        leadingZeros(d.getHours()) + ':' +
        leadingZeros(d.getMinutes());

    return s;
}

function leadingZeros(n, digits) {
    var zero = '';
    n = n.toString();

    if (n.length < digits) {
        for (i = 0; i < digits - n.length; i++)
            zero += '0';
    }
    return zero + n;
}


var min_Date = new Date();
min_Date.setMinutes(min_Date.getMinutes() + 30);

$("#reserved_datetime").datetimepicker({
    dateFormat: 'yy-mm-dd',
    monthNamesShort: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
    dayNamesMin: ['일', '월', '화', '수', '목', '금', '토'],
    changeMonth: true,
    changeYear: true,
    showMonthAfterYear: true,

    // timepicker 설정
    timeFormat: 'HH:mm',
    controlType: 'select',
    oneLine: true,
    minDateTime: min_Date,
});
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
