<?php
$sub_menu = '700200';
include_once('./_common.php');
include_once(G5_ADMIN_PATH . '/admin.head.php');

$g5['title'] = '크레딧 모니터링';

$allowed_sfl = array(
    'uid' => 'H.uid',
    'order_no' => 'H.order_no',
    'matching_no' => 'H.matching_no',
    'memo' => 'H.memo',
    'mb_name' => 'M.mb_name',
    'mb_email' => 'M.mb_email'
);
$allowed_tx_types = array('CHARGE', 'BONUS', 'USE', 'REFUND', 'ADJUST');

$sfl = isset($_GET['sfl']) ? trim((string)$_GET['sfl']) : '';
$stx = isset($_GET['stx']) ? trim((string)$_GET['stx']) : '';
$tx_type = isset($_GET['tx_type']) ? strtoupper(trim((string)$_GET['tx_type'])) : '';
$fr_date = isset($_GET['fr_date']) ? trim((string)$_GET['fr_date']) : '';
$to_date = isset($_GET['to_date']) ? trim((string)$_GET['to_date']) : '';

$sql_common = " from credit_history H left join g5_member M on H.uid = M.mb_id ";
$sql_search = " where (1) ";

if ($stx !== '' && isset($allowed_sfl[$sfl])) {
    $stx_sql = sql_real_escape_string($stx);
    $sql_search .= " and ({$allowed_sfl[$sfl]} like '%{$stx_sql}%') ";
}

if (in_array($tx_type, $allowed_tx_types, true)) {
    $sql_search .= " and H.tx_type = '{$tx_type}' ";
}

if ($fr_date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fr_date)) {
    $sql_search .= " and H.created_at >= '{$fr_date} 00:00:00' ";
}

if ($to_date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to_date)) {
    $sql_search .= " and H.created_at <= '{$to_date} 23:59:59' ";
}

$sql = " select count(*) as cnt " . $sql_common . $sql_search;
$row = sql_fetch($sql);
$total_count = isset($row['cnt']) ? (int)$row['cnt'] : 0;

$rows = isset($config['cf_page_rows']) ? (int)$config['cf_page_rows'] : 20;
if ($rows < 1) {
    $rows = 20;
}
$total_page = (int)ceil($total_count / $rows);
if ($page < 1) {
    $page = 1;
}
$from_record = ($page - 1) * $rows;

$summary_sql = "select
        count(*) as total_cnt,
        IFNULL(sum(if(H.tx_type='CHARGE', H.credit_delta, 0)), 0) as sum_charge,
        IFNULL(sum(if(H.tx_type='BONUS', H.credit_delta, 0)), 0) as sum_bonus,
        IFNULL(sum(if(H.tx_type='USE', -H.credit_delta, 0)), 0) as sum_use,
        IFNULL(sum(if(H.tx_type='REFUND', -H.credit_delta, 0)), 0) as sum_refund,
        IFNULL(sum(if(H.tx_type='ADJUST', H.credit_delta, 0)), 0) as sum_adjust
    {$sql_common} {$sql_search}";
$summary = sql_fetch($summary_sql);

$sql = "select
            H.*,
            M.mb_name,
            M.mb_email,
            (
                select price
                from g5_membership_paybill
                where order_no = H.order_no
                order by no desc
                limit 1
            ) as pay_price
        {$sql_common} {$sql_search}
        order by H.id desc
        limit {$from_record}, {$rows}";
$result = sql_query($sql);

$rnumber = ($total_count - (($page - 1) * $rows)) + 1;
$add_qstr = '';
if ($tx_type !== '') {
    $add_qstr .= '&amp;tx_type=' . urlencode($tx_type);
}
if ($fr_date !== '') {
    $add_qstr .= '&amp;fr_date=' . urlencode($fr_date);
}
if ($to_date !== '') {
    $add_qstr .= '&amp;to_date=' . urlencode($to_date);
}
?>
<style>
.credit-chip {display:inline-block;padding:4px 8px;border-radius:12px;font-size:11px;line-height:1;}
.credit-chip.charge {background:#dcfce7;color:#166534;}
.credit-chip.bonus {background:#fef9c3;color:#854d0e;}
.credit-chip.use {background:#fee2e2;color:#991b1b;}
.credit-chip.refund {background:#dbeafe;color:#1e3a8a;}
.credit-chip.adjust {background:#ede9fe;color:#5b21b6;}
.credit-chip.etc {background:#f3f4f6;color:#374151;}
.credit-delta.plus {color:#0369a1;font-weight:700;}
.credit-delta.minus {color:#dc2626;font-weight:700;}
</style>

<h2><?php echo $g5['title']; ?></h2>

<div class="local_ov01 local_ov">
    <?php if ($page > 1) { ?><a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>">처음으로</a><?php } ?>
    <span class="btn_ov01"><span class="ov_txt">전체 내용</span><span class="ov_num"> <?php echo number_format($total_count); ?>건</span></span>
    <span class="btn_ov01"><span class="ov_txt">충전</span><span class="ov_num"> <?php echo number_format((int)$summary['sum_charge']); ?>C</span></span>
    <span class="btn_ov01"><span class="ov_txt">보너스</span><span class="ov_num"> <?php echo number_format((int)$summary['sum_bonus']); ?>C</span></span>
    <span class="btn_ov01"><span class="ov_txt">사용</span><span class="ov_num"> <?php echo number_format((int)$summary['sum_use']); ?>C</span></span>
    <span class="btn_ov01"><span class="ov_txt">환불</span><span class="ov_num"> <?php echo number_format((int)$summary['sum_refund']); ?>C</span></span>
    <span class="btn_ov01"><span class="ov_txt">관리자조정</span><span class="ov_num"> <?php echo number_format((int)$summary['sum_adjust']); ?>C</span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
    <label for="sfl" class="sound_only">검색대상</label>
    <select name="sfl" id="sfl">
        <option value="uid"<?php echo get_selected($sfl, 'uid'); ?>>아이디</option>
        <option value="order_no"<?php echo get_selected($sfl, 'order_no'); ?>>주문번호</option>
        <option value="matching_no"<?php echo get_selected($sfl, 'matching_no'); ?>>매칭번호</option>
        <option value="memo"<?php echo get_selected($sfl, 'memo'); ?>>메모</option>
        <option value="mb_name"<?php echo get_selected($sfl, 'mb_name'); ?>>회사명</option>
        <option value="mb_email"<?php echo get_selected($sfl, 'mb_email'); ?>>이메일</option>
    </select>
    <label for="stx" class="sound_only">검색어</label>
    <input type="text" name="stx" value="<?php echo get_text($stx); ?>" id="stx" class="frm_input" placeholder="검색어">
    <label for="tx_type" class="sound_only">유형</label>
    <select name="tx_type" id="tx_type">
        <option value="">전체유형</option>
        <option value="CHARGE"<?php echo get_selected($tx_type, 'CHARGE'); ?>>충전</option>
        <option value="BONUS"<?php echo get_selected($tx_type, 'BONUS'); ?>>보너스</option>
        <option value="USE"<?php echo get_selected($tx_type, 'USE'); ?>>사용</option>
        <option value="REFUND"<?php echo get_selected($tx_type, 'REFUND'); ?>>환불</option>
        <option value="ADJUST"<?php echo get_selected($tx_type, 'ADJUST'); ?>>관리자조정</option>
    </select>
    <input type="date" name="fr_date" value="<?php echo get_text($fr_date); ?>" class="frm_input">
    ~
    <input type="date" name="to_date" value="<?php echo get_text($to_date); ?>" class="frm_input">
    <input type="submit" class="btn_submit" value="검색">
    <a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>" class="btn btn_02">초기화</a>
</form>

<div class="tbl_head01 tbl_wrap">
    <table>
        <caption><?php echo $g5['title']; ?> 목록</caption>
        <thead>
        <tr>
            <th scope="col">번호</th>
            <th scope="col">발생일시</th>
            <th scope="col">아이디</th>
            <th scope="col">회사명</th>
            <th scope="col">유형</th>
            <th scope="col">증감</th>
            <th scope="col">잔여</th>
            <th scope="col">주문번호</th>
            <th scope="col">결제금액</th>
            <th scope="col">매칭번호</th>
            <th scope="col">메모</th>
        </tr>
        </thead>
        <tbody>
        <?php
        for ($i = 0; $row = sql_fetch_array($result); $i++) {
            $type = isset($row['tx_type']) ? (string)$row['tx_type'] : '';
            $type_label = $type;
            $type_class = 'etc';
            if ($type === 'CHARGE') {
                $type_label = '충전';
                $type_class = 'charge';
            } else if ($type === 'BONUS') {
                $type_label = '보너스';
                $type_class = 'bonus';
            } else if ($type === 'USE') {
                $type_label = '사용';
                $type_class = 'use';
            } else if ($type === 'REFUND') {
                $type_label = '환불';
                $type_class = 'refund';
            } else if ($type === 'ADJUST') {
                $type_label = '관리자조정';
                $type_class = 'adjust';
            }

            $delta = isset($row['credit_delta']) ? (int)$row['credit_delta'] : 0;
            $delta_class = $delta < 0 ? 'minus' : 'plus';
            $delta_text = ($delta > 0 ? '+' : '') . number_format($delta);
            $order_no = isset($row['order_no']) ? trim((string)$row['order_no']) : '';
            $pay_price = isset($row['pay_price']) ? (int)$row['pay_price'] : 0;
        ?>
        <tr>
            <td class="td_num"><?php echo $rnumber = $rnumber - 1; ?></td>
            <td class="td_center"><?php echo $row['created_at']; ?></td>
            <td class="td_center"><?php echo get_text($row['uid']); ?></td>
            <td class="td_center"><?php echo $row['mb_name'] ? get_text($row['mb_name']) : '-'; ?></td>
            <td class="td_center"><span class="credit-chip <?php echo $type_class; ?>"><?php echo $type_label; ?></span></td>
            <td class="td_center"><span class="credit-delta <?php echo $delta_class; ?>"><?php echo $delta_text; ?></span></td>
            <td class="td_center"><?php echo number_format((int)$row['credit_after']); ?></td>
            <td class="td_center">
                <?php if ($order_no !== '') { ?>
                    <a href="./payment.list.php?sfl=order_no&amp;stx=<?php echo urlencode($order_no); ?>"><?php echo get_text($order_no); ?></a>
                <?php } else { ?>
                    -
                <?php } ?>
            </td>
            <td class="td_center"><?php echo $pay_price > 0 ? number_format($pay_price) . '원' : '-'; ?></td>
            <td class="td_center"><?php echo $row['matching_no'] ? get_text($row['matching_no']) : '-'; ?></td>
            <td class="td_left"><?php echo $row['memo'] ? get_text($row['memo']) : '-'; ?></td>
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

<?php
echo get_paging(
    G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'],
    $page,
    $total_page,
    "{$_SERVER['SCRIPT_NAME']}?$qstr{$add_qstr}&amp;page="
);
include_once(G5_ADMIN_PATH . '/admin.tail.php');
