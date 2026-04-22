<?
include_once("./_common.php");

$board['bo_10']="6"; // 1뎁스
$board['bo_9']="5"; // 2뎁스

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

//맴버쉽 정보
$msinfo_row = getUserMembership();
if(!is_array($msinfo_row)) $msinfo_row = array();
// 멤버십 기본값 보정
if(!empty($msinfo_row[0]) && is_array($msinfo_row[0])) $msinfo = $msinfo_row[0];
else $msinfo = array();

if(empty($msinfo['membership_parent_no']) && !empty($membership[0]['no'])) {
	$msinfo['membership_parent_no'] = (int)$membership[0]['no'];
}

$msinfo = array_merge(array(
	'no' => 0,
	'uid' => '',
	'membership_parent_no' => 0,
	'matching_able_cnt' => 0,
	'coupon' => ''
), $msinfo);

$membership_parent_no = (int)$msinfo['membership_parent_no'];
if($membership_parent_no > 0) $membership_info = sql_fetch("select * from g5_membership_manage where no='{$membership_parent_no}'" );
else $membership_info = array();
if(!is_array($membership_info)) $membership_info = array();
$is_credit_membership = 0;
if((!empty($membership_info['title_en']) && strpos((string)$membership_info['title_en'], 'CREDIT_') === 0) || ((int)$membership_info['matching_cnt'] > 0 && (int)$membership_info['month'] === 0)){
	$is_credit_membership = 1;
}
$membership_cancel_token = uniqid('', true);
set_session('ss_membership_cancel_token', $membership_cancel_token);
$remain_credit = (int)$msinfo['matching_able_cnt'];
$show_credit_cancel_btn = ($is_credit_membership && $remain_credit > 0);

if (!function_exists('my_plan_display_title_en')) {
	function my_plan_display_title_en($rawTitleEn)
	{
		$rawTitleEn = trim((string)$rawTitleEn);
		if ($rawTitleEn === '') return '';
		$rawTitleEn = preg_replace('/^CREDIT_/i', '', $rawTitleEn);
		if ($rawTitleEn === strtoupper($rawTitleEn)) {
			return ucwords(strtolower(str_replace('_', ' ', $rawTitleEn)));
		}
		return str_replace('_', ' ', $rawTitleEn);
	}
}

if (!function_exists('my_plan_pay_method_text')) {
	function my_plan_pay_method_text($raw)
	{
		$raw = strtoupper(trim((string)$raw));
		if ($raw === '') return '';
		if (strpos($raw, 'CARD') !== false || strpos($raw, 'CARD') === 0) return '카드결제';
		if (strpos($raw, 'VBANK') !== false || strpos($raw, 'BANK') !== false || strpos($raw, 'TRANS') !== false) return '계좌이체';
		if (strpos($raw, 'HPP') !== false || strpos($raw, 'PHONE') !== false || strpos($raw, 'MOBILE') !== false) return '휴대폰결제';
		if (strpos($raw, 'KAKAOPAY') !== false || strpos($raw, 'NAVERPAY') !== false || strpos($raw, 'PAYCO') !== false || strpos($raw, 'TOSS') !== false) return '간편결제';
		return trim((string)$raw);
	}
}

// 원장 조회 (구버전 fallback)
$sql = "select
			H.id,
			H.uid,
			H.tx_type,
			H.request_type,
			H.credit_delta,
			H.credit_after,
			H.order_no,
			H.matching_no,
			H.memo,
			H.created_at as event_date,
			B.tid as bill_tid,
			B.card_appl_price as bill_paid_amount,
			B.pay_method as bill_pay_method,
			P.membership_parent_no,
			P.order_goods,
			P.price as payment_price,
			P.pay_method as payment_method,
			MM.title_en as plan_title_en,
			MM.sale as plan_bonus_rate,
			MM.vat as plan_vat,
			TR.trade_direction as trade_request_direction,
			IFNULL(C2.ENP_NM, TR.target_company_name) as company_name
		from g5_matching_credit_history H
		left join g5_membership_paybill B on H.order_no=B.order_no
		left join g5_membership_payment P on H.order_no=P.order_no
		left join g5_membership_manage MM on P.membership_parent_no=MM.no
		left join trade_request TR on H.service_code='TRADE_REQUEST' and H.matching_no=TR.tr_no
		left join company C2 on C2.KEDCD=TR.target_kedcd
		where
			LOWER(TRIM(H.uid))=LOWER(TRIM('{$member['mb_id']}'))
			".($member['mb_email'] ? " or LOWER(TRIM(H.uid))=LOWER(TRIM('{$member['mb_email']}'))" : "")."
			".($msinfo['uid'] ? " or LOWER(TRIM(H.uid))=LOWER(TRIM('{$msinfo['uid']}'))" : "")."
		order by H.created_at desc, H.id desc";
$result = sql_query($sql, false);
if(!$result){
	$sql = "select
				H.id,
				H.uid,
				H.tx_type,
				H.request_type,
				H.credit_delta,
				H.credit_after,
				H.order_no,
				H.matching_no,
				H.memo,
				H.created_at as event_date,
				'' as bill_tid,
				0 as bill_paid_amount,
				'' as bill_pay_method,
				P.membership_parent_no,
				P.order_goods,
				P.price as payment_price,
				P.pay_method as payment_method,
				MM.title_en as plan_title_en,
				MM.sale as plan_bonus_rate,
				MM.vat as plan_vat,
				TR.trade_direction as trade_request_direction,
				IFNULL(C2.ENP_NM, TR.target_company_name) as company_name
			from g5_matching_credit_history H
			left join g5_membership_payment P on H.order_no=P.order_no
			left join g5_membership_manage MM on P.membership_parent_no=MM.no
			left join trade_request TR on H.service_code='TRADE_REQUEST' and H.matching_no=TR.tr_no
			left join company C2 on C2.KEDCD=TR.target_kedcd
			where
				LOWER(TRIM(H.uid))=LOWER(TRIM('{$member['mb_id']}'))
				".($member['mb_email'] ? " or LOWER(TRIM(H.uid))=LOWER(TRIM('{$member['mb_email']}'))" : "")."
				".($msinfo['uid'] ? " or LOWER(TRIM(H.uid))=LOWER(TRIM('{$msinfo['uid']}'))" : "")."
			order by H.created_at desc, H.id desc";
	$result = sql_query($sql, false);
}

$history_rows = array();
if($result){
	while($row = sql_fetch_array($result)){
		$history_rows[] = $row;
	}
}

$payment_rows = array();
$use_rows = array();
// 결제/사용 분리
for($i=0; $i<count($history_rows); $i++){
	$row = $history_rows[$i];
	$tx_type = strtoupper(trim((string)$row['tx_type']));
	if($tx_type=="USE") $use_rows[] = $row;
	else $payment_rows[] = $row;
}

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
                                <span class="num"><?=number_format((float)$remain_credit)?></span>
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
                        <?for($i=0; $i<count($payment_rows); $i++){
                            $row = $payment_rows[$i];
                            $tx_type = strtoupper(trim((string)$row['tx_type']));
                            $amount = (float)$row['credit_delta'];
                            $amount_abs = abs($amount);
                            $delta = number_format($amount_abs, 1);
                            if(substr($delta, -2)=='.0') $delta = number_format($amount_abs, 0);

                            $is_free = ($tx_type=="BONUS" || $tx_type=="FREE" || $tx_type=="EVENT");
                            if(!$is_free && !$row['order_no'] && $amount > 0) $is_free = true;
                            $filter_type = $is_free ? "free" : "charge";
                            $icon_class = $is_free ? "free" : "charge";
                            $is_signup_free = ($is_free && strpos((string)$row['memo'], "신규 가입 무료 크레딧 지급") !== false);
                            $plan_title = my_plan_display_title_en($row['plan_title_en']);
                            $bonus_rate = (int)$row['plan_bonus_rate'];
                            $pay_method_text = my_plan_pay_method_text($row['bill_pay_method'] ? $row['bill_pay_method'] : $row['payment_method']);

                            $title = $row['memo'] ? $row['memo'] : "[크레딧] {$delta} 크레딧 충전";
                            if(!$row['memo']){
                                if($tx_type=="CHARGE" && $plan_title) $title = $plan_title." 플랜 충전";
                                else if($tx_type=="REFUND") $title = "[크레딧] {$delta} 크레딧 환불";
                                else if($tx_type=="ADJUST") $title = "[크레딧] 관리자 조정 {$delta} 크레딧";
                                else if($is_free) $title = "[크레딧] 무료 크레딧 지급 {$delta}";
                            }
                            if($tx_type=="CHARGE" && $plan_title){
                                if(strpos((string)$title, "일반결제 충전")!==false || strpos((string)$title, "[크레딧] ")===0){
                                    $title = $plan_title." 플랜 충전";
                                }
                            }

                            $meta_credit_text = number_format($amount_abs)." 크레딧";
                            if($tx_type=="REFUND"){
                                $meta_credit_text = number_format($amount_abs)." 크레딧 환불";
                            } else if($tx_type=="ADJUST"){
                                $meta_credit_text = number_format($amount_abs)." 크레딧 조정";
                            } else if($tx_type=="CHARGE" && $bonus_rate > 0){
                                $meta_credit_text = number_format($amount_abs)." 크레딧 (보너스 +".$bonus_rate."% 포함)";
                            }
                            $meta_method_text = $is_free ? "무료" : ($pay_method_text ? $pay_method_text : ($tx_type=="REFUND" ? "환불" : "카드결제"));
                            $paid_amount = (int)$row['bill_paid_amount'];
                            if($paid_amount < 1) $paid_amount = (int)$row['payment_price'];
                            $is_vat_separate = (strpos((string)$row['plan_vat'], '별도') !== false);
                            $paid_amount_ex_vat = $paid_amount;
                            if($tx_type=="CHARGE" && $paid_amount > 0 && $is_vat_separate){
                                $paid_amount_ex_vat = (int)round($paid_amount / 1.1);
                            }
                            $price_text_display = $is_free ? "무료" : ($pay_method_text ? $pay_method_text : "카드결제");
                            if($tx_type=="CHARGE" && $paid_amount > 0){
                                $price_text_display = "₩".number_format($paid_amount_ex_vat);
                            }else if($tx_type=="REFUND"){
                                $price_text_display = ($paid_amount > 0) ? "-₩".number_format($paid_amount) : "환불";
                            }

                            $status_text = "완료";
                            $status_class = "done";
                            if($is_free){
                                $status_text = "무상지급";
                                $status_class = "free";
                            }else if($tx_type=="REFUND"){
                                $status_text = "결제취소";
                                $status_class = "used";
                            }else if($tx_type=="ADJUST"){
                                $status_text = "관리자조정";
                                $status_class = "used";
                            }

                            $delta_sign = ($amount >= 0 ? "+" : "-");
                            $delta_class = ($amount >= 0 ? "plus" : "minus");
                            if($is_free){
                                $delta_sign = "+";
                                $delta_class = "green";
                            }
                        ?>
                        <div class="pay-card" data-type="<?=$filter_type?>">
                            <div class="pay-card-icon <?=$icon_class?>">
                                <?if($icon_class=="free"){?>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5" rx="1"/><line x1="12" y1="22" x2="12" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 010-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 000-5C13 2 12 7 12 7z"/></svg>
                                <?}else{?>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                                <?}?>
                            </div>
                            <div class="pay-card-info">
                                <div class="title"><?=htmlspecialchars($title, ENT_QUOTES)?></div>
                                <div class="meta">
                                    <?if($is_signup_free){?>
                                    <span><?=substr((string)$row['event_date'],0,10)?></span>
                                    <span class="dot"></span>
                                    <span>Free 플랜 · 서비스 체험용 · 청약철회 제외</span>
                                    <?}else{?>
                                    <span><?=substr((string)$row['event_date'],0,10)?></span>
                                    <span class="dot"></span>
                                    <span><?=htmlspecialchars($meta_credit_text, ENT_QUOTES)?></span>
                                    <span class="dot"></span>
                                    <span><?=htmlspecialchars($meta_method_text, ENT_QUOTES)?></span>
                                    <?}?>
                                </div>
                            </div>
                            <div class="pay-card-right">
                                <span class="credit-delta <?=$delta_class?>"><?=$delta_sign?><?=$delta?></span>
                                <span class="price-text"><?=htmlspecialchars($price_text_display, ENT_QUOTES)?></span>
                                <span class="status-badge <?=$status_class?>"><?=$status_text?></span>
                            </div>
                        </div>
                        <?}?>
                        <?if(!count($payment_rows)){?>
                        <div class="pay-card">
                            <div class="pay-card-info">
                                <div class="title">결제 내역이 없습니다.</div>
                                <div class="meta"><span>결제 또는 지급 이력이 없습니다.</span></div>
                            </div>
                        </div>
                        <?}?>
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
                            <?for($i=0; $i<count($use_rows); $i++){
                                $row = $use_rows[$i];
                                $amount = (float)$row['credit_delta'];
                                $amount_abs = abs($amount);
                                $delta = number_format($amount_abs, 1);
                                if(substr($delta, -2)=='.0') $delta = number_format($amount_abs, 0);

                                $title = $row['memo'] ? $row['memo'] : "[크레딧] 매칭 요청 {$delta} 크레딧 차감";
                                $company_name = trim((string)$row['company_name']);

                                $is_tm_request = (strpos((string)$title, "TM") !== false);
                                $request_type_local = strtoupper(trim((string)$row['request_type']));
                                if(!$request_type_local){
                                    $request_type_local = $is_tm_request ? "TM" : "BASIC";
                                }
                                $service_label = ($request_type_local === "TM") ? "TM 서포트" : "일반 거래";
                                $direction_raw = strtoupper(trim((string)$row['trade_request_direction']));
                                $direction_label = "구매";
                                if($direction_raw === "P") $direction_label = "구매";
                                else if($direction_raw === "S") $direction_label = "판매";
                                else if($direction_raw === "C") $direction_label = "협업";
                                $is_match_request_memo = (
                                    strpos((string)$title, "일반 거래 요청") !== false ||
                                    strpos((string)$title, "TM 서포트 거래 요청") !== false
                                );
                                if($company_name && $is_match_request_memo){
                                    $title = $is_tm_request ? "TM 서포트 거래 요청 — ".$company_name : "일반 거래 요청 — ".$company_name;
                                }
                                $use_type = "match";
                                if(stripos((string)$title, "AI")!==false || stripos((string)$title, "분석")!==false) $use_type = "analyze";
                            ?>
                            <div class="pay-card" data-type="<?=$use_type?>">
                                <div class="pay-card-icon <?=$use_type?>">
                                    <?if($use_type=="analyze"){?>
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l1.5 4.5L18 9l-4.5 1.5L12 15l-1.5-4.5L6 9l4.5-1.5L12 3z"/></svg>
                                    <?}else{?>
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                                    <?}?>
                                </div>
                                <div class="pay-card-info">
                                    <div class="title"><?=htmlspecialchars($title, ENT_QUOTES)?></div>
                                    <div class="meta">
                                        <?if($use_type=="match"){?>
                                        <span><?=substr((string)$row['event_date'],0,10)?></span>
                                        <span class="dot"></span>
                                        <span>거래 방향 : <?=htmlspecialchars($direction_label, ENT_QUOTES)?></span>
                                        <span class="dot"></span>
                                        <span><?=htmlspecialchars($service_label, ENT_QUOTES)?></span>
                                        <?}else{?>
                                        <span><?=substr((string)$row['event_date'],0,10)?></span>
                                        <?if($row['matching_no']){?>
                                        <span class="dot"></span>
                                        <span>매칭번호 <?=htmlspecialchars($row['matching_no'], ENT_QUOTES)?></span>
                                        <?}?>
                                        <?}?>
                                    </div>
                                </div>
                                <div class="pay-card-right">
                                    <span class="credit-delta minus">-<?=$delta?></span>
                                    <span class="status-badge used">사용</span>
                                </div>
                            </div>
                            <?}?>
                            <?if(!count($use_rows)){?>
                            <div class="pay-card">
                                <div class="pay-card-info">
                                    <div class="title">크레딧 사용 내역이 없습니다.</div>
                                    <div class="meta"><span>사용 이력이 없습니다.</span></div>
                                </div>
                            </div>
                            <?}?>
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
                                    <li>AI 분석(1 크레딧)은 동일 기업 기준 2개월 내 재열람 시 크레딧이 차감되지 않습니다.</li>
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
