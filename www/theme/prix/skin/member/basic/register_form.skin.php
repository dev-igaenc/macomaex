<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
$board['bo_10']="6"; // 1뎁스
$board['bo_9']="3"; // 2뎁스

include_once(G5_THEME_PATH.'/head.php');
?>

<!-- 회원정보 입력/수정 시작 { -->
<script src="<?php echo G5_JS_URL ?>/jquery.register_form.js"></script>
<?php if($config['cf_cert_use'] && ($config['cf_cert_ipin'] || $config['cf_cert_hp'])) { ?>
<script src="<?php echo G5_JS_URL ?>/certify.js?v=<?php echo G5_JS_VER; ?>"></script>
<?php } ?>

<form id="fregisterform" name="fregisterform" action="<?php echo $register_action_url ?>" onsubmit="return fregisterform_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
	<input type="hidden" name="w" value="<?php echo $w ?>">
	<input type="hidden" name="url" value="<?php echo $urlencode ?>">
	<input type="hidden" name="agree" value="<?php echo $agree ?>">
	<input type="hidden" name="agree2" value="<?php echo $agree2 ?>">
	<input type="hidden" name="cert_type" value="<?php echo $member['mb_certify']; ?>">
	<input type="hidden" name="cert_no" value="">
	<?if($w=='u'){?>
	<input type="hidden" name="mb_nick" value="<?=$member['name']?>">
	<?}?>
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
                    <div class="inputBox type2">
                        <p class="title">법인명</p>
                        <input type="text" name="mb_name" id="mb_name" class="readonly" readonly value="<?=$member['mb_name']?>">
						<input type="hidden" name="mb_company" id="mb_company" class="readonly" readonly value="<?=$member['mb_company']?>">
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
                        <p class="title">담당자 이메일</p>
                        <input class="" name="mb_5" type="text" value="<?php echo $member['mb_5'] ?>">
                    </div>
					<div class="inputBox requied type2">
                        <p class="title">회사 유형</p>
                        <ul class="radioBox">
                            <li>
                                <input type="radio" name="mb_3" value="제조사"  id="type1">
                                <label for="type1">제조사</label>
                            </li>
                            <li>
                                <input type="radio" name="mb_3" value="유통사"  id="type2">
                                <label for="type2">유통사</label>
                            </li>
                            <li>
                                <input type="radio" name="mb_3" value="기타" id="type3">
                                <label for="type3">기타</label>
                            </li>
							<?if($member['mb_3']){?>
							<script>$("input[name='mb_3'][value='<?=$member['mb_3']?>']").prop("checked",true);</script>
							<?}?>
                        </ul>
                    </div>
                    <div class="inputBox requied type2">
                        <p class="title">회원사 가입유무</p>
						<span class="checkIn">
							<input type="checkbox" id="mb_10_1" name="mb_10[]"  class="selec_chk" value="벤처기업" ><label for="mb_10_1">벤처기업</label>
							<input type="checkbox" id="mb_10_2" name="mb_10[]"  class="selec_chk" value="메인비즈" ><label for="mb_10_2">메인비즈</label><br>
							<input type="checkbox" id="mb_10_3" name="mb_10[]"  class="selec_chk" value="이노비즈" ><label for="mb_10_3">이노비즈</label>
							<input type="checkbox" id="mb_10_4" name="mb_10[]"  class="selec_chk" value="해당사항없음" ><label for="mb_10_4">해당사항없음</label>
						</span>
							<?if($member['mb_10']){?>
							<script>
								var mb10="<?=$member['mb_10']?>";
								mb10=mb10.split(":|:");
								$.each(mb10, function(index, item){
									$("input[name='mb_10[]'][value='"+item+"'").prop("checked",true);
								})
							</script>
							<?}?>
                    </div>
                    <div class="inputBox requied type2">
                        <p class="title">사업자등록번호</p>
                        <input type="text" name="mb_4" id="mb_4" value="<?=$member['mb_4']?>" class="readonly comnum_chk" maxlength="12" readonly>
                    </div>
                    <div class="inputBox requied type2">
                        <p class="title">담당자 이름</p>
                        <input type="text" name="mb_6" id="mb_6" value="<?=$member['mb_6']?>" maxlength="32">
                    </div>
                    <div class="inputBox requied type2">
                        <p class="title">담당자 직위</p>
                        <input type="text" name="mb_7" id="mb_7" value="<?=$member['mb_7']?>" maxlength="32">
                    </div>
                    <div class="inputBox requied type2">
                        <p class="title">휴대폰 번호</p>
                        <input type="text" name="mb_8" id="mb_8" value="<?=$member['mb_8']?>" class="tel_chk" maxlength="13">
                    </div>
                    <div class="inputBox type2 type3 majors">
                        <p class="title">주요 서비스/제품</p>
						<?$mb_9=explode(":|:",$member['mb_9']);?>
						<?foreach($mb_9 as $value){?>
                        <div class="products">
                            <input type="text" name="mb_9[]" id="mb_9" value="<?=$value?>" maxlength="32">
                            <a href="javascript:;" class="minus">-</a>
                        </div>
						<?}?>
                        <div class="products">
                            <input type="text" name="mb_9[]" id="mb_9" value="" maxlength="32">
                            <a href="javascript:;" class="add">+</a>
                            <a href="javascript:;" class="minus">-</a>
                        </div>
                        <!-- 구조 개선을 원하시면 말씀해주세요 -->
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
//전화번호 자동입력
$(".tel_chk").keyup(function(){
	phone=$(this).val();
	phone=phone.replace(/[^0-9]/g, '').replace(/^(\d{2,3})(\d{3,4})(\d{4})$/, `$1-$2-$3`);
	$(this).val(phone);
})

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
	//서비스 추가
	$add='<div class="products">'+$(".majors .products:last").html()+"</div>";
	$("body").on("click",".products .add",function(){
		$(".majors").append($add);
	})
	//서비스 삭제
	$("body").on("click",".products .minus",function(){
		$(this).parents(".products").remove();
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

$("input[name='mb_10[]']").click(function(){
	$this=$(this);
	if($this.is(":checked")){
		if($this.val()=="해당사항없음") $("input[name='mb_10[]'][value!='해당사항없음']").prop("checked",false);
		else $("input[name='mb_10[]'][value='해당사항없음']").prop("checked",false);
	}
})

function fregisterform_submit()
{


	if(!$("#mb_name").val().trim()) {
		jAlert("법인명을 확인해 주세요.","알림",function(){$("#mb_name").focus();});
		return false;
	}

	if($("input[name='mb_3']:checked").length==0){
		jAlert("회사 유형을 선택해 주세요.","알림",function(){$("input[name='mb_3']").focus();});
		return false;
	}

	if($("input[name='mb_10[]']:checked").length==0){
		jAlert("회원사 가입유무 선택해 주세요.","알림",function(){$("input[name='mb_10[]']").focus();});
		return false;
	}

	if(!$("#mb_4").val().trim()) {
		jAlert("사업자등록번호를 확인해 주세요.","알림",function(){$("#mb_4").focus();});
		return false;
	}
	if(!$("#mb_6").val().trim()) {
		jAlert("담당자 이름을 확인해 주세요.","알림",function(){$("#mb_6").focus();});
		return false;
	}
	if(!$("#mb_7").val().trim()) {
		jAlert("담당자 직위를 확인해 주세요.","알림",function(){$("#mb_7").focus();});
		return false;
	}
	if(!$("#mb_8").val().trim()) {
		jAlert("휴대폰 번호를 확인해 주세요.","알림",function(){$("#mb_8").focus();});
		return false;
	}

	if(!$("#mb_5").val().trim()) {
		jAlert("담당자 이메일을 확인해 주세요.","알림",function(){$("#mb_5").focus();});
		return false;
	}else{
		let checkEmail = RegExp(/^([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})$/);
		if(!checkEmail.test($("#mb_5").val().trim())) {
			jAlert("이메일을 확인해 주세요.","알림",function(){$("#mb_5").focus();});
			return false;
		}
	}

	/*
	mb_9_chk="false";
	$("input[name='mb_9[]']").each(function(){
		if($(this).val().length>0) mb_9_chk="true";
	})
	if(mb_9_chk=="false") {
		mb_9_t=$(".Product .title").text();
		jAlert(mb_9_t+"을 하나이상 입력해 주세요.","알림",function(){$("input[name='mb_9[]']:eq(0)").focus();});
		return false;
	}
*/
	if($("#coupon").val().length>0 && $("#coupon").val().length<6) {
		jAlert("쿠폰번호를 확인해 주세요.","알림");
		return false;
	}

}
</script>



<!-- } 회원정보 입력/수정 끝 -->
