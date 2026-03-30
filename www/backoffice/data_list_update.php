<?php
set_time_limit(0);
ini_set('memory_limit', '1000M');
$sub_menu = '500200';
include_once('./_common.php');

if ($w == "u" || $w == "d")
    check_demo();


//check_admin_token();


if ($c_id)
{
    $sql = " select * from company_update where c_id = '$c_id' ";
    $co_row = sql_fetch($sql);
}

$c_id = isset($_GET['c_id']) ? $_GET['c_id'] : '';

@mkdir(G5_DATA_PATH."/kodata", G5_DIR_PERMISSION);
@chmod(G5_DATA_PATH."/kodata", G5_DIR_PERMISSION);



$error_msg = '';

$file_name=$_FILES['file']['name'];

if ($w == "")
{
    $sql = " insert company_update
				set FILE = '$file_name',
				ACCT_DT=now()
				 ";
    sql_query($sql);
}
else if ($w == "u")
{
	
    $sql = " update company_update
                set FILE = '$file_name',
              where c_id = '$c_id' ";
    sql_query($sql);
}
else if ($w == "d")
{
    @unlink(G5_DATA_PATH."/kodata/".$co_row[FILE]);

    $sql = " delete from company_update where c_id = '$c_id' ";
    sql_query($sql);
}
else if ($w == "df")
{
    @unlink(G5_DATA_PATH."/kodata/".$co_row[FILE]);
}
else if ($w == "s")
{
	$type_table['02']="company";
	$type_table['26']="company_client";
	$type_table['03']="company_financial";
	$type_table['12']="company_stock";

	if($co_row[FILE]) {
		//압축해제
		ob_start();
		system('unzip -x '.G5_DATA_PATH.'/kodata/'.$co_row[FILE].' -d '.G5_DATA_PATH.'/kodata/');
		$run= ob_get_contents();
		ob_end_clean();
		$run_count=substr_count($run,"inflating");
		ob_start();
		echo "총 ".$run_count."개 파일 해제<br>";
		ob_flush();
		flush();
		//업데이트 파일 DB적용
	  $mydir = G5_DATA_PATH.'/kodata/'; 
	  $myfiles = array_diff(scandir($mydir), array('.', '..',$co_row[FILE])); 
	  foreach($myfiles as $value){
		  if(strpos($value,".jar")) continue; //압축파일은 지우지 않기
		  echo $value."<br>";
		  unset($type);
		  unset($file_data);
		  unset($ked);
		 $type=explode("_",$value);
		 $type=substr($type[0],-2);
		$file_data=file($mydir.$value);
		//$file_data=iconv("EUC-KR", "UTF-8",$file_data);
		if($type){
			
			foreach($file_data as $row){

				unset($sql);
				$row=iconv("EUC-KR", "UTF-8",$row);
				$row_data=explode("|",$row);
				if($type=="02"){//기업개요
					$row_data=explode("|",$row);

/*
					$sql="INSERT INTO company (
					KEDCD, 
					ENP_NM,
					ENP_NM_TRD,
					ENP_NM_ENG,
					ENP_TYP,
					ENP_SZE,
					GRDT_PLN_DT,
					ENP_FCD,
					ESTB_FCD
					) VALUES (
					'{$row_data[0]}', 
					'".addslashes($row_data[1])."'
					) ON 
					DUPLICATE KEY UPDATE 
					KEDCD='{$row_data[0]}', 
					ENP_NM='".addslashes($row_data[1])."'
					";
*/

					$sql="insert company set ";
					$sql.="KEDCD='{$row_data[0]}', ";
					$sql.="ENP_NM='".addslashes($row_data[1])."', ";
					$sql.="ENP_NM_TRD='".addslashes($row_data[2])."', ";
					$sql.="ENP_NM_ENG='".addslashes($row_data[3])."', ";
					$sql.="ENP_TYP='{$row_data[4]}', ";
					$sql.="ENP_SZE='{$row_data[5]}', ";
					$sql.="GRDT_PLN_DT='{$row_data[6]}', ";
					$sql.="ENP_FCD='{$row_data[7]}', ";
					$sql.="ESTB_FCD='{$row_data[8]}', ";
					$sql.="ENP_SCD='{$row_data[9]}', ";
					$sql.="ENP_SCD_CHG_DT='{$row_data[10]}', ";
					$sql.="PUBI_FCD='{$row_data[11]}', ";
					$sql.="VENP_YN='{$row_data[12]}', ";
					$sql.="ENP_FORM_FR='{$row_data[13]}', ";
					$sql.="BZC_CD='{$row_data[14]}', ";
					$sql.="FS_BZC_CD='{$row_data[15]}', ";
					$sql.="GRP_CD='{$row_data[16]}', ";
					$sql.="GRP_NM='".addslashes($row_data[17])."', ";
					$sql.="CONO_PID='{$row_data[18]}', ";
					$sql.="ESTB_DT='{$row_data[19]}', ";
					$sql.="IPO_CD='{$row_data[20]}', ";
					$sql.="TRDBZ_RPT_NO='{$row_data[21]}', ";
					$sql.="LIST_DT='{$row_data[22]}', ";
					$sql.="DLIST_DT='{$row_data[23]}', ";
					$sql.="MTX_BNK_CD='{$row_data[24]}', ";
					$sql.="MTX_BNK_NM='".addslashes($row_data[25])."', ";
					$sql.="OVD_TX_BNK_CD='{$row_data[26]}', ";
					$sql.="OVD_TX_BNK_NM='".addslashes($row_data[27])."', ";
					$sql.="ACCT_EDDT='{$row_data[28]}', ";
					$sql.="HPAGE_URL='".addslashes($row_data[29])."', ";
					$sql.="EMAIL='".addslashes($row_data[30])."', ";
					$sql.="STD_DT='{$row_data[31]}', ";
					$sql.="BZNO='{$row_data[32]}', ";
					$sql.="LOC_ZIP='{$row_data[33]}', ";
					$sql.="LOC_ADDRA='".addslashes($row_data[34])."', ";
					$sql.="LOC_ADDRB='".addslashes($row_data[35])."', ";
					$sql.="TEL_NO='{$row_data[36]}', ";
					$sql.="FAX_NO='{$row_data[37]}', ";
					$sql.="LABORER_SUM='{$row_data[38]}', ";
					$sql.="PD_NM='".addslashes($row_data[39])."', ";
					$sql.="KSIC9_BZC_CD='{$row_data[40]}', ";
					$sql.="REL_KEDCD='{$row_data[41]}', ";
					$sql.="REL_ESTB_DT='{$row_data[42]}', ";
					$sql.="LOC_RDNM_ZIP='{$row_data[43]}', ";
					$sql.="LOC_RDNM_ADDRA='".addslashes($row_data[44])."', ";
					$sql.="LOC_RDNM_ADDRB='".addslashes($row_data[45])."', ";
					$sql.="LOC_RDNM_ADDRB_CONF_YN='{$row_data[46]}', ";
					$sql.="LOC_ADDRB_CONF_YN='{$row_data[47]}' ";
				}
				if($type=="03"){//주요 재무정보
					$sql="insert {$type_table[$type]} set ";
					$sql.="KEDCD='{$row_data[0]}', ";
					$sql.="ACCT_DT='{$row_data[1]}', ";
					$sql.="SUMASSET='{$row_data[2]}', ";
					$sql.="PAYMENTFUND='{$row_data[3]}', ";
					$sql.="FUNDTOTAL='{$row_data[4]}', ";
					$sql.="SALES='{$row_data[5]}', ";
					$sql.="PROFIT='{$row_data[6]}', ";
					$sql.="TERMNETPROFIT='{$row_data[7]}' ";
				}
				if($type=="12"){//주식관련사항
					$sql="insert {$type_table[$type]} set ";
					$sql.="KEDCD='{$row_data[0]}', ";
					$sql.="STD_DT='{$row_data[1]}', ";
					$sql.="STH_CN='{$row_data[2]}', ";
					$sql.="STK_FVAL_AM='{$row_data[3]}', ";
					$sql.="STK_AM_SUM='{$row_data[4]}', ";
					$sql.="ISS_STK_CN='{$row_data[5]}', ";
					$sql.="CSTK_CN='{$row_data[6]}', ";
					$sql.="PSTK_CN='{$row_data[7]}', ";
					$sql.="NP='{$row_data[8]}', ";
					$sql.="TTL_AGCER='{$row_data[9]}' ";
				}
				if($type=="26"){ //주요거래처
					$sql="insert {$type_table[$type]} set ";
					$sql.="KEDCD='{$row_data[0]}', ";
					$sql.="STD_DT='{$row_data[1]}', ";
					$sql.="TXPL_CCD='{$row_data[2]}', ";
					$sql.="TXPL_SEQ='{$row_data[3]}', ";
					$sql.="TX_AM_BASE_STDT='{$row_data[4]}', ";
					$sql.="TX_AM_BASE_EDDT='{$row_data[5]}', ";
					$sql.="TXPL_NM='".addslashes($row_data[6])."', ";
					$sql.="TEL_NO='{$row_data[7]}', ";
					$sql.="BZNO='{$row_data[8]}', ";
					$sql.="TXPL_KEDCD='{$row_data[9]}', ";
					$sql.="DO_CCD='{$row_data[10]}', ";
					$sql.="TX_FCD='{$row_data[11]}', ";
					$sql.="PD_NM='".addslashes($row_data[12])."', ";
					$sql.="PD_CD='{$row_data[13]}', ";
					$sql.="TX_AM='{$row_data[14]}', ";
					$sql.="TX_RT='{$row_data[15]}', ";
					$sql.="TX_PRD='{$row_data[16]}', ";
					$sql.="CSH_STL_RT='{$row_data[17]}', ";
					$sql.="CR_STL_RT='{$row_data[18]}', ";
					$sql.="CR_STL_DCN='{$row_data[19]}', ";
					$sql.="CND_ETC='{$row_data[20]}', ";
					$sql.="RMK='{$row_data[21]}' ";
				}
				//echo $sql."<br>";
				if($sql) {
					if($type=="02" && $ked!=$row_data[0]){
						//- 기업개요
						//- 업데이트 방법 : 동일한 KED코드 존재 시 동일한 KED코드를 삭제후 Upload
						//$cnt=sql_fetch("select count(*) as cnt from {$type_table[$type]} where KEDCD='{$row_data[0]}'");
						//if($cnt['cnt']>0) { 
							sql_query("delete from {$type_table[$type]} where KEDCD='{$row_data[0]}'"); 
							//echo "delete from {$type_table[$type]} where KEDCD='{$row_data[0]}'";
							$ked=$row_data[0];
						//}
					}
					
					if($type=="03" && $ked!=$row_data[0]){
						//- 주요 재무정보
						//- 업데이트 방법 : 동일한 KED코드 존재 시 동일한 KED코드를 삭제후 Upload
						//$cnt=sql_fetch("select count(*) as cnt from {$type_table[$type]} where KEDCD='{$row_data[0]}' and ACCT_DT='{$row_data[1]}'");
						//if($cnt['cnt']>0) {
							sql_query("delete from {$type_table[$type]} where KEDCD='{$row_data[0]}' and ACCT_DT='{$row_data[1]}'");
							//echo "delete from {$type_table[$type]} where KEDCD='{$row_data[0]}' and ACCT_DT='{$row_data[1]}'"."<br>";
							$ked=$row_data[0];
						//}
					}
					
					if($type=="12" && $ked!=$row_data[0]){
						//- 주식관련사항
						//- 업데이트 방법 : 동일한 KED코드와 정보기준일자가 일치하는 데이터 존재 시 동일한 KED코드와 정보기준일자가 있는 정보 모두 삭제후 Upload 
						//$cnt=sql_fetch("select count(*) as cnt from {$type_table[$type]} where KEDCD='{$row_data[0]}' and STD_DT='{$row_data[1]}'");
						//if($cnt['cnt']>0) {
							sql_query("delete from {$type_table[$type]} where KEDCD='{$row_data[0]}' and STD_DT='{$row_data[1]}'");
							//echo "delete from {$type_table[$type]} where KEDCD='{$row_data[0]}' and STD_DT='{$row_data[1]}'"."<br>";
							$ked=$row_data[0];
						//}
					}
					
					if($type=="26" && $ked!=$row_data[0]){
						//- 주요거래처
						//- 업데이트 방법 : 동일한 KED코드와 정보기준일자가 일치하는 데이터 존재 시 동일한 KED코드와 정보기준일자가 있는 정보 모두 삭제후 Upload 
						//$cnt=sql_fetch("select count(*) as cnt from {$type_table[$type]} where KEDCD='{$row_data[0]}' and STD_DT='{$row_data[1]}'");
						//if($cnt['cnt']>0) {
							sql_query("delete from {$type_table[$type]} where KEDCD='{$row_data[0]}' and STD_DT='{$row_data[1]}''");
							//echo "delete from {$type_table[$type]} where KEDCD='{$row_data[0]}' and STD_DT='{$row_data[1]}''"."<br>";
							$ked=$row_data[0];
						//}
					}
					//echo $sql."<br>";
					sql_query($sql);
					$prosess++;
				}
				//if($prosess==500) exit;
			};


		}
		//적용된 파일 삭제
		@unlink(G5_DATA_PATH."/kodata/".$value);
	  }
	echo $prosess;

	//매출액 일괄 업데이트 최신 매출액으로 업데이트 안되는 현상이 있어 쿼리 수정 25.04.14
	/*
	 $sql = "UPDATE company A, (SELECT DISTINCT KEDCD, SALES
						   FROM company_financial
						   GROUP BY KEDCD
						  ORDER BY ACCT_DT DESC
						 ) B
		SET A.SALES = B.SALES
	  WHERE A.KEDCD = B.KEDCD";
	*/
	$sql="UPDATE company A, (SELECT DISTINCT KEDCD, SALES
						   FROM company_financial WHERE ACCT_DT=(
								    SELECT MAX(ACCT_DT)
								    FROM company_financial
								    WHERE KEDCD=KEDCD)
						 ) B
		SET A.SALES = B.SALES
	  WHERE A.KEDCD = B.KEDCD";


	  sql_query($sql);

    $sql = "update company_update set STATUS='S', S_DT=now() where c_id = '$c_id' ";
    sql_query($sql);
		//@unlink(G5_DATA_PATH."/kodata/".$co_row[FILE]);

	}

	
}



if ($w == "" || $w == "u")
{
    if ($_FILES['file']['name'])
    {
        $dest_path = G5_DATA_PATH."/kodata/".$_FILES['file']['name'];
        @move_uploaded_file($_FILES['file']['tmp_name'], $dest_path);
        @chmod($dest_path, G5_FILE_PERMISSION);
    }

    if( $error_msg ){
        alert($error_msg, "./data_list.php?w=u&amp;c_id=$co_id");
    } else {
        goto_url("./data_list.php?w=u&amp;c_id=$co_id");
    }
}
else
{
    goto_url("./data_list.php");
}