<?php
include_once('./_common.php');

if ($is_admin != 'super') exit;

$type = isset($_REQUEST['type']) ? trim((string)$_REQUEST['type']) : (isset($type) ? trim((string)$type) : '');

if (!function_exists('admin_ajax_audit_shutdown')) {
	function admin_ajax_audit_shutdown() {
		global $type;
		global $admin_ajax_audit_started_at;

		if (!$type || !function_exists('admin_audit_log')) {
			return;
		}

		$response = '';
		if (function_exists('ob_get_contents')) {
			$buffer = ob_get_contents();
			if ($buffer !== false && $buffer !== null) {
				$response = trim((string)$buffer);
			}
		}

		$result = 'SUCCESS';
		$message = '';
		if ($response === '500' || preg_match('/^500\|/u', $response)) {
			$result = 'FAIL';
			$parts = explode('|', $response, 2);
			$message = isset($parts[1]) ? trim($parts[1]) : '요청 실패';
		} else if ($response === '') {
			$result = 'INFO';
			$message = '응답 본문 없음';
		} else if (preg_match('/^1000\|/u', $response)) {
			$parts = explode('|', $response, 2);
			$message = isset($parts[1]) ? trim($parts[1]) : '';
		}

		$read_only_types = array('com_link', 'comp_search');
		if (in_array($type, $read_only_types) && $result !== 'FAIL') {
			return;
		}

		$target = '';
		if (isset($_REQUEST['oid']) && $_REQUEST['oid'] !== '') {
			$target = 'order_no:' . preg_replace('/[^0-9A-Za-z_\-]/', '', (string)$_REQUEST['oid']);
		} else if (isset($_REQUEST['KEDCD']) && $_REQUEST['KEDCD'] !== '') {
			$target = 'KEDCD:' . preg_replace('/[^0-9A-Za-z_\-]/', '', (string)$_REQUEST['KEDCD']);
		} else if (isset($_REQUEST['idx']) && $_REQUEST['idx'] !== '') {
			$target = 'idx:' . (int)$_REQUEST['idx'];
		}

		$payload = array(
			'elapsed_ms' => (int)round((microtime(true) - (float)$admin_ajax_audit_started_at) * 1000)
		);

		$params = array();
		$param_keys = array('type', 'oid', 'idx', 'S_NO', 'KEDCD', 'mb_no', 'board_name');
		foreach ($param_keys as $param_key) {
			if (!isset($_REQUEST[$param_key]) || $_REQUEST[$param_key] === '') {
				continue;
			}
			$params[$param_key] = function_exists('admin_audit_cut')
				? admin_audit_cut((string)$_REQUEST[$param_key], 120)
				: substr((string)$_REQUEST[$param_key], 0, 120);
		}
		if (!empty($params)) {
			$payload['params'] = $params;
		}
		if ($result === 'FAIL') {
			$payload['response'] = function_exists('admin_audit_cut')
				? admin_audit_cut(strip_tags($response), 500)
				: substr(strip_tags($response), 0, 500);
		}

		admin_audit_log('admin_ajax:' . $type, $target, $result, $message, $payload);
	}
}

$admin_ajax_audit_started_at = microtime(true);
if ($type && function_exists('admin_audit_log')) {
	ob_start();
	register_shutdown_function('admin_ajax_audit_shutdown');
}

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
		$matching = sql_fetch("select
				A.no,
				IFNULL(TRIM(A.adm_chk), '') as adm_chk,
				UPPER(TRIM(IFNULL((select request_type from g5_matching_credit_history where matching_no=A.no and tx_type='USE' order by id desc limit 1), ''))) as request_type
			from g5_matching A
			where A.no='{$idx}'
			limit 1");
		if(!$matching['no']) {echo "500"; exit;}

		$current_chk = trim((string)$matching['adm_chk']);
		if(!$current_chk) $current_chk = trim((string)$chk);

		$st_need = '연락필요';
		$st_prog = '진행중';
		$st_done = '연락완료';

		$is_tm_request = ($matching['request_type'] === 'TM');
		$ret = $st_need;
		if($is_tm_request){
			if($current_chk == $st_need) $ret = $st_prog;
			else if($current_chk == $st_prog) $ret = $st_done;
			else if($current_chk == $st_done) $ret = $st_done;
			else $ret = $st_need;
		}else{
			if($current_chk == $st_done) $ret = $st_need;
			else $ret = $st_done;
		}

		$result=sql_query("update g5_matching set adm_chk='{$ret}' where no='{$idx}'");
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
				<dt><?php if($row[ENP_NM_TRD]){?><?=$row[ENP_NM_TRD]?><?php }else{?><?=$row[ENP_NM]?><?php }?></dt>
				<dd>/ 사업자번호:<?=$row[BZNO]?></dd>
				<dd class="bn1"><button type="button" class="btn btn-info link_sel" link_data="/ko/matching_view.php?cd=<?=$row[KEDCD]?>">선택</button></dd>
				<dd class="bn2"><a type="button" class="btn btn-info text-light" href="/ko/matching_view.php?cd=<?=$row[KEDCD]?>" target="_blank">새창열기</a></dd>
			</dl>
	<?php
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
				$chk=sql_fetch("select * from g5_membership_status where order_no='{$oid}'");
				$porder=sql_fetch("select uid, membership_parent_no from g5_membership_payment where order_no='{$oid}'");
				if(!$pinfo['uid'] && $porder['uid']) $pinfo['uid'] = $porder['uid'];
				if(!$pinfo['uid'] && $chk['uid']) $pinfo['uid'] = $chk['uid'];
				if(!$pinfo['membership_parent_no'] && $porder['membership_parent_no']) $pinfo['membership_parent_no'] = $porder['membership_parent_no'];
				if(!$pinfo['membership_parent_no'] && $chk['membership_parent_no']) $pinfo['membership_parent_no'] = $chk['membership_parent_no'];

				$minfo=sql_fetch("select * from g5_membership_manage where no='{$pinfo['membership_parent_no']}'");
				$is_credit_payment = false;
				if(($minfo['title_en'] && strpos($minfo['title_en'], 'CREDIT_') === 0) || ((int)$minfo['matching_cnt'] > 0 && (int)$minfo['month'] === 0)){
					$is_credit_payment = true;
				}
				//결제취소 프로세스
				//step1. 요청을 위한 파라미터 설정
				if($is_credit_payment){
					$is_test_pay = defined('_test_pay') && strtoupper((string)_test_pay) === 'Y';
					$stdpay_mid = (defined('_stdpay_mid') && trim((string)_stdpay_mid) !== '') ? trim((string)_stdpay_mid) : ((defined('_mid') && trim((string)_mid) !== '') ? trim((string)_mid) : '');
					$stdpay_mid_test = (defined('_stdpay_mid_test') && trim((string)_stdpay_mid_test) !== '') ? trim((string)_stdpay_mid_test) : ($is_test_pay ? $stdpay_mid : '');
					$stdpay_api_key_real = (defined('_stdpay_api_key_real') && trim((string)_stdpay_api_key_real) !== '') ? trim((string)_stdpay_api_key_real) : ((defined('_key') && trim((string)_key) !== '') ? trim((string)_key) : '');
					$stdpay_api_key_test = (defined('_stdpay_api_key_test') && trim((string)_stdpay_api_key_test) !== '') ? trim((string)_stdpay_api_key_test) : (($is_test_pay && defined('_key') && trim((string)_key) !== '') ? trim((string)_key) : $stdpay_api_key_real);
					$tid_mid = (strpos((string)$pinfo['tid'], 'INIpayTest') !== false) ? $stdpay_mid_test : '';
					$mid = $tid_mid ? $tid_mid : ($pinfo['mid'] ? $pinfo['mid'] : $stdpay_mid);
					$key = ($stdpay_mid_test && $mid == $stdpay_mid_test) ? $stdpay_api_key_test : $stdpay_api_key_real;
					if(!$key || !$mid){
						echo "500|취소 오류: 일반결제 환불 설정이 부족합니다.";
						exit;
					}
				}else{
					$key = _key;
					$mid = _mid;
				}
				$refund_credit = (int)$minfo['matching_cnt'];
				$refund_amount = 0;
				if($is_credit_payment){
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
						where uid='{$pinfo['uid']}'");
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
				}

				$type = "refund";
				$timestamp = date("YmdHis");
				$clientIp = _clientIp;

				$refund_lock_name = '';
				if($is_credit_payment){
					$refund_lock_name = membership_refund_lock_acquire($oid, 0);
					if(!$refund_lock_name){
						echo "500|동일 주문의 환불이 처리 중입니다. 잠시 후 다시 시도해 주세요.";
						exit;
					}
					$already_refund = sql_fetch("select id from g5_matching_credit_history where order_no='{$oid}' and tx_type='REFUND' limit 1");
					if($already_refund['id']){
						membership_refund_lock_release($refund_lock_name);
						echo "500|이미 환불 처리된 결제입니다.";
						exit;
					}
				}
				
				$postdata = array();
				$postdata["mid"] = $mid;
				$postdata["type"] = $type;
				$postdata["timestamp"] = $timestamp;
				$postdata["clientIp"] = $clientIp;
				//// Data 상세
				$detail = array();
				$detail["tid"] = $pinfo['tid'];
				$detail["msg"] = "관리자 취소";
				if($is_credit_payment){
					$detail["price"] = (string)$refund_amount;
				}

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
				if($is_credit_payment){
					curl_setopt($ch, CURLOPT_TIMEOUT, 30);
				}
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=utf-8'));
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				 
				$response = curl_exec($ch);
				$curl_errno = curl_errno($ch);
				$curl_error = curl_error($ch);
				$http_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
				$pay_result = null;
				if($response !== false && $response !== null && $response !== ''){
					$pay_result=json_decode($response);
				}
				$pg_error_msg = '';
				if($is_credit_payment){
					if($response === false){
						$pg_error_msg = "PG 통신 실패";
					}else if($http_code < 200 || $http_code >= 300){
						$pg_error_msg = "PG 응답 오류(HTTP {$http_code})";
					}else if(!is_object($pay_result)){
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
				if(!$chk['uid']){
					$chk=sql_fetch("select * from g5_membership_status where uid='{$pinfo['uid']}'");
				}

				//결제로그 기록
				$log_uid = $member['mb_id'] ? $member['mb_id'] : $pinfo['uid'];
				$log_price = $pinfo['price'];
				if($is_credit_payment){
					$log_price = $refund_amount;
				}
				$sql="insert g5_membership_payment_log set 
					uid='{$log_uid}',
					order_no='{$oid}',
					order_goods='{$pinfo['order_goods']}',
					price='{$log_price}',
					send='".addslashes($post_data)."',
					recive='".addslashes($log_response)."',
					regdate=now()";
				sql_query($sql);

				if($is_credit_payment && $pg_error_msg){
					membership_refund_lock_release($refund_lock_name);
					echo "500|".$pg_error_msg;
					exit;
				}

				if(is_object($pay_result) && isset($pay_result->resultCode) && $pay_result->resultCode=="00"){
					//결제취소 성공
					if($is_credit_payment){
						sql_query("START TRANSACTION");
						$already_refund = sql_fetch("select id from g5_matching_credit_history where order_no='{$oid}' and tx_type='REFUND' limit 1");
						if($already_refund['id']){
							sql_query("ROLLBACK");
							membership_refund_lock_release($refund_lock_name);
							echo "500|이미 환불 처리된 결제입니다.";
							exit;
						}

						$status = sql_fetch("select * from g5_membership_status where uid='{$pinfo['uid']}' for update");
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
						$memo_refund = addslashes("관리자 일반결제 환불 (수수료10% 차감, {$refund_amount}원)");
						$refund_history_sql = "insert into g5_matching_credit_history set
							uid='{$pinfo['uid']}',
							tx_type='REFUND',
							credit_delta='-{$refund_credit}',
							credit_after='{$credit_after}',
							order_no='{$oid}',
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
							where uid='{$pinfo['uid']}' and matching_able_cnt >= {$refund_credit}";
						$refund_status = sql_query($refund_status_sql, false);
						$verify_status = sql_fetch("select matching_able_cnt from g5_membership_status where uid='{$pinfo['uid']}'");
						if(!$refund_status || !isset($verify_status['matching_able_cnt']) || (int)$verify_status['matching_able_cnt'] !== (int)$credit_after){
							sql_query("ROLLBACK");
							membership_refund_lock_release($refund_lock_name);
							echo "500|취소 오류: 잔액 반영 실패";
							exit;
						}

						$snapshot = sql_fetch("select * from g5_membership_status where uid='{$pinfo['uid']}'");
						$history_memo = addslashes("관리자 일반결제 환불\n");
						$history_status_sql = "insert g5_membership_status_history set
							uid='{$snapshot['uid']}',
							order_no='{$oid}',
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

						sql_query("update g5_membership_status set status='C' where order_no='{$oid}'");
						sql_query("update g5_membership_status_history set status='C', memo=CONCAT(memo, '{$detail["msg"]}\\n'), ext_1=CONCAT(ext_1, '".G5_TIME_YMDHIS."\\n') where order_no='{$oid}'");

						sql_query("COMMIT");
						membership_refund_lock_release($refund_lock_name);
						echo "1000|결제 취소 되었습니다.";
					}else{
						sql_query("update g5_membership_status set status='C' where order_no='{$chk['order_no']}'");
						sql_query("update g5_membership_status_history set status='C', memo=CONCAT(memo, '{$detail["msg"]}\\n'), ext_1=CONCAT(ext_1, '".G5_TIME_YMDHIS."\\n') where order_no='{$chk['order_no']}'");
						membership_refund_lock_release($refund_lock_name);
						echo "1000|결제 취소 되었습니다.";
					}
				}else{
					//결제취소 오류시
					$result_msg = (is_object($pay_result) && isset($pay_result->resultMsg) && $pay_result->resultMsg) ? $pay_result->resultMsg : 'PG 응답 오류';
					membership_refund_lock_release($refund_lock_name);
					echo "500|취소 오류:".$result_msg;
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
				 <?php for ($i = 0; $row = sql_fetch_array($result); $i++) {?>
				<tr>
					<td class="mb_id text-left p-3">
						<span class="btn-sm btn-secondary"><?=$row['ENP_NM']?></span><br><br>
						주소: <?=$row['LOC_ADDRA']?> <?=$row['LOC_ADDRB']?><br>
						종목: <?=$row['BZC_NAME']?><br>
						담당자: <?php if($row['M_TEL']){?><?=$row['M_TEL']?><?php }else{?><span class="text-danger">미등록</span><?php }?><br>
					</td>
					<td><?=$row['BZNO']?></td>
					<td><button type="button" id="id_slect" class="btn-info btn btn-sm" CINFO="<?=$row['KEDCD']?>|<?=$row['ENP_NM']?>|<?=$row['BZNO']?>|<?=$row['LOC_ADDRA']?> <?=$row['LOC_ADDRB']?>|<?=$row['BZC_NAME']?>">등록</button></td>
				</tr>
				<?php }?>
			</table>
	<?php
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
