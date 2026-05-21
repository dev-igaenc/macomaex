<?
include_once('./_common.php');
include_once(G5_LIB_PATH.'/connect.lib.php');
include_once(G5_LIB_PATH.'/outlogin.lib.php');


switch ($type) {
	case "member_check":
		if($id) {
			$chk=sql_fetch("select count(*) as cnt from {$g5['member_table']} where mb_id='{$id}'");
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

	case "favorite"://관심기업
		if($member['mb_id'] && $chk) {
			if($chk=="true") {
				sql_query("insert g5_favorite set mb_id='{$member['mb_id']}', KEDCD='{$kedcd}', ms_datetime=now()");
			}else{ 
				sql_query("delete from g5_favorite where mb_id='{$member['mb_id']}' and KEDCD='{$kedcd}'");
			}
			echo "1000";
		}else{
			echo "500";
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
					$t_id=sql_fetch("select * from g5_member where mb_company='{$kedcd}'");
					$t_uinfo= getUserInfo($t_id['mb_id']);
					$corp_type['제조사']="M";
					$corp_type['유통사']="D";
					$corp_type['기타']="C";

					$rq_kind=$corp_type[$member['mb_3']];
					$rc_kind=$corp_type[$t_uinfo['mb_3']];
					sql_query("insert g5_matching set request_id='{$member['mb_id']}', rq_kind='{$rq_kind}', receive_id='{$t_id['mb_id']}',rc_kind='{$rc_kind}', KEDCD='{$kedcd}' ,receive_approval='N' , contents='".addslashes($match_content)."', regdate=now()");
						//알림톡 발송
						if($t_uinfo['mb_8']){
							$gw_msg = new NCP_SENS_AT();
							$gw_msg->templateCode	= 'reqmatching2'; // 템플릿 코드 매칭요청
							$gw_msg->to				= $t_uinfo['mb_8']; // 수신자 휴대폰번호
							$gw_msg->send();
						}
						//알림톡 발송 끝

						// legacy membership_status 기능 비활성화: 매칭 카운트 차감 미수행
						// sql_query("update g5_membership_status set matching_able_cnt=matching_able_cnt-1 where uid='{$member['mb_id']}'");
						// sql_query("update g5_membership_status set matching_able_cnt=matching_able_cnt+1 where uid='{$member['mb_id']}'");
					echo "1000";
				}else{
					echo "500";
				}
			}
		break;

		case "matching_check": //매칭체크
			// legacy membership_status 기능 비활성화
			break;
			/*

		//맴버쉽 정보
		$today = date("Y-m-d");
		$msinfo=sql_fetch("select * from g5_membership_status where (status='S' or status='T') and uid='{$member['mb_id']}' and date_begin <= '{$today }' and date_end >='{$today }'" );
		if($msinfo['uid']) {
			if($msinfo['matching_able_cnt']==0) {echo "500|매칭 가능 수량이 없습니다."; exit;}
		}else{
			echo "500|맴버쉽에 가입하시면 사용가능합니다.";
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

			*/

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
				if ($chk=="수락") $status="Y";
				if ($chk=="수락됨") $status="N";
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
			// legacy membership_status 기능 비활성화
			break;
			/*
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

			*/

	case "membership_pay": //맴버쉽 결제 시도
		
		if($member['mb_id']) {
				//맴버쉽 가져오기
				$msinfo_row = getUserMembership();
				$chk=$msinfo_row[0];
				
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
							$chk="500|이미 맴버쉽이 가입되어 있습니다."; //프리미엄 맴버쉽인 경우 결제 금지
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
					$chk="1000";
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
						else echo "1000";
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
			// legacy membership_status 기능 비활성화
			break;
			/*
	//상태(S:유지, C:취소(환불), E:종료(기간만료), T 기간만료후정지)
		if($member['mb_id']) {
				$chk=sql_fetch("select * from g5_membership_status where (status='S' or status='T') and uid='{$member['mb_id']}'");
				if($chk['uid']) {
					$minfo=sql_fetch("select * from g5_membership_manage where no='{$chk['membership_parent_no']}'");
					$start_time= strtotime($chk['date_begin']." 23:59:59");
					$day7=date("Y-m-d",strtotime("+1 week", $start_time));
					$day7_time=strtotime($day7." 23:59:59");
					$now_time=time();
					if($now_time<$day7_time){
					//7일 이내일경우 사용이력 없는경우 취소
						// 1) 매칭 요청을 1번이라도 한 경우
						// 2) 전문가컨설팅 서비스를 신청하거나, 전문가로부터 서비스를 제공받았을 경우
						if($chk['matching_cnt']==$chk['matching_able_cnt'] && $chk['expert_consulting_able_cnt']==$chk['expert_consulting_cnt']) {
								//무료가 아닐경우 실제 결제 취소 처리
								//step1. 요청을 위한 파라미터 설정
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
								//// Data 상세
								$detail = array();
								$detail["tid"] = $pinfo['tid'];
								$detail["msg"] = "사용자 취소";

								$postdata["data"] = $detail;
								$details = str_replace('\\/', '/', json_encode($detail, JSON_UNESCAPED_UNICODE));
								//// Hash Encryption
								$plainTxt = $key.$mid.$type.$timestamp.$details;
								$hashData = hash("sha512", $plainTxt);
								$postdata["hashData"] = $hashData;
								$post_data = json_encode($postdata, JSON_UNESCAPED_UNICODE);
								//step2. 요청전문 POST 전송
								$url = "https://iniapi.inicis.com/v2/pg/refund";
								$ch = curl_init();
								curl_setopt($ch, CURLOPT_URL, $url);
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
								curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
								curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
								curl_setopt($ch, CURLOPT_POST, 1);
								curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=utf-8'));
								curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
								 
								$response = curl_exec($ch);
								$pay_result=json_decode($response);
								curl_close($ch);

								//결제로그 기록
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
									//결제취소 성공
									echo "1000";
									//구독취소 처리
									sql_query("update g5_membership_status set status='C' where order_no='{$chk['order_no']}'");
									sql_query("update g5_membership_status_history set status='C' where order_no='{$chk['order_no']}'");
								}else{
									//결제취소 오류시
									echo "500|취소 오류:".$pay_result->resultMsg;
								}
			
						}else{ 
						//7일 이내 사용이력이 있는경우 구독취소 처리 (만료후 종료로 변경)
							sql_query("update g5_membership_status set status='T' where uid='{$member['mb_id']}'");
							echo "1000|구독은 맴버쉽 만료 후 종료됩니다.";
							exit;
						}

					}else{
						//7일 이후일 경우
						//구독취소 처리 (만료후 종료로 변경)
							sql_query("update g5_membership_status set status='T' where uid='{$member['mb_id']}'");
							echo "1000";
							exit;
					}
						exit;

					$chk['date_end'];//맴버쉽 종료기간
					$chk['membership_parent_no'];// 맴버쉽 종류
					//echo "1000";
				}else{
					echo "500|취소할 구독정보가 없습니다.";
				}
		}else{
			echo "500|로그인후 이용하세요.";
		}
	break;

			*/

		case "matching_num_chk": //매칭가능 수 체크
			// legacy membership_status 기능 비활성화
			break;
			/*

		if($member['mb_id']) {
				$today = date("Y-m-d");
				$msinfo=sql_fetch("select * from g5_membership_status where uid='{$member['mb_id']}' and date_begin <= '{$today }' and date_end >='{$today }'" );
				if($msinfo['matching_able_cnt']>0) echo "1000|".$msinfo['matching_able_cnt'];
				else echo "500|".$msinfo['matching_able_cnt'];
		}else{
			echo "500";
		}
	break;


			*/


    default:
        
}



/*
//매출액 일괄 업데이트
 UPDATE company A, (SELECT DISTINCT KEDCD, SALES
                       FROM company_financial
                       GROUP BY KEDCD
                      ORDER BY ACCT_DT DESC
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
