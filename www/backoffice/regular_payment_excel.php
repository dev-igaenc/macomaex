<?php
include_once('./_common.php');
if ($is_admin != 'super') exit;

require_once G5_PATH . '/lib/PHPExcel.php';

function excel_col_name($index) {
    $name = '';
    $index = (int)$index;
    while ($index > 0) {
        $mod = ($index - 1) % 26;
        $name = chr(65 + $mod) . $name;
        $index = (int)(($index - $mod) / 26);
    }
    return $name;
}

function array_to_excel($headers, $rows, $filename){
    $obj = new PHPExcel();
    $obj->setActiveSheetIndex(0);
    $sheet = $obj->getActiveSheet();
    $sheet->setTitle('결제 관리');

    for ($i = 0; $i < count($headers); $i++) {
        $col = excel_col_name($i + 1);
        $sheet->setCellValueExplicit($col . '1', (string)$headers[$i], PHPExcel_Cell_DataType::TYPE_STRING);
    }

    $row_no = 2;
    for ($r = 0; $r < count($rows); $r++) {
        $line = $rows[$r];
        for ($c = 0; $c < count($headers); $c++) {
            $col = excel_col_name($c + 1);
            $value = isset($line[$c]) ? (string)$line[$c] : '';
            $sheet->setCellValueExplicit($col . $row_no, $value, PHPExcel_Cell_DataType::TYPE_STRING);
        }
        $row_no++;
    }

    for ($c = 0; $c < count($headers); $c++) {
        $sheet->getColumnDimension(excel_col_name($c + 1))->setAutoSize(true);
    }

    $safe_filename = preg_replace('/[^a-zA-Z0-9_\-가-힣]/u', '_', (string)$filename);
    if (!$safe_filename) $safe_filename = 'regular_payment_list';

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $safe_filename . '.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = PHPExcel_IOFactory::createWriter($obj, 'Excel2007');
    $writer->save('php://output');
}

$sql_common = " from g5_membership_paybill PB
    left join g5_membership_payment A on PB.order_no = A.order_no
    left join g5_member B on PB.uid = B.mb_id
    left join g5_membership_manage M on PB.membership_parent_no = M.no ";
$sql_search = " where (1) ";

// 리스트 화면과 같은 검색 키만 허용
$allowed_sfl = array('uid', 'order_no', 'mb_name', 'mb_email');
if (!isset($sfl) || !in_array($sfl, $allowed_sfl, true)) {
    $sfl = '';
}

if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case 'uid':
            $sql_search .= " (PB.uid like '%{$stx}%') ";
            break;
        case 'order_no':
            $sql_search .= " (PB.order_no like '%{$stx}%') ";
            break;
        case 'mb_name':
            $sql_search .= " (B.mb_name like '%{$stx}%') ";
            break;
        case 'mb_email':
            $sql_search .= " (B.mb_email like '%{$stx}%') ";
            break;
        default:
            $sql_search .= " (PB.order_no like '%{$stx}%' or PB.uid like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

// 리스트 화면과 같은 상태 탭(환불완료/환불전) 필터 반영
$refund_filter = isset($_GET['is_refund']) ? trim((string)$_GET['is_refund']) : '';
if ($refund_filter === '1') {
    $sql_search .= " and exists (select 1 from credit_history H where H.order_no = PB.order_no and H.tx_type='REFUND') ";
} else if ($refund_filter === '0') {
    $sql_search .= " and not exists (select 1 from credit_history H where H.order_no = PB.order_no and H.tx_type='REFUND') ";
}

$sql = "select
        PB.*,
        B.mb_name,
        B.mb_email,
        B.mb_4,
        M.title as membership_title,
        M.title_en as membership_title_en
    {$sql_common}
    {$sql_search}
    order by PB.no desc";
$result = sql_query($sql);

$headers = array(
    '회사명',
    '사업자등록번호',
    '이메일주소',
    '결제시간',
    '주문번호',
    '결제수단',
    '금액',
    '이용상품',
    '상태'
);
$rows = array();

for ($i = 0; $row = sql_fetch_array($result); $i++) {
    // 상태는 paybill 컬럼이 아니라 credit_history REFUND 이력으로 계산
    $refundInfo = sql_fetch("select id from credit_history where order_no='{$row['order_no']}' and tx_type='REFUND' order by id desc limit 1");
    $is_refunded = (is_array($refundInfo) && !empty($refundInfo['id'])) ? 1 : 0;
    $company_name_display = trim((string)$row['mb_name']);
    if ($company_name_display === '') $company_name_display = trim((string)$row['uid']);
    if ($company_name_display === '') $company_name_display = '-';

    $biz_no_raw = trim((string)$row['mb_4']);
    $biz_no_digits = preg_replace('/[^0-9]/', '', $biz_no_raw);
    $biz_no_display = $biz_no_raw;
    if (preg_match('/^[0-9]{10}$/', $biz_no_digits)) {
        $biz_no_display = substr($biz_no_digits, 0, 3) . '-' . substr($biz_no_digits, 3, 2) . '-' . substr($biz_no_digits, 5, 5);
    }
    if ($biz_no_display === '') $biz_no_display = '-';

    $email_display = trim((string)$row['mb_email']);
    if ($email_display === '') $email_display = '-';

    $pay_method = "신용카드";
    if ($row['pay_method'] === "DirectBank") $pay_method = "실시간계좌이체";
    else if ($row['pay_method'] === "VBank") $pay_method = "가상계좌";
    else if ($row['pay_method'] === "None" || !$row['pay_method']) $pay_method = "무료";

    $price_value = (int)$row['price'];
    $price_display = $price_value > 0 ? number_format($price_value, 0) . '원' : '-';

    $product_title_display = trim((string)$row['membership_title']);
    if ($product_title_display === '') $product_title_display = trim((string)$row['order_goods']);
    if ($product_title_display === '') $product_title_display = '-';

    $status_text = '결제완료';
    if ($is_refunded) {
        $status_text = '환불완료';
    }

    $rows[] = array(
        $company_name_display,
        $biz_no_display,
        $email_display,
        (string)$row['regdate'],
        (string)$row['order_no'],
        $pay_method,
        $price_display,
        $product_title_display,
        $status_text
    );
}

$dateString = date("Y-m-d", time());
array_to_excel($headers, $rows, '정기결제목록_'.$dateString);
exit;
?>
