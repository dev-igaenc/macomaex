<?php
$sub_menu = "200110";
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'r');
include_once('./admin.head.php');
$sql_common = " from {$g5['member_table']} ";

$sql_search = " where (mb_level < 10 and mb_level > 1)";
if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case 'mb_point' :
            $sql_search .= " ({$sfl} >= '{$stx}') ";
            break;
        case 'mb_level' :
            $sql_search .= " ({$sfl} = '{$stx}') ";
            break;
        case 'mb_tel' :
        case 'mb_hp' :
            $sql_search .= " ({$sfl} like '%{$stx}') ";
            break;
        case 'mb_4' :
			$stx2=str_replace("-","",$stx);
            $sql_search .= " (replace({$sfl},'-','') like '%{$stx2}%')  ";
            break;
        case 'mb_8' :
			$stx2=str_replace("-","",$stx);
            $sql_search .= " (replace({$sfl},'-','') like '%{$stx2}%')  ";
            break;
        default :
            $sql_search .= " ({$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

if (!$sst) {
    $sst = "mb_datetime";
    $sod = "desc";
}

$sql_order = " order by {$sst} {$sod} ";

$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함
$list_num = $total_count - ($page - 1) * $rows ;
// 탈퇴회원수
$sql = " select count(*) as cnt {$sql_common} {$sql_search} and mb_leave_date <> '' {$sql_order} ";
$row = sql_fetch($sql);
$leave_count = $row['cnt'];

// 차단회원수
$sql = " select count(*) as cnt {$sql_common} {$sql_search} and mb_intercept_date <> '' {$sql_order} ";
$row = sql_fetch($sql);
$intercept_count = $row['cnt'];

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$g5['title'] = '회원관리';


$sql = " select * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$colspan = 7;
?>
<style>
tr.leave td {background:#eee !important;}
.mb_leave_msg {color:#444;}
</style>
<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총회원수 </span><span class="ov_num"> <?php echo number_format($total_count) ?>명 </span></span>
    <a href="?sst=mb_intercept_date&amp;sod=desc&amp;sfl=<?php echo $sfl ?>&amp;stx=<?php echo $stx ?>" class="btn_ov01" data-tooltip-text="차단된 순으로 정렬합니다.&#xa;전체 데이터를 출력합니다."> <span class="ov_txt">차단 </span><span class="ov_num"><?php echo number_format($intercept_count) ?>명</span></a>
    <a href="?sst=mb_leave_date&amp;sod=desc&amp;sfl=<?php echo $sfl ?>&amp;stx=<?php echo $stx ?>" class="btn_ov01" data-tooltip-text="탈퇴된 순으로 정렬합니다.&#xa;전체 데이터를 출력합니다."> <span class="ov_txt">탈퇴  </span><span class="ov_num"><?php echo number_format($leave_count) ?>명</span></a>&ensp;<button type="button" class="btn btn-primary btn-sm pull-right" id="exl_download" onclick="$('#fsearch').attr('action','./member_excel.php').submit();$('#fsearch').attr('action','');"><i class="fa fa-file-excel-o" aria-hidden="true"></i> 엑셀 다운로드</button>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">

<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
    <option value="mb_id"<?php echo get_selected($sfl, "mb_id"); ?>>회원아이디</option>
    <option value="mb_name"<?php echo get_selected($sfl, "mb_name"); ?>>법인명</option>
	<option value="mb_4"<?php echo get_selected($sfl, "mb_4"); ?>>사업자등록번호</option>
    <option value="mb_email"<?php echo get_selected($sfl, "mb_email"); ?>>이메일주소</option>
    <option value="mb_8"<?php echo get_selected($sfl, "mb_8"); ?>>연락처</option>
    <option value="mb_datetime"<?php echo get_selected($sfl, "mb_datetime"); ?>>가입일시</option>
    <option value="mb_ip"<?php echo get_selected($sfl, "mb_ip"); ?>>IP</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx"  class=" frm_input">
<input type="submit" class="btn_submit" value="검색">

</form>

<div class="local_desc01 local_desc">
    <!--p>
        회원자료 삭제 시 다른 회원이 기존 회원아이디를 사용하지 못하도록 회원아이디, 이름, 닉네임은 삭제하지 않고 영구 보관합니다.
    </p-->
</div>

<form name="fmemberlist" id="fmemberlist" action="./member_list_update.php" onsubmit="return fmemberlist_submit(this);" method="post">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <!--tr>
        <th scope="col" id="mb_list_chk" rowspan="2" >
            <label for="chkall" class="sound_only">회원 전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col" id="mb_list_id" colspan="2"><?php echo subject_sort_link('mb_id') ?>아이디</a></th>
        <th scope="col" id="mb_list_mailc"><?php echo subject_sort_link('mb_email_certify', '', 'desc') ?>메일인증</a></th>
        <th scope="col" id="mb_list_mailr"><?php echo subject_sort_link('mb_mailling', '', 'desc') ?>메일수신</a></th>
        <th scope="col" id="mb_list_mobile">휴대폰</th>
        <th scope="col" id="mb_list_lastcall"><?php echo subject_sort_link('mb_today_login', '', 'desc') ?>최종접속</a></th>
        <th scope="col" rowspan="2" id="mb_list_mng">관리</th>
    </tr>
    <tr>
        <th scope="col" id="mb_list_name"><?php echo subject_sort_link('mb_name') ?>이름</a></th>
        <th scope="col" id="mb_list_nick"><?php echo subject_sort_link('mb_nick') ?>닉네임</a></th>
        <th scope="col" id="mb_list_sms"><?php echo subject_sort_link('mb_sms', '', 'desc') ?>SMS수신</a></th>
        <th scope="col" id="mb_list_auth"><?php echo subject_sort_link('mb_intercept_date', '', 'desc') ?>접근차단</a></th>
        <th scope="col" id="mb_list_tel">전화번호</th>
        <th scope="col" id="mb_list_join"><?php echo subject_sort_link('mb_datetime', '', 'desc') ?>가입일</a></th>
    </tr-->
	<tr>
        <th scope="col" id="mb_list_id">아이디</th>
        <th scope="col" id="mb_list_1">법인명</th>
		<th scope="col" id="mb_list_2">사업자등록번호</th>
        <th scope="col" id="mb_list_3">담당자 이름</th>
		<th scope="col" id="mb_list_6">연락처</th>
        <th scope="col" id="mb_list_no">가입날짜</th>
		<!--th scope="col" id="mb_list_7">날짜</th-->
		<!--th scope="col" id="mb_list_8">회사 홈페이지</th-->
        <th scope="col" id="mb_list_mng" width="120px">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {


        $leave_date = $row['mb_leave_date'] ? $row['mb_leave_date'] : date('Ymd', G5_SERVER_TIME);
        $intercept_date = $row['mb_intercept_date'] ? $row['mb_intercept_date'] : date('Ymd', G5_SERVER_TIME);

        $mb_nick = get_sideview($row['mb_id'], get_text($row['mb_nick']), $row['mb_email'], $row['mb_homepage']);

        $mb_id = $row['mb_id'];
        $leave_msg = '';
        $intercept_msg = '';
        $intercept_title = '';
        if ($row['mb_leave_date']) {
            $mb_id = $mb_id;
            $leave_msg = '<span class="mb_leave_msg">탈퇴함</span>';
        }
        else if ($row['mb_intercept_date']) {
            $mb_id = $mb_id;
            $intercept_msg = '<span class="mb_intercept_msg">차단됨</span>';
            $intercept_title = '차단해제';
        }
        if ($intercept_title == '')
            $intercept_title = '차단하기';

        $address = $row['mb_zip1'] ? print_address($row['mb_addr1'], $row['mb_addr2'], $row['mb_addr3'], $row['mb_addr_jibeon']) : '';
        $biz_number_raw = trim((string) $row['mb_4']);
        $biz_number = preg_replace('/[^0-9]/', '', $biz_number_raw);
        $display_biz_number = $biz_number !== '' ? get_text($biz_number_raw) : '-';
        $display_company_name = $biz_number !== '' ? get_text($row['mb_name']) : '-';

        $bg = 'bg'.($i%2);

        switch($row['mb_certify']) {
            case 'hp':
                $mb_certify_case = '휴대폰';
                $mb_certify_val = 'hp';
                break;
            case 'ipin':
                $mb_certify_case = '아이핀';
                $mb_certify_val = '';
                break;
            case 'simple':
                $mb_certify_case = '간편인증';
                $mb_certify_val = '';
                break;
            case 'admin':
                $mb_certify_case = '관리자';
                $mb_certify_val = 'admin';
                break;
            default:
                $mb_certify_case = '&nbsp;';
                $mb_certify_val = 'admin';
                break;
        }
    ?>

    <tr class="<?php echo $bg; ?> <?if($leave_msg){?>leave<?}?>">
		<td headers="mb_list_id" class="mb_list_id" alt="아이디"><?php echo get_text($row['mb_id']); ?></td>
		<td headers="mb_list_1" class="mb_list_1" alt="법인명"><?php echo $display_company_name; ?></td>
		<td headers="mb_list_2" class="mb_list_2" alt="사업자등록번호"><?php echo $display_biz_number; ?></td>
		<td headers="mb_list_3" class="mb_list_3" alt="담당자 이름	"><?php echo get_text($row['mb_6']); ?></td>
		<td headers="mb_list_7" class="mb_list_7" alt="연락처"><?php echo get_text($row['mb_8']); ?></td>
		<td headers="mb_list_no" class="mb_list_no" alt="가입날짜"><?if($leave_msg){echo $leave_msg;}else{?><?php echo get_text($row['mb_datetime']); ?><?}?></td>
		<!--td headers="mb_list_8" class="mb_list_8" alt="회사 홈페이지	"><?php echo get_text($row['mb_hp']); ?></td-->
		<td headers="mb_list_mng" class="td_mng td_mng_s">
			<a href="<?='./member_form.php?'.$qstr.'&amp;w=u&amp;mb_id='.$row['mb_id'];?>" class="btn btn-sm text-light btn-info">수정</a>
		</td>
    </tr>

    <?php
    }
    if ($i == 0)
        echo "<tr><td colspan=\"".$colspan."\" class=\"empty_table\">자료가 없습니다.</td></tr>";
    ?>
    </tbody>
    </table>
</div>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>
<div class="btn_right">
    <a href="./member_form.php?<?php echo $qstr ?>" class="btn_admin  btn_02">회원추가</a>
</div>
<script>
function fmemberlist_submit(f)
{
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택삭제") {
        if(!confirm("선택한 자료를 정말 삭제하시겠습니까?")) {
            return false;
        }
    }

    return true;
}
</script>

<?php
include_once ('./admin.tail.php');
