<?php
	$sub_menu = '700100';
	include_once('./_common.php');

	include_once(G5_ADMIN_PATH.'/admin.head.php');

	$g5['title'] = '결제 관리';

	$sql_common = " from g5_membership_paybill PB
		left join g5_membership_payment A on PB.order_no = A.order_no
		left join g5_member B on PB.uid = B.mb_id
		left join g5_membership_manage M on PB.membership_parent_no = M.no ";
	$sql_search = " where (1) ";

	if ($stx) {
		$sql_search .= " and ( ";
		switch ($sfl) {
			case 'uid':
				$sql_search .= " (PB.uid like '%{$stx}%') ";
				break;
			case 'order_no':
				$sql_search .= " (PB.order_no like '%{$stx}%') ";
				break;
			case 'mb_name':
				$sql_search .= " (B.mb_name like '%{$stx}%') ";
				break;
			case 'mb_email':
				$sql_search .= " (B.mb_email like '%{$stx}%') ";
				break;
			default:
				$sql_search .= " (PB.order_no like '%{$stx}%' or PB.uid like '%{$stx}%') ";
				break;
		}
		$sql_search .= " ) ";
	}

	// 상태 탭(환불완료/환불전) 필터
	$refund_filter = isset($_GET['is_refund']) ? trim((string)$_GET['is_refund']) : '';
	if ($refund_filter === '1') {
		$sql_search .= " and (
			(case when trim(ifnull(PB.status, ''))='' then '1' else trim(PB.status) end)='4'
			or exists (select 1 from credit_history H where H.order_no = PB.order_no and H.tx_type='REFUND')
		) ";
	} else if ($refund_filter === '0') {
		$sql_search .= " and not (
			(case when trim(ifnull(PB.status, ''))='' then '1' else trim(PB.status) end)='4'
			or exists (select 1 from credit_history H where H.order_no = PB.order_no and H.tx_type='REFUND')
		) ";
	}

	$sql = " select count(*) as cnt " . $sql_common . $sql_search;
	$row = sql_fetch($sql);
	$total_count = (int)$row['cnt'];

	$rows = $config['cf_page_rows'];
	$total_page = ceil($total_count / $rows);
	if ($page < 1) { $page = 1; }
	$from_record = ($page - 1) * $rows;

	$sql = "select
			PB.*,
			A.no as payment_no,
			B.mb_name,
			B.mb_email,
			B.mb_4,
			M.title as membership_title,
			M.title_en as membership_title_en
		{$sql_common}
		{$sql_search}
		order by PB.no desc
		limit {$from_record}, {$config['cf_page_rows']}";
	$result = sql_query($sql);
	$rnumber = ($total_count - ($page - 1) * $rows) + 1;
?>

<h2><?php echo $g5['title']; ?></h2>
<style>
.payment-status-text {
	background: transparent;
	border: 0;
	padding: 0;
	font-size: inherit;
	line-height: inherit;
	font-weight: inherit;
}
.payment-status-text.is-refund {
	color: #444;
}
.payment-status-text.is-complete {
	color: #1f4e9d;
}
.payment-status-text.is-fail {
	color: #b91c1c;
}
</style>

<div class="local_ov01 local_ov">
	<?php if ($page > 1) { ?><a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>">처음으로</a><?php } ?>
	<span class="btn_ov01"><span class="ov_txt">전체 내용</span><span class="ov_num"> <?php echo $total_count; ?>건</span></span>
</div>

<!-- 상태 탭 -->
<a href="?is_refund=1" class="btn btn-sm btn-dark text-light">환불완료</a>
<a href="?is_refund=0" class="btn btn-sm btn-warning">환불전</a>
<a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>" class="btn btn-sm btn-info text-light">전체</a>
&ensp;<button type="button" class="btn btn-primary btn-sm" id="exl_download" onclick="$('#fsearch').attr('action','./regular_payment_excel.php').submit();$('#fsearch').attr('action','');"><i class="fa fa-file-excel-o" aria-hidden="true"></i> 엑셀 다운로드</button>

<!-- 검색/필터 값은 엑셀 다운로드에도 동일하게 전달 -->
<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
	<label for="sfl" class="sound_only">검색대상</label>
	<select name="sfl" id="sfl">
		<option value="mb_name"<?php echo get_selected($sfl, "mb_name"); ?>>회사명</option>
		<option value="mb_email"<?php echo get_selected($sfl, "mb_email"); ?>>이메일</option>
		<option value="uid"<?php echo get_selected($sfl, "uid"); ?>>아이디</option>
		<option value="order_no"<?php echo get_selected($sfl, "order_no"); ?>>주문번호</option>
	</select>
	<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
	<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
	<?php if ($refund_filter !== '') { ?><input type="hidden" name="is_refund" value="<?php echo $refund_filter; ?>"><?php } ?>
	<input type="submit" class="btn_submit" value="검색">
</form>

<div class="tbl_head01 tbl_wrap">
	<table>
		<caption><?php echo $g5['title']; ?> 목록</caption>
		<thead>
		<tr>
			<th scope="col">번호</th>
			<th scope="col">회사명</th>
			<th scope="col">사업자등록번호</th>
			<th scope="col">이메일주소</th>
			<th scope="col">결제시간</th>
			<th scope="col">주문번호</th>
				<th scope="col">결제수단</th>
				<th scope="col">금액</th>
				<th scope="col">이용상품</th>
				<th scope="col">상태</th>
			</tr>
		</thead>
		<tbody>
		<?php for ($i=0; $row=sql_fetch_array($result); $i++) {
			$company_name_display = trim((string)$row['mb_name']);
			if ($company_name_display === '') $company_name_display = trim((string)$row['uid']);
			if ($company_name_display === '') $company_name_display = '-';

			$biz_no_raw = trim((string)$row['mb_4']);
			$biz_no_digits = preg_replace('/[^0-9]/', '', $biz_no_raw);
			$biz_no_display = $biz_no_raw;
			if (preg_match('/^[0-9]{10}$/', $biz_no_digits)) {
				$biz_no_display = substr($biz_no_digits, 0, 3) . '-' . substr($biz_no_digits, 3, 2) . '-' . substr($biz_no_digits, 5, 5);
			}
			if ($biz_no_display === '') $biz_no_display = '-';

			$email_display = trim((string)$row['mb_email']);
			if ($email_display === '') $email_display = '-';

			$pay_method = "신용카드";
			if ($row['pay_method'] === "DirectBank") $pay_method = "실시간계좌이체";
			else if ($row['pay_method'] === "VBank") $pay_method = "가상계좌";
			else if ($row['pay_method'] === "None" || !$row['pay_method']) $pay_method = "무료";

			$price_value = (int)$row['price'];
			$price_display = $price_value > 0 ? number_format($price_value, 0) . '원' : '-';

			$product_title_display = trim((string)$row['membership_title']);
			if ($product_title_display === '') $product_title_display = trim((string)$row['order_goods']);
			if ($product_title_display === '') $product_title_display = '-';

			// 상태는 paybill.status를 우선 사용하고, 기존 데이터는 REFUND 이력으로 보정
			$refundInfo = sql_fetch("select id from credit_history where order_no='{$row['order_no']}' and tx_type='REFUND' order by id desc limit 1");
			$is_refunded = (is_array($refundInfo) && !empty($refundInfo['id'])) ? 1 : 0;
			$is_credit_goods = (isset($row['membership_title_en']) && strpos((string)$row['membership_title_en'], 'CREDIT_') === 0) ? 1 : 0;

			$refund_step = trim((string)$row['status']);
			if ($refund_step === '') $refund_step = '1';
			if ($is_refunded) $refund_step = '4';

			$status_text = '결제완료';
			$status_css = 'is-complete';
			if ($refund_step === '2') {
				$status_text = '환불요청접수';
				$status_css = 'is-refund';
			} else if ($refund_step === '3') {
				$status_text = '환불처리중';
				$status_css = 'is-refund';
			} else if ($refund_step === '4') {
				$status_text = '환불완료';
				$status_css = 'is-refund';
			} else if ($refund_step === '5') {
				$status_text = '환불반려/실패';
				$status_css = 'is-fail';
			}
			?>
		<tr>
			<td class="td_id"><?php echo $rnumber = $rnumber - 1; ?></td>
			<td class="td_center"><?php echo $company_name_display; ?></td>
			<td class="td_center"><?php echo $biz_no_display; ?></td>
			<td class="td_center"><?php echo $email_display; ?></td>
			<td class="td_center"><?php echo $row['regdate']; ?></td>
			<td class="td_center"><?php echo $row['order_no']; ?></td>
				<td class="td_center"><?php echo $pay_method; ?></td>
				<td class="td_center"><?php echo $price_display; ?></td>
				<td class="td_center"><?php echo $product_title_display; ?></td>
				<td class="td_center">
					<?php // 2단계(환불요청접수)만 관리자 실행 버튼 노출 ?>
					<?php if ($refund_step === '2') { ?>
					<a class="btn btn-sm btn-danger text-light pay_cancel" oid="<?php echo $row['order_no']; ?>" is_credit="<?php echo $is_credit_goods; ?>">환불요청접수</a>
					<?php } else { ?>
					<span class="payment-status-text <?php echo $status_css; ?>"><?php echo $status_text; ?></span>
					<?php } ?>
				</td>
			</tr>
			<?php
			}
			if ($i == 0) {
				echo '<tr><td colspan="10" class="empty_table">자료가 한건도 없습니다.</td></tr>';
			}
			?>
		</tbody>
	</table>
</div>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>

<script>
$(".pay_cancel").click(function(){
	var oid = $(this).attr("oid");
	var isCredit = $(this).attr("is_credit");
	if(!oid) return;

	var msg = (isCredit == "1")
		? "환불 요청 건을 처리하시겠습니까?\n처리 시 실제 PG 환불이 요청됩니다."
		: "환불 요청 건을 처리하시겠습니까?";
	if(!confirm(msg)) return;

	// 관리자 환불(결제취소) 처리 후 목록 새로고침
	$.post("./admin_ajax.php", {
		type: "pay_cancel",
		oid: oid
	}).done(function(jqXHR) {
		jqXHR = String(jqXHR || "").split("|");
		if (jqXHR[0] == "1000") {
			alert(jqXHR[1] ? jqXHR[1] : "환불 처리 되었습니다.");
			location.reload();
		} else {
			alert(jqXHR[1] ? jqXHR[1] : "요청 처리 중 오류가 발생했습니다.");
		}
	});
});
</script>

<?php
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>
