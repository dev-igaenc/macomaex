<?
	$sub_menu = '600100';
	include_once('./_common.php');

	include_once (G5_ADMIN_PATH.'/admin.head.php');

$g5['title'] = '기업매칭 > 제조사';

$sql_common = " from g5_matching_manufacturer ";

$sql_search = " where (1)";
if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        default :
            $sql_search .= " ({$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common . $sql_search;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = "select *  $sql_common $sql_search order by regdate desc limit $from_record, {$config['cf_page_rows']} ";
$result = sql_query($sql);

$rnumber=($total_count-($page-1)*$rows)+1;

?>
  <style>
.throu {text-decoration:line-through; text-decoration-color: red;}
</style>
 <h2><?php echo $g5['title']; ?></h2>

<div class="local_ov01 local_ov">
    <?php if ($page > 1) {?><a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>">처음으로</a><?php } ?>
    <span class="btn_ov01"><span class="ov_txt">전체 내용</span><span class="ov_num"> <?php echo $total_count; ?>건</span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">

<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
    <option value="order_no"<?php echo get_selected($sfl, "order_no"); ?>>주문번호</option>
    <option value="uid"<?php echo get_selected($sfl, "uid"); ?>>아이디</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" required class="required frm_input">
<input type="submit" class="btn_submit" value="검색">

</form>

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col">번호</th>
        <th scope="col">상호명</th>
		<th scope="col">관리번호</th>
        <th scope="col">산업분류</th>
		<th scope="col">거래담당자명</th>
		<th scope="col">거래담당자직급</th>
		<th scope="col">거래담당자연락처	</th>
		<th scope="col">등록일</th>
		<th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php for ($i=0; $v=sql_fetch_array($result); $i++) {
		if($v['KEDCD']) $Companyinfo=getComapyInfo($v['KEDCD']);
		if($Companyinfo['BZC_CD']) $Ksscinfo=getKsscInfo($Companyinfo['BZC_CD']);
		
    ?>
    <tr>
        <td class="td_id"><?=$rnumber=$rnumber-1?></td>
        <td class="td_center"><a href="<?=$link?>"><?=$v['corporate_name']?></a></td>
		<td class="td_center"><?=$v['manage_number']?></td>
		<td class="td_center"><?=$Ksscinfo['k_code_name'];?></td>
		<td class="td_center"><?=$v['charger_name']?></td>
		<td class="td_center"><?=$v['charger_duty']?></td>
		<td class="td_center"><?=$v['charger_phone']?></td>
		<td class="td_center"><?=$v['regdate']?></td>
        <td class="td_mng td_mng_l">
            <a href="./matching_mfr.view.php?no=<?=$v['no']?>&amp;<?=$qstr?>" class="btn btn_03">확인</a>
            <a href="./matching_mfr_update.php?w=d&amp;no=<?php echo $v['no']; ?>&amp;<?=$qstr?>" onclick="return delete_confirm(this);" class="btn btn_02"><span class="sound_only"><?php echo htmlspecialchars2($v['title']); ?> </span>삭제</a>
        </td>
    </tr>
    <?php
    }
    if ($i == 0) {
        echo '<tr><td colspan="10" class="empty_table">자료가 한건도 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>
</div>
<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>
    
<?
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
