<?
include_once("./_common.php");

$board['bo_10']="6"; // 1뎁스
$board['bo_9']="3"; // 2뎁스
//$board['bo_8']="1"; // 3뎁스
//$board['bo_7']="1"; // 4뎁스
// $board['bo_5'] = ""; // 1뎁스 제목
// $board['bo_6'] = ""; // 1뎁스 설명
// $board['bo_subject'] = "";  // 2뎁스 제목
//$board['bo_4'] = $config['cf_title']."의 혜택을 확인하세요.";  // 2뎁스 설명

include_once(G5_THEME_PATH.'/head.php');
?>

<!-- <style>
    #sVisual,
    #pageTop{display: none}
</style> -->

<div id="join_step_02">
<div class="member_sign_layout">
        <h4 class="member_sign_title">회원정보수정</h4>


        <div class="join_agree_head myPage">
            <p>안전한 회원정보 수정을 위해 <br>
            비밀번호 입력 후 확인이 가능합니다.</p>
        </div>

        <div class="join_agree_body">
            <div class="formArea">
                <div class="formBox">
                    <div class="inputBox type2">
                        <p class="title">이메일</p>
                        <input class="readonly" type="text" value="아이디가 들어갑니다." readonly>
                    </div>
                    <div class="formBox password">
                        <div class="inputBox">
                            <input type="password" class="password" placeholder="비밀번호">
                            <div class="input_password_icon"></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="formBtns">
            <button class="agree">수정하기</button>
            <!-- button, a 둘 다 상관없음 -->
        </div>

    </div>

    <div class="member_sign_layout">
        <h4 class="member_sign_title">회원정보수정</h4>

        <div class="join_agree_body">
            <div class="formArea">
                <div class="formBox password">
                    <div class="inputBox type2">
                        <p class="title">이메일</p>
                        <input class="readonly" type="text" value="아이디가 들어갑니다." readonly>
                    </div>
                    <div class="inputBox requied type2 password myPage_password">
                        <p class="title">비밀번호</p>
                        <div class="my_password">
                            <input class="password" type="password" name="" id="" requied>
                            <div class="input_password_icon"></div>
                            <button>변경하기</button>
                        </div>
                    </div>
                    <div class="inputBox requied type2">
                        <p class="title">법인명</p>
                        <input type="text">
                    </div>
                    <div class="inputBox requied type2">
                        <p class="title">회사 유형</p>
                        <ul class="radioBox">
                            <li>
                                <input type="radio" name="typeRadio" id="type1">
                                <label for="type1">제조사</label>
                            </li>
                            <li>
                                <input type="radio" name="typeRadio" id="type2">
                                <label for="type2">유통사</label>
                            </li>
                            <li>
                                <input type="radio" name="typeRadio" id="type3">
                                <label for="type3">기타</label>
                            </li>
                        </ul>
                    </div>
                    <div class="inputBox requied type2">
                        <p class="title">사업자등록번호</p>
                        <input type="text">
                    </div>
                    <div class="inputBox requied type2">
                        <p class="title">담당자 이름</p>
                        <input type="text">
                    </div>
                    <div class="inputBox requied type2">
                        <p class="title">담당자 직위</p>
                        <input type="text">
                    </div>
                    <div class="inputBox requied type2">
                        <p class="title">휴대폰 번호</p>
                        <input type="text">
                    </div>
                    <div class="inputBox type2 type3">
                        <p class="title">주요 서비스/제품</p>
                        <div class="products">
                            <input type="text">
                            <!-- <a href="" class="add">+</a> -->
                            <a href="" class="minus">-</a>
                        </div>
                        <div class="products">
                            <input type="text">
                            <a href="" class="add">+</a>
                            <a href="" class="minus">-</a>
                        </div>
                        <!-- 구조 개선을 원하시면 말씀해주세요 -->
                    </div>

                </div>
            </div>
        </div>

        <div class="formBtns">
            <button class="agree">수정하기</button>
            <button class="disagree">취소하기</button>
            <!-- button, a 둘 다 상관없음 -->
        </div>

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