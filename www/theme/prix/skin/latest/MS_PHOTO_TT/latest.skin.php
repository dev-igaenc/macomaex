<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

include_once(G5_LIB_PATH.'/thumbnail.lib.php');
// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_stylesheet('<link rel="stylesheet" href="'.$latest_skin_url.'/style.css">', 0);
//add_javascript('<script src="'.G5_JS_URL.'/jquery.bxslider.js"></script>', 10);
?>

<?php for ($i=0; $i<count($list); $i++) {  ?>
	<?php
		$sql = " select bf_file from {$g5['board_file_table']} where bo_table = '". $bo_table. "' and wr_id = '". $list[$i]['wr_id'] ."' order by '".$list[$i]['wr_datetime']."' asc ";
		$result = sql_query($sql);
		$imgSrc = array();
		for($k=0; $k<$data=sql_fetch_array($result); $k++)  {
			$imgSrc[$k] = $data['bf_file'];
		}

		if($imgSrc[0] == ""){
			$imgUrl = "/img/noimage.jpg";
		}else{
			$imgUrl = "/data/file/".$bo_table."/".$imgSrc[0];
		}
	?>
	<li>
		<a href="/bbs/board.php?bo_table=<?=$bo_table?>&wr_id=<?=$list[$i]['wr_id']?>" class="gall_cont">
			<div class="thumb"><img src="/theme/prix/img/thumb/thumb_298x298.png" class="ib" alt="" />
				<img src="<?=$imgUrl?>" class="thb" alt="">
			</div>
			<div class="area">
				<div class="in_area">
					<p class="title"><?=$list[$i]['subject']?></p>
					<p class="txt two"><?=str_replace('&nbsp;','',strip_tags($list[$i]['wr_content']));?></p>
				</div>
			</div>
		</a>
	</li>
<?php }  ?>

<?php if (count($list) == 0) { //게시물이 없을 때  ?>
	<li class="noPost"><p>등록된 게시글이 없습니다.</p></li>
<?php }  ?>
