<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');
// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨

$n_thumb_width = 1553; //썸네일 가로 크기
$n_thumb_height = 605; //썸네일 세로 크기
//add_stylesheet('<link rel="stylesheet" href="'.$latest_skin_url.'/style.css">', 0);
?>

<div class="flowing"><div class="inConts">
	<div class="mMaq">
		<div id="scroller_tg1" class="mqRoll">
			<ul>
				<?php for ($i=0; $i<count($list); $i++) {  ?>
				<?php
					$thumb = get_list_thumbnail($bo_table, $list[$i]['wr_id'], $n_thumb_width, $n_thumb_height);
					$subTitle = explode("\n",$list[$i]['wr_content']);
					$link = "javascript:;";
					if( $list[$i]['wr_link1'] ) $link = $list[$i]['wr_link1'];
				?>
					<li class="slider"><img src="<?=$thumb['ori']?>" alt="" /></li>
				<?}?>
			</ul>
		</div>
		<div class="controll">
			<a href="javascript:scroll_direction('scroller_tg1','left');"><img src="<?php echo G5_THEME_IMG_URL ?>/main/btn_flowing_prev.png" alt="prev" /></a>
			<a href="javascript:void(0);" class="play"></a>
			<a href="javascript:scroll_direction('scroller_tg1','right');"><img src="<?php echo G5_THEME_IMG_URL ?>/main/btn_flowing_next.png" alt="next" /></a>
		</div>
	</div>
</div></div>
<script type="text/javascript">
	var dir1,pause1, total1, total2, total3;
	$(window).load(function(){	// $(".testText").text("off2");
		dir1 = "left";
		pause1 = false;
		total1 =  $("#scroller_tg1 ul").width();
		total2 = 0;
		$("#scroller_tg1 ul li").each(function(index){
			total2 = total2 + ($(this).width()+21);
		});
		total3 = total1-total2;
		//console.log(total1+"__"+total2+"__"+total3)
		if(total1 < total2){
			setInterval('scrollBanner()', 25);
		}else{
			$(".mMaq .controll").hide();
		}

		$("#scroller_tg1 ul li").each(function (idx) {
			$(this).on({ 'mouseover': function () {
				pause1 = true;
				},
				'mouseout': function () {
					pause1 = false;
				}
			});
		});

		$(".mMaq .controll>.play").click(function(){
			if($(this).hasClass("active")){
				pause1 = false;
				$(this).removeClass("active")
				scroll_direction('scroller_tg1',dir1);
			}else{
				$(this).addClass("active")
				pause1 = true;
			}
		});
	});
	function scrollBanner() {
		if (!pause1) {
			var lefts = parseInt($('#scroller_tg1 ul').css('left'));
			var widths = parseInt($('#scroller_tg1 ul').css('width'));
			//$(".testText").text("left = "+lefts+", width = "+widths);

			if (dir1 == "left") {
				$('#scroller_tg1 ul').css('left', lefts - 1 + "px");
				if (lefts == total3)
					dir1 = "right";
			} else {
				$('#scroller_tg1 ul').css('left', lefts + 1 + "px");
				if (lefts == 0)
					dir1 = "left";
			}
		}
	}
	function scroll_direction(obj, dir) {
		if (obj == 'scroller_tg1'){
			dir1 = dir;
			pause1 = false;
			$(".mMaq .controll>.play").removeClass("active")
		}
	}
</script>

