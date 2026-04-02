<?
	ob_start();
	include_once $_SERVER['DOCUMENT_ROOT']."/inc/lib/base.class.php";



	$no		= $_REQUEST['no'];
	$kind		= $_REQUEST['kind'];
	$tg		= $_REQUEST['tg'];

	$tbl = "";
	if($kind == "F")
		$tbl = "nb_matching_manufacturer";
	else if($kind == "D")
		$tbl = "nb_matching_distributor";
	else if($kind == "C")
		$tbl = "nb_matching_distributor";

	$fld = "";
	if($tg == "introduction")
		$fld = "company_introduction";
	else if($tg == "catalog")
		$fld = "product_catalog";
	else if($tg == "prod1")
		$fld = "product_file1";
	else if($tg == "prod2")
		$fld = "product_file2";
	else if($tg == "prod3")
		$fld = "product_file3";
	else if($tg == "prod4")
		$fld = "product_file4";
	else if($tg == "prod5")
		$fld = "product_file5";
	else if($tg == "entrycondition") 
		$fld = "entry_condition";
	

	$query = " select $fld from $tbl where no = $no ";
	//echo $query;exit;
	$result=mysql_query($query);
	$data=mysql_fetch_array($result);
	if(!$data){
		error("정보를 찾을 수 없습니다");exit;
	}

	$targetFile				= $data[$fld];
	$filename				= $targetFile;
	$filename_origin		= $targetFile;


	$filepath = $UPLOAD_MATCHING_DIR."/".$filename;


	$filesize = filesize($filepath);
	$path_parts = pathinfo($filepath);
	$filename = $path_parts['basename'];
	$extension = $path_parts['extension'];


	$ie = isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') !== false); 
 
	if($ie) {
	   // UTF-8에서 EUC-KR로 캐릭터셋 변경
	   $filename_origin = utf2euc($filename_origin);
	 
	   // IE인 경우 헤더 변경
	   header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	   header('Pragma: public');
	}else{
	   // IE가 아닌 경우 일반 헤더 적용
	   header("Cache-Control: no-cache, must-revalidate"); 
	   header('Pragma: no-cache');
	}

	

	header("Pragma: public");
	header("Expires: 0");
	header("Content-Type: application/octet-stream");
	//header("Content-Disposition: attachment; filename=$filename_origin");
	header("Content-Disposition: attachment; filename=\"$filename_origin\"");
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: $filesize");

	ob_clean();
	flush();
	readfile($filepath);



?>
