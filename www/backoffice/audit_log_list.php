<?php
$sub_menu = '100000';
include_once('./_common.php');

if ($is_admin != 'super') {
    alert('최고관리자만 접근 가능합니다.');
}

$audit_table = function_exists('admin_audit_table_name') ? admin_audit_table_name() : (G5_TABLE_PREFIX . 'admin_audit_log');

if (!function_exists('admin_audit_ensure_table') || !admin_audit_ensure_table()) {
    $g5['title'] = '감사로그';
    include_once(G5_ADMIN_PATH . '/admin.head.php');
    echo '<div class="local_desc01 local_desc"><p>감사로그 테이블이 없어 로그 조회를 할 수 없습니다. 현재 코드는 운영 DB 보호를 위해 테이블을 자동 생성하지 않습니다.</p></div>';
    include_once(G5_ADMIN_PATH . '/admin.tail.php');
    exit;
}

$allowed_sfl = array(
    'al_action' => '액션',
    'mb_id' => '관리자ID',
    'al_ip' => 'IP',
    'al_uri' => '요청URI',
    'al_message' => '메시지'
);

$sfl = isset($_GET['sfl']) ? trim((string)$_GET['sfl']) : 'al_action';
if (!isset($allowed_sfl[$sfl])) {
    $sfl = 'al_action';
}

$stx = isset($_GET['stx']) ? trim((string)$_GET['stx']) : '';
$result_filter = isset($_GET['result']) ? strtoupper(trim((string)$_GET['result'])) : '';
if (!in_array($result_filter, array('', 'SUCCESS', 'FAIL', 'INFO'))) {
    $result_filter = '';
}

$date_from = isset($_GET['date_from']) ? preg_replace('/[^0-9\-]/', '', (string)$_GET['date_from']) : date('Y-m-d', strtotime('-7 days'));
$date_to = isset($_GET['date_to']) ? preg_replace('/[^0-9\-]/', '', (string)$_GET['date_to']) : date('Y-m-d');

$audit_rows = isset($config['cf_page_rows']) && (int)$config['cf_page_rows'] > 0 ? (int)$config['cf_page_rows'] : 30;
$audit_page = isset($page) ? (int)$page : 1;
if ($audit_page < 1) {
    $audit_page = 1;
}

$sql_search = " where 1=1 ";
if ($result_filter) {
    $sql_search .= " and al_result = '" . sql_real_escape_string($result_filter) . "' ";
}
if ($stx !== '') {
    $sql_search .= " and {$sfl} like '%" . sql_real_escape_string($stx) . "%' ";
}
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from)) {
    $sql_search .= " and al_datetime >= '" . sql_real_escape_string($date_from) . " 00:00:00' ";
}
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to)) {
    $sql_search .= " and al_datetime <= '" . sql_real_escape_string($date_to) . " 23:59:59' ";
}

$audit_sql_common = " from {$audit_table} {$sql_search} ";

$audit_count_row = sql_fetch(" select count(*) as cnt {$audit_sql_common} ");
$audit_total_count = (int)$audit_count_row['cnt'];
$audit_total_page = (int)ceil($audit_total_count / $audit_rows);
$audit_from_record = ($audit_page - 1) * $audit_rows;

$audit_summary = sql_fetch(" select
                        count(*) as total_cnt,
                        sum(case when al_result='FAIL' then 1 else 0 end) as fail_cnt,
                        sum(case when al_result='SUCCESS' then 1 else 0 end) as success_cnt
                      {$audit_sql_common} ");

$audit_sql = " select *
         {$audit_sql_common}
         order by al_id desc
         limit {$audit_from_record}, {$audit_rows} ";
$audit_result = sql_query($audit_sql);

$qarr = array(
    'sfl' => $sfl,
    'stx' => $stx,
    'result' => $result_filter,
    'date_from' => $date_from,
    'date_to' => $date_to
);
$qstr = http_build_query($qarr);
$audit_csv_url = './audit_log_csv.php' . ($qstr ? ('?' . $qstr) : '');

$g5['title'] = '감사로그';
include_once(G5_ADMIN_PATH . '/admin.head.php');
?>

<style>
.audit_payload {
    max-height: 180px;
    overflow: auto;
    margin: 8px 0 0 0;
    padding: 10px;
    background: #f8f9fa;
    border: 1px solid #e5e8ec;
    white-space: pre-wrap;
    word-break: break-all;
}
.audit_badge {
    display: inline-block;
    min-width: 58px;
    text-align: center;
    padding: 3px 8px;
    border-radius: 10px;
    font-size: 12px;
}
.audit_badge.success { background: #e8f7ef; color: #1b7f46; }
.audit_badge.fail { background: #fdecec; color: #b01717; }
.audit_badge.info { background: #eef3ff; color: #2f55a4; }
.audit_uri {
    max-width: 260px;
    word-break: break-all;
}
.audit_filter {
    display: inline-block;
    margin-left: 14px;
}
</style>

<h2><?php echo $g5['title']; ?></h2>
<div class="local_desc01 local_desc">
    <p>관리자 작업 이력을 기록합니다. 장애 추적 시 실패(`FAIL`) 로그부터 확인하세요.</p>
</div>

<form method="get" class="local_sch01 local_sch audit_search_form">
    <div class="local_ov01 local_ov">
        <span class="btn_ov01"><span class="ov_txt">전체</span><span class="ov_num"> <?php echo number_format((int)$audit_summary['total_cnt']); ?>건</span></span>
        <span class="btn_ov01"><span class="ov_txt">실패</span><span class="ov_num"> <?php echo number_format((int)$audit_summary['fail_cnt']); ?>건</span></span>
        <span class="btn_ov01"><span class="ov_txt">성공</span><span class="ov_num"> <?php echo number_format((int)$audit_summary['success_cnt']); ?>건</span></span>

        <div class="audit_filter">
            <label for="date_from">기간</label>
            <input type="date" name="date_from" id="date_from" value="<?php echo $date_from; ?>" class="frm_input" />
            <span>~</span>
            <input type="date" name="date_to" id="date_to" value="<?php echo $date_to; ?>" class="frm_input" />

            <label for="result" class="sound_only">결과</label>
            <select name="result" id="result">
                <option value="" <?php echo get_selected($result_filter, ''); ?>>전체결과</option>
                <option value="FAIL" <?php echo get_selected($result_filter, 'FAIL'); ?>>FAIL</option>
                <option value="SUCCESS" <?php echo get_selected($result_filter, 'SUCCESS'); ?>>SUCCESS</option>
                <option value="INFO" <?php echo get_selected($result_filter, 'INFO'); ?>>INFO</option>
            </select>

            <label for="sfl" class="sound_only">검색대상</label>
            <select name="sfl" id="sfl">
                <?php foreach ($allowed_sfl as $key => $label) { ?>
                    <option value="<?php echo $key; ?>" <?php echo get_selected($sfl, $key); ?>><?php echo $label; ?></option>
                <?php } ?>
            </select>

            <input type="text" name="stx" value="<?php echo get_sanitize_input($stx); ?>" class="frm_input" placeholder="검색어" />
            <button type="submit" class="btn btn_03">검색</button>
            <a href="<?php echo $audit_csv_url; ?>" class="btn btn_03">CSV 다운로드</a>
            <a href="./audit_log_list.php" class="btn btn_02">초기화</a>
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
            <th scope="col">관리자</th>
            <th scope="col">액션</th>
            <th scope="col">결과</th>
            <th scope="col">메시지</th>
            <th scope="col">대상</th>
            <th scope="col">IP</th>
            <th scope="col">요청URI</th>
            <th scope="col">상세</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $audit_list_num = $audit_total_count - (($audit_page - 1) * $audit_rows);
        for ($i = 0; $row = sql_fetch_array($audit_result); $i++) {
            $bg = 'bg' . ($i % 2);
            $result_class = 'info';
            if ($row['al_result'] === 'SUCCESS') {
                $result_class = 'success';
            } else if ($row['al_result'] === 'FAIL') {
                $result_class = 'fail';
            }
            ?>
            <tr class="<?php echo $bg; ?>">
                <td class="td_num"><?php echo $audit_list_num - $i; ?></td>
                <td class="td_datetime"><?php echo htmlspecialchars2($row['al_datetime']); ?></td>
                <td class="td_mng"><?php echo htmlspecialchars2($row['mb_name']); ?><br><span class="txt_gray"><?php echo htmlspecialchars2($row['mb_id']); ?></span></td>
                <td class="td_left"><?php echo htmlspecialchars2($row['al_action']); ?></td>
                <td class="td_center"><span class="audit_badge <?php echo $result_class; ?>"><?php echo htmlspecialchars2($row['al_result']); ?></span></td>
                <td class="td_left"><?php echo htmlspecialchars2($row['al_message']); ?></td>
                <td class="td_left"><?php echo htmlspecialchars2($row['al_target']); ?></td>
                <td class="td_center"><?php echo htmlspecialchars2($row['al_ip']); ?></td>
                <td class="td_left audit_uri"><?php echo htmlspecialchars2($row['al_uri']); ?></td>
                <td class="td_left">
                    <?php if (trim((string)$row['al_payload'])) { ?>
                        <details>
                            <summary>보기</summary>
                            <pre class="audit_payload"><?php echo htmlspecialchars($row['al_payload'], ENT_QUOTES, 'UTF-8'); ?></pre>
                        </details>
                    <?php } else { ?>
                        <span class="txt_gray">-</span>
                    <?php } ?>
                </td>
            </tr>
        <?php
        }
        if ($i == 0) {
            echo '<tr><td colspan="10" class="empty_table">로그가 없습니다.</td></tr>';
        }
        ?>
        </tbody>
    </table>
</div>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $audit_page, $audit_total_page, "{$_SERVER['SCRIPT_NAME']}?{$qstr}&amp;page="); ?>

<?php
include_once(G5_ADMIN_PATH . '/admin.tail.php');
