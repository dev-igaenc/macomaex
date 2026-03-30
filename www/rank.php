<?
set_time_limit ( 0 );
ini_set('memory_limit', '1000M');
include_once('./_common.php');
include_once(G5_LIB_PATH.'/connect.lib.php');
include_once(G5_LIB_PATH.'/outlogin.lib.php');
if($bzno){
//순위 찾기
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
			$total_cnt=sql_fetch("SELECT count(*) AS cnt FROM company WHERE BZC_CD = '{$com['BZC_CD']}'");
			//echo $sql;
			print_r($result);
			if($result['rank']){
				$top30=ROUND($result['rank']*0.3);
				$top50=ROUND($result['rank']*0.5);
				echo "<br>상위 30~50% 구간 ".$top30." ~ ".$top50."<br>";
			
			
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
			for ($i=0; $row=sql_fetch_array($result); $i++) {
				echo $row['rank']." ".$row['ENP_NM']."<br>";
				$REC_KEDCD[]=$row['KEDCD'];
			}
			$REC=implode("','",$REC_KEDCD);
			$sql="SELECT * from company_client where KEDCD in ('{$REC}') and TXPL_KEDCD != '' group by TXPL_KEDCD order by rand() limit 5";
			
			$result=sql_query($sql);
			for ($i=0; $row=sql_fetch_array($result); $i++) {
				echo $row['TXPL_NM']." ".$row['TXPL_KEDCD']."<br>";
				$REC_TXPL_KEDCD[]=$row['TXPL_KEDCD'];
			}
			
			}
	}
}
?>
<form method="get">
	사업자번호<input type="text" name="bzno"> <button>확인</button>
</form>