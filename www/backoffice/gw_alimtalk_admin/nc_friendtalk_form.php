<?php
$sub_menu = "990600";
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = '친구톡 발송';

include_once (G5_ADMIN_PATH.'/admin.head.php');

$colspan = 4;

$channelId = isset($channelId) ? trim((string)$channelId) : ''; // 카카오채널

add_stylesheet('<link rel="stylesheet" href="http://code.jquery.com/ui/1.11.0/themes/smoothness/jquery-ui.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_ADMIN_URL.'/gw_alimtalk_admin/css/jquery-ui-timepicker-addon.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_ADMIN_URL.'/gw_alimtalk_admin/css/style.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_ADMIN_URL.'/gw_alimtalk_admin/css/alimtalk.css">', 0);
add_stylesheet('<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css">', 0);
add_javascript('<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>', 0);
add_javascript('<script src="'.G5_ADMIN_URL.'/gw_alimtalk_admin/js/jquery-ui-timepicker-addon.js"></script>', 0);
add_javascript('<script src="'.G5_ADMIN_URL.'/gw_alimtalk_admin/js/jquery.uniform.min.js"></script>', 0);
?>

<body onload="groupList();">

<section id="anc_cf_basic">

    <div class="local_desc01 local_desc">
        <ol>
            <li>친구톡은 <strong>카카오채널과 친구를 맺은 카카오톡 사용자에게 메시지를 발송하는 기능</strong>이며 카카오의 검수가 필요 없고 이미지 삽입과 광고성 메시지 발송이 가능합니다.</li>
            <li>친구톡 발송은 한/영 구분없이 띄어쓰기 포함 <strong>1,000자 텍스트 + 메시지 하단 링크버튼 최대5개</strong>까지 가능합니다.</li>
            <li>친구톡은 <strong>등록된 발신자(@카카오채널)로만 발송 가능</strong>합니다. 따라서 발신자 등록 후 발송하시기 바랍니다.</li>
            <li>네이버 클라우드 플랫폼(Biz Message)에 Failover SMS 서비스를 연동 한 계정은 친구톡 발송 실패시 자동으로 대체문자가 발송됩니다.</li>
            <li>대체발송은 친구톡 발송실패 시 단문(90Byte), 장문(2000Byte)으로 대체하여 발송하는 기능입니다.</li>
        </ol>
    </div>

    <div class="container">
        <div class="row">
            <!-- 친구톡 보내기 -->

            <!-- section -->
            <div id="section">
                <div class="left">
                    <!-- message_write | 메시지 입력 -->
                    <div class="message_write">
                        <!-- ulTem 프로필 선택 및 만들기 -->
                        <ul class="ulTem clearfix">
                            <li>01. 프로필 선택</li>
                            <li>
                                <div class="upload_g disabled_g">
                                    <div class="box_tf">
                                        <span class="txt_ft channel_select">발신프로필 선택하기</span>
                                    </div>
                                    <div class="upload_btn">
                                        <button type="button" class="btn_g" onclick="channel_load()">불러오기</button>
                                    </div>
                                </div>
                            </li>
                        </ul>
                        <!-- //ulTem 프로필 선택 및 만들기 -->

                        <ul class="ulTem clearfix">
                            <li>02. 이미지 선택</li>
                            <li><?php echo help("* 이미지는 사전에 등록 된 이미지만 사용 가능합니다."); ?></li>
                            <li><?php echo help("* 이미지 등록은 '친구톡 이미지목록' 메뉴에서 등록가능 합니다."); ?></li>

                            <li>
                                <div class="col-xs-3">
                                    <div class="image_setting_disable">
                                        <div class="setting_line1"></div>
                                        <div class="setting_line2"></div>
                                        <p class="setting_msg">No Image</p>
                                    </div>
                                    <a class="image_setting bs-tooltip display-none" data-container="body" data-placement="right" data-html="true">
                                        <img class="image_setting_preview" src="">
                                    </a>
                                </div>
                                <!-- ulTem 이미지 선택 및 만들기 -->

                            </li>
                            <li>
                                <div class="upload_btn2">
                                    <button type="button" class="btn_g2" onclick="image_load()">불러오기</button>
                                    <button type="button" class="btn_g2" onclick="image_delete()">이미지 삭제</button>
                                </div>
                            </li>
                        </ul>
                        <!-- //ulTem 이미지 선택 및 만들기 -->

                        <!-- ulReservation 예약발송 날짜 및 시간 선택 -->
                        <ul class="ulReservation">
                            <li>03. 예약발송 날짜 및 시간 선택</li>
                            <li><?php echo help("* 예약발송 완료 후, 취소는 '친구톡 예약취소' 메뉴에서 가능합니다."); ?></li>
                            <li><?php echo help("* 예약 발송의 경우 현재 시간보다 최소 30분 이후로 입력해 주십시오."); ?></li>
                            <li><input type="checkbox" id="check_reserved_use" value="1"><label for="check_reserved_use"> 예약발송</label></li>
                            <li><input type="text" id="reserved_datetime" title="날짜입력" value="" class="frm_input" readonly></li>
                        </ul>
                        <!-- //ulReservation 예약발송 날짜 및 시간 선택 -->

                        <!-- ulAddress 회원등급 선택 -->
                        <ul class="ulAddress clearfix">
                            <li>04. 회원등급 선택</li>
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
                    <!-- message_view | 친구톡 미리보기 -->
                    <div class="message_view">
                        <div class="talkWindow">
                            <p class="talkTit">친구톡 미리보기</p>
                            <div>
                                <ul class="clearfix">
                                    <li id="v_add" name="v_add" class="pName">(광고) <span id="channel_id_text"></span></li>
                                    <li class="liText">
                                        <textarea name="KAKAO_MSG" id="KAKAO_MSG" onkeyup="nopaste()" placeholder="메시지 내용을 입력해주세요."></textarea>
                                    </li>
                                </ul>
                                <p class="pTxtnum">0/1000자</p>
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
                        <li>05. 템플릿 버튼정보</li>
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
                                        <th>제거</th>
                                    </tr>
                                    </thead>
                                    <tbody id="tbl_posts_body">
                                        <tr id="index_msg">
                                            <td colspan="5">버튼링크 발송 시 버튼을 추가해주세요.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <a class="btn btn_02 pull-right add-record" data-added="0">추가</a>
                            <!-- //div_table -->
                        </li>
                    </ul>
                    <!-- //ulLink 링크버튼 만들기 -->

                    <!-- ulVar 개별변수 입력하기 -->
                    <ul class="ulVar clearfix">
                        <li>06. 개별 입력 사항</li>
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
                            <td width="35%" class="text-left" id="name">친구톡</td>
                            <td width="35%" class="text-left" ><b><span id="res_state" class="txt_orange"></span></b></td>
                            <td width="35%" class="text-left" id="now_datetime">2021-04-28 10:27</td>
                            <td width="35%" class="text-left"><span id="msgP" class="txt_orange"></span>건</td>
                        </tr>
                        </tbody>
                    </table>

                    <h3 class="Alert txt_red">친구톡 발송 등록을 완료하셨습니까?</h3>
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

        <!-- 템플릿 불러오기 모달 channel_upload Modal -->
        <div class="basicModal" id="channel_Upload">
            <div class="basicModal_content">
                <h3 class="modal_title">발신프로필 선택하기</h3>
                <!-- basicModal_body -->
                <div class="basicModal_body select_body">
                    <div class="widget-content mgt_10 h500">
                        <div class="tbl_head03 tbl_wrap" id="template_table"></div>
                    </div>

                    <div class="btn_area text_center">
                        <button class="btn btn_02" onclick="tmpcancel()">취소</button>
                        <button class="btn btn_02" onclick="channel_load()">새로고침</button>
                        <button class="btn_submit btn" onclick="channel_select()">확인</button>
                    </div>
                </div>
                <!-- //basicModal_body -->
            </div>
            <!-- //basicModal_content -->
        </div>
        <!-- //템플릿 불러오기 모달 channel_upload Modal -->

        <!-- 이미지 불러오기 모달 image_upload Modal -->
        <div class="basicModal" id="image_Upload">
            <div class="basicModal_content">
                <h3 class="modal_title">이미지 선택하기</h3>
                <!-- basicModal_body -->
                <div class="basicModal_body select_body">
                    <div class="widget-content mgt_10 h500">
                        <div class="tbl_head03 tbl_wrap" id="image_table"></div>
                    </div>

                    <div class="btn_area text_center">
                        <button class="btn btn_02" onclick="image_cancel()">취소</button>
                        <button class="btn btn_02" onclick="image_load()">새로고침</button>
                        <button class="btn_submit btn" onclick="image_select()">확인</button>
                    </div>
                </div>
                <!-- //basicModal_body -->
            </div>
            <!-- //basicModal_content -->
        </div>
        <!-- //이미지 불러오기 모달 image_upload Modal -->

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

<div style="display:none;">
    <table id="sample_table">
        <tr id="">
            <td><span class="sn"></span>.</td>
            <td>
                <select name="button_type[]" id="at_button_type" required>
                    <option value="">선택</option>
                    <option value="WL">웹링크</option>
                    <option value="AL">앱링크</option>
                    <option value="BK">봇키워드</option>
                    <option value="MD">메시지전달</option>
                </select>
            </td>
            <td>
                <span class="at_button_name"></span>
            </td>
            <td>
                <span class="at_button_link"></span>
            </td>
            <td><a id="delete-record" data-id="0"><i class="fas fa-minus-circle ulMinus_btn"></i></a></td>
        </tr>
    </table>
</div>

<script>
var BefoCode = "";
var Lcnt=1;
var msgObj = {messages:[]};
var _json;

var plusFriendId = "";
var imageId = "";
var imageLink = "";
var isWideImage = "";
var isWide = "";

function nopaste() {
    var content_length = $("#KAKAO_MSG").val().length;
    $(".pTxtnum").text(content_length + "/1000자");

    return;
}

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

function channel_load() {
    $.ajax({
        url:"./include/get_channel_list.php",
        method:"POST",
        data:{},
        success:function(data){
            $('#template_table').html(data);
            $("#channel_Upload").css("display", "block");
        }
    });
}

function image_load() {
    if (plusFriendId) {
        $.ajax({
            url:"./include/get_image_list.php",
            method:"POST",
            data:{
                channel_id : plusFriendId
            },
            success:function(data){
                $('#image_table').html(data);
                $("#image_Upload").css("display", "block");
            }
        });
    } else {
        alert("발신프로필을 선택해주세요.");
        channel_load();
    }
}

function image_cancel() {
    $("#image_Upload").css("display", "none");
}

//선택된 이미지 확인
function check_image(obj) {
    var checkbox = document.getElementsByName("chk_image");
    for(var i = 1; i <= checkbox.length; i++) {
        if(i != obj) {
            $("#chk"+i).prop('checked',false);
            $("#chk"+i).uniform();
        }
    }
}

function image_select() {
    var checkbox = document.getElementsByName("chk_image");
    var check_count = 0;

    for(var i = 1; i <= checkbox.length; i++) {
        if($("#chk"+i).is(":checked") == true) {
            check_count++;
            imageId = $("#chk"+i).attr('data-imageid');
            isWide = $("#chk"+i).attr('data-iswide');
            imageLink = $("#chk"+i).val();
            isWideImage = $("#chk"+i).hasClass('wide_check');
        }
    }

    if (check_count == 1) {
        directPreviewImage(imageLink, isWideImage);
        $("#tbl_posts_body").empty();
        $('#tbl_posts_body').append('<tr id="index_msg"><td colspan="5">버튼링크 발송 시 버튼을 추가해주세요.</td></tr>');
        $("#image_Upload").css("display", "none");
    } else if (check_count == 0) {
        alert("이미지를 선택해주세요.");
    } else {
        alert("이미지는 1개만 선택이 가능합니다.");
    }
}

// 직접입력 - 이미지 선택
function directPreviewImage(imgUrl, isWideImage) {
    // URL 이 있을경우 이미지 표시
    //console.log(imgUrl);
    $('.image_setting_preview').removeClass('wide');
    if (imgUrl) {
        $('.image_setting').removeClass('display-none');
        $('.image_setting_disable').addClass('display-none');
        var imageHtml = '<img src="' + imgUrl + '"/>';
        $('.image_setting_preview').prop('src', imgUrl);
        $('.image_setting').attr("data-original-title", imageHtml);
        // 미리보기 이미지 표시

        // 친구톡 메시지 내용 최대 길이 변경
        if (isWideImage) {
            $('.image_setting_preview').addClass('wide');
            if ($('#direct_btn_table > tbody > tr').length > 2) {
                $('#direct_btn_table > tbody > tr').slice(2).remove();
            }
            $('#direct_btn_plus').attr("disabled", true);
        } else {
            $('#direct_btn_plus').attr("disabled", false);
        }
    }
    // URL 이 없을 경우 X BOX 표시
    else {
        $('.image_setting').addClass('display-none');
        $('.image_setting_disable').removeClass('display-none');
    }
};

// 직접입력 - 이미지 삭제 버튼
function image_delete() {
    imageId = "";
    imageLink = "";
    isWideImage = "";
    isWide = "";

    $('#receiver_image').addClass('display-none');
    $('.image_setting').addClass('display-none');
    $('.image_setting_disable').removeClass('display-none');
    $('.image_setting_preview').prop('src', '');
    // 미리보기 이미지 제거
    $('#receiver_image').prop('src', '');
}

function tmpcancel() {
    $("#channel_Upload").css("display", "none");
}

function md_send_cancel() {
    $("#md_send").css("display", "none");
}

function listck(channel) {
    BefoCode.bgColor = "#ffffff";
    channel.bgColor = "#FFE400";
    BefoCode = channel;
}

function channel_select() {
    plusFriendId = BefoCode.id

    if (plusFriendId == undefined) {
        alert("발신프로필을 선택해주세요.");
        return false;
    }

    $(".channel_select").text(plusFriendId);
    $("#channel_id_text").text(plusFriendId);
    $("#channel_Upload").css("display", "none");
}

//---json전송 ajax-----------단체발송
function readySend(){
    if(!plusFriendId) {
        alert("발신프로필을 선택해주세요.");
        channel_load();
        return;
    }

    if ($('#sendlist_table').length < 1) {
        alert("발송 목록이 없습니다.\n회원그룹을 선택하거나 개별 발송 목록을 입력하세요.");
        return;
    }

    if($("#KAKAO_MSG").val().length < 1) {
        alert("친구톡 발송내용을 입력하세요.");
        return;
    }

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

//############ kakao친구톡전송하기
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
        url : "./include/send_friendtalk_msg.php",
        data : {
            plusFriendId:plusFriendId,
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

    var isAd = "true";
    var countryCode = "82";
    var to = "";
    var content = "";
    var buttons_qty = $('#button_table');
    var sendlist_qty = $('#sendlist_table');
    var image = new Array();

    // 이미지가 있다면 이미지 첨부 발송
    if (imageId && imageLink) {
        image = {"imageId":imageId, "imageLink":imageLink};
    }
    
    //console.log(_json);
    //보낼갯수(Lcnt)만큼 패킷을 만든다.
    $.each(sendlist_qty.find("tr.send_list"), function() {
        to = $(this).find("input[name='hp[]']").val(); // 전화번호
        if (!to) { // 연락처가 있는 행만 전송
            return;
        }

        var buttons = new Array(); // 버튼 배열을 루프마다 재선언(초기화)
        content = $("#KAKAO_MSG").val(); // 발송 내용

        // 버튼이 있다면 버튼의 개수만큼 배열을 생성
        buttons_qty.find("tr.button_list").each(function() {
            var type = $(this).find("select[name='button_type[]']").val();
            var name = $(this).find("input[name='button_name[]']").val();
            var linkMobile = $(this).find("input[name='button_linkMobile[]']").val();
            var linkPc = $(this).find("input[name='button_linkPc[]']").val();
            var schemeAndroid = $(this).find("input[name='button_schemeAndroid[]']").val();
            var schemeIos = $(this).find("input[name='button_schemeIos[]']").val();
            buttons.push( {"type":type, "name":name, "linkMobile":linkMobile, "linkPc":linkPc, "schemeAndroid":schemeAndroid, "schemeIos":schemeIos} );
        });
        addjson(isAd, countryCode, to, content, buttons, image);
    });


    //jsonData = JSON.stringify(msgObj);
    //console.log("데이타:"+jsonData);
}

//-------json 구조 만들기
function addjson(isAd, countryCode, to, content, buttons, image){
    var qmsg = new Object();
    qmsg.isAd = isAd;
    qmsg.countryCode = countryCode;
    qmsg.to= to;
    qmsg.content = content;
    qmsg.buttons = buttons;
    qmsg.image = image;
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
        //console.log(elm+"cn:"+Lcnt);
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

$(document).delegate('a.add-record', 'click', function(e) {
    e.preventDefault();

    $('#index_msg').remove(); // 제일 처음 문구가 있는 tr 삭제
    var id = $('#tbl_posts_body >tr').length + 1; // 추가시 5개 이상은 할 수 없도록 체크

    if (isWide == true && id > 1) {
        alert("와이드 이미지 발송시 최대 1개까지 등록 가능합니다.");
        return false;
    }

    if (id > 5) {
        alert("버튼은 최대 5개까지 등록 가능합니다.");
        return false;
    }

    var content = $('#sample_table tr'),
        size = $('#tbl_posts_body >tr').length + 1,

        element = null,
        element = content.clone();
    element.attr('id', 'rec-'+size);
    element.attr('class', 'button_list');
    element.find('#delete-record').attr('data-id', size);
    element.appendTo('#tbl_posts_body');
    element.find('.sn').html(size);

});

$(document).delegate('#delete-record', 'click', function(e) {
    e.preventDefault();

    var id = $(this).attr('data-id');
    var targetDiv = $(this).attr('targetDiv');
    $('#rec-' + id).remove();

    //regnerate index number on table
    $('#tbl_posts_body tr').each(function(index) {
        $(this).find('span.sn').html(index+1);
    });

    // 마지막 tr까지 삭제되었다면 제일 처음 문구가 있는 tr 추가
    var tr_cnt = $('#tbl_posts_body tr').length;

    if (tr_cnt == 0) {
        $('#tbl_posts_body').append('<tr id="index_msg"><td colspan="5">버튼링크 발송 시 버튼을 추가해주세요.</td></tr>');
    }
    return true;

});

$(document).on('change', '#at_button_type', function() { // 버튼타입선택
    var button_type = $(this).val();

    var $tr = $(this).closest('tr');
    switch (button_type) {
        /* 친구톡에서는 채널추가 버튼을 사용할 수 없음.
        기본적으로 친구톡은 해당 채널을 추가한 사용자에게만 발송되는 것이기 때문에, 채널 추가라는 버튼은 사실상 의미가 없음.
        그래서 카카오의 스펙에서도 제외되어 있음.
        case 'AC':
            $tr.find('.at_button_link').html('<p>* 버튼 명은 \'채널 추가\'로 고정되어 발송됩니다.</p>');
            $tr.find('.at_button_name').html('<input type="hidden" name="button_name[]" class="frm_input" size="20" value="채널 추가">');
            break;
        */
        case 'WL':
            /*alert("현재 카카오 정책상 친구톡 메시지는 PC에서 메시지내용이 숨김 처리되어 PC버전으로 버튼이 보이지 않습니다.\n\n향후 카카오 정책 변경으로 인한(미정) 템플릿 수정의 번거로움을 덜기 위하여 PC 버튼링크를 선택적으로 입력이 가능합니다.");*/
            $tr.find('.at_button_link').html('<div class="rec_form"><label>Mobile</label><input type="text" name="button_linkMobile[]" required class="required frm_input" value="http://" maxlength="150"></div><div class="rec_form"><label>PC(선택)</label><input type="text" name="button_linkPc[]" class="frm_input" maxlength="150"></div>');
            $tr.find('.at_button_name').html('<input type="text" name="button_name[]" class="frm_input" size="20">');
            break;
        case 'AL':
            $tr.find('.at_button_link').html('<div class="rec_form"><label>Android</label><input type="text" name="button_schemeAndroid[]" required class="required frm_input" maxlength="150"></div><div class="rec_form"><label>iOS</label><input type="text" name="button_schemeIos[]" required class="required frm_input" maxlength="150"></div>');
            $tr.find('.at_button_name').html('<input type="text" name="button_name[]" class="frm_input" size="20">');
            break;
        default:
            $tr.find('.at_button_link').html('');
            $tr.find('.at_button_name').html('<input type="text" name="button_name[]" class="frm_input" size="20">');
            break;
    }
});
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
