<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>

	<style>
	html { background-color:#eee; padding:0; margin:0; overflow:hidden; }
	html, body{height:100%; min-height:100%;}
	#mb_login {width:640px; padding:50px 80px; box-sizing:border-box; margin: 8vw auto 10vw; text-align:center; }
	#mb_login #login_box{font-size:0; line-height:0; }
	#mb_login #login_box .login_tlt { font:500 12px 'Montserrat', sans-serif; letter-spacing:8px; }
	#mb_login #login_box .login_tlt span { font:600 35px 'Noto Sans KR', sans-serif; letter-spacing:-0.05em; display:block; margin-top:20px;  }
	#mb_login #login_box>ul {margin-top:40px; padding-left:0;}
	#mb_login #login_box>ul>li {padding-bottom:15px;}
	#mb_login #login_box>ul>li input {vertical-align:middle; font:400 14px 'Montserrat', sans-serif;}
	#mb_login #login_box .login_btn {}
	#mb_login #login_box .login_btn a{display:block; width:100%; height:45px; border:1px solid #0ca0ad; background-color:#00b0bf; text-align:center;  font-size:16px;  font-family:'Noto Sans KR', sans-serif; font-weight:normal; color:#fff; line-height:45px; text-decoration: none;}
	.login_input{width:100%; height:45px; line-height:45px; padding:8px; box-sizing:border-box; background-color:#fff; border:1px solid #e4e4e4; color:#464646;}
	.pass_check{margin-left:30px; }
	#mb_login .copy { font-family:'Montserrat', 'Noto Sans KR', sans-serif; text-align:center; margin:35px auto 0;}
	#mb_login .copy a{ font-family:'Montserrat', 'Noto Sans KR', sans-serif;}
	#mb_login .copy a:after { content:"l"; color:#aeaeae; padding-left:10px; margin-right:5px;}
	#mb_login .copy img {margin:0 10px;}
	#mb_login .copy span { font:500 16px 'Montserrat', 'Noto Sans KR', sans-serif; color:#fb6767}
	.txt_b { font:500 12px 'Noto Sans KR', sans-serif; color:#222; }
	#mb_login .right_con .btn { font:500 12px 'Noto Sans KR', sans-serif; color:#000; display:inline-block; padding:5px 8px; border:1px solid #e3e3e3; background:#e3e3e3; }

	footer{
		background: #333;
		border-top: 1px solid #efefef;
		text-align: center;
		padding: 20px 0;
		width: 100%;
		position: fixed;
		bottom: 0;
		left: 0;
		z-index: 98;
	}
	footer p { color: #fff; }
	@media all and (max-width:639px) {
		#login {width:80%; padding:20px;}
		#login .copy span{display:block; margin-top:8px;}
	}
	</style>

	<script language="javascript">
	<!--
	function sendit() {
		var form=document.flogin;
		if(form.mb_id.value=="") {
			alert("관리자 아이디를 입력해 주십시오.");
			form.mb_id.focus();
			return;
		}
		if(form.mb_password.value=="") {
			alert("관리자 비밀번호를 입력해 주십시오.");
			form.mb_password.focus();
			return;
		}
		document.flogin.submit();
	}

	function inputSendit() {
		if(event.keyCode==13) {
			sendit();
		}
	}
	//-->
	</script>

	<div id="mb_login">
		<form name="flogin" action="<?php echo $login_action_url ?>" onsubmit="return flogin_submit(this);" method="post">
		<input type="hidden" name="url" value="<?php echo $login_url ?>">
		<p><img src="/img/logo.svg" alt="<?php echo $config['cf_title']; ?>"></p>
		<div id="login_box">
			<p class="login_tlt"><br>ADMINISTRATOR LOGIN<br/><span>관리자 로그인</span></p>
			<ul>
				<li><input type="text" id="id" name="mb_id" class="login_input" maxlength="15" placeholder="아이디"/></li>
				<li><input type="password" id="pw" name="mb_password" onKeyDown="inputSendit();" class="login_input" maxlength="15" placeholder="비밀번호"/></li>
			</ul>
			<p class="login_btn"><a href="javascript:sendit();">로그인</a></p>
		</div>
		</form>
	</div>

	<footer>
		<p>COPYRIGHT (C) <?=date("Y")?>. ALL RIGHTS RESERVED.</p>
	</footer>

<script>
	document.flogin.mb_id.focus();


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
