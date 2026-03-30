<?php
$brand_borad=sql_fetch("select * from {$g5['board_table']} where bo_table='menu_adm'");
$brand_category=explode("|",$brand_borad['bo_category_list']);

/* 일반 게시판 */
$link_0101 = "/bbs/board.php?bo_table=notice_en"; // 공지사항
$link_0102 = "/bbs/board.php?bo_table=dataroom_en"; // 자료실
$link_0103 = "/bbs/board.php?bo_table=participation"; // 참여
$link_0104 = "/bbs/board.php?bo_table=qna_en"; // QNA
$link_0105 = "/bbs/board.php?bo_table=conmment_en"; // 자유게시판

/* 이미지 게시판 */
$link_0201 = "/bbs/board.php?bo_table=photo_portrait"; // 기본 (세로)
$link_0202 = "/bbs/board.php?bo_table=photo_landscape"; // 기본 (가로)
$link_0203 = "/bbs/board.php?bo_table=event_en"; // 이벤트
$link_0204 = "/bbs/board.php?bo_table=photo_popup"; // 인증서
$link_0205 = "/bbs/board.php?bo_table=img_only"; // 외부링크
$link_0206 = "/bbs/board.php?bo_table=video"; // 동영상
$link_0207 = "/bbs/board.php?bo_table=photo_down"; // 다운로드

/* 문의 게시판 */
$link_0301 = "/bbs/write.php?bo_table=contact"; //온라인 문의

/* 기타 */
$link_0401 = "/bbs/board.php?bo_table=faq"; // FAQ
$link_0402 = "/bbs/board.php?bo_table=store"; // 매장찾기
$link_0403 = "/bbs/board.php?bo_table=history"; // 연혁
$link_0404 = "/bbs/board.php?bo_table=store2"; // 매장찾기2

/* 메뉴연동 */
$link_0501 = "/bbs/board.php?bo_table=menu_adm"; // 메뉴연동



/* Member */
$link_1001 = "/bbs/login.php"; // 로그인
$link_1002 = "/bbs/register.php"; // 회원가입

/* Mypage */
$link_1101 = "/bbs/member_confirm.php?url=/bbs/register_form.php"; // 정보수정

/* etc */
$link_1201 = "/etc/clause.php";						/* 이용약관 */
$link_1202 = "/etc/privacy.php";						/* 개인정보보호정책 */
$link_1203 = "/etc/email.php";						/* 이메일무단수집거부 */

$link_9901 = "/bbs/board.php?bo_table=mVisual";						/* 비쥬얼관리 */
?>
<script type="text/javascript">
	var depth_01 = 0 ,depth_02 = 0 ,depth_03 = 0 ,depth_04 = 0 ;
	var tit_1="",tit_2="", tit_3="", tit_4="";
	var menuFull = [
		 ["01","EN 일반 게시판", "<?=$link_0101?>", "", "", "진행도"]
			,["0101","공지사항", "<?=$link_0101?>", "", "", "end"]
			,["0102","자료실", "<?=$link_0102?>", "", "", "end"]
			,["0103","참여", "<?=$link_0103?>", "", "", "end"]
			,["0104","QNA", "<?=$link_0104?>", "", "", "end"]
			,["0105","자유 게시판", "<?=$link_0105?>", "", "", "end"]
		,["02","이미지 게시판", "<?=$link_0201?>", "", "", "진행도"]
			,["0201","기본 (세로)", "<?=$link_0201?>", "", "", "진행도"]
				,["020101","기본 (세로)", "<?=$link_0201?>", "", "", "진행도"]
			,["0202","기본 (가로)", "<?=$link_0202?>", "", "", "진행도"]
			,["0203","이벤트", "<?=$link_0203?>", "", "", "진행도"]
			,["0204","인증서", "<?=$link_0204?>", "", "", "진행도"]
			,["0205","외부링크", "<?=$link_0205?>", "", "", "진행도"]
			,["0206","동영상", "<?=$link_0206?>", "", "", "진행도"]
			,["0207","파일다운", "<?=$link_0207?>", "", "", "진행도"]
		,["03","문의 게시판", "<?=$link_0301?>", "", "", "진행도"]
			,["0301","온라인 문의", "<?=$link_0301?>", "", "", "진행도"]
		,["04","기타", "<?=$link_0401?>", "", "", "진행도"]
			,["0401","FAQ", "<?=$link_0401?>", "", "", "진행도"]
			,["0402","매장찾기", "<?=$link_0402?>", "", "", "진행도"]
			,["0404","매장찾기2", "<?=$link_0404?>", "", "", "진행도"]
			,["0403","연혁", "<?=$link_0403?>", "", "", "진행도"]

		,["05","메뉴 연동", "<?=$link_0501?>", "", "", "진행도"]
			<?php
				$menu_cnt=1;
				foreach ($brand_category as $menu){?>
			,["05<?=$menu_cnt<10?"0{$menu_cnt}":$menu_cnt?>","<?=$menu?>", '/bbs/board.php?bo_table=menu_adm&sca=<?=$menu?>', "", "", "진행도"]
			<?php $menu_cnt++; } ?>

		,["10","Member", "<?=$link_1001?>", "", "", "진행도"]
			,["1001","로그인", "<?=$link_1001?>", "", "", "진행도"]
			,["1002","회원가입", "<?=$link_1002?>", "", "", "진행도"]

		,["11","Mypage", "<?=$link_1101?>", "", "", "진행도"]
			,["1101","정보수정", "<?=$link_1101?>", "", "", "진행도"]
			//,["1102","회원탈퇴", "javascript:link1102();", "", "", "진행도"]

		,["12","Etc", "<?=$link_1201?>", "", "", "진행도"]
			,["1201","이용약관", "<?=$link_1201?>", "", "", "진행도"]
			,["1202","개인정보처리방침", "<?=$link_1202?>", "", "", "진행도"]

		,["99","Etc", "<?=$link_9901?>", "", "", "진행도"]
			,["9901","메인 비쥬얼", "<?=$link_9901?>", "", "", "진행도"]
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
		for(var i=0; i < menuFull.length; i++){
			if(menuFull[i][0].length==2){
				if(menuFull[i][0] < "09"){
					if(i != 0 && tmp != menuFull[i][0]){
						result += '</ul></li>';
					}

					result += '<li class="'+menuFull[i][4]+'"><a href="'+menuFull[i][2]+'"><span>' + menuFull[i][1] +'</span><i></i></a>';
					tmp = menuFull[i][0];
				}
			}else if(menuFull[i][0].length==4){
				if(menuFull[i][0] < "09"){
					if(tmp2 == "" || (tmp2.substring(0,2) != menuFull[i][0].substring(0,2))){
						result += '<ul class="gnbSub d2">';
					}

					result += '<li><a href="'+menuFull[i][2]+'"><span>' + menuFull[i][1] +'</span></a>';
					tmp2 = menuFull[i][0];
					if(tmp2.substring(0,2) != menuFull[i][0].substring(0,2)){
						result += '</ul>';
					}
				}
			}
		}
		document.write(result);
	}
	// 진행도
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

		$("ul#nav").prepend('<li class="home"><a href="javascript:link0000();">home</a></li>');

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
					strMainOn = '<li class="sMenu sDepth01"><a href="javascript:void(0);"><span>' + menuFull[i][1] + '</span><i></i></a><div class="lnbSub">';
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

					if(menuFull[i][0] == pcd.substr(0,menuFull[i][0].length)){
						strSubOn = '<li class="sMenu sDepth02"><a href="javascript:void(0);"><span>' + menuFull[i][1] + '</span><i></i></a><div class="lnbSub">';
					}
					strSubOff += '<a href="'+menuFull[i][2]+'" class="'+opens+'"><span>' + menuFull[i][1] + '</span></a>';
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

	function depth(pcd){
		depth_01 = $("#snb #nav .sDepth01 .lnbSub a.active").index();
		depth_02 = $("#snb #nav .sDepth02 .lnbSub a.active").index();
		depth_03 = $("#snb #nav .sDepth03 .lnbSub a.active").index();
		depth_04 = $("#snb #nav .sDepth04 .lnbSub a.active").index();

		if(pcd.length > 2 || pcd.length == 2){
			var tcd_01 = pcd.substr(0,2);
			for(var i=0; i < menuFull.length; i++){
				if(menuFull[i][0].length==2 && menuFull[i][0] == tcd_01){
					break;
				}
			}
			tit_1 = menuFull[i][1];
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
		}
		//////////////////////////////////////////////////////////////

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
		$("head title").html(tit_1 + tit_2 + tit_3 +" | "+siteTitle);

		if(depth_01 != -1){
			$("#pcMenu #list>li:eq("+depth_01+")").addClass("oPage");
			$("#pcMenu #list>li:eq("+depth_01+") .gnbSub>li:eq("+depth_02+")").addClass("oPage");
			$("#pcMenu #list>li:eq("+depth_01+") .gnbSub>li:eq("+depth_02+") .d3Sub>li:eq("+depth_03+")").addClass("oPage");

			$("#menu>li:eq("+depth_01+")").addClass("oPage");
			$("#menu>li:eq("+depth_01+") .gnbSub>li:eq("+depth_02+")").addClass("oPage");
			$("#menu>li:eq("+depth_01+") .gnbSub>li:eq("+depth_02+") .d3Sub>li:eq("+depth_03+")").addClass("oPage");
		}
	}
</script>