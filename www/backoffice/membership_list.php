<?php
$sub_menu = '700400';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "r");

if( !isset($g5['content_table']) ){
    die('<meta charset="utf-8">/data/dbconfig.php 파일에 <strong>$g5[\'content_table\'] = G5_TABLE_PREFIX.\'content\';</strong> 를 추가해 주세요.');
}
$g5['title'] = '크레딧 관리';
include_once (G5_ADMIN_PATH.'/admin.head.php');

$sql_common = " from g5_membership_manage ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = "select * $sql_common order by no limit $from_record, {$config['cf_page_rows']} ";
$result = sql_query($sql);
?>
<style>
.throu {text-decoration:line-through; text-decoration-color: red;}
</style>
 <h2><?php echo $g5['title']; ?></h2>

<div class="local_ov01 local_ov">
    <?php if ($page > 1) {?><a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>">처음으로</a><?php } ?>
    <span class="btn_ov01"><span class="ov_txt">전체 내용</span><span class="ov_num"> <?php echo $total_count; ?>건</span></span>
</div>

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col">번호</th>
        <th scope="col">멤버십명</th>
		<th scope="col">기간</th>
        <th scope="col">금액</th>
		<th scope="col">VAT</th>
		<th scope="col">결제타입</th>
		<th scope="col">정보 열람 횟수</th>
		<th scope="col">매칭 요청 횟수</th>
		<th scope="col">셀렉티드 횟수</th>
		<th scope="col">설명</th>
		<th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php for ($i=0; $row=sql_fetch_array($result); $i++) {
        $bg = 'bg'.($i%2);
		$row['desc']=str_replace("[","<span class='text-primary'>",$row['desc']);
		$row['desc']=str_replace("]","</span>",$row['desc']);
    ?>
    <tr>
        <td class="td_id"><?php echo $i+1; ?></td>
        <td class="td_center"><?php echo $row['title']; ?><br><?php echo $row['title_en']; ?></td>
		<td class="td_center"><?php echo $row['month']; ?> 달</td>
			<td class="td_center">
				<?if($row['price_origin']>-1){?><p class="text-secondary <?if($row['sale']){?>throu<?}?>"><?php echo is_numeric($row['price_origin']) ? number_format((float)$row['price_origin']) : $row['price_origin']; ?></p><?}?>
				<?if(isset($row['sale']) && is_numeric($row['sale'])){?><p class="text-info"><?php echo number_format((float)$row['sale']); ?>%</p><?}?>
				<?if($row['price_sale']){?><p class="text-bold"><?php echo is_numeric($row['price_sale']) ? number_format((float)$row['price_sale']) : $row['price_sale']; ?></p><?}?>
			</td>
		<td class="td_center"><?php echo $row['vat']; ?></td>
		<td class="td_center"><?php echo $row['pay_type']; ?></td>
		<td class="td_center"><?php if($row['open_cnt']=="-") echo "무제한"; else  echo $row['open_cnt'];?></td>
		<td class="td_center"><?php if($row['matching_cnt']=="-") echo "무제한"; else  echo $row['matching_cnt'];?></td>
		<td class="td_center"><?=$row['selected_cnt'];?></td>
		<td class="td_center"><?php echo nl2br($row['desc']); ?></td>
        <td class="td_mng td_mng_l">
            <a href="./membership_form.php?w=u&amp;no=<?php echo $row['no']; ?>" class="btn btn_03"><span class="sound_only"><?php echo htmlspecialchars2($row['title']); ?> </span>수정</a>
            <a href="./membership_update.php?w=d&amp;no=<?php echo $row['no']; ?>" onclick="return delete_confirm(this);" class="btn btn_02"><span class="sound_only"><?php echo htmlspecialchars2($row['title']); ?> </span>삭제</a>
        </td>
    </tr>
    <?php
    }
    if ($i == 0) {
        echo '<tr><td colspan="11" class="empty_table">자료가 한건도 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>
</div>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>

<div class="pull-right">
    <a href="./membership_form.php" class="btn btn-info text-light">추가</a>
</div>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
