<?
include_once("./_common.php");

$board['bo_10']="4"; // 1뎁스
$board['bo_9']="1"; // 2뎁스

include_once(G5_THEME_PATH.'/head.php');
$membership_list=get_membership();
$credit_membership_list = array();
foreach($membership_list as $tmp_membership){
	if(strpos((string)$tmp_membership['title_en'], 'CREDIT_') === 0){
		$credit_membership_list[] = $tmp_membership;
	}
}
if(count($credit_membership_list) > 0){
	$membership_list = $credit_membership_list;
}

/*결제모듈*/
require_once('libs/INIStdPayUtil.php');
$SignatureUtil = new INIStdPayUtil();
$timestamp = $SignatureUtil->getTimestamp();
$merchantKey = _merchantKey; // 이니라이트키
$mid=_mid;
$dateVal = date("YmdHis");
if(_test_pay=='Y') $orderid= "DemoTest_". $timestamp ;
else $orderid= "M_". $timestamp ;
$hashData = hash("sha256",(string)$mid.(string)$orderid.(string)$dateVal.(string)$merchantKey);
$msinfo_row = getUserMembership();
$my_membership = (is_array($msinfo_row) && isset($msinfo_row[0]) && is_array($msinfo_row[0])) ? $msinfo_row[0] : array();
/*결제모듈*/
?>
<style>
	.planList > li:nth-of-type(1) .plan_body .plan_payment{opacity: 1; pointer-events: auto;}
	.planList > li .plan_body .plan_payment.is_disabled{background-color:#9ea4aa; pointer-events:none;}
</style>
<div id="plan">
	<input type="hidden" name="coupon_chk" id="coupon_chk" value="false">
	<input type="hidden" name="coupon_pop" id="coupon_pop" value="false">
    <ul class="planList">
		<li class="bxShadow2">
			<div class="plan_head">
				<p class="kr">30 크레딧</p>
				<p class="en">CREDIT_30</p>
			</div>
			<div class="plan_body new">
				<div class="plan_content">
					<div class="plan_money">
						<div class="price">무료</div>
						<div class="credit_note">회원가입 시 기본 제공</div>
					</div>
					<ul class="price_info">
						<li>VAT <span>없음</span></li>
						<li>1회 지급</li>
					</ul>
					<?if(!$member['mb_id']){?>
						<a href="/bbs/register.php" class="plan_payment">회원가입</a>
					<?}else{?>
						<span class="plan_payment is_disabled">가입 시 제공 완료</span>
					<?}?>
				</div>
				<div class="plan_desc">
					<ul>
						<li>신규 회원가입 시 30 크레딧 지급</li>
						<li>기본 지급 크레딧은 환불 불가</li>
						<li>매칭 요청 시 크레딧 차감</li>
					</ul>
				</div>
			</div>
		</li>
	<?foreach($membership_list as $value){
		 $value['pay_type']=str_replace("[","<span>",$value['pay_type']);
		 $value['pay_type']=str_replace("]","</span>",$value['pay_type']);
		 $value['desc']="<li>".str_replace("\n","</li><li>",$value['desc'])."</li>";
		 $value['desc']=str_replace("<li>[","<li class='under'><span>",$value['desc']);
		 $value['desc']=str_replace("]","</span>",$value['desc']);
		 $value['desc']=str_replace("]\n</li>","</span></li>",$value['desc']);
		 $credit_cnt = isset($value['matching_cnt']) ? (int)$value['matching_cnt'] : 0;
		 if($credit_cnt < 1){
		 	if(strpos((string)$value['title'], '스탠다드') !== false) $credit_cnt = 100;
		 	else if(strpos((string)$value['title'], '프리미엄+') !== false) $credit_cnt = 600;
		 	else if(strpos((string)$value['title'], '프리미엄') !== false) $credit_cnt = 220;
		 }
		 $credit_bonus = 0;
		 if($credit_cnt === 220) $credit_bonus = 20;
		 else if($credit_cnt === 600) $credit_bonus = 100;
		 $is_credit_plan = ($credit_cnt > 0);
		 $display_pay_type = $is_credit_plan ? '1회 결제' : $value['pay_type'];
		 $plan_desc_items = array();
		 if($credit_cnt === 100){
			$plan_desc_items = array(
				'100크레딧 충전',
				'기본 요청 1건 10크레딧 차감',
				'TM 요청 1건 30크레딧 차감'
			);
		 } else if($credit_cnt === 220){
			$plan_desc_items = array(
				'200크레딧 + 20크레딧 추가 지급',
				'기본 요청 1건 10크레딧 차감',
				'TM 요청 1건 30크레딧 차감'
			);
		 } else if($credit_cnt === 600){
			$plan_desc_items = array(
				'500크레딧 + 100크레딧 추가 지급',
				'기본 요청 1건 10크레딧 차감',
				'TM 요청 1건 30크레딧 차감'
			);
		 }
	?>
        <li class="bxShadow2">
            <div class="plan_head">
                <p class="kr"><?=$value['title']?></p>
                <p class="en"><?=$value['title_en']?></p>
            </div>
            <div class="plan_body new">
                <div class="plan_content">
					<div class="plan_money">
						<?if(!$is_credit_plan && $value['price_origin']>-1){?>
							<? if($value['price_origin'] != "0" && $value['price_origin'] != "프리" && is_numeric($value['price_origin'])){?>
								<div class="price_origin">정상가 <?=number_format($value['price_origin'])?></div>
							<?}else{?>
								<div class="price"><?=$value['price_origin']?></div>
							<?}?>
						<?}?>

						<?if($value['price_sale']>-1){?>
							<div class="price_sale <? if($is_credit_plan || ($value['price_origin'] != "0" && $value['price_origin'] != "프리")){?>price<?}?><?if($credit_bonus > 0){?> has_bonus<?}?>">
								<?=(is_numeric($value['price_sale']) ? number_format($value['price_sale']) : $value['price_sale'])?>

								<?if($credit_bonus > 0){?>
									<p class="sale bonus_replace"><strong>+<?=number_format($credit_bonus)?></strong></p>
								<?}else if(!$is_credit_plan && $value['sale'] && is_numeric($value['sale'])){?>
									<p class="sale"><strong><?=number_format($value['sale'])?></strong>%</p>
								<?}?>
							</div>
						<?}?>
						<?if($credit_cnt > 0){?>
							<div class="credit_note empty" aria-hidden="true">&nbsp;</div>
						<?}?>
					</div>
	                    <ul class="price_info">
	                        <li>VAT <span><?=$value['vat']?></span></li>
	                        <li><?=$display_pay_type?></li>
	                    </ul>

					<form name="mobileweb<?= $value['no']?>" method="post" class="mpay">
							<input type="hidden" name="mtype" value="<?=$value['title']?>">
							<input type="hidden" name="authtype" value="D">
							<input type="hidden" name="mid" value="<?php echo $mid ?>">
							<input type="hidden" name="orderid" value="<?php echo $orderid ?>">
							<input type="hidden" name="price" value="<?php echo $value['price_sale']*1.1?>">
							<input type="hidden" name="timestamp" value="<?php echo $dateVal ?>">
							<input type="hidden" name="hashdata" value="<?php echo $hashData ?>">
							<input type="hidden" name="goodname" value="<?=$value['title']?>">
							<input type="hidden" name="buyername" value="<?=$member['mb_id']?>">
							<input type="hidden" name="buyertel" value="<?=$member['mb_hp']?>">
							<input type="hidden" name="buyeremail" value="<?=$member['mb_email']?>">
							<input type="hidden" name="returnurl" value="https://macomaex.com/ko/INIbill_mo_return.php">
							<input type="hidden" name="carduse" value="">
							<a mtype="<?=$value['title']?>" idx="<?= $value['no']?>" class="plan_payment">충전하기</a>
					</form>

                </div>
                <div class="plan_desc">
                    <ul>
						<?if(count($plan_desc_items) > 0){?>
							<?foreach($plan_desc_items as $desc_idx => $desc_item){?>
								<?if($desc_idx === 0){?>
									<li class="under"><span><?=$desc_item?></span></li>
								<?}else{?>
									<li><?=$desc_item?></li>
								<?}?>
							<?}?>
						<?}else{?>
							<?=$value['desc']?>
						<?}?>
                    </ul>
                </div>
            </div>
        </li>
	<?}?>
    </ul>
</div>

<div class="popup popup_submit">
    <div class="popupInner">
        <a href="javascript:popupClose('')" class="popupClose"><img src="/img/icon/icon_close.svg" alt=""></a>

	        <div class="popup_top">
	            <h4 class="popup_top_title">선택한 상품</h4>

	            <div class="popup_top_txt1">스탠다드 9,900원</div>
			<div class="popup_top_txt2">1회 결제 / VAT 10% 별도</div>
	        </div>

        <div class="popup_content">
            <table>
                <tbody>
                    <tr>
                        <th class="coupon_title">할인쿠폰이 있으신가요?</th>
                        <td>
						<input type="text" name="coupon" id="coupon" placeholder="ex) ED9A01" maxlength="6" class="coupon_input">
						</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="popup_btns">
            <button type="submit" id="coupon_submit">다음</button>
        </div>
    </div>
</div>

<script>
<?if($member['mb_id']){?>
	$("#coupon").keyup(function(){
		if($("#coupon").val().length==6){
			var id=$("#coupon").val();
			$.post( "../get_set.php", {
				type: 'coupon_chk',
				id:id
			})
			.done(function(jqXHR) {
					//console.log(jqXHR);
					jqXHR=jqXHR.split("|");
					if(jqXHR[0]==1000){
						//완료시
						var kr=$("a.plan_payment[idx='"+jqXHR[1]+"']").parents("li.bxShadow2").find(".plan_head .kr").text();
						var price_sale=$("a.plan_payment[idx='"+jqXHR[1]+"']").parents("li.bxShadow2").find(".plan_body .price_sale").html();
						var price_sale=price_sale.split("<p");
						var price_info=$("a.plan_payment[idx='"+jqXHR[1]+"']").parents("li.bxShadow2").find(".plan_body .price_info li:eq(0)").text();
						$(".popup .popup_top_txt1").html("<span class='coupon'>적용쿠폰 ("+jqXHR[3]+" 개월) - </span>"+kr+" "+price_sale[0]);
						$(".popup .popup_top_txt2").text(price_info);
						$(".popup #coupon_submit").attr("idx",jqXHR[1]);

						$("#coupon").addClass("readonly").attr('readonly', true);
						$("#coupon_chk").val("true");
						$("#coupon_submit").text("적용");
						jAlert("정상적인 쿠폰입니다.\n적용 버튼을 눌러 적용해주세요.","알림");
					}else if(jqXHR[0]==500){
						//쿠폰 적용 실패시
						jAlert(jqXHR[1],"알림",function(){
							$("#coupon").val("");
							$("#coupon").focus();
							$("#coupon_chk").val("false");
						});
						return false;
					}else{
						//오류시
						$("#coupon_chk").val("false");
						jAlert("처리에 오류가 있습니다.","알림");
					}
			});
			return false;
		}
	})

$("body").on("click","#coupon_submit",function () {
	$this=$(this);
	if($("#coupon_chk").val()=="false"){
		idx=$(this).attr("idx");
		$("a.plan_payment[idx='"+idx+"']").click();
	}else{
		coupon=$("#coupon").val();
			$.post( "../get_set.php", {
				type: 'use_coupon',
				coupon:coupon
			})
			.done(function(jqXHR) {
					jqXHR=jqXHR.split("|");
					if(jqXHR[0]==1000){
						location.href="/ko/my_plan.php";
					}else if(jqXHR[0]==500){
						jAlert(jqXHR[1],"알림");
					}else{
						jAlert(jqXHR[1],"알림");
					}
			});
	}
})



const html = document.querySelector("html")
const popupAll = document.querySelectorAll(".popup")
function popupClose(selector){
   popupAll.forEach((e)=>{e.classList.remove("open")})
	html.classList.remove("hide")
		$("#coupon_chk").val("false");
		$("#coupon_pop").val("false");
}

$("body").on("click",".plan_payment",function () {
	event.preventDefault();

	$this=$(this);
	var kr=$this.parents("li.bxShadow2").find(".plan_head .kr").text();
	var price_sale=$this.parents("li.bxShadow2").find(".plan_body .price_sale").html();
	var price_sale=price_sale.split("<p");
	var price_info=$this.parents("li.bxShadow2").find(".plan_body .price_info li:eq(0)").text();
	var mtype=$(this).attr("mtype");
	var idx=$(this).attr("idx");

	var orderid=$("input[name='orderid']").val();
	var mid=$("input[name='mid']").val();

	if(!orderid || !idx) {
		alert("결제에 문제가 있습니다.");
		return false;
	}

	// 크레딧 일반결제(STDPAY) 주문 생성 후 결제창 호출
	$.post( "../get_set.php", {
		type: 'membership_credit_pay',
		idx:idx,
		mtype:mtype,
		orderid:orderid,
		mid:mid
	})
	.done(function(jqXHR) {
		jqXHR=jqXHR.split("|");
		if(jqXHR[0]==1000){
			var payOrderNo = jqXHR[1];
			window.open("/ko/credit_pay.php?orderid="+encodeURIComponent(payOrderNo), 'pyWin', 'width=520px,height=760px');
		}else{
			if(jqXHR[1]=="02") jAlert("검색 결과가 없습니다.","알림");
			else jAlert(jqXHR[1],"알림");
		}
	});
	return false;

	/*
	기존 정기결제(빌링) 플로우는 보존한다.
	$.post( "../get_set.php", {
		type: 'membership_pay',
		idx:idx,
		mtype:mtype,
		orderid:orderid,
		mid:mid
	})
	.done(function(jqXHR) {
			jqXHR=jqXHR.split("|");
			if(jqXHR[0]==1000){
				//완료시
				if(jqXHR[1]!="true"){
					jCustomConfirm(jqXHR[1], '알림','취소','확인', function(r){
						if(!r){
							popupClose();
							window.open("about:blank", 'pyWin', 'width=480px,height=700px');
							form = $this.parents("form.mpay");
							form.attr("action","https://inilite.inicis.com/inibill/inibill_card.jsp");
							form.attr("target","pyWin");
							form.submit();
							$("#coupon_pop").val("false");
						}
					});
				}if(jqXHR[1]=="true" && $("#coupon_pop").val()=="false"){
					//무료 회원일 경우 쿠폰창 열기
					$(".popup .popup_top_txt1").text(kr+" "+price_sale[0]+" 원");
					$(".popup .popup_top_txt2").text(price_info);
					$(".popup #coupon_submit").attr("idx",idx);
					
					popupAll.forEach((e)=>{e.classList.remove("open")})
					let popupName = document.querySelector(".popup");
					if(popupName){
						html.classList.add("hide")
						popupName.classList.add("open")
					}
						$("#coupon").val("");
						$("#coupon").removeClass("readonly").attr('readonly', false);
						$("#coupon_pop").val("true");
					return false;
				}else{
					popupClose();
					window.open("about:blank", 'pyWin', 'width=480px,height=700px');
					form = $this.parents("form.mpay");
					form.attr("action","https://inilite.inicis.com/inibill/inibill_card.jsp");
					form.attr("target","pyWin");
					form.submit();
					$("#coupon_pop").val("false");
				}
			}else{
				//오류시
				if(jqXHR[1]=="02") jAlert("검색 결과가 없습니다.","알림");
				else jAlert(jqXHR[1],"알림");
			}
	});
	*/

})
<?}else{?>
$("body").on("click",".plan_payment",function () {
	event.preventDefault();
	jAlert("로그인후 이용해 주세요.","알림",function(){
		location.href="/bbs/login.php?url=/ko/plan.php";
	});
})
<?}?>
</script>

<?
include_once(G5_THEME_PATH.'/tail.php');
?>
