<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/newstyle.css">', 0);
?>
<form name="fpasswordlost" action="<?php echo $action_url ?>" onsubmit="return fpasswordlost_submit(this);" method="post" autocomplete="off">
	<div class="skin-member find">
		<dl class="h1">
			<dt>회원정보 찾기</dt>
			<dd>입력하신 이메일로 <span>아이디</span>와 <span>비밀번호 정보</span>를 보내드립니다.</dd>
		</dl>

		<p class="h2">회원가입 시 등록하신 이메일 주소를 입력해 주세요.</p>
		<ul class="skin-form">
			<li>
				<input type="text" name="mb_email" id="mb_email" size="30" maxlength="100" placeholder="이메일" required autocomplete="off" />
			</li>
			<li style="font-size:16px;">
				<?php echo captcha_html(); ?>
			</li>
		</ul>

		<div class="skin-button">
			<a href="<?php echo G5_URL ?>">취소</a>
			<button type="submit" onclick="location.href='<?php echo G5_URL ?>/'">확인</button>
		</div>
	</div>
</form>

<script>
function fpasswordlost_submit(f){
	<?php echo chk_captcha_js();  ?>
	return true;
}

$(function() {
	/*
	var sw = screen.width;
	var sh = screen.height;
	var cw = document.body.clientWidth;
	var ch = document.body.clientHeight;
	var top  = sh / 2 - ch / 2 - 100;
	var left = sw / 2 - cw / 2;
	moveTo(left, top);
	*/
});
</script>
<!-- } 회원정보 찾기 끝 -->