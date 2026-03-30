<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>
<style>.cke_sc{display:none !important;}</style>

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
		<input type="hidden" name="wr_subject" value="">
		<input type="hidden" name="wr_content" value="&nbsp;">
            <input type="hidden" name="wr_1" value="<?=$rid?>">
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

			$option .= "\n".'<p class="checkIn"><input type="checkbox" id="html" name="html" onclick="html_auto_br(this);" value="'.$html_value.'" '.$html_checked.'>'."\n".'<label for="html">HTML</label></p>';
		}
		echo $option_hidden;
		?>
		<div class="board_write">
			<table>
				<tbody>
					<tr>
						<th scope="row"><span class="">성함</span></th>
						<td><input type="text" name="wr_name" value="<?=empty($name) ? $mb_name : $name?> <?php echo $name ?>" id="wr_name" required class="required" placeholder="이름"></td>
					</tr>
					<tr>
						<th scope="row"><span class="">연락처</span></th>
						<td>
							<?php
							list($wr_10_1,$wr_10_2,$wr_10_3)=explode('-',$write['wr_10']);
							?>
							<div class="three phone">
								<select name="phone_change" id="phone_change" required>
									<option value="010" <?=get_selected("010",$wr_10_1)?>>010</option>
									<option value="02" <?=get_selected("02",$wr_10_1)?>>02</option>
									<option value="031" <?=get_selected("031",$wr_10_1)?>>031</option>
									<option value="032" <?=get_selected("032",$wr_10_1)?>>032</option>
									<option value="033" <?=get_selected("033",$wr_10_1)?>>033</option>
									<option value="041" <?=get_selected("041",$wr_10_1)?>>041</option>
									<option value="042" <?=get_selected("042",$wr_10_1)?>>042</option>
									<option value="043" <?=get_selected("043",$wr_10_1)?>>043</option>
									<option value="051" <?=get_selected("051",$wr_10_1)?>>051</option>
									<option value="052" <?=get_selected("052",$wr_10_1)?>>052</option>
									<option value="053" <?=get_selected("053",$wr_10_1)?>>053</option>
									<option value="054" <?=get_selected("054",$wr_10_1)?>>054</option>
									<option value="055" <?=get_selected("055",$wr_10_1)?>>055</option>
									<option value="061" <?=get_selected("061",$wr_10_1)?>>061</option>
									<option value="062" <?=get_selected("062",$wr_10_1)?>>062</option>
									<option value="063" <?=get_selected("063",$wr_10_1)?>>063</option>
									<option value="064" <?=get_selected("064",$wr_10_1)?>>064</option>
									<option value="070" <?=get_selected("070",$wr_10_1)?>>070</option>
									<option value="0505" <?=get_selected("0505",$wr_10_1)?>>0505</option>
									<option value="" <?=get_selected("",$wr_10_1)?>>직접입력</option>
								</select>
								<input type="text" name="wr_10[]" id="wr_10_1" value="<?=empty($wr_10_1) ? $mb_hp_1 : $wr_10_1?>" placeholder="" readonly/>
								<span class="type c blake">-</span>
								<input type="text" name="wr_10[]" id="wr_10_2" value="<?=empty($wr_10_2) ? $mb_hp_2 : $wr_10_2?>" placeholder="" required class="required" />
								<span class="type c">-</span>
								<input type="text" name="wr_10[]" id="wr_10_3" value="<?=empty($wr_10_3) ? $mb_hp_3 : $wr_10_3?>" placeholder="" required class="required" />
							</div>
						</td>
					</tr>
					<tr>
						<th scope="row"><span class="">이메일</span></th>
						<td>
							<?php
							list($wr_email_1,$wr_email_2)=explode('@',$write['wr_email']);
							?>
							<div class="three email">
								<input type="text" name="wr_email[]" id="wr_email_1" value="<?=empty($wr_email_1) ? $mb_email_1 : $wr_email_1?>" placeholder="" required class="required"/>
								<span class="type c">@</span>
								<input type="text" name="wr_email[]" id="wr_email_2" value="<?=empty($wr_email_2) ? $mb_email_2 : $wr_email_2?>" placeholder="" required class="required" />
								<select name="email_addr">
									<option<?=get_selected("",$wr_email_2);?> value="">직접입력</option>
									<option<?=get_selected("chollian.net",$wr_email_2);?> value="chollian.net">chollian.net</option>
									<option<?=get_selected("dreamwiz.com",$wr_email_2);?> value="dreamwiz.com">dreamwiz.com</option>
									<option<?=get_selected("empal.com",$wr_email_2);?> value="empal.com">empal.com</option>
									<option<?=get_selected("freechal.com",$wr_email_2);?> value="freechal.com">freechal.com</option>
									<option<?=get_selected("gmail.com",$wr_email_2);?> value="gmail.com">gmail.com</option>
									<option<?=get_selected("hanmail.net",$wr_email_2);?> value="hanmail.net">hanmail.net</option>
									<option<?=get_selected("hotmail.com",$wr_email_2);?> value="hotmail.com">hotmail.com</option>
									<option<?=get_selected("hanafos.com",$wr_email_2);?> value="hanafos.com">hanafos.com</option>
									<option<?=get_selected("kebi.com",$wr_email_2);?> value="kebi.com">kebi.com</option>
									<option<?=get_selected("korea.com",$wr_email_2);?> value="korea.com">korea.com</option>
									<option<?=get_selected("lycos.co.kr",$wr_email_2);?> value="lycos.co.kr">lycos.co.kr</option>
									<option<?=get_selected("nate.com",$wr_email_2);?> value="nate.com">nate.com</option>
									<option<?=get_selected("naver.com",$wr_email_2);?> value="naver.com">naver.com</option>
									<option<?=get_selected("netian.com",$wr_email_2);?> value="netian.com">netian.com</option>
									<option<?=get_selected("paran.com",$wr_email_2);?> value="paran.com">paran.com</option>
									<option<?=get_selected("yahoo.co.kr",$wr_email_2);?> value="yahoo.co.kr">yahoo.co.kr</option>
								</select>
							</div>
						</td>
					</tr>
					<?php if ($is_category) { ?>
					<tr>
						<th scope="row"><span class="">지원분야</span></th>
						<td>
							<select name="ca_name" id="ca_name" required class="required">
								<option value="">분류를 선택하세요</option>
								<?php echo $category_option ?>
							</select>
						</td>
					</tr>
					<?php } ?>
					<?php for ($i=0; $is_file && $i<$file_count; $i++) { ?>
					<tr>
						<th scope="row"><span class="">첨부파일 <!-- #<?php echo $i+1 ?> --></span></th>
						<td class="bo_w_flie">
							<div class="">
								<input type="file" name="bf_file[]" id="bf_file_<?php echo $i+1 ?>" title="파일첨부 <?php echo $i+1 ?> : 용량 <?php echo $upload_max_filesize ?> 이하만 업로드 가능" <? if(($i+1) == '1'){ ?> class="" <?php } ?>>
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
					<!-- <tr>
						<th scope="row"><span class="psBul">자동등록방지</span></th>
						<td><?php echo $captcha_html ?></td>
					</tr> -->
					<?php } ?>
				</tbody>
			</table>
		</div>
		<div class="board_btn write">
			<ul class="board_user">
				<li><a href="<?php echo get_pretty_url($bo_table); ?>" class="click big">취소</a></li>
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

			function fwrite_submit(f){
				/*
				if (!$("input#contactCheck").prop("checked")) {
					alert('개인정보처리방침에 동의 해주세요');
					$("input#contactCheck").siblings("label").focus();
					return false;
				}
				*/

				var $bo_subject= '<?=$bo_subject?>';
				f.wr_subject.value = f.wr_name.value+"님의 "+$bo_subject + "입니다.";

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

				<?//php echo $captcha_js; // 캡챠 사용시 자바스크립트에서 입력된 캡챠를 검사함  ?>

				document.getElementById("btn_submit").disabled = "disabled";

				return true;
			}

			$("select[name='email_addr']").change(function () {
				$("#wr_email_2").val($(this).val());
			});
			$("select[name='phone_change']").change(function () {
				$("#wr_10_1").val($(this).val());
				//alert($(this).val())
				if($(this).val() == ""){
					$("input#wr_10_1").attr("readonly", false)
				}
			});
		</script>
	</section>
<!-- } 게시물 작성/수정 끝 -->