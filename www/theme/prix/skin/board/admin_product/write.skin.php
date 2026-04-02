<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>
<style>
.cke_sc{display:none !important;}
.wr_icon #icon li {float:left; text-align:center; padding:0 20px; border-right:1px solid #ccc;}
.wr_icon #icon li:last-child { border-right:0px solid #999;}
.wr_icon .icon1img {width:75px; clear:both; display:block; margin-top:5px; }
.wr_icon2 #icon li {float:left; text-align:center; padding:0 20px; border-right:1px solid #ccc;}
.wr_icon2 #icon li:last-child { border-right:0px solid #999;}
.wr_icon2 .icon2img {width:75px;}
.dt .small {width:150px !important;}
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
				<tr>
					<th scope="row"><span class="">제품명</span></th>
					<td>
					<p class="checkIn"><input type="checkbox" id="wr_9" name="wr_9" class="selec_chk" value="main"<?if($write['wr_9']){?> checked<?}?>><label for="wr_9">메인노출</label></p>
					<input type="text" name="wr_subject" value="<?php echo $subject ?>" id="wr_subject" required class="required" size="50" maxlength="255" placeholder="제목">
					</td>
				</tr>
				<tr>
					<th scope="row"><span class="">간단설명</span></th>
					<td><input type="text" name="wr_3" value="<?php echo $write['wr_3'] ?>" id="wr_3" required class="required" size="50" maxlength="255" placeholder="간단설명"></td>
				</tr>
				<tr class="dt">
					<th scope="row"><span class="">제품특징</span></th>
					<td>
						<?php if ($option) { ?>
						<ul class="board_option">
							<?php// echo $option ?>
						</ul>
						<?php } ?>
						<div class="wr_content <?php echo $is_dhtml_editor ? $config['cf_editor'] : ''; ?>">
							<ul id="sortable">
								<li><button type="button" id="add_btn" class="click btn"><i class="fa fa-plus" aria-hidden="true" desc="제품특징"></i> 추가하기</button></li>
							<? 
								$month=explode("||",$content);
								foreach($month as $key) {
									$key2=explode("##",$key);
							?>
								<li class="ui-state-default"><input type="hidden" name="month[]" id="month" placeholder="항목명" class="small" value="<?=$key2[0]?>"> <input type="text" name="month_txt[]" id="month_txt" placeholder="내용" class=""  value="<?=$key2[1]?>">&emsp;<i class="fa fa-arrows-v" aria-hidden="true"></i>&emsp;<i class="fa fa-trash-o" aria-hidden="true" id="trash"></i></li>
							<?}?>
							<input type="hidden" name="wr_content">
							</ul>
						</div>
					</td>
				</tr>
				<tr class="dt">
					<th scope="row"><span class="">제품스팩</span></th>
					<td>
						<div class="wr_content">
							<ul id="sortable">
								<li><button type="button" id="add_btn" class="click btn" desc="제품스팩"><i class="fa fa-plus" aria-hidden="true"></i> 추가하기</button></li>
							<? 
								$spec=explode("||",$write['wr_link1']);
								foreach($spec as $key) {
									$key2=explode("##",$key);
							?>
								<li class="ui-state-default"><input type="text" name="key[]" id="key" placeholder="항목명" class="small" value="<?if($key2[0]){?><?=$key2[0]?><?}else{?>촬상소자<?}?>"> <input type="text" name="key_txt[]" id="key_txt" placeholder="내용" class=""  value="<?=$key2[1]?>">&emsp;<i class="fa fa-arrows-v" aria-hidden="true"></i>&emsp;<i class="fa fa-trash-o" aria-hidden="true" id="trash"></i></li>
							<?}?>
							<input type="hidden" name="wr_link1">
							</ul>
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row"><span class="">아이콘 선택사항</span></th>
					<td>
						<div class="wr_icon">
							<ul id="icon">
								<li><p class="checkIn"><input type="checkbox" id="icon1" name="icon1[]" class="selec_chk" value="0"><label for="icon1">해상도<img src="/img/icon_check0.jpg" class="icon1img"></label></p></li>
								<li><p class="checkIn"><input type="checkbox" id="icon2" name="icon1[]" class="selec_chk" value="1"><label for="icon2">LV필터<img src="/img/icon_check1.jpg" class="icon1img"></label></p></li>
								<li><p class="checkIn"><input type="checkbox" id="icon3" name="icon1[]" class="selec_chk" value="2"><label for="icon3">HW필터<img src="/img/icon_check2.jpg" class="icon1img"></label></p></li>
								<li><p class="checkIn"><input type="checkbox" id="icon4" name="icon1[]" class="selec_chk" value="3"><label for="icon4">ONVIF<img src="/img/icon_check3.jpg" class="icon1img"></label></p></li>
								<li><p class="checkIn"><input type="checkbox" id="icon5" name="icon1[]" class="selec_chk" value="4"><label for="icon5">PoE<img src="/img/icon_check4.jpg" class="icon1img"></label></p></li>
								<li><p class="checkIn"><input type="checkbox" id="icon6" name="icon1[]" class="selec_chk" value="5"><label for="icon6">Day&Night<img src="/img/icon_check5.jpg" class="icon1img"></label></p></li>
								<li><p class="checkIn"><input type="checkbox" id="icon7" name="icon1[]" class="selec_chk" value="6"><label for="icon7">가변형<img src="/img/icon_check6.jpg" class="icon1img"></label></p></li>
							</ul>
							<input type="hidden" name="wr_1">
							<? if($write['wr_1']){
									$icon1=explode("||",$write['wr_1']);
							?>
								<script>
								<?foreach($icon1 as $value){?>
								$("input[name='icon1[]'][value='<?=$value?>']").prop('checked',true);
								<?}?>
								</script>
							<?}?>
						</div>
					</td>
				</tr>

				<tr>
					<th scope="row"><span class="">인증마크 선택사항</span></th>
					<td>
						<div class="wr_icon2">
							<ul id="icon">
								<li><p class="checkIn"><input type="checkbox" id="icon2_1" name="icon2[]" class="selec_chk" value="0"><label for="icon2_1"><img src="/img/crt_0.jpg" class="icon2img"></label></p></li>
								<li><p class="checkIn"><input type="checkbox" id="icon2_2" name="icon2[]" class="selec_chk" value="1"><label for="icon2_2"><img src="/img/crt_1.jpg" class="icon2img"></label></p></li>
								<li><p class="checkIn"><input type="checkbox" id="icon2_3" name="icon2[]" class="selec_chk" value="2"><label for="icon2_3"><img src="/img/crt_2.jpg" class="icon2img"></label></p></li>
								<li><p class="checkIn"><input type="checkbox" id="icon2_4" name="icon2[]" class="selec_chk" value="3"><label for="icon2_4"><img src="/img/crt_3.jpg" class="icon2img"></label></p></li>
								<!-- <li><p class="checkIn"><input type="checkbox" id="icon2_5" name="icon2[]" class="selec_chk" value="4"><label for="icon2_5"><img src="/img/crt_4.png" class="icon2img"></label></p></li> -->
							</ul>
							<input type="hidden" name="wr_2">
							<? if($write['wr_2']){
									$icon2=explode("||",$write['wr_2']);
							?>
								<script>
								<?foreach($icon2 as $value){?>
								$("input[name='icon2[]'][value='<?=$value?>']").prop('checked',true);
								<?}?>
								</script>
							<?}?>
						</div>
					</td>
				</tr>
				<?php for ($i=0; $is_file && $i<$file_count; $i++) { ?>
				<tr>
					<th scope="row">
						<span class="">
							<? 
							if(($i+1) == '1'){ echo"썸네일"; }
							elseif(($i+1) == '2'){echo"제품이미지";}
							elseif(($i+1) == '3'){echo"카다로그";}
							elseif(($i+1) == '4'){echo"도면";}
							else{ echo"첨부파일";} 
							?>
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
	//제품특징
	var month="";
	$("input[name='month[]']").each(function(){
		month += "||"+$(this).val()+"##"+$(this).next().val();
	})
	month=month.substr(2);
	$("input[name='wr_content']").val(month);
	//제품스팩
	var key="";
	$("input[name='key[]']").each(function(){
		key += "||"+$(this).val()+"##"+$(this).next().val();
	})
	key=key.substr(2);
	$("input[name='wr_link1']").val(key);
	//아이콘선택사항
	var icon="";
	$("input[name='icon1[]']:checked").each(function(){
		icon += "||"+$(this).val();
	})
	icon=icon.substr(2);
	$("input[name='wr_1']").val(icon);
	//인증선택사항
	var icon2="";
	$("input[name='icon2[]']:checked").each(function(){
		icon2 += "||"+$(this).val();
	})
	icon2=icon2.substr(2);
	$("input[name='wr_2']").val(icon2);

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
	var arr1 = [];    // 추천기본항목 
	arr1.push("촬상소자"); 
	arr1.push("유효 화소수");
	arr1.push("초점거리");
	arr1.push("줌배율");
	arr1.push("방진/방수규격");
	arr1.push("전원공급");
	arr1.push("중량");
	arr1.push("크기(mm)");
	//추가버튼
	$("button#add_btn").click(function(){
		par=$(this).parents("ul");
		type=$(this).attr("desc");
		$(this).parents("ul").children('li:last').clone().appendTo(par);
		if(type=="제품스팩"){
			idx=par.find('li').length-1;
			par.find('input:last').val("");
			par.find('input:last').prev().val(arr1[idx]);
		}else{
			par.find('li:last input').val("");
		}
		
	})
	//삭제버튼
	$(".wr_content").on("click","i#trash", function(){
		$(this).parents("li").remove();
	})
	//소팅
	$( ".board_write #sortable" ).sortable();

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