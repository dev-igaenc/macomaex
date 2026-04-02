<?
include_once("./_common.php");

$board['bo_10']="4"; // 1뎁스
$board['bo_9']="1"; // 2뎁스
//$board['bo_8']="1"; // 3뎁스
//$board['bo_7']="1"; // 4뎁스
// $board['bo_5'] = ""; // 1뎁스 제목
// $board['bo_6'] = ""; // 1뎁스 설명
// $board['bo_subject'] = "";  // 2뎁스 제목
//$board['bo_4'] = $config['cf_title']."의 혜택을 확인하세요.";  // 2뎁스 설명

include_once(G5_THEME_PATH.'/head.php');
if($member['mb_id']){
	$membership_q="SELECT g5_membership_status where uid='{$member['mb_id']}' ";
	$membership=sql_query($membership_q);
}
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

$use_chkfake	= "Y";											// PC결제 보안강화 사용 ["Y" 고정]
$signKey 		= _merchantKey;	
$mKey 	= $SignatureUtil->makeHash($signKey, "sha256");
/*결제모듈*/
?>
<script language="javascript" type="text/javascript" src="https://stgstdpay.inicis.com/stdjs/INIStdPay.js" charset="UTF-8"></script>

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
<?
$orderNumber 	= $orderid; 						// 가맹점 주문번호(가맹점에서 직접 설정)
$price 			= $value['price_sale'];        
$params = array(
    "oid" => $orderNumber,
    "price" => $price,
    "timestamp" => $timestamp
);
$sign   = $SignatureUtil->makeSignature($params);
$sign2   = $SignatureUtil->makeSignature($params);
?>
					<form name="mobileweb<?= $value['no']?>" method="post" class="mpay">
							<input type="hidden" name="mtype" value="<?=$value['title']?>">
							<input type="hidden" name="authtype" value="D">
							<input type="hidden" name="mid" value="<?php echo $mid ?>">
							<input type="hidden" name="orderid" value="<?php echo $orderid ?>">
							<!--PC-->
							<input type="hidden" name="gopaymethod" value="Card">
							<input type="hidden" name="oid" value="<?php echo $orderid ?>">
                            <input type="hidden" name="use_chkfake" value="<?php echo $use_chkfake ?>">
                            <input type="hidden" name="signature" value="<?php echo $sign ?>">
                            <input type="hidden" name="verification" value="<?php echo $sign2 ?>">
				    		<input type="hidden" name="mKey" value="<?php echo $mKey ?>">
							<input type="hidden" name="currency" value="WON">	
				    		<input type="hidden" name="returnUrl" value="https://macomaex.com/ko/INIbill_pc_return.php">
                            <input type="hidden" name="closeUrl" value="https://macomaex.com/ko/close.php">
							<!--PC-->
							<input type="hidden" name="price" value="<?php echo $value['price_sale'] ?>">
							<input type="hidden" name="timestamp" value="<?php echo $dateVal ?>">
							<input type="hidden" name="hashdata" value="<?php echo $hashData ?>">
							<input type="hidden" name="goodname" value="<?=$value['title']?>">
							<input type="hidden" name="buyername" value="<?=$member['mb_id']?>">
							<input type="hidden" name="buyertel" value="<?=$member['mb_8']?>">
							<input type="hidden" name="buyeremail" value="<?=$member['mb_email']?>">
							<input type="hidden" name="returnurl" value="https://macomaex.com/ko/INIbill_mo_return.php">
							<input type="text" name="carduse" value="">
							<a mtype="<?=$value['title']?>" idx="<?= $value['no']?>" class="plan_payment">결제하기</a>
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
				form = $this.parents("form.mpay");
				INIStdPay.pay(form);
				//완료시
				/*
				window.open("about:blank", 'pyWin', 'width=480px,height=700px');
				form = $this.parents("form.mpay");
				form.attr("action","https://inilite.inicis.com/inibill/inibill_card.jsp");
				form.attr("target","pyWin");
				form.submit();
				*/
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
	jAlert("로그인후 이용해 주세요.","알림");
})
<?}?>
</script>

<?/*
<script language="javascript">
	$("body").on("click",".plan_payment",function () {
			var plan_head=$(this).parents("li").find(".plan_head .kr").text();
			var idx=$(this).attr("idx");
			$.post( "../get_set.php", {
				type: 'membership_pay',
				idx:idx
			})
			.done(function(jqXHR) {
					alert(jqXHR);
					jqXHR=jqXHR.split("|");
					if(jqXHR[0]==1000){
						//완료시
							form = $(this).parents("form.mpay");
							form.attr("action","https://inilite.inicis.com/inibill/inibill_card.jsp");
							form.attr("target","_blank");
							form.submit();
					}else{
						//오류시
						if(jqXHR[1]=="02") jAlert("검색 결과가 없습니다.","알림");
						else jAlert("검색에 오류가 있습니다.","알림");
					}

			});

	})
</script>
*/
?>
<?
include_once(G5_THEME_PATH.'/tail.php');
?>