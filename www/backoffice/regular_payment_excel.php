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

$sql_common = " from g5_membership_status A left join g5_member B on A.uid=B.mb_id ";
$sql_search = " where (1) ";
if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        default :
            $sql_search .= " ({$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

$order = " ORDER BY no desc";
$sql = "select * $sql_common $sql_search $order";
$result = sql_query($sql);

$headers = array(
    '회사명',
    '사업자등록번호',
    '이메일주소',
    '결제일',
    '금액',
    '전문가컨설팅',
    '이용상품',
    '상태'
);
$rows = array();

for ($i = 0; $row = sql_fetch_array($result); $i++) {
    $membershipInfo = getMembership($row['membership_parent_no']);
    $paymentInfo = getPaymentInfo($row['order_no']);
    $refundInfo = sql_fetch("select id from credit_history where order_no='{$row['order_no']}' and tx_type='REFUND' order by id desc limit 1");
    $is_refunded = (is_array($refundInfo) && !empty($refundInfo['id'])) ? 1 : 0;

    if(!is_array($membershipInfo)) $membershipInfo = array('title'=>'-', 'title_en'=>'');
    if(!is_array($paymentInfo)) $paymentInfo = array('price'=>0);

    $company_name_display = isset($row['mb_name']) ? trim((string)$row['mb_name']) : '';
    if ($company_name_display === '') $company_name_display = '-';

    $biz_no_raw = isset($row['mb_4']) ? trim((string)$row['mb_4']) : '';
    $biz_no_digits = preg_replace('/[^0-9]/', '', $biz_no_raw);
    $biz_no_display = $biz_no_raw;
    if (preg_match('/^[0-9]{10}$/', $biz_no_digits)) {
        $biz_no_display = substr($biz_no_digits, 0, 3) . '-' . substr($biz_no_digits, 3, 2) . '-' . substr($biz_no_digits, 5, 5);
    }
    if ($biz_no_display === '') $biz_no_display = '-';

    $email_display = isset($row['mb_email']) ? trim((string)$row['mb_email']) : '';
    if ($email_display === '') $email_display = '-';

    $duedate = '-';
    if (!empty($row['date_end'])) {
        $end_timestamp = strtotime($row['date_end']." 00:00:00");
        if ($end_timestamp) {
            $duedate = date("Y-m-d", strtotime("+1 days", $end_timestamp));
        }
    }

    $price_value = isset($paymentInfo['price']) ? (int)$paymentInfo['price'] : 0;
    $price_display = $price_value > 0 ? number_format($price_value, 0).'원' : '-';

    $consult_display = '-';
    if ((int)$row['expert_consulting_cnt'] > 0 && ((string)$row['status'] === 'S' || (string)$row['status'] === 'T')) {
        $consult_display = ((int)$row['expert_consulting_able_cnt'] > 0) ? '미사용' : '사용';
    }

    $product_title_display = isset($membershipInfo['title']) ? trim((string)$membershipInfo['title']) : '';
    if ($product_title_display === '') $product_title_display = '-';

    $status_text = isset($ms_status_text[$row['status']]) ? $ms_status_text[$row['status']] : trim((string)$row['status']);
    if ($status_text === '') $status_text = '-';
    $pay_cnt = sql_fetch("select count(*) as cnt from g5_membership_status_history where uid='{$row['uid']}'");
    if (isset($pay_cnt['cnt']) && (int)$pay_cnt['cnt'] === 1 && $status_text === '결제완료') {
        $status_text = '첫결제';
    }
    if ($is_refunded) {
        $status_text = '환불완료';
    }
    if (!empty($row['coupon'])) {
        $status_text .= ' / 쿠폰사용';
    }

    $rows[] = array(
        $company_name_display,
        $biz_no_display,
        $email_display,
        $duedate,
        $price_display,
        $consult_display,
        $product_title_display,
        $status_text
    );
}

$dateString = date("Y-m-d", time());
array_to_excel($headers, $rows, '정기결제목록_'.$dateString);
exit;
?>
