<?
include_once('./_common.php');
include_once(G5_LIB_PATH.'/connect.lib.php');
include_once(G5_LIB_PATH.'/outlogin.lib.php');

// Input normalization for legacy query-building code paths.
$type = isset($_REQUEST['type']) ? trim((string)$_REQUEST['type']) : '';
$id = isset($_REQUEST['id']) ? sql_real_escape_string(trim((string)$_REQUEST['id'])) : '';
$comnum = isset($_REQUEST['comnum']) ? preg_replace('/[^0-9\-]/', '', (string)$_REQUEST['comnum']) : '';
$r_mb_id = isset($_REQUEST['r_mb_id']) ? sql_real_escape_string(trim((string)$_REQUEST['r_mb_id'])) : '';
$kedcd = isset($_REQUEST['kedcd']) ? sql_real_escape_string(trim((string)$_REQUEST['kedcd'])) : '';
$chk = isset($_REQUEST['chk']) ? sql_real_escape_string(trim((string)$_REQUEST['chk'])) : '';
$idx = isset($_REQUEST['idx']) ? (int)$_REQUEST['idx'] : 0;
$typ = isset($_REQUEST['typ']) ? sql_real_escape_string(trim((string)$_REQUEST['typ'])) : '';
$pwd = isset($_REQUEST['pwd']) ? trim((string)$_REQUEST['pwd']) : '';
$orderid = isset($_REQUEST['orderid']) ? sql_real_escape_string(trim((string)$_REQUEST['orderid'])) : '';
$mtype = isset($_REQUEST['mtype']) ? sql_real_escape_string(trim((string)$_REQUEST['mtype'])) : '';
$coupon = isset($_REQUEST['coupon']) ? sql_real_escape_string(trim((string)$_REQUEST['coupon'])) : '';
$request_type = isset($_REQUEST['request_type']) ? sql_real_escape_string(trim((string)$_REQUEST['request_type'])) : '';
$match_content = isset($_REQUEST['match_content']) ? sql_real_escape_string(trim((string)$_REQUEST['match_content'])) : '';

if (!function_exists('membership_refund_lock_name')) {
	function membership_refund_lock_name($order_no) {
		$order_no = preg_replace('/[^0-9A-Za-z_\-]/', '', (string)$order_no);
		if ($order_no === '') return '';
		return 'mcm_refund_' . substr(hash('sha256', $order_no), 0, 32);
	}
}

if (!function_exists('membership_refund_lock_acquire')) {
	function membership_refund_lock_acquire($order_no, $timeout = 0) {
		$lock_name = membership_refund_lock_name($order_no);
		if (!$lock_name) return '';
		$timeout = (int)$timeout;
		if ($timeout < 0) $timeout = 0;
		$row = sql_fetch("select GET_LOCK('{$lock_name}', {$timeout}) as lock_ok");
		if (isset($row['lock_ok']) && (int)$row['lock_ok'] === 1) return $lock_name;
		return '';
	}
}

if (!function_exists('membership_refund_lock_release')) {
	function membership_refund_lock_release($lock_name) {
		$lock_name = preg_replace('/[^0-9A-Za-z_\-]/', '', (string)$lock_name);
		if ($lock_name === '') return;
		sql_fetch("select RELEASE_LOCK('{$lock_name}') as release_ok");
	}
}

if (!function_exists('unlisted_value_lock_name')) {
	function unlisted_value_lock_name($once_key) {
		$once_key = preg_replace('/[^0-9A-Za-z]/', '', (string)$once_key);
		if ($once_key === '') return '';
		return 'mcm_ulv_' . substr($once_key, 0, 32);
	}
}

if (!function_exists('unlisted_value_lock_acquire')) {
	function unlisted_value_lock_acquire($once_key, $timeout = 0) {
		$lock_name = unlisted_value_lock_name($once_key);
		if (!$lock_name) return '';
		$timeout = (int)$timeout;
		if ($timeout < 0) $timeout = 0;
		$row = sql_fetch("select GET_LOCK('{$lock_name}', {$timeout}) as lock_ok");
		if (isset($row['lock_ok']) && (int)$row['lock_ok'] === 1) return $lock_name;
		return '';
	}
}

if (!function_exists('unlisted_value_lock_release')) {
	function unlisted_value_lock_release($lock_name) {
		$lock_name = preg_replace('/[^0-9A-Za-z_\-]/', '', (string)$lock_name);
		if ($lock_name === '') return;
		sql_fetch("select RELEASE_LOCK('{$lock_name}') as release_ok");
	}
}


switch ($type) {

	case "member_check":
		if($id) {
			$chk=sql_fetch("select * from {$g5['member_table']} where mb_id='{$id}'");
			if($chk['mb_id']) {
				if($chk['mb_leave_date']) echo "501|탈퇴한 계정입니다.";
				else if($chk['mb_intercept_date']) echo "501|이용 정지된 계정입니다.";
				else echo "500|가입한 이력이 있습니다.";
			}else{ 
				echo "1000";
			}
		}else{
			echo "500|확인 후 이용하세요.";
		}
	break;

	case "member_check2": //담당자 이메일 체크
		if($id) {
			$chk=sql_fetch("select count(*) as cnt from {$g5['member_table']} where mb_id='{$id}' or mb_5='{$id}'");
			if($chk['cnt']==0) echo "1000";
			else echo "500";
		}else{
			echo "500";
		}
	break;

	case "main_comp_chk": //사업자등록증으로 api검색
		if($comnum) {
			$comnum=str_replace("-","",$comnum);

			//오늘 검색 로그에서 있으면 API말고 로그에서 가져오기
			$log_chk=sql_fetch("select * from company_api_log where c_bizno='{$comnum}' and DATE(c_datetime)='".date("Y-m-d")."'");
			if(!$log_chk['c_content']){
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
				 //print_r($html_ori);
				 //echo $html_ori;
				  $xml=simplexml_load_string($html_ori, null, LIBXML_NOCDATA) or die("Error: Cannot create object");
				  $json = json_encode($xml, JSON_UNESCAPED_UNICODE);
				  $header = $xml -> HEADER;
				  if($header->errcd!="00") {
					  $error=$header->errctt;
					  echo "500|".$header->errcd."|".$error;
					  exit;
				  }
				 $arr = $xml -> CONTENTS ;
				//print_r($xml);
				//print_r($arr);
				
				foreach($arr as $row){
					$bzc_cd=$row->ENPintr0000->bzc_cd;
					$kedcd=$row->ENPintr0000->kedcd;
				}
				//API 로그에 기록
				sql_fetch("insert company_api_log set c_bizno='{$comnum}', c_kedcd='{$kedcd}', c_content='{$json}', c_datetime=now(), c_ip='{$_SERVER["REMOTE_ADDR"]}'");
				echo "1000|".$json;
			}else{
				//DB에 있으면 DB값 가져옴
				$json=$log_chk['c_content'];
				echo "1000|".$json;
			}
			 exit;

		}else{
			echo "500|올바른 방법으로 이용해주세요.";
		}
	break;

	case "join_comp_chk": //사업자등록증으로 api검색
		if($comnum) {
			if($r_mb_id) $mem_chk=sql_fetch("select count(*) as cnt from g5_member where mb_leave_date='' and mb_4='$comnum' and mb_id!='{$r_mb_id}'");
			else $mem_chk=sql_fetch("select count(*) as cnt from g5_member where mb_leave_date='' and mb_4='$comnum'");
			if($mem_chk['cnt']>0) {echo "502|이미 등록된 회원 있습니다."; exit;}
			$comnum=str_replace("-","",$comnum);
					$chk=sql_fetch("select * from company where BZNO='$comnum'");

					if($chk['KEDCD']) { 
						if(!$chk['ENP_NM_TRD']) $chk['ENP_NM_TRD']=$chk['ENP_NM'];
						echo "1000|".$chk['ENP_NM_TRD']."|".$chk['KEDCD'];
					}else{
						//등록된 정보가 없으면 API로 가져와서 등록함
						//API 로그기록에 정보가 남아있는지 체크 있으면 해당 기록에서 그냥 가져오기
						$log_chk=sql_fetch("select * from company_api_log where c_bizno='{$comnum}' and DATE(c_datetime)='".date("Y-m-d")."'");
						if(!$log_chk['c_content']){
							$com_d=getKODATA($comnum);
							$com_data=explode("|",$com_d);
							if($com_data[0]==1000){
								$com_info=json_decode($com_data[1]);
								$KEDCD=$com_info->CONTENTS->ENPintr0000->kedcd;
								$enp_nm=$com_info->CONTENTS->ENPintr0000->enp_nm;
								$enp_nm_trd=$com_info->CONTENTS->ENPintr0000->enp_nm_trd;//회사명
								$enp_nm_eng=$com_info->CONTENTS->ENPintr0000->enp_nm_eng;
								$cono_pid=$com_info->CONTENTS->ENPintr0000->cono_pid; //법인번호
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
								$list=$com_info->CONTENTS->ENPsthr0001->list;
								$scnt=$com_info->CONTENTS->ENPsthr0001->cnt;

										if($scnt) {
											$ISS_STK_CN=0;
											$CSTK_CN=0;
											$PSTK_CN=0;
											$slist=$com_info->CONTENTS->ENPsthr0001->list;
											for($tmp=0;$tmp<=$scnt;$tmp++){
												$own_stk_cn=$slist[$tmp]->own_stk_cn; //소유주식
												$cstk_cn=$slist[$tmp]->cstk_cn; //보통주
												$pstk_cn=$slist[$tmp]->pstk_cn; //우선주
												
												$ISS_STK_CN=$ISS_STK_CN+$own_stk_cn; //발행주식수 총계
												$CSTK_CN=$CSTK_CN+$cstk_cn; //보통주식 총계
												$PSTK_CN=$PSTK_CN+$pstk_cn; //우선주식 총계
											}
											$STD_DT=$slist[0]->std_dt;
										}

								//API 로그에 기록
								sql_fetch("insert company_api_log set c_bizno='{$comnum}', c_kedcd='{$KEDCD}', c_content='{$com_data[1]}', c_datetime=now(), c_ip='{$_SERVER["REMOTE_ADDR"]}'");
								echo "1000|".$enp_nm_trd."|".$KEDCD;
							}else{
								echo "501";
							}
						}else{
								//API기록에서 정보가져옴
								$com_info=json_decode($log_chk['c_content']);
								//print_r($com_info); exit;
								$KEDCD=$com_info->CONTENTS->ENPintr0000->kedcd;
								$enp_nm=$com_info->CONTENTS->ENPintr0000->enp_nm;
								$enp_nm_trd=$com_info->CONTENTS->ENPintr0000->enp_nm_trd;//회사명
								$enp_nm_eng=$com_info->CONTENTS->ENPintr0000->enp_nm_eng;
								$cono_pid=$com_info->CONTENTS->ENPintr0000->cono_pid; //법인번호
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

								//ENPsthr0001 주주현황상세
								//ENPcust0001, ENPsupp0001 주요 구매, 판매처 10개
								//ENPfsdt0001 주요재무제표
								/*
								fs_acct_dt 최근결산년_결산일
								fs_val1 최근결산년_유동자산
								fs1_acct_dt 최근결산직전년_결산일
								fs2_acct_dt  최근결산직직전년_결산일
								*/

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
								

								if(!$enp_nm_trd) $enp_nm_trd=$enp_nm;
								echo "1000|".$enp_nm_trd."|".$KEDCD;
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
						if($KEDCD){
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
						}

						if(is_object($fs_val9))  unset($fs_val9);
						if(is_object($fs_val3))  unset($fs_val3);
						if(is_object($fs_val7))  unset($fs_val7);
						if(is_object($fs_val8))  unset($fs_val8);
						if(is_object($fs_val9))  unset($fs_val9);
						if(is_object($fs_val11))  unset($fs_val11);
						if(is_object($fs_val16))  unset($fs_val16);
						//주요재무정보 등록
						if($fs_acct_dt){
						$sql="insert company_financial set ";
						$sql.="KEDCD='{$KEDCD}', ";
						$sql.="ACCT_DT='{$fs_acct_dt}', "; //결산기준연월일
						$sql.="SUMASSET='{$fs_val3}', ";//총자산
						$sql.="PAYMENTFUND='{$fs_val7}', "; //납입자본금
						$sql.="FUNDTOTAL='{$fs_val8}', ";//자본총계
						$sql.="SALES='{$fs_val9}', ";//매출액
						$sql.="PROFIT='{$fs_val11}', ";//영업이익
						$sql.="TERMNETPROFIT='{$fs_val16}' ";//당기순이익
						
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
						$sql="insert company_financial set ";
						$sql.="KEDCD='{$KEDCD}', ";
						$sql.="ACCT_DT='{$fs1_acct_dt}', "; //결산기준연월일
						$sql.="SUMASSET='{$fs1_val3}', ";//총자산
						$sql.="PAYMENTFUND='{$fs1_val7}', "; //납입자본금
						$sql.="FUNDTOTAL='{$fs1_val8}', ";//자본총계
						$sql.="SALES='{$fs1_val9}', ";//매출액
						$sql.="PROFIT='{$fs1_val11}', ";//영업이익
						$sql.="TERMNETPROFIT='{$fs1_val16}' ";//당기순이익
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
						$sql="insert company_financial set ";
						$sql.="KEDCD='{$KEDCD}', ";
						$sql.="ACCT_DT='{$fs2_acct_dt}', "; //결산기준연월일
						$sql.="SUMASSET='{$fs2_val3}', ";//총자산
						$sql.="PAYMENTFUND='{$fs2_val7}', "; //납입자본금
						$sql.="FUNDTOTAL='{$fs2_val8}', ";//자본총계
						$sql.="SALES='{$fs2_val9}', ";//매출액
						$sql.="PROFIT='{$fs2_val11}', ";//영업이익
						$sql.="TERMNETPROFIT='{$fs2_val16}' ";//당기순이익
						sql_query($sql);
						}

						//주식정보
						if($STD_DT){
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
		}else{
			echo "500|올바른 방법으로 이용해주세요.";
		}
	break;

	case "view_chk"://거래처 뷰체크
		if($member['mb_id']) {
			$msinfo_row=getUserMembership();
			if(!$msinfo_row[0]) {
				if($member['mb_open_cnt']>0) echo "1000";
				else echo "500|거래처 오픈 회수를 초과했습니다.";
			}else{
				echo "1000"; //맴버쉽 회원은 
			}
		}else{
			echo "500|로그인 후 이용하세요.";
		}
	break;

	case "detail_view"://거래처 뷰
		if($member['mb_id']) {
			$msinfo_row=getUserMembership();
			if(!$msinfo_row[0]) {
				if($member['mb_open_cnt']>0) $chk="1000";
				else echo "500|거래처 오픈 회수를 초과했습니다.";
			}else{
				$chk="1000";
			}
				if($chk=="1000"){
					unset($comp);
					$comp=getComapyInfo($kedcd);
					$kssc9=getKsscInfo9($comp['KSIC9_BZC_CD']);
					$kssc=getKsscInfo($comp['BZC_CD']);
					$matcing=sql_fetch("SELECT * from g5_matching where del != 'D' and  request_id='{$member['mb_id']}' and KEDCD='{$kedcd}'");//매칭요청
					if($matcing['receive_approval']=="Y") $receve_info=getUserInfo($matcing['receive_id']);
					if(!$matcing['receive_approval']) {
						//$receve_info=getUserInfo($matcing['request_id']);
						$matcing=sql_fetch("SELECT * from g5_matching where del != 'D' and  receive_id='{$member['mb_id']}' and KEDCD='{$kedcd}'");//받은매칭요청
						if($matcing['receive_approval']=="Y") $receve_info=getUserInfo($matcing['request_id']);
					}
					if(!$matcing['receive_approval']) {
						$matcing=sql_fetch("SELECT * from g5_matching where del != 'D' and  receive_id='{$member['mb_id']}' and request_id=(select mb_id from g5_member where mb_company='{$kedcd}')");//받은매칭요청
						if($matcing['receive_approval']=="Y") $receve_info=getUserInfo($matcing['request_id']);
					}
					$favorite=sql_fetch("SELECT * from g5_favorite where mb_id='{$member['mb_id']}' and KEDCD='{$kedcd}'"); //북마크
					$sales=getFinanceInfo($kedcd);
					

?>
	<div class="cv-name">
		<strong><?=$comp['ENP_NM']?></strong>
		<div class="option">
			<button type="button" star class="<?if($favorite['ms_id']){?>active<?}?> star_btn" kedcd="<?=$kedcd?>"><img src="/images/icon_star.svg" alt=""></button>
			<a href="javascript:copyUrl('<?=simple_crypt($kedcd);?>')" link><img src="/images/icon_link.svg" alt=""></a>
			<?if($matcing['receive_approval']=="N"){?>
			<a href="javascript:jAlert('매칭 수락 대기중입니다.','알림')" request><span>수락대기</span></a>
			<?}?>
			<?if($matcing['receive_approval']=="Y"){?>
			<a request class="accepted"><span>매칭완료</span></a>
			<?}?>
			<?if($matcing['receive_approval']=="R"){?>
			<a request class="rejected"><span>매칭거절</span></a>
			<?}?>
			<?if(!$matcing['receive_approval']){?>
			<a id="popup_open" kedcd="<?=$kedcd?>" request><span>매칭 요청</span></a>
			<?}?>
			<!--a href="javascript:void(0);" request>매칭요청</a-->
		</div>
	</div>
	<div class="cv-info-area">
		<div class="cv-info-re normal">
			<p class="cv-tit">기본정보</p>
			<div class="cv-info-conts">
				<!--dl>
					<dt>대표자</dt>
					<dd><span>이영주</span></dd>
				</dl-->
				<dl>
					<dt>설립일자</dt>
					<dd><span><?=substr($comp['ESTB_DT'],0,4)?>-<?=substr($comp['ESTB_DT'],4,2)?></span></dd>
				</dl>

				<dl>
					<dt>법인(주민)번호</dt>
					<dd><span><?=substr($comp['CONO_PID'],0,6)?>-<?=substr($comp['CONO_PID'],6,7)?></span></dd>
				</dl>
				<dl>
					<dt>사업자번호</dt>
					<dd><span><?=licenseNum($comp['BZNO']);?></span></dd>
				</dl>

				<dl>
					<dt>고용인원</dt>
					<dd><span><?=number_format($comp['LABORER_SUM'])?>명</span></dd>
				</dl>
				<dl>
					<dt>기업규모</dt>
					<dd><span><?=$ENP_SZE[$comp['ENP_SZE']]?></span></dd>
				</dl>
			</div>
		</div>
		<div class="cv-info-re business">
			<p class="cv-tit">사업/산업</p>
			<div class="cv-info-conts">
				<dl>
					<dt>기업유형</dt>
					<dd><?=$enp_typt[$comp['ENP_TYP']]?></dd>
				</dl>
				<dl>
					<dt>주요제품</dt>
					<dd><?=$comp['PD_NM']?></dd>
				</dl>

				<dl>
					<dt>표준산업분류(9차)</dt>
					<dd>(<?=$comp['KSIC9_BZC_CD']?>) <?=$kssc9['k_code_name']?></dd>
				</dl>
				<dl>
					<dt>표준산업분류</dt>
					<dd>(<?=$comp['BZC_CD']?>) <?=$kssc['k_code_name']?></dd>
				</dl>

				<dl>
					<dt>직전연도 매출액</dt>
					<dd><?=number_format($sales[0]['SALES']*1000);?>원</dd>
				</dl>
			</div>
		</div>
		<div class="cv-info-re contact">
			<p class="cv-tit">연락방법/위치</p>
			<div class="cv-info-conts" colums>
				<dl>
					<dt>전화번호</dt>
					<dd>
					<?if(!$matcing or $matcing['receive_approval']=="N"){?>
					수락 후 공개
					<?}else{?>
					<a href="tel:<?=$receve_info['mb_8']?>"><?=$receve_info['mb_8']?></a>
					<?}?>
					</dd>
				</dl>
				<dl>
					<dt>이메일</dt>
					<dd>
					<?if(!$matcing or $matcing['receive_approval']=="N"){?>
					수락 후 공개
					<?}else{?>
					<a href="mailto:<?=$receve_info['mb_5']?>" u><?=$receve_info['mb_5']?></a>
					<?}?>
					</dd>
				</dl>
				<dl>
					<dt>홈페이지</dt>
					<dd><a href="http://<?=$comp['HPAGE_URL']?>" target="_blank" u><?=$comp['HPAGE_URL']?></a></dd>
				</dl>
				<dl>
					<dt>주소(도로명)</dt>
					<dd><a href="https://map.kakao.com/?q=<?=$comp['LOC_RDNM_ADDRA']?> <?=$comp['LOC_RDNM_ADDRB']?>" target="_blank"><?=$comp['LOC_RDNM_ADDRA']?><br /><?=$comp['LOC_RDNM_ADDRB']?></a></dd>
				</dl>

				<div class="cv-character">
					<span>좋은 거래처와<br />매칭되기를<br /><strong class="point">매커멕스</strong>가 함께<br />응원할게요</span>
					<p><img src="/images/icon_character.png" alt=""></p>
				</div>
			</div>
		</div>
	</div>

	<div class="cv-info-step">
		<div class="step-left">
			<p class="titles">직접 연결 방법</p>
			<div class="step-re">
				<p class="step"><strong>STEP 1</strong><span><img src="/images/arr_message_r.png" alt=""></span></p>
				<p class="icon"><img src="/images/icon_connection.png" alt=""></p>
				<?if(trim($comp['EMAIL'])){?>
				<p class="tit pVar">직접 연결</p>
				<div class="conts tel pVar">
					<span class="point">대표 이메일</span>로 연결됩니다.
					<p class="btn"><a href="mailto:<?=$comp['EMAIL']?>"><?=format_phone_number($comp['TEL_NO'])?></a></p>
				</div> 
				<?}else{?>
				<p class="tit pVar">직접 연결</p>
				<div class="conts tel pVar">
					<span class="point">대표 이메일</span>로 연결됩니다.
					<?if($comp['TEL_NO']){?><p class="btn"><a><?=format_phone_number($comp['TEL_NO'])?></a></p><?}?>
					<p class="btn">등록된 이메일 정보가 없습니다.</p>
				</div>
				<?}?>
				<?if(trim($comp['TEL_NO'])){?>
				<p class="tit tmVar">직접 연결</p>
				<div class="conts tel tmVar">
					<span class="point">대표 전화번호</span>로 연결됩니다.
					<p class="btn"><a href="tel:<?=$comp['TEL_NO']?>"><?=format_phone_number($comp['TEL_NO'])?></a></p>
				</div>
				<?}else{?>
				<p class="tit tmVar">직접 연결</p>
				<div class="conts tel tmVar">
					<span class="point">대표 전화번호</span>로 연결됩니다.
					<p class="btn">등록된 전화번호 정보가 없습니다.</p>
				</div>
				<?}?>
			</div>
		</div>
		<div class="step-right">
			<p class="titles">매칭 프로세스</p>
			<div class="step-right-in">
				<div class="step-re">
					<p class="step"><strong>STEP 1</strong><span><img src="/images/arr_message_r.png" alt=""></span></p>
					<p class="icon"><img src="/images/icon_request.png" alt=""></p>
					<p class="tit">매칭 요청</p>
					<div class="conts">
						오른쪽 상단의 <span class="point">“매칭요청” 버튼을 클릭</span>
						하시면, 당사 직원이 피매칭 기업 담당
						자에게 직접 연락하여 문의 사항을 확인
						할 수 있도록 도움을 드립니다.
					</div>
				</div>
				<div class="step-re">
					<p class="step"><strong>STEP 2</strong><span><img src="/images/arr_message_r.png" alt=""></span></p>
					<p class="icon"><img src="/images/icon_accept.png" alt=""></p>
					<p class="tit">매칭 수락</p>
					<div class="conts">
						피매칭 기업이 매칭을 수락할 경우,<br />
						<span class="point">담당자의 직통 연락처</span>가 공개됩니다.
					</div>
				</div>
			</div>
		</div>
	</div>
<?
			}
		}else{
			echo "500|로그인 후 이용하세요.";
	}
	break;

	case "unlisted_value_unlock": //비상장 주식가치 최초 조회 언락(최초 1회 크레딧 차감)
		if($_SERVER['REQUEST_METHOD'] !== 'POST'){
			echo "500|잘못된 요청 방식입니다.";
			break;
		}

		// 과도 호출 방지: 짧은 시간 내 반복 요청 제한
		$ulv_rate_window_sec = 10;
		$ulv_rate_max_calls = 12;
		$ulv_rate_key = 'ss_unlisted_value_rate';
		$ulv_now = time();
		$ulv_hist_raw = get_session($ulv_rate_key);
		$ulv_hist = array();
		if($ulv_hist_raw){
			$ulv_decoded = json_decode((string)$ulv_hist_raw, true);
			if(is_array($ulv_decoded)) $ulv_hist = $ulv_decoded;
		}
		$ulv_recent = array();
		foreach($ulv_hist as $ulv_ts){
			$ulv_ts = (int)$ulv_ts;
			if($ulv_ts > ($ulv_now - $ulv_rate_window_sec)) $ulv_recent[] = $ulv_ts;
		}
		if(count($ulv_recent) >= $ulv_rate_max_calls){
			echo "429|요청이 많습니다. 잠시 후 다시 시도해 주세요.";
			break;
		}
		$ulv_recent[] = $ulv_now;
		set_session($ulv_rate_key, json_encode($ulv_recent));

		$unlock_token = isset($_POST['unlock_token']) ? preg_replace('/[^0-9a-z_\.-]/i', '', (string)$_POST['unlock_token']) : '';
		$session_unlock_token = get_session('ss_unlisted_value_token');
		if(!$unlock_token || !$session_unlock_token || $unlock_token !== $session_unlock_token){
			echo "500|유효하지 않은 요청입니다.";
			break;
		}

		if(!$member['mb_id']) {
			echo "501|로그인 후 이용하세요.";
			break;
		}

		$uid = sql_real_escape_string(trim((string)$member['mb_id']));
		$member_kedcd_raw = trim((string)$member['mb_company']);
		if(!$member_kedcd_raw){
			echo "500|회사 정보가 없어 처리할 수 없습니다.";
			break;
		}
		$target_kedcd_raw = isset($_POST['kedcd']) ? trim((string)$_POST['kedcd']) : '';
		if(!$target_kedcd_raw) $target_kedcd_raw = $member_kedcd_raw;
		if(!$target_kedcd_raw) {
			echo "500|회사 정보가 없어 처리할 수 없습니다.";
			break;
		}
		if($member_kedcd_raw && $target_kedcd_raw != $member_kedcd_raw) {
			echo "500|정상적인 접근이 아닙니다.";
			break;
		}
		$target_kedcd = sql_real_escape_string($target_kedcd_raw);

		$service_code = 'UNLISTED_VALUE';
		$service_code_sql = sql_real_escape_string($service_code);
		$credit_cost = (defined('_unlisted_value_credit_cost') && (int)_unlisted_value_credit_cost > 0) ? (int)_unlisted_value_credit_cost : 20;
		if($credit_cost < 1) $credit_cost = 20;
		$check_only = (isset($_POST['check_only']) && trim((string)$_POST['check_only']) === '1');

		$once_key = hash('sha256', $service_code.':'.$member['mb_id'].':'.$target_kedcd_raw);
		$once_key_sql = sql_real_escape_string($once_key);

		$lock_name = unlisted_value_lock_acquire($once_key, 3);
		if(!$lock_name) {
			echo "500|처리가 지연되고 있습니다. 잠시 후 다시 시도해 주세요.";
			break;
		}

		$response = "500|처리 중 오류가 발생했습니다.";

		// 서버에서도 가치 산출 가능 여부를 먼저 확인(우회 호출 방지)
		$financial_years = array();
		$profit_list = array();
		$fund_total_list = array();
		$f_result = sql_query("select ACCT_DT, TERMNETPROFIT, FUNDTOTAL from company_financial where KEDCD='{$target_kedcd}' order by ACCT_DT desc limit 3");
		for($i=0; $f_row=sql_fetch_array($f_result); $i++){
			$yy = substr((string)$f_row['ACCT_DT'], 0, 4);
			if($yy !== '') {
				$financial_years[$yy] = 1;
				$profit_list[$yy] = (float)$f_row['TERMNETPROFIT'];
			}
			$fund_total_list[] = (float)$f_row['FUNDTOTAL'];
		}
		$has_financial = (count($financial_years) > 1);
		$stock_row = sql_fetch("select ISS_STK_CN from company_stock where KEDCD='{$target_kedcd}' order by STD_DT desc limit 1");
		$iss_stk_cn = preg_replace('/[^0-9]/', '', (string)$stock_row['ISS_STK_CN']);
		$iss_stk_cn_int = (int)$iss_stk_cn;
		$has_stock = ($iss_stk_cn_int > 0);
		if(!$has_financial || !$has_stock){
			$response = "500|직전 3개년도 재무정보 또는 주식정보가 정확하지 않아서 확인이 어렵습니다.|WARN";
			unlisted_value_lock_release($lock_name);
			echo $response;
			break;
		}

		$svps_value = 0;
		$warn_text_value = '';
		if($has_financial && $has_stock){
			$asset_value = (((float)$fund_total_list[0]) * 1000) / $iss_stk_cn_int;
			$pv_total = 0;
			$x = 0;
			foreach($profit_list as $yy=>$profit_val){
				$x++;
				$eps = ($profit_val * 1000) / $iss_stk_cn_int;
				if($x==1) $pv_total += ($eps * 3);
				if($x==2) $pv_total += ($eps * 2);
				if($x==3) $pv_total += ($eps * 1);
			}
			$pv2 = ($pv_total / 6) / 0.1;
			$com_av = $asset_value * 0.8;
			$svps_calc = (($asset_value * 2) + ($pv2 * 3)) / 5;
			if($svps_calc < $com_av) $svps_calc = $com_av;
			$svps_value = (int)$svps_calc;
		}
		$warn_text_encoded = rawurlencode($warn_text_value);

		$already = sql_fetch("select id from g5_matching_credit_history where once_key='{$once_key_sql}' and tx_type='USE' and service_code='{$service_code_sql}' limit 1");
		if($already['id']) {
			$status = sql_fetch("select matching_able_cnt from g5_membership_status where uid='{$uid}'");
			$remain_credit = isset($status['matching_able_cnt']) ? (int)$status['matching_able_cnt'] : 0;
			$response = "1000|already|{$remain_credit}|0|{$svps_value}|{$warn_text_encoded}";
		} else if($check_only) {
			$status = sql_fetch("select uid, matching_able_cnt from g5_membership_status where uid='{$uid}'");
			$remain_credit = isset($status['matching_able_cnt']) ? (int)$status['matching_able_cnt'] : 0;
			if(!$status['uid'] || $remain_credit < $credit_cost) {
				$response = "500|가치 조회 크레딧이 부족합니다.|PLAN";
			} else {
				$response = "1000|ready|{$remain_credit}|{$credit_cost}";
			}
		} else {
			$status = sql_fetch("select uid, matching_able_cnt from g5_membership_status where uid='{$uid}'");
			$remain_credit = isset($status['matching_able_cnt']) ? (int)$status['matching_able_cnt'] : 0;
			if(!$status['uid'] || $remain_credit < $credit_cost) {
				$response = "500|가치 조회 크레딧이 부족합니다.|PLAN";
			} else {
				sql_query("START TRANSACTION");

				sql_query("update g5_membership_status set matching_able_cnt=matching_able_cnt-{$credit_cost} where uid='{$uid}' and matching_able_cnt >= {$credit_cost}");
				$affected = sql_fetch("select row_count() as cnt");
				if((int)$affected['cnt'] < 1) {
					sql_query("ROLLBACK");
					$response = "500|가치 조회 크레딧이 부족합니다.|PLAN";
				} else {
					$after = sql_fetch("select matching_able_cnt from g5_membership_status where uid='{$uid}'");
					$credit_after = isset($after['matching_able_cnt']) ? (int)$after['matching_able_cnt'] : 0;
					$memo = addslashes("비상장 주식가치 최초 조회({$credit_cost} 차감)");
					$history_sql = "insert into g5_matching_credit_history set
						uid='{$uid}',
						tx_type='USE',
						service_code='{$service_code_sql}',
						credit_delta='-{$credit_cost}',
						credit_after='{$credit_after}',
						order_no=NULL,
						matching_no='',
						target_kedcd='{$target_kedcd}',
						once_key='{$once_key_sql}',
						memo='{$memo}',
						request_type=NULL,
						created_at=now()";
					$ins = sql_query($history_sql);
					if(!$ins) {
						// g5_membership_status가 트랜잭션 비지원(MyISAM)인 환경을 대비한 보정
						sql_query("update g5_membership_status set matching_able_cnt=matching_able_cnt+{$credit_cost} where uid='{$uid}'");
						sql_query("ROLLBACK");
						$response = "500|크레딧 이력 저장에 실패했습니다.";
					} else {
						sql_query("COMMIT");
						$response = "1000|charged|{$credit_after}|{$credit_cost}|{$svps_value}|{$warn_text_encoded}";
					}
				}
			}
		}

		unlisted_value_lock_release($lock_name);
		echo $response;
	break;

	case "favorite"://관심기업
		if($member['mb_id'] && $chk) {
			if($chk=="true") {
				sql_query("insert g5_favorite set mb_id='{$member['mb_id']}', KEDCD='{$kedcd}', ms_datetime=now()");
			}else{ 
				sql_query("delete from g5_favorite where mb_id='{$member['mb_id']}' and KEDCD='{$kedcd}'");
			}
			echo "1000";
		}else{
			if($member['mb_id']) echo "500";
			else echo "501";
		}
	break;

	case "matching": //매칭요청

		if($chk=="cancel"){
			if($member['mb_id'] && $kedcd) {
				$chk=sql_query("update g5_matching set del='D', receive_approval_date=now() where request_id='{$member['mb_id']}' and KEDCD='{$kedcd}'");
				if($chk) {
					echo "1000";
				}else{ 
					echo "500";
				}
			}else{
				echo "500";
			}
		}else{
			if($member['mb_id'] && $kedcd) {
					$request_type = strtoupper(trim((string)$request_type));
					if($request_type !== "TM") $request_type = "BASIC";
					$matching_basic_credit_cost = (defined('_matching_credit_cost') && (int)_matching_credit_cost > 0) ? (int)_matching_credit_cost : 10;
					$matching_tm_credit_cost_local = (defined('_matching_tm_credit_cost') && (int)_matching_tm_credit_cost > 0) ? (int)_matching_tm_credit_cost : 30;
					$matching_credit_cost = ($request_type === "TM") ? $matching_tm_credit_cost_local : $matching_basic_credit_cost;
					if($matching_credit_cost < 1) $matching_credit_cost = $matching_basic_credit_cost;
					$t_id=sql_fetch("select * from g5_member where mb_company='{$kedcd}' and mb_leave_date='' and mb_intercept_date='' ");
					$t_uinfo= getUserInfo($t_id['mb_id']);
					$corp_type['제조사']="M";
					$corp_type['유통사']="D";
					$corp_type['기타']="C";

					$rq_kind=$corp_type[$member['mb_3']];
					$rc_kind=$corp_type[$t_uinfo['mb_3']];

					$status_row = sql_fetch("select matching_able_cnt from g5_membership_status where uid='{$member['mb_id']}'");
					if(!$status_row['matching_able_cnt'] || (int)$status_row['matching_able_cnt'] < $matching_credit_cost){
						echo "500|매칭 가능 크레딧이 부족합니다. (필요: {$matching_credit_cost})|PLAN";
						exit;
					}

					sql_query("START TRANSACTION");

					$insert_matching = sql_query("insert g5_matching set request_id='{$member['mb_id']}', rq_kind='{$rq_kind}', receive_id='{$t_id['mb_id']}',rc_kind='{$rc_kind}', KEDCD='{$kedcd}' ,receive_approval='N' , contents='".addslashes($match_content)."', regdate=now()");
					if(!$insert_matching){
						sql_query("ROLLBACK");
						echo "500";
						exit;
					}
					$matching_no = sql_insert_id();

					// 매칭 차감(원자적 처리)
					sql_query("update g5_membership_status set matching_able_cnt=matching_able_cnt-{$matching_credit_cost} where uid='{$member['mb_id']}' and matching_able_cnt >= {$matching_credit_cost}");
					$affected = sql_fetch("select row_count() as cnt");
					if((int)$affected['cnt'] < 1){
						sql_query("ROLLBACK");
						echo "500|매칭 가능 크레딧이 부족합니다. (필요: {$matching_credit_cost})|PLAN";
						exit;
					}

					// 차감 후 잔액 조회
					$mat=sql_fetch("select * from g5_membership_status where uid='{$member['mb_id']}' ");
					if($mat['order_no']) sql_query("update g5_membership_status_history set matching_able_cnt={$mat['matching_able_cnt']} where uid='{$member['mb_id']}' and order_no='{$mat['order_no']}' ");

					// 크레딧 원장 기록 (사용)
					$memo_prefix = ($request_type === "TM") ? "TM 요청" : "기본 요청";
					$memo = addslashes($memo_prefix." 사용({$matching_credit_cost} 차감)");
					sql_query("insert into g5_matching_credit_history set uid='{$member['mb_id']}', tx_type='USE', credit_delta='-{$matching_credit_cost}', credit_after='{$mat['matching_able_cnt']}', order_no=NULL, matching_no='{$matching_no}', request_type='{$request_type}', memo='{$memo}', created_at=now()");

					sql_query("COMMIT");

					//알림톡 발송
					if($t_uinfo['mb_8']){
						$gw_msg = new NCP_SENS_AT();
						$gw_msg->templateCode	= 'reqmatching2'; // 템플릿 코드 매칭요청
						$gw_msg->to				= $t_uinfo['mb_8']; // 수신자 휴대폰번호
						$gw_msg->send();
					}
					//알림톡 발송 끝

					echo "1000";
			}else{
				echo "500";
			}
		}
	break;

	case "matching_check": //매칭체크

		//맴버쉽 정보
		$request_type = strtoupper(trim((string)$request_type));
		if($request_type !== "TM") $request_type = "BASIC";
		$matching_basic_credit_cost = (defined('_matching_credit_cost') && (int)_matching_credit_cost > 0) ? (int)_matching_credit_cost : 10;
		$matching_tm_credit_cost_local = (defined('_matching_tm_credit_cost') && (int)_matching_tm_credit_cost > 0) ? (int)_matching_tm_credit_cost : 30;
		$matching_credit_cost = ($request_type === "TM") ? $matching_tm_credit_cost_local : $matching_basic_credit_cost;
		if($matching_credit_cost < 1) $matching_credit_cost = $matching_basic_credit_cost;
		$msinfo=sql_fetch("select * from g5_membership_status where uid='{$member['mb_id']}'" );
		if($msinfo['uid']) {
			if((int)$msinfo['matching_able_cnt'] < $matching_credit_cost) {echo "500|매칭 가능 크레딧이 부족합니다. (필요: {$matching_credit_cost})|PLAN"; exit;}
		}else{
			echo "500|매칭 가능 크레딧이 부족합니다. (필요: {$matching_credit_cost})|PLAN";
			exit;
		}
		if($member['mb_id'] && $kedcd) {
				$chk=sql_fetch("select * from g5_matching where del != 'D' and  request_id='{$member['mb_id']}' and KEDCD='{$kedcd}'");
				if($chk['no']) echo $chk['no']."|".$chk['receive_approval'];
				else echo "1000|".$msinfo['matching_able_cnt'];
		}else{
			echo "500";
		}
	break;

	case "matching_chk": //매칭체크
		if($member['mb_id'] && $c_id) {
				$chk=sql_fetch("select * from g5_matching where del != 'D' and  request_id='{$member['mb_id']}' and c_id='{$c_id}' and status='R'");
				if($chk['ms_id']) echo "501";
				else echo "1000";
		}else{
			echo "500";
		}
	break;

	case "matching_accept": //매칭수락
		if($member['mb_id'] && $idx) {
				$pre=sql_fetch("select * from g5_matching where no='{$idx}'");
				if ($pre['receive_approval']=="N") $status="Y";
				else $status="N";
				if($typ=="accept") $status="Y";
				if($typ=="reject") $status="R";
				$chk2=sql_query("update g5_matching set receive_approval='{$status}', receive_approval_date=now() where receive_id='{$member['mb_id']}' and no='{$idx}'");

				if($status=="Y") {
						//알림톡 발송
						$minfo=sql_fetch("select * from g5_matching where no='{$idx}'");
						$t_uinfo= getUserInfo($minfo['request_id']);
						if($t_uinfo['mb_8']){
							$gw_msg = new NCP_SENS_AT();
							$gw_msg->templateCode	= 'accmatching'; // 템플릿 코드 매칭수락
							$gw_msg->to				= $t_uinfo['mb_8']; // 수신자 휴대폰번호
							$gw_msg->send();
						}
						//알림톡 발송 끝
				}
				echo "1000";
		}else{
			echo "500";
		}
	break;

	case "matching_delete": //매칭삭제
		if($member['mb_id'] && $idx) {
				$chk=sql_query("update g5_matching set del='D' where (request_id='{$member['mb_id']}' or receive_id='{$member['mb_id']}') and no='{$idx}'");
				
				if($chk) echo "1000";
				else echo "500";
		}else{
			echo "500";
		}
	break;

	case "change_pwd": //비밀번호 변경
		if($member['mb_id'] && $pwd) {
				$chk=sql_query("update g5_member set mb_password='".get_encrypt_string($pwd)."' where mb_id='{$member['mb_id']}'");
				if($chk) echo "1000";
				else echo "500";
		}else{
			echo "500";
		}
	break;

	case "membership_chk": //맴버쉽 정보
		if($member['mb_id']) {
				$today = date("Y-m-d");
				$chk=sql_fetch("select * from g5_membership_status where (status='S' or status='T') and uid='{$member['mb_id']}' and date_begin >= '{$today}'");
				if($chk['uid']) {
					$minfo=sql_fetch("select * from g5_membership_manage where no='{$chk['membership_parent_no']}'");
					$end_time= strtotime($chk['date_end']." 23:59:59");
					$now_time=time();
					//echo $end_time."##".$now_time."//";
					if($end_time<$now_time) {
						//맴버쉽이 종료되었을 경우
						echo "1000";
					}else{
						//맴버쉽이 진행중인 경우
						//1. 무료인경우 상위맴버쉽 가입가능
						//2. 베이직인 경우 프로 맴버쉽만 가입가능
						//3. 프로 인 경우 모든 맴버쉽 가입 금지

						//$minfo['title'];//등록된 맴버쉽
						//$mtype; //요청 맴버쉽
						//echo $mtype;
						$chk="500|이미 상위 맴버쉽에 가입되어 있습니다."; //하위 맴버쉽
						if($minfo['title']==$mtype) {
							$chk="500|이미 맴버쉽이 가입되어 있습니다."; //프리미엄 맴버쉽인 경우 결제 금지
						}
						if($minfo['title']=="스탠다드" && ($mtype=="프리미엄" or $mtype=="프리미엄+")) {
							$chk="1000"; //베이직 맴버쉽인 경우 바로 진행하기
						}
						if($minfo['title']=="프리미엄" && $mtype=="프리미엄+") {
							$chk="1000"; //베이직 맴버쉽인 경우 바로 진행하기
						}
						echo $chk;
					}
					$chk['date_end'];//맴버쉽 종료기간
					$chk['membership_parent_no'];// 맴버쉽 종류
					//echo "1000";
				}else{
					echo "1000";
				}
		}else{
			echo "500|로그인후 이용하세요.";
		}
	break;

	case "membership_chk2": //맴버쉽 체크
		if($member['mb_id']) {
				//맴버쉽 가져오기
				$msinfo_row = getUserMembership();
				$chk=$msinfo_row[0];
				echo "1000|".$chk['membership_parent_no'];
		}else{
				echo "500|로그인후 이용하세요.";
		}
	break;

	case "membership_credit_pay": //크레딧 일반결제 주문 생성
		if($member['mb_id']) {
				$stdpay_mid_for_order = (defined('_stdpay_mid') && trim((string)_stdpay_mid) !== '') ? trim((string)_stdpay_mid) : ((defined('_mid') && trim((string)_mid) !== '') ? trim((string)_mid) : '');
				if(!$stdpay_mid_for_order){
					echo "500|일반결제 MID가 설정되지 않았습니다.";
					exit;
				}

				$minfo_manage = sql_fetch("select * from g5_membership_manage where no='{$idx}'");
				if(!$minfo_manage['no']){
					echo "500|상품 정보를 찾을 수 없습니다.";
					exit;
				}
				$is_credit_manage = false;
				if(($minfo_manage['title_en'] && strpos((string)$minfo_manage['title_en'], 'CREDIT_') === 0) || ((int)$minfo_manage['matching_cnt'] > 0 && (int)$minfo_manage['month'] === 0)){
					$is_credit_manage = true;
				}
				if(!$is_credit_manage){
					echo "500|크레딧 결제 대상 상품이 아닙니다.";
					exit;
				}
				if(!$orderid){
					echo "500|주문번호가 없습니다.";
					exit;
				}

			if(!$minfo_manage['price_sale']) $base_price = (int)$minfo_manage['price_origin'];
			else $base_price = (int)$minfo_manage['price_sale'];
				if(strpos((string)$minfo_manage['vat'], '별도') !== false) $price = (int)round($base_price * 1.1);
				else $price = $base_price;
				if($price < 1){
					echo "500|유효하지 않은 결제 금액입니다.";
					exit;
				}

			sql_query("delete from g5_membership_payment where order_no!='{$orderid}' and uid='{$member['mb_id']}' and complete_yn='N'");
			$pinfo=sql_fetch("select * from g5_membership_payment where order_no='{$orderid}' and uid='{$member['mb_id']}'");
				if(!$pinfo['uid']) {
					$sql="insert g5_membership_payment set ";
					$sql.="	uid='{$member['mb_id']}',";
					$sql.="	order_no='{$orderid}',";
					$sql.="	order_goods='[크레딧] {$minfo_manage['title']}',";
					$sql.="	price='{$price}',";
					$sql.="	complete_yn='N',";
					$sql.="	regdate=now(),";
					$sql.="	name='{$member['mb_name']}',";
					$sql.="	phone='{$member['mb_8']}',";
					$sql.="	email='{$member['mb_email']}',";
					$sql.="	mid='{$stdpay_mid_for_order}',";
				$sql.="	membership_parent_no='{$idx}'	";
			}else{
					$sql="update g5_membership_payment set ";
					$sql.="	order_goods='[크레딧] {$minfo_manage['title']}',";
					$sql.="	price='{$price}',";
					$sql.="	complete_yn='N',";
					$sql.="	complete_date=NULL,";
					$sql.="	tid='',";
					$sql.="	app_date='',";
					$sql.="	appl_time='',";
					$sql.="	appl_num='',";
					$sql.="	regdate=now(),";
					$sql.="	name='{$member['mb_name']}',";
					$sql.="	phone='{$member['mb_8']}',";
					$sql.="	email='{$member['mb_email']}',";
					$sql.="	mid='{$stdpay_mid_for_order}',";
				$sql.="	membership_parent_no='{$idx}'	";
				$sql.="	where no='{$pinfo['no']}'";
			}
			$result=sql_query($sql);
			if($result){
				echo "1000|".$orderid;
			}else{
				echo "500|결제 등록에 문제가 있습니다.";
			}
		}else{
				echo "500|로그인후 이용하세요.";
		}
	break;

	case "membership_pay": //맴버쉽 결제 시도
		
		if($member['mb_id']) {
				//맴버쉽 가져오기
				$msinfo_row = getUserMembership();
				$chk=$msinfo_row[0];
				$free="false";
				
				$rminfo=sql_fetch("select * from g5_membership_manage where title='{$mtype}'" );//요청 맴버쉽 정보
				
				if($chk['uid']) {
					
					$minfo=sql_fetch("select * from g5_membership_manage where no='{$chk['membership_parent_no']}'"); //가입 맴버쉽정보
					$end_time= strtotime($chk['date_end']." 23:59:59");

					$now_time=time();
					//echo $end_time."##".$now_time."//";
					if($end_time<$now_time) {
						$chk="1000";
					}else{
						//맴버쉽이 진행중인 경우
						//1. 무료인경우 상위맴버쉽 가입가능
						//2. 베이직인 경우 프로 맴버쉽만 가입가능
						//3. 프로 인 경우 모든 맴버쉽 가입 금지

						//$minfo['title'];//등록된 맴버쉽
						//$mtype; //요청 맴버쉽
						
						if($minfo['no']==$rminfo['no']) {
							$chk="500|해당 맴버쉽을 이용중입니다."; //프리미엄 맴버쉽인 경우 결제 금지
						}
						if($minfo['no']<$rminfo['no']) {
							$chk="1000"; //베이직 맴버쉽인 경우 바로 진행하기
						}
						if($minfo['no']>$rminfo['no']) {
							$chk="1000"; //하위 맴버쉽 허용
						}
						
					}
					$chk['date_end'];//맴버쉽 종료기간
					$chk['membership_parent_no'];// 맴버쉽 종류
					//echo "1000";
				}else{
					//무료맴버쉽일경우
					$chk="1000";
					$free="true";
				}
				//맴버쉽 진행
				if($chk=="1000"){
					//맴버쉽 결제 추가

					$minfo_manage=sql_fetch("select * from g5_membership_manage where no='{$idx}'"); //맴버쉽 정보
					if(!$minfo_manage['price_sale']) $price = $minfo_manage['price_origin']*1.1;
					else $price = $minfo_manage['price_sale']*1.1;
					sql_query("delete from g5_membership_payment where order_no!='{$orderid}' and uid='{$member['mb_id']}' and complete_yn='N'");
					$pinfo=sql_fetch("select * from g5_membership_payment where order_no='{$orderid}' and uid='{$member['mb_id']}'"); // 기존 결제 예정 정보
					if(!$pinfo['uid']) {
						$sql="insert g5_membership_payment set ";
						$sql.="	uid='{$member['mb_id']}',";
						$sql.="	order_no='{$orderid}',";
						$sql.="	order_goods='{$minfo_manage['title']}',";
						$sql.="	price='{$price}',";
						$sql.="	regdate=now(),";
						$sql.="	name='{$member['mb_name']}',";
						$sql.="	phone='{$member['mb_8']}',";
						$sql.="	email='{$member['mb_email']}',";
						$sql.="	mid='{$mid}',";
						$sql.="	membership_parent_no='{$idx}'	";
					}else{
						$sql="update g5_membership_payment set ";
						$sql.="	order_goods='{$minfo_manage['title']}',";
						$sql.="	price='{$price}',";
						$sql.="	regdate=now(),";
						$sql.="	name='{$member['mb_name']}',";
						$sql.="	phone='{$member['mb_8']}',";
						$sql.="	email='{$member['mb_email']}',";
						$sql.="	mid='{$mid}',";
						$sql.="	membership_parent_no='{$idx}'	";
						$sql.="	where no='{$pinfo['no']}'";
					}
					$result=sql_query($sql);
					if($result){
						if($minfo['no']>1) echo "1000|이미 멤버십에 가입되어 있습니다. 멤버십을 변경하시겠습니까?";
						else echo "1000|".$free;
					}else{
						echo "500|결제 등록에 문제가 있습니다.";
					}
					exit;
				}
				echo $chk;
		}else{
			echo "500|로그인후 이용하세요.";
		}
	break;


	case "membership_cancel": //맴버쉽 정기결제 취소
	//상태(S:유지, C:취소(환불), E:종료(기간만료), T 기간만료후정지)
			if($_SERVER['REQUEST_METHOD'] !== 'POST'){
				echo "500|잘못된 요청 방식입니다.";
				exit;
			}
			$cancel_token = isset($_POST['cancel_token']) ? preg_replace('/[^0-9a-z_\.-]/i', '', $_POST['cancel_token']) : '';
			$session_cancel_token = get_session('ss_membership_cancel_token');
			set_session('ss_membership_cancel_token', '');
			if(!$cancel_token || !$session_cancel_token || $cancel_token !== $session_cancel_token){
				echo "500|유효하지 않은 요청입니다.";
				exit;
			}
			if($member['mb_id']) {
				$chk=sql_fetch("select * from g5_membership_status where (status='S' or status='T') and uid='{$member['mb_id']}' order by no desc limit 1");
				if($chk['uid']) {
					$minfo=sql_fetch("select * from g5_membership_manage where no='{$chk['membership_parent_no']}'");
					$is_credit_payment = false;
					if(($minfo['title_en'] && strpos($minfo['title_en'], 'CREDIT_') === 0) || ((int)$minfo['matching_cnt'] > 0 && (int)$minfo['month'] === 0)){
						$is_credit_payment = true;
					}

					if($is_credit_payment){
						$pinfo=sql_fetch("select * from g5_membership_paybill where order_no='{$chk['order_no']}'");
						$porder=sql_fetch("select uid, membership_parent_no, mid from g5_membership_payment where order_no='{$chk['order_no']}'");
						if(!$pinfo['uid'] && $porder['uid']) $pinfo['uid'] = $porder['uid'];
						if(!$pinfo['membership_parent_no'] && $porder['membership_parent_no']) $pinfo['membership_parent_no'] = $porder['membership_parent_no'];
						if(!$pinfo['mid'] && $porder['mid']) $pinfo['mid'] = $porder['mid'];

						if(!$pinfo['tid']){
							echo "500|취소 오류: 결제 TID가 없습니다.";
							exit;
						}

						$is_test_pay = defined('_test_pay') && strtoupper((string)_test_pay) === 'Y';
						$stdpay_mid = (defined('_stdpay_mid') && trim((string)_stdpay_mid) !== '') ? trim((string)_stdpay_mid) : ((defined('_mid') && trim((string)_mid) !== '') ? trim((string)_mid) : '');
						$stdpay_mid_test = (defined('_stdpay_mid_test') && trim((string)_stdpay_mid_test) !== '') ? trim((string)_stdpay_mid_test) : ($is_test_pay ? $stdpay_mid : '');
						$stdpay_api_key_test = (defined('_stdpay_api_key_test') && trim((string)_stdpay_api_key_test) !== '') ? trim((string)_stdpay_api_key_test) : (($is_test_pay && defined('_key') && trim((string)_key) !== '') ? trim((string)_key) : '');
						$stdpay_api_key_real = (defined('_stdpay_api_key_real') && trim((string)_stdpay_api_key_real) !== '') ? trim((string)_stdpay_api_key_real) : ((defined('_key') && trim((string)_key) !== '') ? trim((string)_key) : '');

						$tid_mid = ($stdpay_mid_test && strpos((string)$pinfo['tid'], 'INIpayTest') !== false) ? $stdpay_mid_test : '';
						$mid = $tid_mid ? $tid_mid : (trim($pinfo['mid']) ? trim($pinfo['mid']) : $stdpay_mid);
						$key = ($stdpay_mid_test && $mid == $stdpay_mid_test) ? $stdpay_api_key_test : $stdpay_api_key_real;
						if(!$key){
							echo "500|취소 오류: 일반결제 환불 API KEY 미설정";
							exit;
						}

						$refund_credit = (int)$minfo['matching_cnt'];
						if($refund_credit < 1){
							echo "500|취소 오류: 환불 크레딧 정보가 없습니다.";
							exit;
						}

						$history_summary = sql_fetch("select
							IFNULL(sum(if(tx_type='CHARGE', credit_delta, 0)), 0) as total_charge,
							IFNULL(sum(if(tx_type='BONUS', credit_delta, 0)), 0) as total_bonus,
							IFNULL(sum(if(tx_type='USE', -credit_delta, 0)), 0) as total_used,
							IFNULL(sum(if(tx_type='REFUND', -credit_delta, 0)), 0) as total_refund
							from g5_matching_credit_history
							where uid='{$member['mb_id']}'");
						$payable_credit = (int)$history_summary['total_charge'] - (int)$history_summary['total_refund'];
						$paid_used_credit = (int)$history_summary['total_used'] - (int)$history_summary['total_bonus'];
						if($paid_used_credit < 0){
							$paid_used_credit = 0;
						}
						$payable_credit -= $paid_used_credit;
						if($payable_credit < 0){
							$payable_credit = 0;
						}
						$refund_credit = min($refund_credit, $payable_credit);
						if($refund_credit < 1){
							echo "500|취소 오류: 환불 가능한 크레딧이 없습니다.";
							exit;
						}

						$unit_plan = sql_fetch("select price_sale, price_origin, vat
							from g5_membership_manage
							where ((title_en like 'CREDIT_%') or (matching_cnt > 0 and month = 0))
							and matching_cnt = 100
							order by no asc
							limit 1");
						$unit_base_price = 0;
						if((int)$unit_plan['price_sale'] > 0){
							$unit_base_price = (int)$unit_plan['price_sale'];
						}else if((int)$unit_plan['price_origin'] > 0){
							$unit_base_price = (int)$unit_plan['price_origin'];
						}
						if($unit_base_price > 0 && strpos((string)$unit_plan['vat'], '별도') !== false){
							$unit_base_price = (int)round($unit_base_price * 1.1);
						}
						$unit_credit_price = 0;
						if($unit_base_price > 0){
							$unit_credit_price = ((float)$unit_base_price / 100);
						}
						if($unit_credit_price <= 0){
							echo "500|취소 오류: 100크레딧 기준 환불 단가 정보를 찾을 수 없습니다.";
							exit;
						}
						$refund_amount_before_fee = (int)floor($refund_credit * $unit_credit_price);
						$refund_fee = (int)floor($refund_amount_before_fee * 0.1);
						$refund_amount = $refund_amount_before_fee - $refund_fee;
						if($refund_amount < 1000){
							echo "500|취소 오류: 최소 환불 금액(1,000원) 미만입니다.";
							exit;
						}

						$refund_lock_name = membership_refund_lock_acquire($chk['order_no'], 0);
						if(!$refund_lock_name){
							echo "500|동일 주문의 환불이 처리 중입니다. 잠시 후 다시 시도해 주세요.";
							exit;
						}
						$already_refund = sql_fetch("select id from g5_matching_credit_history where order_no='{$chk['order_no']}' and tx_type='REFUND' limit 1");
						if($already_refund['id']){
							membership_refund_lock_release($refund_lock_name);
							echo "500|이미 환불 처리된 결제입니다.";
							exit;
						}

						$type = "refund";
						$timestamp = date("YmdHis");
						$clientIp = _clientIp;

						$postdata = array();
						$postdata["mid"] = $mid;
						$postdata["type"] = $type;
						$postdata["timestamp"] = $timestamp;
						$postdata["clientIp"] = $clientIp;

						$detail = array();
						$detail["tid"] = $pinfo['tid'];
						$detail["msg"] = "사용자 취소";
						$detail["price"] = (string)$refund_amount;
						$postdata["data"] = $detail;

						$details = str_replace('\\/', '/', json_encode($detail, JSON_UNESCAPED_UNICODE));
						$plainTxt = $key.$mid.$type.$timestamp.$details;
						$postdata["hashData"] = hash("sha512", $plainTxt);
						$post_data = json_encode($postdata, JSON_UNESCAPED_UNICODE);

						$url = "https://iniapi.inicis.com/v2/pg/refund";
						$ch = curl_init();
						curl_setopt($ch, CURLOPT_URL, $url);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
						curl_setopt($ch, CURLOPT_TIMEOUT, 30);
						curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
						curl_setopt($ch, CURLOPT_POST, 1);
						curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=utf-8'));
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
						curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

						$response = curl_exec($ch);
						$curl_errno = curl_errno($ch);
						$curl_error = curl_error($ch);
						$http_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
						curl_close($ch);
						$pay_result = null;
						$pg_error_msg = '';
						if($response === false){
							$pg_error_msg = "PG 통신 실패";
						}else if($http_code < 200 || $http_code >= 300){
							$pg_error_msg = "PG 응답 오류(HTTP {$http_code})";
						}else{
							$pay_result = json_decode($response);
							if(!is_object($pay_result)){
								$pg_error_msg = "PG 응답 파싱 실패";
							}else if(!isset($pay_result->resultCode)){
								$pg_error_msg = "PG 응답 형식 오류";
							}else if($pay_result->resultCode!="00"){
								$result_msg = isset($pay_result->resultMsg) ? $pay_result->resultMsg : 'PG 응답 오류';
								$pg_error_msg = "취소 오류:".$result_msg;
							}
						}
						$log_response = ($response === false || $response === null) ? '' : (string)$response;
						if($log_response === ''){
							$log_response = json_encode(array(
								'http_code' => $http_code,
								'curl_errno' => $curl_errno,
								'curl_error' => $curl_error
							), JSON_UNESCAPED_UNICODE);
						}

						sql_query("insert g5_membership_payment_log set
							uid='{$member['mb_id']}',
							order_no='{$chk['order_no']}',
							order_goods='{$pinfo['order_goods']}',
							price='{$refund_amount}',
							send='".addslashes($post_data)."',
							recive='".addslashes($log_response)."',
							regdate=now()");

						if($pg_error_msg){
							membership_refund_lock_release($refund_lock_name);
							echo "500|".$pg_error_msg;
							exit;
						}

						sql_query("START TRANSACTION");
						$already_refund = sql_fetch("select id from g5_matching_credit_history where order_no='{$chk['order_no']}' and tx_type='REFUND' limit 1");
						if($already_refund['id']){
							sql_query("ROLLBACK");
							membership_refund_lock_release($refund_lock_name);
							echo "500|이미 환불 처리된 결제입니다.";
							exit;
						}

						$status = sql_fetch("select * from g5_membership_status where uid='{$member['mb_id']}' for update");
						if(!$status['uid']){
							sql_query("ROLLBACK");
							membership_refund_lock_release($refund_lock_name);
							echo "500|취소 오류: 회원 상태 정보가 없습니다.";
							exit;
						}

						$current_credit = (int)$status['matching_able_cnt'];
						if($current_credit < $refund_credit){
							sql_query("ROLLBACK");
							membership_refund_lock_release($refund_lock_name);
							echo "500|취소 오류: 잔액 부족으로 환불할 수 없습니다.";
							exit;
						}

						$credit_after = $current_credit - $refund_credit;
						$memo_refund = addslashes("사용자 일반결제 환불 (수수료10% 차감, {$refund_amount}원)");
						$refund_history_sql = "insert into g5_matching_credit_history set
							uid='{$member['mb_id']}',
							tx_type='REFUND',
							credit_delta='-{$refund_credit}',
							credit_after='{$credit_after}',
							order_no='{$chk['order_no']}',
							matching_no='',
							memo='{$memo_refund}',
							created_at=now()";
						$refund_history = sql_query($refund_history_sql, false);
						if(!$refund_history){
							sql_query("ROLLBACK");
							membership_refund_lock_release($refund_lock_name);
							echo "500|취소 오류: 환불 원장 기록 실패";
							exit;
						}

						$refund_status_sql = "update g5_membership_status set
							matching_able_cnt=matching_able_cnt-{$refund_credit},
							matching_cnt=IF(matching_cnt >= {$refund_credit}, matching_cnt-{$refund_credit}, 0)
							where uid='{$member['mb_id']}' and matching_able_cnt >= {$refund_credit}";
						$refund_status = sql_query($refund_status_sql, false);
						$verify_status = sql_fetch("select matching_able_cnt from g5_membership_status where uid='{$member['mb_id']}'");
						if(!$refund_status || !isset($verify_status['matching_able_cnt']) || (int)$verify_status['matching_able_cnt'] !== (int)$credit_after){
							sql_query("ROLLBACK");
							membership_refund_lock_release($refund_lock_name);
							echo "500|취소 오류: 잔액 반영 실패";
							exit;
						}

						$snapshot = sql_fetch("select * from g5_membership_status where uid='{$member['mb_id']}'");
						$history_memo = addslashes("사용자 일반결제 환불\n");
						$history_status_sql = "insert g5_membership_status_history set
							uid='{$snapshot['uid']}',
							order_no='{$chk['order_no']}',
							date_begin='{$snapshot['date_begin']}',
							date_end='{$snapshot['date_end']}',
							month='{$snapshot['month']}',
							open_able_cnt='{$snapshot['open_able_cnt']}',
							open_cnt='{$snapshot['open_cnt']}',
							matching_able_cnt='{$snapshot['matching_able_cnt']}',
							matching_cnt='{$snapshot['matching_cnt']}',
							expert_consulting_able_cnt='{$snapshot['expert_consulting_able_cnt']}',
							expert_consulting_cnt='{$snapshot['expert_consulting_cnt']}',
							selected_able_cnt='{$snapshot['selected_able_cnt']}',
							selected_cnt='{$snapshot['selected_cnt']}',
							regdate=now(),
							status='{$snapshot['status']}',
							membership_parent_no='{$snapshot['membership_parent_no']}',
							memo='{$history_memo}',
							ext_1='".G5_TIME_YMDHIS."\\n'";
						sql_query($history_status_sql, false);

						sql_query("COMMIT");
						membership_refund_lock_release($refund_lock_name);
						echo "1000|결제 취소(환불) 처리되었습니다.";
						exit;
					}

					$start_time= strtotime($chk['date_begin']." 23:59:59");
					$day7=date("Y-m-d",strtotime("+1 week", $start_time));
					$day7_time=strtotime($day7." 23:59:59");
					$now_time=time();
					if($now_time<$day7_time){
						if($chk['matching_cnt']==$chk['matching_able_cnt'] && $chk['expert_consulting_able_cnt']==$chk['expert_consulting_cnt']) {
							$pinfo=sql_fetch("select * from g5_membership_paybill where order_no='{$chk['order_no']}'");
							$key = _key;
							$mid = _mid;
							$type = "refund";
							$timestamp = date("YmdHis");
							$clientIp = _clientIp;

							$postdata = array();
							$postdata["mid"] = $mid;
							$postdata["type"] = $type;
							$postdata["timestamp"] = $timestamp;
							$postdata["clientIp"] = $clientIp;
							$detail = array();
							$detail["tid"] = $pinfo['tid'];
							$detail["msg"] = "사용자 취소";

							$postdata["data"] = $detail;
							$details = str_replace('\\/', '/', json_encode($detail, JSON_UNESCAPED_UNICODE));
							$plainTxt = $key.$mid.$type.$timestamp.$details;
							$hashData = hash("sha512", $plainTxt);
							$postdata["hashData"] = $hashData;
							$post_data = json_encode($postdata, JSON_UNESCAPED_UNICODE);
							$url = "https://iniapi.inicis.com/v2/pg/refund";
							$ch = curl_init();
							curl_setopt($ch, CURLOPT_URL, $url);
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
							curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
							curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
							curl_setopt($ch, CURLOPT_POST, 1);
							curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=utf-8'));
							curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
							curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

							$response = curl_exec($ch);
							$pay_result=json_decode($response);
							curl_close($ch);

							$sql="insert g5_membership_payment_log set
								uid='{$member['mb_id']}',
								order_no='{$chk['order_no']}',
								order_goods='{$pinfo['order_goods']}',
								price='{$pinfo['price']}',
								send='".addslashes($post_data)."',
								recive='".addslashes($response)."',
								regdate=now()";
							sql_query($sql);

							if($pay_result->resultCode=="00"){
								sql_query("update g5_membership_status set status='C' where order_no='{$chk['order_no']}'");
								sql_query("update g5_membership_status_history set status='C', memo=CONCAT(memo, '{$detail["msg"]}\\n'), ext_1=CONCAT(ext_1, '".G5_TIME_YMDHIS."\\n') where order_no='{$chk['order_no']}'");
								echo "1000";
							}else{
								echo "500|취소 오류:".$pay_result->resultMsg;
							}
						}else{
							sql_query("update g5_membership_status set status='T' where uid='{$member['mb_id']}'");
							echo "1000|구독은 맴버쉽 만료 후 종료됩니다.";
							exit;
						}
					}else{
						sql_query("update g5_membership_status set status='T' where uid='{$member['mb_id']}'");
						echo "1000|구독은 맴버쉽 만료 후 종료됩니다.";
						exit;
					}
					exit;

					$chk['date_end'];
					$chk['membership_parent_no'];
				}else{
					echo "500|취소할 구독정보가 없습니다.";
				}
			}else{
				echo "500|로그인후 이용하세요.";
			}
	break;

	case "matching_num_chk": //매칭가능 수 체크

		if($member['mb_id']) {
				$today = date("Y-m-d");
				$msinfo=sql_fetch("select * from g5_membership_status where uid='{$member['mb_id']}' and date_begin <= '{$today }' and date_end >='{$today }'" );
				$cinfo=getComapyInfo($kedcd);
				if($msinfo['matching_able_cnt']>0) echo "1000|".$msinfo['matching_able_cnt']."|".$cinfo['ENP_NM'];
				else echo "500|".$msinfo['matching_able_cnt'];
		}else{
			echo "501|로그인 후 이용하세요.";
		}
	break;

	case "receipt": //영수증

		if($oid) {
				$receipt=sql_fetch("select * from g5_membership_paybill where order_no='{$oid}' " );
				if($receipt['tid']) echo "1000|".$receipt['tid'];
				else echo "500|결제 내역이 없습니다.";
		}else{
			echo "500";
		}
	break;

	case "coupon_chk": //쿠폰 체크

		if($id) {
				$chk=getCouponInfo($id);
				if($chk['chk']=="true"){
					echo "1000|".$chk['cp_type']."|".$chk['cp_subject']."|".$chk['cp_trunc'];
				}else{
					echo "500|".$chk['err'];
				}
		}else{
			echo "500|쿠폰번호를 확인해 주세요.";
		}
	break;

	case "use_coupon": //쿠폰사용
		if($coupon && $member['mb_id']) {
			$use=useCoupon($coupon,$member['mb_id']);
			if($use) echo "1000";
			else echo "500|쿠폰처리에 문제가 있습니다.";
		}else{
			echo "500|정상적으로 이용하세요.";
		}
	break;


    default:
        
}


/*
//매출액 일괄 업데이트
UPDATE company A, (SELECT DISTINCT KEDCD, SALES
						   FROM company_financial WHERE ACCT_DT=(
								    SELECT MAX(ACCT_DT)
								    FROM company_financial
								    WHERE KEDCD=KEDCD)
						 ) B
		SET A.SALES = B.SALES
	  WHERE A.KEDCD = B.KEDCD
*/

/*
//주요 거래처 일괄 업데이트
 UPDATE company_client A, (SELECT KEDCD, LOC_ADDRA, BZC_CD FROM company) B
    SET A.TX_ADDR = B.LOC_ADDRA, A.TX_BZC_CD=B.BZC_CD
  WHERE A.KEDCD = B.KEDCD
*/
/*
//주요거래처 업종명 일괄 업데이트
 UPDATE company_client A, (SELECT KEDCD, LOC_ADDRA, BZC_CD FROM company) B
    SET A.TX_ADDR = B.LOC_ADDRA, A.TX_BZC_CD=B.BZC_CD
  WHERE A.KEDCD = B.KEDCD
*/
//SELECT KEDCD, ENP_NM, SALES, rank FROM ( SELECT p.KEDCD, p.ENP_NM, F.SALES, CASE WHEN @prev_value = F.SALES THEN @vRank WHEN @prev_value := F.SALES THEN @vRank := @vRank + 1 END AS rank FROM company AS p LEFT JOIN company_financial F ON p.KEDCD=F.KEDCD, (SELECT @vRank := 0, @prev_value := NULL) AS r WHERE BZC_CD = 'C26422' ORDER BY ACCT_DT DESC) AS CNT WHERE KEDCD = '0000091399'  LIMIT 1; 
?>
