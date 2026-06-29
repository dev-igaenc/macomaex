<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
$board['bo_10']="6"; // 1뎁스
$board['bo_9']="3"; // 2뎁스

include_once(G5_THEME_PATH.'/head.php');

$member_business_number = trim((string) $member['mb_4']);
$member_company_name = trim((string) $member['mb_name']);
$member_company_code = trim((string) $member['mb_company']);
$has_business_number = $member_business_number !== '';
$has_verified_company = ($member_business_number !== '' && $member_company_name !== '' && $member_company_code !== '');
$display_company_name = $has_business_number ? $member_company_name : '';
$member_terms_agreed = trim((string) ($member['mb_9'] ?? '')) === '1';
$member_privacy_agreed = trim((string) ($member['mb_10'] ?? '')) === '1';
$member_marketing_agreed = trim((string) ($member['mb_11'] ?? '')) === '1';
?>

<!-- 회원정보 입력/수정 시작 { -->
<script src="<?php echo G5_JS_URL ?>/jquery.register_form.js"></script>
<?php if($config['cf_cert_use'] && ($config['cf_cert_ipin'] || $config['cf_cert_hp'])) { ?>
<script src="<?php echo G5_JS_URL ?>/certify.js?v=<?php echo G5_JS_VER; ?>"></script>
<?php } ?>
<style>
#fregisterform .section_divider {
	width: 100%;
	height: 1px;
	margin: 2.4rem 0;
	background: #ddd;
}
#fregisterform .terms_box {
	margin-bottom: 1.8rem;
}
#fregisterform .terms_box:last-of-type {
	margin-bottom: 0;
}
#fregisterform .terms_box .checkBox {
	display: flex;
	align-items: center;
	gap: 0.8rem;
}
#fregisterform .terms_box .checkBox input {
	position: static;
	top: auto;
	left: auto;
	width: 1.6rem;
	height: 1.6rem;
	flex: 0 0 1.6rem;
	cursor: pointer;
	opacity: 1;
	appearance: auto !important;
	-webkit-appearance: checkbox !important;
	-moz-appearance: auto !important;
	accent-color: #111;
}
#fregisterform .consent_text {
	font-size: 1.5rem;
	line-height: 1.4;
	color: #666;
}
#fregisterform .consent_text a {
	color: #666;
	text-decoration: underline;
	text-underline-offset: 0.2rem;
}
#fregisterform .consent_text a:hover {
	color: #444;
}
#fregisterform .cert_input_group {
	display: flex;
	gap: 0.8rem;
	align-items: stretch;
}
#fregisterform .member_phone_row {
	align-items: center;
}
#fregisterform .member_phone_row #mb_8 {
	width: 15.4rem !important;
}
#fregisterform .member_phone_row #member_phone_send {
	width: 5.6rem;
	height: 5rem;
	min-height: 5rem;
	padding: 0;
	margin-bottom: 0;
}
#fregisterform .member_phone_code_row {
	display: none;
	gap: 1rem;
	align-items: center;
	box-sizing: border-box;
	padding-left: calc(100% - 22rem);
	margin-top: 0.8rem;
}
#fregisterform .member_phone_code_row .title {
	display: none;
}
#fregisterform .member_phone_code_row #member_phone_code {
	width: 15.4rem !important;
}
#fregisterform .member_phone_code_row #member_phone_verify {
	width: 5.6rem;
	height: 5rem;
	min-height: 5rem;
	padding: 0;
	margin-bottom: 0;
}
#fregisterform .member_phone_status_row {
	width: 28rem;
	box-sizing: border-box;
	margin-left: calc(100% - 22rem);
}
#fregisterform .cert_btn {
	min-height: 4.8rem;
	padding: 0 1.8rem;
	margin-bottom: 10px;
	border: 0;
	border-radius: 0.4rem;
	background: #111;
	color: #fff;
	font-size: 1.5rem;
	font-weight: 700;
	white-space: nowrap;
	cursor: pointer;
}
#fregisterform .cert_btn.secondary {
	background: #2563eb;
}
#fregisterform .cert_btn:disabled {
	background: #9ca3af;
	cursor: not-allowed;
}
#fregisterform .phone_cert_code {
	margin-top: 0.8rem;
}
#fregisterform .phone_cert_status {
	margin-top: 0.8rem;
	color: #666;
	font-size: 1.4rem;
	line-height: 1.5;
}
#fregisterform .phone_cert_status.success { color: #2563eb; }
#fregisterform .phone_cert_status.warning { color: #b45309; }
#fregisterform .phone_cert_status.error { color: #d92d20; }
@media (max-width: 640px){
	#fregisterform .member_phone_code_row,
	#fregisterform .member_phone_status_row {
		width: 100%;
		margin-left: 0;
		padding-left: 0;
	}
	#fregisterform .member_phone_row #mb_8,
	#fregisterform .member_phone_code_row #member_phone_code {
		width: 100% !important;
	}
	#fregisterform .cert_input_group {
		display: block;
	}
	#fregisterform .cert_btn {
		width: 100%;
		margin-top: 0.8rem;
	}
}
</style>

<form id="fregisterform" name="fregisterform" action="<?php echo $register_action_url ?>" onsubmit="return fregisterform_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
	<input type="hidden" name="w" value="<?php echo $w ?>">
	<input type="hidden" name="url" value="<?php echo $urlencode ?>">
	<input type="hidden" name="agree" value="<?php echo $agree ?>">
	<input type="hidden" name="agree2" value="<?php echo $agree2 ?>">
	<input type="hidden" name="cert_type" value="<?php echo $member['mb_certify']; ?>">
	<input type="hidden" name="cert_no" value="">
		<input type="hidden" name="mb_3" value="<?php echo $member['mb_3'] ?>">
		<input type="hidden" name="mb_7" value="<?php echo $member['mb_7'] ?>">
		<input type="hidden" name="use_member_consent" value="1">
		<input type="hidden" name="com_chk" id="com_chk" value="<?php echo $has_verified_company ? 'true' : 'false'; ?>">
	<input type="hidden" name="origin_mb_4" id="origin_mb_4" value="<?php echo get_text($member_business_number); ?>">
	<input type="hidden" name="origin_mb_name" id="origin_mb_name" value="<?php echo get_text($member_company_name); ?>">
	<input type="hidden" name="origin_mb_company" id="origin_mb_company" value="<?php echo get_text($member_company_code); ?>">
	<input type="hidden" name="old_mb_hp" id="old_mb_hp" value="<?php echo get_text($member['mb_8']); ?>">
	<?php if (isset($member['mb_sex'])) {  ?><input type="hidden" name="mb_sex" value="<?php echo $member['mb_sex'] ?>"><?php }  ?>
	<!--
	<?php if (isset($member['mb_nick_date']) && $member['mb_nick_date'] > date("Y-m-d", G5_SERVER_TIME - ($config['cf_nick_modify'] * 86400))) { // 닉네임수정일이 지나지 않았다면  ?>
	<input type="hidden" name="mb_nick_default" value="<?php echo get_text($member['mb_nick']) ?>">
	<input type="hidden" name="mb_nick" value="<?php echo get_text($member['mb_nick']) ?>">
	<?php }  ?>
	-->

<div id="join_step_02">
    <div class="member_sign_layout">
        <h4 class="member_sign_title">회원정보수정</h4>

        <div class="join_agree_body">
            <div class="formArea">
                <div class="formBox password">
                    <div class="inputBox type2">
                        <p class="title">아이디</p>
                        <input type="text" name="mb_id" id="mb_id" class="readonly" readonly value="<?=$member['mb_id']?>">
                    </div>
                    <div class="inputBox type2 password myPage_password">
                        <p class="title">비밀번호</p>
                        <div class="my_password">
                            <input class="password" type="password" name="mb_password" id="mb_password">
                            <div class="input_password_icon"></div>
                            <button type="button" id="change_pwd">변경하기</button>
                        </div>
                    </div>
	                    <div class="inputBox type2">
	                        <p class="title">이름</p>
	                        <input type="text" name="mb_6" id="mb_6" class="readonly" readonly value="<?=$member['mb_6']?>" maxlength="32">
	                    </div>
	                    <div class="inputBox requied type2 member_phone_row">
	                        <p class="title">휴대폰 번호</p>
							<input type="text" name="mb_8" id="mb_8" value="<?=$member['mb_8']?>" class="tel_chk" maxlength="13">
							<button type="button" id="member_phone_send" class="cert_btn">인증</button>
							<div id="member_phone_code_wrap" class="inputBox type2 member_phone_code_row phone_cert_code">
								<p class="title" aria-hidden="true"></p>
								<input type="text" id="member_phone_code" maxlength="6" inputmode="numeric" placeholder="인증번호 6자리">
								<button type="button" id="member_phone_verify" class="cert_btn secondary">확인</button>
							</div>
						<p id="member_phone_status" class="phone_cert_status member_phone_status_row"></p>
	                    </div>
	                    <div class="section_divider"></div>
	                    <div class="inputBox type2">
	                        <p class="title">사업자등록번호</p>
	                        <input type="text" name="mb_4" id="mb_4" value="<?php echo get_text($member_business_number); ?>" class="comnum_chk" maxlength="12">
							<button type="button" class="btn srh">확인</button>
	                    </div>
	                    <div class="inputBox type2">
	                        <p class="title">법인명</p>
	                        <input type="hidden" name="mb_name" id="mb_name_value" value="<?php echo get_text($member_company_name); ?>">
	                        <input type="text" id="mb_name" class="readonly" readonly value="<?php echo get_text($display_company_name); ?>">
							<input type="hidden" name="mb_company" id="mb_company" value="<?php echo get_text($member_company_code); ?>">
	                    </div>
	                    <div class="section_divider"></div>
	                    <div class="formBox terms_box">
	                    	<div class="checkBox">
	                    		<input type="checkbox" name="terms" id="terms" <?php echo $member_terms_agreed ? 'checked' : ''; ?>>
	                    		<span class="consent_text">
	                    			<a href="<?= $link_1201 ?? '/etc/clause.php' ?>" target="_blank" rel="noopener noreferrer">이용약관</a>에 동의합니다. (필수)
	                    		</span>
	                    	</div>
	                    </div>
	                    <div class="formBox terms_box">
	                    	<div class="checkBox">
	                    		<input type="checkbox" name="privacy" id="privacy" <?php echo $member_privacy_agreed ? 'checked' : ''; ?>>
	                    		<span class="consent_text">
	                    			<a href="/etc/privacy_consent.php" target="_blank" rel="noopener noreferrer">개인정보 수집 및 이용동의</a>에 동의합니다. (필수)
	                    		</span>
	                    	</div>
	                    </div>
	                    <div class="formBox terms_box">
	                    	<div class="checkBox">
	                    		<input type="checkbox" name="marketing" id="marketing" <?php echo $member_marketing_agreed ? 'checked' : ''; ?>>
	                    		<span class="consent_text">
	                    			<a href="<?= $link_1205 ?? '/etc/marketing_consent.php' ?>" target="_blank" rel="noopener noreferrer">마케팅 수신 동의</a>에 동의합니다. (선택)
	                    		</span>
	                    	</div>
	                    </div>
	                </div>
	            </div>
	        </div>

        <div class="formBtns">
            <button class="agree">저장하기</button>
            <button  type="button" onclick="javascript:r_cencel();" class="disagree">나가기</button>
            <!-- button, a 둘 다 상관없음 -->
<?php if($w != '') { ?>
<a href="javascript:popFancy('#layer-leave');" class="btn_cancel">회원탈퇴</a>
<!-- <button class="btn_cancel" onclick="member_leave();">회원탈퇴</button> -->
<?php } // tto?>
        </div>

    </div>


</div>
<div id="layer-leave" class="layer-base">
	<p class="title">회원 탈퇴 전 다음 내용을 꼭 확인해주세요</p>
	<div class="layer-in">
		<div class="member-leave">
			<dl>
				<dd>고객정보 및 마이페이지 정보는 개인 정보 보호 처리방침 기준에 따라 삭제됩니다.</dd>
				<dd>회원 탈퇴시 보유 중인 유료 크레딧, 무료/보너스 크레딧, 레거시 구독형 상품 이용 권한이 있다면 즉시 종료되며, 잔여 기간 또는 잔여 수량이 있더라도 더이상 이용할 수 없게 됩니다.</dd>
				<dd>회원 탈퇴시 해당 계정으로 더이상 서비스 이용이 불가능 합니다.</dd>
			</dl>
			<p class="checkIn">
				<input type="checkbox" id="leaveCheck" name="leaveCheck">
				<label for="leaveCheck">안내 사항을 모두 확인하였으며, 이에 동의합니다</label>
			</p>

			<div class="formBtns">
				<button type="button" onclick="javascript:popFancyClose();" class="disagree">취소하기</button>
				<a href="javascript:member_leave();" class="btn_cancel">탈퇴하기</a>
			</div>
		</div>
	</div>
</div>
<script>
function member_leave() {  // 회원 탈퇴 tto
	if (!$("input#leaveCheck").prop("checked")) {
		alert('회원 탈퇴 안내 사항에 동의 해주세요');
		$("input#leaveCheck").siblings("label").focus();
		return false;
	}else{
		location.href = '<?php echo G5_BBS_URL ?>/member_confirm.php?url=member_leave.php';
	}

    //if (confirm("회원에서 탈퇴 하시겠습니까?"))
            //location.href = '<?php echo G5_BBS_URL ?>/member_confirm.php?url=member_leave.php';
 }
//사업자번호
	$('.comnum_chk').on('keyup', function(){
        var num = $(this).val();
        num.trim();  // 스페이스바 제거
        this.value = AutoHypen(num) ;
    });

function AutoHypen(companyNum){
      companyNum = companyNum.replace(/[^0-9]/g, '');
      var tempNum = '';

      if(companyNum.length < 4){
        return companyNum;
      }
      else if(companyNum.length < 6){
        tempNum += companyNum.substr(0,3);
        tempNum += '-';
        tempNum += companyNum.substr(3,2);
        return tempNum;
      }
      else if(companyNum.length < 11){
        tempNum += companyNum.substr(0,3);
        tempNum += '-';
        tempNum += companyNum.substr(3,2);
        tempNum += '-';
        tempNum += companyNum.substr(5);
        return tempNum;
      }
      else{
        tempNum += companyNum.substr(0,3);
        tempNum += '-';
        tempNum += companyNum.substr(3,2);
        tempNum += '-';
        tempNum += companyNum.substr(5);
        return tempNum;
      }
    }
	var originBizNo = $("#origin_mb_4").val().trim();
	var originCompanyName = $("#origin_mb_name").val();
	var originCompanyCode = $("#origin_mb_company").val();

	function resetCompanyFields(currentBizNo) {
		if (currentBizNo === originBizNo) {
			$("#com_chk").val(originBizNo && originCompanyName && originCompanyCode ? "true" : "false");
			$("#mb_name_value").val(originCompanyName);
			$("#mb_name").val(originBizNo ? originCompanyName : "");
			$("#mb_company").val(originCompanyCode);
			return;
		}

		$("#com_chk").val("false");
		$("#mb_name_value").val("");
		$("#mb_name").val("");
		$("#mb_company").val("");
	}

	$("#mb_4").on("input", function() {
		resetCompanyFields($(this).val().trim());
	});

	$(".srh").click(function(){
		var comnum = $("#mb_4").val().trim();

		if(comnum.length < 12) {
			$("#com_chk").val("false");
			jAlert("사업자등록번호를 확인하세요.","알림",function(){
				$("#mb_4").focus();
			});
			return false;
		}

		$.post("../get_set.php", {
			type: "join_comp_chk",
			comnum: comnum
		})
			.done(function(jqXHR) {
			jqXHR = jqXHR.split("|");
			if(jqXHR[0] == 1000){
				$("#com_chk").val("true");
				$("#mb_name_value").val(jqXHR[1]);
				$("#mb_name").val(jqXHR[1]);
				$("#mb_company").val(jqXHR[2]);
			}else if(jqXHR[0] == 501){
				$("#com_chk").val("false");
				jAlert("해당 사업자 번호로는 등록할 수 없습니다.","알림",function(){
					$("#mb_4").focus();
				});
			}else{
				$("#com_chk").val("false");
				jAlert("처리에 오류가 있습니다.","알림");
			}
		})
		.fail(function() {
			$("#com_chk").val("false");
			jAlert("처리에 오류가 있습니다.","알림");
		});
	});
//전화번호 자동입력
$(".tel_chk").keyup(function(){
	phone=$(this).val();
	phone=phone.replace(/[^0-9]/g, '').replace(/^(\d{2,3})(\d{3,4})(\d{4})$/, `$1-$2-$3`);
	$(this).val(phone);
})

function normalizeMemberPhone(phone) {
	return String(phone || '').replace(/\D/g, '').substring(0, 11).replace(/^(\d{2,3})(\d{3,4})(\d{4})$/, '$1-$2-$3');
}

var memberPhoneVerified = false;
var memberPhoneVerifiedValue = "";

function setMemberPhoneStatus(message, type) {
	var $status = $("#member_phone_status");
	$status.removeClass("success warning error");
	if (type) $status.addClass(type);
	$status.text(message || "");
}

function resetMemberPhoneVerification() {
	memberPhoneVerified = false;
	memberPhoneVerifiedValue = "";
	$("#member_phone_code").val("");
	$("#member_phone_code_wrap").hide();
	setMemberPhoneStatus("", "");
}

function isMemberPhoneChanged() {
	return normalizeMemberPhone($("#mb_8").val()) !== normalizeMemberPhone($("#old_mb_hp").val());
}

$("#mb_8").on("input blur change", function() {
	$(this).val(normalizeMemberPhone($(this).val()));
	if (memberPhoneVerifiedValue !== $.trim($(this).val())) {
		resetMemberPhoneVerification();
	}
	if (isMemberPhoneChanged()) {
		setMemberPhoneStatus("휴대폰 번호를 변경한 경우 인증이 필요합니다.", "warning");
	} else {
		setMemberPhoneStatus("", "");
	}
});

$("#member_phone_send").on("click", function() {
	var phone = $.trim($("#mb_8").val());
	if (!phone) {
		jAlert("휴대폰 번호를 확인해 주세요.","알림",function(){$("#mb_8").focus();});
		return false;
	}

	var $button = $(this);
	resetMemberPhoneVerification();
	$button.prop("disabled", true).text("발송중");

	$.post("<?php echo G5_BBS_URL; ?>/ajax.signup_phone_send.php", { mb_hp: phone })
		.done(function(resp) {
			if (typeof resp === "string") {
				try { resp = JSON.parse(resp); } catch(e) { resp = {}; }
			}
			if (resp.ok) {
				$("#member_phone_code_wrap").css("display", window.matchMedia("(max-width: 640px)").matches ? "block" : "flex");
				setMemberPhoneStatus(resp.message || "인증번호 발송 요청을 처리했습니다.", "success");
				$("#member_phone_code").focus();
			} else {
				setMemberPhoneStatus(resp.message || "인증번호 발송에 실패했습니다.", "error");
			}
		})
		.fail(function() {
			setMemberPhoneStatus("인증번호 발송 중 오류가 발생했습니다.", "error");
		})
		.always(function() {
			$button.prop("disabled", false).text("인증");
		});
});

$("#member_phone_verify").on("click", function() {
	var phone = $.trim($("#mb_8").val());
	var code = $.trim($("#member_phone_code").val()).replace(/[^0-9]/g, "");
	if (code.length !== 6) {
		setMemberPhoneStatus("인증번호 6자리를 입력해 주세요.", "error");
		$("#member_phone_code").focus();
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
				memberPhoneVerified = true;
				memberPhoneVerifiedValue = phone;
				setMemberPhoneStatus(resp.message || "휴대폰 인증이 완료되었습니다.", "success");
			} else {
				memberPhoneVerified = false;
				setMemberPhoneStatus(resp.message || "인증번호를 확인해 주세요.", "error");
			}
		})
		.fail(function() {
			memberPhoneVerified = false;
			setMemberPhoneStatus("인증 확인 중 오류가 발생했습니다.", "error");
		})
		.always(function() {
			$button.prop("disabled", false).text("확인");
		});
});

$("#member_phone_code").on("input", function() {
	$(this).val($(this).val().replace(/[^0-9]/g, "").substring(0, 6));
});

		//비밀번호 변경
		function chkPW(word){

	 var pw = word;
	 var num = pw.search(/[0-9]/g);
	 var eng = pw.search(/[a-z]/ig);
	 var spe = pw.search(/[`~!@@#$%^&*|₩₩₩'₩";:₩/?]/gi);

	 if(pw.length < 8 || pw.length > 20){

	  jAlert("8자리 ~ 20자리 이내로 입력해주세요.","알림",function(){$("#mb_password").focus()});
	  return false;
	 }else if(pw.search(/\s/) != -1){
	  jAlert("비밀번호는 공백 없이 입력해주세요.","알림",function(){$("#mb_password").focus()});
	  return false;
	 }else if(num < 0 || eng < 0 || spe < 0 ){
	  jAlert("영문,숫자, 특수문자를 혼합하여 입력해주세요.","알림",function(){$("#mb_password").focus()});
	  return false;
	 }else {
		return true;
	 }

	}
	$("#change_pwd").click(function(){
		var pwd=$(this).prev().prev().val();
		if(pwd){
			chk=chkPW(pwd);
			if(chk){
				$.post( "../get_set.php", {
					type: 'change_pwd',
					pwd:pwd
				})
				.done(function(jqXHR) {
						jqXHR=jqXHR.split("|");
						if(jqXHR[0]==1000){
							jAlert("성공적으로 변경되었습니다.","알림");
						}else{
							//오류시
							jAlert("처리에 문제가 있습니다.","알림");
						}
				});
			}
		}else{
			jAlert("변경하실 비밀번호를 입력하세요.","알림",function(){$("#mb_password").focus()})
		}
		//8~16자 영문 대소문자, 숫자 조합해 주세요.
	})
	//취소
	function r_cencel() {
		jCustomConfirm("나가기를 하면 작성한 내용이 모두 삭제됩니다.\n정말 나가시겠습니까?","알림","확인","취소",function(a){
			if(a) location.href='/';
		})
	}
    const inputPasswordBoxes = document.querySelectorAll(".formBox.password");

    inputPasswordBoxes.forEach((inputPassword) => {
        const inputPasswordInput = inputPassword.querySelector("input.password");
        const inputPasswordIcon = inputPassword.querySelector(".input_password_icon");

        // 각 아이콘에 클릭 이벤트 리스너 추가
        inputPasswordIcon.addEventListener("click", () => {
            // 아이콘을 클릭할 때 클래스 토글 (아이콘 변경 효과)
            inputPasswordIcon.classList.toggle("view");

            // 현재 input의 타입이 password이면 text로, 아니면 다시 password로 변경
            if (inputPasswordInput.type === "password") {
                inputPasswordInput.type = "text";
            } else {
                inputPasswordInput.type = "password";
            }
        });
    });

function fregisterform_submit()
{
	var currentBizNo = $("#mb_4").val().trim();

	if(currentBizNo) {
		if($("#com_chk").val() != "true") {
			jAlert("사업자등록번호를 먼저 확인해 주세요.","알림",function(){$("#mb_4").focus();});
			return false;
		}
		if(!$("#mb_name_value").val().trim()) {
			jAlert("법인명을 확인해 주세요.","알림",function(){$("#mb_4").focus();});
			return false;
		}
	}

		if(!$("#mb_6").val().trim()) {
			jAlert("이름을 확인해 주세요.","알림",function(){$("#mb_6").focus();});
			return false;
	}
		if(!$("#mb_8").val().trim()) {
			jAlert("휴대폰 번호를 확인해 주세요.","알림",function(){$("#mb_8").focus();});
			return false;
		}
		if(!$("#terms").is(":checked")) {
			jAlert("이용약관에 동의해 주세요.","알림",function(){$("#terms").focus();});
			return false;
		}
		if(!$("#privacy").is(":checked")) {
			jAlert("개인정보 수집 및 이용동의에 동의해 주세요.","알림",function(){$("#privacy").focus();});
			return false;
		}
		if(isMemberPhoneChanged() && (!memberPhoneVerified || memberPhoneVerifiedValue !== $.trim($("#mb_8").val()))) {
			jAlert("변경한 휴대폰 번호의 인증을 완료해 주세요.","알림",function(){$("#mb_8").focus();});
			return false;
		}
	if($("#coupon").val().length>0 && $("#coupon").val().length<6) {
		jAlert("쿠폰번호를 확인해 주세요.","알림");
		return false;
	}

}
</script>



<!-- } 회원정보 입력/수정 끝 -->
