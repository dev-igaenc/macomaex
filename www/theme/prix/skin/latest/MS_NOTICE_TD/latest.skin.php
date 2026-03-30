<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_stylesheet('<link rel="stylesheet" href="'.$latest_skin_url.'/style.css">', 0);
//add_javascript('<script src="'.G5_JS_URL.'/jquery.bxslider.js"></script>', 10);
?>

<?php for ($i=0; $i<count($list); $i++) {  ?>
	<li class="slider">
		<a href="/bbs/board.php?bo_table=<?=$bo_table?>&wr_id=<?=$list[$i]['wr_id']?>" class="gall_cont">
			<p class="title"><?=$list[$i]['subject']?></p>
			<p class="day"><?php echo date("Y.m.d", strtotime($list[$i]['datetime']));?></p>
		</a>
	</li>
<?php }  ?>

<?php if (count($list) == 0) { //게시물이 없을 때  ?>
	<li class="noPost"><p>등록된 게시글이 없습니다.</p></li>
<?php }  ?>
