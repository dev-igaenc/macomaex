<?

	//include_once  "inc/lib/base.class.php";
	//include_once  "inc/lib/login.check.php";
	

	//$isAble = isAblePayment($NO_USR_ID);
	//echo $isAble;


	
	$no		= $_REQUEST['id'];


	$query = "  select
					   a.no
					  ,a.month
					  ,a.title
					  ,a.price_origin
					  ,a.price_discount
					  ,a.discount_percent
					  ,a.open_cnt
					  ,a.matching_cnt
					  ,a.expert_consulting_cnt
					  ,a.expert_consulting_time
					  ,a.regdate
					  from nb_membership_manage a
					where a.no = $no";
	//echo $query;exit;
	$result=mysqli_query($connect, $query);
	$data=mysqli_fetch_array($result);
	
	$months = $data['month'];
	$amount = $data['price_discount'];


	//$memberInfo = getMemberInfo($NO_USR_ID);

	require_once('libs/INIStdPayUtil.php');
	$SignatureUtil = new INIStdPayUtil();

	
		$mid 			= macomaex_env("INICIS_STDPAY_TEST_MID", "");  								// 상점아이디			
		$signKey 	= macomaex_env("INICIS_STDPAY_TEST_SIGN_KEY", ""); 			// 웹 결제 signkey 
		/*
		$mid 			= macomaex_env("INICIS_STDPAY_LIVE_MID", "");  								// 상점아이디			
		$signKey 	= macomaex_env("INICIS_STDPAY_LIVE_SIGN_KEY", ""); 			// 웹 결제 signkey
	*/
	$mKey 	= $SignatureUtil->makeHash($signKey, "sha256");

	$timestamp 		= $SignatureUtil->getTimestamp();   			// util에 의해서 자동생성
	$use_chkfake	= "Y";											// PC결제 보안강화 사용 ["Y" 고정]	
	$orderNumber 	= "MC".$timestamp; 						// 가맹점 주문번호(가맹점에서 직접 설정)
	$price 			= $amount;        								// 상품가격(특수기호 제외, 가맹점에서 직접 설정)

	$params = array(
		"oid" => $orderNumber,
		"price" => $price,
		"timestamp" => $timestamp
	);

	$sign   = $SignatureUtil->makeSignature($params);

	$params = array(
		"oid" => $orderNumber,
		"price" => $price,
		"signKey" => $signKey,
		"timestamp" => $timestamp
	);

	$sign2   = $SignatureUtil->makeSignature($params);


	//$htmlData		= getNBData("policy");


?>

<!DOCTYPE HTML>

<html lang="ko">
<head>
	<?
		include_once "inc/inc_titlemeta.php";
		include_once "inc/inc_css.php";
		include_once "inc/inc_script.php";

		$depthnum = 1;
		$pagenum = 4;
	?>
	<script src="https://code.jquery.com/jquery-latest.min.js"></script>
	<!--테스트 JS<script language="javascript" type="text/javascript" src="https://stgstdpay.inicis.com/stdjs/INIStdPay.js" charset="UTF-8"></script>-->
	<!--운영 JS>--> <script language="javascript" type="text/javascript" src="https://stdpay.inicis.com/stdjs/INIStdPay.js" charset="UTF-8"></script> 
	<script type="text/javascript">
            function paybtn() {
				

				if($(".no_paymethod_sel").is(":checked") == false){
					alert("결제 수단을 선택해주세요");
					return;
				}

				if($("#buyername").val() == ""){
					alert("성명/기업명을 입력해주세요");
					return;
				}

				if($("#buyertel").val() == ""){
					alert("연락처를 입력해주세요");
					return;
				}

				if($("#buyeremail").val() == ""){
					alert("이메일 주소를 입력해주세요");
					return;
				}
				

				if($("#payPolicy").is(":checked") == false){
					alert("상품 구매 조건 확인에 동의해주세요");
					return;
				}
				

                INIStdPay.pay('SendPayForm_id');
            }
        </script>
</head>

<body>

	<div class="no-wrap">
		<!-- Header -->
		<?
			include_once "../../inc/header.php";
		?>

		<!-- Container -->
		<main id="no-main">
			<!-- Section:Navigation -->
			<? 
				include_once "../../inc/lnb_matching.php"; 
			?>
			<!-- Section:Contents -->
			<div class="no-payModal-wrap">
				<div class="no-payModal-box">
					<div class="no-payModal__top">
						<h2>결제 </h2>
						<!--
						<button class="no-payModal-close">
							<i class="fa-regular fa-xmark-large"></i>
						</button>-->
					</div>

					<form name="SendPayForm_id" id="SendPayForm_id" method="post" >
					<input type="hidden" name="version" value="1.0">
					<input type="hidden" id="gopaymethod" name="gopaymethod" value="Card">
					<input type="hidden" name="mid" value="<?php echo $mid ?>">
					<input type="hidden" name="oid" value="<?php echo $orderNumber ?>">
					<input type="hidden" name="price" value="1000">
					<input type="hidden" name="timestamp" value="<?php echo $timestamp ?>">
					<input type="hidden" name="use_chkfake" value="<?php echo $use_chkfake ?>">
					<input type="hidden" name="signature" value="<?php echo $sign ?>">
					<input type="hidden" name="verification" value="<?php echo $sign2 ?>">
					<input type="hidden" name="mKey" value="<?php echo $mKey ?>">
					<input type="hidden" name="currency" value="WON">
					<input type="hidden" name="goodname" value="멤버쉽 결제(<?=$data['title']?> <?=$months?>개월)">
					<!--input type="hidden" name="buyername" value="테스터">
					<input type="hidden" name="buyertel" value="01012345678">
					<input type="hidden" name="buyeremail" value="test@test.com"-->
					<input type="hidden" name="acceptmethod" value="HPP(1):below1000:centerCd(Y)">
					<input type="hidden" name="returnUrl" value="https://<?=$_SERVER[ "HTTP_HOST" ]?>/payment/payment.complete.php">
					<input type="hidden" name="closeUrl" value="https://<?=$_SERVER[ "HTTP_HOST" ]?>/payment/close.php">
					<input type="hidden" name="merchantData" value="<?=$no?>">

						<div class="no-payModal__bot">
							<div class="no-payProcess__item">
								<strong class="no-payProcess__title">선택한 요금제</strong>
								<div class="no-patProcess__price">
									<h3><?=$data['title']?>&nbsp;<?=$months?>개월</h3>
									<span>&#8361;<?=number_format($data['price_discount'],0)?></span>
								</div>
							</div>
							<div class="no-payProcess__item">
								<strong class="no-payProcess__title">결제 수단 선택</strong>
								<ul class="no-payMethod">
									<li>
										<label for="no-payMethod__item_1" class="no-payMethod__item">
											<input type="radio" id="no-payMethod__item_1" name="no-payMethod__item" class="no_paymethod_sel" onClick="doPayMethod('Card');">
											<span>
												신용카드
												<i class="fa-light fa-circle-check" aria-hidden="true"></i>
											</span>
										</label>
									</li>
									<li>
										<label for="no-payMethod__item_2" class="no-payMethod__item">
											<input type="radio" id="no-payMethod__item_2" name="no-payMethod__item" class="no_paymethod_sel" onClick="doPayMethod('Directbank');">
											<span>
												실시간 계좌이체
												<i class="fa-light fa-circle-check" aria-hidden="true"></i>
											</span>
										</label>
									</li>
									<li>
										<label for="no-payMethod__item_4" class="no-payMethod__item">
											<input type="radio" id="no-payMethod__item_4" name="no-payMethod__item" class="no_paymethod_sel" onClick="doPayMethod('vbank');">
											<span>
												무통장입금(가상계좌)
												<i class="fa-light fa-circle-check" aria-hidden="true"></i>
											</span>
										</label>
									</li>
								</ul>
							</div>
							<div class="no-payProcess__item">
								<strong class="no-payProcess__title">결제정보</strong>
								<div class="no-payInfo">
									<div class="no-payInfo__item">
										<span>성명/기업명</span>
										<input type="text" id="buyername" name="buyername" placeholder="성명/기업명을 입력하세요." value="<?=$memberInfo[0]['charger_name']?>">
									</div>
									<div class="no-payInfo__item">
										<span>연락처</span>
										<input type="text" id="buyertel" name="buyertel" placeholder="연락처를 입력하세요." value="<?=$memberInfo[0]['charger_hp']?>">
									</div>
									<div class="no-payInfo__item">
										<span>이메일</span>
										<input type="text" id="buyeremail" name="buyeremail" placeholder="이메일 주소를 입력하세요." >
									</div>
								</div>
								<div class="no-payPolicy">
									<?=$htmlData[0]['contents']?>
								</div>
							</div>
							<div class="no-payProcess__item">
								<div class="no-signUp-check__box">
									<input class="no-signUp-check__input" type="checkbox" name="payPolicy" id="payPolicy"/>
									<label class="no-signUp-check__label" for="payPolicy">
										위 상품의 구매 조건을 확인하였으며 결제에 동의합니다.
									</label>
								</div>
								<div class="no-signUp__button-box">
									<button type="button" class="no-signUp__button no-signUp__button--main" onClick="paybtn();">결제하기</button>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</main>

		<!-- Footer -->
		<?
			include_once "../../inc/footer.php";
		?>
	</div>
	<script type="text/javascript" src="./js/process.js?c=<?=$STATIC_ADMIN_JS_MODIFY_DATE?>"></script>	


</body>


</html>
