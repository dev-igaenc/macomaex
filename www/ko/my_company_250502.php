<?
include_once("./_common.php");

$board['bo_10']="6"; // 1뎁스
$board['bo_9']="1"; // 2뎁스
//$board['bo_8']="1"; // 3뎁스
//$board['bo_7']="1"; // 4뎁스
// $board['bo_5'] = ""; // 1뎁스 제목
// $board['bo_6'] = ""; // 1뎁스 설명
// $board['bo_subject'] = "";  // 2뎁스 제목
//$board['bo_4'] = $config['cf_title']."의 혜택을 확인하세요.";  // 2뎁스 설명

//로그인 체크
login_chk();

$cd=$member['mb_company'];

if($cd){
	$com=sql_fetch("SELECT * from company where KEDCD='$cd'");
	$major_service=sql_fetch("SELECT * from g5_member where mb_company='$cd'");
}else{
	ALERT("정상적으로 이용하세요.","/");
}

include_once(G5_THEME_PATH.'/head.php');


$view_chk="true";

//사업자번호
function addh_bzno($bz){
	if(!$bz) return false;
	$bz=str_replace("-","",$bz);
	$bz1=substr($bz,0,3);
	$bz2=substr($bz,3,2);
	$bz3=substr($bz,5);
	return $bz1."-".$bz2."-".$bz3;
}
if($com['BZNO']) {
	$bzno_c=addh_bzno($com['BZNO']);
	$bzno_h=explode("-",$bzno_c);	
}


 if($com['ext_1']=="API 입력") {
	//updateKODATA($com['BZNO']);
 }
if($com['BZC_CD']) $kssc=sql_fetch("SELECT * from kssc where k_code='{$com['BZC_CD']}'");
if($com['KSIC9_BZC_CD']) $kssc_9=sql_fetch("SELECT * from kssc_9 where k_code='{$com['KSIC9_BZC_CD']}'");

//맴버쉽 정보 가져오기
$minfo=getUserMembership();
$minfo[0]['no'];

	//주식정보
	#당기순이익(3년간) 단위 천원
	$s_query="select * from company_financial where KEDCD='{$cd}' order by ACCT_DT DESC  limit 3";
	$result=sql_query($s_query);
	for ($i=0; $row=sql_fetch_array($result); $i++) {
		//echo $row[ACCT_DT]." ".$row[FUNDTOTAL]."<br>";
		$year=substr($row['ACCT_DT'],0,4);
		$PROFIT[$year]=$row['TERMNETPROFIT'];
		$FTOTAL[]=$row['FUNDTOTAL'];
		$SALESY[$year]=$row['SALES'];
	}

if($minfo[1]['title']=="프리미엄" or $minfo[1]['title']=="프리미엄+"){ 
	//if(1){ //임시 처리
	#주식수
	$sto_info=sql_fetch("select * from company_stock where KEDCD='{$cd}' order by STD_DT DESC limit 1");

	if(count($PROFIT)>1 && $sto_info['ISS_STK_CN']){  //3년도 정보가 있어야 구함


	#자산가치 자본총계(천원) / 총주식
	$asset_value = ($FTOTAL[0]*1000) / $sto_info['ISS_STK_CN'];
	//echo "#자산가치 : 자본총계(천원) / 총주식 : ($FTOTAL[0]*1000) / {$sto_info[ISS_STK_CN]} = ".$asset_value."<br>";
	#주당순이익 구하기 당기순이익/주식수
	foreach($PROFIT as $key=>$value){
		$EPS[$key]=($value*1000)/$sto_info['ISS_STK_CN'];
		//echo "#주당순이익 : ({$value}*1000)/{$sto_info[ISS_STK_CN]}=".$EPS[$key]."<br>";
	}
	#수익가치
	$pv_total=0;
	foreach($EPS as $key=>$value){
		$x++;
		
		if($x==1) $PV[$key]=$value*3;
		if($x==2) $PV[$key]=$value*2;
		if($x==3) $PV[$key]=$value*1;
		$pv_total=$pv_total+$PV[$key];
	}
	//echo "#수익가치:".$pv_total."<br>";
	#주당순손익액
	$pv2= $pv_total / 6;
	//echo "#주당순손익액:".$pv2."<br>";
	# 1주당 순손익가치 (환원율 10%)
	$pv2= ($pv_total / 6)/0.1;
	//echo "1주당 순손익가치 (환원율 10%):".$pv2."<br>";
	#주당 주식가치 (자산가치*2 + 수익가치*3)/5
	

	$com_av=$asset_value*0.8;
	$svps=(($asset_value*2)+($pv2*3))/5;
	if($svps<$com_av) $svps=$com_av;

	/*
	1) #주당 주식가치
	2) #자산가치 * 0.8
	3) 1)과 2) 중 더 큰 값 = 비상장주식가치
	*/
	//echo "#주당 주식가치 (자산가치*2 + 수익가치*3)/5=".$svps."<br>";
	//echo "#주당 주식가치 (($asset_value*2)+($pv2*3))/5=".$svps."<br>";
	

	}else{
		$warn_text="직전 3개년도 재무정보 또는 주식정보가 정확하지 않아서 확인이 어렵습니다.";
	}
}

if($com['HPAGE_URL']) {
	if(!strpos($com['HPAGE_URL'],"http")) $com['HPAGE_URL_LINK']="http://".$com['HPAGE_URL'];
	else $com['HPAGE_URL_LINK']=$com['HPAGE_URL'];
}
?>

<style>
    #pageTop{display: none}
	#CompanyListTable tbody tr:nth-child(n+6) {display:none; }
</style>

<div id="matching_list" class="">
    <div class="myCompanyInfo">
        <h4 class="myCompanyTitle fs35 lh1 c111 fw550 mgb3">내 회사 정보</h4>

       <div class="viewCompanyDesc myCompanyDesc">
            <ul>
                <li>
                    <div class="title">기업명</div>
                    <div class="text">
					<?if($com['ENP_NM_TRD']){?>
						<?=$com['ENP_NM_TRD']?>
					<?}else{?>
						<?=$com['ENP_NM']?>
					<?}?>
					</div>
                </li>
				<?/*
                <li>
                    <div class="title">영문기업명</div>
                    <div class="text">
					<?=$com['ENP_NM_ENG']?>
					</div>
                </li>
				*/
				?>
                <li>
                    <div class="title">법인(등록)번호</div>
                    <div class="text">
					<?=$com['CONO_PID']?>
					</div>
                </li>
                <li>
                    <div class="title">기업규모</div>
                    <div class="text">
					<?=$ENP_SZE[$com['ENP_SZE']]?>
					</div>
                </li>
				<?if($com['HPAGE_URL']){?>
                <li>
                    <div class="title">홈페이지</div>
                    <div class="text">
					<?if($com['HPAGE_URL']){?><a href="<?=$com['HPAGE_URL_LINK']?>" target="_blank"><?=$com['HPAGE_URL']?></a><?}?>
					</div>
                </li>
				<?}?>
                <li>
                    <div class="title">주요제품</div>
                    <div class="text">
					<?=$com['PD_NM']?>
					</div>
                </li>
                <li>
                    <div class="title">사업자번호</div>
                    <div class="text">
						<?=$bzno_c?>
					</div>
                </li>
                <li>
                    <div class="title">설립일자</div>
                    <div class="text">
						<?=$com['ESTB_DT']?>
					</div>
                </li>
				<?if($com['LABORER_SUM']){?>
                <li>
                    <div class="title">종업원수</div>
                    <div class="text"><?if($com['LABORER_SUM']){?><?=$com['LABORER_SUM']?>명<?}?></div>
                </li>
				<?}?>
                <li>
                    <div class="title">기업유형</div>
                    <div class="text">
						<?if($enp_typt[$com['ENP_TYP']]){?><?=$enp_typt[$com['ENP_TYP']]?><?}?>
						<?if($enp_fcdt[$com['ENP_FCD']]){?> / <?=$enp_fcdt[$com['ENP_FCD']]?><?}?>
					</div>
                </li>
                <li>
                    <div class="title">주소</div>
                    <div class="text">
						<?=$com['LOC_ADDRA']?> <?=$com['LOC_ADDRB']?>
					</div>
                </li>
				<?if($com['KSIC9_BZC_CD']){?>
                <li>
                    <div class="title">표준산업분류(9차)</div>
                    <div class="text">
					<?if($com['KSIC9_BZC_CD']){?>(<?=$com['KSIC9_BZC_CD']?>) <?=$kssc_9['k_code_name']?><?}?>
					</div>
                </li>
				<?}?>
                <li>
                    <div class="title">표준산업분류</div>
                    <div class="text"><?if($com['BZC_CD']){?>(<?=$com['BZC_CD']?>) <?=$kssc['k_code_name']?><?}?></div>
                </li>
                <li>
                    <div class="title">전화번호</div>
                    <div class="text">
						<?if($view_chk=="true"){?>
							<?if($com['TEL_NO']){?><?=$com['TEL_NO']?><?}?>
						<?}else{?>
							<?=$tel[0]?>-<?=substr($tel[1],0,1)?>***-<?=substr($tel[2],0,1)?>***
						<?}?>
					</div>
                </li>
				<?
				$yearsm=date("Y")-1; //직전년도 매출액
				if($SALESY[$yearsm]){?>
                <li>
                    <div class="title">매출액 (천원)</div>
                    <div class="text"><?=number_format($SALESY[$yearsm])?></div>
                </li>
				<?}?>
            </ul>
        </div>
    </div>

<?if($svps or $warn_text){?>
	<h4 class="barTitle">비상장주식가치</h4>
	<div class="viewCompanyDesc myCompanyDesc">
            <ul>
                <li>
                    <div class="text">
					<?if($warn_text){?>
						<?echo $warn_text;?>
					<?}else{?>
						<?=number_format((int)$svps)?> / 주
					<?}?>
					</div>
                </li>
			</ul>
	</div>
	
	<p>* 매커멕스에서 제공하는 비상장주식가치 평가금액은 한국평가데이타(KoData)재무제표 정보를 활용하여  약식 평가한 금액으로서   종합적인 세무자료를 이용한 정확한 평가금액이  아닙니다. 한국평가데이타에 자료업데이트가 안되어 있을 경우 평가금액의  오차범위가 다소 발생할수 있으며 또한 3년간 계속 영업을 가정한 것으로 복잡한 구조의 회사 평가에는 적용할 수 없습니다. 참고용으로만 활용하시기 바랍니다
따라서 회사는 평가금액에 대한 법률적 책임은 지지 않습니다</p>
<?}else{?>
	<h4 class="barTitle">비상장주식가치</h4>
	<div class="viewCompanyDesc myCompanyDesc blur">
            <ul>
                <li>
                    <div class="text">
					123,456 / 주  (맴버쉽에 가입하세요)
					</div>
                </li>
			</ul>
	</div>
	<p>* 매커멕스에서 제공하는 비상장주식가치 평가금액은 한국평가데이타(KoData)재무제표 정보를 활용하여  약식 평가한 금액으로서   종합적인 세무자료를 이용한 정확한 평가금액이  아닙니다. 한국평가데이타에 자료업데이트가 안되어 있을 경우 평가금액의  오차범위가 다소 발생할수 있으며 또한 3년간 계속 영업을 가정한 것으로 복잡한 구조의 회사 평가에는 적용할 수 없습니다. 참고용으로만 활용하시기 바랍니다
따라서 회사는 평가금액에 대한 법률적 책임은 지지 않습니다</p>
<?}?>
    </div>
    

</div>
<script>
<?if($msinfo['membership_parent_no']>"1"){?>
vtotal=<?=max($cust_cnt,$supp_cnt)?>;
v_len=$("#CompanyListTable tbody tr:visible").length;
$(".searchCompanyListLoad .length").text("("+(vtotal-v_len)+")");
$(".searchCompanyListLoad").click(function(){
	v_len=$("#CompanyListTable tbody tr:visible").length;
	add=v_len+5;
	$("#CompanyListTable tbody tr:lt("+add+")").show();
	v_len=$("#CompanyListTable tbody tr:visible").length;
	$(".searchCompanyListLoad .length").text("("+(vtotal-v_len)+")");
})
<?}else{?>
$(".searchCompanyListLoad").click(function(){
	jAlert("맴버쉽 요금제를 가입하면 서비스를 이용하실 수 있습니다.","알림");
})
<?}?>
</script>
<?
include_once(G5_THEME_PATH.'/tail.php');
?>