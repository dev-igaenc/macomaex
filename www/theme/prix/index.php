<?php
if (!defined('_INDEX_')) define('_INDEX_', true);
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
if (G5_IS_MOBILE) {
    include_once(G5_THEME_MOBILE_PATH . '/index.php');
    return;
}

include_once(G5_THEME_PATH . '/head.php');

?>

<link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"
/>

<style>
#mLiveStats{padding:16px 0 6px;background:#fff}
#mLiveStats .mLiveStats_head{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px}
#mLiveStats .mLiveStats_title{font-size:16px;font-weight:700;color:#222}
#mLiveStats .mLiveStats_grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}
#mLiveStats .mLiveStats_item{padding:8px 6px 12px;border:1px solid #e8e8e8;border-radius:8px;background:#fff;text-align:center;overflow:visible}
#mLiveStats .mLiveStats_label{font-size:11px;color:#777;font-weight:700}
#mLiveStats .mLiveStats_value{font-size:18px;font-weight:700;color:#222;margin-top:2px}
#mLiveStats .mLiveStats_unit{font-size:11px;color:#666;margin-left:4px}
#mLiveStats .mLiveStats_ringWrap{position:relative;width:104px;height:104px;margin:6px auto 6px}
#mLiveStats .mLiveStats_ring{width:104px;height:104px;transform:rotate(-90deg);overflow:visible}
#mLiveStats .mLiveStats_ringBg{fill:none;stroke:#e9eef5;stroke-width:8}
#mLiveStats .mLiveStats_ringValue{fill:none;stroke:#4a90e2;stroke-width:8;stroke-linecap:round;transition:stroke-dasharray 1s ease-out}
#mLiveStats .mLiveStats_ringText{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:700;color:#222}
#mLiveStats .mLiveStats_desc{font-size:11px;color:#666;margin-top:4px}
#mLiveStats .mLiveStats_note{margin-top:6px;font-size:10px;color:#888;text-align:center}
#mLiveStats .mLiveStats_clock{display:inline-flex;align-items:center;gap:6px;font-size:14px;color:#222;font-weight:700}
#mLiveStats .mLiveStats_clock .clock-dot{width:6px;height:6px;border-radius:50%;background:#2ecc71;display:inline-block;animation:clockPulse 1s ease-in-out infinite}
@keyframes clockPulse{0%{opacity:.3}50%{opacity:1}100%{opacity:.3}}
@media (max-width: 768px){
    #mLiveStats .mLiveStats_head{flex-direction:column;align-items:flex-start;gap:6px}
    #mLiveStats .mLiveStats_grid{grid-template-columns:1fr;gap:8px}
    #mLiveStats .mLiveStats_clock{font-size:13px}
}
</style>

<div id="mVisual">
    <div class="inner">
        <div class="mVisual_contact">
            <h4 class="mTitle mini cfff" data-aos="fade-up" data-aos-delay="100" data-aos-duration= "1000">AI 데이터 기반 거래처 매칭 </h4>
            <h4 class="mTitle cfff" data-aos="fade-up" data-aos-delay="300" data-aos-duration= "1000">사업자번호 입력시 동종업계 <br>상위기업의 핵심거래처를 찾아드립니다</h4>

            <div class="mVisual_search bxShadow" data-aos="fade-up" data-aos-delay="500" data-aos-duration= "1000">
				<form action="/ko/matching_list.php" method="POST" id="match">
				<input type="hidden" name="cominfo" id="cominfo">
                <input type="text" name="comnum" id="comnum" placeholder="사업자번호를 입력해주세요" class="comnum_chk" maxlength="12">
                <button type="submit" id="main_submit"></button>
				</form>
            </div>
        </div>
    </div>
</div>

<div id="mLiveStats" class="mSection">
    <div class="inner">
        <div class="mLiveStats_head">
            <div class="mLiveStats_title">매커멕스 실시간 매칭현황</div>
            <div class="mLiveStats_clock"><span class="clock-dot"></span><span id="live-clock"></span></div>
        </div>
        <div class="mLiveStats_grid">
            <div class="mLiveStats_item" data-aos="fade-up" data-aos-delay="50" data-aos-duration= "1000">
                <div class="mLiveStats_label">매칭 요청률</div>
                <div class="mLiveStats_ringWrap">
                    <svg class="mLiveStats_ring" viewBox="0 0 40 40">
                        <circle class="mLiveStats_ringBg" cx="20" cy="20" r="16"></circle>
                        <circle class="mLiveStats_ringValue" id="ring-request" cx="20" cy="20" r="16" stroke-dasharray="0 101"></circle>
                    </svg>
                    <div class="mLiveStats_ringText" id="percent-request">0%</div>
                </div>
                <div class="mLiveStats_desc">전체 회원 중 요청 비율</div>
            </div>
            <div class="mLiveStats_item" data-aos="fade-up" data-aos-delay="100" data-aos-duration= "1000">
                <div class="mLiveStats_label">매칭 수락률</div>
                <div class="mLiveStats_ringWrap">
                    <svg class="mLiveStats_ring" viewBox="0 0 40 40">
                        <circle class="mLiveStats_ringBg" cx="20" cy="20" r="16"></circle>
                        <circle class="mLiveStats_ringValue" id="ring-accept" cx="20" cy="20" r="16" stroke-dasharray="0 101"></circle>
                    </svg>
                    <div class="mLiveStats_ringText" id="percent-accept">0%</div>
                </div>
                <div class="mLiveStats_desc">요청 건수 중 수락 비율</div>
            </div>
        </div>
        <p class="mLiveStats_note">매일 오후 6시 업데이트</p>
    </div>
</div>

<div id="mHow" class="mSection bgGray">
    <div class="inner">
        <h4 class="mTitle mini cPoint fwb mon tac" data-aos="fade-up" data-aos-delay="100" data-aos-duration= "1000">HOW TO USE</h4>
        <h4 class="mTitle tac" data-aos="fade-up" data-aos-delay="300" data-aos-duration= "1000">매커멕스는 아래와 같은 <br>과정으로 이용할 수 있어요!</h4>

        <ul class="mHow_step">
            <li class="bxShadow" data-aos="fade-up" data-aos-delay="100" data-aos-duration= "1000">
                <p class="num">STEP 01</p>
                <div class="icon"><img src="/theme/prix/img/main/mHow_icon_01.png" alt=""></div>
                <div class="textArea">
                    <h4>사업자 번호 입력</h4>
                    <p>내 사업자 번호를 <br>넣으세요</p>
                </div>
            </li>
            <li class="bxShadow" data-aos="fade-up" data-aos-delay="100" data-aos-duration= "1000">
                <p class="num">STEP 02</p>
                <div class="icon"><img src="/theme/prix/img/main/mHow_icon_02.png" alt=""></div>
                <div class="textArea">
                    <h4>추천 거래처 결과 확인</h4>
                    <p>동종 업계 다른 기업들과 <br class="pVar">거래 중인 믿을 수 있는 거래처를 리스팅 해드려요</p>
                </div>
            </li>
            <li class="bxShadow" data-aos="fade-up" data-aos-delay="100" data-aos-duration= "1000">
                <p class="num">STEP 03</p>
                <div class="icon"><img src="/theme/prix/img/main/mHow_icon_03.png" alt=""></div>
                <div class="textArea">
                    <h4>직접 컨택 및 매칭 요청</h4>
                    <p>마음에 드는 거래처에 <br class="pVar">직접 연락하거나, <br class="pVar">매칭을 요청하세요</p>
                </div>
            </li>
            <li class="bxShadow" data-aos="fade-up" data-aos-delay="100" data-aos-duration= "1000">
                <p class="num">STEP 04</p>
                <div class="icon"><img src="/theme/prix/img/main/mHow_icon_04.png" alt=""></div>
                <div class="textArea">
                    <h4>담당자 연락</h4>
                    <p>상대가 요청을 수락 시 <br class="pVar">공개되는 담당자 연락처로 <br class="pVar">컨택 및 거래를 <br class="pVar">진행할 수 있어요</p>
                </div>
            </li>
        </ul>
    </div>
</div>

<div id="mWe" class="mSection">
    <div class="inner">
        <h4 class="mTitle mini cPoint fwb mon tac" data-aos="fade-up" data-aos-delay="100" data-aos-duration= "1000">WE RECOMMEND</h4>
        <h4 class="mTitle tac" data-aos="fade-up" data-aos-delay="300" data-aos-duration= "1000">매커멕스는 다음과 같은 <br>기업에 추천합니다. </h4>

        <ul class="mWe_list">
            <li data-aos="fade-up" data-aos-delay="100" data-aos-duration= "1000">
                <div class="thumb"><img src="/theme/prix/img/main/mRecommend_01.png" alt=""></div>
                <div class="text">신규 유통 거래처가 <br class="mmVar">필요한 제조사</div>
            </li>
            <li data-aos="fade-up" data-aos-delay="100" data-aos-duration= "1000">
                <div class="thumb"><img src="/theme/prix/img/main/mRecommend_02.png" alt=""></div>
                <div class="text">좋은 제품을 유통하고 <br class="mmVar">싶은 유통사</div>
            </li>
            <li data-aos="fade-up" data-aos-delay="100" data-aos-duration= "1000">
                <div class="thumb"><img src="/theme/prix/img/main/mRecommend_03.png" alt=""></div>
                <div class="text">새로운 제품을 제조사와 함께 <br class="mmVar"> 만들고 싶어하는 사업가</div>
            </li>
        </ul>
    </div>
</div>

<div id="mDeals" class="mSection bgGray">
    <div class="inner">
        <div class="mFlexLayout">
            <div class="textArea">
                <p class="mTitle mini cPoint fwb" data-aos="fade-up" data-aos-delay="100" data-aos-duration= "1000">RECOMMEND DEALS</p>
                <p class="mTitle nMg" data-aos="fade-up" data-aos-delay="300" data-aos-duration= "1000">추천 거래처 <br>한번에</p>
                <p class="mTitle mini mgt" data-aos="fade-up" data-aos-delay="500" data-aos-duration= "1000">추천 거래처 리스트 제공</p>
            </div>
            <div class="thumbArea" data-aos="fade" data-aos-delay="500" data-aos-duration= "1000"><img src="/theme/prix/img/main/mImg_pc_01.png" alt=""></div>
        </div>
    </div>
</div>

<div id="mValue" class="mSection">
    <div class="inner">
        <div class="mFlexLayout even">
            <div class="textArea">
                <p class="mTitle mini cPoint fwb" data-aos="fade-up" data-aos-delay="100" data-aos-duration= "1000">CHECK THE VALUE</p>
                <p class="mTitle nMg" data-aos="fade-up" data-aos-delay="300" data-aos-duration= "1000">우리 회사 비상장 <br>주식 가치 확인</p>
                <p class="mTitle mini mgt" data-aos="fade-up" data-aos-delay="500" data-aos-duration= "1000">사업자 번호 입력하기만 하면!</p>
            </div>
            <div class="thumbArea" data-aos="fade" data-aos-delay="500" data-aos-duration= "1000"><img src="/theme/prix/img/main/mImg_pc_02.png" alt=""></div>
        </div>
    </div>
</div>

<div id="mConsulting" class="mSection bgGray">
    <div class="inner">
        <div class="mFlexLayout">
            <div class="textArea">
                <p class="mTitle mini cPoint fwb" data-aos="fade-up" data-aos-delay="100" data-aos-duration= "1000">EXPERT CONSULTING</p>
                <p class="mTitle nMg" data-aos="fade-up" data-aos-delay="300" data-aos-duration= "1000">전문가 컨설팅을 <br>통한 기업 성장</p>
                <p class="mTitle mini mgt" data-aos="fade-up" data-aos-delay="500" data-aos-duration= "1000">법률, 특허, 회계 등 전문가 컨설팅</p>
            </div>
            <div class="thumbArea" data-aos="fade" data-aos-delay="500" data-aos-duration= "1000"><img src="/theme/prix/img/main/mImg_pc_03.png" alt=""></div>
        </div>
    </div>
</div>

<!-- <div id="mCorporate" class="mSection">
    <div class="inner">
        <div class="textArea">
            <div class="">
                <p class="mTitle mini cPoint fwb" data-aos="fade-up" data-aos-delay="100" data-aos-duration= "1000">CORPORATE PR CENTER</p>
                <p class="mTitle nMg" data-aos="fade-up" data-aos-delay="300" data-aos-duration= "1000">매커멕스를 통해 <br>기업을 홍보하고 <br>있습니다.</p>
            </div>
        </div>
        <div class="slideArea" data-aos="fade" data-aos-delay="500" data-aos-duration= "1000">
            <div class="swiper">
                <div class="swiper-wrapper">
                    <div class="swiper-slide"></div>
                    <div class="swiper-slide"></div>
                    <div class="swiper-slide"></div>
                </div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div>
        </div>
        <div class="scrollbarArea" data-aos="fade-up" data-aos-delay="500" data-aos-duration= "1000">
            <div class="swiper-scrollbar"></div>
        </div>
    </div>
</div> -->

<div id="mCorporate" class="mSection">
    <div class="inner">
        <div class="mFlexLayout even">
            <div class="textArea">
                <p class="mTitle mini cPoint fwb" data-aos="fade-up" data-aos-delay="100" data-aos-duration= "1000">CORPORATE PR CENTER</p>
                <p class="mTitle nMg" data-aos="fade-up" data-aos-delay="300" data-aos-duration= "1000">유튜브 쇼츠를 <br>통한 기업 홍보</p>
                <p class="mTitle mini mgt" data-aos="fade-up" data-aos-delay="500" data-aos-duration= "1000">제품 소개 및 기업 홍보를 쇼츠로!</p>
            </div>
            <div class="thumbArea" data-aos="fade" data-aos-delay="500" data-aos-duration= "1000"><img src="/theme/prix/img/main/mImg_pc_04.png" alt=""></div>
        </div>
    </div>
</div>

<div id="mBusiness" class="mSection bgGray">
    <div class="inner">
        <h4 class="mTitle mini cPoint fwb tac" data-aos="fade-up" data-aos-delay="100" data-aos-duration= "1000">CREATING BUSINESS</h4>
        <h4 class="mTitle tac" data-aos="fade-up" data-aos-delay="300" data-aos-duration= "1000">여러 기업들이 매커멕스를 통해 <br class="pVar">새로운 비즈니스의 창출 기회를 만들고 있어요!</h4>

		<div class="slideArea" data-aos="fade-up" data-aos-delay="500" data-aos-duration= "1000">
			<div class="swiper">
				<div class="swiper-wrapper">
					<div class="swiper-slide">
						<div class="profile_top">
							<p class="company">*** 주식회사</p>
							<p class="team">헤어/미용 전문 쇼핑몰<br />영업팀</p>
							<div class="thumb"><img src="/img/icon/profile01.jpg" alt=""></div>
							<p class="review">
								약 5,000개 정도의 클렌징 패드가 급하게 필요했는데 매커멕스 플랫폼 안에서 제조 기업을 만나 빠르게 담당자와 소통하여 원하는대로 주문할 수 있었습니다. <br />
								그 외에 추가적으로 스킨케어 프라이빗 라벨도 함께 진행하게 되었고 앞으로 많은 부분 함께 할 수 있을 것이라 여겨집니다.
							</p>
						</div>
						<div class="profile_bottom">
							<p class="contact_company">건강 및 미용</p>
						</div>
					</div>
					<div class="swiper-slide">
						<div class="profile_top">
							<p class="company">*** 주식회사</p>
							<p class="team">용접기기 제조업체<br />생산관리팀</p>
							<div class="thumb"><img src="/img/icon/profile02.jpg" alt=""></div>
							<p class="review">
								용접기 제조업체로서 세밀한 디지털 컨트롤러를 찾고 있었습니다. 매커멕스 플랫폼에서 검색하니 다양한 제조업체들을 확인할 수 있었고 그 중 저희 기업과 잘 맞는 기업을 컨택하여 기한에 맞춰 받아볼 수 있었습니다.
								쉽고 빠르며 다양하다는 점이 좋고 담당자님과 빠르게 컨택할 수 있다는 점이 좋았습니다.
							</p>
						</div>
						<div class="profile_bottom">
							<p class="contact_company">기계, 산업 부품 및 기구</p>
						</div>
					</div>
					<div class="swiper-slide">
						<div class="profile_top">
							<p class="company">*** 주식회사</p>
							<p class="team">해외 유통<br />영업팀</p>
							<div class="thumb"><img src="/img/icon/profile03.jpg" alt=""></div>
							<p class="review">
								스낵제품들 해외에 유통하려고 찾고 있었는데 검색 후 담당자와 소통하니 카탈로그 및 견적서를 빠르게 받아볼 수 있었습니다. 빠른 소통으로 수량/기간 맞춰 유통 준비중에 있습니다. 추가적으로 또 이용할 예정입니다.
							</p>
						</div>
						<div class="profile_bottom">
							<p class="contact_company">농업 및 식품</p>
						</div>
					</div>
					<div class="swiper-slide">
						<div class="profile_top">
							<p class="company">*** 주식회사</p>
							<p class="team">의류 제조업체<br />재료관리팀</p>
							<div class="thumb"><img src="/img/icon/profile04.jpg" alt=""></div>
							<p class="review">
								원재료 공급업체 중에서도 비싼 원단보다 가성비 좋은 원단/원사 공급 업체를 찾던 중 매커멕스 플랫폼에서 딱 맞는 기업을 찾을 수 있었습니다. 찾는데 애먹고 있었는데 많은 업체가 있어서 여러 업체와 소통하고 거래할 수 있었습니다.
							</p>
						</div>
						<div class="profile_bottom">
							<p class="contact_company">의류, 직물 및 액세서리</p>
						</div>
					</div>
					<div class="swiper-slide">
						<div class="profile_top">
							<p class="company">*** 주식회사</p>
							<p class="team">룸스프레이/디퓨저 제품 유통업체<br />마케팅팀</p>
							<div class="thumb"><img src="/img/icon/profile05.jpg" alt=""></div>
							<p class="review">
								에어로졸 룸스프레이 용도로 에센셜 오일만 찾고 있던 중 매커멕스 플랫폼 통해 다양한 기업에 요청서를 넣어볼 수 있었습니다. <br />매칭 요청시에 원하는 조건과 카탈로그 및 견적서 등 자료 요청했을때 빠르게 받아보고 확인할 수 있는 점이 좋았습니다.
							</p>
						</div>
						<div class="profile_bottom">
							<p class="contact_company">기계, 화학, 고무 및 플라스틱</p>
						</div>
					</div>
					<div class="swiper-slide">
						<div class="profile_top">
							<p class="company">*** 주식회사</p>
							<p class="team">인덕션 유통업체<br />해외유통팀</p>
							<div class="thumb"><img src="/img/icon/profile06.jpg" alt=""></div>
							<p class="review">
								해외에 급히 많은 양의 인덕션 쿠커를 납품해야 했는데, 매커멕스에서 인덕션쿠커 제조하는 기업을 찾아 컨택했을때 대부분의 기업이 인증기업이라 신뢰도 높은 거래를 할 수 있었습니다.
							</p>
						</div>
						<div class="profile_bottom">
							<p class="contact_company">전자제품</p>
						</div>
					</div>
				</div>
				<div class="swiper-button-prev"></div>
				<div class="swiper-button-next"></div>
				<div class="swiper-scrollbar"></div>
			</div>
		</div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<script>
(function(){
    var endpoint = '/live_stats.php';
    var intervalMs = 5 * 60 * 1000;

    function updateRing(el, percent){
        if (!el) return;
        var clamped = Math.max(0, Math.min(100, percent));
        var radius = 16;
        var circumference = 2 * Math.PI * radius;
        
        // 초기 상태 설정 (0)
        el.setAttribute('stroke-dasharray', '0 ' + circumference);
        
        // 브라우저가 초기 상태를 인식하도록 지연 후 업데이트
        setTimeout(function(){
            var filled = (clamped / 100) * circumference;
            el.setAttribute('stroke-dasharray', filled + ' ' + (circumference - filled));
        }, 100);
    }

    function render(state){
        var requestRate = 0;
        var acceptRate = 0;
        if (state.rates && typeof state.rates.request_rate === 'number') {
            requestRate = state.rates.request_rate;
        }
        if (state.rates && typeof state.rates.accept_rate === 'number') {
            acceptRate = state.rates.accept_rate;
        }

        var requestDisplay = Number(requestRate || 0);
        var acceptDisplay = Number(acceptRate || 0);
        
        var requestText = document.getElementById('percent-request');
        var acceptText = document.getElementById('percent-accept');
        
        if (requestText) animate_value(requestText, 0, requestDisplay, 1000, function(obj, val, progress) {
            obj.textContent = (progress * requestDisplay).toFixed(2) + "%";
        });
        if (acceptText) animate_value(acceptText, 0, acceptDisplay, 1000, function(obj, val, progress) {
            obj.textContent = (progress * acceptDisplay).toFixed(2) + "%";
        });
        
        updateRing(document.getElementById('ring-request'), requestDisplay);
        updateRing(document.getElementById('ring-accept'), acceptDisplay);
    }

    function fetchStats(){
        fetch(endpoint + '?t=' + Date.now())
            .then(function(res){ return res.json(); })
            .then(function(data){
                if (data && data.rates) {
                    render(data);
                }
            })
            .catch(function(){});
    }

    fetchStats();
    setInterval(fetchStats, intervalMs);
})();
</script>

<script>
(function(){
    var clockEl = document.getElementById('live-clock');
    if (!clockEl || !window.Intl || !Intl.DateTimeFormat) return;
    var formatter = new Intl.DateTimeFormat('ko-KR', {
        timeZone: 'Asia/Seoul',
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
    function tick(){
        clockEl.textContent = formatter.format(new Date());
    }
    tick();
    setInterval(tick, 1000);
})();
</script>

<script>
$("#main_submit").click(function(){
	event.preventDefault();
	$(".comnum_chk").blur();
	comnum=$(this).prev().val();
	if(comnum.length!=12){
		jAlert("사업자등록번호를 확인해 주세요.","알림",function(){
			$("#comnum").focus();
		})
			return false;
	}

	$.post( "get_set.php", {
		type: 'main_comp_chk',
		comnum:comnum
	})
	.done(function(jqXHR) {
			jqXHR=jqXHR.split("|");
			if(jqXHR[0]==1000){
				//완료시
				//jAlert(jqXHR[1],"알림");
				$("#cominfo").val(jqXHR[1]);
				$("#match").submit();
			}else{
				//오류시
				if(jqXHR[1]=="02") jAlert("검색 결과가 없습니다.","알림");
				else jAlert("검색에 오류가 있습니다.","알림");
			}

	});



})
//사업자번호
	$('.comnum_chk').on('keyup', function(){
        var num = $(this).val();
        num.trim();  // 스페이스바 제거
        this.value = AutoHypen(num) ;
    });

function AutoHypen(companyNum){
      companyNum = companyNum.replace(/[^0-9]/g, '');
      var tempNum = '';

      if(companyNum.length < 4){
        return companyNum;
      }
      else if(companyNum.length < 6){
        tempNum += companyNum.substr(0,3);
        tempNum += '-';
        tempNum += companyNum.substr(3,2);
        return tempNum;
      }
      else if(companyNum.length < 11){
        tempNum += companyNum.substr(0,3);
        tempNum += '-';
        tempNum += companyNum.substr(3,2);
        tempNum += '-';
        tempNum += companyNum.substr(5);
        return tempNum;
      }
      else{
        tempNum += companyNum.substr(0,3);
        tempNum += '-';
        tempNum += companyNum.substr(3,2);
        tempNum += '-';
        tempNum += companyNum.substr(5);
        return tempNum;
      }
    }
    const mCorporateSwiper = new Swiper('#mCorporate .swiper', {
        slidesPerView: "auto",
        slidesPerGroup: 1,
        spaceBetween: 30,
        pagination: {
            el: '#mCorporate .swiper-pagination',
        },
        navigation: {
            nextEl: '#mCorporate .swiper-button-next',
            prevEl: '#mCorporate .swiper-button-prev',
        },
        scrollbar: {
            el: '#mCorporate .swiper-scrollbar',
            draggable: true,
        },
    });

    const mBusinessSwiper = new Swiper('#mBusiness .swiper', {
        slidesPerView: "auto",
        slidesPerGroup: 1,
        spaceBetween: 30,
        pagination: {
            el: '#mBusiness .swiper-pagination',
        },
        navigation: {
            nextEl: '#mBusiness .swiper-button-next',
            prevEl: '#mBusiness .swiper-button-prev',
        },
        scrollbar: {
            el: '#mBusiness .swiper-scrollbar',
            draggable: true,
        },
    });
</script>

<?php include_once(G5_THEME_PATH . '/tail.php'); ?>
