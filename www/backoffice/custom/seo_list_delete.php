<?php
$sub_menu = "100550";
include_once('./_common.php');

//print_r2($_POST); exit;

check_demo();

auth_check_menu($auth, $sub_menu, 'w');

check_admin_token();

$count = count($_POST['chk']);

if (!$count)
    alert($_POST['act_button'] . '할 게시판그룹을 1개이상 선택해 주세요.');

for ($i = 0; $i < $count; $i++) {
    $k = $_POST['chk'][$i];
    $id = preg_replace('/[^a-z0-9_]/i', '', $_POST['id'][$k]);

    if ($_POST['act_button'] == '선택삭제') {
        $seo=sql_fetch("select * from ".G5_TABLE_PREFIX."seo where id={$id}");
        $del_data=json_decode($seo['value'],true);
        @unlink(G5_DATA_PATH . '/file/' . 'seo' . '/' . $del_data['seo_image']);
        // 그룹 삭제
        sql_query(" delete from ".G5_TABLE_PREFIX."seo where id = '$id' ");

    }
}


goto_url('./seo_list.php', false);


?>
