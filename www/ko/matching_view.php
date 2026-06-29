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

//로그인 체크
login_chk();

include_once(G5_THEME_PATH.'/head.php');

if($cd){
	$com=sql_fetch("SELECT * from company where KEDCD='$cd'");
	$major_service=sql_fetch("SELECT * from g5_member where mb_company='$cd'");
}else{
	ALERT("정상적으로 이용하세요.","/");
}

if($com['BZC_CD']) $kssc=sql_fetch("SELECT * from kssc_11 where k_code='{$com['BZC_CD']}'");

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

if($member['mb_id']) {
	$favorite=sql_fetch("SELECT * from g5_favorite where mb_id='{$member['mb_id']}' and KEDCD='{$cd}'"); //북마크
	$matcing=sql_fetch("SELECT * from g5_matching where del != 'D' and  request_id='{$member['mb_id']}' and KEDCD='{$cd}'");//매칭요청

}

if($com['CONO_PID']) $com['CONO_PID']=substr($com['CONO_PID'],0,6)."-".substr($com['CONO_PID'],-7);

//매칭 상세 정보 출력 여부 체크 (로그인 // 매칭 수락된 경우)
$view_chk="false";
if($member['mb_id'] && $matcing['receive_approval']=="Y") $view_chk="true";


//맴버쉽 정보
$today = date("Y-m-d");
$msinfo_row=getUserMembership();
//맴버쉽이 없는 경우 무료로
if(!$msinfo_row[0]) $msinfo['membership_parent_no']=$msinfo_row[1]['no'];
else $msinfo=$msinfo_row[0];

//무료일 경우
if(!$msinfo_row[0] && $member['mb_level']<10) { 
	//무료인경우만 카운트
	if($member['mb_open_cnt']>0) {
		sql_query("update g5_member set mb_open_cnt=mb_open_cnt-1 where mb_id='{$member['mb_id']}'");
	}else{
		ALERT("거래처 오픈 회수를 초과했습니다.","/ko/plan.php");
	}
}

	//맴버쉽 히스토리 저장
	sql_query("insert g5_membership_history set uid='{$member['mb_id']}', kind='USE', count_number='열람 1건 사용', regdate=now(), order_no='', open_parent_no='{$cd}', matching_parent_no='', membership_parent_no='{$msinfo['membership_parent_no']}'");

	$view_chk="true"; //임시처리

if($view_chk=="false"){
	//법인번호
	$com['CONO_PID']=str_pad(mb_substr($com['CONO_PID'],0, 6), mb_strlen($com['CONO_PID']), "*");
	//홈페이지
	$com['HPAGE_URL']=str_pad(mb_substr($com['HPAGE_URL'],0, 6), mb_strlen($com['HPAGE_URL']), "*");
	//산업분류
	$kssc['k_code_name']=str_pad(mb_substr($kssc['k_code_name'],0, 3), mb_strlen($kssc['k_code_name']), "*");
	//종업원수
	$com['LABORER_SUM']=str_pad(mb_substr($com['LABORER_SUM'],0, 0), mb_strlen($com['LABORER_SUM']), "*");
	//주소
	$LOC_ADDRA=explode(" ",$com['LOC_ADDRA']);
	$com['LOC_ADDRA'] = $LOC_ADDRA[0]." ".$LOC_ADDRA[1];
	$com['LOC_ADDRB']=str_pad(mb_substr($com['LOC_ADDRB'],0, 0), mb_strlen($com['LOC_ADDRB']), "*");
	//사업자등록번호 $bzno_c
	if($bzno_c) $bzno_c=str_pad(mb_substr($bzno_c,0, 5), mb_strlen($bzno_c), "*");
}

?>

<style>
    #pageTop{display: none}
</style>

<div id="matching_view" class="">
    <div class="myCompanyInfo">
        <div class="matching_view_top">
            <h4 class="viewCompanyTitle fs35 lh1 c111 fw550"><?=$com['ENP_NM']?></h4>

            <ul class="viewCompanyOption">
			<?if($member['mb_id']){?>
                <li class="viewOption_bookmark">
                    <input type="checkbox" name="" value="<?=$com['KEDCD']?>"  id="viewOption_bookmark" <?if($favorite['ms_id']){?>checked<?}?>>
                    <label for="viewOption_bookmark"></label>
                </li>
                <!-- check 시 별 색상이 바뀜 -->
                <li class="viewOption_url">
                    <a href="javascript:copyUrl()"></a>
                </li>
                <li class="viewOption_submit">
					<?if($matcing['receive_approval']=="N"){?>
                    <a href="javascript:jAlert('매칭 수락 대기중입니다.','알림')"><span>수락 대기중</span> <span class="icon"><img src="/img/icon/icon_arr_point.png" alt=""></span></a>
					<?}?>
					<?if($matcing['receive_approval']=="Y"){?>
                    <a><span>매칭 수락됨</span> <span class="icon"><img src="/img/icon/icon_arr_point.png" alt=""></span></a>
					<?}?>
					<?if($matcing['receive_approval']==""){?>
                    <a href="javascript:popupOpen('.popup_submit','<?=$com['KEDCD']?>')"><span>매칭 요청</span> <span class="icon"><img src="/img/icon/icon_arr_point.png" alt=""></span></a>
					<?}?>
                </li>
			<?}else{?>
                <li class="viewOption_bookmark">
                    <input type="checkbox" name="" onClick="javascript:jAlert('로그인 후 이용하세요.','알림',function(){location.href='/bbs/login.php'}); return false;"  id="viewOption_bookmark">
                    <label for="viewOption_bookmark"></label>
                </li>
                <!-- check 시 별 색상이 바뀜 -->
                <li class="viewOption_url">
                    <a href="javascript:copyUrl()"></a>
                </li>
                <li class="viewOption_submit">
                    <a href="javascript:jAlert('로그인 후 이용하세요.','알림',function(){location.href='/bbs/login.php'}); "><span>매칭 요청</span> <span class="icon"><img src="/img/icon/icon_arr_point.png" alt=""></span></a>
                </li>
			<?}?>
            </ul>
        </div>

        <div class="viewCompanyDesc myCompanyDesc">
            <ul>
                <li>
                    <div class="title">기업명</div>
                    <div class="text">
					<?=$com['ENP_NM_TRD']?>
					</div>
                </li>
                <li>
                    <div class="title">영문기업명</div>
                    <div class="text">
					<?=$com['ENP_NM_ENG']?>
					</div>
                </li>
                <li>
                    <div class="title">법인(주민)번호</div>
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
                <li>
                    <div class="title">홈페이지</div>
                    <div class="text">
					<?$upos = strpos($com['HPAGE_URL'],"http");?>
					<?if($com['HPAGE_URL']){?><a href="<?if($upos === false){?>http://<?}?><?=$com['HPAGE_URL']?>" target="_blank"><?=$com['HPAGE_URL']?></a><?}?>
					</div>
                </li>
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
                <li>
                    <div class="title">종업원수</div>
                    <div class="text"><?if($com['LABORER_SUM']){?><?=$com['LABORER_SUM']?>명<?}?></div>
                </li>
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
                <li>
                    <div class="title">표준산업분류(11차)</div>
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

            </ul>
        </div>
    </div>

    <div class="common_info">
        <dl class="common_info_lists">
            <dt>직접 연락 방법</dt>
			<dd>거래처와 직접 연력하시려면, 상단의 대표 전화번호로 연락해 주세요.</dd>
        </dl>
        <dl class="common_info_lists">
            <dt>매칭 요청</dt>
			<dd>오른쪽 상단의 "매칭 요청"버튼을 클릭하시면, 당사 직원이 피매칭 기업의 담당자에게 직접 연락하여 문의 사항을 확인할 수 있도록 도움을 드립니다.</dd>
			<dd>*단, 수일이 소요될 수 있는점 양해 부탁드립니다.</dd>
        </dl>
        <dl class="common_info_lists">
            <dt>매칭 수락</dt>
			<dd>피매칭 기업이 매칭을 수락할 경우 담당자의 연락차가 공개됩니다.</dd>
        </dl>
    </div>
</div>


<?
include_once(G5_THEME_PATH.'/tail.php');
?>

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
			<input type="hidden" name="kedcd" value="<?=$com['KEDCD']?>" id="kedcd">
			<input type="hidden" name="c_id" value="<?=$com['c_id']?>" id="c_id">
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
	//북마크 처리
	$("#viewOption_bookmark").click(function(){
		$this=$(this);
		kedcd=$(this).val();
		chk=$(this).is(":checked");
		if(kedcd){
			$.post( "../get_set.php", {
				type: 'favorite',
				kedcd:kedcd,
				chk: chk
			})
			.done(function(jqXHR) {
				if(jqXHR==500){
					if(chk) $this.prop("checked",false);
					else $this.prop("checked",true);
				}
			});
		}
	})
//암호화
function Encrypt( str , key ){
 output = new String;
 Temp = new Array();
 TextSize = str.length;
 
 for( i=0; i<TextSize; i++){
  // key 값을 원하는 대로 연산을 한다
  output += String.fromCharCode(str.charCodeAt(i) + parseInt(key)+123+i);

 }
 
 return output;
}

	

	//URL복사 처리
    function copyUrl() {
        var currentUrl = "<?=$_SERVER["HTTP_HOST"]?>/ko/company_view.php?cd=<?=simple_crypt($cd)?>";// 현재 URL 가져오기
		//window.location.href; // 현재 URL 가져오기
        const textArea = document.createElement("textarea");
		
        textArea.value = currentUrl;  // 현재 URL을 textarea에 삽입
        document.body.appendChild(textArea);  // textarea를 body에 추가
        textArea.select();  // textarea의 내용을 선택

        try {
            document.execCommand('copy');  // execCommand를 사용해 복사
            alert('URL이 복사되었습니다');
        } catch (err) {
            console.error('URL 복사에 실패했습니다: ', err);
        }
            document.body.removeChild(textArea);  // textarea 제거
        }

    const html = document.querySelector("html")
    const popupAll = document.querySelectorAll(".popup")

	//매칭창 열기
    function popupOpen(selector,kedcd){
		if(kedcd){
			$.post( "../get_set.php",{
				type : "matching_check",
				kedcd : kedcd
			})
			.done(function(jqXHR) {
				jqXHR=jqXHR.split("|");
				if(jqXHR[0]=="500"){
					var openMsg = jqXHR[1] ? jqXHR[1] : "요청에 문제가 있습니다.";
					if(jqXHR[2]=="PLAN" || openMsg.indexOf("매칭 가능 크레딧이 부족합니다") === 0){
						jAlert(openMsg,"알림",function(){
							location.href="/ko/plan.php";
						});
					}else if(jqXHR[0]=="501" || openMsg.indexOf("로그인 후 이용하세요.") === 0){
						jAlert("로그인 후 이용하세요.","알림",function(){
							location.href="/bbs/login.php?url="+encodeURIComponent(location.pathname + location.search);
						});
					}else{
						jAlert(openMsg,"알림",function(){ });
					}
				}else if(jqXHR[0]=="1000"){
						//매칭창 열기
						$(".popup_top_macthing_length .length").text(jqXHR[1]);//매칭수 넣기
						popupAll.forEach((e)=>{e.classList.remove("open")})
						let popupName = document.querySelector(selector);
						if(popupName){
							html.classList.add("hide")
							popupName.classList.add("open")
						}
				}else{

					if(jqXHR[0] && jqXHR[1]=="N") {
						jAlert("매칭 수락 대기중입니다.","알림");
						return false;
					}else{
						//매칭창 열기
						$(".popup_top_macthing_length .length").text(jqXHR[1]);
						popupAll.forEach((e)=>{e.classList.remove("open")})
						let popupName = document.querySelector(selector);
						if(popupName){
							html.classList.add("hide")
							popupName.classList.add("open")
						}

					}
				}
			})
		}else{
			jAlert("확인 후 이용해 주세요.","알림");
			return false;
		}
    }
	//매칭창 닫기
    function popupClose(selector){
       popupAll.forEach((e)=>{e.classList.remove("open")})
        html.classList.remove("hide")
    }
	//매칭 요청
    function popupSubmit(selector){
       popupAll.forEach((e)=>{e.classList.remove("open")})
		Formdata=$(".popup_content input, textarea").serialize();
		if(Formdata){
			$.post( "../get_set.php?"+Formdata)
			.done(function(jqXHR) {
				var parts = (jqXHR + "").split("|");
				if(parts[0] == "1000"){
					jAlert("매칭이 요청되었습니다.","알림",function(){
						location.reload();
					});
				}else{
					var msg = parts[1] ? parts[1] : "요청에 문제가 있습니다.";
					if(parts[2]=="PLAN" || msg.indexOf("매칭 가능 크레딧이 부족합니다") === 0){
						jAlert(msg,"알림",function(){
							location.href="/ko/plan.php";
						});
					}else if(parts[0] == "501" || msg.indexOf("로그인 후 이용하세요.") === 0){
						jAlert("로그인 후 이용하세요.","알림",function(){
							location.href="/bbs/login.php?url="+encodeURIComponent(location.pathname + location.search);
						});
					}else{
						jAlert(msg,"알림",function(){});
					}
				}
			});
		}
        html.classList.remove("hide")
    }
</script>
