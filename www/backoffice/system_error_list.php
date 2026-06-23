<?php
$sub_menu = '100000';
$current_menu = '980000';
include_once('./_common.php');

if ($is_admin != 'super') {
    alert('최고관리자만 접근 가능합니다.');
}

$error_table = function_exists('system_error_log_table_name') ? system_error_log_table_name() : (G5_TABLE_PREFIX . 'system_error_log');

if (!function_exists('system_error_log_table_exists') || !system_error_log_table_exists()) {
    $g5['title'] = '시스템 에러 로그';
    include_once(G5_ADMIN_PATH . '/admin.head.php');
    echo '<div class="local_desc01 local_desc"><p>에러 로그 테이블이 없어 조회할 수 없습니다. 자동 생성은 꺼져 있으니 SQL을 수동 적용해 주세요.</p></div>';
    include_once(G5_ADMIN_PATH . '/admin.tail.php');
    exit;
}

$allowed_sfl = array(
    'el_message' => '메시지',
    'el_file' => '파일',
    'el_request_uri' => '요청URI',
    'el_mb_id' => '관리자ID',
    'el_fingerprint' => '지문'
);

$err_sfl = isset($_GET['sfl']) ? trim((string)$_GET['sfl']) : 'el_message';
if (!isset($allowed_sfl[$err_sfl])) {
    $err_sfl = 'el_message';
}
$err_stx = isset($_GET['stx']) ? trim((string)$_GET['stx']) : '';
$err_level = isset($_GET['level']) ? strtoupper(trim((string)$_GET['level'])) : '';
$err_type = isset($_GET['type']) ? strtolower(trim((string)$_GET['type'])) : '';

$err_date_from = isset($_GET['date_from']) ? preg_replace('/[^0-9\-]/', '', (string)$_GET['date_from']) : date('Y-m-d', strtotime('-7 days'));
$err_date_to = isset($_GET['date_to']) ? preg_replace('/[^0-9\-]/', '', (string)$_GET['date_to']) : date('Y-m-d');

$err_rows = isset($config['cf_page_rows']) && (int)$config['cf_page_rows'] > 0 ? (int)$config['cf_page_rows'] : 30;
$err_page = isset($page) ? (int)$page : 1;
if ($err_page < 1) {
    $err_page = 1;
}

$err_where = " where 1=1 ";
if ($err_stx !== '') {
    $err_where .= " and {$err_sfl} like '%" . sql_real_escape_string($err_stx) . "%' ";
}
if ($err_level !== '') {
    $err_where .= " and el_level = '" . sql_real_escape_string($err_level) . "' ";
}
if ($err_type !== '') {
    $err_where .= " and el_type = '" . sql_real_escape_string($err_type) . "' ";
}
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $err_date_from)) {
    $err_where .= " and el_datetime >= '" . sql_real_escape_string($err_date_from) . " 00:00:00' ";
}
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $err_date_to)) {
    $err_where .= " and el_datetime <= '" . sql_real_escape_string($err_date_to) . " 23:59:59' ";
}

$err_sql_common = " from {$error_table} {$err_where} ";

$err_count_row = sql_fetch(" select count(*) as cnt {$err_sql_common} ");
$err_total_count = (int)$err_count_row['cnt'];
$err_total_page = (int)ceil($err_total_count / $err_rows);
$err_from_record = ($err_page - 1) * $err_rows;

$err_summary = sql_fetch(" select
                            count(*) as total_cnt,
                            sum(case when el_type='php_fatal' then 1 else 0 end) as fatal_cnt,
                            sum(case when el_type='php_exception' then 1 else 0 end) as exception_cnt,
                            sum(case when el_type='php_error' then 1 else 0 end) as error_cnt
                          {$err_sql_common} ");

$err_sql = " select *
             {$err_sql_common}
             order by el_id desc
             limit {$err_from_record}, {$err_rows} ";
$err_result = sql_query($err_sql);

$err_qarr = array(
    'sfl' => $err_sfl,
    'stx' => $err_stx,
    'level' => $err_level,
    'type' => $err_type,
    'date_from' => $err_date_from,
    'date_to' => $err_date_to
);
$err_qstr = http_build_query($err_qarr);
$err_csv_url = './system_error_csv.php' . ($err_qstr ? ('?' . $err_qstr) : '');

$g5['title'] = '시스템 에러 로그';
include_once(G5_ADMIN_PATH . '/admin.head.php');
?>
<style>
.err_badge {
    display: inline-block;
    min-width: 70px;
    text-align: center;
    padding: 3px 8px;
    border-radius: 10px;
    font-size: 12px;
}
.err_badge.fatal { background: #fdecec; color: #b01717; }
.err_badge.exception { background: #fff3cd; color: #7d5a00; }
.err_badge.error { background: #eef3ff; color: #2f55a4; }
.err_payload {
    max-height: 180px;
    overflow: auto;
    margin: 8px 0 0 0;
    padding: 10px;
    background: #f8f9fa;
    border: 1px solid #e5e8ec;
    white-space: pre-wrap;
    word-break: break-all;
}
.err_uri {
    max-width: 240px;
    word-break: break-all;
}
</style>

<h2><?php echo $g5['title']; ?></h2>
<div class="local_desc01 local_desc">
    <p>PHP 경고/예외/치명적 에러를 기록합니다. 우선 `php_fatal`, `php_exception`부터 확인하세요.</p>
</div>

<form method="get" class="local_sch01 local_sch">
    <div class="local_ov01 local_ov">
        <span class="btn_ov01"><span class="ov_txt">전체</span><span class="ov_num"> <?php echo number_format((int)$err_summary['total_cnt']); ?>건</span></span>
        <span class="btn_ov01"><span class="ov_txt">Fatal</span><span class="ov_num"> <?php echo number_format((int)$err_summary['fatal_cnt']); ?>건</span></span>
        <span class="btn_ov01"><span class="ov_txt">Exception</span><span class="ov_num"> <?php echo number_format((int)$err_summary['exception_cnt']); ?>건</span></span>
        <span class="btn_ov01"><span class="ov_txt">Error</span><span class="ov_num"> <?php echo number_format((int)$err_summary['error_cnt']); ?>건</span></span>

        <div style="display:inline-block; margin-left:14px;">
            <label for="date_from">기간</label>
            <input type="date" name="date_from" id="date_from" value="<?php echo $err_date_from; ?>" class="frm_input" />
            <span>~</span>
            <input type="date" name="date_to" id="date_to" value="<?php echo $err_date_to; ?>" class="frm_input" />

            <select name="type" id="type">
                <option value="" <?php echo get_selected($err_type, ''); ?>>전체타입</option>
                <option value="php_fatal" <?php echo get_selected($err_type, 'php_fatal'); ?>>php_fatal</option>
                <option value="php_exception" <?php echo get_selected($err_type, 'php_exception'); ?>>php_exception</option>
                <option value="php_error" <?php echo get_selected($err_type, 'php_error'); ?>>php_error</option>
            </select>

            <select name="level" id="level">
                <option value="" <?php echo get_selected($err_level, ''); ?>>전체레벨</option>
                <option value="ERROR" <?php echo get_selected($err_level, 'ERROR'); ?>>ERROR</option>
                <option value="WARNING" <?php echo get_selected($err_level, 'WARNING'); ?>>WARNING</option>
                <option value="EXCEPTION" <?php echo get_selected($err_level, 'EXCEPTION'); ?>>EXCEPTION</option>
                <option value="PARSE" <?php echo get_selected($err_level, 'PARSE'); ?>>PARSE</option>
            </select>

            <select name="sfl" id="sfl">
                <?php foreach ($allowed_sfl as $key => $label) { ?>
                    <option value="<?php echo $key; ?>" <?php echo get_selected($err_sfl, $key); ?>><?php echo $label; ?></option>
                <?php } ?>
            </select>
            <input type="text" name="stx" value="<?php echo get_sanitize_input($err_stx); ?>" class="frm_input" placeholder="검색어" />
            <button type="submit" class="btn btn_03">검색</button>
            <a href="<?php echo $err_csv_url; ?>" class="btn btn_03">CSV 다운로드</a>
            <a href="./system_error_list.php" class="btn btn_02">초기화</a>
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
            <th scope="col">타입</th>
            <th scope="col">레벨</th>
            <th scope="col">메시지</th>
            <th scope="col">파일/라인</th>
            <th scope="col">관리자</th>
            <th scope="col">요청URI</th>
            <th scope="col">IP</th>
            <th scope="col">상세</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $err_list_num = $err_total_count - (($err_page - 1) * $err_rows);
        for ($i = 0; $row = sql_fetch_array($err_result); $i++) {
            $bg = 'bg' . ($i % 2);
            $type_class = 'error';
            if ($row['el_type'] === 'php_fatal') {
                $type_class = 'fatal';
            } else if ($row['el_type'] === 'php_exception') {
                $type_class = 'exception';
            }
            ?>
            <tr class="<?php echo $bg; ?>">
                <td class="td_num"><?php echo $err_list_num - $i; ?></td>
                <td class="td_datetime"><?php echo htmlspecialchars2($row['el_datetime']); ?></td>
                <td class="td_center"><span class="err_badge <?php echo $type_class; ?>"><?php echo htmlspecialchars2($row['el_type']); ?></span></td>
                <td class="td_center"><?php echo htmlspecialchars2($row['el_level']); ?></td>
                <td class="td_left"><?php echo htmlspecialchars2($row['el_message']); ?></td>
                <td class="td_left"><?php echo htmlspecialchars2($row['el_file']); ?><br><span class="txt_gray">line: <?php echo (int)$row['el_line']; ?></span></td>
                <td class="td_center"><?php echo htmlspecialchars2($row['el_mb_id']); ?></td>
                <td class="td_left err_uri"><?php echo htmlspecialchars2($row['el_request_uri']); ?></td>
                <td class="td_center"><?php echo htmlspecialchars2($row['el_ip']); ?></td>
                <td class="td_left">
                    <details>
                        <summary>보기</summary>
                        <div class="txt_gray">Fingerprint: <?php echo htmlspecialchars2($row['el_fingerprint']); ?></div>
                        <pre class="err_payload"><?php echo htmlspecialchars((string)$row['el_payload'], ENT_QUOTES, 'UTF-8'); ?></pre>
                    </details>
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

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $err_page, $err_total_page, "{$_SERVER['SCRIPT_NAME']}?{$err_qstr}&amp;page="); ?>

<?php
include_once(G5_ADMIN_PATH . '/admin.tail.php');
