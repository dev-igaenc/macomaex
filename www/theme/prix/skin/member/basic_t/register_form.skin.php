<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/newstyle.css">', 0);
?>
<script src="<?php echo G5_JS_URL ?>/jquery.register_form.js"></script>
<?php if($config['cf_cert_use'] && ($config['cf_cert_ipin'] || $config['cf_cert_hp'])) { ?>
<script src="<?php echo G5_JS_URL ?>/certify.js?v=<?php echo G5_JS_VER; ?>"></script>
<?php } ?>

<form id="fregisterform" name="fregisterform" action="<?php echo $register_action_url ?>" onsubmit="return fregisterform_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
	<input type="hidden" name="w" value="<?php echo $w ?>">
	<input type="hidden" name="url" value="<?php echo $urlencode ?>">
	<input type="hidden" name="agree" value="<?php echo $agree ?>">
	<input type="hidden" name="agree2" value="<?php echo $agree2 ?>">
	<input type="hidden" name="cert_type" value="<?php echo $member['mb_certify']; ?>">
	<input type="hidden" name="cert_no" value="">
	<input type="hidden" name="mb_nick" value="&nbsp;">
	<?php if (isset($member['mb_sex'])) {  ?><input type="hidden" name="mb_sex" value="<?php echo $member['mb_sex'] ?>"><?php }  ?>
	<!--
	<?php if (isset($member['mb_nick_date']) && $member['mb_nick_date'] > date("Y-m-d", G5_SERVER_TIME - ($config['cf_nick_modify'] * 86400))) { // 닉네임수정일이 지나지 않았다면  ?>
	<input type="hidden" name="mb_nick_default" value="<?php echo get_text($member['mb_nick']) ?>">
	<input type="hidden" name="mb_nick" value="<?php echo get_text($member['mb_nick']) ?>">
	<?php }  ?>
	-->

	<div class="skin-member form">
		<dl class="h1">
			<dt>회원가입 신청서</dt>
		</dl>

		<ul class="member-step two">
			<li>약관동의</li>
			<li>정보입력</li>
			<li>가입완료</li>
		</ul>

		<p class="h2">정보입력</p>
		<ul class="skin-form">
			<li><input type="text" name="mb_id" id="reg_mb_id" value="<?php echo $member['mb_id'] ?>" minlength="3" maxlength="20" placeholder="아이디" <?php echo $required ?> autocomplete="off" /></li>
			<li><input type="password" name="mb_password" id="reg_mb_password" minlength="3" maxlength="20" placeholder="비밀번호" <?php echo $required ?> autocomplete="off" /></li>
			<li><input type="password" name="mb_password_re" id="reg_mb_password_re" minlength="3" maxlength="20" placeholder="비밀번호 확인" <?php echo $required ?> autocomplete="off" /></li>
			<li><input type="text" name="mb_name" id="reg_mb_name" value="<?php echo get_text($member['mb_name']) ?>" size="10" placeholder="이름" <?php echo $required ?> autocomplete="off" />
				<?php
					if($config['cf_cert_use']) {
						if($config['cf_cert_ipin'])
							echo '<button type="button" id="win_ipin_cert" class="btn_frmline">아이핀 본인확인</button>'.PHP_EOL;
						if($config['cf_cert_hp'])
							echo '<button type="button" id="win_hp_cert" class="btn_frmline">휴대폰 본인확인</button>'.PHP_EOL;

						echo '<noscript>본인확인을 위해서는 자바스크립트 사용이 가능해야합니다.</noscript>'.PHP_EOL;
					}
				?>
					<?php
					if ($config['cf_cert_use'] && $member['mb_certify']) {
						if($member['mb_certify'] == 'ipin')
							$mb_cert = '아이핀';
						else
							$mb_cert = '휴대폰';
					?>

					<div id="msg_certify">
						<strong><?php echo $mb_cert; ?> 본인확인</strong><?php if ($member['mb_adult']) { ?> 및 <strong>성인인증</strong><?php } ?> 완료
					</div>
					<?php } ?>
					<?php if ($config['cf_cert_use']) { ?>
					<button type="button" class="tooltip_icon"><i class="fa fa-question-circle-o" aria-hidden="true"></i><span class="sound_only">설명보기</span></button>
					<span class="tooltip">아이핀 본인확인 후에는 이름이 자동 입력되고 휴대폰 본인확인 후에는 이름과 휴대폰번호가 자동 입력되어 수동으로 입력할수 없게 됩니다.</span>
				<?php } ?>
			</li>
			<li>
				<input type="hidden" name="old_email" value="<?php echo $member['mb_email'] ?>">
				<input type="text" name="mb_email" id="reg_mb_email" value="<?php echo isset($member['mb_email'])?$member['mb_email']:''; ?>" size="70" maxlength="100" placeholder="이메일" required autocomplete="off" />
			</li>

			<?php if ($config['cf_use_hp'] || $config['cf_cert_hp']) {  ?>
			<li>
				<?php if ($config['cf_cert_use'] && $config['cf_cert_hp']) { ?>
				<input type="hidden" name="old_mb_hp" value="<?php echo get_text($member['mb_hp']) ?>">
				<?php } ?>
				<input type="text" name="mb_hp" id="reg_mb_hp" value="<?php echo get_text($member['mb_hp']) ?>" maxlength="20" placeholder="연락처" <?php echo ($config['cf_req_hp'])?"required":""; ?> autocomplete="off" />
			</li>
			<?php }  ?>

			<?php if ($config['cf_use_addr']) { ?>
			<li class="juso">
				<input type="text" name="mb_zip" value="<?php echo $member['mb_zip1'].$member['mb_zip2']; ?>" id="reg_mb_zip" <?php echo $config['cf_req_addr']?"required":""; ?> size="5" maxlength="6"  placeholder="우편번호">
				<a href="javascript:win_zip('fregisterform', 'mb_zip', 'mb_addr1', 'mb_addr2', 'mb_addr3', 'mb_addr_jibeon');" class="btns">주소검색</a>

				<input type="text" name="mb_addr1" value="<?php echo get_text($member['mb_addr1']) ?>" id="reg_mb_addr1" <?php echo $config['cf_req_addr']?"required":""; ?> size="50"  placeholder="기본주소">
				<input type="text" name="mb_addr2" value="<?php echo get_text($member['mb_addr2']) ?>" id="reg_mb_addr2" size="50" placeholder="상세주소">
				<input type="text" name="mb_addr3" value="<?php echo get_text($member['mb_addr3']) ?>" id="reg_mb_addr3" size="50" readonly="readonly" placeholder="참고항목">
				<input type="hidden" name="mb_addr_jibeon" value="<?php echo get_text($member['mb_addr_jibeon']); ?>">
			</li>
			<?php }  ?>
			<li style="font-size:16px;">
				<?php echo captcha_html(); ?>
			</li>
		</ul>
		<p class="skin-check">
			<input type="checkbox" name="mb_mailling" id="reg_mb_mailling" <?php echo ($w=='' || $member['mb_mailling'])?'checked':''; ?> value="1" />
			<label for="reg_mb_mailling"><span class="graphic"></span>정보 메일을 받겠습니다.</label>
		</p>

		<div class="skin-button">
			<a href="<?php echo G5_URL ?>">취소</a>
			<button type="submit" id="btn_submit" accesskey="s"><?php echo $w==''?'다음단계로':'정보수정'; ?></button>
			<?php if($w != ''){ ?><a href="javascript:member_leave();">회원탈퇴</a><?php } ?>
		</div>
	</div>
</form>
<script>
	function member_leave() {  // 회원 탈퇴 tto
		if (confirm("회원에서 탈퇴 하시겠습니까?"))
			location.href = '<?php echo G5_BBS_URL ?>/member_confirm.php?url=member_leave.php';
	}
	$(function() {
		$("#reg_zip_find").css("display", "inline-block");

		<?php if($config['cf_cert_use'] && $config['cf_cert_ipin']) { ?>
		// 아이핀인증
		$("#win_ipin_cert").click(function() {
			if(!cert_confirm())
				return false;

			var url = "<?php echo G5_OKNAME_URL; ?>/ipin1.php";
			certify_win_open('kcb-ipin', url);
			return;
		});

		<?php } ?>
		<?php if($config['cf_cert_use'] && $config['cf_cert_hp']) { ?>
		// 휴대폰인증
		$("#win_hp_cert").click(function() {
			if(!cert_confirm())
				return false;

			<?php
			switch($config['cf_cert_hp']) {
				case 'kcb':
					$cert_url = G5_OKNAME_URL.'/hpcert1.php';
					$cert_type = 'kcb-hp';
					break;
				case 'kcp':
					$cert_url = G5_KCPCERT_URL.'/kcpcert_form.php';
					$cert_type = 'kcp-hp';
					break;
				case 'lg':
					$cert_url = G5_LGXPAY_URL.'/AuthOnlyReq.php';
					$cert_type = 'lg-hp';
					break;
				default:
					echo 'alert("기본환경설정에서 휴대폰 본인확인 설정을 해주십시오");';
					echo 'return false;';
					break;
			}
			?>

			certify_win_open("<?php echo $cert_type; ?>", "<?php echo $cert_url; ?>");
			return;
		});
		<?php } ?>
	});

	// submit 최종 폼체크
	function fregisterform_submit(f)
	{
		// 회원아이디 검사
		if (f.w.value == "") {
			var msg = reg_mb_id_check();
			if (msg) {
				alert(msg);
				f.mb_id.select();
				return false;
			}
		}

		if (f.w.value == "") {
			if (f.mb_password.value.length < 3) {
				alert("비밀번호를 3글자 이상 입력하십시오.");
				f.mb_password.focus();
				return false;
			}
		}

		if (f.mb_password.value != f.mb_password_re.value) {
			alert("비밀번호가 같지 않습니다.");
			f.mb_password_re.focus();
			return false;
		}

		if (f.mb_password.value.length > 0) {
			if (f.mb_password_re.value.length < 3) {
				alert("비밀번호를 3글자 이상 입력하십시오.");
				f.mb_password_re.focus();
				return false;
			}
		}

		// 이름 검사
		if (f.w.value=="") {
			if (f.mb_name.value.length < 1) {
				alert("이름을 입력하십시오.");
				f.mb_name.focus();
				return false;
			}

			/*
			var pattern = /([^가-힣\x20])/i;
			if (pattern.test(f.mb_name.value)) {
				alert("이름은 한글로 입력하십시오.");
				f.mb_name.select();
				return false;
			}
			*/
		}

		<?php if($w == '' && $config['cf_cert_use'] && $config['cf_cert_req']) { ?>
		// 본인확인 체크
		if(f.cert_no.value=="") {
			alert("회원가입을 위해서는 본인확인을 해주셔야 합니다.");
			return false;
		}
		<?php } ?>

		// 닉네임 검사
		/*
		if ((f.w.value == "") || (f.w.value == "u" && f.mb_nick.defaultValue != f.mb_nick.value)) {
			var msg = reg_mb_nick_check();
			if (msg) {
				alert(msg);
				f.reg_mb_nick.select();
				return false;
			}
		}
		*/

		// E-mail 검사

		if ((f.w.value == "") || (f.w.value == "u" && f.mb_email.defaultValue != f.mb_email.value)) {
			var msg = reg_mb_email_check();
			if (msg) {
				alert(msg);
				f.reg_mb_email.select();
				return false;
			}
		}

		<?php if (($config['cf_use_hp'] || $config['cf_cert_hp']) && $config['cf_req_hp']) {  ?>
		// 휴대폰번호 체크
		var msg = reg_mb_hp_check();
		if (msg) {
			alert(msg);
			f.reg_mb_hp.select();
			return false;
		}
		<?php } ?>

		if (typeof f.mb_icon != "undefined") {
			if (f.mb_icon.value) {
				if (!f.mb_icon.value.toLowerCase().match(/.(gif|jpe?g|png)$/i)) {
					alert("회원아이콘이 이미지 파일이 아닙니다.");
					f.mb_icon.focus();
					return false;
				}
			}
		}

		if (typeof f.mb_img != "undefined") {
			if (f.mb_img.value) {
				if (!f.mb_img.value.toLowerCase().match(/.(gif|jpe?g|png)$/i)) {
					alert("회원이미지가 이미지 파일이 아닙니다.");
					f.mb_img.focus();
					return false;
				}
			}
		}

		if (typeof(f.mb_recommend) != "undefined" && f.mb_recommend.value) {
			if (f.mb_id.value == f.mb_recommend.value) {
				alert("본인을 추천할 수 없습니다.");
				f.mb_recommend.focus();
				return false;
			}

			var msg = reg_mb_recommend_check();
			if (msg) {
				alert(msg);
				f.mb_recommend.select();
				return false;
			}
		}

		<?php echo chk_captcha_js();  ?>

		document.getElementById("btn_submit").disabled = "disabled";

		return true;
	}

	jQuery(function($){
		//tooltip
		$(document).on("click", ".tooltip_icon", function(e){
			$(this).next(".tooltip").fadeIn(400).css("display","inline-block");
		}).on("mouseout", ".tooltip_icon", function(e){
			$(this).next(".tooltip").fadeOut();
		});
	});
</script>

<!-- } 회원정보 입력/수정 끝 -->