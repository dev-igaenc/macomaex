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
if($com['BZC_CD']) $kssc=sql_fetch("SELECT * from kssc_11 where k_code='{$com['BZC_CD']}'");

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
	#pageTop,
	#sVisual{display: none}
</style>
<div id="sub-visual"><div class="inConts">
	<h2>사업자번호 입력만으로,<br />맞춤형 기업 매칭을 경험하세요.</h2>

	<div search shadow>
		<form action="/ko/matching_list.php" method="POST" id="match">
			<input type="hidden" name="cominfo" id="cominfo">
		</form>
		<input type="text" name="comnum" id="comnum" class="comnum_chk" placeholder="법인 사업자번호를 입력하세요" maxlength="12">
		<button type="submit" id="sub_submit"><img src="/images/icon_search.svg" alt="search"></button>
	</div>
</div></div>

<div class="company-view" no my><div class="layer-in new">
	<dl class="h4-title">
		<dt><span><img src="/images/icon_myCompany.png" alt="company"></span><strong>내 회사 정보</strong></dt>
		<dd><a href="/bbs/write.php?bo_table=Contact" btn shadow>데이터제보/오류알리기</a></dd>
	</dl>
	<div class="cv-info-area">
		<div class="cv-info-re normal">
			<p class="cv-tit">기본정보</p>
			<div class="cv-info-conts">
				<dl>
					<dt>기업명</dt>
					<dd><span>
					<?if($com['ENP_NM_TRD']){?>
						<?=$com['ENP_NM_TRD']?>
					<?}else{?>
						<?=$com['ENP_NM']?>
					<?}?>
					</span></dd>
				</dl>
				<dl>
					<dt>설립일자</dt>
					<dd><span><?=$com['ESTB_DT']?></span></dd>
				</dl>

				<dl>
					<dt>법인(주민)번호</dt>
					<dd><span><?=substr($com['CONO_PID'],0,6)?>-<?=substr($com['CONO_PID'],6,7)?></span></dd>
				</dl>
				<dl>
					<dt>사업자번호</dt>
					<dd><span><?=$bzno_c?></span></dd>
				</dl>

				<dl>
					<dt>고용인원</dt>
					<dd><span><?if($com['LABORER_SUM']){?><?=$com['LABORER_SUM']?>명<?}?></span></dd>
				</dl>
				<dl>
					<dt>기업규모</dt>
					<dd><span><?=$ENP_SZE[$com['ENP_SZE']]?></span></dd>
				</dl>
			</div>
		</div>
		<div class="cv-info-re business">
			<p class="cv-tit">사업/산업</p>
			<div class="cv-info-conts">
				<dl>
					<dt>기업유형</dt>
					<dd>
						<?if($enp_typt[$com['ENP_TYP']]){?><?=$enp_typt[$com['ENP_TYP']]?><?}?>
						<?if($enp_fcdt[$com['ENP_FCD']]){?> / <?=$enp_fcdt[$com['ENP_FCD']]?><?}?>
					</dd>
				</dl>
				<dl>
					<dt>주요제품</dt>
					<dd><?=$com['PD_NM']?></dd>
				</dl>

				<dl>
					<dt>표준산업분류(11차)</dt>
					<dd><?if($com['BZC_CD']){?>(<?=$com['BZC_CD']?>) <?=$kssc['k_code_name']?><?}?></dd>
				</dl>

				<dl>
					<dt>직전연도 매출액 (천원)</dt>
					<dd>
<?
$yearsm=date("Y")-1; //직전년도 매출액
if($SALESY[$yearsm]){?>
					<?=number_format($SALESY[$yearsm])?>
<?}?>
					</dd>
				</dl>
			</div>
		</div>
		<div class="cv-info-re contact">
			<p class="cv-tit">연락방법/위치</p>
			<div class="cv-info-conts" colums>
				<dl>
					<dt>전화번호</dt>
					<dd>
						<?if($view_chk=="true"){?>
							<?if($com['TEL_NO']){?><a href="tel:<?=$com['TEL_NO']?>"><?=$com['TEL_NO']?></a><?}?>
						<?}else{?>
							<a><?=$tel[0]?>-<?=substr($tel[1],0,1)?>***-<?=substr($tel[2],0,1)?>***</a>
						<?}?>
					</dd>
				</dl>
				<dl>
					<dt>주소(도로명)</dt>
					<dd><a href="https://map.kakao.com/?q=<?=$com['LOC_RDNM_ADDRA']?> <?=$com['LOC_RDNM_ADDRB']?>" target="_blank"><?=$com['LOC_RDNM_ADDRA']?><br /><?=$com['LOC_RDNM_ADDRB']?></a>	
</dd>
				</dl>
			</div>
		</div>
	</div>

	<!--[s] 비상장사일 때 -->
	<div class="ca-info-my-be">
<?if($svps or $warn_text){?>
		<div class="ca-info-my-re" front shadow>
			<p class="title">우리회사 비상장주식 가치</p>
			<div class="ju">
				<a href="javascript:void(0);" class="blurs" data-text="(클릭시공개)" shadow><strong>12,129원/주</strong></a>
				<a href="javascript:void(0);" class="btns">
					<span><img src="/images/icon_search.png" alt="search"></span>
					<strong>평가 결과 보기</strong>
					<span><img src="/images/btn_arr2.png" alt="arr"></span>
				</a>
			</div>
			<p class="text">
				본 수치는 KoData 재무 데이터를 기반으로 산출된 추정값으로, 기업의 고유 특성이나 정보 갱신 주기에 따라 변동 가능성이 있습니다. 정확한 평가가 필요하신 경우, 전문가와의 상담을 권장드립니다.
			</p>
		</div>
		<div class="ca-info-my-re" back shadow>
			<p class="title">우리회사 비상장주식 가치</p>
			<div class="ju">
				<div class="blurs" shadow>
					<strong>
					<?if($warn_text){?>
						<?echo $warn_text;?>
					<?}else{?>
						<?=number_format((int)$svps)?> / 주
					<?}?>
					</strong>
					<p>직전년도 매출액 <?=number_format($SALESY[$yearsm]*1000)?>원</p>
				</div>
				<a href="javascript:void(0);" class="btns">
					<span><img src="/images/icon_search.png" alt="search"></span>
					<strong>평가 결과 보기</strong>
					<span><img src="/images/btn_arr2.png" alt="arr"></span>
				</a>
			</div>
			<p class="tit">정밀한 평가와 전략적 접근이 필요하다면?</p>
			<p class="text">
				메커멕스의 전문 컨설턴트와 상담해보세요. 재무 분석은 물론, 업종 특성, 시장 흐름, 유사 기업 비교까지. 귀사의 가치를 객관적으로 진단해드리고, 미래 방향성을 제안해드립니다.
			</p>
		</div>
<?}else{?>
		<div class="ca-info-my-re" front shadow  chk="false">
			<p class="title">우리회사 비상장주식 가치</p>
			<div class="ju">
				<a href="javascript:void(0);" class="blurs" data-text="(클릭시공개)" shadow><strong>12,129원/주</strong></a>
				<a href="javascript:void(0);" class="btns">
					<span><img src="/images/icon_search.png" alt="search"></span>
					<strong>평가 결과 보기</strong>
					<span><img src="/images/btn_arr2.png" alt="arr"></span>
				</a>
			</div>
			<p class="text">
				본 수치는 KoData 재무 데이터를 기반으로 산출된 추정값으로, 기업의 고유 특성이나 정보 갱신 주기에 따라 변동 가능성이 있습니다. 정확한 평가가 필요하신 경우, 전문가와의 상담을 권장드립니다.
			</p>
		</div>
		<div class="ca-info-my-re" back shadow>
			<p class="title">우리회사 비상장주식 가치</p>
			<div class="ju">
				<div class="blurs" shadow>
					<strong>
					1,234,567/주
					
					</strong>
					<p>직전년도 매출액 100,000,000원</p>
				</div>
				<a href="javascript:void(0);" class="btns">
					<span><img src="/images/icon_search.png" alt="search"></span>
					<strong>평가 결과 보기</strong>
					<span><img src="/images/btn_arr2.png" alt="arr"></span>
				</a>
			</div>
			<p class="tit">정밀한 평가와 전략적 접근이 필요하다면?</p>
			<p class="text">
				메커멕스의 전문 컨설턴트와 상담해보세요. 재무 분석은 물론, 업종 특성, 시장 흐름, 유사 기업 비교까지. 귀사의 가치를 객관적으로 진단해드리고, 미래 방향성을 제안해드립니다.
			</p>
		</div>
<?}?>
	</div>
	<!--[e] 비상장사일 때 -->


</div></div>

<script type="text/javascript">
$("#sub_submit").click(function(){
	event.preventDefault();
	comnum=$(this).prev().val();
	if(comnum.length!=12){
		jAlert("사업자등록번호를 확인해 주세요.","알림",function(){
			$("#comnum").focus();
		})
			return false;
	}
		
	$.post( "../get_set.php", {
		type: 'main_comp_chk',
		comnum:comnum
	})
	.done(function(jqXHR) {
			jqXHR=jqXHR.split("|");
			if(jqXHR[0]==1000){
				//완료시
				//jAlert(jqXHR[1],"알림");
				$("#cominfo").val(jqXHR[1]);
				$("#match").submit();
			}else{
				//오류시
				if(jqXHR[1]=="02") jAlert("검색 결과가 없습니다.","알림");
				else jAlert("검색에 오류가 있습니다.","알림");
			}
		
	});



})
//사업자번호
	$('.comnum_chk').on('keyup', function(){
        var num = $(this).val();
        num.trim();  // 스페이스바 제거
        this.value = AutoHypen(num) ;
    });

function AutoHypen(companyNum){
      companyNum = companyNum.replace(/[^0-9]/g, '');
      var tempNum = '';   

      if(companyNum.length < 4){
        return companyNum;
      }
      else if(companyNum.length < 6){
        tempNum += companyNum.substr(0,3);
        tempNum += '-';
        tempNum += companyNum.substr(3,2);
        return tempNum;
      }
      else if(companyNum.length < 11){
        tempNum += companyNum.substr(0,3);
        tempNum += '-';
        tempNum += companyNum.substr(3,2);
        tempNum += '-';
        tempNum += companyNum.substr(5);
        return tempNum;
      }
      else{        
        tempNum += companyNum.substr(0,3);
        tempNum += '-';
        tempNum += companyNum.substr(3,2);
        tempNum += '-';
        tempNum += companyNum.substr(5);
        return tempNum;
      }
    }

	//	비상장
	$("body").on("click", ".ca-info-my-be .ca-info-my-re[front]", function(){ //
		chk=$(this).attr("chk");
		if(chk=="false") {
			<?if($warn_text){?>
				jAlert("<?=$warn_text?>","알림");
			<?}else{?>
			jAlert("맴버쉽을 업그레이드 하세요.","알림",function(){
				location.href='/ko/plan.php';
			});
			<?}?>
			return false;
		}

		if(!$(this).closest(".ca-info-my-be").hasClass("active")){
			$(this).closest(".ca-info-my-be").addClass("active")
		}else{
			$(this).closest(".ca-info-my-be").removeClass("active");
		}
	});

	//상장
	$("body").on("click", ".ca-info-my .ca-info-my-re[front]", function(){ //
		chk=$(this).attr("chk");
		if(chk=="false") {
			<?if($warn_text){?>
				jAlert("<?=$warn_text?>","알림");
			<?}else{?>
			jAlert("맴버쉽을 업그레이드 하세요.","알림",function(){
				location.href='/ko/plan.php';
			});
			<?}?>
			return false;
		}

		if(!$(this).closest(".ca-info-my").hasClass("active")){
			$(this).closest(".ca-info-my").addClass("active")
		}else{
			$(this).closest(".ca-info-my").removeClass("active");
		}
	});
</script>

<?
include_once(G5_THEME_PATH.'/tail.php');
?>
