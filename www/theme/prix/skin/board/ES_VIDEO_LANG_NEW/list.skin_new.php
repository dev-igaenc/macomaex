<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
$cate=explode("|",$board['bo_category_list']);
$c_count=sql_fetch("select count(*) as cnt from g5_write_".$bo_table);
$cate_count['전체']=$c_count['cnt'];
foreach($cate as $value){
	$c_count=sql_fetch("select count(*) as cnt from g5_write_".$bo_table." where ca_name='".$value."'");
	$cate_count[$value]=$c_count['cnt'];
	$category_option=str_replace("<span>".$value."</span>","<span>".$value." ".number_format($cate_count[$value])."</span>",$category_option);
}
$category_option=str_replace("<span>All</span>","<span>All ".number_format($cate_count['전체'])."</span>",$category_option);
?>
<style>
	#pageTop,
	#sVisual{display: none}
</style>
<div id="sub-visual"><div class="inConts">
	<h2>짧지만 긴-여운.</h2>
	<p>숏폼으로 우리회사/제품을 광고하세요.</p>
	<div btn><a href="/ko/plan.php">멤버십 업그레이드 하기</a></div>
</div></div>

<div id="page-title">
	<div><span><img src='/images/btn_youtube.svg' alt='youtube'></span><strong>기업홍보관</strong></div>
	<p>기업 홍보를 원하시는 분은 admin@macomaex.com 으로 숏폼 영상 링크를 보내주세요.</p>
</div>

<!-- 게시판 카테고리 시작 { -->
<?php if ($is_category) { ?>
<nav id="bo_cate" class="multi-tab">
	<h2 class="name"><strong><?php echo $sca ?></strong><span><img src="/images/arr_message.png" alt=""></span></h2>
	<ul id="bo_cate_ul" class="multi-list">
		<?php echo $category_option ?>
	</ul>
</nav>
<?php } ?>
<!-- } 게시판 카테고리 끝 -->
<style>
.promotion_iframe {
    position: relative;
    width: 100%;
    padding-top: 177.78%; /* 9:16 비율 → 16 / 9 * 100 = 177.78% */
    background: #000;
    overflow: hidden;
}

.promotion_iframe iframe,
.yt-thumb-vertical {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.yt-thumb-vertical {
    background-color: #000;
    cursor: pointer;
}

.yt-thumb-vertical img {
    object-fit: cover;
    width: 100%;
    height: 100%;
}

.play-button {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 68px;
    height: 48px;
    background: url('/images/play-button.png') no-repeat center center;
    background-size: contain;
    transform: translate(-50%, -50%);
}
</style>
<div id="promotion">
	<ul class="promotion">
<?php for ($i=0; $i<count($list); $i++) {
    if ($list[$i]['wr_1']) {
        $string = trim($list[$i]['wr_1']);
        if (strpos($string,"&")) {
            $string = substr($string,0,strpos($string,"&"));
        }
		

        $string = str_replace('youtu.be/','youtube.com/embed/',$string);
        $string_url = str_replace('/watch?v=','/embed/',$string);
        $string_url2 = str_replace('/shorts/','/embed/',$string_url);
		//$string= str_replace("https://www.youtube.com/shorts/","https://www.youtube.com/watch?v=",$list[$i]['wr_1']);
        // 썸네일용 video ID 추출
        parse_str(parse_url($string, PHP_URL_QUERY), $query_vars);
        $video_id = $query_vars['v'] ?? basename($string_url2);
    }
    unset($wr_2);
    $wr_2 = explode("=", $list[$i]['wr_2']);
?>
<li>
    <div class="promotion_iframe yt-lazy" data-iframe-url="https://www.youtube.com/embed/<?php echo $video_id ?>">
        <div class="yt-thumb-vertical" onclick="loadIframe(this)">
            <img src="https://img.youtube.com/vi/<?php echo $video_id ?>/hqdefault.jpg" alt="YouTube thumbnail">
            <div class="play-button"></div>
        </div>
        <a href="/ko/company_view.php?cd=<?=simple_crypt($wr_2[1])?>" target="_blank" class="more">기업 상세보기</a>
    </div>
</li>
<?php } ?>
		<?php if (count($list) == 0) { echo "<li class=\"empty_list\">".$lang_list_no."</li>"; } ?>
	 </ul>
</div>

	<!-- 페이지 -->
	<?php echo $write_pages; ?>
	<!-- 페이지 -->

	<script>
function loadIframe(el) {
    var container = el.parentElement;
    var iframeUrl = container.getAttribute('data-iframe-url');
    var iframe = document.createElement('iframe');
    iframe.setAttribute('src', iframeUrl);
    iframe.setAttribute('frameborder', '0');
    iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture');
    iframe.setAttribute('allowfullscreen', true);
    iframe.setAttribute('loading', 'lazy');
    container.innerHTML = '';
    container.appendChild(iframe);
}
</script>