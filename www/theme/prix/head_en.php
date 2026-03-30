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
?>

<!-- 상단 시작 { -->
<div id="hd">
	<h1 id="hd_h1"><?php echo $g5['title'] ?></h1>
	<div id="skip_to_container"><a href="#container">본문 바로가기</a></div>

	<?php if(defined('_INDEX_')) { // index에서만 실행
		include G5_BBS_PATH.'/newwin.inc.php'; // 팝업레이어
	} ?>

	<div id="header" <?php if (!defined("_INDEX_")) { ?>class="sub"<?php } ?>><!-- <p class="gnbBg"></p> --><div class="inConts">
		<h1><a href="<?php echo G5_URL ?>"><img src="<?php echo G5_IMG_URL ?>/logo.png" alt="<?php echo $config['cf_title']; ?>"></a></h1>

		<div id="pcMenu">
			<ul id="list">
				<script type="text/javascript">globalNavigationBar();</script>
				<?php // include(G5_THEME_PATH.'/menu.php'); ?>
			</ul>
		</div>

		<div class="right-utill">
			<form name="fsearchbox" method="get" action="<?php echo G5_BBS_URL ?>/search.php" onsubmit="return fsearchbox_submit(this);">
				<input type="hidden" name="sfl" value="wr_subject||wr_content">
				<input type="hidden" name="sop" value="and">

				<div class="tSchArea">
					<div class="tSch_in">
						<input type="text" name="stx" id="sch_stx" value="<?php echo $stx;?>" placeholder="검색어를 입력해주세요!"/>
						<button type="submit" class="tSch"></button>
					</div>
					<button type="button" class="tSch"></button>
				</div>
			</form>

			<div class="language-area">
				<div class="language">
					<a href="javascript:void(0);"><span>ENG</span></a>
					<ul>
						<li><a href="/"><span>KOR</span></a></li><!-- /index_en.php -->
						<li><a href="/index_cn.php"><span>CHN</span></a></li><!-- /index_cn.php -->
						<li><a href="/index_jp.php"><span>JPN</span></a></li><!-- /index_jp.php -->
					</ul>
				</div>
			</div>

			<button type="button" class="mMenu"><span></span></button>
			<!--
			<div class="tUtill<?php if ($is_member) { ?> on<?php } ?>">
				<?php if ($is_member) { ?>
					<a href="<?php echo G5_BBS_URL ?>/member_confirm.php?url=<?php echo G5_BBS_URL ?>/register_form.php">My page</a>
					<a href="<?php echo G5_BBS_URL; ?>/logout.php" class="">Logout</a>
				<?php } else { ?>
					<a href="<?php echo G5_URL ?>/bbs/login.php?url=<?php echo G5_URL . $_SERVER['REQUEST_URI'] ?>" class="login">Login</a>
					<a href="<?php echo G5_URL ?>/bbs/register.php" class="">Join</a>
				<?php } ?>
			</div>
			-->
		</div>
	</div></div>

	<div id="menuArea" class="menuLayer">
		<!-- <div class="language">
			<a href="/" class="active"><span>KOR</span></a>
			<a href="/index_en.php"><span>ENG</span></a>
		</div> -->
		<ul id="menu" class="gnb">
			<script type="text/javascript">globalNavigationBar();</script>
			<?php //include(G5_THEME_PATH.'/menu.php'); ?>
		</ul>
	</div>
</div>
<!-- } 상단 끝 -->

<hr>

<!-- 콘텐츠 시작 { -->
<div id="wrapper">
	<?php if (!defined("_INDEX_")) { ?>
	<div id="container_wr">
		<div id="sVisual" class="bg_<?=$depth_1?>">
			<h2>
				<strong data-aos="fade-down"><?=$gr_subject?></strong>
				<span data-aos="fade-up" data-aos-delay="100"><?=$gr_text?></span>
			</h2>
		</div>

		<div id="snb" <?php if($board['bo_7'] != ""){ ?>class="four"<?php }?>><div class="inConts">
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
		</div></div>
		<div id="pageTop">
			<h3><?php echo $bo_subject ?></h3>
			<?php if($bo_text != ""){?>
				<p><?php echo $bo_text ?></p>
			<?php } ?>

			<!-- <div class="pageNavi"></div> -->
		</div>
		<div id="container">
	<?php } ?>
