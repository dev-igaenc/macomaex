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
	<h2>사업자번호 입력만으로,<br />맞춤형 기업 매칭을 경험하세요.</h2>

	<div search shadow>
		<form action="/ko/matching_list.php" method="POST" id="match">
			<input type="hidden" name="cominfo" id="cominfo">
		</form>
		<input type="text" name="comnum" id="comnum" class="comnum_chk" placeholder="법인 사업자번호를 입력하세요" maxlength="12">
		<button type="submit" id="sub_submit"><img src="/images/icon_search.svg" alt="search"></button>
	</div>
</div></div>

<div class="my-company-area">
	<dl class="h4-title">
		<dt><span><img src="/images/icon_myCompany.png" alt="company"></span><strong>내 회사 정보</strong></dt>
		<dd><a href="javascript:void(0);" btn shadow>데이터제보/오류알리기</a></dd>
	</dl>
	<div class="long-info" shadow>
		<dl>
			<dt>대표자</dt>
			<dd>이영주</dd>
		</dl>
		<dl>
			<dt>사업자번호</dt>
			<dd>186-81-48386</dd>
		</dl>
		<dl>
			<dt>기업유형</dt>
			<dd>일반법인/주식회사</dd>
		</dl>
		<dl>
			<dt>종목</dt>
			<dd>운송장비용 이차전지 제조업</dd>
		</dl>

		<dl>
			<dt>자본</dt>
			<dd>15,456,306,000 원</dd>
		</dl>
		<dl>
			<dt>직전연도</dt>
			<dd>545,256,000 원</dd>
		</dl>
		<dl c2>
			<dt>주소(도로명)</dt>
			<dd>서울특별시 강남구 논현로 34길 28(도곡동, 신성빌딩)</dd>
		</dl>
	</div>

	<dl class="h4-title">
		<dt><span><img src="/images/icon_data.png" alt="data"></span><strong><span class="point">기업데이터 기반</span> 추천거래처</strong></dt>
	</dl>

	<div class="matching-tab" d2>
		<a href="javascript:void(0);" class="active">구매 거래처<!-- <span>23</span> --></a><!-- 활성화 -->
		<a href="javascript:void(0);">판매 거래처<!-- <span>12</span> --></a>
	</div>

	<div class="column-list full">
		<div class="column-list-re" buy>
			<div class="matching-list">
				<div class="matching-item">
					<p class="name"><strong>(주)세인코퍼레이션</strong></p>
					<div class="info-area">
						<dl>
							<dt>소<i></i>재<i></i>지</dt>
							<dd>경기도 고양시 일산동구 운봉동</dd>
						</dl>
						<dl>
							<dt>동종<i></i>업계<i></i>순위</dt>
							<dd>433위</dd>
						</dl>
						<dl>
							<dt>종<i></i>목</dt>
							<dd>기타 산업용 기계 및 장비 도매업</dd>
						</dl>
					</div>
					<div class="btn-detail">
						<a href="javascript:void(0);">상세보기</a>
					</div>
				</div>
				<div class="matching-item">
					<p class="name"><strong>(주)세인코퍼레이션</strong></p>
					<div class="info-area">
						<dl>
							<dt>소<i></i>재<i></i>지</dt>
							<dd>경기도 고양시 일산동구 운봉동</dd>
						</dl>
						<dl>
							<dt>동종<i></i>업계<i></i>순위</dt>
							<dd>433위</dd>
						</dl>
						<dl>
							<dt>종<i></i>목</dt>
							<dd>기타 산업용 기계 및 장비 도매업</dd>
						</dl>
					</div>
					<div class="btn-detail">
						<a href="javascript:void(0);">상세보기</a>
					</div>
				</div>
				<div class="matching-item">
					<p class="name"><strong>(주)세인코퍼레이션</strong></p>
					<div class="info-area">
						<dl>
							<dt>소<i></i>재<i></i>지</dt>
							<dd>경기도 고양시 일산동구 운봉동</dd>
						</dl>
						<dl>
							<dt>동종<i></i>업계<i></i>순위</dt>
							<dd>433위</dd>
						</dl>
						<dl>
							<dt>종<i></i>목</dt>
							<dd>기타 산업용 기계 및 장비 도매업</dd>
						</dl>
					</div>
					<div class="btn-detail">
						<a href="javascript:void(0);">상세보기</a>
					</div>
				</div>
			</div>
		</div>
		<div class="column-list-re">
			<div class="matching-list">
				<div class="matching-item">
					<p class="name"><strong>(주)세인코퍼레이션</strong></p>
					<div class="info-area">
						<dl>
							<dt>소<i></i>재<i></i>지</dt>
							<dd>경기도 고양시 일산동구 운봉동</dd>
						</dl>
						<dl>
							<dt>동종<i></i>업계<i></i>순위</dt>
							<dd>433위</dd>
						</dl>
						<dl>
							<dt>종<i></i>목</dt>
							<dd>기타 산업용 기계 및 장비 도매업</dd>
						</dl>
					</div>
					<div class="btn-detail">
						<a href="javascript:void(0);">상세보기</a>
					</div>
				</div>
				<div class="matching-item">
					<p class="name"><strong>(주)세인코퍼레이션</strong></p>
					<div class="info-area">
						<dl>
							<dt>소<i></i>재<i></i>지</dt>
							<dd>경기도 고양시 일산동구 운봉동</dd>
						</dl>
						<dl>
							<dt>동종<i></i>업계<i></i>순위</dt>
							<dd>433위</dd>
						</dl>
						<dl>
							<dt>종<i></i>목</dt>
							<dd>기타 산업용 기계 및 장비 도매업</dd>
						</dl>
					</div>
					<div class="btn-detail">
						<a href="javascript:void(0);">상세보기</a>
					</div>
				</div>
				<div class="matching-item">
					<p class="name"><strong>(주)세인코퍼레이션</strong></p>
					<div class="info-area">
						<dl>
							<dt>소<i></i>재<i></i>지</dt>
							<dd>경기도 고양시 일산동구 운봉동</dd>
						</dl>
						<dl>
							<dt>동종<i></i>업계<i></i>순위</dt>
							<dd>433위</dd>
						</dl>
						<dl>
							<dt>종<i></i>목</dt>
							<dd>기타 산업용 기계 및 장비 도매업</dd>
						</dl>
					</div>
					<div class="btn-detail">
						<a href="javascript:void(0);">상세보기</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?
include_once(G5_THEME_PATH.'/tail.php');
?>
