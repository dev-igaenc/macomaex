<?
//print_r($_REQUEST); exit;
include_once("./_common.php");

if($_REQUEST["resultcode"]=="00"){
	//빌링키 저장
	$pinfo=sql_fetch("select * from g5_membership_payment where order_no='{$orderid}' and uid='{$member['mb_id']}'"); // 기존 결제 예정 정보
	$minfo=sql_fetch("select * from g5_membership_manage where no='{$pinfo['membership_parent_no']}'"); // 맴버쉽 정보
	if(!$pinfo['uid']) {
		$sql="insert g5_membership_payment set ";
		$sql.="	uid='{$member['mb_id']}',";
		$sql.="	order_no='{$orderid}',";
		$sql.="	order_goods='',";
		$sql.="	price='',";
		$sql.="	complete_yn='Y',";
		$sql.="	complete_date=now(),";
		$sql.="	regdate=now(),";
		$sql.="	name='',";
		$sql.="	phone='',";
		$sql.="	email='',";
		$sql.="	tid='{$tid}',";
		$sql.="	mid='',";
		$sql.="	moid='',";
		$sql.="	pay_method='',";
		$sql.="	app_date='{$pgauthdate}',";
		$sql.="	appl_time='{$pgauthtime}',";
		$sql.="	buyer_name='',";
		$sql.="	buyer_tel='',";
		$sql.="	buyer_email='',";
		$sql.="	appl_num='',";
		$sql.="	card_num='{$cardno}',";
		$sql.="	card_Code='{$cardcd}',";
		$sql.="	card_check_flag='{$CheckFlag}',";
		$sql.="	card_appl_price='',";
		$sql.="	pco_order_no='',";
		$sql.="	acct_bankcode='',";
		$sql.="	cshr_result_code='',";
		$sql.="	cshr_applnum='',";
		$sql.="	cshr_type='',";
		$sql.="	acct_name='',";
		$sql.="	vact_num='',";
		$sql.="	vact_bankcode='',";
		$sql.="	vactbankname='',";
		$sql.="	vact_name='',";
		$sql.="	vact_inputname='',";
		$sql.="	vact_date='',";
		$sql.="	vact_time='',";
		$sql.="	hpp_num='',";
		$sql.="	membership_parent_no=''	";
	}else{
		$sql="update g5_membership_payment set ";
		$sql.="	complete_yn='Y',";
		$sql.="	complete_date=now(),";
		$sql.="	tid='{$tid}',";
		$sql.="	app_date='{$pgauthdate}',";
		$sql.="	pay_method='{$cardkind}',";
		$sql.="	appl_time='{$pgauthtime}',";
		$sql.="	card_num='{$cardno}',";
		$sql.="	card_Code='{$cardcd}',";
		$sql.="	card_check_flag='{$CheckFlag}',";
		$sql.="	billkey='{$billkey}'";
		$sql.="	where order_no='{$orderid}'	and uid='{$member['mb_id']}'";
	}
	sql_query($sql);

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

	$payinfo=sql_fetch("select * from g5_membership_payment where order_no='{$orderid}' and uid='{$member['mb_id']}'"); // 기존 결제 예정 정보
	
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
		uid='{$member['mb_id']}',
		order_no='{$orderid}',
		order_goods='{$payinfo['order_goods']}',
		price='{$payinfo['price']}',
		send='".addslashes($post_data)."',
		recive='".addslashes($response)."',
		regdate=now()";
	sql_query($sql);

	if($pay_result->resultCode=="00"){
	//결제 정보 저장
	$sql="insert into g5_membership_paybill select * from g5_membership_payment where order_no='{$orderid}' and uid='{$member['mb_id']}'";
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



	}else{
?>
	<script>
	alert("결제실패: <?=$pay_result->resultMsg?>");
	self.close();
	</script>
<?
	exit;
	}
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

	//기존 자료 삭제
	if($member['mb_id']) $msinfo_row = getUserMembership();
	if($member['mb_id']) sql_query("delete from g5_membership_status where uid='{$member['mb_id']}'");
	/*
		맴버쉽 변경시 이전되는 DATA처리

		$msinfo_row = getUserMembership();
		//맴버쉽이 없는 경우 무료로
		if(empty($msinfo_row[0])) 
	*/
	//print_r($msinfo_row);
	if($msinfo_row[0]) { 
		//기존 맴버쉽이 살아있는 경우 기존 남아있는 매칭, 전문가 컨설팅  이월시킴
		$old_membership=$msinfo_row[0];
		$old_matching_able_cnt=$old_membership['matching_able_cnt'];
		$expert_consulting_able_cnt=$old_membership['expert_consulting_able_cnt'];
		$minfo['matching_cnt'] = $minfo['matching_cnt'] + $old_matching_able_cnt;
		$minfo['expert_consulting_cnt'] =  $minfo['expert_consulting_cnt'] + $expert_consulting_able_cnt;

	}
	$sql="insert g5_membership_status set ";
	$sql.="uid='{$member['mb_id']}',";
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
	$sql.="membership_parent_no='{$pinfo['membership_parent_no']}'";
	//echo $sql;
	sql_query($sql);
	
	//맵버쉽 스테이터스 히스토리
	$sql="insert g5_membership_status_history set ";
	$sql.="uid='{$member['mb_id']}',";
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
	$sql.="membership_parent_no='{$pinfo['membership_parent_no']}',";
	$sql.="memo='사용자 결제\\n',";
	$sql.="ext_1='".G5_TIME_YMDHIS."\\n'";
	sql_query($sql);

	//셀렉티드 추가 처리
	if($minfo['selected_cnt']>0){

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
		$sql="insert g5_selected set ";
		$sql.="s_subject='{$member['mb_name']}',";
		$sql.="KEDCD='{$member['mb_company']}',";
		$sql.="s_membership='{$minfo['no']}',";
		$sql.="mb_id='{$member['mb_id']}',";
		$sql.="s_start='{$date_begin}',";
		$sql.="s_end='{$date_end}',";
		$sql.="s_info='',";
		$sql.="s_cnt='0',";
		$sql.="s_cnt_limit='{$minfo['selected_cnt']}',";
		$sql.="s_modi='',";
		$sql.="s_status='S',";
		$sql.="s_datetime=now()";
		sql_query($sql);
	}

?>
	<script>
	opener.location.href='/ko/my_plan.php';
	self.close();
	</script>
<?}else{?>
	<script>
	alert("결제실패: <?=$_REQUEST['resultmsg']?>");
	self.close();
	</script>
<?}?>
