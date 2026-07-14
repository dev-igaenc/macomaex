<?php 
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

$return_url = trim((string)($_GET['return_url'] ?? ''));
if ($return_url !== '') {
	$return_path = parse_url($return_url, PHP_URL_PATH);
	if ($return_path === '/account/trades' && strpos($return_url, '//') !== 0) {
		set_session('ss_my_company_connect_return_url', $return_url);
	}
}

$cd=trim((string)$member['mb_company']);
$com=array();
$major_service=array();
if($cd && !preg_match('/^M[0-9]{9}$/', $cd)){
	$cd_sql=sql_escape_string($cd);
	$com=sql_fetch("SELECT * from company where KEDCD='{$cd_sql}'");
	if(!empty($com['KEDCD'])) $major_service=sql_fetch("SELECT * from g5_member where mb_company='{$cd_sql}'");
}
$has_linked_company=!empty($com['KEDCD']);

include_once(G5_THEME_PATH.'/head.php');

if(!$has_linked_company){
	$my_company_connect_token=bin2hex(random_bytes(24));
	set_session('ss_my_company_connect_token', $my_company_connect_token);
	include_once(G5_PATH.'/ko/my_company_onboarding.php');
	include_once(G5_THEME_PATH.'/tail.php');
	exit;
}

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
if(!is_array($kssc)) $kssc = array();

// 비상장 주식가치 최초 조회 크레딧/언락 상태
$unlisted_credit_cost = (defined('_unlisted_value_credit_cost') && (int)_unlisted_value_credit_cost > 0) ? (int)_unlisted_value_credit_cost : 20;
$unlisted_service_code = 'UNLISTED_VALUE';
$unlisted_unlocked = false;
$unlisted_unlock_token = uniqid('', true);
set_session('ss_unlisted_value_token', $unlisted_unlock_token);
if($member['mb_id'] && $cd){
	$uid_sql = sql_real_escape_string(trim((string)$member['mb_id']));
	$service_code_sql = sql_real_escape_string($unlisted_service_code);
	$unlisted_once_key = hash('sha256', $unlisted_service_code.':'.$member['mb_id'].':'.$cd);
	$unlisted_once_key_sql = sql_real_escape_string($unlisted_once_key);
	$unlock_row = sql_fetch("select id from credit_history where uid='{$uid_sql}' and tx_type='USE' and service_code='{$service_code_sql}' and once_key='{$unlisted_once_key_sql}' limit 1");
	if(is_array($unlock_row) && !empty($unlock_row['id'])) $unlisted_unlocked = true;
}

// 주식정보
#당기순이익(3년간) 단위 천원
$PROFIT=array();
$FTOTAL=array();
$SALESY=array();
$svps = 0;
$warn_text = '';
$s_query="select * from company_financial where KEDCD='{$cd}' order by ACCT_DT DESC  limit 3";
$result=sql_query($s_query);
for ($i=0; $row=sql_fetch_array($result); $i++) {
	$year=substr($row['ACCT_DT'],0,4);
	$PROFIT[$year]=$row['TERMNETPROFIT'];
	$FTOTAL[]=$row['FUNDTOTAL'];
	$SALESY[$year]=$row['SALES'];
}

#주식수
$sto_info=sql_fetch("select * from company_stock where KEDCD='{$cd}' order by STD_DT DESC limit 1");

if($unlisted_unlocked && count($PROFIT)>1 && $sto_info['ISS_STK_CN']){  //3년도 정보가 있어야 구함
	#자산가치 자본총계(천원) / 총주식
	$asset_value = ($FTOTAL[0]*1000) / $sto_info['ISS_STK_CN'];
	#주당순이익 구하기 당기순이익/주식수
	foreach($PROFIT as $key=>$value){
		$EPS[$key]=($value*1000)/$sto_info['ISS_STK_CN'];
	}
	#수익가치
	$pv_total=0;
	$x=0;
	foreach($EPS as $key=>$value){
		$x++;

		if($x==1) $PV[$key]=$value*3;
		if($x==2) $PV[$key]=$value*2;
		if($x==3) $PV[$key]=$value*1;
		$pv_total=$pv_total+$PV[$key];
	}
	#주당순손익액
	$pv2= $pv_total / 6;
	# 1주당 순손익가치 (환원율 10%)
	$pv2= ($pv_total / 6)/0.1;
	#주당 주식가치 (자산가치*2 + 수익가치*3)/5
	$com_av=$asset_value*0.8;
	$svps=(($asset_value*2)+($pv2*3))/5;
	if($svps<$com_av) $svps=$com_av;
}else if($unlisted_unlocked){
	$warn_text="직전 3개년도 재무정보 또는 주식정보가 정확하지 않아서 확인이 어렵습니다.";
}

if($com['HPAGE_URL']) {
	if(!strpos($com['HPAGE_URL'],"http")) $com['HPAGE_URL_LINK']="http://".$com['HPAGE_URL'];
	else $com['HPAGE_URL_LINK']=$com['HPAGE_URL'];
}
?>

<style>
	#pageTop,
	#sVisual{display: none}
	.company-view .ca-info-my-be .ju>.btns .btn-copy{
		display:flex;
		align-items:baseline;
		gap:0.8rem;
		white-space:nowrap;
	}
	.company-view .ca-info-my-be .ju>.btns .btn-copy .main{
		font-weight:600;
		font-size:3rem;
		line-height:1.3;
		margin:0;
	}
	.company-view .ca-info-my-be .ju>.btns .btn-copy em{
		font-style:normal;
		font-weight:500;
		font-size:1.8rem;
		line-height:1;
		opacity:0.92;
	}
</style>
<div id="sub-visual"><div class="inConts">
	<h2>사업자번호 입력만으로,<br />맞춤형 기업 매칭을 경험하세요.</h2>

	<!--
	<div search shadow>
		<form action="/ko/matching_list.php" method="POST" id="match">
			<input type="hidden" name="cominfo" id="cominfo">
		</form>
		<input type="text" name="comnum" id="comnum" class="comnum_chk" placeholder="법인 사업자번호를 입력하세요" maxlength="12">
		<button type="submit" id="sub_submit"><img src="/images/icon_search.svg" alt="search"></button>
	</div>
	-->
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
					<?php if($com['ENP_NM_TRD']){?>
						<?=$com['ENP_NM_TRD']?>
					<?php }else{?>
						<?=$com['ENP_NM']?>
					<?php }?>
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
					<dd><span><?php if($com['LABORER_SUM']){?><?=$com['LABORER_SUM']?>명<?php }?></span></dd>
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
						<?php if($enp_typt[$com['ENP_TYP']]){?><?=$enp_typt[$com['ENP_TYP']]?><?php }?>
						<?php if($enp_fcdt[$com['ENP_FCD']]){?> / <?=$enp_fcdt[$com['ENP_FCD']]?><?php }?>
					</dd>
				</dl>
				<dl>
					<dt>주요제품</dt>
					<dd><?=$com['PD_NM']?></dd>
				</dl>

					<dl>
						<dt>표준산업분류(11차)</dt>
							<dd><?php if($com['BZC_CD']){?>(<?=$com['BZC_CD']?>) <?=isset($kssc['k_code_name']) ? $kssc['k_code_name'] : ''?><?php }?></dd>
					</dl>

				<dl>
					<dt>직전연도 매출액 (천원)</dt>
					<dd>
<?php 
$yearsm=date("Y")-1; //직전년도 매출액
$latest_sales = 0;
if(isset($SALESY[$yearsm]) && $SALESY[$yearsm] !== ''){
	$latest_sales = $SALESY[$yearsm];
}else if(!empty($SALESY)){
	$tmp_sales = reset($SALESY);
	if($tmp_sales !== false && $tmp_sales !== null && $tmp_sales !== ''){
		$latest_sales = $tmp_sales;
	}
}
?>
						<?=number_format($latest_sales)?>
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
						<?php if($view_chk=="true"){?>
							<?php if($com['TEL_NO']){?><a href="tel:<?=$com['TEL_NO']?>"><?=$com['TEL_NO']?></a><?php }?>
						<?php }else{?>
							<a><?=$tel[0]?>-<?=substr($tel[1],0,1)?>***-<?=substr($tel[2],0,1)?>***</a>
						<?php }?>
					</dd>
				</dl>
				<dl>
				<?php if($com['LOC_RDNM_ADDRA']){?>
					<dt>주소(도로명)</dt>
					<dd><a href="https://map.kakao.com/?q=<?=$com['LOC_RDNM_ADDRA']?> <?=$com['LOC_RDNM_ADDRB']?>" target="_blank"><?=$com['LOC_RDNM_ADDRA']?><br /><?=$com['LOC_RDNM_ADDRB']?></a>
					</dd>
				<?php }?>
				</dl>
				<dl>
				<?php if($com['LOC_ADDRA']){?>
					<dt>주소</dt>
					<dd><a href="https://map.kakao.com/?q=<?=$com['LOC_ADDRA']?> <?=$com['LOC_ADDRB']?>" target="_blank"><?=$com['LOC_ADDRA']?><br /><?=$com['LOC_ADDRB']?></a>
					</dd>
				<?php }?>
				</dl>
			</div>
		</div>
	</div>

	<!--[s] 비상장사일 때 -->
	<div class="ca-info-my-be">
<?php if($unlisted_unlocked){?>
		<div class="ca-info-my-re" front shadow chk="<?php if($unlisted_unlocked){?>true<?php }else{?>false<?php }?>" data-unlocked="<?php if($unlisted_unlocked){?>1<?php }else{?>0<?php }?>" data-kedcd="<?=$cd?>" data-credit-cost="<?=$unlisted_credit_cost?>" data-token="<?=$unlisted_unlock_token?>" data-warn="<?php if($warn_text){?>1<?php }else{?>0<?php }?>">
			<p class="title">우리회사 비상장주식 가치</p>
			<div class="ju">
				<a href="javascript:void(0);" class="blurs" data-text="(클릭시공개)" shadow><strong>12,129원/주</strong></a>
				<a href="javascript:void(0);" class="btns">
					<span><img src="/images/icon_search.png" alt="search"></span>
					<span class="btn-copy">
						<span class="main">평가 결과 보기</span>
						<?php if(!$unlisted_unlocked){?><em>(<?=$unlisted_credit_cost?> 크레딧)</em><?php }?>
					</span>
					<span><img src="/images/btn_arr2.png" alt="arr"></span>
				</a>
			</div>
			<p class="text">
				본 수치는 KoData 재무 데이터를 기반으로 산출된 추정값으로, 기업의 고유 특성이나 정보 갱신 주기에 따라 변동 가능성이 있습니다. 가치 공개는 최초 1회만 <?=$unlisted_credit_cost?>크레딧이 사용되며, 이후 재조회는 무료입니다.
			</p>
		</div>
		<div class="ca-info-my-re" back shadow>
			<p class="title">우리회사 비상장주식 가치</p>
			<div class="ju">
				<div class="blurs" shadow>
					<strong>
					<?php if($warn_text){?>
						<?php echo $warn_text;?>
					<?php }else{?>
						<?=number_format((int)$svps)?> / 주
					<?php }?>
					</strong>
					<?php /*<p>직전년도 매출액 <?=number_format($SALESY[$yearsm]*1000)?>원</p>*/?>
				</div>
					<a href="javascript:void(0);" class="btns">
						<span><img src="/images/icon_search.png" alt="search"></span>
						<span class="btn-copy">
							<span class="main">평가 결과 보기</span>
							<?php if(!$unlisted_unlocked){?><em>(<?=$unlisted_credit_cost?> 크레딧)</em><?php }?>
						</span>
						<span><img src="/images/btn_arr2.png" alt="arr"></span>
					</a>
			</div>
			<p class="tit">정밀한 평가와 전략적 접근이 필요하다면?</p>
			<p class="text">
				메커멕스의 전문 컨설턴트와 상담해보세요. 재무 분석은 물론, 업종 특성, 시장 흐름, 유사 기업 비교까지. 귀사의 가치를 객관적으로 진단해드리고, 미래 방향성을 제안해드립니다.
			</p>
		</div>
<?php }else{?>
		<div class="ca-info-my-re" front shadow chk="false" data-unlocked="0" data-kedcd="<?=$cd?>" data-credit-cost="<?=$unlisted_credit_cost?>" data-token="<?=$unlisted_unlock_token?>" data-warn="1">
			<p class="title">우리회사 비상장주식 가치</p>
			<div class="ju">
				<a href="javascript:void(0);" class="blurs" data-text="(클릭시공개)" shadow><strong>12,129원/주</strong></a>
				<a href="javascript:void(0);" class="btns">
					<span><img src="/images/icon_search.png" alt="search"></span>
					<span class="btn-copy">
						<span class="main">평가 결과 보기</span>
						<em>(<?=$unlisted_credit_cost?> 크레딧)</em>
					</span>
					<span><img src="/images/btn_arr2.png" alt="arr"></span>
				</a>
			</div>
			<p class="text">
				본 수치는 KoData 재무 데이터를 기반으로 산출된 추정값으로, 기업의 고유 특성이나 정보 갱신 주기에 따라 변동 가능성이 있습니다. 가치 공개는 최초 1회만 <?=$unlisted_credit_cost?>크레딧이 사용되며, 이후 재조회는 무료입니다.
			</p>
		</div>
		<div class="ca-info-my-re" back shadow>
			<p class="title">우리회사 비상장주식 가치</p>
			<div class="ju">
					<div class="blurs" shadow>
						<strong>
						가치 공개 후 확인 가능
						</strong>
					</div>
					<a href="javascript:void(0);" class="btns">
						<span><img src="/images/icon_search.png" alt="search"></span>
						<span class="btn-copy">
						<span class="main">평가 결과 보기</span>
						<em>(<?=$unlisted_credit_cost?> 크레딧)</em>
					</span>
					<span><img src="/images/btn_arr2.png" alt="arr"></span>
				</a>
			</div>
			<p class="tit">정밀한 평가와 전략적 접근이 필요하다면?</p>
			<p class="text">
				메커멕스의 전문 컨설턴트와 상담해보세요. 재무 분석은 물론, 업종 특성, 시장 흐름, 유사 기업 비교까지. 귀사의 가치를 객관적으로 진단해드리고, 미래 방향성을 제안해드립니다.
			</p>
		</div>
<?php }?>
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

	function toggleUnlistedCard($front){
		var $box = $front.closest(".ca-info-my-be");
		if(!$box.length) $box = $front.closest(".ca-info-my");
		if(!$box.length) return;
		if(!$box.hasClass("active")) $box.addClass("active");
		else $box.removeClass("active");
	}

	function applyUnlistedReveal($front, svpsRaw, warnEncoded){
		var $box = $front.closest(".ca-info-my-be");
		if(!$box.length) $box = $front.closest(".ca-info-my");
		if(!$box.length) return;

		var warnText = "";
		if(warnEncoded){
			try{
				warnText = decodeURIComponent(String(warnEncoded));
			}catch(e){
				warnText = String(warnEncoded);
			}
		}

		var $strong = $box.find(".ca-info-my-re[back] .blurs strong").first();
		if($strong.length){
			if(warnText){
				$strong.text(warnText);
			}else{
				var svpsInt = parseInt(svpsRaw, 10);
				if(!isNaN(svpsInt)) $strong.text(svpsInt.toLocaleString() + " / 주");
				else $strong.text("가치 공개 후 확인 가능");
			}
		}

		$front.attr("chk","true");
		$front.attr("data-unlocked","1");
		$front.data("unlocked", 1);
		$box.find(".ca-info-my-re[front]").attr("data-unlocked","1").data("unlocked", 1);
		$box.find(".btn-copy em").remove();
	}

	$("body").on("click", ".ca-info-my-be .ca-info-my-re[front], .ca-info-my .ca-info-my-re[front]", function(){
		var $front = $(this);
		var unlocked = String($front.data("unlocked")) === "1";
		var kedcd = $front.data("kedcd");
		var unlockToken = String($front.data("token") || "");
		var creditCost = parseInt($front.data("credit-cost"), 10);
		if(!creditCost || creditCost < 1) creditCost = <?= (int)$unlisted_credit_cost ?>;

		if(unlocked){
			toggleUnlistedCard($front);
			return false;
		}

		if(!kedcd){
			jAlert("회사 정보를 확인할 수 없어 처리하지 못했습니다.","알림");
			return false;
		}

			var unlockAction = function(){
				$.post("../get_set.php", {type:'unlisted_value_unlock', kedcd:kedcd, unlock_token:unlockToken}, function(data){
					// PHP warning/notice가 앞에 섞여도 상태코드를 안정적으로 파싱한다.
					var parsePipeResponse = function(raw){
						var text = String(raw || "");
						text = text.replace(/^\uFEFF/, "").trim();
						var codeIdx = text.search(/(?:1000|500|429|501)\|/);
						if(codeIdx > 0) text = text.substring(codeIdx);
						text = text.replace(/<[^>]*>/g, "").trim();
						return text.split("|");
					};
					var jqXHR = parsePipeResponse(data);
					if(jqXHR[0] == "1000"){
					applyUnlistedReveal($front, jqXHR[4], jqXHR[5]);
					if(jqXHR[1] == "charged"){
						jAlert("가치 공개로 " + creditCost + "크레딧이 사용되었습니다.","알림",function(){
							toggleUnlistedCard($front);
						});
					}else{
						toggleUnlistedCard($front);
					}
						}else{
								var msg = jqXHR[1] ? jqXHR[1] : "처리 중 오류가 발생했습니다.";
								if(jqXHR[2] == "PLAN"){
									if (typeof window.openCreditShortageGuide === "function") {
										window.openCreditShortageGuide({
											text: msg,
											text2: "크레딧 충전 후 다시 가치 조회를 진행해 주세요."
										});
									} else if (typeof window.openAiGuideModal === "function") {
										window.openAiGuideModal({
											title: "크레딧 부족 안내",
											text: msg,
											text2: "크레딧 충전 후 다시 가치 조회를 진행해 주세요.",
											okText: "닫기",
											okVariant: "outline",
											secondaryText: "크레딧 충전하러 가기",
											secondaryHref: "/plans"
										});
									} else {
										jAlert(msg,"알림",function(){ location.href='/plans'; });
									}
							}else{
								jAlert(msg,"알림");
							}
						}
				});
		};

				$.post("../get_set.php", {type:'unlisted_value_unlock', kedcd:kedcd, check_only:1, unlock_token:unlockToken}, function(preData){
					// check_only 응답도 동일 파서로 처리해서 "ready" 오탐을 막는다.
					var parsePipeResponse = function(raw){
						var text = String(raw || "");
						text = text.replace(/^\uFEFF/, "").trim();
						var codeIdx = text.search(/(?:1000|500|429|501)\|/);
						if(codeIdx > 0) text = text.substring(codeIdx);
						text = text.replace(/<[^>]*>/g, "").trim();
						return text.split("|");
					};
					var pre = parsePipeResponse(preData);
				if(pre[0] == "1000"){
					if(pre[1] == "already"){
						unlockAction();
						return;
					}

				if(pre[3]){
					var checkCost = parseInt(pre[3], 10);
					if(checkCost > 0) creditCost = checkCost;
				}

				var confirmMessage = "비상장주식가치 공개 시 " + creditCost + "크레딧이 사용됩니다.\n진행하시겠습니까?";
				if(typeof jConfirm === "function"){
					jConfirm(confirmMessage, "알림", function(isOk){
						if(isOk) unlockAction();
					});
				}else{
					if(confirm(confirmMessage)) unlockAction();
				}
					}else{
							var preMsg = pre[1] ? pre[1] : "처리 중 오류가 발생했습니다.";
							if(pre[2] == "PLAN"){
								if (typeof window.openCreditShortageGuide === "function") {
									window.openCreditShortageGuide({
										text: preMsg,
										text2: "크레딧 충전 후 다시 가치 조회를 진행해 주세요."
									});
								} else if (typeof window.openAiGuideModal === "function") {
									window.openAiGuideModal({
										title: "크레딧 부족 안내",
										text: preMsg,
										text2: "크레딧 충전 후 다시 가치 조회를 진행해 주세요.",
										okText: "닫기",
										okVariant: "outline",
										secondaryText: "크레딧 충전하러 가기",
										secondaryHref: "/plans"
									});
								} else {
									jAlert(preMsg,"알림",function(){ location.href='/plans'; });
								}
						}else{
							jAlert(preMsg,"알림");
						}
					}
			});

		return false;
	});
</script>

<?php 
include_once(G5_THEME_PATH.'/tail.php');
?>
