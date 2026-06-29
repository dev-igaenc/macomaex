<?php
$sub_menu = "500200";
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'w');

include_once('./admin.head.php');

$g5['title'] = '기업매칭 > 제조사 > 기업정보';


if (isset($no)) {
    $sql = "select * from g5_member  where mb_no = '{$no}'";
    $data = sql_fetch($sql);

	if($data['mb_company']) $Companyinfo=getComapyInfo($data['mb_company']);
	if($Companyinfo['BZC_CD']) $Ksscinfo=getKsscInfo($Companyinfo['BZC_CD']);
	if($data['mb_company']) $credit=sql_fetch("select * from company_credit  where KEDCD = '{$data['mb_company']}'");
}else{
	ALERT("정상적으로 이용하세요.","/");
}
add_javascript(G5_POSTCODE_JS, 0);    //다음 주소 js
?>

<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" />
<style>
.frm_input {width:30%;}
//.small3 {width:30%;}
//.small {width:auto;}
</style>
<form id="frm" name="frm" method="post" enctype="multipart/form-data">
<h1><?=$g5['title']?></h1>
<div class="tbl_frm01 tbl_wrap">
    <table class="view_1_form">
    <caption><?php echo $g5['title']; ?></caption>
	<h2>
		제조사 - 기업정보<?if(strpos($Companyinfo['KEDCD'],"M")>-1){ $data_type="M";?>&ensp;<span class="btn btn-sm btn-secondary">직접입력</span><?}else{$data_type="K";?><span class="btn btn-sm btn-primary">한국평가데이터</span><?}?>

	</h2>
	
    <colgroup>
        <col class="grid_2">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="shorts_code">기업명</label></th>
        <td>
			<input type="hidden" name="type" class="frm_input" value="company_update" />
			<input type="hidden" name="KEDCD" class="frm_input" value="<?=$Companyinfo['KEDCD']?>" />
			<input type="text" name="ENP_NM" class="frm_input" value="<?=$Companyinfo['ENP_NM']?>" />
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="shorts_code">영문기업명</label></th>
        <td>
			<input type="text" name="ENP_NM_ENG" class="frm_input" value="<?=$Companyinfo['ENP_NM_ENG']?>" />
        </td>
    </tr>
	    <tr>
        <th scope="row"><label for="shorts_code">법인번호</label></th>
        <td>
			<input type="text" name="CONO_PID" class="frm_input" value="<?=$Companyinfo['CONO_PID']?>" />
        </td>
    </tr>
	    <tr>
        <th scope="row"><label for="shorts_code">기업규모</label></th>
        <td>
			<select name="ENP_SZE" id="ENP_SZE">
				<option value="">선택</option>
				<option value="01">대기업</option>
				<option value="02">중기업</option>
				<option value="03">소기업</option>
				<option value="04">한시성중소기업</option>
				<option value="05">중견기업</option>
				<option value="06">소상공인</option>
				<option value="07">보호대상 중견기업</option>
				<option value="08">판단제외대상</option>
			</select>
			<?if($Companyinfo['ENP_SZE']){?><script>$("#ENP_SZE option[value='<?=$Companyinfo['ENP_SZE']?>']").prop("selected",true);</script><?}?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="shorts_code">회사 홈페이지</label></th>
        <td>
			<input type="text" name="HPAGE_URL" class="frm_input" value="<?=$Companyinfo['HPAGE_URL']?>" />
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="shorts_code">주요제품</label></th>
        <td>
			<input type="text" name="PD_NM" class="frm_input" value="<?=$Companyinfo['PD_NM']?>" />
        </td>
    </tr>
	<tr>
        <th scope="row"><label for="shorts_code">사업자번호</label></th>
        <td>
			<input type="text" name="BZNO" class="frm_input" value="<?=$Companyinfo['BZNO']?>" />
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="shorts_code">설립일자</label></th>
        <td>
			<input type="text" name="ESTB_DT" class="frm_input pickdate" value="<?=$Companyinfo['ESTB_DT']?>" />
        </td>
    </tr>
<script>
      $(function(){
         $('.pickdate').datepicker({
                dateFormat: 'yymmdd' //Input Display Format 변경
                ,showOtherMonths: true //빈 공간에 현재월의 앞뒤월의 날짜를 표시
                ,showMonthAfterYear:true //년도 먼저 나오고, 뒤에 월 표시
                ,changeYear: true //콤보박스에서 년 선택 가능
                ,changeMonth: true //콤보박스에서 월 선택 가능                
                ,showOn: "both" //button:버튼을 표시하고,버튼을 눌러야만 달력 표시 ^ both:버튼을 표시하고,버튼을 누르거나 input을 클릭하면 달력 표시  
                ,buttonImage: "http://jqueryui.com/resources/demos/datepicker/images/calendar.gif" //버튼 이미지 경로
                ,buttonImageOnly: true //기본 버튼의 회색 부분을 없애고, 이미지만 보이게 함
                ,buttonText: "선택" //버튼에 마우스 갖다 댔을 때 표시되는 텍스트                
                ,yearSuffix: "년" //달력의 년도 부분 뒤에 붙는 텍스트
                ,monthNamesShort: ['1','2','3','4','5','6','7','8','9','10','11','12'] //달력의 월 부분 텍스트
                ,monthNames: ['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'] //달력의 월 부분 Tooltip 텍스트
                ,dayNamesMin: ['일','월','화','수','목','금','토'] //달력의 요일 부분 텍스트
                ,dayNames: ['일요일','월요일','화요일','수요일','목요일','금요일','토요일'] //달력의 요일 부분 Tooltip 텍스트
		 });
      })
</script>
	 <tr>
        <th scope="row"><label for="shorts_code">종업원수</label></th>
        <td>
			<input type="text" name="LABORER_SUM" class="frm_input" value="<?=$Companyinfo['LABORER_SUM']?>" />
        </td>
    </tr>
	<tr>
        <th scope="row"><label for="shorts_code">기업유형<?=$Companyinfo['ENP_TYP']?></label></th>
        <td>
			<select name="ENP_TYP" id="ENP_TYP">
				<option value="">선택</option>
				<option value="1">법인기업</option>
				<option value="2">개인기업</option>
			</select>
			<?if($Companyinfo['ENP_TYP']){?><script>$("#ENP_TYP option[value='<?=$Companyinfo['ENP_TYP']?>']").prop("selected",true);</script><?}?>
        </td>
    </tr>
	    <tr>
        <th scope="row"><label for="shorts_code">법인 사업장 소재지</label></th>
        <td>
		<button type="button" class="btn_frmline" onclick="win_zip('frm', 'LOC_ZIP', 'LOC_ADDRA', 'LOC_ADDRB', 'LOC_ADDRB', '');">주소 검색</button>
			<input type="text" name="LOC_ZIP" id="LOC_ZIP" class="frm_input" value="<?=$Companyinfo['LOC_ZIP']?>" placeholder="우편번호" />
			<input type="text" name="LOC_ADDRA" id="LOC_ADDRA" class="frm_input" value="<?=$Companyinfo['LOC_ADDRA']?>" placeholder="주소" />
			<input type="text" name="LOC_ADDRB" id="LOC_ADDRB" class="frm_input" value="<?=$Companyinfo['LOC_ADDRB']?>"  placeholder="나머지주소"/>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="shorts_code">산업분류</label></th>
        <td>
			<input type="text" name="BZC_CD" class="frm_input" value="<?=$Companyinfo['BZC_CD']?>" />
        </td>
    </tr>
		<tr>
        <td colspan="2">
			<button type="button" class="btn btn-sm btn-info pull-right" id="view_1_con">기업정보 적용</button>
        </td>
    </tr>
    </tbody>
    </table>
<script>
$("#view_1_con").click(function(){
	<?if($data_type=="M"){?>
		var result = confirm('정말 적용하시겠습니까?');
	<?}else{?>
		var result = confirm('한국평가데이터 정보는 수정 하더라도 업데이트시 다시 복구될 수 있습니다.\n정말 적용하시겠습니까?');
	<?}?>
	if(!result) {return false;}
	var Formdata=$(".view_1_form input, .view_1_form select").serialize();
		if(Formdata){
			$.post( "./admin_ajax.php?"+Formdata)
			.done(function(jqXHR) {
				if(jqXHR==500){
					alert("요청에 문제가 있습니다.");
				}else{
					alert("적용되었습니다.");
						location.reload();
				}
			});
		}
})
</script>

    <table class="view_2_form">
	<h2>제조사 - 거래담당자정보</h2>
    <colgroup>
        <col class="grid_2">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="shorts_code">성함</label></th>
        <td>
			<input type="hidden" name="type" class="frm_input" value="agent_update" />
			<input type="hidden" name="mb_no" class="frm_input" value="<?=$no?>" />
			<input type="text" name="mb_6" class="frm_input" value="<?=$data['mb_6']?>" />
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="shorts_code">직급</label></th>
        <td>
			<input type="text" name="mb_7" class="frm_input" value="<?=$data['mb_7']?>" />
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="shorts_code">직통연락처</label></th>
        <td>
			<input type="text" name="mb_8" class="frm_input" value="<?=$data['mb_8']?>" />
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="shorts_code">이메일</label></th>
        <td>
			<input type="text" name="mb_email" class="frm_input" value="<?=$data['mb_email']?>" />
        </td>
    </tr>
	<tr>
        <td colspan="2">
			<button type="button" class="btn btn-sm btn-info pull-right" id="view_2_con">담당자정보 적용</button>
        </td>
    </tr>
    </tbody>
    </table>
<script>
$("#view_2_con").click(function(){
	var result = confirm('정말 적용하시겠습니까?');
	if(!result) {return false;}
	var Formdata=$(".view_2_form input, .view_2_form select").serialize();
		if(Formdata){
			$.post( "./admin_ajax.php?"+Formdata)
			.done(function(jqXHR) {
				if(jqXHR==500){
					alert("요청에 문제가 있습니다.");
				}else{
					alert("적용되었습니다.");
						location.reload();
				}
			});
		}
})
</script>
    <table class="view_3_form">
	<h2>관리자 설정 영역</h2>
    <colgroup>
        <col class="grid_2">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="shorts_code">신용등급[재무기준]</label></th>
        <td>
			<input type="hidden" name="type" class="frm_input" value="credit_update" />
			<input type="hidden" name="KEDCD" class="frm_input" value="<?=$Companyinfo['KEDCD']?>" />
				<input
					type="date"
					name="ACCT_DT"
					id="ACCT_DT"
					class="frm_input frm_input--01"
					placeholder="신용등급"
					value="<?=$credit['ACCT_DT']?>"
				/>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="shorts_code">신용등급[등급구분]</label></th>
        <td>
				<select
					name="CREDIT_1"
					id="CREDIT_1"
					class="no-MCMX-select"
				>
					<option value="">등급구분</option>
					<option value="모형등급" <?=($credit['CREDIT_1']) == "모형등급" ? "selected" : "" ?>>모형등급</option>
					<option value="신용등급" <?=($credit['CREDIT_1']) == "신용등급" ? "selected" : "" ?>>신용등급</option>
				</select>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="shorts_code">신용등급[등급]</label></th>
        <td>
				<select
					name="CREDIT_2"
					id="CREDIT_2"
					class="no-MCMX-select"
				>
					<option value="">선택</option>
					<option value="nr" <?=($credit['CREDIT_2']) == "nr" ? "selected" : "" ?>>NR</option>
					<option value="d" <?=($credit['CREDIT_2']) == "d" ? "selected" : "" ?>>D</option>
					<option value="c" <?=($credit['CREDIT_2']) == "c" ? "selected" : "" ?>>C</option>
					<option value="cc" <?=($credit['CREDIT_2']) == "cc" ? "selected" : "" ?>>CC</option>
					<option value="ccc_minus" <?=($credit['CREDIT_2']) == "ccc_minus" ? "selected" : "" ?>>CCC-</option>
					<option value="ccc" <?=($credit['CREDIT_2']) == "ccc" ? "selected" : "" ?>>CCC</option>
					<option value="ccc_plus" <?=($credit['CREDIT_2']) == "ccc_plus" ? "selected" : "" ?>>CCC+</option>
					<option value="b_minus" <?=($credit['CREDIT_2']) == "b_minus" ? "selected" : "" ?>>B-</option>
					<option value="b" <?=($credit['CREDIT_2']) == "b" ? "selected" : "" ?>>B</option>
					<option value="b_plus" <?=($credit['CREDIT_2']) == "b_plus" ? "selected" : "" ?>>B+</option>
					<option value="bb_minus" <?=($credit['CREDIT_2']) == "bb_minus" ? "selected" : "" ?>>BB-</option>
					<option value="bb" <?=($credit['CREDIT_2']) == "bb" ? "selected" : "" ?>>BB</option>
					<option value="bb_plus" <?=($credit['CREDIT_2']) == "bb_plus" ? "selected" : "" ?>>BB+</option>
					<option value="bbb_minus" <?=($credit['CREDIT_2']) == "bbb_minus" ? "selected" : "" ?>>BBB-</option>
					<option value="bbb" <?=($credit['CREDIT_2']) == "bbb" ? "selected" : "" ?>>BBB</option>
					<option value="bbb_plus" <?=($credit['CREDIT_2']) == "bbb_plus" ? "selected" : "" ?>>BBB+</option>
					<option value="a_minus" <?=($credit['CREDIT_2']) == "a_minus" ? "selected" : "" ?>>A-</option>
					<option value="a" <?=($credit['CREDIT_2']) == "a" ? "selected" : "" ?>>A</option>
					<option value="a_plus" <?=($credit['CREDIT_2']) == "a_plus" ? "selected" : "" ?>>A+</option>
					<option value="aa_minus" <?=($credit['CREDIT_2']) == "aa_minus" ? "selected" : "" ?>>AA-</option>
					<option value="aa" <?=($credit['CREDIT_2']) == "aa" ? "selected" : "" ?>>AA</option>
					<option value="aa_plus" <?=($credit['CREDIT_2']) == "aa_plus" ? "selected" : "" ?>>AA+</option>
					<option value="aaa" <?=($credit['CREDIT_2']) == "aaa" ? "selected" : "" ?>>AAA</option>
				</select>
        </td>
    </tr>
	<tr>
        <td colspan="2">
			<button type="button" class="btn btn-sm btn-info pull-right" id="view_3_con">신용등급 적용</button>
        </td>
    </tr>
<script>
$("#view_3_con").click(function(){
	var result = confirm('정말 적용하시겠습니까?');
	if(!result) {return false;}
	var Formdata=$(".view_3_form input,.view_3_form select").serialize();
		if(Formdata){
			$.post( "./admin_ajax.php?"+Formdata)
			.done(function(jqXHR) {
				if(jqXHR==500){
					alert("요청에 문제가 있습니다.");
				}else{
					alert("적용되었습니다.");
						location.reload();
				}
			});
		}
})
</script>
    <!--tr>
        <th scope="row"><label for="shorts_code">재무상태[매출액 연도]</label></th>
        <td>
											<input
												type="text"
												name="sales_year1"
												id="sales_year1"
												class="frm_input frm_input--01"
												placeholder="매출액 연도"
												value="<?=$data['sales_year1']?>"
												
											/>
											<input
												type="text"
												name="sales_year2"
												id="sales_year2"
												class="frm_input frm_input--01"
												placeholder="매출액 연도"
												value="<?=$data['sales_year2']?>"
												
											/>
											<input
												type="text"
												name="sales_year3"
												id="sales_year3"
												class="frm_input frm_input--01"
												placeholder="매출액 연도"
												value="<?=$data['sales_year3']?>"
												
											/>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="shorts_code">재무상태[매출액]</label></th>
        <td>
											<input
												type="text"
												name="sales_price1"
												id="sales_price1"
												class="frm_input frm_input--01"
												placeholder="매출액"
												value="<?=$data['sales_price1']?>"
												
											/>
											<input
												type="text"
												name="sales_price2"
												id="sales_price2"
												class="frm_input frm_input--01"
												placeholder="매출액"
												value="<?=$data['sales_price2']?>"
												
											/>
											<input
												type="text"
												name="sales_price3"
												id="sales_price3"
												class="frm_input frm_input--01"
												placeholder="매출액"
												value="<?=$data['sales_price3']?>"
												
											/>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="shorts_code">재무상태[영업이익 연도]</label></th>
        <td>
											<input
												type="text"
												name="business_profit_year1"
												id="business_profit_year1"
												class="frm_input frm_input--01"
												placeholder="영업이익 연도"
												value="<?=$data['business_profit_year1']?>"
												
											/>
											<input
												type="text"
												name="business_profit_year2"
												id="business_profit_year2"
												class="frm_input frm_input--01"
												placeholder="영업이익 연도"
												value="<?=$data['business_profit_year2']?>"
												
											/>
											<input
												type="text"
												name="business_profit_year3"
												id="business_profit_year3"
												class="frm_input frm_input--01"
												placeholder="영업이익 연도"
												value="<?=$data['business_profit_year3']?>"
												
											/>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="shorts_code">재무상태[영업이익]</label></th>
        <td>
											<input
												type="text"
												name="business_profit_price1"
												id="business_profit_price1"
												class="frm_input frm_input--01"
												placeholder="영업이익"
												value="<?=$data['business_profit_price1']?>"
												
											/>
											<input
												type="text"
												name="business_profit_price2"
												id="business_profit_price2"
												class="frm_input frm_input--01"
												placeholder="영업이익"
												value="<?=$data['business_profit_price2']?>"
												
											/>
											<input
												type="text"
												name="business_profit_price3"
												id="business_profit_price3"
												class="frm_input frm_input--01"
												placeholder="영업이익"
												value="<?=$data['business_profit_price3']?>"
												
											/>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="shorts_code">재무상태[당기순이익 연도]</label></th>
        <td>
											<input
												type="text"
												name="net_income_year1"
												id="net_income_year1"
												class="frm_input frm_input--01"
												placeholder="당기순이익 연도"
												value="<?=$data['net_income_year1']?>"
												
											/>
											<input
												type="text"
												name="net_income_year2"
												id="net_income_year2"
												class="frm_input frm_input--01"
												placeholder="당기순이익 연도"
												value="<?=$data['net_income_year2']?>"
												
											/>
											<input
												type="text"
												name="net_income_year3"
												id="net_income_year3"
												class="frm_input frm_input--01"
												placeholder="당기순이익 연도"
												value="<?=$data['net_income_year3']?>"
												
											/>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="shorts_code">재무상태[당기순이익]</label></th>
        <td>
											<input
												type="text"
												name="net_income_price1"
												id="net_income_price1"
												class="frm_input frm_input--01"
												placeholder="당기순이익"
												value="<?=$data['net_income_price1']?>"
												
											/>
											<input
												type="text"
												name="net_income_price2"
												id="net_income_price2"
												class="frm_input frm_input--01"
												placeholder="당기순이익"
												value="<?=$data['net_income_price2']?>"
												
											/>
											<input
												type="text"
												name="net_income_price3"
												id="net_income_price3"
												class="frm_input frm_input--01"
												placeholder="당기순이익"
												value="<?=$data['net_income_price3']?>"
												
											/>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="shorts_code">재무상태[자본금 연도]</label></th>
        <td>
											<input
												type="text"
												name="capital_year1"
												id="capital_year1"
												class="frm_input frm_input--01"
												placeholder="자본금 연도"
												value="<?=$data['capital_year1']?>"
											/>
											<input
												type="text"
												name="capital_year2"
												id="capital_year2"
												class="frm_input frm_input--01"
												placeholder="자본금 연도"
												value="<?=$data['capital_year2']?>"
											/>
											<input
												type="text"
												name="capital_year3"
												id="capital_year3"
												class="frm_input frm_input--01"
												placeholder="자본금 연도"
												value="<?=$data['capital_year3']?>"
											/>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="shorts_code">재무상태[자본금]</label></th>
        <td>
											<input
												type="text"
												name="capital_price1"
												id="capital_price1"
												class="frm_input frm_input--01"
												placeholder="자본금"
												value="<?=$data['capital_price1']?>"
											/>
											<input
												type="text"
												name="capital_price2"
												id="capital_price2"
												class="frm_input frm_input--01"
												placeholder="자본금"
												value="<?=$data['capital_price2']?>"
											/>
											<input
												type="text"
												name="capital_price3"
												id="capital_price3"
												class="frm_input frm_input--01"
												placeholder="자본금"
												value="<?=$data['capital_price3']?>"
											/>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="shorts_code">노출설정</label></th>
        <td>
											<div class="no-ad__radio-box">
												<input type="radio" id="view_yn1" name="view_yn" value="N" <? if($data['view_yn'] == "N") echo "checked";?>>
												<label for="view_yn1">숨김</label>
											</div>
											<div class="no-ad__radio-box">
												<input type="radio" id="view_yn2" name="view_yn" value="Y" <? if($data['view_yn'] == "Y") echo "checked";?>>
												<label for="view_yn2">노출</label>
											</div>
        </td>
    </tr-->
    </tbody>
    </table>

</div>
<div class="btn_right">
    <a href="./matching_mfr.list.php?<?php echo $qstr ?>" class="btn_admin  btn_02">목록</a>
</div>
</form>
       

<?
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
