/**
 * 커스텀 확인 모달 공통 모듈
 *
 * 사용법:
 *   <link rel="stylesheet" href="css/confirm-modal.css">
 *   <script src="js/confirm-modal.js"></script>
 *
 *   window._confirm({
 *     icon: 'purple',  // 'purple' | 'blue' | 'amber'
 *     iconSvg: '<path d="..."/>',  // optional custom svg inner
 *     title: '제목',
 *     desc: '설명 <strong>굵게</strong>',
 *     confirmText: '확인',    // default: '확인'
 *     cancelText: '취소',     // default: '취소'
 *     confirmClass: 'purple', // 'purple' | 'blue'
 *     onConfirm: function() { ... },
 *     onCancel: function() { ... }  // optional
 *   });
 */
(function () {
  var overlay = null;

  function close() {
    if (overlay) {
      overlay.classList.remove('is-open');
      setTimeout(function () { if (overlay) overlay.remove(); overlay = null; }, 200);
    }
  }

  window._confirm = function (opts) {
    if (overlay) overlay.remove();
    opts = opts || {};

    var iconColor = opts.icon || 'purple';
    var iconSvg = opts.iconSvg || '<path d="M12 3l1.5 4.5L18 9l-4.5 1.5L12 15l-1.5-4.5L6 9l4.5-1.5L12 3z"/>';
    var confirmClass = opts.confirmClass || iconColor;

    overlay = document.createElement('div');
    overlay.className = 'confirm-modal_overlay';
    overlay.innerHTML =
      '<div class="confirm-modal">' +
        '<div class="confirm-modal_icon confirm-modal_icon--' + iconColor + '">' +
          '<svg viewBox="0 0 24 24">' + iconSvg + '</svg>' +
        '</div>' +
        '<h3 class="confirm-modal_title">' + (opts.title || '확인') + '</h3>' +
        '<p class="confirm-modal_desc">' + (opts.desc || '') + '</p>' +
        '<div class="confirm-modal_btns">' +
          '<button type="button" class="confirm-modal_btn confirm-modal_btn--cancel" id="cfmCancel">' + (opts.cancelText || '취소') + '</button>' +
          '<button type="button" class="confirm-modal_btn confirm-modal_btn--' + confirmClass + '" id="cfmOk">' + (opts.confirmText || '확인') + '</button>' +
        '</div>' +
      '</div>';

    document.body.appendChild(overlay);
    requestAnimationFrame(function () { overlay.classList.add('is-open'); });

    overlay.querySelector('#cfmOk').addEventListener('click', function () {
      close();
      if (opts.onConfirm) opts.onConfirm();
    });
    overlay.querySelector('#cfmCancel').addEventListener('click', function () {
      close();
      if (opts.onCancel) opts.onCancel();
    });
    overlay.addEventListener('click', function (e) {
      if (e.target === overlay) { close(); if (opts.onCancel) opts.onCancel(); }
    });
    document.addEventListener('keydown', function handler(e) {
      if (e.key === 'Escape') { document.removeEventListener('keydown', handler); close(); if (opts.onCancel) opts.onCancel(); }
    });
  };
})();