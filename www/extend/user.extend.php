<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

//암호화 복호화
function simple_crypt( $string, $action = 'e' ) {
    // 아래값을 임의로 수정해주세요.
    $secret_key = function_exists('macomaex_env') ? macomaex_env('APP_SECRET_KEY', '') : '';
    $secret_iv = function_exists('macomaex_env') ? macomaex_env('APP_SECRET_IV', '') : '';
 
    $output = false;
    $encrypt_method = "AES-256-CBC";
    $key = hash( 'sha256', $secret_key );
    $iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );
 
    if( $action == 'e' ) {
        $output = base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
    }
    else if( $action == 'd' ){
        $output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
    }
 
    return $output;
}

if (!function_exists('macomaex_kodata_log')) {
	function macomaex_kodata_log($stage, $context = array()) {
		$payload = array(
			'stage' => (string) $stage,
			'context' => is_array($context) ? $context : array('value' => $context),
		);

		error_log('[macomaex_kodata] ' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
	}
}

if (!function_exists('macomaex_parse_kodata_xml')) {
	function macomaex_parse_kodata_xml($rawXml, $context = array()) {
		$rawXml = (string) $rawXml;
		$trimmedXml = trim($rawXml);

		if ($trimmedXml === '') {
			macomaex_kodata_log('empty_response', array_merge($context, array(
				'raw_preview' => '',
			)));

			return array(
				'success' => false,
				'error_code' => 'EMPTY_RESPONSE',
				'error_message' => '기업 정보 응답이 비어 있습니다.',
			);
		}

		$useErrorsBackup = libxml_use_internal_errors(true);
		$xml = simplexml_load_string($trimmedXml, null, LIBXML_NOCDATA);
		$libxmlErrors = libxml_get_errors();
		libxml_clear_errors();
		libxml_use_internal_errors($useErrorsBackup);

		if ($xml === false) {
			$errorMessages = array();

			foreach ($libxmlErrors as $error) {
				$message = trim((string) $error->message);
				if ($message !== '') $errorMessages[] = $message;
			}

			macomaex_kodata_log('xml_parse_failed', array_merge($context, array(
				'raw_preview' => substr($trimmedXml, 0, 500),
				'libxml_errors' => $errorMessages,
			)));

			return array(
				'success' => false,
				'error_code' => 'XML_PARSE_ERROR',
				'error_message' => '기업 정보 응답을 해석하지 못했습니다.',
			);
		}

		return array(
			'success' => true,
			'xml' => $xml,
			'json' => json_encode($xml, JSON_UNESCAPED_UNICODE),
		);
	}
}

//사업자등록증 하이픈
function licenseNum($str=""){
	$tmp="";
	if(strlen($str) < 4){
		return $str;
	}elseif(strlen($str) <7){
		$tmp .= substr($str, 0, 3);
		$tmp .= '-';
		$tmp .= substr($str, 3);
		return $tmp;
	}else{
		$tmp .= substr($str, 0, 3);
		$tmp .= '-';
		$tmp .= substr($str,3, 2);
		$tmp .= '-';
		$tmp .= substr($str,5);
		return $tmp;
	}
}

//회사 정보가져오기
function getComapyInfo($no){
	if(!$no) return false;
	$sql="select * from company where KEDCD = '{$no}' ";
	$result=sql_fetch($sql);
	 return $result;
}

//산업분류 정보가져오기
function getKsscInfo($no){
	if(!$no) return false;
	$sql="select * from kssc where k_code = '{$no}' ";
	$result=sql_fetch($sql);
	 return $result;
}
//산업분류 정보가져오기(9차)
function getKsscInfo9($no){
	if(!$no) return false;
	$sql="select * from kssc_9 where k_code = '{$no}' ";
	$result=sql_fetch($sql);
	 return $result;
}

//재무 정보가져오기
function getFinanceInfo($no,$year=''){
	if(!$no) return false;
	$sql="select * from company_financial where KEDCD= '{$no}'";
	if($year) $sql.=" and ACCT_DT like '{$year}%'";
	$result=sql_query($sql);
	 for ($i=0; $row=sql_fetch_array($result); $i++) {
		 $result_list[]=$row;
	 }
	 return $result_list;
}

//맴버쉽 정보 가져오기
function get_membership(){
	$membership_list = array();
	$sql="select * from g5_membership_manage order by no asc";
	$result=sql_query($sql);
	 for ($i=0; $row=sql_fetch_array($result); $i++) {
		 $membership_list[]=$row;
	 }
	 return $membership_list;
}

//로그인 체크
function login_chk(){
	global $member;
	if(!$member['mb_id']) ALERT("로그인 후 이용하세요.","/bbs/login.php?url=".$_SERVER["REQUEST_URI"]);
}

//맴버쉽 정보가져오기
function getMembership($no){
	if(!$no) return false;
	$sql="select * from g5_membership_manage where no = '{$no}'";
	$result=sql_fetch($sql);
	 return $result;
}

//페이먼츠 정보가져오기
function getPaymentInfo($no){
	if(!$no) return false;
	$sql="select * from g5_membership_payment where order_no = '{$no}' ";
	$result=sql_fetch($sql);
	 return $result;
}

//유저 정보가져오기
function getUserInfo($id){
	if(!$id) return false;
	$sql="select * from g5_member where mb_id= '{$id}'";
	$result=sql_fetch($sql);
	 return $result;
}

//유저 정보가져오기
function getCompUser($cd){
	if(!$id) return false;
	$sql="select * from g5_member where mb_company= '{$cd}' and mb_leave_date=''";
	$result=sql_fetch($sql);
	 return $result;
}

//즐겨찾기 정보가져오기
function getFavorite($user){
	if(!$user) return false;
	$sql="select * from g5_favorite where mb_id= '{$user}'";
	$result=sql_query($sql);
	$r = array(); 
	while($row = sql_fetch_array($result)) { 
		$r[] = $row['KEDCD'];
	}
	 return $r;
}

//회원 맴버쉽 정보 가져오기
function getUserMembership($id=""){
	//맴버쉽 정보
	GLOBAL $member;
	if(!$id) $id=$member['mb_id'];
	if(!$id) {return false; exit;}
	$today = date("Y-m-d");
	$msinfo = sql_fetch("select * from g5_membership_status where uid='{$member['mb_id']}' and date_begin <= '{$today }' and date_end >='{$today }'  and (status='S' or status='T') order by no desc" );
	//맴버쉽이 없을경우 무료 맴버쉽으로 지정
	if(empty($msinfo)) $membership_info = sql_fetch("select * from g5_membership_manage order by no asc limit 1");
	else $membership_info = sql_fetch("select * from g5_membership_manage where no='{$msinfo['membership_parent_no']}'" );

	//마스터의 경우 최고 맴버쉽
	if($member['mb_level']==20) {
		$membership_info = sql_fetch("select * from g5_membership_manage order by no desc limit 1");
		$msinfo['membership_parent_no'] = $membership_info['no'];
	}
	$result[]=$msinfo;
	$result[]=$membership_info;

	return $result;
}
##매칭 라이브러리
function getMatchingCompanyInfo($kind, $no){

	
	$tbl = "";
	if($kind == "M")
		$tbl = "g5_matching_manufacturer";
	else if($kind == "D")
		$tbl = "g5_matching_distributor";
	else if($kind == "C")
		$tbl = "g5_matching_closemall";

	$query = "select  
					 a.*
						from $tbl a
					where a.no = $no";
	//echo $query;
	$result=sql_query($query);
	$r = array(); 
	while($row = sql_fetch_array($result)) { 
		$r[] = $row; 
	} 
	if($result){
		mysqli_free_result( $result );
	}
	return $r;
	

}

function getKindString($kind){

	$rtn = "";
	if($kind == "M")
		$rtn = "제조사";
	else if($kind == "D")
		$rtn = "유통사";
	else if($kind == "C")
		$rtn = "기타";

	return $rtn;

}

function getKODATA($comnum){ // 한국평가데이터 정보 가져오기 API

		if($comnum) {
			$comnum=str_replace("-","",$comnum);
			//사용자 토큰 받기
			$TOKEN_URL = "https://218.55.37.214:6059/enpinfo?userid=igaenconl&process=S&bzno={$comnum}&pidagryn=Y&jmno=ig001&format=XML&Length=10000";
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
			 $curl_error = curl_error($ch);
			 $curl_errno = curl_errno($ch);
			 curl_close($ch);

			 if ($html_ori === false) {
			 	macomaex_kodata_log('curl_failed', array(
			 		'context' => 'getKODATA',
			 		'comnum' => $comnum,
			 		'curl_errno' => $curl_errno,
			 		'curl_error' => $curl_error,
			 	));

			 	return "500|CURL_ERROR|기업 정보 조회 중 통신 오류가 발생했습니다.";
			 }

			 $parsed = macomaex_parse_kodata_xml($html_ori, array(
			 	'context' => 'getKODATA',
			 	'comnum' => $comnum,
			 ));

			 if (!$parsed['success']) {
			 	return "500|".$parsed['error_code']."|".$parsed['error_message'];
			 }

			  $xml = $parsed['xml'];
			  $json = $parsed['json'];
			  $header = $xml -> HEADER;
			  if($header->errcd!="00") {
				  $error=$header->errctt;
				  RETURN "500|".$header->errcd."|".$error;
				  exit;
			  }
			 $arr = $xml -> CONTENTS ;
			//print_r($xml);
			//print_r($arr);
			foreach($arr as $row){
				$bzc_cd=$row->ENPintr0000->bzc_cd;
				//$bzc_cd=substr($bzc_cd,1);
				//echo "select * from kssc where k_code='$bzc_cd'";
				$info = sql_fetch("select * from kssc where k_code='$bzc_cd'");
				//echo "1000|".$row->ENPintr0000->enp_nm_trd." / ".$info['k_code_name'];
				RETURN "1000|".$json;
			}
			 exit;
		}

}

function updateKODATA($comnum){ // 한국평가데이터 정보 업데이트 API

		if($comnum) {
			$comnum=str_replace("-","",$comnum);
						//API 로그기록에 정보가 남아있는지 체크 있으면 해당 기록에서 그냥 가져오기
						$log_chk=sql_fetch("select * from company_api_log where c_bizno='{$comnum}' and DATE(c_datetime)='".date("Y-m-d")."'");
						if(!$log_chk['c_content']){
							$com_d=getKODATA($comnum);
							$com_data=explode("|",$com_d);
							if($com_data[0]==1000){
								$com_info=json_decode($com_data[1]);
								$KEDCD=$com_info->CONTENTS->ENPintr0000->kedcd;
								//API 로그에 기록
								sql_fetch("insert company_api_log set c_bizno='{$comnum}', c_kedcd='{$KEDCD}', c_content='{$com_data[1]}', c_datetime=now(), c_ip='{$_SERVER["REMOTE_ADDR"]}'");
							}
						}else{
							//API기록에서 정보가져옴
							$com_info=json_decode($log_chk['c_content']);
						}

								$KEDCD=$com_info->CONTENTS->ENPintr0000->kedcd;
								$enp_nm=$com_info->CONTENTS->ENPintr0000->enp_nm;
								$enp_nm_trd=$com_info->CONTENTS->ENPintr0000->enp_nm_trd;//회사명
								$enp_nm_eng=$com_info->CONTENTS->ENPintr0000->enp_nm_eng;
								$cono_pid=$com_info->CONTENTS->ENPintr0000->cono_pid; //법인번호
								$enp_nm_eng=$com_info->CONTENTS->ENPintr0000->enp_nm_eng;
								$reper_name=$com_info->CONTENTS->ENPintr0000->reper_name; //대표자명
								$enp_typ=$com_info->CONTENTS->ENPintr0000->enp_typ;//기업유형
								$enp_sze=$com_info->CONTENTS->ENPintr0000->enp_sze;
								$ipo_cd=$com_info->CONTENTS->ENPintr0000->ipo_cd;
								$enp_scd=$com_info->CONTENTS->ENPintr0000->enp_scd;
								$enp_scd_chg_dt=$com_info->CONTENTS->ENPintr0000->enp_scd_chg_dt;
								$enp_form_fr=$com_info->CONTENTS->ENPintr0000->enp_form_fr;
								$bzc_cd=$com_info->CONTENTS->ENPintr0000->bzc_cd;
								$bzc_nm=$com_info->CONTENTS->ENPintr0000->bzc_nm;//업종명
								$loc_zip=$com_info->CONTENTS->ENPintr0000->loc_zip;
								$loc_addra=$com_info->CONTENTS->ENPintr0000->loc_addra;//주소
								$loc_addrb=$com_info->CONTENTS->ENPintr0000->loc_addrb;
								$tel_no=$com_info->CONTENTS->ENPintr0000->tel_no;
								$fax_no=$com_info->CONTENTS->ENPintr0000->fax_no;
								$hpage_url=$com_info->CONTENTS->ENPintr0000->hpage_url;
								$email=$com_info->CONTENTS->ENPintr0000->email;
								$estb_dt=$com_info->CONTENTS->ENPintr0000->estb_dt;
								$grp_nm=$com_info->CONTENTS->ENPintr0000->grp_nm;
								$mpd=$com_info->CONTENTS->ENPintr0000->mpd;//주생산품
								$std_dt=$com_info->CONTENTS->ENPintr0000->std_dt;
								$enp_fcd=$com_info->CONTENTS->ENPintr0000->enp_fcd;//기업형태
								
								$fs_acct_dt=$com_info->CONTENTS->ENPfsdt0001->fs_acct_dt;
								for($tmp=0;$tmp<=20;$tmp++){
									${"fs_val".$tmp}=$com_info->CONTENTS->ENPfsdt0001->{"fs_val".$tmp};
								}

								$fs1_acct_dt=$com_info->CONTENTS->ENPfsdt0001->fs1_acct_dt;
								for($tmp=0;$tmp<=20;$tmp++){
									${"fs1_val".$tmp}=$com_info->CONTENTS->ENPfsdt0001->{"fs1_val".$tmp};
								}

								$fs2_acct_dt=$com_info->CONTENTS->ENPfsdt0001->fs2_acct_dt;
								for($tmp=0;$tmp<=20;$tmp++){
									${"fs2_val".$tmp}=$com_info->CONTENTS->ENPfsdt0001->{"fs2_val".$tmp};
								}

								//주식정보
								$scnt=$com_info->CONTENTS->ENPsthr0001->cnt;
								if(is_object($scnt)) unset($scnt);

								if($scnt>1) {
									$ISS_STK_CN=0;
									$CSTK_CN=0;
									$PSTK_CN=0;
									$slist=$com_info->CONTENTS->ENPsthr0001->list;
									for($tmp=0;$tmp<$scnt;$tmp++){
										$own_stk_cn=$slist[$tmp]->own_stk_cn; //소유주식
										$cstk_cn=$slist[$tmp]->cstk_cn; //보통주
										$pstk_cn=$slist[$tmp]->pstk_cn; //우선주
										
										$ISS_STK_CN=$ISS_STK_CN+$own_stk_cn; //발행주식수 총계
										$CSTK_CN=$CSTK_CN+$cstk_cn; //보통주식 총계
										$PSTK_CN=$PSTK_CN+$pstk_cn; //우선주식 총계
									}
									$STD_DT=$slist[0]->std_dt;
								}
								if($scnt==1) {
									$slist=$com_info->CONTENTS->ENPsthr0001->list;
									$ISS_STK_CN=$slist->own_stk_cn; //소유주식
									$CSTK_CN=$slist->cstk_cn; //보통주
									$PSTK_CN=$slist->pstk_cn; //우선주
									$STD_DT=$slist->std_dt;
								}
		
								


								if(is_object($enp_scd_chg_dt)) unset($enp_scd_chg_dt);
								if(is_object($grp_nm)) unset($grp_nm);
								if(is_object($enp_nm_trd)) unset($enp_nm_trd);
								if(is_object($enp_nm)) unset($enp_nm);
								if(is_object($enp_nm_eng)) unset($enp_nm_eng);
								if(is_object($cono_pid)) unset($cono_pid);
								if(is_object($reper_name)) unset($reper_name);
								if(is_object($enp_typ)) unset($enp_typ);
								if(is_object($enp_sze)) unset($enp_sze);
								if(is_object($enp_fcd)) unset($enp_fcd);
								if(is_object($enp_scd)) unset($enp_scd);
								if(is_object($enp_form_fr)) unset($enp_form_fr);
								if(is_object($estb_dt)) unset($estb_dt);
								if(is_object($ipo_cd)) unset($ipo_cd);
								if(is_object($std_dt)) unset($std_dt);
								if(is_object($loc_addra)) unset($loc_addra);
								if(is_object($fs_acct_dt)) unset($fs_acct_dt);

								if(is_object($hpage_url)) unset($hpage_url);
								if(is_object($email))  unset($email);
								if(is_object($tel_no))  unset($tel_no);
								if(is_object($fax_no))  unset($fax_no);
								if(is_object($fs_val9))  unset($fs_val9);

								if(is_object($STD_DT))  unset($STD_DT);
								if(is_object($ISS_STK_CN))  unset($ISS_STK_CN);
								if(is_object($CSTK_CN))  unset($CSTK_CN);
								if(is_object($PSTK_CN))  unset($PSTK_CN);

								if(!$bzno) $bzno=$comnum;
								//echo $bzno;

								//기업개요 정보 등록
								$sql="delete from company where KEDCD='{$KEDCD}'";
								sql_query($sql);
								$sql="insert company set ";
								$sql.="KEDCD='{$KEDCD}', ";
								$sql.="ENP_NM='".addslashes($enp_nm)."', ";
								$sql.="ENP_NM_TRD='".addslashes($enp_nm_trd)."', ";
								$sql.="ENP_NM_ENG='".addslashes($enp_nm_eng)."', ";
								$sql.="ENP_TYP='{$enp_typ}', ";
								$sql.="ENP_SZE='{$enp_sze}', ";
								$sql.="GRDT_PLN_DT='{$row_data[6]}', ";
								$sql.="ENP_FCD='{$enp_fcd}', ";
								$sql.="ESTB_FCD='{$row_data[8]}', ";
								$sql.="ENP_SCD='{$enp_scd}', ";
								$sql.="ENP_SCD_CHG_DT='{$enp_scd_chg_dt}', ";
								$sql.="PUBI_FCD='{$row_data[11]}', ";
								$sql.="VENP_YN='{$row_data[12]}', ";
								$sql.="ENP_FORM_FR='{$enp_form_fr}', ";
								$sql.="BZC_CD='{$bzc_cd}', ";
								$sql.="FS_BZC_CD='{$row_data[15]}', ";
								$sql.="GRP_CD='{$row_data[16]}', ";
								$sql.="GRP_NM='".addslashes($grp_nm)."', ";
								$sql.="CONO_PID='{$cono_pid}', ";
								$sql.="ESTB_DT='{$estb_dt}', ";
								$sql.="IPO_CD='{$ipo_cd}', ";
								$sql.="TRDBZ_RPT_NO='{$row_data[21]}', ";
								$sql.="LIST_DT='{$row_data[22]}', ";
								$sql.="DLIST_DT='{$row_data[23]}', ";
								$sql.="MTX_BNK_CD='{$row_data[24]}', ";
								$sql.="MTX_BNK_NM='".addslashes($row_data[25])."', ";
								$sql.="OVD_TX_BNK_CD='{$row_data[26]}', ";
								$sql.="OVD_TX_BNK_NM='".addslashes($row_data[27])."', ";
								$sql.="ACCT_EDDT='{$row_data[28]}', ";
								$sql.="HPAGE_URL='{$hpage_url}', ";
								$sql.="EMAIL='{$email}', ";
								$sql.="STD_DT='{$std_dt}', ";
								$sql.="BZNO='{$bzno}', ";
								$sql.="LOC_ZIP='{$loc_zip}', ";
								$sql.="LOC_ADDRA='".addslashes($loc_addra)."', ";
								$sql.="LOC_ADDRB='".addslashes($loc_addrb)."', ";
								$sql.="TEL_NO='{$tel_no}', ";
								$sql.="FAX_NO='{$fax_no}', ";
								$sql.="LABORER_SUM='{$row_data[38]}', ";
								$sql.="PD_NM='".addslashes($mpd)."', ";
								$sql.="KSIC9_BZC_CD='{$row_data[40]}', ";
								$sql.="REL_KEDCD='{$row_data[41]}', ";
								$sql.="REL_ESTB_DT='{$row_data[42]}', ";
								$sql.="LOC_RDNM_ZIP='{$row_data[43]}', ";
								$sql.="LOC_RDNM_ADDRA='".addslashes($row_data[44])."', ";
								$sql.="LOC_RDNM_ADDRB='".addslashes($row_data[45])."', ";
								$sql.="LOC_RDNM_ADDRB_CONF_YN='{$row_data[46]}', ";
								$sql.="LOC_ADDRB_CONF_YN='{$row_data[47]}', ";
								$sql.="SALES='{$fs_val9}', ";
								$sql.="ext_1='API 입력' ";
								sql_query($sql);

								if(is_object($fs_val9))  unset($fs_val9);
								if(is_object($fs_val3))  unset($fs_val3);
								if(is_object($fs_val7))  unset($fs_val7);
								if(is_object($fs_val8))  unset($fs_val8);
								if(is_object($fs_val9))  unset($fs_val9);
								if(is_object($fs_val11))  unset($fs_val11);
								if(is_object($fs_val16))  unset($fs_val16);
								//주요재무정보 등록
								if($fs_acct_dt){
								$sql="delete from company_financial where KEDCD='{$KEDCD}' and ACCT_DT='{$fs_acct_dt}'";
								sql_query($sql);

								$sql="insert company_financial set ";
								$sql.="KEDCD='{$KEDCD}', ";
								$sql.="ACCT_DT='{$fs_acct_dt}', "; //결산기준연월일
								$sql.="SUMASSET='{$fs_val3}', ";//총자산
								$sql.="PAYMENTFUND='{$fs_val7}', "; //납입자본금
								$sql.="FUNDTOTAL='{$fs_val8}', ";//자본총계
								$sql.="SALES='{$fs_val9}', ";//매출액
								$sql.="PROFIT='{$fs_val11}', ";//영업이익
								$sql.="TERMNETPROFIT='{$fs_val16}', ";//당기순이익
								$sql.="ext_1='API 입력' ";
								
								sql_query($sql);
								}

								if(is_object($fs1_acct_dt))  unset($fs1_acct_dt);
								if(is_object($fs1_val3))  unset($fs1_val3);
								if(is_object($fs1_val7))  unset($fs1_val7);
								if(is_object($fs1_val8))  unset($fs1_val8);
								if(is_object($fs1_val9))  unset($fs1_val9);
								if(is_object($fs1_val11))  unset($fs1_val11);
								if(is_object($fs1_val16))  unset($fs1_val16);
								if($fs1_acct_dt){
								$sql="delete from company_financial where KEDCD='{$KEDCD}' and ACCT_DT='{$fs1_acct_dt}'";
								sql_query($sql);
								$sql="insert company_financial set ";
								$sql.="KEDCD='{$KEDCD}', ";
								$sql.="ACCT_DT='{$fs1_acct_dt}', "; //결산기준연월일
								$sql.="SUMASSET='{$fs1_val3}', ";//총자산
								$sql.="PAYMENTFUND='{$fs1_val7}', "; //납입자본금
								$sql.="FUNDTOTAL='{$fs1_val8}', ";//자본총계
								$sql.="SALES='{$fs1_val9}', ";//매출액
								$sql.="PROFIT='{$fs1_val11}', ";//영업이익
								$sql.="TERMNETPROFIT='{$fs1_val16}', ";//당기순이익
								$sql.="ext_1='API 입력' ";
								sql_query($sql);
								}

								if(is_object($fs2_acct_dt))  unset($fs2_acct_dt);
								if(is_object($fs2_val3))  unset($fs2_val3);
								if(is_object($fs2_val7))  unset($fs2_val7);
								if(is_object($fs2_val8))  unset($fs2_val8);
								if(is_object($fs2_val9))  unset($fs2_val9);
								if(is_object($fs2_val11))  unset($fs2_val11);
								if(is_object($fs2_val16))  unset($fs2_val16);
								if($fs2_acct_dt){
								$sql="delete from company_financial where KEDCD='{$KEDCD}' and ACCT_DT='{$fs2_acct_dt}'";
								sql_query($sql);
								$sql="insert company_financial set ";
								$sql.="KEDCD='{$KEDCD}', ";
								$sql.="ACCT_DT='{$fs2_acct_dt}', "; //결산기준연월일
								$sql.="SUMASSET='{$fs2_val3}', ";//총자산
								$sql.="PAYMENTFUND='{$fs2_val7}', "; //납입자본금
								$sql.="FUNDTOTAL='{$fs2_val8}', ";//자본총계
								$sql.="SALES='{$fs2_val9}', ";//매출액
								$sql.="PROFIT='{$fs2_val11}', ";//영업이익
								$sql.="TERMNETPROFIT='{$fs2_val16}', ";//당기순이익
								$sql.="ext_1='API 입력' ";
								sql_query($sql);
								}

								//주식정보
								if($STD_DT){
								$sql="delete from company_stock where KEDCD='{$KEDCD}' and STD_DT='{$STD_DT}'";
								sql_query($sql);
								$sql="insert company_stock set ";
								$sql.="KEDCD='{$KEDCD}', ";
								$sql.="STD_DT='{$STD_DT}', "; //정보기준일자
								$sql.="ISS_STK_CN='{$ISS_STK_CN}', ";//발행주식총계
								$sql.="CSTK_CN='{$CSTK_CN}', "; //보통주식총계
								$sql.="PSTK_CN='{$PSTK_CN}', ";//우선주식총계
								$sql.="ext_1='API 입력' ";//추가필드
								sql_query($sql);
								}
		}
}


//쿠폰 정보가져오기
function getCouponInfo($id){
	if(strlen($id)<6) {
		$result['chk']="false";
		$result['err']="쿠폰번호를 확인해주세요.";
		return $result;
		return false;
	}
	$today = date("Y-m-d");
	$sql="select * from g5_shop_coupon where cp_id = '{$id}' and ( DATE(cp_start) <= '{$today}' and DATE(cp_end) >= '{$today}' )";
	
	$result=sql_fetch($sql);

		if($result)  {
			$result['chk']="true";
		}else{ 
			$result['chk']="false";
			$result['err']="없는 쿠폰입니다. 다시 확인해주세요.";
		}
	 return $result;
}

//쿠폰 사용하기
function useCoupon($id, $mb_id=""){
	if($id && $mb_id){
		$coupon=getCouponInfo($id);
		$user_info=getUserInfo($mb_id);
		if($coupon['chk']=="true"){
			//쿠폰 사용처리

			$minfo=sql_fetch("select * from g5_membership_manage where no='{$coupon['cp_type']}'"); // 맴버쉽 정보
			$date_begin = date("Y-m-d");
			
			$timestamp = strtotime("+{$coupon['cp_trunc']} months");
			$date_end=date("Y-m-d H:i:s", $timestamp);

			$e_month=date("m", $timestamp );

			$tmpmonth=date("Y-m")."-01";
			$tmpmonth_stamp=strtotime($tmpmonth);
			$nextstamp = strtotime("+{$coupon['cp_trunc']} months",$tmpmonth_stamp);
			$nextmonth = date("m",$nextstamp);

			//다음달 같은 일자가 없는 경우 마지막일자로
			if($nextmonth!=$e_month) {
				$tmpmonth=date("Y-m")."-01";
				$tmpmonth_stamp=strtotime($tmpmonth);
				$nextstamp = strtotime("+{$coupon['cp_trunc']} months",$tmpmonth_stamp);
				$nextmonth = date("Y-m",$nextstamp);
				$lastDay = DATE('t', strtotime($nextmonth )); //마지말일 구하기
				$date_end=$nextmonth."-".$lastDay;
				//$timestamp = strtotime($date_end);
			}

			$selected_cnt_limit=$minfo['selected_cnt']*$coupon['cp_trunc'];
			
			$orderid="C_".time().round((float)microtime(false) * 1000);
			
			//기존 맴버쉽 삭제
			sql_query("delete from g5_membership_status where uid='{$mb_id}'");

			//맴버쉽 추가
			$sql="insert g5_membership_status set ";
			$sql.="uid='{$mb_id}',";
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
			$sql.="selected_cnt='{$selected_cnt_limit}',";
			$sql.="regdate=now(),";
			$sql.="coupon='{$coupon['cp_id']}',";
			$sql.="status='T',";
			$sql.="membership_parent_no='{$coupon['cp_type']}'";
			sql_query($sql);
			
			//맵버쉽 스테이터스 히스토리
			$sql="insert g5_membership_status_history set ";
			$sql.="uid='{$mb_id}',";
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
			$sql.="selected_cnt='{$selected_cnt_limit}',";
			$sql.="regdate=now(),";
			$sql.="coupon='{$coupon['cp_id']}',";
			$sql.="status='T',";
			$sql.="membership_parent_no='{$coupon['cp_type']}',";
			$sql.="memo='쿠폰 사용\\n',";
			$sql.="ext_1='".G5_TIME_YMDHIS."\\n'";
			sql_query($sql);

			//페이먼트 정보 추가
			$sql="insert g5_membership_payment set ";
			$sql.="	uid='{$mb_id}',";
			$sql.="	order_no='{$orderid}',";
			$sql.="	order_goods='{$minfo['title']}',";
			$sql.="	price='{$minfo['price_sale']}',";
			$sql.="	complete_yn='Y',";
			$sql.="	complete_date=now(),";
			$sql.="	regdate=now(),";
			$sql.="	name='{$user_info['mb_name']}',";
			$sql.="	phone='{$user_info['mb_8']}',";
			$sql.="	email='{$mb_id}',";
			$sql.="	tid='{$id}',";
			$sql.="	mid='COUPON',";
			$sql.="	moid='',";
			$sql.="	pay_method='',";
			$sql.="	app_date='{$pgauthdate}',";
			$sql.="	appl_time='{$pgauthtime}',";
			$sql.="	buyer_name='',";
			$sql.="	buyer_tel='',";
			$sql.="	buyer_email='',";
			$sql.="	appl_num='',";
			$sql.="	card_num='',";
			$sql.="	card_Code='',";
			$sql.="	card_check_flag='',";
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
			$sql.="	membership_parent_no='{$coupon['cp_type']}'	";
			sql_query($sql);

			//쿠폰사용 히스토리 저장
			$sql="insert g5_shop_coupon_log set ";
			$sql.="cp_id='{$id}',";
			$sql.="mb_id='{$mb_id}',";
			$sql.="cp_subject='{$coupon['cp_subject']}',";
			$sql.="cp_start='{$coupon['cp_start']}',";
			$sql.="cp_end='{$coupon['cp_end']}',";
			$sql.="cp_type='{$coupon['cp_type']}',";
			$sql.="cp_trunc='{$coupon['cp_trunc']}',";
			$sql.="cp_price='{$coupon['cp_price']}',";
			$sql.="cl_datetime=now()";
			sql_query($sql);

			//셀렉티드 추가 처리
			if($minfo['selected_cnt']>0){
				
				/*
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
				*/
				
				$sql="insert g5_selected set ";
				$sql.="s_subject='{$user_info['mb_name']}',";
				$sql.="KEDCD='{$user_info['mb_company']}',";
				$sql.="s_membership='{$minfo['no']}',";
				$sql.="mb_id='{$mb_id}',";
				$sql.="s_start='{$date_begin}',";
				$sql.="s_end='{$date_end}',";
				$sql.="s_info='',";
				$sql.="s_cnt='0',";
				$sql.="s_cnt_limit='{$selected_cnt_limit}',";
				$sql.="s_modi='',";
				$sql.="s_status='S',";
				$sql.="s_datetime=now()";
				sql_query($sql);
			}

			return true;
		}else{
			alert($coupon['err']);
			return false;
		}
	}else{
		return false;
	}
}


//selected 기간정보 가져오기
function SelectedRange($id=""){
	if(!$id) {
		return false;
	}
	//셀렉티드 정보
    $sql = " select * from g5_selected where s_no = '$id' ";
    $cp = sql_fetch($sql);
	$msm_info=sql_fetch("select * from g5_membership_manage where no={$cp['s_membership']}"); //맴버쉽 정보
	$now=time();
	//한달 이상일 경우
	if($msm_info['selected_cnt']!=$cp['s_cnt_limit']){

		$timestamp = strtotime("+1 months");
		$date_end=date("Y-m-d H:i:s", $timestamp);

		$month=$cp['s_cnt_limit']/$msm_info['selected_cnt']; //달 수 구하기
		for($tmp=0;$tmp<$month;$tmp++){

				if(!$date_end){
				$stimestamp = strtotime("+{$tmp} months",strtotime($cp['s_start']));
				$date_begin = date("Y-m-d", $stimestamp);
					$chk_lastDay = DATE('t', strtotime(substr($date_begin ,0,-3)));
					if(substr($cp['s_start'],-2)==$chk_lastDay) $chk_lastDay="true";
				}

				if($date_end){
					//$date_begin = substr($date_end ,0,-2).DATE('t', strtotime(substr($date_end ,0,-3)));
					$date_begin=$date_end;
					$tmpmonth=substr($date_begin ,0,-3)."-01";
					$tmpmonth_stamp=strtotime($tmpmonth);
					$nextstamp = strtotime("+1 months",$tmpmonth_stamp);
					$nextstamp_m=date("Y-m", $nextstamp);
					$next_lastDay = DATE('t', strtotime($nextstamp_m)); //마지말일 구하기
					if(substr($cp['s_start'],-2)==$next_lastDay) $date_end=$nextstamp_m."-".$next_lastDay;
					elseif(substr($cp['s_start'],-2)<$next_lastDay) $date_end=$nextstamp_m."-".substr($cp['s_start'],-2);
					else $date_end=$nextstamp_m."-".$next_lastDay;

				}else{

					//echo "##".substr($cp[s_start],-2)."/".$chk_lastDay."##<br>";
					$timestamp = strtotime("+1 months",strtotime($date_begin));
					$date_end=date("Y-m-d", $timestamp);

					$e_month=date("m", $timestamp );
					$tmpmonth=substr($date_begin ,0,-3)."-01";
					$tmpmonth_stamp=strtotime($tmpmonth);
					$nextstamp = strtotime("+1 months",$tmpmonth_stamp);
					$nextmonth = date("m",$nextstamp);

					//다음달 같은 일자가 없는 경우 마지막일자로
					if($nextmonth!=$e_month) {
						$tmpmonth=substr($date_begin ,0,-3)."-01";
						$tmpmonth_stamp=strtotime($tmpmonth);
						$nextstamp = strtotime("+1 months",$tmpmonth_stamp);
						$nextmonth = date("Y-m",$nextstamp);
						$lastDay = DATE('t', strtotime($nextmonth)); //마지말일 구하기
						$date_end=$nextmonth."-".$lastDay;
						//$timestamp = strtotime($date_end);
					}
				}
				$Range[$tmp]['S']=$date_begin;
				$Range[$tmp]['E']=date("Y-m-d", strtotime("-1 day",strtotime($date_end)));
				//echo $date_begin." - ".date("Y-m-d", strtotime("-1 day",strtotime($date_end)))."<br>";
				$tmp_s=strtotime($Range[$tmp]['S']." 00:00:00");
				$tmp_e=strtotime($Range[$tmp]['E']. "23:59:59");
				if($tmp_s <= $now and $tmp_e >= $now) {
					$Range['NOW_S']=$Range[$tmp]['S'];
					$Range['NOW_E']=$Range[$tmp]['E'];
				}
		}
	}else{
		$Range[0]['S']=$cp['s_start'];
		$Range[0]['E']=date("Y-m-d", strtotime("-1 day",strtotime($cp['s_end'])));
		$tmp_s=strtotime($Range[0]['S']." 00:00:00");
		$tmp_e=strtotime($Range[0]['E']. "23:59:59");
		if($tmp_s <= $now and $tmp_e >= $now) {
			$Range['NOW_S']=$Range[0]['S'];
			$Range['NOW_E']=$Range[0]['E'];
		}
	}
	$Range['LIMIT']=$msm_info['selected_cnt'];
	$Range['MEMBERSHIP']=$msm_info;
	 return $Range;
}

//전화번호 자동하이픈
function format_phone_number($phone_number) {
    // 1. 숫자만 추출합니다.
    $phone_number = preg_replace('/[^0-9]/', '', $phone_number);
    $length = strlen($phone_number);

    // 2. 길이에 따라 다른 형식으로 하이픈을 추가합니다.
    if ($length == 11) { // 휴대폰 번호 또는 일부 지역번호
        return preg_replace('/(\d{3})(\d{4})(\d{4})/', '$1-$2-$3', $phone_number);
    } elseif ($length == 12) { // 0507과 같은 12자리 번호
        return preg_replace('/(\d{4})(\d{4})(\d{4})/', '$1-$2-$3', $phone_number);
    } elseif (strpos($phone_number, '02') === 0 && $length >= 9) { // 서울 지역번호 (02)
        if ($length == 9) {
            return preg_replace('/(\d{2})(\d{3})(\d{4})/', '$1-$2-$3', $phone_number);
        } else { // 10자리
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '$1-$2-$3', $phone_number);
        }
    } elseif ($length == 10) { // 그 외 지역번호
        return preg_replace('/(\d{3})(\d{3})(\d{4})/', '$1-$2-$3', $phone_number);
    } else { // 그 외의 경우는 그대로 반환
        return $phone_number;
    }
}
