<?
	include_once  "../../inc/lib/base.class.php";
	include_once  "../../inc/lib/login.check.php";

	require_once('libs/INIStdPayUtil.php');
	require_once('libs/HttpClient.php');
	require_once('libs/properties.php');

	$util = new INIStdPayUtil();
	$prop = new properties();

	try {

		//#############################
		// 인증결과 파라미터 수신
		//#############################

		if (strcmp("0000", $_REQUEST["resultCode"]) == 0) {

			//############################################
			// 1.전문 필드 값 설정(***가맹점 개발수정***)
			//############################################

			$mid            = $_REQUEST["mid"];
			$signKey 	    = macomaex_env("INICIS_STDPAY_LIVE_SIGN_KEY", "");
			$timestamp      = $util->getTimestamp();
			$charset        = "UTF-8";
			$format         = "JSON";
			$authToken      = $_REQUEST["authToken"]; 
			$authUrl        = $_REQUEST["authUrl"];
			$netCancel      = $_REQUEST["netCancelUrl"];        
			$merchantData   = $_REQUEST["merchantData"];

			//echo "merchantData".$merchantData;exit;
			
			//##########################################################################
			// 승인요청 API url (authUrl) 리스트 는 properties 에 세팅하여 사용합니다.
			// idc_name 으로 수신 받은 센터 네임을 properties 에서 include 하여 승인요청하시면 됩니다.
			//##########################################################################
			$idc_name 	= $_REQUEST["idc_name"];
			$authUrl    = $prop->getAuthUrl($idc_name);

			if (strcmp($authUrl, $_REQUEST["authUrl"]) == 0) {

				//#####################
				// 2.signature 생성
				//#####################
				$signParam["authToken"] = $authToken;   // 필수
				$signParam["timestamp"] = $timestamp;   // 필수
				// signature 데이터 생성 (모듈에서 자동으로 signParam을 알파벳 순으로 정렬후 NVP 방식으로 나열해 hash)
				$signature = $util->makeSignature($signParam);

				$veriParam["authToken"] = $authToken;   // 필수
				$veriParam["signKey"]   = $signKey;     // 필수
				$veriParam["timestamp"] = $timestamp;   // 필수
				// verification 데이터 생성 (모듈에서 자동으로 signParam을 알파벳 순으로 정렬후 NVP 방식으로 나열해 hash)
				$verification = $util->makeSignature($veriParam);


				//#####################
				// 3.API 요청 전문 생성
				//#####################
				$authMap["mid"]          = $mid;            // 필수
				$authMap["authToken"]    = $authToken;      // 필수
				$authMap["signature"]    = $signature;      // 필수
				$authMap["verification"] = $verification;   // 필수
				$authMap["timestamp"]    = $timestamp;      // 필수
				$authMap["charset"]      = $charset;        // default=UTF-8
				$authMap["format"]       = $format;         // default=XML

				try {

					$httpUtil = new HttpClient();

					//#####################
					// 4.API 통신 시작
					//#####################

					$authResultString = "";
					if ($httpUtil->processHTTP($authUrl, $authMap)) {
						$authResultString = $httpUtil->body;

					} else {
						echo "Http Connect Error\n";
						echo $httpUtil->errormsg;

						throw new Exception("Http Connect Error");
					}

					//############################################################
					//5.API 통신결과 처리(***가맹점 개발수정***)
					//############################################################
					
					$resultMap = json_decode($authResultString, true);

					$resultCode = $resultMap['resultCode'];

					if($resultCode == "0000"){

						/* 필드 가이드 https://manual.inicis.com/pay/stdpay_pc.html */
						
						$tid					= $resultMap['tid'];
						$mid					= $resultMap['mid'];
						$MOID				= $resultMap['MOID'];
						$TotPrice			= $resultMap['TotPrice'];
						$goodName		= $resultMap['goodName'];
						$payMethod		= $resultMap['payMethod'];
						$applDate			= $resultMap['applDate'];
						$applTime			= $resultMap['applTime'];
						$buyerName		= $resultMap['buyerName'];
						$buyerTel			= $resultMap['buyerTel'];
						$buyerEmail		= $resultMap['buyerEmail'];

						/*신용카드*/
						$applNum					= $resultMap['applNum'];
						$CARD_Num				= $resultMap['CARD_Num'];
						$CARD_Code				= $resultMap['CARD_Code'];
						$CARD_CheckFlag		= $resultMap['CARD_CheckFlag'];
						$CARD_ApplPrice		= $resultMap['CARD_ApplPrice'];
						$PCO_OrderNo			= $resultMap['PCO_OrderNo'];

						/*계좌이체/가상계좌 */
						/* PC 가상계좌 / 폼페이 가상계좌 -> 가맹점관리자 내 입금통보수신URL 설정 모바일 가상계좌 -> 결제요청 시 P_NOTI_URL 설정 */
						$ACCT_BankCode		= $resultMap['ACCT_BankCode'];
						$CSHR_ResultCode	= $resultMap['CSHR_ResultCode'];
						$CSHR_ApplNum		= $resultMap['CSHR_ApplNum'];
						$CSHR_Type				= $resultMap['CSHR_Type'];
						$ACCT_Name			= $resultMap['ACCT_Name'];
						$VACT_Num				= $resultMap['VACT_Num'];
						$VACT_BankCode		= $resultMap['VACT_BankCode'];
						$vactBankName			= $resultMap['vactBankName'];
						$VACT_Name				= $resultMap['VACT_Name'];
						$VACT_InputName		= $resultMap['VACT_InputName'];
						$VACT_Date				= $resultMap['VACT_Date'];
						$VACT_Time				= $resultMap['VACT_Time'];
						$HPP_Num				= $resultMap['HPP_Num'];


						$membership_no		= $merchantData;
						


						$query = "insert into  nb_membership_payment (
							sitekey
							,uid
							,order_no
							,order_goods
							,price
							,complete_yn
							,complete_date
							,regdate
							,name
							,phone
							,email
							,tid
							,mid
							,moid
							,pay_method
							,app_date
							,appl_time
							,buyer_name
							,buyer_tel
							,buyer_email
							,appl_num
							,card_num
							,card_Code
							,card_check_flag
							,card_appl_price
							,pco_order_no
							,acct_bankcode
							,cshr_result_code
							,cshr_applnum
							,cshr_type
							,acct_name
							,vact_num
							,vact_bankcode
							,vactbankname
							,vact_name
							,vact_inputname
							,vact_date
							,vact_time
							,hpp_num
							,membership_parent_no
						) 
						values
						(
							 '$NO_SITE_UNIQUE_KEY' 
							,'$NO_USR_ID'
							,'$MOID'
							,'$goodName'
							,'$TotPrice'
							,'Y'
							,now()
							,now()
							,'$buyerName'
							,'$buyerTel'
							,'$buyerEmail'
							,'$tid'
							,'$mid'
							,'$MOID'
							,'$payMethod'
							,'$applDate'
							,'$applTime'
							,'$buyerName'
							,'$buyerTel'
							,'$buyerEmail'
							,'$applNum'
							,'$CARD_Num'
							,'$CARD_Code'
							,'$CARD_CheckFlag'
							,'$CARD_ApplPrice'
							,'$PCO_OrderNo'
							,'$ACCT_BankCode'
							,'$CSHR_ResultCode'
							,'$CSHR_ApplNum'
							,'$CSHR_Type'
							,'$ACCT_Name'
							,'$VACT_Num'
							,'$VACT_BankCode'
							,'$vactBankName'
							,'$VACT_Name'
							,'$VACT_InputName'
							,'$VACT_Date'
							,'$VACT_Time'
							,'$HPP_Num'
							, $membership_no
			
						)";
						//echo $query;
						$result = mysql_query($query);


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
										where a.no = $membership_no";
						//echo $query;
						$result=mysql_query($query);
						$data=mysql_fetch_array($result);

						$month								= $data['month'];
						$title									= $data['title'];
						$price_origin						= $data['price_origin'];
						$price_discount					= $data['price_discount'];
						$open_cnt							= $data['open_cnt'];
						$matching_cnt					= $data['matching_cnt'];
						$expert_consulting_cnt		= $data['expert_consulting_cnt'];
						$expert_consulting_time		= $data['expert_consulting_time'];

						$date_begin	= date("Y-m-d");
						//$timestamp	= strtotime("+".$month." months");
						//$date_end		= date("Y-m-d", $timestamp);


						$date_end = endCycle($date_begin, $month);

						$query = "delete from nb_membership_status where uid = '$NO_USR_ID'";
						mysql_query($query);

						$query = "insert into  nb_membership_status (
							sitekey
							,uid
							,order_no
							,date_begin
							,date_end
							,month
							,open_able_cnt
							,open_cnt
							,matching_able_cnt
							,matching_cnt
							,expert_consulting_able_cnt
							,expert_consulting_cnt
							,regdate
							,membership_parent_no
						) 
						values
						(
							 '$NO_SITE_UNIQUE_KEY' 
							,'$NO_USR_ID'
							,'$MOID'
							,'$date_begin'
							,'$date_end'
							, $month
							, $open_cnt
							, $open_cnt
							, $matching_cnt
							, $matching_cnt
							, $expert_consulting_cnt
							, $expert_consulting_cnt
							, now()
							, $membership_no
			
						)";
						//echo $query;
						$result = mysql_query($query);



						$history_text = "열람 ".$open_cnt."건 / 매칭 ".$matching_cnt."건  / 전문가 컨설팅 ".$expert_consulting_cnt."건";
						
						$query = "insert into  nb_membership_history (
							sitekey
							,uid
							,kind
							,count_number
							,regdate
							,order_no
						) 
						values
						(
							 '$NO_SITE_UNIQUE_KEY' 
							,'$NO_USR_ID'
							,'ADD'
							,'$history_text'
							, now()
							, '$MOID'
			
						)";
						//echo $query;
						$result = mysql_query($query);



					}else{
						

						echo "<script>alert('결제가 실패되었습니다.'".$resultMsg.");location.href = '/pages/payment/payment.php';</script>";
						exit;
					}


				} catch (Exception $e) {
					//    $s = $e->getMessage() . ' (오류코드:' . $e->getCode() . ')';
					//####################################
					// 실패시 처리(***가맹점 개발수정***)
					//####################################
					//---- db 저장 실패시 등 예외처리----//
					$s = $e->getMessage() . ' (오류코드:' . $e->getCode() . ')';
					echo $s;

					//#####################
					// 망취소 API
					//#####################

					$netcancelResultString = ""; // 망취소 요청 API url(고정, 임의 세팅 금지)
					$netCancel    = $prop->getNetCancel($idc_name);
					
					if (strcmp($netCancel, $_REQUEST["netCancelUrl"]) == 0) {

						if ($httpUtil->processHTTP($netCancel, $authMap)) {
							$netcancelResultString = $httpUtil->body;
						} else {
							echo "Http Connect Error\n";
							echo $httpUtil->errormsg;
	
							throw new Exception("Http Connect Error");
						}
	
						echo "<br/>## 망취소 API 결과 ##<br/>";
						
						/*##XML output##*/
						//$netcancelResultString = str_replace("<", "&lt;", $$netcancelResultString);
						//$netcancelResultString = str_replace(">", "&gt;", $$netcancelResultString);
	
						// 취소 결과 확인
						echo "<p>". $netcancelResultString . "</p>";
					}
				}

			} else {
				echo "authUrl check Fail\n";
				exit;
			}

		} else {

		}

	} catch (Exception $e) {
		$s = $e->getMessage() . ' (오류코드:' . $e->getCode() . ')';
		echo $s;
		echo "<script>alert('결제가 실패되었습니다.'".$e->getCode().");location.href = '/pages/payment/payment.php';</script>";
		exit;
	}
?>

<!DOCTYPE HTML>

<html lang="ko">
<head>

	<?
		include_once "../../inc/inc_titlemeta.php";
		include_once "../../inc/inc_css.php";
		include_once "../../inc/inc_script.php";

		$depthnum = 1;
		$pagenum = 4;
	?>
	
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
			<section class="no-payment-complete">
				<div class="no-container">
					<div class="no-payment-complete__inner">

						<div class="no-payment-complete-status">
							<div class="no-payment-complete-check">
								<i class="fa-regular fa-check"></i>
							</div>

							<div class="no-payment-complete-title">
								<h2><b><?=$goodName?></b> 상품의 결제가 완료되었습니다.</h2>
							</div>
						</div>

						<div class="no-payment-complete-info">
							<ul>
								<li>
									<h5>번호</h5>
									<p><?=$MOID?></p>
								</li>
								<li>
									<h5>결제승인일자</h5>
									<p><?=$applDate?> <?=$applTime?></p>
								</li>
								<li>
									<h5>결제금액</h5>
									<b><?=number_format($TotPrice, 0)?>원</b>
								</li>
								<li>
									<h5>서비스 상품</h5>
									<p><?=$goodName?></p>
								</li>
								<li>
									<h5>상태</h5>
									<p>결제성공</p>
								</li>
							</ul>
							<div class="no-payment-complete-detail">
								<button type='button' class="bill" onClick="location.href='/pages/userprocess/mypage_payment.php';">확인</button>
							</div>
						</div>
					</div>
				</div>
			</section>
		</main>

		<script src='/resource/js/payment.js'  defer></script>	
		<!-- Footer -->
		<?
			include_once "../../inc/footer.php";
		?>
	</div>

	<? include_once  dirname(__FILE__)."/inc/lib/popup.inc.php"; ?>
	<? include_once  dirname(__FILE__)."/inc/lib/popup.m.inc.php"; ?>

</body>
</html>
