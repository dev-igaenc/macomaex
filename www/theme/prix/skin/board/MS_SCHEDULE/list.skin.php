<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);

$category_list=explode("|",$board['bo_category_list']);
?>

<link rel="stylesheet" type="text/css" href="/build/fullcalendar/main.css" />
<script type="text/javascript" src="/build/fullcalendar/main.js"></script>
<script type="text/javascript" src="/build/fullcalendar/locales-all.js"></script>

<style>
	#calendar{max-width:1024px; margin:0 auto; font-size:16px;}

	.fc *{font-size:1.2rem;}
	.fc .fc-toolbar-chunk{}

	.fc-theme-standard .fc-scrollgrid{border-width:0px !important;}
	.fc-theme-standard td,
	.fc-theme-standard th{border-width:1px !important;}

	.fc thead[role="rowgroup"],
	.fc-col-header-cell{display:none !important;}

	.fc .fc-button{font-size: 1.2em;}
	.fc .fc-button.fc-today-button{padding: 0.4em 1.5em;}
	.fc .fc-button .fc-icon{width: 1.15em; height: 1.15em; font-size: 1.2em;}


	.fc .fc-day-sun .fc-daygrid-day-number{color: #ff5a71;}
	.fc .fc-day-sat .fc-daygrid-day-number{color: #071fb4;}

	.fc .fc-daygrid-day-number{width:3rem; height:3rem; margin-right:1rem;}
	.fc .fc-day-today{background-color:rgba(7,31,180, 0.1);}
	.fc .fc-day-today .fc-daygrid-day-number{border:none; color: #000;}

	.fc .fc-h-event .fc-event-main .fc-event-title{font-size:1.3rem;}

	@media only screen and (max-width : 840px){
		.fc *{font-size:1.6rem;}

		.fc .fc-button{}
		.fc .fc-button.fc-today-button{}
		.fc .fc-button .fc-icon{}

		.fc .fc-daygrid-day-number{width:3rem; height:3rem;}

		.fc .fc-h-event .fc-event-main .fc-event-title{font-size:1.6rem;}
	}
</style>

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
	<?php if ($is_admin) {  ?>
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
	<?php } ?>
	<!-- } 게시판 페이지 정보 및 버튼 끝 -->

	<div id="calendar"></div>

	<script type="text/javascript">
		var colorBg = ["#ffcea2", "#bbecd9"];

		$(window).load(function () {	// $(".testText").text("off2");
			var initialLocaleCode = 'ko';
			var calendarEl = document.getElementById('calendar');

			var calendar = new FullCalendar.Calendar(calendarEl, {
				headerToolbar: {
					left: 'title',//prev,next today prevYear,prev,next,nextYear
					center: '', //title
					right: 'prevYear,prev,today,next,nextYear'
					//right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
				},
				//initialDate: '2022-03-24',
				locale: initialLocaleCode,
				buttonIcons: true, // show the prev/next text
				weekNumbers: false,
				navLinks: false, // can click day/week names to navigate views
				editable: false, // Drag-n-Drop Events
				dayMaxEvents: true, // allow "more" link when too many events
				//Boolean: false,
				events: [
					<?php for ($i=0; $i<count($list); $i++) {

						if(strtotime($check_date)<strtotime($row['wr_7'])){
							$apply_status='<div class="tt e_icon"><span>대기</span></div>';
						}elseif (strtotime($check_date) >= strtotime($row['wr_7']) && strtotime($check_date) <= strtotime($row['wr_8'])){
							$apply_status='<div class="tt e_icon ing"><span>진행중</span></div>';
						}else if(strtotime($check_date) > strtotime($row['wr_8'])){
							$apply_status='<div class="tt e_icon end"><span>종료</span></div>';
						}
					?>
					{
						//backgroundColor : "<?php echo $list[$i]['wr_6'] ?>",
						//borderColor : "<?php echo $list[$i]['wr_6'] ?>",

						backgroundColor : colorBg["<?php echo array_search($list[$i]['ca_name'] ,$category_list); ?>"],
						borderColor : colorBg["<?php echo array_search($list[$i]['ca_name'] ,$category_list); ?>"],
						title: "<?php echo $list[$i]['subject'] ?>​",
						url: "/bbs/board.php?bo_table=<?=$bo_table;?>&wr_id=<?php echo $list[$i]['wr_id'] ?>",
						start: "<?php echo $list[$i]['wr_7'] ?>",
						end: "<?php echo $list[$i]['wr_8'] ?>"
					},
					<?php } ?>
						/*
					{
						backgroundColor : "#ffcea2",
						borderColor : "#ffcea2",
						title: '행사 타이틀 행사 타이틀 행사 타이틀',
						start: '2022-03-14',
						end: '2022-03-18'
					},
					{
						backgroundColor : "#bbecd9",
						borderColor : "#bbecd9",
						title: '[Module 1] 국내 융복합 의료제품 분류 및 유형의 이해​',
						start: '2022-03-23',
						end: '2022-03-31'
					}
					*/
				],
				eventClick: function(info) {
					info.jsEvent.preventDefault(); // don't let the browser navigate

					if (info.event.url) {
						location.href=info.event.url;
					}
				}
			});
			calendar.render();
		});
	</script>

	<?php if ($list_href || $is_checkbox || $write_href) { ?>
	<div class="board_btn end">
		<ul class="board_user">
			<?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="click write big">글쓰기</a></li><?php } ?>
		</ul>
	</div>
	<?php } ?>
</form>

<?php if($is_checkbox) { ?>
<noscript>
<p>자바스크립트를 사용하지 않는 경우<br>별도의 확인 절차 없이 바로 선택삭제 처리하므로 주의하시기 바랍니다.</p>
</noscript>
<?php } ?>

<?php if ($is_checkbox) { ?>
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
        f.action = "./board_list_update.php";
    }

    return true;
}

// 선택한 게시물 복사 및 이동
function select_copy(sw) {
    var f = document.fboardlist;

    if (sw == 'copy')
        str = "복사";
    else
        str = "이동";

    var sub_win = window.open("", "move", "left=50, top=50, width=500, height=550, scrollbars=1");

    f.sw.value = sw;
    f.target = "move";
    f.action = "./move.php";
    f.submit();
}

// 게시판 리스트 관리자 옵션
jQuery(function($){
	$(".btn_more_opt.is_list_btn").on("click", function(e) {
		e.stopPropagation();
		$(".more_opt.is_list_btn").toggle();
	});
	$(document).on("click", function (e) {
		if(!$(e.target).closest('.is_list_btn').length) {
			$(".more_opt.is_list_btn").hide();
		}
	});
});
</script>
<?php } ?>
<!-- } 게시판 목록 끝 -->
