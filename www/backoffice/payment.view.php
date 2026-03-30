<?php
$sub_menu = "700200";
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'w');

include_once('./admin.head.php');


if (isset($no)) {
    $sql = "select * from g5_membership_payment where no = '{$no}'";
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
        <td>
			<input type="text" readonly class="frm_input" value="<?=$data['uid']?>" />
        </td>
        <th scope="row"><label for="shorts_code">결제방법</label></th>
        <td>
								<?
									
									$payMethod = "신용카드";
									if($data['pay_method'] == "Card")
										$payMethod = "신용카드";
									else if($data['pay_method'] == "Card")
										$payMethod = "신용카드";
									else if($data['pay_method'] == "DirectBank")
										$payMethod = "실시간계좌이체";
									else if($data['pay_method'] == "VBank")
										$payMethod = "가상계좌";
									else if($data['pay_method'] == "None")
										$payMethod = "무료";

								?>
			<input type="text" readonly class="frm_input" value="<?=$payMethod?>" />
		</td>
    </tr>
    <tr>
        <th scope="row"><label for="shorts_code">주문번호</label></th>
        <td>
			<input type="text" readonly class="frm_input" value="<?=$data['order_no']?>" />
        </td>
        <th scope="row"><label for="shorts_code">주문상품</label></th>
        <td>
			<input type="text" readonly class="frm_input" value="<?=$data['order_goods']?>" />
		</td>
    </tr>
    <tr>
        <th scope="row"><label for="shorts_code">주문금액</label></th>
        <td>
			<input type="text" readonly class="frm_input" value="<?=number_format($data['price'],0)?>" />
        </td>
        <th scope="row"><label for="shorts_code">결제상태</label></th>
        <td>
								<?
									$status = "미승인";
									if($data['complete_yn'] == "Y")
										$status = "승인완료 (".$data['complete_date'].")";
								?>
			<input type="text" readonly class="frm_input" value="<?=$status?>" />
		</td>
    </tr>
    <tr>
        <th scope="row"><label for="shorts_code">결제승인일시</label></th>
        <td>
			<input type="text" readonly class="frm_input" value="<?=$data['app_date']?> <?=$data['appl_time']?>" />
        </td>
        <th scope="row"><label for="shorts_code">구매자</label></th>
        <td>
			<input type="text" readonly class="frm_input" value="<?=$data['buyer_name']?>" />
		</td>
    </tr>
    <tr>
        <th scope="row"><label for="shorts_code">구매자 연락처</label></th>
        <td>
			<input type="text" readonly class="frm_input" value="<?=$data['buyer_tel']?>" />
        </td>
        <th scope="row"><label for="shorts_code">구매자 이메일</label></th>
        <td>
			<input type="text" readonly class="frm_input" value="<?=$data['buyer_email']?>" />
		</td>
    </tr>
    <tr>
        <th scope="row"><label for="shorts_code">신용카드 승인번호</label></th>
        <td>
			<input type="text" readonly class="frm_input" value="<?=$data['appl_num']?>" />
        </td>
        <th scope="row"><label for="shorts_code">신용카드번호</label></th>
        <td>
			<input type="text" readonly class="frm_input" value="<?=$data['card_num']?>" />
		</td>
    </tr>
    <tr>
        <th scope="row"><label for="shorts_code">계좌주명</label></th>
        <td>
			<input type="text" readonly class="frm_input" value="<?=$data['acct_name']?>" />
        </td>
        <th scope="row"><label for="shorts_code">가상계좌번호</label></th>
        <td>
			<input type="text" readonly class="frm_input" value="<?=$data['vact_num']?>" />
		</td>
    </tr>
    <tr>
        <th scope="row"><label for="shorts_code">입금은행코드</label></th>
        <td>
			<input type="text" readonly class="frm_input" value="<?=$data['vact_bankcode']?>" />
        </td>
        <th scope="row"><label for="shorts_code">입금은행명</label></th>
        <td>
			<input type="text" readonly class="frm_input" value="<?=$data['vactbankname']?>" />
		</td>
    </tr>
    <tr>
        <th scope="row"><label for="shorts_code">예금주명</label></th>
        <td>
			<input type="text" readonly class="frm_input" value="<?=$data['vact_name']?>" />
        </td>
        <th scope="row"><label for="shorts_code">송금자명</label></th>
        <td>
			<input type="text" readonly class="frm_input" value="<?=$data['vact_inputname']?>" />
		</td>
    </tr>
    <tr>
        <th scope="row"><label for="shorts_code">입금기한</label></th>
        <td>
			<input type="text" readonly class="frm_input" value="<?=$data['vact_date']?> <?=$data['hpp_num']?>" />
        </td>
        <th scope="row"><label for="shorts_code"></label></th>
        <td>

		</td>
    </tr>
    </tbody>
    </table>
</div>
<div class="btn_right">
    <a href="./payment.list.php?<?php echo $qstr ?>" class="btn_admin  btn_02">목록</a>
    <!--input type="submit" value="확인" class="btn_submit btn_admin " accesskey='s'-->
</div>
</form>

<?
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>