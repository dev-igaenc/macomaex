<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>

<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>

<!-- 게시물 읽기 시작 { -->
	<?php if ($is_admin == 'super' || $is_auth) {  ?>
	<div class="board_btn end">
		<?php ob_start(); ?>

		<?php
		$link_buttons = ob_get_contents();
		ob_end_flush();
		?>
	</div>
	<?php } ?>

    <div class="product_view prdt_view">
        <div class="left_box">
            <div class="productPhoto">
                <div class="swiper-wrapper">
                    <div class="swiper-slide">
                        <img src="/theme/prix/img/main/prdt_test.jpg" alt="">
                    </div>
                    <div class="swiper-slide">
                        <img src="/theme/prix/img/main/prdt_test.jpg" alt="">
                    </div>
                    <div class="swiper-slide">
                        <img src="/theme/prix/img/main/prdt_test.jpg" alt="">
                    </div>
                    <div class="swiper-slide">
                        <img src="/theme/prix/img/main/prdt_test.jpg" alt="">
                    </div>
                    <div class="swiper-slide">
                        <img src="/theme/prix/img/main/prdt_test.jpg" alt="">
                    </div>
                </div>
                <!-- slide 최대 5개 -->
                <ul class="iconArea">
			    	<?
			    	$icon=explode("||",$view['wr_2']);
			    	foreach($icon AS $value){?>
                    <li><img src="/img/crt_<?=$value?>.jpg" alt=""></li>
			    	<?}?>
                </ul>
                <div class="swiper-button-prev"><img src="/theme/prix/img/sub/prdt_sw_prev.png" alt=""></div>
                <div class="swiper-button-next"><img src="/theme/prix/img/sub/prdt_sw_next.png" alt=""></div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
        <div class="right_box">
            <div class="productText">
                <div class="view-hGroup">
                    <div class="prdt_code">NFH-63354A3BHV</div>
                    <p class="prdt_tit">3M 36배 IP PTZ 카메라 </p>
                    <p class="pdesc">
                        FULL-HD IR 돔 카메라 <br>전동 가변 초점 렌즈(2.8 ~ 12MM) 
                    </p>
                </div>
                <div class="product_point">
			    	<?
			    	$con=explode("||",$view['wr_content']);
			    	if($view['wr_content']){
			    	?>
                    <h4 class="point_tit">주요 기능</h4>
                    <ul class="point_ul">
			    		<?
			    		foreach($con AS $key){
			    			$value=explode("##",$key);
			    			if($value>-1){
			    		?>
                        <li><?=$value[1]?></li>
			    		<?}
			    		}?>
                    </ul>
			    	<?}?>
			    	<a href="<?php echo $list_href ?>" class="list_btn">목록보기</a>
                </div>
            </div>
        </div>
    </div>

    <div class="product_view_btm prdt_view">
        <div class="product_view_btm_btn menu-tab">
            <a href="javascript:void(0);" class="pvb_re active">상세정보</a>
            <a href="javascript:void(0);" class="pvb_re">상세정보</a>
            <a href="javascript:void(0);" class="pvb_re">상세정보</a>
        </div>
        <div class="menu-conts">
            <div class="prdt_cont">123</div>
            <div class="prdt_cont product_desc">
				<?
				$con=explode("||",$view['wr_link1']);
				if($view['wr_link1']){
				?>
                <table>
                    <tbody>
					    <tr>
                            <th colspan="2">모델명</th>
                            <td class="prdt_code">NDH-6L32</td>
                        </tr>
					    <tr>
                            <th colspan="2">영상</th>
                            <td>고정 초점 렌즈 / 3.6mm, F2.0</td>
                        </tr>
					    <tr>
                            <th colspan="2">렌즈</th>
                            <td>고정 초점 렌즈 / 3.6mm, F2.0</td>
                        </tr>
					    <tr>
                            <th colspan="2">화각</th>
                            <td>고정 초점 렌즈 / 3.6mm, F2.0</td>
                        </tr>
					    <tr>
                            <th colspan="2">촬상 소자</th>
                            <td>고정 초점 렌즈 / 3.6mm, F2.0</td>
                        </tr>
					    <tr>
                            <th colspan="2">최저조도</th>
                            <td>고정 초점 렌즈 / 3.6mm, F2.0</td>
                        </tr>
					    <tr>
                            <th rowspan="2">내장 IR</th>
                            <th class="split">수량</th>
                            <td>고정 초점 렌즈 / 3.6mm, F2.0</td>
                        </tr>
                        <tr>
                            <th class="split">가시 거리</th>
                            <td>고정 초점 렌즈 / 3.6mm, F2.0</td>
                        </tr>
                    </tbody>
                </table>
				<?}?>
            </div>
            <div class="prdt_cont product_down">
                <a href="" download class="file_down">
                    <span>
                        <em class="file_name_ko">제품 카다록</em>
                        <em class="file_name_en">Catalog</em>
                    </span>
                </a>
                <a href="" download class="file_down">
                    <span>
                        <em class="file_name_ko">간편사양서</em>
                        <em class="file_name_en">Simple specifications</em>
                    </span>
                </a>
                <a href="" download class="file_down">
                    <span>
                        <em class="file_name_ko">제품 카다록</em>
                        <em class="file_name_en">Catalog</em>
                    </span>
                </a>
                <a href="" download class="file_down">
                    <span>
                        <em class="file_name_ko">간편사양서</em>
                        <em class="file_name_en">Simple specifications</em>
                    </span>
                </a>
                <a href="" download class="file_down">
                    <span>
                        <em class="file_name_ko">간편사양서</em>
                        <em class="file_name_en">Simple specifications</em>
                    </span>
                </a>
            </div>
        </div>
        
    </div>

    <!-- <div class="esca_product_view">
        <div class="thumbArea">
            <div class="thumb">
                <?php
                    // 파일 출력
                    $v_img_count = count($view['file']);
                    if($v_img_count) {
                        for ($i=1; $i<=count($view['file']); $i++) {
                            if ($view['file'][$i]['view']) {
                                echo "<img src='{$view['file'][$i]['path']}/{$view['file'][$i]['file']}'>";
                            }
                        }
                    }
                ?>
            </div>
            <ul class="iconArea">
				<?
				$icon=explode("||",$view['wr_2']);
				foreach($icon AS $value){?>
                <li><img src="/img/crt_<?=$value?>.jpg" alt=""></li>
				<?}?>
            </ul>
        </div>

        <div class="infoArea">
            <div class="product_top">
                <p class="category"><?=$view['ca_name']?></p>
                <h4 class="title"><?=$view['wr_subject']?></h4>
				<p class="pdesc"><?=$view['wr_3']?></p>
            </div>
            <div class="product_point">
				<?
				$con=explode("||",$view['wr_content']);
				if($view['wr_content']){
				?>
                <h4 class="title">제품특징</h4>
                <ul class="point_ul">
					<?
					foreach($con AS $key){
						$value=explode("##",$key);
						if($value>-1){
					?>
                    <li><?=$value[1]?></li>
					<?}
					}?>
                </ul>
				<?}?>
				<? if (isset($view['file'][2]['source']) && $view['file'][2]['source'] && !$view['file'][2]['view']) {?>
                <a href="<?php echo $view['file'][02]['href'];  ?>" download="" class="catalog_down">카다로그 다운로드 <i class="fa fa-download" aria-hidden="true"></i></a>
				<? }?>
            </div>
            <div class="product_desc">
				<?
				$con=explode("||",$view['wr_link1']);
				if($view['wr_link1']){
				?>
                <table>
                    <tbody>
					<?
					foreach($con AS $key){
						$value=explode("##",$key);
						if($value[1]){
					?>
                        <tr>
                            <th><?=$value[0]?></th>
                            <td><?=$value[1]?></td>
                        </tr>
					<?}
					}?>
                    </tbody>
                </table>
				<?}?>
            </div>
        </div>
    </div> -->

    <!-- <?php if ($prev_href || $next_href) { ?>
	<div class="pageNavigation">
		<dl class="prev">
			<dt><?=$lang_view_prev?></dt>
			<dd>
				<?php if ($prev_href) { ?>
					<a href="<?php echo $prev_href ?>"><?php echo $prev_wr_subject;?></a>
					<p class="day"><?php echo date("Y.m.d", strtotime($prev_wr_date)); ?></p>
				<?php }else{ ?>
					<?=$lang_view_prev_long?>
				<?php } ?>
			</dd>
		</dl>
		<dl class="next">
			<dt><?=$lang_view_next?></dt>
			<dd>
				<?php if ($next_href) { ?>
					<a href="<?php echo $next_href ?>"><?php echo $next_wr_subject;?></a>
					<p class="day"><?php echo date("Y.m.d", strtotime($next_wr_date)); ?></p>
				<?php }else{ ?>
					<?=$lang_view_next_long?>
				<?php } ?>
			</dd>
		</dl>
	</div>
	<?php } ?> -->

	<!-- <div class="board_btn end">
		<ul class="board_user">
			<li><a href="<?php echo $list_href ?>" class="click write big"><?=$lang_btn_list?></a></li>
		</ul>
	</div> -->

	<?php
	// 코멘트 입출력
	//include_once(G5_BBS_PATH.'/view_comment.php');
	?>
<!-- } 게시판 읽기 끝 -->

<script>
    const prdtView = new Swiper('.productPhoto', {
        spaceBetween: 0,
        slidesPerView: 1,
        direction: 'horizontal',
        loop: true,
        speed: 1500,
        allowTouchMove: false,
        // autoplay: {
        //     delay: 4000,
        // },
        // Navigation arrows
        navigation: {
            nextEl: '.productPhoto .swiper-button-next',
            prevEl: '.productPhoto .swiper-button-prev',
        },
        pagination: {
            el: '.productPhoto .swiper-pagination',
            clickable: true,
        },
    });

    $(".menu-tab>a").click(function () {
        var idx = $(".menu-tab>a").index($(this));
        $(".menu-tab>a").each(function (index) {
            if (idx == index) {
                $(this).addClass("active");
                $(".menu-conts > .prdt_cont").eq(index).show()
            } else {
                $(this).removeClass("active");
                $(".menu-conts > .prdt_cont").eq(index).hide()
            }
        });
    }).filter(":first").click();

<?php if ($board['bo_download_point'] < 0) { ?>
$(function() {
    $("a.view_file_download").click(function() {
        if(!g5_is_member) {
            alert("다운로드 권한이 없습니다.\n회원이시라면 로그인 후 이용해 보십시오.");
            return false;
        }

        var msg = "파일을 다운로드 하시면 포인트가 차감(<?php echo number_format($board['bo_download_point']) ?>점)됩니다.\n\n포인트는 게시물당 한번만 차감되며 다음에 다시 다운로드 하셔도 중복하여 차감하지 않습니다.\n\n그래도 다운로드 하시겠습니까?";

        if(confirm(msg)) {
            var href = $(this).attr("href")+"&js=on";
            $(this).attr("href", href);

            return true;
        } else {
            return false;
        }
    });
});
<?php } ?>

function board_move(href)
{
    window.open(href, "boardmove", "left=50, top=50, width=500, height=550, scrollbars=1");
}
</script>

<script>
$(function() {
    $("a.view_image").click(function() {
        window.open(this.href, "large_image", "location=yes,links=no,toolbar=no,top=10,left=10,width=10,height=10,resizable=yes,scrollbars=no,status=no");
        return false;
    });

    // 추천, 비추천
    $("#good_button, #nogood_button").click(function() {
        var $tx;
        if(this.id == "good_button")
            $tx = $("#bo_v_act_good");
        else
            $tx = $("#bo_v_act_nogood");

        excute_good(this.href, $(this), $tx);
        return false;
    });

    // 이미지 리사이즈
    $("#bo_v_atc").viewimageresize();
});

function excute_good(href, $el, $tx)
{
    $.post(
        href,
        { js: "on" },
        function(data) {
            if(data.error) {
                alert(data.error);
                return false;
            }

            if(data.count) {
                $el.find("strong").text(number_format(String(data.count)));
                if($tx.attr("id").search("nogood") > -1) {
                    $tx.text("이 글을 비추천하셨습니다.");
                    $tx.fadeIn(200).delay(2500).fadeOut(200);
                } else {
                    $tx.text("이 글을 추천하셨습니다.");
                    $tx.fadeIn(200).delay(2500).fadeOut(200);
                }
            }
        }, "json"
    );
}
</script>
<!-- } 게시글 읽기 끝 -->