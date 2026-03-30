<?php
$sub_menu = "999700";
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = '친구톡 설정';

include_once (G5_ADMIN_PATH.'/admin.head.php');

$sql_common = " from {$g5['nc_friendtalk_image']} ";

$sql_search = " where nc_plus_friend_id = '{$nc_config['nc_plus_friend_id']}' ";

$sql_order = " order by nc_id desc ";

$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$colspan = 6;

// 스크립트 & 스타일시트
add_stylesheet('<link rel="stylesheet" href="'.G5_ADMIN_URL.'/gw_alimtalk_admin/css/style.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_ADMIN_URL.'/gw_alimtalk_admin/css/bootstrap.css">', 0);
add_javascript('<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>', 0);
?>

<div class="local_desc01 local_desc">
    <ol>
        <li>친구톡 발송 시 사용될 이미지를 업로드 할 수 있습니다.</li>
        <li>권장 해상도 : 720px * 720px</li>
        <li>허용 해상도 : 가로 크기 500px 이상, 가로:세로 비율이 2:1 (가로가 긴 타입)부터 3:4 (세로가 긴 타입) 비율까지 업로드 가능합니다.</li>
        <li>지원 파일형식 및 크기 : png, jpg, jpeg / 최대 500KB</li>
        <li>와이드형 이미지 허용 해상도 : 가로 크기 800px, 세로 크기 600px만 업로드 가능합니다.</li>
        <li>와이드형 이미지 지원 파일형식 및 크기 : png, jpg, jpeg / 최대 2MB</li>
        <li>이미지를 클릭하여 상세 이미지와 이미지URL(친구톡 업로드 발송시 필요)을 확인하세요.</li>
    </ol>
</div>

<div class="local_desc01 local_desc">
    <p>친구톡 이미지 설정을 하는 메뉴입니다.</p>
    <p><strong>주의!</strong> 이미지 업로드 후 반드시 <strong>우측상단의 확인</strong> 버튼을 누르셔야 저장됩니다.</p>
</div>


<section id="nc_alimtalk_config">
    <h2 class="h2_frm">네이버 클라우드 플랫폼 [Biz Message 친구톡 이미지 등록]</h2>
    <div class="local_desc02 local_desc">
        <p>친구톡 발송 시 사용될 이미지를 업로드 할 수 있습니다.</p>
    </div>

    <div class="thumb">
        <ul class="thumblist">
            <?php
            for ($i=0; $row=sql_fetch_array($result); $i++) {
            ?>
            <li style="text-align:center;">
                <div align="center" style="border: 1px solid black; width: 100%; margin-top: 20px; margin-bottom: 10px; height: 270px;" onclick="img_detail(<?php echo $i; ?>);">
                    <img class="relative" align="center" src="<?php echo $row['nc_image_url']; ?>" style="cursor: pointer; height: 268px;">
                    <input type="hidden" id="imageId_<?php echo $i; ?>" value="<?php echo $row['nc_image_id']; ?>">
                    <input type="hidden" id="imageUrl_<?php echo $i; ?>" value="<?php echo $row['nc_image_url']; ?>">
                    <input type="hidden" id="imageName" value="<?php echo $row['nc_image_name']; ?>">
                    <input type="hidden" id="isWide" value="<?php echo $row['nc_image_is_wide']; ?>">
                    <input type="hidden" id="createTime" value="<?php echo $row['nc_datetime']; ?>">
                </div>
                <input type="checkbox" class="uniform" name="chk_image[]" value="<?php echo $row['nc_image_id']; ?>" onclick="javascript:check_image('chk_image[]')">
            </li>
            <?php
            }

            if ($i == 0)
                echo "<li class=\"empty_li\">등록된 이미지가 없습니다.</li>";
            ?>
        </ul>
    </div>

    <table class="table table-hover table-striped table-bordered table-highlight-head table-checkable">
        <tbody>

        </tbody>
    </table>

    <div class="mt30" align="left">
        <button class="btn" type="button" onclick="javascript:delete_image()"><i class="icon-trash"></i>선택 삭제</button>
        <button class="btn" type="button" onclick="javascript:click_bntimage()"><i class="icon-file"></i>이미지 업로드</button>
        <button class="btn" type="button" onclick="javascript:click_bntwideimage()"><i class="icon-file"></i>와이드 이미지 업로드</button>

        <input type="file" name="image_file" id="image_file" accept=".png, .jpg, .jpeg" value="upload" style="visibility: hidden" class="upload-hidden" onclick="$('#image_file').val('');" onchange="img_size();">
    </div>

</section>

<style>
#overlay {
    background: rgba(255, 255, 255, .8) 50% 50% no-repeat;
    color: #666666;
    position: fixed;
    height: 100%;
    width: 100%;
    z-index: 5000;
    top: 0;
    left: 0;
    float: left;
    text-align: center;
    padding-top: 25%;
    opacity: 1.0;
}

.mt30 {
    margin-top: 30px !important;
}
</style>

    <div id="overlay" style="display: none">
        <img src="<?php echo G5_ADMIN_URL?>/gw_alimtalk_admin/img/loader.gif" alt="Loading"/><br/>
        Loading...
    </div>

    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" id="modal">
            <div class="modal-content">
                <br/>
                <div class="modal-body">
                    <div class="content identify" style="-ms-word-break: break-all !important; word-break: break-all !important;">
                    </div>
                    <div>
                        <p align="right">
                            <br/><br/>
                            <button type="button" class="btn btn-primary enter" data-dismiss="modal" id="identify">확인</button>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" id="modal2">
            <div class="modal-content">
                <br/>
                <div class="modal-body">
                    <div class="content2">
                    </div>
                    <div>
                        <p align="right">
                            <br/><br/>&nbsp;
                            <button type="button" id="close_btn" class="btn btn-default" data-dismiss="modal">취소
                            </button>
                            <button type="button" id="confirm_btn" class="btn btn-primary" data-dismiss="modal">확인
                            </button>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
//예-아니오에서의 확인버튼
function click_btn_primary() {
    $(document).unbind("keyup").keyup(function (e) {
        var code = e.which;
        if (code == 13) {
            $(".btn-primary").click();
        }
    });
}

$("#myModal").unbind("keyup").keyup(function (e) {
    var code = e.which;
    if (code == 13) {
        $(".enter").click();
    }
});

//이미지 업로드 버튼 클릭
function click_bntimage() {
    $("#image_file").removeClass("wide");
    $("#image_file").trigger("click");
}

function click_bntwideimage() {
    $("#image_file").removeClass("wide");
    $("#image_file").addClass("wide");
    $("#image_file").trigger("click");
}

function img_size(){
    var img_file = $("#image_file")[0].files[0];
    var _URL = window.URL || window.webkitURL;
    var img = new Image();
    img.src = _URL.createObjectURL(img_file);
    img.onload = function() {
        var img_width = img.width;
        var img_height = img.height;
        var img_check = true;
        //console.log(img_width);
        //console.log(img_height);
        var isWideImage = $("#image_file").hasClass('wide');

        if(isWideImage){
            if (img_width != 800 || img_height != 600) { //이미지 사이즈 체크
                img_check = false;
            }
        } else {
            if (img_width < 500 || img_width / img_height < 0.75 || img_width / img_height > 2) { //이미지 사이즈 체크
                img_check = false;
            }
        }

        upload(img_check);
    };
}

//파일 용량 및 확장자 확인 ->500KB제한
function upload(check) {
    var thumbext = document.getElementById('image_file').value;

    var isWideImage = $("#image_file").hasClass('wide');

    var file_data = new FormData();
    file_data.append("image_file", $("input[name=image_file]")[0].files[0]);
    file_data.append("wide", isWideImage ? "true" : "false");

    thumbext = thumbext.slice(thumbext.indexOf(".") + 1).toLowerCase();

    if (thumbext) {
        if ($("#image_file")[0].files[0].size > (isWideImage ? 2097152 : 512000)) //파일 용량 체크 (500KB 제한)
        {
            if (isWideImage) {
                $(".content").html("2MB를 초과하였습니다. 다시 선택해 주세요.");
            } else {
                $(".content").html("500KB를 초과하였습니다. 다시 선택해 주세요.");
            }

            $("#myModal").modal('show');
            $(document).unbind("keyup").keyup(function (e) {
                var code = e.which;
                if (code == 13) {
                    $(".btn-primary").click();
                }
            });
        } else if (check == false){
            if(isWideImage){
                $(".content").html("가로 800px 세로 600px 이미지만 가능합니다.");
            }  else {
                $(".content").html("가로 500px 미만 또는 가로:세로 비율이 2:1 미만 또는 3:4 초과시 업로드 불가합니다.");
            }
            $("#myModal").modal('show');
            $(document).unbind("keyup").keyup(function (e) {
                var code = e.which;
                if (code == 13) {
                    $(".btn-primary").click();
                }
            });
        } else {
            $.ajax({
                url: "./include/image_upload.php",
                type: "POST",
                dataType: "json",
                data: file_data,
                processData: false,
                contentType: false,
                beforeSend: function () {
                    $('#overlay').fadeIn();
                },
                complete: function () {
                    $('#overlay').fadeOut();
                },
                success: function (json) {
                    showResult(json);
                },
                error: function (data, status, er) {
                    $(".content").html("처리중 오류가 발생하였습니다.<br/>관리자에게 문의해주시기 바랍니다.");
                    $("#myModal").modal('show');
                }
            });
            function showResult(json) {
                if (json['code'] == 200) {
                    //window.location.href = './nc_friendtalk_image_list.php';
                    location.reload();
                }
                else {
                    var message = json['message'];
                    var messagekr = '';
                    if (message == 'UnknownException') {
                        messagekr = '관리자에게 문의해주시기 바랍니다.';
                    } else if (message == 'InvalidImageMaxLengthException') {
                        messagekr = '이미지 용량을 초과하였습니다. (최대500KB)';
                    } else if (message == 'InvalidImageSizeException') {
                        messagekr = '이미지 비율이 초과되어 업로드 불가합니다.';
                    } else if (message == 'InvalidImageFormatException') {
                        messagekr = '지원하지 않는 이미지 형식입니다. (png, jpg, jpeg만 가능)';
                    } else if (message == 'NotFoundProfile') {
                        messagekr = '발신 프로필을 먼저 등록해주시기 바랍니다.';
                    } else if (message == 'InvalidImageResolutionException') {
                        messagekr = '800x600 이미지만 가능합니다.';
                    } else {
                        messagekr = '관리자에게 문의해주시기 바랍니다.';
                    }

					messagekr = json['errorMessage'];

                    var text = '이미지 업로드에 실패하였습니다' + '\n' + messagekr;
                    $(".content").html(text.replace(/\n/g, "<br/>"));
                    $("#myModal").modal('show');
                    $(document).unbind("keyup").keyup(function (e) {
                        var code = e.which;
                        if (code == 13) {
                            $(".btn-primary").click();
                        }
                    });
                }
            }

        }
    }
}

//이미지 상세보기
function img_detail(obj) {
    var img_key = $("#imageId_"+obj).val(); //이미지 서버 URL
    var img = $("#imageUrl_"+obj).val(); // 이미지 카카오 URL
    var html = '<div align="center">' +
        '<table style="width:450px;" class="tpl_ver_form">' +
        '<tr>' +
        '<th style="width:90px;">이미지 KEY</th>' +
        '<td style="word-break: break-all;" id="img_key"><strong>' + img_key + '</strong></td>' +
        '<td style="width:90px;">' +
        '   <button style="height: 30px;" class="btn" id="btn_copy_clipboard" onclick="copyToClipboard(\'' + img_key + '\');">복사하기</button>' +
        '</td>' +
        '</tr>' +
        '<tr>' +
        '<th style="width:90px;">이미지 URL</th>' +
        '<td style="word-break: break-all;" id="img_url"><strong>' + img + '</strong></td>' +
        '<td style="width:90px;">' +
        '   <button style="height: 30px;" class="btn" onclick="copyToClipboard(\'' + img + '\');">복사하기</button>' +
        '</td>' +
        '</tr>' +
        '</table>' +
        '<br><br>' +
        '<img align="center" src="' + img + '" style="width:450px !important;"/>' +
        '</div>' +
        '<div id="copyMessage" style="display: none; position:absolute; top:30%; left:30%; ' +
        'width:200px; height:30px; background-color: white; text-align: center; padding-top: 7px; ' +
        'border-radius: 10px; opacity: 0.7;">클립보드에 복사되었습니다.</div>';

    $(".content").html(html);
    $(".modal-dialog").css("width","500px").css("height","450px").css("top","10%");
    $("#myModal").modal({backdrop: 'static'});

    if (img_key === 'N/A') {
        $('#btn_copy_clipboard').attr('disabled', true);
    }
}

//이미지 url 복사 기능
function copyToClipboard(url) {
    var $temp = $("<input id='copy_url'>");
    $('.tpl_ver_form').append($temp);
    $('#copy_url').val(url);
    $('#copy_url').select();
    document.execCommand("copy");
    $('#copy_url').remove();

    setTimeout(function() {
        $("#copyMessage").animate({
            opacity: "show"
        });
    },0);
    setTimeout(function() {
        $("#copyMessage").animate({
            opacity: "hide"
        });
    },1000);
}

//선택된 이미지 이름 확인
function check_image(obj) {
    var sum = 0, tag = [];
    var chk = document.getElementsByName(obj);
    var tot = chk.length;
    for (var i = 0; i < tot; i++) {
        if (chk[i].checked == true) {
            var url = chk[i].value;
            tag[sum] = url;
            sum++;
        }
    }

    return tag
}
var obj_image_id = new Array();

//이미지 삭제
function delete_image() {
    var image_id = check_image("chk_image[]");

    for (var i = 0; i < check_image("chk_image[]").length; i++) {
        obj_image_id.push(image_id[i]);
    }

    if (check_image("chk_image[]").length > 0) {

        $(".content2").html(check_image("chk_image[]").length + "개를 삭제 하시겠습니까?");
        $("#myModal2").modal('show');
        click_btn_primary();
        $("#confirm_btn").click(function () {

            $.ajax({
                url: "./include/image_delete.php",
                type: "POST",
                dataType : "json",
                data: {
                    image_id: obj_image_id,
                    count: check_image("chk_image[]").length
                },
                beforeSend: function () {
                    $('#overlay').fadeIn();
                },
                complete: function () {
                    $('#overlay').fadeOut();
                },
                success: function (json) {
                    showResult(json);
                },
                error: function (data, status, er) {
                    $(".content").html("처리중 오류가 발생하였습니다.<br/>관리자에게 문의해주시기 바랍니다.");
                    $("#myModal").modal('show');
                }
            });

            $('#myModal').on('hidden.bs.modal', function () {
                location.reload();
            });
        });
        
    } else {
        $(".content").html("삭제할 이미지를 선택해주세요.");
        $("#myModal").modal('show');
        click_btn_primary();
    }
}

function showResult(json) {
    var text = '이미지 삭제 요청하였습니다.' + '\n' + '[요청결과] 성공 : ' + json['success'] + '건, ' + '실패 :' + json['fail'] + '건';
    $(".content").html(text.replace(/\n/g, "<br/>"));
    $("#myModal").modal('show');
    $("#myModal").unbind("keyup").keyup(function (e) {
        var code = e.which;
        if (code == 13) {
            $(".btn-primary").click();
        }
    });
    obj_image_id = [];
}
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
