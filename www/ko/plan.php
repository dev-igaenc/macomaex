<?php
include_once(__DIR__ . '/_common.php');

$board['bo_10'] = '4'; // 1뎁스
$board['bo_9'] = '1'; // 2뎁스

$plan_css_ver = file_exists(__DIR__ . '/css/plan.css') ? filemtime(__DIR__ . '/css/plan.css') : time();
$plan_js_ver = file_exists(__DIR__ . '/js/plan.js') ? filemtime(__DIR__ . '/js/plan.js') : time();
$hide_theme_svisual = true;
$hide_theme_pagetop = true;
add_stylesheet('<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/variable/pretendardvariable-dynamic-subset.min.css">', 180);
add_stylesheet('<link rel="stylesheet" href="/ko/css/plan.css?ver=' . $plan_css_ver . '">', 200);
add_javascript('<script src="/ko/js/plan.js?ver=' . $plan_js_ver . '"></script>', 200);
include_once(G5_THEME_PATH . '/head.php');
$membership_list = get_membership();

require_once(__DIR__ . '/libs/INIStdPayUtil.php');
$signature_util = new INIStdPayUtil();
$timestamp = $signature_util->getTimestamp();
$orderid = (defined('_test_pay') && _test_pay == 'Y') ? 'DemoTest_' . $timestamp : 'M_' . $timestamp;
// membership_credit_pay 요청 검증 토큰
$membership_credit_pay_token = uniqid('', true);
set_session('ss_membership_credit_pay_token', $membership_credit_pay_token);

$signup_bonus = (defined('_new_member_signup_credit') && (int)_new_member_signup_credit > 0)
    ? (int)_new_member_signup_credit
    : 30;
$free_trade_count = $signup_bonus > 0 ? (int)floor($signup_bonus / 10) : 0;

$plan_ui_map = array(
    100 => array(
        'label_en' => 'Starter',
        'label_kr' => '기본형',
        'target' => '소규모 스타트업 · 영업팀',
        'target_icon' => 'users',
        'badge' => '',
        'featured' => false,
        'request_count' => 10,
        'analysis_count' => 200,
    ),
    220 => array(
        'label_en' => 'Professional',
        'label_kr' => '프로페셔널',
        'target' => '중소기업 마케팅 · 영업팀 추천',
        'target_icon' => 'briefcase',
        'badge' => '추천',
        'featured' => true,
        'request_count' => 22,
        'analysis_count' => 440,
    ),
    600 => array(
        'label_en' => 'Business',
        'label_kr' => '비즈니스',
        'target' => '제조 · 유통 기업에 최적',
        'target_icon' => 'monitor',
        'badge' => '',
        'featured' => false,
        'request_count' => 60,
        'analysis_count' => 1200,
    ),
    1300 => array(
        'label_en' => 'Enterprise',
        'label_kr' => '기업형',
        'target' => '대기업 구매팀 전용',
        'target_icon' => 'home',
        'badge' => '',
        'featured' => false,
        'request_count' => 130,
        'analysis_count' => 2600,
    ),
);

$note_items = array(
    '표시 금액은 부가세(VAT 10%) 별도이며, 실제 결제 금액은 부가세 포함 금액으로 청구됩니다',
    '10 크레딧 = 일반 거래 요청 1회 · 30 크레딧 = TM 서포트 거래 요청 1회 · 1 크레딧 = AI 기업 분석 보고서 1회',
    'AI 기업 분석 보고서는 동일 기업 기준 2개월 내 재열람 시 크레딧이 차감되지 않습니다',
    '보너스 크레딧은 기본 크레딧 소진 후 자동 사용되며, 환불 산정 대상에서 제외됩니다',
    '크레딧 유효기간 및 잔여 내역은 고객센터를 통해 확인하실 수 있습니다',
    '거래 수락 시 채팅방이 오픈되며 추가 크레딧은 차감되지 않습니다',
    '결제 후 7일 이내 해당 결제로 충전한 크레딧을 사용하지 않은 경우 전액 환불이 가능합니다. 7일 경과 또는 일부 사용 후에는 환불이 불가합니다.<br>단, 결제 오류·시스템 장애 등 회사 귀책 사유 발생 시 고객센터를 통해 처리해 드립니다',
);

if (!function_exists('plan_icon_svg')) {
    function plan_icon_svg($key)
    {
        switch ($key) {
            case 'gift':
                return <<<'SVG'
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
    <polyline points="20 12 20 22 4 22 4 12"></polyline>
    <rect x="2" y="7" width="20" height="5" rx="1"></rect>
    <line x1="12" y1="22" x2="12" y2="7"></line>
    <path d="M12 7H7.5a2.5 2.5 0 010-5C11 2 12 7 12 7z"></path>
    <path d="M12 7h4.5a2.5 2.5 0 000-5C13 2 12 7 12 7z"></path>
</svg>
SVG;
            case 'users':
                return <<<'SVG'
<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"></path>
    <circle cx="9" cy="7" r="4"></circle>
    <path d="M23 21v-2a4 4 0 00-3-3.87"></path>
    <path d="M16 3.13a4 4 0 010 7.75"></path>
</svg>
SVG;
            case 'briefcase':
                return <<<'SVG'
<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <rect x="2" y="7" width="20" height="14" rx="2"></rect>
    <path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"></path>
</svg>
SVG;
            case 'monitor':
                return <<<'SVG'
<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <rect x="2" y="3" width="20" height="14" rx="2"></rect>
    <path d="M8 21h8"></path>
    <path d="M12 17v4"></path>
</svg>
SVG;
            case 'home':
                return <<<'SVG'
<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
    <polyline points="9 22 9 12 15 12 15 22"></polyline>
</svg>
SVG;
            case 'feature_users':
                return <<<'SVG'
<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"></path>
    <circle cx="9" cy="7" r="4"></circle>
    <path d="M23 21v-2a4 4 0 00-3-3.87"></path>
    <path d="M16 3.13a4 4 0 010 7.75"></path>
</svg>
SVG;
            case 'feature_search':
                return <<<'SVG'
<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <circle cx="11" cy="11" r="8"></circle>
    <path d="M21 21l-4.35-4.35"></path>
    <path d="M11 8v6"></path>
    <path d="M8 11h6"></path>
</svg>
SVG;
            case 'info':
                return <<<'SVG'
<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <circle cx="12" cy="12" r="10"></circle>
    <line x1="12" y1="8" x2="12" y2="12"></line>
    <line x1="12" y1="16" x2="12.01" y2="16"></line>
</svg>
SVG;
            default:
                return '';
        }
    }
}

if (!function_exists('plan_display_title_en')) {
    function plan_display_title_en($rawTitleEn)
    {
        $rawTitleEn = trim((string)$rawTitleEn);
        if ($rawTitleEn === '') {
            return '';
        }

        if ($rawTitleEn === strtoupper($rawTitleEn)) {
            return ucwords(strtolower(str_replace('_', ' ', $rawTitleEn)));
        }

        return str_replace('_', ' ', $rawTitleEn);
    }
}
?>
<div
    id="planWrap"
    data-is-member="<?= $member['mb_id'] ? '1' : '0' ?>"
    data-get-set-url="../get_set.php"
    data-login-url="/bbs/login.php?url=/ko/plan.php"
    data-my-plan-url="/ko/my_plan.php"
    data-credit-pay-url="/ko/credit_pay.php"
    data-membership-credit-pay-token="<?= htmlspecialchars($membership_credit_pay_token, ENT_QUOTES) ?>"
>
    <div class="plan-page">
        <!-- 현재 미사용: 쿠폰 결제 플로우 복구 대비 보존
        <input type="hidden" name="coupon_chk" id="coupon_chk" value="false">
        <input type="hidden" name="coupon_pop" id="coupon_pop" value="false">
        -->
        <input type="hidden" id="plan_orderid" name="orderid" value="<?= htmlspecialchars($orderid, ENT_QUOTES) ?>">

        <div class="plan-hero">
            <span class="eyebrow">PRICING PLAN</span>
            <h1>크레딧 기반 <em>요금제</em></h1>
            <p class="sub">원하는 만큼 충전해서 원하는 기업과 연결하세요</p>
        </div>

        <div class="free-banner">
            <div class="ic"><?= plan_icon_svg('gift') ?></div>
            <div class="txt">
                <div class="name">Free 플랜 · 무료 체험</div>
                <div class="desc">신규 가입 시 <?= number_format($signup_bonus) ?> 크레딧(<?= number_format($free_trade_count) ?>회 거래) 제공 · 서비스 체험용</div>
            </div>
        </div>

        <div class="plan-grid">
            <?php
            $rendered_plan_count = 0;
            foreach ((array)$membership_list as $value) {
                $title_en = strtoupper(trim((string)$value['title_en']));
                $credit_cnt = isset($value['matching_cnt']) ? (int)$value['matching_cnt'] : 0;
                $month = isset($value['month']) ? (int)$value['month'] : 0;

                if ($title_en === 'FREE') {
                    continue;
                }

                if ($credit_cnt < 1 || $month !== 0) {
                    continue;
                }

                $ui = isset($plan_ui_map[$credit_cnt]) ? $plan_ui_map[$credit_cnt] : array();
                $label_en = plan_display_title_en($value['title_en']);
                $label_kr = isset($ui['label_kr']) ? $ui['label_kr'] : trim((string)$value['title']);
                $target = isset($ui['target']) ? $ui['target'] : '기업 맞춤 충전형';
                $target_icon = isset($ui['target_icon']) ? $ui['target_icon'] : 'users';
                $badge = isset($ui['badge']) ? $ui['badge'] : '';
                $featured = !empty($ui['featured']);
                $request_count = (int)floor($credit_cnt / 10);
                $analysis_count = (isset($value['open_cnt']) && (int)$value['open_cnt'] > 0)
                    ? (int)$value['open_cnt']
                    : (isset($ui['analysis_count']) ? (int)$ui['analysis_count'] : ($credit_cnt * 2));
                $bonus_rate = isset($value['sale']) ? (int)$value['sale'] : 0;
                $bonus_label = $bonus_rate > 0 ? '+' . number_format($bonus_rate) . '% 보너스' : '-';
                $base_price = 0.0;
                if (is_numeric($value['price_sale']) && (float)$value['price_sale'] > 0) {
                    $base_price = (float)$value['price_sale'];
                } else if (is_numeric($value['price_origin']) && (float)$value['price_origin'] > 0) {
                    $base_price = (float)$value['price_origin'];
                }

                if ($base_price > 0) {
                    $price_text = number_format($base_price);
                } else {
                    $price_text = trim((string)$value['price_sale']) !== ''
                        ? trim((string)$value['price_sale'])
                        : trim((string)$value['price_origin']);
                }

                $rendered_plan_count++;
                ?>
                <div class="plan-card<?= $featured ? ' is-featured' : '' ?>">
                    <?php if ($badge !== '') { ?>
                        <div class="plan-recommend-badge"><?= htmlspecialchars($badge, ENT_QUOTES) ?></div>
                    <?php } ?>
                    <div class="plan-name"><?= htmlspecialchars($label_en, ENT_QUOTES) ?></div>
                    <div class="plan-price">
                        <span class="currency">₩</span><span class="amount"><?= htmlspecialchars($price_text, ENT_QUOTES) ?></span>
                    </div>
                    <div class="plan-price-label"><?= htmlspecialchars($label_kr, ENT_QUOTES) ?></div>
                    <div class="plan-target">
                        <?= plan_icon_svg($target_icon) ?>
                        <?= htmlspecialchars($target, ENT_QUOTES) ?>
                    </div>
                    <div class="plan-credit-box">
                        <div class="credit-num"><?= number_format($credit_cnt) ?></div>
                        <div class="credit-label">크레딧</div>
                    </div>
                    <span class="plan-bonus<?= $bonus_rate > 0 ? '' : ' is-hidden' ?>"><?= htmlspecialchars($bonus_label, ENT_QUOTES) ?></span>
                    <div class="plan-stats">
                        <div class="plan-feature-row">
                            <?= plan_icon_svg('feature_users') ?>
                            거래 요청 가능 횟수
                            <span class="feat-val"><?= number_format($request_count) ?>회</span>
                        </div>
                        <div class="plan-feature-row">
                            <?= plan_icon_svg('feature_search') ?>
                            기업 AI 분석
                            <span class="feat-credit">최대 <?= number_format($analysis_count) ?>회</span>
                        </div>
                    </div>
                    <div class="mpay">
                        <a
                            href="#"
                            idx="<?= (int)$value['no'] ?>"
                            class="plan-cta <?= $featured ? 'solid' : 'outline' ?> plan_payment"
                        >충전하기</a>
                    </div>
                </div>
            <?php } ?>

            <?php if ($rendered_plan_count < 1) { ?>
                <div class="plan-empty">표시할 크레딧 요금제가 없습니다.</div>
            <?php } ?>
        </div>

        <div class="plan-note">
            <h3>
                <?= plan_icon_svg('info') ?>
                크레딧 이용 안내
            </h3>
            <ul>
                <?php foreach ($note_items as $note_item) { ?>
                    <li><?= $note_item ?></li>
                <?php } ?>
            </ul>
        </div>
    </div>
</div>

<!-- 현재 미사용: 쿠폰 결제 플로우 복구 대비 보존
<div class="popup popup_submit">
    <div class="popupInner">
        <a href="javascript:popupClose('')" class="popupClose"><img src="/img/icon/icon_close.svg" alt=""></a>

        <div class="popup_top">
            <h4 class="popup_top_title">선택한 상품</h4>

            <div class="popup_top_txt1">스탠다드 9,900원</div>
            <div class="popup_top_txt2">1회 결제 / VAT 10% 별도</div>
        </div>

        <div class="popup_content">
            <table>
                <tbody>
                    <tr>
                        <th class="coupon_title">할인쿠폰이 있으신가요?</th>
                        <td>
                            <input type="text" name="coupon" id="coupon" placeholder="ex) ED9A01" maxlength="6" class="coupon_input">
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="popup_btns">
            <button type="submit" id="coupon_submit">다음</button>
        </div>
    </div>
</div>
-->

<?php
include_once(G5_THEME_PATH . '/tail.php');
?>
