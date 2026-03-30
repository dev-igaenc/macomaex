<?
error_reporting(E_ALL);
ini_set('display_errors', '1');

// 다트에서 공시업체목록 api
// https://opendart.fss.or.kr/api/corpCode.xml?crtfc_key=e0a16724ef85615c03a73edbfa6911b1d55191f5

include_once("./_common.php");
include_once("./plugin/simple_html_dom.php");

//사용자 토큰 받기
$TOKEN_URL = "https://opendart.fss.or.kr/api/corpCode.xml?crtfc_key=e0a16724ef85615c03a73edbfa6911b1d55191f5";
//----------curl 변수 세팅----------
 $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)'; //cURL 옵션에 넣을 Agent 정보
 $cookie_nm = './cookie.txt'; 
//----------curl 옵션 세팅----------
 $ch = curl_init();  //curl 형태 초기화
 curl_setopt ($ch, CURLOPT_URL,$TOKEN_URL); //접속할 URL 주소 
 curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, TRUE); 
 curl_setopt ($ch, CURLOPT_SSLVERSION,1); 
 curl_setopt ($ch, CURLOPT_HEADER, 0); 
 curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
 curl_setopt ($ch, CURLOPT_COOKIEJAR, $cookie_nm);
 curl_setopt ($ch, CURLOPT_COOKIEFILE, $cookie_nm); 
 curl_setopt ($ch, CURLOPT_USERAGENT, $agent); 
 curl_setopt ($ch, CURLOPT_TIMEOUT, 30); 
 curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);

//----------curl 실행----------
 $html_ori = curl_exec($ch);  //cURL 함수 실행 후 XML을 String으로 리턴
 //$html_ori = iconv("EUC-KR", "UTF-8", $html_ori);

$fp = fopen("corp.zip", 'a');    // list.txt 파일을 쓰기 전용으로 열고 반환된 파일 포인터를 $fp에 저장.
fwrite($fp, $html_ori);               // list.txt 파일에 $str 변수를 저장함.
fclose($fp);

  $zip = new ZipArchive();
$zipfile = 'corp.zip';

if($zip->open($zipfile) !== false){
for($i=0; $i<$zip->numFiles; $i++){
$filename = $zip->getNameIndex($i);
copy('zip://'.$zipfile.'#'.$filename, './testdir/'.$filename);
}
$zip->close();
}


//echo  $html_ori ;
/*
$html = str_get_html(trim($html_ori));
foreach( $html->find( '<tr>' ) as $tr )
{
  $c_name=addslashes($tr->find( 'td' , 0 )->plaintext);
  $c_code=$tr->find( 'td' , 1 )->plaintext;
  $c_sector=addslashes($tr->find( 'td' , 2 )->plaintext);
  $c_major=$tr->find( 'td' , 3 )->plaintext;
  $c_ldate=$tr->find( 'td' , 4 )->plaintext;
  $c_ceo=addslashes($tr->find( 'td' , 6 )->plaintext);
  $c_homepage=$tr->find( 'td' , 7 )->plaintext;
  $c_local=$tr->find( 'td' , 8 )->plaintext;
  if(!$c_name) continue;
  $sql="insert into g5_corp set c_name='{$c_name}', c_code='{$c_code}', c_sector='{$c_sector}', c_major='{$c_major}', c_ldate='{$c_ldate}', c_ceo='{$c_ceo}', c_homepage='{$c_homepage}', c_local='{$c_local}', c_datetime='".G5_TIME_YMDHIS."'";
  echo $sql."<br>";
  //sql_query($sql);
}
*/
?>