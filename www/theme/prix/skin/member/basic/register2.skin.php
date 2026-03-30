<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>


<style>
    #sVisual,
    #pageTop{display: none}
</style>

<div id="join_step_01">
    <div class="member_sign_layout">
        <h4 class="member_sign_title">회원가입</h4>

        <ul class="join_step step2">
        <!-- step1, 2, 3 마다 하단 그래픽이 달라짐 -->
            <li>
                <span class="circle"></span>
                <span class="text">계정정보</span>
            </li>
			<li>
                <span class="circle"></span>
                <span class="text">약관동의</span>
            </li>
            <li>
                <span class="circle"></span>
                <span class="text">기업정보</span>
            </li>
        </ul>

        <div class="join_agree_head">
            <h4>회원약관동의</h4>
            <p>회원 가입을 위해서 아래의 서비스 이용약관, 개인정보 수집 및 이용에 대한 안내를 읽고 동의해주세요.</p>
        </div>

        <div class="join_agree_body">
            <div class="formArea">
                <div class="formBox">
                    <p class="formBox_title">이용약관(필수)</p>
                        <div class="clauseArea">
                            <div class="scroll">
                                <? include_once(G5_THEME_PATH . '/clause.php'); ?>
                            </div>
                        </div> 
                    <div class="checkBox">
                        <input type="checkbox" name="terms" id="terms" class="req">
                        <label for="">이용약관에 동의합니다.</label>
                    </div>
                </div>
                <div class="formBox">
                    <p class="formBox_title">개인정보 수집 및 이용동의(필수)</p>
                    <div class="clauseArea">
                        <div class="scroll">
                            <? include_once(G5_THEME_PATH . '/privacy.php'); ?>
                        </div>
                    </div>     
                    <div class="checkBox">
                        <input type="checkbox" name="privacy" id="privacy" class="req">
                        <label for="">개인정보수집에 동의합니다.</label>
                    </div>            
                </div>
                <div class="formBox">
                    <div class="checkBox all">
                        <input type="checkbox" name="agreeAll" id="agreeAll">
                        <label for="">모든 약관에 동의합니다.</label>
                    </div>        
                </div>
            </div>
        </div>

        <div class="formBtns">
			<form action="register.php" method="post" id="step_form">
				<input type="hidden" name="url" value="<?=$url?>">
				<input type="hidden" name="step" value="3">
				<input type="hidden" name="id" value="<?=$id?>">
				<input type="hidden" name="password" value="<?=$password?>">
				<input type="hidden" name="agree_chk" value="">
				<input type="hidden" name="provider" value="<?php echo $provider; ?>">
				<input type="hidden" name="cert_type" value="<?php echo $cert_type; ?>">
				<input type="hidden" name="cert_no" value="<?php echo $cert_no; ?>">
				<input type="hidden" name="mb_id" value="<?php echo $mb_id; ?>" id="reg_mb_id">
			</form>
            <button type="button" class="agree">동의합니다</button>
            <button type="button" class="disagree">동의하지 않습니다</button>
            <!-- button, a 둘 다 상관없음 -->
        </div>

    </div>

</div>

<script>
//모두동의
$("#agreeAll").click(function(){
	chk=$(this).is(":checked");

	if(chk) $("input.req").prop("checked",true);
	else $("input.req").prop("checked",false);
})

$(".formBtns button.agree").click(function(){
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

	$("#step_form").submit();
})
$(".formBtns button.disagree").click(function(){
        var result = confirm('동의하지 않으면 회원가입이 불가능합니다.\n확인을 클릭하시면 메인페이지로 이동합니다.');

        if(result) {
            location.replace('/');
        } else {
            //no
        }
})
</script>