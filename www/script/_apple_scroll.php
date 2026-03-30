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
	html {
	  height: 100vh;
	}

	body {
	  height: 500vh;
	  background: #000;
	}

	canvas {
	  position: fixed;
	  left: 50%;
	  top: 50%;
	  transform: translate(-50%, -50%);
	  max-width: 100vw;
	  max-height: 100vh;
	}
</style>
<div class="hero-lightpass">
<canvas id="hero-lightpass"></canvas>
</div>
<script type="text/javascript">
	const html = document.documentElement;
	const canvas = document.getElementById("hero-lightpass");
	const context = canvas.getContext("2d");

	const frameCount = 148;
	const currentFrame = index => (
	  `https://www.apple.com/105/media/us/airpods-pro/2019/1299e2f5_9206_4470_b28e_08307a42f19b/anim/sequence/large/01-hero-lightpass/${index.toString().padStart(4, '0')}.jpg`
	)

	const preloadImages = () => {
	  for (let i = 1; i < frameCount; i++) {
		const img = new Image();
		img.src = currentFrame(i);
	  }
	};

	const img = new Image()
	img.src = currentFrame(1);
	canvas.width=1158;
	canvas.height=770;
	img.onload=function(){
	  context.drawImage(img, 0, 0);
	}

	const updateImage = index => {
	  img.src = currentFrame(index);

	  console.log(img.src)
	  context.drawImage(img, 0, 0);
	}

	window.addEventListener('scroll', () => {
	  const scrollTop = html.scrollTop;
	  const maxScrollTop = html.scrollHeight - window.innerHeight;
	  const scrollFraction = scrollTop / maxScrollTop;
	  const frameIndex = Math.min(
		frameCount - 1,
		Math.ceil(scrollFraction * frameCount)
	  );

	  requestAnimationFrame(() => updateImage(frameIndex + 1))
	});

	preloadImages()
</script>

<?
include_once(G5_THEME_PATH.'/tail.php');
?>