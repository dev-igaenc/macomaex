<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

include_once(G5_LIB_PATH.'/thumbnail.lib.php');
// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_stylesheet('<link rel="stylesheet" href="'.$latest_skin_url.'/style.css">', 0);
//add_javascript('<script src="'.G5_JS_URL.'/jquery.bxslider.js"></script>', 10);
?>

<?php for ($i=0; $i<count($list); $i++) {  ?>
	<?php
		$sql = " select bf_file from $g5[board_file_table] where bo_table = '". $bo_table. "' and wr_id = '". $list[$i][wr_id] ."' order by '".$list[$i][wr_datetime]."' asc ";
		$result = sql_query($sql);
		$imgSrc = array();
		for($k=0; $k<$data=sql_fetch_array($result); $k++)  {
			$imgSrc[$k] = $data['bf_file'];
		}
	?>
	<li>
		<a href="/bbs/board.php?bo_table=<?=$bo_table?>&wr_id=<?=$list[$i]['wr_id']?>" class="gall_cont">
			<div class="thumb"><img src="/theme/prix/img/thumb/thumb_270x270.png" alt=""><!-- 고정 -->
				<img src="/data/file/<?=$bo_table?>/<?=$imgSrc[0]?>" class="thb" alt=""><p class="over"><span>view</span></p>
			</div>
			<div class="area"><div class="in_area">
				<p class="tt cate">[<?=$list[$i]['ca_name']?>]</p>
				<div class="title__area">
					<div class="tt title two"><?=$list[$i]['subject']?></div>
				</div>

				<div class="tt txt two"><?=str_replace('&nbsp;','',strip_tags($list[$i]['wr_content']));?></div>
				<div class="tt day"><?php echo date("Y.m.d", strtotime($list[$i]['datetime']));?></div>
			</div></div>
		</a>
	</li>
<?php }  ?>

<?php if (count($list) == 0) { //게시물이 없을 때  ?>
	<li class="noPost"><p>등록된 게시글이 없습니다.</p></li>
<?php }  ?>
