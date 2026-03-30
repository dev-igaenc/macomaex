<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (G5_IS_MOBILE) {
    include_once(G5_THEME_MOBILE_PATH.'/head.php');
    return;
}

include_once(G5_THEME_PATH.'/head.sub.php');
include_once(G5_LIB_PATH.'/latest.lib.php');
include_once(G5_LIB_PATH.'/outlogin.lib.php');
include_once(G5_LIB_PATH.'/poll.lib.php');
include_once(G5_LIB_PATH.'/visit.lib.php');
include_once(G5_LIB_PATH.'/connect.lib.php');
include_once(G5_LIB_PATH.'/popular.lib.php');
//header (){Authorization: '[ ACCES TOKEN KEY ]'}
?>

<!-- 상단 시작 { -->
<div id="hd">
	<div id="hd_h1"><?php echo $g5['title'] ?></div>

	<?php if(defined('_INDEX_')) { // index에서만 실행
		include G5_BBS_PATH.'/newwin.inc.php'; // 팝업레이어
	} ?>

	<div id="header" class="hh"><!-- class="hh" --><p class="gnbBg"></p><div class="inConts">
		<h1><a href="<?php echo G5_URL ?>/index.php">
			<img src="/img/logo.svg" alt="<?php echo $config['cf_title']; ?>">
		</a></h1>

		<div id="pcMenu">
			<ul id="list">
				<script type="text/javascript">globalNavigationBar();</script>
				<?php // include(G5_THEME_PATH.'/menu.php'); ?>
                <!-- </li> -->
                <!-- <li>
                    <a href="<?=$link_1101?>"><span>마이페이지</span></a>
                    <ul class="gnbSub d2">
                        <li><a href="<?=$link_1101?>"><span>매칭 히스토리</span></a></li>
                        <li><a href="<?=$link_1102?>"><span>관심기업</span></a></li>
                        <li><a href="<?=$link_1103?>"><span>회원정보수정</span></a>
                        <li><a href="<?=$link_1104?>"><span>결제정보</span></a>
                    </li></ul>
                </li> -->
			</ul>
		</div>

		<div class="right-utill">
            <?php if (!$is_member) {  ?>
				<a href="<?php echo G5_BBS_URL ?>/login.php?url=<?=$_SERVER['REQUEST_URI']?>" class="menu_txt">로그인</a>
				<a href="<?php echo G5_BBS_URL ?>/register.php" class="menu_txt">회원가입</a>
			<?php }else{  ?>
				<a href="<?php echo G5_BBS_URL ?>/logout.php" class="menu_txt">로그아웃</a>
				<?if($member['mb_level']>9) {?>
				<a href="/backoffice/" class="menu_txt"><span>관리자</span></a>
				<?}else{?>
				<!-- <a href="<?php echo G5_BBS_URL ?>/member_confirm.php?url=/bbs/register_form.php" class="menu_txt"><span>마이페이지</span></a> -->
				<?}?>
			<?php }  ?>

			<button type="button" class="mMenu"><span></span></button>
		</div>
	</div></div>

    <div id="sideMenuArea">
        <ul id="sideMenu">
            <div class="sideMenu_top">
                <div class="logo"><img src="/img/logo.svg" alt=""></div>
                <button type="button" class="mMenu"><span></span></button>
            </div>

            <div class="all_user all_layout">
                <?php if (!$is_member) {  ?>
                    <li class="one"><a href="<?php echo G5_BBS_URL ?>/login.php?url=<?=$_SERVER['REQUEST_URI']?>" class="menu_txt">로그인</a></li>
                    <li class="one"><a href="<?php echo G5_BBS_URL ?>/register.php" class="menu_txt">회원가입</a></li>
                <?php }else{  ?>
                    <li class="one"><a href="<?php echo G5_BBS_URL ?>/logout.php" class="menu_txt">로그아웃</a></li>

                    <?if($member['mb_level']>9) {?>
                    <li><a href="/backoffice/" class="menu_txt"><span>관리자</span></a></li>
                    <?}else{?>
						<!--
                    <li>
                        <a href="#">마이페이지</a>
                        <ul class="gnbSub d2">
							<li><a href="<?=$link_0601?>"><span>내 회사 정보</span></a></li>
                            <li><a href="<?=$link_0602?>"><span>매칭 히스토리</span></a></li>
                            <li><a href="<?=$link_0603?>"><span>관심기업</span></a></li>
                            <li><a href="<?=$link_0604?>"><span>회원 정보 수정</span></a></li>
                            <li><a href="<?=$link_0605?>"><span>결제정보</span></a></li>
                        </ul>
                    </li>
					 -->
                    <?}?>
                <?php }  ?>
            </div>

			<div class="all_menu all_layout">
                <script type="text/javascript">globalNavigationBar();</script>
            </div>
        </ul>
    </div>

	<div id="menuArea" class="menuLayer">
		<div id="menu" class="gnb">
            <ul class="all_user all_layout">
                <?php if (!$is_member) {  ?>
                    <li class="one"><a href="<?php echo G5_BBS_URL ?>/login.php?url=<?=$_SERVER['REQUEST_URI']?>" class="menu_txt">로그인</a></li>
                    <li class="one"><a href="<?php echo G5_BBS_URL ?>/register.php" class="menu_txt">회원가입</a></li>
                <?php }else{  ?>
                    <li class="one"><a href="<?php echo G5_BBS_URL ?>/logout.php?url=<?=$_SERVER['REQUEST_URI']?>" class="menu_txt">로그아웃</a></li>

                    <?if($member['mb_level']>9) {?>
                    <li class="one"><a href="/backoffice/" class="menu_txt"><span>관리자</span></a></li>
                    <?}else{?>
                    <!-- <li><a href="<?php echo G5_BBS_URL ?>/member_confirm.php?url=/bbs/register_form.php" class="menu_txt"><span>마이페이지</span></a></li>
                    <li>
                        <a href="#">마이페이지</a>
                        <ul class="gnbSub d2">
                            <li><a href="<?=$link_1101?>"><span>매칭 히스토리</span></a></li>
                            <li><a href="<?=$link_1102?>"><span>관심기업</span></a></li>
                            <li><a href="<?=$link_1103?>"><span>회원 정보 수정</span></a></li>
                            <li><a href="<?=$link_1104?>"><span>결제정보</span></a></li>
                        </ul>
                    </li>
					 -->
                    <?}?>
                <?php }  ?>
            </ul>

			<ul class="all_menu all_layout">
                <script type="text/javascript">globalNavigationBar();</script>
            </ul>
			<?php //include(G5_THEME_PATH.'/menu.php'); ?>
		</div>
	</div>
</div>
<!-- } 상단 끝 -->

<hr>

<!-- 콘텐츠 시작 { -->
<div id="wrapper">
	<?php if (!defined("_INDEX_")) { ?>
	<div id="container_wr">


		<?php if($depth_1 == 3){ ?>
			<div id="sVisual" class="bg_<?=$depth_1?>">
				<h2>
					<strong data-aos="fade-down"><?=$gr_subject?></strong>
					<span data-aos="fade-up" data-aos-delay="100"><?=$gr_text?></span>
				</h2>

				<div class="matching mVisual_search bxShadow">
					<form action="/ko/matching_list.php" method="POST" id="match">
					<input type="hidden" name="cominfo" id="cominfo">
					</form>
					<input type="text" name="comnum" id="comnum" class="comnum_chk" placeholder="법인 사업자번호를 입력하세요" maxlength="12">
					<button type="submit" id="sub_submit">확인</button>
				</div>
			</div>
		<?php }else{ ?>
			<div id="sVisual" class="bg_<?=$depth_1?>">
				<h2>
					<strong data-aos="fade-down"><?=$gr_subject?></strong>
					<span data-aos="fade-up" data-aos-delay="100"><?=$gr_text?></span>
				</h2>
			</div>
		<?php } ?>

		<!-- <div id="snb" <?php if($board['bo_7'] != ""){ ?>class="four"<?php }?>><div class="inConts">
			<ul id="nav">
				<script type="text/javascript">
					var dn1 = "<?=$depth_1?>";
					var dn2 = "<?=$depth_2?>";
					var dn3 = "<?=$depth_3?>";
					var dn4 = "<?=$depth_4?>";

					//alert(dn1+"__"+dn2+"__"+dn3+"__"+dn4)
					leftMenuList(dn1, dn2, dn3, dn4);
				</script>
			</ul>
		</div></div> -->
		<div id="pageTop">
            <div class="inConts">
                <div class="pageTop_text">
                    <h3><?php echo $bo_subject ?></h3>
                    <p></p>
                    <!-- <?php if($bo_text != ""){?> -->
                        <!-- <p><?php echo $bo_text ?></p> -->
                    <!-- <?php } ?> -->
                </div>
                <div class="pageTop_nav">
                    <script type="text/javascript">
                        var dn1 = "<?=$depth_1?>";
                        var dn2 = "<?=$depth_2?>";
                        var dn3 = "<?=$depth_3?>";
                        var dn4 = "<?=$depth_4?>";

                        //alert(dn1+"__"+dn2+"__"+dn3+"__"+dn4)
                        leftMenuList(dn1, dn2, dn3, dn4);
                    </script>
                </div>
            </div>
		</div>
		<div id="container">
	<?php } ?>
	<script>
$("#sub_submit").click(function(){
	event.preventDefault();
	$(".comnum_chk").blur();
	comnum=$(this).prev().val();
	if(comnum.length!=12){
		jAlert("사업자등록번호를 확인해 주세요.","알림",function(){
			$("#comnum").focus();
		})
			return false;
	}

	$.post( "../get_set.php", {
		type: 'main_comp_chk',
		comnum:comnum
	})
	.done(function(jqXHR) {
			jqXHR=jqXHR.split("|");
			if(jqXHR[0]==1000){
				//완료시
				//jAlert(jqXHR[1],"알림");
				$("#cominfo").val(jqXHR[1]);
				$("#match").submit();
			}else{
				//오류시
				if(jqXHR[1]=="02") jAlert("검색 결과가 없습니다.","알림");
				else jAlert("검색에 오류가 있습니다.","알림");
			}

	});



})
//사업자번호
	$('.comnum_chk').on('blur', function(){
        var num = $(this).val();
        num.trim();  // 스페이스바 제거
        this.value = AutoHypen(num) ;
    });

function AutoHypen(companyNum){
      companyNum = companyNum.replace(/[^0-9]/g, '');
      var tempNum = '';

      if(companyNum.length < 4){
        return companyNum;
      }
      else if(companyNum.length < 6){
        tempNum += companyNum.substr(0,3);
        tempNum += '-';
        tempNum += companyNum.substr(3,2);
        return tempNum;
      }
      else if(companyNum.length < 11){
        tempNum += companyNum.substr(0,3);
        tempNum += '-';
        tempNum += companyNum.substr(3,2);
        tempNum += '-';
        tempNum += companyNum.substr(5);
        return tempNum;
      }
      else{
        tempNum += companyNum.substr(0,3);
        tempNum += '-';
        tempNum += companyNum.substr(3,2);
        tempNum += '-';
        tempNum += companyNum.substr(5);
        return tempNum;
      }
    }
</script>