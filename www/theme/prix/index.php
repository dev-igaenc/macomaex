<?php
if (!defined('_INDEX_')) define('_INDEX_', true);
if (!defined('_GNUBOARD_')) exit;
if (G5_IS_MOBILE) {
    include_once(G5_THEME_MOBILE_PATH . '/index.php');
    return;
}
include_once(G5_THEME_PATH . '/head.php');

function get_processed_companies($order_by_col, $limit = 4, $default_tags = []) {
    $sql = "SELECT a.KEDCD, a.ENP_NM, a.ENP_NM_TRD, a.PD_NM, a.VENP_YN,
                   b.growth_score_5, b.profit_score_5, b.stability_score_5, b.final_total_score_5
            FROM company a
            LEFT JOIN company_score_final_dedup b ON a.KEDCD = b.KEDCD
            WHERE b.{$order_by_col} IS NOT NULL
            ORDER BY b.{$order_by_col} DESC, b.final_total_score_5 DESC
            LIMIT {$limit}";

    $result = sql_query($sql);
    $list = array();

    while($row = sql_fetch_array($result)) {
        $row['display_name'] = $row['ENP_NM_TRD'] ?: $row['ENP_NM'];
        $row['final_score']  = number_format($row['final_total_score_5'], 1);
        
        // [수정] PD_NM을 콤마로 잘라 배열로 저장
        $row['PD_NM_TAGS'] = []; 
        if (!empty($row['PD_NM'])) {
            $raw_tags = explode(',', $row['PD_NM']);
            foreach($raw_tags as $rt) {
                $trimmed = trim($rt);
                if($trimmed) {
                    // ' 외' 문구 제거 후 배열에 추가
                    $row['PD_NM_TAGS'][] = str_replace(' 외', '', $trimmed);
                }
            }
        }

        $list[] = $row;
    }
    return $list;
}

$company_list = get_processed_companies('growth_score_5', 4); 
$stable_list = get_processed_companies('stability_score_5', 4);
?>


<div id="container" class="full">

<div id="mVisual">
    <video class="mVisual_bgVideo" autoplay="" loop="" muted="" playsinline="" preload="auto">
        <source src="/theme/prix/video/circle-loop.mp4" type="video/mp4">
        비디오를 로드할 수 없습니다.
    </video>
    <div class="inner">
        <div class="mVisual_main">
            <div class="mVisual_left">
                <h1 class="mVisual_heading">사업자번호 입력하고 <br>AI 거래처 추천받기!</h1>
                <div class="mVisual_searchWrap bxShadow">
                    <form action="/ko/matching_list.php" method="POST" id="match">
                        <input type="hidden" name="cominfo" id="cominfo">
                        <input type="text" name="comnum" id="comnum" placeholder="사업자등록번호를 입력해보세요" class="comnum_chk" maxlength="12">
                        <button type="submit" id="main_submit" class="search_btn" aria-label="AI 추천받기">
                            <span class="sparkle"><svg viewBox="0 0 24 24"><use href="#icon-magic-wand"></use></svg></span>
                            <span>AI 추천받기</span>
                        </button>
                    </form>
                </div>
            </div>
            <div class="mVisual_promo">
                <div class="mVisual_promo_head">
                    <span class="mVisual_promo_head_title">실시간 매칭현황</span>
                    <span class="mVisual_promo_clock"><span class="clock-dot"></span><span id="promo-live-clock">2026. 02. 11. 오전 08:17:37</span></span>
                </div>
                <div class="mVisual_promo_card">
                    <div class="mVisual_promo_card_top">
                        <span class="mVisual_promo_card_title">매칭 요청률</span>
                        <span class="mVisual_promo_card_right">
                            <span class="mVisual_promo_card_q_wrap">
                                <span class="mVisual_promo_card_q" aria-label="도움말">?</span>
                                <span class="mVisual_promo_card_q_tooltip">전체 회원 중 매칭을 요청한 비율입니다.</span>
                            </span>
                        </span>
                    </div>
                    <div class="mVisual_promo_card_value" id="percent-request">28.47%</div>
                    <div class="mVisual_promo_card_compare up" id="promo-request-compare">
                        <span>지난 주 대비</span> <span class="compare_val"><span class="arrow">↑</span> 0.7%P</span>
                    </div>
                </div>
                <div class="mVisual_promo_card">
                    <div class="mVisual_promo_card_top">
                        <span class="mVisual_promo_card_title">매칭 수락률</span>
                        <span class="mVisual_promo_card_right">
                            <span class="mVisual_promo_card_q_wrap">
                                <span class="mVisual_promo_card_q" aria-label="도움말">?</span>
                                <span class="mVisual_promo_card_q_tooltip">요청 건수 중 수락한 비율입니다.</span>
                            </span>
                        </span>
                    </div>
                    <div class="mVisual_promo_card_value" id="percent-accept">31.20%</div>
                    <div class="mVisual_promo_card_compare up" id="promo-accept-compare">
                        <span>지난 주 대비</span> <span class="compare_val"><span class="arrow">↑</span> 0.2%P</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="mCategories" class="mSection">
    <div class="inner">
        <div class="mCategories_row">
            <div class="mCategories_primary">
        <!-- 데스크탑/태블릿: 그리드 (대분류 8개) -->
        <div class="mCategories_grid">
            <a href="/ko/matching_list.php?cat=metal" class="mCategories_item" title="금속·소재"><div class="cat_inner"><div class="cat_icon"><svg viewBox="0 0 48 48" data-inline-ready="1"><circle cx="24" cy="24" r="14" fill="#ECEFF1"></circle><path d="M14 20 L32 16 L36 22 L18 26 Z" fill="#B0BEC5"></path><path d="M14 20 L14 28 L18 34 L18 26 Z" fill="#90A4AE"></path><path d="M18 26 L36 22 L36 30 L18 34 Z" fill="#78909C"></path><path d="M15.5 20.8 L31.5 17.4 L34.2 21.4 L18.2 24.8 Z" fill="#CFD8DC"></path><circle cx="19.5" cy="23.5" r="0.9" fill="#ECEFF1"></circle><circle cx="25.5" cy="22.3" r="0.9" fill="#ECEFF1"></circle><circle cx="30.8" cy="21.3" r="0.9" fill="#ECEFF1"></circle><circle cx="21.2" cy="28.4" r="0.9" fill="#ECEFF1"></circle><circle cx="27.5" cy="26.9" r="0.9" fill="#ECEFF1"></circle><path d="M19 30 H33" stroke="#B0BEC5" stroke-width="0.8" stroke-linecap="round" opacity="0.8"></path><path d="M19.5 31.6 H31.5" stroke="#B0BEC5" stroke-width="0.7" stroke-linecap="round" opacity="0.7"></path></svg></div><span class="cat_label">금속·소재</span></div></a>
            <a href="/ko/matching_list.php?cat=chemical" class="mCategories_item" title="화학·케미컬·원료"><div class="cat_inner"><div class="cat_icon"><svg viewBox="0 0 48 48" data-inline-ready="1"><circle fill="#e9f5ff" cx="24" cy="24" r="14"></circle><rect x="20" y="10" width="8" height="8" rx="2" fill="#455a64"></rect><rect x="21" y="11" width="6" height="6" rx="1.5" fill="#607d8b"></rect><path d="M18 18 H30 L31.5 24 C32 26 31.6 28.2 30.5 30 L27 35 C26 36.5 25 37 24 37 C23 37 22 36.5 21 35 L17.5 30 C16.4 28.2 16 26 16.5 24 Z" fill="#37474f"></path><path d="M18.5 25 H29.5 C30 27.2 29.6 29 28.7 30.3 L26 33.6 C25.3 34.5 24.6 35 24 35 C23.4 35 22.7 34.5 22 33.6 L19.3 30.3 C18.4 29 18 27.2 18.5 25 Z" fill="#26a69a"></path><path d="M19.5 26.2 C21.2 25.3 22.8 25 24 25 C25.2 25 26.8 25.3 28.5 26.2 L28 27.4 C26.6 26.8 25.3 26.5 24 26.5 C22.7 26.5 21.4 26.8 20 27.4 Z" fill="#4db6ac"></path><circle cx="21" cy="23" r="1.2" fill="#b2ebf2"></circle><circle cx="26.5" cy="25.5" r="1" fill="#b2ebf2"></circle><circle cx="23.4" cy="21.2" r="0.9" fill="#b2ebf2"></circle><path d="M22 19.5 C23 18.8 23.9 18.5 25 18.5 C25.6 18.5 26.2 18.6 26.8 18.8" fill="none" stroke="#ffffff" stroke-width="1.2" stroke-linecap="round" opacity="0.7"></path></svg></div><span class="cat_label">화학·케미컬·원료</span></div></a>
            <a href="/ko/matching_list.php?cat=electronics" class="mCategories_item" title="전자·전기·반도체"><div class="cat_inner"><div class="cat_icon"><svg viewBox="0 0 48 48" data-inline-ready="1"><circle fill="#eaf2ff" cx="24" cy="24" r="14"></circle><rect fill="#1e88e5" x="16" y="16" width="16" height="16" rx="2"></rect><rect fill="#90caf9" x="20" y="20" width="8" height="8" rx="1.2"></rect><path fill="#42a5f5" d="M14 19 H16 V21 H14 Z M14 23 H16 V25 H14 Z M14 27 H16 V29 H14 Z M32 19 H34 V21 H32 Z M32 23 H34 V25 H32 Z M32 27 H34 V29 H32 Z M19 14 H21 V16 H19 Z M23 14 H25 V16 H23 Z M27 14 H29 V16 H27 Z M19 32 H21 V34 H19 Z M23 32 H25 V34 H23 Z M27 32 H29 V34 H27 Z"></path><path fill="#ffd54f" d="M24 18 L27.5 23 H24.8 L27 29 L21 24.5 H23.8 L22 18 Z"></path></svg></div><span class="cat_label">전자·전기·반도체</span></div></a>
            <a href="/ko/matching_list.php?cat=machinery" class="mCategories_item" title="기계·설비·산업장비"><div class="cat_inner"><div class="cat_icon"><svg viewBox="0 0 48 48" data-inline-ready="1"><circle fill="#edf2f7" cx="24" cy="24" r="14"></circle><rect fill="#78909c" x="10" y="30" width="28" height="4" rx="1.2"></rect><path fill="#607d8b" d="M12 26 L36 26 L34 30 L14 30 Z"></path><circle fill="#546e7a" cx="20" cy="22" r="6.5"></circle><circle fill="#cfd8dc" cx="20" cy="22" r="2.4"></circle><rect fill="#90a4ae" x="19.4" y="12.5" width="1.2" height="3.8" rx="0.6"></rect><rect fill="#90a4ae" x="19.4" y="27.7" width="1.2" height="3.8" rx="0.6"></rect><rect fill="#90a4ae" x="10.5" y="21.4" width="3.8" height="1.2" rx="0.6"></rect><rect fill="#90a4ae" x="25.7" y="21.4" width="3.8" height="1.2" rx="0.6"></rect><path fill="#90a4ae" d="M13.7 16.6 L14.6 15.7 L17.3 18.4 L16.4 19.3 Z"></path><path fill="#90a4ae" d="M23.6 26.5 L24.5 25.6 L27.2 28.3 L26.3 29.2 Z"></path><path fill="#90a4ae" d="M23.6 17.5 L26.3 14.8 L27.2 15.7 L24.5 18.4 Z"></path><path fill="#90a4ae" d="M13.7 27.4 L16.4 24.7 L17.3 25.6 L14.6 28.3 Z"></path><circle fill="#455a64" cx="31.5" cy="27.5" r="4.2"></circle><circle fill="#b0bec5" cx="31.5" cy="27.5" r="1.5"></circle><path fill="#5f6f7a" d="M30 19 L35.5 13.5 L37.2 15.2 L31.7 20.7 Z"></path></svg></div><span class="cat_label">기계·설비·산업장비</span></div></a>
            <a href="/ko/matching_list.php?cat=auto" class="mCategories_item" title="자동차·모빌리티"><div class="cat_inner"><div class="cat_icon"><svg viewBox="0 0 48 48" data-inline-ready="1"><rect fill="#e1f5fe" x="8" y="20" width="32" height="12" rx="4"></rect><path fill="#29b6f6" d="M14 18 L34 18 L38 22 L10 22 Z"></path><circle fill="#37474f" cx="16" cy="30" r="3.5"></circle><circle fill="#37474f" cx="32" cy="30" r="3.5"></circle><rect fill="#ffffff" x="16" y="19" width="8" height="4" rx="1"></rect><rect fill="#ffffff" x="24" y="19" width="8" height="4" rx="1"></rect></svg></div><span class="cat_label">자동차·모빌리티</span></div></a>
            <a href="/ko/matching_list.php?cat=food" class="mCategories_item" title="식자재·식품 B2B"><div class="cat_inner"><div class="cat_icon"><svg viewBox="0 0 48 48" data-inline-ready="1"><circle fill="#ffb74d" cx="24" cy="22" r="11"></circle><path fill="#ffa726" d="M24 11 Q21 14 24 17 Q27 14 24 11"></path><path fill="#ffe082" d="M18 22 Q20 18 24 20 Q28 18 30 22 Q28 26 24 24 Q20 26 18 22"></path><path fill="#8bc34a" d="M22 12 Q23 10 25 10 Q24 12 24 14 Q23 13 22 12"></path></svg></div><span class="cat_label">식자재·식품 B2B</span></div></a>
            <a href="/ko/matching_list.php?cat=medical" class="mCategories_item" title="의료·헬스케어"><div class="cat_inner"><div class="cat_icon"><svg viewBox="0 0 48 48" data-inline-ready="1"><circle fill="#ffebee" cx="24" cy="24" r="14"></circle><circle fill="#e53935" cx="24" cy="24" r="9"></circle><path fill="#ffffff" d="M23 18 L25 18 L25 22 L29 22 L29 24 L25 24 L25 28 L23 28 L23 24 L19 24 L19 22 L23 22 Z"></path></svg></div><span class="cat_label">의료·헬스케어</span></div></a>
            <a href="/ko/matching_list.php?cat=packaging" class="mCategories_item" title="포장재·소모성 자재"><div class="cat_inner"><div class="cat_icon"><svg viewBox="0 0 48 48" data-inline-ready="1"><path fill="#ede7f6" d="M10 16 L38 16 L38 34 L10 34 Z"></path><path fill="#d1c4e9" d="M10 16 L24 10 L38 16 L24 22 Z"></path><path fill="#b39ddb" d="M24 22 L38 16 L38 34 L24 40 Z"></path><path fill="#9575cd" d="M24 22 L10 16 L10 34 L24 40 Z"></path><path fill="#7e57c2" d="M20 18 L24 20 L28 18 L24 16 Z"></path></svg></div><span class="cat_label">포장재·소모성 자재</span></div></a>
        </div>
        <!-- 모바일 전용: 대분류 8개 2줄 스와이프 캐러셀 -->
        <div class="mCategories_carousel_wrap">
            <div class="mCategories_carousel_card">
            <div class="mCategories_swiper swiper swiper-backface-hidden">
                <div class="swiper-wrapper" id="swiper-wrapper-4924dd818a341e8e" aria-live="polite">
                    <div class="swiper-slide" role="group" aria-label="1 / 8" style=""><a href="/ko/matching_list.php?cat=metal" class="mCategories_item" title="금속·소재"><div class="cat_inner"><div class="cat_icon"><svg viewBox="0 0 48 48" data-inline-ready="1"><circle cx="24" cy="24" r="14" fill="#ECEFF1"></circle><path d="M14 20 L32 16 L36 22 L18 26 Z" fill="#B0BEC5"></path><path d="M14 20 L14 28 L18 34 L18 26 Z" fill="#90A4AE"></path><path d="M18 26 L36 22 L36 30 L18 34 Z" fill="#78909C"></path><path d="M15.5 20.8 L31.5 17.4 L34.2 21.4 L18.2 24.8 Z" fill="#CFD8DC"></path><circle cx="19.5" cy="23.5" r="0.9" fill="#ECEFF1"></circle><circle cx="25.5" cy="22.3" r="0.9" fill="#ECEFF1"></circle><circle cx="30.8" cy="21.3" r="0.9" fill="#ECEFF1"></circle><circle cx="21.2" cy="28.4" r="0.9" fill="#ECEFF1"></circle><circle cx="27.5" cy="26.9" r="0.9" fill="#ECEFF1"></circle><path d="M19 30 H33" stroke="#B0BEC5" stroke-width="0.8" stroke-linecap="round" opacity="0.8"></path><path d="M19.5 31.6 H31.5" stroke="#B0BEC5" stroke-width="0.7" stroke-linecap="round" opacity="0.7"></path></svg></div><span class="cat_label">금속·소재</span></div></a></div>
                    <div class="swiper-slide" role="group" aria-label="2 / 8" style=""><a href="/ko/matching_list.php?cat=chemical" class="mCategories_item" title="화학·케미컬·원료"><div class="cat_inner"><div class="cat_icon"><svg viewBox="0 0 48 48" data-inline-ready="1"><circle fill="#e9f5ff" cx="24" cy="24" r="14"></circle><rect x="20" y="10" width="8" height="8" rx="2" fill="#455a64"></rect><rect x="21" y="11" width="6" height="6" rx="1.5" fill="#607d8b"></rect><path d="M18 18 H30 L31.5 24 C32 26 31.6 28.2 30.5 30 L27 35 C26 36.5 25 37 24 37 C23 37 22 36.5 21 35 L17.5 30 C16.4 28.2 16 26 16.5 24 Z" fill="#37474f"></path><path d="M18.5 25 H29.5 C30 27.2 29.6 29 28.7 30.3 L26 33.6 C25.3 34.5 24.6 35 24 35 C23.4 35 22.7 34.5 22 33.6 L19.3 30.3 C18.4 29 18 27.2 18.5 25 Z" fill="#26a69a"></path><path d="M19.5 26.2 C21.2 25.3 22.8 25 24 25 C25.2 25 26.8 25.3 28.5 26.2 L28 27.4 C26.6 26.8 25.3 26.5 24 26.5 C22.7 26.5 21.4 26.8 20 27.4 Z" fill="#4db6ac"></path><circle cx="21" cy="23" r="1.2" fill="#b2ebf2"></circle><circle cx="26.5" cy="25.5" r="1" fill="#b2ebf2"></circle><circle cx="23.4" cy="21.2" r="0.9" fill="#b2ebf2"></circle><path d="M22 19.5 C23 18.8 23.9 18.5 25 18.5 C25.6 18.5 26.2 18.6 26.8 18.8" fill="none" stroke="#ffffff" stroke-width="1.2" stroke-linecap="round" opacity="0.7"></path></svg></div><span class="cat_label">화학·케미컬·원료</span></div></a></div>
                    <div class="swiper-slide" role="group" aria-label="3 / 8" style=""><a href="/ko/matching_list.php?cat=electronics" class="mCategories_item" title="전자·전기·반도체"><div class="cat_inner"><div class="cat_icon"><svg viewBox="0 0 48 48" data-inline-ready="1"><circle fill="#eaf2ff" cx="24" cy="24" r="14"></circle><rect fill="#1e88e5" x="16" y="16" width="16" height="16" rx="2"></rect><rect fill="#90caf9" x="20" y="20" width="8" height="8" rx="1.2"></rect><path fill="#42a5f5" d="M14 19 H16 V21 H14 Z M14 23 H16 V25 H14 Z M14 27 H16 V29 H14 Z M32 19 H34 V21 H32 Z M32 23 H34 V25 H32 Z M32 27 H34 V29 H32 Z M19 14 H21 V16 H19 Z M23 14 H25 V16 H23 Z M27 14 H29 V16 H27 Z M19 32 H21 V34 H19 Z M23 32 H25 V34 H23 Z M27 32 H29 V34 H27 Z"></path><path fill="#ffd54f" d="M24 18 L27.5 23 H24.8 L27 29 L21 24.5 H23.8 L22 18 Z"></path></svg></div><span class="cat_label">전자·전기·반도체</span></div></a></div>
                    <div class="swiper-slide" role="group" aria-label="4 / 8" style=""><a href="/ko/matching_list.php?cat=machinery" class="mCategories_item" title="기계·설비·산업장비"><div class="cat_inner"><div class="cat_icon"><svg viewBox="0 0 48 48" data-inline-ready="1"><circle fill="#edf2f7" cx="24" cy="24" r="14"></circle><rect fill="#78909c" x="10" y="30" width="28" height="4" rx="1.2"></rect><path fill="#607d8b" d="M12 26 L36 26 L34 30 L14 30 Z"></path><circle fill="#546e7a" cx="20" cy="22" r="6.5"></circle><circle fill="#cfd8dc" cx="20" cy="22" r="2.4"></circle><rect fill="#90a4ae" x="19.4" y="12.5" width="1.2" height="3.8" rx="0.6"></rect><rect fill="#90a4ae" x="19.4" y="27.7" width="1.2" height="3.8" rx="0.6"></rect><rect fill="#90a4ae" x="10.5" y="21.4" width="3.8" height="1.2" rx="0.6"></rect><rect fill="#90a4ae" x="25.7" y="21.4" width="3.8" height="1.2" rx="0.6"></rect><path fill="#90a4ae" d="M13.7 16.6 L14.6 15.7 L17.3 18.4 L16.4 19.3 Z"></path><path fill="#90a4ae" d="M23.6 26.5 L24.5 25.6 L27.2 28.3 L26.3 29.2 Z"></path><path fill="#90a4ae" d="M23.6 17.5 L26.3 14.8 L27.2 15.7 L24.5 18.4 Z"></path><path fill="#90a4ae" d="M13.7 27.4 L16.4 24.7 L17.3 25.6 L14.6 28.3 Z"></path><circle fill="#455a64" cx="31.5" cy="27.5" r="4.2"></circle><circle fill="#b0bec5" cx="31.5" cy="27.5" r="1.5"></circle><path fill="#5f6f7a" d="M30 19 L35.5 13.5 L37.2 15.2 L31.7 20.7 Z"></path></svg></div><span class="cat_label">기계·설비·산업장비</span></div></a></div>
                    <div class="swiper-slide" role="group" aria-label="5 / 8" style=""><a href="/ko/matching_list.php?cat=auto" class="mCategories_item" title="자동차·모빌리티"><div class="cat_inner"><div class="cat_icon"><svg viewBox="0 0 48 48" data-inline-ready="1"><rect fill="#e1f5fe" x="8" y="20" width="32" height="12" rx="4"></rect><path fill="#29b6f6" d="M14 18 L34 18 L38 22 L10 22 Z"></path><circle fill="#37474f" cx="16" cy="30" r="3.5"></circle><circle fill="#37474f" cx="32" cy="30" r="3.5"></circle><rect fill="#ffffff" x="16" y="19" width="8" height="4" rx="1"></rect><rect fill="#ffffff" x="24" y="19" width="8" height="4" rx="1"></rect></svg></div><span class="cat_label">자동차·모빌리티</span></div></a></div>
                    <div class="swiper-slide" role="group" aria-label="6 / 8" style=""><a href="/ko/matching_list.php?cat=food" class="mCategories_item" title="식자재·식품 B2B"><div class="cat_inner"><div class="cat_icon"><svg viewBox="0 0 48 48" data-inline-ready="1"><circle fill="#ffb74d" cx="24" cy="22" r="11"></circle><path fill="#ffa726" d="M24 11 Q21 14 24 17 Q27 14 24 11"></path><path fill="#ffe082" d="M18 22 Q20 18 24 20 Q28 18 30 22 Q28 26 24 24 Q20 26 18 22"></path><path fill="#8bc34a" d="M22 12 Q23 10 25 10 Q24 12 24 14 Q23 13 22 12"></path></svg></div><span class="cat_label">식자재·식품 B2B</span></div></a></div>
                    <div class="swiper-slide" role="group" aria-label="7 / 8" style=""><a href="/ko/matching_list.php?cat=medical" class="mCategories_item" title="의료·헬스케어"><div class="cat_inner"><div class="cat_icon"><svg viewBox="0 0 48 48" data-inline-ready="1"><circle fill="#ffebee" cx="24" cy="24" r="14"></circle><circle fill="#e53935" cx="24" cy="24" r="9"></circle><path fill="#ffffff" d="M23 18 L25 18 L25 22 L29 22 L29 24 L25 24 L25 28 L23 28 L23 24 L19 24 L19 22 L23 22 Z"></path></svg></div><span class="cat_label">의료·헬스케어</span></div></a></div>
                    <div class="swiper-slide" role="group" aria-label="8 / 8" style=""><a href="/ko/matching_list.php?cat=packaging" class="mCategories_item" title="포장재·소모성 자재"><div class="cat_inner"><div class="cat_icon"><svg viewBox="0 0 48 48" data-inline-ready="1"><path fill="#ede7f6" d="M10 16 L38 16 L38 34 L10 34 Z"></path><path fill="#d1c4e9" d="M10 16 L24 10 L38 16 L24 22 Z"></path><path fill="#b39ddb" d="M24 22 L38 16 L38 34 L24 40 Z"></path><path fill="#9575cd" d="M24 22 L10 16 L10 34 L24 40 Z"></path><path fill="#7e57c2" d="M20 18 L24 20 L28 18 L24 16 Z"></path></svg></div><span class="cat_label">포장재·소모성 자재</span></div></a></div>
                </div>
                <div class="swiper-pagination mCategories_carousel_pagination swiper-pagination-lock"><span class="swiper-pagination-progressbar-fill" style="transform: translate3d(0px, 0px, 0px) scaleX(1) scaleY(1); transition-duration: 300ms;"></span></div>
            </div>
            </div>
        </div>
            </div>
            <div class="mVisual_banner_wrap mVisual_banner_compact">
                <div class="mVisual_banner_swiper swiper swiper-initialized swiper-horizontal swiper-backface-hidden">
                    <div class="swiper-wrapper" id="swiper-wrapper-231a4b9bd65dda47" aria-live="off" style="transition-duration: 0ms; transform: translate3d(-280px, 0px, 0px); transition-delay: 0ms;">
                        <div class="swiper-slide swiper-slide-prev" role="group" aria-label="1 / 3" data-swiper-slide-index="0" style="width: 280px;">
                            <aside class="mVisual_banner mVisual_banner_card">
                                <div class="mVisual_banner_inner">
                                    <span class="mVisual_banner_status">테스트 운영 중 · 3월 말 런칭</span>
                                    <p class="mVisual_banner_main">거래 빈도가 높은<br>핵심 산업 집중</p>
                                    <p class="mVisual_banner_sub">제조·유통 기반 추천 모델 고도화</p>
                                </div>
                            </aside>
                        </div>
                        <div class="swiper-slide swiper-slide-active" role="group" aria-label="2 / 3" data-swiper-slide-index="1" style="width: 280px;">
                            <aside class="mVisual_banner mVisual_banner_card">
                                <div class="mVisual_banner_inner">
                                    <span class="mVisual_banner_status">오픈 기념 · 첫 달 무료</span>
                                    <p class="mVisual_banner_main">프리미엄 멤버십 오픈기념<br>첫 달 무료 체험</p>
                                    <p class="mVisual_banner_sub">지금 가입하고 프리미엄을 무료로 경험하세요</p>
                                </div>
                            </aside>
                        </div>
                        <div class="swiper-slide swiper-slide-next" role="group" aria-label="3 / 3" data-swiper-slide-index="2" style="width: 280px;">
                            <aside class="mVisual_banner mVisual_banner_card">
                                <div class="mVisual_banner_inner">
                                    <span class="mVisual_banner_status">테스트 운영 중 · 3월 말 런칭</span>
                                    <p class="mVisual_banner_main">거래 빈도가 높은<br>핵심 산업 집중</p>
                                    <p class="mVisual_banner_sub">제조·유통 기반 추천 모델 고도화</p>
                                </div>
                            </aside>
                        </div>
                    </div>
                <div class="mVisual_banner_pagination" aria-hidden="true">
                    <span class="mVisual_banner_page_current">2</span>/<span class="mVisual_banner_page_total">3</span>
                    <button type="button" class="mVisual_banner_next" aria-label="다음 배너">&gt;</button>
                </div>
                <span class="swiper-notification" aria-live="assertive" aria-atomic="true"></span></div>
            </div>
        </div>
    </div>
</div>

<div id="mHookSections">
    <div class="inner">
        <div class="mHookSection">
            <div class="mHookSection_header">
                <h2 class="mHookSection_title"><span class="mHookSection_title_highlight">3년 연속 성장 기업</span></h2>
                <a href="/ko/matching_list.php" class="mHookSection_more">더보기</a>
            </div>
            <div class="mCompanyGrid swiper swiper-backface-hidden">
                <div class="swiper-wrapper" id="swiper-wrapper-1094e3f103adebb89f" aria-live="polite">
                
                <?php foreach ($company_list as $index => $com): 
                    // 태그 계산 로직 추가
                    $allTags = $com['PD_NM_TAGS'] ?? [];
                    $totalCount = count($allTags);
                    $showNum = 2; // 보여줄 개수
                    $displayTags = array_slice($allTags, 0, $showNum);
                    $restCount = $totalCount - $showNum;
                ?>
                <div class="mCompanyCard swiper-slide detail_view" kedcd="<?= $com['KEDCD'] ?>" role="group" aria-label="<?= ($index+1) ?> / 4">
                    <div class="mCompanyCard_content">
                        <div class="mCompanyCard_badges">
                            <span class="mCompanyCard_badge mCompanyCard_badge_growth">성장성높은</span>
                            <?php if (($com['VENP_YN'] ?? 'N') === 'Y'): ?>
                            <span class="mCompanyCard_badge">벤처인증기업</span>
                            <?php endif; ?>
                        </div>

                        <h3 class="mCompanyCard_title"><?= htmlspecialchars($com['display_name']) ?></h3>

                        <p class="mCompanyCard_desc">
                            <?php if ($totalCount > 0): ?>
                                <?php foreach ($displayTags as $tag): ?>
                                    <span class="mCompanyCard_desc_tag"><?= htmlspecialchars($tag) ?></span>
                                <?php endforeach; ?>
                                
                                <?php if ($restCount > 0): ?>
                                    <span class="mCompanyCard_desc_more">+<?= $restCount ?></span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </p>

                        <div class="mCompanyCard_rating">
                            <div class="mCompanyCard_rating_left">
                                <span class="mCompanyCard_rating_label">종합</span>
                                <span class="mCompanyCard_rating_score"><?= $com['final_score'] ?></span>
                                <div class="mCompanyCard_rating_stars">
                                    <?php 
                                    $full_stars = floor($com['final_total_score_5']);
                                    for($i=1; $i<=5; $i++): 
                                        if($i <= $full_stars):
                                    ?>
                                        <svg viewBox="0 0 24 24" fill="#ffc107"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>
                                    <?php else: ?>
                                        <svg viewBox="0 0 24 24" fill="#e0e0e0"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>
                                    <?php endif; endfor; ?>
                                </div>
                            </div>

                            <div class="mCompanyCard_rating_right">
                                <div class="mCompanyCard_rating_bar">
                                    <span class="mCompanyCard_rating_bar_label">성장성</span>
                                    <div class="mCompanyCard_rating_bar_chart">
                                        <div class="mCompanyCard_rating_bar_fill" style="width: <?= ($com['growth_score_5'] / 5 * 100) ?>%;"></div>
                                    </div>
                                    <span class="mCompanyCard_rating_bar_count"><?= number_format($com['growth_score_5'], 1) ?></span>
                                </div>
                                <div class="mCompanyCard_rating_bar">
                                    <span class="mCompanyCard_rating_bar_label">수익성</span>
                                    <div class="mCompanyCard_rating_bar_chart">
                                        <div class="mCompanyCard_rating_bar_fill" style="width: <?= ($com['profit_score_5'] / 5 * 100) ?>%;"></div>
                                    </div>
                                    <span class="mCompanyCard_rating_bar_count"><?= number_format($com['profit_score_5'], 1) ?></span>
                                </div>
                                <div class="mCompanyCard_rating_bar">
                                    <span class="mCompanyCard_rating_bar_label">재무안정</span>
                                    <div class="mCompanyCard_rating_bar_chart">
                                        <div class="mCompanyCard_rating_bar_fill" style="width: <?= ($com['stability_score_5'] / 5 * 100) ?>%;"></div>
                                    </div>
                                    <span class="mCompanyCard_rating_bar_count"><?= number_format($com['stability_score_5'], 1) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                </div>
            </div>
        </div><!-- /.mHookSection (성장성) -->

        <div class="mHookSection mHookSection_stable">
                <div class="mHookSection_header">
                    <h2 class="mHookSection_title"><span class="mHookSection_title_highlight mHookSection_title_highlight_green">안정성이 높은 기업</span></h2>
                    <a href="/ko/matching_list.php" class="mHookSection_more">더보기</a>
                </div>
                <div class="mCompanyGrid swiper swiper-backface-hidden">
                    <div class="swiper-wrapper" id="swiper-wrapper-d6e6dd9b107565ec8" aria-live="polite">
                        
                        <?php foreach ($stable_list as $idx => $com): 
                            // 태그 계산 로직 추가
                            $allTags = $com['PD_NM_TAGS'] ?? [];
                            $totalCount = count($allTags);
                            $showNum = 2; // 보여줄 개수
                            $displayTags = array_slice($allTags, 0, $showNum);
                            $restCount = $totalCount - $showNum;
                        ?>
                        <div class="mCompanyCard swiper-slide detail_view" kedcd="<?= $com['KEDCD'] ?>" role="group" aria-label="<?= ($idx+1) ?> / 4">
                            <div class="mCompanyCard_content">
                                <div class="mCompanyCard_badges">
                                    <span class="mCompanyCard_badge mCompanyCard_badge_stable">안정성높은</span>
                                    <?php if (($com['VENP_YN'] ?? 'N') === 'Y'): ?>
                                    <span class="mCompanyCard_badge">벤처인증기업</span>
                                    <?php endif; ?>
                                </div>
                                
                                <h3 class="mCompanyCard_title"><?= htmlspecialchars($com['display_name']) ?></h3>
                                
                                <p class="mCompanyCard_desc">
                                    <?php if ($totalCount > 0): ?>
                                        <?php foreach ($displayTags as $tag): ?>
                                            <span class="mCompanyCard_desc_tag"><?= htmlspecialchars($tag) ?></span>
                                        <?php endforeach; ?>
                                        
                                        <?php if ($restCount > 0): ?>
                                            <span class="mCompanyCard_desc_more">+<?= $restCount ?></span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </p>

                                <div class="mCompanyCard_rating">
                                    <div class="mCompanyCard_rating_left">
                                        <span class="mCompanyCard_rating_label">종합</span>
                                        <span class="mCompanyCard_rating_score"><?= $com['final_score'] ?></span>
                                        <div class="mCompanyCard_rating_stars">
                                            <?php 
                                            $full_stars = floor($com['final_total_score_5']);
                                            for($i=1; $i<=5; $i++): 
                                                if($i <= $full_stars):
                                            ?>
                                                <svg viewBox="0 0 24 24" fill="#ffc107"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>
                                            <?php else: ?>
                                                <svg viewBox="0 0 24 24" fill="#e0e0e0"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>
                                            <?php endif; endfor; ?>
                                        </div>
                                    </div>
                                    <div class="mCompanyCard_rating_right">
                                        <div class="mCompanyCard_rating_bar">
                                            <span class="mCompanyCard_rating_bar_label">성장성</span>
                                            <div class="mCompanyCard_rating_bar_chart">
                                                <div class="mCompanyCard_rating_bar_fill" style="width: <?= ($com['growth_score_5'] / 5 * 100) ?>%;"></div>
                                            </div>
                                            <span class="mCompanyCard_rating_bar_count"><?= number_format($com['growth_score_5'], 1) ?></span>
                                        </div>
                                        <div class="mCompanyCard_rating_bar">
                                            <span class="mCompanyCard_rating_bar_label">수익성</span>
                                            <div class="mCompanyCard_rating_bar_chart">
                                                <div class="mCompanyCard_rating_bar_fill" style="width: <?= ($com['profit_score_5'] / 5 * 100) ?>%;"></div>
                                            </div>
                                            <span class="mCompanyCard_rating_bar_count"><?= number_format($com['profit_score_5'], 1) ?></span>
                                        </div>
                                        <div class="mCompanyCard_rating_bar">
                                            <span class="mCompanyCard_rating_bar_label">재무안정</span>
                                            <div class="mCompanyCard_rating_bar_chart">
                                                <div class="mCompanyCard_rating_bar_fill" style="width: <?= ($com['stability_score_5'] / 5 * 100) ?>%;"></div>
                                            </div>
                                            <span class="mCompanyCard_rating_bar_count"><?= number_format($com['stability_score_5'], 1) ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mCompanyCard_footer">
                                    <span class="mCompanyCard_price">
                                        <span class="mCompanyCard_price_icon"><svg viewBox="0 0 48 48"><use href="#cat-iso-<?= (5 + $idx) ?>"></use></svg></span>
                                    </span>
                                    <div class="mCompanyCard_provider">
                                        <span class="mCompanyCard_provider_name"><?= htmlspecialchars($com['display_name']) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
            </div>
        </div><!-- /.mHookSection_stable -->
    </div><!-- /.inner -->
</div><!-- /#mHookSections -->

    <div id="company-view-lay" class="layer-base layer-main company-view">
        <div class="layer-in new"></div>
    </div>  

    <?php
    $company_detail_popup_variant = 'advanced';
    include_once(G5_THEME_PATH . '/partials/company-detail-matching-popup.php');
    ?>


<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
// 카테고리 태그 선택 토글 (프론트 전용)
document.querySelectorAll('.mVisual_tag').forEach(function(btn) {
    btn.addEventListener('click', function() { this.classList.toggle('selected'); });
});
// 카테고리 아이콘: <use> 참조를 실제 요소로 펼쳐서(path/circle/rect) 개별 애니메이션 가능하게 처리
(function() {
    function inlineCategoryIcons() {
        var svgs = document.querySelectorAll('.mCategories_item .cat_icon svg');
        svgs.forEach(function(svg) {
            if (svg.getAttribute('data-inline-ready') === '1') return;
            var useEl = svg.querySelector('use');
            if (!useEl) return;

            var ref = useEl.getAttribute('href') || useEl.getAttribute('xlink:href');
            if (!ref || ref.charAt(0) !== '#') return;

            var symbol = document.querySelector(ref);
            if (!symbol) return;

            var viewBox = symbol.getAttribute('viewBox');
            if (viewBox && !svg.getAttribute('viewBox')) {
                svg.setAttribute('viewBox', viewBox);
            }

            while (svg.firstChild) {
                svg.removeChild(svg.firstChild);
            }

            Array.prototype.forEach.call(symbol.childNodes, function(node) {
                if (node.nodeType === 1) {
                    svg.appendChild(node.cloneNode(true));
                }
            });

            svg.setAttribute('data-inline-ready', '1');
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', inlineCategoryIcons);
    } else {
        inlineCategoryIcons();
    }
})();
// 모바일 전용: 카테고리 2줄 스와이프 캐러셀 (대분류 8개)
(function() {
    var wrap = document.querySelector('.mCategories_carousel_wrap');
    var el = document.querySelector('.mCategories_swiper');
    var catSwiper = null;
    function initCategoriesCarousel() {
        if (!el || !wrap) return;
        if (window.innerWidth > 640) {
            if (catSwiper) {
                catSwiper.destroy(true, true);
                catSwiper = null;
            }
            return;
        }
        if (catSwiper) return;
        catSwiper = new Swiper(el, {
            slidesPerView: 4.15,
            spaceBetween: 14,
            grid: { rows: 2, fill: 'row' },
            freeMode: true,
            resistance: true,
            resistanceRatio: 0.85,
            grabCursor: true,
            pagination: {
                el: '.mCategories_carousel_pagination',
                type: 'progressbar',
            },
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCategoriesCarousel);
    } else {
        initCategoriesCarousel();
    }
    window.addEventListener('resize', function() {
        var w = window.innerWidth;
        if (w <= 640 && !catSwiper) initCategoriesCarousel();
        else if (w > 640 && catSwiper) {
            catSwiper.destroy(true, true);
            catSwiper = null;
        }
    });
})();

// 매칭 요청률/수락률 물음표 툴팁: 호버 + 클릭 토글
document.querySelectorAll('.mVisual_promo_card_q_wrap').forEach(function(wrap) {
    var q = wrap.querySelector('.mVisual_promo_card_q');
    if (!q) return;
    q.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        document.querySelectorAll('.mVisual_promo_card_q_wrap.open').forEach(function(o) {
            if (o !== wrap) o.classList.remove('open');
        });
        wrap.classList.toggle('open');
    });
});
document.addEventListener('click', function(ev) {
    if (ev.target.closest && ev.target.closest('#mCategories')) return;
    document.querySelectorAll('.mVisual_promo_card_q_wrap.open').forEach(function(w) { w.classList.remove('open'); });
});

// 사업자 확인 모달: 오버레이 클릭 시 닫기
    // 비주얼 배너 캐러셀: 3장 겹침, 4초 자동 로테이션, 1/3> 페이지네이션
    (function() {
        var wrap = document.querySelector('.mVisual_banner_wrap');
        var swiperEl = wrap && wrap.querySelector('.mVisual_banner_swiper');
        var paginationEl = wrap && wrap.querySelector('.mVisual_banner_pagination');
        var nextBtn = wrap && wrap.querySelector('.mVisual_banner_next');
        if (!swiperEl || !paginationEl) return;
        var total = 3;
        function updatePagination(swiper) {
            var cur = paginationEl.querySelector('.mVisual_banner_page_current');
            var tot = paginationEl.querySelector('.mVisual_banner_page_total');
            if (cur) cur.textContent = (swiper.realIndex + 1);
            if (tot) tot.textContent = total;
        }
        var bannerSwiper = new Swiper(swiperEl, {
            slidesPerView: 1,
            spaceBetween: 0,
            loop: true,
            autoplay: { delay: 4000, disableOnInteraction: false },
            on: {
                init: updatePagination,
                slideChange: updatePagination,
            },
        });
        if (nextBtn) nextBtn.addEventListener('click', function() { bannerSwiper.slideNext(); });
    })();
    // 모바일 뷰에서 기업카드 캐러셀 초기화
    function initCompanyCardCarousel() {
        document.querySelectorAll('.mCompanyGrid.swiper').forEach(function(grid) {
            // 기존 Swiper 인스턴스가 있으면 먼저 제거
            if (grid.swiperInstance) {
                grid.swiperInstance.destroy(true, true);
                grid.swiperInstance = null;
            }
            
            if (window.innerWidth <= 640) {
                // 모바일에서만 Swiper 초기화
                grid.swiperInstance = new Swiper(grid, {
                    slidesPerView: 1.2,
                    spaceBetween: 16,
                    freeMode: true,
                    resistance: true,
                    resistanceRatio: 0.85,
                });
            }
        });
    }

    // 초기화 및 리사이즈 이벤트
    initCompanyCardCarousel();
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(initCompanyCardCarousel, 250);
    });

    // 실시간 매칭 현황 업데이트 (live_stats.php 연결)
    (function() {
        const updateInterval = 10000; // 10초마다 서버에서 최신 데이터 가져옴
        
        function updateUI(data) {
            if (!data || !data.rates) return;
            
            const reqRateEl = document.getElementById('percent-request');
            const accRateEl = document.getElementById('percent-accept');

            if (reqRateEl) reqRateEl.textContent = data.rates.request_rate.toFixed(2) + '%';
            if (accRateEl) accRateEl.textContent = data.rates.accept_rate.toFixed(2) + '%';

            // 지난 주 대비 수치 업데이트
            const reqDiffEl = document.querySelector('#promo-request-compare .compare_val');
            const accDiffEl = document.querySelector('#promo-accept-compare .compare_val');

            if (reqDiffEl && data.rates.request_diff !== undefined) {
                const arrow = data.rates.request_diff >= 0 ? '↑' : '↓';
                reqDiffEl.innerHTML = `<span class="arrow">${arrow}</span> ${Math.abs(data.rates.request_diff).toFixed(2)}%P`;
                const parent = document.getElementById('promo-request-compare');
                if (parent) parent.className = `mVisual_promo_card_compare ${data.rates.request_diff >= 0 ? 'up' : 'down'}`;
            }
            if (accDiffEl && data.rates.accept_diff !== undefined) {
                const arrow = data.rates.accept_diff >= 0 ? '↑' : '↓';
                accDiffEl.innerHTML = `<span class="arrow">${arrow}</span> ${Math.abs(data.rates.accept_diff).toFixed(2)}%P`;
                const parent = document.getElementById('promo-accept-compare');
                if (parent) parent.className = `mVisual_promo_card_compare ${data.rates.accept_diff >= 0 ? 'up' : 'down'}`;
            }
        }

        function fetchLiveStats() {
            fetch('/live_stats.php?t=' + Date.now())
                .then(response => response.json())
                .then(data => {
                    updateUI(data);
                })
                .catch(error => console.error('Live stats fetch failed:', error));
        }

        function updateClock() {
            const now = new Date();
            const options = { 
                year: 'numeric', month: '2-digit', day: '2-digit',
                hour: '2-digit', minute: '2-digit', second: '2-digit',
                hour12: true 
            };
            const clockStr = now.toLocaleString('ko-KR', options);
            const clockEl = document.getElementById('promo-live-clock');
            if (clockEl) clockEl.textContent = clockStr;
        }

        // 초기 실행
        fetchLiveStats();
        updateClock();
        
    // 주기적 실행
        setInterval(fetchLiveStats, updateInterval);
        setInterval(updateClock, 1000);
    })();

    var matchingMessageTemplates = {
        distributor: [
            { title: '신규 제품 소싱', body: '판매 가능한 신규 제품을 공급할 제조사를 찾고 있습니다. 테스트 거래 후 지속적인 거래를 희망합니다.' },
            { title: '제조 파트너 발굴', body: '안정적인 제품 공급이 가능한 제조사를 찾고 있습니다. 협의를 통해 장기 거래를 검토하고자 합니다.' },
            { title: 'OEM/ODM 생산 협력', body: '자체 브랜드 제품 생산을 위해 OEM 또는 ODM 협력 가능한 제조사를 찾고 있습니다.' },
            { title: '신규 상품 발굴', body: '판매 경쟁력 있는 신규 제품을 발굴하기 위해 협력 가능한 제조 파트너를 찾고 있습니다.' },
            { title: '안정적인 공급처 확보', body: '지속적으로 제품 공급이 가능한 제조사를 찾고 있습니다. 상호 협의를 통해 거래 조건을 논의하고자 합니다.' }
        ],
        manufacturer: [
            { title: '유통 파트너 발굴', body: '자사 제품의 판매 확대를 위해 유통 및 판매 협력 가능한 기업을 찾고 있습니다. 안정적이고 장기적인 거래 파트너를 희망합니다.' },
            { title: '신규 판매 채널 확보', body: '제품 판매처를 확대하기 위해 협업 가능한 유통사를 찾고 있습니다. 상호 협의를 통해 거래 조건을 논의하고자 합니다.' },
            { title: '수출/해외 유통 협력', body: '자사 제품의 해외 시장 진출을 위해 유통 및 판매 협력 가능한 파트너를 찾고 있습니다.' },
            { title: '신규 브랜드 유통 협력', body: '신제품 출시와 함께 판매 확대가 가능한 유통사를 찾고 있습니다. 초기 협의 후 장기적인 거래를 희망합니다.' },
            { title: '안정적인 판매 파트너 확보', body: '지속적인 제품 공급이 가능한 유통 및 판매 파트너를 찾고 있습니다. 안정적인 거래 관계 구축을 희망합니다.' }
        ]
    };

    function getCurrentBizRole() {
        var selected = $('input[name="biz_role_ui"]:checked').val();
        return selected === 'manufacturer' ? 'manufacturer' : 'distributor';
    }

    function renderMatchingTemplateCards() {
        var role = getCurrentBizRole();
        var rows = matchingMessageTemplates[role] || [];
        var html = '';
        for (var i = 0; i < rows.length; i++) {
            var n = i + 1;
            html += ''
                + '<div class="req-tpl-card" data-idx="' + i + '">'
                + '  <div class="req-tpl-head">'
                + '    <input type="checkbox" class="reqTplChk" value="' + i + '" id="reqTplChk_' + i + '">'
                + '    <h6>' + n + '. ' + rows[i].title + '</h6>'
                + '  </div>'
                + '  <p>' + rows[i].body + '</p>'
                + '</div>';
        }
        $('#reqTplGrid').html(html);
    }

    function updateMatchingMessageContent() {
        var role = getCurrentBizRole();
        var rows = matchingMessageTemplates[role] || [];
        var selectedTexts = [];
        $('.reqTplChk:checked').each(function () {
            var idx = parseInt($(this).val(), 10);
            if (!isNaN(idx) && rows[idx]) selectedTexts.push(rows[idx].body);
        });
        var extra = $.trim($('#match_content_extra').val() || '');
        var merged = selectedTexts.join("\n\n");
        if (extra) merged = merged ? (merged + "\n\n" + extra) : extra;
        $('#match_content').val(merged);
    }

    $('body').on('click', '.requestTypeBtn', function () {
        var value = $(this).data('value') === 'TM' ? 'TM' : 'BASIC';
        $('#request_type').val(value);

        $('.requestTypeBtn').removeClass('is-active').css({
            borderColor: '#d9dce3',
            background: '#fff',
            color: '#333'
        });
        $(this).addClass('is-active').css({
            borderColor: '#3856ff',
            background: '#3856ff',
            color: '#fff'
        });

        if (value === 'TM') {
            $('#requestTypeDesc').text('TM 요청은 1건당 30크레딧이 차감되며, 24시간 동안 상대 기업 응답이 없으면 운영팀이 오프라인 연락을 지원합니다.');
        } else {
            $('#requestTypeDesc').text('기본 요청은 상대 기업에 매칭 요청만 전달되며 1건당 10크레딧이 차감됩니다.');
        }
    });

    $('body').on('change', 'input[name="biz_role_ui"]', function () {
        renderMatchingTemplateCards();
        updateMatchingMessageContent();
    });

    $('body').on('click', '.req-tpl-card', function (e) {
        if ($(e.target).is('input')) return;
        var $chk = $(this).find('.reqTplChk');
        $chk.prop('checked', !$chk.prop('checked')).trigger('change');
    });

    $('body').on('change', '.reqTplChk', function () {
        $(this).closest('.req-tpl-card').toggleClass('is-on', $(this).is(':checked'));
        updateMatchingMessageContent();
    });

    $('body').on('input', '#match_content_extra', function () {
        updateMatchingMessageContent();
    });

    if (window.CompanyDetailActions && typeof window.CompanyDetailActions.init === 'function') {
        window.CompanyDetailActions.init({
            getSetUrl: '/get_set.php',
            popupSelector: '.popup_submit',
            requestTypeSelector: '#request_type',
            receiverNameSelector: '#recv',
            planUrl: '/ko/plan.php',
            loginUrl: function () {
                return '/bbs/login.php?url=' + encodeURIComponent(location.pathname + location.search);
            },
            beforeSubmit: function () {
                updateMatchingMessageContent();
                if (!$.trim($('#match_content').val())) {
                    if (typeof jAlert === 'function') jAlert('매칭 메시지 선택(선택) 또는 추가 요청사항(선택) 중 1개 이상 입력해 주세요.', '알림');
                    else alert('매칭 메시지 선택(선택) 또는 추가 요청사항(선택) 중 1개 이상 입력해 주세요.');
                    return false;
                }
                return true;
            },
            onPopupReady: function (kedcd, parts) {
                $('#match_content_extra').val('');
                renderMatchingTemplateCards();
                updateMatchingMessageContent();
                if (parts && parts[2]) $('#recv').text(parts[2]);
            }
        });
    }

</script>

	</div>
<?php
include_once(G5_THEME_PATH.'/tail.php');
?>
