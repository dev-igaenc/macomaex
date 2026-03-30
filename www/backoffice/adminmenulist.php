<?php
$sub_menu = '100110';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "r");

$g5['title'] = '팝업레이어 관리';
include_once (G5_ADMIN_PATH.'/admin.head.php');

$sql_common = " from g5_admin_menu ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$sql = "select * $sql_common order by me_code asc ";
$result = sql_query($sql);
?>
<div class="local_ov01 local_ov"><span class="btn_ov01"><span class="ov_txt">전체 </span><span class="ov_num">  <?php echo $total_count; ?>건</span></span></div>

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col">번호</th>
        <th scope="col">메뉴그룹</th>
        <th scope="col">메뉴코드</th>
        <th scope="col">메뉴명</th>
        <th scope="col">메뉴링크</th>
        <th scope="col">메뉴구분</th>
        <th scope="col">CSS</th>
        <th scope="col">Target</th>
        <th scope="col">Order</th>
        <th scope="col">사용</th>
        <th scope="col">모바일사용</th>
		<th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        $bg = 'bg'.($i%2);

        switch($row['nw_device']) {
            case 'pc':
                $nw_device = 'PC';
                break;
            case 'mobile':
                $nw_device = '모바일';
                break;
            default:
                $nw_device = '모두';
                break;
        }
    ?>
    <tr class="<?=$bg; ?>">
		<td class="td_num"><?=$i; ?></td>
        <td class=""><?php echo $row['me_gr']; ?></td>
        <td class=""><?php echo $row['me_code']; ?></td>
        <td class=""><?php echo $row['me_name']; ?></td>
        <td class=""><?php echo $row['me_link']; ?></td>
        <td class=""><?php echo $row['me_diff']; ?></td>
        <td class=""><?php echo $row['me_css']; ?></td>
        <td class=""><?php echo $row['me_target']; ?></td>
        <td class=""><?php echo $row['me_order']; ?></td>
        <td class=""><?php echo $row['me_use']; ?></td>
        <td class=""><?php echo $row['me_mobile_use']; ?></td>
        <td class="td_mng td_mng_m">
            <a href="./adminmenuform.php?w=u&amp;nw_id=<?php echo $row['nw_id']; ?>" class="btn btn_03"><span class="sound_only"><?php echo $row['nw_subject']; ?> </span>수정</a>
            <a href="./adminmenuformupdate.php?w=d&amp;nw_id=<?php echo $row['nw_id']; ?>" onclick="return delete_confirm(this);" class="btn btn_02"><span class="sound_only"><?php echo $row['nw_subject']; ?> </span>삭제</a>
        </td>
    </tr>
    <?php
    }

    if ($i == 0) {
        echo '<tr><td colspan="11" class="empty_table">자료가 한건도 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>
</div>
<div class="btn_right">
    <a href="./newwinform.php" class="btn_admin btn_01">새창관리추가</a>
</div>


<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');