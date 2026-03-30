<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>
<style>
.cke_sc{display:none !important;}
div.link_result {
	border:1px solid #999; padding:10px 10px 0px 10px; margin-top:10px; background-color:#fff;
	display:none;
	position:relative;
}
.link_result dl {
	display:inline-block;
	border:1px solid #999;
	padding-left:5px;
	width:45%;
	color:#111;
	background-color:#fefefe;
	border-radius:5px;
}
.link_result dt,dd {
	float:left;
	margin-right:5px;
	padding-top:5px;
}
.link_result dd.bn1, dd.bn2 {
	float:right;
}
div.link_result .cl_btn{
	position:absolute; right:-5px; top:0px;
}
</style>
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
	<input type="hidden" name="rt" value="<?php echo $rt ?>">
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
						<select name="ca_name" id="ca_name" required class="required small">
							<option value="">분류를 선택하세요</option>
							<?php echo $category_option ?>
						</select>
					</td>
				</tr>
				<?php } ?>
				<?php if ($is_name) { ?>
				<tr>
					<th scope="row"><span class="">이름</span></th>
					<td><input type="text" name="wr_name" value="<?php echo $name ?>" id="wr_name" required class="small" placeholder="이름"></td>
				</tr>
				<?php } ?>
				<?php if ($is_email) { ?>
					<input type="hidden" name="wr_email" value="<?php echo $email ?>" id="wr_email" required class="required" placeholder="이메일">
				<?php } ?>
				<?php if($is_admin){?>
				<tr class="">
					<th scope="row"><span class="">등록일</span></th>
					<td><input type="text" name="wr_datetime" value="<?php echo substr($write['wr_datetime'],0,10) ?>" id="wr_datetime"  class="datetimepicker small" size="50" maxlength="255" placeholder="미입력 시 현재 날짜로 등록됩니다."></td>
				</tr>
				<?php }?>
				<tr>
					<th scope="row"><span class="">기업명</span></th>
					<td>
					<input type="text" name="wr_subject" value="<?php echo $subject ?>" id="wr_subject" required class="required small" size="50" maxlength="255" placeholder="기업명">
					<button type="button" id="company_srh" class="btn btn-lg btn-primary">상세보기링크 검색</button>
					</td>
				</tr>
							<tr>
					<th scope="row"><span class="">상세보기</span></th>
					<td>
						<input type="text" name="wr_2" value="<?php echo $wr_2 ?>" id="wr_2" required class="required small" size="50" maxlength="255" placeholder="상세보기링크">
						<div class="link_result">
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row"><span class="">게시여부</span></th>
					<td>
						<p class="checkIn"><input type="radio" id="wr_91" name="wr_9" <? if($wr_9=='Y') echo "checked";?> value="Y" /><label for="wr_91">Yes</label></p>
						<p class="checkIn"><input type="radio" id="wr_92" name="wr_9" <? if($wr_9=='N' or !$wr_9) echo "checked";?> value="N" /><label for="wr_92">No</label></p>
					</td>
				</tr>
				<input type="hidden" name="wr_content" value="&nbsp;">
				<tr>
					<th scope="row"><span class="psBul">유튜브 주소 링크 </span></th>
					<td><input type="text" name="wr_1" value="<?php echo $wr_1 ?>" id="wr_1" class="frm_input full_input required" size="50" required placeholder="ex) https://www.youtube.com/watch?v=fW34q6eGMEc"></td>
				</tr>
				<?php for ($i=0; $is_file && $i<$file_count; $i++) { ?>
				<tr>
					<th scope="row"><span class=""><? if(($i+1) == '1'){ ?>썸네일<br /><span class="type point">1:1 비율</span><?php }else{ ?>첨부파일<?php } ?></span></th>
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
			<?php if($rt) { ?>
				<li><a href="./index.php?bo_table=<?=$bo_table?>" class="click big">취소하기</a></li>
			<?}else{?>
				<li><a href="<?php echo get_pretty_url($bo_table); ?>" class="click big">취소하기</a></li>
			<?}?>
			<li><button type="submit" id="btn_submit" accesskey="s" class="click write big">등록하기</button></li>
		</ul>
	</div>
	</form>

    <script>
	$("#company_srh").click(function(){
		com_name=$(this).prev().val();
		com_name=$.trim(com_name);
		if(!com_name) {
			alert("기업명을 입력하세요.");
			 return false;
		}

		$.post( "../admin_ajax.php", {
			type: 'com_link',
			com_name: com_name
		})
		.done(function(jqXHR) {
			//alert(jqXHR);
			if(jqXHR=="500"){
				alert("처리에 오류가 발생했습니다.");
			}else{

				$(".link_result").show().html(jqXHR);
			}
		});
	})
	$("body").on("click",".link_sel",function(){
		link_href=$(this).attr("link_data");
		$("#wr_2").val(link_href);
	})
	$("body").on("click","div.link_result .cl_btn",function(){
		$(".link_result").hide();
	})

    <?php if($write_min || $write_max) { ?>
    // 글자수 제한
    var char_min = parseInt(<?php echo $write_min; ?>); // 최소
    var char_max = parseInt(<?php echo $write_max; ?>); // 최대
    check_byte("wr_content", "char_count");

    $(function() {
        $("#wr_content").on("keyup", function() {
            check_byte("wr_content", "char_count");
        });
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