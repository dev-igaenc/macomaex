<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
$list_count = (is_array($list) && $list) ? count($list) : 0;
?>

    <?php
    for ($i=0; $i<$list_count; $i++) {
        $wr_href = get_pretty_url($bo_table, $list[$i]['wr_id']);
    ?>
                <div class="mVisual_text_eq">
                    <p class="ii title"><?=nl2br($list[$i]['wr_subject']); ?></p>
                    <p class="ii text"><?=nl2br($list[$i]['wr_content']); ?></p>
                </div>
    <?php }  ?>
    <?php if ($list_count == 0) { //게시물이 없을 때  ?>
                <div class="mVisual_text_eq">
                    <p class="ii title"></p>
                    <p class="ii text"></p>
                </div>
    <?php }  ?>
