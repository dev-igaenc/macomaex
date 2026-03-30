<?
	/*기업매칭*/
	$matching_MFR = "/pages/matching/matching_mfr.php";
	$matching_DISTI = "/pages/matching/matching_disti.php";
	$matching_OM = "/pages/matching/matching_om.php";
	$rate = "/pages/shorts/shorts.php";
	$promotion = "/pages/payment/payment.php";

	/*기업찾기*/
	$searching ="/pages/searching/searching.php";

	/*커뮤니티*/
	$community = "/pages/board/board.list.php?board_no=52";
	$boast = "/pages/board/board.list.php?board_no=55";
	$people = "/pages/board/board.list.php?board_no=56";
	$hobby = "/pages/board/board.list.php?board_no=57";
	$review = "/pages/board/board.list.php?board_no=58";
	
	/*서비스*/
	$service_matching = "/pages/service/matchingstation.php";
	$service_consulting = "/pages/service/consultingstation.php";
	$service_exit = "/pages/service/exitstation.php";

	/*매커멕스소개*/
	$e2eservice = "/pages/macomaex/e2eservice.php";
	$e2eprocess = "/pages/macomaex/e2eprocess.php";

	/*제조사안내*/
	$manufacture = "/pages/manufacture/manufacture.php";
	$MFR_form = "/pages/manufacture/mfr_form.php";
	$MFR_view = "/pages/manufacture/mfr_view.php";

	/*유통사안내*/
	$distribution = "/pages/distribution/distribution.php";
	$openmarket = "/pages/distribution/openmarket.php";
	$DISTI_form = "/pages/distribution/disti_form.php";
	$DISTI_view = "/pages/distribution/disti_view.php";
	$OM_form = "";
	$OM_view = "/pages/distribution/om_view.php";

	/*고객센터*/
	$cm_notice = "/pages/board/board.list.php?board_no=53";
	$cm_faq = "/pages/board/board.list.php?board_no=54";
	$cm_policy = "/pages/terms/policy.php";
	$cm_terms = "/pages/terms/terms.php";
	$contact = "/pages/terms/contact.php";

	/*유저페이지*/
	$signIn = "/pages/userprocess/signIn.php";
	$signUp = "/pages/userprocess/signUp.php";
	$mypage = "/pages/userprocess/mypage.php";
	$mypage_MFR = "/pages/userprocess/mypage_mfr.php";
	$mypage_DISTI = "/pages/userprocess/mypage_disti.php";
	$mypage_payment = "/pages/userprocess/mypage_payment.php";
	$mypage_membership = "/pages/userprocess/mypage_membership.php";
	$b2bSelect = "/pages/userprocess/b2bSelect.php";
	$matching_success = "/pages/userprocess/mymatching_success.php";
	$matching_request = "/pages/userprocess/mymatching_request.php";
	$matching_hold = "/pages/userprocess/mymatching_hold.php";
	$matching_like = "/pages/userprocess/mymatching_like.php"


?>

<header id="no-header">
	<!-- Header:Desktop -->
	<div class="no-header__inner">
		<!-- Header:left -->
		<div class="no-header__left">
			<a href="#none" class="no-header__btn">
				<i class="fa-solid fa-bars-staggered"></i>
			</a>
			<h1 class="no-header__logo">
				<a href="/">
					<img src="/resource/images/common/Macomaex_logo.png" alt="MACOMAEX로고" />
				</a>
			</h1>
			<ul class="no-header__menu">
				<li>
					<a href="<?=$matching_MFR?>">기업매칭</a>
				</li>
				
				<li>
					<a href="<?=$rate?>">기업홍보관</a>
				</li>
				
				<li>
					<a href="<?=$promotion?>">멤버십 요금제</a>
				</li>
				
				<li>
					<a href="<?=$boast?>">커뮤니티</a>
				</li>
			</ul>
		</div>
		<!-- Header:Right -->
		<div class="no-header__right">
			<ul class="no-header__login__items">
			<? if($NO_USR_ID){?>
				<li>
					<a href="/pages/userprocess/logout.php">로그아웃</a>
				</li>
				<li>
					<a href="<?=$mypage?>" class="active">마이페이지</a>
				</li>
			<? } else { ?>
				<li>
					<a href="<?=$signIn?>">로그인</a>
				</li>
				<li>
					<a href="<?=$signUp?>" class="active">회원가입</a>
				</li>
			<? } ?>
				
			</ul>
		</div>
	</div>
	<!-- Header:Mobile -->
	<div class="no-mobile__wrap">
		<div class="no-mobile__box">
			<!-- Mobile:Btn -->
			<a href="#none" class="no-mobile__btn">
				<i class="fa-solid fa-xmark"></i>
			</a>
			<!-- Mobile:Menu -->
			<nav class="no-mobile__nav">
				<ul class="no-mobile__menu">
					<li>
						<a href="<?=$matching_MFR?>">기업매칭</a>
					</li>
					
					<li>
						<a href="<?=$rate?>">기업홍보관</a>
					</li>
					<li>
						<a href="<?=$promotion?>">멤버십 요금제</a>
					</li>
				
					<li>
						<a href="<?=$boast?>">커뮤니티</a>
					</li>
				</ul>
				<ul class="no-mobile__login">
				<? if($NO_USR_ID){?>
					<li>
						<a href="/pages/userprocess/logout.php">로그아웃</a>
					</li>
					<li>
						<a href="<?=$mypage?>" class="active">마이페이지</a>
					</li>
				<? }else {?>
					<li>
						<a href="<?=$SignIn?>">로그인</a>
					</li>
					<li>
						<a href="<?=$SignUp?>" class="active">회원가입</a>
					</li>
				<? } ?>	
				</ul>
			</nav>
		</div>
	</div>
</header>