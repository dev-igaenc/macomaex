<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>
<!-- 로그인 시작 { -->
<div id="mb_login" class="mbskin newLogin kookMin">
	<!-- <h1><?php echo $g5['title'] ?></h1> -->
	<h1><img src="<?php echo G5_IMG_URL ?>/logo.svg" alt="<?php echo $config['cf_title']; ?>"></h1>
	<!-- <p class="txt">로그인을 하시면 더 많은 서비스를 이용하실 수 있습니다.</p> -->

	<form name="flogin" action="<?php echo $login_action_url ?>" onsubmit="return flogin_submit(this);" method="post">
	<input type="hidden" name="url" value="<?php echo $login_url ?>">

	<fieldset id="login_fs">
		<legend>회원로그인</legend>
		<label for="login_id" class="sound_only">회원아이디<strong class="sound_only"> 필수</strong></label>
		<input type="text" name="mb_id" id="login_id" required class="frm_input required" size="20" maxLength="32" placeholder="아이디">
		<label for="login_pw" class="sound_only">비밀번호<strong class="sound_only"> 필수</strong></label>
		<input type="password" name="mb_password" id="login_pw" required class="frm_input required" size="20" maxLength="32" placeholder="비밀번호">

		<p class="checkIn save_id">
			<input type="checkbox" id="login_auto_login" name="auto_login" />
			<label for="login_auto_login">아이디 저장</label>
		</p>

		<input type="submit" value="로그인" class="btn_submit">
	</fieldset>

	<?php
	// 소셜로그인 사용시 소셜로그인 버튼
	@include_once(get_social_skin_path().'/social_login.skin.php');
	?>


	<ul class="txtBtn">
		<li>
			<span>- 아이디와 비밀번호를 알려드립니다.</span>
			<a href="<?php echo G5_BBS_URL ?>/password_lost.php"><span>아이디/비밀번호찾기</span></a>
		</li>
		<li>
			<span>- 아직 회원이 아니신가요?</span>
			<a href="./register.php">회원 가입</a>
		</li>
	</ul>
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

	function flogin_submit(f){
		return true;
	}
</script>
<!-- } 로그인 끝 -->
