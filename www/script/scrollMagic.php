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

	.back-to-position { /* 스크롤이 80%이상 넘어가기 전의 기본 상태 */
	  opacity: 0;
	  transition: 1s;
	}
	.back-to-position.to-right { /* 왼쪽에서 오른쪽으로 나타나는 에니에이션*/
	  transform: translateX(-150px); /* 화면의 요소가 왼쪽으로 150px만큼 이동함*/
	}
	.back-to-position.to-left { /* 오른쪽에서 왼쪽으로 나타나는 에니에이션*/
	  transform: translateX(150px); /* 화면의 요소가 오른쪽으로 150px만큼 이동해서 다시 되돌아옴*/
	}
	.show .back-to-position { /* 화면에서 &0%이상 스크롤이 넘어가면 show 클래스의 css속성이 실행됨  */
	  opacity: 1;
	  transform: translateX(0);
	}
	/* 스크롤이 화면의 80% 이상 넘어가면 0.3초 간격으로 해당 show 클래스의 요소들이 화면에 나타남*/
	.show .back-to-position.delay-0 {
	  transition-delay: 0s;
	}
	.show .back-to-position.delay-1 {
	  transition-delay: 0.3s;
	}
	.show .back-to-position.delay-2 {
	  transition-delay: 0.6s;
	}
	.show .back-to-position.delay-3 {
	  transition-delay: 0.9s;
	}
</style>

<div class="trigger-area">
	<section class="scroll-spy"><img src="/theme/prix/img/main/img_m01.jpg" class="back-to-position to-right" alt=""></section>
	<section id="pin2" class="scroll-spy"><img src="/theme/prix/img/main/img_m02.jpg" alt=""></section>
	<section class="scroll-spy"><img src="/theme/prix/img/main/img_m03.jpg" class="back-to-position to-right" alt=""></section>
	<section class="scroll-spy"><img src="/theme/prix/img/main/img_m04.jpg" class="back-to-position to-left" alt=""></section>
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

<!--
https://animate.style/
<link rel="stylesheet" href="/build/animate.css">

https://imivory.tistory.com/2
-->
<script src="//cdnjs.cloudflare.com/ajax/libs/ScrollMagic/2.0.8/ScrollMagic.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/ScrollMagic/2.0.8/plugins/debug.addIndicators.min.js"></script>
<script type="text/javascript">
	var controller = new ScrollMagic.Controller();

	var spyEls = document.querySelectorAll("section.scroll-spy");
	spyEls.forEach(function (spyEl) {
		new ScrollMagic.Scene({
		triggerElement: spyEl, // 보여짐 여부를 감시할 요소 (target)
		triggerHook: 0.5, // 전체 화면 수직 방향의 80% 지점을 감시 (0 ~ 1)
		})
		.setClassToggle(spyEl, "show") // 해당 요소(splEl)가 화면에 보여지면 해당 메소들 실행
		.addTo(new ScrollMagic.Controller()); // 내부에 컨트롤러에 실제 동작하도록 넣음
	});

	var tween = TweenMax.to('#pin2 img', 0.5, {	//2. animation object 생성
		backgroundColor: "#6431ff",
		scale: 2.5,
		rotation: 360,
		x: 600,
		y: 500
	});

		var scene = new ScrollMagic.Scene({triggerElement: "#pin2", duration: 1000})
		.triggerHook("onLeave")
			.setTween(tween)
		.setPin("#pin2", {pushFollowers: true})
		.addIndicators({name: "2 (duration: 100%)"}) // add indicators (requires plugin)

		.addTo(controller)
		.addIndicators();


	$(document).ready(function() {

	});
</script>

<?
include_once(G5_THEME_PATH.'/tail.php');
?>