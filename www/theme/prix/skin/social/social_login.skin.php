<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if( ! $config['cf_social_login_use']) {     //소셜 로그인을 사용하지 않으면
    return;
}

$social_pop_once = false;

$self_url = G5_BBS_URL."/login.php";

//새창을 사용한다면
if( G5_SOCIAL_USE_POPUP ) {
    $self_url = G5_SOCIAL_LOGIN_URL.'/popup.php';
}

?>
		<div class="login-sns">
			<p class="h2">SNS 간편로그인</p>
			<?php if( social_service_check('naver') ) { //네이버 로그인을 사용한다면 ?>
			<button type="button" class="sns-naver" href="<?php echo $self_url;?>?provider=naver&amp;url=<?php echo $urlencode;?>"><img src="/theme/prix/skin/member/basic/img/icon_login_naver.svg" width="21"><strong>네이버</strong> 계정으로 로그인</button>
			<?php }     //end if ?>
			<?php if( social_service_check('kakao') ) {     //카카오 로그인을 사용한다면 ?>
			<button type="button" class="sns-kakao" href="<?php echo $self_url;?>?provider=kakao&amp;url=<?php echo $urlencode;?>"><img src="/theme/prix/skin/member/basic/img/icon_login_kakao.svg" width="27"><strong>카카오</strong> 계정으로 로그인</button>
			<?php }     //end if ?>
			<?php if( social_service_check('google') ) {     //구글 로그인을 사용한다면 ?>
			<button type="button" class="sns-google" href="<?php echo $self_url;?>?provider=google&amp;url=<?php echo $urlencode;?>"><img src="/theme/prix/skin/member/basic/img/icon_login_google.svg" width="23" style="margin-left:-9px;"><strong>구글</strong> 계정으로 로그인</button>
			<?php }     //end if ?>
		</div>


		<?php if( G5_SOCIAL_USE_POPUP && !$social_pop_once ){
		$social_pop_once = true;
		?>
		<script>
			jQuery(function($){
				$(".login-sns").on("click", "button", function(e){
					e.preventDefault();

					var pop_url = $(this).attr("href");
					var newWin = window.open(
						pop_url,
						"social_sing_on",
						"location=0,status=0,scrollbars=1,width=600,height=500"
					);

					if(!newWin || newWin.closed || typeof newWin.closed=='undefined')
						 alert('브라우저에서 팝업이 차단되어 있습니다. 팝업 활성화 후 다시 시도해 주세요.');

					return false;
				});
			});
		</script>
		<?php } ?>