<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
$delete_str = "";
if ($w == 'x') $delete_str = "댓";
if ($w == 'u') $g5['title'] = $delete_str.$lang_password_title3;
else if ($w == 'd' || $w == 'x') $g5['title'] = $delete_str.$lang_password_title2;
else $g5['title'] = $g5['title'];

//add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>
<!-- 비밀번호 확인 시작 { -->
<div id="pw_confirm" class="mbskin kookMin newLogin">
	<dl class="topTit">
		<dt><?php echo $g5['title'] ?></dt>
		<dd>
			<?php if ($w == 'u') { ?>
				<?=$lang_password_text3?>
			<?php } else if ($w == 'd' || $w == 'x') {  ?>
				<?=$lang_password_text2?>
			<?php } else {  ?>
				<?=$lang_password_text?>
			<?php }  ?>
		</dd>
	</dl>

	<form name="fboardpassword" action="<?php echo $action;  ?>" method="post">
		<input type="hidden" name="w" value="<?php echo $w ?>">
		<input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
		<input type="hidden" name="wr_id" value="<?php echo $wr_id ?>">
		<input type="hidden" name="comment_id" value="<?php echo $comment_id ?>">
		<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
		<input type="hidden" name="stx" value="<?php echo $stx ?>">
		<input type="hidden" name="page" value="<?php echo $page ?>">

		<fieldset id="login_fs">
			<label for="pw_wr_password" class="sound_only">비밀번호<strong>필수</strong></label>
			<input type="password" name="wr_password" id="password_wr_password" required class="frm_input required" size="15" maxLength="20" placeholder="<?=$lang_password_input?>">

			<div class="btn_box">
				<button type="button" class="btn_submit btn_cancel" onclick="javascript:history.go(-1);"><?=$lang_btn_cancel?></button>
				<input type="submit" value="<?=$lang_btn_ok?>" class="btn_submit">
			</div>
		</fieldset>
	</form>
</div>
<!-- } 비밀번호 확인 끝 -->