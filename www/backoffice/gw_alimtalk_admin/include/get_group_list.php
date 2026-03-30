<?php
include_once('./_common.php');

$return_arr = array();

$sql = " select mb_level from {$g5['member_table']} where mb_level not in ('0', '1', '10') GROUP BY mb_level ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++) {
    $sql2 = " select count(*) as cnt from {$g5['member_table']} where mb_level = '{$row['mb_level']}}' and mb_hp != '' ";
    $row2 = sql_fetch($sql2);
    $return_arr[$i] = array("count" => number_format($row2['cnt']), "mb_level" => $row['mb_level'], 'group_name' => "회원등급 : ".$row['mb_level'],);
}

echo json_encode($return_arr);
