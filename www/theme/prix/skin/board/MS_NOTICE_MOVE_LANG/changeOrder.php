<?php
include $_SERVER["DOCUMENT_ROOT"]."/common.php";

if ($is_member && ($is_admin == 'super' || $group['gr_admin'] == $member['mb_id'] || $board['bo_admin'] == $member['mb_id'])) {
	for ($i=0; $i<count($_POST['order_wr_id']); $i++){
		$wr_10 = $_POST['biggest'] - $i;
		$sql1 = " update ".$write_table." set wr_10 = '".$wr_10."' where wr_id = '".$_POST['order_wr_id'][$i]."' ";
		sql_query($sql1);
	}
}