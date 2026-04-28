<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>

<style>
    #sVisual,
    #pageTop{display: none}
</style>

<div id="join">
    <div class="member_sign_layout">
		<form action="register.php" method="post">
		<input type="hidden" name="url" value="<?=$url?>">
		<input type="hidden" name="step" value="2">
        <h4 class="member_sign_title">회원가입</h4>
        <div class="formArea">
            <div class="formBox email">
                <p class="formBox_title">이메일</p>
                <div class="inputBox">
					<input type="hidden" name="id_chk" value="false" id="id_chk">
                    <input type="text" name="id" id="id" class="normal" maxlength="32">
                </div>
                <p class="formBox_errorM">이메일 주소를 입력해주세요</p>
            </div>
            <div class="formBox password">
                <p class="formBox_title">비밀번호</p>
                <div class="inputBox">
					<input type="hidden" name="password_chk" value="false" id="password_chk">
                    <input type="password" name="password" id="password"  class="password pass_chk" maxlength="16">
                    <div class="input_password_icon"></div>
                </div>
                <p class="formBox_errorM">8~16자 영문/숫자/특수문자 조합해 주세요.</p>
            </div>
            <div class="formBox password">
                <p class="formBox_title">비밀번호 확인</p>
                <div class="inputBox">
					<input type="hidden" name="passwordre_chk" value="false" id="passwordre_chk">
                    <input type="password" name="password_re" id="password_re" class="password" maxlength="16">
                    <div class="input_password_icon"></div>
                </div>
                <p class="formBox_errorM"></p>
            </div>
        </div>

        <div class="formBtns">
            <button type="button" id="confirm">이메일 회원가입</button>
        </div>
		</form>
    </div>
</div>

<script>
function validateSignupEmailAndMark() {
	var $input = $("#id");
	var email = $.trim($input.val());
	var patternEmail = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

	if (!patternEmail.test(email)) {
		if (email) $input.parents(".formBox").find(".formBox_errorM").text("올바른 이메일 주소가 아닙니다.");
		else $input.parents(".formBox").find(".formBox_errorM").text("이메일 주소를 입력해주세요.");
		$("#id_chk").val("false");
		return;
	}

	$input.parents(".formBox").find(".formBox_errorM").text("");
	$.post("../get_set.php", {
		type: "member_check",
		id: email
	}).done(function(resp) {
		var parsed = $.trim(resp).split("|");
		if (parsed[0] != "1000") {
			$input.parents(".formBox").find(".formBox_errorM").text(parsed[1] || "가입한 이력이 있습니다.");
			$("#id_chk").val("false");
		} else {
			$("#id_chk").val("true");
		}
	}).fail(function() {
		// 네트워크 오류 시 오탐 방지: 제출 시점에 다시 한 번 확인한다.
		$("#id_chk").val("true");
	});
}

$("#id").on("input blur change", function(){
	validateSignupEmailAndMark();
});

function validateString(event){
	const pattern = /^(?=.*[a-zA-Z])(?=.*\d)(?=.*[~@#$!%*?&^()])[a-zA-Z\d~@#$!%*?&^()]{8,14}$/          
	//영문 + 숫자 + 특수문자 + 8자리 이상 정규식
	return !/[ㄱ-ㅎㅏ-ㅣ가-힣]/.test(event) && pattern.test(event);                           
	//한글 입력 시 false 리턴
}
var errorM="first";


$(".pass_chk").keyup(function(){
	$this=$(this);
	if(errorM=="first") errorM=$this.parents(".formBox").find(".formBox_errorM").text();
	if($this.val().length==0) {
		$this.parents(".formBox").find(".formBox_errorM").text(errorM);
		return false;
	}
	if(validateString($(this).val())) {
		$this.parents(".formBox").find(".formBox_errorM").text("").hide();
		$("#password_chk").val("true");
	}else{
		$this.parents(".formBox").find(".formBox_errorM").text("8~16자 영문/숫자/특수문자 조합해 주세요").show();
		$("#password_chk").val("false");
	}
})

$("#password_re").keyup(function(){
	$this=$(this);
	password=$("#password").val();
	password_re=$this.val();

	if(password == password_re) {
		$this.parents(".formBox").find(".formBox_errorM").text("");
		$("#passwordre_chk").val("true");
	}else{
		$this.parents(".formBox").find(".formBox_errorM").text("비밀번호가 일치하지 않습니다.").show();
		$("#passwordre_chk").val("false");
	}
})

$("#confirm").click(function(){
	var email = $.trim($("#id").val());
	let checkEmail = RegExp(/^([a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})$/);
	if(!checkEmail.test(email)) {
		alert("이메일을 확인해 주세요.");
		$("#id").focus();
		return false;
	}

	// keyup 미발생(붙여넣기/자동완성) 대비: 제출 시점에 1회 동기 검증
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

	if($("#password_chk").val()=="false") {
		alert("비밀번호를 확인해 주세요.");
		return false;
	}
	if($("#passwordre_chk").val()=="false") {
		alert("비밀번호 확인을 진행해 주세요.");
		return false;
	}

	$(this).parents("form").submit();
})

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
</script>
