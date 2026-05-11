<?php
$sub_menu = "100550";
include_once('./_common.php');
include_once('../admin.head.php');

auth_check($auth[$sub_menu], 'r');

$fr_date = isset($_REQUEST['fr_date']) ? preg_replace('/[^0-9 :\-]/i', '', $_REQUEST['fr_date']) : "";
$to_date = isset($_REQUEST['to_date']) ? preg_replace('/[^0-9 :\-]/i', '', $_REQUEST['to_date']) : "";
$event_type = isset($_REQUEST['event_type']) ? (int)$_REQUEST['event_type'] : 0;


$sql_common = " from  ".G5_TABLE_PREFIX."seo ";

$sql_search = " where (1) ";
$qstr="";

if(!empty($fr_date) && !empty($to_date)){
    $fr_datetime=$fr_date." 00:00:00";
    $to_datetime=$to_date." 23:59:59";
    $qstr="&fr_date={$fr_date}&to_date={$to_date}";
    $sql_search.=" and create_date between '{$fr_datetime}' and '{$to_datetime}' ";
}

if ($stx) {
    $sql_search .= " and ( ";

    $sql_search .= " ({$sfl} like '%{$stx}%') ";

    $sql_search .= " ) ";
    $qstr.="&sfl={$sfl}&stx={$stx}";
}
if($event_type > 0) {
    $sql_search.= " and event_type={$event_type}";
    $qstr.="&event_type={$event_type}";
}
if (!$sst) {
    $sst = "id";
    $sod = "desc";
}
$sql_order = " order by {$sst} {$sod} ";

$sql = " select count(*) as cnt
            {$sql_common}
            {$sql_search}
            {$sql_order} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];
$rows = $config['cf_page_rows'];
$total_page = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select *
            {$sql_common}
            {$sql_search}
            {$sql_order}
            limit {$from_record}, {$rows} ";
$result = sql_query($sql);
$listall = '<a href="' . $_SERVER['SCRIPT_NAME'] . '" class="ov_listall">전체목록</a>';

$g5['title'] = "SEO 관리";

//include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$colspan = 9;

$po_expire_term = '';
if ($config['cf_point_term'] > 0) {
    $po_expire_term = $config['cf_point_term'];
}

?>
<div class="local_ov01 local_ov">
    <span class="btn_ov01"><span class="ov_txt">전체 </span><span
            class="ov_num"> <?php echo number_format($total_count) ?> 건 </span></span>
</div>

<form name="fpointlist" id="fpointlist" method="post" action="./seo_list_delete.php"
      onsubmit="return fpointlist_submit(this);">
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
            <tr>
                <th scope="col">
                    <label for="chkall" class="sound_only">전체</label>
                    <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
                </th>
                <th scope="col">파일 이름</th>
                <th scope="col">편집</th>
            </tr>
            </thead>
            <tbody>
            <?php
            for ($i = 0; $row = sql_fetch_array($result); $i++) {
                $bg = 'bg' . ($i % 2);

                ?>

                <tr class="<?php echo $bg; ?>">
                    <td class="td_chk">
                        <input type="hidden" name="id[<?php echo $i ?>]" value="<?php echo $row['id'] ?>"
                               id="id_<?php echo $i ?>">
                        <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo isset($row['file_name']) ? get_text($row['file_name']) : '' ?>
                            내역</label>
                        <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
                    </td>
                    <td class="td_left"><?php echo get_text($row['file_name']); ?></td>
                    <td>
                        <a href="./seo_form.php?sst=&amp;sod=&amp;sfl=&amp;stx=&amp;page=&amp;w=u&amp;id=<?php echo $row['id'] ?>"
                           class="btn btn_03">수정</a>
                    </td>
                </tr>

                <?php
            }

            if ($i == 0)
                echo '<tr><td colspan="' . $colspan . '" class="empty_table">자료가 없습니다.</td></tr>';
            ?>
            </tbody>
        </table>
        <div style="padding: 15px 0;">
            <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>
        </div>
    </div>

    <div class="btn_right">
        <a href="./seo_form.php"  id="member_add" class="btn_admin btn_01">추가</a>
        <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_admin btn_02">
    </div>

</form>


<script>
    function fpointlist_submit(f) {
        if (!is_checked("chk[]")) {
            alert(document.pressed + " 하실 항목을 하나 이상 선택하세요.");
            return false;
        }

        if (document.pressed == "선택삭제") {
            if (!confirm("선택한 자료를 정말 삭제하시겠습니까?")) {
                return false;
            }
        }

        return true;
    }

    $(function(){
        $("#fr_date, #to_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d" });
    });
</script>

<?php
include_once('../admin.tail.php');
?>
