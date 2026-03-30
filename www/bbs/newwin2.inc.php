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
<div id="popLayer" class="disNone">
	<div id="moPopArea">
		<div class="moPop">
			<div id="moPopRoll">
				<?php
					for ($i=0; $nw=sql_fetch_array($result); $i++){
						// 이미 체크 되었다면 Continue
						if (isset($_COOKIE["hd_pops_{$nw['nw_id']}"]) && $_COOKIE["hd_pops_{$nw['nw_id']}"])
						continue;
				?>
				<div id="hd_pops_<?php echo $nw['nw_id'] ?>" class="slider"><?php echo conv_content($nw['nw_content'], 1); ?></div>
				<?php }?>
			</div>

			<div class="moPopClose">
				<button type="button" class="toDay_close" id="todayPopClose"><span>오늘 하루 보지 않음</span></button>
				<button type="button" class="close" id="layerPopClose"><span>닫기</span></button>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		function getCookie( name ){
			var nameOfCookie = name + "=";
			var x = 0;
			while ( x <= document.cookie.length )
			{
				var y = (x+nameOfCookie.length);
				if ( document.cookie.substring( x, y ) == nameOfCookie ) {
					if ( (endOfCookie=document.cookie.indexOf( ";", y )) == -1 )
						endOfCookie = document.cookie.length;
					return unescape( document.cookie.substring( y, endOfCookie ) );
				}
				x = document.cookie.indexOf( " ", x ) + 1;
				if ( x == 0 )
					break;
			}
			return "";
		}
		function setCookie( name, value, expiredays ){
			var todayDate = new Date();
			todayDate.setDate( todayDate.getDate() + expiredays );
			document.cookie = name + "=" + escape( value )
					  + "; path=/; expires=" + todayDate.toGMTString() + ";"
		}
		$(function() {
			var pop_length = $("#moPopRoll .slider").length;

			if(getCookie("popLayer") != "done"){
				if (pop_length != 0){
					$("#popLayer").removeClass("disNone")
					$("html").addClass("hide")

					//$(".moPop").draggable();
					$("#moPopRoll").slick({
						infinite: false,
						speed: 300,
						autoplay: false,
						slidesToShow: 1,
						slidesToScroll: 1,
						arrows: false,
						dots: true,
						adaptiveHeight: true,
						centerMode: true,
						centerPadding: '0'
					});

					$(document).on("click","#layerPopClose",function(){
						$("#popLayer").addClass("disNone")
						$("html").removeClass("hide")
					});

					$(document).on("click","#todayPopClose",function(){
						$("#popLayer").addClass("disNone")
						$("html").removeClass("hide")
						setCookie("popLayer", "done", 1);
					});
				}
			}
		})
	</script>
</div>
<!-- } 팝업레이어 끝 -->