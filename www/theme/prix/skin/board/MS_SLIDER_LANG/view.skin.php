<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>
<style>
	.look-around-area{width:100%; max-width:128rem; margin:0 auto; font-size:0; line-height:0; position: relative;}
		.look-around-area .slider{background-repeat: no-repeat; background-position: center center; background-size: cover; box-sizing: border-box; position: relative;}
		.look-around-area .look-around-big{width:100%; position: relative;}
		.look-around-area .look-around-big .slider{width:100%;}
		.look-around-area .look-around-big .slider img{width:100%; height:100%; max-height:76rem; object-fit:contain; object-position: center center;}
		.look-around-area .look-around-big .slick-arrow{width:5rem; height:5rem; margin-top:-2.5rem; background-color: rgba(255,255,255,0.8); border:none; font-weight: bold; font-size:2.4rem; line-height:0; color: #666; position: absolute; top:50%; z-index:7; cursor: pointer; border-radius:999px;}
		.look-around-area .look-around-big .slick-arrow.slick-prev{left:2rem;}
		.look-around-area .look-around-big .slick-arrow.slick-next{right:2rem;}
		.look-around-area .look-around-big .slick-arrow.slick-disabled{opacity: 0.3; cursor: default;}

		.look-around-area .look-around-big .slick-arrow:hover{border-color:#1bc0ff;}

		.look-around-area .look-around-small{width:100%; margin:2.5rem 0 0 0; position: relative;}
		.look-around-area .look-around-small .slick-slide{padding:0 1rem;}
		.look-around-area .look-around-small .slider{}
		.look-around-area .look-around-small .slider:after{content: ""; background-color:rgba(0,0,0,0.3); position:absolute; left: 0; top:0px; right:0; bottom:0;}
		.look-around-area .look-around-small .slider img{width:36.7rem; height:21.8rem; object-fit:contain; object-position: center center;}

		.look-around-area .look-around-small .slick-arrow{width:5rem; height:5rem; margin-top:-2.5rem; background-color: rgba(255,255,255,0.8); border:none; font-weight: bold; font-size:2.4rem; line-height:0; color: #666; position: absolute; top:50%; z-index:7; cursor: pointer; border-radius:999px;}
		.look-around-area .look-around-small .slick-arrow.slick-prev{left:13.5rem;}
		.look-around-area .look-around-small .slick-arrow.slick-next{right:13.5rem;}
		.look-around-area .look-around-small .slick-arrow.slick-disabled{opacity: 0.3; cursor: default;}

		.look-around-area .look-around-small .slick-current .slider:before{content: ""; border:2px solid var(--point); position:absolute; left: 0; top:0px; right:0; bottom:0;}
		.look-around-area .look-around-small .slick-current .slider:after{background-color:rgba(0,0,0,0);}

		.look-around-area .look-around-big,
		.look-around-area .look-around-small{opacity: 0; visibility: hidden; transition: opacity 1s ease;}
		.look-around-area .look-around-big.slick-initialized,
		.look-around-area .look-around-small.slick-initialized{visibility: visible; opacity: 1;}
</style>
<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>

<!-- 게시물 읽기 시작 { -->
	<?php if ($is_admin == 'super' || $is_auth) {  ?>
	<div class="board_btn end">
		<?php ob_start(); ?>
		<ul class="board_user">
			<?php if ($admin_href) { ?><li><a href="<?php echo $admin_href ?>" class="click admin"><i class="fa fa-cog fa-spin fa-fw"></i> 관리자</a></li><?php } ?>

			<?php if ($is_admin == 'super' || $is_auth) {  ?>
			<?php if ($update_href) { ?><li><a href="<?php echo $update_href ?>" class="click"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> 수정</a></li><?php } ?>
			<!-- <?php if ($delete_href) { ?><li><a href="<?php echo $delete_href ?>" class="click" onclick="del(this.href); return false;"><i class="fa fa-trash-o" aria-hidden="true"></i> 삭제</a></li><?php } ?> -->
			<!--
			<?php if ($reply_href) { ?><li><a href="<?php echo $reply_href ?>" class="click"><i class="fa fa-reply" aria-hidden="true"></i> 답변</a></li><?php } ?>
			<?php if ($copy_href) { ?><li><a href="<?php echo $copy_href ?>" class="click" onclick="board_move(this.href); return false;"><i class="fa fa-files-o" aria-hidden="true"></i> 복사</a></li><?php } ?>
			<?php if ($move_href) { ?><li><a href="<?php echo $move_href ?>" class="click" onclick="board_move(this.href); return false;"><i class="fa fa-arrows" aria-hidden="true"></i> 이동</a></li><?php } ?>
			<?php if ($search_href) { ?><li><a href="<?php echo $search_href ?>" class="click"><i class="fa fa-search" aria-hidden="true"></i> 검색</a></li><?php } ?>
			-->
			<?php } ?>

			<!-- <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="click write">글쓰기 <i class="fa fa-pencil" aria-hidden="true"></i></a></li><?php } ?> -->
		</ul>
		<?php
		$link_buttons = ob_get_contents();
		ob_end_flush();
		?>
	</div>
	<?php } ?>

	<div class="look-around-area">
		<div class="look-around-big"></div>
		<div class="look-around-small">
			<?php
			// 파일 출력
			$v_img_count = count($view['file']);
			if($v_img_count) {
				//echo "<div id=\"bo_v_img\">\n";
				for ($i=0; $i<=count($view['file']); $i++) {
					if ($view['file'][$i]['view']) {
						//echo $view['file'][$i]['view'];
						echo "<div class=\"slider\"><img src='{$view['file'][$i]['path']}/{$view['file'][$i]['file']}' alt='{$view['file'][$i]['source']}'></div>";
					}
				}
				//echo "</div>\n";
			}
			?>
		</div>
	</div>
	<script type="text/javascript">
		$(window).load(function(){	// $(".testText").text("off2");
			$(".look-around-big").html($('.look-around-small').html()).show();

			$(".look-around-big").slick({
				slidesToShow: 1,
				slidesToScroll: 1,
				adaptiveHeight: true,
				arrows: true,
				prevArrow: '<button class="slick-prev f_arr" aria-label="Previous" type="button">←</button>',
				nextArrow: '<button class="slick-next f_arr" aria-label="Next" type="button">→</button>',
				dots: false,
				fade: true,
				asNavFor: '.look-around-small',
				responsive: [
					{
						breakpoint: 840,
						settings: {
							slidesToShow: 1,
							slidesToScroll: 1,
							arrows: false,
							dots: false,
							fade: true,
							asNavFor: '.look-around-small',
						}
					}
				]
			});
			$('.look-around-small').slick({
				slidesToShow: 3,
				slidesToScroll: 1,
				asNavFor: '.look-around-big',
				arrows: false,
				dots: false,
				centerMode: true,
				centerPadding: '6rem',
				focusOnSelect: true,
				responsive: [
					{
						breakpoint: 640,
						settings: {
							slidesToShow: 1,
							slidesToScroll: 1,
							asNavFor: '.look-around-big',
							arrows: true,
							prevArrow: '<button class="slick-prev f_arr" aria-label="Previous" type="button">←</button>',
							nextArrow: '<button class="slick-next f_arr" aria-label="Next" type="button">→</button>',
							dots: false,
							centerMode: true,
							centerPadding: '13rem',
							focusOnSelect: true,
						}
					}
				]
			});
			/*
			$('.slider-big').on('beforeChange', function(event, slick, currentSlide, nextSlide){
				slideTit_change(nextSlide)
			});
			slideTit_change(0)
			*/
		});
		function slideTit_change(num){
			var tit = $(".slick-slide").eq(num).find(".slider").attr("data-title");
			//$("h4.tit").html(tit)
		}
	</script>

	<?php
	// 코멘트 입출력
	//include_once(G5_BBS_PATH.'/view_comment.php');
	?>
<!-- } 게시판 읽기 끝 -->

<script>
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