<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>
<link href="<?=$member_skin_url?>/newstyle.css" rel="stylesheet">

<style>/*
    #sVisual,
    #pageTop{display: none}*/
</style>

<form action="register_t.php" method="post">
	<input type="hidden" name="step" value="2">
	<div class="skin-member form">
		<dl class="h1">
			<dt>회원가입</dt>
		</dl>

		<ul class="skin-form">
			<li class="formBox number">
				<div class="inputBox juso">
					<input type="hidden" name="num_chk" value="false" id="num_chk">
					<input type="text" name="number" id="number" class="number" maxlength="32" placeholder="사업자등록번호">
					<a href="javascript:void(0);" class="btns">중복확인</a>
				</div>
				<p class="formBox_errorM">사업자등록번호를 입력해주세요</p>
			</li>
			<li class="formBox">
				<div class="inputBox">
					<input type="text" name="c_name" id="c_name" class="c_name" disabled value="이가이엔씨" placeholder="상호명" >
				</div>
			</li>
			<li class="formBox email">
				<div class="inputBox juso">
					<input type="hidden" name="id_chk" value="false" id="id_chk">
					<input type="text" name="id" id="id" class="normal" maxlength="32" placeholder="이메일">
					<select name="">
						<option value="">직접입력</option>
						<option value="">naver.com</option>
						<option value="">gmail.com</option>
						<option value="">hanmail.com</option>
						<option value="">daum.net</option>
					</select>
				</div>
				<p class="formBox_errorM">이메일 주소를 입력해주세요</p>
			</li>
			<li class="formBox password">
				<div class="inputBox">
					<input type="hidden" name="password_chk" value="false" id="password_chk" placeholder="비밀번호">
					<input type="password" name="password" id="password"  class="password pass_chk" maxlength="16">
					<div class="input_password_icon"></div>
				</div>
				<p class="formBox_errorM">8~16자 영문/숫자/특수문자 조합해 주세요.</p>
			</li>
			<li class="formBox password">
				<div class="inputBox">
					<input type="hidden" name="passwordre_chk" value="false" id="passwordre_chk" placeholder="비밀번호 확인">
					<input type="password" name="password_re" id="password_re" class="password" maxlength="16">
					<div class="input_password_icon"></div>
				</div>
				<p class="formBox_errorM"></p>
			</li>
		</ul>

		<ul class="terms-new">
			<li class="title">
				<p class="skin-check-new"><input type="checkbox" name="agreeAll" id="agreeAll"><label for="agreeAll"><span class="graphic"></span>전체동의</label></p>
			</li>
			<li>
				<p class="skin-check-new"><input type="checkbox" name="terms-check" id="terms" class="req"><label for="terms"><span class="graphic"></span>서비스 이용 약관에 동의(필수)</label></p>
				<a href="javascript:popFancy('#layer-term');" class="more">내용보기</a>
			</li>
			<li>
				<p class="skin-check-new"><input type="checkbox" name="terms-check" id="privacy" class="req"><label for="privacy"><span class="graphic"></span>개인정보 수집 및 이용에 동의(필수)</label></p>
				<a href="javascript:popFancy('#layer-privacy');" class="more">내용보기</a>
			</li>
			<li>
				<p class="skin-check-new"><input type="checkbox" name="terms-check" id="promotions" class="req"><label for="promotions"><span class="graphic"></span>프로모션 정보 수신 동의(선택)</label></p>
				<a href="javascript:popFancy('#layer-promotions');" class="more">내용보기</a>
			</li>
		</ul>

		<div class="skin-button">
			<button type="submit" id="confirm" accesskey="s">이메일 회원가입</button>
		</div>
	</div>
</form>

<div id="layer-term" class="layer-base layer-term"><div class="layer-in">
	<div class="titles">서비스 이용 약관</div>
	<div class="clauseArea">
		<div class="scroll">
			<? include_once(G5_THEME_PATH . '/clause.php'); ?>
		</div>
	</div>
</div></div>
<div id="layer-privacy" class="layer-base layer-term"><div class="layer-in">
	<div class="titles">개인정보 수집 및 이용</div>
	<div class="clauseArea">
		<div class="scroll">
			<? include_once(G5_THEME_PATH . '/privacy.php'); ?>
		</div>
	</div>
</div></div>
<div id="layer-promotions" class="layer-base layer-term"><div class="layer-in">
	<div class="titles">프로모션 정보 수신</div>
	<div class="clauseArea">
		<div class="scroll">
			프로모션 정보 수신 내용<br />
			프로모션 정보 수신 내용<br />
			프로모션 정보 수신 내용<br />
			프로모션 정보 수신 내용<br />
			프로모션 정보 수신 내용<br />
		</div>
	</div>
</div></div>

<script>
//모두동의
/*
$("#agreeAll").click(function(){
	chk=$(this).is(":checked");

	if(chk) $("input.req").prop("checked",true);
	else $("input.req").prop("checked",false);
})
*/
$("body").on("click", "input[name=terms-check]", function(){
	var check_length = $("input[name=terms-check]").length;
	var check_on_length = $("input[name=terms-check]:checked").length;
	if(check_length == check_on_length){
		$("input[name=agreeAll]").prop("checked", true);
	}else{
		$("input[name=agreeAll]").prop("checked", false);
	}
});
// 체크박스 전체
$("body").on("click", "input[name=agreeAll]", function(){
	if(!$(this).prop("checked")){
		$("input[name=terms-check]").prop("checked", false);
	}else{
		$("input[name=terms-check]").prop("checked", true);
	}
});

$("#id").keyup(function(){
	$this=$(this);
	  var email = $(this).val();

	  let pattern_email = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9-]+\.[a-zA-Z]{2,4}$/;
	  	  pattern_email = new RegExp(pattern_email);
	  if (pattern_email.test(email) == false) {
		  if(email) $this.parents(".formBox").find(".formBox_errorM").text("올바른 이메일 주소가 아닙니다.");
		  else $this.parents(".formBox").find(".formBox_errorM").text("이메일 주소를 입력해주세요.");
		  $("#id_chk").val("false");
		return false;
	  }else{
		  $this.parents(".formBox").find(".formBox_errorM").text("");

			$.post( "../get_set.php", {
				type: 'member_check',
				id: email
			}).done(function(jqXHR) {
				if(jqXHR=="500") {
					$this.parents(".formBox").find(".formBox_errorM").text("가입한 이력이 있습니다.");
					$("#id_chk").val("false");
				}else{
					$("#id_chk").val("true");
				}
			});
	  }
  })

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
	//이용약관
	if(!$("#terms").is(":checked")) {
		alert("이용약관에 동의해 주세요.");
		return false;
	}else{
		$("input[name='agree_chk']").val("#terms");
	}

	//개인정보수집
	if(!$("#privacy").is(":checked")) {
		alert("개인정보수집에 동의해 주세요.");
		return false;
	}else{
		$("input[name='agree_chk']").val($("input[name='agree_chk']").val()+"#privacy");
	}

	/* 프로모션
	if(!$("#privacy").is(":checked")) {
		alert("프로모션 정보 수신에 동의해 주세요.");
		return false;
	}else{
		$("input[name='agree_chk']").val($("input[name='agree_chk']").val()+"#privacy");
	}*/

	if($("#id_chk").val()=="false") {
		alert("이메일을 확인해 주세요.");
		return false;
	}

	id=$("#id").val();
	$.post( "../get_set.php", {
		type: 'member_check',
		id: id
	}).done(function(jqXHR) {
		if(jqXHR=="500") {
			jAlert("가입한 이력이 있습니다.","알림");
		}
	});

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