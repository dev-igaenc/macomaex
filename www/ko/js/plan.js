(function($) {
    function isLikelyMobileBrowser() {
        var ua = navigator.userAgent || navigator.vendor || window.opera || '';
        return /android|iphone|ipad|ipod|mobile|windows phone/i.test(ua);
    }

    function showPlanAlert(message, callback) {
        if (typeof jAlert === 'function') {
            jAlert(message, '알림', function() {
                if (typeof callback === 'function') callback();
            });
            return;
        }

        alert(message);
        if (typeof callback === 'function') callback();
    }

    function getPlanContext($button) {
        return {
            kr: $button.data('planKr') || '',
            priceSale: $button.data('priceDisplay') || '',
            priceInfo: $button.data('priceInfo') || ''
        };
    }

    function getLoginReturnUrl() {
        return '/bbs/login.php?url=' + encodeURIComponent(window.location.pathname + window.location.search);
    }

    function submitCreditPayPopup(creditPayUrl, orderid) {
        var formId = 'creditPayPopupForm';
        var fieldId = 'creditPayPopupOrderid';
        var form = document.getElementById(formId);
        var orderField;

        if (!form) {
            form = document.createElement('form');
            form.id = formId;
            form.method = 'GET';
            form.target = 'pyWin';
            form.style.display = 'none';

            orderField = document.createElement('input');
            orderField.type = 'hidden';
            orderField.name = 'orderid';
            orderField.id = fieldId;
            form.appendChild(orderField);

            document.body.appendChild(form);
        } else {
            orderField = document.getElementById(fieldId);
        }

        form.action = creditPayUrl;
        orderField.value = orderid;
        form.submit();
    }

    // 기업목록(matching_list.js)의 로그인 필요 모달과 동일한 UI/동작을 재사용한다.
    function openLoginRequiredModal(descriptionHtml) {
        var modalId = 'matchingListLoginRequiredModal';
        var overlay = document.getElementById(modalId);

        function closeModal() {
            if (!overlay) return;
            overlay.classList.remove('is-open');
            document.documentElement.classList.remove('hide-scroll');
        }

        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = modalId;
            overlay.className = 'login-guard-modal';
            overlay.innerHTML = ''
                + '<div class="login-guard">'
                + '  <div class="login-guard__overlay" aria-hidden="true"></div>'
                + '  <div class="login-guard__modal" role="dialog" aria-modal="true" aria-labelledby="matchingListLoginModalTitle">'
                + '    <button type="button" class="login-guard__close" aria-label="닫기">'
                + '      <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>'
                + '    </button>'
                + '    <div class="login-guard__icon" aria-hidden="true">'
                + '      <svg viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1s3.1 1.39 3.1 3.1v2z"/></svg>'
                + '    </div>'
                + '    <h3 class="login-guard__title" id="matchingListLoginModalTitle">로그인이 필요합니다</h3>'
                + '    <p class="login-guard__desc" id="matchingListLoginModalDesc"></p>'
                + '    <div class="login-guard__actions">'
                + '      <a href="/signup" class="login-guard__btn login-guard__btn--ghost">회원가입</a>'
                + '      <a href="' + getLoginReturnUrl() + '" class="login-guard__btn login-guard__btn--primary">로그인</a>'
                + '    </div>'
                + '  </div>'
                + '</div>';
            document.body.appendChild(overlay);

            var dim = overlay.querySelector('.login-guard__overlay');
            if (dim) dim.addEventListener('click', closeModal);

            var closeBtn = overlay.querySelector('.login-guard__close');
            if (closeBtn) closeBtn.addEventListener('click', closeModal);

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && overlay.classList.contains('is-open')) closeModal();
            });
        }

        var descEl = overlay.querySelector('#matchingListLoginModalDesc');
        if (descEl) descEl.innerHTML = descriptionHtml || '더보기는<br>로그인 후 이용하실 수 있습니다.';

        var loginBtn = overlay.querySelector('.login-guard__btn--primary');
        if (loginBtn) loginBtn.setAttribute('href', getLoginReturnUrl());

        overlay.classList.add('is-open');
        document.documentElement.classList.add('hide-scroll');
    }

    $(function() {
        var $root = $('#planWrap');
        if (!$root.length) return;

        var isMember = String($root.data('isMember')) === '1';
        var getSetUrl = $root.data('getSetUrl') || '../get_set.php';
        var loginUrl = $root.data('loginUrl') || '/bbs/login.php?url=/plans';
        var myPlanUrl = $root.data('myPlanUrl') || '/account/credits';
        var creditPayUrl = $root.data('creditPayUrl') || '/ko/credit_pay.php';
        var creditMobilePayUrl = $root.data('creditMobilePayUrl') || '/ko/credit_pay_mobile.php';
        var membershipCreditPayToken = String($root.data('membershipCreditPayToken') || '');
        var isSubmittingPayment = false;
        var isMobileFlow = isLikelyMobileBrowser();

        /*
        현재 미사용: 쿠폰 결제 플로우 복구 대비 보존
        var html = document.querySelector('html');
        var popupAll = document.querySelectorAll('.popup');

        window.popupClose = function() {
            popupAll.forEach(function(element) {
                element.classList.remove('open');
            });
            if (html) {
                html.classList.remove('hide');
            }
            $('#coupon_chk').val('false');
            $('#coupon_pop').val('false');
        };

        $('#coupon').on('keyup', function() {
            if ($('#coupon').val().length !== 6) {
                return;
            }

            $.post(getSetUrl, {
                type: 'coupon_chk',
                id: $('#coupon').val()
            }).done(function(response) {
                var parts = String(response).split('|');

                if (parts[0] === '1000') {
                    var $targetButton = $("a.plan_payment[idx='" + parts[1] + "']").first();
                    var planContext = getPlanContext($targetButton);

                    $('.popup .popup_top_txt1').html("<span class='coupon'>적용쿠폰 (" + parts[3] + " 개월) - </span>" + planContext.kr + ' ' + planContext.priceSale);
                    $('.popup .popup_top_txt2').text(planContext.priceInfo);
                    $('.popup #coupon_submit').attr('idx', parts[1]);

                    $('#coupon').addClass('readonly').attr('readonly', true);
                    $('#coupon_chk').val('true');
                    $('#coupon_submit').text('적용');
                    showPlanAlert("정상적인 쿠폰입니다.\n적용 버튼을 눌러 적용해주세요.");
                } else if (parts[0] === '500') {
                    showPlanAlert(parts[1], function() {
                        $('#coupon').val('');
                        $('#coupon').focus();
                        $('#coupon_chk').val('false');
                    });
                } else {
                    $('#coupon_chk').val('false');
                    showPlanAlert('처리에 오류가 있습니다.');
                }
            }).fail(function() {
                $('#coupon_chk').val('false');
                showPlanAlert('처리에 오류가 있습니다.');
            });

            return false;
        });

        $('body').on('click', '#coupon_submit', function() {
            var $button = $(this);

            if ($('#coupon_chk').val() === 'false') {
                var idx = $button.attr('idx');
                $("a.plan_payment[idx='" + idx + "']").first().trigger('click');
                return;
            }

            $.post(getSetUrl, {
                type: 'use_coupon',
                coupon: $('#coupon').val()
            }).done(function(response) {
                var parts = String(response).split('|');
                if (parts[0] === '1000') {
                    location.href = myPlanUrl;
                } else {
                    showPlanAlert(parts[1] || '처리에 오류가 있습니다.');
                }
            }).fail(function() {
                showPlanAlert('처리에 오류가 있습니다.');
            });
        });
        */

        $('body').on('click', '.plan_payment', function(event) {
            event.preventDefault();

            if (!isMember) {
                openLoginRequiredModal('충전은<br>로그인 후 이용하실 수 있습니다.');
                return false;
            }

            if (isSubmittingPayment) return false;

            var idx = $(this).attr('idx');
            var orderid = $('#plan_orderid').val() || $("input[name='orderid']").val();

            var payWindow = null;
            if (!isMobileFlow) {
                // 사용자 클릭 제스처 안에서 먼저 팝업을 열어 팝업 차단을 줄인다.
                payWindow = window.open('about:blank', 'pyWin', 'width=860px,height=630px');
                if (!payWindow) {
                    showPlanAlert('팝업이 차단되었습니다. 브라우저 팝업을 허용해 주세요.');
                    return false;
                }
            }

            if (!orderid || !idx || !membershipCreditPayToken) {
                if (payWindow && !payWindow.closed) payWindow.close();
                showPlanAlert('결제에 문제가 있습니다.');
                return false;
            }

            isSubmittingPayment = true;
            $.post(getSetUrl, {
                type: 'membership_credit_pay',
                idx: idx,
                orderid: orderid,
                pay_token: membershipCreditPayToken
            }).done(function(response) {
                var parts = $.trim(String(response)).split('|');
                if (parts[0] === '1000' && parts[1]) {
                    if (isMobileFlow) {
                        // 모바일은 일반결제 전용 엔드포인트로 바로 이동한다.
                        window.location.href = creditMobilePayUrl + '?orderid=' + encodeURIComponent(parts[1]);
                        return;
                    }
                    // 기존 멤버십 결제 방식과 동일하게, DOM에 유지되는 form을 pyWin으로 submit한다.
                    submitCreditPayPopup(creditPayUrl, parts[1]);
                    return;
                }

                if (payWindow && !payWindow.closed) payWindow.close();
                showPlanAlert(parts[1] || '처리에 오류가 있습니다.');
            }).fail(function() {
                if (payWindow && !payWindow.closed) payWindow.close();
                showPlanAlert('처리에 오류가 있습니다.');
            }).always(function() {
                isSubmittingPayment = false;
            });

            return false;
        });
    });
})(jQuery);
