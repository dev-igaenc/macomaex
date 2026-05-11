<?
	
	include_once "../../../../inc/lib/base.class.php";
	include_once "../../../lib/admin.check.ajax.php";

	$mode	= $_POST['mode'];

	 
	 if($mode == "matching.manufacturer.edit"){
		
		$no									= xss_clean($_POST['no']);

		$sales_year1						= xss_clean($_POST['sales_year1']);
		$sales_year2						= xss_clean($_POST['sales_year2']);
		$sales_year3						= xss_clean($_POST['sales_year3']);
		$sales_price1						= xss_clean($_POST['sales_price1']);
		$sales_price2						= xss_clean($_POST['sales_price2']);
		$sales_price3						= xss_clean($_POST['sales_price3']);
		$business_profit_year1		= xss_clean($_POST['business_profit_year1']);
		$business_profit_year2		= xss_clean($_POST['business_profit_year2']);
		$business_profit_year3		= xss_clean($_POST['business_profit_year3']);
		$business_profit_price1		= xss_clean($_POST['business_profit_price1']);
		$business_profit_price2		= xss_clean($_POST['business_profit_price2']);
		$business_profit_price3		= xss_clean($_POST['business_profit_price3']);
		$net_income_year1			= xss_clean($_POST['net_income_year1']);
		$net_income_year2			= xss_clean($_POST['net_income_year2']);
		$net_income_year3			= xss_clean($_POST['net_income_year3']);
		$net_income_price1			= xss_clean($_POST['net_income_price1']);
		$net_income_price2			= xss_clean($_POST['net_income_price2']);
		$net_income_price3			= xss_clean($_POST['net_income_price3']);
		$capital_year1					= xss_clean($_POST['capital_year1']);
		$capital_year2					= xss_clean($_POST['capital_year2']);
		$capital_year3					= xss_clean($_POST['capital_year3']);
		$capital_price1					= xss_clean($_POST['capital_price1']);
		$capital_price2					= xss_clean($_POST['capital_price2']);
		$capital_price3					= xss_clean($_POST['capital_price3']);
		$view_yn							= xss_clean($_POST['view_yn']);
	
		$credit_rating_finance_standard		= xss_clean($_POST['credit_rating_finance_standard']);
		$credit_rating_level_standard			= xss_clean($_POST['credit_rating_level_standard']);
		$credit_rating									= xss_clean($_POST['credit_rating']);



		/* FILE ATTACH */
	 
		$allow							= $matching_allow;
		$uploads_dir					= $UPLOAD_MATCHING_DIR;
		
		$uploadResult					= fileUpoad($uploads_dir, $_FILES['corporate_logo'], $origin_file, false, $allow);
		$corporate_logo				= $uploadResult['saved'];

		$uploadResult					= fileUpoad($uploads_dir, $_FILES['company_introduction'], $origin_file, false, $allow);
		$company_introduction	= $uploadResult['saved'];
		
		$uploadResult					= fileUpoad($uploads_dir, $_FILES['product_catalog'], $origin_file, false, $allow);
		$product_catalog				= $uploadResult['saved'];

		$uploadResult					= fileUpoad($uploads_dir, $_FILES['product_file1'], $origin_file, false, $allow);
		$product_file1					= $uploadResult['saved'];

		$uploadResult					= fileUpoad($uploads_dir, $_FILES['product_file2'], $origin_file, false, $allow);
		$product_file2					= $uploadResult['saved'];

		$uploadResult					= fileUpoad($uploads_dir, $_FILES['product_file3'], $origin_file, false, $allow);
		$product_file3					= $uploadResult['saved'];

		$uploadResult					= fileUpoad($uploads_dir, $_FILES['product_file4'], $origin_file, false, $allow);
		$product_file4					= $uploadResult['saved'];

		$uploadResult					= fileUpoad($uploads_dir, $_FILES['product_file5'], $origin_file, false, $allow);
		$product_file5					= $uploadResult['saved'];

		/* FILE ATTACH */


		$query = "update nb_matching_manufacturer set 
						 sales_year1 = '$sales_year1'
						,sales_year2 = '$sales_year2'
						,sales_year3 = '$sales_year3'
						,sales_price1 = '$sales_price1'
						,sales_price2 = '$sales_price2'
						,sales_price3 = '$sales_price3'
						,business_profit_year1 = '$business_profit_year1'
						,business_profit_year2 = '$business_profit_year2'
						,business_profit_year3 = '$business_profit_year3'
						,business_profit_price1 = '$business_profit_price1'
						,business_profit_price2 = '$business_profit_price2'
						,business_profit_price3 = '$business_profit_price3'
						,net_income_year1 = '$net_income_year1'
						,net_income_year2 = '$net_income_year2'
						,net_income_year3 = '$net_income_year3'
						,net_income_price1 = '$net_income_price1'
						,net_income_price2 = '$net_income_price2'
						,net_income_price3 = '$net_income_price3'
						,capital_year1 = '$capital_year1'
						,capital_year2 = '$capital_year2'
						,capital_year3 = '$capital_year3'
						,capital_price1 = '$capital_price1'
						,capital_price2 = '$capital_price2'
						,capital_price3 = '$capital_price3'
						,credit_rating_finance_standard = '$credit_rating_finance_standard'
						,credit_rating_level_standard = '$credit_rating_level_standard'
						,credit_rating = '$credit_rating'
						";
		if($company_introduction){
			$query .= ",company_introduction = '$company_introduction' ";
		}	

		if($corporate_logo){
			$query .= ",corporate_logo = '$corporate_logo' ";
		}	

		if($product_file1){
			$query .= ",product_file1 = '$product_file1' ";
		}	

		if($product_file2){
			$query .= ",product_file2 = '$product_file2' ";
		}	

		if($product_file3){
			$query .= ",product_file3 = '$product_file3' ";
		}	

		if($product_file4){
			$query .= ",product_file4 = '$product_file4' ";
		}	

		if($product_file5){
			$query .= ",product_file5 = '$product_file5' ";
		}	
		
		$query .= "	,view_yn = '$view_yn' ";
		$query.= " where no = $no";
		
					
		echo $query;
		$result = mysql_query($query);
		//echo mysql_error();
		
		if($result){
			echo "{\"result\":\"success\", \"msg\":\"정상적으로 수정되었습니다.\"}";
		}else{
			echo "{\"result\":\"fail\", \"msg\":\"처리중 문제가 발생하였습니다.[Error-DB]\"}";
		}	



	 }else if($mode == "matching.distributor.edit"){
		
		$no									= xss_clean($_POST['no']);

		$sales_year1						= xss_clean($_POST['sales_year1']);
		$sales_year2						= xss_clean($_POST['sales_year2']);
		$sales_year3						= xss_clean($_POST['sales_year3']);
		$sales_price1						= xss_clean($_POST['sales_price1']);
		$sales_price2						= xss_clean($_POST['sales_price2']);
		$sales_price3						= xss_clean($_POST['sales_price3']);
		$business_profit_year1		= xss_clean($_POST['business_profit_year1']);
		$business_profit_year2		= xss_clean($_POST['business_profit_year2']);
		$business_profit_year3		= xss_clean($_POST['business_profit_year3']);
		$business_profit_price1		= xss_clean($_POST['business_profit_price1']);
		$business_profit_price2		= xss_clean($_POST['business_profit_price2']);
		$business_profit_price3		= xss_clean($_POST['business_profit_price3']);
		$net_income_year1			= xss_clean($_POST['net_income_year1']);
		$net_income_year2			= xss_clean($_POST['net_income_year2']);
		$net_income_year3			= xss_clean($_POST['net_income_year3']);
		$net_income_price1			= xss_clean($_POST['net_income_price1']);
		$net_income_price2			= xss_clean($_POST['net_income_price2']);
		$net_income_price3			= xss_clean($_POST['net_income_price3']);
		$capital_year1					= xss_clean($_POST['capital_year1']);
		$capital_year2					= xss_clean($_POST['capital_year2']);
		$capital_year3					= xss_clean($_POST['capital_year3']);
		$capital_price1					= xss_clean($_POST['capital_price1']);
		$capital_price2					= xss_clean($_POST['capital_price2']);
		$capital_price3					= xss_clean($_POST['capital_price3']);
		$view_yn							= xss_clean($_POST['view_yn']);
		
		$credit_rating_finance_standard		= xss_clean($_POST['credit_rating_finance_standard']);
		$credit_rating_level_standard			= xss_clean($_POST['credit_rating_level_standard']);
		$credit_rating									= xss_clean($_POST['credit_rating']);

		/* FILE ATTACH */
	 
		$allow							= $matching_allow;
		$uploads_dir					= $UPLOAD_MATCHING_DIR;
		
		$uploadResult					= fileUpoad($uploads_dir, $_FILES['corporate_logo'], $origin_file, false, $allow);
		$corporate_logo				= $uploadResult['saved'];

		$uploadResult					= fileUpoad($uploads_dir, $_FILES['company_introduction'], $origin_file, false, $allow);
		$company_introduction	= $uploadResult['saved'];
		

		/* FILE ATTACH */

		$query = "update nb_matching_distributor set 
						 sales_year1 = '$sales_year1'
						,sales_year2 = '$sales_year2'
						,sales_year3 = '$sales_year3'
						,sales_price1 = '$sales_price1'
						,sales_price2 = '$sales_price2'
						,sales_price3 = '$sales_price3'
						,business_profit_year1 = '$business_profit_year1'
						,business_profit_year2 = '$business_profit_year2'
						,business_profit_year3 = '$business_profit_year3'
						,business_profit_price1 = '$business_profit_price1'
						,business_profit_price2 = '$business_profit_price2'
						,business_profit_price3 = '$business_profit_price3'
						,net_income_year1 = '$net_income_year1'
						,net_income_year2 = '$net_income_year2'
						,net_income_year3 = '$net_income_year3'
						,net_income_price1 = '$net_income_price1'
						,net_income_price2 = '$net_income_price2'
						,net_income_price3 = '$net_income_price3'
						,capital_year1 = '$capital_year1'
						,capital_year2 = '$capital_year2'
						,capital_year3 = '$capital_year3'
						,capital_price1 = '$capital_price1'
						,capital_price2 = '$capital_price2'
						,capital_price3 = '$capital_price3'
						,view_yn = '$view_yn' 
						,credit_rating_finance_standard = '$credit_rating_finance_standard'
						,credit_rating_level_standard = '$credit_rating_level_standard'
						,credit_rating = '$credit_rating'
						";

		if($company_introduction){
			$query .= ",company_introduction = '$company_introduction' ";
		}	

		if($corporate_logo){
			$query .= ",corporate_logo = '$corporate_logo' ";
		}	
		$query.= " where no = $no";
		
					
		//echo $query;
		$result = mysql_query($query);
		//echo mysql_error();
		
		if($result){
			echo "{\"result\":\"success\", \"msg\":\"정상적으로 수정되었습니다.\"}";
		}else{
			echo "{\"result\":\"fail\", \"msg\":\"처리중 문제가 발생하였습니다.[Error-DB]\"}";
		}	



	 }else if($mode == "getCategory"){
		
		$kind	= $_REQUEST["kind"];
		$big	= $_REQUEST["big"];
		$mid	= $_REQUEST["mid"];
		$sml	= $_REQUEST["sml"];
		
		if($big){
			$bigArr = explode("^", $big);
			$big = $bigArr[0];
		}

		if($mid){
			$midArr = explode("^", $mid);
			$mid = $midArr[0];
		}

		if($sml){
			$smlArr = explode("^", $sml);
			$sml = $smlArr[0];
		}

		if($kind == "big"){
		
			$query = " select * from nb_category where mid=0 and sml=0 and itm=0 and kind='b' order by sort_no asc";

			$result=mysql_query($query);
			//echo $query;

			echo "{";
			echo "\"result\":\"success\",";
			echo "\"msg\":\"데이터 로드 성공\",";
			echo "\"rows\":[";
			
			$i=0;

			while($row = mysql_fetch_assoc($result)) { 
				
				if($i>0)
					echo ",";
					
					$no			= $row['no'];
					$big			= $row['big'];
					$mid			= $row['mid'];
					$sml			= $row['sml'];
					$itm			= $row['itm'];
					$subject		= $row['subject'];
					$disp			= $row['disp'];

					echo "{ \"no\":\"".$no."\", \"big\":\"".$big."\", \"mid\":\"".$mid."\", \"sml\":\"".$sml."\", \"itm\":\"".$itm."\",\"subject\":\"".$subject."\", \"disp\":\"".$disp."\" }";

				$i++;

			}

			echo "]";
			echo "}";


		}else if($kind == "mid"){
			

			$query = " select * from nb_category where big= $big and mid>0 and sml = 0 and itm = 0 order by sort_no asc";

			$result=mysql_query($query);
			
			echo "{";
			echo "\"result\":\"success\",";
			echo "\"msg\":\"데이터 로드 성공\",";
			echo "\"rows\":[";
			
			$i=0;

			while($row = mysql_fetch_assoc($result)) { 
				
				if($i>0)
					echo ",";
					
					$no			= $row['no'];
					$big			= $row['big'];
					$mid			= $row['mid'];
					$sml			= $row['sml'];
					$itm			= $row['itm'];
					$subject		= $row['subject'];
					$disp			= $row['disp'];

					echo "{ \"no\":\"".$no."\", \"big\":\"".$big."\", \"mid\":\"".$mid."\", \"sml\":\"".$sml."\", \"itm\":\"".$itm."\",\"subject\":\"".$subject."\", \"disp\":\"".$disp."\" }";

				$i++;

			}

			echo "]";
			echo "}";
		
		
		}else if($kind == "sml"){
			

			$query = " select * from nb_category where big= $big and mid = $mid and sml > 0 and itm = 0 order by sort_no asc";
			//echo $query;
			$result=mysql_query($query);
			
			echo "{";
			echo "\"result\":\"success\",";
			echo "\"msg\":\"데이터 로드 성공\",";
			echo "\"rows\":[";
			
			$i=0;

			while($row = mysql_fetch_assoc($result)) { 
				
				if($i>0)
					echo ",";
					
					$no			= $row['no'];
					$big			= $row['big'];
					$mid			= $row['mid'];
					$sml			= $row['sml'];
					$itm			= $row['itm'];
					$subject		= $row['subject'];
					$disp			= $row['disp'];

					echo "{ \"no\":\"".$no."\", \"big\":\"".$big."\", \"mid\":\"".$mid."\", \"sml\":\"".$sml."\", \"itm\":\"".$itm."\",\"subject\":\"".$subject."\", \"disp\":\"".$disp."\" }";

				$i++;

			}

			echo "]";
			echo "}";
		
		
		}else if($kind == "itm"){
			

			$query = " select * from nb_category where big= $big and mid = $mid and sml = $sml and itm > 0 order by sort_no asc";

			$result=mysql_query($query);
			
			echo "{";
			echo "\"result\":\"success\",";
			echo "\"msg\":\"데이터 로드 성공\",";
			echo "\"rows\":[";
			
			$i=0;

			while($row = mysql_fetch_assoc($result)) { 
				
				if($i>0)
					echo ",";
					
					$no			= $row['no'];
					$big			= $row['big'];
					$mid			= $row['mid'];
					$sml			= $row['sml'];
					$itm			= $row['itm'];
					$subject		= $row['subject'];
					$disp			= $row['disp'];

					echo "{ \"no\":\"".$no."\", \"big\":\"".$big."\", \"mid\":\"".$mid."\", \"sml\":\"".$sml."\", \"itm\":\"".$itm."\",\"subject\":\"".$subject."\", \"disp\":\"".$disp."\" }";

				$i++;

			}

			echo "]";
			echo "}";
		
		
		}

	 
	 
	 }else if($mode == "matching.closemall.save") {
		

		$corporate_name									= xss_clean($_POST['corporate_name']);
		/* corporate_logo */
		$corporate_ssn										= xss_clean($_POST['corporate_ssn']);
		$corporate_location									= xss_clean($_POST['corporate_location']);
		$category_big											= xss_clean($_POST['category_big']);
		$category_mid										= xss_clean($_POST['category_mid']);
		$category_sml										= xss_clean($_POST['category_sml']);
		/* company_introduction */
		$company_site_url									= xss_clean($_POST['company_site_url']);
		$career1_category_sml								= xss_clean($_POST['career1_category_sml']);
		$career1_category_itm								= xss_clean($_POST['career1_category_itm']);
		$career1_period										= xss_clean($_POST['career1_period']);
		$career2_category_sml								= xss_clean($_POST['career2_category_sml']);
		$career2_category_itm								= xss_clean($_POST['career2_category_itm']);
		$career2_period										= xss_clean($_POST['career2_period']);
		$career3_category_sml								= xss_clean($_POST['career3_category_sml']);
		$career3_category_itm								= xss_clean($_POST['career3_category_itm']);
		$career3_period										= xss_clean($_POST['career3_period']);
		$charger_name										= xss_clean($_POST['charger_name']);
		$charger_duty											= xss_clean($_POST['charger_duty']);
		$charger_phone										= xss_clean($_POST['charger_phone']);
		$charger_email										= xss_clean($_POST['charger_email']);
		$new_deal_detp_phone								= xss_clean($_POST['new_deal_detp_phone']);
		$sales_detp_phone									= xss_clean($_POST['sales_detp_phone']);
		$partnership_detp_phone							= xss_clean($_POST['partnership_detp_phone']);
		$mou_detp_phone									= xss_clean($_POST['mou_detp_phone']);
		$register_id											= xss_clean($_POST['register_id']);						


		$sales_year1											= xss_clean($_POST['sales_year1']);
		$sales_year2											= xss_clean($_POST['sales_year2']);
		$sales_year3											= xss_clean($_POST['sales_year3']);
		$sales_price1											= xss_clean($_POST['sales_price1']);
		$sales_price2											= xss_clean($_POST['sales_price2']);
		$sales_price3											= xss_clean($_POST['sales_price3']);
		$business_profit_year1							= xss_clean($_POST['business_profit_year1']);
		$business_profit_year2							= xss_clean($_POST['business_profit_year2']);
		$business_profit_year3							= xss_clean($_POST['business_profit_year3']);
		$business_profit_price1							= xss_clean($_POST['business_profit_price1']);
		$business_profit_price2							= xss_clean($_POST['business_profit_price2']);
		$business_profit_price3							= xss_clean($_POST['business_profit_price3']);
		$net_income_year1								= xss_clean($_POST['net_income_year1']);
		$net_income_year2								= xss_clean($_POST['net_income_year2']);
		$net_income_year3								= xss_clean($_POST['net_income_year3']);
		$net_income_price1								= xss_clean($_POST['net_income_price1']);
		$net_income_price2								= xss_clean($_POST['net_income_price2']);
		$net_income_price3								= xss_clean($_POST['net_income_price3']);
		$capital_year1										= xss_clean($_POST['capital_year1']);
		$capital_year2										= xss_clean($_POST['capital_year2']);
		$capital_year3										= xss_clean($_POST['capital_year3']);
		$capital_price1										= xss_clean($_POST['capital_price1']);
		$capital_price2										= xss_clean($_POST['capital_price2']);
		$capital_price3										= xss_clean($_POST['capital_price3']);
		

		if($category_big){
			$bigArr = explode("^", $category_big);
			$category_big = $bigArr[0];
		}

		if($category_mid){
			$midArr = explode("^", $category_mid);
			$category_mid = $midArr[0];
		}

		if($category_sml){
			$smlArr = explode("^", $category_sml);
			$category_sml = $smlArr[0];
		}


		

		if($career1_category_sml){
			$smlArr = explode("^", $career1_category_sml);
			$career1_category_sml = $smlArr[0];
		}

		if($career1_category_itm){
			$itmArr = explode("^", $career1_category_itm);
			$career1_category_itm = $itmArr[0];
		}


		if($career2_category_sml){
			$smlArr = explode("^", $career2_category_sml);
			$career2_category_sml = $smlArr[0];
		}

		if($career2_category_itm){
			$itmArr = explode("^", $career2_category_itm);
			$career2_category_itm = $itmArr[0];
		}


		if($career3_category_sml){
			$smlArr = explode("^", $career3_category_sml);
			$career3_category_sml = $smlArr[0];
		}

		if($career3_category_itm){
			$itmArr = explode("^", $career3_category_itm);
			$career3_category_itm = $itmArr[0];
		}

		/* FILE ATTACH */
	 
		$allow							= $matching_allow;
		$uploads_dir					= $UPLOAD_MATCHING_DIR;
		
		$uploadResult					= fileUpoad($uploads_dir, $_FILES['corporate_logo'], $origin_file, false, $allow);
		$corporate_logo				= $uploadResult['saved'];

		$uploadResult					= fileUpoad($uploads_dir, $_FILES['company_introduction'], $origin_file, false, $allow);
		$company_introduction	= $uploadResult['saved'];
		
		$uploadResult					= fileUpoad($uploads_dir, $_FILES['entry_condition'], $origin_file, false, $allow);
		$entry_condition				= $uploadResult['saved'];

		/* FILE ATTACH */


		$rndcode = getRndNumericCode(5);
		$curdate = date("YmdHis");
		$manage_number = "MC".$curdate.$rndcode;
		

		/* 제품기술 관련 당사 보유특허 */

		$array_patent = array();
		$i=0;
		foreach ($_POST['registration_number'] as $value) {
			
			$patent_kind					= $_POST['patent_kind'][$i];
			$registration_number		= $_POST['registration_number'][$i];
			$patent_name					= $_POST['patent_name'][$i];

			$uploadResult					= filesUpoad($uploads_dir, $_FILES['patent_file'], $origin_file, false, $allow, $i);
			$patent_file						= $uploadResult['saved'];


			$item_patent = array();
			$item_patent['patent_kind']				= $patent_kind;
			$item_patent['registration_number']	= $registration_number;
			$item_patent['patent_name']			= $patent_name;
			$item_patent['patent_file']				= $patent_file;

			$array_patent[] = $item_patent;


			$i++;

		}

		/* 제품기술 관련 당사 수상내역 */
		$array_award = array();
		$i=0;
		foreach ($_POST['award_kind'] as $value) {
			
			$award_kind					= $_POST['award_kind'][$i];

			$uploadResult					= filesUpoad($uploads_dir, $_FILES['award_file'], $origin_file, false, $allow, $i);
			$award_file						= $uploadResult['saved'];


			$item_award = array();
			$item_award['award_kind']				= $award_kind;
			$item_award['award_file']				= $award_file;

			$array_award[] = $item_award;


			$i++;

		}

		

		$query = "insert into  nb_matching_closemall (
						sitekey
						,manage_number
						,register_id
						,corporate_name
						,corporate_logo
						,corporate_ssn
						,corporate_location
						,category_big
						,category_mid
						,category_sml
						,company_introduction
						,company_site_url
						,career1_category_sml
						,career1_category_itm
						,career1_period
						,career2_category_sml
						,career2_category_itm
						,career2_period
						,career3_category_sml
						,career3_category_itm
						,career3_period
						,charger_name
						,charger_duty
						,charger_phone
						,charger_email
						,new_deal_detp_phone
						,sales_detp_phone
						,partnership_detp_phone
						,mou_detp_phone
						,sales_year1						
						,sales_year2						
						,sales_year3						
						,sales_price1						
						,sales_price2						
						,sales_price3						
						,business_profit_year1		
						,business_profit_year2		
						,business_profit_year3		
						,business_profit_price1		
						,business_profit_price2		
						,business_profit_price3		
						,net_income_year1			
						,net_income_year2			
						,net_income_year3			
						,net_income_price1			
						,net_income_price2			
						,net_income_price3			
						,capital_year1					
						,capital_year2					
						,capital_year3					
						,capital_price1					
						,capital_price2					
						,capital_price3			
						,entry_condition
						,regdate
					) 
					values
					(
						'$NO_SITE_UNIQUE_KEY' 
						,'$manage_number'
						,'$register_id'
						,'$corporate_name'
						,'$corporate_logo'
						,'$corporate_ssn'
						,'$corporate_location'
						, $category_big
						, $category_mid
						, $category_sml
						,'$company_introduction'
						,'$company_site_url'
						, $career1_category_sml
						, $career1_category_itm
						,'$career1_period'
						, $career2_category_sml
						, $career2_category_itm
						,'$career2_period'
						, $career3_category_sml
						, $career3_category_itm
						,'$career3_period'
						,'$charger_name'
						,'$charger_duty'
						,'$charger_phone'
						,'$charger_email'
						,'$new_deal_detp_phone'
						,'$sales_detp_phone'
						,'$partnership_detp_phone'
						,'$mou_detp_phone'
						,'$sales_year1'
						,'$sales_year2'
						,'$sales_year3'
						,'$sales_price1'
						,'$sales_price2'
						,'$sales_price3'
						,'$business_profit_year1'
						,'$business_profit_year2'
						,'$business_profit_year3'
						,'$business_profit_price1'
						,'$business_profit_price2'
						,'$business_profit_price3'
						,'$net_income_year1'
						,'$net_income_year2'
						,'$net_income_year3'
						,'$net_income_price1'
						,'$net_income_price2'
						,'$net_income_price3'
						,'$capital_year1'
						,'$capital_year2'
						,'$capital_year3'
						,'$capital_price1'
						,'$capital_price2'
						,'$capital_price3'
						,'$entry_condition'
						,now()		
		
					)";
		//echo $query;
		$result = mysql_query($query);
		$insert_id = mysql_insert_id();
		echo mysql_error();

		

		/* 제품기술 관련 당사 보유특허 */
		if(count($array_patent) > 0){
		
			foreach($array_patent as $value){
				
				$patent_kind							= $value['patent_kind'];
				$registration_number				= $value['registration_number'];
				$patent_name							= $value['patent_name'];
				$patent_file								= $value['patent_file'];

				$query = "insert into  
							  nb_matching_patent (
																sitekey
																,parent_no
																,register_id
																,kind
																,registration_number
																,patent_name
																,patent_file
																,regdate
								) values (
									 '$NO_SITE_UNIQUE_KEY' 
									 ,$insert_id
									 ,'$register_id'
									 ,'$patent_kind'
									 ,'$registration_number'
									 ,'$patent_name'
									 ,'$patent_file'
									 , now()
								)";
				$result = mysql_query($query);

			}
		
		}

		/* 제품기술 관련 당사 수상내역 */
		if(count($array_award) > 0){
		
			foreach($array_award as $value){
	
				$award_kind					= $value['award_kind'];
				$award_file						= $value['award_file'];
			

				$query = "insert into  
							  nb_matching_award (
																sitekey
																,parent_no
																,register_id
																,kind
																,award_file
																,regdate
								) values (
									 '$NO_SITE_UNIQUE_KEY' 
									 ,$insert_id
									 ,'$register_id'
									 ,'$award_kind'
									 ,'$award_file'
									 , now()
								)";
				$result = mysql_query($query);

			}
		
		}

		$query = "update nb_member set matching_reg_kind = 'C', matching_reg_no = $insert_id where uid = '$register_id'";
		mysql_query($query);

		//exit;
		if($result){
			echo "{\"result\":\"success\", \"msg\":\"정상적으로 등록 되었습니다.\"}";
		}else{
			echo "{\"result\":\"fail\", \"msg\":\"처리중 문제가 발생하였습니다.[Error-DB]\"}";
		}
	 	
	 
	 
	 }else if($mode == "delete") {
	 
	 	$no				= xss_clean($_POST['no']);
		$kind				= xss_clean($_POST['kind']);


		$tbl = "";
		if($kind == "F")
			$tbl = "nb_matching_manufacturer";
		else if($kind == "D")
			$tbl = "nb_matching_distributor";
		else if($kind == "C")
			$tbl = "nb_matching_closemall";

		$query = "delete from $tbl where no = $no";
		$result = mysql_query($query);

		$query = " delete from nb_matching_manufacturer_product where parent_no= $no ";
		mysql_query($query);

		$query = " delete from nb_matching_award where parent_no= $no ";
		mysql_query($query);

		$query = " delete from nb_matching_patent where parent_no= $no ";
		mysql_query($query);

		if($result){
			echo "{\"result\":\"success\", \"msg\":\"정상적으로 삭제 되었습니다.\"}";
		}else{
			echo "{\"result\":\"fail\", \"msg\":\"처리중 문제가 발생하였습니다.[Error-DB]\"}";
		}

	 
	 }else if($mode == "matching.delete") {
			
		$no				= xss_clean($_POST['no']);
		
		$query = " delete from nb_matching where no= $no ";
		//echo $query;
		$result = mysql_query($query);

		if($result){
			echo "{\"result\":\"success\", \"msg\":\"정상적으로 삭제 되었습니다.\"}";
		}else{
			echo "{\"result\":\"fail\", \"msg\":\"처리중 문제가 발생하였습니다.[Error-DB]\"}";
		}

	 }

?>