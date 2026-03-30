<?php
include_once('./_common.php');
if ($is_admin != 'super') exit;
// 엑셀 다운로드 함수
function array_to_excel($data, $filename){
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
    header('Cache-Control: max-age=0');
    $out = fopen('php://output', 'w');
    fputs($out, "\xEF\xBB\xBF"); // UTF-8 with BOM
    fputcsv($out, array_keys($data[0]), "\t");
    foreach ($data as $row) {
        fputcsv($out, $row, "\t");
    }
    fclose($out);
}

$sql_common = " from g5_membership_status A left join g5_member B on A.uid=B.mb_id";

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

$sql = "select *  $sql_common $sql_search $order";

$result = sql_query($sql);
// 테이블 출력을 위한 HTML 코드 추가
echo "<table border='1'>";
echo "<tr>";
echo "<th>회사명</th>";
echo "<th>사업자등록번호</th>";
echo "<th>이메일주소</th>";
echo "<th>결제일</th>";
echo "<th>금액</th>";
echo "<th>이용시작일</th>";
echo "<th>이용종료일</th>";
echo "<th>상태</th>";
echo "<th>이용상품</th>";
echo "</tr>";

for ($i=0; $row=sql_fetch_array($result); $i++) {

			$membershipInfo	= getMembership($row['membership_parent_no']);
			$paymentInfo			= getPaymentInfo($row['order_no']);
			$timestamp = strtotime("+1 days", strtotime($row['date_end']." 00:00:00"));
			$duedate=date("Y-m-d", $timestamp);
			$pay_cnt=sql_fetch("select count(*) as cnt from  g5_membership_status_history where uid='{$row['uid']}'");
			if($pay_cnt['cnt']>1 && $ms_status_text[$row['status']]=="결제완료") $ms_status_text[$row['status']]="첫결제";
?>
<tr>
<td><?php echo $row['mb_name']; ?></td>
<td><?php echo $row['mb_4']; ?></td>
<td><?php echo $row['mb_email']; ?></td>
<td><?=$duedate?></td>
<td><?=number_format($paymentInfo['price'],0)?>원</td>
<td><?=$row['date_begin']?></td>
<td><?=$row['date_end']?></td>
<td><?=$ms_status_text[$row['status']]?></td>
<td><?=$membershipInfo['title']?></td>
</tr>
<?
}

// 테이블 출력을 위한 HTML 코드 추가
echo "</table>";

// 엑셀 다운로드
$dateString = date("Y-m-d", time());
 array_to_excel($data, '정기결제목록_'.$dateString);
?>
