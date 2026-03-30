<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);

if($write[wr_8] == null){$write[wr_8] =  37.566400714093284;}
if($write[wr_9] == null){$write[wr_9] = 126.9785391897507;}
$colspan=5;
if ($is_admin) $colspan++;
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

	<!--[s] Search -->
	<form name="fsearch" method="get">
		<input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
		<input type="hidden" name="sca" value="<?php echo $sca ?>">
		<input type="hidden" name="sop" value="and">

		<div class="board_search store2">
			<p class="mobile_Block">
				<select name="wr_6" id="wr_6"></select>
				<select name="wr_7" id="wr_7"></select>
			</p>
			<!-- <select name="sfl" id="sfl">
				<?php echo get_board_sfl_select_options($sfl); ?>
			</select> -->
			<input type="text" name="stx" value="<?php echo stripslashes($stx) ?>" id="stx" class="sch_input" size="25" maxlength="20" placeholder=" 검색어를 입력해주세요">
			<input type="submit" name="" value="검색" />
		</div>
	</form>
	<!--[e] Search -->

	<form name="fboardlist" id="fboardlist" action="<?php echo G5_BBS_URL; ?>/board_list_update.php" onsubmit="return fboardlist_submit(this);" method="post">
		<input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
		<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
		<input type="hidden" name="stx" value="<?php echo $stx ?>">
		<input type="hidden" name="spt" value="<?php echo $spt ?>">
		<input type="hidden" name="sca" value="<?php echo $sca ?>">
		<input type="hidden" name="sst" value="<?php echo $sst ?>">
		<input type="hidden" name="sod" value="<?php echo $sod ?>">
		<input type="hidden" name="page" value="<?php echo $page ?>">
		<input type="hidden" name="sw" value="">

		<!-- 게시판 페이지 정보 및 버튼 시작 { -->
		<div class="board_btn">
			<div class="total">Total <strong><?php echo number_format($total_count) ?></strong>건,  <strong><?php echo $page ?></strong> 페이지</div>

			<ul class="board_user">
				<?php if ($admin_href) { ?><li><a href="<?php echo $admin_href ?>" class="click admin"><i class="fa fa-cog fa-spin fa-fw"></i> 관리자</a></li><?php } ?>
				<?php if ($is_admin == 'super' || $is_auth) {  ?>
					<li><button type="submit" name="btn_submit" value="선택삭제" onclick="document.pressed=this.value" class="click"><i class="fa fa-trash-o" aria-hidden="true"></i> 선택삭제</button></li>
					<!--
					<li><button type="submit" name="btn_submit" value="선택복사" onclick="document.pressed=this.value" class="click"><i class="fa fa-files-o" aria-hidden="true"></i> 선택복사</button></li>
					<li><button type="submit" name="btn_submit" value="선택이동" onclick="document.pressed=this.value" class="click"><i class="fa fa-arrows" aria-hidden="true"></i> 선택이동</button></li>
					-->
				<?php } ?>
				<?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="click write">글쓰기 <i class="fa fa-pencil" aria-hidden="true"></i></a></li><?php } ?>
			</ul>
		</div>
		<!-- } 게시판 페이지 정보 및 버튼 끝 -->

		<div class="storeMapType2">
			<div id="storeMap"></div>
			<ul class="storeMap_list">
				<?php for ($i=0; $i<count($list); $i++) { ?>
				<li>
					<?php if ($is_checkbox) { ?>
					<p class="checkIn noTxt">
						<input type="checkbox" name="chk_wr_id[]" value="<?php echo $list[$i]['wr_id'] ?>" id="chk_wr_id_<?php echo $i ?>">
						<label for="chk_wr_id_<?php echo $i ?>"><?php echo $list[$i]['subject'] ?></label>
					</p>
					<?php } ?>

					<? if( $member['mb_level'] >= '9' ) :?>
					<a href="/bbs/write.php?bo_table=<?=$bo_table?>&wr_id=<?=$list[$i]['wr_id']?>&w=u" class="admGos">수정</a>
					<? endif ?>

					<!-- 팝업 안뜨고 지도 이동 (지도 에서 아이콘 눌러야 팝업뜸)
					<a href="javascript:storeFancy('<?php echo $bo_table ?>', <?php echo $list[$i]['wr_id']?>);" class="link" data-link="<?php echo $list[$i]['wr_id']?>"> -->

					<a href="javascript:storeMove('<?php echo $bo_table ?>', <?php echo $list[$i]['wr_id']?>);" class="link" data-link="<?php echo $list[$i]['wr_id']?>">
						<p class="title"><?php echo $list[$i]['subject'] ?></p>
						<p class="text juso"><?php echo $list[$i]['wr_4']?></p>
						<p class="text"><?php echo $list[$i]['wr_5']?></p>
						<p class="btns"><span>자세히보기</span></p>
					</a>
				</li>
				<?php } ?>
				<?php if (count($list) == 0) { echo '<li class="noPost">등록된 글이 없습니다.</li>'; } ?>
			</ul>
		</div>

		<?php if ($list_href || $is_checkbox || $write_href) { ?>
		<div class="board_btn end">
			<ul class="board_user">
				<?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>"  class="click write big">글쓰기 <i class="fa fa-pencil" aria-hidden="true"></i></a></li><?php } ?>
				<!--
				<?php if($_SERVER['REMOTE_ADDR']=="112.147.36.25"){ ?>
					<?php if($is_admin){?>
						<li><a class="click write big" href="/store/storeexceldownload.php?bo_table=<?=$bo_table?>"><span>대리점 데이터 다운</span></a></li>
						<li><a class="click write big" href="/store/excel_upload.php?bo_table=<?=$bo_table?>" onclick="return excelform(this.href);"><span>Excel등록</span></a></li>
						<li><a class="click write big" href="/store/update_latlng.php?bo_table=<?=$bo_table?>" onclick="return excelform(this.href);"><span>좌표등록</span></a></li>
					<?php }?>
				<?php } ?>
				-->
			</ul>
		</div>
		<?php } ?>

		<!-- 페이지 -->
		<?php echo $write_pages; ?>
		<!-- 페이지 -->
	</form>

	<div id="storeFancyLay" class="storeFancyLay">
		<div class="store_view">
			<div class="right_box">
				<div class="storePhoto">
					<div class="store-big"><div class="store-big_in"></div><p class="store-big-cont op"><span class="t"></span><span class="l">/</span><span class="t2"></span></p></div>
				</div>
			</div>
			<div class="left_box">
				<table class="store_info">
					<tbody>
						<tr>
							<th>매장명</th>
							<td class="subject"></td>
						</tr>
						<tr>
							<th>지역</th>
							<td class="area"></td>
						</tr>
						<tr>
							<th>주소</th>
							<td class="addr"></td>
						</tr>
						<tr>
							<th>전화번호</th>
							<td class="tel"></td>
						</tr>
						<tr>
							<th>운영시간</th>
							<td class="open"></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<div id="storeLayMap"></div>
	</div>

	<script type="text/javascript" src="//dapi.kakao.com/v2/maps/sdk.js?appkey=<?=$config['cf_10']?>&libraries=services,clusterer"></script>
	<script type="text/javascript">
		var mapContainer = document.getElementById("storeMap"), // 지도의 중심좌표
		mapOption = {
			center: new daum.maps.LatLng(37.4010148291019, 126.991355744449), // 지도의 중심좌표
			level: 10 // 지도의 확대 레벨
		};

		var map = new kakao.maps.Map(mapContainer, mapOption); // 지도를 생성합니다

		// 지도 확대 축소를 제어할 수 있는  줌 컨트롤을 생성합니다
		var zoomControl = new daum.maps.ZoomControl();
		map.addControl(zoomControl, daum.maps.ControlPosition.RIGHT);

		var geocoder = new kakao.maps.services.Geocoder();
		var bounds = new kakao.maps.LatLngBounds();

		var clusterer = new kakao.maps.MarkerClusterer({
			map: map, // 마커들을 클러스터로 관리하고 표시할 지도 객체
			averageCenter: true, // 클러스터에 포함된 마커들의 평균 위치를 클러스터 마커 위치로 설정
			minLevel: 10 // 클러스터 할 최소 지도 레벨
		});

		<?php
			if (count($list) != 0) {
				$sql = " select * from {$write_table} where 1  order by wr_id asc ";
				$result = sql_query($sql);
				$cnt = 0;

				for ($i=0;$i<count($list);$i++){
					$row=$list[$i];
					//if($row[wr_8] && $row[wr_9]) {
		?>

		//alert('<?php echo $list[$i]["wr_4"]; ?>')
					geocoder.addressSearch("<?=$row['wr_4']; ?>", function(result, status) {
						// 정상적으로 검색이 완료됐으면
						if (status === kakao.maps.services.Status.OK) {
							var coords = new kakao.maps.LatLng(result[0].y, result[0].x);

							//new kakao.maps.LatLng(<?=$row[wr_8]?>, <?=$row[wr_9]?>)

							$(".storeMap_list a.link").eq(<?php echo $i; ?>).attr({"data-codes-lat": coords.getLat(), "data-codes-lng": coords.getLng()})

							var markers = $(coords).map(function(i, position) {
								return new kakao.maps.Marker({
									position : coords
								});
							});

							//alert(coords)
							//console.log(<?=$row[wr_8]?>, <?=$row[wr_9]?>)

							var contentStr = '<div class="customoverlay"><a href="javascript:storeFancy(\'<?=$bo_table ?>\', <?php echo $list[$i]['wr_id']?>);" class="coBox"><span class="title"><?=$row[subject]?></span></a></div>';
							var customOverlay = new kakao.maps.CustomOverlay({
								map: map,
								content: contentStr,
								position: coords,
								yAnchor: 1
							});

							bounds.extend(coords);

							clusterer.addMarkers(markers);

							daum.maps.event.addListener(map, 'zoom_changed', function() {
								var currentLevel = map.getLevel();
								console.log(currentLevel)
								// 레벨이 9 이상인 경우 CustomOverlay를 숨깁니다.
								if (currentLevel > 9) {
									customOverlay.setMap(null);
								} else {
									customOverlay.setMap(map);
								}
							});
						}
					});
					<?php $cnt++;
					//}
				}
			}else{ ?>
				//bounds.extend(coords);
				var coords = kakao.maps.LatLng(33.450701, 126.570667);
				bounds.extend(coords);
		<?php } ?>
		map.setBounds(bounds, 90, 30, 10, 30);

		$(window).on('throttledresize', function(){// Resize Event
			setTimeout(function(){
				map.setBounds(bounds, 90, 30, 10, 30);
			}, 10);
		}).resize();

		function excelform(url){
			var opt = "width=600,height=450,left=10,top=10";
			window.open(url, "win_excel", opt);
			return false;
		}
	</script>
	<script type="text/javascript">
		function storeMove(bo_table, wr_id, code){
			$(".storeMap_list a.link").each(function(index){
				if(wr_id == $(this).attr("data-link")){
					//console.log(wr_id, $(this).attr("data-link"))
					var moveLatLon = new kakao.maps.LatLng($(this).attr("data-codes-lat"), $(this).attr("data-codes-lng"));
					map.setLevel(4);
					map.panTo(moveLatLon);
				}
			});
		}

		function storeFancy(bo_table, wr_id){
			$.ajax({
				url:g5_bbs_url+"/ajax.get_store.php",
				data : {bo_table : bo_table, wr_id : wr_id},
				dataType : 'json',
				method : "POST",
				success : function (data) {
					if(data.data > 0 ){
						$(".storeFancyLay .store_info .subject").html(data.wr_subject);
						$(".storeFancyLay .store_info .area").html(data.area);
						$(".storeFancyLay .store_info .addr").html(data.addr);
						$(".storeFancyLay .store_info .tel").html(data.tel);
						$(".storeFancyLay .store_info .open").html(data.open);

						var img="";
						try{
							//console.log(data.img);
							data.img.forEach(function (ele,index) {
								img+='<div class="slider" style="background-image:url('+ele+')"></div>';
							});
							//console.log(img);
						}catch (e) {
							img='<div class="slider" style="background-image:url(\'/img/noimage.jpg\')"></div>';
						}

						$(".storeFancyLay .store-big_in").html(img);
						//alert(data.wr_8+"__"+ data.wr_9)
						/*
						var img="";
						try{
							data.img.forEach(function (ele,index) {
								img+='<div class="swiper-slide"> ' +
									'<div class="responsiveBox"> ' +
									'<div class="img" ' +
									'style="background-image:url('+ele+')"' +
									'alt="">' +
									'</div> ' +
									'</div> ' +
									'</div>';
							});
						}catch (e) {
							img='<div class="swiper-slide"> ' +
								'<div class="responsiveBox"> ' +
								'<div class="img" ' +
								'style="background-image:url(/theme/basic/img/main/img_eventList01.png)"' +
								'alt="">' +
								'</div> ' +
								'</div> ' +
								'</div>';
							console.log(img);
						}

						$(".map_view_swiper").html(img);
						const popSlider = new Swiper('.mappopSlider .swiper-container', {
							direction: 'horizontal',
							loop: true,
							// Default parameters
							slidesPerView: 1,
							speed: 1000,
							allowTouchMove: true,
							pagination: {
								el: '.mappopSlider .swiper-pagination',
								type: 'bullets',
								clickable: true,
							},
							autoplay: {
								disableOnInteraction: false,
								delay: 3000,
							},
						});
						*/

						var mapContainer = document.getElementById("storeLayMap"), // 지도의 중심좌표
							mapOption = {
								center: new daum.maps.LatLng(data.wr_8, data.wr_9), // 지도의 중심좌표
								level: 2 // 지도의 확대 레벨
							};
						var map = new daum.maps.Map(mapContainer, mapOption); // 지도를 생성합니다

						map.setZoomable(false);

						var geocoder = new kakao.maps.services.Geocoder();
						geocoder.addressSearch(data.addr, function(result, status) {
							// 정상적으로 검색이 완료됐으면
							if (status === kakao.maps.services.Status.OK) {
								var coords = new kakao.maps.LatLng(result[0].y, result[0].x);
							//alert(coords)
								// 지도 확대 축소를 제어할 수 있는  줌 컨트롤을 생성합니다
								var zoomControl = new daum.maps.ZoomControl();
								map.addControl(zoomControl, daum.maps.ControlPosition.RIGHT);

								var bounds = new kakao.maps.LatLngBounds();

								// 지도에 마커를 표시합니다
								var marker = new daum.maps.Marker({
									map: map,
									position: coords
								});

								var contentStr = "<div class='customoverlay'><p class='coBox'><span class='title'>"+data.wr_subject+"</span></p></div>";
								var overlay = new kakao.maps.CustomOverlay({
									map: map,
									position: coords,
									content: contentStr,
									yAnchor: 1
								});


								bounds.extend(coords);

								$(window).on('throttledresize', function(){// Resize Event
									setTimeout(function(){
										map.setBounds(bounds, 30, 30, 10, 30);
									}, 10);
								}).resize();
							}
						});
							/*
						// 지도 확대 축소를 제어할 수 있는  줌 컨트롤을 생성합니다
						var zoomControl = new daum.maps.ZoomControl();
						map.addControl(zoomControl, daum.maps.ControlPosition.RIGHT);

						var bounds = new kakao.maps.LatLngBounds();

						// 지도에 마커를 표시합니다
						var marker = new daum.maps.Marker({
							map: map,
							position: new daum.maps.LatLng(data.wr_8, data.wr_9)
						});

						var contentStr = "<div class='customoverlay'><p class='coBox'><span class='title'>"+data.wr_subject+"</span></p></div>";
						var overlay = new kakao.maps.CustomOverlay({
							map: map,
							position: new kakao.maps.LatLng(data.wr_8, data.wr_9),
							content: contentStr,
							yAnchor: 1
						});

						bounds.extend(new kakao.maps.LatLng(data.wr_8, data.wr_9));

						$(window).on('throttledresize', function(){// Resize Event
							setTimeout(function(){
								map.setBounds(bounds, 30, 30, 10, 30);
							}, 10);
						}).resize();
						*/

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
					}
				}
			});
			//$.fancybox.getInstance('jumpTo', n);

			new Fancybox(
				[{src:"#storeFancyLay", type: "inline"},],
				{
					on: {
						"*": (event, fancybox, slide) => {
							//console.log(`event: ${event}`);

							if(event == "closing"){
								$(".storeFancyLay .store-big .store-big_in.slick-slider").slick("destroy");
								$(".store-big .store-big-cont").addClass("op");
							}
						},
					},
				}
			);

			//Fancybox.show([{src:"#storeFancyLay", type: "inline"}]);
			//Fancybox.close(alert(`done!`));
			/*
				on: {
					'done': (fancybox, slide) => {
						alert(`done!`);
					},
					'close': (fancybox, slide) => {
						alert(`close!`);
					},
					'closing': (fancybox, slide) => {
						alert(`closing!`);
					},
					'shouldClose': (fancybox, slide) => {
						alert(`shouldClose!`);
					},
				},

				beforeShow: function () {
				},
				afterClose: function () {

				}

			}]);
			*/
		}
	</script>
	<?php include('store_add.php'); ?>

	<?php if($is_checkbox) { ?>
	<noscript>
		<p>자바스크립트를 사용하지 않는 경우<br>별도의 확인 절차 없이 바로 선택삭제 처리하므로 주의하시기 바랍니다.</p>
	</noscript>

	<script>
		function all_checked(sw) {
			var f = document.fboardlist;

			for (var i=0; i<f.length; i++) {
				if (f.elements[i].name == "chk_wr_id[]")
					f.elements[i].checked = sw;
			}
		}
		function fboardlist_submit(f) {
			var chk_count = 0;

			for (var i=0; i<f.length; i++) {
				if (f.elements[i].name == "chk_wr_id[]" && f.elements[i].checked)
					chk_count++;
			}

			if (!chk_count) {
				alert(document.pressed + "할 게시물을 하나 이상 선택하세요.");
				return false;
			}

			if(document.pressed == "선택복사") {
				select_copy("copy");
				return;
			}

			if(document.pressed == "선택이동") {
				select_copy("move");
				return;
			}

			if(document.pressed == "선택삭제") {
				if (!confirm("선택한 게시물을 정말 삭제하시겠습니까?\n\n한번 삭제한 자료는 복구할 수 없습니다\n\n답변글이 있는 게시글을 선택하신 경우\n답변글도 선택하셔야 게시글이 삭제됩니다."))
					return false;

				f.removeAttribute("target");
				f.action = g5_bbs_url+"/board_list_update.php";
			}

			return true;
		}
		// 선택한 게시물 복사 및 이동
		function select_copy(sw) {
			var f = document.fboardlist;

			if (sw == "copy")
				str = "복사";
			else
				str = "이동";

			var sub_win = window.open("", "move", "left=50, top=50, width=500, height=550, scrollbars=1");

			f.sw.value = sw;
			f.target = "move";
			f.action = g5_bbs_url+"/move.php";
			f.submit();
		}
	</script>
	<?php } ?>
	<!-- } 게시판 목록 끝 -->