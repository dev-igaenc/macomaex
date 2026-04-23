<?php
/**
 * api/company.php — Cafe24용 company API
 *
 * GET ?bizno=1868101689   → company_api_log 에서 cold-start 파라미터 반환
 * GET ?kedcds=0001234567  → 카드 기업정보 보강 (미구현, 빈 응답)
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// ── DB 연결 (gnuboard dbconfig 재사용) ───────────────────────────
if (!defined('_GNUBOARD_')) define('_GNUBOARD_', true);
$_dbconfig = __DIR__ . '/../data/dbconfig.php';
if (file_exists($_dbconfig)) {
    require_once $_dbconfig;
}

function db_connect() {
    $host = defined('G5_MYSQL_HOST') ? G5_MYSQL_HOST : 'localhost';
    $user = defined('G5_MYSQL_USER') ? G5_MYSQL_USER : '';
    $pass = defined('G5_MYSQL_PASSWORD') ? G5_MYSQL_PASSWORD : '';
    $db   = defined('G5_MYSQL_DB') ? G5_MYSQL_DB : '';
    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) return null;
    $conn->set_charset('utf8mb4');
    return $conn;
}

function xml_val($v) {
    if ($v === null || is_array($v) || is_object($v)) return '';
    return trim((string)$v);
}
function xml_int($v) {
    $s = xml_val($v);
    return $s !== '' ? (int)$s : 0;
}

// ── 라우팅 ───────────────────────────────────────────────────────
$bizno  = preg_replace('/\D/', '', $_GET['bizno']  ?? '');
$kedcds = $_GET['kedcds'] ?? '';

// ── Mode 2: bizno → company_api_log cold-start 파라미터 ─────────
if ($bizno !== '') {
    if (strlen($bizno) !== 10) {
        echo json_encode(['ok' => false, 'error' => 'bizno 10자리 필요']);
        exit;
    }

    $conn = db_connect();
    if (!$conn) {
        echo json_encode(['ok' => false, 'error' => 'DB 연결 실패']);
        exit;
    }

    $stmt = $conn->prepare(
        "SELECT c_kedcd, c_content FROM company_api_log WHERE c_bizno = ? ORDER BY c_datetime DESC LIMIT 1"
    );
    if (!$stmt) {
        $conn->close();
        echo json_encode(['ok' => false, 'error' => 'company_api_log 테이블 없음']);
        exit;
    }
    $stmt->bind_param('s', $bizno);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    $conn->close();

    if (!$row) {
        echo json_encode(['ok' => false, 'message' => 'company_api_log 미존재']);
        exit;
    }

    $kedcd = $row['c_kedcd'] ?? '';
    $info  = json_decode($row['c_content'] ?? '{}', true) ?: [];

    $enp  = ($info['CONTENTS']['ENPintr0000'] ?? []);
    $cust = ($info['CONTENTS']['ENPcust0001'] ?? []);
    $supp = ($info['CONTENTS']['ENPsupp0001'] ?? []);
    $fsdt = ($info['CONTENTS']['ENPfsdt0001'] ?? []);
    $kbad = ($info['CONTENTS']['ENPkbad0000'] ?? []);

    // 거래처 이름 수집
    $cust_names = []; $supp_names = [];
    for ($i = 1; $i <= 10; $i++) {
        $cn = xml_val($cust['cust_nm' . $i] ?? '');
        $sn = xml_val($supp['supp_nm' . $i] ?? '');
        if ($cn && $cn !== '기타') $cust_names[] = $cn;
        if ($sn && $sn !== '기타') $supp_names[] = $sn;
    }

    $real_kedcd = preg_replace('/\D/', '', xml_val($enp['kedcd'] ?? ''));
    if (strlen($real_kedcd) < 10) $real_kedcd = $kedcd;
    $real_kedcd = str_pad($real_kedcd, 10, '0', STR_PAD_LEFT);

    echo json_encode([
        'ok'   => true,
        'cold' => [
            'kedcd'      => $real_kedcd,
            'enp_nm'     => xml_val($enp['enp_nm_trd'] ?? '') ?: xml_val($enp['enp_nm'] ?? ''),
            'bzc_cd'     => xml_val($enp['bzc_cd']   ?? ''),
            'bzc_nm'     => xml_val($enp['bzc_nm']   ?? ''),
            'enp_typ'    => xml_val($enp['enp_typ']  ?? ''),
            'sido'       => mb_substr(xml_val($enp['loc_addra'] ?? ''), 0, 3),
            'sze'        => xml_val($enp['enp_sze']  ?? '') ?: '3',
            'estb_dt'    => xml_val($enp['estb_dt']  ?? ''),
            'mpd'        => xml_val($enp['mpd']      ?? ''),
            'sales'      => xml_int($fsdt['fs_val9']  ?? 0),
            'prev_sales' => xml_int($fsdt['fs1_val9'] ?? 0),
            'asset'      => xml_int($fsdt['fs_val3']  ?? 0),
            'debt'       => xml_int($fsdt['fs_val6']  ?? 0),
            'bad_cnt'    => xml_int($kbad['kfb_bad_cnt'] ?? 0),
            'cust_names' => implode('|', array_slice($cust_names, 0, 10)),
            'supp_names' => implode('|', array_slice($supp_names, 0, 10)),
        ],
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Mode 1: kedcds 배치 조회 (미구현 — 빈 응답) ─────────────────
if ($kedcds !== '') {
    echo json_encode(['ok' => true, 'companies' => []]);
    exit;
}

echo json_encode(['ok' => false, 'error' => 'bizno 또는 kedcds 파라미터 필요']);
