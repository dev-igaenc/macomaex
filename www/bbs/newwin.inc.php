<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if (!defined('_SHOP_')) {
    $pop_division = 'comm';
} else {
    $pop_division = 'shop';
}

$sql = " select * from {$g5['new_win_table']}
          where '".G5_TIME_YMDHIS."' between nw_begin_time and nw_end_time
            and nw_device IN ( 'both', 'pc', 'mobile' ) and nw_division IN ( 'both', '".$pop_division."' )
          order by nw_id asc ";
$result = sql_query($sql, false);
?>

<!-- 팝업레이어 시작 { -->
<div id="hd_pop">
	<h2>팝업레이어 알림</h2>
	<?php
		for ($i=0; $nw=sql_fetch_array($result); $i++){
			// 이미 체크 되었다면 Continue
			if (isset($_COOKIE["hd_pops_{$nw['nw_id']}"]) && $_COOKIE["hd_pops_{$nw['nw_id']}"])
				continue;

			$nw_content = preg_replace(
				'/(?:<br\s+style=["\']clear:both;["\']>\s*)+$/i',
				'',
				trim($nw['nw_content'])
			);
	?>
		<div id="hd_pops_<?php echo $nw['nw_id'] ?>" class="hd_pops" style="display:none; top:<?php echo $nw['nw_top']?>px;left:<?php echo $nw['nw_left']?>px" data-device="<?=$nw['nw_device']?>">
			<div class="hd_pops_con" style="width:<?php echo $nw['nw_width'] ?>px;"><!-- height:<?php echo $nw['nw_height'] ?>px; -->
				<?//php echo str_replace('&nbsp;','',strip_tags($nw['nw_content']));?>
				<?if($nw['nw_link']){?>
					<a href="<?=$nw['nw_link']?>" <?if($nw['nw_blank']){?>target="_blank"<?}?>>
					<?php echo conv_content($nw_content, 1); ?>
					</a>
				<?}else{?>
					<?php echo conv_content($nw_content, 1); ?>
				<?}?>
			</div>
			<div class="hd_pops_footer">
				<button class="hd_pops_reject hd_pops_<?php echo $nw['nw_id']; ?> <?php echo $nw['nw_disable_hours']; ?>"><strong><?php echo $nw['nw_disable_hours']; ?></strong>시간 동안 다시 열람하지 않습니다.</button>
				<button class="hd_pops_close hd_pops_<?php echo $nw['nw_id']; ?>">닫기 <i class="fa fa-times" aria-hidden="true"></i></button>
			</div>
		</div>
	<?php }
		if ($i == 0) echo '<span class="sound_only">팝업레이어 알림이 없습니다.</span>';
	?>
</div>

<script>
	$(function() {
		$(".hd_pops").draggable();
		$(".hd_pops_reject").click(function() {
			var id = $(this).attr('class').split(' ');
			var ck_name = id[1];
			var exp_time = parseInt(id[2]);
			$("#"+id[1]).css("display", "none").addClass("xx");
			set_cookie(ck_name, 1, exp_time, g5_cookie_domain);
		});
		$('.hd_pops_close').click(function() {
			var idb = $(this).attr('class').split(' ');
			$('#'+idb[1]).css('display','none').addClass("xx");
		});
		$("#hd").css("z-index", 10);

		$(window).on('throttledresize', function(){// Resize Event
			win_w = $(window).width();
			win_h = $(window).height();
			if(win_w <= "1024"){
				$(".hd_pops:not('.xx')[data-device='pc']").hide();
				$(".hd_pops:not('.xx')[data-device='mobile']").show();
				$(".hd_pops:not('.xx')[data-device='both']").show();
			}else{
				$(".hd_pops:not('.xx')[data-device='both']").show();
				$(".hd_pops:not('.xx')[data-device='pc']").show();
				$(".hd_pops:not('.xx')[data-device='mobile']").hide();
			}

		}).resize();
	});
</script>
<!-- } 팝업레이어 끝 -->
