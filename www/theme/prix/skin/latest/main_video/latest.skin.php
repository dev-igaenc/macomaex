<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');
$thumb_width = 305;
$thumb_height = 203;
$list_count = (is_array($list) && $list) ? count($list) : 0;
?>

    <?php
    for ($i=0; $i<$list_count; $i++) {

        $img_link_html = '';
        $wr_href = get_pretty_url($bo_table, $list[$i]['wr_id']);

		$thumb = get_list_thumbnail($bo_table, $list[$i]['wr_id'], $thumb_width, $thumb_height, false, true);
		if(isset($thumb['src']) && $thumb['src']) {
			$img = $thumb['ori'];
		} else {
			$img = G5_IMG_URL.'/no_img.png';
			$thumb['alt'] = '이미지가 없습니다.';
		}
		$img_content = '<img src="'.$img.'" alt="'.$thumb['alt'].'" >';
		$img_link_html = '<a href="'.$wr_href.'" class="lt_img" >'.run_replace('thumb_image_tag', $img_content, $thumb).'</a>';

    ?>
                <li><a href="/bbs/<?=$wr_href?>#pageTop">
                    <div class="thumb"><?=$img_content?></div>
                    <div class="info">
                        <p class="title"><?=$list[$i]['subject'];?></p>
                        <p class="date"><?php echo $list[$i]['datetime'] ?></p>
                    </div>
                </a></li>
    <?php }  ?>
    <?php if ($list_count == 0) { //게시물이 없을 때  ?>
                <li><a href="<?=$wr_href?>">
                    <div class="thumb"><?=$img_content?></div>
                    <div class="info">
                        <p class="title">자료가 없습니다.</p>
                        <p class="date"></p>
                    </div>
                </a></li>
    <?php }  ?>
