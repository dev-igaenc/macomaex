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

<div class="company-view" no my><div class="layer-in new">
	<dl class="h4-title">
		<dt><span><img src="/images/icon_myCompany.png" alt="company"></span><strong>내 회사 정보</strong></dt>
		<dd><a href="javascript:void(0);" btn shadow>데이터제보/오류알리기</a></dd>
	</dl>
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
					<dt>표준산업분류(11차)</dt>
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
					<dt>주소(도로명)</dt>
					<dd>서울특별시 강남구 논현로 34길 28<br />(도곡동, 신성빌딩)</dd>
				</dl>
			</div>
		</div>
	</div>

	<!--[s] 비상장사일 때 -->
	<div class="ca-info-my-be">
		<div class="ca-info-my-re" front shadow>
			<p class="title">우리회사 비상장주식 가치</p>
			<div class="ju">
				<a href="javascript:void(0);" class="blurs" data-text="(클릭시공개)" shadow><strong>12,129원/주</strong></a>
				<a href="javascript:void(0);" class="btns">
					<span><img src="/images/icon_search.png" alt="search"></span>
					<strong>평가 결과 보기</strong>
					<span><img src="/images/btn_arr2.png" alt="arr"></span>
				</a>
			</div>
			<p class="text">
				본 수치는 KoData 재무 데이터를 기반으로 산출된 추정값으로, 기업의 고유 특성이나 정보 갱신 주기에 따라 변동 가능성이 있습니다. 정확한 평가가 필요하신 경우, 전문가와의 상담을 권장드립니다.
			</p>
		</div>
		<div class="ca-info-my-re" back shadow>
			<p class="title">우리회사 비상장주식 가치</p>
			<div class="ju">
				<div class="blurs" shadow>
					<strong>12,129원/주</strong>
					<p>직전년도 매출액 100,000,000원</p>
				</div>
				<a href="javascript:void(0);" class="btns">
					<span><img src="/images/icon_search.png" alt="search"></span>
					<strong>평가 결과 보기</strong>
					<span><img src="/images/btn_arr2.png" alt="arr"></span>
				</a>
			</div>
			<p class="tit">정밀한 평가와 전략적 접근이 필요하다면?</p>
			<p class="text">
				메커멕스의 전문 컨설턴트와 상담해보세요. 재무 분석은 물론, 업종 특성, 시장 흐름, 유사 기업 비교까지. 귀사의 가치를 객관적으로 진단해드리고, 미래 방향성을 제안해드립니다.
			</p>
		</div>
	</div>
	<!--[e] 비상장사일 때 -->

	<!--[s] 상장사일 때 -->
	<dl class="h4-title">
		<dt><span><img src="/images/icon_myCompany.png" alt="company"></span><strong>계정 정보</strong></dt>
	</dl>
	<div class="ca-info-my">
		<div class="ca-info-my-re" front shadow>
			<p class="title">우리회사 비상장주식 가치</p>
			<p class="ju"><strong>12,129원/주</strong></p>
			<p class="text">
				매커멕스의 비상장주식 평가는 KoData 재무정보로 산출된
				약식 평가이니,  참고용으로만 봐주세요.  데이터 업데이트
				나 기업 특성에 따라 차이가 있을 수 있으며, 정확한 판단이
				필요하다면 전문가 상담을 추천드립니다.
			</p>
			<a href="javascript:void(0);" class="btn"><img src="/images/btn_arr.png" alt=""></a>
		</div>
		<div class="ca-info-my-re" back shadow>
			<p class="title">우리회사 비상장주식 가치</p>
			<p class="ju"><strong>12,129원/주</strong></p>
			<p class="tit"><span>직전년도 매출액</span> 100,000,000원</p>
			<p class="tit mt70">정밀한 평가와 전략적 접근이 필요하다면?</p>
			<p class="text mt20">
				지금, 메커멕스의 비상장시장 전문 컨설턴트와 상담해
				보세요. 재무 분석은 물론, 업종 특성, 시장 흐름, 유사
				기업 비교까지. 기업가치 평가에 특화된 전문가가 함께
				고민해드립니다.
			</p>
		</div>
	</div>
	<!--[e] 상장사일 때 -->
</div></div>

<script type="text/javascript">
	$("body").on("click", ".ca-info-my .ca-info-my-re[front]", function(){ //
		if(!$(this).closest(".ca-info-my").hasClass("active")){
			$(this).closest(".ca-info-my").addClass("active")
		}else{
			$(this).closest(".ca-info-my").removeClass("active");
		}
	});
	$("body").on("click", ".ca-info-my-be .ca-info-my-re[front]", function(){ //
		if(!$(this).closest(".ca-info-my-be").hasClass("active")){
			$(this).closest(".ca-info-my-be").addClass("active")
		}else{
			$(this).closest(".ca-info-my-be").removeClass("active");
		}
	});
</script>

<?
include_once(G5_THEME_PATH.'/tail.php');
?>
