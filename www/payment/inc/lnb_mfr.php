<? 

    $lnbActive_dep1 = "";
    $lnbActive_dep2 = "";
    $lnbActive_dep3 = "";
    $lnbActive_dep4 = "";
    $lnbActive_dep5 = "";
    $lnbActive_dep6 = "";
    $lnbActive_dep7 = "";
	$lnbActive_dep8 = "";
    $txtActive_dep = ""; 

    $lnbActive_page1 = "";
    $lnbActive_page2 = "";
    $lnbActive_page3 = "";
    $lnbActive_page4 = "";
    $lnbActive_page5 = "";
    $lnbActive_page6 = "";
    $lnbActive_page7 = "";
	$lnbActive_page8 = "";
    $txtActive_page = "";


    $lnbActive_sub1 = "";
    $lnbActive_sub2 = "";
    $lnbActive_sub3 = "";
    $lnbActive_sub4 = "";
    $lnbActive_sub5 = "";
    $lnbActive_sub6 = "";
    $lnbActive_sub7 = "";
	$lnbActive_sub8 = "";
    $txtActive_sub = "";

    // $depthnum : 해당 페이지 활성화
    // $pagenum : 해당 뎁스 활설화
    // $txtActive : 해당 페이지 텍스트
    // lnbActive : 해당 페이지 활성화효과

    if ($depthnum == "1" || $depthnum == 1) {
		$lnbActive_dep1 = "class=\"active\"";
		$txtActive_dep = "MACOMAEX";
	} else if ($depthnum == "2" || $depthnum == 2) {
		$lnbActive_dep2 = "class=\"active\"";
		$txtActive_dep = "서비스";
	} else if ($depthnum == "3" || $depthnum == 3) {
		$lnbActive_dep3 = "class=\"active\"";
		$txtActive_dep = "매커멕스소개";
	} else if ($depthnum == "4" || $depthnum == 4) {
		$lnbActive_dep4 = "class=\"active\"";
		$txtActive_dep = "제조사안내";
	} else if ($depthnum == "5" || $depthnum == 5) {
		$lnbActive_dep5 = "class=\"active\"";
		$txtActive_dep = "유통사안내";
	} else if ($depthnum == "6" || $depthnum == 6) {
		$lnbActive_dep6 = "class=\"active\"";
		$txtActive_dep = "고객센터";
	}

	if ($pagenum == "1" || $pagenum == 1) {
		$lnbActive_page1 = "class=\"active\"";
		$txtActive_page = "이용안내";
	} else if ($pagenum == "2" || $pagenum == 2) {
		$lnbActive_page2 = "class=\"active\"";
		$txtActive_page = "등록하기";
	} 

?>
<section class="no-subNav__wrap">
	<div class="no-inner">
		<div class="no-subNav__box">
			<span class="no-subNav__top"><?=$txtActive_dep?></span>
			<h2 class="no-subNav__bot"><?=$txtActive_page?></h2>
		</div>
	</div>
</section>