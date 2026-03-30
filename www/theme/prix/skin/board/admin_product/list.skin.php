<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');
// 선택옵션으로 인해 셀합치기가 가변적으로 변함
$colspan = 6;

if ($is_checkbox) $colspan++;
if ($is_good) $colspan++;
if ($is_nogood) $colspan++;

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>
<style>
.top_cate {}
.btn {font-size:0.8em;}
a.btn {color:#fff;}
.top_cate #bo_cate_ul li {float:left;}
.top_cate #bo_cate_ul li a {color:#fff; font-size:1em; padding:2px 20px;}
.top_cate #bo_cate_ul #bo_cate_on {background:#169F85; border-color:#169F85;}
.top_search {display:inline-block; float:right; padding:10px 30px;}
.top_search input {border:1px solid #eee; border-radius:2px; line-height:35px;}
.top_search .btn { font-size:1em; padding:0 20px;}
#dialog input {border:1px solid #eee; border-radius:2px; padding:5px; line-height:25px;}
td {vertical-align:middle !important;}
.column-center {text-align:center;}
.table-responsive img {max-width:none;} 
.dialog {display:none;}
</style>
<link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

	<!-- 게시판 카테고리 시작 { -->
	<?php if ($is_category) { ?>
	<nav id="bo_cate" class="top_cate">
		<ul id="bo_cate_ul">
			<?php echo str_replace("<a","<a class='btn btn-info' ",$category_option); ?>
		</ul>
	</nav>
	<button type="button" class="btn btn-secondary" id="category_edit">편집</button>


<div id="dialog" title="카테고리 편집" class="dialog">
  <ul>
	<?
		$category_op=str_replace("<a","<span class='btn btn-info' ",$category_option); 
		$category_op=str_replace("</a>","</span> <button type='button' class='btn delete'>X</button> ",$category_op);
		echo $category_op;
	?>
  </ul>
  <input type="text" class="sch_input">
  <button type="button" class="btn btn-secondary add">추가</button>
  <br><br><button type="button" class="btn btn-success submit">적용</button> <button type="button" class="btn btn-warning">닫기</button>
</div>
	<?php } ?>
	<!-- } 게시판 카테고리 끝 -->
<!--btn btn-success-->

	<form name="fsearch" method="get" class="top_search">
		<input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
		<input type="hidden" name="sca" value="<?php echo $sca ?>">
		<input type="hidden" name="sop" value="and">

		<div class="search_top tar">
			<select>
				<?php echo get_board_sfl_select_options($sfl); ?>
			</select>
			<span>
				<input type="text" name="stx" value="<?php echo stripslashes($stx) ?>" required id="stx" class="sch_input" size="25" maxlength="20" placeholder=" 검색어를 입력해주세요">
				<input type="submit" name="" value="검색" class="btn btn-info" />
			</span>
		</div>
	</form>

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

                    <div class="table-responsive">
                      <table class="table table-striped jambo_table bulk_action">
					   <colgroup> 
						   <col width="4%"> 
						   <col width="5%"> 
						   <col width="10%"> 
						   <col width="*"> 
						   <col width="10%">
						   <col width="10%">
						   <col width="10%">
					   </colgroup>
                        <thead>
                          <tr class="headings bulk-actions" >
                            <th colspan="7">
                              <a class="antoo" style="color:#fff; font-weight:500;"><span class="action-cnt"> </span>  <i class="fa fa-chevron-down"></i></a>
                            </th>
                          </tr>
                          <tr class="headings column-center">
                            <th>
                              <input type="checkbox" id="check-all" class="flat">
                            </th>
                            <th class="column-title">번호</th>
                            <th class="column-title">이미지</th>
                            <th class="column-title">제목 </th>
                            <th class="column-title">작성자</th>
                            <th class="column-title">등록일 </th>
                            <th class="column-title no-link last"><span class="nobr">관리</span>
                            </th>
                          </tr>
                        </thead>

                        <tbody>
				<?php
				for ($i=0; $i<count($list); $i++) {
						$thumb = get_list_thumbnail($board['bo_table'], $list[$i]['wr_id'],"200","100", false, true);
						if($thumb['src']) {
							$img_content = '<img src="'.$thumb['ori'].'" class="ofi alt="'.$thumb['alt'].'" style="height:80px; width:auto;" />';
						} else {
							$img_content = '<img src="/img/noimage.jpg" class="ofi bg" alt="'.$thumb['alt'].'" style="height:80px; width:auto;"/>';
						}
				?>
                          <tr class="pointer <?php if ($list[$i]['is_notice']) echo "bo_notice"; ?> <?php echo $lt_class ?>">
                            <td class="column-center ">
							<?php if ($is_checkbox) { ?>
							<input type="checkbox" class="flat" name="chk_wr_id[]" value="<?php echo $list[$i]['wr_id'] ?>">
							<?php } ?>
                              
                            </td>
                            <td class="column-center" valign="middle">
								<?php
								if ($list[$i]['is_notice']) // 공지사항
									echo '<strong class="notice_icon">공지</strong>';
								else if ($wr_id == $list[$i]['wr_id'])
									echo "<span class=\"bo_current\">열람중</span>";
								else
									echo $list[$i]['num'];
								 ?>
							</td>
                            <td class="column-center"><div style="overflow: hidden; display:block; width:150px;"><?=$img_content?></div></td>
                            <td class="">
							<?if($list[$i]['wr_9']){?><a class="btn btn-round btn-warning text-light btn-sm" href="?bo_table=<?=$bo_table?>&sfl=wr_9&stx=main">메인표시</a>&ensp;<?}?>
							<?if($list[$i]['ca_name']){?><span class="btn btn-round btn-info btn-sm"><?=$list[$i]['ca_name'] ?></span>&ensp;<?}?>
							<a href="<?php echo $list[$i]['href'] ?>"><?php echo $list[$i]['subject'] ?></a>
							</td>
                            <td class="column-center"><?php echo $list[$i]['wr_name'] ?></td>
                            <td class="column-center"><?php echo date("Y.m.d", strtotime($list[$i]['wr_datetime'])) ?></td>
                            <td class="column-center last">
								<button type="button" class="btn btn-danger" id="list_del">삭제</button>
								<a href="write.php?w=u&bo_table=<?=$bo_table?>&wr_id=<?=$list[$i]['wr_id'] ?>&page=<?=$page?>" class="btn btn-secondary">수정</a>
                            </td>
                          </tr>
				<?php } ?>
				<?php if (count($list) == 0) { echo '<tr class="noPost"><td colspan="'.$colspan.'">등록된 글이 없습니다.</td></tr>'; } ?>

                        </tbody>
                      </table>
                    </div>

		<!--[e] List -->

		<!-- 페이지 -->
		<?php echo $write_pages; ?>
		<!-- 페이지 -->

		<!-- 게시판 페이지 정보 및 버튼 시작 { -->
		<div class="board_btn">
			<div class="total">Total <strong><?php echo number_format($total_count) ?></strong>건,  <strong><?php echo $page ?></strong> 페이지</div>

			<ul class="board_user">
				<?php if ($is_admin == 'super' || $is_auth) {  ?>
					<li><button type="submit" name="btn_submit" value="선택삭제" onclick="document.pressed=this.value" class="click" id="list_del_sub"><i class="fa fa-trash-o" aria-hidden="true"></i> 선택삭제</button></li>
				<?php } ?>
				<?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="click write">글쓰기 <i class="fa fa-pencil" aria-hidden="true"></i></a></li><?php } ?>
			</ul>
		</div>
		<!-- } 게시판 페이지 정보 및 버튼 끝 -->
	</form>

	<?php if($is_checkbox) { ?>
	<noscript>
	<p>자바스크립트를 사용하지 않는 경우<br>별도의 확인 절차 없이 바로 선택삭제 처리하므로 주의하시기 바랍니다.</p>
	</noscript>
	<?php } ?>


	<script>
	//카테고리
	$("#category_edit").click(function(){
		  $( "#dialog" ).dialog();
		  $( "#dialog input" ).focus();
	})
	$("#dialog .btn-warning").click(function(){
		  $("#dialog").dialog("close"); 
	})
	$( "#dialog ul" ).sortable();
	$( "#dialog ul li:eq(0)" ).remove();
	$( "#dialog" ).on("click","ul li .delete",function(){
		$(this).parents("li").remove();
	});
	$( "#dialog .add" ).click(function(){
		chk=$(this).prev().val();
		if(chk){
			ojb="<li><span class='btn btn-info'>"+chk+"</span> <button type='button' class='btn delete'>X</button> </li>"
			$("#dialog ul").append(ojb);
			$(this).prev().val("");
		}
	});

	$( "#dialog .submit" ).click(function(){
		var result = confirm('적용하시겠습니까?');
		 if(result) {
			var category = "";
			var board = $("input[name='bo_table']").val();
			$("#dialog ul li").each(function(){
				category+="|"+$(this).text().replace(" X ","");
			})
				category=category.substr(1).replaceAll("열린 분류 ","");
				//alert(category);
				$.post( "../admin_ajax.php", {
					type: 'category',
					board_name: board,
					data: category
				}, function(jqXHR) {
					//alert( jqXHR );
				}, )
				.done(function(jqXHR) {
					if(jqXHR=='1000') {
						alert("적용되었습니다.");
						window.location.reload()
					}
				})
				.fail(function(jqXHR) {
					//alert( "error" );
				})
				.always(function(jqXHR) {
					//alert( "finished" );
				});
		 }

	});

		$("button#list_del").click(function(){
			$(".bulk_action input[name='chk_wr_id[]']").iCheck('uncheck');
			$(this).parents("tr").find(".iCheck-helper").click();
			$("#list_del_sub").click();
		})

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
				f.action = "./board_list_update.php";
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

<!-- } 게시판 목록 끝 -->
