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
			SELECT    KEDCD, ENP_NM, SALES, rank FROM (
			SELECT    KEDCD, ENP_NM,
					  SALES,
					  CASE
					  WHEN @prev_value = SALES THEN @vRank
					  WHEN @prev_value := SALES THEN @vRank := @vRank + 1
					  END AS rank
			FROM company AS p , (SELECT @vRank := 0, @prev_value := NULL) AS r
			WHERE BZC_CD = '{$com['BZC_CD']}'
			ORDER BY SALES DESC
			) AS CNT WHERE KEDCD = '{$com['KEDCD']}';
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
				SELECT    KEDCD, ENP_NM, SALES, rank FROM (
				SELECT    KEDCD, ENP_NM,
						  SALES,
						  CASE
						  WHEN @prev_value = SALES THEN @vRank
						  WHEN @prev_value := SALES THEN @vRank := @vRank + 1
						  END AS rank
				FROM company AS p , (SELECT @vRank := 0, @prev_value := NULL) AS r
				WHERE BZC_CD like '{$com['BZC_CD']}'
				ORDER BY RAND()
				) AS CNT WHERE rank !={$result['rank']}  and rank BETWEEN 1 AND 20 LIMIT 5
				";
			}else{
				//일반적인 업체
				//구간에서 랜덤 5개 추출
				$sql="
				SELECT    KEDCD, ENP_NM, SALES, rank FROM (
				SELECT    KEDCD, ENP_NM,
						  SALES,
						  CASE
						  WHEN @prev_value = SALES THEN @vRank
						  WHEN @prev_value := SALES THEN @vRank := @vRank + 1
						  END AS rank
				FROM company AS p , (SELECT @vRank := 0, @prev_value := NULL) AS r
				WHERE BZC_CD like '{$com['BZC_CD']}'
				ORDER BY RAND()
				) AS CNT WHERE rank BETWEEN {$top30} AND {$top50} LIMIT 5
				";
			}
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

			$update_q=" UPDATE company_client A, (SELECT KEDCD, LOC_ADDRA, BZC_CD FROM company) B
										SET A.TX_ADDR = B.LOC_ADDRA, A.TX_BZC_CD=B.BZC_CD
									  WHERE A.TXPL_KEDCD = B.KEDCD AND A.KEDCD in ('{$REC}')";
									  //echo $update_q;
			/*
			sql_query($update_q);
			*/
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
					$r_query="SELECT    KEDCD, ENP_NM, SALES, rank FROM (
					SELECT    KEDCD, ENP_NM,
							  SALES,
							  CASE
							  WHEN @prev_value = SALES THEN @vRank
							  WHEN @prev_value := SALES THEN @vRank := @vRank + 1
							  END AS rank
					FROM company AS p , (SELECT @vRank := 0, @prev_value := NULL) AS r
					WHERE BZC_CD like '{$row['BZC_CD']}'
					ORDER BY RAND()
					) AS CNT WHERE KEDCD='{$row['TXPL_KEDCD']}'";
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
					if($row['LOC_ADDRA']) $cust_sub_rank[$row['TXPL_RANK']]=$row;
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

ksort($cust_sub_rank);
ksort($supp_sub_rank);
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
    #pageTop{display: none}
	#CompanyListTable tbody tr:nth-child(n+6) {display:none; }
</style>

<div id="matching_list" class="">
    <div class="myCompanyInfo">
        <h4 class="myCompanyTitle fs35 lh1 c111 fw550 mgb3">내 회사 정보</h4>

        <div class="myCompanyDesc">
            <ul>
                <li>
                    <div class="title">기업명</div>
                    <div class="text"><?=$data->CONTENTS->ENPintr0000->enp_nm_trd;?></div>
                </li>
                <li>
                    <div class="title">사업자번호</div>
                    <div class="text"><?=$bzno_c?></div>
                </li>
				<?
				/*
                <li>
                    <div class="title">판매거래처</div>
                    <div class="text"><?=$supp_txt?></div>
                </li>
				*/
				?>
                <li>
                    <div class="title">대표자명</div>
                    <div class="text"><?=$reper_name?></div>
                </li>
                <li>
                    <div class="title">주소</div>
                    <div class="text"><?=$loc_addra?></div>
                </li>
                <li>
                    <div class="title">매출액</div>
                    <div class="text"><?=number_format($take)?> 원</div>
                </li>
				<?
				/*
                <li>
                    <div class="title">구매거래처</div>
                    <div class="text"><?=$cust_txt?></div>
                </li>
				*/
				?>
                <li>
                    <div class="title">종목</div>
                    <div class="text"><?=$bzc_nm?></div>
                </li>
                <li>
                    <div class="title">기업유형</div>
                    <div class="text">
					<?if($enp_typt[$enp_typ]){?><?=$enp_typt[$enp_typ]?><?}?>
					<?if($enp_fcdt[$enp_fcd]){?> / <?=$enp_fcdt[$enp_fcd]?><?}?>
					</div>
                </li>
            </ul>
        </div>
    </div>

    <div class="searchCompanyList">
        <h4 class="barTitle">내 동종업계<span class="point">(<?=$bzc_nm?>)</span> 기업들의 거래처 검색 결과입니다. </h4>

        <table id="CompanyListTable">
            <thead>
                <tr>
                    <td>구매거래처 <span class="length point">(<?=$cust_cnt?>)</span></td>
                    <td>판매거래처 <span class="length point">(<?=$supp_cnt?>)</span></td>
                </tr>
            </thead>
            <tbody>
				<?
				$cpoint = current($cust_sub_rank);
				$spoint = current($supp_sub_rank);
				for($i=0;$i<max($cust_cnt,$supp_cnt);$i++){
					//$add_info=sql_fetch("select * from company where KEDCD='{$cust_sub[$i][TXPL_KEDCD]}'");
				?>
                <tr>
                    <td>
						<?if($cpoint['TXPL_NM']){?>
                        <div class="company_top">
                            <div class="title"><?=$cpoint['TXPL_NM']?></div>
                            <a href="/ko/matching_view.php?cd=<?=$cpoint['TXPL_KEDCD']?>" class="viewDetail">상세보기</a>
                        </div>
                        <ul class="company_desc">
                            <li>
                                <div class="title">소재지</div>
                                <div class="text"><?=$cpoint['LOC_ADDRA']?></div>
                            </li>
                            <li>
                                <div class="title">동종 업계 순위</div>
                                <div class="text"><?if($cpoint['TXPL_RANK']<9999999){?><?=$cpoint['TXPL_RANK']?> 위<?}?></div>
                            </li>
                            <li>
                                <div class="title">종목</div>
                                <div class="text"><?=$cpoint['k_code_name']?></div>
                            </li>
                        </ul>
						<?}?>
                    </td>
                    <td>
						<?if($spoint['TXPL_NM']){?>
                        <div class="company_top">
                            <div class="title"><?=$spoint['TXPL_NM']?></div>
                            <a href="/ko/matching_view.php?cd=<?=$spoint['TXPL_KEDCD']?>" class="viewDetail">상세보기</a>
                        </div>
                        <ul class="company_desc">
                            <li>
                                <div class="title">소재지</div>
                                <div class="text"><?=$spoint['LOC_ADDRA']?></div>
                            </li>
                            <li>
                                <div class="title">동종 업계 순위</div>
                                <div class="text"><?if($spoint['TXPL_RANK']<9999999){?><?=$spoint['TXPL_RANK']?> 위<?}?></div>
                            </li>
                            <li>
                                <div class="title">종목</div>
                                <div class="text"><?=$spoint['k_code_name']?></div>
                            </li>
                        </ul>
						<?}?>
                    </td>
                </tr>
				<?
				$cpoint = next($cust_sub_rank);
				$spoint = next($supp_sub_rank);
				}?>
            </tbody>
        </table>
<?if(max($cust_cnt,$supp_cnt)<5){?>
		<dl class="next-month">
			<dt>다음 달, <strong>더 많은 거래처들</strong>이</dt>
			<dd><span>매커멕스</span>에 찾아와요</dd>
		</dl>
<?}?>


		<?if(max($cust_cnt,$supp_cnt)>0){?>
        <a href="javascript:;" class="searchCompanyListLoad">더보기 <span class="length">(<?=max($cust_cnt,$supp_cnt)?>)</span></a>
		<?}?>
    </div>
</div>
<script>
$("#CompanyListTable a").click(function(){
	event.preventDefault();
	var href=$(this).attr("href");
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
			location.href=href;
		}
	});
})
vtotal=<?=max($cust_cnt,$supp_cnt)?>;
<?if($msinfo['membership_parent_no']>1 and $msinfo){?>
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
v_len=$("#CompanyListTable tbody tr:visible").length;
$(".searchCompanyListLoad .length").text("("+(vtotal-v_len)+")");
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