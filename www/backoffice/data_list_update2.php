<?php
set_time_limit(0);
ini_set('memory_limit','-1');
ini_set('max_execution_time', 0); 
$sub_menu = '800100';
include_once('./_common.php');
include_once(G5_LIB_PATH.'/PHPExcel/IOFactory.php');

if ($w == "u" || $w == "d")
    check_demo();


//check_admin_token();


if ($c_id)
{
    $sql = " select * from company_update_excel where c_id = '$c_id' ";
    $co_row = sql_fetch($sql);
}

$c_id = isset($_GET['c_id']) ? $_GET['c_id'] : '';

@mkdir(G5_DATA_PATH."/exldata", G5_DIR_PERMISSION);
@chmod(G5_DATA_PATH."/exldata", G5_DIR_PERMISSION);



$error_msg = '';

$file_name=$_FILES['file']['name'];

if ($w == "")
{
    $sql = " insert company_update_excel
				set FILE = '$file_name',
				ACCT_DT=now()
				 ";
    sql_query($sql);
}
else if ($w == "u")
{
	
    $sql = " update company_update_excel
                set FILE = '$file_name',
              where c_id = '$c_id' ";
    sql_query($sql);
}
else if ($w == "d")
{
    @unlink(G5_DATA_PATH."/exldata/".$co_row['FILE']);

    $sql = " delete from company_update_excel where c_id = '$c_id' ";
    sql_query($sql);
}
else if ($w == "df")
{
    @unlink(G5_DATA_PATH."/exldata/".$co_row['FILE']);
}
else if ($w == "s")
{
	if($co_row['FILE']) {
		ob_start();
		unset($myfiles);
		$mydir = G5_DATA_PATH.'/exldata/'; 

		if(strpos($co_row['FILE'],".zip")) {
			system('unzip -x '.G5_DATA_PATH.'/exldata/'.$co_row['FILE'].' -d '.G5_DATA_PATH.'/exldata/');
			$run= ob_get_contents();
			$run_count=substr_count($run,"inflating");
			echo "총 ".$run_count."개 파일 해제<br>";

		}
  
	  //업데이트 파일 DB적용
	  if(strpos($co_row['FILE'],".zip")) $myfiles = array_diff(scandir($mydir), array('.', '..',$co_row['FILE']));
	  else $myfiles[] = $co_row['FILE'];
	  
	  foreach($myfiles as $value){
		  if(strpos($value,".zip")) continue; //압축파일은 지우지 않기

		  unset($type);
		  unset($file_data);
		  unset($ked);


		if($value){
			unset($file );
			unset($objPHPExcel );
			unset($sheet );
			unset($num_rows );
			unset($highestColumn );
			$file = $mydir.$value;
			$objPHPExcel = PHPExcel_IOFactory::load($file);
			$sheet = $objPHPExcel->getSheet(0);

			$num_rows = $sheet->getHighestRow();
			$highestColumn = $sheet->getHighestColumn();
			$prosess=0;

			//ob_end_flush();
				for ($i = 2; $i <= $num_rows; $i++) {
					unset($sql);
					unset($row_data);
					$row_data = $sheet->rangeToArray('A' . $i . ':' . $highestColumn . $i,
														NULL,
														TRUE,
														FALSE);
					$sql="insert company set ";
					$sql.="KEDCD='{$row_data[0][0]}', ";
					$sql.="ENP_NM='".addslashes($row_data[0][1])."', ";
					$sql.="ENP_NM_TRD='".addslashes($row_data[0][2])."', ";
					$sql.="ENP_NM_ENG='".addslashes($row_data[0][3])."', ";
					$sql.="ENP_TYP='{$row_data[0][4]}', ";
					$sql.="ENP_SZE='{$row_data[0][5]}', ";
					$sql.="GRDT_PLN_DT='{$row_data[0][6]}', ";
					$sql.="ENP_FCD='{$row_data[0][7]}', ";
					$sql.="ESTB_FCD='{$row_data[0][8]}', ";
					$sql.="ENP_SCD='{$row_data[0][9]}', ";
					$sql.="ENP_SCD_CHG_DT='{$row_data[0][10]}', ";
					$sql.="PUBI_FCD='{$row_data[0][11]}', ";
					$sql.="VENP_YN='{$row_data[0][12]}', ";
					$sql.="ENP_FORM_FR='{$row_data[0][13]}', ";
					$sql.="BZC_CD='{$row_data[0][14]}', ";
					$sql.="FS_BZC_CD='{$row_data[0][15]}', ";
					$sql.="GRP_CD='{$row_data[0][16]}', ";
					$sql.="GRP_NM='".addslashes($row_data[0][17])."', ";
					$sql.="CONO_PID='{$row_data[0][18]}', ";
					$sql.="ESTB_DT='{$row_data[0][19]}', ";
					$sql.="IPO_CD='{$row_data[0][20]}', ";
					$sql.="TRDBZ_RPT_NO='{$row_data[0][21]}', ";
					$sql.="LIST_DT='{$row_data[0][22]}', ";
					$sql.="DLIST_DT='{$row_data[0][23]}', ";
					$sql.="MTX_BNK_CD='{$row_data[0][24]}', ";
					$sql.="MTX_BNK_NM='".addslashes($row_data[0][25])."', ";
					$sql.="OVD_TX_BNK_CD='{$row_data[0][26]}', ";
					$sql.="OVD_TX_BNK_NM='".addslashes($row_data[0][27])."', ";
					$sql.="ACCT_EDDT='{$row_data[0][28]}', ";
					$sql.="HPAGE_URL='".addslashes($row_data[0][29])."', ";
					$sql.="EMAIL='".addslashes($row_data[0][30])."', ";
					$sql.="STD_DT='{$row_data[0][31]}', ";
					$sql.="BZNO='{$row_data[0][32]}', ";
					$sql.="LOC_ZIP='{$row_data[0][33]}', ";
					$sql.="LOC_ADDRA='".addslashes($row_data[0][34])."', ";
					$sql.="LOC_ADDRB='".addslashes($row_data[0][35])."', ";
					$sql.="TEL_NO='{$row_data[0][36]}', ";
					$sql.="FAX_NO='{$row_data[0][37]}', ";
					$sql.="LABORER_SUM='{$row_data[0][38]}', ";
					$sql.="PD_NM='".addslashes($row_data[0][39])."', ";
						$sql.="REL_KEDCD='{$row_data[0][41]}', ";
					$sql.="REL_ESTB_DT='{$row_data[0][42]}', ";
					$sql.="LOC_RDNM_ZIP='{$row_data[0][43]}', ";
					$sql.="LOC_RDNM_ADDRA='".addslashes($row_data[0][44])."', ";
					$sql.="LOC_RDNM_ADDRB='".addslashes($row_data[0][45])."', ";
					$sql.="LOC_RDNM_ADDRB_CONF_YN='{$row_data[0][46]}', ";
					$sql.="LOC_ADDRB_CONF_YN='{$row_data[0][47]}', ";
					$sql.="SALES='{$row_data[0][48]}', ";
					$sql.="ext_1='엑셀업로드'";

					//echo $sql."<br>";

					$prosess++;
					//echo $prosess;
					flush();
					if($sql) {
						sql_query("delete from company where KEDCD='{$row_data[0][0]}'"); 
						$ked=$row_data[0];
						sql_query($sql);
					}
					//sql_query($sqlsum);
					//if($prosess==500) exit;

				}


					
				ob_end_flush();

			//적용된 파일 삭제
			if(strpos($co_row['FILE'],".zip")) @unlink(G5_DATA_PATH."/exldata/".$value);
		}

	  }
	

	//매출액 일괄 업데이트
	 $sql = "UPDATE company A, (SELECT DISTINCT KEDCD, SALES
						   FROM company_financial
						   GROUP BY KEDCD
						  ORDER BY ACCT_DT DESC
						 ) B
		SET A.SALES = B.SALES
	  WHERE A.KEDCD = B.KEDCD";
	  //sql_query($sql);

    $sql = "update company_update_excel set STATUS='S', S_DT=now() where c_id = '$c_id' ";
    sql_query($sql);
		//@unlink(G5_DATA_PATH."/kodata/".$co_row[FILE]);

	}

	
}



if ($w == "" || $w == "u")
{
    if ($_FILES['file']['name'])
    {
        $dest_path = G5_DATA_PATH."/exldata/".$_FILES['file']['name'];
        @move_uploaded_file($_FILES['file']['tmp_name'], $dest_path);
        @chmod($dest_path, G5_FILE_PERMISSION);
    }

    if( $error_msg ){
        alert($error_msg, "./data_list2.php?w=u&amp;c_id=$co_id");
    } else {
        goto_url("./data_list2.php?w=u&amp;c_id=$co_id");
    }
}
else
{
    goto_url("./data_list2.php");
}
