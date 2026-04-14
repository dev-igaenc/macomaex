/**
 * 사업자 확인/안내 모달 공통 모듈
 *
 * 사용법:
 *   <link rel="stylesheet" href="css/verify-modal.css">
 *   <!-- verify-modal HTML 포함 -->
 *   <script src="js/verify-modal.js"></script>
 *
 *   openVerifyModal('(주)이가이엔씨');
 *   openAiGuideModal({ title: '...', text: '...', text2: '...' });
 */
(function () {
    // ─── 사업자 확인 모달 ───
    function openVerifyModal(companyName) {
        var overlay = document.getElementById('modal_verify_overlay');
        var companyEl = document.getElementById('modal_verify_company');
        if (companyEl && companyName) companyEl.textContent = companyName;
        if (!overlay) return;
        overlay.classList.add('is_open');
        overlay.setAttribute('aria-hidden', 'false');
    }

    function closeVerifyModal() {
        var overlay = document.getElementById('modal_verify_overlay');
        if (!overlay) return;
        overlay.classList.remove('is_open');
        overlay.setAttribute('aria-hidden', 'true');
    }

    // ─── AI 안내 모달 ───
    function openAiGuideModal(opts) {
        opts = opts || {};
        var overlay = document.getElementById('modal_ai_guide_overlay');
        if (!overlay) return;
        var titleEl = document.getElementById('modal_ai_guide_title');
        var textEl = document.getElementById('modal_ai_guide_text');
        var text2El = document.getElementById('modal_ai_guide_text2');
        if (titleEl && opts.title) titleEl.textContent = opts.title;
        if (textEl && opts.text) textEl.textContent = opts.text;
        if (text2El && opts.text2) text2El.textContent = opts.text2;
        overlay.classList.add('is_open');
        overlay.setAttribute('aria-hidden', 'false');
    }

    function closeAiGuideModal() {
        var overlay = document.getElementById('modal_ai_guide_overlay');
        if (!overlay) return;
        overlay.classList.remove('is_open');
        overlay.setAttribute('aria-hidden', 'true');
    }

    // ─── 이벤트 바인딩 ───
    function bindVerifyModals() {
        var verifyOverlay = document.getElementById('modal_verify_overlay');
        if (verifyOverlay) {
            verifyOverlay.addEventListener('click', function (ev) {
                if (ev.target === this) closeVerifyModal();
            });
        }
        var verifyCloseBtn = document.getElementById('modal_verify_btn_close');
        if (verifyCloseBtn) verifyCloseBtn.addEventListener('click', closeVerifyModal);

        var guideOverlay = document.getElementById('modal_ai_guide_overlay');
        if (guideOverlay) {
            guideOverlay.addEventListener('click', function (ev) {
                if (ev.target === this) closeAiGuideModal();
            });
        }
        var guideCloseBtn = document.getElementById('modal_ai_guide_btn_close');
        if (guideCloseBtn) guideCloseBtn.addEventListener('click', closeAiGuideModal);

        var guideOkBtn = document.getElementById('modal_ai_guide_btn_ok');
        if (guideOkBtn) guideOkBtn.addEventListener('click', closeAiGuideModal);
    }

    bindVerifyModals();

    // 전역 노출
    window.openVerifyModal = openVerifyModal;
    window.closeVerifyModal = closeVerifyModal;
    window.openAiGuideModal = openAiGuideModal;
    window.closeAiGuideModal = closeAiGuideModal;
    window._bindVerifyModals = bindVerifyModals;
})();