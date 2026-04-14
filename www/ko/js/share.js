/**
 * 공유하기 공통 모듈
 *
 * 사용법:
 *   <link rel="stylesheet" href="css/share.css">
 *   <button class="btn-topnav-icon" data-share title="공유하기">...</button>
 *   <script src="js/share.js"></script>
 */
(function () {
  var popover = null;

  function closePopover() {
    if (popover) { popover.remove(); popover = null; }
    document.removeEventListener('click', onDocClick);
  }

  function onDocClick(e) {
    if (popover && !popover.contains(e.target) && !e.target.closest('[data-share]')) {
      closePopover();
    }
  }

  function getShareUrl() {
    var url = window.location.href;
    // 로그인 페이지(일반·AI)는 비로그인용 공유 전용 페이지로 변환
    url = url.replace('company_detail_ai.html', 'company_detail_shared.html');
    url = url.replace('company_detail.html',    'company_detail_shared.html');
    return url;
  }

  function copyLink() {
    var url = getShareUrl();
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(url);
    } else {
      var tmp = document.createElement('input');
      tmp.value = url;
      document.body.appendChild(tmp);
      tmp.select();
      document.execCommand('copy');
      document.body.removeChild(tmp);
    }
    closePopover();
    if (window._showToast) {
      window._showToast(
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>',
        '링크가 복사되었습니다', 'info'
      );
    }
  }


  function shareEmail() {
    var subject = encodeURIComponent(document.title);
    var body = encodeURIComponent('이 기업을 확인해보세요: ' + getShareUrl());
    window.open('mailto:?subject=' + subject + '&body=' + body);
    closePopover();
  }

  function openPopover(btn) {
    if (popover) { closePopover(); return; }

    popover = document.createElement('div');
    popover.className = 'share-popover';
    popover.innerHTML =
      '<div class="share-popover_title">공유하기</div>' +
      '<div class="share-popover_options">' +
        '<button type="button" class="share-popover_item" data-action="copy">' +
          '<span class="share-popover_icon share-popover_icon--link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg></span>' +
          '<span>링크 복사</span>' +
        '</button>' +
        '<button type="button" class="share-popover_item" data-action="email">' +
          '<span class="share-popover_icon share-popover_icon--email"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 7l-10 7L2 7"/></svg></span>' +
          '<span>이메일</span>' +
        '</button>' +
      '</div>';

    // Position relative to button
    var rect = btn.getBoundingClientRect();
    popover.style.top = (rect.bottom + 8) + 'px';
    popover.style.right = (window.innerWidth - rect.right) + 'px';

    document.body.appendChild(popover);
    requestAnimationFrame(function () { popover.classList.add('is-show'); });

    popover.querySelector('[data-action="copy"]').addEventListener('click', copyLink);
    popover.querySelector('[data-action="email"]').addEventListener('click', shareEmail);

    setTimeout(function () {
      document.addEventListener('click', onDocClick);
    }, 0);
  }

  // Bind
  document.querySelectorAll('[data-share]').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      openPopover(btn);
    });
  });
})();