<?php
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
	ini_set('memory_limit','-1');
include_once('./_common.php');

if ($is_admin != 'super') exit;
// 엑셀 다운로드 함수

function array_to_excel($data, $filename){
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
    header('Cache-Control: max-age=0');
    $out = fopen('php://output', 'w');
    fputs($out, "\xEF\xBB\xBF"); // UTF-8 with BOM
    fputcsv($out, array_keys($data[0]), "\t");
    foreach ($data as $row) {
        fputcsv($out, $row, "\t");
    }
    fclose($out);
}

//폴더생성
$ex_dir = G5_DATA_PATH.'/db_down';
@ mkdir(G5_DATA_PATH.'/db_down/', G5_DIR_PERMISSION);
@ mkdir($ex_dir, G5_DIR_PERMISSION);
@chmod($ex_dir, G5_DIR_PERMISSION);
//폴더내 모든 파일 삭제
  $dhandle = opendir($ex_dir);
  if ($dhandle) {
    while (false !== ($fname = readdir($dhandle))) {
       if (is_dir( "{$ex_dir}/{$fname}" )) {
          if (($fname != '.') && ($fname != '..')) {
             $this->rmdir_all("$ex_dir/$fname");
          }
       } else {
          unlink("{$ex_dir}/{$fname}");
       }
    }
    closedir($dhandle);
  }
$n_row=20000; //파일당 자료수
$tcnt=sql_fetch("select count(*) as cnt from company");
$dcnt=round($tcnt['cnt']/$n_row);

for ($div=0;$div<$dcnt;$div++){
$start=$div*$n_row;
$sql = "select * from company order by c_id asc limit $start,$n_row";
$result = sql_query($sql);
ob_start();
// 테이블 출력을 위한 HTML 코드 추가

echo "<table border='1'>";
echo "<tr>";
echo "<th>기업식별ID</th>";
echo "<th>기업체명</th>";
echo "<th>기업체TradeName</th>";
echo "<th>기업체명_영문</th>";
echo "<th>기업유형</th>";
echo "<th>기업규모</th>";
echo "<th>졸업예정일</th>";
echo "<th>기업형태코드</th>";
echo "<th>설립형태</th>";
echo "<th>기업상태</th>";
echo "<th>기업상태변동일</th>";
echo "<th>정부및공공기관구분</th>";
echo "<th>벤쳐기업유무</th>";
echo "<th>기업형태앞뒤구분</th>";
echo "<th>업종코드</th>";
echo "<th>재무업종코드</th>";
echo "<th>그룹코드</th>";
echo "<th>그룹명</th>";
echo "<th>법인_주민등록번호</th>";
echo "<th>설립일자</th>";
echo "<th>기업공개코드</th>";
echo "<th>무역업신고번호</th>";
echo "<th>상장일자</th>";
echo "<th>상장폐지일자</th>";
echo "<th>주거래여신기관코드</th>";
echo "<th>주거래여신기관명</th>";
echo "<th>당좌거래은행코드</th>";
echo "<th>당좌거래은행명</th>";
echo "<th>결산기준월일</th>";
echo "<th>홈페이지</th>";
echo "<th>이메일</th>";
echo "<th>정보기준일자</th>";
echo "<th>사업자번호</th>";
echo "<th>소재지우편번호</th>";
echo "<th>소재지우편번호주소</th>";
echo "<th>소재지_나머지주소</th>";
echo "<th>전화번호</th>";
echo "<th>팩스번호</th>";
echo "<th>상시근로자수(합계)</th>";
echo "<th>제품명</th>";
echo "<th>업종코드 (표준산업분류 9차)</th>";
echo "<th>관련기업KED코드</th>";
echo "<th>관련기업설립일</th>";
echo "<th>도로명소재지우편번호</th>";
echo "<th>도로명소재지 우편번호주소</th>";
echo "<th>도로명소재지 우편번호이하주소</th>";
echo "<th>도로명소재지 주소확인여부</th>";
echo "<th>소재지주소확인여부</th>";
echo "<th>매출액(최근)</th>";
echo "</tr>";


for ($i=0; $row=sql_fetch_array($result); $i++) {

    // 테이블 출력을 위한 HTML 코드 추가

    echo "<tr>";
    echo "<td style=mso-number-format:'\@'>" . $row['KEDCD'] . "</td>";
    echo "<td style=mso-number-format:'\@'>" . $row['ENP_NM'] . "</td>";
    echo "<td style=mso-number-format:'\@'>" . $row['ENP_NM_TRD'] . "</td>";
    echo "<td style=mso-number-format:'\@'>" . $row['ENP_NM_ENG'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['ENP_TYP'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['ENP_SZE'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['GRDT_PLN_DT'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['ENP_FCD'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['ESTB_FCD'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['ENP_SCD'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['ENP_SCD_CHG_DT'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['PUBI_FCD'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['VENP_YN'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['ENP_FORM_FR'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['BZC_CD'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['FS_BZC_CD'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['GRP_CD'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['GRP_NM'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['CONO_PID'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['ESTB_DT'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['IPO_CD'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['TRDBZ_RPT_NO'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['LIST_DT'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['DLIST_DT'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['MTX_BNK_CD'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['MTX_BNK_NM'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['OVD_TX_BNK_CD'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['OVD_TX_BNK_NM'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['ACCT_EDDT'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['HPAGE_URL'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['EMAIL'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['STD_DT'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['BZNO'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['LOC_ZIP'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['LOC_ADDRA'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['LOC_ADDRB'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['TEL_NO'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['FAX_NO'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['LABORER_SUM'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['PD_NM'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['KSIC9_BZC_CD'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['REL_KEDCD'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['REL_ESTB_DT'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['LOC_RDNM_ZIP'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['LOC_RDNM_ADDRA'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['LOC_RDNM_ADDRB'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['LOC_RDNM_ADDRB_CONF_YN'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['LOC_ADDRB_CONF_YN'] . "</td>";
	echo "<td style=mso-number-format:'\@'>" . $row['SALES'] . "</td>";
    echo "</tr>";
}

// 테이블 출력을 위한 HTML 코드 추가
echo "</table>";

$contents = ob_get_contents();
ob_end_clean();


$add_txt="";
if($type=="all") $add_txt="_all";

$dateString = date("Ymd", time());
$fp = fopen(G5_DATA_PATH.'/db_down/db_'.$dateString.'_'.$div.'.xls', 'w+');
fwrite($fp, $contents);
fclose($fp);
}



$passed=base64_decode($config['cf_10']); 
$zip = new ZipArchive();
if( $zip->open(G5_DATA_PATH.'/db_down/db_'.$dateString.$add_txt.'.zip', ZipArchive::CREATE)) {
	$zip->setPassword($passed);
	for($z=0;$z<$dcnt;$z++){
	$zip->addFile(G5_DATA_PATH . '/db_down/db_'.$dateString.'_'.$z.'.xls','/db_'.$dateString.'_'.$z.'.xls');
	}
	$zip->setEncryptionName('/db_down'.$dateString.'.xls', ZipArchive::EM_AES_256);
	$zip->close();
	header('Location: //'.$_SERVER['HTTP_HOST'].'/data/db_down/db_'.$dateString.$add_txt.'.zip'); //다운받게 함
}
for($z=0;$z<$dcnt;$z++){
	unlink(G5_DATA_PATH.'/db_down/db_'.$dateString.'_'.$z.'.xls');
}

?>
