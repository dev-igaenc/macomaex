<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);

$reg_prev_url = trim((string)get_session('ss_reg_prev_url'));
if ($reg_prev_url === '') $reg_prev_url = G5_URL;
$trade_request_return_url = trim((string)get_session('ss_reg_trade_request_return_url'));

$is_trade_request_return = false;
if ($trade_request_return_url !== '') {
	$parsed = @parse_url($trade_request_return_url);
	$path = isset($parsed['path']) ? (string)$parsed['path'] : '';
	$query = isset($parsed['query']) ? (string)$parsed['query'] : '';
	parse_str($query, $query_params);
	$trade_login_flag = isset($query_params['trade_request_login']) ? (string)$query_params['trade_request_login'] : '';
	$trade_request_paths = array(
		'/companies/detail',
		'/recommendations/detail',
		'/ko/company_detail.php',
		'/ko/company_detail_ai.php',
	);
	if (in_array($path, $trade_request_paths, true) && $trade_login_flag === '1') {
		$is_trade_request_return = true;
		$reg_prev_url = $trade_request_return_url;
	}
}
?>

<!-- 회원가입결과 시작 { -->
<div id="reg_result" class="mbskin kookMin newLogin">
	<h1><img src="<?php echo G5_IMG_URL ?>/logo.svg" alt="<?php echo $config['cf_title']; ?>"></h1>
	<dl class="topTit">
		<dt><strong><?php echo get_text($mb['mb_name']); ?></strong>님의 회원가입을 축하합니다.</dt>
		<dd>
			<?php if (is_use_email_certify()) {  ?>
			<p class="result_txt">
				회원 가입 시 입력하신 이메일 주소로 인증메일이 발송되었습니다.<br>
				발송된 인증메일을 확인하신 후 인증처리를 하시면 사이트를 원활하게 이용하실 수 있습니다.
			</p>
			<div id="result_email">
				<span>아이디</span>
				<strong><?php echo $mb['mb_id'] ?></strong><br>
				<span>이메일 주소</span>
				<strong><?php echo $mb['mb_email'] ?></strong>
			</div>
			<p>
				이메일 주소를 잘못 입력하셨다면, 사이트 관리자에게 문의해주시기 바랍니다.
			</p>
			<?php }  ?>

			<p class="result_txt">
				회원님의 비밀번호는 아무도 알 수 없는 암호화 코드로 저장되므로 안심하셔도 좋습니다.<br>
				아이디, 비밀번호 분실시에는 회원가입시 입력하신 이메일 주소를 이용하여 찾을 수 있습니다.
			</p>

			<p class="result_txt">
				회원 탈퇴는 언제든지 가능하며 일정기간이 지난 후, 회원님의 정보는 삭제하고 있습니다.<br>
				감사합니다.
			</p>
		</dd>
	</dl>

	<fieldset id="login_fs">
		<div class="btn_box">
			<?php if ($is_trade_request_return) { ?>
			<button type="button" id="btn_submit" accesskey="s" class="btn_submit" onclick="location.href='<?php echo htmlspecialchars($reg_prev_url, ENT_QUOTES); ?>'">추천기업 페이지로</button>
			<?php } else { ?>
			<button type="button" id="btn_submit" accesskey="s" class="btn_submit" onclick="location.href='<?php echo G5_URL ?>/'">메인으로</button>
			<?php } ?>
		</div>
	</fieldset>
</div>
<!-- } 회원가입결과 끝 -->

<style>
#reg_result #login_fs .btn_box{display:flex;justify-content:space-between;}
#reg_result #login_fs .btn_box .btn_submit{width:100%;margin:40px 0 0 !important;}
</style>
