<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
$cate=explode("|",$board[bo_category_list]);
$c_count=sql_fetch("select count(*) as cnt from g5_write_".$bo_table);
$cate_count[전체]=$c_count[cnt];
foreach($cate as $value){
	$c_count=sql_fetch("select count(*) as cnt from g5_write_".$bo_table." where ca_name='".$value."'");
	$cate_count[$value]=$c_count[cnt];
	//$category_option=str_replace("<span>".$value."</span>","<span>".$value." ".number_format($cate_count[$value])."</span>",$category_option);
}
//$category_option=str_replace("<span>All</span>","<span>All ".number_format($cate_count[전체])."</span>",$category_option);
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

<div id="promotion">
	<ul class="promotion">
		<?php for ($i=0; $i<count($list); $i++) {
			if($list[$i]['wr_1']){
				$string = nl2br($list[$i]['wr_1']);
				if(strpos($string,"&")){
					$string = substr($string,0,strpos($string,"&"));
				}
				if(strcmp()){}
				//echo $string;
				$string = str_replace('youtu.be/','youtube.com/embed/',$string);
				$string_url = str_replace('/watch?v=','/embed/',$string);
				$string_url2 = str_replace('/shorts/','/embed/',$string_url);
				//$string_url3 = str_replace('https://youtube.com/shorts/','/embed/',$string_url2);
				/*
				$string_url = str_replace('https://www.youtube.com/shorts/','https://www.youtube.com/embed/',$string);
				https://www.youtube.com/shorts/tt7sWBiXVMk?feature=share
				https://youtube.com/shorts/a3hF6Y25Wes

				<iframe width="441" height="784" src="https://www.youtube.com/embed/tt7sWBiXVMk" title="거래처 찾을땐, 매커멕스!" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
				// echo $string;
				*/
			}
			unset($wr_2);
			$wr_2=explode("=",$list[$i]['wr_2']);
		?>
		<li>
			<!--
			<div class="promotion_info">
				<h4 class="company"><?php echo $list[$i]['subject'] ?></h4>
				<a href="<?php echo $list[$i]['wr_2'] ?>" target="_blank" class="more">상세보기</a>
			</div>
			-->
			<div class="promotion_iframe">
				<iframe src="<?php echo $string_url2 ?>"
					frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" loading="lazy"
					allowfullscreen>
				</iframe>
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