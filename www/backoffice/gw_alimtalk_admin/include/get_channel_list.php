<?php
include_once('./_common.php');

$gw_msg = new NCP_SENS_AT();
$res = $gw_msg->get_channels();
?>

<table class="scrolltbody clearfix">
    <thead>
    <tr style="cursor:default;">
        <th width="100">카카오톡 채널 아이디</th>
        <th width="100">카카오톡 채널 이름</th>
        <th width="100">생성 시간</th>
        <th width="100">수정 시간</th>
        <th width="100">카카오톡 채널 상태</th>
        <th width="100">SMS 대체 발송 사용 여부</th>
    </tr>
    </thead>
    <tbody class="table-content" style="cursor: pointer;">

    <?php
    for ($i = 0; $i < count($res); $i++) {
        $res[$i]['createTime'] = date('Y-m-d H:i:s', strtotime($res[$i]['createTime'])); // 생성 시간
        $res[$i]['updateTime'] = date('Y-m-d H:i:s', strtotime($res[$i]['updateTime'])); // 수정 시간

        // 카카오톡 채널 상태 한글로 변환
        $channel_status = '';
        switch ($res[$i]['channelStatus']){
            case 'ACTIVE':
                $channel_status = "<span class='request_state_box'><i class='request_state success'></i>정상</span>";
                break;
            case 'DELETED':
                $channel_status = "<span class='request_state_box'><i class='request_state fail'></i>삭제</span>";
                break;
            case 'DELETING_PERMANENTLY':
                $channel_status = "<span class='request_state_box'><i class='request_state processing'></i>영구 삭제 중</span>";
                break;
            case 'PERMANENTLY_DELETED':
                $channel_status = "<span class='request_state_box'><i class='request_state canceled'></i>영구 삭제</span>";
                break;
            case 'BLOCKED':
                $channel_status = "<span class='request_state_box'><i class='request_state canceled'></i>차단</span>";
                break;
            case 'PENDING_DELETE':
                $channel_status = "<span class='request_state_box'><i class='request_state processing'></i>삭제 지연 중</span>";
                break;
        }

        $sms_failover_status = '';
        switch ($res[$i]['useSmsFailover']){
            case '1':
                $sms_failover_status = "<span class='request_state_box'><i class='request_state success'></i>사용</span>";
                break;
            default:
                $sms_failover_status = "<span class='request_state_box'><i class='request_state fail'></i>미사용</span>";
                break;
        }
        ?>
        <tr align="center" name="select" onclick="listck(this)" id="<?php echo $res[$i]['channelId'] ?>">
            <td class="text kakao_channel"><?php echo $res[$i]['channelId'] ?></td>
            <td class="text"><?php echo $res[$i]['channelName'] ?></td>
            <td class="text"><?php echo $res[$i]['createTime'] ?></td>
            <td class="text"><?php echo $res[$i]['updateTime'] ?></td>
            <td class="text"><?php echo $channel_status ?></td>
            <td class="text"><?php echo $sms_failover_status ?></td>
        </tr>
        <?php
    }
    if ($i == 0)
        echo "<tr><td colspan=\"3\" class=\"empty_table\">등록된 템플릿이 없거나, 네이버 클라우드 플랫폼과 통신이 원할하지 않습니다.</br>하단의 새로고침 버튼을 클릭하시거나, 잠시 후 다시 시도하세요.</td></tr>";
    ?>
    </tbody>
</table>