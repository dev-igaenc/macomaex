<?
include_once("./_common.php");

$board['bo_10']="6"; // 1뎁스
$board['bo_9']="5"; // 2뎁스
//$board['bo_8']="1"; // 3뎁스
//$board['bo_7']="1"; // 4뎁스
// $board['bo_5'] = ""; // 1뎁스 제목
// $board['bo_6'] = ""; // 1뎁스 설명
// $board['bo_subject'] = "";  // 2뎁스 제목
//$board['bo_4'] = $config['cf_title']."의 혜택을 확인하세요.";  // 2뎁스 설명

//로그인 체크
if(!$member['mb_id']) ALERT("로그인 후 이용하세요.","/bbs/login.php");

$my_plan_css_ver = file_exists(__DIR__ . '/css/my_plan.css') ? filemtime(__DIR__ . '/css/my_plan.css') : time();
$my_plan_js_ver = file_exists(__DIR__ . '/js/my_plan.js') ? filemtime(__DIR__ . '/js/my_plan.js') : time();
$design_tokens_css_ver = file_exists(__DIR__ . '/css/design-tokens.css') ? filemtime(__DIR__ . '/css/design-tokens.css') : time();
$header_css_ver = file_exists(__DIR__ . '/css/header.css') ? filemtime(__DIR__ . '/css/header.css') : time();
add_stylesheet('<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/variable/pretendardvariable-dynamic-subset.min.css">', 190);
add_stylesheet('<link rel="stylesheet" href="/ko/css/design-tokens.css?ver=' . $design_tokens_css_ver . '">', 191);
add_stylesheet('<link rel="stylesheet" href="/ko/css/header.css?ver=' . $header_css_ver . '">', 192);
add_stylesheet('<link rel="stylesheet" href="/ko/css/my_plan.css?ver=' . $my_plan_css_ver . '">', 200);
add_javascript('<script src="/ko/js/my_plan.js?ver=' . $my_plan_js_ver . '"></script>', 200);
$hide_theme_svisual = true;
$hide_theme_pagetop = true;
include_once(G5_THEME_PATH.'/head.php');
$membership=get_membership();
foreach($membership as $value){
	$mship[$value['title_en']]=$value;
}

//맴버쉽 정보
$today = date("Y-m-d");
$msinfo_row = getUserMembership();
//print_r($msinfo_row[0]);
$chk=$msinfo_row[0];
//if($chk['uid']) print_r($chk);

//맴버쉽이 없는 경우 무료로
if(empty($msinfo_row[0])) $msinfo['membership_parent_no']=$membership[0]['no'];
else $msinfo=$msinfo_row[0];
$membership_info = sql_fetch("select * from g5_membership_manage where no='{$msinfo['membership_parent_no']}'" );
$is_credit_membership = 0;
if(($membership_info['title_en'] && strpos($membership_info['title_en'], 'CREDIT_') === 0) || ((int)$membership_info['matching_cnt'] > 0 && (int)$membership_info['month'] === 0)){
	$is_credit_membership = 1;
}
$membership_cancel_token = uniqid('', true);
set_session('ss_membership_cancel_token', $membership_cancel_token);
$remain_credit = (int)$msinfo['matching_able_cnt'];
$show_credit_cancel_btn = ($is_credit_membership && $remain_credit > 0);

if($msinfo['coupon']) $coupon=getCouponInfo($msinfo['coupon']);
?>


<div id="my_plan">

    <input type="hidden" name="ms_id" id="ms_id" value="<?=$msinfo['no']?>">
    <input type="hidden" name="is_credit_membership" id="is_credit_membership" value="<?=$is_credit_membership?>">
    <input type="hidden" name="membership_cancel_token" id="membership_cancel_token" value="<?=$membership_cancel_token?>">
    <div class="payment_history_cards">
        <div class="pay-page">
                    <div class="pay-page-header">
                        <div class="breadcrumb">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            마이페이지
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                            결제 정보
                        </div>
                        <h1>결제 정보</h1>
                    </div>

                    <div class="credit-summary">
                        <div class="credit-summary-left">
                            <div class="label">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                현재 보유 크레딧
                            </div>
                            <div class="amount">
                                <span class="num">120</span>
                                <span class="unit">크레딧</span>
                            </div>
                        </div>
                        <div class="credit-summary-right">
                            <a href="/ko/plan.php" class="credit-cta-btn">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                크레딧 충전하기
                            </a>
                        </div>
                    </div>

                    <div class="section-label">
                        <h2>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                            결제 내역
                        </h2>
                        <span class="count-badge" id="chargeBadge"></span>
                    </div>

                    <div class="filter-row" id="chargeFilter">
                        <button class="filter-btn active" data-filter="all" type="button">전체</button>
                        <button class="filter-btn" data-filter="charge" type="button">유료 충전</button>
                        <button class="filter-btn" data-filter="free" type="button">무상 지급</button>
                    </div>

                    <div id="chargeList" class="card-list">
                        <div class="pay-card" data-type="charge">
                            <div class="pay-card-icon charge">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                            </div>
                            <div class="pay-card-info">
                                <div class="title">Enterprise 플랜 충전</div>
                                <div class="meta">
                                    <span>2026-04-01</span>
                                    <span class="dot"></span>
                                    <span>1,300 크레딧 (보너스 +30% 포함)</span>
                                    <span class="dot"></span>
                                    <span>카드결제</span>
                                </div>
                            </div>
                            <div class="pay-card-right">
                                <span class="credit-delta plus">+1,300</span>
                                <span class="price-text">₩100,000</span>
                                <span class="status-badge done">완료</span>
                            </div>
                        </div>

                        <div class="pay-card" data-type="charge">
                            <div class="pay-card-icon charge">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                            </div>
                            <div class="pay-card-info">
                                <div class="title">Business 플랜 충전</div>
                                <div class="meta">
                                    <span>2026-03-18</span>
                                    <span class="dot"></span>
                                    <span>600 크레딧 (보너스 +20% 포함)</span>
                                    <span class="dot"></span>
                                    <span>카드결제</span>
                                </div>
                            </div>
                            <div class="pay-card-right">
                                <span class="credit-delta plus">+600</span>
                                <span class="price-text">₩50,000</span>
                                <span class="status-badge done">완료</span>
                            </div>
                        </div>

                        <div class="pay-card" data-type="charge">
                            <div class="pay-card-icon charge">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                            </div>
                            <div class="pay-card-info">
                                <div class="title">Professional 플랜 충전</div>
                                <div class="meta">
                                    <span>2026-01-20</span>
                                    <span class="dot"></span>
                                    <span>220 크레딧 (보너스 +10% 포함)</span>
                                    <span class="dot"></span>
                                    <span>카드결제</span>
                                </div>
                            </div>
                            <div class="pay-card-right">
                                <span class="credit-delta plus">+220</span>
                                <span class="price-text">₩20,000</span>
                                <span class="status-badge done">완료</span>
                            </div>
                        </div>

                        <div class="pay-card" data-type="charge">
                            <div class="pay-card-icon charge">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                            </div>
                            <div class="pay-card-info">
                                <div class="title">Starter 플랜 충전</div>
                                <div class="meta">
                                    <span>2026-01-05</span>
                                    <span class="dot"></span>
                                    <span>100 크레딧</span>
                                    <span class="dot"></span>
                                    <span>카드결제</span>
                                </div>
                            </div>
                            <div class="pay-card-right">
                                <span class="credit-delta plus">+100</span>
                                <span class="price-text">₩10,000</span>
                                <span class="status-badge done">완료</span>
                            </div>
                        </div>

                        <div class="pay-card" data-type="free">
                            <div class="pay-card-icon free">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5" rx="1"/><line x1="12" y1="22" x2="12" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 010-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 000-5C13 2 12 7 12 7z"/></svg>
                            </div>
                            <div class="pay-card-info">
                                <div class="title">신규 가입 무료 크레딧 지급</div>
                                <div class="meta">
                                    <span>2025-12-20</span>
                                    <span class="dot"></span>
                                    <span>Free 플랜 · 서비스 체험용 · 청약철회 제외</span>
                                </div>
                            </div>
                            <div class="pay-card-right">
                                <span class="credit-delta green">+30</span>
                                <span class="price-text">무료</span>
                                <span class="status-badge free">무상지급</span>
                            </div>
                        </div>
                    </div>
                    <div class="load-more-wrap" id="chargeMoreWrap">
                        <button class="load-more-btn" id="chargeMoreBtn" type="button">
                            더 보기
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                        </button>
                    </div>

                    <div class="section-gap">
                        <div class="section-label">
                            <h2>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                크레딧 사용 내역
                            </h2>
                            <span class="count-badge" id="useBadge"></span>
                        </div>

                        <div class="filter-row" id="useFilter">
                            <button class="filter-btn active" data-filter="all" type="button">전체</button>
                            <button class="filter-btn" data-filter="match" type="button">거래 요청</button>
                            <button class="filter-btn" data-filter="analyze" type="button">AI 분석</button>
                        </div>

                        <div id="useList" class="card-list">
                            <div class="pay-card" data-type="match">
                                <div class="pay-card-icon match">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                                </div>
                                <div class="pay-card-info">
                                    <div class="title">TM 서포트 거래 요청 — (주)한국정밀기계</div>
                                    <div class="meta">
                                        <span>2026-04-10</span>
                                        <span class="dot"></span>
                                        <span>거래 방향: 구매</span>
                                        <span class="dot"></span>
                                        <span>TM 서포트</span>
                                    </div>
                                </div>
                                <div class="pay-card-right">
                                    <span class="credit-delta minus">−30</span>
                                    <span class="status-badge used">사용</span>
                                </div>
                            </div>

                            <div class="pay-card" data-type="match">
                                <div class="pay-card-icon match">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                                </div>
                                <div class="pay-card-info">
                                    <div class="title">일반 거래 요청 — 대원스틸(주)</div>
                                    <div class="meta">
                                        <span>2026-04-08</span>
                                        <span class="dot"></span>
                                        <span>거래 방향: 구매</span>
                                        <span class="dot"></span>
                                        <span>일반 거래</span>
                                    </div>
                                </div>
                                <div class="pay-card-right">
                                    <span class="credit-delta minus">−10</span>
                                    <span class="status-badge used">사용</span>
                                </div>
                            </div>

                            <div class="pay-card" data-type="analyze">
                                <div class="pay-card-icon analyze">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l1.5 4.5L18 9l-4.5 1.5L12 15l-1.5-4.5L6 9l4.5-1.5L12 3z"/></svg>
                                </div>
                                <div class="pay-card-info">
                                    <div class="title">AI 기업 분석 — (주)안정기계</div>
                                    <div class="meta">
                                        <span>2026-04-07</span>
                                        <span class="dot"></span>
                                        <span>기업 심층 분석 리포트</span>
                                    </div>
                                </div>
                                <div class="pay-card-right">
                                    <span class="credit-delta minus">−0.5</span>
                                    <span class="status-badge used">사용</span>
                                </div>
                            </div>

                            <div class="pay-card" data-type="match">
                                <div class="pay-card-icon match">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                                </div>
                                <div class="pay-card-info">
                                    <div class="title">일반 거래 요청 — (주)태양화학</div>
                                    <div class="meta">
                                        <span>2026-03-25</span>
                                        <span class="dot"></span>
                                        <span>거래 방향: 판매</span>
                                        <span class="dot"></span>
                                        <span>일반 거래</span>
                                    </div>
                                </div>
                                <div class="pay-card-right">
                                    <span class="credit-delta minus">−10</span>
                                    <span class="status-badge used">사용</span>
                                </div>
                            </div>

                            <div class="pay-card" data-type="analyze">
                                <div class="pay-card-icon analyze">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l1.5 4.5L18 9l-4.5 1.5L12 15l-1.5-4.5L6 9l4.5-1.5L12 3z"/></svg>
                                </div>
                                <div class="pay-card-info">
                                    <div class="title">AI 기업 분석 — (주)태양화학</div>
                                    <div class="meta">
                                        <span>2026-03-25</span>
                                        <span class="dot"></span>
                                        <span>기업 심층 분석 리포트</span>
                                    </div>
                                </div>
                                <div class="pay-card-right">
                                    <span class="credit-delta minus">−0.5</span>
                                    <span class="status-badge used">사용</span>
                                </div>
                            </div>

                            <div class="pay-card" data-type="match">
                                <div class="pay-card-icon match">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                                </div>
                                <div class="pay-card-info">
                                    <div class="title">일반 거래 요청 — 삼원포장(주)</div>
                                    <div class="meta">
                                        <span>2026-01-12</span>
                                        <span class="dot"></span>
                                        <span>거래 방향: 구매</span>
                                        <span class="dot"></span>
                                        <span>일반 거래</span>
                                    </div>
                                </div>
                                <div class="pay-card-right">
                                    <span class="credit-delta minus">−10</span>
                                    <span class="status-badge used">사용</span>
                                </div>
                            </div>
                        </div>
                        <div class="load-more-wrap" id="useMoreWrap">
                            <button class="load-more-btn" id="useMoreBtn" type="button">
                                더 보기
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="policy-box" id="policyBox">
                        <div class="policy-box-header" id="policyToggle" role="button" aria-expanded="false" tabindex="0">
                            <span class="policy-box-title">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                청약철회 및 크레딧 환불 정책
                            </span>
                            <svg class="policy-box-chev" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                        <div class="policy-box-body">
                            <div class="policy-notice" style="margin-top:16px; background:#f0fdf4; border-color:#bbf7d0;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                <span style="color:#14532d;">크레딧은 디지털 콘텐츠 이용권으로, <strong>첫 크레딧 사용 시점</strong>을 서비스 제공 개시로 봅니다. 결제 후 7일 이내 크레딧을 전혀 사용하지 않은 경우 전자상거래법 제17조에 따라 100% 환불이 가능합니다. 7일 경과 또는 일부 사용 후에는 잔여 유료 크레딧 기준으로 수수료(10%)를 제외한 금액이 환불됩니다.</span>
                            </div>
                            <table class="policy-table">
                                <thead>
                                    <tr>
                                        <th>크레딧 유형</th>
                                        <th>환불 여부</th>
                                        <th>사유</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>청약철회<br><span style="font-size:11px;color:#9ca3af;">결제 후 7일 이내 · 미사용</span></td>
                                        <td><span class="tag-allowed">100% 환불</span></td>
                                        <td>결제 후 7일 이내 크레딧을 전혀 사용하지 않은 경우 전액 환불<br><span style="font-size:11px;color:#9ca3af;">전자상거래법 제17조 적용</span></td>
                                    </tr>
                                    <tr>
                                        <td>중도 해지<br><span style="font-size:11px;color:#9ca3af;">7일 경과 또는 일부 사용</span></td>
                                        <td><span class="tag-partial">수수료 제외 환불</span></td>
                                        <td>잔여 유료 크레딧 기준, 수수료 10% 제외 후 환불<br><span style="font-size:11px;color:#9ca3af;">예: 잔여 유료 150cr(₩15,000) → 수수료 ₩1,500 제외 → 환불액 ₩13,500</span></td>
                                    </tr>
                                    <tr>
                                        <td>사용된 크레딧<br><span style="font-size:11px;color:#9ca3af;">거래 요청 · AI 분석</span></td>
                                        <td><span class="tag-blocked">불가</span></td>
                                        <td>첫 사용 시점에 서비스 제공이 개시된 것으로 간주<br><span style="font-size:11px;color:#9ca3af;">상대방의 거절·무응답 여부와 무관하게 적용</span></td>
                                    </tr>
                                    <tr>
                                        <td>보너스 크레딧<br><span style="font-size:11px;color:#9ca3af;">플랜 충전 시 무상 지급분</span></td>
                                        <td><span class="tag-blocked">환불 제외</span></td>
                                        <td>무상 지급분으로 환불 산정 대상에서 제외<br><span style="font-size:11px;color:#9ca3af;">환불 계산 시 유료 크레딧 잔여분만 기준으로 산정</span></td>
                                    </tr>
                                    <tr>
                                        <td>무상 지급 크레딧<br><span style="font-size:11px;color:#9ca3af;">신규 가입 보너스 · 이벤트</span></td>
                                        <td><span class="tag-blocked">불가</span></td>
                                        <td>대가 없이 지급된 크레딧으로 환불 대상 아님</td>
                                    </tr>
                                    <tr>
                                        <td>결제 오류 · 시스템 장애<br><span style="font-size:11px;color:#9ca3af;">회사 귀책 사유</span></td>
                                        <td><span class="tag-allowed">가능</span></td>
                                        <td>결제는 완료됐으나 크레딧이 정상 지급되지 않은 경우<br>고객센터 확인 후 결제 취소 또는 크레딧 보정 처리</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="info-box">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                <ul>
                                    <li>보너스 크레딧 잔여 내역은 마이페이지 &gt; 결제정보에서 확인하실 수 있습니다.</li>
                                    <li>AI 분석(0.5 크레딧)은 동일 기업 기준 6개월 내 재열람 시 크레딧이 차감되지 않습니다.</li>
                                    <li>결제 오류 문의: 고객센터 접수 후 영업일 기준 3일 이내 처리됩니다.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
        </div>
    </div>

</div>

<?
include_once(G5_THEME_PATH.'/tail.php');
?>
