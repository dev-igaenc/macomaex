<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);

// 다음지도 처음화면 설정
if($write['wr_8'] == null){$write['wr_8'] =  37.566400714093284;}
if($write['wr_9'] == null){$write['wr_9'] = 126.9785391897507;}
?>
<style>.cke_sc{display:none !important;}</style>

	<div id="storeMap"></div>
	<div id="clickLatlng"></div>
	<script type="text/javascript" src="//dapi.kakao.com/v2/maps/sdk.js?appkey=<?=$config['cf_10']?>&libraries=services"></script>
	<script>
		var mapContainer = document.getElementById("storeMap"), // 지도를 표시할 div
			mapOption = {
				center: new kakao.maps.LatLng(<?=$write['wr_8']?>, <?=$write['wr_9']?>), // 지도의 중심좌표
				level: 3 // 지도의 확대 레벨
			};

		// 지도를 생성합니다
		var map = new kakao.maps.Map(mapContainer, mapOption);

		// 주소-좌표 변환 객체를 생성합니다
		var geocoder = new kakao.maps.services.Geocoder();

		// 지도를 클릭한 위치에 표출할 마커입니다
		var marker = new kakao.maps.Marker({
			map: map,
			// 지도 중심좌표에 마커를 생성합니다
			position: map.getCenter()
		});


		function getLByAddress(address) {
			// 주소로 좌표를 검색합니다
			geocoder.addressSearch(address, function(result, status) {

				// 정상적으로 검색이 완료됐으면
				 if (status === kakao.maps.services.Status.OK) {

					var coords = new kakao.maps.LatLng(result[0].y, result[0].x);

					// 결과값으로 받은 위치를 마커로 표시합니다
				  //  var marker = new kakao.maps.Marker({
				   //     map: map,
					//    position: coords
					//});

					// 인포윈도우로 장소에 대한 설명을 표시합니다
					//var infowindow = new kakao.maps.InfoWindow({
					//    content: '<div style="width:150px;text-align:center;padding:6px 0;">우리회사</div>'
				   // });
					//infowindow.open(map, marker);
					marker.setPosition(coords);
					// 지도에 마커를 표시합니다
					marker.setMap(map);

					// 지도의 중심을 결과값으로 받은 위치로 이동시킵니다
					map.setCenter(coords);
					document.getElementById('wr_8').value = coords.getLat();
					document.getElementById('wr_9').value = coords.getLng();
				}
			});
		}

		// 지도에 클릭 이벤트를 등록합니다
		// 지도를 클릭하면 마지막 파라미터로 넘어온 함수를 호출합니다
		kakao.maps.event.addListener(map, 'click', function(mouseEvent) {
			// 지도에 마커를 표시합니다
			marker.setMap(map);

			// 클릭한 위도, 경도 정보를 가져옵니다
			var latlng = mouseEvent.latLng;

			// 마커 위치를 클릭한 위치로 옮깁니다
			marker.setPosition(latlng);

			document.getElementById('wr_8').value = latlng.getLat();
			document.getElementById('wr_9').value = latlng.getLng();

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
		<input type="hidden" name="wr_content" value="&nbsp;">
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
					<tr>
						<th scope="row"><label for="wr_pi_goods_color_s">주소</label></th>
						<td>
							<input type="text" name="wr_4" id="wr_4" value="<?=$write['wr_4']?>" required class="required" maxlength="255" onBlur="getLByAddress(this.value);" placeholder="*주소가 정확치 않으면 나타나지 않습니다.">
						</td>
					</tr>
					<tr>
						<th>위도 / 경도</th>
						<td>
							<div class="two">
								<input type="text" name="wr_8" value="<?php echo $write['wr_8'] ?>" id="wr_8" readonly class="frm_input full_input" placeholder="위도">
								<span class="type c">/</span>
								<input type="text" name="wr_9" value="<?php echo $write['wr_9'] ?>" id="wr_9" readonly class="frm_input full_input" placeholder="경도">
							</div>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="wr_pi_goods_color_s">전화번호</label></th>
						<td>
							<input type="text" name="wr_5" id="wr_5" value="<?=$write['wr_5']?>" maxlength="255" required class="required">
						</td>
					</tr>
					<tr>
						<th scope="row"><span class="">매장명</span></th>
						<td><input type="text" name="wr_subject" value="<?php echo $subject ?>" id="wr_subject" required class="required" size="50" maxlength="255" placeholder="매장명"></td>
					</tr>
					<tr>
						<th scope="row"><span class="">내용</span></th>
						<td>
							<?php if ($option) { ?>
							<!-- <ul class="board_option">
								<?php echo $option ?>
							</ul> -->
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
					<!-- <?php for ($i=1; $is_link && $i<=G5_LINK_COUNT; $i++) { ?>
					<tr>
						<th scope="row"><span class="">링크 #<?php echo $i ?></span></th>
						<td class="bo_w_link">
							<input type="text" name="wr_link<?php echo $i ?>" value="<?php if($w=="u"){ echo $write['wr_link'.$i]; } ?>" id="wr_link<?php echo $i ?>" class="" size="50">
						</td>
					</tr>
					<?php } ?> -->
					<?php for ($i=0; $is_file && $i<$file_count; $i++) { ?>
					<tr>
						<th scope="row"><span class="">매장사진 #<?php echo $i+1 ?><br /><span class="type point">665*400</span></span></th>
						<td class="bo_w_flie">
							<div class="">
								<input type="file" name="bf_file[]" id="bf_file_<?php echo $i+1 ?>" title="파일첨부 <?php echo $i+1 ?> : 용량 <?php echo $upload_max_filesize ?> 이하만 업로드 가능" class=" ">
							</div>
							<?php if ($is_file_content) { ?>
							<input type="text" name="bf_content[]" value="<?php echo ($w == 'u') ? $file[$i]['bf_content'] : ''; ?>" title="파일 설명을 입력해주세요." class="full_input frm_input" size="50" placeholder="파일 설명을 입력해주세요.">
							<?php } ?>

							<?php if($w == 'u' && $file[$i]['file']) { ?>
							<span class="file_del">
								<input type="checkbox" id="bf_file_del<?php echo $i ?>" name="bf_file_del[<?php echo $i;  ?>]" value="1"> <label for="bf_file_del<?php echo $i ?>"><?php echo $file[$i]['source'].'('.$file[$i]['size'].')';  ?> 파일 삭제</label>
							</span>
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

<? if($write['wr_4']) { ?>
<script>
	getLByAddress('<?=$write['wr_4']?>');
</script>
<? } ?>