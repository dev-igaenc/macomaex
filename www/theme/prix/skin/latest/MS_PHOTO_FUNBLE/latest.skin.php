<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

include_once(G5_LIB_PATH.'/thumbnail.lib.php');
// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_stylesheet('<link rel="stylesheet" href="'.$latest_skin_url.'/style.css">', 0);
//add_javascript('<script src="'.G5_JS_URL.'/jquery.bxslider.js"></script>', 10);

function ConvertLineFeed($cVal, $tag){
	$tmpContent = "";

	If ($cVal!=""){
		//$cVal = nl2br($cVal);

		$arrVal = explode("\r\n", $cVal);
		for($i=0;$i< count($arrVal);$i++){
			If($arrVal[$i]!=""){
				$tmpContent = $tmpContent."<".$tag.">".$arrVal[$i]."</".$tag.">";
			}
		}
		return $tmpContent;
	}
}
?>

<?php for ($i=0; $i<count($list); $i++) {  ?>
	<?php
		$sql = " select bf_file from $g5[board_file_table] where bo_table = '". $bo_table. "' and wr_id = '". $list[$i][wr_id] ."' order by '".$list[$i][wr_datetime]."' asc ";
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
		<a href="javascript:void(0);" class="ray">
			<div class="thumb"><img src="/theme/prix/img/thumb/thumb_290.png" alt=""><!-- 290*290 -->
				<img src="<?=$imgUrl?>" class="ofi thb" alt="">
				<div class="ray_cont"><div class="ray_cont_in">
					<ul>
						<?php echo ConvertLineFeed($list[$i]['wr_2'], "li") ?>
					</ul>
				</div></div>
			</div>
			<div class="area">
				<p class="tit"><?php echo $list[$i]['subject'] ?></p>
				<p class="txt en"><?=$list[$i]['wr_1']?></p>
			</div>
		</a>
	</li>
<?php }  ?>

<?php if (count($list) == 0) { //게시물이 없을 때  ?>
	<li class="noPost"><p>등록된 게시글이 없습니다.</p></li>
<?php }  ?>
