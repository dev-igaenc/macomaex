<?
include_once("./_common.php");

$board['bo_10']="6"; // 1뎁스
$board['bo_9']="2"; // 2뎁스
//$board['bo_8']="1"; // 3뎁스
//$board['bo_7']="1"; // 4뎁스
// $board['bo_5'] = ""; // 1뎁스 제목
// $board['bo_6'] = ""; // 1뎁스 설명
// $board['bo_subject'] = "";  // 2뎁스 제목
//$board['bo_4'] = $config['cf_title']."의 혜택을 확인하세요.";  // 2뎁스 설명

//로그인 체크
login_chk();

include_once(G5_THEME_PATH.'/head.php');

$sql_common = " from g5_matching A LEFT JOIN company B on A.KEDCD=B.KEDCD";

//매칭요청한
$sql_search = " where A.del != 'D' and request_id='{$member['mb_id']}' ";

if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case 'mb_id' :
            $sql_search .= " ({$sfl} = '{$stx}') ";
            break;
        default :
            $sql_search .= " ({$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

if (!$sst) {
    $sst  = "regdate";
    $sod = "desc";
}
$sql_order = " order by {$sst} {$sod} ";

$sql = " select count(*) as cnt, GROUP_CONCAT(A.KEDCD) as KEDCD
            {$sql_common}
            {$sql_search}
            {$sql_order} ";
$row = sql_fetch($sql);
//echo $sql;
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if (($page < 1 && $sview==1) or !$sview) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
 $from_record = ($page - 1) * $rows; // 시작 열을 구함
$spage=$page;

$sql = " select *, (SELECT k_code_name from kssc where k_code=B.BZC_CD) as BZC_NAME
            {$sql_common}
            {$sql_search}
            {$sql_order}
            limit {$from_record}, {$rows} ";

$result = sql_query($sql);
//echo $sql;

//매칭요청받은
$rsql_common = " from g5_matching";
$r_sql_search = " where del != 'D' and receive_id='{$member['mb_id']}' ";

if ($stx) {
    $r_sql_search .= " and ( ";
    switch ($sfl) {
        case 'mb_id' :
            $r_sql_search .= " ({$sfl} = '{$stx}') ";
            break;
        default :
            $r_sql_search .= " ({$sfl} like '%{$stx}%') ";
            break;
    }
    $r_sql_search .= " ) ";
}
$r_sql = " select count(*) as cnt
            {$sql_common}
            {$r_sql_search}
            {$sql_order} ";

$r_row = sql_fetch($r_sql);
$r_total_count = $r_row['cnt'];

$r_rows = $config['cf_page_rows'];
$r_total_page  = ceil($r_total_count / $r_rows);  // 전체 페이지 계산

if (($page < 1  && $rview==1) or !$rview) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
else $page=$_GET['page'];
//echo $page;
 $rfrom_record = ($page - 1) * $rows; // 시작 열을 구함
$r_sql = " select *
            {$sql_common}
            {$r_sql_search}
            {$sql_order}
            limit {$rfrom_record}, {$rows} ";
$r_result = sql_query($r_sql);
//echo $r_sql;
/*
	$sql="SELECT * from g5_matching where mb_id='{$memeber['mb_id']}'";
	$s_result=sql_query($sql);
	$sql="SELECT * from g5_matching where t_id='{$memeber['mb_id']}'";
	$r_result=sql_query($sql);
	//for ($i=0; $row=sql_fetch_array($result); $i++) {
*/
   //for ($i=0; $row=sql_fetch_array($result); $i++) {

?>
<div class="tabWrap">
    <ul class="tabNav">
        <li>매칭 요청 받은 (<span class="length"><?=number_format($r_total_count);?></span>)</li>
        <li>매칭 요청 보낸 (<span class="length"><?=number_format($total_count);?></span>)</li>
    </ul>

    <div class="tabContent">
        <ul class="matching_lish">
			<?for ($i=0; $row=sql_fetch_array($r_result); $i++) {
				$r_info=sql_fetch("select *, (SELECT k_code_name from kssc where k_code=A.BZC_CD) as BZC_NAME from company A LEFT JOIN  g5_member B ON A.KEDCD=B.mb_company where B.mb_id='{$row['request_id']}'");
			?>
            <li>
                <div class="matching_list_top">
                    <h4 class="company"><?=$r_info['ENP_NM']?></h4>
                    <div class="matching_list_top_btns">
						<?if($row['receive_approval']=="N"){?>
                        <a id="maccept" idx="<?=$row['no']?>" class="accept">수락</a>
						<?}?>
						<?if($row['receive_approval']=="Y"){?>
                        <a id="maccept" idx="<?=$row['no']?>" class="accepted">수락됨</a>
						<?}?>
                        <a id="mdelete" idx="<?=$row['no']?>" class="delete">삭제</a>
                        <!-- a, button 편하신대로 바꾸시면 됩니다. -->
                    </div>
                </div>
                <div class="matching_list_info">
                    <ul>
                        <li class="address">
                            <div class="title">주소</div>
                            <div class="text"><?=$r_info['LOC_ADDRA']?> <?=$r_info['LOC_ADDRB']?></div>
                        </li>
                        <li class="category">
                            <div class="title">종목</div>
                            <div class="text"><?=$r_info['BZC_NAME']?></div>
                        </li>
                        <li class="manager">
                            <div class="title">담당자</div>
							<?if($row['receive_approval']=="Y"){?>
							<div class="text"><?=$r_info['mb_6']?> <?=$r_info['mb_7']?></div>
							<?}else{?>
                            <div class="text">수락 후 공개</div>
							<?}?>
                        </li>
                        <li class="contact">
                            <div class="title">전화번호</div>
							<?if($row['receive_approval']=="Y"){?>
							<div class="text"><?=$r_info['mb_8']?></div>
							<?}else{?>
                            <div class="text">수락후 전화번호 공개</div>
							<?}?>
                        </li>
                    </ul>
                </div>
                <div class="matching_list_message">
                    <?=nl2br($row['contents']);?>
                </div>
            </li>
			<?}?>
			<?if ($i==0) {?>
			<li class="NoData">매칭 자료가 없습니다.</li>
			<?}?>
        </ul>
		<?
		if($rview==1 && $page<1) $page=1;
		echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $r_total_page, '?'.$qstr.'&amp;rview=1&amp;page='); 
		?>
    </div>
    <div class="tabContent">
        <ul class="matching_lish">
			<?for ($i=0; $row=sql_fetch_array($result); $i++) {
				$s_info=sql_fetch("select * from   g5_member where mb_id='{$row['receive_id']}'");
			?>
            <li>
                <div class="matching_list_top">
                    <h4 class="company"><?=$row['ENP_NM']?></h4>
                    <div class="matching_list_top_btns">
						<?if($row['receive_approval']=="Y"){?>
                        <a href="matching_view.php?cd=<?=$row['KEDCD']?>" class="accepted">수락됨</a>
						<?}else{?>
						<a class="accept">대기</a>
						<?}?>
                        <a href="javascript:;" id="mdelete" idx="<?=$row['no']?>" class="delete">삭제</a>
                        <!-- a, button 편하신대로 바꾸시면 됩니다. -->
                    </div>
                </div>
                <div class="matching_list_info">
                    <ul>
                        <li class="address">
                            <div class="title">주소</div>
                            <div class="text"><?=$row['LOC_ADDRA']?> <?=$row['LOC_ADDRB']?></div>
                        </li>
                        <li class="category">
                            <div class="title">종목</div>
                            <div class="text"><?=$row['BZC_NAME']?></div>
                        </li>
                        <li class="manager">
							<?if($row['receive_approval']=="Y"){?>
							<div class="text"><?=$s_info['mb_6']?> <?=$s_info['mb_7']?></div>
							<?}else{?>
                            <div class="text">수락 후 공개</div>
							<?}?>
                        </li>
                        <li class="contact">
                            <div class="title">전화번호</div>
							<?if($row['receive_approval']=="Y"){?>
							<div class="text"><?=$s_info['mb_8']?></div>
							<?}else{?>
                            <div class="text">수락후 전화번호 공개</div>
							<?}?>
                        </li>
                    </ul>
                </div>
                <div class="matching_list_message">
                    <?=nl2br($row['contents']);?>
                </div>
            </li>
			<?}?>
        </ul>
		<?
		if($sview==1 && $page<1) $page=1;
		else $page=$spage;
		echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;sview=1&amp;page='); 
		?>
    </div>
</div>



<script>
//매칭수락
	$("body").on("click","#maccept",function(){
		$this=$(this);
		chk=$this.text();
		idx=$this.attr("idx");
		if(chk=="수락") atxt="정말 수락하시겠습니까?";
		if(chk=="수락됨") atxt="수락을 취소 하시겠습니까?";
		jCustomConfirm(atxt,"알림","확인","취소",function(a){
			if(a) {
				$.post( "../get_set.php", {
					type: 'matching_accept',
					idx : idx,
					chk : chk
				})
				.done(function(jqXHR) {
					if(jqXHR==1000) {
						location.reload();
					}else {
						jAlert("처리에 문제가 있습니다.","알림");
					}
				});
			}
		});
	})
//매칭삭제
	$("body").on("click","#mdelete",function(){
		$this=$(this);
		idx=$this.attr("idx");
		jCustomConfirm("정말 삭제하시겠습니까?","알림","확인","취소",function(a){
			if(a) {
				$.post( "../get_set.php", {
					type: 'matching_delete',
					idx: idx
				})
				.done(function(jqXHR) {
					if(jqXHR==1000) {
						num=parseInt($(".tabNav .active .length").text().replace("(","").replace(")",""));
						$(".tabNav .active .length").text((num-1))
						$this.parents("li").remove();

					}else {
						jAlert("처리에 문제가 있습니다.","알림");
					}
				});
			}
		});
	})
    // const tabWrap = document.querySelector(".tabWrap")
	//텝선택
    const tabNavLi = document.querySelectorAll(".tabWrap .tabNav > li")
    const tabContent = document.querySelectorAll(".tabWrap .tabContent")

    tabNavLi.forEach((element, index) => {
        element.addEventListener("click", () => {
            tabNavLi.forEach((e) => e.classList.remove("active"))
            element.classList.add("active")

            tabContent.forEach((e) => e.classList.remove("active"))
            tabContent[index].classList.add("active")
        })
    })
	<?if($rview==1 or (!$rview && !$sview)){?>
		tabNavLi[0].click();
	<?}else{?>
		tabNavLi[1].click();
	<?}?>
</script>

<?
include_once(G5_THEME_PATH.'/tail.php');
?>