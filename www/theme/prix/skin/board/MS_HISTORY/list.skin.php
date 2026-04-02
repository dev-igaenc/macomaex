<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH . '/thumbnail.lib.php');
include_once $board_skin_path . "/board_head.skin.php";
//add_stylesheet('<link rel="stylesheet" href="' . $board_skin_url . '/css/style.css">', 20);
//add_stylesheet('<link rel="stylesheet" href="' . $board_skin_url . '/css/lightbox.css">', 21);
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

	<?
		if($category) {
			$categories = explode(",", $category);
			if(!$sca){
				$where = " wr_is_comment = 0 ";
			}else{
				$where = " ca_name='".$sca."'";
			}

			$sql = " select * from {$g5['write_prefix']}$bo_table where ".$where." order by wr_subject desc, wr_10 desc, wr_1, wr_2, wr_9";
		}else{
			$sql="SELECT * FROM {$g5['write_prefix']}$bo_table ORDER BY wr_subject desc, wr_10 desc, wr_1, wr_2, wr_9";
		}

		$result=sql_query($sql);
		while($data=sql_fetch_array($result)) {
			$hList[$data['wr_subject']][]=$data;
		}
	?>
	<ul class="historyArea move <?=$board['bo_3']=="m"?"month":""?> <?php if ($is_admin) echo 'admin'?>"><!-- month -->
		<div id="his_progress"></div>
		<?php
			foreach ($hList as $key => $val) {
				if ($is_admin || $is_checkbox)  $checkbox='<input type="checkbox" name="chk_wr_id[]" value="' . $value['wr_id'] . '" title="' . $value['wr_subject'] . '"> ';
		?>
			<li>
				<div class="txt_wrap">
					<strong data-aos="fade-up"><?=$key?></strong>
					<ul>
						<?php foreach ($val as $value){ ?>
						<li data-aos="fade-up" data-aos-delay="100" data-month="<?php echo $value['wr_1'] ?>">
							<?php if ($is_admin) { ?>
							<p class="checkIn noTxt">
								<input type="checkbox" name="chk_wr_id[]" value="<?php echo $value['wr_id'] ?>" id="chk_wr_id_<?php echo $value['wr_id'] ?>">
								<label for="chk_wr_id_<?php echo $value['wr_id'] ?>"><?php echo $value['wr_content'] ?></label>
							</p>
							<?php } ?>
							<?php
								if ($is_admin) {
									echo ' <a href="' . G5_BBS_URL . '/write.php?bo_table=' . $bo_table . '&w=u&wr_id=' . $value['wr_id'] . '">';
								}
								echo $value['wr_content'];
								if ($is_admin) {
									echo "</a>";
								}
							?>
						</li>
						<?php } ?>
					</ul>
				</div>
			</li>
		<?php $count++; } ?>
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
