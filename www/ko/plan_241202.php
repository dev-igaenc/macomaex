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
$merchantKey = macomaex_env("INIBILL_TEST_MERCHANT_KEY", ""); // 이니라이트키
$dateVal = date("YmdHis");
$mid = macomaex_env("INIBILL_TEST_MID", "");
$orderid= "DemoTest_". $timestamp ;
$hashData = hash("sha256",(string)$mid.(string)$orderid.(string)$dateVal.(string)$merchantKey);
/*결제모듈*/
?>


<div id="plan">
    <ul class="planList">
	<?foreach($membership_list as $value){
		 $value['pay_type']=str_replace("[","<span>",$value['pay_type']);
		 $value['pay_type']=str_replace("]","</span>",$value['pay_type']);
		 $value['desc']="<li>".str_replace("\n","</li><li>",$value['desc'])."</li>";
		 $value['desc']=str_replace("<li>[","<li class='under'><span>",$value['desc']);
		 $value['desc']=str_replace("]</li>","</span></li>",$value['desc']);
		 $value['desc']=str_replace("]","",$value['desc']);
	?>
        <li class="bxShadow2">
            <div class="plan_head">
                <p class="kr"><?=$value['title']?></p>
                <p class="en"><?=$value['title_en']?></p>
            </div>
            <div class="plan_body">
                <div class="plan_content">
					<?if($value['price_origin']>-1){?>
                    <p class="price">
					<?=number_format($value['price_origin'])?>
					</p>
					<?}?>
					<!--
					<?if($value['sale']){?>
					<p class="sale">
					<?=number_format($value['sale'])?>%
					</p>
					<?}?>
					<?if($value['price_sale']>-1){?>
					<p class="price_sale">
					<?=number_format($value['price_sale'])?>
					</p>
					<?}?>
					-->
                    <ul class="price_info">
                        <li>VAT <span><?=$value['vat']?></span></li>
                        <li><?=$value['pay_type']?></li>
                    </ul>

					<form name="mobileweb<?= $value['no']?>" method="post" class="mpay">
							<input type="hidden" name="mtype" value="<?=$value['title']?>">
							<input type="hidden" name="authtype" value="D">
							<input type="hidden" name="mid" value="<?php echo $mid ?>">
							<input type="hidden" name="orderid" value="<?php echo $orderid ?>">
							<input type="hidden" name="price" value="<?php echo $value['price_sale'] ?>">
							<input type="hidden" name="timestamp" value="<?php echo $dateVal ?>">
							<input type="hidden" name="hashdata" value="<?php echo $hashData ?>">
							<input type="hidden" name="goodname" value="<?=$value['title']?>">
							<input type="hidden" name="buyername" value="<?=$member['mb_id']?>">
							<input type="hidden" name="buyertel" value="<?=$member['mb_hp']?>">
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
	<div class="privacy">
		<div class="no-payPolicy">
									<p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">개인정보활용동의서, 환불규정 및 처리방침</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">&nbsp;</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">주식회사 이가이엔씨(이하 총칭하여 ‘회사’라한다)는 ‘회사’는 개인정보보호법에 따라 이용자의 개인정보 보호 및 권익을 보호하고 개인정보와 관련한 이용자의 고충을 원활하게 처리할 수 있도록 다음과 같은 처리방침을 두고 있습니다. '회사’ 은(는)&nbsp; 개인정보처리방침을 개정하는 경우 웹사이트 공지사항(또는 개별공지)을 통하여 공지할 것입니다.</span></p>
									
									<p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">&nbsp;</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="font-weight:700">환불규정</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">- 결제후 7영업일이내 구독취소시 무조건 전액환불</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">- 7영업일 이후 매칭1건이상 요청 이력이 있으면 취소불가</span></p>
									<p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">&nbsp;</span></p>
									
									
									<p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">○ 본 방침은부터 2024년 8월 15 일부터 시행됩니다.</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 19.2px;"><span style=" NanumBarunGothic, NanumBarunGothicOTF;"><br></span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">1. 개인정보의 처리 목적</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">&nbsp;가.회사’'은(는) 개인정보를 다음의 목적을 위해 처리합니다. 처리한 개인정보는 다음의 목적이외의 용도로는 사용되지 않으며 이용 목적이 변경될 시에는 사전동의를 구할 예정입니다</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">1) 인터넷사이트 (<a href="http://www.macomaex.com)"><span style="">www.macomaex.com)</span></a> 회원가입 및 관리 회원 가입의사 확인, 회원제 서비스 제공에 따른 본인 식별·인증, 회원자격 유지·관리, 제한적 본인확인제 시행에 따른 본인확인, 서비스 부정이용 방지 등을 목적으로 개인정보를 처리합니다.</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">2) 민원사무 처리 민원인의 신원 확인, 처리결과 통보 등을 목적으로 개인정보를 처리합니다.</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">다. 재화 또는 서비스 제공 서비스 제공, 콘텐츠 제공, 맞춤 서비스 제공, 본인인증 등을 목적으로 개인정보를 처리합니다.</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">3) 마케팅 및 광고에의 활용 신규 서비스(제품) 개발 및 맞춤 서비스 제공, 이벤트 및 광고성 정보 제공 및 참여기회 제공&nbsp; , 접속빈도 파악 또는 회원의 서비스 이용에 대한 통계 등을 목적으로 개인정보를 처리합니다.</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">2.개인정보의 수집항목</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">1)’회사’는 ‘회사’가 운영하는 본사이트(<a href="http://www.macomaex.com)"><span style="">www.macomaex.com)</span></a> 원할한 서비스 제공를 위하여 본사이트 회원가입시 아래 회원의 개인정보를 수집 및 이용합니다</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">개인정보 수집항목은 다음과 같습니다&nbsp;</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">2)수집항목&nbsp;&nbsp;</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">- 이름(담당자), 휴대폰번호, 연락처, 이메일주소</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 19.2px;"><span style=" NanumBarunGothic, NanumBarunGothicOTF;"><br></span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">&nbsp;</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 19.2px;"><span style=" NanumBarunGothic, NanumBarunGothicOTF;"><br></span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">3. 개인정보의 보유근거/ 관련법령 /보유기간&nbsp; &nbsp;</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">1) 보유근거 : 회원서비스의&nbsp; 및 회원관리</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">&nbsp; '회사'은(는) 법령에 따른 회원의 개인정보 보유근거·보유기간 또는 정보주체로부터 개인정보를 수집시에 동의 받은 개인정보 보유근거,보유기간 내에서 개인정보를 처리,보유합니다.</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">&nbsp;</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">&nbsp;</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">2)관련법령 :</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 19.2px;"><span style=" NanumBarunGothic, NanumBarunGothicOTF;"><br></span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">개인정보법15조(개인정보의 수집ㆍ이용)</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">① 개인정보처리자는 다음 각 호의 어느 하나에 해당하는 경우에는 개인정보를 수집할 수 있으며 그 수집 목적의 범위에서 이용할 수 있다.</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">1 정보주체의 동의를 받은 경우</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">2. 법률에 특별한 규정이 있거나 법령상 의무를 준수하기 위하여 불가피한 경우</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">3. 공공기관이 법령 등에서 정하는 소관 업무의 수행을 위하여 불가피한 경우</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">4. 정보주체와의 계약의 체결 및 이행을 위하여 불가피하게 필요한 경우</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">5. 정보주체 또는 그 법정대리인이 의사표시를 할 수 없는 상태에 있거나 주소불명 등으로 사전 동의를 받을 수 없는 경우로서 명백히 정보주체 또는 제3자의 급박한 생명, 신체, 재산의 이익을 위하여 필요하다고 인정되는 경우</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">6. 개인정보처리자의 정당한 이익을 달성하기 위하여 필요한 경우로서 명백하게 정보주체의 권리보다 우선하는 경우. 이 경우 개인정보처리자의 정당한 이익과 상당한 관련이 있고 합리적인 범위를 초과하지 아니하는 경우에 한한다.</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">② 개인정보처리자는 제1항제1호에 따른 동의를 받을 때에는 다음 각 호의 사항을 정보주체에게 알려야 한다. 다음 각 호의 어느 하나의 사항을 변경하는 경우에도 이를 알리고 동의를 받아야 한다.</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">1. 개인정보의 수집·이용 목적</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">2. 수집하려는 개인정보의 항목</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">3. 개인정보의 보유 및 이용 기간</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">4. 동의를 거부할 권리가 있다는 사실 및 동의 거부에 따른 불이익이 있는 경우에는 그 불이익의 내용</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">③ 개인정보처리자는 당초 수집 목적과 합리적으로 관련된 범위에서 정보주체에게 불이익이 발생하는지 여부, 암호화 등 안전성 확보에 필요한 조치를 하였는지 여부 등을 고려하여 대통령령으로 정하는 바에 따라 정보주체의 동의 없이 개인정보를 이용할 수 있다.</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 19.2px;"><span style=" NanumBarunGothic, NanumBarunGothicOTF;"><br></span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">3)보유기간</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">개인정보보유기간&nbsp;</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">1 신용정보의 수집/처리 및 이용 등에 관한 기록 : 5년</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">2 소비자의 불만 또는 분쟁처리에 관한 기록 : 3년</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">3 대금결제 및 재화 등의 공급에 관한 기록 : 5년</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">4 계약 또는 청약철회 등에 관한 기록 : 5년</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 19.2px;"><span style=" NanumBarunGothic, NanumBarunGothicOTF;"><br></span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 19.2px;"><span style=" NanumBarunGothic, NanumBarunGothicOTF;"><br></span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">&nbsp; :</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 19.2px;"><span style=" NanumBarunGothic, NanumBarunGothicOTF;"><br></span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">&nbsp;&nbsp;</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 19.2px;"><span style=" NanumBarunGothic, NanumBarunGothicOTF;"><br></span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 19.2px;"><span style=" NanumBarunGothic, NanumBarunGothicOTF;"><br></span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">4. 개인정보주체와 법정대리인의 권리·의무 및 그 행사방법 이용자는 개인정보주체로써 다음과 같은 권리를 행사할 수 있습니다.</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">1) 정보주체는 ‘회사’에 대해 언제든지 개인정보 열람,정정,삭제,처리정지 요구 등의 권리를 행사할 수 있습니다.</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">2) 제1항에 따른 권리 행사는 ‘회사’에 대해 개인정보 보호법 시행령 제41조제1항에 따라 서면, 전자우편, 모사전송(FAX) 등을 통하여 하실 수 있으며 ‘회사’(는)은 이에 대해 지체 없이 조치하겠습니다.</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">3) 제1항에 따른 권리 행사는 정보주체의 법정대리인이나 위임을 받은 자 등 대리인을 통하여 하실 수 있습니다. 이 경우 개인정보 보호법 시행규칙 별지 제11호 서식에 따른 위임장을 제출하셔야 합니다.</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">4) 개인정보 열람 및 처리정지 요구는 개인정보보호법 제35조 제5항, 제37조 제2항에 의하여 정보주체의 권리가 제한 될 수 있습니다.</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">5) 개인정보의 정정 및 삭제 요구는 다른 법령에서 그 개인정보가 수집 대상으로 명시되어 있는 경우에는 그 삭제를 요구할 수 없습니다.</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">6) ‘회사’은(는) 정보주체 권리에 따른 열람의 요구, 정정·삭제의 요구, 처리정지의 요구 시 열람 등 요구를 한 자가 본인이거나 정당한 대리인인지를 확인합니다.</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 19.2px;"><span style=" NanumBarunGothic, NanumBarunGothicOTF;"><br></span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 19.2px;"><span style=" NanumBarunGothic, NanumBarunGothicOTF;"><br></span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">&nbsp;</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">5. 개인정보의 파기'회사'은(는) 파기의 기한 및 방법은 다음과 같습니다.</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">&nbsp;.</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">1) 파기기한 : 이용자의 개인정보는 상기 제3조 3항의 개인정보의 보유기간이 경과된 경우에는 보유기간의 종료일로부터 5일 이내에, 개인정보의 처리 목적 달성, 해당 서비스의 폐지, 사업의 종료 등 그 개인정보가 불필요하게 되었을 때에는 개인정보의 처리가 불필요한 것으로 인정되는 날로부터 5일 이내에 그 개인정보를 파기합니다.</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">2) 파기방법 : 전자적 파일 형태의 정보는 기록을 재생할 수 없는 기술적 방법을 사용합니다.</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 19.2px;"><span style=" NanumBarunGothic, NanumBarunGothicOTF;"><br></span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 19.2px;"><span style=" NanumBarunGothic, NanumBarunGothicOTF;"><br></span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 19.2px;"><span style=" NanumBarunGothic, NanumBarunGothicOTF;"><br></span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 19.2px;"><span style=" NanumBarunGothic, NanumBarunGothicOTF;"><br></span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">6. 개인정보 자동 수집 장치의 설치•운영 및 거부에 관한 사항</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">1) ‘회사’는 개별적인 맞춤서비스를 제공하기 위해 이용정보를 저장하고 수시로 불러오는 ‘쿠기(cookie)’를 사용합니다.</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">2) 쿠키는 웹사이트를 운영하는데 이용되는 서버(http)가 이용자의 컴퓨터 브라우저에게 보내는 소량의 정보이며 이용자들의 PC 컴퓨터내의 하드디스크에 저장되기도 합니다.</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">가. 쿠키의 사용목적: 이용자가 방문한 각 서비스와 웹 사이트들에 대한 방문 및 이용형태, 인기 검색어, 보안접속 여부, 등을 파악하여 이용자에게 최적화된 정보 제공을 위해 사용됩니다.</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">나. 쿠키의 설치, 운영 및 거부 : 웹브라우저 상단의 도구&gt;인터넷 옵션&gt;개인정보 탭의 옵션 설정을 통해 쿠키 저장을 거부 할 수 있습니다.</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">다. 쿠키 저장을 거부할 경우 맞춤형 서비스 이용에 어려움이 발생할 수 있습니다.</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 19.2px;"><span style=" NanumBarunGothic, NanumBarunGothicOTF;"><br></span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">7. 개인정보 보호책임자 작성</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">1) ‘회사’(‘<a href="http://www.macomaex.com’이하)"><span style="">www.macomaex.com’이하)</span></a> 은(는) 개인정보 처리에 관한 업무를 총괄해서 책임지고, 개인정보 처리와 관련한 정보주체의 불만처리 및 피해구제 등을 위하여 아래와 같이 개인정보 보호담당자를 지정하고 있습니다.</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">▶ 개인정보 담당자</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">성명 : 이민형</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">직책 : 대표이사</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">연락처 : 02-573-8677, mhlee@igaemc.com</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">정보주체께서는 회사(‘<a href="http://www.macomaex.com’이하"><span style="">www.macomaex.com’이하</span></a> ) 의 서비스(또는 사업)를 이용하시면서 발생한 모든 개인정보 보호 관련 문의, 불만처리, 피해구제 등에 관한 사항을 개인정보 담당자에게 문의하실 수 있습니다. ‘회사’(‘<a href="http://www.macomaex.com’이하"><span style="">www.macomaex.com’이하</span></a> ‘회사’ 은(는) 정보주체의 문의에 대해 지체 없이 답변 및 처리합니다.</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 19.2px;"><span style=" NanumBarunGothic, NanumBarunGothicOTF;"><br></span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">8. 개인정보 처리방침 변경</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">이 개인정보처리방침은 시행일로부터 적용되며, 법령 및 방침에 따른 변경내용의 추가, 삭제 및 정정이 있는 경우에는 변경사항의 시행 7일 전부터 공지사항을 통하여 고지할 것입니다.</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 19.2px;"><span style=" NanumBarunGothic, NanumBarunGothicOTF;"><br></span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">&nbsp;</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">9. 고지의 의무</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">본 방침은 정부나 회사의 정책이 변경됨에 따라 내용의 추가 및 삭제, 수정이 있을 수 있으며, 이 경우 시행 7일전 홈페이지 또는 이메일 등을 통해 사전 공지하고 사전 공지가 곤란한 경우 지체없이 공지합니다.</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">또한 당사는 개인정보의 수집 및 활동, 제3자 제공 등 정보통신망 이용촉진 및 정보보호 등에 관한 법률 등 관련 법령에 따라 고객의 별도 동의가 필요한 사항과 관련된 내용이 추가, 변경되는 경우에는 관련 법령에 따른 고객의 별도 동의를 받습니다.</span></p>
									
								


									<p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">본 방침은 공지한 날로부터 시행됩니다.</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">- 개인정보 처리방침 버전 번호 : VER.1</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">- 개인정보 처리방침 공고일자: 2024.08.15</span></p><p class="" style="margin: 0cm; text-align: justify; line-height: 2;"><span style="">- 개인정보 처리방침 적용일자: 2024.08.15 VER.1</span></p><div style="text-align: justify;"><br></div>								</div>
			
								<div class="checkBox payBox">
									<p class="checkIn"><input type="checkbox" id="payPolicy" name="payPolicy"  value="구매조건" /><label for="payPolicy">위 상품의 구매 조건을 확인하였으며 결제에 동의합니다.</label></p>
								</div>


	</div>
</div>
<script>
<?if($member['mb_id']){?>
$("body").on("click",".plan_payment",function () {
	event.preventDefault();
	payPolicy=$("#payPolicy").prop("checked");
	if(!payPolicy) {

		jAlert("구매조건 확인 후 동의에 체크해 주세요.","알림",function(){
			//$("#payPolicy").focus();
			$("#payPolicy").select();
		});

		return false;
	}
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
				window.open("about:blank", 'pyWin', 'width=480px');
				if(mtype=="무료"){
					form = $this.parents("form.mpay");
					form.attr("action","https://macomaex.com/ko/INIbill_mo_return.php");
					form.attr("target","pyWin");
					form.submit();
				}else{
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
