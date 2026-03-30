<?php



function getMembership($no){
	
	global $NO_SITE_UNIQUE_KEY, $connect;
	

	
	$query = "  select 
						a.no
						,a.sitekey
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
	$result=mysqli_query($connect ,$query);
	$r = array(); 
	while($row = mysqli_fetch_assoc($result)) { 
		$r[] = $row; 
	} 
	if($result){
		mysqli_free_result( $result );
	}
	return $r;

}


function getPaymentInfo($oid){

	global $NO_SITE_UNIQUE_KEY, $connect;
	
	$query = "  select 
						no
						,sitekey
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
			  from nb_membership_payment a 
			  where order_no = '$oid' ";
	$result=mysqli_query($connect ,$query);
	$r = array(); 
	while($row = mysqli_fetch_assoc($result)) { 
		$r[] = $row; 
	} 
	if($result){
		mysqli_free_result( $result );
	}
	return $r;

}

function isAblePayment($id){
	
	global $NO_SITE_UNIQUE_KEY, $connect;
	$nowdate = date("Y-m-d");
	$isAble = "Y";

	$query = "  select
							no
							,sitekey
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
					from nb_membership_status 
					where uid = '$id'
					and date_end >= '$nowdate'
					";
	$result=mysqli_query($connect ,$query);
	$data=mysqli_fetch_array($result);

	if($data){
		if($data['open_cnt'] == 0 && $data['matching_cnt'] == 0)
			$isAble = "Y";
		else
			$isAble = "N";
	}
	
	return $isAble;
}


function isOpenAble($id){
	
	global $NO_SITE_UNIQUE_KEY, $connect;
	$nowdate = date("Y-m-d");
	$isAble = "N";

	$query = "  select
							no
							,sitekey
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
					from nb_membership_status 
					where uid = '$id'
					and date_end >= '$nowdate'
					";
	$result=mysqli_query($connect ,$query);
	$data=mysqli_fetch_array($result);
	//echo $query;exit;
	if($data){
		if($data['open_cnt'] <= 0)
			$isAble = "N";
		else
			$isAble = "Y";
	}
	
	return $isAble;
}


function doNotOpenAlert(){
	echo "<script>";
	echo "alert('멤버쉽 결제 기간이 만료되었거나, 멤버쉽 열람 잔여 횟수가 없습니다.');";
	echo "location.href = '/pages/payment/payment.php';";
	echo "</script>";
	exit;
}

function doOpenHistory($id, $no){

	global $NO_SITE_UNIQUE_KEY, $connect;


	$query = "  select
							no
							,sitekey
							,uid
							,kind
							,count_number
							,regdate
							,order_no
							,open_parent_no
					from nb_membership_history 
					where uid = '$id' and open_parent_no = $no
					";
	$result=mysqli_query($connect ,$query);
	$data=mysqli_fetch_array($result);
	if(!$data){

		$query = "  update nb_membership_status set open_cnt = open_cnt - 1 where uid = '$id'";
		$result=mysqli_query($connect ,$query);
		
		$history_text = "열람 1건 사용";

		$query = "insert into  nb_membership_history (
				sitekey
				,uid
				,kind
				,count_number
				,regdate
				,order_no
				,open_parent_no
			) 
			values
			(
				 '$NO_SITE_UNIQUE_KEY' 
				,'$id'
				,'USE'
				,'$history_text'
				, now()
				, ''
				,$no

			)";
			//echo $query;
			$result = mysqli_query($connect ,$query);

	}

}

function doNotMatchingAlert(){
	echo "{\"result\":\"fail\", \"msg\":\"멤버쉽 결제가 필요하거나 매칭 잔여 횟수가 없습니다.\"}";
	exit;
}

function isMatchingAble($id){
	
	global $NO_SITE_UNIQUE_KEY, $connect;
	$nowdate = date("Y-m-d");
	$isAble = "N";

	$query = "  select
							no
							,sitekey
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
					from nb_membership_status 
					where uid = '$id'
					and date_end >= '$nowdate'
					";
	$result=mysqli_query($connect ,$query);
	$data=mysqli_fetch_array($result);

	if($data){
		if($data['matching_cnt'] == 0)
			$isAble = "N";
		else
			$isAble = "Y";
	}
	
	return $isAble;
}



function doMatchingHistory($id, $no){

	global $NO_SITE_UNIQUE_KEY, $connect;


	$query = "  select
							no
							,sitekey
							,uid
							,kind
							,count_number
							,regdate
							,order_no
							,open_parent_no
					from nb_membership_history 
					where uid = '$id' and matching_parent_no = $no
					";
	//echo $query;exit;
	$result=mysqli_query($connect ,$query);
	$data=mysqli_fetch_array($result);
	if(!$data){

		$query = "  update nb_membership_status set matching_cnt = matching_cnt - 1 where uid = '$id'";
		$result=mysqli_query($connect ,$query);
		
		$history_text = "매칭요청 1건 사용";

		$query = "insert into  nb_membership_history (
				sitekey
				,uid
				,kind
				,count_number
				,regdate
				,order_no
				,open_parent_no
			) 
			values
			(
				 '$NO_SITE_UNIQUE_KEY' 
				,'$id'
				,'USE'
				,'$history_text'
				, now()
				, ''
				,$no

			)";
			//echo $query;
			$result = mysqli_query($connect ,$query);

	}

}



function isExpertAble($id, $targetCnt){
	
	global $NO_SITE_UNIQUE_KEY, $connect;
	$nowdate = date("Y-m-d");
	$isAble = "N";

	$query = "  select
							no
							,sitekey
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
					from nb_membership_status 
					where uid = '$id'
					and date_end >= '$nowdate'
					";
	$result=mysqli_query($connect ,$query);
	$data=mysqli_fetch_array($result);

	if($data){
		if($data['expert_consulting_cnt'] < $targetCnt)
			$isAble = "N";
		else
			$isAble = "Y";
	}
	
	return $isAble;
}


function doNotExpertAlert(){
	echo "{\"result\":\"fail\", \"msg\":\"멤버쉽 결제가 필요하거나 전문가 요청 잔여 횟수가 부족합니다.\"}";
	exit;
}


function doExpertHistory($id, $no, $targetCnt){

	global $NO_SITE_UNIQUE_KEY, $connect;

	$query = "  update nb_membership_status set expert_consulting_cnt = expert_consulting_cnt - 1 where uid = '$id'";
	$result=mysqli_query($connect ,$query);
	
	$history_text = "전문가 요청 ".$targetCnt."건 사용";

	$query = "insert into  nb_membership_history (
			sitekey
			,uid
			,kind
			,count_number
			,regdate
			,order_no
			,membership_parent_no
		) 
		values
		(
			 '$NO_SITE_UNIQUE_KEY' 
			,'$id'
			,'USE'
			,'$history_text'
			, now()
			, ''
			,$no

		)";
		//echo $query;
		$result = mysqli_query($connect ,$query);


}
?>