<?php
$sub_menu = '200250';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "w");

$s_no = isset($_REQUEST['s_no']) ? clean_xss_tags($_REQUEST['s_no'], 1, 1) : '';
$cp = array(
'cp_method'=>'',
'cp_subject'=>'',
'cp_target'=>'',
'mb_id'=>'',
'cp_type'=>'',
'cp_price'=>'',
'cp_trunc'=>'',
'cp_minimum'=>'',
'cp_maximum'=>'',
);
//시간지난 셀렉티드 중지
$now = date("Y-m-d");
sql_query("UPDATE g5_selected_data set s_status='E' WHERE DATE(s_start)<='{$now}' AND DATE(s_end)<'{$now}' AND s_status='S'");
sql_query("
    UPDATE g5_selected AS gs
    JOIN (
        SELECT s_parent, COUNT(*) AS cnt
        FROM g5_selected_data
        WHERE s_status = 'S'
        GROUP BY s_parent
    ) AS sd
    ON gs.s_no = sd.s_parent
    SET gs.s_cnt = sd.cnt, gs.s_modi = NOW()
");


$g5['title'] = '셀렉티드관리';

if ($w == 'u') {
    $html_title = '셀렉티드 수정';
	
    $sql = " select * from {$g5['selected_table']} where s_no = '$s_no' ";
    $cp = sql_fetch($sql);
    if (!$cp['s_no']) alert('등록된 자료가 없습니다.');
}
else
{
    $html_title = '셀렉티드 입력';
    $cp['cp_start'] = G5_TIME_YMD;
    $cp['cp_end'] = date('Y-m-d', (G5_SERVER_TIME + 86400 * 7));
}

if($cp['cp_method'] == 1) {
    $cp_target_label = '적용분류';
    $cp_target_btn = '분류검색';
} else {
    $cp_target_label = '적용상품';
    $cp_target_btn = '상품검색';
}

include_once (G5_ADMIN_PATH.'/admin.head.php');

$Range=SelectedRange($s_no);

?>
<link rel="stylesheet" href="/theme/prix/css/jquery.ui.lastest.css" />
<script>
$.datepicker.setDefaults({
  dateFormat: 'yy-mm-dd',
  prevText: '이전 달',
  nextText: '다음 달',
  monthNames: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
  monthNamesShort: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
  dayNames: ['일', '월', '화', '수', '목', '금', '토'],
  dayNamesShort: ['일', '월', '화', '수', '목', '금', '토'],
  dayNamesMin: ['일', '월', '화', '수', '목', '금', '토']
});
</script>

<form name="fcouponform" action="./selectedformupdate.php" method="post" onsubmit="return form_check(this);">
<input type="hidden" name="w" value="<?php echo $w; ?>">
<input type="hidden" name="s_no" value="<?php echo $s_no; ?>">
<input type="hidden" name="sst" value="<?php echo $sst; ?>">
<input type="hidden" name="sod" value="<?php echo $sod; ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
<input type="hidden" name="stx" value="<?php echo $stx; ?>">
<input type="hidden" name="page" value="<?php echo $page;?>">
<input type="hidden" name="limit" value="<?php echo $Range[LIMIT];?>">

<div class="tbl_frm01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?></caption>
    <colgroup>
        <col class="grid_4">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="mb_id">아이디</label></th>
        <td>
            <input type="hidden" name="mb_id" value="<?php echo get_sanitize_input($cp['mb_id']); ?>" id="mb_id" required class="required frm_input" size="50" readonly>
			<?php echo get_sanitize_input($cp['mb_id']); ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="s_subject">회사명</label></th>
        <td>
			<?php echo get_sanitize_input($cp['s_subject']); ?>
            <input type="hidden" name="s_subject" value="<?php echo get_sanitize_input($cp['s_subject']); ?>" id="s_subject" required class="required frm_input" size="50" readonly>
			<input type="hidden" name="KEDCD" value="<?=$cp['KEDCD']; ?>" id="KEDCD">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="s_membership">이용상품</label></th>
        <td>
		<?//print_r($Range[MEMBERSHIP]);?>
            <input type="hidden" name="s_membership" value="<?=$cp['s_membership']; ?>" id="s_membership" required class="required frm_input" size="50">
			<a class="btn bg-info text-light"><?=$Range[MEMBERSHIP][title]?> </a>
			<div class="btn-group">
				<a class="btn bg-dark text-light">월간 셀렉티드 제공</a>
				<a class="btn bg-secondary text-light"><?=$Range[MEMBERSHIP][selected_cnt]?></a>
			</div>
			<div class="btn-group">
				<a class="btn bg-primary text-light">제공 셀렉티드</a>
				<a class="btn bg-secondary text-light"><?=$cp['s_cnt']; ?></a>
			</div>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="s_start">이용 기간</label></th>
        <td>
            <input type="hidden" name="s_start" value="<?php echo stripslashes($cp['s_start']); ?>" id="s_start" required class="frm_input required">
			<input type="hidden" name="s_end" value="<?php echo stripslashes($cp['s_end']); ?>" id="s_end" required class="frm_input required">
			<span class="bg-secondary text-light p-2 rounded"><?php echo stripslashes($Range[NOW_S]); ?> - <?php echo stripslashes($Range[NOW_E]); ?></span>
        </td>
    </tr>

    <tr>
        <th scope="row"><label for="s_info">셀렉티드 업체 정보</label></th>
        <td>

            <!--input type="text" name="s_info" value="<?=$cp['s_info']; ?>" id="s_info"  class="frm_input" size="100"-->
			<div class="ml-2">
				<span class="btn btn-sm btn-primary">현재추천</span>
				<span class="btn btn-sm btn-secondary">종료</span>
				<button type="button" class="btn btn-sm btn-info" id="ids_btn">셀렉티드 추가</button>
			</div>
			<div class="selected_item mb-3">
			
<?
$sql="select * from g5_selected_data where s_parent={$s_no} and s_status='S'";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++) {
	$cmp_info = sql_fetch(" select *, (select mb_id from g5_member where mb_company=KEDCD) as c_id from company where KEDCD='{$row[KEDCD]}' ");
?>
		<div class="card enable" style="width: 18rem;" KEDCD="<?=$row[KEDCD]?>">
		  <div class="card-header bg-primary text-light"><span class="pull-left  badge bg-success p-1 mr-1 mt-0 "><?=$i+1?></span><b><?=$row[s_subject]?></b><?if(!$cmp_info[c_id]){?><a class="bg-dark text-light pull-right pl-2 pr-2 rounded">미등록</a><?}?></div>
		  <div class="card-body">
		    <h5 class="card-title">
				<?=licenseNum($cmp_info[BZNO])?>
			</h5>
		    <p class="card-text">
			<span class="display-6"><?=$row[s_start]?> ~ <?=$row[s_end]?></span><br>
			<?=$cmp_info[LOC_ADDRA]?> <?=$cmp_info[LOC_ADDRB]?>
			</p>
		    <button type="button" class="btn btn-danger btn-sm card_del" S_NO="<?=$row[s_no]?>">삭제</button>
		  </div>
		</div>
<?}?>
<hr>
<?
$sql="select * from g5_selected_data where s_parent={$s_no} and s_status!='S'";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++) {
	$cmp_info = sql_fetch(" select *, (select mb_id from g5_member where mb_company=KEDCD) as c_id from company where KEDCD='{$row[KEDCD]}' ");
?>
		<div class="card" style="width: 18rem;" KEDCD="<?=$row[KEDCD]?>">
		  <div class="card-header bg-secondary text-light"><span class="pull-left  badge bg-dark p-1 mr-1 mt-0 "><?=$i+1?></span><b><?=$row[s_subject]?></b><?if(!$cmp_info[c_id]){?><a class="bg-dark text-light pull-right pl-2 pr-2 rounded">미등록</a><?}?></div>
		  <div class="card-body">
		    <h5 class="card-title">
				<?=licenseNum($cmp_info[BZNO])?>
			</h5>
		    <p class="card-text">
			<span class="display-6"><?=$row[s_start]?> ~ <?=$row[s_end]?></span><br>
			<?=$cmp_info[LOC_ADDRA]?> <?=$cmp_info[LOC_ADDRB]?>
			</p>
		    <button type="button" class="btn btn-danger btn-sm card_del" S_NO="<?=$row[s_no]?>">삭제</button>
		  </div>
		</div>
<?}?>
			</div>
			<button type="button" class="btn btn-sm btn-info" id="ids_btn">셀렉티드 추가</button>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="s_cnt">셀렉티드 제공 개수</label></th>
        <td>
            <input type="text" name="s_cnt" value="<?=$cp['s_cnt']; ?>" id="s_cnt"  class="frm_input">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="s_cnt_limit">셀렉티드 가능 개수</label></th>
        <td>
            <input type="text" name="s_cnt_limit" value="<?=$cp['s_cnt_limit']; ?>" id="s_cnt_limit"  class="frm_input">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="s_modi">셀렉티드 제공일</label></th>
        <td>
            <?php echo help('입력 예: '.date('Y-m-d')); ?>
            <input type="text" name="s_modi" value="<?php echo stripslashes($cp['s_modi']); ?>" id="s_modi"  class="frm_input">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="s_status">상태</label></th>
        <td>
            <select name="s_status" id="s_status">
				<option value="S">이용중</option>
				<option value="E">종료</option>
           </select>
		   <?if($cp[s_status]){?>
		   <script>$("#s_status option[value='<?=$cp[s_status]?>']").prop("selected",true);</script>
		   <?}?>
        </td>
    </tr>
	<?if($w=='u'){?>
    <tr>
        <th scope="row"><label for="s_datetime">셀렉티드 생성일</th>
        <td>
            <input type="text" name="s_datetime" value="<?=$cp['s_datetime'];?>" id="s_datetime"  class="frm_input ">
        </td>
    </tr>
	<?}?>
    </tbody>
    </table>
</div>

<div class="pull-right">
    <a href="./selected_list.php?page=<?=$page?>" class="btn btn_02">목록</a>
    <input type="submit" value="확인" class="btn btn-info" accesskey="s">
</div>

</form>

<style>
#dialog { display:none; text-align:center;}
.han { font-weight:700;}
.han_scale { font-weight:700; color:#17a2b8;}
.han_scale_calc { font-weight:700; color:#0dcaf0}
.mb_data { margin-top:10px;}
.mb_data .title {text-align:center; font-weight:700;}
.mb_data td {border:1px solid #ddd; padding:3px}

.card {display:inline-block; margin:10px;}
.card-text {min-height:72px;}
</style>
<div id="dialog" title="셀렉티드 추가">
  <p>업체명, 사업자번호로 검색 가능합니다.</p>
  <br>
  <p><input type="text" name="sr_txt" class="frm_input" onkeyup="if(event.keyCode=='13'){$('#sr_btn').click()}"> <button type="button" id="sr_btn" class="btn btn-info btn-sm">검색</button></p>
  <div class="sr_data" id="sr_data"></div>
</div>

<script>

    function business_num(str) {
        str = str.replace(/[^0-9]/g, '');
        let tmp = '';
        if( str.length < 4){
            return str;
        }else if(str.length < 6){
            tmp += str.substr(0, 3);
            tmp += '-';
            tmp += str.substr(3);
            return tmp;
        }else if(str.length < 11){
            tmp += str.substr(0, 3);
            tmp += '-';
            tmp += str.substr(3, 2);
            tmp += '-';
            tmp += str.substr(5);
            return tmp;
        }
    }

  	$("button#ids_btn").click(function(){
		$("#dialog").dialog({
		width: 700,
		height: 400
		});
	})

	$("#sr_btn").click(function(){
		sr_txt=$(this).prev().val();
		$.post( "admin_ajax.php", {
			type: 'comp_search',
			sr_txt: sr_txt
		})
		.done(function(jqXHR) {
			$("#dialog #sr_data").html(jqXHR);
		});
	})

	$( "#dialog").on("click","#id_slect",function(){
		s_cnt_limit=parseInt($("input[name='limit']").val());
		s_cnt=parseInt($(".selected_item div.enable").length);
		if(s_cnt_limit<=s_cnt) {
			alert("제공 개수를 초과했습니다.");
			return false;
		}
		CINFOS=$(this).attr("CINFO");
		CINFO=CINFOS.split("|");
		var result = true;
		$(".selected_item div.card").each(function(){
			KEDCD=$(this).attr("KEDCD");
			if(CINFO[0]==KEDCD) {
				result = false;
				alert("이미 추가 하셨습니다.");
				return result;
			}
		})
		if(!result)
		return;
		S_NO=$("input[name='s_no']").val();
		$.post( "admin_ajax.php", {
			type: 'add_seleted',
			S_NO: S_NO,
			CINFO:CINFOS
		})
		.done(function(jqXHR) {
			if(jqXHR==1000){
				location.reload();
			}else{
				alert("처리에 문제가 있습니다.");
				$('#dialog').dialog('close');
			}
		});
	})

	$( "body").on("click",".card_del",function(){
		S_NO=$(this).attr("S_NO");
		$.post( "admin_ajax.php", {
			type: 'del_seleted',
			S_NO: S_NO
		})
		.done(function(jqXHR) {
			if(jqXHR==1000){
				location.reload();
			}else{
				alert("처리에 문제가 있습니다.");
				$('#dialog').dialog('close');
			}
		});
	})

$(function() {

    $("#cp_start, #cp_end").datepicker(
        { changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" }
    );
});

function form_check(f)
{
    var sel_type = f.cp_type;
    var cp_type = sel_type.options[sel_type.selectedIndex].value;
    var cp_price = f.cp_price.value;


    return true;
}
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');