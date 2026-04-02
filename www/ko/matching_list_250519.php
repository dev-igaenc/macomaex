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

include_once(G5_THEME_PATH.'/head.php');


$cominfo=stripslashes($cominfo);
$data=json_decode($cominfo);
 //print_r($data->CONTENTS);
 //
//ENPintr0000 기업일반
//ENPsthr0001 주주현황상세
//ENPcust0001 (구매처)
//ENPsupp0001(판매처)
//ENPfsdt0001 재무제표
//ENPkbad0000
//
$KEDCD=$data->CONTENTS->ENPintr0000->kedcd;
$bzno=$data->CONTENTS->ENPintr0000->bzno; //사업자번호
$reper_name=$data->CONTENTS->ENPintr0000->reper_name; //대표자명
$loc_addra=$data->CONTENTS->ENPintr0000->loc_addra;//주소
$mpd=$data->CONTENTS->ENPintr0000->mpd;//주생산품
$enp_typ=$data->CONTENTS->ENPintr0000->enp_typ;//기업유형
$enp_fcd=$data->CONTENTS->ENPintr0000->enp_fcd;//기업형태
$bzc_nm=$data->CONTENTS->ENPintr0000->bzc_nm;//업종명

if(!$KEDCD) goto_url("/");

//구매처
foreach($data->CONTENTS->ENPcust0001 as $key => $value){
	if (strpos($key,"cust_nm")>-1 && strlen($value)) $cust_nm[]=$value;
}
if(count($cust_nm)==0) $cust_txt="-";
else if(count($cust_nm)==1) $cust_txt=$cust_nm[0];
else $cust_txt=$cust_nm[0]." 외 ".(count($cust_nm)-1)."개";
//판매처
foreach($data->CONTENTS->ENPsupp0001 as $key => $value){
	if (strpos($key,"supp_nm")>-1 && strlen($value)) $supp_nm[]=$value;
}
if(count($supp_nm)==0) $supp_txt="-";
else if(count($supp_nm)==1) $supp_txt=$cust_nm[0];
else $supp_txt=$supp_nm[0]." 외 ".(count($supp_nm)-1)."개";

//print_r($data->CONTENTS->ENPsthr0001);
// 매출액
$fs_val9=$kedcd=$data->CONTENTS->ENPfsdt0001->fs_val9; //최근결산년_매출액
$take=$fs_val9*1000;

$kedcd=$data->CONTENTS->ENPintr0000->kedcd;
//exit;
//기업가치 순자산가치법 자산-부채 (데이터 단위 천원)
$asset=$data->CONTENTS->ENPfsdt0001->fs_val3;
$liab=$data->CONTENTS->ENPfsdt0001->fs_val6;
$capital=$data->CONTENTS->ENPfsdt0001->fs_val7; //자본금
$E_VAL=$capital*1000;
//$E_VAL=($asset-$liab)*1000;
//echo $asset-$liab."";
function addh_bzno($bz){
	if(!$bz) return false;
	$bz=str_replace("-","",$bz);
	$bz1=substr($bz,0,3);
	$bz2=substr($bz,3,2);
	$bz3=substr($bz,5);
	return $bz1."-".$bz2."-".$bz3;
}
if($bzno) $bzno_c=addh_bzno($bzno);
?>
<style>
.progress_wrap{width:100%; height:100%; background-color: rgba(0,0,0,0.7); text-align:center; position: fixed; left:0; top:0; z-index:9999; display: flex; align-items: center; justify-content: center;}
.progress_wrap .progress {width:40rem; padding:5rem 5rem; background-color:#fff; font-size:1.8rem; line-height:1.4; color: #333 ;border:1px solid #ccc; border-radius:0.5rem;}

.progress_wrap #progressbar{width:100%; height:4rem; margin-top:2rem; background-color: #eee; position: relative; overflow: hidden;}
.progress_wrap #progressbar:before{content: ""; width: 0; height: 100%; background-color: var(--point); display: inline-block; position:absolute; left: 0; top:0px; box-sizing: border-box;
	animation: progressMove 4s ease-in-out infinite;
}
@media only screen and (max-width : 840px){
	.progress_wrap .progress {width:calc(100% - 4rem); font-size:2.4rem;}
}

@keyframes progressMove {
	0%{
		width: 0;
		left: 0;
	}
	50%{
		width: 100%;
		left: 0;
	}
	100% {
		width: 100%;
		left: 100%;
	}
}
</style>

<link rel="stylesheet" href="https://code.jquery.com/ui/1.14.1/themes/base/jquery-ui.css">
<div class="progress_wrap">
	<div class="progress ">
		<p class="mtitl">잠시 기다려주세요</p>
		<p class="stitl">추천 거래처 검색 중....</p>
		<div id="progressbar"></div>
	</div>
</div>
<!--
 <script>
    $( ".progress_wrap #progressbar" ).progressbar({
      value: 20
    });
	$(".progress_wrap .stitl").text("기본 기업 정보 검색중...");
 </script>
 -->
<?
ob_get_flush();
flush();
$tmp_cnt=9999999;
if($KEDCD){

	$com=sql_fetch("SELECT * from company where BZNO='$bzno'");
	if($com['BZC_CD']){
			$sql="
				SELECT KEDCD, ENP_NM, SALES, rank
				FROM (
					SELECT
						KEDCD,
						ENP_NM,
						SALES,
						@rank := IF(@prev = SALES, @rank, @rank + 1) AS rank,
						@prev := SALES
					FROM company,
						 (SELECT @rank := 0, @prev := NULL) AS vars
					WHERE BZC_CD = '{$com['BZC_CD']}'
					ORDER BY SALES DESC
				) AS ranked
				WHERE KEDCD = '{$com['KEDCD']}'
			";
			$result=sql_fetch($sql);
			//echo $sql;
			$total_cnt=sql_fetch("SELECT count(*) AS cnt FROM company WHERE BZC_CD = '{$com['BZC_CD']}'");
			//echo $sql;
			//print_r($result);
			if($result['rank']){
				$top30=ROUND($result['rank']*0.3);
				$top50=ROUND($result['rank']*0.5);
				//echo "<br>상위 30~50% 구간 ".$top30." ~ ".$top50."<br>";

			if(($top50-$top30)<6) {
				//구간이 너무 작으면 처리(상위권 업체)
				//구간에서 랜덤 5개 추출
				$sql="
					SELECT KEDCD, ENP_NM, SALES, rank
					FROM (
						SELECT
							KEDCD,
							ENP_NM,
							SALES,
							@rank := IF(@prev = SALES, @rank, @rank + 1) AS rank,
							@prev := SALES
						FROM company,
							 (SELECT @rank := 0, @prev := NULL) AS vars
						WHERE BZC_CD = '{$com['BZC_CD']}'
						ORDER BY SALES DESC
					) AS ranked
					WHERE rank != {$result['rank']} AND rank BETWEEN 1 AND 20
					ORDER BY RAND()
					LIMIT 5
				";
			}else{
				//일반적인 업체
				//구간에서 랜덤 5개 추출
				$sql="
					SELECT KEDCD, ENP_NM, SALES, rank
					FROM (
						SELECT
							KEDCD,
							ENP_NM,
							SALES,
							@rank := IF(@prev = SALES, @rank, @rank + 1) AS rank,
							@prev := SALES
						FROM company,
							 (SELECT @rank := 0, @prev := NULL) AS vars
						WHERE BZC_CD = '{$com['BZC_CD']}'
						ORDER BY SALES DESC
					) AS ranked
					WHERE rank BETWEEN {$top30} AND {$top50}
					ORDER BY RAND()
					LIMIT 15
				";
			}
			//echo $sql;
			$result=sql_query($sql);
?>
	<!--
 <script>
    $( ".progress_wrap #progressbar" ).progressbar({
      value: 60
    });
	$(".progress_wrap .stitl").text("내 동종업계 기업 검색 중....");
  </script>
  -->
<?
ob_get_flush();
flush();
			for ($i=0; $row=sql_fetch_array($result); $i++) {
				//echo $row['rank']." ".$row['ENP_NM']."<br>";
				$REC_KEDCD[]=$row['KEDCD'];
			}
			$REC=implode("','",$REC_KEDCD);
?>
	<!--
 <script>
    $( ".progress_wrap #progressbar" ).progressbar({
      value: 80
    });
	$(".progress_wrap .stitl").text("거래처 동종 업계 순위 집계중...");
  </script>
  -->
<?
ob_get_flush();
flush();
			//$sql="SELECT * from company_client where KEDCD in ('{$REC}') and TXPL_KEDCD != '' group by TXPL_KEDCD order by TX_BZC_CD DESC";
			$sql="SELECT A.*, B.BZC_CD, B.LOC_ADDRA, C.k_code_name from company_client A left join company B on A.TXPL_KEDCD=B.KEDCD left join kssc C on B.BZC_CD=C.k_code WHERE A.KEDCD in ('{$REC}') and TXPL_KEDCD != '' group by TXPL_KEDCD order BY B.BZC_CD DESC";
			//echo $sql;
			$result=sql_query($sql);
			for ($i=0; $row=sql_fetch_array($result); $i++) {
				$REC_TXPL_KEDCD[]=$row['TXPL_KEDCD'];
				if($row['BZC_CD'] && $row['TXPL_KEDCD']){
					//랭크 쿼리
					$r_query="SELECT KEDCD, ENP_NM, SALES, rank
					FROM (
						SELECT
							KEDCD,
							ENP_NM,
							SALES,
							@vRank := IF(@group = BZC_CD,
										 IF(@prev_value = SALES, @vRank, @vRank + 1),
										 1) AS rank,
							@prev_value := SALES,
							@group := BZC_CD
						FROM company,
							 (SELECT @vRank := 0, @prev_value := NULL, @group := NULL) AS vars
						WHERE BZC_CD = '{$row['BZC_CD']}'
						ORDER BY BZC_CD, SALES DESC
					) AS ranked
					WHERE KEDCD = '{$row['TXPL_KEDCD']}'";

					//echo $r_query."<br>";
					$TX_rank=sql_fetch($r_query);
					$row['TXPL_RANK']=$TX_rank['rank'];

				}else{
					//$row['TXPL_RANK']="";
				}

				if($row['TXPL_CCD']==1) {
					$cust_sub[]=$row;

					if(!$row['TXPL_RANK']) {
						$row['TXPL_RANK']=$tmp_cnt;
						$tmp_cnt++;
					}
					if($row['LOC_ADDRA']) $cust_sub_rank[]=$row;
				}
				if($row['TXPL_CCD']==2) {
					$supp_sub[]=$row;

					if(!$row['TXPL_RANK']) {
						$row['TXPL_RANK']=$tmp_cnt;
						$tmp_cnt++;
					}
					if($row['LOC_ADDRA']) $supp_sub_rank[$row['TXPL_RANK']]=$row;
				}
			}

			}
	}
}
?>
 <script>
	/*
    $( ".progress_wrap #progressbar" ).progressbar({
      value: 100
    });
	*/
	$( ".progress_wrap").fadeOut(300);
  </script>
<?
ob_get_flush();
flush();

$C_RANK = array_column($cust_sub_rank, 'TXPL_RANK');
$S_RANK = array_column($supp_sub_rank, 'TXPL_RANK');
//array_multisort($C_RANK, SORT_ASC, $cust_sub_rank);
//array_multisort($S_RANK, SORT_ASC, $supp_sub_rank);
//ksort($cust_sub_rank);
//ksort($supp_sub_rank);
//print_r($cust_sub_rank);
$cust_cnt=count($cust_sub_rank); //구매거래처 수
$supp_cnt=count($supp_sub_rank); //판매거래처 수

//맴버쉽 정보 (마스터는 최고 맴버쉽 getUserMembership)
$today = date("Y-m-d");
if($member['mb_id']) {
	$msinfo_row = getUserMembership();
	//맴버쉽이 없는 경우 무료로
	if(!$msinfo_row[0]) $msinfo['membership_parent_no']=$membership[0]['no'];
	else $msinfo=$msinfo_row[0];
}

?>

<style>
	#pageTop,
	#sVisual{display: none}
	.matching-list .matching-item:nth-child(n+7) {display:none; }
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

<div class="my-company-area">
	<dl class="h4-title">
		<dt><span><img src="/images/icon_myCompany.png" alt="company"></span><strong>내 회사 정보</strong></dt>
		<dd><a href="/bbs/write.php?bo_table=Contact" btn shadow>데이터제보/오류알리기</a></dd>
	</dl>
	<div class="long-info" shadow>
		<dl>
			<dt>대표자</dt>
			<dd><?=$reper_name?></dd>
		</dl>
		<dl>
			<dt>사업자번호</dt>
			<dd><?=$bzno_c?></dd>
		</dl>
		<dl>
			<dt>기업유형</dt>
			<dd>
					<?if($enp_typt[$enp_typ]){?><?=$enp_typt[$enp_typ]?><?}?>
					<?if($enp_fcdt[$enp_fcd]){?> / <?=$enp_fcdt[$enp_fcd]?><?}?>
			</dd>
		</dl>
		<dl>
			<dt>종목</dt>
			<dd><?=$bzc_nm?></dd>
		</dl>

		<dl>
			<dt>자본금</dt>
			<dd><?=number_format($E_VAL)?> 원</dd>
		</dl>
		<dl>
			<dt>직전연도 매출액</dt>
			<dd><?=number_format($take)?> 원</dd>
		</dl>
		<dl c2>
			<dt>주소(도로명)</dt>
			<dd><?=$loc_addra?></dd>
		</dl>
	</div>

	<dl class="h4-title">
		<dt><span><img src="/images/icon_data.png" alt="data"></span><strong><span class="point">기업데이터 기반</span> 추천거래처</strong></dt>
	</dl>

	<div class="matching-tab" d2>
		<a href="javascript:void(0);" class="active">구매 거래처<!-- <span>23</span> --></a><!-- 활성화 -->
		<a href="javascript:void(0);">판매 거래처<!-- <span>12</span> --></a>
	</div>

	<div class="column-list full">
		<div class="column-list-re" buy>
			<div class="matching-list">
				<?
				$cpoint = current($cust_sub_rank);
				$spoint = current($supp_sub_rank);
				for($i=0;$i<$cust_cnt;$i++){
				?>
				<div class="matching-item">
					<p class="name"><strong><?=$cpoint['TXPL_NM']?></strong></p>
					<div class="info-area">
						<dl>
							<dt>소<i></i>재<i></i>지</dt>
							<dd><?=$cpoint['LOC_ADDRA']?></dd>
						</dl>
						<dl>
							<dt>동종<i></i>업계<i></i>순위</dt>
							<dd><?if($cpoint['TXPL_RANK']<9999999){?><?=$cpoint['TXPL_RANK']?> 위<?}?></dd>
						</dl>
						<dl>
							<dt>종<i></i>목</dt>
							<dd><?=$cpoint['k_code_name']?></dd>
						</dl>
					</div>
					<div class="btn-detail">
						<a href="javascript:void(0);" kedcd="<?=$cpoint['TXPL_KEDCD']?>" class="detail_view">상세보기</a>
					</div>
				</div>
				<?
				$cpoint = next($cust_sub_rank);
				}
				?>
			</div>

			<?if($cust_cnt>0){?>
			<a href="javascript:;" class="searchCompanyListLoad">더보기 <span class="length">(<?=$cust_cnt?>)</span></a>
			<?}?>

		</div>

		<div class="column-list-re" sell>
			<div class="matching-list">
				<?
				for($i=0;$i<$supp_cnt;$i++){
				?>
				<div class="matching-item">
					<p class="name"><strong><?=$spoint['TXPL_NM']?></strong></p>
					<div class="info-area">
						<dl>
							<dt>소<i></i>재<i></i>지</dt>
							<dd><?=$spoint['LOC_ADDRA']?></dd>
						</dl>
						<dl>
							<dt>동종<i></i>업계<i></i>순위</dt>
							<dd><?if($spoint['TXPL_RANK']<9999999){?><?=$spoint['TXPL_RANK']?> 위<?}?></dd>
						</dl>
						<dl>
							<dt>종<i></i>목</dt>
							<dd><?=$spoint['k_code_name']?></dd>
						</dl>
					</div>
					<div class="btn-detail">
						<a href="javascript:void(0);" kedcd="<?=$spoint['TXPL_KEDCD']?>" class="detail_view">상세보기</a>
					</div>
				</div>
				<?
				$spoint = next($supp_sub_rank);
				}
				?>
			</div>
			<?if($supp_cnt>0){?>
			<a href="javascript:;" class="searchCompanyListLoad">더보기 <span class="length">(<?=$supp_cnt?>)</span></a>
			<?}?>
		</div>
	</div>
</div>

<!--[s] 상세보기 팝업 -->
<div id="company-view-lay" class="layer-base layer-main company-view"><div class="layer-in new">
</div></div>
<!--[e] 상세보기 팝업 -->

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
                        <td id="recv"><?=$com['ENP_NM']?> 신규 거래처 담당자</td>
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

<script type="text/javascript" src="/build/jquery-equal-height.js"></script>
<script>
$(window).on('load', function(event) {
	equal_height();
});
$(window).resize(function(event) {
	equal_height();
});

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
function equal_height(){
	/*
	$(".column-list").jQueryEqualHeight(".matching-item dl:eq(2)");
	$(".column-list").jQueryEqualHeight(".matching-item dl:eq(1)");
	$(".column-list").jQueryEqualHeight(".matching-item dl:eq(0)");
	//$(".column-list").jQueryEqualHeight(".matching-item .info-area");
	$(".column-list").jQueryEqualHeight(".matching-item .name");
	$(".column-list").jQueryEqualHeight(".matching-item");
	*/
}
function setMatchingItemHeights() {
	const leftItems = document.querySelectorAll('.column-list-re:first-of-type .matching-item');
	const rightItems = document.querySelectorAll('.column-list-re:last-of-type .matching-item');
	const count = Math.min(leftItems.length, rightItems.length);

	for (let i = 0; i < count; i++) {
		// 높이를 리셋 (리사이즈 시 기존 높이를 초기화해야 정확한 높이 계산 가능)
		leftItems[i].style.height = 'auto';
		rightItems[i].style.height = 'auto';

		const leftHeight = leftItems[i].offsetHeight;
		const rightHeight = rightItems[i].offsetHeight;
		const maxHeight = Math.max(leftHeight, rightHeight);

		leftItems[i].style.height = maxHeight + 'px';
		rightItems[i].style.height = maxHeight + 'px';
	}
}
function debounce(func, wait) {
	let timeout;
	return function () {
		clearTimeout(timeout);
		timeout = setTimeout(func, wait);
	};
}
// 페이지 로드 시 실행
//window.addEventListener('load', setMatchingItemHeights);
// 창 크기 변경 시 실행
//window.addEventListener('resize', debounce(setMatchingItemHeights, 100));

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


//상세보기
$(".detail_view").click(function(){
	event.preventDefault();
	var kedcd=$(this).attr("kedcd");
	$.post( "../get_set.php", {
		type: 'view_chk'
	})
	.done(function(jqXHR) {
		jqXHR=jqXHR.split("|");
		if(jqXHR[0]==500){
			//if(jqXHR[1]) jAlert(jqXHR[1],"알림");

			if(jqXHR[1]=="로그인 후 이용하세요."){
				jAlert(jqXHR[1],"알림",function(){
				location.href="/bbs/login.php?url=/ko/matching_list.php";
				});
			}else if(jqXHR[1]=="거래처 오픈 회수를 초과했습니다."){
				jAlert(jqXHR[1],"알림",function(){
				location.href="/ko/plan.php";
				});
			}else{
				jAlert(jqXHR[1],"알림");
			}

		}else{

			//상세보기
			$.post( "../get_set.php",{type:'detail_view',kedcd:kedcd})
			.done(function(jqXHR) {
				jqXHR=jqXHR.split("|");
				if(jqXHR[0]==500){
					jAlert("거래처 오픈 회수를 초과했습니다.","알림");
					return false;
				}else{
					$("#company-view-lay .layer-in").html(jqXHR[0]);
					popFancy('#company-view-lay');
				}
			});


		}
	});
})
/*
$(".detail_view").click(function(){
	kedcd=$(this).attr("kedcd");
	$.post( "../get_set.php",{type:'detail_view',kedcd:kedcd})
	.done(function(jqXHR) {
		jqXHR=jqXHR.split("|");
		if(jqXHR[0]==500){
			jAlert("거래처 오픈 회수를 초과했습니다.","알림");
			return false;
		}else{
			$("#company-view-lay .layer-in").html(jqXHR[0]);
			popFancy('#company-view-lay');
		}
	});
})
*/
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
					else $this.addClass("active");
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
		$.post( "../get_set.php",{type:'matching_num_chk',kedcd:kedcd})
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
				$("#recv").html(jqXHR[2]);
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

$("#CompanyListTable a").click(function(){
	event.preventDefault();
	var href=$(this).attr("href");
	$.post( "../get_set.php", {
		type: 'view_chk'
	})
	.done(function(jqXHR) {
		jqXHR=jqXHR.split("|");
		if(jqXHR[0]==500){
			if(jqXHR[1]) jAlert(jqXHR[1],"알림");
			/*
			if(jqXHR[1]=="로그인 후 이용하세요."){
				jAlert(jqXHR[1],"알림",function(){
				location.href="/bbs/login.php?url=/ko/matching_list.php";
				});
			}else{
				jAlert(jqXHR[1],"알림");
			}
			*/
		}else{
			location.href=href;
		}
	});
})

//텝클릭
$(".matching-tab a").click(function(){
	idx=$(this).index();
	$(".matching-tab a").removeClass("active");
	$(this).addClass("active");
	$(".column-list .column-list-re").hide();
	$(".column-list .column-list-re:eq("+idx+")").show();
})
$(".matching-tab a:eq(0)").click();

vtotal=<?=$cust_cnt;?>;
vtotal2=<?=$supp_cnt?>;

v_len=$(".column-list .column-list-re:eq(0) .matching-item:visible").length;
v_len2=$(".column-list .column-list-re:eq(1) .matching-item:visible").length;

$(".column-list .column-list-re:eq(0) .searchCompanyListLoad .length").text("("+(vtotal-v_len)+")");
$(".column-list .column-list-re:eq(1) .searchCompanyListLoad .length").text("("+(vtotal2-v_len2)+")");

<?if($msinfo['membership_parent_no']>1 and $msinfo){?>
$(".searchCompanyListLoad").click(function(){
	idx=$(this).parent().index();
	if(idx==0) v_len=$(".column-list-re:eq(0) .matching-item:visible").length;
	else v_len=$(".column-list-re:eq(1) .matching-item:visible").length;
	add=v_len+6;
	$(".column-list-re:eq("+idx+") .matching-item:lt("+add+")").show();
	v_len=$(".column-list-re:eq("+idx+") .matching-item:visible").length;
	if(idx==0) $(".column-list-re:eq("+idx+") .searchCompanyListLoad .length").text("("+(vtotal-v_len)+")");
	else $(".column-list-re:eq("+idx+") .searchCompanyListLoad .length").text("("+(vtotal2-v_len)+")");
})
<?}else{?>
$(".searchCompanyListLoad").click(function(){
	jAlert("맴버쉽 요금제를 가입하면 서비스를 이용하실 수 있습니다.","알림",function(){
		location.href="/ko/plan.php";
	});
})
<?}?>
</script>
<?
include_once(G5_THEME_PATH.'/tail.php');
?>