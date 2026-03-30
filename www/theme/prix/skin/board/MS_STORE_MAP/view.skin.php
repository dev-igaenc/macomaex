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
	<?php } ?>

	<div class="board_view">
		<table>
			<thead>
			<tr>
				<th>
					<?php if ($category_name) { ?>
					<p class="view_cate"><?php echo $view['ca_name']; // 분류 출력 끝 ?></p>
					<?php } ?>

					<div class="view_title"><?php echo cut_str(get_text($view['wr_subject']), 9999); // 글제목 출력 ?></div>
					<!-- <div class="view_tit">서브 타이틀</div> -->
					<div class="view_info">
						<div class="view_txt">
							<p class="txt"><strong>매장주소</strong>&nbsp;<?php echo $view['wr_4'] ?></p>
							<p class="txt"><strong>전화번호</strong>&nbsp;<?php echo $view['wr_5'] ?></p>
						</div>
					</div>
				</th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td class="view_cont">
					<!-- 본문 내용 시작 { -->
					<div id="bo_v_con">
						<div class="store_view">
							<div class="left_box">
								<div id="storeMap"></div>
								<script type="text/javascript" src="//dapi.kakao.com/v2/maps/sdk.js?appkey=<?=$config['cf_10']?>&libraries=services"></script>
								<script>
									var mapContainer = document.getElementById("storeMap"), // 지도의 중심좌표
										mapOption = {
											center: new daum.maps.LatLng(<?=$view[wr_8]?>, <?=$view[wr_9]?>), // 지도의 중심좌표
											level: 2 // 지도의 확대 레벨
										};
									var map = new daum.maps.Map(mapContainer, mapOption); // 지도를 생성합니다


									// 지도 확대 축소를 제어할 수 있는  줌 컨트롤을 생성합니다
									var zoomControl = new daum.maps.ZoomControl();
									map.addControl(zoomControl, daum.maps.ControlPosition.RIGHT);

									var bounds = new kakao.maps.LatLngBounds();

									// 지도에 마커를 표시합니다
									var marker = new daum.maps.Marker({
										map: map,
										position: new daum.maps.LatLng(<?=$view[wr_8]?>, <?=$view[wr_9]?>)
									});

									var contentStr = "<div class='customoverlay'><p class='coBox'><span class='title'><?=$view[wr_subject]?></span></p></div>";
									var overlay = new kakao.maps.CustomOverlay({
										map: map,
										position: new kakao.maps.LatLng(<?=$view[wr_8]?>, <?=$view[wr_9]?>),
										content: contentStr,
										yAnchor: 1
									});

									bounds.extend(new kakao.maps.LatLng(<?=$view[wr_8]?>, <?=$view[wr_9]?>));

									$(window).on('throttledresize', function(){// Resize Event
										setTimeout(function(){
											map.setBounds(bounds, 30, 30, 10, 30);
										}, 10);
									}).resize();
								</script>
							</div>

							<div class="right_box">
								<div class="storePhoto">
									<div class="store-big"><div class="store-big_in">
										<?php
											if($view['file'][count]){
												for($i=0; $i<$view['file']['count'];$i++){
													if($view['file'][$i]['view']){
														if($i>=5) continue;
														//echo "<div class='slider'><img src='{$view['file'][$i][path]}/{$view['file'][$i][file]}' class='ofi'></div>";
														echo "<div class='slider' style='background-image: url(\"{$view['file'][$i][path]}/{$view['file'][$i][file]}\");'></div>";
													}
												}
											}else{
												echo "<div class='slider' style='background-image: url(\"/img/noimage.jpg\");'></div>";
											}
										?>
									</div><p class="store-big-cont op"><span class="t"></span><span class="l">/</span><span class="t2"></span></p></div>

									<script type="text/javascript">
										$(window).load(function(){	// $(".testText").text("off2");
											//$(".store-small .store-small_in").append($(".store-big .store-big_in").html());
											var ttl = $(".store-big .store-big_in .slider").length;

											if(ttl > 1){
												$(".store-big .store-big-cont").removeClass("op");
												$(".store-big .store-big-cont .t").html("1");
												$(".store-big .store-big-cont .t2").html(ttl);

												$(".store-big .store-big_in").slick({
													slidesToShow: 1,
													slidesToScroll: 1,
													//infinite: false,
													arrows: true,
													dots: false
													//fade: true,
												});

												$(".store-big .store-big_in").on('beforeChange', function(event, slick, currentSlide, nextSlide){
													$(".store-big .store-big-cont .t").html(nextSlide+1);
													//console.log(ttl+"__"+nextSlide+"__"+currentSlide);
												});
											}
										});
									</script>
								</div>
							</div>
						</div>

						<?php echo get_view_thumbnail($view['content']); ?>
					</div>
					</div>
					<!-- } 본문 내용 끝 -->

					<?php
					$cnt = 0;
					if ($view['file']['count']) {
						for ($i=0; $i<count($view['file']); $i++) {
							if (isset($view['file'][$i]['source']) && $view['file'][$i]['source'] && !$view['file'][$i]['view'])
								$cnt++;
						}
					}
					?>
					<?php if($cnt) { ?>
					<div class="view_file">
						<?php
						// 가변 파일
						for ($i=0; $i<count($view['file']); $i++) {
							if (isset($view['file'][$i]['source']) && $view['file'][$i]['source'] && !$view['file'][$i]['view']) {
						?>
						<a href="<?php echo $view['file'][$i]['href'];  ?>"><?php echo $view['file'][$i]['source'] ?>
							<!-- <?php echo $view['file'][$i]['content'] ?> (<?php echo $view['file'][$i]['size'] ?>)
							<span class="bo_v_file_cnt"><?php echo $view['file'][$i]['download'] ?>회 다운로드 | DATE : <?php echo $view['file'][$i]['datetime'] ?></span> -->
						</a>
						<?php }  } ?>
					</div>
					<?php } ?>
				</td>
			</tr>
			</tbody>
		</table>
	</div>

	<?php if ($prev_href || $next_href) { ?>
	<div class="pageNavigation">
		<dl class="prev">
			<dt>이전글</dt>
			<dd>
				<?php if ($prev_href) { ?>
					<a href="<?php echo $prev_href ?>"><?php echo $prev_wr_subject;?></a>
					<p class="day"><?php echo date("Y.m.d", strtotime($prev_wr_date)); ?></p>
				<?php }else{ ?>
					이전글이 없습니다.
				<?php } ?>
			</dd>
		</dl>
		<dl class="next">
			<dt>다음글</dt>
			<dd>
				<?php if ($next_href) { ?>
					<a href="<?php echo $next_href ?>"><?php echo $next_wr_subject;?></a>
					<p class="day"><?php echo date("Y.m.d", strtotime($next_wr_date)); ?></p>
				<?php }else{ ?>
					다음글이 없습니다.
				<?php } ?>
			</dd>
		</dl>
	</div>
	<?php } ?>

	<div class="board_btn end">
		<ul class="board_user">
			<li><a href="<?php echo $list_href ?>" class="click write big">목록</a></li>
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

		function board_move(href){
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

	function excute_good(href, $el, $tx){
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
<script type="text/javascript">
$(function(){
	$("#imgList li>img").hover(function(){
		$("#mainImg img").attr('src', $(this).attr('src'));
	});

});
</script>