<?php
include_once('./_common.php');
if (!$is_admin) alert("잘못된 접근방식입니다.");

$sql = "UPDATE {$g5['write_prefix']}{$bo_table} set 
        wr_8 = '{$wr_8}',
                       wr_9='{$wr_9}'
WHERE wr_id= '{$wr_id}'
";
sql_query($sql);
exit;