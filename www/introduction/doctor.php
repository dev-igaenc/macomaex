<?
include_once("./_common.php");

$board['bo_10']="1"; // 1뎁스
$board['bo_9']="2"; // 2뎁스
//$board['bo_8']="1"; // 3뎁스
//$board['bo_7']="1"; // 4뎁스

//$board['bo_5'] = "브랜드 스토리"; // 1뎁스 제목
//$board['bo_6'] = ""; // 1뎁스 설명
//$board['bo_subject'] = "";  // 2뎁스 제목
//$board['bo_4'] = $config['cf_title']."의 혜택을 확인하세요.";  // 2뎁스 설명

include_once(G5_THEME_PATH.'/head.php'); // data-aos="fade-up" data-aos-delay="100"
?>
	<div class="lastDepth" data-aos="fade-up">
		<a href="javascript:void(0);" class="active">윤철용 대표원장</a>
		<a href="javascript:void(0);">이영주 간호부장</a>
	</div>

	<div id="doctor" class="f0">
		<div class="doctor-list lastDepthConts">
			<div class="thumb" data-aos="fade-up"><img src="/theme/prix/img/sub/img_doctor.png" class="cVar" alt=""><img src="/theme/prix/img/sub/img_doctor_m.png" class="cmVar" alt=""></div>
			<div class="text-area" data-aos="fade-up" data-aos-delay="200">
				<dl class="name">
					<dt><strong>윤철용</strong> 대표원장</dt>
					<dd data-font="Oswald">Yoon Cheol-Young</dd>
				</dl>
				<div class="option">
					<p><strong>DOMESTIC</strong> <strong>국내 (<span>國內</span>)</strong></p>
					<ul class="two">
						<li>고려대학교 의과대학 졸업</li>
						<li>고려대학교 구로병원 비뇨기과 전임강사</li>
						<li>고려대학교 의과대학 의학석사</li>
						<li>분당서울대학병원 비뇨기과 촉탁전문의</li>
						<li>고려대학교 의과대학 의학박사</li>
						<li>고려대학교 구로병원 비뇨기과 임상 부교수</li>
						<li>고려대학교병원 인턴</li>
						<li>신촌세브란스병원 임상연구 조교수</li>
						<li>고려대학교 구로병원 레지던트</li>
						<li>인천나은병원 비뇨기과 과장</li>
						<li>고려대학교 안산병원 비뇨기과 전임의</li>
						<li>성베드로병원 비뇨의학과 원장</li>
						<li>고려대학교 구로병원 비뇨기과 전임의</li>
					</ul>
				</div>

				<div class="option">
					<p><strong>INTERNATIONAL</strong> <strong>국제 (<span>國際</span>)</strong></p>
					<ul>
						<li>Lab of tissue engineering, Children's hospital, Harvard, Boston.   <br class="pVar" />Research fellow (하버드 의과대학)</li>
						<li>Lab of cell cycle and carcinogenesis, NCI/NIH Maryland. <br class="pVar" />연구원 (미국 국립 암 연구소)</li>
						<li>State University of NY, Stony Brook, NY. Senior research <br class="pVar" />scientist (미국 뉴욕주립대학교)</li>
					</ul>
				</div>
			</div>
		</div>
		<div class="doctor-list lastDepthConts">
			<div class="thumb" data-aos="fade-up"><img src="/theme/prix/img/sub/img_doctor02.png" class="cVar" alt=""><img src="/theme/prix/img/sub/img_doctor02_m.png" class="cmVar" alt=""></div>
			<div class="text-area" data-aos="fade-up" data-aos-delay="200">
				<dl class="name">
					<dt><strong>이영주</strong> 간호부장</dt>
					<dd data-font="Oswald">Lee Young-Ju</dd>
				</dl>
				<div class="option">
					<p><strong>CAREER</strong> <strong>경력 (<span>經歷</span>)</strong></p>
					<ul>
						<li>중앙대학교 용산병원 UH | IM (내과)</li>
						<li>나은병원 GH | OS (정형외과)</li>
						<li>바로병원 SH | OS (정형외과)</li>
						<li>나은병원 GH | ICU (중환자실)</li>
						<li>나은병원 GH | URO (비뇨기과)</li>
						<li>자이비뇨의학과병원 SH | URO (비뇨기과)</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
<?
include_once(G5_THEME_PATH.'/tail.php');
?>

<script type="text/javascript">
	$(function () {
		$(".lastDepth>a").click(function(){
			var idx = $(".lastDepth>a").index($(this));
			$(".lastDepth>a").each(function(index){
				if(idx == index){
					$(this).addClass("active");
					$(".lastDepthConts").eq(index).addClass("on")
				}else{
					$(this).removeClass("active");
					$(".lastDepthConts").eq(index).removeClass("on")
				}
			});
		}).filter(":first").click();
	});
</script>