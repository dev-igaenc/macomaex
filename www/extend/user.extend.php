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

if (!function_exists('credit_lot_tables_ready')) {
	// LOT 기반 충전/차감 테이블이 준비된 환경인지 확인한다.
	function credit_lot_tables_ready() {
		static $ready = null;
		if ($ready !== null) return $ready;

		$lot = sql_fetch("SHOW TABLES LIKE 'credit_lot'");
		$lot_use = sql_fetch("SHOW TABLES LIKE 'credit_lot_use'");
		$ready = (is_array($lot) && count($lot) > 0 && is_array($lot_use) && count($lot_use) > 0);
		return $ready;
	}
}

if (!function_exists('credit_ledger_get_latest_row')) {
	// 원장 최신 1건(credit_after)을 조회한다. 차감 시에는 FOR UPDATE로 잠근다.
	function credit_ledger_get_latest_row($mb_id, $for_update = false) {
		$mb_id = trim((string)$mb_id);
		if ($mb_id === '') return array();
		$mb_id_sql = sql_escape_string($mb_id);
		$for_update_sql = $for_update ? " for update" : "";
		$row = sql_fetch("select id, credit_after
			from credit_history
			where uid='{$mb_id_sql}'
			order by id desc
			limit 1{$for_update_sql}");
		return is_array($row) ? $row : array();
	}
}

if (!function_exists('credit_ledger_get_balance')) {
	// 실시간 잔액은 원장 최신 credit_after를 사용한다.
	function credit_ledger_get_balance($mb_id) {
		$row = credit_ledger_get_latest_row($mb_id, false);
		return isset($row['credit_after']) ? (int)$row['credit_after'] : 0;
	}
}

if (!function_exists('credit_lot_register_charge')) {
	// CHARGE 원장 1건을 LOT 1건으로 등록한다.
	function credit_lot_register_charge($mb_id, $charge_history_id, $order_no, $credit_amount, $charged_at = '') {
		$ret = array('ok' => false, 'skip' => false, 'message' => '');
		if (!credit_lot_tables_ready()) {
			$ret['skip'] = true;
			$ret['message'] = 'credit_lot tables not ready';
			return $ret;
		}

		$mb_id = trim((string)$mb_id);
		$charge_history_id = (int)$charge_history_id;
		$order_no = trim((string)$order_no);
		$credit_amount = (int)$credit_amount;
		if ($mb_id === '' || $charge_history_id < 1 || $order_no === '' || $credit_amount < 1) {
			$ret['message'] = 'invalid params';
			return $ret;
		}

		$charged_at_ts = strtotime((string)$charged_at);
		if (!$charged_at_ts) $charged_at_ts = time();
		$charged_at_sql = date('Y-m-d H:i:s', $charged_at_ts);
		$refund_deadline_at_sql = date('Y-m-d H:i:s', strtotime('+7 days', $charged_at_ts));

		$mb_id_sql = sql_escape_string($mb_id);
		$order_no_sql = sql_escape_string($order_no);
		$insert_sql = "insert into credit_lot set
			mb_id='{$mb_id_sql}',
			charge_history_id='{$charge_history_id}',
			order_no='{$order_no_sql}',
			credit_amount='{$credit_amount}',
			remain_amount='{$credit_amount}',
			charged_at='{$charged_at_sql}',
			refund_deadline_at='{$refund_deadline_at_sql}',
			lot_status='ACTIVE',
			refunded_at=NULL,
			created_at=now(),
			updated_at=now()
			on duplicate key update
				lot_id=lot_id";
		$ok = sql_query($insert_sql, false);
		$ret['ok'] = (bool)$ok;
		$ret['message'] = $ok ? 'ok' : 'insert failed';
		return $ret;
	}
}

if (!function_exists('credit_lot_allocate_use')) {
	// USE 원장 1건을 FIFO로 여러 LOT에 분할 차감한다.
	function credit_lot_allocate_use($mb_id, $use_history_id, $use_amount, $used_at = '') {
		$ret = array('ok' => false, 'skip' => false, 'allocated' => 0, 'missing' => 0, 'message' => '');
		if (!credit_lot_tables_ready()) {
			$ret['skip'] = true;
			$ret['message'] = 'credit_lot tables not ready';
			return $ret;
		}

		$mb_id = trim((string)$mb_id);
		$use_history_id = (int)$use_history_id;
		$use_amount = (int)$use_amount;
		if ($mb_id === '' || $use_history_id < 1 || $use_amount < 1) {
			$ret['message'] = 'invalid params';
			return $ret;
		}

		$mb_id_sql = sql_escape_string($mb_id);
		$already = sql_fetch("select lot_use_id from credit_lot_use where use_history_id='{$use_history_id}' limit 1");
		if (is_array($already) && !empty($already['lot_use_id'])) {
			$ret['ok'] = true;
			$ret['allocated'] = $use_amount;
			$ret['message'] = 'already allocated';
			return $ret;
		}

		$rows = array();
		$total_remain = 0;
		$result = sql_query("select lot_id, remain_amount
			from credit_lot
			where mb_id='{$mb_id_sql}'
			  and lot_status<>'REFUNDED'
			  and remain_amount > 0
			order by charged_at asc, lot_id asc
			for update", false);
		if ($result) {
			for ($i=0; $row=sql_fetch_array($result); $i++) {
				$rows[] = $row;
				$total_remain += (int)$row['remain_amount'];
			}
		}
		if ($total_remain < $use_amount) {
			$ret['missing'] = $use_amount - $total_remain;
			$ret['message'] = 'not enough lot remain';
			return $ret;
		}

		$used_at_ts = strtotime((string)$used_at);
		if (!$used_at_ts) $used_at_ts = time();
		$used_at_sql = date('Y-m-d H:i:s', $used_at_ts);

		$remain_need = $use_amount;
		$allocated = 0;
		for ($i=0; $i<count($rows); $i++) {
			if ($remain_need < 1) break;
			$lot_id = (int)$rows[$i]['lot_id'];
			$lot_remain = (int)$rows[$i]['remain_amount'];
			if ($lot_id < 1 || $lot_remain < 1) continue;

			$alloc = min($remain_need, $lot_remain);
			$upd = sql_query("update credit_lot set
					remain_amount=remain_amount-{$alloc},
					lot_status=case when remain_amount = {$alloc} then 'EXHAUSTED' else 'ACTIVE' end,
					updated_at=now()
				where lot_id='{$lot_id}'
				  and remain_amount >= {$alloc}", false);
			$affected = sql_fetch("select row_count() as cnt");
			if (!$upd || (int)$affected['cnt'] < 1) {
				$ret['message'] = 'lot update failed';
				return $ret;
			}

			$ins = sql_query("insert into credit_lot_use set
				use_history_id='{$use_history_id}',
				lot_id='{$lot_id}',
				mb_id='{$mb_id_sql}',
				use_amount='{$alloc}',
				created_at='{$used_at_sql}'", false);
			if (!$ins) {
				$ret['message'] = 'lot_use insert failed';
				return $ret;
			}

			$allocated += $alloc;
			$remain_need -= $alloc;
		}

		$ret['ok'] = ($allocated === $use_amount);
		$ret['allocated'] = $allocated;
		$ret['missing'] = max(0, $use_amount - $allocated);
		$ret['message'] = $ret['ok'] ? 'ok' : 'partial';
		return $ret;
	}
}

if (!function_exists('credit_lot_release_use')) {
	// USE 원장 롤백 시 기존 LOT 차감을 되돌린다.
	function credit_lot_release_use($use_history_id) {
		$ret = array('ok' => false, 'skip' => false, 'released' => 0, 'message' => '');
		if (!credit_lot_tables_ready()) {
			$ret['skip'] = true;
			$ret['message'] = 'credit_lot tables not ready';
			return $ret;
		}

		$use_history_id = (int)$use_history_id;
		if ($use_history_id < 1) {
			$ret['message'] = 'invalid params';
			return $ret;
		}

		$result = sql_query("select lot_use_id, lot_id, use_amount
			from credit_lot_use
			where use_history_id='{$use_history_id}'
			order by lot_use_id asc
			for update", false);
		$rows = array();
		if ($result) {
			for ($i=0; $row=sql_fetch_array($result); $i++) {
				$rows[] = $row;
			}
		}
		if (!count($rows)) {
			$ret['ok'] = true;
			$ret['message'] = 'nothing to release';
			return $ret;
		}

		$released = 0;
		for ($i=0; $i<count($rows); $i++) {
			$lot_id = (int)$rows[$i]['lot_id'];
			$use_amount = (int)$rows[$i]['use_amount'];
			if ($lot_id < 1 || $use_amount < 1) continue;

			$upd = sql_query("update credit_lot set
					remain_amount=remain_amount+{$use_amount},
					lot_status=case when lot_status='REFUNDED' then lot_status else 'ACTIVE' end,
					updated_at=now()
				where lot_id='{$lot_id}'", false);
			if (!$upd) {
				$ret['message'] = 'lot release update failed';
				return $ret;
			}
			$released += $use_amount;
		}

		$del = sql_query("delete from credit_lot_use where use_history_id='{$use_history_id}'", false);
		if (!$del) {
			$ret['message'] = 'lot_use delete failed';
			return $ret;
		}

		$ret['ok'] = true;
		$ret['released'] = $released;
		$ret['message'] = 'ok';
		return $ret;
	}
}

if (!function_exists('credit_lot_find_refundable_by_order')) {
	// 주문번호 기준으로 "미사용 + 7일 이내" 환불 가능 LOT를 찾는다.
	function credit_lot_find_refundable_by_order($mb_id, $order_no, $as_of = '') {
		$ret = array(
			'ok' => false,
			'skip' => false,
			'message' => '',
			'lot' => null,
			'refund_credit' => 0
		);
		if (!credit_lot_tables_ready()) {
			$ret['skip'] = true;
			$ret['message'] = 'credit_lot tables not ready';
			return $ret;
		}

		$mb_id = trim((string)$mb_id);
		$order_no = trim((string)$order_no);
		if ($mb_id === '' || $order_no === '') {
			$ret['message'] = 'invalid params';
			return $ret;
		}

		$mb_id_sql = sql_escape_string($mb_id);
		$order_no_sql = sql_escape_string($order_no);
		$lot = sql_fetch("select lot_id, mb_id, charge_history_id, order_no, credit_amount, remain_amount, charged_at, refund_deadline_at, lot_status, refunded_at
			from credit_lot
			where mb_id='{$mb_id_sql}'
			  and order_no='{$order_no_sql}'
			order by lot_id desc
			limit 1");
		if (!is_array($lot) || empty($lot['lot_id'])) {
			$ret['message'] = 'charge lot not found';
			return $ret;
		}

		$as_of_ts = strtotime((string)$as_of);
		if (!$as_of_ts) $as_of_ts = time();
		$deadline_ts = strtotime((string)$lot['refund_deadline_at']);
		$credit_amount = (int)$lot['credit_amount'];
		$remain_amount = (int)$lot['remain_amount'];
		$lot_status = strtoupper(trim((string)$lot['lot_status']));

		if ($credit_amount < 1) {
			$ret['message'] = 'invalid lot amount';
			return $ret;
		}
		if ($lot_status === 'REFUNDED') {
			$ret['message'] = 'already refunded';
			return $ret;
		}
		if ($deadline_ts && $as_of_ts > $deadline_ts) {
			$ret['message'] = 'refund deadline passed';
			return $ret;
		}
		if ($remain_amount !== $credit_amount) {
			$ret['message'] = 'lot already used';
			return $ret;
		}
		if ($lot_status !== 'ACTIVE') {
			$ret['message'] = 'lot is not active';
			return $ret;
		}

		$ret['ok'] = true;
		$ret['lot'] = $lot;
		$ret['refund_credit'] = $credit_amount;
		$ret['message'] = 'ok';
		return $ret;
	}
}

if (!function_exists('credit_lot_mark_refunded')) {
	// 환불 확정된 LOT를 REFUNDED 상태로 마감한다.
	function credit_lot_mark_refunded($lot_id) {
		$ret = array('ok' => false, 'skip' => false, 'message' => '');
		if (!credit_lot_tables_ready()) {
			$ret['skip'] = true;
			$ret['message'] = 'credit_lot tables not ready';
			return $ret;
		}

		$lot_id = (int)$lot_id;
		if ($lot_id < 1) {
			$ret['message'] = 'invalid lot_id';
			return $ret;
		}

		$upd = sql_query("update credit_lot set
				remain_amount=0,
				lot_status='REFUNDED',
				refunded_at=now(),
				updated_at=now()
			where lot_id='{$lot_id}'
			  and lot_status='ACTIVE'
			  and remain_amount=credit_amount", false);
		$affected = sql_fetch("select row_count() as cnt");
		if ($upd && (int)$affected['cnt'] > 0) {
			$ret['ok'] = true;
			$ret['message'] = 'ok';
			return $ret;
		}

		$current = sql_fetch("select lot_status, remain_amount, credit_amount from credit_lot where lot_id='{$lot_id}'");
		if (is_array($current) && strtoupper(trim((string)$current['lot_status'])) === 'REFUNDED') {
			$ret['ok'] = true;
			$ret['message'] = 'already refunded';
			return $ret;
		}

		$ret['message'] = 'lot refund update failed';
		return $ret;
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
	if(!$member['mb_id']) {
		login_required_modal("로그인 후 이용하세요.", "/bbs/login.php?url=" . urlencode($_SERVER["REQUEST_URI"]));
	}
}

function login_required_modal($msg = "로그인 후 이용하세요.", $loginUrl = "/bbs/login.php")
{
	$msg = '서비스를 이용하시려면 로그인이 필요합니다.';

	$loginUrl = trim((string)$loginUrl);
	if ($loginUrl === '') $loginUrl = "/bbs/login.php";
	$signupUrl = "/bbs/register.php";

	header('Content-Type: text/html; charset=utf-8');
	echo '<!doctype html>';
	echo '<html lang="ko">';
	echo '<head>';
	echo '<meta charset="utf-8">';
	echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
	echo '<title>로그인 안내</title>';
	echo '<style>';
	echo ':root{color-scheme:light;--bg:#f5f7fb;--card:#fff;--text:#1f2937;--muted:#6b7280;--line:#e5e7eb;--primary:#2f5fe3;--primary-dark:#1d4ed8;--shadow:0 24px 60px rgba(15,23,42,.16);}';
	echo '*{box-sizing:border-box}html,body{height:100%}body{margin:0;font-family:Pretendard,Apple SD Gothic Neo,Noto Sans KR,sans-serif;background:linear-gradient(180deg,#f8fbff 0%,#f3f6fb 100%);color:var(--text);}';
	echo '.login-guard{min-height:100%;display:flex;align-items:center;justify-content:center;padding:24px;}';
	echo '.login-guard__overlay{position:fixed;inset:0;background:rgba(15,23,42,.18);backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);}';
	echo '.login-guard__modal{position:relative;max-width:380px;width:90%;background:var(--card);border-radius:20px;padding:40px 36px 32px;box-shadow:var(--shadow);text-align:center;animation:loginGuardFade .18s ease-out;}';
	echo '@keyframes loginGuardFade{from{opacity:0;transform:translateY(10px) scale(.98)}to{opacity:1;transform:translateY(0) scale(1)}}';
	echo '.login-guard__close{position:absolute;top:12px;right:12px;width:28px;height:28px;border:none;border-radius:50%;background:transparent;color:#9ca3af;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;}';
	echo '.login-guard__close svg{width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2.2;stroke-linecap:round}';
	echo '.login-guard__icon{width:56px;height:56px;margin:0 auto 18px;border-radius:999px;background:var(--primary);display:flex;align-items:center;justify-content:center;color:#fff;}';
	echo '.login-guard__icon svg{width:24px;height:24px;fill:#fff}';
	echo '.login-guard__title{margin:0 0 10px;font-size:18px;font-weight:700;letter-spacing:-.02em;}';
	echo '.login-guard__desc{margin:0 0 26px;font-size:14px;line-height:1.5;color:var(--muted);}';
	echo '.login-guard__actions{display:flex;gap:10px;}';
	echo '.login-guard__btn{flex:1;display:inline-flex;align-items:center;justify-content:center;height:48px;border-radius:14px;border:1px solid transparent;font-size:15px;font-weight:600;text-decoration:none;cursor:pointer;transition:.18s ease;}';
	echo '.login-guard__btn--ghost{background:#f3f4f6;color:#4b5563;border-color:#e5e7eb;}';
	echo '.login-guard__btn--ghost:hover{background:#e5e7eb;}';
	echo '.login-guard__btn--primary{background:var(--primary);color:#fff;}';
	echo '.login-guard__btn--primary:hover{background:var(--primary-dark);}';
	echo '@media (max-width:480px){.login-guard{padding:18px}.login-guard__modal{border-radius:20px;padding:30px 20px 22px}.login-guard__title{font-size:19px}.login-guard__desc{font-size:14px}.login-guard__actions{flex-direction:row;}}';
	echo '</style>';
	echo '</head>';
	echo '<body>';
	echo '<div class="login-guard">';
	echo '<div class="login-guard__overlay" aria-hidden="true"></div>';
	echo '<div class="login-guard__modal" role="dialog" aria-modal="true" aria-labelledby="loginGuardTitle">';
	echo '<button type="button" class="login-guard__close" aria-label="닫기" onclick="history.back();"><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>';
	echo '<div class="login-guard__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1s3.1 1.39 3.1 3.1v2z"/></svg></div>';
	echo '<h1 class="login-guard__title" id="loginGuardTitle">로그인이 필요합니다</h1>';
	echo '<p class="login-guard__desc">' . $msg . '</p>';
	echo '<div class="login-guard__actions">';
	echo '<a class="login-guard__btn login-guard__btn--ghost" href="' . htmlspecialchars($signupUrl, ENT_QUOTES, 'UTF-8') . '">회원가입</a>';
	echo '<a class="login-guard__btn login-guard__btn--primary" href="' . htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8') . '">로그인</a>';
	echo '</div>';
	echo '</div>';
	echo '</div>';
	echo '</body>';
	echo '</html>';
	exit;
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
			
			// membership_status / membership_status_history 미사용 정책:
			// 쿠폰 처리 시 해당 테이블 저장은 수행하지 않는다.

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
