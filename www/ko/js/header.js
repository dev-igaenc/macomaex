/**
 * 공통 헤더 모듈 — 단일 파일로 모든 페이지 헤더 관리
 *
 * 사용법:
 *   <div id="site-header" data-variant="search|nav|none"></div>
 *   <script src="js/header.js"></script>
 *
 * data-variant 값:
 *   "search" — 사업자번호 AI 추천 검색창 표시 (기본값)
 *   "nav"    — PC 메뉴 링크 표시
 *   "none"   — 로고 + 로그인/회원가입/햄버거만 표시
 *
 * 공통 (항상 표시): MACOMAEX 로고, 로그인, 회원가입, 햄버거
 */
(function () {
  var el = document.getElementById('site-header');
  if (!el) return;

  // data-variant 우선, 하위 호환으로 data-search도 지원
  var variant = el.getAttribute('data-variant');
  if (!variant) {
    variant = el.getAttribute('data-search') === 'false' ? 'nav' : 'search';
  }
  var showSearch = variant === 'search';

  // header-core.css 자동 로드
  if (!document.querySelector('link[href*="css/header-core.css"]')) {
    var coreLink = document.createElement('link');
    coreLink.rel = 'stylesheet';
    coreLink.href = 'css/header-core.css';
    document.head.appendChild(coreLink);
  }

  // header 스타일 보정
  var menuFix = document.createElement('style');
  menuFix.textContent = [
    '.list-header_logo_img{height:28px;width:auto;display:block;}',
    '@media(max-width:768px){.list-header_logo_img{height:20px;}}',
    '@media(max-width:480px){.list-header_logo_img{height:16px;}}',
    '.mMenu{overflow:visible!important;width:36px!important;height:36px!important;}',
    '.mMenu::before{top:calc(50% - 5px)!important;left:50%!important;width:70%!important;}',
    '.mMenu::after{top:calc(50% + 5px)!important;left:50%!important;width:45%!important;}',
    '#header .inConts{max-width:1200px!important;padding:0 24px!important;margin:0 auto!important;box-sizing:border-box!important;height:60px!important;display:flex!important;align-items:center!important;justify-content:space-between!important;gap:24px!important;}',
    '#header h1{display:inline-flex!important;align-items:center!important;}',
    '#header h1 a{width:auto!important;display:inline-flex!important;align-items:center!important;text-decoration:none!important;}',
    '#header .right-utill{display:flex!important;gap:20px!important;align-items:center!important;margin-left:auto!important;margin-right:12px!important;}',
    '#header .right-utill a{font-size:14px!important;font-weight:500!important;color:#555!important;text-decoration:none!important;}',
    '#header .inConts>*{align-items:center!important;}',
    '#header .header-left{flex:1!important;min-width:0!important;display:flex!important;align-items:center!important;}',
    '#sideMenuArea{position:fixed!important;top:0!important;left:0!important;width:100%!important;height:100%!important;right:auto!important;opacity:0!important;visibility:hidden!important;pointer-events:none!important;transition:opacity 0.26s ease,visibility 0.26s ease!important;}',
    '#sideMenuArea.open{opacity:1!important;visibility:visible!important;pointer-events:all!important;}',
    '#sideMenu{padding:0!important;display:flex!important;flex-direction:column!important;max-width:290px!important;width:80vw!important;box-shadow:-8px 0 32px rgba(0,0,0,0.1)!important;position:absolute!important;top:0!important;right:0!important;height:100%!important;transform:translateX(100%)!important;transition:transform 0.28s cubic-bezier(0.4,0,0.2,1)!important;}',
    '#sideMenuArea.open #sideMenu{transform:translateX(0)!important;}',
    '#sideMenu .sideMenu_top{padding:24px 20px 20px!important;border-bottom:1px solid #f0f0f0!important;margin:0!important;display:flex!important;align-items:center!important;justify-content:space-between!important;}',
    '#sideMenu .sideMenu_top .logo{font-size:15px!important;font-weight:800!important;color:#111!important;width:auto!important;letter-spacing:-0.5px!important;}',
    '.all_user{display:flex!important;gap:8px!important;padding:16px 20px!important;border-bottom:1px solid #f0f0f0!important;margin:0!important;}',
    '.all_layout{padding:10px 20px 40px!important;margin:0!important;list-style:none!important;}',
    '.all_layout li a{text-decoration:none!important;font-family:inherit!important;}',
    '.all_layout .one a{display:flex!important;align-items:center!important;gap:11px!important;padding:8px 10px!important;margin:1px -10px!important;border-radius:9px!important;font-size:14px!important;font-weight:500!important;color:#222!important;line-height:1!important;}',
    '.snav-ic{width:32px!important;height:32px!important;border-radius:8px!important;background:#f5f4ff!important;display:flex!important;align-items:center!important;justify-content:center!important;flex-shrink:0!important;color:#6655ee!important;}',
    '.snav-ic svg{display:block!important;width:16px!important;height:16px!important;}',
    '.all_layout .side-toggle{display:flex!important;align-items:center!important;justify-content:space-between!important;padding:8px 10px!important;margin:1px -10px!important;border-radius:9px!important;font-size:14px!important;font-weight:500!important;color:#222!important;cursor:pointer!important;list-style:none!important;user-select:none!important;}',
    '.side-toggle-left{display:flex!important;align-items:center!important;gap:11px!important;}',
    '.all_layout .side-toggle.open{color:#452EFF!important;}',
    '.side-chev{color:#ccc!important;display:flex!important;align-items:center!important;flex-shrink:0!important;}',
    '.all_layout .side-toggle.open .side-chev{transform:rotate(180deg)!important;color:#452EFF!important;}',
    '.all_layout .sub a{display:block!important;padding:7px 10px 7px 53px!important;margin:0 -10px!important;border-radius:7px!important;font-size:13px!important;font-weight:400!important;color:#888!important;line-height:1!important;border-left:none!important;}',
  ].join(' ');
  document.head.appendChild(menuFix);

  // 사파리 포함 모든 브라우저 폰트 호환
  if (!document.querySelector('link[href*="pretendard"]')) {
    var fontLink = document.createElement('link');
    fontLink.rel = 'stylesheet';
    fontLink.href = 'https://fastly.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css';
    document.head.appendChild(fontLink);
  }

  // 검색폼 CSS 동적 로드
  if (showSearch) {
    var link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'css/header-search.css';
    document.head.appendChild(link);
  }

  var searchForm =
    '<form class="header-search-form" id="match" role="search">' +
      '<input type="hidden" name="cominfo" id="cominfo">' +
      '<input type="text" name="comnum" id="comnum" class="header-search-input comnum_chk" placeholder="사업자등록번호를 입력해보세요" aria-label="사업자번호 검색" value="" maxlength="12" inputmode="numeric" pattern="[0-9\\-]*">' +
      '<button type="submit" class="header-search-btn" id="main_submit" aria-label="AI 추천받기">' +
        '<span class="ai-magic-wand"><svg viewBox="0 0 24 24"><path d="M16.8 2.2l1.1 2.3 2.3 1.1-2.3 1.1-1.1 2.3-1.1-2.3-2.3-1.1 2.3-1.1 1.1-2.3zm3.2 2.8.6 1.1 1.1.6-1.1.6-.6 1.1-.6-1.1-1.1-.6 1.1-.6.6-1.1zM7.4 17.4 16.1 8.7l2.6 2.6-8.7 8.7-2.6-2.6zm-1 2.4 1.3 1.3-3.1 1 .8-2.9z"/></svg></span>' +
        '<span>AI 추천받기</span>' +
      '</button>' +
    '</form>';

  var pcMenu =
    '<div id="pcMenu">' +
      '<ul id="list">' +
        '<li><a href="/pages/matching/matching_mfr.php"><span>기업거래</span></a></li>' +
        '<li><a href="/pages/shorts/shorts.php"><span>기업홍보관</span></a></li>' +
        '<li><a href="/pages/payment/payment.php"><span>크레딧 요금제</span></a></li>' +
        '<li><a href="/pages/board/board.list.php?board_no=55"><span>커뮤니티</span></a></li>' +
      '</ul>' +
    '</div>';

  var html =
    '<header id="header">' +
      '<div class="inConts">' +
        '<div class="header-left">' +
          '<h1>' +
            '<a href="/" style="display:inline-flex;align-items:center;text-decoration:none;" aria-label="매커멕스 홈">' +
              '<img src="../img/logo.svg" alt="MACOMAEX" class="list-header_logo_img">' +
            '</a>' +
          '</h1>' +
          (variant === 'search' ? searchForm : variant === 'nav' ? pcMenu : '') +
        '</div>' +
        '<div class="right-utill">' +
          '<a href="login.html">로그인</a>' +
          '<a href="/pages/userprocess/signUp.php">회원가입</a>' +
        '</div>' +
        '<a href="#none" class="mMenu" style="display:block;overflow:visible;"></a>' +
      '</div>' +
    '</header>' +

    '<div id="sideMenuArea">' +
      '<div id="sideMenu">' +

        // ── 상단: 로고 + 닫기 버튼
        '<div class="sideMenu_top">' +
          '<div class="logo"><img src="../img/logo.svg" alt="MACOMAEX" style="height:24px;width:auto;display:block;"></div>' +
          '<button class="sideMenu_close_btn" aria-label="닫기">' +
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" width="16" height="16"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>' +
          '</button>' +
        '</div>' +

        // ── 로그인 / 회원가입 버튼
        '<div class="all_user">' +
          '<a href="login.html" class="side-auth side-login">로그인</a>' +
          '<a href="/pages/userprocess/signUp.php" class="side-auth side-signup">회원가입</a>' +
        '</div>' +

        // ── 메뉴 목록
        '<ul class="all_layout">' +

          // 주요 탐색
          '<li class="one"><a href="/">' +
            '<span class="snav-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span>홈</a></li>' +
          '<li class="one"><a href="company_list.html?cat=all">' +
            '<span class="snav-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg></span>기업 목록</a></li>' +
          '<li class="one"><a href="/recommendations/demo?entry=matching_ai_demo">' +
            '<span class="snav-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></span>AI 추천</a></li>' +

          '<li class="side-divider"></li>' +

          // 서비스 소개
          '<li class="one"><a href="about.html">' +
            '<span class="snav-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></span>매커멕스란</a></li>' +
          '<li class="one"><a href="plan.html">' +
            '<span class="snav-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 16L3 6l5.5 5L12 2l3.5 9L21 6l-2 10H5z"/><line x1="5" y1="20" x2="19" y2="20"/></svg></span>크레딧 요금제</a></li>' +

          '<li class="side-divider"></li>' +

          // 이용자 지원 (아코디언)
          '<li class="side-toggle" data-group="support">' +
            '<span class="side-toggle-left">' +
              '<span class="snav-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg></span>' +
              '이용자 지원' +
            '</span>' +
            '<span class="side-chev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg></span>' +
          '</li>' +
          '<li class="sub" data-gi="support" style="display:none"><a href="notice.html">공지사항</a></li>' +
          '<li class="sub" data-gi="support" style="display:none"><a href="faq.html">자주 묻는 질문</a></li>' +
          '<li class="sub" data-gi="support" style="display:none"><a href="contact.html">고객문의</a></li>' +

          '<li class="side-divider"></li>' +

          // 마이페이지 (아코디언)
          '<li class="side-toggle" data-group="mypage">' +
            '<span class="side-toggle-left">' +
              '<span class="snav-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>' +
              '마이페이지' +
            '</span>' +
            '<span class="side-chev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg></span>' +
          '</li>' +
          '<li class="sub" data-gi="mypage" style="display:none"><a href="my_company.html">내 회사 정보</a></li>' +
          '<li class="sub" data-gi="mypage" style="display:none"><a href="my_matching.html#received">거래 히스토리</a></li>' +
          '<li class="sub" data-gi="mypage" style="display:none"><a href="my_matching.html#interested">관심기업</a></li>' +
          '<li class="sub" data-gi="mypage" style="display:none"><a href="my_matching.html#selected">셀렉티드</a></li>' +
          '<li class="sub" data-gi="mypage" style="display:none"><a href="edit_profile.html">회원정보수정</a></li>' +
          '<li class="sub" data-gi="mypage" style="display:none"><a href="payment_info.html">결제정보</a></li>' +

        '</ul>' +
      '</div>' +
    '</div>';

  el.outerHTML = html;

  // 헤더 fixed 높이만큼 body 여백 확보
  var headerH = '60px';
  document.body.style.paddingTop = headerH;
  document.documentElement.style.setProperty('--header-h', headerH);

  // 사이드 메뉴 토글
  var mMenu = document.querySelector('.mMenu');
  var sideMenuArea = document.getElementById('sideMenuArea');

  function openMenu() {
    if (mMenu) mMenu.classList.add('open');
    if (sideMenuArea) sideMenuArea.classList.add('open');
    document.documentElement.classList.add('hide-scroll');
  }
  function closeMenu() {
    if (mMenu) mMenu.classList.remove('open');
    if (sideMenuArea) sideMenuArea.classList.remove('open');
    document.documentElement.classList.remove('hide-scroll');
  }
  var closeBtn = document.querySelector('.sideMenu_close_btn');
  if (closeBtn) closeBtn.addEventListener('click', closeMenu);

  // 아코디언 토글
  var toggleEls = document.querySelectorAll('.side-toggle');
  for (var ti = 0; ti < toggleEls.length; ti++) {
    (function(toggle) {
      toggle.addEventListener('click', function() {
        var group = toggle.getAttribute('data-group');
        var isOpen = toggle.classList.contains('open');
        var items = document.querySelectorAll('.sub[data-gi="' + group + '"]');
        if (isOpen) {
          toggle.classList.remove('open');
          for (var i = 0; i < items.length; i++) items[i].style.display = 'none';
        } else {
          toggle.classList.add('open');
          for (var i = 0; i < items.length; i++) items[i].style.display = 'block';
        }
      });
    })(toggleEls[ti]);
  }

  if (mMenu) mMenu.addEventListener('click', function(e) {
    e.preventDefault();
    mMenu.classList.contains('open') ? closeMenu() : openMenu();
  });
  if (sideMenuArea) sideMenuArea.addEventListener('click', function(e) {
    if (e.target === sideMenuArea) closeMenu();
  });
  document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeMenu(); });

  // 가로 스크롤 방지 (clip은 sticky를 깨뜨리지 않음)
  document.documentElement.style.overflowX = 'clip';
  document.body.style.overflowX = 'clip';

  // 검색창 placeholder 타이핑 효과
  var searchInput = document.getElementById('comnum');
  if (searchInput) {
    var fullText = '사업자등록번호를 입력해보세요';
    var typingTimer = null;
    var charIndex = 0;
    var isDeleting = false;
    var isPaused = false;

    function typeEffect() {
      if (searchInput.value || document.activeElement === searchInput) return;
      if (isPaused) { typingTimer = setTimeout(typeEffect, 80); return; }

      if (!isDeleting) {
        charIndex++;
        searchInput.placeholder = fullText.slice(0, charIndex);
        if (charIndex === fullText.length) {
          isPaused = true;
          setTimeout(function() { isPaused = false; isDeleting = true; }, 1800);
        }
      } else {
        charIndex--;
        searchInput.placeholder = fullText.slice(0, charIndex);
        if (charIndex === 0) {
          isDeleting = false;
          isPaused = true;
          setTimeout(function() { isPaused = false; }, 500);
        }
      }
      typingTimer = setTimeout(typeEffect, isDeleting ? 40 : 80);
    }

    searchInput.addEventListener('focus', function() {
      clearTimeout(typingTimer);
      searchInput.placeholder = '';
    });
    searchInput.addEventListener('blur', function() {
      if (!searchInput.value) {
        charIndex = 0;
        isDeleting = false;
        isPaused = false;
        searchInput.placeholder = '';
        typeEffect();
      }
    });

    typeEffect();
  }

  // ── 헤더 AI 추천받기 공통 핸들러 ──
  // window.__aiSearchCustom = true 인 페이지(ai_recommend, index)만 제외
  setTimeout(function() {
    if (window.__aiSearchCustom) return;
    var form = document.getElementById('match');
    var btn  = document.getElementById('main_submit');
    var inp  = document.getElementById('comnum');
    if (!btn && !form) return;

    // theme/prix/head.php의 공통 모달/핸들러가 이미 있으면
    // 레거시 주입 로직을 실행하지 않아 중복 바인딩과 스타일 충돌을 막는다.
    var hasSharedModalLayer = !!document.getElementById('modal_ai_guide_overlay')
      && !!document.getElementById('modal_verify_overlay')
      && typeof window.openAiGuideModal === 'function';
    if (hasSharedModalLayer) return;

    var _pendingComnum = '';

    // ── CSS 로드 ──
    if (!document.querySelector('link[href*="verify-modal.css"]')) {
      var lnk = document.createElement('link');
      lnk.rel = 'stylesheet'; lnk.href = '/ko/css/verify-modal.css';
      document.head.appendChild(lnk);
    }

    // ── 가이드 모달 주입 (없을 때만) ──
    if (!document.getElementById('modal_ai_guide_overlay')) {
      var gw = document.createElement('div');
      gw.innerHTML =
        '<div class="modal_verify_overlay" id="modal_ai_guide_overlay" aria-hidden="true">' +
          '<div class="modal_verify" role="dialog" aria-modal="true">' +
            '<button type="button" class="modal_verify_close" id="modal_ai_guide_btn_close" aria-label="닫기">&times;</button>' +
            '<div class="modal_verify_icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 17v-5M12 8h.01M4 12a8 8 0 1 0 16 0 8 8 0 1 0-16 0z"/></svg></div>' +
            '<h2 class="modal_verify_company" id="modal_ai_guide_title">AI 추천 안내</h2>' +
            '<p class="modal_verify_text" id="modal_ai_guide_text"></p>' +
            '<p class="modal_verify_text2" id="modal_ai_guide_text2"></p>' +
            '<button type="button" class="modal_verify_btn" id="modal_ai_guide_btn_ok">확인했어요</button>' +
          '</div>' +
        '</div>';
      document.body.appendChild(gw.firstChild);
    }

    // ── 검증 모달 주입 (없을 때만) ──
    if (!document.getElementById('modal_verify_overlay')) {
      var vw = document.createElement('div');
      vw.innerHTML =
        '<div class="modal_verify_overlay" id="modal_verify_overlay" aria-hidden="true">' +
          '<div class="modal_verify" role="dialog" aria-modal="true">' +
            '<button type="button" class="modal_verify_close" id="modal_verify_btn_close" aria-label="닫기">&times;</button>' +
            '<div class="modal_verify_icon"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></div>' +
            '<h2 class="modal_verify_company" id="modal_verify_company"></h2>' +
            '<p class="modal_verify_text">귀사와 동일·유사 업종의 실제 거래 데이터를 분석해<br>적합한 거래처를 추천해드립니다.</p>' +
            '<hr class="modal_verify_hr">' +
            '<p class="modal_verify_guide">추천 결과를 확인해보세요.</p>' +
            '<button type="button" class="modal_verify_btn" id="modal_verify_btn_ai">' +
              '<svg viewBox="0 0 24 24" width="17" height="17" fill="currentColor"><path d="M16.8 2.2l1.1 2.3 2.3 1.1-2.3 1.1-1.1 2.3-1.1-2.3-2.3-1.1 2.3-1.1 1.1-2.3zm3.2 2.8.6 1.1 1.1.6-1.1.6-.6 1.1-.6-1.1-1.1-.6 1.1-.6.6-1.1zM7.4 17.4 16.1 8.7l2.6 2.6-8.7 8.7-2.6-2.6zm-1 2.4 1.3 1.3-3.1 1 .8-2.9z"/></svg>' +
              '거래처 확인하기' +
            '</button>' +
          '</div>' +
        '</div>';
      document.body.appendChild(vw.firstChild);
    }

    // ── 모달 열기/닫기 ──
    function openGuide(title, text, text2) {
      var ov = document.getElementById('modal_ai_guide_overlay');
      var t  = document.getElementById('modal_ai_guide_title');
      var p  = document.getElementById('modal_ai_guide_text');
      var p2 = document.getElementById('modal_ai_guide_text2');
      if (t)  t.textContent  = title || 'AI 추천 안내';
      if (p)  p.textContent  = text  || '';
      if (p2) p2.textContent = text2 || '';
      if (ov) { ov.classList.add('is_open'); ov.setAttribute('aria-hidden', 'false'); }
    }
    function closeGuide() {
      var ov = document.getElementById('modal_ai_guide_overlay');
      if (ov) { ov.classList.remove('is_open'); ov.setAttribute('aria-hidden', 'true'); }
    }
    function openVerify(name) {
      var ov = document.getElementById('modal_verify_overlay');
      var el = document.getElementById('modal_verify_company');
      if (el && name) el.textContent = name;
      if (ov) { ov.classList.add('is_open'); ov.setAttribute('aria-hidden', 'false'); }
    }
    function closeVerify() {
      var ov = document.getElementById('modal_verify_overlay');
      if (ov) { ov.classList.remove('is_open'); ov.setAttribute('aria-hidden', 'true'); }
    }

    // ── 하이픈 포매팅 ──
    function autoHypen(v) {
      v = (v || '').replace(/[^0-9]/g, '');
      if (v.length < 4) return v;
      if (v.length < 6) return v.substr(0,3) + '-' + v.substr(3,2);
      return v.substr(0,3) + '-' + v.substr(3,2) + '-' + v.substr(5,5);
    }

    // ── 검색 실행 ──
    function doSearch() {
      if (inp) inp.blur();
      var comnum = inp ? inp.value : '';
      var digits = comnum.replace(/[^0-9]/g, '');

      if (!digits.length) {
        openGuide('AI 추천 안내', '사업자 번호를 입력해 주세요.', '사업자 번호를 입력하고 AI 추천받기를 누르면 AI 분석을 통해 거래처·판매처를 추천해드립니다.');
        return;
      }
      if (digits.length !== 10) {
        openGuide('사업자번호 재확인', '입력하신 사업자번호 형식이 올바르지 않습니다.', '사업자번호를 다시 확인한 뒤 AI 추천받기를 눌러주세요.');
        return;
      }

      _pendingComnum = digits;

      // 데모: 1868101689
      if (digits === '1868101689') { openVerify('(주)이가이엔씨'); return; }

      fetch('get_set.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: new URLSearchParams({ type: 'main_comp_chk', comnum: comnum }).toString()
      })
      .then(function(r) { return r.text(); })
      .then(function(txt) {
        var p = (txt || '').split('|');
        if (p[0] === '1000') { openVerify(p[2] || ''); }
        else if (p[1] === '02') { openGuide('사업자번호 재확인', '입력하신 사업자번호로 기업 정보를 찾지 못했습니다.', '사업자번호를 다시 확인해 주세요.'); }
        else { openGuide('조회 오류 안내', '사업자번호 조회 중 일시적인 오류가 발생했습니다.', '잠시 후 다시 시도해 주세요.'); }
      })
      .catch(function() {
        openGuide('조회 오류 안내', '사업자번호 조회 중 일시적인 오류가 발생했습니다.', '잠시 후 다시 시도해 주세요.');
      });
    }

    // ── 이벤트 바인딩 ──
    if (form) form.addEventListener('submit', function(e) { e.preventDefault(); doSearch(); });
    if (btn)  btn.addEventListener('click',   function(e) { e.preventDefault(); doSearch(); });
    if (inp) {
      inp.addEventListener('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); doSearch(); } });
      inp.addEventListener('input',   function() { this.value = autoHypen(this.value); });
    }

    // ── 모달 버튼 이벤트 ──
    var gClose = document.getElementById('modal_ai_guide_btn_close');
    var gOk    = document.getElementById('modal_ai_guide_btn_ok');
    var gOv    = document.getElementById('modal_ai_guide_overlay');
    var vClose = document.getElementById('modal_verify_btn_close');
    var vGo    = document.getElementById('modal_verify_btn_ai');
    var vOv    = document.getElementById('modal_verify_overlay');

    if (gClose) gClose.addEventListener('click', function() { closeGuide(); if (inp) inp.focus(); });
    if (gOk)    gOk.addEventListener('click',    function() { closeGuide(); if (inp) inp.focus(); });
    if (gOv)    gOv.addEventListener('click',    function(e) { if (e.target === this) { closeGuide(); if (inp) inp.focus(); } });
    if (vClose) vClose.addEventListener('click', closeVerify);
    if (vOv)    vOv.addEventListener('click',    function(e) { if (e.target === this) closeVerify(); });
    if (vGo) {
      vGo.addEventListener('click', function() {
        closeVerify();
        var url = 'company_list.html?cat=all&type=ai';
        if (_pendingComnum) url += '&comnum=' + encodeURIComponent(_pendingComnum);
        if (typeof showAiLoading === 'function') { showAiLoading(url); }
        else { location.href = url; }
      });
    }
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') { closeGuide(); closeVerify(); }
    });
  }, 0);
})();
