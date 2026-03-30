<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
if($_POST['wr_datetime']) sql_query("UPDATE {$g5['write_prefix']}{$bo_table} set wr_datetime='{$_POST['wr_datetime']}' where wr_id={$wr_id}");

if ($w == '' ) {
	/*
    $link_url = get_pretty_url($bo_table, $wr_id, $qstr);

    $super_admin = get_admin('super');
    include_once(G5_LIB_PATH.'/mailer.lib.php');
    ob_start();
    include_once ('./write_update_mail.php');
    $content = ob_get_contents();
    ob_end_clean();

    mailer($wr_name, $super_admin['mb_email'], $super_admin['mb_email'], $wr_subject, $content, 1);
	*/

	//alert('감사합니다. '.$wr_name.'님의 '.$board['bo_subject'].' 되었습니다.', G5_ADMIN_BBS_URL.'/board.php?bo_table='.$bo_table);
	goto_url(G5_ADMIN_BBS_URL.'/board.php?bo_table='.$bo_table, false);
}
