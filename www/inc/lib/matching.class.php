<?php


function getMatchingLIst($kind, $limit, $orderby="a.regdate desc"){
	global $NO_SITE_UNIQUE_KEY, $connect;
	$tbl = "";
	if($kind == "F"){
		$tbl = "nb_matching_manufacturer";
		
		$query = " select
					   a.no
					  ,a.sitekey
					  ,a.manage_number
					  ,a.register_id
					  ,a.corporate_name
					  ,a.corporate_logo
					  ,a.corporate_ssn
					  ,a.corporate_location
					  ,a.category_big
					  ,a.category_mid
					  ,a.category_sml
					  ,a.company_introduction
					  ,a.company_site_url
					  ,a.manufacturing_plant_own
					  ,a.manufacturing_plant_foreign
					  ,a.manufacturing_plant_oem_domestic
					  ,a.manufacturing_plant_oem_international
					  ,a.rnd_own
					  ,a.rnd_foreign
					  ,a.rnd_oem_domestic
					  ,a.rnd_oem_international
					  ,a.product_catalog
					  ,a.product_file1
					  ,a.product_file2
					  ,a.product_file3
					  ,a.product_file4
					  ,a.product_file5
					  ,a.charger_name
					  ,a.charger_duty
					  ,a.charger_phone
					  ,a.charger_email
					  ,a.new_deal_detp_phone
					  ,a.sales_detp_phone
					  ,a.partnership_detp_phone
					  ,a.mou_detp_phone
					  ,b.subject as category_big_title
					  ,c.subject as category_mid_title
					  ,d.subject as category_sml_title
					 from $tbl a
				 	left join nb_category b on a.category_big = b.no
				 	left join nb_category c on a.category_mid = c.no
					left join nb_category d on a.category_sml = d.no
					where a.view_yn = 'Y'
					order by $orderby limit $limit
		";

		//echo $query;

		$result =mysqli_query($connect ,$query);
		$arrResultSet = array(); 
		while($row = mysqli_fetch_assoc($result)) { 
			$arrResultSet[] = $row; 
		}

	}else if($kind == "D"){

		$tbl = "nb_matching_distributor";

		$query = " select
					   a.no
					  ,a.sitekey
					  ,a.manage_number
					  ,a.register_id
					  ,a.corporate_name
					  ,a.corporate_logo
					  ,a.corporate_ssn
					  ,a.corporate_location
					  ,a.category_big
					  ,a.category_mid
					  ,a.category_sml
					  ,a.company_introduction
					  ,a.company_site_url
					  ,a.career1_category_sml
					  ,a.career1_category_itm
					  ,a.career1_period
					  ,a.career2_category_sml
					  ,a.career2_category_itm
					  ,a.career2_period
					  ,a.career3_category_sml
					  ,a.career3_category_itm
					  ,a.career3_period
					  ,a.charger_name
					  ,a.charger_duty
					  ,a.charger_phone
					  ,a.charger_email
					  ,a.new_deal_detp_phone
					  ,a.sales_detp_phone
					  ,a.partnership_detp_phone
					  ,a.mou_detp_phone
					  ,b.subject as category_big_title
					  ,c.subject as category_mid_title
					  ,d.subject as category_sml_title
					 from $tbl a
				 	left join nb_category b on a.category_big = b.no
				 	left join nb_category c on a.category_mid = c.no
					left join nb_category d on a.category_sml = d.no
					where a.view_yn = 'Y'
					order by $orderby limit $limit
		";
		//echo $query;
		$result =mysqli_query($connect ,$query);
		$arrResultSet = array(); 
		while($row = mysqli_fetch_assoc($result)) { 
			$arrResultSet[] = $row; 
		}


	}else if($kind == "C"){
		
		$tbl = "nb_matching_closemall";
		
		$query = " select
					   a.no
					  ,a.sitekey
					  ,a.manage_number
					  ,a.register_id
					  ,a.corporate_name
					  ,a.corporate_logo
					  ,a.corporate_ssn
					  ,a.corporate_location
					  ,a.category_big
					  ,a.category_mid
					  ,a.category_sml
					  ,a.company_introduction
					  ,a.company_site_url
					  ,a.career1_category_sml
					  ,a.career1_category_itm
					  ,a.career1_period
					  ,a.career2_category_sml
					  ,a.career2_category_itm
					  ,a.career2_period
					  ,a.career3_category_sml
					  ,a.career3_category_itm
					  ,a.career3_period
					  ,a.charger_name
					  ,a.charger_duty
					  ,a.charger_phone
					  ,a.charger_email
					  ,a.new_deal_detp_phone
					  ,a.sales_detp_phone
					  ,a.partnership_detp_phone
					  ,a.mou_detp_phone
					  ,b.subject as category_big_title
					  ,c.subject as category_mid_title
					  ,d.subject as category_sml_title
					 from $tbl a
				 	left join nb_category b on a.category_big = b.no
				 	left join nb_category c on a.category_mid = c.no
					left join nb_category d on a.category_sml = d.no
					where a.view_yn = 'Y'
					order by $orderby limit $limit
		";

		$result =mysqli_query($connect ,$query);
		$arrResultSet = array(); 
		while($row = mysqli_fetch_assoc($result)) { 
			$arrResultSet[] = $row; 
		}


	}
	
	return $arrResultSet;
	

}


function getMatchingSucessCnt(){
	global $NO_SITE_UNIQUE_KEY, $connect;
	$query = " select count(*)  as cnt from nb_matching a where a.receive_approval = 'Y' ";
	$result=mysqli_query($connect ,$query);
	$data=mysqli_fetch_array($result);
	if($data){
		$totalCnt = $data[cnt];
	}

	return $totalCnt;

}


function getMatchingSucess($limit){
	global $NO_SITE_UNIQUE_KEY, $connect;
	$query = " select
					   a.no
					   ,a.sitekey
					   ,a.parent_no
					   ,a.kind
					   ,a.request_id
					   ,a.receive_id
					   ,a.receive_approval
					   ,a.receive_approval_date
					   ,a.regdate
					   ,a.collaboration_newdeal
					   ,a.collaboration_sales_goods
					   ,a.collaboration_tech
					   ,a.collaboration_mou
					   ,a.request_kind
					   ,a.request_matching_data_no
					 from nb_matching a
					where a.receive_approval = 'Y'   order by a.regdate desc limit $limit
	";

	//echo $query;

	$result =mysqli_query($connect ,$query);
	$arrResultSet = array(); 
	while($row = mysqli_fetch_assoc($result)) { 
		$arrResultSet[] = $row; 
	}

	return $arrResultSet;


}


function getProductList($parent_no){

	global $NO_SITE_UNIQUE_KEY, $connect;
	$arr = array();

	$query = " select no, name, first_supply_cnt, qty, amount, stock, pdate, regdate from nb_matching_manufacturer_product where parent_no = $parent_no order by no asc";
	//echo $query;
	$result =mysqli_query($connect ,$query);
	$r = array(); 
	while($row = mysqli_fetch_assoc($result)) { 
		$r[] = $row; 
	} 
	if($result){
		mysqli_free_result( $result );
	}
	return $r;
}


function getMatchingCategoryName($no, $kind){

	global $NO_SITE_UNIQUE_KEY, $connect;
	$query = "select big, mid, sml, itm, kind, subject
						from nb_category 
					where sitekey = '$NO_SITE_UNIQUE_KEY' and no = $no and kind = '$kind'";
			
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


function getMatchingCompanyInfo($kind, $no){

	global $NO_SITE_UNIQUE_KEY, $connect;
	$tbl = "";
	if($kind == "F")
		$tbl = "nb_matching_manufacturer";
	else if($kind == "D")
		$tbl = "nb_matching_distributor";
	else if($kind == "C")
		$tbl = "nb_matching_closemall";

	$query = "select  
					 a.*
						from $tbl a
					where a.no = $no";
	//echo $query;
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


function getMatchingCompanyInfoWithCategory($kind, $no){

	global $NO_SITE_UNIQUE_KEY, $connect;
	$tbl = "";
	if($kind == "F")
		$tbl = "nb_matching_manufacturer";
	else if($kind == "D")
		$tbl = "nb_matching_distributor";
	else if($kind == "C")
		$tbl = "nb_matching_closemall";

	$query = "select  
						a.*
						,b.subject as category_big_title
						,c.subject as category_mid_title
						,d.subject as category_sml_title
						from $tbl a
						left join nb_category b on a.category_big = b.no
				 		left join nb_category c on a.category_mid = c.no
						left join nb_category d on a.category_sml = d.no
						
					where a.no = $no";
	//echo $query;
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


function getMatchingCompanyInfoWithUser($kind, $id){

	global $NO_SITE_UNIQUE_KEY, $connect;
	$tbl = "";
	if($kind == "F")
		$tbl = "nb_matching_manufacturer";
	else if($kind == "D")
		$tbl = "nb_matching_distributor";
	else if($kind == "C")
		$tbl = "nb_matching_closemall";

	$query = "select  
					  a.*
						from $tbl a
					where a.register_id = '$id'";
	//echo $query;
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

function getMatchingProdPeriod($kind, $no){
	
	global $NO_SITE_UNIQUE_KEY, $connect;
	if($v['kind'] == "F"){
		$prodArr = getProductList($no);
		$prodNames = "";
		$j=0;
		foreach($prodArr as $k=>$vp){
			if($j>0)
				$prodNames .= ", ";
			$prodNames .= $vp['name'];

			$j++;
		}
		$rtn['title'] = "제품";
		$rtn['contents'] = $prodNames;

	}else if($v['kind'] == "D"){
		$query = " select
					  a.career1_period
					  from nb_matching_distributor a
					where a.no = $no
		";
		$result =mysqli_query($connect ,$query);
		$data=mysql_fetch_array($result);
		
		$rtn['title'] = "유통경력";
		$rtn['contents'] = $data['career1_period'];


	}else if($v['kind'] == "C"){
		$query = " select
					  a.career1_period
					  from nb_matching_closemall a
					where a.no = $no
		";
		$result =mysqli_query($connect ,$query);
		$data=mysql_fetch_array($result);
		
		$rtn['title'] = "유통경력";
		$rtn['contents'] = $data['career1_period'];


	}

	return $rtn;

}


function getMatchingComplete($parent_no, $uid){

	global $NO_SITE_UNIQUE_KEY, $connect;
	

	$query = "select  
					  a.no
					   ,a.sitekey
					   ,a.parent_no
					   ,a.kind
					   ,a.request_id
					   ,a.receive_id
					   ,a.receive_approval
					   ,a.receive_approval_date
					   ,a.regdate
					   ,a.collaboration_newdeal
					   ,a.collaboration_sales_goods
					   ,a.collaboration_tech
					   ,a.collaboration_mou
					   ,a.request_kind
					   ,a.request_matching_data_no
					 from nb_matching a
					where (a.parent_no = $parent_no or a.request_matching_data_no = $parent_no) and (a.request_id = '$uid' or a.receive_id = '$uid') and a.receive_approval = 'Y'";
	//echo $query;
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


function isMatching($parent_no, $uid){

	global $NO_SITE_UNIQUE_KEY, $connect;
	

	$query = "select  
					  a.no
					   ,a.sitekey
					   ,a.parent_no
					   ,a.kind
					   ,a.request_id
					   ,a.receive_id
					   ,a.receive_approval
					   ,a.receive_approval_date
					   ,a.regdate
					   ,a.collaboration_newdeal
					   ,a.collaboration_sales_goods
					   ,a.collaboration_tech
					   ,a.collaboration_mou
					   ,a.request_kind
					   ,a.request_matching_data_no
					 from nb_matching a
					where (a.parent_no = $parent_no or a.request_matching_data_no = $parent_no)  and (a.request_id = '$uid' or a.receive_id = '$uid') ";
	//echo $query;
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

function getKindString($kind){

	$rtn = "";
	if($kind == "F")
		$rtn = "제조사";
	else if($kind == "D")
		$rtn = "유통사";
	else if($kind == "C")
		$rtn = "폐쇄몰";

	return $rtn;

}


function getMemberInfo($id){
	
	global $NO_SITE_UNIQUE_KEY, $connect;
	

	$query = "select
						a.no
						,a.lev
						,a.uid
						,a.upwd
						,a.corporate_name
						,a.ssn
						,a.owner_name
						,a.owner_hp
						,a.charger_name
						,a.charger_duty
						,a.charger_hp
						,a.regdate
					    ,a.matching_reg_kind
					    ,a.matching_reg_no
					 from nb_member a
					where  a.uid = '$id'";
	
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





?>