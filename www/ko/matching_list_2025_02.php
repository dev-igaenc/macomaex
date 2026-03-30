<?
include_once("./_common.php");

$board['bo_10']="3"; // 1뎁스
$board['bo_9']="1"; // 2뎁스
//$board['bo_8']="1"; // 3뎁스
//$board['bo_7']="1"; // 4뎁스
// $board['bo_5'] = ""; // 1뎁스 제목
// $board['bo_6'] = ""; // 1뎁스 설명
// $board['bo_subject'] = "";  // 2뎁스 제목
//$board['bo_4'] = $config['cf_title']."의 혜택을 확인하세요.";  // 2뎁스 설명

include_once(G5_THEME_PATH.'/head.php');
?>

<style>
	#pageTop,
	#sVisual{display: none}
</style>
<div id="sub-visual"><div class="inConts">
	<h2>비즈니스 성공의 시작점 매칭.</h2>
	<p>멤버십 업그레이드로 더 많은 가능성을 열어보세요.</p>
	<div btn><a href="javascript:void(0);">업그레이드 하기</a></div>
</div></div>

<div class="matching-tab">
	<a href="my_interested_test.php">관심기업</a>
	<a href="my_matching_r.php" class="active">받은매칭</a><!-- 활성화 -->
	<a href="matching_list_2025_03.php">보낸매칭</a>
	<a href="matching_list_2025_04.php">셀렉티드</a>
</div>

<div class="matching-tab" d2>
	<a href="javascript:void(0);" class="active">전체<span>6</span></a><!-- 활성화 -->
	<a href="javascript:void(0);">매칭중<span>2</span></a>
	<a href="javascript:void(0);">매칭성공 <span>4</span></a>
</div>

<div class="matching-list">
	<div class="matching-item"><!-- 전체 -->
		<p class="name"><strong>(주)세인코퍼레이션 - 전체</strong>
			<button type="button" star><img src="/images/icon_star.svg" alt=""></button>
		</p>
		<div class="info-area">
			<dl>
				<dt>주<i></i>소</dt>
				<dd>경기도 고양시 일산동구 운봉동</dd>
			</dl>
			<dl>
				<dt>종<i></i>목</dt>
				<dd>기타 산업용 기계 및 장비 도매업</dd>
			</dl>
			<dl>
				<dt>담<i></i>당<i></i>자</dt>
				<dd>매칭 성공시 공개</dd>
			</dl>
			<dl>
				<dt>메<i></i>시<i></i>지</dt>
				<dd message>
					<div>
						안녕하세요, ***입니다.<br />
						저희는 귀사의 혁신적인 거래처 발굴 플랫폼
						인 매커맥스에 깊은 관심을 가지고 있으며, 이
						를 통해 상호 협력의 기회를 모색하고자 합니
						다. 매커맥스가 제공하는 맞춤형 거래처 추천
						및 기업 간 매칭 서비스를 통해 저희 비즈니스
						에도 큰 서너지를 창출할 수 있을 것으로 기대
						하고 있습니다.<br />
						이에 따라, 귀사와의 제휴 가능성에 대해 구체
						적으로 논의하고 싶습니다. 협의가 가능하신 일
						정과 방법을 알려주시면, 이에 맞춰 준비하도록
						하겠습니다.<br />
						긍정적인 답변 기다리겠습니다. <br />
						감사합니다.
					</div>

					<button type="button" message-more><span>더보기</span><i><img src="/images/arr_message.png" alt="arrow"></i></button>
				</dd>
			</dl>
		</div>
		<div class="btn-option">
			<a href="javascript:void(0);">상세보기</a>
			<a href="javascript:void(0);">수락하기</a>
			<a href="javascript:void(0);">거절하기</a>
		</div>
	</div>
	<div class="matching-item"><!-- 매칭중 -->
		<p class="name"><strong>(주)세인코퍼레이션 - 매칭중</strong>
			<button type="button" star><img src="/images/icon_star.svg" alt=""></button>
		</p>
		<div class="info-area">
			<dl>
				<dt>주<i></i>소</dt>
				<dd>경기도 고양시 일산동구 운봉동</dd>
			</dl>
			<dl>
				<dt>종<i></i>목</dt>
				<dd>기타 산업용 기계 및 장비 도매업</dd>
			</dl>
			<dl>
				<dt>담<i></i>당<i></i>자</dt>
				<dd>매칭 성공시 공개</dd>
			</dl>
		</div>
		<div class="btn-option">
			<a href="javascript:void(0);">상세보기</a>
			<a href="javascript:void(0);">수락하기</a>
			<a href="javascript:void(0);">거절하기</a>
		</div>
	</div>
	<div class="matching-item"><!-- 매칭성공 -->
		<p class="name"><strong>(주)세인코퍼레이션 - 매칭성공</strong>
			<button type="button" star><img src="/images/icon_star.svg" alt=""></button>
		</p>
		<div class="info-area">
			<dl>
				<dt>주<i></i>소</dt>
				<dd>경기도 고양시 일산동구 운봉동</dd>
			</dl>
			<dl>
				<dt>종<i></i>목</dt>
				<dd>기타 산업용 기계 및 장비 도매업</dd>
			</dl>
			<dl>
				<dt>담<i></i>당<i></i>자</dt>
				<dd>010-0000-0000</dd>
			</dl>
		</div>
		<div class="btn-option">
			<a href="javascript:void(0);">상세보기</a>
			<a href="javascript:void(0);">담당자 연결하기</a>
		</div>
	</div>
	<div class="matching-item"><!-- 전체 -->
		<p class="name"><strong>(주)세인코퍼레이션 - 전체</strong>
			<button type="button" star><img src="/images/icon_star.svg" alt=""></button>
		</p>
		<div class="info-area">
			<dl>
				<dt>주<i></i>소</dt>
				<dd>경기도 고양시 일산동구 운봉동</dd>
			</dl>
			<dl>
				<dt>종<i></i>목</dt>
				<dd>기타 산업용 기계 및 장비 도매업</dd>
			</dl>
			<dl>
				<dt>담<i></i>당<i></i>자</dt>
				<dd>매칭 성공시 공개</dd>
			</dl>
			<dl>
				<dt>메<i></i>시<i></i>지</dt>
				<dd message>
					<div>
						안녕하세요, ***입니다.<br />
						저희는 귀사의 혁신적인 거래처 발굴 플랫폼
						인 매커맥스에 깊은 관심을 가지고 있으며, 이
						를 통해 상호 협력의 기회를 모색하고자 합니
						다. 매커맥스가 제공하는 맞춤형 거래처 추천
					</div>

					<button type="button" message-more><span>더보기</span><i><img src="/images/arr_message.png" alt="arrow"></i></button>
				</dd>
			</dl>
		</div>
		<div class="btn-option">
			<a href="javascript:void(0);">상세보기</a>
			<a href="javascript:void(0);">수락하기</a>
			<a href="javascript:void(0);">거절하기</a>
		</div>
	</div>
</div>

<!-- <link rel="stylesheet" href="/build/masonry/isotope-docs.css"> -->
<script type="text/javascript" src="/build/masonry/isotope-docs.min.js"></script>
<script type="text/javascript">
	var $dynamic = $(".matching-list").isotope({
		filter: '*',
		transitionDuration: '0.4s',
		percentPosition: true,
		//layoutMode: 'fitRows', // 기본
		layoutMode: 'masonry', // 벽돌
		masonry: {
			// use element for option
			columnWidth: '.matching-item',
			//gutter: 80
		}
	});

	$("body").on("click", "button[star]", function(){ // 관심기업
		if(!$(this).hasClass("active")){
			$(this).addClass("active")
		}else{
			$(this).removeClass("active");
		}
	});
	$("body").on("click", "button[message-more]", function(){ // 메시지 더보기
		if(!$(this).closest("dd").hasClass("active")){
			$(this).closest("dd").addClass("active")
			$(this).find("span").text("내용접기")
		}else{
			$(this).closest("dd").removeClass("active");
			$(this).find("span").text("더보기")
		}
		$dynamic.isotope({ filter: '*' });
	});
</script>

<?
include_once(G5_THEME_PATH.'/tail.php');
?>