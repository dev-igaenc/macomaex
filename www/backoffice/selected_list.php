<?php
$sub_menu = '200250';
include_once('./_common.php');
include_once (G5_ADMIN_PATH.'/admin.head.php');
auth_check_menu($auth, $sub_menu, "r");

$sql_common = " from {$g5['selected_table']} ";

$sql_search = " where (1) ";
if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case 'mb_id' :
            $sql_search .= " ({$sfl} = '{$stx}') ";
            break;
        default :
            $sql_search .= " ({$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

if (!$sst) {
    $sst  = "s_datetime";
    $sod = "desc";
}
$sql_order = " order by {$sst} {$sod} ";

$sql = " select count(*) as cnt
            {$sql_common}
            {$sql_search}
            {$sql_order} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select *
            {$sql_common}
            {$sql_search}
            {$sql_order}
            limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$g5['title'] = '셀렉티드관리';

$rnumber=($total_count-($page-1)*$rows);
$colspan = 10;
?>

<div class="local_ov">
    <span class="btn_ov01"><span class="ov_txt">전체 </span><span class="ov_num"> <?php echo number_format($total_count) ?> 개</span></span>
	<!--&ensp;<button type="button" class="btn btn-primary btn-sm pull-right" id="exl_download" onclick="$('#fsearch').attr('action','./coupon_excel.php').submit();$('#fsearch').attr('action','');"><i class="fa fa-file-excel-o" aria-hidden="true"></i> 엑셀 다운로드</button>-->
</div>
<form name="fsearch" id="fsearch" class="local_sch01 local_sch" method="get">

<select name="sfl" title="검색대상">
    <option value="s_subject"<?php echo get_selected($sfl, "s_subject"); ?>>회사명</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx"  class=" frm_input">
<input type="submit" class="btn_submit" value="검색">
</form>



<form name="fcouponlist" id="fcouponlist" method="post" action="./selectedlist_delete.php" onsubmit="return fcouponlist_submit(this);">
<input type="hidden" name="sst" value="<?php echo $sst; ?>">
<input type="hidden" name="sod" value="<?php echo $sod; ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
<input type="hidden" name="stx" value="<?php echo $stx; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">
<input type="hidden" name="token" value="">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?></caption>
    <thead>
    <tr>
        <th scope="col">
            <label for="chkall" class="sound_only">쿠폰 전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col">번호</th>
        <th scope="col">회사명</th>
        <th scope="col">이용상품</th>
        <th scope="col"><?php echo subject_sort_link('s_start') ?>이용시작일</a></th>
        <th scope="col"><?php echo subject_sort_link('s_end') ?>이용종료일</a></th>
		<th scope="col"><?php echo subject_sort_link('s_modi') ?>셀렉티드 제공일</a></th>
		<th scope="col"><?php echo subject_sort_link('s_cnt') ?>셀렉티드</a></th>
        <th scope="col"><?php echo subject_sort_link('s_status') ?>상태</a></th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        $bg = 'bg'.($i%2);
		$mship=sql_fetch("select title from g5_membership_manage where no='{$row['s_membership']}'");
		if($row['s_status']=="S") $status="<span class='btn btn-sm btn-primary'>이용중</span>";
		if($row['s_status']=="E") $status="<span class='btn btn-sm btn-secondary'>종료</span>";
    ?>

    <tr class="<?php echo $bg; ?>">
        <td class="td_chk">
            <input type="hidden" id="cp_id_<?php echo $i; ?>" name="cp_id[<?php echo $i; ?>]" value="<?php echo $row['s_no']; ?>">
            <input type="checkbox" id="chk_<?php echo $i; ?>" name="chk[]" value="<?php echo $i; ?>" title="내역선택">
        </td>
        <td><?php echo $rnumber-$i; ?></td>
        <td><?php echo $row['s_subject']; ?></td>
        <td class=""><?=$mship['title']; ?></td>
        <td class="td_datetime"><?php echo substr($row['s_start'], 0, 10); ?></td>
        <td class="td_datetime"> <?php echo substr($row['s_end'], 0, 10); ?></td>
		<td class="td_datetime"> <?php echo substr($row['s_modi'], 0, 10); ?></td>
		<td class=""><?=$row['s_cnt']; ?></td>
        <td class=""><?=$status; ?></td>
        <td class="td_mng td_mng_s">
            <a href="./selectedform.php?w=u&amp;s_no=<?php echo $row['s_no']; ?>&amp;<?php echo $qstr; ?>" class="btn btn_03"><span class="sound_only"><?php echo $row['s_no']; ?> </span>수정</a>
        </td>
    </tr>

    <?php
    }

    if ($i == 0)
        echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>
</div>
<div class="pull-right">
     <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn btn-sm btn-danger">
</div>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>

<script>
function fcouponlist_submit(f)
{
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택삭제") {
        if(!confirm("선택한 자료를 정말 삭제하시겠습니까?")) {
            return false;
        }
    }

    return true;
}
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');