<?
include_once("./_common.php");

$board['bo_10']=""; // 1뎁스
$board['bo_9']=""; // 2뎁스
//$board['bo_8']="1"; // 3뎁스
//$board['bo_7']="1"; // 4뎁스
// $board['bo_5'] = ""; // 1뎁스 제목
// $board['bo_6'] = ""; // 1뎁스 설명
// $board['bo_subject'] = "";  // 2뎁스 제목
//$board['bo_4'] = $config['cf_title']."의 혜택을 확인하세요.";  // 2뎁스 설명

include_once(G5_THEME_PATH.'/head.php');
?>

<style>
    #sVisual,
    #pageTop{display: none}
</style>

<div id="login">
    <div class="member_sign_layout">
        <h4 class="member_sign_title">로그인</h4>

        <div class="formArea">
            <div class="formBox email">
                <p class="formBox_title">이메일</p>
                <div class="inputBox">
                    <input type="text" class="normal">
                </div>
                <p class="formBox_errorM">이메일 주소를 입력해주세요</p>
            </div>
            <div class="formBox password">
                <p class="formBox_title">비밀번호</p>
                <div class="inputBox">
                    <input type="password" class="password">
                    <div class="input_password_icon"></div>
                </div>
                <p class="formBox_errorM">비밀번호를 입력해주세요</p>
            </div>
        </div>

        <div class="formBtns">
            <button type="submit">로그인</button>
        </div>

        <ul class="formLinks">
            <li><a href="">
                <p class="text">아직 회원이 아니신가요?</p>
                <p class="title">회원가입하기</p>
            </a></li>
            <li><a href="">
                <p class="text">비밀번호를 잊으셨나요?</p>
                <p class="title">비밀번호찾기</p>
            </a></li>
        </ul>
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
</script>




<?
include_once(G5_THEME_PATH.'/tail.php');
?>