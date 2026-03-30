<div class="no-board-pagination">
	<?
		if ($listCurPage > 1) {
			$prevpage = $listCurPage - 1;
	?>
	<a href="javascript:void(0);"  title="이전" onClick="goListMove(<?=$prevpage?>, '<?=$PHP_SELF?>');">
		<i class="fa-solid fa-angle-left"></i>
	</a>
	<? } else { ?>
	<a href="javascript:void(0);"  title="이전">
		<i class="fa-solid fa-angle-left"></i>
	</a>
	<? } ?>
	
	<?
		for ($x = ($listCurPage - $pageBlock); $x < (($listCurPage + $pageBlock) + 1); $x++) {

		   if (($x > 0) && ($x <= $Page)) {
			   if ($x == $listCurPage) {
	?>
		<a href="javascript:void(0);" title="<?=$x?>페이지" class="active"><?=$x?></a>
	<? 
			}else{
	?>
	<a href="javascript:void(0);" title="<?=$x?>페이지" onClick="goListMove(<?=$x?>, '<?=$PHP_SELF?>');"><?=$x?></a>
	<?
				} //end else
			} //end if 
		} //end for
	?>

	<?
		if ($listCurPage != $Page) {
			 $nextpage = $listCurPage + 1;
	?>
	<a href="javascript:void(0);"  title="다음" onClick="goListMove(<?=$nextpage?>, '<?=$PHP_SELF?>');">
		<i class="fa-solid fa-angle-right"></i>
	</a>
	<?
		}else{
	?>
	<a href="javascript:void(0);"  title="다음">
		<i class="fa-solid fa-angle-right"></i>
	</a>
	<?
		}
	?>
</div>

<script>
function goListMove(start, url){
	$("#frm").append("<input type='hidden' name='page' value='"+start+"'>");
	$("#frm").attr("action", url);
	$("#frm").submit();
}
</script>