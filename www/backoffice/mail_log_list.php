<?php
$sub_menu = '500100';
include_once('./_common.php');
include_once(G5_LIB_PATH.'/mailer.lib.php');

$mail_log_table = function_exists('mailer_log_table_name') ? mailer_log_table_name() : (G5_TABLE_PREFIX . 'mail_log');
$mail_open_tracking_ready = function_exists('mailer_log_ensure_open_tracking_schema') ? mailer_log_ensure_open_tracking_schema() : false;
$mail_click_tracking_ready = function_exists('mailer_log_click_tracking_ready') ? mailer_log_click_tracking_ready() : false;

$g5['title'] = '메일 발송 이력';
include_once('./admin.head.php');

if (!sql_query(" DESC `{$mail_log_table}` ", false)) {
    echo '<div class="local_desc01 local_desc"><p>메일 로그 테이블이 없습니다. `g5_mail_log` SQL을 먼저 적용해 주세요.</p></div>';
    include_once('./admin.tail.php');
    exit;
}

$allowed_sfl = array(
    'mail_type' => '메일 유형',
    'related_id' => '관련 ID',
    'recipient_email' => '수신자',
    'subject' => '제목',
    'error_message' => '에러'
);

$mail_type_labels = array(
    'signup_member' => '회원가입 완료',
    'password_lost' => '비밀번호 찾기',
    'trade_request_receiver' => '거래 요청 안내(수신자)',
    'general' => '기타'
);

$sfl = isset($_GET['sfl']) ? trim((string)$_GET['sfl']) : 'recipient_email';
if (!isset($allowed_sfl[$sfl])) {
    $sfl = 'recipient_email';
}

$stx = isset($_GET['stx']) ? trim((string)$_GET['stx']) : '';
$status = isset($_GET['status']) ? strtoupper(trim((string)$_GET['status'])) : '';
if (!in_array($status, array('', 'SUCCESS', 'FAIL', 'SKIP'), true)) {
    $status = '';
}

$date_from = isset($_GET['date_from']) ? preg_replace('/[^0-9\-]/', '', (string)$_GET['date_from']) : date('Y-m-d', strtotime('-7 days'));
$date_to = isset($_GET['date_to']) ? preg_replace('/[^0-9\-]/', '', (string)$_GET['date_to']) : date('Y-m-d');

$rows = isset($config['cf_page_rows']) && (int)$config['cf_page_rows'] > 0 ? (int)$config['cf_page_rows'] : 30;
$page = isset($page) ? (int)$page : 1;
if ($page < 1) {
    $page = 1;
}

$sql_where = " where 1=1 ";
if ($stx !== '') {
    $sql_where .= " and `{$sfl}` like '%" . sql_real_escape_string($stx) . "%' ";
}
if ($status !== '') {
    $sql_where .= " and `status` = '" . sql_real_escape_string($status) . "' ";
}
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from)) {
    $sql_where .= " and `created_at` >= '" . sql_real_escape_string($date_from) . " 00:00:00' ";
}
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to)) {
    $sql_where .= " and `created_at` <= '" . sql_real_escape_string($date_to) . " 23:59:59' ";
}

$sql_common = " from `{$mail_log_table}` {$sql_where} ";
$count_row = sql_fetch(" select count(*) as cnt {$sql_common} ");
$total_count = (int)$count_row['cnt'];
$total_page = $rows > 0 ? (int)ceil($total_count / $rows) : 1;
$from_record = ($page - 1) * $rows;

$summary = sql_fetch(" select
                            count(*) as total_cnt,
                            sum(case when `status` = 'SUCCESS' then 1 else 0 end) as success_cnt,
                            sum(case when `status` = 'FAIL' then 1 else 0 end) as fail_cnt,
                            sum(case when `status` = 'SKIP' then 1 else 0 end) as skip_cnt
                        {$sql_common} ");

$result = sql_query(" select *
                      {$sql_common}
                      order by created_at desc, id desc
                      limit {$from_record}, {$rows} ");

$qarr = array(
    'sfl' => $sfl,
    'stx' => $stx,
    'status' => $status,
    'date_from' => $date_from,
    'date_to' => $date_to
);
$qstr = http_build_query($qarr);

function mail_log_build_qstr($params, $overrides = array())
{
    $query = array_merge($params, $overrides);

    return http_build_query($query);
}

?>
<style>
.mail-log-badge {
    display: inline-block;
    min-width: 68px;
    text-align: center;
    padding: 4px 8px;
    border-radius: 10px;
    font-size: 12px;
}
.mail-log-badge.success { background: #edf8f0; color: #1d7a34; }
.mail-log-badge.fail { background: #fdecec; color: #b01717; }
.mail-log-badge.skip { background: #f1f3f5; color: #4f5d6b; }
.mail-log-subject, .mail-log-error, .mail-log-email {
    word-break: break-all;
}
.mail-log-error {
    max-width: 320px;
    white-space: pre-wrap;
}
.mail-log-row-fail td {
    background: #fffaf9;
}
</style>

<h2><?php echo $g5['title']; ?></h2>
<div class="local_desc01 local_desc">
    <p>메일 발송 내역을 확인할 수 있습니다.</p>
</div>

<form method="get" class="local_sch01 local_sch">
    <div class="local_ov01 local_ov">
        <a href="./mail_log_list.php?<?php echo mail_log_build_qstr($qarr, array('status' => '')); ?>" class="btn_ov01">
            <span class="ov_txt">전체</span><span class="ov_num"> <?php echo number_format((int)$summary['total_cnt']); ?>건</span>
        </a>
        <a href="./mail_log_list.php?<?php echo mail_log_build_qstr($qarr, array('status' => 'SUCCESS')); ?>" class="btn_ov01">
            <span class="ov_txt">성공</span><span class="ov_num"> <?php echo number_format((int)$summary['success_cnt']); ?>건</span>
        </a>
        <a href="./mail_log_list.php?<?php echo mail_log_build_qstr($qarr, array('status' => 'FAIL')); ?>" class="btn_ov01">
            <span class="ov_txt">실패</span><span class="ov_num"> <?php echo number_format((int)$summary['fail_cnt']); ?>건</span>
        </a>
        <a href="./mail_log_list.php?<?php echo mail_log_build_qstr($qarr, array('status' => 'SKIP')); ?>" class="btn_ov01">
            <span class="ov_txt">스킵</span><span class="ov_num"> <?php echo number_format((int)$summary['skip_cnt']); ?>건</span>
        </a>

        <div style="display:inline-block; margin-left:14px;">
            <label for="date_from">기간</label>
            <input type="date" name="date_from" id="date_from" value="<?php echo $date_from; ?>" class="frm_input">
            <span>~</span>
            <input type="date" name="date_to" id="date_to" value="<?php echo $date_to; ?>" class="frm_input">

            <select name="status" id="status">
                <option value="" <?php echo get_selected($status, ''); ?>>전체상태</option>
                <option value="SUCCESS" <?php echo get_selected($status, 'SUCCESS'); ?>>성공</option>
                <option value="FAIL" <?php echo get_selected($status, 'FAIL'); ?>>실패</option>
                <option value="SKIP" <?php echo get_selected($status, 'SKIP'); ?>>스킵</option>
            </select>

            <select name="sfl" id="sfl">
                <?php foreach ($allowed_sfl as $key => $label) { ?>
                    <option value="<?php echo $key; ?>" <?php echo get_selected($sfl, $key); ?>><?php echo $label; ?></option>
                <?php } ?>
            </select>
            <input type="text" name="stx" value="<?php echo get_sanitize_input($stx); ?>" class="frm_input" placeholder="검색어">
            <button type="submit" class="btn btn_03">검색</button>
            <a href="./mail_log_list.php" class="btn btn_02">초기화</a>
        </div>
    </div>
</form>

<div class="tbl_head01 tbl_wrap">
    <table>
        <caption><?php echo $g5['title']; ?> 목록</caption>
        <thead>
        <tr>
            <th scope="col">번호</th>
            <th scope="col">일시</th>
            <th scope="col">메일 유형</th>
            <th scope="col">관련 ID</th>
            <th scope="col">수신자</th>
            <th scope="col">제목</th>
            <th scope="col">상태</th>
            <?php if ($mail_open_tracking_ready) { ?>
            <th scope="col">오픈 시간</th>
            <th scope="col">오픈수</th>
            <?php } ?>
            <?php if ($mail_click_tracking_ready) { ?>
            <th scope="col">클릭 시간</th>
            <th scope="col">클릭수</th>
            <?php } ?>
            <th scope="col">에러</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $list_num = $total_count - (($page - 1) * $rows);
        for ($i = 0; $row = sql_fetch_array($result); $i++) {
            $bg = 'bg' . ($i % 2);
            $status_value = strtoupper(trim((string)$row['status']));
            $status_class = 'fail';
            if ($status_value === 'SUCCESS') {
                $status_class = 'success';
            } else if ($status_value === 'SKIP') {
                $status_class = 'skip';
            }
            $type_label = isset($mail_type_labels[$row['mail_type']]) ? $mail_type_labels[$row['mail_type']] : $mail_type_labels['general'];
            $tr_class = trim($bg . ' ' . ($status_class === 'fail' ? 'mail-log-row-fail' : ''));
            ?>
            <tr class="<?php echo $tr_class; ?>">
                <td class="td_num"><?php echo $list_num - $i; ?></td>
                <td class="td_datetime"><?php echo htmlspecialchars2($row['created_at']); ?></td>
                <td class="td_left"><?php echo htmlspecialchars2($type_label); ?></td>
                <td class="td_left"><?php echo htmlspecialchars2($row['related_id']); ?></td>
                <td class="td_left mail-log-email"><?php echo htmlspecialchars2($row['recipient_email']); ?></td>
                <td class="td_left mail-log-subject"><?php echo htmlspecialchars2($row['subject']); ?></td>
                <td class="td_center"><span class="mail-log-badge <?php echo $status_class; ?>"><?php echo htmlspecialchars2($row['status']); ?></span></td>
                <?php if ($mail_open_tracking_ready) { ?>
                <td class="td_datetime"><?php echo !empty($row['first_opened_at']) ? htmlspecialchars2($row['first_opened_at']) : '<span class="txt_gray">-</span>'; ?></td>
                <td class="td_num"><?php echo isset($row['open_count']) ? number_format((int) $row['open_count']) : 0; ?></td>
                <?php } ?>
                <?php if ($mail_click_tracking_ready) { ?>
                <td class="td_datetime"><?php echo !empty($row['first_clicked_at']) ? htmlspecialchars2($row['first_clicked_at']) : '<span class="txt_gray">-</span>'; ?></td>
                <td class="td_num"><?php echo isset($row['click_count']) ? number_format((int) $row['click_count']) : 0; ?></td>
                <?php } ?>
                <td class="td_left mail-log-error"><?php echo $row['error_message'] !== '' ? nl2br(htmlspecialchars($row['error_message'], ENT_QUOTES, 'UTF-8')) : '<span class="txt_gray">-</span>'; ?></td>
            </tr>
        <?php
        }
        if ($i == 0) {
            $empty_colspan = 8;
            if ($mail_open_tracking_ready) {
                $empty_colspan += 2;
            }
            if ($mail_click_tracking_ready) {
                $empty_colspan += 2;
            }
            echo '<tr><td colspan="' . $empty_colspan . '" class="empty_table">메일 발송 이력이 없습니다.</td></tr>';
        }
        ?>
        </tbody>
    </table>
</div>

<?php
if ($total_page > 1) {
    echo get_paging($config['cf_write_pages'], $page, $total_page, './mail_log_list.php?' . preg_replace('/^&?/', '', $qstr) . '&amp;page=');
}

include_once('./admin.tail.php');
?>
