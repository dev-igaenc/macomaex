<?
include_once("./_common.php");

$board['bo_10']="3"; // 1뎁스
$board['bo_9']="1"; // 2뎁스
//$board['bo_8']="1"; // 3뎁스
//$board['bo_7']="1"; // 4뎁스
// $board['bo_5'] = ""; // 1뎁스 제목
// $board['bo_6'] = ""; // 1뎁스 설명
// $board['bo_subject'] = "";  // 2뎁스 제목
//$board['bo_4'] = $config['cf_title']."의 혜택을 확인하세요.";  // 2뎁스 설명
//https://macomaex.com/ko/matching_view.php?cd=0000074175
include_once(G5_THEME_PATH.'/head.php');
$cd= simple_crypt( $cd, 'd' );

if($cd && strlen($cd)==10){
	$com=sql_fetch("SELECT * from company where KEDCD='$cd'");
	$major_service=sql_fetch("SELECT * from g5_member where mb_company='$cd'");
}else{
	ALERT("정상적으로 이용하세요.","/");
}

if($com['BZC_CD']) $kssc=sql_fetch("SELECT * from kssc where k_code='{$com['BZC_CD']}'");
if($com['KSIC9_BZC_CD']) $kssc_9=sql_fetch("SELECT * from kssc_9 where k_code='{$com['KSIC9_BZC_CD']}'");

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

//주소
$addr=explode(" ",$com['LOC_ADDRA']);
//전화번호
if($com['TEL_NO']){
	function align_tel($telNo) {
		$telNo = preg_replace('/[^\d\n]+/', '', $telNo);
		if(substr($telNo,0,1)!="0" && strlen($telNo)>8) $telNo = "0".$telNo;
		$Pn3 = substr($telNo,-4);
		if(substr($telNo,0,2)=="01") $Pn1 =  substr($telNo,0,3);
		elseif(substr($telNo,0,2)=="02") $Pn1 =  substr($telNo,0,2);
		elseif(substr($telNo,0,1)=="0") $Pn1 =  substr($telNo,0,3);
		$Pn2 = substr($telNo,strlen($Pn1),-4);
		if(!$Pn1) return $Pn2."-".$Pn3;
		else return $Pn1."-".$Pn2."-".$Pn3;
	}
	$com['TEL_NO']=align_tel(str_replace(" ","",$com['TEL_NO']));
}
$tel=explode("-",$com['TEL_NO']);

if($com['CONO_PID']) $com['CONO_PID']=substr($com['CONO_PID'],0,6)."-".substr($com['CONO_PID'],-7);

$view_chk="true"; //임시처리
$kssc9=getKsscInfo9($com[KSIC9_BZC_CD]);

if($member['mb_id']){
	$matcing=sql_fetch("SELECT * from g5_matching where del != 'D' and  request_id='{$member['mb_id']}' and KEDCD='{$cd}'");//매칭요청
	if($matcing['receive_approval']=="Y") $receve_info=getUserInfo($matcing['receive_id']);
	if(!$matcing['receive_approval']) {
		$matcing=sql_fetch("SELECT * from g5_matching where del != 'D' and  receive_id='{$member['mb_id']}' and KEDCD='{$cd}'");//받은매칭요청
		if($matcing['receive_approval']=="Y") $receve_info=getUserInfo($matcing['request_id']);
	}
	$favorite=sql_fetch("SELECT * from g5_favorite where mb_id='{$member['mb_id']}' and KEDCD='{$cd}'"); //북마크
}
$sales=getFinanceInfo($cd);
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

<div class="company-view" no><div class="layer-in new">
	<div class="cv-name">
		<strong><?=$com[ENP_NM]?></strong>
		<div class="option">
			<button type="button" star class="<?if($favorite['ms_id']){?>active<?}?> star_btn" kedcd="<?=$cd?>"><img src="/images/icon_star.svg" alt=""></button>
			<a href="javascript:copyUrl('<?=$_REQUEST['cd']?>')" link><img src="/images/icon_link.svg" alt=""></a>
			<?if($matcing['receive_approval']=="N"){?>
			<a href="javascript:jAlert('매칭 수락 대기중입니다.','알림')" request><span>수락대기</span></a>
			<?}?>
			<?if($matcing['receive_approval']=="Y"){?>
			<a request class="accepted"><span>매칭완료</span></a>
			<?}?>
			<?if($matcing['receive_approval']=="R"){?>
			<a request class="rejected"><span>매칭거절</span></a>
			<?}?>
			<?if(!$matcing['receive_approval']){?>
			<a id="popup_open" kedcd="<?=$kedcd?>" request><span>매칭 요청</span></a>
			<?}?>
		</div>
	</div>
	<div class="cv-info-area">
		<div class="cv-info-re normal">
			<p class="cv-tit">기본정보</p>
			<div class="cv-info-conts">
				<dl>
					<dt>설립일자</dt>
					<dd><span><?=substr($com[ESTB_DT],0,4)?>-<?=substr($com[ESTB_DT],4,2)?></span></dd>
				</dl>

				<dl>
					<dt>법인(주민)번호</dt>
					<dd><span><?=substr($com[CONO_PID],0,6)?><?=substr($com[CONO_PID],6,7)?></span></dd>
				</dl>
				<dl>
					<dt>사업자번호</dt>
					<dd><span><?=licenseNum($com[BZNO]);?></span></dd>
				</dl>

				<dl>
					<dt>고용인원</dt>
					<dd><span><?=number_format($com[LABORER_SUM])?>명</span></dd>
				</dl>
				<dl>
					<dt>기업규모</dt>
					<dd><span><?=$ENP_SZE[$com[ENP_SZE]]?></span></dd>
				</dl>
			</div>
		</div>
		<div class="cv-info-re business">
			<p class="cv-tit">사업/산업</p>
			<div class="cv-info-conts">
				<dl>
					<dt>기업유형</dt>
					<dd><?=$enp_typt[$com[ENP_TYP]]?></dd>
				</dl>
				<dl>
					<dt>주요제품</dt>
					<dd><?=$com[PD_NM]?></dd>
				</dl>

				<dl>
					<dt>표준산업분류(9차)</dt>
					<dd>(<?=$com[KSIC9_BZC_CD]?>) <?=$kssc9[k_code_name]?></dd>
				</dl>
				<dl>
					<dt>표준산업분류</dt>
					<dd>(<?=$com[BZC_CD]?>) <?=$kssc[k_code_name]?></dd>
				</dl>

				<dl>
					<dt>직전연도 매출액</dt>
					<dd><?=number_format($sales[0][SALES]*1000);?>원</dd>
				</dl>
			</div>
		</div>
		<div class="cv-info-re contact">
			<p class="cv-tit">연락방법/위치</p>
			<div class="cv-info-conts" colums>
				<dl>
					<dt>전화번호</dt>
					<dd>
						<?if(!$matcing or $matcing['receive_approval']=="N"){?>
						수락 후 공개
						<?}else{?>
						<a href="tel:<?=$receve_info[mb_8]?>"><?=$receve_info[mb_8]?></a>
						<?}?>
					</dd>
				</dl>
				<dl>
					<dt>이메일</dt>
					<dd>
						<?if(!$matcing or $matcing['receive_approval']=="N"){?>
						수락 후 공개
						<?}else{?>
						<a href="mailto:<?=$receve_info[mb_5]?>" u><?=$receve_info[mb_5]?></a>
						<?}?>
					</dd>
				</dl>
				<dl>
					<dt>홈페이지</dt>
					<dd><a href="http://<?=$com[HPAGE_URL]?>" target="_blank" u><?=$com[HPAGE_URL]?></a></dd>
				</dl>
				<dl>
					<dt>주소(도로명)</dt>
					<dd><a href="https://map.kakao.com/?q=<?=$com[LOC_RDNM_ADDRA]?> <?=$com[LOC_RDNM_ADDRB]?>" target="_blank"><?=$com[LOC_RDNM_ADDRA]?><br /><?=$com[LOC_RDNM_ADDRB]?></a></dd>
				</dl>

				<div class="cv-character">
					<span>좋은 거래처와<br />매칭되기를<br /><strong class="point">매커멕스</strong>가 함께<br />응원할게요</span>
					<p><img src="/images/icon_character.png" alt=""></p>
				</div>
			</div>
		</div>
	</div>

	<div class="cv-info-step">
		<div class="step-left">
			<p class="titles">직접 연결 방법</p>
			<div class="step-re">
				<p class="step"><strong>STEP 1</strong><span><img src="/images/arr_message_r.png" alt=""></span></p>
				<p class="icon"><img src="/images/icon_connection.png" alt=""></p>
				<p class="tit">직접 연결</p>
				<div class="conts tel">
					<span class="point">대표 전화번호</span>로 연결됩니다.
					<p class="btn"><a href="tel:02-545-8454">02-545-8454</a></p>
				</div>
			</div>
		</div>
		<div class="step-right">
			<p class="titles">매칭 프로세스</p>
			<div class="step-right-in">
				<div class="step-re">
					<p class="step"><strong>STEP 1</strong><span><img src="/images/arr_message_r.png" alt=""></span></p>
					<p class="icon"><img src="/images/icon_request.png" alt=""></p>
					<p class="tit">매칭 요청</p>
					<div class="conts">
						오른쪽 상단의 <span class="point">“매칭요청” 버튼을 클릭</span>
						하시면, 당사 직원이 피매칭 기업 담당
						자에게 직접 연락하여 문의 사항을 확인
						할 수 있도록 도움을 드립니다.
					</div>
				</div>
				<div class="step-re">
					<p class="step"><strong>STEP 2</strong><span><img src="/images/arr_message_r.png" alt=""></span></p>
					<p class="icon"><img src="/images/icon_accept.png" alt=""></p>
					<p class="tit">매칭 수락</p>
					<div class="conts">
						피매칭 기업이 매칭을 수락할 경우,<br />
						<span class="point">담당자의 직통 연락처</span>가 공개됩니다.
					</div>
				</div>
			</div>
		</div>
	</div>
</div></div>

<div class="popup popup_submit">
    <div class="popupInner">
        <a href="javascript:popupClose('')" class="popupClose"><img src="/img/icon/icon_close.svg" alt=""></a>

        <div class="popup_top">
            <h4 class="popup_top_title">매칭요청</h4>

            <div class="popup_top_macthing_length">남은 매칭 <span class="point">가능 수 <b class="length"><?=$msinfo['matching_able_cnt']?></b>개</span></div>
        </div>

        <div class="popup_content">
            <table>
                <tbody>
                    <tr>
                        <th>보내는 사람</th>
                        <td><?=$member['mb_name']?> <?=$member['mb_6']?> <?=$member['mb_7']?></td>
                    </tr>
                    <tr>
                        <th>받는 사람</th>
                        <td><?=$com['ENP_NM']?> 신규 거래처 담당자</td>
                    </tr>
                </tbody>
            </table>
			<input type="hidden" name="type" value="matching">
			<input type="hidden" name="kedcd" value="" id="kedcd">
			<input type="hidden" name="typ" value="accept">
            <textarea name="match_content" id="match_content" placeholder="저희는 ㅇㅇ을 제조/유통하는 전문 업체로서, 매칭 수락시 담당자분께
거래에 관한 구체적인 내용을 제안 드리도록 하겠습니다."></textarea>

            <p class="popup_textarea_tip">* 거래에 관하여 구체적인 내용을 적어주시면 매칭확률이 높아집니다.</p>
        </div>

        <div class="popup_btns">
            <button type="submit" onclick="popupSubmit()">매칭요청</button>
        </div>
    </div>
</div>
<script>

$("button#sub_submit").click(function(){
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
				equal_height();
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



	//URL복사 처리
    function copyUrl(cd) {
		var currentHost = location.host; // 현재 호스트 가져오기
		var currentUrl = "https://" + currentHost + "/ko/company_view.php?cd=" + cd;
		if (navigator.clipboard) {
			// 최신 브라우저
			navigator.clipboard.writeText(currentUrl).then(function() {
				alert('URL이 복사되었습니다');
			}).catch(function(err) {
				console.error('URL 복사에 실패했습니다: ', err);
			});
		} else {
			// fallback for old browsers
			const textArea = document.createElement("textarea");
			textArea.value = currentUrl;
			document.body.appendChild(textArea);
			textArea.select();
			try {
				document.execCommand('copy');
				alert('URL이 복사되었습니다');
			} catch (err) {
				console.error('URL 복사에 실패했습니다: ', err);
			}
			document.body.removeChild(textArea);
		}
}

	//북마크 처리
	$("body").on("click",".star_btn",function(){
		$this=$(this);
		kedcd=$(this).attr("kedcd");
		chk=!($(this).hasClass("active"));
		if(kedcd){
			$.post( "../get_set.php", {
				type: 'favorite',
				kedcd:kedcd,
				chk: chk
			})
			.done(function(jqXHR) {
				if(jqXHR==500){
					if(chk) $this.removeClass("active");
				}else if(jqXHR==501){
				jAlert("로그인 후 이용하세요.","알림",function(){
					location.href="/bbs/login.php?url=/ko/company_view.php?cd=<?=$_REQUEST['cd']?>";
				});
				return false;
				}else{
					if(chk) $("button.star_btn[kedcd='"+kedcd+"']").addClass("active");
					else $("button.star_btn[kedcd='"+kedcd+"']").removeClass("active");
				}
			});
		}
	})


    const html = document.querySelector("html")
    const popupAll = document.querySelectorAll(".popup")

	$("body").on("click","#popup_open",function(){
		kedcd = $(this).attr("kedcd");
		$.post( "../get_set.php",{type:'matching_num_chk'})
		.done(function(jqXHR) {
			jqXHR=jqXHR.split("|");
			if(jqXHR[0]==500){
				jAlert("매칭 가능 횟수가 없습니다.","알림");
				return false;
			}else if(jqXHR[0]==501){
				jAlert("로그인 후 이용하세요.","알림",function(){
					location.href="/bbs/login.php?url=/ko/matching_list.php";
				});
				return false;
			}else{
				$("button.is-close").click();
				$(".popup_top_macthing_length .length").text(jqXHR[1]);
				$(".popup_content #kedcd").val(kedcd);
				popupAll.forEach((e)=>{e.classList.remove("open")})
				let popupName = document.querySelector(".popup");
				if(popupName){
					html.classList.add("hide")
					popupName.classList.add("open")
				}
			}
		});
    })

    function popupClose(selector){

       popupAll.forEach((e)=>{e.classList.remove("open")})
        html.classList.remove("hide")
    }

    function popupSubmit(selector){

       popupAll.forEach((e)=>{e.classList.remove("open")})
		Formdata=$(".popup_content input, textarea").serialize();
		if(Formdata){
			$.post( "../get_set.php?"+Formdata)
			.done(function(jqXHR) {
				if(jqXHR==500){
					jAlert("요청에 문제가 있습니다.","알림");
					return false;
				}else{
					jAlert("매칭이 요청되었습니다.","알림",function(){
						popupClose('');
					});
				}
			});
		}
        html.classList.remove("hide")
    }




</script>
<?
include_once(G5_THEME_PATH.'/tail.php');
?>