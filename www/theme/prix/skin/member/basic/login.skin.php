<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/newstyle.css">', 0);
if (file_exists(G5_THEME_MSHOP_PATH.'/shop.head.php')) {
	include_once(G5_THEME_MSHOP_PATH.'/shop.head.php');
}
?>
<form name="flogin" action="<?php echo $login_action_url ?>" onsubmit="return flogin_submit(this);" method="post" id="flogin">
	<input type="hidden" name="url" value="<?php echo $login_url ?>">
	<div class="skin-member login">
		<dl class="h1">
			<dt><img src="/img/logo.svg" alt="매커멕스" title=""></dt>
		</dl>
		<div class="login-fild">
			<label for="login-id"><input type="text" name="mb_id" id="login-id" value="" maxlength="32" placeholder="이메일" required autocomplete="off" /></label>
			<label for="login-pw"><input type="password" name="mb_password" id="login-pw" value="" maxlength="32" placeholder="비밀번호" required autocomplete="off" /></label>

			<input type="submit" value="로그인 하기" />

			<div class="login-option">
				<p class="skin-check save_id">
					<input type="checkbox" name="" id="login-save" />
					<label for="login-save"><span class="graphic"></span>자동로그인</label>
				</p>
				<a href="<?php echo G5_BBS_URL ?>/password_lost.php">아이디찾기 / 비밀번호 찾기</a>
			</div>
		</div>
		<?php // @include_once(get_social_skin_path().'/social_login.skin.php'); // 소셜로그인 임시 비활성화 ?>

		<div class="login-link">
			<dl>
				<dt>아직 회원가입을 안하셨다면?</dt>
				<dd><a href="./register.php?url=<?=$url?>">회원가입 하기</a></dd>
			</dl>
		</div>
	</div>

</form>

</div>

<script>
$(function(){
    $("#login_auto_login").click(function(){
        if (this.checked) {
            this.checked = confirm("자동로그인을 사용하시면 다음부터 회원아이디와 비밀번호를 입력하실 필요가 없습니다.\n\n공공장소에서는 개인정보가 유출될 수 있으니 사용을 자제하여 주십시오.\n\n자동로그인을 사용하시겠습니까?");
        }
    });
});

function flogin_submit(f)
{
    return true;
}
</script>

<?php
if (file_exists(G5_THEME_MSHOP_PATH.'/shop.tail.php')) {
	include_once(G5_THEME_MSHOP_PATH.'/shop.tail.php');
}
?>
