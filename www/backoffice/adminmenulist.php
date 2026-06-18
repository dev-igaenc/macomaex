<?php
$sub_menu = '100110';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "r");

$g5['title'] = '관리자메뉴 관리';
include_once (G5_ADMIN_PATH.'/admin.head.php');

$sql_common = " from g5_admin_menu ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = isset($row['cnt']) ? (int)$row['cnt'] : 0;

$sql = "select * $sql_common order by me_code asc ";
$result = sql_query($sql);
?>
<div class="local_ov01 local_ov">
    <span class="btn_ov01"><span class="ov_txt">전체 </span><span class="ov_num"><?php echo number_format($total_count); ?>건</span></span>
</div>

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
        <th scope="col">PC사용</th>
        <th scope="col">모바일사용</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i = 0; $row = sql_fetch_array($result); $i++) {
        $bg = 'bg'.($i%2);
        $me_use = !empty($row['me_use']) ? '사용' : '미사용';
        $me_mobile_use = !empty($row['me_mobile_use']) ? '사용' : '미사용';
    ?>
    <tr class="<?php echo $bg; ?>">
        <td class="td_num"><?php echo $i + 1; ?></td>
        <td><?php echo get_text($row['me_gr']); ?></td>
        <td><?php echo get_text($row['me_code']); ?></td>
        <td><?php echo get_text($row['me_name']); ?></td>
        <td><?php echo get_text($row['me_link']); ?></td>
        <td><?php echo get_text($row['me_diff']); ?></td>
        <td><?php echo get_text($row['me_css']); ?></td>
        <td><?php echo get_text($row['me_target']); ?></td>
        <td class="td_num"><?php echo (int)$row['me_order']; ?></td>
        <td><?php echo $me_use; ?></td>
        <td><?php echo $me_mobile_use; ?></td>
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

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
