<?php
include_once('./_common.php');

$mb_level = $_POST['mb_level'];
$return_arr = array();

if ($mb_level) {
    // 중복 번호를 걸러내는 부분이나, 테스트를 위해서 걸러내지 않음.
    //$sql = " select * from {$g5['member_table']} where mb_level = '{$mb_level}' and mb_hp != '' group by mb_hp order by mb_name asc ";

    $sql = " select * from {$g5['member_table']} where mb_level = '{$mb_level}' and mb_hp != '' order by mb_name asc ";
    $result = sql_query($sql);
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        $row['mb_hp'] = preg_replace("/[^0-9]*/s", "", $row['mb_hp']);
        $return_arr[$i] = array("mb_id" => $row['mb_id'], "mb_hp" => $row['mb_hp'], 'mb_name'	=> $row['mb_name']);
    }
}

echo json_encode($return_arr);
