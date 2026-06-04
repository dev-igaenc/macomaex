<?php
session_start();
require_once __DIR__ . '/api_config.php';

if (!isset($_SESSION['mltest_auth'])) {
    http_response_code(401);
    echo json_encode(['error' => '인증 필요']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$bzc_cd     = substr(preg_replace('/[^A-Za-z0-9]/', '', $_GET['bzc_cd'] ?? ''), 0, 10);
$trade_type = (int)($_GET['trade_type'] ?? 1);
if (!in_array($trade_type, [1, 2], true)) $trade_type = 1;

if (!$bzc_cd) {
    echo json_encode(['ok' => false, 'error' => 'bzc_cd 필요']);
    exit;
}

$conn = @new mysqli(DEV_DB_HOST, DEV_DB_USER, DEV_DB_PASS, DEV_DB_NAME);
if ($conn->connect_error) {
    echo json_encode(['ok' => false, 'error' => 'DB 연결 실패: ' . $conn->connect_error]);
    exit;
}
$conn->set_charset('utf8mb4');

// 정확히 일치 → 없으면 앞 4자리 prefix fallback
$stmt = $conn->prepare(
    "SELECT c_partner_bzc, c_partner_kw, c_trade_cnt
     FROM bzc_trade_rec
     WHERE c_source_bzc = ? AND c_trade_type = ?
     ORDER BY c_trade_cnt DESC
     LIMIT 15"
);
if (!$stmt) {
    $conn->close();
    echo json_encode(['ok' => false, 'error' => '쿼리 실패: ' . $conn->error]);
    exit;
}

$stmt->bind_param('si', $bzc_cd, $trade_type);
$stmt->execute();
$res  = $stmt->get_result();
$rows = [];
while ($r = $res->fetch_assoc()) {
    $rows[] = [
        'bzc' => $r['c_partner_bzc'],
        'kw'  => $r['c_partner_kw'],
        'cnt' => (int)$r['c_trade_cnt'],
    ];
}
$stmt->close();

// fallback: prefix 4자리로 재시도
if (empty($rows) && strlen($bzc_cd) > 4) {
    $prefix = substr($bzc_cd, 0, 4) . '%';
    $stmt2  = $conn->prepare(
        "SELECT c_partner_bzc, c_partner_kw, c_trade_cnt
         FROM bzc_trade_rec
         WHERE c_source_bzc LIKE ? AND c_trade_type = ?
         ORDER BY c_trade_cnt DESC
         LIMIT 15"
    );
    if ($stmt2) {
        $stmt2->bind_param('si', $prefix, $trade_type);
        $stmt2->execute();
        $res2 = $stmt2->get_result();
        while ($r = $res2->fetch_assoc()) {
            $rows[] = [
                'bzc' => $r['c_partner_bzc'],
                'kw'  => $r['c_partner_kw'],
                'cnt' => (int)$r['c_trade_cnt'],
            ];
        }
        $stmt2->close();
    }
}

$conn->close();
echo json_encode(['ok' => true, 'keywords' => $rows, 'bzc_cd' => $bzc_cd, 'trade_type' => $trade_type], JSON_UNESCAPED_UNICODE);
