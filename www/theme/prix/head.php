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
?>

	<!-- 상단 시작 { -->
	<div id="hd">
		<div id="hd_h1"><?php echo $g5['title'] ?></div>

		<svg xmlns="http://www.w3.org/2000/svg" style="position:absolute;width:0;height:0;">
		  <defs>
			<symbol id="cat-iso-5" viewBox="0 0 48 48">
			  <circle fill="#e9f5ff" cx="24" cy="24" r="14"></circle>
			  <rect x="20" y="10" width="8" height="8" rx="2" fill="#455a64"></rect>
			  <rect x="21" y="11" width="6" height="6" rx="1.5" fill="#607d8b"></rect>
			  <path d="M18 18 H30 L31.5 24 C32 26 31.6 28.2 30.5 30 L27 35 C26 36.5 25 37 24 37 C23 37 22 36.5 21 35 L17.5 30 C16.4 28.2 16 26 16.5 24 Z" fill="#37474f"></path>
			  <path d="M18.5 25 H29.5 C30 27.2 29.6 29 28.7 30.3 L26 33.6 C25.3 34.5 24.6 35 24 35 C23.4 35 22.7 34.5 22 33.6 L19.3 30.3 C18.4 29 18 27.2 18.5 25 Z" fill="#26a69a"></path>
			  <path d="M19.5 26.2 C21.2 25.3 22.8 25 24 25 C25.2 25 26.8 25.3 28.5 26.2 L28 27.4 C26.6 26.8 25.3 26.5 24 26.5 C22.7 26.5 21.4 26.8 20 27.4 Z" fill="#4db6ac"></path>
			  <circle cx="21" cy="23" r="1.2" fill="#b2ebf2"></circle>
			  <circle cx="26.5" cy="25.5" r="1" fill="#b2ebf2"></circle>
			  <circle cx="23.4" cy="21.2" r="0.9" fill="#b2ebf2"></circle>
			  <path d="M22 19.5 C23 18.8 23.9 18.5 25 18.5 C25.6 18.5 26.2 18.6 26.8 18.8" fill="none" stroke="#ffffff" stroke-width="1.2" stroke-linecap="round" opacity="0.7"></path>
			</symbol>
			<symbol id="cat-iso-6" viewBox="0 0 48 48"><circle fill="#edf2f7" cx="24" cy="24" r="14"></circle><rect fill="#78909c" x="10" y="30" width="28" height="4" rx="1.2"></rect><path fill="#607d8b" d="M12 26 L36 26 L34 30 L14 30 Z"></path><circle fill="#546e7a" cx="20" cy="22" r="6.5"></circle><circle fill="#cfd8dc" cx="20" cy="22" r="2.4"></circle><rect fill="#90a4ae" x="19.4" y="12.5" width="1.2" height="3.8" rx="0.6"></rect><rect fill="#90a4ae" x="19.4" y="27.7" width="1.2" height="3.8" rx="0.6"></rect><rect fill="#90a4ae" x="10.5" y="21.4" width="3.8" height="1.2" rx="0.6"></rect><rect fill="#90a4ae" x="25.7" y="21.4" width="3.8" height="1.2" rx="0.6"></rect><path fill="#90a4ae" d="M13.7 16.6 L14.6 15.7 L17.3 18.4 L16.4 19.3 Z"></path><path fill="#90a4ae" d="M23.6 26.5 L24.5 25.6 L27.2 28.3 L26.3 29.2 Z"></path><path fill="#90a4ae" d="M23.6 17.5 L26.3 14.8 L27.2 15.7 L24.5 18.4 Z"></path><path fill="#90a4ae" d="M13.7 27.4 L16.4 24.7 L17.3 25.6 L14.6 28.3 Z"></path><circle fill="#455a64" cx="31.5" cy="27.5" r="4.2"></circle><circle fill="#b0bec5" cx="31.5" cy="27.5" r="1.5"></circle><path fill="#5f6f7a" d="M30 19 L35.5 13.5 L37.2 15.2 L31.7 20.7 Z"></path></symbol>
			<symbol id="cat-iso-7" viewBox="0 0 48 48">
			  <circle cx="24" cy="24" r="14" fill="#ECEFF1"></circle>
			  <path d="M14 20 L32 16 L36 22 L18 26 Z" fill="#B0BEC5"></path>
			  <path d="M14 20 L14 28 L18 34 L18 26 Z" fill="#90A4AE"></path>
			  <path d="M18 26 L36 22 L36 30 L18 34 Z" fill="#78909C"></path>
			  <path d="M15.5 20.8 L31.5 17.4 L34.2 21.4 L18.2 24.8 Z" fill="#CFD8DC"></path>
			  <circle cx="19.5" cy="23.5" r="0.9" fill="#ECEFF1"></circle>
			  <circle cx="25.5" cy="22.3" r="0.9" fill="#ECEFF1"></circle>
			  <circle cx="30.8" cy="21.3" r="0.9" fill="#ECEFF1"></circle>
			  <circle cx="21.2" cy="28.4" r="0.9" fill="#ECEFF1"></circle>
			  <circle cx="27.5" cy="26.9" r="0.9" fill="#ECEFF1"></circle>
			  <path d="M19 30 H33" stroke="#B0BEC5" stroke-width="0.8" stroke-linecap="round" opacity="0.8"></path>
			  <path d="M19.5 31.6 H31.5" stroke="#B0BEC5" stroke-width="0.7" stroke-linecap="round" opacity="0.7"></path>
			</symbol>
			<symbol id="cat-iso-8" viewBox="0 0 48 48"><path fill="#66bb6a" d="M24 12 Q20 16 24 20 Q28 16 24 12"></path><path fill="#4caf50" d="M20 20 Q24 24 28 20 Q24 28 20 20"></path><path fill="#81c784" d="M22 18 L26 18 L26 22 L22 22 Z"></path><path fill="#43a047" d="M18 24 L30 24 L30 32 L18 32 Z"></path><path fill="#388e3c" d="M18 24 L18 32 L16 34 L16 26 Z"></path><path fill="#66bb6a" d="M30 24 L30 32 L32 34 L32 26 Z"></path></symbol>
			<symbol id="icon-magic-wand" viewBox="0 0 24 24"><path fill="currentColor" d="M16.8 2.2l1.1 2.3 2.3 1.1-2.3 1.1-1.1 2.3-1.1-2.3-2.3-1.1 2.3-1.1 1.1-2.3zm3.2 2.8.6 1.1 1.1.6-1.1.6-.6 1.1-.6-1.1-1.1-.6 1.1-.6.6-1.1zM7.4 17.4 16.1 8.7l2.6 2.6-8.7 8.7-2.6-2.6zm-1 2.4 1.3 1.3-3.1 1 .8-2.9z"></path></symbol>
		  </defs>
		</svg>

		<?php if(defined('_INDEX_')) { // index에서만 실행
			include G5_BBS_PATH.'/newwin.inc.php'; // 팝업레이어
		} ?>

	<div id="header" class="hh"><!-- class="hh" --><p class="gnbBg"></p><div class="inConts">
		<h1><a href="<?php echo G5_URL ?>/index.php">
			<img src="/img/logo.svg" alt="<?php echo $config['cf_title']; ?>">
		</a></h1>

		<div id="pcMenu">
			<ul id="list">
				<script type="text/javascript">globalNavigationBar();</script>
				<?php // include(G5_THEME_PATH.'/menu.php'); ?>
                <!-- </li> -->
                <!-- <li>
                    <a href="<?=$link_1101?>"><span>마이페이지</span></a>
                    <ul class="gnbSub d2">
                        <li><a href="<?=$link_1101?>"><span>매칭 히스토리</span></a></li>
                        <li><a href="<?=$link_1102?>"><span>관심기업</span></a></li>
                        <li><a href="<?=$link_1103?>"><span>회원정보수정</span></a>
                        <li><a href="<?=$link_1104?>"><span>결제정보</span></a>
                    </li></ul>
                </li> -->
			</ul>
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
        <ul id="sideMenu">
            <div class="sideMenu_top">
                <div class="logo"><img src="/img/logo.svg" alt=""></div>
                <button type="button" class="mMenu"><span></span></button>
            </div>

            <div class="all_user all_layout">
                <?php if (!$is_member) {  ?>
                    <li class="one"><a href="<?php echo G5_BBS_URL ?>/login.php?url=<?=$_SERVER['REQUEST_URI']?>" class="menu_txt">로그인</a></li>
                    <li class="one"><a href="<?php echo G5_BBS_URL ?>/register.php" class="menu_txt">회원가입</a></li>
                <?php }else{  ?>
                    <li class="one"><a href="<?php echo G5_BBS_URL ?>/logout.php" class="menu_txt">로그아웃</a></li>

                    <?if($member['mb_level']>9) {?>
                    <li><a href="/backoffice/" class="menu_txt"><span>관리자</span></a></li>
                    <?}else{?>
						<!--
                    <li>
                        <a href="#">마이페이지</a>
                        <ul class="gnbSub d2">
							<li><a href="<?=$link_0601?>"><span>내 회사 정보</span></a></li>
                            <li><a href="<?=$link_0602?>"><span>매칭 히스토리</span></a></li>
                            <li><a href="<?=$link_0603?>"><span>관심기업</span></a></li>
                            <li><a href="<?=$link_0604?>"><span>회원 정보 수정</span></a></li>
                            <li><a href="<?=$link_0605?>"><span>결제정보</span></a></li>
                        </ul>
                    </li>
					 -->
                    <?}?>
                <?php }  ?>
            </div>

			<div class="all_menu all_layout">
                <script type="text/javascript">globalNavigationBar();</script>
            </div>
        </ul>
    </div>

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
                    <!-- <li><a href="<?php echo G5_BBS_URL ?>/member_confirm.php?url=/bbs/register_form.php" class="menu_txt"><span>마이페이지</span></a></li>
                    <li>
                        <a href="#">마이페이지</a>
                        <ul class="gnbSub d2">
                            <li><a href="<?=$link_1101?>"><span>매칭 히스토리</span></a></li>
                            <li><a href="<?=$link_1102?>"><span>관심기업</span></a></li>
                            <li><a href="<?=$link_1103?>"><span>회원 정보 수정</span></a></li>
                            <li><a href="<?=$link_1104?>"><span>결제정보</span></a></li>
                        </ul>
                    </li>
					 -->
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


		<?php if($depth_1 == 3){ ?>
			<div id="sVisual" class="bg_<?=$depth_1?>">
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
				<h2>
					<strong data-aos="fade-down"><?=$gr_subject?></strong>
					<span data-aos="fade-up" data-aos-delay="100"><?=$gr_text?></span>
				</h2>
			</div>
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
			<div id="container">
		<?php } ?>
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

		<script>
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
						$form.submit();
					}
				} else {
					alert("기업 정보를 분석하는 중 오류가 발생했습니다.\n" + (parts[2] || parts[1] || "다시 시도해 주세요."));
					restoreButton();
				}
			} catch (e) {
				console.error("main_comp_chk parse error:", e);
				restoreButton();
			}
		}).fail(function() {
			alert("서버 통신 중 오류가 발생했습니다.");
			restoreButton();
		});
	}

	$(document).on("click", "#main_submit, #hd_submit, #sub_submit", function(e){
		e.preventDefault();
		var $this = $(this);
		var $input;
		var $form;

		if ($this.attr("id") === "hd_submit") {
			$input = $("#comnum_hd");
			$form = $("#match_hd");
		} else if ($this.attr("id") === "main_submit") {
			$input = $("#comnum");
			$form = $("#match");
		} else {
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

		if (comnumDigits.length !== 10) {
			openAiGuideModal({
				title: "사업자 번호 재확인",
				text: "입력하신 사업자 번호 형식이 올바르지 않습니다.",
				text2: "사업자 번호를 다시 확인한 뒤 AI 추천받기를 눌러주세요."
			});
			return false;
		}

		window.currentSearchInput = $input;
		window.currentSearchForm = $form;

		$.post("<?php echo G5_URL; ?>/get_company_name.php", { comnum: comnumDigits }, function(res) {
			try {
				var data = JSON.parse(res);
				if (data.result === "success") {
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
					}
				} else {
					if ($form && $form.length) {
						$form.find("input[name='cominfo']").val("");
					}
					openAiGuideModal({
						title: "기업 정보 없음",
						text: "입력하신 사업자 번호로 등록된 기업 정보를 찾지 못했습니다.",
						text2: "사업자 번호를 다시 확인해 주세요."
					});
				}
			} catch (e) {
				console.error("Lookup error:", e);
				requestMainCompCheck(comnumDigits, $form);
			}
		}).fail(function() {
			requestMainCompCheck(comnumDigits, $form);
		});
	});

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
				$form.submit();
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
			alert("사업자 번호를 다시 확인해 주세요.");
		}
	});

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

	$(document).on("keyup", ".comnum_chk", function(){
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
	</script>
