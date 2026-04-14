$(function () {
    $("a.receipt").on("click", function () {
        var oid = $(this).attr("oid");

        $.post("../get_set.php", {
            type: "receipt",
            oid: oid
        }).done(function (jqXHR) {
            jqXHR = jqXHR.split("|");
            if (jqXHR[0] == 1000) {
                var receiptUrl = "https://iniweb.inicis.com/DefaultWebApp/mall/cr/cm/mCmReceipt_head.jsp?noTid=" + jqXHR[1] + "&noMethod=1";
                window.open(receiptUrl, "receipt", "width=430,height=700,scrollbars=yes,toolbar=no,menubar=no");
            } else {
                if (jqXHR[1] == "02") {
                    jAlert("검색 결과가 없습니다.", "알림");
                } else {
                    jAlert(jqXHR[1], "알림");
                }
            }
        });
    });

    $("#membership_cancel").on("click", function () {
        var ms_id = $("#ms_id").val();
        var isCredit = $("#is_credit_membership").val();

        if (ms_id) {
            var confirmText = (isCredit == "1")
                ? "결제 취소(환불) 시 구매한 크레딧이 회수됩니다.\n이미 일부 사용한 경우 환불이 제한될 수 있습니다.\n결제 취소를 진행하시겠습니까?"
                : "구독취소는 결제 후 7일 이내\n매칭 요청 서비스를 이용하지 않은 경우, 전액 환불처리 되며 \n7일 이후 혹은 사용 이력이 있으시면\n만료일까지 유지후 구독 취소됩니다. \n구독을 취소하시겠습니까?";

            jCustomConfirm(confirmText, "알림", "취소", "확인", function (e) {
                if (!e) {
                    $.post("../get_set.php", {
                        type: "membership_cancel",
                        cancel_token: $("#membership_cancel_token").val()
                    }).done(function (jqXHR) {
                        jqXHR = jqXHR.split("|");
                        if (jqXHR[0] == 1000) {
                            if (jqXHR[1]) {
                                jAlert(jqXHR[1], "알림", function () {
                                    location.reload();
                                });
                            } else {
                                jAlert("구독 취소 처리되었습니다.", "알림", function () {
                                    location.reload();
                                });
                            }
                        } else {
                            if (jqXHR[1] == "02") {
                                jAlert("검색 결과가 없습니다.", "알림");
                            } else {
                                jAlert(jqXHR[1], "알림");
                            }
                        }
                    });
                }
            });
        } else {
            jAlert("구독취소는 맴버쉽 이용자만 가능합니다.");
        }
    });
});
