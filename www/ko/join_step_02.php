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

<div id="join_step_02">
    <div class="member_sign_layout">
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
                        <p class="title">법인명</p>
                        <input type="text" requied>
                    </div>
                    <div class="inputBox requied type2">
                        <p class="title">이메일</p>
                        <input type="text" requied>
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
                        <input type="text" requied>
                    </div>
                    <div class="inputBox requied type2">
                        <p class="title">담당자 이름</p>
                        <input type="text" requied>
                    </div>
                    <div class="inputBox requied type2">
                        <p class="title">담당자 직위</p>
                        <input type="text" requied>
                    </div>
                    <div class="inputBox requied type2">
                        <p class="title">휴대폰 번호</p>
                        <input type="text" requied>
                    </div>
                    <div class="inputBox type2 type3">
                        <p class="title">주요 서비스/제품</p>
                        <div class="products">
                            <input type="text">
                            <a href="" class="add">+</a>
                            <a href="" class="minus">-</a>
                        </div>
                        <div class="products">
                            <input type="text">
                            <!-- <a href="" class="add">+</a> -->
                            <a href="" class="minus">-</a>
                        </div>
                        <div class="products">
                            <input type="text">
                            <!-- <a href="" class="add">+</a> -->
                            <a href="" class="minus">-</a>
                        </div>
                        <!-- 구조 개선을 원하시면 말씀해주세요 -->
                    </div>

                </div>
            </div>
        </div>

        <div class="formBtns">
            <button class="agree">가입하기</button>
            <button class="disagree">취소하기</button>
            <!-- button, a 둘 다 상관없음 -->
        </div>

    </div>



</div>

<?
include_once(G5_THEME_PATH.'/tail.php');
?>