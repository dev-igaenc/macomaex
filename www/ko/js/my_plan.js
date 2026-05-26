$(function () {
    var PAGE_SIZE = 3;

    // 카드 리스트 공통: 필터 + 더보기/접기 + 건수 배지 처리
    function initCardSection(opts) {
        var listEl = document.getElementById(opts.listId);
        var moreWrap = document.getElementById(opts.moreWrapId);
        var moreBtn = document.getElementById(opts.moreBtnId);
        var badgeEl = document.getElementById(opts.badgeId);
        var filterEl = opts.filterId ? document.getElementById(opts.filterId) : null;
        if (!listEl || !moreBtn || !moreWrap) {
            return;
        }

        var currentFilter = "all";
        var showCount = PAGE_SIZE;

        function getCards() {
            return Array.prototype.slice.call(listEl.querySelectorAll(".pay-card[data-type]"));
        }

        function matchFilter(card) {
            if (currentFilter === "all") {
                return true;
            }
            return card.getAttribute(opts.filterAttr) === currentFilter;
        }

        function render() {
            var cards = getCards();
            var matched = cards.filter(matchFilter);
            var visible = 0;

            cards.forEach(function (card) {
                if (matchFilter(card)) {
                    card.style.display = visible < showCount ? "" : "none";
                    visible++;
                } else {
                    card.style.display = "none";
                }
            });

            var remaining = matched.length - showCount;
            if (remaining > 0) {
                moreWrap.style.display = "";
                moreBtn.classList.remove("expanded");
                moreBtn.innerHTML =
                    '더 보기 <span style="color:#2563eb;font-weight:700;">+' + remaining + '</span>' +
                    '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>';
            } else {
                if (matched.length > PAGE_SIZE && showCount >= matched.length) {
                    moreWrap.style.display = "";
                    moreBtn.classList.add("expanded");
                    moreBtn.innerHTML =
                        '접기' +
                        '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>';
                } else {
                    moreWrap.style.display = "none";
                }
            }

            if (badgeEl) {
                badgeEl.textContent = matched.length + "건";
            }
        }

        moreBtn.addEventListener("click", function () {
            var matchedLength = getCards().filter(matchFilter).length;
            if (showCount >= matchedLength) {
                showCount = PAGE_SIZE;
            } else {
                showCount += PAGE_SIZE;
            }
            render();
        });

        if (filterEl) {
            var btns = filterEl.querySelectorAll(".filter-btn");
            btns.forEach(function (btn) {
                btn.addEventListener("click", function () {
                    btns.forEach(function (b) {
                        b.classList.remove("active");
                    });
                    btn.classList.add("active");
                    currentFilter = btn.getAttribute("data-filter");
                    showCount = PAGE_SIZE;
                    render();
                });
            });
        }

        render();
    }

    initCardSection({
        listId: "chargeList",
        moreWrapId: "chargeMoreWrap",
        moreBtnId: "chargeMoreBtn",
        badgeId: "chargeBadge",
        filterId: "chargeFilter",
        filterAttr: "data-type"
    });

    initCardSection({
        listId: "useList",
        moreWrapId: "useMoreWrap",
        moreBtnId: "useMoreBtn",
        badgeId: "useBadge",
        filterId: "useFilter",
        filterAttr: "data-type"
    });

    // 환불 정책 아코디언
    (function () {
        var box = document.getElementById("policyBox");
        var toggle = document.getElementById("policyToggle");
        if (!box || !toggle) {
            return;
        }
        toggle.addEventListener("click", function () {
            var isOpen = box.classList.contains("open");
            box.classList.toggle("open", !isOpen);
            toggle.setAttribute("aria-expanded", String(!isOpen));
        });
        toggle.addEventListener("keydown", function (e) {
            if (e.key === "Enter" || e.key === " ") {
                e.preventDefault();
                toggle.click();
            }
        });
    })();

    // 충전건 개별 환불 신청
    $(document).on("click", ".js-charge-refund-btn", function () {
        var orderNo = ($(this).data("order-no") || "").toString().trim();
        if (!orderNo) {
            jAlert("주문 정보를 확인할 수 없습니다.", "알림");
            return;
        }

        var doRefund = function () {
            $("#current_order_no").val(orderNo);
            $.post("../get_set.php", {
                type: "credit_charge_refund",
                order_no: orderNo,
                cancel_token: $("#membership_cancel_token").val()
            }).done(function (raw) {
                var text = String(raw || "").replace(/^\uFEFF/, "").trim();
                var codeIdx = text.search(/(?:1000|500|429|501)\|/);
                if (codeIdx > 0) text = text.substring(codeIdx);
                text = text.replace(/<[^>]*>/g, "").trim();
                var parts = text.split("|");
                if (parts[0] === "1000") {
                    window.dataLayer = window.dataLayer || [];
                    window.dataLayer.push({
                        event: "refund",
                        ecommerce: {
                            transaction_id: $("#current_order_no").val(),
                            currency: "KRW"
                        }
                    });
                    jAlert(parts[1] ? parts[1] : "환불요청이 접수되었습니다.", "알림", function () {
                        location.reload();
                    });
                } else {
                    jAlert(parts[1] ? parts[1] : "환불요청 처리 중 오류가 발생했습니다.", "알림");
                }
            });
        };

        var confirmText = "해당 충전건 환불을 신청하시겠습니까?\n결제 후 7일 이내, 해당 충전건 미사용인 경우에만 환불됩니다.";
        if (typeof jCustomConfirm === "function") {
            jCustomConfirm(confirmText, "알림", "취소", "확인", function (e) {
                if (!e) doRefund();
            });
        } else if (typeof jConfirm === "function") {
            jConfirm(confirmText, "알림", function (ok) {
                if (ok) doRefund();
            });
        } else {
            if (confirm(confirmText)) doRefund();
        }
    });
});
