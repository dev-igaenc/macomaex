<?
error_reporting(E_ALL);
ini_set('display_errors', '1');

// 다트에서 공시업체목록 api
// https://opendart.fss.or.kr/api/corpCode.xml?crtfc_key=e0a16724ef85615c03a73edbfa6911b1d55191f5

include_once("./_common.php");
//include_once("./plugin/simple_html_dom.php");
/*
$url = "https://218.55.37.204:6059/enpinfo?userid=igaenconl&process=S&bzno=1198102316&pidagryn=Y&jmno=ig001&format=XML";

$ch = curl_init();                                 //curl 초기화
curl_setopt($ch, CURLOPT_URL, $url);               //URL 지정하기
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    //요청 결과를 문자열로 반환 
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);      //connection timeout 10초 
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);   //원격 서버의 인증서가 유효한지 검사 안함
 
$response = curl_exec($ch);
curl_close($ch);
//print_r($response);
echo $response;
exit;
*/
//사용자 토큰 받기
$TOKEN_URL = "https://218.55.37.204:6059/enpinfo?userid=igaenconl&process=S&bzno=1198102316&pidagryn=Y&jmno=ig001&format=XML&Length=10000";
//----------curl 변수 세팅----------
 $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)'; //cURL 옵션에 넣을 Agent 정보
 $cookie_nm = './cookie.txt'; 
//----------curl 옵션 세팅----------
 $ch = curl_init();  //curl 형태 초기화
 curl_setopt ($ch, CURLOPT_URL,$TOKEN_URL); //접속할 URL 주소 
 curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
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
 //print_r($html_ori);
 echo $html_ori;
  $xml=simplexml_load_string($html_ori) or die("Error: Cannot create object");
 $arr = $xml -> CONTENTS ;
//print_r($xml);
//print_r($arr);
foreach($arr as $row){
	$facilName=explode(",",$row->facilName);
	foreach($facilName as $row2){
	$sql="insert into g5_univers_major set 
	c_seq='{$row->ENPintr0000->bzc_cd }', 
	c_name='{$row->mClass}', 
	c_class='{$row->bzc_nm->lClass}', 
	c_fname='{$row2}', 
	c_datetime='".G5_TIME_YMDHIS."'";
	
	//sql_query($sql);
	//echo $sql;
		$bzc_cd=$row->ENPintr0000->bzc_cd;
		//echo "select * from kssc_11 where k_code='$bzc_cd'";
		$info = sql_fetch("select * from kssc_11 where k_code='$bzc_cd'");

	echo $info['k_code_name'];
	}
}

 exit;

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
