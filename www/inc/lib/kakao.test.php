<?
	include_once  $_SERVER['DOCUMENT_ROOT']."/inc/lib/base.class.php";

	$kakaoInfo = array();
	$kakaoInfo['idType'] = "M";
	$kakaoInfo['id'] = "mincar20";
	$kakaoInfo['apiKey'] = "cba9da1ce8e940e4f21a89bdf51ff72e";
	$kakaoInfo['senderKey'] = "63ab04465cb9bc7331479e22cc1da8b85896f9d7";
	$kakaoInfo['templateCode'] = "MC00001";
	$kakaoInfo['templateMsg'] = "[매커멕스] 홍길동님 회원가입을 축하드립니다.";
	$kakaoInfo['resend'] = "SMS";
	
	$resendData = array();
	$resendData['msg'] = "[매커멕스] 홍길동님 회원가입을 축하드립니다.";
	$resendData['callNumber'] = "01046749171";

	$kakaoInfo['resendData'] = $resendData;
	
	$rcvData = array();
	$rcvData['rcvId'] = "";
	$rcvData['rcvNumber'] = "01046749171";

	$kakaoInfo['rcvData'] = $rcvData;


	$rtn = httpPostNanoKakao($kakaoInfo);
	print_r($rtn);



?>