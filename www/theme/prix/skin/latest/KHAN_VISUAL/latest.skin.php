<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');
// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_stylesheet('<link rel="stylesheet" href="'.$latest_skin_url.'/style.css">', 0);
global $link1,$link2,$link3,$en_link1,$en_link2,$en_link3;
?>
<div id="mVisual">
	<div class="visualRoll">
		<?php for ($i=0; $i<count($list); $i++) {  ?>
		<?php
			$sql = " select bf_file from {$g5['board_file_table']} where bo_table = '". $bo_table. "' and wr_id = '". $list[$i]['wr_id'] ."' order by bf_no ";
			$result = sql_query($sql);
			$imgSrc = array();
			for($k=0; $k<$data=sql_fetch_array($result); $k++)  {
				$imgSrc[$k] = $data['bf_file'];
			}
			//style=""cursor:pointer;""
			$subTitle = explode("\n",$list[$i]['wr_content']);
			$link = "";
			if($list[$i]['wr_link1']) $link = $list[$i]['wr_link1'];
		?>
			<div class="item reset-animation"><!-- style="<? if($link) { ?> cursor: pointer; <?}?>" <? if($link) { ?> onclick="javascript:location.href='<?=$link?>'"; <?}?> -->
				<img src="/data/file/<?=$bo_table?>/<?=$imgSrc[0]?>" class="ptVar" alt="" />
				<img src="/data/file/<?=$bo_table?>/<?=$imgSrc[1]?>" class="mVar" alt="" />

				<div class="vTxtArea"><div class="vTxt_in">
					<div class="vTxt">
						<p class="ii cate i01" data-font="Oswald">KHAN UROLOGY CLINIC</p>
						<p class="ii title i02">칸비뇨의학과의원은 진심을 담아 진료합니다.</p>
						<p class="ii text i03">유로리프트 중점 시술 의료기관으로서<br />결과로 답하겠습니다.</p>
						<p class="ii btns i04"><a href="javascript:void(0);">자세히 보기 <img src="/theme/prix/img/arr.png" alt="" /></a></p>
					</div>
				</div></div>
			</div>
		<?php }?>
	</div>
</div>
<script type="text/javascript">
	var mVisual;
	$(function () {
		var vsNum = $("#mVisual .visualRoll .item").length;
		if (vsNum > 1) {
			$("#mVisual .visualRoll").on('init', function (event, slick) {
				$("#mVisual .slick-dots>li").removeClass("slick-active")
				setTimeout(function(){
					$("#mVisual .slick-dots>li").eq(0).addClass("slick-active first")
					$("#mVisual .slick-slide.slick-current .item").removeClass('reset-animation').addClass('slick-active');
				}, 400);
			});

			$("#mVisual .visualRoll").slick({
				prevArrow: '<button class="slick-prev" aria-label="Previous" type="button">←</button>',
				nextArrow: '<button class="slick-next" aria-label="Next" type="button">→</button>',
				appendArrows: $("#mVisual .v-controll .controll-btn"),
				appendDots: $("#mVisual .v-controll .controll-dot"),
				speed: 1500,
				autoplay: true,
				autoplaySpeed: 7000,
				cssEase: 'cubic-bezier(.25,.46,.45,.94)',//cubic-bezier(.25,.46,.45,.94)
				arrows: true,
				dots: true,
				customPaging: function(slider, i) {
					return $('<button type="button" />').text((i + 1).toString().padStart(2, "0"));
				}
			});

			$("#mVisual .visualRoll").on('beforeChange', function(event, slick, currentSlide, nextSlide){
				//$("#mVisual .slick-dots>li").removeClass("first")
				$("#mVisual .slick-slide:not(.slick-cloned) .item").eq(nextSlide).removeClass('reset-animation').addClass('slick-active');
			});
		}else{
			$("#mVisual .item.reset-animation").removeClass('reset-animation').addClass('slick-active');
			$("#mVisual .visualRoll").addClass('slick-slider');
		}
	});
</script>