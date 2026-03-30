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

$sql_common = " from {$g5['member_table']}";

$sql_search = " where mb_level <11 ";
if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case 'mb_point':
            $sql_search .= " ({$sfl} >= '{$stx}') ";
            break;
        case 'mb_level':
			$stx=$level_num[$stx];
            $sql_search .= " ({$sfl} = '{$stx}') ";
            break;
        case 'mb_tel':
        case 'mb_hp':
            $sql_search .= " ({$sfl} like '%{$stx}') ";
            break;
        default:
            $sql_search .= " ({$sfl} like '{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

if ($mb_type) {
    $sql_search .= " and ( ";
    switch ($mb_type) {
        case '개인':
            $sql_search .= " (mb_1 = '{$mb_type}') ";
            break;
        case '사업자':
            $sql_search .= " (mb_1 = '{$mb_type}') ";
            break;
        default:
            break;
    }
    $sql_search .= " ) ";
}

if ($is_admin != 'super') {
    $sql_search .= " and mb_level <= '{$member['mb_level']}' ";
}

if (!$sst) {
    $sst = "mb_datetime";
    $sod = "desc";
}

$sql_order = " order by {$sst} {$sod} ";

$sql = " select * {$sql_common} {$sql_search} {$sql_order} ";
$result = sql_query($sql);
// 테이블 출력을 위한 HTML 코드 추가
echo "<table border='1'>";
echo "<tr>";
echo "<th>번호</th>";
echo "<th>가입일</th>";
echo "<th>아이디</th>";
echo "<th>법인명</th>";
echo "<th>사업자등록번호</th>";
echo "<th>담당자 이름</th>";
echo "<th>담당자 직위</th>";
echo "<th>이메일주소</th>";
echo "<th>연락처</th>";
echo "<th>주요서비스/제품</th>";
echo "<th>최종접속일</th>";
echo "</tr>";

for ($i=0; $row=sql_fetch_array($result); $i++) {
    // 데이터 가공
    $mb_gender = '';
    if ($row['mb_gender'] == 'M') {
        $mb_gender = '남성';
    } else if ($row['mb_gender'] == 'F') {
        $mb_gender = '여성';
    }

	// 테이블 출력을 위한 HTML 코드 추가
    echo "<tr>";
    echo "<td>" . ($i+1) . "</td>";
	echo "<td>" . $row['mb_datetime'] . "</td>";
    echo "<td>" . $row['mb_id'] . "</td>";
    echo "<td>" . $row['mb_name'] . "</td>";
    echo "<td>" . $row['mb_4'] . "</td>";
    echo "<td>" . $row['mb_6'] . "</td>";
	echo "<td>" . $row['mb_7'] . "</td>";
	echo "<td>" . $row['mb_email'] . "</td>";
	echo "<td>" . $row['mb_8'] . "</td>";
	echo "<td>" . str_replace(":|:",", ",$row['mb_9']) . "</td>";
    echo "<td>" . $row['mb_today_login'] . "</td>";
    echo "</tr>";
}

// 테이블 출력을 위한 HTML 코드 추가
echo "</table>";

// 엑셀 다운로드
$dateString = date("Y-m-d", time());
 array_to_excel($data, '회원리스트_'.$dateString);
?>
