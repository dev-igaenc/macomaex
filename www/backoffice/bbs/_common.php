<?php
/**************************
@Filename: _common.php
@Version : 1.0
@Author  : Freemaster
@Date  : 2016/05/27
@Content : PHP by Editplus
**************************/
define('G5_IS_ADMIN', true);
$sub_menu = "300250";
include_once('../../common.php');
include_once(G5_ADMIN_PATH.'/admin.lib.php');
include_once(G5_ADMIN_PATH.'/bbs/adm.bbs.config.php');

if (isset($bo_table)) {
    $board_menu_map = array(
        'Banner' => '510000',
        'Notice' => '520000',
        'Promotion' => '530000',
        'FAQ' => '540000',
        'Contact' => '550000'
    );

    if (isset($board_menu_map[$bo_table])) {
        $current_menu = $board_menu_map[$bo_table];
    }
}
?>
