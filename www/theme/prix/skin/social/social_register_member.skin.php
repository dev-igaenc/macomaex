<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/remodal/remodal.css">', 11);
add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/remodal/remodal-default-theme.css">', 12);

add_javascript('<script src="'.G5_JS_URL.'/remodal/remodal.js"></script>', 10);
add_javascript('<script src="'.G5_JS_URL.'/jquery.register_form.js"></script>', 14);
if ($config['cf_cert_use'] && ($config['cf_cert_simple'] || $config['cf_cert_ipin'] || $config['cf_cert_hp']))
    add_javascript('<script src="'.G5_JS_URL.'/certify.js?v='.G5_JS_VER.'"></script>', 15);

$email_msg = $is_exists_email ? '등록할 이메일이 중복되었습니다.다른 이메일을 입력해 주세요.' : ''; 

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);

if($user_email) $mem_chk=sql_fetch("select * from g5_member where mb_id='".$user_email."'");
?>

<style>
    #sVisual,
    #pageTop{display: none}
</style>
<?if($user_email && !$mem_chk['mb_id']){?>
		<form action="/bbs/register.php" method="post" id="form1">
		<input type="hidden" name="step" value="2">
        <!-- 새로가입 시작 -->
        <input type="hidden" name="w" value="<?php echo $w; ?>">
        <input type="hidden" name="url" value="<?php echo $urlencode; ?>">
        <input type="hidden" name="provider" value="<?php echo $provider_name; ?>">
        <input type="hidden" name="action" value="register">
        <input type="hidden" name="cert_type" value="<?php echo $member['mb_certify']; ?>">
        <input type="hidden" name="cert_no" value="">
        <input type="hidden" name="mb_id" value="<?php echo $user_id; ?>" id="reg_mb_id">
		<input type="hidden" name="id" id="id" value="<?=$user_email?>" class="normal" maxlength="32">
		</form>
		<script>$("#form1").submit();</script>
<?}else{?>
<div id="join">
    <div class="member_sign_layout">
		<form action="/bbs/register.php" method="post" id="form1">
		<input type="hidden" name="step" value="2">
        <!-- 새로가입 시작 -->
        <input type="hidden" name="w" value="<?php echo $w; ?>">
        <input type="hidden" name="url" value="<?php echo $urlencode; ?>">
        <input type="hidden" name="provider" value="<?php echo $provider_name; ?>">
        <input type="hidden" name="action" value="register">
        <input type="hidden" name="cert_type" value="<?php echo $member['mb_certify']; ?>">
        <input type="hidden" name="cert_no" value="">
        <input type="hidden" name="mb_id" value="<?php echo $user_id; ?>" id="reg_mb_id">
        <?php if ($config["cf_cert_use"]) { ?>
            <input type="hidden" id="reg_mb_name" name="mb_name" value="<?php echo $user_name ? $user_name : $user_nick ?>">
        <?php } ?>
        <?php if ($config['cf_use_hp'] || ($config["cf_cert_use"] && ($config['cf_cert_hp'] || $config['cf_cert_simple']))) {  ?>
            <input type="hidden" name="mb_hp" value="<?php echo get_text($user_phone); ?>" id="reg_mb_hp">
            <?php if ($config['cf_cert_use'] && ($config['cf_cert_hp'] || $config['cf_cert_simple'])) { ?>
                <input type="hidden" name="old_mb_hp" value="<?php echo get_text($user_phone); ?>">
            <?php } ?>
        <?php }  ?>

        <h4 class="member_sign_title">회원가입</h4>
        <div class="formArea">
            <div class="formBox email">
                <p class="formBox_title">이메일</p>
                <div class="inputBox">
					<input type="hidden" name="id_chk" value="false" id="id_chk">
                    <input type="text" name="id" id="id" value="<?=$user_email?>" class="normal" maxlength="32">
                </div>
                <p class="formBox_errorM">이메일 주소를 입력해주세요</p>
            </div>
        </div>
		<?if($mem_chk['mb_id'] && ($mem_chk['mb_leave_date'] || $mem_chk['mb_intercept_date'])){?>
        <div class="formBtns">
            <button type="button" id="confirm">회원가입</button>
        </div>
		<?}else{?>
		<div class="member_connect">
			<p class="strong">혹시 기존 회원이신가요?</p>
			<button type="button" class="connect-opener btn-txt" data-remodal-target="modal">
				기존 계정에 연결하기
				<i class="fa fa-angle-double-right"></i>
			</button>
		</div>
		<?}?>
		</form>



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

            <div class="connect-desc">
                기존 아이디에 SNS 아이디를 연결합니다.<br>
                이 후 SNS 아이디로 로그인 하시면 기존 아이디로 로그인 할 수 있습니다.
            </div>

            <div id="login_fs">
                <label for="login_id" class="login_id">아이디 (필수)</label>
                <span class="lg_id"><input type="text" name="mb_id" id="login_id" value="<?=$user_email?>" class="frm_input required" size="20" maxLength="20"></span>
                <label for="login_pw" class="login_pw">비밀번호 (필수)</label>
                <span class="lg_pw"><input type="password" name="mb_password" id="login_pw" class="frm_input required" size="20" maxLength="20"></span>
                <br>
                <input type="submit" value="연결하기" class="login_submit btn_submit">
            </div>

        </form>
    </div>
</div>

</div>
<?}?>
<script>
$("#id").keyup(function(){
	$this=$(this);
	  var email = $(this).val();
	  
	  let pattern_email = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
	  	  pattern_email = new RegExp(pattern_email);
	  if (pattern_email.test(email) == false) {
		  if(email) $this.parents(".formBox").find(".formBox_errorM").text("올바른 이메일 주소가 아닙니다.");
		  else $this.parents(".formBox").find(".formBox_errorM").text("이메일 주소를 입력해주세요.");
		  $("#id_chk").val("false");
		return false;
	  }else{
		  $this.parents(".formBox").find(".formBox_errorM").text("");

			$.post( "../../get_set.php", {
				type: 'member_check',
				id: email
			}).done(function(jqXHR) {
				jqXHR=jqXHR.split("|");
				if(jqXHR[0]!="1000") {
					$this.parents(".formBox").find(".formBox_errorM").text(jqXHR[1]);
					$("#id_chk").val("false");
				}else{
					$("#id_chk").val("true");
				}
			});
	  }
  })
  <?if($user_email){?>
  $("#id").keyup();
	<?}?>
function validateString(event){
	const pattern = /^(?=.*[a-zA-Z])(?=.*\d)(?=.*[~@#$!%*?&^()])[a-zA-Z\d~@#$!%*?&^()]{8,14}$/          
	//영문 + 숫자 + 특수문자 + 8자리 이상 정규식
	return !/[ㄱ-ㅎㅏ-ㅣ가-힣]/.test(event) && pattern.test(event);                           
	//한글 입력 시 false 리턴
}
var errorM="first";

/*
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
*/
$("#confirm").click(function(){
	if($("#id_chk").val()=="false") {
		alert("이메일을 확인해 주세요.");
		return false;
	}

	id=$("#id").val();
	$.post( "../get_set.php", {
		type: 'member_check',
		id: id
	}).done(function(jqXHR) {
		jqXHR=jqXHR.split("|");
		if(jqXHR[0]!="1000") {
			jAlert(jqXHR[1],"알림");
			return false;
		}
	});
/*
	if($("#password_chk").val()=="false") {
		alert("비밀번호를 확인해 주세요.");
		return false;
	}
	if($("#passwordre_chk").val()=="false") {
		alert("비밀번호 확인을 진행해 주세요.");
		return false;
	}
*/
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
