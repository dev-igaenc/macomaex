<?
include_once("./_common.php");
goto_url('/ko/my_matching.php#sent');

$board['bo_10']="6"; // 1뎁스
$board['bo_9']="2"; // 2뎁스
//$board['bo_8']="1"; // 3뎁스
//$board['bo_7']="1"; // 4뎁스
// $board['bo_5'] = ""; // 1뎁스 제목
// $board['bo_6'] = ""; // 1뎁스 설명
// $board['bo_subject'] = "";  // 2뎁스 제목
//$board['bo_4'] = $config['cf_title']."의 혜택을 확인하세요.";  // 2뎁스 설명

//로그인 체크
login_chk('account_trades_sent');

include_once(G5_THEME_PATH.'/head.php');

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

$sql_common = " from g5_matching A LEFT JOIN company B on A.KEDCD=B.KEDCD";
$sql_search = " where A.del != 'D' and request_id='{$member['mb_id']}' ";

//전체
$sql = " select count(*) as cnt
            {$sql_common}
            {$sql_search}
            {$sql_order} ";
//매칭중
$sql2 = " select count(*) as cnt
            {$sql_common}
            {$sql_search} and receive_approval='N'
            {$sql_order} ";
//매칭성공
$sql3 = " select count(*) as cnt
            {$sql_common}
            {$sql_search} and receive_approval='Y'
            {$sql_order} ";
$row = sql_fetch($sql);
$row2 = sql_fetch($sql2);
$row3 = sql_fetch($sql3);
$atotal_count = $row['cnt'];
$atotal_count2 = $row2['cnt'];
$atotal_count3 = $row3['cnt'];

if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case 'appro' :
            $sql_search .= " (receive_approval = '{$stx}') ";
            break;
        default :
            $sql_search .= " ({$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}
$sql = " select count(*) as cnt
            {$sql_common}
            {$sql_search}
            {$sql_order} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];
$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
 $from_record = ($page - 1) * $rows; // 시작 열을 구함


$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산

$sql = " select *, (select k_code_name from kssc_11 where k_code=B.BZC_CD) as BZC_NAME
            {$sql_common}
            {$sql_search}
            {$sql_order}
            limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$favorite=getFavorite($member['mb_id']);
?>
<style>
	#pageTop,
	#sVisual{display: none}
</style>
<div id="sub-visual"><div class="inConts">
	<h2>비즈니스 성공의 시작점 매칭.</h2>
	<p>멤버십 업그레이드로 더 많은 가능성을 열어보세요.</p>
	<div btn><a href="/ko/plan.php">업그레이드 하기</a></div>
</div></div>

<div class="matching-tab">
	<a href="my_matching.php#interested">관심기업</a>
	<a href="my_matching_r.php">받은매칭</a><!-- 활성화 -->
	<a href="my_matching_s.php"class="active">보낸매칭</a>
	<a href="my_selected.php">셀렉티드</a>
</div>

<div class="matching-tab" d2>
	<a href="my_matching_s.php" <?if(!$sfl && !$stx){?>class="active"<?}?>>전체<span><?=number_format($atotal_count);?></span></a><!-- 활성화 -->
	<a href="my_matching_s.php?sfl=appro&stx=N" <?if($sfl=="appro" && $stx=="N"){?>class="active"<?}?>>매칭중<span><?=number_format($atotal_count2);?></span></a>
	<a href="my_matching_s.php?sfl=appro&stx=Y" <?if($sfl=="appro" && $stx=="Y"){?>class="active"<?}?>>매칭성공 <span><?=number_format($atotal_count3);?></span></a>
</div>

<div class="matching-list">
<?for ($i=0; $row=sql_fetch_array($result); $i++) {
	$s_info=sql_fetch("select * from   g5_member where mb_id='{$row['receive_id']}'");
	$f_chk=in_array($row['KEDCD'],$favorite);
?>
	<div class="matching-item" kedcd="<?=$row['KEDCD']?>"><!-- 전체 -->
		<p class="name"><strong><?=$row['ENP_NM']?></strong>
			<button type="button" star class="<?if($f_chk){?>active<?}?> star_btn" kedcd="<?=$row['KEDCD']?>"><img src="/images/icon_star.svg" alt=""></button>
		</p>
		<div class="info-area">
			<dl>
				<dt>주<i></i>소</dt>
				<dd><?=$row['LOC_ADDRA']?> <?=$row['LOC_ADDRB']?></dd>
			</dl>
			<dl>
				<dt>종<i></i>목</dt>
				<dd><?=$row['BZC_NAME']?></dd>
			</dl>
			<dl>
				<dt>담<i></i>당<i></i>자</dt>
				<?if($row['receive_approval']=="Y"){?>
				<dd><?=$s_info['mb_8']?></dd>
				<?}else{?>
				<dd>매칭 성공시 공개</dd>
				<?}?>
			</dl>
			<dl>
				<dt>메<i></i>시<i></i>지</dt>
				<dd message>
					<div>
						<?=nl2br($row['contents']);?>
					</div>

					<button type="button" message-more><span>더보기</span><i><img src="/images/arr_message.png" alt="arrow"></i></button>
				</dd>
			</dl>
		</div>
		<div class="btn-option">
			<a href="javascript:void(0);" class="detail_view" kedcd="<?=$row['KEDCD']?>">상세보기</a>
			<?if($row['receive_approval']=="N"){?>
			<a id="mdelete" idx="<?=$row['no']?>" href="javascript:void(0);">매칭 취소</a>
			<?}?>
			<?if($row['receive_approval']=="Y"){?>
			<a href="tel:<?=$s_info['mb_8']?>" class="tmVar">담당자 연결하기</a>
			<a href="mailto:<?=$s_info['mb_5']?>" class="pVar">담당자 연결하기</a>
			<?}?>
			<?if($row['receive_approval']=="R"){?>
			<a class="rejected">매칭거절</a>
			<?}?>
			
			<!-- a, button 편하신대로 바꾸시면 됩니다. -->
			<!--a href="javascript:void(0);">수락하기</a-->
			
		</div>
	</div>
<?}?>
<?if ($i==0) {?>
	<div class="NoData">매칭 자료가 없습니다.</div>
<?}?>

	<?echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;sview=1&amp;page='); ?>
</div>

<!--[s] 상세보기 팝업 -->
<div id="company-view-lay" class="layer-base layer-main company-view"><div class="layer-in new">
</div></div>
<!--[e] 상세보기 팝업 -->


<!-- <link rel="stylesheet" href="/build/masonry/isotope-docs.css"> -->
<script type="text/javascript" src="/build/masonry/isotope-docs.min.js"></script>
<script type="text/javascript">
	var $dynamic = $(".matching-list").isotope({
		filter: '*',
		transitionDuration: '0.4s',
		percentPosition: true,
		//layoutMode: 'fitRows', // 기본
		layoutMode: 'masonry', // 벽돌
		masonry: {
			// use element for option
			columnWidth: '.matching-item',
			//gutter: 80
		}
	});

	$("body").on("click", "button[message-more]", function(){ // 메시지 더보기
		if(!$(this).closest("dd").hasClass("active")){
			$(this).closest("dd").addClass("active")
			$(this).find("span").text("내용접기")
		}else{
			$(this).closest("dd").removeClass("active");
			$(this).find("span").text("더보기")
		}
		$dynamic.isotope({ filter: '*' });
	});

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
				if (window.MacomaLoginGuard && typeof window.MacomaLoginGuard.show === "function") {
					window.MacomaLoginGuard.show({
						descriptionText: "기업 상세 조회는 로그인 후 이용하실 수 있습니다.",
						loginUrl: "/bbs/login.php?url=/ko/matching_list.php",
						signupUrl: (window.MacomaLoginGuard && typeof window.MacomaLoginGuard.buildSignupUrl === "function")
							? window.MacomaLoginGuard.buildSignupUrl("/ko/matching_list.php")
							: "/signup"
					});
				} else {
					location.href="/bbs/login.php?url=/ko/matching_list.php";
				}
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

//매칭수락
	$("body").on("click","#maccept",function(){
		$this=$(this);
		chk=$this.text();
		idx=$this.attr("idx");
		typ=$this.attr("typ");
		if(typ=="accept") atxt="정말 수락하시겠습니까?";
		if(typ=="reject") atxt="정말 거절하시겠습니까?";
		jCustomConfirm(atxt,"알림","확인","취소",function(a){
			if(a) {
				$.post( "../get_set.php", {
					type: 'matching_accept',
					idx : idx,
					chk : chk,
					typ:typ
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
		jCustomConfirm("정말 취소 하시겠습니까?","알림","확인","취소",function(a){
			if(a) {
				$.post( "../get_set.php", {
					type: 'matching_delete',
					idx: idx
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
