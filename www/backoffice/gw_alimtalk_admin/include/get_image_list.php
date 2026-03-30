<?php
include_once('./_common.php');

$channel_id = $_POST['channel_id'];

$sql_common = " from {$g5['nc_friendtalk_image']} ";

$sql_search = " where nc_plus_friend_id = '{$channel_id}' ";

$sql_order = " order by nc_id desc ";

$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

?>
<link rel="stylesheet" href="<?php echo G5_ADMIN_URL?>/gw_alimtalk_admin/css/style.css" />

<div class="thumb">
    <ul class="thumblist">
        <?php
        for ($i=0; $row=sql_fetch_array($result); $i++) {
            ?>
            <li style="text-align:center;">
                <div align="center" style="border: 1px solid black; width: 100%; margin-top: 20px; margin-bottom: 10px; height: 270px;">
                    <img class="relative" align="center" src="<?php echo $row['nc_image_url']; ?>" style="cursor: pointer; height: 268px;">
                    <input type="hidden" id="imageId_<?php echo $i; ?>" value="<?php echo $row['nc_image_id']; ?>">
                    <input type="hidden" id="imageUrl_<?php echo $i; ?>" value="<?php echo $row['nc_image_url']; ?>">
                    <input type="hidden" id="imageName" value="<?php echo $row['nc_image_name']; ?>">
                    <input type="hidden" id="isWide" value="<?php echo $row['nc_image_is_wide']; ?>">
                    <input type="hidden" id="createTime" value="<?php echo $row['nc_datetime']; ?>">
                </div>
                <input type="checkbox" class="uniform" name="chk_image" id="chk<?php echo $i+1?>" value="<?php echo $row['nc_image_url']; ?>" data-imageid="<?php echo $row['nc_image_id']; ?>" data-iswide="<?php echo $row['nc_image_is_wide']; ?>" onclick="javascript:check_image('<?php echo $i+1?>')">
            </li>
            <?php
        }

        if ($i == 0)
            echo "<li class=\"empty_li\">등록된 이미지가 없습니다.</li>";
        ?>
    </ul>
</div>