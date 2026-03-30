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
	<div class="wideKakao" data-aos="flip-down"><div id="daumRoughmapContainer1710730235460" class="root_daum_roughmap root_daum_roughmap_landing"></div></div>
	<script charset="UTF-8" class="daum_roughmap_loader_script" src="https://ssl.daumcdn.net/dmaps/map_js_init/roughmapLoader.js"></script>
	<script charset="UTF-8">
		new daum.roughmap.Lander({
			"timestamp" : "1710730235460",
			"key" : "2ijy4",
			//"mapWidth" : "640",
			//"mapHeight" : "360"
		}).render();
	</script>

	<div class="location-link mt80 f0">
		<a href="tel:02-517-0123" data-aos="fade-right" data-aos-delay="100">
			<dl>
				<dt><img src="/theme/prix/img/sub/icon_location01.jpg" alt=""></dt>
				<dd>전화 상담<br />02-517-0123</dd>
			</dl>
		</a>
		<a href="/bbs/write.php?bo_table=contact" data-aos="fade-right" data-aos-delay="200">
			<dl>
				<dt><img src="/theme/prix/img/sub/icon_location02.jpg" alt=""></dt>
				<dd>온라인 상담</dd>
			</dl>
		</a>
		<a href="javascript:void(0);" data-aos="fade-right" data-aos-delay="300">
			<dl>
				<dt><img src="/theme/prix/img/sub/icon_location03.jpg" alt=""></dt>
				<dd>카카오톡 상담</dd>
			</dl>
		</a>
		<a href="https://m.booking.naver.com/booking/13/bizes/892470?theme=place&entry=pll&area=pll" target="_blank" data-aos="fade-right" data-aos-delay="400">
			<dl>
				<dt><img src="/theme/prix/img/sub/icon_location04.jpg" alt=""></dt>
				<dd>네이버 예약</dd>
			</dl>
		</a>
	</div>

	<div class="location-info mt100 f0">
		<dl class="juso" data-aos="fade-left" data-aos-delay="100">
			<dt><img src="/theme/prix/img/sub/icon_location_juso.png" style="height:1.9rem;" alt=""><strong>주소</strong></dt>
			<dd>서울시 서초구 강남대로 527 브랜드칸타워 8층</dd>
		</dl>
		<dl class="tel" data-aos="fade-left" data-aos-delay="200">
			<dt><img src="/theme/prix/img/sub/icon_location_tel.png" style="height:1.8rem;" alt=""><strong>Tel</strong></dt>
			<dd>02-517-0123</dd>
		</dl>
		<dl class="time" data-aos="fade-left" data-aos-delay="300">
			<dt><img src="/theme/prix/img/sub/icon_location_time.png" style="height:2.3rem;" alt=""><strong>영업시간</strong></dt>
			<dd>
				<span>평일 09:30 ~ 18:00</span>
				<span>점심시간 13:00 ~ 14:00</span><br />

				<span>토요일 09:30 ~ 15:00</span>
				<span>(일요일 및 공휴일 휴진)</span>
			</dd>
		</dl>
	</div>
<?
include_once(G5_THEME_PATH.'/tail.php');
?>

<script type="text/javascript">
	$(function () {
	});
</script>