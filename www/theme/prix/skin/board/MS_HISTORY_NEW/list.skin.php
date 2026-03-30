<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH . '/thumbnail.lib.php');
include_once $board_skin_path . "/board_head.skin.php";
//add_stylesheet('<link rel="stylesheet" href="' . $board_skin_url . '/css/style.css">', 20);
//add_stylesheet('<link rel="stylesheet" href="' . $board_skin_url . '/css/lightbox.css">', 21);
?>
<?php
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
<!-- 게시판 카테고리 시작 { -->
<?php if ($is_category) { ?>
<nav id="bo_cate" class="category_top">
	<h2><?php echo $board['bo_subject'] ?> 카테고리</h2>
	<ul id="bo_cate_ul">
		<?php echo $category_option ?>
	</ul>
</nav>
<?php } ?>
<!-- } 게시판 카테고리 끝 -->

<form name="fboardlist" id="fboardlist" action="./board_list_update.php" onsubmit="return fboardlist_submit(this);" method="post">
	<input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
	<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
	<input type="hidden" name="stx" value="<?php echo $stx ?>">
	<input type="hidden" name="spt" value="<?php echo $spt ?>">
	<input type="hidden" name="page" value="<?php echo $page ?>">
	<input type="hidden" name="sw" value="">

	<?php if ($is_admin == 'super' || $is_auth) {  ?>
	<div class="board_btn">
		<div class="total"><!-- Total <strong><?php echo number_format($total_count) ?></strong>건,  <strong><?php echo $page ?></strong> 페이지 --></div>

		<ul class="board_user">
			<?php if ($admin_href) { ?><li><a href="<?php echo $admin_href ?>" class="click admin"><i class="fa fa-cog fa-spin fa-fw"></i> 관리자</a></li><?php } ?>
			<?php if ($is_admin == 'super' || $is_auth) {  ?>
				<li><button type="submit" name="btn_submit" value="선택삭제" onclick="document.pressed=this.value" class="click"><i class="fa fa-trash-o" aria-hidden="true"></i> 선택삭제</button></li>
				<!--
				<li><button type="submit" name="btn_submit" value="선택복사" onclick="document.pressed=this.value" class="click"><i class="fa fa-files-o" aria-hidden="true"></i> 선택복사</button></li>
				<li><button type="submit" name="btn_submit" value="선택이동" onclick="document.pressed=this.value" class="click"><i class="fa fa-arrows" aria-hidden="true"></i> 선택이동</button></li>
				-->
			<?php } ?>
			<?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="click write">글쓰기 <i class="fa fa-pencil" aria-hidden="true"></i></a></li><?php } ?>
		</ul>
	</div>
	<?php } ?>

	<ul class="history-area move flex <?php if ($is_admin) echo 'admin'?>">
		<div id="his_progress"></div>
		<?php for ($i=0; $i<count($list); $i++) {?>
		<li>
			<?php
			$thumb = get_list_thumbnail($board['bo_table'], $list[$i]['wr_id'], $board['bo_gallery_width'], $board['bo_gallery_height'], false, true);

			if($thumb['src']) {
				$img_content = '<img src="'.$thumb['ori'].'" class="ofi thb" alt="'.$thumb['alt'].'" />';
			}else{
				$img_content = "";
			}
			?>

			<a<?php if ($is_admin) { ?> href="/bbs/write.php?bo_table=<?=$bo_table?>&wr_id=<?=$list[$i]['wr_id']?>&w=u"<? } ?> class="history-in">
				<?php if ($is_checkbox) { ?>
				<div class="gall_box">
					<p class="checkIn noTxt">
						<input type="checkbox" name="chk_wr_id[]" value="<?php echo $list[$i]['wr_id'] ?>" id="chk_wr_id_<?php echo $i ?>">
						<label for="chk_wr_id_<?php echo $i ?>"><?php echo $list[$i]['subject'] ?></label>
					</p>
				</div>
				<?php } ?>

				<strong data-aos="fade-up"><?php echo $list[$i]['subject'] ?></strong>

				<div class="txt_wrap" data-aos="fade-up" data-aos-delay="200">
					<?php echo ConvertLineFeed($list[$i]['wr_content'], "p");?>
				</div>
			</a>
			<div class="thumb" data-aos="fade-up" data-aos-delay="300"><?php echo $img_content; ?></div>
		</li>
		<?php } ?>
		<?php if (count($list) == 0) { echo "<li class=\"empty_list\">".$lang_list_no."</li>"; } ?>
	</ul>

	<?php if ($list_href || $is_checkbox || $write_href) { ?>
	<div class="board_btn end">
		<ul class="board_user">
			<?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="click write big">글쓰기 <i class="fa fa-pencil" aria-hidden="true"></i></a></li><?php } ?>
		</ul>
	</div>
	<?php } ?>
	</form>

    <!-- 페이지 -->
    <?php //echo $write_pages;  ?>

</div>

<script type="text/javascript">
    /*if ('<?=$sca?>') document.fcategory.sca.value = '<?=$sca?>';

    if ('<?=$stx?>') {
    document.fsearch.sfl.value = '<?=$sfl?>';

    if ('<?=$sop?>' == 'and')
        document.fsearch.sop[0].checked = true;

    if ('<?=$sop?>' == 'or')
        document.fsearch.sop[1].checked = true;
} else {
    document.fsearch.sop[0].checked = true;
}
*/
</script>

<?php if ($is_checkbox) { ?>
    <script>
        function all_checked(sw) {
            var f = document.fboardlist;

            for (var i = 0; i < f.length; i++) {
                if (f.elements[i].name == "chk_wr_id[]")
                    f.elements[i].checked = sw;
            }
        }

        function fboardlist_submit(f) {
            var chk_count = 0;

            for (var i = 0; i < f.length; i++) {
                if (f.elements[i].name == "chk_wr_id[]" && f.elements[i].checked)
                    chk_count++;
            }

            if (!chk_count) {
                alert(document.pressed + "할 게시물을 하나 이상 선택하세요.");
                return false;
            }

            if (document.pressed == "선택복사") {
                select_copy("copy");
                return;
            }

            if (document.pressed == "선택이동") {
                select_copy("move");
                return;
            }

            if (document.pressed == "선택삭제") {
                if (!confirm("선택한 게시물을 정말 삭제하시겠습니까?\n\n한번 삭제한 자료는 복구할 수 없습니다"))
                    return false;
            }

            return true;
        }

        // 선택한 게시물 복사 및 이동
        function select_copy(sw) {
            var f = document.fboardlist;

            if (sw == "copy")
                str = "복사";
            else
                str = "이동";

            var sub_win = window.open("", "move", "left=50, top=50, width=500, height=550, scrollbars=1");

            f.sw.value = sw;
            f.target = "move";
            f.action = "./move.php";
            f.submit();
        }
    </script>
<?php } ?>
<!-- } 게시판 목록 끝 -->
