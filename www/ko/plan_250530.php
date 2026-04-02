<?
include_once("./_common.php");

$board['bo_10']="4"; // 1뎁스
$board['bo_9']="1"; // 2뎁스

include_once(G5_THEME_PATH.'/head.php');
$membership_list=get_membership();

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
$my_membership=$msinfo_row[0];
/*결제모듈*/
?>
<style>
	.planList > li:nth-of-type(1) .plan_body .plan_payment{opacity: 0; pointer-events: none;}
</style>
<div id="plan">
    <ul class="planList">
	<?foreach($membership_list as $value){
		 $value['pay_type']=str_replace("[","<span>",$value['pay_type']);
		 $value['pay_type']=str_replace("]","</span>",$value['pay_type']);
		 $value['desc']="<li>".str_replace("\n","</li><li>",$value['desc'])."</li>";
		 $value['desc']=str_replace("<li>[","<li class='under'><span>",$value['desc']);
		 $value['desc']=str_replace("]</li>","</span></li>",$value['desc']);
	?>
        <li class="bxShadow2">
            <div class="plan_head">
                <p class="kr"><?=$value['title']?></p>
                <p class="en"><?=$value['title_en']?></p>
            </div>
            <div class="plan_body new">
                <div class="plan_content">
					<div class="plan_money">
						<?if($value['price_origin']>-1){?>
							<? if($value['price_origin'] != "0" && $value['price_origin'] != "프리"){?>
								<div class="price_origin">정상가 <?=number_format($value['price_origin'])?></div>
							<?}else{?>
								<div class="price"><?=number_format($value['price_origin'])?></div>
							<?}?>
						<?}?>

						<?if($value['price_sale']>-1){?>
							<div class="price_sale <? if($value['price_origin'] != "0" && $value['price_origin'] != "프리"){?>price<?}?>">
								<?=number_format($value['price_sale'])?>

								<?if($value['sale']){?>
									<p class="sale"><strong><?=number_format($value['sale'])?></strong>%</p>
								<?}?>
							</div>
						<?}?>
					</div>
                    <ul class="price_info">
                        <li>VAT <span><?=$value['vat']?></span></li>
                        <li><?=$value['pay_type']?></li>
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
							<?if($my_membership['membership_parent_no']==$value['no']){?>
							<a class="plan_payment_use">맴버쉽 이용중</a>
							<?}else{?>
							<a mtype="<?=$value['title']?>" idx="<?= $value['no']?>" class="plan_payment">결제하기</a>
							<?}?>
					</form>

                </div>
                <div class="plan_desc">
                    <ul>
                        <?=$value['desc']?>
                    </ul>
                </div>
            </div>
        </li>
	<?}?>
    </ul>
</div>
<script>
<?if($member['mb_id']){?>
$("body").on("click",".plan_payment",function () {
	event.preventDefault();
	$this=$(this);
	var mtype=$(this).attr("mtype");
	var idx=$(this).attr("idx");
	var orderid=$("input[name='orderid']").val();
	var mid=$("input[name='mid']").val();

	if(!orderid || !idx) {
		alert("결제에 문제가 있습니다.");
		return false;
	}

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
				if(jqXHR[1]!=="true"){
					jCustomConfirm(jqXHR[1], '알림','취소','확인', function(r){
						if(!r){
							window.open("about:blank", 'pyWin', 'width=480px,height=700px');
							form = $this.parents("form.mpay");
							form.attr("action","https://inilite.inicis.com/inibill/inibill_card.jsp");
							form.attr("target","pyWin");
							form.submit();
						}
					});
				}else{
					window.open("about:blank", 'pyWin', 'width=480px,height=700px');
					form = $this.parents("form.mpay");
					form.attr("action","https://inilite.inicis.com/inibill/inibill_card.jsp");
					form.attr("target","pyWin");
					form.submit();
				}
			}else{
				//오류시
				if(jqXHR[1]=="02") jAlert("검색 결과가 없습니다.","알림");
				else jAlert(jqXHR[1],"알림");
			}
	});

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