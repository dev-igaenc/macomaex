<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

?>
<!-- <link rel="stylesheet" href="<?php echo $board_skin_url ?>/style.css">
 -->
<? include_once $board_skin_path."/board_head.skin.php"; ?>
<section id="bo_w" class="widthfix">

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
					<!--
					<tr>
						<th scope="row"><span class="psBul">순서</span></th>
						<td><input type="text" name="wr_10" value="<?php echo empty($write['wr_10'])?0:$write['wr_10'] ?>" id="wr_10" required class="frm_input full_input required" size="50" maxlength="255" placeholder="순서"></td>
					</tr>
					-->
					<tr>
						<th scope="row">연도</th>
						<td><input type="text" name="wr_subject" value="<?php echo $subject ?>" id="wr_subject" required class="required small" size="50" maxlength="255" placeholder="ex) 1987"></td>
					</tr>
					<tr>
						<th scope="row"><label for="wr_content">내용<strong class="sound_only">필수</strong></label></th>
						<td class="wr_content"><?php echo $editor_html; // 에디터 사용시는 에디터로, 아니면 textarea 로 노출  ?></td>
					</tr>
					<?php for ($i=0; $is_file && $i<$file_count; $i++) { ?>
					<tr>
						<th scope="row"><span class="">첨부파일 <!-- #<?php echo $i+1 ?> --></span></th>
						<td class="bo_w_flie">
							<div class="">
								<input type="file" name="bf_file[]" id="bf_file_<?php echo $i+1 ?>" title="파일첨부 <?php echo $i+1 ?> : 용량 <?php echo $upload_max_filesize ?> 이하만 업로드 가능" class=" ">
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

<script>
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
function daysInMonth (month, year) {
    return new Date(year, month, 0).getDate();
}

$("select[name='wr_1']").change(function () {
    var last_day=daysInMonth(parseInt($(this).val()),parseInt($("select[name='wr_subject']").val()));
    $("select[name='wr_2']").html('<option value="">일 선택</option>');
    for (var i=1;i<last_day+1;i++){
        let day="";
        if(i<10){
             day="0"+i;
        }else{
             day=i;
        }
        $("select[name='wr_2']").append('<option value="'+day+'">'+day+'</option>');
    }

})

function fwrite_submit(f)
{
    <?php echo $editor_js; // 에디터 사용시 자바스크립트에서 내용을 폼필드로 넣어주며 내용이 입력되었는지 검사함   ?>

    var subject = "";
    var content = "";
    $.ajax({
        url: G5_bbs_url+"/ajax.filter.php",
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

    <?php echo $captcha_js; // 캡챠 사용시 자바스크립트에서 입력된 캡챠를 검사함  ?>

    document.getElementById("btn_submit").disabled = "disabled";

    return true;
}
</script>
<!-- } 게시물 작성/수정 끝 -->
