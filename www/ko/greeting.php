<?
include_once("./_common.php");

$board['bo_10']="1"; // 1뎁스
$board['bo_9']="1"; // 2뎁스
//$board['bo_8']="1"; // 3뎁스
//$board['bo_7']="1"; // 4뎁스
// $board['bo_5'] = ""; // 1뎁스 제목
// $board['bo_6'] = ""; // 1뎁스 설명
// $board['bo_subject'] = "";  // 2뎁스 제목
//$board['bo_4'] = $config['cf_title']."의 혜택을 확인하세요.";  // 2뎁스 설명

include_once(G5_THEME_PATH.'/head.php');
?>

<div id="greeting">

    <div class="sSection full tac p0_15">
        <div class="inner">
            <h4 class="fs30 lh1-25 c111 fw550 mgb3">MACOMAEX(매커멕스)는</h4>
            <p class="fs18 lh1-5 c666666 mgb1">
                MATCHING(매칭), CONSULTING(컨설팅), M&A(기업인수합병), EXIT(엑시트)의 영문 철자를 조합한 플랫폼명으로, <br class="pVar">
                매커멕스로 하여금 B2B를 넘어선 E2E라 할 수 있는 기업 간의 거래를,  <br class="ptVar">
                매칭을 시작으로기업의 성장을 꾀하여 엑시트까지 하는 시스템을 만들고자 함을 의미합니다.
            </p>
            <p class="fs18 lh1-5 c666666 mgb5">
                메커멕스에서 제공하는서비스를 통해 기업의 평생 과제라 할 수 있는 신규거래처 발굴에 대한 어려움을 줄이는 동시에 <br class="pVar">
                각 기업에서 특화된 장점으로 기업 내·외부적인 어려움을 상쇄하는 장기적인 시너지를 기대할 수 있습니다.
            </p>
            <div class="img imgShadow tac"><img src="/theme/prix/img/sub/greeting_graphic_01.png" alt=""></div>
        </div>
    </div>

    <div class="sSection full tac bgGray p15_15">
        <div class="inner">
            <h4 class="fs30 lh1-25 c111 fw550 mgb3">매커멕스 프로세스</h4>
            <p class="fs18 lh1-5 c666666 mgb1">
                매커멕스에서 ‘매칭’은 신규 거래처를 찾고 관계가 형성되는 1차적인 개념을 넘어 기업과 기업 간의 연결을 통한 <br class="ptVar">
                전략적 제휴를 의미하며, 이를 B2B를 넘어선 E2E라고 표현하고 있습니다.
            </p>
            <p class="fs18 lh1-5 c666666 mgb5">
                매커멕스에서는 다음과 같은 프로세스로 매칭이 진행됩니다.
            </p>
            <ul class="mHow_step">
                <li class="bxShadow">
                    <p class="num">STEP 01</p>
                    <div class="icon"><img src="/theme/prix/img/main/mHow_icon_01.png" alt=""></div>
                    <div class="textArea">
                        <p>신규거래처를 찾고자 하는  <br class="pVar">[우리 기업]의 <br><span>사업자번호입력 및 검색</span></p>
                    </div>
                </li>
                <li class="bxShadow">
                    <p class="num">STEP 02</p>
                    <div class="icon"><img src="/theme/prix/img/main/mHow_icon_02.png" alt=""></div>
                    <div class="textArea">
                        <p>검색결과로 나온, <br>[우리 기업]과 매칭할만한 <br><span>[추천 거래처] 확인</span></p>
                    </div>
                </li>
                <li class="bxShadow">
                    <p class="num">STEP 03</p>
                    <div class="icon"><img src="/theme/prix/img/main/mHow_icon_03.png" alt=""></div>
                    <div class="textArea">
                        <p>[추천 거래처]에 뜬 기업들의 상세 정보 확인 후 원하는 [상대 기업]에 직접 연락하여 업무 제안  <br>
                            ↓ <br>
                            <span>직접 컨택 및 매칭 요청<!-- <b class="point">*</b> --></span>
                        </p>
                    </div>
                </li>
                <li class="bxShadow">
                    <p class="num">STEP 04</p>
                    <div class="icon"><img src="/theme/prix/img/sub/greeting_process_icon_04.png" alt=""></div>
                    <div class="textArea">
                        <p> [상대 기업]이 매칭 승인 시, 양쪽 기업에 사전 입력한 영업 담당자 연락처 공개<br>
                            ↓ <br>
                            <span>담당자와 빠른 소통</span>
                        </p>
                    </div>
                </li>
            </ul>
        </div>
    </div>

    <div class="sSection full tac p15_0">
        <div class="inner">
            <h4 class="fs30 lh1-25 c111 fw550 mgb5">한눈에 보는 매커멕스</h4>

            <ul class="greeting_view">
                <li class="bxShadow bxShadow2">
                    <p class="num">STEP 01</p>
                    <div class="icon"><img src="/theme/prix/img/sub/greeting_view_icon_01.png" alt=""></div>
                    <div class="textArea">
                        <p>우리 기업의 <br>사업자 번호만 <br>입력하면 거래처 추천</p>
                    </div>
                </li>
                <li class="bxShadow bxShadow2">
                    <p class="num">STEP 02</p>
                    <div class="icon"><img src="/theme/prix/img/sub/greeting_view_icon_02.png" alt=""></div>
                    <div class="textArea">
                        <p>매칭을 통한 <br>기업 간 전략적 제휴</p>
                    </div>
                </li>
                <li class="bxShadow bxShadow2">
                    <p class="num">STEP 03</p>
                    <div class="icon"><img src="/theme/prix/img/sub/greeting_view_icon_03.png" alt=""></div>
                    <div class="textArea">
                        <p>특별히 제공되는 <br>전문가 컨설팅 <br>
                            <span class="mgt1">* 유료 멤버십 이용 시 제공</span>
                        </p>
                    </div>
                </li>
                <li class="bxShadow bxShadow2">
                    <p class="num">STEP 04</p>
                    <div class="icon"><img src="/theme/prix/img/sub/greeting_view_icon_04.png" alt=""></div>
                    <div class="textArea">
                        <p>우리기업의 주식가치 확인<br>
                            <span class="mgt1">* 마이페이지에서 확인 가능</span>
                        </p>
                    </div>
                </li>
                <li class="bxShadow bxShadow2">
                    <p class="num">STEP 05</p>
                    <div class="icon"><img src="/theme/prix/img/sub/greeting_view_icon_05.png" alt=""></div>
                    <div class="textArea">
                        <p>숏폼 시대에 걸맞는 <br>기업 홍보관 <br>
                            <a class="mgt1" href="<?=$link_0201?>">구경하기 →</a>
                        </p>
                    </div>
                </li>
                <li class="bxShadow bxShadow2">
                    <p class="num">STEP 06</p>
                    <div class="icon"><img src="/theme/prix/img/sub/greeting_view_icon_06.png" alt=""></div>
                    <div class="textArea">
                        <p>AI 기능으로 <br> 딱 맞는 거래처 추천 <br>
                            <span class="mgt1">* 2025년 업데이트 예정</span>
                        </p>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>

<?
include_once(G5_THEME_PATH.'/tail.php');
?>