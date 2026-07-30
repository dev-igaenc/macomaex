<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (G5_IS_MOBILE) {
    include_once(G5_THEME_MOBILE_PATH.'/head.php');
    return;
}

include_once(G5_THEME_PATH.'/head.sub.php');
include_once(G5_LIB_PATH.'/latest.lib.php');
include_once(G5_LIB_PATH.'/outlogin.lib.php');
include_once(G5_LIB_PATH.'/poll.lib.php');
include_once(G5_LIB_PATH.'/visit.lib.php');
include_once(G5_LIB_PATH.'/connect.lib.php');
include_once(G5_LIB_PATH.'/popular.lib.php');
//header (){Authorization: '[ ACCES TOKEN KEY ]'}

$auth_session_guard_js_ver = file_exists(G5_PATH . '/ko/js/auth-session-guard.js')
    ? filemtime(G5_PATH . '/ko/js/auth-session-guard.js')
    : time();
$auth_session_status_url = '/bbs/ajax.login_status.php';

// 재배포 확인 2026-07-06-2: 헤더 AI 추천 링크 유지
$header_display_name = '';
$header_credit = 0;

if ($is_member) {
    $header_display_name = isset($member['mb_id']) ? trim((string)$member['mb_id']) : '';

    if (function_exists('credit_ledger_get_balance')) {
        $header_credit = (float)credit_ledger_get_balance($member['mb_id']);
    } else {
        $uid_sql = sql_real_escape_string((string)$member['mb_id']);
        $credit_row = sql_fetch("select credit_after from g5_matching_credit_history where uid='{$uid_sql}' order by id desc limit 1");
        if (isset($credit_row['credit_after']) && $credit_row['credit_after'] !== null && $credit_row['credit_after'] !== '') {
            $header_credit = (int)$credit_row['credit_after'];
        }
    }
}
?>

<link rel="stylesheet" href="<?php echo $prix_root_asset_url('ko/css/ai-loading.css', G5_CSS_VER); ?>">
<link rel="stylesheet" href="<?php echo $prix_theme_asset_url('css/design-tokens.css', G5_CSS_VER); ?>">
<link rel="stylesheet" href="<?php echo $prix_theme_asset_url('css/common.layout.css', G5_CSS_VER); ?>">
<link rel="stylesheet" href="<?php echo $prix_theme_asset_url('css/common.scrollbar.css', G5_CSS_VER); ?>">
<link rel="stylesheet" href="<?php echo $prix_theme_asset_url('css/common.ai-magic-wand.css', G5_CSS_VER); ?>">
<link rel="stylesheet" href="<?php echo $prix_theme_asset_url('css/header-core.css', G5_CSS_VER); ?>">
<link rel="stylesheet" href="<?php echo $prix_theme_asset_url('css/header-bridge.css', G5_CSS_VER); ?>">
<script>
window.__AUTH_SESSION_GUARD_CONFIG__ = {
    isMember: <?php echo $is_member ? 'true' : 'false'; ?>,
    authStatusUrl: <?php echo json_encode($auth_session_status_url, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
    pollIntervalMs: 5000,
    redirectDelayMs: 2200
};
</script>
<script src="<?php echo G5_URL; ?>/ko/js/auth-session-guard.js?ver=<?php echo $auth_session_guard_js_ver; ?>"></script>
<?php if (!defined('_INDEX_')) { ?>
<link rel="stylesheet" href="<?php echo $prix_theme_asset_url('css/header-search.css', G5_CSS_VER); ?>">
<?php } ?>
<!-- 상단 시작 { -->
<div id="hd">
	<div id="hd_h1"><?php echo $g5['title'] ?></div>

		<svg xmlns="http://www.w3.org/2000/svg" style="position:absolute;width:0;height:0;">
	  <defs>
	    <!-- 섬유·의류·패션 - 티셔츠 -->
	    <symbol id="cat-iso-1" viewBox="0 0 48 48"><path fill="#5c6bc0" d="M9 17 L18 11 L21 14 Q24 16.5 27 14 L30 11 L39 17 L33 22 L31 20 L31 39 L17 39 L17 20 L15 22 Z"/><path fill="#3f51b5" d="M9 17 L18 11 L17 14 L15 22 Z"/><path fill="#7986cb" d="M39 17 L30 11 L31 14 L33 22 Z"/><path fill="#3949ab" d="M21 14 Q24 16.5 27 14 L25.5 12 Q24 13.2 22.5 12 Z"/></symbol>
	    <!-- 화장품·위생 - 펌프 보틀 -->
	    <symbol id="cat-iso-2" viewBox="-0.6 -0.6 49.2 49.2"><rect fill="#ad1457" x="22" y="8" width="6" height="3" rx="1"/><path fill="#c2185b" d="M22 9 L17 9 L17 11 L22 11 Z"/><rect fill="#ec407a" x="23" y="11" width="4" height="5"/><rect fill="#f06292" x="16" y="16" width="16" height="24" rx="3"/><rect fill="#ec407a" x="16" y="26" width="16" height="7"/><rect fill="#f8bbd0" x="18.5" y="19" width="2.5" height="5" rx="1.2" opacity="0.7"/></symbol>
	    <!-- 플라스틱·고무 - 페트병 -->
	    <symbol id="cat-iso-3" viewBox="-0.6 -0.6 49.2 49.2"><rect fill="#00695c" x="21" y="8" width="6" height="4.5" rx="1"/><path fill="#26a69a" d="M22 12.5 L26 12.5 L26 16 Q31 18 31 23 L31 26 Q28.5 27.5 31 29 L31 37 Q31 40 24 40 Q17 40 17 37 L17 29 Q19.5 27.5 17 26 L17 23 Q17 18 22 16 Z"/><path fill="#80cbc4" d="M20.5 18 L22 18 L22 38 L20.5 38 Z" opacity="0.55"/></symbol>
	    <!-- 식품·음료 - 포크·스푼 -->
	    <symbol id="cat-iso-4" viewBox="0.9 0.65 46.2 46.2"><ellipse fill="#ffb74d" cx="19" cy="14" rx="4" ry="5.5"/><rect fill="#fb8c00" x="17.5" y="18" width="3" height="21" rx="1.5"/><rect fill="#ffb74d" x="26.3" y="9" width="1.6" height="10" rx="0.8"/><rect fill="#ffb74d" x="28.7" y="9" width="1.6" height="10" rx="0.8"/><rect fill="#ffb74d" x="31.1" y="9" width="1.6" height="10" rx="0.8"/><rect fill="#fb8c00" x="26" y="16.5" width="6.7" height="4.5" rx="2"/><rect fill="#fb8c00" x="27.8" y="20" width="3" height="19" rx="1.5"/></symbol>
	    <!-- 화학·케미컬·원료 -->
	    <symbol id="cat-iso-5" viewBox="5.5 5.5 37 37">
	      <circle fill="#e9f5ff" cx="24" cy="24" r="14"/>
	      <rect x="20" y="10" width="8" height="8" rx="2" fill="#455a64"/>
	      <rect x="21" y="11" width="6" height="6" rx="1.5" fill="#607d8b"/>
	      <path d="M18 18 H30 L31.5 24 C32 26 31.6 28.2 30.5 30 L27 35 C26 36.5 25 37 24 37 C23 37 22 36.5 21 35 L17.5 30 C16.4 28.2 16 26 16.5 24 Z" fill="#37474f"/>
	      <path d="M18.5 25 H29.5 C30 27.2 29.6 29 28.7 30.3 L26 33.6 C25.3 34.5 24.6 35 24 35 C23.4 35 22.7 34.5 22 33.6 L19.3 30.3 C18.4 29 18 27.2 18.5 25 Z" fill="#26a69a"/>
	      <path d="M19.5 26.2 C21.2 25.3 22.8 25 24 25 C25.2 25 26.8 25.3 28.5 26.2 L28 27.4 C26.6 26.8 25.3 26.5 24 26.5 C22.7 26.5 21.4 26.8 20 27.4 Z" fill="#4db6ac"/>
	      <circle cx="21" cy="23" r="1.2" fill="#b2ebf2"/>
	      <circle cx="26.5" cy="25.5" r="1" fill="#b2ebf2"/>
	      <circle cx="23.4" cy="21.2" r="0.9" fill="#b2ebf2"/>
	      <path d="M22 19.5 C23 18.8 23.9 18.5 25 18.5 C25.6 18.5 26.2 18.6 26.8 18.8" fill="none" stroke="#ffffff" stroke-width="1.2" stroke-linecap="round" opacity="0.7"/>
	    </symbol>
	    <!-- 기계·설비·산업장비 -->
	    <symbol id="cat-iso-6" viewBox="5.5 5.5 37 37"><circle fill="#edf2f7" cx="24" cy="24" r="14"/><rect fill="#78909c" x="10" y="30" width="28" height="4" rx="1.2"/><path fill="#607d8b" d="M12 26 L36 26 L34 30 L14 30 Z"/><circle fill="#546e7a" cx="20" cy="22" r="6.5"/><circle fill="#cfd8dc" cx="20" cy="22" r="2.4"/><rect fill="#90a4ae" x="19.4" y="12.5" width="1.2" height="3.8" rx="0.6"/><rect fill="#90a4ae" x="19.4" y="27.7" width="1.2" height="3.8" rx="0.6"/><rect fill="#90a4ae" x="10.5" y="21.4" width="3.8" height="1.2" rx="0.6"/><rect fill="#90a4ae" x="25.7" y="21.4" width="3.8" height="1.2" rx="0.6"/><path fill="#90a4ae" d="M13.7 16.6 L14.6 15.7 L17.3 18.4 L16.4 19.3 Z"/><path fill="#90a4ae" d="M23.6 26.5 L24.5 25.6 L27.2 28.3 L26.3 29.2 Z"/><path fill="#90a4ae" d="M23.6 17.5 L26.3 14.8 L27.2 15.7 L24.5 18.4 Z"/><path fill="#90a4ae" d="M13.7 27.4 L16.4 24.7 L17.3 25.6 L14.6 28.3 Z"/><circle fill="#455a64" cx="31.5" cy="27.5" r="4.2"/><circle fill="#b0bec5" cx="31.5" cy="27.5" r="1.5"/><path fill="#5f6f7a" d="M30 19 L35.5 13.5 L37.2 15.2 L31.7 20.7 Z"/></symbol>
	    <!-- 금속·소재 -->
	    <symbol id="cat-iso-7" viewBox="5.5 5.5 37 37">
	      <circle cx="24" cy="24" r="14" fill="#ECEFF1"/>
	      <path d="M14 20 L32 16 L36 22 L18 26 Z" fill="#B0BEC5"/>
	      <path d="M14 20 L14 28 L18 34 L18 26 Z" fill="#90A4AE"/>
	      <path d="M18 26 L36 22 L36 30 L18 34 Z" fill="#78909C"/>
	      <path d="M15.5 20.8 L31.5 17.4 L34.2 21.4 L18.2 24.8 Z" fill="#CFD8DC"/>
	      <circle cx="19.5" cy="23.5" r="0.9" fill="#ECEFF1"/>
	      <circle cx="25.5" cy="22.3" r="0.9" fill="#ECEFF1"/>
	      <circle cx="30.8" cy="21.3" r="0.9" fill="#ECEFF1"/>
	      <circle cx="21.2" cy="28.4" r="0.9" fill="#ECEFF1"/>
	      <circle cx="27.5" cy="26.9" r="0.9" fill="#ECEFF1"/>
	      <path d="M19 30 H33" stroke="#B0BEC5" stroke-width="0.8" stroke-linecap="round" opacity="0.8"/>
	      <path d="M19.5 31.6 H31.5" stroke="#B0BEC5" stroke-width="0.7" stroke-linecap="round" opacity="0.7"/>
	    </symbol>
	    <!-- 비금속·요업 - 벽돌 -->
	    <symbol id="cat-iso-8" viewBox="2.5 3.5 43 43"><rect fill="#c1694a" x="13" y="15" width="10" height="6" rx="1"/><rect fill="#a85638" x="24" y="15" width="11" height="6" rx="1"/><rect fill="#a85638" x="9" y="22" width="9" height="6" rx="1"/><rect fill="#c1694a" x="19" y="22" width="11" height="6" rx="1"/><rect fill="#8d6e63" x="31" y="22" width="8" height="6" rx="1"/><rect fill="#a85638" x="13" y="29" width="10" height="6" rx="1"/><rect fill="#c1694a" x="24" y="29" width="11" height="6" rx="1"/></symbol>
	    <!-- 소매·전자상거래 - 쇼핑백 -->
	    <symbol id="cat-iso-9" viewBox="0 0 48 48"><path fill="#ff8f00" d="M18 16 Q18 9 24 9 Q30 9 30 16 L27 16 Q27 11.5 24 11.5 Q21 11.5 21 16 Z"/><path fill="#ffb300" d="M13.5 15 L34.5 15 L33 38 Q32.8 40 30.8 40 L17.2 40 Q15.2 40 15 38 Z"/><path fill="#ffa000" d="M13.5 15 L34.5 15 L34.2 20 L13.8 20 Z"/></symbol>
	    <!-- 전자·전기·반도체 -->
	    <symbol id="cat-iso-10" viewBox="5.5 5.5 37 37"><circle fill="#eaf2ff" cx="24" cy="24" r="14"/><rect fill="#1e88e5" x="16" y="16" width="16" height="16" rx="2"/><rect fill="#90caf9" x="20" y="20" width="8" height="8" rx="1.2"/><path fill="#42a5f5" d="M14 19 H16 V21 H14 Z M14 23 H16 V25 H14 Z M14 27 H16 V29 H14 Z M32 19 H34 V21 H32 Z M32 23 H34 V25 H32 Z M32 27 H34 V29 H32 Z M19 14 H21 V16 H19 Z M23 14 H25 V16 H23 Z M27 14 H29 V16 H27 Z M19 32 H21 V34 H19 Z M23 32 H25 V34 H23 Z M27 32 H29 V34 H27 Z"/><path fill="#ffd54f" d="M24 18 L27.5 23 H24.8 L27 29 L21 24.5 H23.8 L22 18 Z"/></symbol>
	    <!-- 자동차·모빌리티 -->
	    <symbol id="cat-iso-11" viewBox="5.2 6.9 37.6 37.6"><rect fill="#e1f5fe" x="8" y="20" width="32" height="12" rx="4"/><path fill="#29b6f6" d="M14 18 L34 18 L38 22 L10 22 Z"/><circle fill="#37474f" cx="16" cy="30" r="3.5"/><circle fill="#37474f" cx="32" cy="30" r="3.5"/><rect fill="#ffffff" x="16" y="19" width="8" height="4" rx="1"/><rect fill="#ffffff" x="24" y="19" width="8" height="4" rx="1"/></symbol>
	    <!-- 포장재·소모성 자재 -->
	    <symbol id="cat-iso-12" viewBox="0.9 1.9 46.2 46.2"><path fill="#ede7f6" d="M10 16 L38 16 L38 34 L10 34 Z"/><path fill="#d1c4e9" d="M10 16 L24 10 L38 16 L24 22 Z"/><path fill="#b39ddb" d="M24 22 L38 16 L38 34 L24 40 Z"/><path fill="#9575cd" d="M24 22 L10 16 L10 34 L24 40 Z"/><path fill="#7e57c2" d="M20 18 L24 20 L28 18 L24 16 Z"/></symbol>
	    <!-- 전기장비·2차전지 - 배터리 -->
	    <symbol id="cat-iso-13" viewBox="0.9 0.9 46.2 46.2"><rect fill="#2e7d32" x="20.5" y="9" width="7" height="3.5" rx="1.2"/><rect fill="#66bb6a" x="16" y="12" width="16" height="27" rx="3"/><rect fill="#a5d6a7" x="18.5" y="14.5" width="11" height="22" rx="1.5"/><path fill="#f9a825" d="M25.5 16 L19.5 27.5 L23.2 27.5 L22 36 L28.5 23.5 L24.6 23.5 Z"/></symbol>
	    <!-- 가구·리빙·잡화 - 소파 -->
	    <symbol id="cat-iso-14" viewBox="1.8 2.8 44.4 44.4"><path fill="#1e88e5" d="M11 18 Q11 15 14 15 L34 15 Q37 15 37 18 L37 24 L11 24 Z"/><rect fill="#1565c0" x="8" y="21" width="6" height="12" rx="2.5"/><rect fill="#1565c0" x="34" y="21" width="6" height="12" rx="2.5"/><rect fill="#42a5f5" x="13" y="23" width="22" height="8" rx="2"/><rect fill="#0d47a1" x="12" y="32" width="3" height="3" rx="0.5"/><rect fill="#0d47a1" x="33" y="32" width="3" height="3" rx="0.5"/></symbol>
	    <!-- 통신 -->
	    <symbol id="cat-iso-15" viewBox="0 0 48 48"><path fill="#5c6bc0" d="M24 8 L26 8 L26 40 L24 40 Z"/><path fill="#3f51b5" d="M18 16 L20 16 L20 40 L18 40 Z"/><path fill="#7986cb" d="M30 16 L32 16 L32 40 L30 40 Z"/><path fill="#3949ab" d="M12 24 L14 24 L14 40 L12 40 Z"/><path fill="#5c6bc0" d="M36 24 L38 24 L38 40 L36 40 Z"/></symbol>
	    <!-- 토목 -->
	    <symbol id="cat-iso-16" viewBox="-0.3 0.7 48.6 48.6"><path fill="#78909c" d="M8 28 L40 28 L40 32 L8 32 Z"/><path fill="#546e7a" d="M8 28 L8 32 L6 34 L6 30 Z"/><path fill="#90a4ae" d="M40 28 L40 32 L42 34 L42 30 Z"/><path fill="#607d8b" d="M12 20 L16 20 L16 28 L12 28 Z"/><path fill="#455a64" d="M32 20 L36 20 L36 28 L32 28 Z"/><path fill="#78909c" d="M20 16 L28 16 L28 28 L20 28 Z"/></symbol>
	    <!-- 의료·헬스케어 -->
	    <symbol id="cat-iso-17" viewBox="5.5 5.5 37 37"><circle fill="#ffebee" cx="24" cy="24" r="14"/><circle fill="#e53935" cx="24" cy="24" r="9"/><path fill="#ffffff" d="M23 18 L25 18 L25 22 L29 22 L29 24 L25 24 L25 28 L23 28 L23 24 L19 24 L19 22 L23 22 Z"/></symbol>
	    <!-- 인력 -->
	    <symbol id="cat-iso-18" viewBox="4 3 40 40"><circle fill="#66bb6a" cx="24" cy="16" r="6"/><path fill="#4caf50" d="M16 28 Q16 24 20 24 L28 24 Q32 24 32 28 L32 36 L16 36 Z"/><path fill="#43a047" d="M18 30 L30 30 L30 36 L18 36 Z"/></symbol>
	    <!-- 정밀·측정·광학 - 돋보기 -->
	    <symbol id="cat-iso-19" viewBox="4 4 42 42"><circle fill="#ce93d8" cx="21" cy="21" r="10"/><circle fill="#f3e5f5" cx="21" cy="21" r="6.5" opacity="0.55"/><circle fill="none" stroke="#9c27b0" stroke-width="3" cx="21" cy="21" r="10"/><path d="M28.5 28.5 L37 37" fill="none" stroke="#7b1fa2" stroke-width="4.5" stroke-linecap="round"/></symbol>
	    <!-- 공통 아이콘 - 요술봉 -->
	    <symbol id="icon-magic-wand" viewBox="0 0 24 24"><path fill="currentColor" d="M16.8 2.2l1.1 2.3 2.3 1.1-2.3 1.1-1.1 2.3-1.1-2.3-2.3-1.1 2.3-1.1 1.1-2.3zm3.2 2.8.6 1.1 1.1.6-1.1.6-.6 1.1-.6-1.1-1.1-.6 1.1-.6.6-1.1zM7.4 17.4 16.1 8.7l2.6 2.6-8.7 8.7-2.6-2.6zm-1 2.4 1.3 1.3-3.1 1 .8-2.9z"/></symbol>
	    <!-- 전체보기 -->
	    <symbol id="cat-iso-all" viewBox="0 0 48 48"><path fill="#2196f3" d="M8 8 L26 8 L26 26 L8 26 Z"/><path fill="#ffc107" d="M28 8 L46 8 L46 26 L28 26 Z"/><path fill="#4caf50" d="M8 28 L26 28 L26 46 L8 46 Z"/><path fill="#e53935" d="M28 28 L46 28 L46 46 L28 46 Z"/></symbol>
	  </defs>
	</svg>

	<?php if(defined('_INDEX_')) { // index에서만 실행
		include G5_BBS_PATH.'/newwin.inc.php'; // 팝업레이어
	} ?>

	<div id="header" class="hh"><!-- class="hh" --><p class="gnbBg"></p><div class="inConts">
			<div class="header-left">
				<h1><a href="<?php echo rtrim(G5_URL, '/'); ?>/">
					<img src="/img/logo.svg" onerror="this.onerror=null;this.src='/img/logo.png';" alt="<?php echo $config['cf_title']; ?>" class="list-header_logo_img">
				</a></h1>

			<?php if(!defined('_INDEX_')) { ?>
				<div id="hd_search">
					<form action="/ko/matching_list.php" method="POST" id="match_hd" class="header-search-form">
						<input type="hidden" name="cominfo" id="cominfo_hd">
						<input type="text" name="comnum" id="comnum_hd" class="comnum_chk header-search-input" placeholder="사업자등록번호를 입력해보세요" maxlength="12" value="<?php echo isset($_REQUEST['comnum']) ? htmlspecialchars($_REQUEST['comnum']) : ''; ?>">
						<button type="button" id="hd_submit" class="search_btn header-search-btn" aria-label="AI 추천받기">
							<span class="sparkle ai-magic-wand"><svg viewBox="0 0 24 24"><use href="#icon-magic-wand"></use></svg></span>
							<span>AI 추천받기</span>
						</button>
					</form>
				</div>
			<?php } ?>
		</div>

		<div class="right-utill">
            <?php if (!$is_member) {  ?>
				<a href="<?php echo G5_BBS_URL ?>/login.php?url=<?=$_SERVER['REQUEST_URI']?>" class="menu_txt">로그인</a>
				<a href="/signup" class="menu_txt">회원가입</a>
			<?php }else{  ?>
				<a href="<?php echo G5_BBS_URL ?>/logout.php" class="menu_txt">로그아웃</a>
				<?if($member['mb_level']>9) {?>
				<a href="/backoffice/" class="menu_txt"><span>관리자</span></a>
				<?}else{?>
				<!-- <a href="<?php echo G5_BBS_URL ?>/member_confirm.php?url=/bbs/register_form.php" class="menu_txt"><span>마이페이지</span></a> -->
				<?}?>
			<?php }  ?>

			<button type="button" class="mMenu"><span></span></button>
		</div>
	</div></div>

    <div id="sideMenuArea" inert aria-hidden="true">
        <div id="sideMenu">
            <div class="sideMenu_top">
                <div class="logo"><img src="/img/logo.svg" onerror="this.onerror=null;this.src='/img/logo.png';" alt="<?php echo $config['cf_title']; ?>" style="height:24px;width:auto;display:block;"></div>
                <button type="button" class="sideMenu_close_btn" aria-label="닫기">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" width="16" height="16">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <?php if ($is_member): ?>
            <div class="sideMenu_user_wrap">
                <p class="sideMenu_user_greeting"><?php echo htmlspecialchars($header_display_name); ?> 님</p>
                <div class="sideMenu_plan_card">
                    <a href="/plans" class="sideMenu_plan_card_title">
                        <span>크레딧 충전하기</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" width="15" height="15"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                    <div class="sideMenu_plan_card_body">
                        <div class="sideMenu_plan_row">
                            <span class="sideMenu_plan_label">보유 크레딧</span>
                            <span class="sideMenu_plan_value"><?php echo number_format($header_credit); ?> <em class="sideMenu_plan_unit">cr</em></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

			<ul class="all_menu all_layout">
                <li class="one">
                    <a href="<?php echo rtrim(G5_URL, '/'); ?>/">
                        <span class="snav-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg></span>홈
                    </a>
                </li>
                <li class="one">
                    <a href="<?php echo $link_0302; ?>">
                        <span class="snav-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"></rect><rect x="14" y="3" width="7" height="7" rx="1.5"></rect><rect x="3" y="14" width="7" height="7" rx="1.5"></rect><rect x="14" y="14" width="7" height="7" rx="1.5"></rect></svg></span>기업 목록
                    </a>
                </li>
                <li class="one">
                    <a href="/recommendations/demo?entry=matching_ai_demo">
                        <span class="snav-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg></span>AI 추천
                    </a>
                </li>

                <li class="side-divider"></li>

                <!-- about 임시 비활성화
				<li class="one">
                    <a href="<?php echo $link_0101; ?>">
                        <span class="snav-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg></span>매커멕스란
                    </a>
                </li>
				-->

                <li class="one">
                    <a href="<?php echo $link_0401; ?>">
                        <span class="snav-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 16L3 6l5.5 5L12 2l3.5 9L21 6l-2 10H5z"></path><line x1="5" y1="20" x2="19" y2="20"></line></svg></span>크레딧 요금제
                    </a>
                </li>

                <li class="side-divider"></li>

                <li class="side-toggle" data-group="support" tabindex="0" role="button" aria-expanded="false">
                    <span class="side-toggle-left">
                        <span class="snav-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><path d="M12 8v4M12 16h.01"></path></svg></span>
                        이용자 지원
                    </span>
                    <span class="side-chev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><polyline points="6 9 12 15 18 9"></polyline></svg></span>
                </li>
                <li class="sub one" data-gi="support" style="display:none"><a href="<?php echo $link_0501; ?>">공지사항</a></li>
                <li class="sub one" data-gi="support" style="display:none"><a href="<?php echo $link_0502; ?>">자주 묻는 질문</a></li>
                <li class="sub one" data-gi="support" style="display:none"><a href="<?php echo $link_0503; ?>">고객문의</a></li>

                <li class="side-divider"></li>

                <li class="side-toggle" data-group="mypage" tabindex="0" role="button" aria-expanded="false">
                    <span class="side-toggle-left">
                        <span class="snav-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg></span>
                        마이페이지
                    </span>
                    <span class="side-chev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><polyline points="6 9 12 15 18 9"></polyline></svg></span>
                </li>
                <li class="sub one" data-gi="mypage" style="display:none"><a href="<?php echo $link_0601; ?>">내 회사 정보</a></li>
                <li class="sub one" data-gi="mypage" style="display:none"><a href="<?php echo $link_0602; ?>">거래 히스토리</a></li>
                <li class="sub one" data-gi="mypage" style="display:none"><a href="<?php echo $link_0603; ?>">관심기업</a></li>
                <li class="sub one" data-gi="mypage" style="display:none"><a href="<?php echo $link_0604; ?>">회원정보수정</a></li>
                <li class="sub one" data-gi="mypage" style="display:none"><a href="<?php echo $link_0605; ?>">결제정보</a></li>
                <li class="sub one" data-gi="mypage" style="display:none"><a href="<?php echo $link_0607; ?>">문의 내역</a></li>
            </ul>
			<div class="all_user">
				<?php if (!$is_member) { ?>
					<a href="<?php echo G5_BBS_URL ?>/login.php?url=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="side-auth side-login">로그인</a>
					<a href="/signup" class="side-auth side-signup">회원가입</a>
				<?php } else { ?>
					<a href="<?php echo G5_BBS_URL ?>/logout.php?url=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="side-auth side-login">로그아웃</a>
				<?php } ?>
			</div>
        </div>
    </div>
    <script>
    (function() {
        var closeBtn = document.querySelector('#sideMenuArea .sideMenu_close_btn');
        if (closeBtn) {
            closeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (typeof sideMenuClose === 'function') {
                    sideMenuClose();
                    return;
                }
                document.documentElement.classList.remove('hide');
                document.querySelectorAll('.mMenu').forEach(function(el) { el.classList.remove('open'); });
                var area = document.getElementById('sideMenuArea');
                if (area) {
                    area.classList.remove('open');
                    area.setAttribute('inert', '');
                    area.setAttribute('aria-hidden', 'true');
                }
                var legacyMenuArea = document.getElementById('menuArea');
                if (legacyMenuArea && !legacyMenuArea.classList.contains('open')) {
                    legacyMenuArea.setAttribute('inert', '');
                    legacyMenuArea.setAttribute('aria-hidden', 'true');
                }
            });
        }

        document.querySelectorAll('#sideMenuArea .side-toggle').forEach(function(toggle) {
            function syncToggle(nextOpen) {
                var group = toggle.getAttribute('data-group');
                var subs = document.querySelectorAll('#sideMenuArea .sub[data-gi="' + group + '"]');
                toggle.classList.toggle('open', nextOpen);
                toggle.setAttribute('aria-expanded', nextOpen ? 'true' : 'false');
                subs.forEach(function(item) {
                    item.style.display = nextOpen ? 'block' : 'none';
                });
            }

            toggle.addEventListener('click', function() {
                var isOpen = toggle.classList.contains('open');
                syncToggle(!isOpen);
            });

            toggle.addEventListener('keydown', function(e) {
                var key = e.key || e.keyCode;
                if (key !== 'Enter' && key !== ' ' && key !== 'Spacebar' && key !== 13 && key !== 32) return;
                e.preventDefault();
                var isOpen = toggle.classList.contains('open');
                syncToggle(!isOpen);
            });
        });
    })();
    </script>

	<div id="menuArea" class="menuLayer" inert aria-hidden="true">
		<div id="menu" class="gnb">
            <ul class="all_user all_layout">
                <?php if (!$is_member) {  ?>
                    <li class="one"><a href="<?php echo G5_BBS_URL ?>/login.php?url=<?=$_SERVER['REQUEST_URI']?>" class="menu_txt">로그인</a></li>
                    <li class="one"><a href="/signup" class="menu_txt">회원가입</a></li>
                <?php }else{  ?>
                    <li class="one"><a href="<?php echo G5_BBS_URL ?>/logout.php?url=<?=$_SERVER['REQUEST_URI']?>" class="menu_txt">로그아웃</a></li>

                    <?if($member['mb_level']>9) {?>
                    <li class="one"><a href="/backoffice/" class="menu_txt"><span>관리자</span></a></li>
                    <?}else{?>
                    <?}?>
                <?php }  ?>
            </ul>

			<ul class="all_menu all_layout">
                <script type="text/javascript">globalNavigationBar();</script>
            </ul>
			<?php //include(G5_THEME_PATH.'/menu.php'); ?>
		</div>
	</div>
</div>
<!-- } 상단 끝 -->

<hr>

<!-- 콘텐츠 시작 { -->
<div id="wrapper"<?php echo !empty($theme_wrapper_inline_style) ? ' style="'.htmlspecialchars($theme_wrapper_inline_style, ENT_QUOTES, 'UTF-8').'"' : ''; ?>>
	<?php if (!defined("_INDEX_")) { ?>
	<div id="container_wr"<?php echo !empty($theme_container_wr_inline_style) ? ' style="'.htmlspecialchars($theme_container_wr_inline_style, ENT_QUOTES, 'UTF-8').'"' : ''; ?>>


			<?php if (empty($hide_theme_svisual)) { ?>
				<?php if($depth_1 == 3){ ?>
					<div id="sVisual" class="bg_<?=$depth_1?>" style="display: none;">

					<?php if (!empty($sub_visual_video)) { ?>
	    <video class="svisual-video" autoplay muted loop playsinline preload="metadata">
	        <source src="<?= $sub_visual_video ?>" type="video/mp4">
	    </video>
					<?php } ?>
					<h2>
						<strong data-aos="fade-down"><?=$gr_subject?></strong>
						<span data-aos="fade-up" data-aos-delay="100"><?=$gr_text?></span>
					</h2>

					<div class="matching mVisual_search bxShadow">
						<form action="/ko/matching_list.php" method="POST" id="match">
						<input type="hidden" name="cominfo" id="cominfo">
						</form>
						<input type="text" name="comnum" id="comnum" class="comnum_chk" placeholder="법인 사업자번호를 입력하세요" maxlength="12">
						<button type="submit" id="sub_submit">확인</button>
					</div>
					</div>
				<?php }else{ ?>
					<div id="sVisual" class="bg_<?=$depth_1?>">
					<?php if (!empty($sub_visual_video)) { ?>
	    <video class="svisual-video" autoplay muted loop playsinline preload="metadata">
	        <source src="<?= $sub_visual_video ?>" type="video/mp4">
	    </video>
	<?php } ?>
					<h2>
						<strong data-aos="fade-down"><?=$gr_subject?></strong>
						<span data-aos="fade-up" data-aos-delay="100"><?=$gr_text?></span>
					</h2>
					</div>
				<?php } ?>
			<?php } ?>

		<!-- <div id="snb" <?php if($board['bo_7'] != ""){ ?>class="four"<?php }?>><div class="inConts">
			<ul id="nav">
				<script type="text/javascript">
					var dn1 = "<?=$depth_1?>";
					var dn2 = "<?=$depth_2?>";
					var dn3 = "<?=$depth_3?>";
					var dn4 = "<?=$depth_4?>";

					//alert(dn1+"__"+dn2+"__"+dn3+"__"+dn4)
					leftMenuList(dn1, dn2, dn3, dn4);
				</script>
			</ul>
		</div></div> -->
			<?php if (empty($hide_theme_pagetop)) { ?>
			<div id="pageTop">
	            <div class="inConts">
                <div class="pageTop_text">
                    <h3><?php echo $bo_subject ?></h3>
                    <?php if($bo_text != ""){?>
                        <p><?php echo $bo_text ?></p>
                    <?php } else { ?>
                        <p></p>
                    <?php } ?>
                </div>
                <?php if (empty($hide_theme_pagetop_nav)) { ?>
                <div class="pageTop_nav">
                    <script type="text/javascript">
                        var dn1 = "<?=$depth_1?>";
                        var dn2 = "<?=$depth_2?>";
                        var dn3 = "<?=$depth_3?>";
                        var dn4 = "<?=$depth_4?>";

                        //alert(dn1+"__"+dn2+"__"+dn3+"__"+dn4)
                        leftMenuList(dn1, dn2, dn3, dn4);
                    </script>
                </div>
                <?php } ?>
            </div>
			</div>
			<?php } ?>
			<div id="container"<?php echo !empty($theme_container_inline_style) ? ' style="'.htmlspecialchars($theme_container_inline_style, ENT_QUOTES, 'UTF-8').'"' : ''; ?>>
	<?php } ?>

<!-- 사업자 확인 + 추천 키워드 선택 모달 -->
<div class="modal_verify_overlay" id="modal_verify_overlay" aria-hidden="true">
    <div class="modal_verify modal_verify_wide" role="dialog" aria-labelledby="modal_verify_company" aria-modal="true">
        <button type="button" class="modal_verify_close" id="modal_verify_btn_close" aria-label="닫기">×</button>

        <div class="modal_verify_compact">
            <span class="modal_verify_check_sm" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"></path></svg>
            </span>
            <div class="modal_verify_compact_info">
                <strong class="modal_verify_company_sm" id="modal_verify_company"></strong>
                <span class="modal_verify_desc_sm">귀사는 <span id="modal_verify_bzc"></span> · <span id="modal_verify_type"></span>으로 확인되었습니다.</span>
            </div>
        </div>

        <div id="modal_verify_pd_wrap">
            <div class="modal_verify_pd_title_row">
                <p class="modal_verify_pd_title">찾고 싶은 거래품목을 선택해주세요</p>
                <span class="modal_verify_pd_title_hint">OEM 협업포함</span>
            </div>
            <div class="modal_pd_trade_toggle">
                <button type="button" class="modal_pd_trade_btn active" id="modal_pd_trade_buy">
                    <svg class="modal_pd_trade_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                    </svg>
                    구매처 찾기
                </button>
                <button type="button" class="modal_pd_trade_btn" id="modal_pd_trade_sell">
                    <svg class="modal_pd_trade_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="1" y="3" width="15" height="13" rx="1"></rect>
                        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                        <circle cx="5.5" cy="18.5" r="2.5"></circle>
                        <circle cx="18.5" cy="18.5" r="2.5"></circle>
                    </svg>
                    판매처 찾기
                </button>
            </div>
            <p class="modal_pd_trade_desc" id="modal_pd_trade_desc">귀사가 구매하려는 품목을 선택하세요 <span class="modal_pd_multi_hint">복수 선택 가능</span></p>
            <div class="modal_pd_grid" id="modal_pd_grid" aria-live="polite">
                <span class="modal_pd_empty">추천 키워드를 불러올 준비 중입니다.</span>
            </div>
            <div class="modal_pd_custom">
                <p class="modal_pd_custom_title">찾는 품목이 없나요? 직접 입력해보세요</p>
                <div class="modal_pd_custom_row">
                    <input type="text" class="modal_pd_custom_input" id="modal_pd_custom_input" maxlength="30" autocomplete="off" placeholder="품목명을 입력하세요" aria-controls="modal_pd_custom_results" aria-autocomplete="list">
                    <button type="button" class="modal_pd_custom_add" id="modal_pd_custom_add" disabled><span aria-hidden="true">+</span> 추가</button>
                </div>
                <div class="modal_pd_custom_results" id="modal_pd_custom_results" role="listbox" aria-live="polite" hidden></div>
            </div>
            <div class="modal_pd_bar">
                <div class="modal_pd_bar_info">
                    <span><b id="modal_pd_count">0</b>개 선택됨</span>
                    <div id="modal_pd_preview"><span class="modal_pd_empty">품목을 선택하면 여기에 표시됩니다</span></div>
                </div>
                <button type="button" class="modal_pd_btn_confirm" id="modal_pd_btn_confirm" disabled>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"></path></svg>
                    AI 거래처 추천받기
                </button>
            </div>
            <button type="button" class="modal_pd_skip" id="modal_verify_btn_ai">건너뛰고 전체 추천받기</button>
        </div>
    </div>
</div>

<!-- 사업자 미입력 안내 모달 -->
<div class="modal_verify_overlay" id="modal_ai_guide_overlay" aria-hidden="true">
    <div class="modal_verify" role="dialog" aria-labelledby="modal_ai_guide_title" aria-modal="true">
        <button type="button" class="modal_verify_close" id="modal_ai_guide_btn_close" aria-label="닫기">×</button>
        <div class="modal_verify_icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M12 17v-5M12 8h.01M4 12a8 8 0 1 0 16 0 8 8 0 1 0-16 0z"></path></svg>
        </div>
        <h2 class="modal_verify_company" id="modal_ai_guide_title">AI 추천 안내</h2>
        <p class="modal_verify_text" id="modal_ai_guide_text">사업자 번호를 입력하고 AI 추천받기를 눌러주세요.</p>
        <p class="modal_verify_text2" id="modal_ai_guide_text2">AI 분석을 통해 업종/거래 데이터를 기반으로 적합한 거래처 판매처를 추천해드립니다.</p>
        <div class="modal_verify_btn_row">
            <button type="button" class="modal_verify_btn modal_verify_btn_secondary" id="modal_ai_guide_btn_secondary" hidden>바로가기</button>
            <button type="button" class="modal_verify_btn" id="modal_ai_guide_btn_ok">확인했어요</button>
        </div>
    </div>
</div>

<!-- AI 분석 로딩 오버레이 -->
<div class="ai-loading_overlay" id="aiLoadingOverlay">
    <div class="ai-loading_card">
        <div class="ai-loading_spinner"></div>
        <h3 class="ai-loading_title">AI가 거래처를 분석하고 있습니다</h3>
        <div class="ai-loading_steps">
            <div class="ai-loading_step" data-step="0">
                <span class="ai-loading_step_icon"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"></path></svg></span>
                <span class="ai-loading_step_text" data-pending="공급망 근접도 분석 중..." data-done="공급망 근접도 분석 완료 ✓">공급망 근접도 분석 중...</span>
            </div>
            <div class="ai-loading_step" data-step="1">
                <span class="ai-loading_step_icon"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"></path></svg></span>
                <span class="ai-loading_step_text" data-pending="거래처 유사성 분석 중..." data-done="거래처 유사성 분석 완료 ✓">거래처 유사성 분석 중...</span>
            </div>
            <div class="ai-loading_step" data-step="2">
                <span class="ai-loading_step_icon"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"></path></svg></span>
                <span class="ai-loading_step_text" data-pending="거래 활성도 분석 중..." data-done="거래 활성도 분석 완료 ✓">거래 활성도 분석 중...</span>
            </div>
            <div class="ai-loading_step" data-step="3">
                <span class="ai-loading_step_icon"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"></path></svg></span>
                <span class="ai-loading_step_text" data-pending="재무 안정성 분석 중..." data-done="재무 안정성 분석 완료 ✓">재무 안정성 분석 중...</span>
            </div>
            <div class="ai-loading_step" data-step="4">
                <span class="ai-loading_step_icon"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"></path></svg></span>
                <span class="ai-loading_step_text" data-pending="지역 접근성 분석 중..." data-done="지역 접근성 분석 완료 ✓">지역 접근성 분석 중...</span>
            </div>
            <div class="ai-loading_step" data-step="5">
                <span class="ai-loading_step_icon"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"></path></svg></span>
                <span class="ai-loading_step_text" data-pending="규모 적합도 분석 중..." data-done="규모 적합도 분석 완료 ✓">규모 적합도 분석 중...</span>
            </div>
        </div>
        <div class="ai-loading_bar"><div class="ai-loading_bar_fill" id="aiLoadingBarFill"></div></div>
    </div>
</div>

	<script>
// 모달 열기/닫기 함수들
function syncVerifyModalPageState() {
	var hasOpenModal = document.querySelector(".modal_verify_overlay.is_open") !== null;
	var html = document.documentElement;
	var body = document.body;
	if (html) {
		if (hasOpenModal) html.classList.add("modal_verify_open");
		else html.classList.remove("modal_verify_open");
	}
	if (body) {
		if (hasOpenModal) body.classList.add("modal_verify_open");
		else body.classList.remove("modal_verify_open");
	}
}

function focusVerifyModal(overlay) {
	if (!overlay) return;
	window.setTimeout(function() {
		var focusTarget = overlay.querySelector(".modal_verify_close, button:not([disabled])");
		if (focusTarget && typeof focusTarget.focus === "function") focusTarget.focus();
	}, 0);
}

function openAiGuideModal(opts) {
	var overlay = document.getElementById("modal_ai_guide_overlay");
	var titleEl = document.getElementById("modal_ai_guide_title");
	var textEl = document.getElementById("modal_ai_guide_text");
	var text2El = document.getElementById("modal_ai_guide_text2");
	var okBtn = document.getElementById("modal_ai_guide_btn_ok");
	var secondaryBtn = document.getElementById("modal_ai_guide_btn_secondary");
	if (!overlay) return;
	opts = opts || {};
	if (titleEl) titleEl.textContent = opts.title || "AI 추천 안내";
	if (textEl) textEl.textContent = opts.text || "사업자 번호를 입력하고 AI 추천받기를 눌러주세요.";
	if (text2El) text2El.textContent = opts.text2 || "AI 분석을 통해 업종/거래 데이터를 기반으로 적합한 거래처 판매처를 추천해드립니다.";
	if (okBtn) {
		okBtn.textContent = opts.okText || "확인했어요";
		okBtn.classList.remove("modal_verify_btn_outline");
		if (opts.okVariant === "outline") {
			okBtn.classList.add("modal_verify_btn_outline");
		}
	}
	if (secondaryBtn) {
		var forceHideSecondary = !!opts.hideSecondary;
		var secondaryText = (opts.secondaryText || "").trim();
		if (!forceHideSecondary && secondaryText) {
			secondaryBtn.hidden = false;
			secondaryBtn.style.display = "";
			secondaryBtn.textContent = secondaryText;
		} else {
			secondaryBtn.hidden = true;
			secondaryBtn.style.display = "none";
			secondaryBtn.textContent = "바로가기";
		}
	}
	window._aiGuideModalState = {
		onOk: (typeof opts.onOk === "function") ? opts.onOk : null,
		onSecondary: (typeof opts.onSecondary === "function") ? opts.onSecondary : null,
		secondaryHref: opts.secondaryHref || "",
		kind: opts.kind || ""
	};
	overlay.classList.add("is_open");
	overlay.setAttribute("aria-hidden", "false");
	syncVerifyModalPageState();
	focusVerifyModal(overlay);
}

function openCreditShortageGuide(opts) {
	var options = opts || {};
	if (typeof options === "string") {
		options = { text: options };
	}
	var text = (options.text || options.message || "매칭 가능 크레딧이 부족합니다.");
	var text2 = (options.text2 || "크레딧 충전 후 다시 거래 요청을 진행해 주세요.");
	var secondaryHref = options.secondaryHref || "/plans";
	var secondaryText = options.secondaryText || "크레딧 충전하러 가기";
	var okText = options.okText || "닫기";
	var okVariant = options.okVariant || "outline";

	if (typeof openAiGuideModal !== "function") return false;

	openAiGuideModal({
		title: "크레딧 부족 안내",
		text: text,
		text2: text2,
		okText: okText,
		okVariant: okVariant,
		secondaryText: secondaryText,
		secondaryHref: secondaryHref
	});
	return true;
}

	function openGuide(title, text, text2) {
		openAiGuideModal({
			title: title || "AI 추천 안내",
			text: text || "사업자 번호를 입력하고 AI 추천받기를 눌러주세요.",
			text2: text2 || "AI 분석을 통해 업종/거래 데이터를 기반으로 적합한 거래처 판매처를 추천해드립니다."
		});
	}

	function openCompanyLookupFailGuide(data) {
		var code = data && data.code ? String(data.code) : "";
		var message = data && data.message ? String(data.message) : "";
		if (code === "timeout" || message.indexOf("초과") !== -1) {
			openGuide("기업 정보 조회 지연", "기업 정보 조회가 예상보다 오래 걸리고 있습니다.", "잠시 후 다시 시도해 주세요. 계속 반복되면 사업자번호를 확인해 주세요.");
			return;
		}
		if (code === "not_found") {
			openGuide("사업자번호 재확인", "입력하신 사업자번호로 기업 정보를 찾지 못했습니다.", "사업자번호를 다시 확인한 뒤 AI 추천받기를 눌러주세요.");
			return;
		}
		openGuide("기업 정보 조회 안내", message || "기업 정보를 불러오지 못했습니다.", "잠시 후 다시 시도해 주세요.");
	}

	function openCompanyLookupPendingGuide() {
		openAiGuideModal({
			title: "기업 정보 조회 중",
			text: "KODATA 기업 정보를 확인하고 있습니다.",
			text2: "외부 기관 응답이 지연될 수 있어 잠시만 기다려 주세요.",
			okText: "계속 기다리기",
			okVariant: "outline",
			kind: "company_lookup_pending"
		});
	}

	function closeCompanyLookupPendingGuide() {
		var state = window._aiGuideModalState || null;
		if (!state || state.kind !== "company_lookup_pending") return;
		closeAiGuideModal();
	}

	function getPartnerTradeDescHtml(tradeType) {
		var isBuy = Number(tradeType) !== 2;
		var baseText = isBuy ? "귀사가 구매하려는 품목을 선택하세요" : "귀사가 판매하려는 품목을 선택하세요";
		return baseText + ' <span class="modal_pd_multi_hint">복수 선택 가능</span>';
	}

	function setPartnerTradeDesc(tradeType) {
		$("#modal_pd_trade_desc").html(getPartnerTradeDescHtml(tradeType));
	}

	function openCompanyVerifyModalFromData(data, $form) {
		if (!data || data.result !== "success") return false;
		closeCompanyLookupPendingGuide();

		var overlay = document.getElementById("modal_verify_overlay");
		var companyEl = document.getElementById("modal_verify_company");
			var bzcEl = document.getElementById("modal_verify_bzc");
			var typeEl = document.getElementById("modal_verify_type");
			var cominfoVal = data.cominfo ? data.cominfo : "";
		var initialTradeType = Number(data.trade_type) === 2 ? 2 : 1;

			window.currentSearchBizno = data.bizno || window.currentSearchBizno || "";
			window.currentSearchBiznoToken = data.bizno_token || window.currentSearchBiznoToken || "";
				window.currentSearchBzcCd = data.bzc_cd || "";
				// 업종코드 보강/전체 업종 fallback 상태를 이후 키워드 조회와 추천 진입까지 유지한다.
				window.currentOriginalSearchBzcCd = data.original_bzc_cd || data.bzc_cd || "";
			window.currentSearchKeywordScope = data.keyword_scope || (data.bzc_cd ? "related" : "all");
			window.currentSearchBzcMissing = Number(data.source_bzc_missing || 0) ? 1 : 0;
			window.currentSearchBzcResolved = Number(data.source_bzc_resolved || 0) ? 1 : 0;
			window.currentSearchBzcResolveSource = data.source_bzc_resolve_source || "";
			window.currentSearchBzcFallbackReason = data.fallback_reason || "";
			window.currentSearchKedcd = data.kedcd || "";
			window.currentSearchTradeType = initialTradeType;

		if ($form && $form.length) {
			$form.find("input[name='cominfo']").val(cominfoVal);
		}

			if (companyEl) companyEl.textContent = data.name || "";
			if (bzcEl) bzcEl.textContent = data.bzc_name || "";
			if (typeEl) typeEl.textContent = data.enp_type || "";
		$("#modal_pd_trade_buy").toggleClass("active", initialTradeType === 1);
		$("#modal_pd_trade_sell").toggleClass("active", initialTradeType === 2);
		setPartnerTradeDesc(initialTradeType);

		if (overlay) {
			overlay.classList.add("is_open");
			overlay.setAttribute("aria-hidden", "false");
			syncVerifyModalPageState();
			focusVerifyModal(overlay);
			loadPartnerKeywordChips(initialTradeType);
		}
		return true;
	}

// 구버전/중복 스크립트가 alert를 호출해도 안내 모달로 통일한다.
(function() {
	var nativeAlert = window.alert;
	window.alert = function(message) {
		var text = (message == null) ? "" : String(message);
		if (text.indexOf("기업 정보를 분석하는 중 오류가 발생했습니다.") !== -1 ||
			text.indexOf("기업 정보 조회 중 통신 오류가 발생했습니다.") !== -1) {
			var lines = text.split("\n");
			openGuide("조회 오류 안내", (lines[0] || "사업자번호 조회 중 일시적인 오류가 발생했습니다.").trim(), (lines[1] || "잠시 후 다시 시도해 주세요.").trim());
			return;
		}
		return nativeAlert.apply(window, arguments);
	};
})();

function closeAiGuideModal() {
	var overlay = document.getElementById("modal_ai_guide_overlay");
	if (!overlay) return;
	overlay.classList.remove("is_open");
	overlay.setAttribute("aria-hidden", "true");
	window._aiGuideModalState = null;
	syncVerifyModalPageState();
}

	function closeVerifyModal() {
		var overlay = document.getElementById("modal_verify_overlay");
		window.__partnerRecommendPreviewState = null;
		if (overlay && overlay.classList.contains("is_open")) {
			overlay.classList.remove("is_open");
			overlay.setAttribute("aria-hidden", "true");
			syncVerifyModalPageState();
		}
	}

	window.currentSearchTradeType = 1;
	window.currentPartnerBzcItems = [];
	window.currentSelectedPartnerBzcItems = [];
	window.currentCustomKeywordItems = [];
	window.currentCustomKeywordTimer = null;
	window.currentCustomKeywordRequest = null;
	window.currentCustomKeywordRequestSeq = 0;
	window.currentPartnerKeywordRequestSeq = 0;

	function escapeModalText(text) {
		var div = document.createElement("div");
		div.textContent = (text == null) ? "" : String(text);
		return div.innerHTML;
	}

	function resetPartnerKeywordSelection() {
		window.currentSelectedPartnerBzcItems = [];
		$("#modal_pd_grid .modal_pd_chip").removeClass("selected");
		var countEl = document.getElementById("modal_pd_count");
		var previewEl = document.getElementById("modal_pd_preview");
		var confirmBtn = document.getElementById("modal_pd_btn_confirm");
		if (countEl) countEl.textContent = "0";
		if (previewEl) previewEl.innerHTML = '<span class="modal_pd_empty">품목을 선택하면 여기에 표시됩니다</span>';
		if (confirmBtn) confirmBtn.disabled = true;
	}

	function showRecommendationToast(message) {
		// 공통 토스트가 없는 메인 페이지에서도 방향 미선택·5개 제한 안내를 모달로 가리지 않고 표시한다.
		if (typeof window._showToast === "function") {
			window._showToast("!", message, "default");
			return;
		}
		var oldToast = document.querySelector(".modal_pd_toast");
		if (oldToast) oldToast.remove();
		var toast = document.createElement("div");
		toast.className = "modal_pd_toast";
		toast.setAttribute("role", "status");
		toast.textContent = message;
		document.body.appendChild(toast);
		window.requestAnimationFrame(function() {
			toast.classList.add("is_show");
		});
		window.setTimeout(function() {
			toast.classList.remove("is_show");
			window.setTimeout(function() {
				if (toast.parentNode) toast.parentNode.removeChild(toast);
			}, 200);
		}, 2200);
	}

	function cancelCustomKeywordRequest() {
		// 검색어·거래 방향이 바뀌면 진행 중 응답을 취소하고 순번을 올려 늦은 응답도 화면 반영에서 제외한다.
		window.currentCustomKeywordRequestSeq++;
		if (window.currentCustomKeywordRequest && window.currentCustomKeywordRequest.readyState !== 4) {
			window.currentCustomKeywordRequest.abort();
		}
		window.currentCustomKeywordRequest = null;
	}

	function resetCustomKeywordSearch() {
		// 거래 방향 변경 또는 후보 선택 뒤 이전 검색 결과가 다른 방향에 재사용되지 않도록 초기화한다.
		var input = document.getElementById("modal_pd_custom_input");
		var results = document.getElementById("modal_pd_custom_results");
		var addBtn = document.getElementById("modal_pd_custom_add");
		window.currentCustomKeywordItems = [];
		if (window.currentCustomKeywordTimer) window.clearTimeout(window.currentCustomKeywordTimer);
		window.currentCustomKeywordTimer = null;
		cancelCustomKeywordRequest();
		if (input) input.value = "";
		if (results) {
			results.innerHTML = "";
			results.hidden = true;
		}
		if (addBtn) addBtn.disabled = true;
	}

	function relevanceBadgeClass(matchType) {
		if (matchType === "exact") return " exact";
		if (matchType === "prefix_5") return " prefix_5";
		if (matchType === "prefix_4") return " prefix_4";
		if (matchType === "global_all") return " all";
		return " all";
	}

	function renderCustomKeywordResults(items, query, canExpand) {
		// 서버가 내부 테이블에서 확정한 표시명만 노출하며 업종코드는 화면에 출력하지 않는다.
		var results = document.getElementById("modal_pd_custom_results");
		var addBtn = document.getElementById("modal_pd_custom_add");
		window.currentCustomKeywordItems = Array.isArray(items) ? items : [];
		if (!results) return;
		if (!window.currentCustomKeywordItems.length) {
			results.innerHTML = canExpand
				? '<div class="modal_pd_custom_expand"><span>현재 업종과 관련된 품목을 찾지 못했어요.</span><button type="button" id="modal_pd_custom_expand">검색 범위 넓히기</button></div>'
				: '<span class="modal_pd_custom_empty">전체 업종에서도 일치하는 품목이 없어요</span>';
			results.hidden = false;
			if (addBtn) addBtn.disabled = true;
			return;
		}
		results.innerHTML = window.currentCustomKeywordItems.map(function(item, idx) {
			return '<button type="button" class="modal_pd_custom_option" role="option" data-custom-idx="' + idx + '">' +
				'<span>' + escapeModalText(item.label || "") + '</span>' +
				'<em class="modal_pd_relevance' + relevanceBadgeClass(item.match_type || "all") + '">' + escapeModalText(item.relevance_label || "다른 업종") + '</em></button>';
		}).join("");
		results.hidden = false;
		if (addBtn) {
			addBtn.disabled = false;
			addBtn.setAttribute("data-query", query || "");
		}
	}

	function renderCustomKeywordExpandError(message) {
		// 전체 업종 검색이 지연되거나 실패해도 사용자가 다시 시도하거나 다른 동작을 할 수 있게 로딩 상태를 해제한다.
		var results = document.getElementById("modal_pd_custom_results");
		var addBtn = document.getElementById("modal_pd_custom_add");
		window.currentCustomKeywordItems = [];
		if (!results) return;
		results.innerHTML = '<div class="modal_pd_custom_expand"><span>' + escapeModalText(message) + '</span><button type="button" id="modal_pd_custom_expand">다시 검색하기</button></div>';
		results.hidden = false;
		if (addBtn) addBtn.disabled = true;
	}

	function partnerKeywordItemKey(item) {
		return String((item && item.partner_bzc) || "") + "|" + String((item && item.label) || "");
	}

	function findSelectedPartnerKeywordIndex(item) {
		var selected = window.currentSelectedPartnerBzcItems || [];
		var itemKey = partnerKeywordItemKey(item);
		for (var i = 0; i < selected.length; i++) {
			if (partnerKeywordItemKey(selected[i]) === itemKey) return i;
		}
		return -1;
	}

	function syncPartnerKeywordChipSelection() {
		$("#modal_pd_grid .modal_pd_chip").each(function() {
			var idx = parseInt($(this).attr("data-idx"), 10);
			var item = window.currentPartnerBzcItems && window.currentPartnerBzcItems[idx] ? window.currentPartnerBzcItems[idx] : null;
			$(this).toggleClass("selected", !!item && findSelectedPartnerKeywordIndex(item) >= 0);
		});
	}

	function addCustomKeywordItem(item, query) {
		// 검색 결과가 기존 추천 칩이면 기존 칩만 선택하고, 새 품목만 중단 영역에 추가해 최대 5개로 제한한다.
		if (!item) return;
		var selected = window.currentSelectedPartnerBzcItems || [];
		var allItems = window.currentPartnerBzcItems || [];
		var matchingItem = null;
		for (var i = 0; i < allItems.length; i++) {
			if (partnerKeywordItemKey(allItems[i]) === partnerKeywordItemKey(item)) {
				matchingItem = allItems[i];
				break;
			}
		}
		if (matchingItem) {
			// 기존 추천 칩을 검색으로 선택한 경우에도 검색 당시 관련도 단계는 선택 로그에 남긴다.
			matchingItem.match_type = item.match_type || matchingItem.match_type;
			matchingItem.relevance_label = item.relevance_label || matchingItem.relevance_label;
			item = matchingItem;
		}

		var customCount = allItems.filter(function(existingItem) {
			return existingItem.selection_source === "custom";
		}).length;
		if (findSelectedPartnerKeywordIndex(item) >= 0) {
			showRecommendationToast("이미 선택한 품목입니다.");
			return;
		}
		if (!matchingItem && customCount >= 5) {
			showRecommendationToast("직접 검색한 품목은 최대 5개까지 추가할 수 있습니다.");
			return;
		}

		if (!matchingItem) {
			item.selection_source = "custom";
			allItems.push(item);
			window.currentPartnerBzcItems = allItems;
		}
		selected.push(item);
		window.currentSelectedPartnerBzcItems = selected;
		renderPartnerKeywordChips(window.currentPartnerBzcItems, true);
		updatePartnerKeywordSelection();
		$.post("<?php echo G5_URL; ?>/ko/bzc_trade_rec_keyword_search.php", {
			action: "select",
			query: query || item.label || "",
			label: item.label || "",
			partner_cd: item.partner_cd || "",
			match_type: item.match_type || "custom_keyword",
			scope: item.match_type === "all" || item.match_type === "global_all" ? "all" : (window.currentSearchKeywordScope || "related"),
			bzc_cd: window.currentSearchBzcCd || "",
			trade_type: window.currentSearchTradeType || 0,
			source_bzc_missing: window.currentSearchBzcMissing || 0,
			source_bzc_resolved: window.currentSearchBzcResolved || 0,
			source_bzc_resolve_source: window.currentSearchBzcResolveSource || "",
			fallback_reason: window.currentSearchBzcFallbackReason || "",
			trace_id: window.currentRecommendTraceId || ""
		});
		resetCustomKeywordSearch();
		var grid = document.getElementById("modal_pd_grid");
		if (grid && !matchingItem) grid.scrollTop = grid.scrollHeight;
	}

	function updatePartnerKeywordSelection() {
		var selected = window.currentSelectedPartnerBzcItems || [];
		var countEl = document.getElementById("modal_pd_count");
		var previewEl = document.getElementById("modal_pd_preview");
		var confirmBtn = document.getElementById("modal_pd_btn_confirm");
		if (countEl) countEl.textContent = String(selected.length);
		if (previewEl) {
			if (!selected.length) {
				previewEl.innerHTML = '<span class="modal_pd_empty">품목을 선택하면 여기에 표시됩니다</span>';
			} else {
				var showItems = selected.slice(0, 4).map(function(item) {
					return '<span class="modal_pd_preview_chip">' + escapeModalText(item.label || "") + '</span>';
				}).join("");
				if (selected.length > 4) {
					showItems += '<span class="modal_pd_empty"> 외 ' + (selected.length - 4) + '개</span>';
				}
				previewEl.innerHTML = showItems;
			}
		}
		if (confirmBtn) confirmBtn.disabled = selected.length === 0;
	}

	function renderPartnerKeywordChips(items, preserveSelection) {
		var grid = document.getElementById("modal_pd_grid");
		if (!grid) return;
		window.currentPartnerBzcItems = Array.isArray(items) ? items : [];
		if (!preserveSelection) resetPartnerKeywordSelection();
		if (!window.currentPartnerBzcItems.length) {
			grid.innerHTML = '<span class="modal_pd_empty">추천 키워드가 없습니다. 건너뛰고 전체 추천받기를 이용해 주세요.</span>';
			return;
		}
		grid.innerHTML = window.currentPartnerBzcItems.map(function(item, idx) {
			var label = item.label || ((item.keywords || []).join(", "));
			var selectedClass = findSelectedPartnerKeywordIndex(item) >= 0 ? " selected" : "";
			return '<button type="button" class="modal_pd_chip' + selectedClass + '" data-idx="' + idx + '" data-partner-bzc="' + escapeModalText(item.partner_bzc || "") + '">' + escapeModalText(label) + '</button>';
		}).join("");
	}

	function loadPartnerKeywordChips(tradeType) {
		if (window.__partnerRecommendPreviewState) {
			var previewState = window.__partnerRecommendPreviewState;
			window.currentSearchTradeType = (tradeType === 2) ? 2 : 1;
			renderPartnerKeywordChips(previewState.items || [], false);
			return;
		}

		// [추천 키워드 조회]
		// KODATA에서 확인한 기준 업종코드와 사용자가 선택한 구매/판매 방향으로 키워드 후보를 요청한다.
		var grid = document.getElementById("modal_pd_grid");
		var bzcCd = window.currentSearchBzcCd || "";
			// 업종코드가 없으면 서버가 scope=all로 전체 업종 후보를 조회한다.
			var keywordScope = window.currentSearchKeywordScope || (bzcCd ? "related" : "all");
		var requestSeq = ++window.currentPartnerKeywordRequestSeq;
		tradeType = (tradeType === 2) ? 2 : 1;
		window.currentSearchTradeType = tradeType;
		resetPartnerKeywordSelection();
		resetCustomKeywordSearch();
		if (grid) {
			grid.classList.add("is_loading");
			grid.setAttribute("aria-busy", "true");
		}
		// 동일 trace_id와 trade_type을 다음 화면까지 전달해 키워드 조회부터 카드 출력까지 추적한다.
		$.getJSON("<?php echo G5_URL; ?>/ko/bzc_trade_rec_keywords.php", {
			bzc_cd: bzcCd,
			trade_type: tradeType,
			scope: keywordScope,
			source_bzc_missing: window.currentSearchBzcMissing || 0,
			source_bzc_resolved: window.currentSearchBzcResolved || 0,
			source_bzc_resolve_source: window.currentSearchBzcResolveSource || "",
			fallback_reason: window.currentSearchBzcFallbackReason || "",
			trace_id: window.currentRecommendTraceId || ""
		}).done(function(data) {
			// 빠르게 탭을 전환했을 때 이전 방향의 늦은 응답이 현재 키워드를 덮어쓰지 않도록 무시한다.
			if (requestSeq !== window.currentPartnerKeywordRequestSeq) return;
			if (grid) {
				grid.classList.remove("is_loading");
				grid.removeAttribute("aria-busy");
			}
			if (data && data.ok && data.items && data.items.length) {
				renderPartnerKeywordChips(data.items || []);
				return;
			}
			if (grid) grid.innerHTML = '<span class="modal_pd_empty">추천 키워드를 불러오지 못했습니다. 전체 추천을 이용해 주세요.</span>';
		}).fail(function() {
			if (requestSeq !== window.currentPartnerKeywordRequestSeq) return;
			if (grid) {
				grid.classList.remove("is_loading");
				grid.removeAttribute("aria-busy");
				grid.innerHTML = '<span class="modal_pd_empty">추천 키워드 조회 중 오류가 발생했습니다. 전체 추천을 이용해 주세요.</span>';
			}
		});
	}

	function moveToAiRecommendByBizno(comnum) {
		// 키워드를 건너뛴 전체 추천임을 서버 진입 로그에서 구분할 수 있게 전달한다.
		var digits = (comnum || "").replace(/[^0-9]/g, "");
		if (digits.length !== 10) return false;
		var biznoToken = String(window.currentSearchBiznoToken || "");
		if (!biznoToken) {
			openGuide("추천 진입 오류", "사업자번호 보안 토큰을 생성하지 못했습니다.", "새로고침 후 다시 시도해 주세요.");
			return false;
		}
		var tradeType = (window.currentSearchTradeType === 2) ? 2 : 1;
		var type = tradeType === 2 ? "seller" : "buyer";
		window.location.href = "/recommendations?bizno=" + encodeURIComponent(biznoToken) +
			"&type=" + encodeURIComponent(type) +
			"&trade_type=" + encodeURIComponent(String(tradeType)) +
			"&recommendation_action=skip&trace_id=" + encodeURIComponent(String(window.currentRecommendTraceId || ""));
		return true;
	}

function requestMainCompCheck(comnum, $form, opts) {
	opts = opts || {};

	var overlay = opts.overlay || null;
	var btn = opts.button || null;
	var originalHtml = opts.originalHtml || "";

	function restoreButton() {
		if (!btn) return;
		btn.disabled = false;
		if (originalHtml) btn.innerHTML = originalHtml;
	}

	var mainCompCheckPendingTimer = setTimeout(function() {
		openCompanyLookupPendingGuide();
	}, 3000);

	function refreshCompanyLookupFromCache() {
		$.post("<?php echo G5_URL; ?>/get_company_name.php", { comnum: comnum }, function(res) {
			try {
				var data = JSON.parse(res);
				if (openCompanyVerifyModalFromData(data, $form)) {
					restoreButton();
					if (opts.mode === "recommend") {
						closeVerifyModal();
						moveToAiRecommendByBizno(comnum);
					}
					return;
				}
				openCompanyLookupFailGuide(data);
				restoreButton();
			} catch (e) {
				console.error("company lookup refresh parse error:", e);
				openGuide("조회 오류 안내", "응답 처리 중 오류가 발생했습니다.", "잠시 후 다시 시도해 주세요.");
				restoreButton();
			}
		}).fail(function() {
			openGuide("조회 오류 안내", "서버 통신 중 일시적인 오류가 발생했습니다.", "잠시 후 다시 시도해 주세요.");
			restoreButton();
		});
	}

	// cominfo 보정 후 최종 진입 URL은 company_list_ai.php(bizno)로 통일
	$.post("<?php echo G5_URL; ?>/get_set.php", { type: "main_comp_chk", comnum: comnum, sync_company: "main_recommendation" }, function(res) {
		clearTimeout(mainCompCheckPendingTimer);
		try {
			var raw = res + "";
			var sepIdx = raw.indexOf("|");
			var status = sepIdx >= 0 ? raw.substring(0, sepIdx) : raw;
			var body = sepIdx >= 0 ? raw.substring(sepIdx + 1) : "";
			var parts = raw.split("|");
			if (status === "1000") {
				if ($form && $form.length) {
					$form.find("input[name='cominfo']").val(body || "");
				}
				refreshCompanyLookupFromCache();
				} else {
					if (parts[1] === "02") {
						openGuide("사업자번호 재확인", "입력하신 사업자번호로 기업 정보를 찾지 못했습니다.", "사업자번호를 다시 확인해 주세요.");
					} else if (parts[1] === "CURL_ERROR") {
						openGuide("조회 오류", "사업자번호 조회 중 일시적인 오류가 발생했습니다.", "잠시 후 다시 시도해 주세요.");
					} else {
						openGuide("조회 오류", "기업 정보를 분석하는 중 오류가 발생했습니다.", (parts[2] || parts[1] || "다시 시도해 주세요."));
					}
					restoreButton();
				}
			} catch (e) {
				console.error("main_comp_chk parse error:", e);
				openGuide("조회 오류 안내", "응답 처리 중 오류가 발생했습니다.", "잠시 후 다시 시도해 주세요.");
				restoreButton();
			}
		}).fail(function() {
			clearTimeout(mainCompCheckPendingTimer);
			openGuide("조회 오류 안내", "서버 통신 중 일시적인 오류가 발생했습니다.", "잠시 후 다시 시도해 주세요.");
			restoreButton();
		});
		}

	// Enter 제출 시에도 클릭 검색 로직과 동일하게 처리
	$(document).on("submit", "#match_hd, #match_index, #match", function(e){
		e.preventDefault();
		var id = this.id || "";
		if (id === "match_hd") {
			$("#hd_submit").trigger("click");
			return false;
		}
		if (id === "match_index") {
			$("#main_submit_index").trigger("click");
			return false;
		}
		$("#sub_submit").trigger("click");
		return false;
	});

	// 공통 검색 처리 로직
	$(document).on("click", "#main_submit, #main_submit_index, #hd_submit, #sub_submit", function(e){
		e.preventDefault();
	var $this = $(this);
	
	// 현재 클릭된 버튼의 컨텍스트에 맞는 input과 form 찾기
	var $input, $form;
	if ($this.attr('id') === 'hd_submit') {
		$input = $("#comnum_hd");
		$form = $("#match_hd");
	} else if ($this.attr('id') === 'main_submit_index') {
		$input = $("#comnum_index");
		$form = $("#match_index");
	} else if ($this.attr('id') === 'main_submit') {
		$input = $("#comnum");
		$form = $("#match");
	} else {
		// sub_submit 등 기타 (sVisual 내)
		$input = $this.siblings("input[name='comnum']").length ? $this.siblings("input[name='comnum']") : $("#comnum");
		$form = $this.closest("form").length ? $this.closest("form") : $("#match");
	}

	$(".comnum_chk").blur();
	var comnum = $input.val();
	var comnumDigits = (comnum || "").replace(/[^0-9]/g, "");

	if (comnumDigits.length === 0) {
		openAiGuideModal({
			title: "AI 추천 안내",
			text: "사업자 번호를 입력해 주세요.",
			text2: "사업자 번호를 입력하고 AI 추천받기를 누르면 AI 분석을 통해 거래처 판매처를 추천해드립니다."
		});
		return false;
	}

	if (comnumDigits.length != 10) {
		openAiGuideModal({
			title: "사업자 번호 재확인",
			text: "입력하신 사업자 번호 형식이 올바르지 않습니다.",
			text2: "사업자 번호를 다시 확인한 뒤 AI 추천받기를 눌러주세요."
		});
		return false;
	}

	// 전역 변수로 현재 사용중인 input/form 정보 저장 (모달 버튼 클릭 시 사용)
	window.currentSearchInput = $input;
	window.currentSearchForm = $form;
	window.currentSearchBizno = comnumDigits;
	window.currentSearchBiznoToken = "";
	window.currentSearchBzcCd = "";
	window.currentSearchKedcd = "";
	// 한 번의 추천 실행에서 발생한 서버 로그를 사람이 조회하기 쉬운 짧은 ID로 묶는다.
	var traceAlphabet = "ABCDEFGHJKLMNPQRSTUVWXYZ23456789";
	var traceRandom = "";
	var traceBytes = new Uint8Array(4);
	if (window.crypto && typeof window.crypto.getRandomValues === "function") {
		window.crypto.getRandomValues(traceBytes);
		for (var traceIndex = 0; traceIndex < traceBytes.length; traceIndex++) {
			traceRandom += traceAlphabet.charAt(traceBytes[traceIndex] % traceAlphabet.length);
		}
	} else {
		for (var fallbackIndex = 0; fallbackIndex < 4; fallbackIndex++) {
			traceRandom += traceAlphabet.charAt(Math.floor(Math.random() * traceAlphabet.length));
		}
	}
	var traceNow = new Date();
	var tracePad = function(value) { return Number(value) < 10 ? "0" + Number(value) : String(value); };
	window.currentRecommendTraceId = "R-"
		+ String(traceNow.getFullYear()).slice(-2)
		+ tracePad(traceNow.getMonth() + 1)
		+ tracePad(traceNow.getDate())
		+ "-"
		+ tracePad(traceNow.getHours())
		+ tracePad(traceNow.getMinutes())
		+ tracePad(traceNow.getSeconds())
		+ "-"
		+ traceRandom;

	// test_ml2와 동일하게 KODATA(main_comp_chk) 기준 기업정보를 먼저 확인한다.
	requestMainCompCheck(comnumDigits, $form);
	});

	$(document).on("click", "#modal_pd_trade_buy, #modal_pd_trade_sell", function() {
		var isBuy = this.id === "modal_pd_trade_buy";
		$("#modal_pd_trade_buy").toggleClass("active", isBuy);
		$("#modal_pd_trade_sell").toggleClass("active", !isBuy);
		setPartnerTradeDesc(isBuy ? 1 : 2);
		loadPartnerKeywordChips(isBuy ? 1 : 2);
	});

	window.previewPartnerRecommendModal = function(opts) {
		opts = opts || {};
		window.__partnerRecommendPreviewState = {
			items: (Array.isArray(opts.items) && opts.items.length ? opts.items : [
				"응용소프트웨어개발공급",
				"시스템소프트웨어개발공급",
				"포털및인터넷정보매개서비스",
				"데이터베이스온라인정보제공"
			]).map(function(item) {
				if (typeof item === "string") return { label: item };
				return item;
			})
		};
		var mockData = {
			result: "success",
			name: opts.name || "(주)가이엔씨",
			bzc_name: opts.bzcName || "데이터베이스 및 온라인정보 제공업",
			enp_type: opts.enpType || "법인기업",
			trade_type: Number(opts.tradeType) === 2 ? 2 : 1,
			bzc_cd: opts.bzcCd || "",
			original_bzc_cd: opts.originalBzcCd || "",
			keyword_scope: opts.keywordScope || "all",
			source_bzc_missing: 1,
			source_bzc_resolved: 0,
			source_bzc_resolve_source: "",
			fallback_reason: "local_preview",
			bizno: opts.bizno || "1234567890",
			bizno_token: opts.biznoToken || "local-preview-token",
			kedcd: opts.kedcd || "",
			cominfo: opts.cominfo || ""
		};

		openCompanyVerifyModalFromData(mockData, window.currentSearchForm || null);
	};

	$(document).on("input", "#modal_pd_custom_input", function() {
		// 입력 중 API 호출을 줄이기 위해 350ms 대기하고, 선택된 구매/판매 방향으로만 후보를 조회한다.
		var query = String(this.value || "").trim().replace(/\s+/g, " ");
		var results = document.getElementById("modal_pd_custom_results");
		var addBtn = document.getElementById("modal_pd_custom_add");
		if (window.currentCustomKeywordTimer) window.clearTimeout(window.currentCustomKeywordTimer);
		cancelCustomKeywordRequest();
		if (addBtn) addBtn.disabled = true;
		if (query.length < 2) {
			window.currentCustomKeywordItems = [];
			if (results) {
				results.innerHTML = "";
				results.hidden = true;
			}
			return;
		}
		if (window.currentSearchTradeType !== 1 && window.currentSearchTradeType !== 2) {
			showRecommendationToast("먼저 구매처 찾기 또는 판매처 찾기를 선택해주세요.");
			return;
		}
		window.currentCustomKeywordTimer = window.setTimeout(function() {
			var requestSeq = ++window.currentCustomKeywordRequestSeq;
			var requestTradeType = window.currentSearchTradeType;
			var requestBzcCd = window.currentSearchBzcCd || "";
			// 직접 입력 검색도 추천 키워드 조회와 같은 fallback 범위를 사용한다.
			var requestScope = window.currentSearchKeywordScope || (requestBzcCd ? "related" : "all");
			var request = $.getJSON("<?php echo G5_URL; ?>/ko/bzc_trade_rec_keyword_search.php", {
				query: query,
				bzc_cd: requestBzcCd,
				trade_type: requestTradeType,
				scope: requestScope,
				source_bzc_missing: window.currentSearchBzcMissing || 0,
				source_bzc_resolved: window.currentSearchBzcResolved || 0,
				source_bzc_resolve_source: window.currentSearchBzcResolveSource || "",
				fallback_reason: window.currentSearchBzcFallbackReason || "",
				trace_id: window.currentRecommendTraceId || ""
			});
			window.currentCustomKeywordRequest = request;
			request.done(function(data) {
				if (requestSeq !== window.currentCustomKeywordRequestSeq ||
					requestTradeType !== window.currentSearchTradeType ||
					requestBzcCd !== (window.currentSearchBzcCd || "")) return;
				renderCustomKeywordResults(data && data.ok ? (data.items || []) : [], query, !!(data && data.can_expand));
			}).fail(function(xhr) {
				if (requestSeq !== window.currentCustomKeywordRequestSeq || (xhr && xhr.statusText === "abort")) return;
				var error = xhr && xhr.responseJSON ? xhr.responseJSON.error : "";
				renderCustomKeywordResults([], query, false);
				if (error === "invalid_query") showRecommendationToast("품목명은 2~30자의 한글, 영문, 숫자로 입력해주세요.");
			}).always(function() {
				if (window.currentCustomKeywordRequest === request) window.currentCustomKeywordRequest = null;
			});
		}, 350);
	});

	$(document).on("click", "#modal_pd_custom_expand", function() {
		var input = document.getElementById("modal_pd_custom_input");
		var query = String((input && input.value) || "").trim().replace(/\s+/g, " ");
		if (query.length < 2) return;
		cancelCustomKeywordRequest();
		$(this).prop("disabled", true).text("전체 업종 검색 중...");
		var requestSeq = ++window.currentCustomKeywordRequestSeq;
		var requestTradeType = window.currentSearchTradeType;
		var requestBzcCd = window.currentSearchBzcCd || "";
		// 전체 업종 검색이 3초 안에 응답하지 않으면 UI를 복구해 다른 동작을 계속할 수 있게 한다.
		var request = $.ajax({
			url: "<?php echo G5_URL; ?>/ko/bzc_trade_rec_keyword_search.php",
			method: "GET",
			dataType: "json",
			timeout: 3000,
			data: {
				query: query,
				bzc_cd: requestBzcCd,
				trade_type: requestTradeType,
				scope: "all",
				source_bzc_missing: window.currentSearchBzcMissing || 0,
				source_bzc_resolved: window.currentSearchBzcResolved || 0,
				source_bzc_resolve_source: window.currentSearchBzcResolveSource || "",
				fallback_reason: window.currentSearchBzcFallbackReason || "",
				trace_id: window.currentRecommendTraceId || ""
			}
		});
		window.currentCustomKeywordRequest = request;
		request.done(function(data) {
			if (requestSeq !== window.currentCustomKeywordRequestSeq ||
				requestTradeType !== window.currentSearchTradeType ||
				requestBzcCd !== (window.currentSearchBzcCd || "")) return;
			renderCustomKeywordResults(data && data.ok ? (data.items || []) : [], query, false);
		}).fail(function(xhr) {
			if (requestSeq !== window.currentCustomKeywordRequestSeq || (xhr && xhr.statusText === "abort")) return;
			if (xhr && xhr.statusText === "timeout") {
				renderCustomKeywordExpandError("전체 업종 검색 시간이 초과되었습니다. 잠시 후 다시 시도해 주세요.");
				showRecommendationToast("검색 시간이 초과되어 검색을 중단했습니다.");
				return;
			}
			renderCustomKeywordExpandError("전체 업종 검색 중 오류가 발생했습니다. 잠시 후 다시 시도해 주세요.");
		}).always(function() {
			if (window.currentCustomKeywordRequest === request) window.currentCustomKeywordRequest = null;
		});
	});

	$(document).on("click", ".modal_pd_custom_option", function() {
		var idx = parseInt($(this).attr("data-custom-idx"), 10);
		var item = window.currentCustomKeywordItems && window.currentCustomKeywordItems[idx] ? window.currentCustomKeywordItems[idx] : null;
		var input = document.getElementById("modal_pd_custom_input");
		addCustomKeywordItem(item, input ? input.value : "");
	});

	$(document).on("click", "#modal_pd_custom_add", function() {
		if (this.disabled) return;
		var input = document.getElementById("modal_pd_custom_input");
		addCustomKeywordItem(window.currentCustomKeywordItems[0] || null, input ? input.value : "");
	});

	$(document).on("click", ".modal_pd_chip", function() {
		var idx = parseInt($(this).attr("data-idx"), 10);
		var item = window.currentPartnerBzcItems && window.currentPartnerBzcItems[idx] ? window.currentPartnerBzcItems[idx] : null;
		if (!item) return;

		var selected = window.currentSelectedPartnerBzcItems || [];
		var existingIdx = findSelectedPartnerKeywordIndex(item);

		if (existingIdx >= 0) {
			selected.splice(existingIdx, 1);
			$(this).removeClass("selected");
		} else {
			selected.push(item);
			$(this).addClass("selected");
		}

		window.currentSelectedPartnerBzcItems = selected;
		syncPartnerKeywordChipSelection();
		updatePartnerKeywordSelection();
	});

	$(document).on("click", "#modal_pd_btn_confirm", function() {
		// [선택 키워드 -> 추천 요청 조건 변환]
		// 같은 상대 업종코드의 키워드를 여러 개 선택하면 선택 수를 가중치로 합산해 목록 페이지로 전달한다.
		if (this.disabled) return;
		var selected = window.currentSelectedPartnerBzcItems || [];
		var partnerCodes = [];
		var partnerWeights = [];
		var selectedKeywords = [];
		var keywordSourceBzcCodes = [];
		var keywordMatchTypes = [];
		var partnerIndexes = {};
		for (var i = 0; i < selected.length; i++) {
			var partnerCode = String(selected[i].partner_cd || "");
			var partnerBzc = String(selected[i].partner_bzc || "");
			if (!partnerCode || !partnerBzc) continue;
			if (selected[i].label) selectedKeywords.push(String(selected[i].label));
			if (selected[i].source_bzc) keywordSourceBzcCodes.push(String(selected[i].source_bzc));
			if (selected[i].match_type) keywordMatchTypes.push(String(selected[i].match_type));
			if (partnerIndexes[partnerBzc] === undefined) {
				partnerIndexes[partnerBzc] = partnerCodes.length;
				partnerCodes.push(partnerCode);
				partnerWeights.push(1);
			} else {
				partnerWeights[partnerIndexes[partnerBzc]] += 1;
			}
		}
		if (!partnerCodes.length) return;

		var bizno = String(window.currentSearchBizno || ((window.currentSearchInput && window.currentSearchInput.val()) || "")).replace(/[^0-9]/g, "");
		if (bizno.length !== 10) {
			openGuide("사업자번호 재확인", "입력하신 사업자번호 형식이 올바르지 않습니다.", "사업자번호를 다시 확인해 주세요.");
			return;
		}

		closeVerifyModal();
		var tradeType = (window.currentSearchTradeType === 2) ? 2 : 1;
		var type = tradeType === 2 ? "seller" : "buyer";
		var biznoToken = String(window.currentSearchBiznoToken || "");
		if (!biznoToken) {
			openGuide("추천 진입 오류", "사업자번호 보안 토큰을 생성하지 못했습니다.", "새로고침 후 다시 시도해 주세요.");
			return;
		}
		// 선택 키워드, 원본 업종코드, 가중치를 함께 보내 EC2 요청과 캐시 키를 재현할 수 있게 한다.
		// 선택 키워드 추천 진입 시에도 scope/fallback 메타를 URL에 싣고 서버 로그까지 연결한다.
		var keywordScope = window.currentSearchKeywordScope || (window.currentSearchBzcCd ? "related" : "all");
		var url = "/recommendations?bizno=" + encodeURIComponent(biznoToken) +
			"&type=" + encodeURIComponent(type) +
			"&trade_type=" + encodeURIComponent(String(tradeType)) +
			"&partner_cd=" + encodeURIComponent(partnerCodes.join(",")) +
			"&partner_weight=" + encodeURIComponent(partnerWeights.join(",")) +
			"&selected_keyword=" + encodeURIComponent(selectedKeywords.join(",")) +
			"&keyword_source_bzc=" + encodeURIComponent(keywordSourceBzcCodes.join(",")) +
			"&keyword_match_type=" + encodeURIComponent(keywordMatchTypes.join(",")) +
			"&keyword_scope=" + encodeURIComponent(keywordScope) +
			"&source_bzc_missing=" + encodeURIComponent(String(window.currentSearchBzcMissing || 0)) +
			"&source_bzc_resolved=" + encodeURIComponent(String(window.currentSearchBzcResolved || 0)) +
			"&source_bzc_resolve_source=" + encodeURIComponent(String(window.currentSearchBzcResolveSource || "")) +
			"&fallback_reason=" + encodeURIComponent(String(window.currentSearchBzcFallbackReason || "")) +
			"&recommendation_action=keyword_select" +
			"&trace_id=" + encodeURIComponent(String(window.currentRecommendTraceId || ""));
		if (typeof showAiLoading === "function") {
			showAiLoading(url);
		} else {
			window.location.href = url;
		}
	});

// 모달 내부 버튼 이벤트
$(document).on("click", "#modal_verify_btn_ai", function() {
	var btn = this;
	var overlay = document.getElementById("modal_verify_overlay");
	var $input = window.currentSearchInput;
	var $form = window.currentSearchForm;

	if (!$input || !$form) return;

	var comnum = $input.val();

	if (comnum.replace(/[^0-9]/g, "").length === 10) {
		if (window.currentSearchBiznoToken) {
			if (overlay) {
				overlay.classList.remove("is_open");
				overlay.setAttribute("aria-hidden", "true");
				syncVerifyModalPageState();
			}
			moveToAiRecommendByBizno(comnum);
			return;
		}

		var originalHtml = btn.innerHTML;
		btn.disabled = true;
		btn.innerHTML = '<span class="sparkle" aria-hidden="true"><svg viewBox="0 0 24 24"><use href="#icon-magic-wand"></use></svg></span> 분석 중...';

			requestMainCompCheck(comnum, $form, {
				overlay: overlay,
				button: btn,
				originalHtml: originalHtml,
				mode: "recommend"
			});
			} else {
				openGuide("사업자번호 재확인", "입력하신 사업자번호 형식이 올바르지 않습니다.", "사업자번호를 다시 확인해 주세요.");
			}
		});

// 모달 CTA 버튼에 포커스된 상태에서 Enter 입력 시 클릭과 동일 동작
$(document).on("keydown", "#modal_verify_btn_ai", function(e) {
	if (e.key !== "Enter" && e.key !== "NumpadEnter") return;
	e.preventDefault();
	$(this).trigger("click");
});

// 모달 닫기 이벤트들
$(document).on("click", "#modal_verify_overlay", function(e) {
	if (e.target === this) closeVerifyModal();
});
$(document).on("click", "#modal_verify_btn_close", function() {
	closeVerifyModal();
});
$(document).on("click", "#modal_ai_guide_overlay", function(e) {
	if (e.target === this) {
		closeAiGuideModal();
		if (window.currentSearchInput) window.currentSearchInput.focus();
	}
});
$(document).on("click", "#modal_ai_guide_btn_close", function() {
	closeAiGuideModal();
	if (window.currentSearchInput) window.currentSearchInput.focus();
});
$(document).on("click", "#modal_ai_guide_btn_ok", function() {
	var state = window._aiGuideModalState || null;
	closeAiGuideModal();
	if (state && typeof state.onOk === "function") state.onOk();
	if (window.currentSearchInput) window.currentSearchInput.focus();
});
$(document).on("click", "#modal_ai_guide_btn_secondary", function() {
	var state = window._aiGuideModalState || null;
	closeAiGuideModal();
	if (state && typeof state.onSecondary === "function") {
		state.onSecondary();
		return;
	}
	if (state && state.secondaryHref) {
		window.location.href = state.secondaryHref;
	}
});

$(document).on("keydown", function(e) {
	if (e.key === "Escape") {
		closeVerifyModal();
		closeAiGuideModal();
	}
});

// 사업자번호 하이픈 자동 입력
$(document).on('keyup', '.comnum_chk', function(){
	var num = $(this).val();
	num.trim();
	this.value = AutoHypen(num);
});

function AutoHypen(companyNum){
	companyNum = companyNum.replace(/[^0-9]/g, '');
	var tempNum = '';
	if(companyNum.length < 4){
		return companyNum;
	} else if(companyNum.length < 6){
		tempNum += companyNum.substr(0,3) + '-' + companyNum.substr(3,2);
		return tempNum;
	} else {
		tempNum += companyNum.substr(0,3) + '-' + companyNum.substr(3,2) + '-' + companyNum.substr(5);
		return tempNum;
	}
}

// 시안과 동일한 검색창 placeholder 타이핑 효과 (헤더/메인)
function initComnumPlaceholderTyping() {
	var selector = '#comnum_hd, #comnum_index, #comnum';
	var inputs = document.querySelectorAll(selector);
	if (!inputs.length) return;

	Array.prototype.forEach.call(inputs, function (input) {
		if (!input || input.dataset.typingInit === '1') return;
		input.dataset.typingInit = '1';

		var fullText = (input.getAttribute('placeholder') || '').trim();
		if (!fullText) return;

		var timer = null;
		var charIndex = 0;
		var mode = 'typing'; // typing -> pauseFull -> deleting -> pauseEmpty

		function clearTimer() {
			if (timer) {
				clearTimeout(timer);
				timer = null;
			}
		}

		function schedule(delay) {
			clearTimer();
			timer = setTimeout(tick, delay);
		}

		function shouldPause() {
			return document.activeElement === input || !!input.value;
		}

		function resetAndResume() {
			charIndex = 0;
			mode = 'typing';
			input.placeholder = '';
			schedule(260);
		}

		function tick() {
			if (shouldPause()) {
				input.placeholder = '';
				schedule(280);
				return;
			}

			if (mode === 'typing') {
				charIndex += 1;
				input.placeholder = fullText.slice(0, charIndex);
				if (charIndex >= fullText.length) {
					mode = 'pauseFull';
					schedule(1200);
					return;
				}
				schedule(90);
				return;
			}

			if (mode === 'pauseFull') {
				mode = 'deleting';
				schedule(45);
				return;
			}

			if (mode === 'deleting') {
				charIndex -= 1;
				input.placeholder = fullText.slice(0, Math.max(0, charIndex));
				if (charIndex <= 0) {
					charIndex = 0;
					mode = 'pauseEmpty';
					schedule(320);
					return;
				}
				schedule(45);
				return;
			}

			mode = 'typing';
			schedule(90);
		}

		input.addEventListener('focus', function () {
			clearTimer();
			input.placeholder = '';
		});

		input.addEventListener('blur', function () {
			if (!input.value) resetAndResume();
		});

		input.addEventListener('input', function () {
			if (input.value) {
				clearTimer();
				input.placeholder = '';
			} else if (document.activeElement !== input) {
				resetAndResume();
			}
		});

			setTimeout(function () {
				resetAndResume();
			}, 200);
		});
}

initComnumPlaceholderTyping();
if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', initComnumPlaceholderTyping, false);
}
</script>
