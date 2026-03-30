<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_stylesheet('<link rel="stylesheet" href="'.$latest_skin_url.'/style.css">', 0);
//add_javascript('<script src="'.G5_JS_URL.'/jquery.bxslider.js"></script>', 10);

global $member;
?>

<?php for ($i=0; $i<count($list); $i++) {  ?>
	<li>
		<?php echo $member['mb_level']; ?>
		<?php if ( $list[$i]['wr_link1'] == "") { ?>
		<a href="<?php echo $list[$i]['href'] ?>" class="bo_cont">
		<?php }else{ ?>
			<?php if ($member['mb_level'] >= '9') {  ?>
			<a href="<?php echo $list[$i]['href'] ?>" class="bo_cont">
			<?php }else{ ?>
			<a href="<?php echo $list[$i]['wr_link1'] ?>" target="_blank" class="bo_cont">
			<?php } ?>
		<?php } ?>
		<!-- <a href="/bbs/board.php?bo_table=<?=$bo_table?>&wr_id=<?=$list[$i]['wr_id']?>" class="bo_cont"> -->
			<div class="etc">
				<p class="name"><?=$list[$i]['wr_9']?></p>
				<p class="day"><?php echo date("Y.m.d", strtotime($list[$i]['datetime']));?></p>
			</div>
			<div class="subject"><?=$list[$i]['subject']?></div>
		</a>
	</li>
<?php }  ?>

<?php if (count($list) == 0) { //게시물이 없을 때  ?>
	<li class="noPost"><p>등록된 게시글이 없습니다.</p></li>
<?php }  ?>
