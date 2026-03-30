<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>

<!-- 게시판 카테고리 시작 { -->
<?php if ($is_category) { ?>
<nav id="bo_cate" class="category_top">
	<h2><?php echo $board['bo_subject'] ?> 카테고리</h2>
	<ul id="bo_cate_ul">
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
		?>
        <li>
            <div class="promotion_info">
                <h4 class="company"><?php echo $list[$i]['subject'] ?></h4>
                <a href="<?php echo $list[$i]['wr_2'] ?>" target="_blank" class="more">상세보기</a>
            </div>
            <div class="promotion_iframe">
                <iframe src="<?php echo $string_url2 ?>"
                    frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen>
                </iframe>
            </div>
        </li>
		<?php } ?>
		<?php if (count($list) == 0) { echo "<li class=\"empty_list\">".$lang_list_no."</li>"; } ?>
     </ul>
</div>

	<!-- 페이지 -->
	<?php echo $write_pages; ?>
	<!-- 페이지 -->