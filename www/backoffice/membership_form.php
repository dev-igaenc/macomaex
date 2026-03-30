<?php
$sub_menu = "700400";
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'w');

if ($is_admin != 'super')
    alert_close('최고관리자만 접근 가능합니다.');

$g5['title'] = '맴버쉽관리';

$w = isset($_GET['w']) ? clean_xss_tags($_GET['w'], 1, 1) : '';
$no = isset($_GET['no']) ? preg_replace('/[^0-9a-zA-Z]/', '', $_GET['no']) : '';

if ($w == 'u')  {
    $sql = " select * from g5_membership_manage where no = '{$no}' ";
    $po = sql_fetch($sql);
}

include_once('./admin.head.php');
?>

<div id="menu_frm" class="new_win">
    <h2><?php echo $g5['title']; ?></h2>

    <form name="fmenuform" id="fmenuform" action="membership_update.php" method="post" class="">
		<input type="hidden" name="no" value="<?php echo $no ?>">
		<input type="hidden" name="w" value="<?php echo $w ?>">
		<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
		<input type="hidden" name="stx" value="<?php echo $stx ?>">
		<input type="hidden" name="sst" value="<?php echo $sst ?>">
		<input type="hidden" name="sod" value="<?php echo $sod ?>">
		<input type="hidden" name="page" value="<?php echo $page ?>">
		<input type="hidden" name="token" value="">
		<div class="tbl_frm01 tbl_wrap">
			<table>
				<tr>
					<th scope="row"><label for="title">멤버십명</label></th>
					<td>
						<input type="text" name="title" value="<?php echo get_text($po['title']) ?>" id="title" class="frm_input" size="80">
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="title_en">멤버십명 (영문)</label></th>
					<td>
						<input type="text" name="title_en" value="<?php echo get_text($po['title_en']) ?>" id="title_en" class="frm_input" size="80">
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="month">기간</label></th>
					<td>
						<input type="text" name="month" value="<?php echo get_text($po['month']) ?>" id="month" class="frm_input small">
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="price_origin">가격</label></th>
					<td>
						<input type="text" name="price_origin" value="<?php echo get_text($po['price_origin']) ?>" id="price_origin" class="frm_input small">
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="price_sale">세일가격</label></th>
					<td>
						<input type="text" name="price_sale" value="<?php echo get_text($po['price_sale']) ?>" id="price_sale" class="frm_input small">
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="sale">세일(%)</label></th>
					<td>
						<input type="text" name="sale" value="<?php echo get_text($po['sale']) ?>" id="sale" class="frm_input small">
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="vat">VAT</label></th>
					<td>
						<input type="text" name="vat" value="<?php echo get_text($po['vat']) ?>" id="vat" class="frm_input small">
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="pay_type">결제타입</label></th>
					<td>
						<input type="text" name="pay_type" value="<?php echo get_text($po['pay_type']) ?>" id="pay_type" class="frm_input small">
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="desc">상세</label></th>
					<td>
						<textarea name="desc" id="desc" class="frm_textarea" style="width:630px"><?php echo get_text($po['desc']) ?></textarea>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="open_cnt">회원사 정보열람 횟수</label></th>
					<td>
						<input type="text" name="open_cnt" value="<?php echo get_text($po['open_cnt']) ?>" id="open_cnt" class="frm_input small">
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="matching_cnt">매칭요청 횟수</label></th>
					<td>
						<input type="text" name="matching_cnt" value="<?php echo get_text($po['matching_cnt']) ?>" id="matching_cnt" class="frm_input small">
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="selected_cnt">셀렉티드 횟수</label></th>
					<td>
						<input type="text" name="selected_cnt" value="<?php echo get_text($po['selected_cnt']) ?>" id="selected_cnt" class="frm_input small">
					</td>
				</tr>
				<?if($w!=""){?>
				<tr>
					<th scope="row"><label for="regdate">등록일자</label></th>
					<td>
						<input type="text" name="regdate" value="<?php echo get_text($po['regdate']) ?>" id="regdate" class="frm_input small" >
					</td>
				</tr>
				<?}?>
			</table>
		</div>
		<div class=" pull-right">
			<a href="./membership_list.php?<?php echo $qstr ?>" class="btn_02 btn">목록</a>
			<input type="submit" value="등록" class="btn btn-info" accesskey="s">
		</div>
    </form>

</div>


<?php
include_once('./admin.tail.php');