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

<div id="join_step_03">
    <div class="member_sign_layout">
        <h4 class="member_sign_title">회원정보입력</h4>

        <ul class="join_step step3">
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

        <div class="join_agree_done">
            <div class="icon"><img src="/img/icon/icon_check_fff.svg" alt=""></div>
            <h4>회원 가입 신청이 완료되었습니다.</h4>
        </div>

        <div class="formBtns">
            <button class="">로그인하기</button>
            <button class="">메인으로 가기</button>
            <!-- button, a 둘 다 상관없음 -->
        </div>

    </div>



</div>

<?
include_once(G5_THEME_PATH.'/tail.php');
?>