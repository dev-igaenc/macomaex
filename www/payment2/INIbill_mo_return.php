<!DOCTYPE html>
<html lang="ko">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport"
            content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <title>KG이니시스 결제샘플</title>
        <link rel="stylesheet" href="css/style.css">
		<link rel="stylesheet" href="css/bootstrap.min.css">
    </head>

    <body class="wrap">

        <!-- 본문 -->
        <main class="col-8 cont" id="bill-01">
            <!-- 페이지타이틀 -->
            <section class="mb-5">
                <div class="tit">
                    <h2>빌링(정기과금)</h2>
                    <p>KG이니시스 결제창을 호출하여 정기결제를 위한 billkey 발급 서비스</p>
                </div>
            </section>
            <!-- //페이지타이틀 -->


            <!-- 카드CONTENTS -->
            <section class="menu_cont mb-5">
                <div class="card">
                    <div class="card_tit">
                        <h3>PC 빌링키발급</h3>
                    </div>

                    <!-- 유의사항 -->
                    <div class="card_desc">
                        <h4>※ 유의사항</h4>
                        <ul>
                            <li>테스트MID 결제시 실 승인되며, 당일 자정(24:00) 이전에 자동으로 취소처리 됩니다.</li>
							<li>가상계좌 채번 후 입금할 경우 자동환불되지 않사오니, 가맹점관리자 내 "입금통보테스트" 메뉴를 이용부탁드립니다.<br>(실 입금하신 경우 별도로 환불요청해주셔야 합니다.)</li>
							<li>국민카드 정책상 테스트 결제가 불가하여 오류가 발생될 수 있습니다. 국민, 카카오뱅크 외 다른 카드로 테스트결제 부탁드립니다.</li>
                        </ul>
                    </div>
                    <!-- //유의사항 -->


                    <form name="" id="result" method="post" class="mt-5">
                    <div class="row g-3 justify-content-between" style="--bs-gutter-x:0rem;">
 
                        <label class="col-10 col-sm-2 gap-2 input param" style="border:none;">resultcode</label>
                        <label class="col-10 col-sm-9 reinput">
                            <?php echo $_REQUEST["resultcode"] ?>
                        </label>
						
						<label class="col-10 col-sm-2 input param" style="border:none;">resultmsg</label>
                        <label class="col-10 col-sm-9 reinput">
                            <?php echo $_REQUEST["resultmsg"] ?>
                        </label>
						
						<label class="col-10 col-sm-2 input param" style="border:none;">tid</label>
                        <label class="col-10 col-sm-9 reinput">
                            <?php echo $_REQUEST["tid"] ?>
                        </label>
						
						<label class="col-10 col-sm-2 input param" style="border:none;">billkey</label>
                        <label class="col-10 col-sm-9 reinput">
                            <?php echo $_REQUEST["billkey"] ?>
                        </label>
						
						<label class="col-10 col-sm-2 input param" style="border:none;">orderid</label>
                        <label class="col-10 col-sm-9 reinput">
                            <?php echo $_REQUEST["orderid"] ?>
                        </label>
						
						<label class="col-10 col-sm-2 input param" style="border:none;">pgauthdate</label>
                        <label class="col-10 col-sm-9 reinput">
                            <?php echo $_REQUEST["pgauthdate"] ?>
                        </label>
						
						<label class="col-10 col-sm-2 input param" style="border:none;">pgauthtime</label>
                        <label class="col-10 col-sm-9 reinput">
                            <?php echo $_REQUEST["pgauthtime"] ?>
                        </label>
						
						<label class="col-10 col-sm-2 input param" style="border:none;">cardcd</label>
                        <label class="col-10 col-sm-9 reinput">
                            <?php echo $_REQUEST["cardcd"] ?>
                        </label>
						
						<label class="col-10 col-sm-2 input param" style="border:none;">cardno</label>
                        <label class="col-10 col-sm-9 reinput">
                            <?php echo $_REQUEST["cardno"] ?>
                        </label>
						
						<label class="col-10 col-sm-2 input param" style="border:none;">cardkind</label>
                        <label class="col-10 col-sm-9 reinput">
                            <?php echo $_REQUEST["cardkind"] ?>
                        </label>
						
						<label class="col-10 col-sm-2 input param" style="border:none;">CheckFlag</label>
                        <label class="col-10 col-sm-9 reinput">
                            <?php echo $_REQUEST["CheckFlag"] ?>
                        </label>
 
                    </div>
                </form>

				
				<button onclick="location.href='INIbill_mo_req.php'" class="btn_solid_pri col-6 mx-auto btn_lg" style="margin-top:50px">돌아가기</button>
					
                </div>
            </section>
			
        </main>
		
    </body>
</html>