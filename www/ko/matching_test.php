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
?>

<style>
    #sVisual,
    #pageTop{display: none}
    #container{max-width: initial; padding: 0}
</style>

<div id="matching_index" class="sSection tac">
    <div class="inner">
        <h4 class="mTitle mini cfff">MACOMAEX</h4>
        <h4 class="mTitle cfff">신규거래처 발굴 고민이신가요? <br>매커멕스가 찾아드립니다!</h4>

        <div class="matching mVisual_search bxShadow">
			<form action="/ko/matching_list2.php" method="POST" id="match">
			<input type="hidden" name="cominfo" id="cominfo">
			</form>
            <input type="text" name="comnum" id="comnum" class="comnum_chk" maxlength="12" placeholder="법인 사업자번호를 입력하세요">
            <button type="button" id="sub_submit2">확인</button>
        </div>
    </div>
</div>


<script>
$("#sub_submit2").click(function(){
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
				$("#match").attr("action","/ko/matching_list_test.php");
				$("#match").submit();
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
</script>
<?
include_once(G5_THEME_PATH.'/tail.php');
?>