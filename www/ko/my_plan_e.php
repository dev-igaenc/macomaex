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

if(!$member['mb_id']) ALERT("로그인 후 이용하세요.","/"); 

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
?>


<div id="my_plan">

    <div class="fullBox my_plan mgb10">
	<input type="hidden" name="ms_id"  id="ms_id" value="<?=$msinfo['no']?>">


		<?if($membership_info['title_en']!="FREE" && $msinfo){?>
        <!-- 무료가 아닌경우 -->
        <div class="inner">
            <div class="textArea">
                <div class="vat">VAT <?=$mship[$membership_info['title_en']]['vat']?></div>
                <h4 class="title">
                    <?=$mship[$membership_info['title_en']]['title']?><span><?=$mship[$membership_info['title_en']]['title_en']?></span> <br>
					<?if($mship[$membership_info['title_en']]['price_origin']>-1){?><p class="price_origin"><?=number_format($mship[$membership_info['title_en']]['price_origin']);?></p><?}?>
					<?if($mship[$membership_info['title_en']]['sale']){?><p class="sale"><?=number_format($mship[$membership_info['title_en']]['sale']);?>%</p><?}?>
					<?if($mship[$membership_info['title_en']]['price_sale']){?><p class="price_sale"><?=number_format($mship[$membership_info['title_en']]['price_sale']);?></p><?}?>
                </h4>
                <ul class="venefit">
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
                </ul>
				<?if($msinfo['status']=="T"){?>
				 <a href="javascript:jAlert('<?=$msinfo['date_end']?>일 이후 구독 취소 됩니다.','알림');" id="" class="unSubscribe"><span>만료일 후 취소</span></a>
				<?}else{?>
                <a href="javascript:;" id="membership_cancel" class="unSubscribe"><span>구독 취소</span></a>
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
$sql="select * from g5_membership_payment A left join g5_membership_status_history B on A.order_no=B.order_no  where A.uid='{$member['mb_id']}' and A.complete_yn='Y' order by A.regdate desc ";
$result = sql_query($sql);
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
					 <?php for ($i=0; $row=sql_fetch_array($result); $i++) {
						$status="결제완료";
						if($row['status']=="S") $status="결제완료";
						if($row['status']=="C") $status="결제취소";
					 ?>
                        <tr>
                            <td><?=$i+1?></td>
                            <td><?=$row['price']?></td>
                            <td><?=substr($row['complete_date'],0,10)?></td>
                            <td><?=$row['date_begin']?></td>
                            <td><?=$row['date_end']?></td>
                            <td><?=$row['order_goods']?></td>
                            <td><?=$status?></td>
                            <td><?if($status=="결제완료" && $row['price']>0){?><a href="https://iniweb.inicis.com/receipt/iniReceipt.jsp?noTid=<?=$row['tid']?>" target="_blank" class="receipt">영수증</a><?}?></td>
                        </tr>
						<?}?>
						<? if ($i == 0) {?>
                        <tr>
                            <td colspan="8">이용 내역이 없습니다.</td>
                        </tr>
						<?}?>
                        <!--tr>
                            <td>20</td>
                            <td>150,000</td>
                            <td>2024.02.22</td>
                            <td>2024.02.22</td>
                            <td>2024.02.22</td>
                            <td>베이직</td>
                            <td>결제완료</td>
                            <td><a href="" class="receipt">영수증</a></td>
                        </tr-->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<script>

$("#membership_cancel").click(function(){
	ms_id=$("#ms_id").val();
	if(ms_id){
		jCustomConfirm("구독취소는 결제 후 7일 이내\n매칭 요청 서비스를 이용하지 않은 경우, 전액 환불처리 되며 \n7일 이후 혹은 사용 이력이 있으시면\n만료일까지 유지후 구독 취소됩니다. \n구독을 취소하시겠습니까?","알림","취소","확인",function(e){
			if(!e){

				$.post( "../get_set_e.php", {
					type: 'membership_cancel'
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