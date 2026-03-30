<?
include_once("./_common.php");

$board['bo_10']="7";
$board['bo_9']="2";
$board['bo_5'] = "스크립트";
$board['bo_subject'] = "스크롤 반응";
//$board['bo_4'] = $config['cf_title']."의 혜택을 확인하세요.";

include_once(G5_THEME_PATH.'/head.php');
?>

<style type="text/css">
	#container{padding-bottom:0;}
	.inConts{max-width:124rem; padding:0 2rem;}
	.trigger-area{width:100vw; margin-left:-50vw; position: relative; left:50%;}
	.trigger{width:100%; font-size:0; line-height:0; overflow:hidden; position: relative;}
	.trigger img.bg{width:100%; height:100vh !important; object-fit:cover; object-position: center center; position: absolute; left:0; top:0;}
	.trigger .scroll{width:51.666%; max-width:62rem; float:right; overflow:hidden; position: relative; z-index:3;}
	.trigger .scroll .list{margin:0 0 0 -4rem;}
	.trigger .scroll .list .item{width:50%; padding:16rem 0 0 4rem; display:inline-block; vertical-align: top; }
	.trigger .scroll .list .item:first-child{padding-top:0rem;}
</style>

<div class="trigger-area">
	<p><img src="/theme/prix/img/main/img_m01.jpg" alt=""></p>
	<p><img src="/theme/prix/img/main/img_m02.jpg" alt=""></p>
	<p><img src="/theme/prix/img/main/img_m03.jpg" alt=""></p>
	<p><img src="/theme/prix/img/main/img_m04.jpg" alt=""></p>
	<div class="trigger"><img src="/theme/prix/img/main/img_m05.jpg" class="bg" alt=""><div class="inConts">
		<div class="scroll">
			<div class="list">
				<div class="item"><img src="/theme/prix/img/main/img_m05_1.jpg" alt=""></div>
				<div class="item"><img src="/theme/prix/img/main/img_m05_2.jpg" alt=""></div>
				<div class="item"><img src="/theme/prix/img/main/thumb_02.jpg" alt=""></div>
				<div class="item"><img src="/theme/prix/img/main/thumb_04.jpg" alt=""></div>
				<div class="item"><img src="/theme/prix/img/main/thumb_05.jpg" alt=""></div>
				<div class="item"><img src="/theme/prix/img/main/thumb_jpg_yj01.jpg" alt=""></div>
				<div class="item"><img src="/theme/prix/img/main/thumb_jpg_yj18.jpg" alt=""></div>

				<div class="item"><img src="/theme/prix/img/main/img_m05_1.jpg" alt=""></div>
				<div class="item"><img src="/theme/prix/img/main/img_m05_2.jpg" alt=""></div>
				<div class="item"><img src="/theme/prix/img/main/thumb_02.jpg" alt=""></div>
				<div class="item"><img src="/theme/prix/img/main/thumb_04.jpg" alt=""></div>
				<div class="item"><img src="/theme/prix/img/main/thumb_05.jpg" alt=""></div>
				<div class="item"><img src="/theme/prix/img/main/thumb_jpg_yj01.jpg" alt=""></div>
				<div class="item"><img src="/theme/prix/img/main/thumb_jpg_yj18.jpg" alt=""></div>

				<div class="item"><img src="/theme/prix/img/main/img_m05_1.jpg" alt=""></div>
				<div class="item"><img src="/theme/prix/img/main/img_m05_2.jpg" alt=""></div>
				<div class="item"><img src="/theme/prix/img/main/thumb_02.jpg" alt=""></div>
				<div class="item"><img src="/theme/prix/img/main/thumb_04.jpg" alt=""></div>
				<div class="item"><img src="/theme/prix/img/main/thumb_05.jpg" alt=""></div>
				<div class="item"><img src="/theme/prix/img/main/thumb_jpg_yj01.jpg" alt=""></div>
				<div class="item"><img src="/theme/prix/img/main/thumb_jpg_yj18.jpg" alt=""></div>
			</div>
		</div>
	</div></div>
	<p><img src="/theme/prix/img/main/img_m06.jpg" alt=""></p>
</div>


<link rel="stylesheet" href="/build/masonry/isotope-docs.css">
<script type="text/javascript" src="/build/masonry/isotope-docs.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.5/ScrollTrigger.min.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		var $dynamic = $(".trigger .list").isotope({
			transitionDuration: '0.5s',
			percentPosition: true,
			masonry: {
				// use element for option
				columnWidth: '.item'
			}
		});

		$dynamic.on( 'layoutComplete', function( event, laidOutItems ) {
			//console.log( 'Isotope layout completed on ' + laidOutItems.length + ' items' );
			gsap.registerPlugin(ScrollTrigger);
			ScrollTrigger.create({
				trigger: ".trigger .scroll",
				start: "top top",
				end: "bottom bottom",
				pin: ".trigger .bg",
				//onEnter: function() { animateFrom(elem) }, //scroll-start ~ scroll-end 사이
				//onEnterBack: function() { animateFrom(elem, -1) }, //scroll-start ~ scroll-end 사이로 다시 들어갈 때
				//onLeave: function() { hide(elem) }, // scroll-end부분을 넘어갈 때
				//onLeaveBack: function() { hide(elem) } // scroll-end부분을 다시 넘어갈 때
			});
		});

		$(window).on('scroll', function(){// Scroll Event
			var triggerBox = $(".trigger");
			console.log(scroll_T, triggerBox.offset().top - (window_H + window_H / 2))
			if(scroll_T >= triggerBox.offset().top - (window_H + window_H / 2) && !triggerBox.hasClass("one")){
				$dynamic.isotope('layout');

				triggerBox.addClass("one")
			}
		});
		/*
		setTimeout(function(){
		}, 300);
		*/
	});

</script>

<?
include_once(G5_THEME_PATH.'/tail.php');
?>