;(function (window, $) {
    "use strict";

    if (!$) return;

    var defaults = {
        getSetUrl: "/get_set.php",
        detailTrigger: ".detail_view",
        detailLayerSelector: "#company-view-lay .layer-in",
        detailLayerModal: "#company-view-lay",
        starTrigger: ".star_btn",
        popupOpenTrigger: "#popup_open",
        popupSelector: ".popup_submit",
        requestTypeSelector: "#request_type",
        receiverNameSelector: "#recv",
        planUrl: "/ko/plan.php",
        loginUrl: function () {
            return "/bbs/login.php?url=" + encodeURIComponent(location.pathname + location.search);
        },
        reloadOnSuccess: false,
        successMessage: "매칭이 요청되었습니다.",
        shortagePrefix: "매칭 가능 크레딧이 부족합니다",
        onPopupReady: null,
        beforeSubmit: null,
        onDetailLoaded: null
    };

    function splitRes(res) {
        return (res + "").split("|");
    }

    function showAlert(msg, cb) {
        if (typeof jAlert === "function") {
            jAlert(msg, "알림", cb || function () {});
        } else {
            alert(msg);
            if (cb) cb();
        }
    }

    function getLoginUrl(opts) {
        return (typeof opts.loginUrl === "function") ? opts.loginUrl() : opts.loginUrl;
    }

    function closePopup() {
        document.querySelectorAll(".popup").forEach(function (el) {
            el.classList.remove("open");
        });
        document.documentElement.classList.remove("hide");
    }

    function closeDetailLayer() {
        try {
            if (typeof window.popFancyClose === "function") {
                window.popFancyClose();
            } else if (window.Fancybox && typeof window.Fancybox.close === "function") {
                window.Fancybox.close();
            }
        } catch (e) {}

        var fancyCloseBtn = document.querySelector("button.is-close");
        if (fancyCloseBtn) {
            try { fancyCloseBtn.click(); } catch (e) {}
        }
    }

    function hasOpenFancybox() {
        return !!document.querySelector(".fancybox__container:not([aria-hidden='true'])");
    }

    function waitUntilFancyboxClosed(done) {
        var startedAt = Date.now();

        (function poll() {
            if (!hasOpenFancybox()) {
                done();
                return;
            }

            // 안전장치: 0.8초를 넘기면 강제로 다음 단계 진행
            if (Date.now() - startedAt > 800) {
                done();
                return;
            }

            setTimeout(poll, 20);
        })();
    }

    function openPopup(selector) {
        var doOpen = function () {
            document.querySelectorAll(".popup").forEach(function (el) {
                el.classList.remove("open");
            });
            var popup = document.querySelector(selector);
            if (!popup) return;
            document.documentElement.classList.add("hide");
            popup.classList.add("open");
        };

        if (hasOpenFancybox()) {
            closeDetailLayer();
            waitUntilFancyboxClosed(function () {
                // 닫힘 직후 한 프레임 쉬고 열면 시각적으로 덜 튄다.
                setTimeout(doOpen, 16);
            });
            return;
        }

        doOpen();
    }

    function copyUrl(cd) {
        if (!cd) return false;
        var shareUrl = location.protocol + "//" + location.host + "/ko/company_view.php?cd=" + cd;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(shareUrl).then(function () {
                showAlert("URL이 복사되었습니다.");
            }).catch(function () {
                showAlert("URL 복사에 실패했습니다.");
            });
        } else {
            var textArea = document.createElement("textarea");
            textArea.value = shareUrl;
            document.body.appendChild(textArea);
            textArea.select();
            try {
                document.execCommand("copy");
                showAlert("URL이 복사되었습니다.");
            } catch (e) {
                showAlert("URL 복사에 실패했습니다.");
            }
            document.body.removeChild(textArea);
        }
        return false;
    }

    function submitMatching(opts) {
        if (typeof opts.beforeSubmit === "function") {
            var canSubmit = opts.beforeSubmit();
            if (canSubmit === false) return false;
        }

        var formData = $(opts.popupSelector).find("input, textarea").serialize();
        if (!formData) return false;

        $.post(opts.getSetUrl + "?" + formData).done(function (res) {
            var parts = splitRes(res);
            if (parts[0] === "1000") {
                showAlert(opts.successMessage, function () {
                    closePopup();
                    if (opts.reloadOnSuccess) location.reload();
                });
                return;
            }

            var msg = parts[1] ? parts[1] : "요청에 문제가 있습니다.";
            if (parts[2] === "PLAN" || msg.indexOf(opts.shortagePrefix) === 0) {
                showAlert(msg, function () {
                    location.href = opts.planUrl;
                });
                return;
            }

            if (parts[0] === "501" || msg.indexOf("로그인 후 이용하세요.") === 0) {
                showAlert("로그인 후 이용하세요.", function () {
                    location.href = getLoginUrl(opts);
                });
                return;
            }

            showAlert(msg);
        });

        return false;
    }

    function init(userOptions) {
        var opts = $.extend({}, defaults, userOptions || {});
        var loadingDetail = false;

        window.copyUrl = copyUrl;
        window.popupClose = function () { closePopup(); };
        window.popupSubmit = function () { return submitMatching(opts); };

        $("body")
            .off("click.companyDetailActions", opts.starTrigger)
            .on("click.companyDetailActions", opts.starTrigger, function (event) {
                event.preventDefault();
                event.stopPropagation();

                var $this = $(this);
                var kedcd = $this.attr("kedcd");
                var chk = !$this.hasClass("active");
                if (!kedcd) return;

                $.post(opts.getSetUrl, { type: "favorite", kedcd: kedcd, chk: chk }).done(function (res) {
                    var code = splitRes(res)[0];
                    if (code === "1000") {
                        $("button.star_btn[kedcd='" + kedcd + "']").toggleClass("active", chk);
                        return;
                    }
                    if (code === "501") {
                        showAlert("로그인 후 이용하세요.", function () {
                            location.href = getLoginUrl(opts);
                        });
                        return;
                    }
                    $this.toggleClass("active", !chk);
                });
            });

        $("body")
            .off("click.companyDetailActions", opts.popupOpenTrigger)
            .on("click.companyDetailActions", opts.popupOpenTrigger, function (event) {
                event.preventDefault();
                event.stopPropagation();

                var kedcd = $(this).attr("kedcd");
                if (!kedcd) {
                    showAlert("회사 정보를 확인할 수 없어 처리하지 못했습니다.");
                    return false;
                }

                var companyName = $(this).attr("company_name") || $(this).attr("data-company-name") || "";
                if (!companyName) {
                    companyName = $.trim($(this).closest(".cv-name").find("strong").first().text());
                }

                $(opts.popupSelector).find("#kedcd").val(kedcd);

                if (opts.receiverNameSelector) {
                    var $receiver = $(opts.popupSelector).find(opts.receiverNameSelector);
                    if ($receiver.length) {
                        $receiver.text(companyName || "");
                    }
                }

                if (typeof opts.onPopupReady === "function") {
                    opts.onPopupReady(kedcd, ["1000", "", companyName]);
                }

                openPopup(opts.popupSelector);

                return false;
            });

        $("body")
            .off("click.companyDetailActions", opts.detailTrigger)
            .on("click.companyDetailActions", opts.detailTrigger, function (event) {
                event.preventDefault();
                if (loadingDetail) return;

                var kedcd = $(this).attr("kedcd");
                if (!kedcd) return;
                loadingDetail = true;

                $.post(opts.getSetUrl, { type: "view_chk" }).done(function (res) {
                    var parts = splitRes(res);

                    if (parts[0] === "500") {
                        loadingDetail = false;
                        if (parts[1] === "로그인 후 이용하세요.") {
                            showAlert(parts[1], function () {
                                location.href = getLoginUrl(opts);
                            });
                        } else if (parts[1] === "거래처 오픈 회수를 초과했습니다.") {
                            showAlert(parts[1], function () {
                                location.href = opts.planUrl;
                            });
                        } else {
                            showAlert(parts[1] || "요청에 문제가 있습니다.");
                        }
                        return;
                    }

                    $.post(opts.getSetUrl, { type: "detail_view", kedcd: kedcd }).done(function (detail) {
                        var dParts = splitRes(detail);
                        if (dParts[0] === "500") {
                            showAlert("거래처 오픈 회수를 초과했습니다.");
                        } else {
                            $(opts.detailLayerSelector).html(dParts[0]);
                            if (typeof popFancy === "function") popFancy(opts.detailLayerModal);
                            if (typeof opts.onDetailLoaded === "function") opts.onDetailLoaded(kedcd, dParts);
                        }
                    }).always(function () {
                        loadingDetail = false;
                    });
                }).fail(function () {
                    loadingDetail = false;
                });
            });
    }

    window.CompanyDetailActions = {
        init: init,
        closePopup: closePopup,
        submitMatching: submitMatching
    };
})(window, window.jQuery);
