<?
include_once("./_common.php");
require_once('libs/INIStdPayUtil.php');

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
			$value = str_replace(array("\r\n", "\r", "\n"), '\n', (string)$value);
			$lines[] = $key . ': ' . $value;
		}
		$lines[] = '---';
		$lines[] = '';
		@file_put_contents($log_file, implode("\n", $lines), FILE_APPEND | LOCK_EX);
	}
}

$payment_log_base = array(
	'event_type' => 'pg_ui_enter',
	'context' => 'credit_pay',
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
?>
<script>
alert("로그인 후 이용하세요.");
self.close();
</script>
<?
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
self.close();
</script>
<?
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
self.close();
</script>
<?
	exit;
}

$is_test_pay = defined('_test_pay') && strtoupper((string)_test_pay) === 'Y';
$stdpay_mid = (defined('_stdpay_mid') && trim((string)_stdpay_mid) !== '') ? trim((string)_stdpay_mid) : ((defined('_mid') && trim((string)_mid) !== '') ? trim((string)_mid) : '');
$stdpay_mid_test = (defined('_stdpay_mid_test') && trim((string)_stdpay_mid_test) !== '') ? trim((string)_stdpay_mid_test) : ($is_test_pay ? $stdpay_mid : '');
$stdpay_signkey = (defined('_stdpay_signkey') && trim((string)_stdpay_signkey) !== '') ? trim((string)_stdpay_signkey) : ((defined('_key') && trim((string)_key) !== '') ? trim((string)_key) : '');
$stdpay_signkey_test = (defined('_stdpay_signkey_test') && trim((string)_stdpay_signkey_test) !== '') ? trim((string)_stdpay_signkey_test) : (($is_test_pay && defined('_key') && trim((string)_key) !== '') ? trim((string)_key) : '');

$mid = trim($pinfo['mid']) ? trim($pinfo['mid']) : $stdpay_mid;
$signKey = ($stdpay_mid_test && $mid === $stdpay_mid_test) ? $stdpay_signkey_test : $stdpay_signkey;
if(!$mid || !$signKey){
macomaex_payment_log_write(array_merge($payment_log_base, array(
	'event_status' => 'fail',
	'order_no' => (string)$orderid,
	'mid' => (string)$mid,
	'reason' => 'invalid_mid_or_signkey',
	'message' => '일반결제 키가 설정되지 않았습니다.'
)));
?>
<script>
alert("일반결제 키가 설정되지 않았습니다.");
self.close();
</script>
<?
	exit;
}

$SignatureUtil = new INIStdPayUtil();
$timestamp = $SignatureUtil->getTimestamp();
$use_chkfake = "Y";
$price = (int)$pinfo['price'];
$orderNumber = $pinfo['order_no'];

$params = array(
	"oid" => $orderNumber,
	"price" => $price,
	"timestamp" => $timestamp
);
$sign = $SignatureUtil->makeSignature($params);

$params = array(
	"oid" => $orderNumber,
	"price" => $price,
	"signKey" => $signKey,
	"timestamp" => $timestamp
);
$verification = $SignatureUtil->makeSignature($params);
$mKey = $SignatureUtil->makeHash($signKey, "sha256");

$payJs = ($stdpay_mid_test && $mid === $stdpay_mid_test)
	? "https://stgstdpay.inicis.com/stdjs/INIStdPay.js"
	: "https://stdpay.inicis.com/stdjs/INIStdPay.js";

$baseUrl = defined('G5_URL') && G5_URL ? rtrim(G5_URL, '/') : 'https://macomaex.com';
if(strpos($baseUrl, 'http://') !== 0 && strpos($baseUrl, 'https://') !== 0){
	$baseUrl = 'https://macomaex.com';
}
$returnUrl = $baseUrl . '/ko/credit_pay_return.php';
$closeUrl = $baseUrl . '/ko/close.php';

macomaex_payment_log_write(array_merge($payment_log_base, array(
	'event_status' => 'success',
	'order_no' => (string)$orderNumber,
	'mid' => (string)$mid,
	'gopaymethod' => 'Card',
	'price' => (string)$price,
	'return_url' => (string)$returnUrl,
	'close_url' => (string)$closeUrl,
	'pg_js' => (string)$payJs,
	'message' => '이니시스 일반결제 UI 호출을 시작했습니다.'
)));
?>
<!DOCTYPE html>
<html lang="ko">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>크레딧 결제</title>
	<script src="<?=$payJs?>" charset="UTF-8"></script>
	<script>
	function paybtn() {
		INIStdPay.pay('SendPayForm_id');
	}
	window.onload = function () {
		paybtn();
	};
	</script>
</head>
<body>
	<form id="SendPayForm_id" method="post">
		<input type="hidden" name="version" value="1.0">
		<input type="hidden" name="gopaymethod" value="Card">
		<input type="hidden" name="mid" value="<?=$mid?>">
		<input type="hidden" name="oid" value="<?=$orderNumber?>">
		<input type="hidden" name="price" value="<?=$price?>">
		<input type="hidden" name="timestamp" value="<?=$timestamp?>">
		<input type="hidden" name="use_chkfake" value="<?=$use_chkfake?>">
		<input type="hidden" name="signature" value="<?=$sign?>">
		<input type="hidden" name="verification" value="<?=$verification?>">
		<input type="hidden" name="mKey" value="<?=$mKey?>">
		<input type="hidden" name="currency" value="WON">
		<input type="hidden" name="goodname" value="<?=htmlspecialchars($pinfo['order_goods'])?>">
		<input type="hidden" name="buyername" value="<?=htmlspecialchars($pinfo['name'])?>">
		<input type="hidden" name="buyertel" value="<?=htmlspecialchars($pinfo['phone'])?>">
		<input type="hidden" name="buyeremail" value="<?=htmlspecialchars($pinfo['email'])?>">
		<input type="hidden" name="returnUrl" value="<?=$returnUrl?>">
		<input type="hidden" name="closeUrl" value="<?=$closeUrl?>">
	</form>
</body>
</html>
