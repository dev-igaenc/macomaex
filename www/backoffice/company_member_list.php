<?php
$sub_menu = '200110';
// 권한 기존 회원관리와 공유, 메뉴 활성화만 별도 코드로 분리
$current_menu = '200120';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'r');
include_once('./admin.head.php');

if (!function_exists('admin_company_member_format_bizno')) {
    function admin_company_member_format_bizno($value)
    {
        $digits = preg_replace('/[^0-9]/', '', (string)$value);
        if (strlen($digits) === 10) {
            return substr($digits, 0, 3) . '-' . substr($digits, 3, 2) . '-' . substr($digits, 5);
        }

        return trim((string)$value);
    }
}

$allowed_sort_fields = array(
    'company_name' => 'C.ENP_NM',
    'biz_number' => 'C.BZNO',
    'kedcd' => 'C.KEDCD',
    'member_cnt' => 'CM.member_cnt',
    'member_request_cnt' => 'ifnull(TS.sent_trade_cnt, 0)',
    'received_trade_cnt' => 'ifnull(TR.received_trade_cnt, 0)'
);

$sfl = isset($sfl) ? trim((string)$sfl) : 'company_name';
$stx = isset($stx) ? trim((string)$stx) : '';
$sst = isset($allowed_sort_fields[$sst]) ? $sst : 'company_name';
$sod = (isset($sod) && strtolower((string)$sod) === 'asc') ? 'asc' : 'desc';

// 회사 테이블을 기준으로 회원 수, 최근 가입 회원, 거래요청 집계를 한 번에 묶음
$sql_common = "
    from company C
    inner join (
        select
            mb_company,
            count(*) as member_cnt,
            max(mb_no) as latest_mb_no
        from {$g5['member_table']}
        where ifnull(mb_company, '') != ''
          and mb_level < 10
          and mb_level > 1
        group by mb_company
    ) CM on CM.mb_company = C.KEDCD
    left join {$g5['member_table']} LM on LM.mb_no = CM.latest_mb_no
    left join (
        -- 현재 이 회사에 연결된 회원이 요청자로 남아 있는 거래요청 건수
        select
            M.mb_company,
            count(*) as sent_trade_cnt
        from g5_matching GM
        inner join {$g5['member_table']} M on M.mb_id = GM.request_id
        where ifnull(GM.del, '') != 'D'
          and ifnull(M.mb_company, '') != ''
        group by M.mb_company
    ) TS on TS.mb_company = C.KEDCD
    left join (
        -- 회사 KEDCD로 수신된 거래요청 건수
        select
            KEDCD,
            count(*) as received_trade_cnt
        from g5_matching
        where ifnull(del, '') != 'D'
          and ifnull(KEDCD, '') != ''
        group by KEDCD
    ) TR on TR.KEDCD = C.KEDCD
";

$sql_search = " where 1 ";
if ($stx !== '') {
    switch ($sfl) {
        case 'biz_number':
            $biz_number = preg_replace('/[^0-9]/', '', $stx);
            $sql_search .= " and replace(ifnull(C.BZNO, ''), '-', '') like '%{$biz_number}%'";
            break;
        case 'kedcd':
            $sql_search .= " and C.KEDCD like '%{$stx}%'";
            break;
        case 'member_count':
            $member_count = (int)$stx;
            $sql_search .= " and CM.member_cnt = '{$member_count}'";
            break;
        case 'company_name':
        default:
            $sql_search .= " and (
                ifnull(C.ENP_NM, '') like '%{$stx}%'
                or ifnull(C.ENP_NM_TRD, '') like '%{$stx}%'
            )";
            break;
    }
}

$sql_order = " order by " . $allowed_sort_fields[$sst] . " {$sod} ";

$summary_sql = " select count(*) as cnt, ifnull(sum(CM.member_cnt), 0) as member_total {$sql_common} {$sql_search} ";
$summary_row = sql_fetch($summary_sql);
$total_count = (int)$summary_row['cnt'];
$member_total = (int)$summary_row['member_total'];

$rows = $config['cf_page_rows'];
$total_page = ceil($total_count / $rows);
if ($page < 1) $page = 1;
// 페이징 시작 위치
$from_record = ($page - 1) * $rows;

$g5['title'] = '회사별 회원';
$listall = '<a href="' . $_SERVER['SCRIPT_NAME'] . '" class="ov_listall">전체목록</a>';

$sql = "
    select
        C.KEDCD,
        C.ENP_NM,
        C.BZNO,
        C.LOC_ADDRA,
        C.LOC_ADDRB,
        LM.mb_id as latest_mb_id,
        CM.member_cnt,
        ifnull(TS.sent_trade_cnt, 0) as sent_trade_cnt,
        ifnull(TR.received_trade_cnt, 0) as received_trade_cnt
    {$sql_common}
    {$sql_search}
    {$sql_order}
    limit {$from_record}, {$rows}
";
$result = sql_query($sql);

$colspan = 8;
?>

<div class="local_ov01 local_ov">
    <?php echo $listall; ?>
    <span class="btn_ov01"><span class="ov_txt">등록 회사 </span><span class="ov_num"><?php echo number_format($total_count); ?>곳</span></span>
    <span class="btn_ov01"><span class="ov_txt">연결 회원 </span><span class="ov_num"><?php echo number_format($member_total); ?>명</span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
    <option value="company_name"<?php echo get_selected($sfl, 'company_name'); ?>>회사명</option>
    <option value="biz_number"<?php echo get_selected($sfl, 'biz_number'); ?>>사업자등록번호</option>
    <option value="kedcd"<?php echo get_selected($sfl, 'kedcd'); ?>>KEDCD</option>
    <option value="member_count"<?php echo get_selected($sfl, 'member_count'); ?>>회원수</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo htmlspecialchars($stx, ENT_QUOTES, 'UTF-8'); ?>" id="stx" class="frm_input">
<input type="submit" class="btn_submit" value="검색">
</form>

<div class="local_desc01 local_desc">
    <p>회사 기준으로 묶어서 등록된 회원 현황을 확인할 수 있습니다.</p>
</div>

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col">회사명</th>
        <th scope="col">KEDCD</th>
        <th scope="col">사업자등록번호</th>
        <th scope="col">최근 가입 회원아이디</th>
        <th scope="col">회원수</th>
        <th scope="col">소속 회원 요청건수</th>
        <th scope="col">거래요청 받은 횟수</th>
        <th scope="col" width="140">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i = 0; $row = sql_fetch_array($result); $i++) {
        $bg = 'bg' . ($i % 2);
        $company_name = trim((string)$row['ENP_NM']) !== '' ? get_text($row['ENP_NM']) : '-';
        $biz_number = trim((string)$row['BZNO']) !== '' ? admin_company_member_format_bizno($row['BZNO']) : '-';
        $latest_member_id = trim((string)$row['latest_mb_id']) !== '' ? get_text($row['latest_mb_id']) : '-';
    ?>
    <tr class="<?php echo $bg; ?>">
        <td><?php echo $company_name; ?></td>
        <td class="td_center"><?php echo get_text($row['KEDCD']); ?></td>
        <td class="td_center"><?php echo $biz_number; ?></td>
        <td class="td_center"><?php echo $latest_member_id; ?></td>
        <td class="td_center"><?php echo number_format((int)$row['member_cnt']); ?></td>
        <td class="td_center"><?php echo number_format((int)$row['sent_trade_cnt']); ?></td>
        <td class="td_center"><?php echo number_format((int)$row['received_trade_cnt']); ?></td>
        <td class="td_mng td_mng_s">
            <a href="./member_list.php?sfl=mb_company&amp;stx=<?php echo urlencode((string)$row['KEDCD']); ?>" class="btn btn-sm text-light btn-secondary">회원목록</a>
        </td>
    </tr>
    <?php
    }
    if ($i === 0) {
        echo '<tr><td colspan="' . $colspan . '" class="empty_table">자료가 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>
</div>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?' . $qstr . '&amp;page='); ?>

<?php
include_once('./admin.tail.php');
