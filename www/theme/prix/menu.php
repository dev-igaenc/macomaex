<?php
$brand_borad=sql_fetch("select * from {$g5['board_table']} where bo_table='menu_adm'");
$brand_category=explode("|",$brand_borad['bo_category_list']);

$link_0000 = "/"; // 홈

// 매커멕스 소개
$link_0101 = "/ko/greeting.php"; // 매커멕스 소개

// 기업홍보관
$link_0201 = "/bbs/board.php?bo_table=Promotion"; // 기업홍보관

// 기업매칭
$link_0301 = "/ko/matching.php"; // 기업매칭 index
$link_0302 = "/ko/matching_list.php"; // 기업매칭 List
$link_0303 = "/ko/matching_view.php"; // 기업매칭 View

// 멤버쉽 요금제
$link_0401 = "/ko/plan.php"; // 멤버쉽 요금제

// 이용자 지원
$link_0501 = "/bbs/board.php?bo_table=Notice"; // 공지사항
$link_0502 = "/bbs/board.php?bo_table=FAQ"; // 자주 묻는 질문
$link_0503 = "/bbs/write.php?bo_table=Contact"; // 고객문의


/* Member */
$link_1001 = "/bbs/login.php"; // 로그인
$link_1002 = "/bbs/register.php"; // 회원가입

/* Mypage */
/*
if($member[mb_11]=='dev'){
	$link_0601 = "/ko/my_company_t.php"; // 내 회사 정보
}else{
	$link_0601 = "/ko/my_company.php"; // 내 회사 정보
}
*/
$link_0601 = "/ko/my_company.php"; // 내 회사 정보
$link_0602 = "/ko/my_matching_r.php"; // 매칭 히스토리
$link_0603 = "/ko/my_interested.php"; // 관심기업
$link_0604 = G5_BBS_URL."/member_confirm.php?url=".G5_BBS_URL."/register_form.php";
//else $link_0604 = "/ko/my_info.php"; // 회원 정보 수정
$link_0605 = "/ko/my_plan.php"; // 결제정보
$link_0606 = "/ko/my_selected.php"; // 마이 셀렉티드

/* etc */
$link_1201 = "/etc/clause.php";						/* 이용약관 */
$link_1202 = "/etc/privacy.php";						/* 개인정보처리방침 */
$link_1203 = "/etc/email.php";						/* 이메일무단수집거부 */
$link_1204 = "/etc/refund.php";						/* 환불규정 */

/* ADMINISTRATOR */
$link_9901 = "/bbs/board.php?bo_table=mainVisual";						/* 비쥬얼관리 */
$link_9801 = "/bbs/search.php";						/* 전체 검색 */
$link_9701 = "/bbs/new.php";						/* 새글 */
?>

<script type="text/javascript">
	var depth_01 = 0 ,depth_02 = 0 ,depth_03 = 0 ,depth_04 = 0 ;
	var tit_1="",tit_2="", tit_3="", tit_4="";
	var menu_ca = getParam["sca"];
	var menuFull = [
		 ["01","매커멕스란", "<?=$link_0101?>", "", "", "one"]
			,["0101","매커멕스란", "<?=$link_0101?>", "매커멕스란", "", ""]
		
		,["04","멤버쉽 요금제", "<?=$link_0401?>", "", "", "one"]
			,["0401","멤버쉽 요금제", "<?=$link_0401?>", "멤버쉽 요금제", "", ""]
		
		,["05","이용자 지원", "<?=$link_0501?>", "", "", ""]
			,["0501","공지사항", "<?=$link_0501?>", "이용자 지원", "매커멕스 이용자들에게 알려드리는 공지사항입니다. ", ""]
			,["0502","자주 묻는 질문", "<?=$link_0502?>", "이용자 지원", "자주 질문해주시는 내용을 모아 보다 편리하게 궁금증을 알아볼 수 있습니다.", ""]
			,["0503","고객문의", "<?=$link_0503?>", "이용자 지원", "서비스 이용 중 FAQ로 해결하기 힘든 궁금 하신 부분이 있으신 경우, 아래의 폼에 내용을 입력하여 문의해주세요.", ""]
        ,["06","마이페이지", "<?=$link_0601?>", "", "", ""]
			,["0601","내 회사 정보", "<?=$link_0601?>", "내 회사 정보", "내 회사 정보입니다.", ""]
			,["0602","매칭 히스토리", "<?=$link_0602?>", "매칭 히스토리", "매칭 기업입니다.", ""]
			,["0603","관심기업", "<?=$link_0603?>", "관심기업", "관심기업입니다.", ""]
			,["0604","회원정보수정", "<?=$link_0604?>", "회원정보수정", "기본 정보 * 표시는 필수 입력 사항입니다.", ""]
			,["0605","결제정보", "<?=$link_0605?>", "결제정보", "이용 상품 안내입니다.", ""]
			,["0606","셀렉티드", "<?=$link_0606?>", "셀렉티드", "이용 상품 안내입니다.", ""]



		,["10","멤버쉽", "<?=$link_1001?>", "", "", ""]
			,["1001","로그인", "<?=$link_1001?>", "", "", ""]
			,["1002","회원가입", "<?=$link_1002?>", "", "", ""]


		,["12","고객센터", "<?=$link_1201?>", "", "", ""]
			,["1201","이용약관", "<?=$link_1201?>", "고객센터", "이용약관", ""]
			,["1202","개인정보처리방침", "<?=$link_1202?>", "고객센터", "개인정보활용동의서 및 처리방침", ""]



		,["97","ADMINISTRATOR", "<?=$link_9701?>", "", "", ""]
			,["9701","새글", "<?=$link_9701?>", "", "", ""]
		,["98","ADMINISTRATOR", "<?=$link_9801?>", "", "", ""]
			,["9801","전체검색", "<?=$link_9801?>", "", "", ""]
		,["99","ADMINISTRATOR", "<?=$link_9901?>", "", "", ""]
			,["9901","메인 비쥬얼", "<?=$link_9901?>", "", "", ""]
	];
	function replaceAll(str, searchStr, replaceStr) {
		return str.split(searchStr).join(replaceStr);
	}
	//@Page Navi셋팅
	function findMenuName(cd){
		var menuName = '';
		for(var i=0; i < menuFull.length; i++){
			if(menuFull[i][0]==cd){
				menuName = replaceAll(menuFull[i][1],"<br />","");
				break;
			}
		}
		return menuName;
	}
	function globalNavigation(pcd){
		if(!pcd)return;
		var navi = '';
		var lastNaviNum = parseInt(pcd.length / 2,10);
		for(var y=1; y <= lastNaviNum; y++){
			mcd = pcd.substr(0,y*2);
			if(findMenuName(mcd)){
				if(y!=lastNaviNum){
					navi +='<span>'+findMenuName(mcd)+'</span>';
				}else{
					navi +='<span class="last">'+findMenuName(mcd)+'</span>';
				}
			}
		}

		$("#pageTop>.pageNavi").html('<span class="home">Home</span>'+navi);
	}
	// GNB
	function globalNavigationBar(){
		var tmp = "";
		var tmp2 = "";
		var tmp3 = "";
		var tmp4 = "";
		var result = "";
		var blank = "";
		for(var i=0; i < menuFull.length; i++){
			if(menuFull[i][0].length==2){
				if(menuFull[i][0] < "09"){
					if(i != 0 && tmp != menuFull[i][0]){
						result += '</ul></li>';
					}

					result += '<li class="'+menuFull[i][5]+'"><a href="'+menuFull[i][2]+'" '+blank+'><span>' + menuFull[i][1] +'</span><i></i></a>';
					tmp = menuFull[i][0];
				}
			}else if(menuFull[i][0].length==4){
				if(menuFull[i][0] < "09"){
					if(tmp2 == "" || (tmp2.substring(0,2) != menuFull[i][0].substring(0,2))){
						result += '<ul class="gnbSub d2">';
					}

					result += '<li><a href="'+menuFull[i][2]+'" '+blank+'><span>' + menuFull[i][1] +'</span></a>';
					tmp2 = menuFull[i][0];
					if(tmp2.substring(0,2) != menuFull[i][0].substring(0,2)){
						result += '</ul>';
					}
				}
			}
		}
		document.write(result);
	}
	//
	function globalLinkManager(){
		var tmp = "";
		var tmp2 = "";
		var tmp3 = "";
		var tmp4 = "";
		var result = "";
		for(var i=0; i < menuFull.length; i++){
			if(menuFull[i][0].length==2){
				if(tmp3 != ""){
					if(tmp3.substring(0,2) != menuFull[i][0].substring(0,2)){
						result += '</ul>';
					} else {
						if(i != 0 && tmp != menuFull[i][0]){
							result += '</ul></li>';
						}
					}
					tmp3 = "";
				}
				if(tmp4 != ""){
					if(tmp4.substring(0,2) != menuFull[i][0].substring(0,2)){
						result += '</ul>';
					} else {
						if(i != 0 && tmp != menuFull[i][0]){
							result += '</ul></li>';
						}
					}
					tmp4 = "";
				}

				if(i != 0 && tmp != menuFull[i][0]){
					result += '</ul></li>';
				}

				result += '<li><a href="javascript:void(0);" class="dTit" data-num="'+menuFull[i][0]+'" data-link="'+menuFull[i][2]+'" data-state="'+menuFull[i][5]+'"><span>' + menuFull[i][1] +'</span></a>';
				tmp = menuFull[i][0];
			}else if(menuFull[i][0].length==4){
				if(tmp3 != ""){
					if(tmp3.substring(0,4) != menuFull[i][0].substring(0,4)){
						result += '</ul>';
					}
					tmp3 = "";
				}
				if(tmp4 != ""){
					if(tmp4.substring(0,4) != menuFull[i][0].substring(0,4)){
						result += '</ul>';
					}
					tmp4 = "";
				}
				if(tmp2 == "" || (tmp2.substring(0,2) != menuFull[i][0].substring(0,2))){
					result += '<ul>';
				}
				result += '<li><a href="javascript:void(0);" class="dTit" data-num="'+menuFull[i][0]+'" data-link="'+menuFull[i][2]+'" data-state="'+menuFull[i][5]+'"><span>' + menuFull[i][1] +'</span></a>';
				tmp2 = menuFull[i][0];
				if(tmp2.substring(0,2) != menuFull[i][0].substring(0,2)){
					result += '</ul>';
				}
			}else if(menuFull[i][0].length==6){
				if(tmp4 != ""){
					if(tmp4.substring(0,6) != menuFull[i][0].substring(0,6)){
						result += '</ul>';
					}
					tmp4 = "";
				}
				if(tmp3 == "" || (tmp3.substring(0,4) != menuFull[i][0].substring(0,4))){
					result += '<ul>';
				}
				result += '<li><a href="javascript:void(0);" class="dTit" data-num="'+menuFull[i][0]+'" data-link="'+menuFull[i][2]+'" data-state="'+menuFull[i][5]+'"><span>' + menuFull[i][1] +'</span></a>';
				tmp3 = menuFull[i][0];
				if(tmp3.substring(0,4) != menuFull[i][0].substring(0,4)){
					result += '</ul>';
				}
			}else if(menuFull[i][0].length==8){
				if(tmp4 == "" || (tmp4.substring(0,6) != menuFull[i][0].substring(0,6))){
					result += '<ul>';
				}
				result += '<li><a href="javascript:void(0);" class="dTit" data-num="'+menuFull[i][0]+'" data-link="'+menuFull[i][2]+'" data-state="'+menuFull[i][5]+'"><span>' + menuFull[i][1] +'</span></a>';
				tmp4 = menuFull[i][0];
				if(tmp4.substring(0,6) != menuFull[i][0].substring(0,6)){
					result += '</ul>';
				}
			}
		}
		document.write(result);
	}
	//@ 메뉴 리스트
	var pcd;
	function leftMenuList(dn1, dn2, dn3, dn4){
		if(dn1 < 10 && dn1){if(dn1 != 0){dn1 = "0"+dn1;}else{dn1 = ""}}
		if(dn2 < 10 && dn2){if(dn2 != 0){dn2 = "0"+dn2;}else{dn2 = ""}}
		if(dn3 < 10 && dn3){if(dn3 != 0){dn3 = "0"+dn3;}else{dn3 = ""}}
		if(dn4 < 10 && dn4){if(dn4 != 0){dn4 = "0"+dn4;}else{dn4 = ""}}

		pcd = dn1+dn2+dn3+dn4;

		//alert(dn1+"__"+dn2+"__"+dn3+"__"+dn4+"__"+pcd)
		//alert(depth_01+"__"+depth_02+"__"+depth_03+"__"+depth_04+"__"+pcd)

		// $("ul#nav").prepend('<li class="home"><a href="javascript:link0000();">H</a></li>');
		$("#pageTop .pageTop_nav").prepend('<li class="home"><a href="<?=$link_0000?>">홈</a></li>');

		if(!pcd)return;
		var strMainOn = '';
		var strMainOff = '';
		var strSubOn = '';
		var strSubOff = '';
		var str3dpthOn = '';
		var str3dpthOff = '';
		var str4dpthOn = '';
		var str4dpthOff = '';
		for(var i=0; i < menuFull.length; i++){
			// 원뎁스
			if(menuFull[i][0].length==2){
				mCss="";
				if(menuFull[i][0] == pcd.substr(0,menuFull[i][0].length)) mCss='_on';
				var opens = (mCss) ? "active" : ""; //@ Left 현재 메뉴 고정

				if(menuFull[i][0] == pcd.substr(0,menuFull[i][0].length)){
					strMainOn = '<li class="sMenu sDepth01 '+menuFull[i][5]+'"><a href="'+menuFull[i][2]+'"><span>' + menuFull[i][1] + '</span></a><div class="lnbSub">';
				}

				if(menuFull[i][0] < "09"){
					strMainOff += '<a href="'+menuFull[i][2]+'" class="'+opens+'"><span>' + menuFull[i][1] + '</span></a>';
				}
			}

			// 투뎁스
			if(menuFull[i][0].length==4 && menuFull[i][0].substr(0,2) == pcd.substr(0,2)){
				if(menuFull[i][0].length==4){
					mCss="";
					if(menuFull[i][0] == pcd.substr(0,menuFull[i][0].length)) mCss='_on';
					var opens = (mCss) ? "active" : ""; //@ Left 현재 메뉴 고정

					//@ 메뉴 연동일때
					if(menuFull[i][0].substr(0,2) == "05" && menuFull[i][0] < "05<?=$menu_cnt<10?"0{$menu_cnt}":$menu_cnt?>"){
						//console.log(menu_ca, menuFull[i][1], menuFull[i][0].substr(0,2), menuFull[i][0], "05<?=$menu_cnt<10?"0{$menu_cnt}":$menu_cnt?>")

						if(menu_ca != undefined){
							menu_ca = replaceAll(menu_ca,"+"," ");
						}

						if(menu_ca == menuFull[i][1]){
							opens = "active";
						}else{
							opens = "";
						}
						if(menu_ca == undefined){
							menu_ca = "전체"
						}

						if(menuFull[i][0] == pcd.substr(0,menuFull[i][0].length)){
							strSubOn = '<li class="sMenu sDepth02" data-class="'+menuFull[i][4]+'"><a href="'+menuFull[i][2]+'"><span>' + menu_ca + '</span><i></i></a><div class="lnbSub">';
						}

						strSubOff += '<a href="'+menuFull[i][2]+'" class="'+opens+'"><span>' + menuFull[i][1] + '</span></a>';
					}else{//@ 메뉴 연동 아닐때
						if(menuFull[i][0] == pcd.substr(0,menuFull[i][0].length)){
							strSubOn = '<li class="sMenu sDepth02" data-class="'+menuFull[i][4]+'"><a href="'+menuFull[i][2]+'"><span>' + menuFull[i][1] + '</span><i></i></a><div class="lnbSub">';
						}
						strSubOff += '<a href="'+menuFull[i][2]+'" class="'+opens+'"><span>' + menuFull[i][1] + '</span></a>';
					}
				}
			}

			// 쓰리뎁스
			if(menuFull[i][0].length==6 && menuFull[i][0].substr(0,2) == pcd.substr(0,2) && menuFull[i][0].substr(0,4) == pcd.substr(0,4)){
				if(menuFull[i][0].length==6){
					mCss="";
					if(menuFull[i][0] == pcd.substr(0,menuFull[i][0].length)) mCss='_on';
					var opens = (mCss) ? "active" : ""; //@ Left 현재 메뉴 고정

					if(menuFull[i][0] == pcd.substr(0,menuFull[i][0].length)){
						str3dpthOn = '<li class="sMenu sDepth03"><a href="javascript:void(0);"><span>' + menuFull[i][1] + '</span><i></i></a><div class="lnbSub">';
					}
					str3dpthOff += '<a href="'+menuFull[i][2]+'" class="'+opens+'"><span>' + menuFull[i][1] + '</span></a>';
				}
			}

			// 포뎁스
			if(menuFull[i][0].length==8 && menuFull[i][0].substr(0,2) == pcd.substr(0,2) && menuFull[i][0].substr(0,6) == pcd.substr(0,6)){
				if(menuFull[i][0].length==8){
					mCss="";
					if(menuFull[i][0] == pcd.substr(0,menuFull[i][0].length)) mCss='_on';
					var opens = (mCss) ? "active" : ""; //@ Left 현재 메뉴 고정

					if(menuFull[i][0] == pcd.substr(0,menuFull[i][0].length)){
						str4dpthOn = '<li class="sMenu sDepth04"><a href="javascript:void(0);"><span>' + menuFull[i][1] + '</span><i></i></a><div class="lnbSub">';
					}
					str4dpthOff += '<a href="'+menuFull[i][2]+'" class="'+opens+'"><span>' + menuFull[i][1] + '</span></a>';
				}
			}
		}
		document.write(strMainOn+strMainOff+'</li>');
		document.write(strSubOn+strSubOff+'</li>');
		document.write(str3dpthOn+str3dpthOff+'</li>');
		document.write(str4dpthOn+str4dpthOff+'</li>');

		setTimeout(function(){
			depth(pcd);
			//globalNavigation(pcd)
		}, 0);
	}

	var sVisualTitle = "";
	var pageTopTitle = "";
	var pageTopText = "";
	function depth(pcd){
		depth_01 = $(".pageTop_nav .sDepth01 .lnbSub a.active").index();
		depth_02 = $(".pageTop_nav .sDepth02 .lnbSub a.active").index();
		depth_03 = $(".pageTop_nav .sDepth03 .lnbSub a.active").index();
		depth_04 = $(".pageTop_nav .sDepth04 .lnbSub a.active").index();

		if(pcd.length > 2 || pcd.length == 2){
			var tcd_01 = pcd.substr(0,2);
			for(var i=0; i < menuFull.length; i++){
				if(menuFull[i][0].length==2 && menuFull[i][0] == tcd_01){
					break;
				}
			}
			tit_1 = menuFull[i][1];

			h2_title = menuFull[i][1];
		}
		//////////////////////////////////////////////////////////////
		if(pcd.length > 2 || pcd.length == 4){
			var tcd_02 = pcd.substr(0,4);
			for(var i=0; i < menuFull.length; i++){
				if(menuFull[i][0].length==4 && menuFull[i][0] == tcd_02){
					break;
				}
			}
			tit_1 = tit_1+" &gt; ";
			tit_2 = menuFull[i][1];

			pageTopTitle = menuFull[i][1];

            sVisualTitle = menuFull[i][3];
            pageTopText = menuFull[i][4]

            setTimeout(() => {
                $("#pageTop h3").text(pageTopTitle)
                $("#pageTop p").text(pageTopText)

				if(pageTopText == ""){
					$("#pageTop p").remove();
				}
            }, 20);
		}
		//////////////////////////////////////////////////////////////
		if(pcd.length > 4 || pcd.length == 6){
			var tcd_03 = pcd.substr(0,6);
			for(var i=0; i < menuFull.length; i++){
				if(menuFull[i][0].length==6 && menuFull[i][0] == tcd_03){
					break;
				}
			}
			tit_2 = tit_2+" &gt; ";
			tit_3 = menuFull[i][1];

			pageTopTitle = menuFull[i][5];
		}
		//////////////////////////////////////////////////////////////

        $("#sVisual strong").html(sVisualTitle)
        // $("#sVisual span").html(sVisualText)

		/*
		var datapgTitle = $("body").attr("data-pgTitle");
		if(datapgTitle != "" && datapgTitle != undefined){
			$("#pageTop").html("<h3>"+datapgTitle+"</h3>");
		}else{
			$("#pageTop").html("<h3>"+pgt+"</h3>");
		}

		var newMenu = $("#snb .sDepth02 .lnbSub").html();
		if(newMenu){
			$("#snbPc").html('<div class="inConts">'+newMenu+'</div>');
			$(".snbMobileSub").html(newMenu);
		}else{
			$("#snbPc").hide();
		}
		*/
		//alert(tit_1 +"__"+ tit_2 +"__"+ tit_3 )


		if(menu_ca != undefined){
			$("#pageTop h3").html(replaceAll(menu_ca,"+"," "));
		}
		$("head title").html(tit_1 + tit_2 + tit_3 +" | "+siteTitle);

		if(depth_01 != -1){
			$("#pcMenu #list>li:eq("+depth_01+")").addClass("oPage");
			if(depth_02 != -1){
				$("#pcMenu #list>li:eq("+depth_01+") .gnbSub.d2>li:eq("+depth_02+")").addClass("oPage");
				if(depth_03 != -1){
					$("#pcMenu #list>li:eq("+depth_01+") .gnbSub.d2>li:eq("+depth_02+") .gnbSub.d3>li:eq("+depth_03+")").addClass("oPage");
				}
			}

			$(".all_menu > li:eq("+depth_01+")").addClass("oPage");
			if(depth_02 != -1){
				$(".all_menu > li:eq("+depth_01+") .gnbSub.d2>li:eq("+depth_02+")").addClass("oPage");
				if(depth_03 != -1){
					$(".all_menu > li:eq("+depth_01+") .gnbSub.d2>li:eq("+depth_02+") .gnbSub.d3>li:eq("+depth_03+")").addClass("oPage");
				}
			}

			$("#menu>li:eq("+depth_01+")").addClass("oPage");
			if(depth_02 != -1){
				$("#menu>li:eq("+depth_01+") .gnbSub.d2>li:eq("+depth_02+")").addClass("oPage");
				if(depth_03 != -1){
					$("#menu>li:eq("+depth_01+") .gnbSub.d2>li:eq("+depth_02+") .gnbSub.d3>li:eq("+depth_03+")").addClass("oPage");
				}
			}
		}
	}
</script>