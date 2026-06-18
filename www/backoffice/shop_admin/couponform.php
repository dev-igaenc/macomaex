<?php
$sub_menu = '810000';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "w");

$cp_id = isset($_REQUEST['cp_id']) ? clean_xss_tags($_REQUEST['cp_id'], 1, 1) : '';
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

$g5['title'] = '쿠폰관리';

if ($w == 'u') {
    $html_title = '쿠폰 수정';

    $sql = " select * from {$g5['g5_shop_coupon_table']} where cp_id = '$cp_id' ";
    $cp = sql_fetch($sql);
    if (!$cp['cp_id']) alert('등록된 자료가 없습니다.');
}
else
{
    $html_title = '쿠폰 입력';
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
<form name="fcouponform" action="./couponformupdate.php" method="post" onsubmit="return form_check(this);">
<input type="hidden" name="w" value="<?php echo $w; ?>">
<input type="hidden" name="cp_id" value="<?php echo $cp_id; ?>">
<input type="hidden" name="sst" value="<?php echo $sst; ?>">
<input type="hidden" name="sod" value="<?php echo $sod; ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
<input type="hidden" name="stx" value="<?php echo $stx; ?>">
<input type="hidden" name="page" value="<?php echo $page;?>">

<div class="tbl_frm01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?></caption>
    <colgroup>
        <col class="grid_4">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="cp_subject">쿠폰이름</label></th>
        <td>
            <input type="text" name="cp_subject" value="<?php echo get_sanitize_input($cp['cp_subject']); ?>" id="cp_subject" required class="required frm_input" size="50">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cp_start">쿠폰생성일</label></th>
        <td>
            <?php echo help('입력 예: '.date('Y-m-d')); ?>
            <input type="text" name="cp_start" value="<?php echo stripslashes($cp['cp_start']); ?>" id="cp_start" required class="frm_input required">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cp_end">쿠폰종료일</label></th>
        <td>
            <?php echo help('입력 예: '.date('Y-m-d')); ?>
            <input type="text" name="cp_end" value="<?php echo stripslashes($cp['cp_end']); ?>" id="cp_end" required class="frm_input required">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cp_type">맴버쉽선택</label></th>
        <td>
            <select name="cp_type" id="cp_type">
			<?$mship=sql_fetch("select group_concat(no) as no,group_concat(title) as title from g5_membership_manage where title!='무료'");
			$no=explode(",",$mship['no']);
			$title=explode(",",$mship['title']);
			?>
			<?foreach($no as $key => $value){?>
            <option value="<?=$value?>" <?if($value==$cp['cp_type']){?>selected<?}?>><?=$title[$key]?></option>
			<?}?>
           </select>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cp_trunc">무료기간</th>
        <td>
            <select name="cp_trunc" id="cp_trunc">
            <option value="1"<?php echo get_selected('1', $cp['cp_trunc']); ?>>1개월</option>
            <option value="2"<?php echo get_selected('2', $cp['cp_trunc']); ?>>2개월</option>
            <option value="3"<?php echo get_selected('3', $cp['cp_trunc']); ?>>3개월</option>
            <option value="4"<?php echo get_selected('4', $cp['cp_trunc']); ?>>4개월</option>
			<option value="5"<?php echo get_selected('5', $cp['cp_trunc']); ?>>5개월</option>
           </select>
        </td>
    </tr>
	<?if(!$w){?>
    <tr>
        <th scope="row"><label for="cp_copy">생성갯수</th>
        <td>
            <input type="text" name="cp_copy" value="<?if(!$w) echo 1;?>" id="cp_copy" required class="frm_input required">개
        </td>
    </tr>
	<?}?>
    </tbody>
    </table>
</div>

<div class="pull-right">
    <a href="./couponlist.php" class="btn btn_02">목록</a>
    <input type="submit" value="확인" class="btn btn-info" accesskey="s">
</div>

</form>

<script>
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
