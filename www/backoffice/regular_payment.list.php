<?
	$sub_menu = '700100';
	include_once('./_common.php');

	include_once (G5_ADMIN_PATH.'/admin.head.php');

$g5['title'] = '결제 관리';


/*
select
	*
from(
	select
		*
	from g5_membership_status
	where (uid, no) in (
		select uid, max(no) as no
		from g5_membership_status group by uid
	)
	order by no desc
) t
group by t.uid ORDER BY date_end desc
*/

$sql_common = " from g5_membership_status A left join g5_member B on A.uid=B.mb_id ";
//상태(S:유지, C:취소, E:종료)status='S'
$sql_search = " where (1)";

$order = " ORDER BY no desc";

if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {

		 case 'status' :
            $sql_search .= " ({$sfl} = '{$stx}') ";
            break;

        default :
            $sql_search .= " ({$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common . $sql_search . $order;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = "select * $sql_common $sql_search order by date_end desc  limit $from_record, {$config['cf_page_rows']}  ";
$result = sql_query($sql);

$rnumber=($total_count-($page-1)*$rows)+1;

?>
<style>
.throu {text-decoration:line-through; text-decoration-color: red;}
</style>
 <h2><?php echo $g5['title']; ?></h2>

<div class="local_ov01 local_ov">
    <?php if ($page > 1) {?><a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>">처음으로</a><?php } ?>
    <span class="btn_ov01"><span class="ov_txt">전체 내용</span><span class="ov_num"> <?php echo $total_count; ?>건</span></span>
	&ensp;<button type="button" class="btn btn-primary btn-sm pull-right" id="exl_download" onclick="$('#fsearch').attr('action','./regular_payment_excel.php').submit();$('#fsearch').attr('action','');"><i class="fa fa-file-excel-o" aria-hidden="true"></i> 엑셀 다운로드</button>
</div>
<!--a href="?sfl=status&stx=S" class="btn btn-sm btn-primary" title="정상유지 맴버쉽">유지</a>
<a href="?sfl=status&stx=E" class="btn btn-sm btn-secondary text-light" title="기간이 지난 맴버쉽">만료종료</a>
<a href="?sfl=status&stx=C" class="btn btn-sm btn-danger" title="7일이내 취소 환불된 맴버쉽">취소환불</a>
<a href="?sfl=status&stx=T" class="btn btn-sm btn-warning" title="7일이후 사용건수 있는경우 만료 후 정지될 맴버쉽">만료정지</a-->
<a href="?sfl=status&stx=S" class="btn btn-sm btn-primary" title="정상유지 맴버쉽">결제완료</a>
<a href="?sfl=status&stx=E" class="btn btn-sm btn-secondary text-light" title="기간이 지난 맴버쉽">이용종료</a>
<a href="?sfl=status&stx=C" class="btn btn-sm btn-danger" title="취소/환불된 결제">취소완료</a>
<a href="?sfl=status&stx=T" class="btn btn-sm btn-warning" title="7일이후 사용건수 있는경우 만료 후 정지될 맴버쉽">취소예정</a>
  <script>
  $( function() {
    $( "a" ).tooltip();
  } );
  </script>
<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">

<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
    <option value="mb_name"<?php echo get_selected($sfl, "mb_name"); ?>>회사명</option>
	<option value="mb_email"<?php echo get_selected($sfl, "mb_email"); ?>>이메일</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx"  class=" frm_input">
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
		<th scope="col">결제일</th>
		<th scope="col">금액</th>
		<th scope="col">이용시작일</th>
		<th scope="col">이용종료일</th>
		<th scope="col">전문가컨설팅	</th>
		<th scope="col">상태	</th>
		<th scope="col">이용상품	</th>
		<th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php for ($i=0; $row=sql_fetch_array($result); $i++) {
			$membershipInfo	= getMembership($row['membership_parent_no']);
			$paymentInfo			= getPaymentInfo($row['order_no']);
			$is_credit_goods = (isset($membershipInfo['title_en']) && strpos($membershipInfo['title_en'], 'CREDIT_') === 0) ? 1 : 0;
			$refundInfo = sql_fetch("select id from g5_matching_credit_history where order_no='{$row['order_no']}' and tx_type='REFUND' order by id desc limit 1");
			$is_refunded = $refundInfo['id'] ? 1 : 0;
			$status_text = $ms_status_text[$row['status']];
			$status_btn_class = "btn-primary";
			if($row['status']=="C") $status_btn_class = "btn-danger";
			else if($row['status']=="E") $status_btn_class = "btn-secondary";
			else if($row['status']=="T") $status_btn_class = "btn-warning";

			if($is_refunded){
				$status_text = "환불완료";
				$status_btn_class = "btn-dark";
			}
			$timestamp = strtotime("+1 days", strtotime($row['date_end']." 00:00:00"));
			$duedate=date("Y-m-d", $timestamp);
			$pay_cnt=sql_fetch("select count(*) as cnt from  g5_membership_status_history where uid='{$row['uid']}'");

			if($pay_cnt['cnt']==1 && $status_text=="결제완료") $status_text="첫결제";
			
	?>
    <tr>
        <td class="td_id"><?=$rnumber=$rnumber-1?></td>
        <td class="td_center" title="<?php echo $row['uid']; ?>"><?php echo $row['mb_name']; ?></td>
		<td class="td_center"><?php echo $row['mb_4']; ?></td>
		<td class="td_center"><?php echo $row['mb_email']; ?></td>
		<td class="td_center"><?=$duedate?></td>
		<td class="td_center">
			<?=number_format($paymentInfo['price'],0)?>원
		</td>
		<td class="td_center"><?=$row['date_begin']?></td>
		<td class="td_center"><?=$row['date_end']?></td>
		<td class="td_center">
			<?if($row['expert_consulting_cnt']>0 and ($row['status']=="S" or  $row['status']=="T")){?>
				<?if(!$row['expert_consulting_able_cnt']){?>
				<button type="button" class="btn btn-sm btn-info euse" idx="<?=$row['no']?>">사용</button>
				<?}else{?>
				<button type="button" class="btn btn-sm btn-secondary euse" idx="<?=$row['no']?>">미사용</button>
				<?}?>
			<?}else{?>
				-
			<?}?>
		</td>
		<td class="td_center td_mng" title="열람 <?=$row['open_able_cnt']?> / <?=$row['open_cnt']?><br>매칭 <?=$row['matching_able_cnt']?> / <?=$row['matching_cnt']?>">			<!--//상태(S:유지, C:취소, E:종료)-->
			<span class="btn btn-sm <?=$status_btn_class?>">
				<?=$status_text?>
			</span>
			<?if($row['coupon']){?><p  class="btn btn-sm bg-dark text-light">쿠폰사용</p><?}?>
		</td>
		<td class="td_center"><?=$membershipInfo['title']?></td>
        <td class="td_mng td_mng_l">
			<?if($is_refunded){?>
			<span class="btn btn-sm btn-secondary">환불완료</span><br>
			<?}else if($row['status']=="S" or $row['status']=="T"){?>
			<a class="btn btn-danger text-light pay_cancel" oid="<?=$row['order_no']?>" is_credit="<?=$is_credit_goods?>">결제취소</a><br>
			<?}?>
            <a href="./regular_payment.view.php?no=<?=$row['no']?>&amp;<?=$qstr?>" class="btn btn_03">확인</a>
            <a href="./regular_payment.list_update.php?w=d&amp;no=<?php echo $row['no']; ?>&amp;<?=$qstr?>" onclick="return delete_confirm(this);" class="btn btn_02"><span class="sound_only"><?php echo htmlspecialchars2($row['title']); ?> </span>삭제</a>
        </td>
    </tr>
    <?php
    }
    if ($i == 0) {
        echo '<tr><td colspan="8" class="empty_table">자료가 한건도 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>
</div>
<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>
<script>
$(".pay_cancel").click(function(){
	oid=$(this).attr("oid");
	isCredit=$(this).attr("is_credit");
	if(oid){
		msg = (isCredit=="1")
			? "결제 취소(환불) 시 충전 크레딧이 회수됩니다.\n정말 결제 취소하시겠습니까?"
			: "결제 취소시 멤버십도 종료됩니다.\n정말 결제 취소하시겠습니까?";
		if(confirm(msg)){
				$.post( "./admin_ajax.php", {
					type: 'pay_cancel',
					oid:oid
				})
				.done(function(jqXHR) {
						jqXHR=jqXHR.split("|");
						if(jqXHR[0]==1000){
							//완료시
							if(jqXHR[1]){
								alert(jqXHR[1]);
								location.reload();
							}else{
								alert("결제 취소 처리 되었습니다.");
								location.reload();
							}
						}else{
							//오류시
							alert(jqXHR[1]);
						}
				});
		} ;
	}
}) ;
$(".euse").click(function(){
	$this=$(this);
	chk=$(this).text();
	idx=$(this).attr("idx");

	$.post( "./admin_ajax.php",{
		type:'member_euse',
		chk:chk,
		idx:idx
	})
	.done(function(jqXHR) {
		if(jqXHR==500){
			alert("요청에 문제가 있습니다.");
		}else{
			if(chk=="미사용") $this.text("사용").removeClass("btn-secondary").addClass("btn-info");
			else $this.text("미사용").removeClass("btn-info").addClass("btn-secondary");
		}
	});


})
</script>
<?
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
