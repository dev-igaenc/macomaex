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

$sql_common = " from g5_matching A left join g5_member B on A.request_id=B.mb_id left join g5_member C on A.receive_id=C.mb_id";

$sql_search = " where del !='D' ";
if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        default :
            $sql_search .= " ({$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}
$sql = "select *, C.mb_name as r_name  $sql_common $sql_search order by no desc";

$result = sql_query($sql);
// 테이블 출력을 위한 HTML 코드 추가
echo "<table border='1'>";
echo "<tr>";
echo "<th>상태</th>";
echo "<th>매칭요청</th>";
echo "<th>사업자등록번호</th>";
echo "<th>담당자이름</th>";
echo "<th>직위</th>";
echo "<th>이메일</th>";
echo "<th>연락처</th>";
echo "<th>매칭수락</th>";
echo "<th>사업자등록번호</th>";
echo "<th>담당자이름</th>";
echo "<th>직위</th>";
echo "<th>연락처</th>";
echo "<th>이메일</th>";
echo "<th>메세지</th>";
echo "<th>등록일</th>";
echo "</tr>";

for ($i=0; $v=sql_fetch_array($result); $i++) {

			$kind = $v['rc_kind'];
			$requestCon	= getUserInfo($v['request_id']);
			$recieveCon	= getUserInfo($v['receive_id']);

			$recieveCon2	= getComapyInfo($v['KEDCD']);
			$status = "";
			$statusClass = "";
			if($v['receive_approval'] == "N"){
				$status = "매칭요청중";
				$statusClass = "btn btn-sm bg-secondary text-secondary text-light";
			}else if($v['receive_approval'] == "Y"){
				$status = "매칭완료";
				$statusClass = "btn btn-sm bg-success text-success text-light";
			}

			$mb_6="-";
			if($recieveCon['mb_6']){
				$mb_6=$recieveCon['mb_6'];
			}

			$mb_7="-";
			if($recieveCon['mb_7']){
				$mb_7=$recieveCon['mb_7'];
			}

			$mb_email="-";
			if($recieveCon['mb_email']){
				$mb_email=$recieveCon['mb_email'];
			}

			$mb_8="-";
			if($recieveCon['mb_8']){
				$mb_8=$recieveCon['mb_8'];
			}


	// 테이블 출력을 위한 HTML 코드 추가
    echo "<tr>";
    echo "<td>" . $status . "</td>";
	echo "<td>" . $requestCon['mb_name'] . "</td>";
    echo "<td>" . $requestCon['mb_4'] . "</td>";
    echo "<td>" . $requestCon['mb_6'] . "</td>";
    echo "<td>" . $requestCon['mb_7'] . "</td>";
    echo "<td>" . $requestCon['mb_email'] . "</td>";
	echo "<td>" . $requestCon['mb_8'] . "</td>";
	echo "<td>" . $recieveCon2[ENP_NM] . "</td>";
	echo "<td>" . $recieveCon2[BZNO] . "</td>";
	echo "<td>" . $mb_6 . "</td>";
    echo "<td>" . $mb_7 . "</td>";
	echo "<td>" . $mb_email . "</td>";
	echo "<td>" . $mb_8 . "</td>";
	echo "<td>" . nl2br($v['contents']) . "</td>";
	echo "<td>" . $v['regdate'] . "</td>";
    echo "</tr>";
}

// 테이블 출력을 위한 HTML 코드 추가
echo "</table>";

// 엑셀 다운로드
$dateString = date("Y-m-d", time());
 array_to_excel($data, '매칭리스트_'.$dateString);
?>
