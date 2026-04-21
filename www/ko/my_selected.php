<?
include_once("./_common.php");

$board['bo_10']="6"; // 1뎁스
$board['bo_9']="6"; // 2뎁스
//$board['bo_8']="1"; // 3뎁스
//$board['bo_7']="1"; // 4뎁스
// $board['bo_5'] = ""; // 1뎁스 제목
// $board['bo_6'] = ""; // 1뎁스 설명
// $board['bo_subject'] = "";  // 2뎁스 제목
//$board['bo_4'] = $config['cf_title']."의 혜택을 확인하세요.";  // 2뎁스 설명

//로그인 체크
login_chk();

include_once(G5_THEME_PATH.'/head.php');


if($member['mb_id']){

	//시간지난 셀렉티드 중지
	$now = date("Y-m-d");
	sql_query("UPDATE g5_selected_data set s_status='E' WHERE DATE(s_start)<='{$now}' AND DATE(s_end)<'{$now}' AND s_status='S' and mb_id='{$member['mb_id']}'");
	sql_query("
		UPDATE g5_selected AS gs
		JOIN (
			SELECT s_parent, COUNT(*) AS cnt
			FROM g5_selected_data
			WHERE s_status = 'S'
			GROUP BY s_parent
		) AS sd
		ON gs.s_no = sd.s_parent
		SET gs.s_cnt = sd.cnt, gs.s_modi = NOW()
	");

	$query="SELECT *, (select k_code_name from kssc where k_code=B.BZC_CD) as BZC_NAME from g5_selected_data A left join company B ON A.KEDCD=B.KEDCD where A.mb_id='{$member['mb_id']}' and A.s_status='S' ";
	//echo $query;
	$result=sql_query($query);
	$total_count=mysqli_num_rows($result);
}


$view_chk = "false";
//맴버쉽 정보
$today = date("Y-m-d");
$msinfo=sql_fetch("select * from g5_membership_status where uid='{$member['mb_id']}' and date_begin <= '{$today }' and date_end >='{$today }'" );
if($msinfo['uid']) {
	if($msinfo['open_able_cnt']>0 or $msinfo['membership_parent_no']>1) {
	}
}
//즐겨찾기
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
	<a href="my_matching.php#received">받은매칭</a><!-- 활성화 -->
	<a href="my_matching.php#sent">보낸매칭</a>
	<a href="my_selected.php" class="active">셀렉티드</a>
</div>

<p class="matching-length"><span class="point"><?=number_format($total_count);?>개</span>의 선별된 리스트</p>
<div class="matching-list">
<?for ($i=0; $row=sql_fetch_array($result); $i++) {
		$match=sql_fetch("select * from g5_matching where del != 'D' and KEDCD='{$row['KEDCD']}' and request_id='{$member['mb_id']}'");
		if(!is_array($match)) $match = array();
		if($match['receive_approval']=="Y") $receve_info=getUserInfo($match['receive_id']);
		if(!$match['receive_approval']) {
			$match=sql_fetch("SELECT * from g5_matching where del != 'D' and  receive_id='{$member['mb_id']}' and KEDCD='{$row['KEDCD']}'");//받은매칭요청
			if(!is_array($match)) $match = array();
			if($match['receive_approval']=="Y") $receve_info=getUserInfo($match['request_id']);
		}

		$f_chk=in_array($row['KEDCD'],$favorite);
?>
	<div class="matching-item" kedcd="<?=$row['KEDCD']?>">
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
				<?if($match['receive_approval']=="Y"){?>
				<dd><?=$receve_info['mb_8']?></dd>
				<?}else{?>
				<dd>매칭 성공시 공개</dd>
				<?}?>
			</dl>
		</div>
		<div class="btn-option">
			<a href="javascript:" class="detail_view">상세보기</a>
			<?if(!$match['no']){?>
				<a id="popup_open" kedcd="<?=$row['KEDCD']?>" class="submit">매칭요청</a>
			<?}else if($match['receive_approval']=="Y"){?>
				<a class="accepted">매칭완료</a>
			<?}else if($match['receive_approval']=="N"){?>
				<a kedcd="<?=$row['KEDCD']?>" href="javascript:jAlert('매칭 수락 대기중입니다.','알림')" class="accepting" style="cursor:default">수락대기</a>
			<?}else if($match['receive_approval']=="R"){?>
				<a class="rejected" style="cursor:default">매칭거절</a>
			<?}?>
		</div>
	</div>
<?}?>
<?if($i==0){?>
		<div class="NoData">등록된 셀렉티드 기업이 없습니다.</div>
<?}?>
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
	                        <td>신규 거래처 담당자</td>
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


//상세보기
$(".detail_view").click(function(){
	kedcd=$(this).parents("div.matching-item").attr("kedcd");
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


//관심기업 삭제
	function fdelete(idx){
		jCustomConfirm("정말 삭제하시겠습니까?","알림","확인","취소",function(a){
			if(a) location.href='?w=d&idx='+idx;
		})
	}



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
						location.reload();
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
