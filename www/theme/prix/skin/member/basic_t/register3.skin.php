<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>
<script src="<?php echo G5_JS_URL ?>/jquery.register_form.js"></script>

<style>
    #sVisual,
    #pageTop{display: none}
</style>

<div id="join_step_02">
    <div class="member_sign_layout">
			<form action="<?=$register_action_url?>" method="post" id="step_form" onsubmit="return fregisterform_submit();">
				<input type="hidden" name="w" value="">
				<input type="hidden" name="mb_id" id="reg_mb_id" value="<?=$id?>">
				<input type="hidden" name="mb_password" value="<?=$password?>">
				<input type="hidden" name="agree_chk" value="<?=$agree_chk?>">

        <h4 class="member_sign_title">회원정보입력</h4>

        <ul class="join_step step2">
        <!-- step1, 2, 3 마다 하단 그래픽이 달라짐 -->
            <li>
                <span class="circle"></span>
                <span class="text">약관동의</span>
            </li>
            <li>
                <span class="circle"></span>
                <span class="text">계정정보</span>
            </li>
            <li>
                <span class="circle"></span>
                <span class="text">기업정보</span>
            </li>
        </ul>

        <div class="join_agree_head">
            <h4>회원정보입력</h4>
            <p>기본 정보 *표시는 필수 입력 사항입니다. </p>
        </div>

        <div class="join_agree_body">
            <div class="formArea">
                <div class="formBox ">
                    <div class="inputBox requied type2">
                        <p class="title">사업자등록번호</p>
						<input type="hidden" name="com_chk" id="com_chk" class="com_chk" value="false">
                        <input type="text" name="mb_4" id="mb_4" class="comnum_chk" maxlength="12">
						<button type="button" class="btn srh">중복확인</button>
                    </div>
                    <div class="inputBox requied type2">
                        <p class="title">법인명</p>
                        <input type="text" name="mb_name" id="mb_name" value="" readonly>
						<input type="hidden" name="mb_company" id="mb_company" class="readonly" readonly value="">
                    </div>
                    <div class="inputBox requied type2">
                        <p class="title">회사 유형</p>
                        <ul class="radioBox">
                            <li>
                                <input type="radio" name="mb_3" value="제조사" id="type1">
                                <label for="type1">제조사</label>
                            </li>
                            <li>
                                <input type="radio" name="mb_3" value="유통사" id="type2">
                                <label for="type2">유통사</label>
                            </li>
                            <li>
                                <input type="radio" name="mb_3" value="기타" id="type3">
                                <label for="type3">기타</label>
                            </li>
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
                    </div>
                    <div class="inputBox requied type2">
                        <p class="title">담당자 이름</p>
                        <input type="text" name="mb_6" id="mb_6" maxlength="32">
                    </div>
                    <div class="inputBox requied type2">
                        <p class="title">담당자 직위</p>
                        <input type="text" name="mb_7" id="mb_7" maxlength="32">
                    </div>
                    <div class="inputBox requied type2">
                        <p class="title">휴대폰 번호</p>
                        <input type="text" name="mb_8" id="mb_8" class="tel_chk" maxlength="13" >
                    </div>
					<div class="inputBox requied type2">
                        <p class="title">담당자 이메일</p>
                        <input type="text" name="mb_5" id="mb_5" maxlength="32">
                    </div>
                    <div class="inputBox type2 type3 Product">
                        <p class="title">주요 서비스/제품</p>
                        <div class="products addz">
                            <input name="mb_9[]" type="text" maxlength="32">
                            <button type="button" class="add">+</button>
                        </div>
                        <!-- 구조 개선을 원하시면 말씀해주세요 -->
                    </div>
					<div class="inputBox type2">
                        <p class="title">가입이벤트쿠폰</p>
                        <input type="text" name="coupon" id="coupon" placeholder="ex) ED9A01" maxlength="6">
                    </div>
				</div>
			</div>
        </div>

        <div class="formBtns">
            <button class="agree">가입하기</button>
            <button class="disagree">취소하기</button>
            <!-- button, a 둘 다 상관없음 -->
        </div>
		</form>
    </div>

</div>
<script>

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
	$(".srh").click(function(){
	 comnum=$(this).prev().val();
	 if(comnum.length<12) {
		 $("#com_chk").val("false");
		 jAlert("사업자등록번호를 확인하세요.","알림",function(){
			 $("#mb_4").focus();
		 });
		 return false;
	 }
	$.post( "../get_set.php", {
		type: 'join_comp_chk',
		comnum:comnum
	})
	.done(function(jqXHR) {
			//alert(jqXHR);
			//console.log(jqXHR);
			jqXHR=jqXHR.split("|");
			if(jqXHR[0]==1000){
				//완료시
				$("#com_chk").val("true");
				$("#mb_name").val(jqXHR[1]).attr('readonly', true);
				$("#mb_company").val(jqXHR[2]).attr('readonly', true);
			}else if(jqXHR[0]==501){
				//등록된 사업자가 없을 경우
				$("#com_chk").val("false");
				jAlert("해당 사업자 번호로는 등록할 수 없습니다.","알림",function(){
					$("#mb_4").focus();
				});
				//$("#com_chk").val("true");
				//$("#mb_name").val("").attr('readonly', false).focus();
				//$("#mb_company").val("").attr('readonly', true);
			}else if(jqXHR[0]==502){
				//등록된 사업자가 있을 경우
				$("#com_chk").val("false");
				jAlert("해당 사업자 번호로 등록된 가입자가 있습니다.","알림",function(){
					$("#mb_4").focus();
				});
			}else{
				//오류시
				$("#com_chk").val("false");
				jAlert("처리에 오류가 있습니다.","알림");
			}
	});



	})
//전화번호 자동입력
$(".tel_chk").keyup(function(){
	phone=$(this).val();
	phone=phone.replace(/[^0-9]/g, '').replace(/^(\d{2,3})(\d{3,4})(\d{4})$/, `$1-$2-$3`);
	$(this).val(phone);
})
$("input[name='mb_3']").click(function(){
	chk=$(this).val();
	if(chk=="제조사") $(".Product .title").text("주요 서비스/제품");
	if(chk=="유통사") $(".Product .title").text("주요 유통 취급 제품");
	else $(".Product .title").text("주요 서비스/제품");
})
$(".Product .addz").wrap("<div></div>");
var addz=$(".Product .addz").parent().html();
addz=addz.replace("+","-");
addz=addz.replace('"add"','"minus"');
$(".Product .addz").unwrap();
$(".Product .add").click(function(){
	$(".Product").append(addz);
})
$("body").on("click",".Product .minus",function(){
	$(this).parents(".products").remove();
})

	if($("#coupon").length>0){
		$("#coupon").keyup(function(){
			if($("#coupon").val().length==6){
				var id=$("#coupon").val();
				$.post( "../get_set.php", {
					type: 'coupon_chk',
					id:id
				})
				.done(function(jqXHR) {
						//console.log(jqXHR);
						jqXHR=jqXHR.split("|");
						if(jqXHR[0]==1000){
							//완료시
							$("#coupon").addClass("readonly").attr('readonly', true);
							jAlert("할인 쿠폰 적용됨");
						}else if(jqXHR[0]==500){
							//쿠폰 적용 실패시
							jAlert(jqXHR[1],"알림",function(){
								$("#coupon").val("");
								$("#coupon").focus();
							});
							return false;
						}else{
							//오류시
							$("#com_chk").val("false");
							jAlert("처리에 오류가 있습니다.","알림");
						}
				});
				return false;
			}
		})
	}

$("input[name='mb_10[]']").click(function(){
	$this=$(this);
	if($this.is(":checked")){
		if($this.val()=="해당사항없음") $("input[name='mb_10[]'][value!='해당사항없음']").prop("checked",false);
		else $("input[name='mb_10[]'][value='해당사항없음']").prop("checked",false);
	}
})

function fregisterform_submit()
{

    // 회원아이디 검사
    if ($("input[name='w']").val() == "") {
        var msg = reg_mb_id_check();
        if (msg) {
            alert(msg);
            return false;
        }
    }

	if($("#com_chk").val()!="true") {
		jAlert("등록가능한 사업자등록번호를 입력해주세요.","알림",function(){$("#mb_4").focus();});
		return false;
	}
	
	if(!$("#mb_name").val().trim()) {
		jAlert("법인명을 확인해 주세요.","알림",function(){$("#mb_name").focus();});
		return false;
	}

	if($("input[name='mb_3']:checked").length==0){
		jAlert("회사 유형을 선택해 주세요.","알림",function(){$("input[name='mb_3']").focus();});
		return false;
	}

	if($("input[name='mb_10[]']:checked").length==0){
		jAlert("회원사 가입유무 선택해 주세요.","알림",function(){$("input[name='mb_10[]:eq(0)']").focus();});
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