<?php
// Local-only helper for opening the recommendation keyword modal without KODATA.
include_once('./_common.php');
include_once(G5_PATH . '/extend/user.extend.php');

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function company_recommend_modal_test_response($status, $payload)
{
    http_response_code((int)$status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function company_recommend_modal_test_enabled()
{
    $enabled = strtolower(trim((string)macomaex_env('LOCAL_RECOMMEND_MODAL_TEST', '')));
    if (!in_array($enabled, array('1', 'true', 'yes', 'on'), true)) return false;

    $host = strtolower(preg_replace('/:\d+$/', '', (string)($_SERVER['HTTP_HOST'] ?? '')));
    return in_array($host, array('localhost', '127.0.0.1', '::1'), true)
        || substr($host, -5) === '.test'
        || substr($host, -6) === '.local';
}

if (!company_recommend_modal_test_enabled()) {
    company_recommend_modal_test_response(404, array(
        'result' => 'fail',
        'message' => 'Local recommendation modal test is disabled.',
    ));
}

$kedcd = str_pad(preg_replace('/\D/', '', (string)($_GET['kedcd'] ?? '')), 10, '0', STR_PAD_LEFT);
if (strlen($kedcd) !== 10 || preg_match('/^0+$/', $kedcd)) $kedcd = '';

$bizno = preg_replace('/\D/', '', (string)($_GET['bizno'] ?? ''));
if (strlen($bizno) !== 10) $bizno = '';

$keyword_source = trim((string)($_GET['keyword_source'] ?? '')) === 'partner_kw' ? 'partner_kw' : 'expanded';

$where = array("c.KEDCD != ''");
if ($kedcd !== '') {
    $where[] = "ml.c_kedcd='" . sql_real_escape_string($kedcd) . "'";
} else if ($bizno !== '') {
    $where[] = "(ml.c_bizno='" . sql_real_escape_string($bizno) . "' OR c.BZNO='" . sql_real_escape_string($bizno) . "')";
}

$sql = "
    SELECT
        ml.c_id AS ml_log_id,
        ml.c_bizno AS ml_bizno,
        ml.c_trade_type,
        ml.c_datetime AS ml_datetime,
        c.KEDCD,
        c.BZNO,
        c.ENP_NM,
        c.ENP_NM_TRD,
        c.BZC_CD,
        c.ENP_TYP,
        COALESCE(
            (SELECT k_code_name FROM kssc WHERE k_code = c.BZC_CD LIMIT 1),
            (SELECT k_code_name FROM kssc_9 WHERE k_code = c.BZC_CD LIMIT 1),
            ''
        ) AS BZC_NM
    FROM company_ml_log ml
    INNER JOIN company c ON c.KEDCD = ml.c_kedcd
    WHERE " . implode(' AND ', $where) . "
    ORDER BY ml.c_datetime DESC, ml.c_id DESC
    LIMIT 1
";
$row = sql_fetch($sql);

if (!is_array($row) || empty($row['KEDCD'])) {
    company_recommend_modal_test_response(404, array(
        'result' => 'fail',
        'message' => 'company_ml_log와 company 테이블에서 테스트할 기업을 찾지 못했습니다.',
    ));
}

$resolved_bizno = preg_replace('/\D/', '', (string)($row['BZNO'] ?: $row['ml_bizno']));
if (strlen($resolved_bizno) !== 10) {
    company_recommend_modal_test_response(422, array(
        'result' => 'fail',
        'message' => '테스트 기업의 사업자번호가 올바르지 않습니다.',
    ));
}

$company_name = trim((string)($row['ENP_NM_TRD'] ?: $row['ENP_NM']));
$enp_type = '기업';
if ((string)$row['ENP_TYP'] === '1') $enp_type = '법인기업';
else if ((string)$row['ENP_TYP'] === '2') $enp_type = '개인기업';

company_recommend_modal_test_response(200, array(
    'result' => 'success',
    'name' => $company_name !== '' ? $company_name : '로컬 테스트 기업',
    'bzc_name' => trim((string)$row['BZC_NM']) !== '' ? trim((string)$row['BZC_NM']) : '기타 업종',
    'enp_type' => $enp_type,
    'enp_type_code' => (string)$row['ENP_TYP'],
    'bzc_cd' => (string)$row['BZC_CD'],
    'bizno' => $resolved_bizno,
    'bizno_token' => simple_crypt($resolved_bizno, 'e'),
    'kedcd' => (string)$row['KEDCD'],
    'trade_type' => ((int)$row['c_trade_type'] === 2) ? 2 : 1,
    'keyword_source' => $keyword_source,
    'source' => 'local_company_ml_log',
    'test_meta' => array(
        'ml_log_id' => (int)$row['ml_log_id'],
        'ml_datetime' => (string)$row['ml_datetime'],
    ),
));
?>
