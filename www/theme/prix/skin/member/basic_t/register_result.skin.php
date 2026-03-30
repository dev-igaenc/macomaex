<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/newstyle.css">', 0);
?>

<div class="skin-member terms">
	<dl class="h1">
		<dt>회원가입완료</dt>
	</dl>

	<ul class="member-step three">
		<li>약관동의</li>
		<li>정보입력</li>
		<li>가입완료</li>
	</ul>

	<div class="skin-result">
		<dl class="result-top">
			<dt><strong>회원가입이 완료</strong>되었습니다.</dt>
			<dd>기입 내용을 확인 후 원하시는 서비스를 이용해주세요.</dd>
		</dl>
		<div class="result-info">
			<dl>
				<dt>아이디</dt>
				<dd><?php echo $mb['mb_id'] ?></dd>
			</dl>
			<dl>
				<dt>가입일</dt>
				<dd><?php echo substr($mb['mb_datetime'],0,16); ?></dd>
			</dl>
			<dl>
				<dt>이메일</dt>
				<dd><?php echo $mb['mb_email'] ?></dd>
			</dl>
		</div>
	</div>

	<div class="skin-button">
		<button type="submit" onclick="location.href='<?php echo G5_URL ?>/'">메인 페이지로 이동</button>
	</div>
</div>