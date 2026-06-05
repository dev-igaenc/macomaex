<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/remodal/remodal.css">', 11);
add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/remodal/remodal-default-theme.css">', 12);

add_javascript('<script src="'.G5_JS_URL.'/remodal/remodal.js"></script>', 10);
add_javascript('<script src="'.G5_JS_URL.'/jquery.register_form.js"></script>', 14);

$social_email = get_email_address($user_email);
$social_mb_id = $social_email ? strtolower(trim((string) $social_email)) : $user_id;
$social_name = isset($user_name) ? get_text($user_name) : '';
$social_phone = isset($user_phone) ? get_text($user_phone) : '';
$social_provider_label = social_get_provider_service_name($provider_name);
$social_phone_digits = preg_replace('/[^0-9]/', '', $social_phone);

if ($social_email) {
    $mem_chk = sql_fetch("select mb_id, mb_leave_date, mb_intercept_date from g5_member where mb_id='".sql_real_escape_string($social_email)."'");
} else {
    $mem_chk = array();
}
?>

<style>
    #sVisual,
    #pageTop{display:none}
    .social-join-wrap{max-width:720px;margin:72px auto 96px;padding:0 20px}
    .social-join-card{background:#fff}
    .social-join-provider{display:inline-flex;align-items:center;height:28px;padding:0 10px;border-radius:4px;background:#f4f6f8;color:#667085;font-size:13px;font-weight:700}
    .social-join-title{margin:18px 0 8px;color:#111827;font-size:28px;line-height:1.35;font-weight:800;letter-spacing:0}
    .social-join-desc{margin:0 0 28px;color:#667085;font-size:15px;line-height:1.65}
    .social-join-form{display:flex;flex-direction:column;gap:18px}
    .social-join-field label{display:block;margin-bottom:8px;color:#344054;font-size:14px;font-weight:700}
    .social-join-input{width:100%;height:52px;border:1px solid #d0d5dd;border-radius:6px;padding:0 15px;color:#101828;font-size:16px;background:#fff;box-sizing:border-box}
    .social-join-input:read-only{background:#f9fafb;color:#667085}
    .social-join-cert{display:flex;gap:8px;align-items:center}
    .social-join-cert .social-join-input{flex:1;min-width:0}
    .social-join-cert-code{display:none;margin-top:8px}
    .social-join-cert-btn{height:52px;padding:0 18px;border:0;border-radius:6px;background:#111827;color:#fff;font-size:15px;font-weight:800;white-space:nowrap;cursor:pointer}
    .social-join-cert-btn.secondary{background:#2563eb}
    .social-join-cert-btn:disabled{background:#9ca3af;cursor:not-allowed}
    .social-join-help{margin:8px 0 0;color:#8a94a6;font-size:13px;line-height:1.5}
    .social-join-error{margin:8px 0 0;color:#d92d20;font-size:13px;line-height:1.5}
    .social-join-status{margin:8px 0 0;color:#667085;font-size:13px;line-height:1.5}
    .social-join-status.success{color:#2563eb}
    .social-join-status.error{color:#d92d20}
    .social-join-actions{display:flex;gap:10px;margin-top:10px}
    .social-join-submit,.social-join-cancel{display:inline-flex;align-items:center;justify-content:center;height:54px;border-radius:6px;font-size:15px;font-weight:800;text-decoration:none;box-sizing:border-box}
    .social-join-submit{flex:1;border:0;background:#111827;color:#fff;cursor:pointer}
    .social-join-cancel{width:120px;border:1px solid #d0d5dd;background:#fff;color:#344054}
    .member_connect{margin-top:22px;text-align:center}
    .member_connect .strong{margin:0 0 8px;color:#667085;font-size:14px}
    .member_connect .btn-txt{border:0;background:transparent;color:#2563eb;font-weight:800;cursor:pointer}
    @media (max-width:640px){
        .social-join-wrap{margin:36px auto 64px;padding:0 16px}
        .social-join-title{font-size:24px}
        .social-join-actions{flex-direction:column}
        .social-join-cancel{width:100%}
        .social-join-cert{display:block}
        .social-join-cert-btn{width:100%;margin-top:8px}
    }
</style>

<div class="social-join-wrap">
    <div class="social-join-card member_sign_layout">
        <span class="social-join-provider"><?php echo get_text($social_provider_label); ?> 계정 확인 완료</span>
        <h4 class="social-join-title member_sign_title">회원가입을 마무리해 주세요</h4>
        <p class="social-join-desc">이메일, 이름, 휴대폰 번호를 확인하면 가입이 완료됩니다.</p>

        <?php if (!$social_email) { ?>
            <p class="social-join-error">소셜 계정에서 이메일을 확인할 수 없습니다. 다시 로그인해 주세요.</p>
            <div class="social-join-actions">
                <a href="<?php echo G5_BBS_URL; ?>/login.php" class="social-join-cancel" style="width:100%">로그인으로 이동</a>
            </div>
        <?php } else if (!empty($mem_chk['mb_id']) && empty($mem_chk['mb_leave_date']) && empty($mem_chk['mb_intercept_date'])) { ?>
            <p class="social-join-error">이미 가입된 이메일입니다. 기존 계정에 소셜 계정을 연결해 주세요.</p>
            <div class="member_connect">
                <p class="strong">기존 회원이신가요?</p>
                <button type="button" class="connect-opener btn-txt" data-remodal-target="modal">기존 계정에 연결하기</button>
            </div>
        <?php } else { ?>
            <form name="fregisterform" id="fregisterform" class="social-join-form" action="<?php echo $register_action_url; ?>" onsubmit="return fregisterform_submit(this);" method="post" autocomplete="off">
                <input type="hidden" name="w" value="<?php echo $w; ?>">
                <input type="hidden" name="url" value="<?php echo $urlencode; ?>">
                <input type="hidden" name="provider" value="<?php echo $provider_name; ?>">
                <input type="hidden" name="action" value="register">
                <input type="hidden" name="mb_id" value="<?php echo get_text($social_mb_id); ?>" id="reg_mb_id">
                <input type="hidden" name="mb_email" value="<?php echo get_text($social_email); ?>" id="reg_mb_email">
                <input type="hidden" name="mb_5" value="<?php echo get_text($social_email); ?>">
                <input type="hidden" name="mb_6" value="<?php echo $social_name; ?>">
                <input type="hidden" name="mb_8" value="<?php echo $social_phone; ?>">
                <input type="hidden" name="old_mb_hp" value="<?php echo $social_phone; ?>">

                <div class="social-join-field">
                    <label for="social_email_view">이메일</label>
                    <input type="text" id="social_email_view" class="social-join-input" value="<?php echo get_text($social_email); ?>" readonly>
                </div>

                <div class="social-join-field">
                    <label for="reg_mb_name">이름</label>
                    <input type="text" id="reg_mb_name" name="mb_name" class="social-join-input" value="<?php echo $social_name; ?>" maxlength="40" placeholder="이름을 입력해 주세요" required>
                    <p class="social-join-help">소셜 계정에서 이름이 내려오면 자동으로 채워집니다.</p>
                </div>

                <div class="social-join-field">
                    <label for="reg_mb_hp">휴대폰 번호</label>
                    <div class="social-join-cert">
                        <input type="text" id="reg_mb_hp" name="mb_hp" class="social-join-input" value="<?php echo $social_phone; ?>" maxlength="13" placeholder="010-0000-0000" required>
                        <button type="button" id="signup_phone_send" class="social-join-cert-btn">인증</button>
                    </div>
                    <div id="signup_phone_code_wrap" class="social-join-cert social-join-cert-code">
                        <input type="text" id="signup_phone_code" class="social-join-input" maxlength="6" inputmode="numeric" placeholder="인증번호 6자리">
                        <button type="button" id="signup_phone_verify" class="social-join-cert-btn secondary">확인</button>
                    </div>
                    <p class="social-join-help"><?php echo $social_phone_digits ? '소셜 계정에서 받은 번호를 확인해 주세요.' : '번호가 없다면 직접 입력해 주세요.'; ?></p>
                    <p id="signup_phone_status" class="social-join-status"></p>
                </div>

                <div class="social-join-actions">
                    <a href="<?php echo G5_BBS_URL; ?>/login.php" class="social-join-cancel">취소</a>
                    <button type="submit" id="btn_submit" class="social-join-submit">가입 완료</button>
                </div>
            </form>
        <?php } ?>
    </div>

    <div id="sns-link-pnl" class="remodal" data-remodal-id="modal" role="dialog" aria-labelledby="modal1Title" aria-describedby="modal1Desc">
        <button type="button" class="connect-close" data-remodal-action="close">
            <i class="fa fa-close"></i>
            <span class="txt">닫기</span>
        </button>
        <div class="connect-fg">
            <form method="post" action="<?php echo $login_action_url ?>" onsubmit="return social_obj.flogin_submit(this);">
                <input type="hidden" id="url" name="url" value="<?php echo $login_url ?>">
                <input type="hidden" id="provider" name="provider" value="<?php echo $provider_name ?>">
                <input type="hidden" id="action" name="action" value="social_account_linking">

                <div class="connect-title">기존 계정에 연결하기</div>
                <div class="connect-desc">기존 아이디에 SNS 아이디를 연결합니다.</div>

                <div id="login_fs">
                    <label for="login_id" class="login_id">아이디 (필수)</label>
                    <span class="lg_id"><input type="text" name="mb_id" id="login_id" value="<?php echo get_text($social_email); ?>" class="frm_input required" size="20" maxLength="100"></span>
                    <label for="login_pw" class="login_pw">비밀번호 (필수)</label>
                    <span class="lg_pw"><input type="password" name="mb_password" id="login_pw" class="frm_input required" size="20" maxLength="20"></span>
                    <br>
                    <input type="submit" value="연결하기" class="login_submit btn_submit">
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(function() {
    var signupPhoneVerified = false;
    var signupPhoneVerifiedValue = "";

    function setSignupPhoneStatus(message, type) {
        var $status = $("#signup_phone_status");
        $status.removeClass("success error");
        if (type) $status.addClass(type);
        $status.text(message || "");
    }

    function resetSignupPhoneVerification() {
        signupPhoneVerified = false;
        signupPhoneVerifiedValue = "";
        $("#signup_phone_code").val("");
        $("#signup_phone_code_wrap").hide();
        setSignupPhoneStatus("", "");
    }

    $("#reg_mb_hp").on("keyup blur", function() {
        var phone = $(this).val().replace(/[^0-9]/g, "");
        if (phone.length > 11) phone = phone.substr(0, 11);
        if (phone.length >= 8) {
            phone = phone.replace(/^(\d{2,3})(\d{3,4})(\d{0,4})$/, "$1-$2-$3");
        }
        $(this).val(phone);
        $("input[name=mb_8]").val(phone);
        if (signupPhoneVerifiedValue !== phone) {
            resetSignupPhoneVerification();
        }
    });

    $("#reg_mb_name").on("keyup blur", function() {
        $("input[name=mb_6]").val($.trim($(this).val()));
    });

    $("#signup_phone_send").on("click", function() {
        var phone = $.trim($("#reg_mb_hp").val());
        if (phone === "") {
            alert("휴대폰 번호를 입력해 주세요.");
            $("#reg_mb_hp").focus();
            return false;
        }

        resetSignupPhoneVerification();
        var $button = $(this);
        $button.prop("disabled", true).text("발송중");

        $.post("<?php echo G5_BBS_URL; ?>/ajax.signup_phone_send.php", { mb_hp: phone })
            .done(function(resp) {
                if (typeof resp === "string") {
                    try { resp = JSON.parse(resp); } catch(e) { resp = {}; }
                }
                if (resp.ok) {
                    $("#signup_phone_code_wrap").show();
                    setSignupPhoneStatus(resp.message || "인증번호를 발송했습니다.", "success");
                    $("#signup_phone_code").focus();
                } else {
                    setSignupPhoneStatus(resp.message || "인증번호 발송에 실패했습니다.", "error");
                    if (resp.debug) console.log("signup_phone_send", resp.debug);
                }
            })
            .fail(function() {
                setSignupPhoneStatus("인증번호 발송 중 오류가 발생했습니다.", "error");
            })
            .always(function() {
                $button.prop("disabled", false).text("인증");
            });
    });

    $("#signup_phone_verify").on("click", function() {
        var phone = $.trim($("#reg_mb_hp").val());
        var code = $.trim($("#signup_phone_code").val()).replace(/[^0-9]/g, "");
        if (code.length !== 6) {
            setSignupPhoneStatus("인증번호 6자리를 입력해 주세요.", "error");
            $("#signup_phone_code").focus();
            return false;
        }

        var $button = $(this);
        $button.prop("disabled", true).text("확인중");

        $.post("<?php echo G5_BBS_URL; ?>/ajax.signup_phone_verify.php", { mb_hp: phone, code: code })
            .done(function(resp) {
                if (typeof resp === "string") {
                    try { resp = JSON.parse(resp); } catch(e) { resp = {}; }
                }
                if (resp.ok) {
                    signupPhoneVerified = true;
                    signupPhoneVerifiedValue = phone;
                    setSignupPhoneStatus(resp.message || "휴대폰 인증이 완료되었습니다.", "success");
                } else {
                    signupPhoneVerified = false;
                    setSignupPhoneStatus(resp.message || "인증번호를 확인해 주세요.", "error");
                }
            })
            .fail(function() {
                signupPhoneVerified = false;
                setSignupPhoneStatus("인증 확인 중 오류가 발생했습니다.", "error");
            })
            .always(function() {
                $button.prop("disabled", false).text("확인");
            });
    });

    $("#signup_phone_code").on("input", function() {
        $(this).val($(this).val().replace(/[^0-9]/g, "").substring(0, 6));
    });

    window.macomaexSocialPhoneVerified = function() {
        return signupPhoneVerified && signupPhoneVerifiedValue === $.trim($("#reg_mb_hp").val());
    };
});

function fregisterform_submit(f) {
    if ($.trim(f.mb_name.value) === "") {
        alert("이름을 입력해 주세요.");
        f.mb_name.focus();
        return false;
    }

    if ($.trim(f.mb_hp.value) === "") {
        alert("휴대폰 번호를 입력해 주세요.");
        f.mb_hp.focus();
        return false;
    }

    if (typeof window.macomaexSocialPhoneVerified !== "function" || !window.macomaexSocialPhoneVerified()) {
        alert("휴대폰 인증을 완료해 주세요.");
        f.mb_hp.focus();
        return false;
    }

    $("input[name=mb_6]").val($.trim(f.mb_name.value));
    $("input[name=mb_8]").val($.trim(f.mb_hp.value));

    $("#btn_submit").prop("disabled", true);
    return true;
}
</script>
