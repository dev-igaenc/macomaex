(function($) {
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

    $(function() {
        var $root = $('#planWrap');
        if (!$root.length) return;

        var isMember = String($root.data('isMember')) === '1';
        var getSetUrl = $root.data('getSetUrl') || '../get_set.php';
        var loginUrl = $root.data('loginUrl') || '/bbs/login.php?url=/ko/plan.php';
        var myPlanUrl = $root.data('myPlanUrl') || '/ko/my_plan.php';
        var creditPayUrl = $root.data('creditPayUrl') || '/ko/credit_pay.php';
        var membershipCreditPayToken = String($root.data('membershipCreditPayToken') || '');
        var isSubmittingPayment = false;

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
                showPlanAlert('로그인후 이용해 주세요.', function() {
                    location.href = loginUrl;
                });
                return false;
            }

            if (isSubmittingPayment) return false;

            var idx = $(this).attr('idx');
            var orderid = $('#plan_orderid').val() || $("input[name='orderid']").val();

            // 사용자 클릭 제스처 안에서 먼저 팝업을 열어 팝업 차단을 줄인다.
            var payWindow = window.open('about:blank', 'pyWin', 'width=520px,height=760px');
            if (!payWindow) {
                showPlanAlert('팝업이 차단되었습니다. 브라우저 팝업을 허용해 주세요.');
                return false;
            }

            if (!orderid || !idx || !membershipCreditPayToken) {
                if (!payWindow.closed) payWindow.close();
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
                    payWindow.location.href = creditPayUrl + '?orderid=' + encodeURIComponent(parts[1]);
                    return;
                }

                if (!payWindow.closed) payWindow.close();
                showPlanAlert(parts[1] || '처리에 오류가 있습니다.');
            }).fail(function() {
                if (!payWindow.closed) payWindow.close();
                showPlanAlert('처리에 오류가 있습니다.');
            }).always(function() {
                isSubmittingPayment = false;
            });

            return false;
        });
    });
})(jQuery);
