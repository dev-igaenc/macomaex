<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>
<script src="<?php echo G5_JS_URL ?>/jquery.register_form.js"></script>

<style>
    #sVisual,
    #pageTop{display: none}
    #join_single .join_agree_head{border-bottom: none; margin-bottom: 0; padding-bottom: 0;}
    #join_single .section_divider{width: 100%; height: 1px; background: #ddd; margin: 0 0 3rem;}
    #join_single .required_mark{position: relative; display: inline-block;}
    #join_single .required_mark::after{content: '*'; color: #ff0000; padding-left: 0.25rem;}
    #join_single .formBox_title a{color: #666; text-decoration: underline; text-underline-offset: 0.2rem;}
    #join_single .formBox_title a:hover{color: #444;}
    #join_single .formBox.terms_box{margin-bottom: 2.2rem;}
    #join_single .formBox.terms_box .formBox_title{margin-bottom: 2.2rem;}
    #join_single .formBox.terms_box .checkBox{margin-top: 0.8rem;}
    #join_single .formBox.terms_all_box{margin-top: 0.6rem; margin-bottom: 2.6rem;}
    #join_single .formBox.terms_all_box .checkBox label{font-size: 1.8rem; font-weight: 700; line-height: 1.2; padding-left: 2.4rem;}
    #join_single .formBox.terms_all_box .checkBox label::before{width: 1.8rem; height: 1.8rem;}
    #join_single .formBox.terms_all_box .checkBox label::after{left: 0.25rem; width: 1.3rem;}
    #join_single .formBox.terms_box .checkBox{display: flex; align-items: center; gap: 0.8rem;}
    #join_single .formBox.terms_box .checkBox input{
        position: static;
        top: auto;
        left: auto;
        width: 1.6rem;
        height: 1.6rem;
        z-index: auto;
        flex: 0 0 1.6rem;
        cursor: pointer;
        opacity: 1;
        appearance: auto !important;
        -webkit-appearance: checkbox !important;
        -moz-appearance: auto !important;
        accent-color: #111;
    }
    #join_single .formBox.terms_box .consent_text{font-size: 1.5rem; line-height: 1.4; color: #666;}
    #join_single .formBox.terms_box .consent_text a{
        color: #666;
        text-decoration: underline;
        text-underline-offset: 0.2rem;
    }
    #join_single .formBox.terms_box .consent_text a:hover{color: #444;}
</style>

<div id="join_single">
    <div class="member_sign_layout">
        <form action="<?= $register_action_url ?? '' ?>" method="post" id="join_form" autocomplete="off">
            <input type="hidden" name="url" value="<?= $url ?? '' ?>">
            <input type="hidden" name="w" value="">
            <input type="hidden" name="mb_id" id="mb_id" value="">
            <input type="hidden" id="reg_mb_id" value="">
            <input type="hidden" name="mb_password" id="mb_password_hidden" value="">
            <input type="hidden" name="agree_chk" id="agree_chk" value="">
            <!-- 사업자번호/법인명 자동조회 기능 이관 전 임시 유지값 -->
            <input type="hidden" name="com_chk" id="com_chk" class="com_chk" value="true">
            <input type="hidden" name="mb_name" id="mb_name" value="">
            <input type="hidden" name="mb_company" id="mb_company" value="">
            <input type="hidden" name="register_flow" value="single_join_v1">
            <input type="hidden" name="provider" value="<?php echo $provider ?? ''; ?>">
            <input type="hidden" name="cert_type" value="<?php echo $cert_type ?? ''; ?>">
            <input type="hidden" name="cert_no" value="<?php echo $cert_no ?? ''; ?>">
            <input type="hidden" name="id" value="<?php echo $mb_id ?? ''; ?>">

            <h4 class="member_sign_title">회원가입</h4>

			<!--
            <ul class="join_step step3">
                <li><span class="circle"></span><span class="text">계정정보</span></li>
                <li><span class="circle"></span><span class="text">약관동의</span></li>
                <li><span class="circle"></span><span class="text">기업정보</span></li>
            </ul>
			-->

            <div class="join_agree_body">
                <div class="formArea">
                    <div class="formBox email">
                        <p class="formBox_title required_mark">이메일</p>
                        <div class="inputBox">
                            <input type="hidden" name="id_chk" value="false" id="id_chk">
                            <input type="text" name="id" id="id" class="normal" maxlength="32">
                        </div>
                        <p class="formBox_errorM">이메일 주소를 입력해주세요</p>
                    </div>

                    <div class="formBox password">
                        <p class="formBox_title required_mark">비밀번호</p>
                        <div class="inputBox">
                            <input type="hidden" name="password_chk" value="false" id="password_chk">
                            <input type="password" name="password" id="password" class="password pass_chk" maxlength="16">
                            <div class="input_password_icon"></div>
                        </div>
                        <p class="formBox_errorM">8~16자 영문/숫자/특수문자 조합해 주세요.</p>
                    </div>

                    <div class="formBox password">
                        <p class="formBox_title required_mark">비밀번호 확인</p>
                        <div class="inputBox">
                            <input type="hidden" name="passwordre_chk" value="false" id="passwordre_chk">
                            <input type="password" name="password_re" id="password_re" class="password" maxlength="16">
                            <div class="input_password_icon"></div>
                        </div>
                        <p class="formBox_errorM"></p>
                    </div>

                    <!--
                    <div class="formBox">
                        <div class="inputBox requied type2">
                            <p class="title">사업자등록번호</p>
                            <input type="text" name="mb_4" id="mb_4" class="comnum_chk" maxlength="12">
                            <button type="button" class="btn srh">확인</button>
                        </div>
                    </div>

                    <div class="formBox">
                        <div class="inputBox requied type2">
                            <p class="title">법인명</p>
                            <input type="text" name="mb_name" id="mb_name" value="" readonly>
                            <input type="hidden" name="mb_company" id="mb_company" class="readonly" readonly value="">
                        </div>
                    </div>

                    <div class="formBox">
                        <div class="inputBox type2 type3 Product">
                            <p class="title">주요 서비스/제품</p>
                            <div class="products addz">
                                <input name="mb_9[]" type="text" maxlength="32">
                                <button type="button" class="add">+</button>
                            </div>
                        </div>
                    </div>
                    -->

                    <div class="section_divider"></div>

					<div class="join_agree_head">
						<div class="formBox terms_all_box">
                        	<div class="checkBox all"><input type="checkbox" name="agreeAll" id="agreeAll"><label for="agreeAll">모두 동의합니다.</label></div>
                    	</div>
						<p>회원 가입 및 회원 관리 등의 목적으로 서비스 이용약관, 개인정보를 수집 및 이용하고 있습니다.</p>
					</div>

                    <div class="formBox terms_box">
                        <div class="checkBox">
                            <input type="checkbox" name="terms" id="terms" class="req">
                            <span class="consent_text">
                                <a href="<?= $link_1201 ?? '/etc/clause.php' ?>" target="_blank" rel="noopener noreferrer">이용약관</a>에 동의합니다.
                            </span>
                        </div>
                    </div>

                    <div class="formBox terms_box">
                        <div class="checkBox">
                            <input type="checkbox" name="privacy" id="privacy" class="req">
                            <span class="consent_text">
                                <a href="<?= $link_1202 ?? '/etc/privacy.php' ?>" target="_blank" rel="noopener noreferrer">개인정보 수집 및 이용동의</a>에 동의합니다.
                            </span>
                        </div>
                    </div>

					<div class="formBox">
                        <div class="inputBox type2">
                            <p class="title">가입이벤트쿠폰</p>
                            <input type="text" name="coupon" id="coupon" placeholder="ex) ED9A01" maxlength="6">
                        </div>
                    </div>
                </div>
            </div>

            <div class="formBtns">
                <button type="button" id="join_submit" class="agree">가입하기</button>
                <button type="button" class="disagree">취소하기</button>
            </div>
        </form>
    </div>
</div>

<script>
function isValidEmailFormat(email) {
    return /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/.test(email);
}

function levenshteinDistance(a, b) {
    var s = String(a || "");
    var t = String(b || "");
    var m = s.length;
    var n = t.length;
    var dp = [];
    var i, j;

    for (i = 0; i <= m; i++) {
        dp[i] = [];
        dp[i][0] = i;
    }
    for (j = 0; j <= n; j++) {
        dp[0][j] = j;
    }

    for (i = 1; i <= m; i++) {
        for (j = 1; j <= n; j++) {
            var cost = (s.charAt(i - 1) === t.charAt(j - 1)) ? 0 : 1;
            dp[i][j] = Math.min(
                dp[i - 1][j] + 1,
                dp[i][j - 1] + 1,
                dp[i - 1][j - 1] + cost
            );
        }
    }

    return dp[m][n];
}

function suggestEmailDomainCorrection(email) {
    var value = $.trim(String(email || ""));
    var at = value.lastIndexOf("@");
    if (at < 1 || at === value.length - 1) return "";

    var localPart = value.substring(0, at);
    var domain = value.substring(at + 1).toLowerCase();
    var parts = domain.split(".");
    if (parts.length < 2) return "";

    var commonDomains = [
        "gmail.com", "naver.com", "daum.net", "hanmail.net", "kakao.com",
        "nate.com", "hotmail.com", "outlook.com", "icloud.com", "yahoo.com"
    ];
    var tldFixMap = {
        "cmo": "com", "xom": "com", "vom": "com", "con": "com",
        "nett": "net", "nte": "net", "or": "org", "ogr": "org",
        "krd": "kr", "coom": "com", "comm": "com"
    };

    var tld = parts[parts.length - 1];
    var sld = parts[parts.length - 2];
    var base = sld + "." + tld;
    var best = "";
    var bestDist = 99;
    var i;

    if (tldFixMap[tld]) {
        var fixedParts = parts.slice(0);
        fixedParts[fixedParts.length - 1] = tldFixMap[tld];
        return localPart + "@" + fixedParts.join(".");
    }

    for (i = 0; i < commonDomains.length; i++) {
        var d = levenshteinDistance(base, commonDomains[i]);
        if (d < bestDist) {
            bestDist = d;
            best = commonDomains[i];
        }
    }

    if (best && bestDist <= 2 && base !== best) {
        var prefixParts = parts.slice(0, parts.length - 2);
        var suggestedDomain = (prefixParts.length ? (prefixParts.join(".") + ".") : "") + best;
        return localPart + "@" + suggestedDomain;
    }

    return "";
}

function validateSignupEmailAndMark() {
    var $input = $("#id");
    var email = $.trim($input.val());

    if (!isValidEmailFormat(email)) {
        if (email) $input.parents(".formBox").find(".formBox_errorM").text("올바른 이메일 주소가 아닙니다.");
        else $input.parents(".formBox").find(".formBox_errorM").text("이메일 주소를 입력해주세요.");
        $("#id_chk").val("false");
        return;
    }

    var suggestion = suggestEmailDomainCorrection(email);
    if (suggestion) {
        $input.parents(".formBox").find(".formBox_errorM").text("이메일 도메인 오타 가능성: " + suggestion);
        $("#id_chk").val("false");
        return;
    }

    $input.parents(".formBox").find(".formBox_errorM").text("");
    $.post("../get_set.php", { type: "member_check", id: email })
        .done(function(resp) {
            var parsed = $.trim(resp).split("|");
            if (parsed[0] != "1000") {
                $input.parents(".formBox").find(".formBox_errorM").text(parsed[1] || "가입한 이력이 있습니다.");
                $("#id_chk").val("false");
            } else {
                $("#id_chk").val("true");
            }
        })
        .fail(function() {
            $("#id_chk").val("true");
        });
}

$("#id").on("input blur change", function(){ validateSignupEmailAndMark(); });

function validateString(event){
    const pattern = /^(?=.*[a-zA-Z])(?=.*\d)(?=.*[~@#$!%*?&^()])[a-zA-Z\d~@#$!%*?&^()]{8,16}$/;
    return !/[ㄱ-ㅎㅏ-ㅣ가-힣]/.test(event) && pattern.test(event);
}

var errorM = "first";
$(".pass_chk").keyup(function(){
    var $this = $(this);
    if(errorM == "first") errorM = $this.parents(".formBox").find(".formBox_errorM").text();
    if($this.val().length == 0) {
        $this.parents(".formBox").find(".formBox_errorM").text(errorM);
        return false;
    }
    if(validateString($this.val())) {
        $this.parents(".formBox").find(".formBox_errorM").text("").hide();
        $("#password_chk").val("true");
    } else {
        $this.parents(".formBox").find(".formBox_errorM").text("8~16자 영문/숫자/특수문자 조합해 주세요").show();
        $("#password_chk").val("false");
    }
});

$("#password_re").keyup(function(){
    var $this = $(this);
    var password = $("#password").val();
    var password_re = $this.val();

    if(password == password_re) {
        $this.parents(".formBox").find(".formBox_errorM").text("");
        $("#passwordre_chk").val("true");
    } else {
        $this.parents(".formBox").find(".formBox_errorM").text("비밀번호가 일치하지 않습니다.").show();
        $("#passwordre_chk").val("false");
    }
});

$("#agreeAll").click(function(){
    var chk = $(this).is(":checked");
    if(chk) $("input.req").prop("checked", true);
    else $("input.req").prop("checked", false);
});

// 사업자등록번호 자동조회 기능 이관 예정으로 임시 비활성화
// $('.comnum_chk').on('keyup', function(){
//     var num = $(this).val();
//     num.trim();
//     this.value = AutoHypen(num);
// });

function AutoHypen(companyNum){
    companyNum = companyNum.replace(/[^0-9]/g, '');
    var tempNum = '';

    if(companyNum.length < 4){
        return companyNum;
    } else if(companyNum.length < 6){
        tempNum += companyNum.substr(0,3) + '-' + companyNum.substr(3,2);
        return tempNum;
    } else {
        tempNum += companyNum.substr(0,3) + '-' + companyNum.substr(3,2) + '-' + companyNum.substr(5);
        return tempNum;
    }
}

// $(".srh").click(function(){
//     var comnum = $(this).prev().val();
//     if(comnum.length < 12) {
//         $("#com_chk").val("false");
//         jAlert("사업자등록번호를 확인하세요.","알림",function(){ $("#mb_4").focus(); });
//         return false;
//     }
//
//     $.post("../get_set.php", { type: 'join_comp_chk', comnum: comnum })
//         .done(function(jqXHR) {
//             jqXHR = jqXHR.split("|");
//             if(jqXHR[0] == 1000){
//                 $("#com_chk").val("true");
//                 $("#mb_name").val(jqXHR[1]).attr('readonly', true);
//                 $("#mb_company").val(jqXHR[2]).attr('readonly', true);
//             } else if(jqXHR[0] == 501){
//                 $("#com_chk").val("false");
//                 jAlert("해당 사업자 번호로는 등록할 수 없습니다.","알림",function(){ $("#mb_4").focus(); });
//             } else {
//                 $("#com_chk").val("false");
//                 jAlert("처리에 오류가 있습니다.","알림");
//             }
//         })
//         .fail(function() {
//             $("#com_chk").val("false");
//             jAlert("처리에 오류가 있습니다.","알림");
//         });
// });

if ($(".Product .addz").length > 0) {
    $(".Product .addz").wrap("<div></div>");
    var addz = $(".Product .addz").parent().html();
    if (addz) {
        addz = addz.replace("+","-");
        addz = addz.replace('"add"','"minus"');
    }
    $(".Product .addz").unwrap();
    $(".Product .add").click(function(){ if (addz) $(".Product").append(addz); });
    $("body").on("click", ".Product .minus", function(){ $(this).parents(".products").remove(); });
}

if($("#coupon").length > 0){
    $("#coupon").keyup(function(){
        if($("#coupon").val().length == 6){
            var id = $("#coupon").val();
            $.post("../get_set.php", { type: 'coupon_chk', id: id })
                .done(function(jqXHR) {
                    jqXHR = jqXHR.split("|");
                    if(jqXHR[0] == 1000){
                        $("#coupon").addClass("readonly").attr('readonly', true);
                        jAlert("할인 쿠폰 적용됨");
                    } else if(jqXHR[0] == 500){
                        jAlert(jqXHR[1],"알림",function(){ $("#coupon").val(""); $("#coupon").focus(); });
                        return false;
                    } else {
                        $("#com_chk").val("false");
                        jAlert("처리에 오류가 있습니다.","알림");
                    }
                });
            return false;
        }
    });
}

function fregisterform_submit(){
    if ($("input[name='w']").val() == "") {
        var msg = $.trim(reg_mb_id_check());
        if (msg) {
            alert(msg);
            return false;
        }
    }

    // 사업자등록번호/법인명 확인 기능은 이관 예정으로 임시 비활성화

    if($("#coupon").val().length > 0 && $("#coupon").val().length < 6) {
        jAlert("쿠폰번호를 확인해 주세요.","알림");
        return false;
    }

    return true;
}

$("#join_submit").on("click", function(){
    var email = $.trim($("#id").val());

    if(!isValidEmailFormat(email)) {
        alert("이메일을 확인해 주세요.");
        $("#id").focus();
        return false;
    }

    var suggestedEmail = suggestEmailDomainCorrection(email);
    if (suggestedEmail) {
        jAlert("이메일 도메인이 오타로 보입니다.\n" + suggestedEmail + " 로 입력해 주세요.","알림", function(){
            $("#id").val(suggestedEmail).focus();
            $("#id").trigger("input");
        });
        return false;
    }

    var emailBlocked = false;
    $.ajax({
        url: "../get_set.php",
        type: "POST",
        data: { type: "member_check", id: email },
        async: false,
        success: function(resp) {
            var parsed = $.trim(resp).split("|");
            if (parsed[0] != "1000") {
                emailBlocked = true;
                jAlert(parsed[1] || "가입한 이력이 있습니다.","알림", function(){ $("#id").focus(); });
            }
        },
        error: function() {
            emailBlocked = false;
        }
    });
    if(emailBlocked) return false;

    if($("#password_chk").val() == "false") {
        alert("비밀번호를 확인해 주세요.");
        return false;
    }
    if($("#passwordre_chk").val() == "false") {
        alert("비밀번호 확인을 진행해 주세요.");
        return false;
    }

    if(!$("#terms").is(":checked")) {
        alert("이용약관에 동의해 주세요.");
        return false;
    }
    if(!$("#privacy").is(":checked")) {
        alert("개인정보수집에 동의해 주세요.");
        return false;
    }

    $("#reg_mb_id").val(email);
    if(!fregisterform_submit()) return false;

    $("#agree_chk").val("#terms#privacy");
    $("#mb_id").val(email);
    // 서버의 mb_name 필수검증 유지 대응: 별도 입력필드가 없으므로 이메일을 기본값으로 전달
    $("#mb_name").val(email);
    $("#mb_password_hidden").val($("#password").val());
    $("#join_form").off("submit").on("submit", function(){ return true; });
    $("#join_form").submit();
});

$(".formBtns button.disagree").click(function(){
    var result = confirm('동의하지 않으면 회원가입이 불가능합니다.\\n확인을 클릭하시면 메인페이지로 이동합니다.');
    if(result) location.replace('/');
});

const inputPasswordBoxes = document.querySelectorAll(".formBox.password");
inputPasswordBoxes.forEach((inputPassword) => {
    const inputPasswordInput = inputPassword.querySelector("input.password");
    const inputPasswordIcon = inputPassword.querySelector(".input_password_icon");
    if (!inputPasswordIcon || !inputPasswordInput) return;
    inputPasswordIcon.addEventListener("click", () => {
        inputPasswordIcon.classList.toggle("view");
        inputPasswordInput.type = (inputPasswordInput.type === "password") ? "text" : "password";
    });
});
</script>
