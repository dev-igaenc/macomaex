<?php
$sub_menu = "200110";
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'w');
include_once('./admin.head.php');

// 추가 등록 기업코드 기본값 처리
$ADDKEDCD = isset($_REQUEST['ADDKEDCD']) ? preg_replace('/[^A-Za-z0-9]/', '', (string)$_REQUEST['ADDKEDCD']) : '';

$mb = array(
'mb_certify' => null,
'mb_adult' => null,
'mb_sms' => null,
'mb_intercept_date' => null,
'mb_id' => null,
'mb_name' => null,
'mb_nick' => null,
'mb_point' => null,
'mb_email' => null,
'mb_homepage' => null,
'mb_hp' => null,
'mb_tel' => null,
'mb_zip1' => null,
'mb_zip2' => null,
'mb_addr1' => null,
'mb_addr2' => null,
'mb_addr3' => null,
'mb_addr_jibeon' => null,
'mb_signature' => null,
'mb_profile' => null,
'mb_memo' => null,
'mb_leave_date' => null,
'mb_company' => null,
'mb_1' => null,
'mb_2' => null,
'mb_3' => null,
'mb_4' => null,
'mb_5' => null,
'mb_6' => null,
'mb_7' => null,
'mb_8' => null,
'mb_9' => null,
'mb_10' => null,
);

$sound_only = '';
$required_mb_id_class = '';
$required_mb_password = '';

if ($w == '')
{
    $required_mb_id = 'required';
    $required_mb_id_class = 'required alnum_';
    $required_mb_password = 'required';
    $sound_only = '<strong class="sound_only">필수</strong>';

    $mb['mb_mailling'] = 1;
    $mb['mb_open'] = 1;
    $mb['mb_level'] = $config['cf_register_level'];
    $html_title = '추가';
}
else if ($w == 'u')
{
    $mb = get_member($mb_id);
    if (!$mb['mb_id'])
        alert('존재하지 않는 회원자료입니다.');

    if ($is_admin != 'super' && $mb['mb_level'] >= $member['mb_level'])
        alert('자신보다 권한이 높거나 같은 회원은 수정할 수 없습니다.');

    $required_mb_id = 'readonly';
    $html_title = '수정';

    $mb['mb_name'] = get_text($mb['mb_name']);
    $mb['mb_nick'] = get_text($mb['mb_nick']);
    $mb['mb_email'] = get_text($mb['mb_email']);
    $mb['mb_homepage'] = get_text($mb['mb_homepage']);
    $mb['mb_birth'] = get_text($mb['mb_birth']);
    $mb['mb_tel'] = get_text($mb['mb_tel']);
    $mb['mb_hp'] = get_text($mb['mb_hp']);
    $mb['mb_addr1'] = get_text($mb['mb_addr1']);
    $mb['mb_addr2'] = get_text($mb['mb_addr2']);
    $mb['mb_addr3'] = get_text($mb['mb_addr3']);
    $mb['mb_signature'] = get_text($mb['mb_signature']);
    $mb['mb_recommend'] = get_text($mb['mb_recommend']);
    $mb['mb_profile'] = get_text($mb['mb_profile']);
    $mb['mb_1'] = get_text($mb['mb_1']);
    $mb['mb_2'] = get_text($mb['mb_2']);
    $mb['mb_3'] = get_text($mb['mb_3']);
    $mb['mb_4'] = get_text($mb['mb_4']);
    $mb['mb_5'] = get_text($mb['mb_5']);
    $mb['mb_6'] = get_text($mb['mb_6']);
    $mb['mb_7'] = get_text($mb['mb_7']);
    $mb['mb_8'] = get_text($mb['mb_8']);
    $mb['mb_9'] = get_text($mb['mb_9']);
    $mb['mb_10'] = get_text($mb['mb_10']);
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');

// 본인확인방법
switch($mb['mb_certify']) {
    case 'simple':
        $mb_certify_case = '간편인증';
        $mb_certify_val = 'simple';
        break;
    case 'hp':
        $mb_certify_case = '휴대폰';
        $mb_certify_val = 'hp';
        break;
    case 'ipin':
        $mb_certify_case = '아이핀';
        $mb_certify_val = 'ipin';
        break;
    case 'admin':
        $mb_certify_case = '관리자 수정';
        $mb_certify_val = 'admin';
        break;
    default:
        $mb_certify_case = '';
        $mb_certify_val = 'admin';
        break;
}

// 본인확인
$mb_certify_yes  =  $mb['mb_certify'] ? 'checked="checked"' : '';
$mb_certify_no   = !$mb['mb_certify'] ? 'checked="checked"' : '';

// 성인인증
$mb_adult_yes       =  $mb['mb_adult']      ? 'checked="checked"' : '';
$mb_adult_no        = !$mb['mb_adult']      ? 'checked="checked"' : '';

//메일수신
$mb_mailling_yes    =  $mb['mb_mailling']   ? 'checked="checked"' : '';
$mb_mailling_no     = !$mb['mb_mailling']   ? 'checked="checked"' : '';

// SMS 수신
$mb_sms_yes         =  $mb['mb_sms']        ? 'checked="checked"' : '';
$mb_sms_no          = !$mb['mb_sms']        ? 'checked="checked"' : '';

// 정보 공개
$mb_open_yes        =  $mb['mb_open']       ? 'checked="checked"' : '';
$mb_open_no         = !$mb['mb_open']       ? 'checked="checked"' : '';

if (isset($mb['mb_certify'])) {
    // 날짜시간형이라면 drop 시킴
    if (preg_match("/-/", $mb['mb_certify'])) {
        sql_query(" ALTER TABLE `{$g5['member_table']}` DROP `mb_certify` ", false);
    }
} else {
    sql_query(" ALTER TABLE `{$g5['member_table']}` ADD `mb_certify` TINYINT(4) NOT NULL DEFAULT '0' AFTER `mb_hp` ", false);
}

if(isset($mb['mb_adult'])) {
    sql_query(" ALTER TABLE `{$g5['member_table']}` CHANGE `mb_adult` `mb_adult` TINYINT(4) NOT NULL DEFAULT '0' ", false);
} else {
    sql_query(" ALTER TABLE `{$g5['member_table']}` ADD `mb_adult` TINYINT NOT NULL DEFAULT '0' AFTER `mb_certify` ", false);
}

// 지번주소 필드추가
if(!isset($mb['mb_addr_jibeon'])) {
    sql_query(" ALTER TABLE {$g5['member_table']} ADD `mb_addr_jibeon` varchar(255) NOT NULL DEFAULT '' AFTER `mb_addr2` ", false);
}

// 건물명필드추가
if(!isset($mb['mb_addr3'])) {
    sql_query(" ALTER TABLE {$g5['member_table']} ADD `mb_addr3` varchar(255) NOT NULL DEFAULT '' AFTER `mb_addr2` ", false);
}

// 중복가입 확인필드 추가
if(!isset($mb['mb_dupinfo'])) {
    sql_query(" ALTER TABLE {$g5['member_table']} ADD `mb_dupinfo` varchar(255) NOT NULL DEFAULT '' AFTER `mb_adult` ", false);
}

// 이메일인증 체크 필드추가
if(!isset($mb['mb_email_certify2'])) {
    sql_query(" ALTER TABLE {$g5['member_table']} ADD `mb_email_certify2` varchar(255) NOT NULL DEFAULT '' AFTER `mb_email_certify` ", false);
}

// 본인인증 내역 테이블 정보가 dbconfig에 없으면 소셜 테이블 정의
if( !isset($g5['member_cert_history']) ){
    $g5['member_cert_history_table'] = G5_TABLE_PREFIX.'member_cert_history';
}
// 멤버 본인인증 정보 변경 내역 테이블 없을 경우 생성
if(isset($g5['member_cert_history_table']) && !sql_query(" DESC {$g5['member_cert_history_table']} ", false)) {
    sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['member_cert_history_table']}` (
                    `ch_id` int(11) NOT NULL auto_increment,
                    `mb_id` varchar(20) NOT NULL DEFAULT '',
                    `ch_name` varchar(255) NOT NULL DEFAULT '',
                    `ch_hp` varchar(255) NOT NULL DEFAULT '',
                    `ch_birth` varchar(255) NOT NULL DEFAULT '',
                    `ch_type` varchar(20) NOT NULL DEFAULT '',
                    `ch_datetime` datetime NOT NULL default '0000-00-00 00:00:00',
                    PRIMARY KEY (`ch_id`),
                    KEY `mb_id` (`mb_id`)
                ) ", true);
}

$mb_cert_history = '';
if (isset($mb_id) && $mb_id) {
    $sql = "select * from {$g5['member_cert_history_table']} where mb_id = '{$mb_id}' order by ch_id asc";
    $mb_cert_history = sql_query($sql);
}

if ($mb['mb_intercept_date']) $g5['title'] = "차단된 ";
else $g5['title'] .= "";
$g5['title'] .= '회원 '.$html_title;

if($ADDKEDCD) {
	$add_corp=getComapyInfo($ADDKEDCD);
	$mb['mb_company']=$add_corp['KEDCD'];
	$mb['mb_name'] =$add_corp['ENP_NM'];
	$mb['mb_4']  =$add_corp['BZNO'];
}
// add_javascript('js 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_javascript(G5_POSTCODE_JS, 0);    //다음 주소 js
?>
<style>
.frm_input {width:30%;}
.products .add, .products .minus {display:inline-block; border:1px solid #ccc; border-radius:5px; padding:0 10px;}
input:read-only {background-color:#eeee;}
</style>
<form name="fmember" id="fmember" action="./member_form_update.php" onsubmit="return fmember_submit(this);" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">


<div class="tbl_frm01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?></caption>
    <colgroup>
        <col class="grid_4"></col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="mb_id">아이디<?php echo $sound_only ?></label></th>
        <td>
            <input type="text" name="mb_id" value="<?php echo $mb['mb_id'] ?>" id="mb_id" <?php echo $required_mb_id ?> class="frm_input <?php echo $required_mb_email_class ?>" size="15"  maxlength="20" placeholder="abc@email.com">
        </td>
    </tr>
	<tr>
        <th scope="row"><label for="mb_password">비밀번호<?php echo $sound_only ?></label></th>
        <td><input type="password" name="mb_password" id="mb_password" <?php echo $required_mb_password ?> class="frm_input <?php echo $required_mb_password ?>" size="15" maxlength="20"placeholder="8~16자 영문 대소문자, 숫자 조합해 주세요."></td>
	</tr>
	<tr>
        <th scope="row"><label for="">사업자등록번호</label></th>
        <td>
			<input type="hidden" name="com_chk" id="com_chk" class="com_chk" value="<?php echo !empty($mb['mb_company']) ? 'true' : 'false'; ?>">
			<input type="text" name="mb_4" value="<?php echo $mb['mb_4'] ?>" id="mb_4" required class="required frm_input comnum_chk" size="15"  maxlength="12"> <button type="button" class="btn btn-sm btn-info" id="bz_sch">조회</button>
		</td>
	</tr>
    <tr>
        <th scope="row"><label for="mb_name">법인명<strong class="sound_only">필수</strong></label></th>
        <td>
		<input type="text" name="mb_name" value="<?php echo $mb['mb_name'] ?>" id="mb_name" readonly required class="frm_input" size="15"  maxlength="20">
		<input type="hidden" name="mb_nick" value="<?php echo $mb['mb_nick'] ?>" id="mb_nick" >
		</td> 
    </tr>
	<tr>
        <th scope="row"><label for="">KEDCD</label></th>
        <td>
			<input type="text" name="mb_company" value="<?php echo $mb['mb_company'] ?>" id="mb_company" readonly  class="frm_input" size="15"  maxlength="12" readonly>
		</td>
	</tr>
		<input type="hidden" name="mb_email" value="<?php echo $mb['mb_email'] ?>">
		<input type="hidden" name="mb_level" value="<?php echo $mb['mb_level']; ?>">
	<tr>
        <th scope="row"><label for="">담당자 이름</label></th>
        <td>
			<input type="text" name="mb_6" value="<?php echo $mb['mb_6'] ?>" id="mb_6" required class="required frm_input" size="15">
		</td>
	</tr>
	<tr>
        <th scope="row"><label for="">휴대폰 번호</label></th>
        <td>
			<input type="text" name="mb_8" value="<?php echo $mb['mb_8'] ?>" id="mb_8"  class=" frm_input tel_chk" size="15" maxlength="13">
		</td>
	</tr>
    <tr>
        <th scope="row"><label for="mb_leave_date">탈퇴일자</label></th>
        <td>
            <input type="text" name="mb_leave_date" value="<?php echo $mb['mb_leave_date'] ?>" id="mb_leave_date" class="frm_input" maxlength="8">
            <input type="checkbox" value="<?php echo date("Ymd"); ?>" id="mb_leave_date_set_today" onclick="if (this.form.mb_leave_date.value==this.form.mb_leave_date.defaultValue) {
this.form.mb_leave_date.value=this.value; } else { this.form.mb_leave_date.value=this.form.mb_leave_date.defaultValue; }">
            <label for="mb_leave_date_set_today">탈퇴</label>
        </td>
    </tr>
	<tr>
        <th scope="row">접근차단일자</th>
        <td>
            <input type="text" name="mb_intercept_date" value="<?php echo $mb['mb_intercept_date'] ?>" id="mb_intercept_date" class="frm_input" maxlength="8">
            <input type="checkbox" value="<?php echo date("Ymd"); ?>" id="mb_intercept_date_set_today" onclick="if
(this.form.mb_intercept_date.value==this.form.mb_intercept_date.defaultValue) { this.form.mb_intercept_date.value=this.value; } else {
this.form.mb_intercept_date.value=this.form.mb_intercept_date.defaultValue; }">
            <label for="mb_intercept_date_set_today">접근차단</label>
        </td>
	</tr>
<?php if ($w == 'u') { ?>
    <tr>
        <th scope="row">회원가입일</th>
        <td><?php echo $mb['mb_datetime'] ?></td>
    </tr>
	<tr>
        <th scope="row">최근접속일</th>
        <td><?php echo $mb['mb_today_login'] ?></td>
	</tr>
    <tr>
        <th scope="row">IP</th>
        <td><?php echo $mb['mb_ip'] ?></td>
    </tr>
<?php } ?>
    </tbody>
    </table>
</div>

<div class="btn_right">
    <a href="./member_list.php?<?php echo $qstr ?>" class="btn_admin  btn_02">목록</a>
    <input type="submit" value="확인" class="bg-info btn_submit btn_admin btn_03" accesskey='s'>
</div>
</form>
<script>
//
$("#bz_sch").click(function(){
	biz_no = $(this).prev().val();
	r_mb_id = $("#mb_id").val();
	lenchk=biz_no.length;
	if(lenchk!=12) {
		alert("사업자 등록번호를 확인해 주세요.");
		return false;
	}
		$.post( "../get_set.php", {
			type: 'join_comp_chk',
			comnum:biz_no,
			r_mb_id:r_mb_id
		})
		.done(function(jqXHR) {
				jqXHR=jqXHR.split("|");
				if(jqXHR[0]==1000){
					//완료시
					$("#com_chk").val("true");
					alert("성공적으로 조회되었습니다.");
					$("#mb_name").val(jqXHR[1]).attr('readonly', true);
					$("#mb_company").val(jqXHR[2]).attr('readonly', true);
				}else if(jqXHR[0]==501){
					//등록된 사업자가 없을 경우
					$("#com_chk").val("true");
					$("#mb_name").val("").attr('readonly', false).focus();
					$("#mb_company").val("").attr('readonly', true);
				}else if(jqXHR[0]==502){
					//등록된 사업자가 있을 경우
					$("#com_chk").val("false");
					alert("해당 사업자 번호로 등록된 가입자가 있습니다.");
					$("#mb_4").focus();
				}else{
					//오류시
					$("#com_chk").val("false");
					alert("처리에 오류가 있습니다.");
				}
		});
})

//사업자번호
	$('.comnum_chk').on('keyup', function(){
        var num = $(this).val();
        num.trim();  // 스페이스바 제거
        this.value = AutoHypen(num) ;
    });
	$('.comnum_chk').keyup();
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
//전화번호 자동입력
$(".tel_chk").keyup(function(){
	phone=$(this).val();
	phone=phone.replace(/[^0-9]/g, '').replace(/^(\d{2,3})(\d{3,4})(\d{4})$/, `$1-$2-$3`);
	$(this).val(phone);
})

</script>

<script>
function fmember_submit(f)
{
	if($("#com_chk").val()!="true") {
		alert("사업등록번호를 검색해주세요.");
		$("#mb_4").focus();
		return false;
	}

    if (!f.mb_icon.value.match(/\.(gif|jpe?g|png)$/i) && f.mb_icon.value) {
        alert('아이콘은 이미지 파일만 가능합니다.');
        return false;
    }

    if (!f.mb_img.value.match(/\.(gif|jpe?g|png)$/i) && f.mb_img.value) {
        alert('회원이미지는 이미지 파일만 가능합니다.');
        return false;
    }

    return true;
}
</script>
<?php
run_event('admin_member_form_after', $mb, $w);

include_once('./admin.tail.php');
