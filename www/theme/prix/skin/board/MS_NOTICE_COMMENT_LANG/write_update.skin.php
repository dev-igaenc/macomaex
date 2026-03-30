<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// 순서 사용시 필요함
//$alter="ALTER TABLE {$g5['write_prefix']}{$bo_table} MODIFY wr_10 float";
//sql_query($alter);

// 등록일 사용시 필요함
if($_POST['wr_datetime']) sql_query("UPDATE {$g5['write_prefix']}{$bo_table} set wr_datetime='{$_POST['wr_datetime']}' where wr_id={$wr_id}");
