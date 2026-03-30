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
?>

    <div id="my_interested">
        <ul class="matching_lish">
			<?for ($i=0; $row=sql_fetch_array($result); $i++) {
				$match=sql_fetch("select * from g5_matching where del != 'D' and KEDCD='{$row[KEDCD]}' and request_id='{$member['mb_id']}'");
				if($match['receive_approval']=="Y") $receve_info=getUserInfo($match['receive_id']);
				?>
            <li kedcd="<?=$row[KEDCD]?>">
                <div class="matching_list_top">
                    <h4 class="company"><?=$row[ENP_NM]?></h4>
                    <div class="matching_list_top_btns">
						<?if(!$match['no']){?>
							<a id="popup_open" kedcd="<?=$row[KEDCD]?>" class="submit">매칭요청</a>
						<?}else if($match['receive_approval']!="N"){?>
							<a class="accepted">매칭완료</a>
						<?}else{?>
							<a kedcd="<?=$row[KEDCD]?>" class="accept" style="cursor:default">수락대기</a>
							<?/*<a id="mcancel" kedcd="<?=$row[KEDCD]?>" class="accept">수락대기</a>*/?>
						<?}?>
                        <a href="javascript:fdelete(<?=$row[ms_id]?>)" class="delete">삭제</a>
                    </div>
                </div>
                <div class="matching_list_info">
                    <ul>
                        <li class="address">
                            <div class="title">주소</div>
                            <div class="text"><?=$row[LOC_ADDRA]?> <?=$row[LOC_ADDRB]?></div>
                        </li>
                        <li class="category">
                            <div class="title">종목</div>
                            <div class="text"><?=$row[BZC_NAME]?></div>
                        </li>
                        <li class="manager">
                            <div class="title">담당자</div>
							<?if(!$match or $match['receive_approval']=="N"){?>
                            <div class="text">수락 후 공개</div>
							<?}else{?>
							<div class="text"><?=$receve_info['mb_6']?> <?=$receve_info['mb_7']?> (<?=$receve_info['mb_8']?>)</div>
							<?}?>
                        </li>
                    </ul>
                </div>
            </li>
			<?}?>
			<?if($i==0){?>
			<p class="NoData">등록된 관심 기업이 없습니다.</p>
			<?}?>
        </ul>
    </div>

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
						$(".matching_lish li[kedcd='"+kedcd+"'] .matching_list_top_btns a:eq(0)").removeClass("submit").addClass("accept").text("수락대기").attr("id","mcancel");
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