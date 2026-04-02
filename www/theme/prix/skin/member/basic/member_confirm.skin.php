<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
$board['bo_10']="6"; // 1뎁스
$board['bo_9']="3"; // 2뎁스
include_once(G5_THEME_PATH.'/head.php');
?>
<div id="join_step_02">
<div class="member_sign_layout">
        <h4 class="member_sign_title">회원정보수정</h4>


        <div class="join_agree_head myPage">
            <p>안전한 회원정보 수정을 위해 <br>
            비밀번호 입력 후 확인이 가능합니다.</p>
        </div>
		<form name="fmemberconfirm" action="<?php echo $url ?>" onsubmit="return fmemberconfirm_submit(this);" method="post">
		<input type="hidden" name="mb_id" value="<?php echo $member['mb_id'] ?>">
		<input type="hidden" name="w" value="u">
        <div class="join_agree_body">
            <div class="formArea">
                <div class="formBox">
                    <div class="inputBox type2">
                        <p class="title">이메일</p>
                        <input class="readonly" type="text" value="<?=$member['mb_id']?>" readonly>
                    </div>
                    <div class="formBox password">
                        <div class="inputBox">
                            <input type="password" class="required password"name="mb_password" id="confirm_mb_password" required size="15" maxLength="20" placeholder="비밀번호">
                            <div class="input_password_icon"></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="formBtns">
            <button class="agree">확인</button>
        </div>
		</form>
    </div>


</div>


<script>
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

	function fmemberconfirm_submit(f){
		document.getElementById("btn_submit").disabled = true;
		return true;
	}
</script>

<?
include_once(G5_THEME_PATH.'/tail.php');
?>