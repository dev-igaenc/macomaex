<?
include_once(G5_LIB_PATH . '/thumbnail.lib.php');

for ($i=0; $i<count($list); $i++) {
?>
<li>
	<?php if ($is_checkbox) { ?>
		<div class="gall_box">
			<p class="checkIn noTxt">
				<input type="checkbox" name="chk_wr_id[]" value="<?php echo $list[$i]['wr_id'] ?>" id="chk_wr_id_<?php echo $i ?>">
				<label for="chk_wr_id_<?php echo $i ?>"><?php echo $list[$i]['subject'] ?></label>
			</p>
		</div>
	<?php } ?>
	<a href="javascript:goContent('<?=$list[$i]['wr_id']?>')" class="gall_cont">
		<div class="thumb"><img src="/theme/prix/img/thumb/thumb_270x270.png" alt="" /><!-- 고정 -->
			<?php
			$thumb = get_list_thumbnail($board['bo_table'], $list[$i]['wr_id'], $board['bo_gallery_width'], $board['bo_gallery_height'], false, true);

			if ($thumb['src']) {
				$img_content = '<img src="' . $thumb['ori'] . '" class="ofi thb" alt="' . $thumb['alt'] . '" />';
			} else {
				//$img_content = '<span class="no_image" style="line-height:'.$board['bo_gallery_height'].'px;">no image</span>';
				$img_content = '<img src="/img/noimage.jpg" class="ofi thb bg" alt="' . $thumb['alt'] . '" />';
			}

			echo $img_content;
			?><p class="over"><span>view</span></p>
		</div>
		<div class="area"><div class="in_area">
			<?php if ($is_category && $list[$i]['ca_name']) { ?> <p class="cate">[<?php echo $list[$i]['ca_name'] ?>]</p> <?php } ?>

			<div class="title__area">
				<div class="tt title two"><?php echo $list[$i]['subject'] ?></div>
				<?php if (!empty($list[$i]['icon_new'])) {echo "<p class='icon new'></p>";} ?>
				<!--
				<?php if (isset($list[$i]['icon_hot'])) echo "<p class='icon hot'></p>"; ?>
				<?php if (isset($list[$i]['icon_new'])) echo rtrim($list[$i]['icon_new']);?>
				<?php if (isset($list[$i]['icon_file'])) echo "<p class='icon fil'>".rtrim($list[$i]['icon_file'])."</p>"; ?>
				<?php if (isset($list[$i]['icon_link'])) echo "<p class='icon link'>".rtrim($list[$i]['icon_link'])."</p>"; ?>
				<?php if (isset($list[$i]['icon_secret'])) echo "<p class='icon secre'>".rtrim($list[$i]['icon_secret'])."</p>"; ?>
				-->
			</div>

			<div class="tt txt two"><?=$list[$i]['wr_content']?><?=str_replace('&nbsp;','',strip_tags($list[$i]['wr_content']));?></div>
			<div class="tt day"><?php echo $list[$i]['datetime'] ?></div>
		</div></div>
	</a>
</li>
<?}?>