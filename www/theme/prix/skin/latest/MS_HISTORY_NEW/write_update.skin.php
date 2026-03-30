<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// 순서 사용시 필요함
//$alter="ALTER TABLE {$g5['write_prefix']}{$bo_table} MODIFY wr_10 float";
//sql_query($alter);

goto_url(G5_HTTP_BBS_URL.'/board.php?bo_table='.$bo_table);
?>
