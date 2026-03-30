<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>
<style>td {background:#fff;}</style>
<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>


	<!-- 게시물 읽기 시작 { -->
	<div class="board_btn end">

	</div>
	<div class="board_write">
		<table>
			<tbody>
			<?php if ($category_name) { ?>
			<tr>
				<th scope="row"><span class="">분류</span></th>
				<td><?php echo $view['ca_name']?></td>
			</tr>
			<?php } ?>
			<tr>
				<th scope="row"><span class="">이름</span></th>
				<td><?php echo $view['wr_name']?></td>
			</tr>
			<tr>
				<th scope="row"><span class="">등록일</span></th>
				<td><?php echo date("Y.m.d", strtotime($view['wr_datetime'])) ?></td>
			</tr>
			<tr>
				<th scope="row"><span class="">제목</span></th>
				<td><?php echo $view['subject']?></td>
			</tr>
			<tr>
				<th scope="row"><span class="">게시여부</span></th>
				<td><?if($view['wr_9']=="N"){?>No<?}else{?>Yes<?}?></td>
			</tr>
			<tr>
				<th scope="row"><span class="">내용</span></th>
				<td><?php echo $view['wr_content']?></td>
			</tr>
			<?php if($view['file']['count']>0) { ?>
			<tr>
				<th scope="row"><span class="">첨부파일</span></th>
				<td>
					<div class="view_file">
						<?php
						// 가변 파일
						for ($i=0; $i<$view['file']['count']; $i++) {
							//if (isset($view['file'][$i]['source']) && $view['file'][$i]['source'] && !$view['file'][$i]['view']) {
						?>
						<a href="<?php echo $view['file'][$i]['href']; ?>"><?php echo $view['file'][$i]['source'] ?></a>
						<?php } //} ?>
					</div>
				</td>
			</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>

	<?php
	// 코멘트 입출력
	//include_once(G5_BBS_PATH.'/view_comment.php');
	?>

	<div class="board_btn end">
		<ul class="board_user">
			<?php if ($is_admin == 'super' || $is_auth) {  ?>
			<?php if ($update_href) { ?><li><a href="<?php echo $update_href ?>" class="click"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> 수정</a></li><?php } ?>
			<?php if ($delete_href) { ?><li><a href="<?php echo $delete_href ?>" class="click" onclick="del(this.href); return false;"><i class="fa fa-trash-o" aria-hidden="true"></i> 삭제</a></li><?php } ?>
			<?php } ?>

			<?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="click write">글쓰기 <i class="fa fa-pencil" aria-hidden="true"></i></a></li><?php } ?>
			<li><a href="<?php echo $list_href ?>" class="click write">목록</a></li>
		</ul>
	</div>
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