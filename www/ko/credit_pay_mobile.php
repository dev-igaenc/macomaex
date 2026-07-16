<?php
include_once("./_common.php");

if (!function_exists('macomaex_payment_log_write')) {
	function macomaex_payment_log_write($fields) {
		$log_dir = G5_PATH . '/logs/payments';
		if (!is_dir($log_dir)) {
			@mkdir($log_dir, 0777, true);
		}
		$log_file = $log_dir . '/payments_log_' . date('Ymd') . '.php';
		if (!file_exists($log_file)) {
			@file_put_contents($log_file, "<?php exit; ?>\n\n", FILE_APPEND | LOCK_EX);
		}

		$lines = array();
		$lines[] = '[' . date('Y-m-d H:i:s') . ']';
		foreach ((array)$fields as $key => $value) {
			if ($value === null) $value = '';
			if (is_array($value) || is_object($value)) {
				$value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			}
			$value = str_replace(array("\r\n", "\r", "\n"), '\\n', (string)$value);
			$lines[] = $key . ': ' . $value;
		}
		$lines[] = '---';
		$lines[] = '';
		@file_put_contents($log_file, implode("\n", $lines), FILE_APPEND | LOCK_EX);
	}
}

if (!function_exists('macomaex_current_base_url')) {
	function macomaex_is_local_host($host) {
		$host = strtolower(trim((string)$host));
		if ($host === 'localhost' || $host === '127.0.0.1' || $host === '::1') {
			return true;
		}
		if (preg_match('/^10\./', $host)) return true;
		if (preg_match('/^192\.168\./', $host)) return true;
		if (preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])\./', $host)) return true;
		return false;
	}

	function macomaex_normalize_origin($url) {
		$url = trim((string)$url);
		if ($url === '') return '';

		$parsed = @parse_url($url);
		if (!is_array($parsed) || empty($parsed['host'])) return '';

		$host = strtolower(trim((string)$parsed['host']));
		if ($host === '') return '';
		if (!preg_match('/^[a-z0-9\.\-]+$/', $host) && !filter_var($host, FILTER_VALIDATE_IP)) return '';

		$is_local = macomaex_is_local_host($host);
		$scheme = isset($parsed['scheme']) ? strtolower(trim((string)$parsed['scheme'])) : '';
		if ($scheme !== 'http' && $scheme !== 'https') {
			$scheme = $is_local ? 'http' : 'https';
		}
		if (!$is_local) {
			$scheme = 'https';
		}

		$origin = $scheme . '://' . $host;
		if (isset($parsed['port']) && ctype_digit((string)$parsed['port'])) {
			$origin .= ':' . (int)$parsed['port'];
		}

		return $origin;
	}

	function macomaex_current_base_url() {
		$candidates = array();
		// 콜백 도메인은 요청 헤더(HTTP_HOST)를 신뢰하지 않고, 서버 설정값만 사용한다.
		// 우선순위: MOBILE_PG_BASE_URL(환경별 오버라이드) -> G5_URL(PC 결제와 동일 기준)
		if (function_exists('macomaex_env')) {
			$candidates[] = (string)macomaex_env('MOBILE_PG_BASE_URL', '');
		}
		$candidates[] = defined('G5_URL') ? (string)G5_URL : '';

		foreach ($candidates as $candidate) {
			$origin = macomaex_normalize_origin($candidate);
			if ($origin !== '') {
				return $origin;
			}
		}
		return 'https://macomaex.com';
	}
}

$payment_log_base = array(
	'event_type' => 'mobile_pg_ui_enter',
	'context' => 'credit_pay_mobile',
	'request_uri' => isset($_SERVER['REQUEST_URI']) ? (string)$_SERVER['REQUEST_URI'] : '',
	'request_method' => isset($_SERVER['REQUEST_METHOD']) ? (string)$_SERVER['REQUEST_METHOD'] : '',
	'mb_id' => isset($member['mb_id']) ? (string)$member['mb_id'] : ''
);

if(!$member['mb_id']) {
	macomaex_payment_log_write(array_merge($payment_log_base, array(
		'event_status' => 'fail',
		'reason' => 'not_logged_in',
		'message' => '로그인 후 이용하세요.'
	)));
	if (function_exists('login_required_modal')) {
		login_required_modal(
			'',
			macomaex_login_guard_build_login_url('/ko/plan.php'),
			'credit_payment'
		);
	}
?>
<script>
location.href = "/bbs/login.php?url=/ko/plan.php";
</script>
<?php
	exit;
}

$orderid = isset($_GET['orderid']) ? preg_replace('/[^A-Za-z0-9_\-]/', '', $_GET['orderid']) : '';
if(!$orderid){
	macomaex_payment_log_write(array_merge($payment_log_base, array(
		'event_status' => 'fail',
		'reason' => 'missing_order_no',
		'message' => '주문번호가 없습니다.'
	)));
?>
<script>
alert("주문번호가 없습니다.");
location.href = "/ko/plan.php";
</script>
<?php
	exit;
}

$pinfo = sql_fetch("select * from g5_membership_payment where order_no='{$orderid}' and uid='{$member['mb_id']}'");
if(!$pinfo['order_no']){
	macomaex_payment_log_write(array_merge($payment_log_base, array(
		'event_status' => 'fail',
		'order_no' => (string)$orderid,
		'reason' => 'order_not_found',
		'message' => '결제 주문 정보를 찾을 수 없습니다.'
	)));
?>
<script>
alert("결제 주문 정보를 찾을 수 없습니다.");
location.href = "/ko/plan.php";
</script>
<?php
	exit;
}

if ((string)$pinfo['complete_yn'] === 'Y' && trim((string)$pinfo['tid']) !== '') {
	macomaex_payment_log_write(array_merge($payment_log_base, array(
		'event_status' => 'skip',
		'order_no' => (string)$orderid,
		'reason' => 'already_paid',
		'message' => '이미 결제가 완료된 주문입니다.'
	)));
?>
<script>
location.href = "/ko/my_plan.php";
</script>
<?php
	exit;
}

$is_test_pay = defined('_test_pay') && strtoupper((string)_test_pay) === 'Y';
$stdpay_mid = (defined('_stdpay_mid') && trim((string)_stdpay_mid) !== '') ? trim((string)_stdpay_mid) : ((defined('_mid') && trim((string)_mid) !== '') ? trim((string)_mid) : '');
$stdpay_mid_test = (defined('_stdpay_mid_test') && trim((string)_stdpay_mid_test) !== '') ? trim((string)_stdpay_mid_test) : ($is_test_pay ? $stdpay_mid : '');

$mid = trim((string)$pinfo['mid']) !== '' ? trim((string)$pinfo['mid']) : $stdpay_mid;
if(!$mid){
	macomaex_payment_log_write(array_merge($payment_log_base, array(
		'event_status' => 'fail',
		'order_no' => (string)$orderid,
		'reason' => 'invalid_mid',
		'mid' => (string)$mid,
		'message' => '모바일 일반결제 MID 설정값이 올바르지 않습니다.'
	)));
?>
<script>
alert("모바일 일반결제 MID 설정값이 올바르지 않습니다.");
location.href = "/ko/plan.php";
</script>
<?php
	exit;
}

$price = (int)$pinfo['price'];
if($price < 1){
	macomaex_payment_log_write(array_merge($payment_log_base, array(
		'event_status' => 'fail',
		'order_no' => (string)$orderid,
		'reason' => 'invalid_price',
		'price' => (string)$price,
		'message' => '유효하지 않은 결제 금액입니다.'
	)));
?>
<script>
alert("유효하지 않은 결제 금액입니다.");
location.href = "/ko/plan.php";
</script>
<?php
	exit;
}

// 콜백 URL은 신뢰 가능한 고정값(ENV/G5_DOMAIN)만 사용한다.
// 로컬/스테이징 테스트가 필요하면 .env 의 MOBILE_PG_BASE_URL 을 설정한다.
$baseUrl = macomaex_current_base_url();
$nextUrl = $baseUrl . '/ko/credit_pay_mobile_return.php';
$notiUrl = $nextUrl;
$mobile_hash_key_live = function_exists('macomaex_env') ? trim((string)macomaex_env('INICIS_MOBILE_HASH_KEY_LIVE', '')) : '';
$mobile_hash_key_test = function_exists('macomaex_env') ? trim((string)macomaex_env('INICIS_MOBILE_HASH_KEY_TEST', '')) : '';
$mobile_hash_key = ($stdpay_mid_test && $mid === $stdpay_mid_test) ? $mobile_hash_key_test : $mobile_hash_key_live;
$timestamp = (string)round(microtime(true) * 1000);
$chkfake = '';
$reserved = 'centerCd=Y';
if ($mobile_hash_key !== '') {
	// 모바일 금액위변조 방지: P_AMT + P_OID + P_TIMESTAMP + HashKey 를 SHA512 후 Base64 인코딩
	$chkfake_plain = (string)$price . (string)$orderid . (string)$timestamp . (string)$mobile_hash_key;
	$chkfake = base64_encode(hash('sha512', $chkfake_plain, true));
	$reserved .= '&amt_hash=Y';
}
$payAction = 'https://mobile.inicis.com/smart/payment/';

macomaex_payment_log_write(array_merge($payment_log_base, array(
	'event_status' => 'success',
	'order_no' => (string)$orderid,
	'mid' => (string)$mid,
	'mid_mode' => ($stdpay_mid_test && $mid === $stdpay_mid_test) ? 'test' : 'live',
	'price' => (string)$price,
	'next_url' => (string)$nextUrl,
	'noti_url' => (string)$notiUrl,
	'amt_hash_enabled' => ($chkfake !== '') ? 'Y' : 'N',
	'pay_action' => (string)$payAction,
	'message' => '모바일 일반결제창으로 자동 이동합니다.'
)));
?>
<!DOCTYPE html>
<html lang="ko">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>결제 진행중</title>
</head>
<body>
	<form id="mobileStdPayForm" name="mobileStdPayForm" method="post" action="<?=$payAction?>" accept-charset="EUC-KR">
		<!-- 모바일 일반결제(StdPay-M) 요청 파라미터 -->
		<input type="hidden" name="P_INI_PAYMENT" value="CARD">
		<input type="hidden" name="P_MID" value="<?=htmlspecialchars($mid, ENT_QUOTES)?>">
		<input type="hidden" name="P_OID" value="<?=htmlspecialchars($orderid, ENT_QUOTES)?>">
		<input type="hidden" name="P_AMT" value="<?=htmlspecialchars((string)$price, ENT_QUOTES)?>">
		<input type="hidden" name="P_GOODS" value="<?=htmlspecialchars((string)$pinfo['order_goods'], ENT_QUOTES)?>">
			<input type="hidden" name="P_UNAME" value="<?=htmlspecialchars((string)$pinfo['name'], ENT_QUOTES)?>">
			<input type="hidden" name="P_MOBILE" value="<?=htmlspecialchars((string)$pinfo['phone'], ENT_QUOTES)?>">
			<input type="hidden" name="P_EMAIL" value="<?=htmlspecialchars((string)$pinfo['email'], ENT_QUOTES)?>">
			<?php if ($chkfake !== '') { ?>
			<input type="hidden" name="P_TIMESTAMP" value="<?=htmlspecialchars($timestamp, ENT_QUOTES)?>">
			<input type="hidden" name="P_CHKFAKE" value="<?=htmlspecialchars($chkfake, ENT_QUOTES)?>">
			<?php } ?>
			<input type="hidden" name="P_NEXT_URL" value="<?=htmlspecialchars($nextUrl, ENT_QUOTES)?>">
			<input type="hidden" name="P_NOTI_URL" value="<?=htmlspecialchars($notiUrl, ENT_QUOTES)?>">
			<input type="hidden" name="P_RESERVED" value="<?=htmlspecialchars($reserved, ENT_QUOTES)?>">
		<input type="hidden" name="P_CHARSET" value="utf8">
		<input type="hidden" name="P_LANG" value="ko">
	</form>
	<script>
	document.getElementById('mobileStdPayForm').submit();
	</script>
</body>
</html>
