/**
 * Legacy wrapper for verify modal.
 * - If shared modal from theme head is present, do not rebind events.
 * - Fallback to standalone behavior only when shared layer is missing.
 */
(function () {
    function openVerifyModal(companyName) {
        var overlay = document.getElementById("modal_verify_overlay");
        var companyEl = document.getElementById("modal_verify_company");
        if (companyEl && companyName) companyEl.textContent = companyName;
        if (!overlay) return;
        overlay.classList.add("is_open");
        overlay.setAttribute("aria-hidden", "false");
    }

    function closeVerifyModal() {
        var overlay = document.getElementById("modal_verify_overlay");
        if (!overlay) return;
        overlay.classList.remove("is_open");
        overlay.setAttribute("aria-hidden", "true");
    }

    var hasSharedLayer = !!document.getElementById("modal_ai_guide_overlay")
        && !!document.getElementById("modal_verify_overlay")
        && typeof window.openAiGuideModal === "function"
        && typeof window.closeAiGuideModal === "function";

    if (hasSharedLayer) {
        if (typeof window.openVerifyModal !== "function") window.openVerifyModal = openVerifyModal;
        if (typeof window.closeVerifyModal !== "function") window.closeVerifyModal = closeVerifyModal;
        return;
    }

    if (window.__verifyModalLegacyBound) return;
    window.__verifyModalLegacyBound = true;

    function openAiGuideModal(opts) {
        opts = opts || {};
        var overlay = document.getElementById("modal_ai_guide_overlay");
        if (!overlay) return;
        var titleEl = document.getElementById("modal_ai_guide_title");
        var textEl = document.getElementById("modal_ai_guide_text");
        var text2El = document.getElementById("modal_ai_guide_text2");
        var okBtn = document.getElementById("modal_ai_guide_btn_ok");
        var secondaryBtn = document.getElementById("modal_ai_guide_btn_secondary");

        if (titleEl) titleEl.textContent = opts.title || "AI 추천 안내";
        if (textEl) textEl.textContent = opts.text || "사업자 번호를 입력하고 AI 추천받기를 눌러주세요.";
        if (text2El) text2El.textContent = opts.text2 || "AI 분석을 통해 업종/거래 데이터를 기반으로 적합한 거래처 판매처를 추천해드립니다.";
        if (okBtn) {
            okBtn.textContent = opts.okText || "확인했어요";
            okBtn.classList.remove("modal_verify_btn_outline");
            if (opts.okVariant === "outline") okBtn.classList.add("modal_verify_btn_outline");
        }
        if (secondaryBtn) {
            var secondaryText = (opts.secondaryText || "").trim();
            if (secondaryText) {
                secondaryBtn.hidden = false;
                secondaryBtn.textContent = secondaryText;
            } else {
                secondaryBtn.hidden = true;
                secondaryBtn.textContent = "바로가기";
            }
        }

        window._aiGuideModalState = {
            onOk: (typeof opts.onOk === "function") ? opts.onOk : null,
            onSecondary: (typeof opts.onSecondary === "function") ? opts.onSecondary : null,
            secondaryHref: opts.secondaryHref || ""
        };

        overlay.classList.add("is_open");
        overlay.setAttribute("aria-hidden", "false");
    }

    function closeAiGuideModal() {
        var overlay = document.getElementById("modal_ai_guide_overlay");
        if (!overlay) return;
        overlay.classList.remove("is_open");
        overlay.setAttribute("aria-hidden", "true");
        window._aiGuideModalState = null;
    }

    function bindVerifyModals() {
        var verifyOverlay = document.getElementById("modal_verify_overlay");
        if (verifyOverlay) {
            verifyOverlay.addEventListener("click", function (ev) {
                if (ev.target === this) closeVerifyModal();
            });
        }

        var verifyCloseBtn = document.getElementById("modal_verify_btn_close");
        if (verifyCloseBtn) verifyCloseBtn.addEventListener("click", closeVerifyModal);

        var guideOverlay = document.getElementById("modal_ai_guide_overlay");
        if (guideOverlay) {
            guideOverlay.addEventListener("click", function (ev) {
                if (ev.target === this) closeAiGuideModal();
            });
        }

        var guideCloseBtn = document.getElementById("modal_ai_guide_btn_close");
        if (guideCloseBtn) guideCloseBtn.addEventListener("click", closeAiGuideModal);

        var guideOkBtn = document.getElementById("modal_ai_guide_btn_ok");
        if (guideOkBtn) {
            guideOkBtn.addEventListener("click", function () {
                var state = window._aiGuideModalState || null;
                closeAiGuideModal();
                if (state && typeof state.onOk === "function") state.onOk();
            });
        }

        var guideSecondaryBtn = document.getElementById("modal_ai_guide_btn_secondary");
        if (guideSecondaryBtn) {
            guideSecondaryBtn.addEventListener("click", function () {
                var state = window._aiGuideModalState || null;
                closeAiGuideModal();
                if (state && typeof state.onSecondary === "function") {
                    state.onSecondary();
                    return;
                }
                if (state && state.secondaryHref) window.location.href = state.secondaryHref;
            });
        }
    }

    bindVerifyModals();

    window.openVerifyModal = openVerifyModal;
    window.closeVerifyModal = closeVerifyModal;
    window.openAiGuideModal = openAiGuideModal;
    window.closeAiGuideModal = closeAiGuideModal;
    window._bindVerifyModals = bindVerifyModals;
})();
