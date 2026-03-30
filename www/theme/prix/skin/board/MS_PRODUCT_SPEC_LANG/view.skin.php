<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH . '/thumbnail.lib.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>

<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>

<!-- 게시물 읽기 시작 { -->
<?php if ($is_admin == 'super' || $is_auth) {  ?>
    <div class="board_btn end">
        <?php ob_start(); ?>
        <ul class="board_user">
            <?php if ($admin_href) { ?><li><a href="<?php echo $admin_href ?>" class="click admin"><i class="fa fa-cog fa-spin fa-fw"></i> 관리자</a></li><?php } ?>

            <?php if ($is_admin == 'super' || $is_auth) {  ?>
                <?php if ($update_href) { ?><li><a href="<?php echo $update_href ?>" class="click"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> 수정</a></li><?php } ?>
                <?php if ($delete_href) { ?><li><a href="<?php echo $delete_href ?>" class="click" onclick="del(this.href); return false;"><i class="fa fa-trash-o" aria-hidden="true"></i> 삭제</a></li><?php } ?>
                <!--
			<?php if ($reply_href) { ?><li><a href="<?php echo $reply_href ?>" class="click"><i class="fa fa-reply" aria-hidden="true"></i> 답변</a></li><?php } ?>
			<?php if ($copy_href) { ?><li><a href="<?php echo $copy_href ?>" class="click" onclick="board_move(this.href); return false;"><i class="fa fa-files-o" aria-hidden="true"></i> 복사</a></li><?php } ?>
			<?php if ($move_href) { ?><li><a href="<?php echo $move_href ?>" class="click" onclick="board_move(this.href); return false;"><i class="fa fa-arrows" aria-hidden="true"></i> 이동</a></li><?php } ?>
			<?php if ($search_href) { ?><li><a href="<?php echo $search_href ?>" class="click"><i class="fa fa-search" aria-hidden="true"></i> 검색</a></li><?php } ?>
			-->
            <?php } ?>

            <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="click write">글쓰기 <i class="fa fa-pencil" aria-hidden="true"></i></a></li><?php } ?>
        </ul>
        <?php
        $link_buttons = ob_get_contents();
        ob_end_flush();
        ?>
    </div>
<?php }

//echo $view['file'][count].", ";
//echo $view['file']['count'].", ";
//echo $view['file'][$i]['view'];
?>

<div class="product_view">
    <div class="left_box">
        <div class="productPhoto">
            <div class="product-big">
                <div class="product-big_in">
                    <?php
                    if ($view['file'][count] > 1 && $view['file'][count] < 9) {
                        $img_many = "many";
                        for ($i = 1; $i < $view['file']['count']; $i++) {

                            if ($view['file'][$i]['view']) {
                                if ($i >= 6) continue;
                                //echo "<div class='slider'><img src='{$view['file'][$i][path]}/{$view['file'][$i][file]}' class='ofi'></div>";
                                echo "<div class='slider' style='background-image: url(\"{$view['file'][$i][path]}/{$view['file'][$i][file]}\");' data-src='{$view['file'][$i][path]}/{$view['file'][$i][file]}'><img src='/theme/prix/img/thumb/thumb_580x460.png' alt=''></div>";
                            }
                        }
                    } else {
                        echo "<div class='slider' style='background-image: url(\"/img/noimage.jpg\");'><img src='/theme/prix/img/thumb/thumb_580x460.png' alt=''></div>";
                    }
                    ?>
                </div>
            </div>
            <div class="product-small">
                <div class="product-small_in"></div>
            </div>

            <script type="text/javascript">
                $(window).load(function() { // $(".testText").text("off2");
                    var arr_img = []; // 이미지 변수 담을곳
                    $(".product-big .slider").each(function(index) { // 00. 지정 컨테이너 안에 이미지를 변수에 담기
                        arr_img[index] = $(this).attr("data-src");
                    });
                    $("body").on("click", ".product-big .slider", function() {
                        var idx = $(".product-big .slider").index($(this));
                        Fancybox.bind(".product-big [data-src]", {
                            placeFocusBack: false,
                            groupAll: true,
                            infinite: false,
                            //startIndex: idx,
                            Thumbs: {
                                autoStart: false,
                            },
                            Toolbar: {
                                display: [
                                    //{ id: "prev", position: "center" },
                                    {
                                        id: "counter",
                                        position: "left"
                                    },
                                    //{ id: "next", position: "center" },
                                    "zoom",
                                    //"slideshow",
                                    //"fullscreen",
                                    //"download",
                                    //"thumbs",
                                    "close",
                                ],
                            },
                        });
                    })
                    var ttl = $(".product-big .product-big_in .slider").length;
                    if (ttl > 1) {
                        $(".product-small .product-small_in").append($(".product-big .product-big_in").html());
                        $(".product-big .product-big_in").slick({
                            infinite: false,
                            slidesToShow: 1,
                            slidesToScroll: 1,
                            //infinite: false,
                            arrows: false,
                            dots: false,
                            asNavFor: ".product-small .product-small_in"
                            //fade: true,
                        });
                        $(".product-small .product-small_in").slick({
                            infinite: false,
                            slidesToShow: 5,
                            slidesToScroll: 1,
                            asNavFor: ".product-big .product-big_in",
                            arrows: true,
                            dots: false,
                            focusOnSelect: true
                        });
                    }
                });
            </script>
        </div>
    </div>

    <div class="right_box <?= $img_many ?>">
        <div class="productText">
            <div class="view-hGroup">
                <div class="title"><?php echo cut_str(get_text($view['wr_subject']), 9999); // 글제목 출력 ?></div>
                <div class="tit"><?php echo $view['wr_1'] ?></div>
            </div>
            <div class="option">
				<?
				$arr_SpecNm = explode("^|^",$write['wr_9']);
				$arr_SpecCon = explode("^|^",$write['wr_10']);

				for($i=0; $i<sizeof($arr_SpecNm); $i++){
					$specNm = $arr_SpecNm[$i];
					$specCon = $arr_SpecCon[$i];

					if (str_replace(" ","",$specNm)!="" || str_replace(" ","",$specCon)!=""){
				?>
                <dl>
                    <dt><?=$specNm?></dt>
                    <dd><?=$specCon?></dd>
                </dl>
				<?
					}
				}
				?>
            </div>
        </div>
        <div class="board_btn end">
            <ul class="board_user">
				<? if ($view['file'][6]['source']){ ?>
                <li>
					<a href="<?php echo $view['file'][6]['href'];  ?>" download="" class="click write big icon">제품 카달로그 <img src="/theme/prix/img/icon_down_w.png" alt="" title=""></a>
				</li>
				<? } ?>
                <li><a href="<?php echo $list_href ?>" class="click write big"><?= $lang_btn_list ?></a></li>
            </ul>
        </div>
    </div>
</div>
<div class="product_view_btm">
    <div class="product_view_btm_btn">
        <p class="pvb_re">상세정보</p>
    </div>
    <?php echo get_view_thumbnail($view['content']); ?>
</div>

<?php if ($prev_href || $next_href) { ?>
    <div class="pageNavigation">
        <dl class="prev">
            <dt><?= $lang_view_prev ?></dt>
            <dd>
                <?php if ($prev_href) { ?>
                    <a href="<?php echo $prev_href ?>"><?php echo $prev_wr_subject; ?></a>
                    <p class="day"><?php echo date("Y.m.d", strtotime($prev_wr_date)); ?></p>
                <?php } else { ?>
                    <?= $lang_view_prev_long ?>
                <?php } ?>
            </dd>
        </dl>
        <dl class="next">
            <dt><?= $lang_view_next ?></dt>
            <dd>
                <?php if ($next_href) { ?>
                    <a href="<?php echo $next_href ?>"><?php echo $next_wr_subject; ?></a>
                    <p class="day"><?php echo date("Y.m.d", strtotime($next_wr_date)); ?></p>
                <?php } else { ?>
                    <?= $lang_view_next_long ?>
                <?php } ?>
            </dd>
        </dl>
    </div>
<?php } ?>

<div class="board_btn end">
    <ul class="board_user">
        <li><a href="<?php echo $list_href ?>" class="click write big"><?= $lang_btn_list ?></a></li>
    </ul>
</div>

<?php
// 코멘트 입출력
//include_once(G5_BBS_PATH.'/view_comment.php');
?>
<!-- } 게시판 읽기 끝 -->

<script>
    <?php if ($board['bo_download_point'] < 0) { ?>
        $(function() {
            $("a.view_file_download").click(function() {
                if (!g5_is_member) {
                    alert("다운로드 권한이 없습니다.\n회원이시라면 로그인 후 이용해 보십시오.");
                    return false;
                }

                var msg = "파일을 다운로드 하시면 포인트가 차감(<?php echo number_format($board['bo_download_point']) ?>점)됩니다.\n\n포인트는 게시물당 한번만 차감되며 다음에 다시 다운로드 하셔도 중복하여 차감하지 않습니다.\n\n그래도 다운로드 하시겠습니까?";

                if (confirm(msg)) {
                    var href = $(this).attr("href") + "&js=on";
                    $(this).attr("href", href);

                    return true;
                } else {
                    return false;
                }
            });
        });
    <?php } ?>

    function board_move(href) {
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
            if (this.id == "good_button")
                $tx = $("#bo_v_act_good");
            else
                $tx = $("#bo_v_act_nogood");

            excute_good(this.href, $(this), $tx);
            return false;
        });

        // 이미지 리사이즈
        $("#bo_v_atc").viewimageresize();
    });

    function excute_good(href, $el, $tx) {
        $.post(
            href, {
                js: "on"
            },
            function(data) {
                if (data.error) {
                    alert(data.error);
                    return false;
                }

                if (data.count) {
                    $el.find("strong").text(number_format(String(data.count)));
                    if ($tx.attr("id").search("nogood") > -1) {
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