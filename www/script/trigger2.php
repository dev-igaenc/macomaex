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
				<div class="item" data-aos="fade-up"><img src="/theme/prix/img/main/img_m05_1.jpg" alt=""></div>
				<div class="item" data-aos="fade-down"><img src="/theme/prix/img/main/img_m05_2.jpg" alt=""></div>
				<div class="item" data-aos="fade-right"><img src="/theme/prix/img/main/thumb_02.jpg" alt=""></div>
				<div class="item" data-aos="fade-left"><img src="/theme/prix/img/main/thumb_04.jpg" alt=""></div>
				<div class="item" data-aos="fade-up-right"><img src="/theme/prix/img/main/thumb_05.jpg" alt=""></div>
				<div class="item" data-aos="fade-up-left"><img src="/theme/prix/img/main/thumb_jpg_yj01.jpg" alt=""></div>
				<div class="item" data-aos="fade-down-right"><img src="/theme/prix/img/main/thumb_jpg_yj18.jpg" alt=""></div>

				<div class="item" data-aos="fade-down-left"><img src="/theme/prix/img/main/img_m05_1.jpg" alt=""></div>
				<div class="item" data-aos="flip-up"><img src="/theme/prix/img/main/img_m05_2.jpg" alt=""></div>
				<div class="item" data-aos="flip-down"><img src="/theme/prix/img/main/thumb_02.jpg" alt=""></div>
				<div class="item" data-aos="flip-left"><img src="/theme/prix/img/main/thumb_04.jpg" alt=""></div>
				<div class="item" data-aos="flip-right"><img src="/theme/prix/img/main/thumb_05.jpg" alt=""></div>
				<div class="item" data-aos="slide-up"><img src="/theme/prix/img/main/thumb_jpg_yj01.jpg" alt=""></div>
				<div class="item" data-aos="slide-down"><img src="/theme/prix/img/main/thumb_jpg_yj18.jpg" alt=""></div>

				<div class="item" data-aos="slide-left"><img src="/theme/prix/img/main/img_m05_1.jpg" alt=""></div>
				<div class="item" data-aos="slide-right"><img src="/theme/prix/img/main/img_m05_2.jpg" alt=""></div>
				<div class="item" data-aos="zoom-in"><img src="/theme/prix/img/main/thumb_02.jpg" alt=""></div>
				<div class="item" data-aos="zoom-in-up"><img src="/theme/prix/img/main/thumb_04.jpg" alt=""></div>
				<div class="item" data-aos="zoom-in-down"><img src="/theme/prix/img/main/thumb_05.jpg" alt=""></div>
				<div class="item" data-aos="zoom-in-left"><img src="/theme/prix/img/main/thumb_jpg_yj01.jpg" alt=""></div>
				<div class="item" data-aos="zoom-in-right"><img src="/theme/prix/img/main/thumb_jpg_yj18.jpg" alt=""></div>
			</div>
		</div>
	</div></div>
	<p><img src="/theme/prix/img/main/img_m06.jpg" alt=""></p>
</div>


<link rel="stylesheet" href="/build/masonry/isotope-docs.css">
<script type="text/javascript" src="/build/masonry/isotope-docs.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.6.0/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.6.0/ScrollTrigger.min.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
	});
</script>

<?
include_once(G5_THEME_PATH.'/tail.php');
?>