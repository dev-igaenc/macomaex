(function($) {
    function showPlanAlert(message, callback) {
        if (typeof jAlert === 'function') {
            jAlert(message, '알림', function() {
                if (typeof callback === 'function') {
                    callback();
                }
            });
            return;
        }

        alert(message);
        if (typeof callback === 'function') {
            callback();
        }
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
        if (!$root.length) {
            return;
        }

        var isMember = String($root.data('isMember')) === '1';
        var getSetUrl = $root.data('getSetUrl') || '../get_set.php';
        var loginUrl = $root.data('loginUrl') || '/bbs/login.php?url=/ko/plan.php';
        var myPlanUrl = $root.data('myPlanUrl') || '/ko/my_plan.php';
        var creditPayUrl = $root.data('creditPayUrl') || '/ko/credit_pay.php';

        if (isMember) {
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

                var $button = $(this);
                var idx = $button.attr('idx');
                var mtype = $button.attr('mtype');
                var orderid = $("input[name='orderid']").val();
                var mid = $("input[name='mid']").val();

                if (!orderid || !idx) {
                    showPlanAlert('결제에 문제가 있습니다.');
                    return false;
                }

                $.post(getSetUrl, {
                    type: 'membership_credit_pay',
                    idx: idx,
                    mtype: mtype,
                    orderid: orderid,
                    mid: mid
                }).done(function(response) {
                    var parts = String(response).split('|');

                    if (parts[0] === '1000') {
                        window.open(creditPayUrl + '?orderid=' + encodeURIComponent(parts[1]), 'pyWin', 'width=520px,height=760px');
                        return;
                    }

                    if (parts[1] === '02') {
                        showPlanAlert('검색 결과가 없습니다.');
                        return;
                    }

                    showPlanAlert(parts[1] || '처리에 오류가 있습니다.');
                }).fail(function() {
                    showPlanAlert('처리에 오류가 있습니다.');
                });

                return false;

                /*
                기존 정기결제(빌링) 플로우는 보존한다.
                var planContext = getPlanContext($button);
                var kr = planContext.kr;
                var price_sale = planContext.priceSale;
                var price_info = planContext.priceInfo;

                $.post(getSetUrl, {
                    type: 'membership_pay',
                    idx: idx,
                    mtype: mtype,
                    orderid: orderid,
                    mid: mid
                }).done(function(response) {
                    var parts = String(response).split('|');
                    if (parts[0] === '1000') {
                        if (parts[1] !== 'true') {
                            jCustomConfirm(parts[1], '알림', '취소', '확인', function(r) {
                                if (!r) {
                                    popupClose();
                                    window.open('about:blank', 'pyWin', 'width=480px,height=700px');
                                    var form = $button.parents('form.mpay');
                                    form.attr('action', 'https://inilite.inicis.com/inibill/inibill_card.jsp');
                                    form.attr('target', 'pyWin');
                                    form.submit();
                                    $('#coupon_pop').val('false');
                                }
                            });
                        } else if (parts[1] === 'true' && $('#coupon_pop').val() === 'false') {
                            $('.popup .popup_top_txt1').text(kr + ' ' + price_sale + ' 원');
                            $('.popup .popup_top_txt2').text(price_info);
                            $('.popup #coupon_submit').attr('idx', idx);

                            popupAll.forEach(function(element) {
                                element.classList.remove('open');
                            });
                            var popupName = document.querySelector('.popup');
                            if (popupName && html) {
                                html.classList.add('hide');
                                popupName.classList.add('open');
                            }
                            $('#coupon').val('');
                            $('#coupon').removeClass('readonly').attr('readonly', false);
                            $('#coupon_pop').val('true');
                            return false;
                        } else {
                            popupClose();
                            window.open('about:blank', 'pyWin', 'width=480px,height=700px');
                            var form = $button.parents('form.mpay');
                            form.attr('action', 'https://inilite.inicis.com/inibill/inibill_card.jsp');
                            form.attr('target', 'pyWin');
                            form.submit();
                            $('#coupon_pop').val('false');
                        }
                    } else if (parts[1] === '02') {
                        showPlanAlert('검색 결과가 없습니다.');
                    } else {
                        showPlanAlert(parts[1] || '처리에 오류가 있습니다.');
                    }
                });
                */
            });
        } else {
            $('body').on('click', '.plan_payment', function(event) {
                event.preventDefault();
                showPlanAlert('로그인후 이용해 주세요.', function() {
                    location.href = loginUrl;
                });
            });
        }
    });
})(jQuery);
