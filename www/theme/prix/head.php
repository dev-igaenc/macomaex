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

$header_display_name = '';
$header_credit = 0;

if ($is_member) {
    $header_display_name = isset($member['mb_5']) ? trim((string)$member['mb_6']) : '';
    if ($header_display_name === '') {
        $header_display_name = isset($member['mb_nick']) ? $member['mb_nick'] : '';
    }

    $uid_sql = sql_real_escape_string((string)$member['mb_id']);
    $credit_row = sql_fetch("select credit_after from g5_matching_credit_history where uid='{$uid_sql}' order by id desc limit 1");
    if (isset($credit_row['credit_after']) && $credit_row['credit_after'] !== null && $credit_row['credit_after'] !== '') {
        $header_credit = (int)$credit_row['credit_after'];
    }
}
?>

<link rel="stylesheet" href="<?php echo G5_URL; ?>/ko/css/ai-loading.css">
<link rel="stylesheet" href="<?php echo G5_THEME_URL; ?>/css/design-tokens.css?v=20260427">
<link rel="stylesheet" href="<?php echo G5_THEME_URL; ?>/css/common.layout.css?v=20260427">
<link rel="stylesheet" href="<?php echo G5_THEME_URL; ?>/css/common.scrollbar.css?v=20260427">
<link rel="stylesheet" href="<?php echo G5_THEME_URL; ?>/css/common.ai-magic-wand.css?v=20260427">
<link rel="stylesheet" href="<?php echo G5_THEME_URL; ?>/css/header-core.css?v=20260427">
<link rel="stylesheet" href="<?php echo G5_THEME_URL; ?>/css/header-bridge.css?v=20260427">
<?php if (!defined('_INDEX_')) { ?>
<link rel="stylesheet" href="<?php echo G5_THEME_URL; ?>/css/header-search.css?v=20260427">
<?php } ?>
<!-- 상단 시작 { -->
<div id="hd">
	<div id="hd_h1"><?php echo $g5['title'] ?></div>

	<svg xmlns="http://www.w3.org/2000/svg" style="position:absolute;width:0;height:0;">
	  <defs>
		<!-- 건설 - 망치 -->
		<symbol id="cat-iso-1" viewBox="0 0 48 48"><path fill="#e53935" d="M20 8 L28 8 L28 16 L20 16 Z"></path><path fill="#c62828" d="M20 8 L20 16 L16 20 L16 12 Z"></path><path fill="#ef5350" d="M28 8 L28 16 L32 20 L32 12 Z"></path><path fill="#ff9800" d="M12 20 L20 20 L20 28 L12 28 Z"></path><path fill="#ff6f00" d="M12 20 L12 28 L8 32 L8 24 Z"></path><path fill="#ffb74d" d="M20 20 L20 28 L24 32 L24 24 Z"></path></symbol>
		<!-- 의류 - 티셔츠 -->
		<symbol id="cat-iso-2" viewBox="0 0 48 48"><path fill="#ec407a" d="M18 8 L22 8 L22 12 L18 12 Z"></path><path fill="#ad1457" d="M18 8 L18 12 L14 16 L14 10 Z"></path><path fill="#f48fb1" d="M22 8 L22 12 L26 16 L26 10 Z"></path><path fill="#ab47bc" d="M14 16 L34 16 L34 40 L14 40 Z"></path><path fill="#7b1fa2" d="M14 16 L14 40 L10 44 L10 20 Z"></path><path fill="#ba68c8" d="M34 16 L34 40 L38 44 L38 20 Z"></path></symbol>
		<!-- 화장품 - 립스틱 -->
		<symbol id="cat-iso-3" viewBox="0 0 48 48"><path fill="#e91e63" d="M20 8 L28 8 L28 12 L20 12 Z"></path><path fill="#c2185b" d="M20 8 L20 12 L18 14 L18 10 Z"></path><path fill="#f06292" d="M28 8 L28 12 L30 14 L30 10 Z"></path><path fill="#26a69a" d="M18 14 L30 14 L30 36 L18 36 Z"></path><path fill="#00897b" d="M18 14 L18 36 L16 38 L16 16 Z"></path><path fill="#4db6ac" d="M30 14 L30 36 L32 38 L32 16 Z"></path><path fill="#ff6f00" d="M22 36 L26 36 L26 40 L22 40 Z"></path></symbol>
		<!-- 식자재·식품 B2B - 식품/원재료 -->
		<symbol id="cat-iso-4" viewBox="0 0 48 48"><circle fill="#ffb74d" cx="24" cy="22" r="11"></circle><path fill="#ffa726" d="M24 11 Q21 14 24 17 Q27 14 24 11"></path><path fill="#ffe082" d="M18 22 Q20 18 24 20 Q28 18 30 22 Q28 26 24 24 Q20 26 18 22"></path><path fill="#8bc34a" d="M22 12 Q23 10 25 10 Q24 12 24 14 Q23 13 22 12"></path></symbol>
		<!-- 화학·케미컬·원료 - 라운드 플라스크/버블 -->
		<symbol id="cat-iso-5" viewBox="0 0 48 48">
		  <!-- 배경 원 -->
		  <circle fill="#e9f5ff" cx="24" cy="24" r="14"></circle>
		  <!-- 목 부분 -->
		  <rect x="20" y="10" width="8" height="8" rx="2" fill="#455a64"></rect>
		  <rect x="21" y="11" width="6" height="6" rx="1.5" fill="#607d8b"></rect>
		  <!-- 플라스크 몸통 -->
		  <path d="M18 18 H30 L31.5 24 C32 26 31.6 28.2 30.5 30 L27 35 C26 36.5 25 37 24 37 C23 37 22 36.5 21 35 L17.5 30 C16.4 28.2 16 26 16.5 24 Z" fill="#37474f"></path>
		  <!-- 액체 -->
		  <path d="M18.5 25 H29.5 C30 27.2 29.6 29 28.7 30.3 L26 33.6 C25.3 34.5 24.6 35 24 35 C23.4 35 22.7 34.5 22 33.6 L19.3 30.3 C18.4 29 18 27.2 18.5 25 Z" fill="#26a69a"></path>
		  <!-- 액체 하이라이트 -->
		  <path d="M19.5 26.2 C21.2 25.3 22.8 25 24 25 C25.2 25 26.8 25.3 28.5 26.2 L28 27.4 C26.6 26.8 25.3 26.5 24 26.5 C22.7 26.5 21.4 26.8 20 27.4 Z" fill="#4db6ac"></path>
		  <!-- 기포 -->
		  <circle cx="21" cy="23" r="1.2" fill="#b2ebf2"></circle>
		  <circle cx="26.5" cy="25.5" r="1" fill="#b2ebf2"></circle>
		  <circle cx="23.4" cy="21.2" r="0.9" fill="#b2ebf2"></circle>
		  <!-- 반사 하이라이트 -->
		  <path d="M22 19.5 C23 18.8 23.9 18.5 25 18.5 C25.6 18.5 26.2 18.6 26.8 18.8" fill="none" stroke="#ffffff" stroke-width="1.2" stroke-linecap="round" opacity="0.7"></path>
		</symbol>
		<!-- 기계·설비·산업장비 - 산업 기어/설비암 -->
		<symbol id="cat-iso-6" viewBox="0 0 48 48"><circle fill="#edf2f7" cx="24" cy="24" r="14"></circle><rect fill="#78909c" x="10" y="30" width="28" height="4" rx="1.2"></rect><path fill="#607d8b" d="M12 26 L36 26 L34 30 L14 30 Z"></path><circle fill="#546e7a" cx="20" cy="22" r="6.5"></circle><circle fill="#cfd8dc" cx="20" cy="22" r="2.4"></circle><rect fill="#90a4ae" x="19.4" y="12.5" width="1.2" height="3.8" rx="0.6"></rect><rect fill="#90a4ae" x="19.4" y="27.7" width="1.2" height="3.8" rx="0.6"></rect><rect fill="#90a4ae" x="10.5" y="21.4" width="3.8" height="1.2" rx="0.6"></rect><rect fill="#90a4ae" x="25.7" y="21.4" width="3.8" height="1.2" rx="0.6"></rect><path fill="#90a4ae" d="M13.7 16.6 L14.6 15.7 L17.3 18.4 L16.4 19.3 Z"></path><path fill="#90a4ae" d="M23.6 26.5 L24.5 25.6 L27.2 28.3 L26.3 29.2 Z"></path><path fill="#90a4ae" d="M23.6 17.5 L26.3 14.8 L27.2 15.7 L24.5 18.4 Z"></path><path fill="#90a4ae" d="M13.7 27.4 L16.4 24.7 L17.3 25.6 L14.6 28.3 Z"></path><circle fill="#455a64" cx="31.5" cy="27.5" r="4.2"></circle><circle fill="#b0bec5" cx="31.5" cy="27.5" r="1.5"></circle><path fill="#5f6f7a" d="M30 19 L35.5 13.5 L37.2 15.2 L31.7 20.7 Z"></path></symbol>
		<!-- 금속·소재 - 메탈 블록/플레이트 -->
		<symbol id="cat-iso-7" viewBox="0 0 48 48">
		  <!-- 차가운 메탈 배경 -->
		  <circle cx="24" cy="24" r="14" fill="#ECEFF1"></circle>
		  <!-- 메탈 플레이트(원근감) -->
		  <path d="M14 20 L32 16 L36 22 L18 26 Z" fill="#B0BEC5"></path>
		  <path d="M14 20 L14 28 L18 34 L18 26 Z" fill="#90A4AE"></path>
		  <path d="M18 26 L36 22 L36 30 L18 34 Z" fill="#78909C"></path>
		  <!-- 상단 빛 반사 -->
		  <path d="M15.5 20.8 L31.5 17.4 L34.2 21.4 L18.2 24.8 Z" fill="#CFD8DC"></path>
		  <!-- 리벳(볼트) -->
		  <circle cx="19.5" cy="23.5" r="0.9" fill="#ECEFF1"></circle>
		  <circle cx="25.5" cy="22.3" r="0.9" fill="#ECEFF1"></circle>
		  <circle cx="30.8" cy="21.3" r="0.9" fill="#ECEFF1"></circle>
		  <circle cx="21.2" cy="28.4" r="0.9" fill="#ECEFF1"></circle>
		  <circle cx="27.5" cy="26.9" r="0.9" fill="#ECEFF1"></circle>
		  <!-- 메탈 브러시 느낌의 라인 -->
		  <path d="M19 30 H33" stroke="#B0BEC5" stroke-width="0.8" stroke-linecap="round" opacity="0.8"></path>
		  <path d="M19.5 31.6 H31.5" stroke="#B0BEC5" stroke-width="0.7" stroke-linecap="round" opacity="0.7"></path>
		</symbol>
		<!-- 농축수산 - 농작물/물고기 -->
		<symbol id="cat-iso-8" viewBox="0 0 48 48"><path fill="#66bb6a" d="M24 12 Q20 16 24 20 Q28 16 24 12"></path><path fill="#4caf50" d="M20 20 Q24 24 28 20 Q24 28 20 20"></path><path fill="#81c784" d="M22 18 L26 18 L26 22 L22 22 Z"></path><path fill="#43a047" d="M18 24 L30 24 L30 32 L18 32 Z"></path><path fill="#388e3c" d="M18 24 L18 32 L16 34 L16 26 Z"></path><path fill="#66bb6a" d="M30 24 L30 32 L32 34 L32 26 Z"></path></symbol>
		<!-- 에너지·환경 - 태양 -->
		<symbol id="cat-iso-9" viewBox="0 0 48 48"><circle fill="#ffc107" cx="24" cy="24" r="8"></circle><path fill="#ff9800" d="M24 8 L26 8 L26 12 L24 12 Z"></path><path fill="#ff9800" d="M24 36 L26 36 L26 40 L24 40 Z"></path><path fill="#ff9800" d="M8 24 L12 24 L12 26 L8 26 Z"></path><path fill="#ff9800" d="M36 24 L40 24 L40 26 L36 26 Z"></path><path fill="#ff9800" d="M14 14 L16 16 L13 19 L11 17 Z"></path><path fill="#ff9800" d="M34 14 L36 16 L35 19 L33 17 Z"></path><path fill="#ff9800" d="M14 34 L16 32 L19 35 L17 37 Z"></path><path fill="#ff9800" d="M34 34 L32 32 L29 35 L31 37 Z"></path></symbol>
		<!-- 전자·전기·반도체 - 칩/전기 -->
		<symbol id="cat-iso-10" viewBox="0 0 48 48"><circle fill="#eaf2ff" cx="24" cy="24" r="14"></circle><rect fill="#1e88e5" x="16" y="16" width="16" height="16" rx="2"></rect><rect fill="#90caf9" x="20" y="20" width="8" height="8" rx="1.2"></rect><path fill="#42a5f5" d="M14 19 H16 V21 H14 Z M14 23 H16 V25 H14 Z M14 27 H16 V29 H14 Z M32 19 H34 V21 H32 Z M32 23 H34 V25 H32 Z M32 27 H34 V29 H32 Z M19 14 H21 V16 H19 Z M23 14 H25 V16 H23 Z M27 14 H29 V16 H27 Z M19 32 H21 V34 H19 Z M23 32 H25 V34 H23 Z M27 32 H29 V34 H27 Z"></path><path fill="#ffd54f" d="M24 18 L27.5 23 H24.8 L27 29 L21 24.5 H23.8 L22 18 Z"></path></symbol>
		<!-- 자동차·모빌리티 - 자동차 -->
		<symbol id="cat-iso-11" viewBox="0 0 48 48"><rect fill="#e1f5fe" x="8" y="20" width="32" height="12" rx="4"></rect><path fill="#29b6f6" d="M14 18 L34 18 L38 22 L10 22 Z"></path><circle fill="#37474f" cx="16" cy="30" r="3.5"></circle><circle fill="#37474f" cx="32" cy="30" r="3.5"></circle><rect fill="#ffffff" x="16" y="19" width="8" height="4" rx="1"></rect><rect fill="#ffffff" x="24" y="19" width="8" height="4" rx="1"></rect></symbol>
		<!-- 포장재·소모성 자재 - 상자/패키징 -->
		<symbol id="cat-iso-12" viewBox="0 0 48 48"><path fill="#ede7f6" d="M10 16 L38 16 L38 34 L10 34 Z"></path><path fill="#d1c4e9" d="M10 16 L24 10 L38 16 L24 22 Z"></path><path fill="#b39ddb" d="M24 22 L38 16 L38 34 L24 40 Z"></path><path fill="#9575cd" d="M24 22 L10 16 L10 34 L24 40 Z"></path><path fill="#7e57c2" d="M20 18 L24 20 L28 18 L24 16 Z"></path></symbol>
		<!-- 반도체 - 칩 -->
		<symbol id="cat-iso-13" viewBox="0 0 48 48"><path fill="#29b6f6" d="M16 16 L32 16 L32 32 L16 32 Z"></path><path fill="#0288d1" d="M16 16 L16 32 L14 34 L14 18 Z"></path><path fill="#4fc3f7" d="M32 16 L32 32 L34 34 L34 18 Z"></path><path fill="#01579b" d="M18 18 L30 18 L30 30 L18 30 Z"></path><path fill="#0277bd" d="M20 20 L28 20 L28 28 L20 28 Z"></path></symbol>
		<!-- IT·소프트웨어 - 컴퓨터 -->
		<symbol id="cat-iso-14" viewBox="0 0 48 48"><path fill="#42a5f5" d="M10 12 L38 12 L38 28 L10 28 Z"></path><path fill="#1976d2" d="M10 12 L10 28 L8 30 L8 14 Z"></path><path fill="#64b5f6" d="M38 12 L38 28 L40 30 L40 14 Z"></path><path fill="#1565c0" d="M12 14 L36 14 L36 26 L12 26 Z"></path><path fill="#1e88e5" d="M14 16 L34 16 L34 24 L14 24 Z"></path><path fill="#90caf9" d="M18 28 L30 28 L30 30 L18 30 Z"></path></symbol>
		<!-- 통신 - 신호/안테나 -->
		<symbol id="cat-iso-15" viewBox="0 0 48 48"><path fill="#5c6bc0" d="M24 8 L26 8 L26 40 L24 40 Z"></path><path fill="#3f51b5" d="M18 16 L20 16 L20 40 L18 40 Z"></path><path fill="#7986cb" d="M30 16 L32 16 L32 40 L30 40 Z"></path><path fill="#3949ab" d="M12 24 L14 24 L14 40 L12 40 Z"></path><path fill="#5c6bc0" d="M36 24 L38 24 L38 40 L36 40 Z"></path></symbol>
		<!-- 토목 - 다리 -->
		<symbol id="cat-iso-16" viewBox="0 0 48 48"><path fill="#78909c" d="M8 28 L40 28 L40 32 L8 32 Z"></path><path fill="#546e7a" d="M8 28 L8 32 L6 34 L6 30 Z"></path><path fill="#90a4ae" d="M40 28 L40 32 L42 34 L42 30 Z"></path><path fill="#607d8b" d="M12 20 L16 20 L16 28 L12 28 Z"></path><path fill="#455a64" d="M32 20 L36 20 L36 28 L32 28 Z"></path><path fill="#78909c" d="M20 16 L28 16 L28 28 L20 28 Z"></path></symbol>
		<!-- 의료·헬스케어 - 십자가/케어 -->
		<symbol id="cat-iso-17" viewBox="0 0 48 48"><circle fill="#ffebee" cx="24" cy="24" r="14"></circle><circle fill="#e53935" cx="24" cy="24" r="9"></circle><path fill="#ffffff" d="M23 18 L25 18 L25 22 L29 22 L29 24 L25 24 L25 28 L23 28 L23 24 L19 24 L19 22 L23 22 Z"></path></symbol>
		<!-- 인력 - 사람 -->
		<symbol id="cat-iso-18" viewBox="0 0 48 48"><circle fill="#66bb6a" cx="24" cy="16" r="6"></circle><path fill="#4caf50" d="M16 28 Q16 24 20 24 L28 24 Q32 24 32 28 L32 36 L16 36 Z"></path><path fill="#43a047" d="M18 30 L30 30 L30 36 L18 36 Z"></path></symbol>
		<!-- 연구·기술 - 현미경/전구 -->
		<symbol id="cat-iso-19" viewBox="0 0 48 48"><circle fill="#ab47bc" cx="24" cy="20" r="8" opacity="0.3"></circle><path fill="#9c27b0" d="M20 12 L28 12 L28 16 L20 16 Z"></path><path fill="#ba68c8" d="M22 16 L26 16 L26 24 L22 24 Z"></path><path fill="#7b1fa2" d="M18 24 L30 24 L30 28 L18 28 Z"></path><path fill="#ce93d8" d="M24 28 L24 32 L22 34 L26 34 Z"></path></symbol>
		<!-- 공통 아이콘 - 요술봉 -->
		<symbol id="icon-magic-wand" viewBox="0 0 24 24"><path fill="currentColor" d="M16.8 2.2l1.1 2.3 2.3 1.1-2.3 1.1-1.1 2.3-1.1-2.3-2.3-1.1 2.3-1.1 1.1-2.3zm3.2 2.8.6 1.1 1.1.6-1.1.6-.6 1.1-.6-1.1-1.1-.6 1.1-.6.6-1.1zM7.4 17.4 16.1 8.7l2.6 2.6-8.7 8.7-2.6-2.6zm-1 2.4 1.3 1.3-3.1 1 .8-2.9z"></path></symbol>
		<!-- 전체보기 - 그리드 (4칸) -->
		<symbol id="cat-iso-all" viewBox="0 0 48 48"><path fill="#2196f3" d="M8 8 L26 8 L26 26 L8 26 Z"></path><path fill="#ffc107" d="M28 8 L46 8 L46 26 L28 26 Z"></path><path fill="#4caf50" d="M8 28 L26 28 L26 46 L8 46 Z"></path><path fill="#e53935" d="M28 28 L46 28 L46 46 L28 46 Z"></path></symbol>
	  </defs>
	</svg>

	<?php if(defined('_INDEX_')) { // index에서만 실행
		include G5_BBS_PATH.'/newwin.inc.php'; // 팝업레이어
	} ?>

	<div id="header" class="hh"><!-- class="hh" --><p class="gnbBg"></p><div class="inConts">
			<div class="header-left">
				<h1><a href="<?php echo G5_URL ?>/index.php">
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
				<a href="<?php echo G5_BBS_URL ?>/register.php" class="menu_txt">회원가입</a>
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

    <div id="sideMenuArea">
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
                    <a href="/ko/plan.php" class="sideMenu_plan_card_title">
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
                    <a href="<?php echo G5_URL; ?>/index.php">
                        <span class="snav-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg></span>홈
                    </a>
                </li>
                <li class="one">
                    <a href="<?php echo $link_0302; ?>">
                        <span class="snav-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"></rect><rect x="14" y="3" width="7" height="7" rx="1.5"></rect><rect x="3" y="14" width="7" height="7" rx="1.5"></rect><rect x="14" y="14" width="7" height="7" rx="1.5"></rect></svg></span>기업 목록
                    </a>
                </li>
                <li class="one">
                    <a href="/ko/company_list_ai_demo.php?entry=matching_ai_demo">
                        <span class="snav-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg></span>AI 추천
                    </a>
                </li>

                <li class="side-divider"></li>

                <li class="one">
                    <a href="<?php echo $link_0101; ?>">
                        <span class="snav-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg></span>매커멕스란
                    </a>
                </li>
                <li class="one">
                    <a href="<?php echo $link_0401; ?>">
                        <span class="snav-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 16L3 6l5.5 5L12 2l3.5 9L21 6l-2 10H5z"></path><line x1="5" y1="20" x2="19" y2="20"></line></svg></span>크레딧 요금제
                    </a>
                </li>

                <li class="side-divider"></li>

                <li class="side-toggle" data-group="support">
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

                <li class="side-toggle" data-group="mypage">
                    <span class="side-toggle-left">
                        <span class="snav-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg></span>
                        마이페이지
                    </span>
                    <span class="side-chev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><polyline points="6 9 12 15 18 9"></polyline></svg></span>
                </li>
                <li class="sub one" data-gi="mypage" style="display:none"><a href="<?php echo $link_0601; ?>">내 회사 정보</a></li>
                <li class="sub one" data-gi="mypage" style="display:none"><a href="<?php echo $link_0602; ?>">거래 히스토리</a></li>
                <li class="sub one" data-gi="mypage" style="display:none"><a href="<?php echo $link_0606; ?>">셀렉티드</a></li>
                <li class="sub one" data-gi="mypage" style="display:none"><a href="<?php echo $link_0603; ?>">관심기업</a></li>
                <li class="sub one" data-gi="mypage" style="display:none"><a href="<?php echo $link_0604; ?>">회원정보수정</a></li>
                <li class="sub one" data-gi="mypage" style="display:none"><a href="<?php echo $link_0605; ?>">결제정보</a></li>
            </ul>
			<div class="all_user">
				<?php if (!$is_member) { ?>
					<a href="<?php echo G5_BBS_URL ?>/login.php?url=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="side-auth side-login">로그인</a>
					<a href="<?php echo G5_BBS_URL ?>/register.php" class="side-auth side-signup">회원가입</a>
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
                if (area) area.classList.remove('open');
            });
        }

        document.querySelectorAll('#sideMenuArea .side-toggle').forEach(function(toggle) {
            toggle.addEventListener('click', function() {
                var group = toggle.getAttribute('data-group');
                var isOpen = toggle.classList.contains('open');
                var subs = document.querySelectorAll('#sideMenuArea .sub[data-gi="' + group + '"]');
                toggle.classList.toggle('open', !isOpen);
                subs.forEach(function(item) {
                    item.style.display = isOpen ? 'none' : 'block';
                });
            });
        });
    })();
    </script>

	<div id="menuArea" class="menuLayer">
		<div id="menu" class="gnb">
            <ul class="all_user all_layout">
                <?php if (!$is_member) {  ?>
                    <li class="one"><a href="<?php echo G5_BBS_URL ?>/login.php?url=<?=$_SERVER['REQUEST_URI']?>" class="menu_txt">로그인</a></li>
                    <li class="one"><a href="<?php echo G5_BBS_URL ?>/register.php" class="menu_txt">회원가입</a></li>
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
<div id="wrapper">
	<?php if (!defined("_INDEX_")) { ?>
	<div id="container_wr">


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
                    <p></p>
                    <!-- <?php if($bo_text != ""){?> -->
                        <!-- <p><?php echo $bo_text ?></p> -->
                    <!-- <?php } ?> -->
                </div>
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
            </div>
			</div>
			<?php } ?>
			<div id="container">
	<?php } ?>

<!-- 사업자 확인 모달 (돋보기 클릭 후 표시) -->
<div class="modal_verify_overlay" id="modal_verify_overlay" aria-hidden="true">
    <div class="modal_verify" role="dialog" aria-labelledby="modal_verify_company" aria-modal="true">
        <button type="button" class="modal_verify_close" id="modal_verify_btn_close" aria-label="닫기">×</button>
        <div class="modal_verify_icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"></path></svg>
        </div>
        <h2 class="modal_verify_company" id="modal_verify_company"></h2>
        <p class="modal_verify_text">귀사는 <strong id="modal_verify_bzc"></strong><br class="modal_verify_br_pc"><strong id="modal_verify_type"></strong>으로 확인되었습니다.</p>
        <p class="modal_verify_text2">본 서비스는 귀사와 동일·유사 업종의<br class="modal_verify_br_pc">실제 거래 데이터를 분석해<br class="modal_verify_br_pc">적합한 거래처를 추천해드립니다.</p>
        <hr class="modal_verify_hr">
        <p class="modal_verify_guide">추천 결과를 확인해보세요.</p>
        <button type="button" class="modal_verify_btn" id="modal_verify_btn_ai">
            <span class="sparkle" aria-hidden="true"><svg viewBox="0 0 24 24"><use href="#icon-magic-wand"></use></svg></span>
            거래처 확인하기
        </button>
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
        <button type="button" class="modal_verify_btn" id="modal_ai_guide_btn_ok">확인했어요</button>
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
function openAiGuideModal(opts) {
	var overlay = document.getElementById("modal_ai_guide_overlay");
	var titleEl = document.getElementById("modal_ai_guide_title");
	var textEl = document.getElementById("modal_ai_guide_text");
	var text2El = document.getElementById("modal_ai_guide_text2");
	if (!overlay) return;
	opts = opts || {};
	if (titleEl) titleEl.textContent = opts.title || "AI 추천 안내";
	if (textEl) textEl.textContent = opts.text || "사업자 번호를 입력하고 AI 추천받기를 눌러주세요.";
	if (text2El) text2El.textContent = opts.text2 || "AI 분석을 통해 업종/거래 데이터를 기반으로 적합한 거래처 판매처를 추천해드립니다.";
	overlay.classList.add("is_open");
	overlay.setAttribute("aria-hidden", "false");
}

function openGuide(title, text, text2) {
	openAiGuideModal({
		title: title || "AI 추천 안내",
		text: text || "사업자 번호를 입력하고 AI 추천받기를 눌러주세요.",
		text2: text2 || "AI 분석을 통해 업종/거래 데이터를 기반으로 적합한 거래처 판매처를 추천해드립니다."
	});
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
}

function closeVerifyModal() {
	var overlay = document.getElementById("modal_verify_overlay");
	if (overlay && overlay.classList.contains("is_open")) {
		overlay.classList.remove("is_open");
		overlay.setAttribute("aria-hidden", "true");
	}
}

function moveToAiRecommendByBizno(comnum) {
	// 검색창 사업자번호 흐름은 AI 목록 페이지로 직접 이동
	var digits = (comnum || "").replace(/[^0-9]/g, "");
	if (digits.length !== 10) return false;
	window.location.href = "/ko/company_list_ai.php?bizno=" + encodeURIComponent(digits) + "&type=buyer";
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

	// cominfo 보정 후 최종 진입 URL은 company_list_ai.php(bizno)로 통일
	$.post("<?php echo G5_URL; ?>/get_set.php", { type: "main_comp_chk", comnum: comnum }, function(res) {
		try {
			var parts = (res + "").split("|");
			if (parts[0] === "1000") {
				if (overlay) {
					overlay.classList.remove("is_open");
					overlay.setAttribute("aria-hidden", "true");
				}
				if ($form && $form.length) {
					$form.find("input[name='cominfo']").val(parts[1] || "");
					moveToAiRecommendByBizno(comnum);
				}
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

	// 1단계: DB에서 기업명 조회하여 모달 띄우기
		$.post("<?php echo G5_URL; ?>/get_company_name.php", { comnum: comnumDigits }, function(res) {
			try {
				var data = JSON.parse(res);
				if (data.result === 'success') {
					var overlay = document.getElementById("modal_verify_overlay");
				var companyEl = document.getElementById("modal_verify_company");
				var bzcEl = document.getElementById("modal_verify_bzc");
				var typeEl = document.getElementById("modal_verify_type");
				var cominfoVal = data.cominfo ? data.cominfo : "";

				if ($form && $form.length) {
					$form.find("input[name='cominfo']").val(cominfoVal);
				}

				if (companyEl) companyEl.textContent = data.name;
				if (bzcEl) bzcEl.textContent = data.bzc_name;
				if (typeEl) typeEl.textContent = data.enp_type;

				if (overlay) {
					overlay.classList.add("is_open");
					overlay.setAttribute("aria-hidden", "false");
					var actionBtn = document.getElementById("modal_verify_btn_ai");
					if (actionBtn) actionBtn.focus();
				}
				} else {
					if ($form && $form.length) {
						$form.find("input[name='cominfo']").val("");
					}
					// DB/캐시 miss 시 main_comp_chk로 실시간 재조회
					requestMainCompCheck(comnumDigits, $form);
					}
				} catch(e) {
				console.error("Lookup error:", e);
				requestMainCompCheck(comnumDigits, $form);
			}
		}).fail(function() {
			requestMainCompCheck(comnumDigits, $form);
		});
	});

// 모달 내부 버튼 이벤트
$(document).on("click", "#modal_verify_btn_ai", function() {
	var btn = this;
	var overlay = document.getElementById("modal_verify_overlay");
	var $input = window.currentSearchInput;
	var $form = window.currentSearchForm;

	if (!$input || !$form) return;

	var comnum = $input.val();
	var existingCominfo = ($form && $form.length) ? $form.find("input[name='cominfo']").val() : "";

	if (comnum.replace(/[^0-9]/g, "").length === 10) {
		if (existingCominfo) {
			if (overlay) {
				overlay.classList.remove("is_open");
				overlay.setAttribute("aria-hidden", "true");
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
				originalHtml: originalHtml
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
$(document).on("click", "#modal_ai_guide_btn_close, #modal_ai_guide_btn_ok", function() {
	closeAiGuideModal();
	if (window.currentSearchInput) window.currentSearchInput.focus();
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

	inputs.forEach(function (input) {
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
	document.addEventListener('DOMContentLoaded', initComnumPlaceholderTyping, { once: true });
}
</script>
