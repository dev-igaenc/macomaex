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
print("<meta http-equiv=\"Content-Type\" content=\"application/vnd.ms-excel; charset=utf-8\">");
$sql_common = " from {$g5['g5_shop_coupon_log_table']}";

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
if (!$sst) {
    $sst  = "cl_id";
    $sod = "desc";
}
$sql_order = " order by {$sst} {$sod} ";

$sql = "select * $sql_common $sql_search {$sql_order}";

$result = sql_query($sql);
// 테이블 출력을 위한 HTML 코드 추가
echo "<table border='1'>".PHP_EOL;
echo "<tr>".PHP_EOL;
echo "<th>번호</th>".PHP_EOL;
echo "<th>아이디</th>".PHP_EOL;
echo "<th>쿠폰명</th>".PHP_EOL;
echo "<th>쿠폰번호</th>".PHP_EOL;
echo "<th>쿠폰생성일</th>".PHP_EOL;
echo "<th>쿠폰사용일</th>".PHP_EOL;
echo "<th>맴버쉽</th>".PHP_EOL;
echo "<th>무료기간</th>".PHP_EOL;
echo "</tr>".PHP_EOL;

for ($i=0; $v=sql_fetch_array($result); $i++) {
	$mship=sql_fetch("select title from g5_membership_manage where no='{$v[cp_type]}'");

	// 테이블 출력을 위한 HTML 코드 추가
    echo "<tr>".PHP_EOL;
    echo "<td>" . ($i+1) ."</td>".PHP_EOL;
	echo "<td>" . $v['mb_id'] . "</td>".PHP_EOL;
	echo "<td>" . $v['cp_subject'] . "</td>".PHP_EOL;
    echo "<td>" . $v['cp_id'] . "</td>".PHP_EOL;
	echo "<td>" . $v['cp_start'] . "</td>".PHP_EOL;
	echo "<td>" . $v['cl_datetime'] . "</td>".PHP_EOL;
	echo "<td>" . $mship['title'] . "</td>".PHP_EOL;
	echo "<td>" . $v['cp_trunc'] . "</td>".PHP_EOL;
    echo "</tr>".PHP_EOL;
}

// 테이블 출력을 위한 HTML 코드 추가
echo "</table>".PHP_EOL;

// 엑셀 다운로드
$dateString = date("Ymd", time());
 array_to_excel($data, '쿠폰사용목록_'.$dateString);
?>
