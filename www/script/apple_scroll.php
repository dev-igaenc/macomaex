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
	html{}
	.hero-lightpass{height: 200vh; background: #000; position: relative;}
	canvas{max-width: 100vw; max-height: 100vh; position: absolute; left: 50%; top: 0; transform: translate(-50%, 0);}
</style>
<div class="hero-lightpass">
<canvas id="hero-lightpass"></canvas>
</div>
<script type="text/javascript">
	const html = document.documentElement;
	const scrollGo = $(".hero-lightpass").offset().top;
	const canvas = document.getElementById("hero-lightpass");
	const context = canvas.getContext("2d");

	const frameCount = 7;
	const currentFrame = index => (
		`/theme/prix/img/lightpass/${index.toString().padStart(4, '0')}.jpg`
	)

	const preloadImages = () => {
		for (let i = 1; i < frameCount; i++) {
			const img = new Image();
			img.src = currentFrame(i);
		}
	};

	const img = new Image()
	const w = $(window).width();
	const h = $(window).height();
	//const w = 1600;
	//const h = 800;
	img.src = currentFrame(1);
	canvas.width=w;
	canvas.height=h;
	img.onload=function(){
		context.drawImage(img, 0, 0, w, h);
	}

	const updateImage = index => {
		img.src = currentFrame(index);
		//console.log(img.src)
		context.drawImage(img, 0, 0, w, h);
	}

	window.addEventListener('scroll', () => {
		const scrollTop = html.scrollTop;
		const maxScrollTop = html.scrollHeight - window.innerHeight;


		const scrollStop = $(".hero-lightpass").height() - (h-800);

		const scrollFraction = (scrollTop-scrollGo) / ($(".hero-lightpass").height() - h);

		//console.log(scrollTop, maxScrollTop, scrollGo, scrollGo2, html.scrollHeight, window.innerHeight, scrollStop, $(".hero-lightpass").height(), $(window).height(), scrollStop2)

		if(scrollTop > scrollGo && scrollTop < scrollStop){
			const frameIndex = Math.min(
				frameCount - 1,
				Math.ceil(scrollFraction * frameCount)
			);

			//console.log(frameIndex, frameCount)
			requestAnimationFrame(() => updateImage(frameIndex))

			$("#hero-lightpass").css("position", "fixed");
		}else if(scrollTop < scrollGo){
			$("#hero-lightpass").css({"position":"absolute", "top":"0", "bottom":"auto"});
		}else{
			$("#hero-lightpass").css({"position":"absolute", "top":"auto", "bottom":"0"});
		}
	});

	preloadImages()
</script>

<?
include_once(G5_THEME_PATH.'/tail.php');
?>