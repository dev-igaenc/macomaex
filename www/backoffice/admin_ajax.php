<?php
include_once('./_common.php');

if ($is_admin != 'super') exit;

switch ($type) {

    case "category":
        $sql="update g5_board set bo_category_list='".addslashes($data)."' where bo_table='".$board_name."' ";
		sql_query($sql);
		echo "1000";
        break;

    case "company_update": //기업정보 변경
		if(!$KEDCD) {echo "500"; exit;}
		$ENP_SZE= $_GET['ENP_SZE'];
		$BZNO=str_replace("-","",$BZNO);
		$ESTB_DT=str_replace("-","",$ESTB_DT);
		$CONO_PID=str_replace("-","",$CONO_PID);

        $sql="update company set ";
		$sql.="ENP_NM='".$ENP_NM."', ";
		$sql.="ENP_NM_ENG='".$ENP_NM_ENG."', ";
		$sql.="CONO_PID='".$CONO_PID."', ";
		$sql.="ENP_SZE='".$ENP_SZE."', ";
		$sql.="HPAGE_URL='".$HPAGE_URL."', ";
		$sql.="PD_NM='".$PD_NM."', ";
		$sql.="BZNO='".$BZNO."', ";
		$sql.="ESTB_DT='".$ESTB_DT."', ";
		$sql.="LABORER_SUM='".$LABORER_SUM."', ";
		$sql.="ENP_TYP='".$ENP_TYP."', ";
		$sql.="LOC_ZIP='".$LOC_ZIP."', ";
		$sql.="LOC_ADDRA='".$LOC_ADDRA."', ";
		$sql.="LOC_ADDRB='".$LOC_ADDRB."', ";
		$sql.="BZC_CD='".$BZC_CD."', ";
		$sql.="KSIC9_BZC_CD='".$KSIC9_BZC_CD."' ";
		$sql.="where KEDCD='".$KEDCD."' ";
		$result=sql_query($sql);
		if($result) echo "1000";
		else echo "500";
        break;

    case "agent_update": //담당자정보 변경
		if(!$mb_no) {echo "500"; exit;}
		$mb_no= $_GET['mb_no'];
        $sql="update g5_member set ";
		$sql.="mb_6='".$mb_6."', ";
		$sql.="mb_7='".$mb_7."', ";
		$sql.="mb_8='".$mb_8."', ";
		$sql.="mb_email='".$mb_email."' ";
		$sql.="where mb_no='".$mb_no."' ";
		$result=sql_query($sql);
		if($result) echo "1000";
		else echo "500";
        break;

    case "credit_update": //신용등급 변경
		$KEDCD= $_GET['KEDCD'];

		if(!$KEDCD) {echo "500"; exit;}
		$chk=sql_fetch("select count(*) as cnt from company_credit where KEDCD='{$KEDCD}'");
		if($chk[cnt]==0){
			$sql="insert company_credit set ";
			$sql.="KEDCD='".$KEDCD."', ";
			$sql.="ACCT_DT='".$ACCT_DT."', ";
			$sql.="CREDIT_1='".$CREDIT_1."', ";
			$sql.="CREDIT_2='".$CREDIT_2."' ";
		}else{
			$sql="update company_credit set ";
			$sql.="ACCT_DT='".$ACCT_DT."', ";
			$sql.="CREDIT_1='".$CREDIT_1."', ";
			$sql.="CREDIT_2='".$CREDIT_2."', ";
			$sql.="where KEDCD='".$KEDCD."' ";
		}
		$result=sql_query($sql);
		if($result) echo "1000";
		else echo "500";
        break;

    case "matching_admchk": //매칭 관리자체크

		if(!$idx) {echo "500"; exit;}
		$ret="";
		if($chk=="연락완료") {
			$ret="연락필요";
			$sql="update g5_matching set adm_chk='' where no='{$idx}'";
		}else{
			$ret="연락완료";
			$sql="update g5_matching set adm_chk='연락완료' where no='{$idx}'";
		}
		$result=sql_query($sql);
		if($result) echo $ret;
		else echo "500";
        break;

    case "zip_pass": //패스워드 변경

		$sql="update g5_config set cf_10='{$pass}'";
		$result=sql_query($sql);
		if($result) echo "1000";
		else echo "500";
        break;

    case "member_euse": //전문가컨설팅 사용 체크
		if($chk &&  $idx){
			$mif=sql_fetch("select * from g5_membership_status where no='{$idx}'");
			if($chk=="미사용") $cnt=0;
			else $cnt=$mif['expert_consulting_cnt'];
			$sql="update g5_membership_status set expert_consulting_able_cnt='{$cnt}' where no='{$idx}'";
			$result=sql_query($sql);
			if($result) echo "1000";
			else echo "500";
		}else{
			echo "500";
		}
        break;


    case "com_link": //기업홍보관 링크 검색
		if($com_name){
			$sql="select * from company  where ENP_NM like '%{$com_name}%' or ENP_NM_TRD like '%{$com_name}%'";
			$result=sql_query($sql);
			echo '<button type="button" class="btn btn-warning cl_btn"><i class="fa fa-times" aria-hidden="true"></i></button>';
			 for ($i=0; $row=sql_fetch_array($result); $i++) {
?>
		<dl>
			<dt><?if($row[ENP_NM_TRD]){?><?=$row[ENP_NM_TRD]?><?}else{?><?=$row[ENP_NM]?><?}?></dt>
			<dd>/ 사업자번호:<?=$row[BZNO]?></dd>
			<dd class="bn1"><button type="button" class="btn btn-info link_sel" link_data="/ko/matching_view.php?cd=<?=$row[KEDCD]?>">선택</button></dd>
			<dd class="bn2"><a type="button" class="btn btn-info text-light" href="/ko/matching_view.php?cd=<?=$row[KEDCD]?>" target="_blank">새창열기</a></dd>
		</dl>
<?
			 }
		}else{
			echo "500";
		}
        break;

    case "pay_cancel": //관리자 결제 취소 (정기결제관리)
		if($oid){
				$pinfo=sql_fetch("select * from g5_membership_paybill where order_no='{$oid}'" );
				if(!$pinfo['tid']) {
					echo "500|결제 내역이 없습니다.";
					exit;
				}
				//결제취소 프로세스
				//step1. 요청을 위한 파라미터 설정
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
				$detail["msg"] = "관리자 취소";

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

				$chk=sql_fetch("select * from g5_membership_status where order_no='{$oid}'");

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
					echo "1000|결제 취소 되었습니다.";
					//구독취소 처리
					sql_query("update g5_membership_status set status='C' where order_no='{$chk['order_no']}'");
					sql_query("update g5_membership_status_history set status='C', memo=CONCAT(memo, '{$detail["msg"]}\\n'), ext_1=CONCAT(ext_1, '".G5_TIME_YMDHIS."\\n') where order_no='{$chk['order_no']}'");
					
				}else{
					//결제취소 오류시
					echo "500|취소 오류:".$pay_result->resultMsg;
				}

		}else{
			echo "500";
		}
        break;

    case "comp_search":
		$sql = " select *,(select k_code_name from kssc where k_code=KSIC9_BZC_CD) as BZC_NAME, (select mb_8 from g5_member where mb_company=KEDCD) as M_TEL from company where ENP_NM like '%".$sr_txt."%' or  BZNO like '%".$sr_txt."%' ";
		$result = sql_query($sql);
?>
		<table class="mb_data">
			<tr class="title"><td>회사명</td><td width="150">사업자번호</td><td width="68">선택</td></tr>
			 <?for ($i = 0; $row = sql_fetch_array($result); $i++) {?>
			<tr>
				<td class="mb_id text-left p-3">
					<span class="btn-sm btn-secondary"><?=$row['ENP_NM']?></span><br><br>
					주소: <?=$row['LOC_ADDRA']?> <?=$row['LOC_ADDRB']?><br>
					종목: <?=$row['BZC_NAME']?><br>
					담당자: <?if($row['M_TEL']){?><?=$row['M_TEL']?><?}else{?><span class="text-danger">미등록</span><?}?><br>
				</td>
				<td><?=$row['BZNO']?></td>
				<td><button type="button" id="id_slect" class="btn-info btn btn-sm" CINFO="<?=$row['KEDCD']?>|<?=$row['ENP_NM']?>|<?=$row['BZNO']?>|<?=$row['LOC_ADDRA']?> <?=$row['LOC_ADDRB']?>|<?=$row['BZC_NAME']?>">등록</button></td>
			</tr>
			<?}?>
		</table>
<?
        break;

    case "add_seleted": //셀렉티드 관리자 추가
		if($S_NO &&  $CINFO){
			$Range=SelectedRange($S_NO);
			$CINFO=explode("|",$CINFO);
			$P_INFO=sql_fetch("select * from g5_selected where s_no={$S_NO}");
			$sql="insert  g5_selected_data set 
			s_parent='{$S_NO}',
			s_subject='{$CINFO[1]}',
			KEDCD='{$CINFO[0]}',
			mb_id='{$P_INFO[mb_id]}',
			s_start='{$Range[NOW_S]}',
			s_end='{$Range[NOW_E]}',
			s_status='S',
			s_datetime=now()";
			$result=sql_query($sql);
			$s_cnt=sql_fetch("select count(*) as cnt, mb_id  from g5_selected_data where s_parent={$S_NO} and s_status='S'");
			sql_query("update g5_selected set s_cnt='{$s_cnt[cnt]}', s_modi=now() where s_no={$S_NO}");
			sql_query("update g5_membership_status set selected_able_cnt=selected_cnt-{$s_cnt[cnt]} where uid='{$s_cnt['mb_id']}' ");
			$mat=sql_fetch("select * from g5_membership_status where uid='{$s_cnt['mb_id']}' ");
			sql_query("update g5_membership_status_history set selected_able_cnt=selected_cnt-{$s_cnt[cnt]} where uid='{$s_cnt['mb_id']}' and order_no='{$mat['order_no']}' ");
			if($result) echo "1000";
			else echo "500";
		}else{
			echo "500";
		}
        break;

    case "del_seleted": //셀렉티드 관리자 삭제
		if($S_NO){
			$P_INFO=sql_fetch("select * from g5_selected_data where s_no={$S_NO}");
			$result=sql_query("delete  from g5_selected_data where s_no={$S_NO}");
			$s_cnt=sql_fetch("select count(*)  as cnt, mb_id from g5_selected_data where s_parent={$P_INFO[s_parent]} and s_status='S'");
			if(!$s_cnt[cnt]) $s_cnt[cnt]=0;
			sql_query("update g5_selected set s_cnt='{$s_cnt[cnt]}', s_modi=now() where s_no={$P_INFO[s_parent]}");
			sql_query("update g5_membership_status set selected_able_cnt=selected_cnt-{$s_cnt[cnt]} where uid='{$P_INFO['mb_id']}' ");
			$mat=sql_fetch("select * from g5_membership_status where uid='{$P_INFO['mb_id']}' ");
			sql_query("update g5_membership_status_history set selected_able_cnt=selected_cnt-{$s_cnt[cnt]} where uid='{$P_INFO['mb_id']}' and order_no='{$mat['order_no']}' ");
			if($result) echo "1000";
			else echo "500";
		}else{
			echo "500";
		}
        break;

}
exit;