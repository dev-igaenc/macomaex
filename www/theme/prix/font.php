<?php
if (!defined('_INDEX_')) define('_INDEX_', true);
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
if (G5_IS_MOBILE) {
    include_once(G5_THEME_MOBILE_PATH . '/index.php');
    return;
}

include_once(G5_THEME_PATH . '/head.php');
?>
<link href="/theme/prix/font_hanwha/font.css" rel="stylesheet">
<script type="text/javascript">
	$(function () {
	});
</script>
<style>
	@import url('//spoqa.github.io/spoqa-han-sans/css/SpoqaHanSansNeo.css');
	.f_SpoqaHanSansNeo{font-family:'Spoqa Han Sans Neo', sans-serif !important;}

	@import url('//spoqa.github.io/spoqa-han-sans/css/SpoqaHanSans-kr.css');
	.f_SpoqaHanSans{font-family:'Spoqa Han Sans', sans-serif !important;}

	@font-face {
		font-family: 'Godo'; font-style: normal; font-weight: 400;
		src: url('//cdn.jsdelivr.net/korean-webfonts/1/corps/godo/Godo/GodoM.woff2') format('woff2'), url('//cdn.jsdelivr.net/korean-webfonts/1/corps/godo/Godo/GodoM.woff') format('woff');
	}
	@font-face {
		font-family: 'Godo'; font-style: normal; font-weight: 700;
		src: url('//cdn.jsdelivr.net/korean-webfonts/1/corps/godo/Godo/GodoB.woff2') format('woff2'), url('//cdn.jsdelivr.net/korean-webfonts/1/corps/godo/Godo/GodoB.woff') format('woff');
	}
	.f_Godo{font-family: 'Godo', sans-serif !important;}

	@font-face {
		font-family: 'GmarketSans'; font-style: normal; font-weight: 300;
		src: url('https://cdn.jsdelivr.net/gh/projectnoonnu/noonfonts_2001@1.1/GmarketSansLight.woff') format('woff');
	}
	@font-face {
		font-family: 'GmarketSans'; font-style: normal; font-weight: normal;
		src: url('https://cdn.jsdelivr.net/gh/projectnoonnu/noonfonts_2001@1.1/GmarketSansMedium.woff') format('woff');
	}
	@font-face {
		font-family: 'GmarketSans'; font-style: normal; font-weight: bold;
		src: url('https://cdn.jsdelivr.net/gh/projectnoonnu/noonfonts_2001@1.1/GmarketSansBold.woff') format('woff');
	}
	.f_GmarketSans{font-family: 'GmarketSans', sans-serif !important;}

	@import url('https://cdn.rawgit.com/innks/NanumSquareRound/master/nanumsquareround.min.css');
	.f_NanumSquareRound{font-family:'NanumSquareRound', sans-serif !important;}


	@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap');
	.f_Poppins{font-family: 'Poppins', sans-serif !important;}

	@import url('https://fonts.googleapis.com/css2?family=Play:wght@400;700&display=swap');
	.f_Play{font-family: 'Play', sans-serif !important;}

	@import url('https://fonts.googleapis.com/css2?family=Prompt:wght@100;200;300;400;500;600;700;800;900&display=swap');
	.f_Prompt{font-family: 'Prompt', sans-serif !important;}

	@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap');
	.f_Montserrat{font-family: 'Montserrat', sans-serif !important;}

	@import url('https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@100;300;400;500;700;900&display=swap');
	.f_NotoSansKR{font-family: 'Noto Sans KR', sans-serif !important;}

	@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700;900&display=swap');
	.f_Roboto{font-family:'Roboto', sans-serif !important;}

	@import url('https://fonts.googleapis.com/css2?family=Oxygen:wght@300;400;700&display=swap');
	.f_Oxygen{font-family:'Oxygen', sans-serif !important;}

	@import url('https://fonts.googleapis.com/css2?family=Nanum+Myeongjo:wght@400;700;800&display=swap');
	.f_NanumMyeongjo{font-family:'Nanum Myeongjo', sans-serif !important;}

	.font_Guide{padding:6rem 3rem; font-size:1.8rem; line-height:1.6; color: #000;}
	.font_Guide a{margin-right:3rem; display:inline-block; vertical-align: top; position: relative;}
	.font_Guide a>span{position: relative; z-index:2;}
	.font_Guide a:before{content: ""; width: 100%; height: 0.5rem; background-color: #eee; display: inline-block; position:absolute; left: 0; bottom:0.4rem;}
	.font_Guide a:hover{color: #fc3737;}
	.font_Guide a:hover:before{background-color: #fff;}
	.font_Guide code{margin-top:1rem; padding:1rem; background-color: #eee; font-weight: 300; font-size:1.4rem; color: #333; display:block;}
	.font_Guide>p{}
	.font_Guide>p>span{color: #fc3737;}
	.font_Guide>*+p{margin-top:3rem;}

	.f_HanwhaGothic{font-family:'HanwhaGothic', sans-serif !important;}
	.f_Hanwha{font-family:'Hanwha', sans-serif !important;}
</style>
<div class="font_Guide">
	<a href="https://fonts.google.com/" target="_blank"><span>구글 폰트 바로가기</span></a>
	<a href="https://noonnu.cc/index" target="_blank"><span>눈누 폰트 바로가기</span></a>

	<p data-font="Mont">Mont [Regular-400, Bold-600]<br />Mont ExtraLight DEMO font</p>
	<p data-font="Mont"><strong>Mont [Regular-400, Bold-600]<br />Mont ExtraLight DEMO font</strong></p>
	<img src="https://www.cufonfonts.com/images/21818/mont-extralight-demo-font-list-preview.png" alt="">
	<code>
		@import url('//spoqa.github.io/spoqa-han-sans/css/SpoqaHanSansNeo.css');<br />
		font-family:'Spoqa Han Sans Neo', sans-serif;
	</code>

	<p class="f_HanwhaGothic">Hanwha Gothic [Thin-100, Regular-400, Bold-700, havy-900]<br />가나다라마바사아자차카타파하</p>
	<code>
		@import url('//host.iceserver.co.kr/theme/prix/font_hanwha/font.css');<br />
		font-family:'HanwhaGothic', sans-serif;
	</code>

	<p class="f_Hanwha">Hanwha [Regular-400, Bold-600]<br />가나다라마바사아자차카타파하</p>
	<code>
		@import url('//host.iceserver.co.kr/theme/prix/font_hanwha/font.css');<br />
		font-family:'Hanwha', sans-serif;
	</code>



	<p class="f_SpoqaHanSansNeo">Spoqa Han Sans Neo [Thin-100, Light-300, Regular-400, Bold-700]<br />가나다라마바사아자차카타파하</p>
	<code>
		@import url('//spoqa.github.io/spoqa-han-sans/css/SpoqaHanSansNeo.css');<br />
		font-family:'Spoqa Han Sans Neo', sans-serif;
	</code>

	<p class="f_SpoqaHanSans">Spoqa Han Sans [Thin-100, Light-300, Regular-400, Bold-700]<br />가나다라마바사아자차카타파하</p>
	<code>
		@import url('//spoqa.github.io/spoqa-han-sans/css/SpoqaHanSans-kr.css');<br />
		font-family:'Spoqa Han Sans', sans-serif;
	</code>

	<p class="f_Godo">Godo [Regular-400, Bold-700]<br />가나다라마바사아자차카타파하</p>
	<code>
		@font-face {<br />
			font-family: 'Godo'; font-style: normal; font-weight: 400;<br />
			src: url('//cdn.jsdelivr.net/korean-webfonts/1/corps/godo/Godo/GodoM.woff2') format('woff2'), url('//cdn.jsdelivr.net/korean-webfonts/1/corps/godo/Godo/GodoM.woff') format('woff');<br />
		}<br />
		@font-face {<br />
			font-family: 'Godo'; font-style: normal; font-weight: 700;<br />
			src: url('//cdn.jsdelivr.net/korean-webfonts/1/corps/godo/Godo/GodoB.woff2') format('woff2'), url('//cdn.jsdelivr.net/korean-webfonts/1/corps/godo/Godo/GodoB.woff') format('woff');<br />
		}<br />
		font-family:'Godo', sans-serif;
	</code>

	<p class="f_GmarketSans">GmarketSans [Light-300, Regular-400, Bold-700]<br />가나다라마바사아자차카타파하</p>
	<code>
		@font-face {<br />
			font-family: 'GmarketSans'; font-style: normal; font-weight: 300;<br />
			src: url('https://cdn.jsdelivr.net/gh/projectnoonnu/noonfonts_2001@1.1/GmarketSansLight.woff') format('woff');<br />
		}<br />
		@font-face {<br />
			font-family: 'GmarketSans'; font-style: normal; font-weight: normal;<br />
			src: url('https://cdn.jsdelivr.net/gh/projectnoonnu/noonfonts_2001@1.1/GmarketSansMedium.woff') format('woff');<br />
		}<br />
		@font-face {<br />
			font-family: 'GmarketSans'; font-style: normal; font-weight: bold;<br />
			src: url('https://cdn.jsdelivr.net/gh/projectnoonnu/noonfonts_2001@1.1/GmarketSansBold.woff') format('woff');<br />
		}<br />
		font-family:'GmarketSans', sans-serif;
	</code>

	<p class="f_NanumSquareRound">NanumSquareRound [Light-300, Regular-400, Bold-700, ExtraBold-800]<br />가나다라마바사아자차카타파하</p>
	<code>
		@import url('https://cdn.rawgit.com/innks/NanumSquareRound/master/nanumsquareround.min.css');<br />
		font-family:'NanumSquareRound', sans-serif;
	</code>

	<p class="f_Poppins"><span>[GOOGLE]</span> Poppins [Thin-100, ExtraLight-200, Light-300, Regular-400, Medium-500, SemiBold-600, Bold-700, ExtraBold-800, Black-900]<br />가나다라마바사아자차카타파하</p>
	<code>
		@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap');<br />
		font-family:'Poppins', sans-serif;
	</code>

	<p class="f_Play"><span>[GOOGLE]</span> Play [Regular-400, Bold-700]<br />가나다라마바사아자차카타파하</p>
	<code>
		@import url('https://fonts.googleapis.com/css2?family=Play:wght@400;700&display=swap');<br />
		font-family:'Play', sans-serif;
	</code>

	<p class="f_Prompt"><span>[GOOGLE]</span> Prompt [Thin-100, ExtraLight-200, Light-300, Regular-400, Medium-500, SemiBold-600, Bold-700, ExtraBold-800, Black-900]<br />가나다라마바사아자차카타파하</p>
	<code>
		@import url('https://fonts.googleapis.com/css2?family=Prompt:wght@100;200;300;400;500;600;700;800;900&display=swap');<br />
		font-family:'Prompt', sans-serif;
	</code>

	<p class="f_Montserrat"><span>[GOOGLE]</span> Montserrat [Thin-100, ExtraLight-200, Light-300, Regular-400, Medium-500, SemiBold-600, Bold-700, ExtraBold-800, Black-900]<br />가나다라마바사아자차카타파하</p>
	<code>
		@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap');<br />
		font-family:'Montserrat', sans-serif;
	</code>

	<p class="f_NotoSansKR"><span>[GOOGLE]</span> Noto Sans KR [Thin-100, Light-300, Regular-400, Medium-500, Bold-700, Black-900]<br />가나다라마바사아자차카타파하</p>
	<code>
		@import url('https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@100;300;400;500;700;900&display=swap');<br />
		font-family:'Noto Sans KR', sans-serif;
	</code>

	<p class="f_Roboto"><span>[GOOGLE]</span> Roboto [Thin-100, Light-300, Regular-400, Medium-500, Bold-700, Black-900]<br />가나다라마바사아자차카타파하</p>
	<code>
		@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700;900&display=swap');<br />
		font-family:'Roboto', sans-serif;
	</code>

	<p class="f_Oxygen"><span>[GOOGLE]</span> Oxygen [Light-300, Regular-400, Bold-700]<br />가나다라마바사아자차카타파하</p>
	<code>
		@import url('https://fonts.googleapis.com/css2?family=Oxygen:wght@300;400;700&display=swap');<br />
		font-family:'Oxygen', sans-serif;
	</code>

	<p class="f_NanumMyeongjo"><span>[GOOGLE]</span> Nanum Myeongjo [Regular-400, Bold-700, ExtraBold-800]<br />가나다라마바사아자차카타파하</p>
	<code>
		@import url('https://fonts.googleapis.com/css2?family=Nanum+Myeongjo:wght@400;700;800&display=swap');<br />
		font-family:'Nanum Myeongjo', sans-serif;
	</code>
</div>

<?php include_once(G5_THEME_PATH . '/tail.php'); ?>