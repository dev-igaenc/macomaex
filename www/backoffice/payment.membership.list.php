<?
	$sub_menu = '700300';
	include_once('./_common.php');

	include_once (G5_ADMIN_PATH.'/admin.head.php');

$g5['title'] = '맴버쉽 결제 히스토리';

echo '<div class="local_ov01 local_ov">membership_status_history 레거시 기능 비활성화(로컬 주석 처리)</div>';
include_once (G5_ADMIN_PATH.'/admin.tail.php');
return;

$sql_common = " from g5_membership_status_history";

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

$sql = "select * $sql_common $sql_search order by no desc limit $from_record, {$config['cf_page_rows']} ";
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
        <th scope="col">아이디</th>
		<th scope="col">서비스 상품</th>
        <th scope="col">금액</th>
		<th scope="col">기간</th>
		<th scope="col">열람</th>
		<th scope="col">매칭	</th>
		<th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php for ($i=0; $row=sql_fetch_array($result); $i++) {
			$membershipInfo	= getMembership($row['membership_parent_no']);
			$paymentInfo			= getPaymentInfo($row['order_no']);
    ?>
    <tr>
        <td class="td_id"><?=$rnumber=$rnumber-1?></td>
        <td class="td_center"><?php echo $row['uid']; ?></td>
		<td class="td_center"><?=$membershipInfo['title']?></td>
		<td class="td_center"><?=number_format($paymentInfo['price'],0)?>원</td>
		<td class="td_center"><?=$row['date_begin']?> - <?=$row['date_end']?></td>
		<td class="td_center"><?=$row['open_able_cnt']?> / <?=$row['open_cnt']?></td>
		<td class="td_center"><?=$row['matching_able_cnt']?> / <?=$row['matching_cnt']?></td>
        <td class="td_mng td_mng_l">
            <a href="./payment.membership.view.php?no=<?=$row['no']?>&amp;<?=$qstr?>" class="btn btn_03">확인</a>
            <a href="./payment.membership.list_update.php?w=d&amp;no=<?php echo $row['no']; ?>&amp;<?=$qstr?>" onclick="return delete_confirm(this);" class="btn btn_02"><span class="sound_only"><?php echo htmlspecialchars2($row['title']); ?> </span>삭제</a>
        </td>
    </tr>
    <?php
    }
    if ($i == 0) {
        echo '<tr><td colspan="8" class="empty_table">자료가 한건도 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>
</div>
<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>
    
<?
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
