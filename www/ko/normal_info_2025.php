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

<div class="company-view" no><div class="layer-in new">
	<div class="cv-name">
		<strong>(주)신성반도체</strong>
		<div class="option">
			<button type="button" star><img src="/images/icon_star.svg" alt=""></button>
			<a href="javascript:void(0);" link><img src="/images/icon_link.svg" alt=""></a>
			<a href="javascript:void(0);" request>매칭요청</a>
		</div>
	</div>
	<div class="cv-info-area">
		<div class="cv-info-re normal">
			<p class="cv-tit">기본정보</p>
			<div class="cv-info-conts">
				<dl>
					<dt>대표자</dt>
					<dd><span>이영주</span></dd>
				</dl>
				<dl>
					<dt>설립일자</dt>
					<dd><span>2020-04</span></dd>
				</dl>

				<dl>
					<dt>법인(주민)번호</dt>
					<dd><span>123-45-67890</span></dd>
				</dl>
				<dl>
					<dt>사업자번호</dt>
					<dd><span>186-81-48386</span></dd>
				</dl>

				<dl>
					<dt>고용인원</dt>
					<dd><span>4명</span></dd>
				</dl>
				<dl>
					<dt>기업규모</dt>
					<dd><span>소기업</span></dd>
				</dl>
			</div>
		</div>
		<div class="cv-info-re business">
			<p class="cv-tit">사업/산업</p>
			<div class="cv-info-conts">
				<dl>
					<dt>기업유형</dt>
					<dd>일반법인/주식회사</dd>
				</dl>
				<dl>
					<dt>주요제품</dt>
					<dd>자동차 신품 부품</dd>
				</dl>

				<dl>
					<dt>표준산업분류(9차)</dt>
					<dd>(J58221) 시스템 소프트웨어 개발 및 공급망</dd>
				</dl>
				<dl>
					<dt>표준산업분류</dt>
					<dd>(J58221) 시스템 소프트웨어 개발 및 공급망</dd>
				</dl>

				<dl>
					<dt>직전연도 매출액</dt>
					<dd>567,446,000원</dd>
				</dl>
			</div>
		</div>
		<div class="cv-info-re contact">
			<p class="cv-tit">연락방법/위치</p>
			<div class="cv-info-conts" colums>
				<dl>
					<dt>전화번호</dt>
					<dd><a href="tel:02-545-8485">02-545-8485</a></dd>
				</dl>
				<dl>
					<dt>이메일</dt>
					<dd><a href="mailto:youngju@sinsung.com" u>youngju@sinsung.com</a></dd>
				</dl>
				<dl>
					<dt>홈페이지</dt>
					<dd><a href="http://www.sinsungsemi.co.kr" target="_blank" u>http://www.sinsungsemi.co.kr</a></dd>
				</dl>
				<dl>
					<dt>주소(도로명)</dt>
					<dd>서울특별시 강남구 논현로 34길 28<br />(도곡동, 신성빌딩)</dd>
				</dl>

				<div class="cv-character">
					<span>좋은 거래처와<br />매칭되기를<br /><strong class="point">매커멕스</strong>가 함께<br />응원할게요</span>
					<p><img src="/images/icon_character.png" alt=""></p>
				</div>
			</div>
		</div>
	</div>

	<div class="cv-info-step">
		<div class="step-left">
			<p class="titles">직접 연결 방법</p>
			<div class="step-re">
				<p class="step"><strong>STEP 1</strong><span><img src="/images/arr_message_r.png" alt=""></span></p>
				<p class="icon"><img src="/images/icon_connection.png" alt=""></p>
				<p class="tit">직접 연결</p>
				<div class="conts tel">
					<span class="point">대표 전화번호</span>로 연결됩니다.
					<p class="btn"><a href="tel:02-545-8454">02-545-8454</a></p>
				</div>
			</div>
		</div>
		<div class="step-right">
			<p class="titles">매칭 프로세스</p>
			<div class="step-right-in">
				<div class="step-re">
					<p class="step"><strong>STEP 1</strong><span><img src="/images/arr_message_r.png" alt=""></span></p>
					<p class="icon"><img src="/images/icon_request.png" alt=""></p>
					<p class="tit">매칭 요청</p>
					<div class="conts">
						오른쪽 상단의 <span class="point">“매칭요청” 버튼을 클릭</span>
						하시면, 당사 직원이 피매칭 기업 담당
						자에게 직접 연락하여 문의 사항을 확인
						할 수 있도록 도움을 드립니다.
					</div>
				</div>
				<div class="step-re">
					<p class="step"><strong>STEP 2</strong><span><img src="/images/arr_message_r.png" alt=""></span></p>
					<p class="icon"><img src="/images/icon_accept.png" alt=""></p>
					<p class="tit">매칭 수락</p>
					<div class="conts">
						피매칭 기업이 매칭을 수락할 경우,<br />
						<span class="point">담당자의 직통 연락처</span>가 공개됩니다.
					</div>
				</div>
			</div>
		</div>
	</div>
</div></div>

<script type="text/javascript">
	$("body").on("click", "button[star]", function(){ // 관심기업
		if(!$(this).hasClass("active")){
			$(this).addClass("active")
		}else{
			$(this).removeClass("active");
		}
	});
</script>

<?
include_once(G5_THEME_PATH.'/tail.php');
?>