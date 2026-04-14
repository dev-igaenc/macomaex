
/**
 * 관심기업 별 버튼 + 토스트 알림 공통 모듈
 *
 * 사용법:
 *   <link rel="stylesheet" href="/css/favorite.css">
 *   <button class="btn-favorite" data-favorite title="관심기업 등록" aria-label="관심기업 등록">
 *     <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
 *   </button>
 *   <script src="/inc/favorite.js"></script>
 */
(function () {
  // ─── 토스트 ───
  var toastEl = null;
  var toastTimer = null;

  var anchorBtn = null;

  function showToast(icon, message, type) {
    if (toastEl) { toastEl.remove(); clearTimeout(toastTimer); }
    toastEl = document.createElement('div');
    toastEl.className = 'toast-notify toast-' + (type || 'info');
    toastEl.innerHTML = '<span class="toast-icon">' + icon + '</span><span class="toast-msg">' + message + '</span>';

    if (anchorBtn) {
      toastEl.classList.add('toast-anchored');
      anchorBtn.style.position = anchorBtn.style.position || 'relative';
      var parent = anchorBtn.parentElement;
      if (parent) {
        parent.style.position = parent.style.position || 'relative';
        parent.appendChild(toastEl);
      } else {
        document.body.appendChild(toastEl);
      }
    } else {
      document.body.appendChild(toastEl);
    }

    requestAnimationFrame(function () { toastEl.classList.add('is-show'); });
    toastTimer = setTimeout(function () {
      toastEl.classList.remove('is-show');
      setTimeout(function () { if (toastEl) toastEl.remove(); toastEl = null; }, 300);
    }, 2200);
  }

  window._showToast = showToast;

  var starOn = '<svg viewBox="0 0 24 24" fill="#fbbf24" stroke="#fbbf24" stroke-width="1.5" width="20" height="20"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
  var starOff = '<svg viewBox="0 0 24 24" fill="none" stroke="#888" stroke-width="1.5" width="20" height="20"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';

  // ─── 관심기업 별 버튼 바인딩 ───
  function bindFavorites() {
    document.querySelectorAll('[data-favorite]').forEach(function (btn) {
      if (btn._favBound) return;
      btn._favBound = true;
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if (window._isLoggedIn && !window._isLoggedIn()) {
          if (window._requireLogin) window._requireLogin('관심기업 등록');
          return;
        }
        btn.classList.toggle('is-active');
        anchorBtn = btn;
        if (btn.classList.contains('is-active')) {
          showToast(starOn, '관심기업으로 등록되었습니다', 'success');
        } else {
          showToast(starOff, '관심기업에서 해제되었습니다', 'default');
        }
      });
    });
  }

  // 초기 바인딩 + 동적으로 추가된 버튼 대응
  bindFavorites();
  window._bindFavorites = bindFavorites;
})();
