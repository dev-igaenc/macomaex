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
			<form action="<?= $register_action_url ?? '' ?>" method="post" id="step_form" onsubmit="return fregisterform_submit();">
				<input type="hidden" name="url" value="<?= $url ?? '' ?>">
				<input type="hidden" name="w" value="">
				<input type="hidden" name="mb_id" id="reg_mb_id" value="<?= $id ?? '' ?>">
				<input type="hidden" name="mb_password" value="<?= $password ?? '' ?>">
				<input type="hidden" name="agree_chk" value="<?= $agree_chk ?? '' ?>">
				<input type="hidden" name="provider" value="<?php echo $provider ?? ''; ?>">
				<input type="hidden" name="cert_type" value="<?php echo $cert_type ?? ''; ?>">
				<input type="hidden" name="cert_no" value="<?php echo $cert_no ?? ''; ?>">
				<input type="hidden" name="id" value="<?php echo $mb_id ?? ''; ?>">
        <h4 class="member_sign_title">회원정보입력</h4>

        <ul class="join_step step3">
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
						<button type="button" class="btn srh">확인</button>
                    </div>
                    <div class="inputBox requied type2">
                        <p class="title">법인명</p>
                        <input type="text" name="mb_name" id="mb_name" value="" readonly>
						<input type="hidden" name="mb_company" id="mb_company" class="readonly" readonly value="">
                    </div>
                    <!--
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
						<p class="txt-right formBox_errorM ">이메일 주소를 입력해주세요</p>
                    </div>
                    -->
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
// 담당자 정보 입력(이메일 포함) 비활성화에 따라 mb_5 중복체크 로직 비활성화
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
				}else{
					//오류시
					$("#com_chk").val("false");
					jAlert("처리에 오류가 있습니다.","알림");
				}
		})
		.fail(function() {
			$("#com_chk").val("false");
			jAlert("처리에 오류가 있습니다.","알림");
		});



	})
//전화번호 자동입력
$(".tel_chk").keyup(function(){
	phone=$(this).val();
	phone=phone.replace(/[^0-9]/g, '').replace(/^(\d{2,3})(\d{3,4})(\d{4})$/, `$1-$2-$3`);
	$(this).val(phone);
})
// 회사 유형 입력 비활성화로 타이틀 변경 로직 비활성화
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

// 회원사 가입유무 입력 비활성화

function fregisterform_submit()
{

    // 회원아이디 검사
    if ($("input[name='w']").val() == "") {
        var msg = $.trim(reg_mb_id_check());
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

	if(!$("#mb_4").val().trim()) {
		jAlert("사업자등록번호를 확인해 주세요.","알림",function(){$("#mb_4").focus();});
		return false;
	}
	// 담당자 정보 입력/검증 비활성화 (mb_5/mb_6/mb_7/mb_8)
	if($("#coupon").val().length>0 && $("#coupon").val().length<6) {
		jAlert("쿠폰번호를 확인해 주세요.","알림");
		return false;
	}

}
</script>
