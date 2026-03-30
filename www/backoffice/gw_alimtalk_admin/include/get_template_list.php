<?php
include_once('./_common.php');

$gw_msg = new NCP_SENS_AT();
$res = $gw_msg->get_templates();
?>

<table class="scrolltbody clearfix">
    <thead>
    <tr style="cursor:default;">
        <th width="100">발신프로필</th>
        <th width="100">템플릿 코드</th>
        <th width="100">템플릿 명</th>
    </tr>
    </thead>
    <tbody class="table-content" style="cursor: pointer;">

    <?php
    for ($i = 0; $i < count($res); $i++) {
        // 선택된 카카오톡 채널의 템플릿만 보여줌.
        if ($res[$i]['channelId'] != $nc_config['nc_plus_friend_id']) continue;
        
        $res[$i]['createTime'] = date('Y-m-d H:i:s', strtotime($res[$i]['createTime']));
    ?>
        <tr align="center" name="select" onclick="listck(this)" id="<?php echo $res[$i]['templateCode'] ?>">
            <td class="text"><?php echo $res[$i]['channelId'] ?></td>
            <td class="text tmp_code"><?php echo $res[$i]['templateCode'] ?></td>
            <td class="text"><?php echo $res[$i]['templateName'] ?></td>
        </tr>
    <?php
    }
    if ($i == 0)
        echo "<tr><td colspan=\"3\" class=\"empty_table\">등록된 템플릿이 없거나, 네이버 클라우드 플랫폼과 통신이 원할하지 않습니다.</br>하단의 새로고침 버튼을 클릭하시거나, 잠시 후 다시 시도하세요.</td></tr>";
    ?>
    </tbody>
</table>