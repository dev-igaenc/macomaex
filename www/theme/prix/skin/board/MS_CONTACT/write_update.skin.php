<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// 연락처
$sql1  = " update $write_table set wr_10 = '{$_POST['wr_10']}' where wr_id = '$wr_id' ";
sql_query($sql1);
if($_POST['wr_email'])  sql_query("update $write_table set wr_email = '{$_POST['wr_email']}' where wr_id = '$wr_id' ");
if($_POST['wr_name'])  sql_query("update $write_table set wr_name = '{$_POST['wr_name']}' where wr_id = '$wr_id' ");
if ($w == '' ) {
	//alert('감사합니다. Contact가 등록되었습니다.', G5_URL);

	/* 단일 메일(회원 => 최고관리자에게 메일감)
		$link_url = get_pretty_url($bo_table, $wr_id, $qstr); // 메일용 url

		$super_admin = get_admin('super');
		include_once(G5_LIB_PATH.'/mailer.lib.php');
		ob_start();
		include_once ('./write_update_mail.php');
		$content = ob_get_contents();
		ob_end_clean();

		mailer($wr_name, $super_admin['mb_email'], $super_admin['mb_email'], $wr_subject, $content, 1);
	*/

	/* 다중 메일
		$link_url=G5_URL."/bbs/board.php?bo_table={$bo_table}&wr_id={$wr_id}"; // 메일용 url
		include_once(G5_LIB_PATH.'/mailer.lib.php');
		ob_start();
		include_once ('./write_update_mail.php');
		$content = ob_get_contents();
		ob_end_clean();

		$array_email = array();
		$super_admin = get_admin('super');
		$mail_title=$_POST['wr_name']."님의 문의글이 등록되었습니다";

		$receive_mail=array(
			"jms54@naver.com",
			"eventietter@naver.com"
		);
		foreach ($receive_mail as $mails){
			mailer($_POST['wr_name'], $super_admin['mb_email'], $mails, $mail_title, $content, 1);
		}
	*/

	alert('감사합니다. '.$board['bo_subject'].'가 접수 되었습니다.', G5_HTTP_BBS_URL.'/write.php?bo_table='.$bo_table);
}
