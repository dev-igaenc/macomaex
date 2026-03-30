<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>

<!-- 회원 비밀번호 확인 시작 { -->
<div id="pw_confirm" class="mbskin kookMin newLogin">
	<dl class="topTit">
		<dt><?php echo $g5['title'] ?></dt>
		<dd>
			<strong>비밀번호를 한번 더 입력해주세요.</strong>
			<?php if ($url == 'member_leave.php') { ?>
			비밀번호를 입력하시면 회원탈퇴가 완료됩니다.
			<?php }else{ ?>
			회원님의 정보를 안전하게 보호하기 위해 비밀번호를 한번 더 확인합니다.
			<?php }  ?>
		</dd>
	</dl>

	<form name="fmemberconfirm" action="<?php echo $url ?>" onsubmit="return fmemberconfirm_submit(this);" method="post">
		<input type="hidden" name="mb_id" value="<?php echo $member['mb_id'] ?>">
		<input type="hidden" name="w" value="u">

		<fieldset id="login_fs" class="tal">
			<span id="mb_confirm_id">아이디 : <?php echo $member['mb_id'] ?></span>
			<input type="password" name="mb_password" id="confirm_mb_password" required class="required frm_input" size="15" maxLength="20" placeholder="비밀번호">

			<div class="btn_box">
				<button type="button" class="btn_submit btn_cancel" onclick="javascript:history.go(-1);">취소</button>
				<input type="submit" value="확인" id="btn_submit" class="btn_submit">
			</div>
		</fieldset>
	</form>
</div>
<script>
	function fmemberconfirm_submit(f){
		document.getElementById("btn_submit").disabled = true;
		return true;
	}
</script>
<!-- } 회원 비밀번호 확인 끝 -->