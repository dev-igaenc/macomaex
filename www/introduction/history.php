<?
include_once("./_common.php");

$board['bo_10']="1"; // 1뎁스
$board['bo_9']="4"; // 2뎁스
//$board['bo_8']="1"; // 3뎁스
//$board['bo_7']="1"; // 4뎁스

//$board['bo_5'] = "브랜드 스토리"; // 1뎁스 제목
//$board['bo_6'] = ""; // 1뎁스 설명
//$board['bo_subject'] = "";  // 2뎁스 제목
//$board['bo_4'] = $config['cf_title']."의 혜택을 확인하세요.";  // 2뎁스 설명

include_once(G5_THEME_PATH.'/head.php'); // data-aos="fade-up" data-aos-delay="100"
?>
	<div class="history-visual" data-aos="fade-up">
		<p class="icon"><img src="/theme/prix/img/sub/emblem_history.png" style="width:11.8rem;" alt=""></p>
		<p class="txt">칸비뇨기과가 환자의 건강을 위해 <br class="mVar"/>끊임없이 노력해  걸어온 길입니다.</p>
	</div>
	<ul id="history" class="f0">
		<li class="history-re"><!-- 년 반복 -->
			<div class="text-area" data-aos="fade-up">
				<p class="year">2024</p>
				<dl class="month"><!-- 월 반복 -->
					<dt>04월</dt>
					<dd>홈페이지 리뉴어 오픈</dd>
				</dl>
			</div>
			<div class="thumb" data-aos="fade-up" data-aos-delay="200"><img src="/theme/prix/img/sub/img_drug05.jpg" alt=""></div>
		</li>
		<li class="history-re"><!-- 년 반복 -->
			<div class="text-area" data-aos="fade-up">
				<p class="year">2023</p>
				<dl class="month">
					<dt>12월</dt>
					<dd>고려대학교 안산병원 비뇨기과 전임의</dd>
				</dl>
				<dl class="month">
					<dt>10월</dt>
					<dd>고려대학교 안산병원 비뇨기과 전임의</dd>
				</dl>
				<dl class="month">
					<dt>02월</dt>
					<dd>고려대학교 안산병원 비뇨기과 전임의 내용이 긴 경우를 고려하여..................</dd>
				</dl>
			</div>
			<div class="thumb" data-aos="fade-up" data-aos-delay="200"><img src="/theme/prix/img/sub/img_drug05.jpg" alt=""></div>
		</li>
	</ul>
<?
include_once(G5_THEME_PATH.'/tail.php');
?>

<script type="text/javascript">
	$(function () {
	});
</script>