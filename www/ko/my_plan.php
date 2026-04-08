<?
include_once("./_common.php");

$board['bo_10']="6"; // 1뎁스
$board['bo_9']="5"; // 2뎁스
//$board['bo_8']="1"; // 3뎁스
//$board['bo_7']="1"; // 4뎁스
// $board['bo_5'] = ""; // 1뎁스 제목
// $board['bo_6'] = ""; // 1뎁스 설명
// $board['bo_subject'] = "";  // 2뎁스 제목
//$board['bo_4'] = $config['cf_title']."의 혜택을 확인하세요.";  // 2뎁스 설명

//로그인 체크
if(!$member['mb_id']) ALERT("로그인 후 이용하세요.","/bbs/login.php");

include_once(G5_THEME_PATH.'/head.php');
$membership=get_membership();
foreach($membership as $value){
	$mship[$value['title_en']]=$value;
}

//맴버쉽 정보
$today = date("Y-m-d");
$msinfo_row = getUserMembership();
//print_r($msinfo_row[0]);
$chk=$msinfo_row[0];
//if($chk['uid']) print_r($chk);

//맴버쉽이 없는 경우 무료로
if(empty($msinfo_row[0])) $msinfo['membership_parent_no']=$membership[0]['no'];
else $msinfo=$msinfo_row[0];
$membership_info = sql_fetch("select * from g5_membership_manage where no='{$msinfo['membership_parent_no']}'" );
$is_credit_membership = 0;
if(($membership_info['title_en'] && strpos($membership_info['title_en'], 'CREDIT_') === 0) || ((int)$membership_info['matching_cnt'] > 0 && (int)$membership_info['month'] === 0)){
	$is_credit_membership = 1;
}
$membership_cancel_token = uniqid('', true);
set_session('ss_membership_cancel_token', $membership_cancel_token);
$remain_credit = (int)$msinfo['matching_able_cnt'];
$show_credit_cancel_btn = ($is_credit_membership && $remain_credit > 0);

if($msinfo['coupon']) $coupon=getCouponInfo($msinfo['coupon']);
?>


<div id="my_plan">

    <div class="fullBox my_plan mgb10">
		<input type="hidden" name="ms_id"  id="ms_id" value="<?=$msinfo['no']?>">
		<input type="hidden" name="is_credit_membership" id="is_credit_membership" value="<?=$is_credit_membership?>">
		<input type="hidden" name="membership_cancel_token" id="membership_cancel_token" value="<?=$membership_cancel_token?>">


		<?if($membership_info['title_en']!="FREE" && $msinfo){?>
        <!-- 무료가 아닌경우 -->
        <div class="inner">
            <div class="textArea">
                <div class="vat">VAT <?=$mship[$membership_info['title_en']]['vat']?></div><?if($coupon['cp_trunc']){?><span class="coupon_use vat"><?=$coupon['cp_trunc']?>개월 무료</span><?}?>
				<?if($is_credit_membership){?><div class="vat">잔여 크레딧 <strong><?=$remain_credit?></strong></div><?}?>
                <h4 class="title">
					<?if($is_credit_membership){?>
						크레딧 잔액 <span>CREDIT</span> <br>
						<p class="price_sale"><?=number_format($remain_credit)?></p>
					<?}else{?>
						<?=$mship[$membership_info['title_en']]['title']?><span><?=$mship[$membership_info['title_en']]['title_en']?></span> <br>
							<?if($mship[$membership_info['title_en']]['price_origin']>-1){?><p class="price_origin"><?=(is_numeric($mship[$membership_info['title_en']]['price_origin']) ? number_format((float)$mship[$membership_info['title_en']]['price_origin']) : $mship[$membership_info['title_en']]['price_origin']);?></p><?}?>
							<?if(isset($mship[$membership_info['title_en']]['sale']) && is_numeric($mship[$membership_info['title_en']]['sale'])){?><p class="sale"><?=number_format((float)$mship[$membership_info['title_en']]['sale']);?>%</p><?}?>
							<?if($mship[$membership_info['title_en']]['price_sale']){?><p class="price_sale"><?=(is_numeric($mship[$membership_info['title_en']]['price_sale']) ? number_format((float)$mship[$membership_info['title_en']]['price_sale']) : $mship[$membership_info['title_en']]['price_sale']);?></p><?}?>
					<?}?>
                </h4>
                <ul class="venefit">
				<?if($is_credit_membership){?>
					<li>
						<div class="icon"><img src="/theme/prix/img/sub/my_plan_icon_list.png" alt=""></div>
						<div class="text">일반결제 크레딧 서비스</div>
					</li>
					<li>
						<div class="icon"><img src="/theme/prix/img/sub/my_plan_icon_clock.png" alt=""></div>
						<div class="text">자동 만료 없음 (잔여 크레딧 기준)</div>
					</li>
				<?}else{?>
					<?$desc=explode("\n",$mship[$membership_info['title_en']]['desc']);
						foreach($desc as $value2){
							$value2=str_replace("[","",$value2);
							$value2=str_replace("]","",$value2);
							unset($imgsrc);
							$imgsrc="/theme/prix/img/sub/my_plan_icon_interested.png";
							if(strpos($value2,"거래처 정보")) $imgsrc="/theme/prix/img/sub/my_plan_icon_list.png";
							if(strpos($value2,"개 매칭")) $imgsrc="/theme/prix/img/sub/my_plan_icon_list.png";
							if(strpos($value2,"정보 오픈")) $imgsrc="/theme/prix/img/sub/my_plan_icon_list.png";
							if(strpos($value2,"비상장")) $imgsrc="/theme/prix/img/sub/my_plan_icon_value.png";
							if(strpos($value2,"더보기")) $imgsrc="/theme/prix/img/sub/my_plan_icon_limit.png";
							if(strpos($value2,"0분")) $imgsrc="/theme/prix/img/sub/my_plan_icon_clock.png";
							if(strpos($value2,"기업정보")) $imgsrc="/theme/prix/img/sub/my_plan_icon_info.png";
							if(strpos($value2,"거래처 수")) $imgsrc="/theme/prix/img/sub/my_plan_icon_partners.png";
							if(strpos($value2,"거래처 등록")) $imgsrc="/theme/prix/img/sub/my_plan_icon_interested.png";
					?>
						<li>
							<div class="icon"><img src="<?=$imgsrc?>" alt=""></div>
							<div class="text"><?=$value2?></div>
						</li>
					<?}?>
				<?}?>
                </ul>
				<?if($msinfo['status']=="T" && !$is_credit_membership){?>
				 <a href="javascript:jAlert('<?=$msinfo['date_end']?>일 이후 구독 취소 됩니다.','알림');" id="" class="unSubscribe"><span>만료일 후 취소</span></a>
				<?}else if($is_credit_membership && !$show_credit_cancel_btn){?>
				<!-- 잔여 크레딧이 0이면 취소 버튼 노출하지 않음 -->
				<?}else{?>
                <a href="javascript:;" id="membership_cancel" class="unSubscribe"><span><?if($is_credit_membership){?>결제 취소<?}else{?>구독 취소<?}?></span></a>
				<?}?>
            </div>
            <div class="thumbArea"><img src="/theme/prix/img/sub/my_plan_pc.png" alt=""></div>
        </div>
		<?}?>
		<?if($membership_info['title_en']=="FREE"){?>
        <!-- 무료 -->
        <div class="inner">
            <div class="textArea">
                <div class="vat">VAT <?=$mship['FREE']['vat']?></div>
                <h4 class="title">
                    <?=$mship['FREE']['title']?><span><?=$mship['FREE']['title_en']?></span> <br>
					<?if($mship['FREE']['price_origin']>-1){?><p class="price_origin"><?=number_format($mship['FREE']['price_origin']);?></p><?}?>
					<?if($mship['FREE']['sale']){?><p class="sale"><?=number_format($mship['FREE']['sale']);?>%</p><?}?>
					<?if($mship['FREE']['price_sale']){?><p class="price_sale"><?=number_format($mship['FREE']['price_sale']);?></p><?}?>
                </h4>
                <ul class="venefit">
				<?$desc=explode("\n",$mship['FREE']['desc']);
					foreach($desc as $value2){
						$value2=str_replace("[","",$value2);
						$value2=str_replace("]","",$value2);
						unset($imgsrc);
						$imgsrc="/theme/prix/img/sub/my_plan_icon_interested.png";
						if(strpos($value2,"거래처 정보")) $imgsrc="/theme/prix/img/sub/my_plan_icon_list.png";
						if(strpos($value2,"개 매칭")) $imgsrc="/theme/prix/img/sub/my_plan_icon_list.png";
						if(strpos($value2,"정보 오픈")) $imgsrc="/theme/prix/img/sub/my_plan_icon_list.png";
						if(strpos($value2,"비상장")) $imgsrc="/theme/prix/img/sub/my_plan_icon_value.png";
						if(strpos($value2,"더보기")) $imgsrc="/theme/prix/img/sub/my_plan_icon_limit.png";
						if(strpos($value2,"0분")) $imgsrc="/theme/prix/img/sub/my_plan_icon_clock.png";
						if(strpos($value2,"기업정보")) $imgsrc="/theme/prix/img/sub/my_plan_icon_info.png";
						if(strpos($value2,"거래처 수")) $imgsrc="/theme/prix/img/sub/my_plan_icon_partners.png";
						if(strpos($value2,"거래처 등록")) $imgsrc="/theme/prix/img/sub/my_plan_icon_interested.png";
				?>
                    <li>
                        <div class="icon"><img src="<?=$imgsrc?>" alt=""></div>
                        <div class="text"><?=$value2?></div>
                    </li>
				<?}?>
                    <!--li>
                        <div class="icon"><img src="/theme/prix/img/sub/my_plan_icon_list.png" alt=""></div>
                        <div class="text">4개 거래처 <br>정보 오픈</div>
                    </li>
                    <li>
                        <div class="icon"><img src="/theme/prix/img/sub/my_plan_icon_list.png" alt=""></div>
                        <div class="text">추천 거래처 <br>더보기 제한</div>
                    </li>
                    <li>
                        <div class="icon"><img src="/theme/prix/img/sub/my_plan_icon_info.png" alt=""></div>
                        <div class="text">내 기업 <br>정보</div>
                    </li>
                    <li>
                        <div class="icon"><img src="/theme/prix/img/sub/my_plan_icon_partners.png" alt=""></div>
                        <div class="text">관련 <br>거래처 수</div>
                    </li>
                    <li>
                        <div class="icon"><img src="/theme/prix/img/sub/my_plan_icon_interested.png" alt=""></div>
                        <div class="text">관심 <br>거래처 등록</div>
                    </li-->
                </ul>
            </div>
            <div class="thumbArea"><img src="/theme/prix/img/sub/my_plan_pc.png" alt=""></div>
        </div>
		<?}?>
    </div>
<?
// 결제관리: 원장(g5_matching_credit_history) 단일 기준
$sql = "select
			H.id,
			H.uid,
			H.tx_type,
			H.credit_delta,
			H.credit_after,
			H.order_no,
			H.matching_no,
			H.memo,
			H.created_at as event_date,
			B.tid as bill_tid
		from g5_matching_credit_history H
		left join g5_membership_paybill B on H.order_no=B.order_no
		where
			LOWER(TRIM(H.uid))=LOWER(TRIM('{$member['mb_id']}'))
			".($member['mb_email'] ? " or LOWER(TRIM(H.uid))=LOWER(TRIM('{$member['mb_email']}'))" : "")."
			".($msinfo['uid'] ? " or LOWER(TRIM(H.uid))=LOWER(TRIM('{$msinfo['uid']}'))" : "")."
		order by H.created_at desc, H.id desc";
$result = sql_query($sql, false);
$is_join_query_ok = $result ? true : false;
if(!$result){
	// paybill 조인 실패 시 원장 단독 조회로 폴백 (이력 미노출 방지)
	$sql = "select
				H.id,
				H.uid,
				H.tx_type,
				H.credit_delta,
				H.credit_after,
				H.order_no,
				H.matching_no,
				H.memo,
				H.created_at as event_date,
				'' as bill_tid
			from g5_matching_credit_history H
			where
				LOWER(TRIM(H.uid))=LOWER(TRIM('{$member['mb_id']}'))
				".($member['mb_email'] ? " or LOWER(TRIM(H.uid))=LOWER(TRIM('{$member['mb_email']}'))" : "")."
				".($msinfo['uid'] ? " or LOWER(TRIM(H.uid))=LOWER(TRIM('{$msinfo['uid']}'))" : "")."
			order by H.created_at desc, H.id desc";
	$result = sql_query($sql, false);
}
$history_rows = array();
if($result){
	while($row = sql_fetch_array($result)){
		$history_rows[] = $row;
	}
}
$row_count = count($history_rows);
?>
    <div class="fullBox">
        <div class="inner">
            <div class="payment_history">
                <h4 class="fs35 lh1 c111 fw550 mgb2">결제관리</h4>

                <table>
                    <colgroup>
                        <col width="9%"> <!-- 6 -->
                        <col width="13%"> <!-- 16 -->
                        <col width="13%"> <!-- 26 -->
                        <col width="13%"> <!-- 36 -->
                        <col width="13%"> <!-- 46 -->
                        <col width="13%"> <!-- 56 -->
                        <col width="13%"> <!-- 66 -->
                        <col width="13%"> <!-- 76 -->
                    </colgroup>
                    <thead>
                        <tr>
                            <td>No</td>
                            <td>결제</td>
                            <td>결제일</td>
                            <td>이용시작일</td>
                            <td>이용종료일</td>
                            <td>이용상품</td>
                            <td>상태</td>
                            <td>영수증</td>
                        </tr>
                    </thead>
                    <tbody>
					 <?php for ($i=0; $i<$row_count; $i++) {
						$row = $history_rows[$i];
						$status = "처리완료";
						if($row['tx_type']=="CHARGE") $status = "결제완료";
						else if($row['tx_type']=="REFUND") $status = "결제취소";
						else if($row['tx_type']=="USE") $status = "크레딧사용";
						else if($row['tx_type']=="ADJUST") $status = "관리자조정";
						$amount = (int)$row['credit_delta'];
						$credit_abs = abs($amount);
						$item_name = "-";
						if($row['tx_type']=="CHARGE") $item_name = "[크레딧] {$credit_abs} 크레딧 충전";
						else if($row['tx_type']=="REFUND") $item_name = "[크레딧] {$credit_abs} 크레딧 환불";
						else if($row['tx_type']=="USE") $item_name = "[크레딧] 매칭 요청 {$credit_abs} 크레딧 차감";
						else if($row['tx_type']=="ADJUST") $item_name = "[크레딧] 관리자 조정 {$credit_abs} 크레딧";
						else if($row['tx_type']=="BONUS") $item_name = "[크레딧] 가입 기본 지급 {$credit_abs} 크레딧";
						if($row['memo']) $item_name = $row['memo'];
					 ?>
                        <tr>
                            <td><?=$i+1?></td>
                            <td><?=number_format($amount)?></td>
                            <td><?=substr($row['event_date'],0,10)?></td>
                            <td>-</td>
                            <td>-</td>
                            <td><?=$item_name?></td>
                            <td><?=$status?></td>
                            <td>
								<?if($row['tx_type']=="CHARGE" && $row['bill_tid'] && $row['order_no']){?><a class="receipt" oid="<?=$row['order_no']?>">영수증</a><?}else{?>-<?}?>
                            </td>
                        </tr>
						<?}?>
						<? if ($row_count == 0) {?>
                        <tr>
                            <td colspan="8">이용 내역이 없습니다.</td>
                        </tr>
						<?}?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<script>
$("a.receipt").click(function(){
	oid=$(this).attr("oid");
	$.post( "../get_set.php", {
		type: 'receipt',
		oid:oid
	})
	.done(function(jqXHR) {
			jqXHR=jqXHR.split("|");
			if(jqXHR[0]==1000){
				//완료시
			   var receiptUrl = "https://iniweb.inicis.com/DefaultWebApp/mall/cr/cm/mCmReceipt_head.jsp?noTid=" + jqXHR[1] + "&noMethod=1";
				window.open(receiptUrl, "receipt", "width=430,height=700,scrollbars=yes,toolbar=no,menubar=no");
			}else{
				//오류시
				if(jqXHR[1]=="02") jAlert("검색 결과가 없습니다.","알림");
				else jAlert(jqXHR[1],"알림");
			}
	});

}) ;
$("#membership_cancel").click(function(){
	ms_id=$("#ms_id").val();
	isCredit=$("#is_credit_membership").val();
	if(ms_id){
		confirmText = (isCredit=="1")
			? "결제 취소(환불) 시 구매한 크레딧이 회수됩니다.\n이미 일부 사용한 경우 환불이 제한될 수 있습니다.\n결제 취소를 진행하시겠습니까?"
			: "구독취소는 결제 후 7일 이내\n매칭 요청 서비스를 이용하지 않은 경우, 전액 환불처리 되며 \n7일 이후 혹은 사용 이력이 있으시면\n만료일까지 유지후 구독 취소됩니다. \n구독을 취소하시겠습니까?";
		jCustomConfirm(confirmText,"알림","취소","확인",function(e){
			if(!e){

					$.post( "../get_set.php", {
						type: 'membership_cancel',
						cancel_token: $("#membership_cancel_token").val()
					})
				.done(function(jqXHR) {
						jqXHR=jqXHR.split("|");
						if(jqXHR[0]==1000){
							//완료시
							if(jqXHR[1]){
								jAlert(jqXHR[1],"알림",function(){
									location.reload();
								});
							}else{
								jAlert("구독 취소 처리되었습니다.","알림",function(){
									location.reload();
								});
							}
						}else{
							//오류시
							if(jqXHR[1]=="02") jAlert("검색 결과가 없습니다.","알림");
							else jAlert(jqXHR[1],"알림");
						}
				});

			}
		}) ;
			//= function(message, title, okButton, cancelButton, callback) {	
	}else{
		jAlert("구독취소는 맴버쉽 이용자만 가능합니다.");
	}
})
</script>

<?
include_once(G5_THEME_PATH.'/tail.php');
?>
