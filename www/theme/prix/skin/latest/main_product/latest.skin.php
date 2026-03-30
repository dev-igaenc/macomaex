<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');
$thumb_width = 260;
$thumb_height = 260;
$list_count = (is_array($list) && $list) ? count($list) : 0;
?>

    <?php
    for ($i=0; $i<$list_count; $i++) {

        $img_link_html = '';
        $wr_href = G5_BBS_DIR."/".get_pretty_url($bo_table, $list[$i]['wr_id']);

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
                <div class="swiper-slide">
                    <div class="thumb"><a href="<?= $wr_href?>"><?=$img_content?></a></div>
                    <div class="txt">
						<a href="<?= $wr_href?>">
                        <b><?=$list[$i]['subject'];?></b>
                        <p>
                            <?=strip_tags($list[$i]['wr_3']);?>
                        </p>
						</a>
                    </div>
                </div>
    <?php }  ?>
    <?php if ($list_count == 0) { //게시물이 없을 때  ?>
    <?php }  ?>
