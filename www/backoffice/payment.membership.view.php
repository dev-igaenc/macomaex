<?php
$sub_menu = "700300";
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'w');

include_once('./admin.head.php');

$g5['title'] = '맴버쉽 결제 관리';


if (isset($no)) {
    $sql = "select * from g5_membership_status_history where no = '{$no}'";
    $data = sql_fetch($sql);
}else{
	ALERT("정상적으로 이용하세요.","/");
}
?>
<?


?>
<form id="frm" name="frm" method="post" enctype="multipart/form-data">

<div class="tbl_frm01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?></caption>
    <colgroup>
        <col class="grid_4">
        <col>
        <col class="grid_4">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="shorts_code">아이디</label></th>
        <td colspan=3>
			<input type="text" readonly class="frm_input" value="<?=$data['uid']?>" />
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="shorts_code">주문번호</label></th>
        <td>
			<input type="text" readonly class="frm_input" value="<?=$data['order_no']?>" />
        </td>
        <th scope="row"><label for="shorts_code">주문상품</label></th>
        <td>
			<?$membershipInfo	= getMembership($data['membership_parent_no']);?>
			<input type="text" readonly class="frm_input" value="<?=$membershipInfo['title']?>" />
		</td>
    </tr>
    <tr>
        <th scope="row"><label for="shorts_code">시작일자</label></th>
        <td>
			<input type="text" readonly class="frm_input" value="<?=$data['date_begin']?>" />
        </td>
        <th scope="row"><label for="shorts_code">종료일자</label></th>
        <td>
			<input type="text" readonly class="frm_input" value="<?=$data['date_end']?>" />
		</td>
    </tr>
    <tr>
        <th scope="row"><label for="shorts_code">열람 가능 횟수</label></th>
        <td>
			<input type="text" readonly class="frm_input" value="<?=$data['open_able_cnt']?>" />
        </td>
        <th scope="row"><label for="shorts_code">회원사 정보열람 횟수</label></th>
        <td>
			<input type="text" readonly class="frm_input" value="<?=$data['open_cnt']?>" />
		</td>
    </tr>
    <tr>
        <th scope="row"><label for="shorts_code">매칭 가능 횟수</label></th>
        <td>
			<input type="text" readonly class="frm_input" value="<?=$data['matching_able_cnt']?>" />
        </td>
        <th scope="row"><label for="shorts_code">매칭요청 횟수</label></th>
        <td>
			<input type="text" readonly class="frm_input" value="<?=$data['matching_cnt']?>" />
		</td>
    </tr>
    <tr>
        <th scope="row"><label for="shorts_code">등록일</label></th>
        <td colspan="3">
			<input type="text" readonly class="frm_input" value="<?=$data['regdate']?>" />
        </td>
    </tr>
  
    </tbody>
    </table>
</div>
<div class="btn_right">
    <a href="./payment.membership.list.php?<?php echo $qstr ?>" class="btn_admin  btn_02">목록</a>
    <!--input type="submit" value="확인" class="btn_submit btn_admin " accesskey='s'-->
</div>
</form>

<?
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>