<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>
<style>.cke_sc{display:none !important;}</style>
<style>
#specArea table{width:100%; table-layout:fixed;}
#specArea table tr th{width:auto; height:auto; padding:0.8rem; font-size:1.5rem; border-top:1px solid #e2e2e2}
#specArea table tr th:last-child{border-right:0}
#specArea table tr td{padding:1rem; border-right:1px solid #e2e2e2; height:auto;}
#specArea table tr td:last-child{border-right:0}

#specArea input{padding:1rem; height:auto; line-height:normal; font-size:1.5rem;}
#specArea .btn.specAdd{background-color:#0069a2; color:#fff;}
#specArea .btn.specDel{background-color:#9f0000; color:#fff;}

.disNone{display:none;}
</style>

<script type="text/javascript">
$(document).ready(function(){
	$(document).on("click",".specAdd",function(){
		var cnt = $("#stockTB tbody tr").size()
		var html = $("#stockCopyArea tbody").html()

		$("#stockTB tbody").append(html)
		setOrderBtnSet();
	});

	$(document).on("click",".specDel",function(){
		var cnt = $("#stockTB tbody tr").size()

		if (cnt==1){
			$(this).closest("tr").find("input[type='hidden']").val("");
			$(this).closest("tr").find("input[type='text']").val("");
		}else{
			$(this).closest("tr").remove();
		}
		setOrderBtnSet();
	});
});
</script>

<section id="bo_w">
    <h2 class="sound_only"><?php echo $g5['title'] ?></h2>

    <!-- 게시물 작성/수정 시작 { -->
    <form name="fwrite" id="fwrite" action="<?php echo $action_url ?>" onsubmit="return fwrite_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off" style="width:<?php echo $width; ?>">
    <input type="hidden" name="uid" value="<?php echo get_uniqid(); ?>">
    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <input type="hidden" name="wr_id" value="<?php echo $wr_id ?>">
    <input type="hidden" name="sca" value="<?php echo $sca ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="spt" value="<?php echo $spt ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <?php
    $option = '';
    $option_hidden = '';
    if ($is_notice || $is_html || $is_secret || $is_mail) {
        $option = '';
        if ($is_notice) {
            $option .= PHP_EOL.'<li class="chk_box"><p class="checkIn"><input type="checkbox" id="notice" name="notice"  class="selec_chk" value="1" '.$notice_checked.'>'.PHP_EOL.'<label for="notice">공지</label></p></li>';
        }
        if ($is_html) {
            if ($is_dhtml_editor) {
                $option_hidden .= '<input type="hidden" value="html1" name="html">';
            } else {
                $option .= PHP_EOL.'<li class="chk_box"><p class="checkIn"><input type="checkbox" id="html" name="html" onclick="html_auto_br(this);" class="selec_chk" value="'.$html_value.'" '.$html_checked.'>'.PHP_EOL.'<label for="html">html</label></p></li>';
            }
        }
        if ($is_secret) {
            if ($is_admin || $is_secret==1) {
                $option .= PHP_EOL.'<li class="chk_box"><p class="checkIn"><input type="checkbox" id="secret" name="secret"  class="selec_chk" value="secret" '.$secret_checked.'>'.PHP_EOL.'<label for="secret">비밀글</label></p></li>';
            } else {
                $option_hidden .= '<input type="hidden" name="secret" value="secret">';
            }
        }
        if ($is_mail) {
            $option .= PHP_EOL.'<li class="chk_box"><p class="checkIn"><input type="checkbox" id="mail" name="mail"  class="selec_chk" value="mail" '.$recv_email_checked.'>'.PHP_EOL.'<label for="mail">답변메일받기</label></p></li>';
        }

		//$option .= "\n".'<p class="checkIn"><input type="checkbox" id="html" name="html" onclick="html_auto_br(this);" value="'.$html_value.'" '.$html_checked.'>'."\n".'<label for="html">HTML</label></p>';

		// 유니트쿨러|콘덴싱유니트|췰링유니트 ＆ 췰러|리모트콘덴서 ＆ 코일|스크류 칠링유니트|냉동부품
    }
    echo $option_hidden;
    ?>
	<div class="board_write">
		<table>
			<tbody>
				<?php if ($is_category) { ?>
				<tr>
					<th scope="row"><span class="">분류</span></th>
					<td>
						<select name="ca_name" id="ca_name" required class="required">
							<option value="">분류를 선택하세요</option>
							<?php echo $category_option ?>
						</select>
					</td>
				</tr>
				<?php } ?>


				<?php if ($is_name) { ?>
				<tr>
					<th scope="row"><span class="">이름</span></th>
					<td><input type="text" name="wr_name" value="<?php echo $name ?>" id="wr_name" required class="required" placeholder="이름"></td>
				</tr>
				<?php } ?>
				<?php if ($is_password) { ?>
				<tr>
					<th scope="row"><span class="">비밀번호</span></th>
					<td><input type="password" name="wr_password" id="wr_password" <?php echo $password_required ?> required class="required" <?php echo $password_required ?>" placeholder="비밀번호"></td>
				</tr>
				<?php } ?>
				<?php if ($is_email) { ?>
				<tr>
					<th scope="row"><span class="">이메일</span></th>
					<td><input type="text" name="wr_email" value="<?php echo $email ?>" id="wr_email" required class="required" placeholder="이메일"></td>
				</tr>
				<?php } ?>
				<?php if($is_admin){?>
				<tr class="">
					<th scope="row"><span class="">등록일</span></th>
					<td><input type="text" name="wr_datetime" value="<?php echo substr($write['wr_datetime'],0,10) ?>" id="wr_datetime"  class="datetimepicker small" size="50" maxlength="255" placeholder="미입력 시 현재 날짜로 등록됩니다."></td>
				</tr>
				<?php }?>
				<!--
				<tr>
					<th scope="row"><span class="psBul">순서</span></th>
					<td><input type="text" name="wr_10" value="<?php echo empty($write['wr_10'])?0:$write['wr_10'] ?>" id="wr_10" required class="frm_input full_input required" size="50" maxlength="255" placeholder="순서"></td>
				</tr>
				-->

				<tr>
					<th scope="row"><span class="">소제목</span></th>
					<td><input type="text" name="wr_1" value="<?php echo $wr_1 ?>" id="wr_1" required class="required" size="50" maxlength="255" placeholder="소제목"></td>
				</tr>
                <tr>
					<th scope="row"><span class="">제목</span></th>
					<td><input type="text" name="wr_subject" value="<?php echo $subject ?>" id="wr_subject" required class="required" size="50" maxlength="255" placeholder="제목"></td>
				</tr>

				<tr>
					<th scope="row"><span class="">SPEC</span></th>
					<td>
						<div id="specArea">
							<table id="stockTB">
								<colgroup>
									<col width="200"></col>
									<col width="*"></col>
									<col width="100"></col>
								</colgroup>
								<thead>
								<tr>
									<th >스펙명</th>
									<th>스펙내용</th>
									<th ><input type="button" value="추가" class="btn specAdd"></th>
								</tr>
								</thead>
								<tbody>
								<?
								$arr_SpecNm = explode("^|^",$write['wr_9']);
								$arr_SpecCon = explode("^|^",$write['wr_10']);

								for($i=0; $i<sizeof($arr_SpecNm); $i++){
									$specNm = $arr_SpecNm[$i];
									$specCon = $arr_SpecCon[$i];
								?>
								<tr>
									<td><input type="text" name="specNm[]" value="<?=$specNm?>" maxlength="20"></td>
									<td><input type="text" name="specCon[]" value="<?=$specCon?>" maxlength="50"></td>
									<td><input type="button" value="삭제" class="btn specDel"></td>
								</tr>								
								<?
								}
								?>
								</tbody>
							</table>
						</div>
					</td>
				</tr>
                

				<tr>
					<th scope="row"><span class="">내용</span></th>
					<td>
						<?php if ($option) { ?>
						<ul class="board_option">
							<?php// echo $option ?>
						</ul>
						<?php } ?>
						<div class="wr_content <?php echo $is_dhtml_editor ? $config['cf_editor'] : ''; ?>">
							<?php if($write_min || $write_max) { ?>
							<p id="char_count_desc">이 게시판은 최소 <strong><?php echo $write_min; ?></strong>글자 이상, 최대 <strong><?php echo $write_max; ?></strong>글자 이하까지 글을 쓰실 수 있습니다.</p>
							<?php } ?>
							<?php echo $editor_html; // 에디터 사용시는 에디터로, 아니면 textarea 로 노출 ?>
							<?php if($write_min || $write_max) { ?>
							<div id="char_count_wrap"><span id="char_count"></span>글자</div>
							<?php } ?>
						</div>
					</td>
				</tr>
				<?php for ($i=0; $is_file && $i<$file_count; $i++) { ?>
				<tr>
					<th scope="row"><span class="">
						<? if(($i+1) == '1'){ ?>
						썸네일<br /><span class="type point">270*270</span>
						<?php }else if(($i+1) >= '2' && ($i+1) < '7'){ ?>
						상세이미지 <?php echo $i ?><br /><span class="type point">580*460</span>
						<?php }else if(($i+1) >= '7' && ($i+1) < '8'){ ?>
						국문 카탈로그
						<?php }else{ ?>
						사용설명서
						<?php } ?>
						</span>
					</th>
					<td class="">
						<div class="">
							<input type="file" name="bf_file[]" id="bf_file_<?php echo $i+1 ?>" title="파일첨부 <?php echo $i+1 ?> : 용량 <?php echo $upload_max_filesize ?> 이하만 업로드 가능" >
						</div>
						<?php if ($is_file_content) { ?>
						<input type="text" name="bf_content[]" value="<?php echo ($w == 'u') ? $file[$i]['bf_content'] : ''; ?>" title="파일 설명을 입력해주세요." class="full_input frm_input" size="50" placeholder="파일 설명을 입력해주세요.">
						<?php } ?>

						<?php if($w == 'u' && $file[$i]['file']) { ?>
						<p class="checkIn file_del">
							<input type="checkbox" id="bf_file_del<?php echo $i ?>" name="bf_file_del[<?php echo $i;  ?>]" value="1">
							<label for="bf_file_del<?php echo $i ?>"><?php echo $file[$i]['source'].'('.$file[$i]['size'].')';  ?> 파일삭제</label>
						</p>
						<?php } ?>
					</td>
				</tr>
				<?php } ?>
				<?php if ($is_use_captcha) { //자동등록방지  ?>
				<tr>
					<th scope="row"><span class="psBul">자동등록방지</span></th>
					<td><?php echo $captcha_html ?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>

	<div class="board_btn write">
		<ul class="board_user">
			<li><a href="<?php echo get_pretty_url($bo_table); ?>" class="click big">취소하기</a></li>
			<li><button type="submit" id="btn_submit" accesskey="s" class="click write big">등록하기</button></li>
		</ul>
	</div>
	</form>

	<table id="stockCopyArea" class="disNone">
		<tbody>
		<tr>
			<td><input type="text" name="specNm[]" value="" maxlength="20"></td>
			<td><input type="text" name="specCon[]" value="" maxlength="50"></td>
			<td><input type="button" value="삭제" class="btn specDel"></td>
		</tr>
		</tbody>
	</table>

    <script>
    <?php if($write_min || $write_max) { ?>
    // 글자수 제한
    var char_min = parseInt(<?php echo $write_min; ?>); // 최소
    var char_max = parseInt(<?php echo $write_max; ?>); // 최대
    check_byte("wr_content", "char_count");

	$(function() {
		$("#wr_content").on("keyup", function() {
			check_byte("wr_content", "char_count");
		});
		/*
		$("select[name='phone_change']").change(function () {
			$("#wr_10_1").val($(this).val());
			//alert($(this).val())
			if($(this).val() != ""){
				$("input#wr_10_1").prop("readonly", true)
			}else{
				$("input#wr_10_1").prop("readonly", false)
			}
		});
		*/
    });

    <?php } ?>
    function html_auto_br(obj)
    {
        if (obj.checked) {
            result = confirm("자동 줄바꿈을 하시겠습니까?\n\n자동 줄바꿈은 게시물 내용중 줄바뀐 곳을<br>태그로 변환하는 기능입니다.");
            if (result)
                obj.value = "html2";
            else
                obj.value = "html1";
        }
        else
            obj.value = "";
    }

    function fwrite_submit(f)
    {
        <?php echo $editor_js; // 에디터 사용시 자바스크립트에서 내용을 폼필드로 넣어주며 내용이 입력되었는지 검사함   ?>

        var subject = "";
        var content = "";
        $.ajax({
            url: g5_bbs_url+"/ajax.filter.php",
            type: "POST",
            data: {
                "subject": f.wr_subject.value,
                "content": f.wr_content.value
            },
            dataType: "json",
            async: false,
            cache: false,
            success: function(data, textStatus) {
                subject = data.subject;
                content = data.content;
            }
        });

        if (subject) {
            alert("제목에 금지단어('"+subject+"')가 포함되어있습니다");
            f.wr_subject.focus();
            return false;
        }

        if (content) {
            alert("내용에 금지단어('"+content+"')가 포함되어있습니다");
            if (typeof(ed_wr_content) != "undefined")
                ed_wr_content.returnFalse();
            else
                f.wr_content.focus();
            return false;
        }

        if (document.getElementById("char_count")) {
            if (char_min > 0 || char_max > 0) {
                var cnt = parseInt(check_byte("wr_content", "char_count"));
                if (char_min > 0 && char_min > cnt) {
                    alert("내용은 "+char_min+"글자 이상 쓰셔야 합니다.");
                    return false;
                }
                else if (char_max > 0 && char_max < cnt) {
                    alert("내용은 "+char_max+"글자 이하로 쓰셔야 합니다.");
                    return false;
                }
            }
        }

        <?php echo $captcha_js; // 캡챠 사용시 자바스크립트에서 입력된 캡챠를 검사함  ?>

        document.getElementById("btn_submit").disabled = "disabled";

        return true;
    }
	</script>
<?php if ($bo_table == "refrigerator") { ?>
	<script>
	$(function(){
		//alert($("select[name='ca_name'] option:selected").index())
		$("body").on("change", "select[name='ca_name']", function(){
			var ca = $(this).val();
			//alert(ca)
			$(".board_write .option[data-caName]").each(function(index){
				//console.log($(this))
				if($(this).attr("data-caName") == ca){
					$(this).show();
					$(this).find("input").prop("disabled", false);
				}else{
					$(this).hide();
					$(this).find("input").prop("disabled", true);
				}
			});
		})
		$("select[name='ca_name'] option:selected").change()
	});
    </script>
<?php } ?>
</section>
<!-- } 게시물 작성/수정 끝 -->

<link rel="stylesheet" href="/build/flatpickr/dist/flatpickr.css">
<link rel="stylesheet" href="/build/flatpickr/dist/plugins/confirmDate/confirmDate.css">
<link rel="stylesheet" href="/build/flatpickr/dist/plugins/monthSelect/style.css">

<script type="text/javascript" src="/build/flatpickr/dist/flatpickr.js"></script>
<script type="text/javascript" src="/build/flatpickr/dist/plugins/confirmDate/confirmDate.js"></script>
<script type="text/javascript" src="/build/flatpickr/dist/plugins/weekSelect/weekSelect.js"></script>
<script type="text/javascript" src="/build/flatpickr/dist/plugins/rangePlugin.js"></script>
<script type="text/javascript" src="/build/flatpickr/dist/plugins/minMaxTimePlugin.js"></script>
<script type="text/javascript" src="/build/flatpickr/dist/plugins/monthSelect/index.js"></script>
<script type="text/javascript" src="/build/flatpickr/dist/plugins/l10n/ko.js"></script>

<script>
	// 참고 주소
	// https://flatpickr.js.org/events/

	$(".datetimepicker").flatpickr({
		//"enableTime": true,
		"locale": "ko",
		//minDate: "today",
		//mode: "range",
		dateFormat: "Y-m-d",
		//"plugins": [new rangePlugin({ input: "#wr_8"})],
		onClose: function(selectedDates, dateStr, instance){
		}
	});
</script>

<!--
<link rel="stylesheet" type="text/css" href="/build/jquery.datetimepicker.css"/>
<script src="/build/jquery.datetimepicker.full.min.js"></script>
<script>
	$.datetimepicker.setLocale('ko');
	$('.datetimepicker').datetimepicker({
		format: 'Y-m-d',
		timepicker:false
	});
</script>
-->