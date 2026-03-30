<?php
include_once('/home/hosting_users/ice_macomaex/www/common.php');
//include_once("./_common.php");
//require_once('./ko/libs/INIStdPayUtil.php');

	class INIStdPayUtil	{
		function getTimestamp()	{
			// timezone 을 설정하지 않으면 getTimestapme() 실행시 오류가 발생한다.
			// php.ini 에 timezone 설정이 되어 잇으면 아래 코드가 필요없다. 
			// php 5.3 이후로는 반드시 timezone 설정을 해야하기 때문에 아래 코드가 필요없을 수 있음. 나중에 확인 후 수정필요.
			// 이니시스 플로우에서 timestamp 값이 중요하게 사용되는 것으로 보이기 때문에 정확한 timezone 설정후 timestamp 값이 필요하지 않을까 함.
			date_default_timezone_set('Asia/Seoul');
			$date = new DateTime();
			
			$milliseconds = round(microtime(true) * 1000);	
			$tempValue1 = round($milliseconds/1000);		//max integer 자릿수가 9이므로 뒤 3자리를 뺀다
			$tempValue2 = round((float)microtime(false) * 1000);	//뒤 3자리를 저장
			switch (strlen($tempValue2)) {
				case '3':
					break;
				case '2':
					$tempValue2 = "0".$tempValue2;
					break;
				case '1':
					$tempValue2 = "00".$tempValue2;
					break;
				default:
					$tempValue2 = "000";
					break;
			}
			
			return "".$tempValue1.$tempValue2;
		}
	
		/*
		 //*** 위변조 방지체크를 signature 생성 ***
		
		 mid, price, timestamp 3개의 키와 값을	
		 key=value 형식으로 하여 '&'로 연결한 하여 SHA-256 Hash로 생성 된값	
		 ex) mid=INIpayTest&price=819000&timestamp=2012-02-01 09:19:04.004
		
		 * key기준 알파벳 정렬
		 * timestamp는 반드시 signature생성에 사용한 timestamp 값을 timestamp input에 그데로 사용하여야함
		 */	
		function makeSignature($signParam) {
			ksort($signParam);
			$string = "";
			foreach ($signParam as $key => $value) {
				$string .= "&$key=$value";
			}		
			$string = substr($string, 1); // remove leading "&"
			
			$sign = $this->makeHash($string, "sha256");
			//$this->hash256($string);
					
			return $sign;
		}
		
		function makeHash($data, $alg) {
			// $s = hash_hmac('sha256', $data, 'secret', true);
			// return base64_encode($s);
			
			$ret = openssl_digest($data, $alg);
			return $ret;
		}
		
		//결제보안 추가 인증.
		function makeSignatureAuth($parameters) {

			if ($parameters == null || sizeof($parameters) == 0) {
				throw new Exception("<p>Parameters can not be empty.</P>");
			}

			$stringToSign = "";															//반환용 text
	        $mid          = $parameters["mid"];                                                //mid
			$tstamp       = $parameters["tstamp"];												//auth timestamp
			$MOID         = $parameters["MOID"];												//OID
			$TotPrice     = $parameters["TotPrice"];											//total price
			$tstampKey    = substr($parameters["tstamp"], strlen($parameters["tstamp"]) - 1);	// timestamp 마지막 자리 1자리 숫자
			
			switch (intval($tstampKey)){
			case 1 :
				$stringToSign = "MOID=" . $MOID . "&mid=" . $mid . "&tstamp=" . $tstamp ;
				break;
			case 2 :
				$stringToSign = "MOID=" . $MOID . "&tstamp=" . $tstamp . "&mid=" . $mid ;
				break;
			case 3 :
				$stringToSign = "mid=" . $mid . "&MOID=" . $MOID . "&tstamp=" . $tstamp ;
				break;
			case 4 :
				$stringToSign = "mid=" . $mid . "&tstamp=" . $tstamp . "&MOID=" . $MOID ;
				break;
			case 5 :
				$stringToSign = "tstamp=" . $tstamp . "&mid=" . $mid . "&MOID=" . $MOID ;
				break;
			case 6 :
				$stringToSign = "tstamp=" . $tstamp . "&MOID=" . $MOID . "&mid=" . $mid ;
				break;
			case 7 :
				$stringToSign = "TotPrice=" . $TotPrice . "&mid=" . $mid . "&tstamp=" . $tstamp ;
				break;
			case 8 :
				$stringToSign = "TotPrice=" . $TotPrice . "&tstamp=" . $tstamp . "&mid=" . $mid ;
				break;
			case 9 :
				$stringToSign = "TotPrice=" . $TotPrice . "&MOID=" . $MOID . "&tstamp=" . $tstamp ;
				break;
			case 0 :
				$stringToSign = "TotPrice=" . $TotPrice . "&tstamp=" . $tstamp . "&MOID=" . $MOID ;
				break;
			}

	        $signature = hash("sha256", $stringToSign);            				// sha256 처리하여 hash 암호화
	        //$signature = $this->makeHash($stringToSign, "sha256");            // sha256 처리하여 hash 암호화

			return $signature;
		}
		
	}

$SignatureUtil = new INIStdPayUtil();

	//정기결제 실행
	//서버에서만 실행
	/*
	if($_SERVER["REMOTE_ADDR"]!="183.111.182.249") {
		exit;
	}
	*/
	$today = date("Y-m-d");

	//맴버쉽 스테이트 설정
	$date_begin = date("Y-m-d");
	$timestamp = strtotime("+1 months");
	$date_end=date("Y-m-d H:i:s", $timestamp);

	$e_month=date("m", $timestamp );

	$tmpmonth=date("Y-m")."-01";
	$tmpmonth_stamp=strtotime($tmpmonth);
	$nextstamp = strtotime("+1 months",$tmpmonth_stamp);
	$nextmonth = date("m",$nextstamp);

	//다음달 같은 일자가 없는 경우 마지막일자로
	if($nextmonth!=$e_month) {
		$tmpmonth=date("Y-m")."-01";
		$tmpmonth_stamp=strtotime($tmpmonth);
		$nextstamp = strtotime("+1 months",$tmpmonth_stamp);
		$nextmonth = date("Y-m",$nextstamp);
		$lastDay = DATE('t', strtotime($nextmonth )); //마지말일 구하기
		$date_end=$nextmonth."-".$lastDay;
		//$timestamp = strtotime($date_end);
	}
	//만료일 지난 맴버쉽 종료로 바꾸기
	$sql = " update g5_membership_status SET STATUS='E' where date_end < '{$today}' AND (status='S' OR status='T')";
	sql_query($sql);

	//시간지난 셀렉티드 중지
	sql_query("UPDATE g5_selected_data set s_status='E' WHERE DATE(s_start)<='{$today}' AND DATE(s_end)<'{$today}' AND s_status='S'");
	sql_query("
		UPDATE g5_selected AS gs
		JOIN (
			SELECT s_parent, COUNT(*) AS cnt
			FROM g5_selected_data
			WHERE s_status = 'S'
			GROUP BY s_parent
		) AS sd
		ON gs.s_no = sd.s_parent
		SET gs.s_cnt = sd.cnt, gs.s_modi = NOW()
	");
	
	//오늘 만료일 이용자 검색
	$sql = " select * from g5_membership_status A left join g5_membership_paybill B on A.order_no=B.order_no where A.date_end='{$today}' and A.status='S'";
	$result = sql_query($sql);
	for($i = 0; $row = sql_fetch_array($result); $i++){
		//신규오더 넘버부여
		$timestamp = $SignatureUtil->getTimestamp();
		if(_test_pay=='Y') $orderid= "DemoTest_". $timestamp ;
		else $orderid= "M_". $timestamp ;
		//echo $orderid;

		//빌링키로 결제 진행
		$key = _key;
		$iv = _iv;
		$mid = _mid;
		$type = "billing";
		$paymethod = "Card";
		$timestamp = date("YmdHis");
		$clientIp = _clientIp;

		$postdata = array();
		$postdata["mid"] = $mid;
		$postdata["type"] = $type;
		$postdata["paymethod"] = $paymethod;
		$postdata["timestamp"] = $timestamp;
		$postdata["clientIp"] = $clientIp;

		$payinfo=sql_fetch("select * from g5_membership_payment where order_no='{$row[order_no]}'"); // 기존 결제 예정 정보
		$minfo=sql_fetch("select * from g5_membership_manage where no='{$payinfo['membership_parent_no']}'"); // 맴버쉽 정보
		
		//// Data 상세
		$detail = array();
		$detail["url"] = "www.inicis.com";
		$detail["moid"] = $mid."_".$timestamp;
		$detail["goodName"] = $payinfo['order_goods'];
		$detail["buyerName"] = $payinfo['name'];
		$detail["buyerEmail"] = $payinfo['email'];
		$detail["buyerTel"] = $payinfo['phone'];
		$detail["price"] = $payinfo['price'];
		$detail["billKey"] = $payinfo['billkey'];
		$detail["authentification"] = "00";
		$detail["cardQuota"] = "00";
		$detail["quotaInterest"] = "0";
		$postdata["data"] = $detail;
		$details = str_replace('\\/', '/', json_encode($detail, JSON_UNESCAPED_UNICODE));
		//// Hash Encryption
		$plainTxt = $key.$mid.$type.$timestamp.$details;
		$hashData = hash("sha512", $plainTxt);
		$postdata["hashData"] = $hashData;
		$post_data = json_encode($postdata, JSON_UNESCAPED_UNICODE);

		$url = "https://iniapi.inicis.com/v2/pg/billing";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=utf-8'));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);


		$response = curl_exec($ch);
		curl_close($ch);

		$pay_result=json_decode($response);

		//결제로그 기록
		$sql="insert g5_membership_payment_log set 
			uid='{$payinfo['uid']}',
			order_no='{$orderid}',
			order_goods='{$payinfo['order_goods']}',
			price='{$payinfo['price']}',
			send='".addslashes($post_data)."',
			recive='".addslashes($response)."',
			regdate=now()";
		sql_query($sql);

		if($pay_result->resultCode=="00"){
			//결제성공이면

			//기존 자료 삭제
			 sql_query("delete from g5_membership_status where uid='{$payinfo['uid']}'");
			$sql="insert g5_membership_status set ";
			$sql.="uid='{$payinfo['uid']}',";
			$sql.="order_no='{$orderid}',";
			$sql.="date_begin='{$date_begin}',";
			$sql.="date_end='{$date_end}',";
			$sql.="month='1',";
			$sql.="open_able_cnt='{$minfo['open_cnt']}',";
			$sql.="open_cnt='{$minfo['open_cnt']}',";
			$sql.="matching_able_cnt='{$minfo['matching_cnt']}',";
			$sql.="matching_cnt='{$minfo['matching_cnt']}',";
			$sql.="expert_consulting_able_cnt='{$minfo['expert_consulting_cnt']}',";
			$sql.="expert_consulting_cnt='{$minfo['expert_consulting_cnt']}',";
			$sql.="selected_able_cnt='{$minfo['selected_cnt']}',";
			$sql.="selected_cnt='{$minfo['selected_cnt']}',";
			$sql.="regdate=now(),";
			$sql.="status='S',";
			$sql.="membership_parent_no='{$row['membership_parent_no']}'";
			//echo $sql."<br>";
			sql_query($sql);

			//맴버쉽 스테이터스 히스토리 기록
			$sql="insert g5_membership_status_history set ";
			$sql.="uid='{$payinfo['uid']}',";
			$sql.="order_no='{$orderid}',";
			$sql.="date_begin='{$date_begin}',";
			$sql.="date_end='{$date_end}',";
			$sql.="month='1',";
			$sql.="open_able_cnt='{$minfo['open_cnt']}',";
			$sql.="open_cnt='{$minfo['open_cnt']}',";
			$sql.="matching_able_cnt='{$minfo['matching_cnt']}',";
			$sql.="matching_cnt='{$minfo['matching_cnt']}',";
			$sql.="expert_consulting_able_cnt='{$minfo['expert_consulting_cnt']}',";
			$sql.="expert_consulting_cnt='{$minfo['expert_consulting_cnt']}',";
			$sql.="selected_able_cnt='{$minfo['selected_cnt']}',";
			$sql.="selected_cnt='{$minfo['selected_cnt']}',";
			$sql.="regdate=now(),";
			$sql.="status='S',";
			$sql.="membership_parent_no='{$row['membership_parent_no']}'";
			//echo $sql."<br>";
			sql_query($sql);

			$sql="insert g5_membership_payment set ";
			$sql.="	uid='{$payinfo['uid']}',";
			$sql.="	order_no='{$orderid}',";
			$sql.="	order_goods='{$payinfo['order_goods']}',";
			$sql.="	price='{$payinfo['price']}',";
			$sql.="	complete_yn='Y',";
			$sql.="	complete_date=now(),";
			$sql.="	regdate=now(),";
			$sql.="	name='{$payinfo['name']}',";
			$sql.="	phone='{$payinfo['phone']}',";
			$sql.="	email='{$payinfo['email']}',";
			$sql.="	tid='{$payinfo['tid']}',";
			$sql.="	mid='{$payinfo['mid']}',";
			$sql.="	moid='{$payinfo['moid']}',";
			$sql.="	pay_method='{$payinfo['pay_method']}',";
			$sql.="	app_date='{$payinfo['app_date']}',";
			$sql.="	appl_time='{$payinfo['appl_time']}',";
			$sql.="	buyer_name='{$payinfo['buyer_name']}',";
			$sql.="	buyer_tel='{$payinfo['buyer_tel']}',";
			$sql.="	buyer_email='{$payinfo['buyer_email']}',";
			$sql.="	appl_num='{$payinfo['appl_num']}',";
			$sql.="	card_num='{$payinfo['card_num']}',";
			$sql.="	card_Code='{$payinfo['card_Code']}',";
			$sql.="	card_check_flag='{$payinfo['card_check_flag']}',";
			$sql.="	card_appl_price='{$payinfo['card_appl_price']}',";
			$sql.="	pco_order_no='{$payinfo['pco_order_no']}',";
			$sql.="	acct_bankcode='{$payinfo['acct_bankcode']}',";
			$sql.="	cshr_result_code='{$payinfo['cshr_result_code']}',";
			$sql.="	cshr_applnum='{$payinfo['cshr_applnum']}',";
			$sql.="	cshr_type='{$payinfo['cshr_type']}',";
			$sql.="	acct_name='{$payinfo['acct_name']}',";
			$sql.="	vact_num='{$payinfo['vact_num']}',";
			$sql.="	vact_bankcode='{$payinfo['vact_bankcode']}',";
			$sql.="	vactbankname='{$payinfo['vactbankname']}',";
			$sql.="	vact_name='{$payinfo['vact_name']}',";
			$sql.="	vact_inputname='{$payinfo['vact_inputname']}',";
			$sql.="	vact_date='{$payinfo['vact_date']}',";
			$sql.="	vact_time='{$payinfo['vact_time']}',";
			$sql.="	hpp_num='{$payinfo['hpp_num']}',";
			$sql.="	membership_parent_no='{$payinfo['membership_parent_no']}',";
			$sql.="	billkey='{$payinfo['billkey']}',";
			$sql.="	status='{$payinfo['status']}'";
			sql_query($sql);

			//결제 정보 저장
			$sql="insert into g5_membership_paybill select * from g5_membership_payment where order_no='{$orderid}'";
			sql_query($sql);

			$sql="update g5_membership_paybill set 
			tid='{$pay_result->tid}',
			card_appl_price='{$pay_result->price}', 
			app_date='{$pay_result->payDate}',
			appl_time='{$pay_result->payTime}',
			appl_num='{$pay_result->payAuthCode}',
			card_Code='{$pay_result->cardCode}',
			card_check_flag='{$pay_result->checkFlg}',
			acct_name='{$pay_result->prtcCode}',
			vact_num='{$pay_result->cardQuota}',
			card_num='{$pay_result->cardNumber}'
			where order_no='{$orderid}'";
			sql_query($sql);
			

			//셀렉티드 추가 처리
			if($minfo[selected_cnt]>0){

				$timestamp = strtotime("+1 months");
				$date_end=date("Y-m-d H:i:s", $timestamp);

				$e_month=date("m", $timestamp );

				$tmpmonth=date("Y-m")."-01";
				$tmpmonth_stamp=strtotime($tmpmonth);
				$nextstamp = strtotime("+1 months",$tmpmonth_stamp);
				$nextmonth = date("m",$nextstamp);

				//다음달 같은 일자가 없는 경우 마지막일자로
				if($nextmonth!=$e_month) {
					$tmpmonth=date("Y-m")."-01";
					$tmpmonth_stamp=strtotime($tmpmonth);
					$nextstamp = strtotime("+1 months",$tmpmonth_stamp);
					$nextmonth = date("Y-m",$nextstamp);
					$lastDay = DATE('t', strtotime($nextmonth )); //마지말일 구하기
					$date_end=$nextmonth."-".$lastDay;
				}
				$KEDCD=sql_fetch("select mb_company as kedcd from g5_member where mb_id='{$payinfo['uid']}'");
				$sql="insert g5_selected set ";
				$sql.="s_subject='{$payinfo['name']}',";
				$sql.="KEDCD='{$KEDCD[kedcd]}',";
				$sql.="s_membership='{$row['membership_parent_no']}',";
				$sql.="mb_id='{$payinfo['uid']}',";
				$sql.="s_start='{$date_begin}',";
				$sql.="s_end='{$date_end}',";
				$sql.="s_info='',";
				$sql.="s_cnt='0',";
				$sql.="s_cnt_limit='{$minfo[selected_cnt]}',";
				$sql.="s_modi='',";
				$sql.="s_status='S',";
				$sql.="s_datetime=now()";
				sql_query($sql);
			}
			
			
		}
		
	}

?>