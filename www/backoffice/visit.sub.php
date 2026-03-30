<?php
if (!defined('_GNUBOARD_')) exit;

include_once(G5_LIB_PATH.'/visit.lib.php');
include_once('./admin.head.php');

if (empty($fr_date) || ! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_date) ) $fr_date = G5_TIME_YMD;
if (empty($to_date) || ! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_date) ) $to_date = G5_TIME_YMD;

$qstr = "fr_date=".$fr_date."&amp;to_date=".$to_date;
$query_string = $qstr ? '?'.$qstr : '';

$now_date = date("Y-m-d");
$yesterday = date("Y-m-d", strtotime("-1 Day"));
$sql="select sum(vs_count)  as total, avg(vs_count) as avg from g5_visit_sum where vs_date >= '".$now_date."'";
$today = sql_fetch($sql);
$sql="select sum(vs_count)  as total, avg(vs_count) as avg from g5_visit_sum where vs_date >= '".$yesterday."'";
$yesterday= sql_fetch($sql);
$sql="select sum(vs_count)  as total, avg(vs_count) as avg from g5_visit_sum";
$total = sql_fetch($sql);
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
<script>
jQuery(function($){
    $.datepicker.regional["ko"] = {
        closeText: "닫기",
        prevText: "이전달",
        nextText: "다음달",
        currentText: "오늘",
        monthNames: ["1월(JAN)","2월(FEB)","3월(MAR)","4월(APR)","5월(MAY)","6월(JUN)", "7월(JUL)","8월(AUG)","9월(SEP)","10월(OCT)","11월(NOV)","12월(DEC)"],
        monthNamesShort: ["1월","2월","3월","4월","5월","6월", "7월","8월","9월","10월","11월","12월"],
        dayNames: ["일","월","화","수","목","금","토"],
        dayNamesShort: ["일","월","화","수","목","금","토"],
        dayNamesMin: ["일","월","화","수","목","금","토"],
        weekHeader: "Wk",
        dateFormat: "yymmdd",
        firstDay: 0,
        isRTL: false,
        showMonthAfterYear: true,
        yearSuffix: ""
    };
	$.datepicker.setDefaults($.datepicker.regional["ko"]);
});
</script>

<form name="fvisit" id="fvisit" class="local_sch03 local_sch" method="get">

	<table class="tbl_row">
		<colgroup>
			<col style="width: 12%">
			<col style="width: *">
		</colgroup>
		<tbody>
			<tr><th colspan="2" style="text-align:center;">확인코자 하는 내역의 조회 기간을 먼저 입력하신 후 조건에 해당하는 항목을 클릭해 주세요!</th></tr>
			<tr>
				<th scope="col">총접속수</th>
				<td><?=number_format($total['total'])?> 명</td>
			</tr>
			<tr>
				<th scope="col">어제 접속수</th>
				<td><?=number_format($yesterday['total'])?> 명</td>
			</tr>
			<tr>
				<th scope="col">오늘 접속수</th>
				 <td><?=number_format($today['total'])?> 명</td>
			</tr>
			<tr>
				<th scope="col">조회기간</th>
				<td>
					<div class="term_srch">
						<div class="date_wrap">
							<input type="text" name="fr_date" value="<?php echo $fr_date ?>" id="fr_date" class="frm_input" size="11" maxlength="10">
							<label for="fr_date" class="sound_only">시작일</label>
							~
							<input type="text" name="to_date" value="<?php echo $to_date ?>" id="to_date" class="frm_input" size="11" maxlength="10">
							<label for="to_date" class="sound_only">종료일</label>
							<input type="submit" value="검색" class="btn_submit">
						</div>
					</div>
				</td>
			</tr>
			<tr>
				<th scope="col">조회항목</th>
				<td>
					<ul class="anchor">
						<li><a href="./visit_list.php<?php echo $query_string ?>" <?if(strpos($_SERVER['REQUEST_URI'],"visit_list")) {?>class="active"<?}?>>접속자</a></li>
						<li><a href="./visit_domain.php<?php echo $query_string ?>" <?if(strpos($_SERVER['REQUEST_URI'],"visit_domain")) {?>class="active"<?}?>>도메인</a></li>
						<li><a href="./visit_browser.php<?php echo $query_string ?>" <?if(strpos($_SERVER['REQUEST_URI'],"visit_browser")) {?>class="active"<?}?>>브라우저</a></li>
						<li><a href="./visit_os.php<?php echo $query_string ?>" <?if(strpos($_SERVER['REQUEST_URI'],"visit_os")) {?>class="active"<?}?>>운영체제</a></li>
						<?php if(version_compare(phpversion(), '5.3.0', '>=') && defined('G5_BROWSCAP_USE') && G5_BROWSCAP_USE) { ?>
						<li><a href="./visit_device.php<?php echo $query_string ?>" <?if(strpos($_SERVER['REQUEST_URI'],"visit_device")) {?>class="active"<?}?>>접속기기</a></li>
						<?php } ?>
						<li><a href="./visit_hour.php<?php echo $query_string ?>" <?if(strpos($_SERVER['REQUEST_URI'],"visit_hour")) {?>class="active"<?}?>>시간</a></li>
						<li><a href="./visit_week.php<?php echo $query_string ?>" <?if(strpos($_SERVER['REQUEST_URI'],"visit_week")) {?>class="active"<?}?>>요일</a></li>
						<li><a href="./visit_date.php<?php echo $query_string ?>" <?if(strpos($_SERVER['REQUEST_URI'],"visit_date")) {?>class="active"<?}?>>일</a></li>
						<li><a href="./visit_month.php<?php echo $query_string ?>" <?if(strpos($_SERVER['REQUEST_URI'],"visit_month")) {?>class="active"<?}?>>월</a></li>
						<li><a href="./visit_year.php<?php echo $query_string ?>" <?if(strpos($_SERVER['REQUEST_URI'],"visit_year")) {?>class="active"<?}?>>년</a></li>
					</ul>
				</td>
			</tr>
		</tbody>
	</table>

</form>



<script>
$("#fr_date, #to_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d" });
function fvisit_submit(act)
{
    var f = document.fvisit;
    f.action = act;
    f.submit();
}
</script>
