<?
include_once("./_common.php");

$board['bo_10']="6"; // 1뎁스
$board['bo_9']="3"; // 2뎁스
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
	if($w==d && $idx){
		sql_query("delete from g5_favorite where ms_id='{$idx}' and mb_id='{$member['mb_id']}'");
	}
	$query="SELECT *, (select k_code_name from kssc where k_code=B.BZC_CD) as BZC_NAME  from g5_favorite A left join company B ON A.KEDCD=B.KEDCD where A.mb_id='{$member['mb_id']}' ";
	//echo $query;
	$result=sql_query($query);
}

$view_chk="false";
if($member['mb_id'] && $matcing['receive_approval']=="Y") $view_chk="true";
//맴버쉽 정보
$today = date("Y-m-d");
$msinfo=sql_fetch("select * from g5_membership_status where uid='{$member['mb_id']}' and date_begin <= '{$today }' and date_end >='{$today }'" );
if($msinfo['uid']) {
	if($msinfo['open_able_cnt']>0 or $msinfo['membership_parent_no']>1) {
		$view_chk="true";
		//무료인경우만 카운트
		if($msinfo['membership_parent_no']=="1") {
			sql_query("update g5_membership_status set open_able_cnt=open_able_cnt-1 where uid='{$member['mb_id']}'");
			//맴버쉽 히스토리 저장
			sql_query("insert g5_membership_history set uid='{$member['mb_id']}', kind='USE', count_number='열람 1건 사용', regdate=now(), order_no='', open_parent_no='{$cd}', matching_parent_no='', membership_parent_no='{$msinfo['membership_parent_no']}'");
		}
	}
}

$total_cnt=mysqli_num_rows($result);
?>
<style>
	#pageTop,
	#sVisual{display: none}
</style>
<div id="sub-visual"><div class="inConts">
	<h2>비즈니스 성공의 시작점 매칭.</h2>
	<p>멤버십 업그레이드로 더 많은 가능성을 열어보세요.</p>
	<div btn><a href="javascript:void(0);">업그레이드 하기</a></div>
</div></div>

<div class="matching-tab">
	<a href="my_interested_test.php" class="active">관심기업</a><!-- 활성화 -->
	<a href="my_matching.php#received">받은매칭</a>
	<a href="my_matching.php#sent">보낸매칭</a>
	<a href="my_selected.php">셀렉티드</a>
</div>

<p class="matching-length"><span class="point"><?=$total_cnt?>개</span>의 관심 기업</p>
<div class="matching-list">
<?for ($i=0; $row=sql_fetch_array($result); $i++) {
	$match=sql_fetch("select * from g5_matching where del != 'D' and KEDCD='{$row['KEDCD']}' and request_id='{$member['mb_id']}'");
	if($match['receive_approval']=="Y") $receve_info=getUserInfo($match['receive_id']);
	if(!$match['receive_approval']) {
		$match=sql_fetch("SELECT * from g5_matching where del != 'D' and  receive_id='{$member['mb_id']}' and KEDCD='{$row['KEDCD']}'");//받은매칭요청
		if($match['receive_approval']=="Y") $receve_info=getUserInfo($match['request_id']);
	}

	?>
	<div class="matching-item" kedcd="<?=$row['KEDCD']?>">
		<p class="name"><strong><?=$row['ENP_NM']?></strong>
			<button type="button" star class="active star_btn" kedcd="<?=$row['KEDCD']?>"><img src="/images/icon_star.svg" alt=""></button>
		</p>
		<div class="info-area">
			<dl>
				<dt>주<i></i>소</dt>
				<dd><?=$row['LOC_ADDRA']?></dd>
			</dl>
			<dl>
				<dt>종<i></i>목</dt>
				<dd><?=$row['BZC_NAME']?></dd>
			</dl>
			<dl>
				<dt>연<i></i>락<i></i>처</dt>
				<?if(!$match or $match['receive_approval']=="N"){?>
				<dd>매칭 성공시 공개</dd>
				<?}else{?>
				<dd><?=$receve_info['mb_6']?> <?=$receve_info['mb_7']?> (<?=$receve_info['mb_8']?>)</dd>
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
	<div class="NoData">등록된 관심 기업이 없습니다.</div>
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
<?/*
//매칭요청 삭제
	$("body").on("click","#mcancel",function(){
		kedcd = $(this).attr("kedcd");
		jCustomConfirm("매칭요청을 취소 하시겠습니까?","알림","확인","취소",function(a){
			if(a) {
				$(".popup_content #kedcd").val(kedcd);
				Formdata=$(".popup_content input, textarea").serialize();
				$.post( "../get_set.php?"+Formdata+"&chk=cancel")
				.done(function(jqXHR) {
					if(jqXHR==500){
						jAlert("요청에 문제가 있습니다.","알림");
						return false;
					}else{
						$(".matching_lish li[kedcd='"+kedcd+"'] .matching_list_top_btns a:eq(0)").removeClass("accept").addClass("submit").text("매칭요청").attr("id","popup_open");
					}
				});
			}
		})
	})
*/?>
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


//관심기업 삭제
	function fdelete(idx){
		jCustomConfirm("정말 삭제하시겠습니까?","알림","확인","취소",function(a){
			if(a) location.href='?w=d&idx='+idx;
		})
	}

	$("body").on("click", "button[star]", function(){ // 관심기업
		if(!$(this).hasClass("active")){
			$(this).addClass("active")
		}else{
			$(this).removeClass("active");
		}
	});

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
