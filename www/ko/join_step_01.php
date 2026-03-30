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

<div id="join_step_01">
    <div class="member_sign_layout">
        <h4 class="member_sign_title">회원가입</h4>

        <ul class="join_step step1">
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
                        <input type="checkbox" name="" id="">
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
                        <input type="checkbox" name="" id="">
                        <label for="">개인정보수집에 동의합니다.</label>
                    </div>            
                </div>
                <div class="formBox">
                    <div class="checkBox all">
                        <input type="checkbox" name="" id="">
                        <label for="">모든 약관에 동의합니다.</label>
                    </div>        
                </div>
            </div>
        </div>

        <div class="formBtns">
            <button class="agree">동의합니다</button>
            <button class="disagree">동의하지 않습니다</button>
            <!-- button, a 둘 다 상관없음 -->
        </div>

    </div>



</div>

<?
include_once(G5_THEME_PATH.'/tail.php');
?>