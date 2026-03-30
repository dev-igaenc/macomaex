<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

include_once(G5_LIB_PATH.'/thumbnail.lib.php');
// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_stylesheet('<link rel="stylesheet" href="'.$latest_skin_url.'/style.css">', 0);
//add_javascript('<script src="'.G5_JS_URL.'/jquery.bxslider.js"></script>', 10);
?>

<?php for ($i=0; $i<count($list); $i++) {  ?>
	<?php $sql = " select bf_file from $g5[board_file_table] where bo_table = '". $bo_table. "' and wr_id = '". $list[$i][wr_id] ."' order by '".$list[$i][wr_datetime]."' asc "; ?>

	<?php $files=get_file($bo_table,$list[$i]['wr_id'])?>

	<?php if($files['count'] > 0) {
		$_SESSION["ss_view_{$bo_table}_{$list[$i]['wr_id']}"]=true;

		for ($i=0; $i<=$files['count']-1; $i++) {
	?>
		<img src="<?=$files[$i]['path']; ?>/<?=$files[$i]['file']; ?>" alt="<?=$files[$i]['source']; ?>">
	<?php  } } ?>
<?php }  ?>