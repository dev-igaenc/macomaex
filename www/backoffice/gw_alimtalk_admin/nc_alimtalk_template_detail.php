<?php
$sub_menu = "990100";
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'r');

$gw_msg = new NCP_SENS_AT();

$template_code = trim($_REQUEST['template_code']);
if(empty($template_code))
    die('템플릿 코드가 넘어오지 않았습니다.');

$res = $gw_msg->get_templates($template_code);
$res = $res[0];

$request_msg = '
<div class="tbl_head01 tbl_wrap">
    <h2 class="h2_frm">템플릿 내용</h2>
    <table>
        <thead>
        <tr>
            <th>템플릿 상세보기 실패</th>
        </tr>
        </thead>

        <tbody>
        <tr>
            <td><span class="request_state_box"><i class="request_state fail"></i></span> 반복 된 요청으로 네이버 클라우드 플랫폼에서 응답실패 하였습니다. 잠시 후 다시 시도하세요.</td>
        </tr>
        </tbody>
    </table>
</div>
';
if(empty($res))
    die($request_msg);



// 템플릿 검수 상태 한글로 변환
switch ($res['templateInspectionStatus']){
    case 'ACCEPT':
        $res['templateInspectionStatus'] = "<span class='request_state_box'><i class='request_state canceled'></i>수락</span>";
        break;
    case 'REGISTER':
        $res['templateInspectionStatus'] = "<span class='request_state_box'><i class='request_state processing'></i>등록</span>";
        break;
    case 'INSPECT':
        $res['templateInspectionStatus'] = "<span class='request_state_box'><i class='request_state reserved'></i>검수 중</span>";
        break;
    case 'COMPLETE':
        $res['templateInspectionStatus'] = "<span class='request_state_box'><i class='request_state success'></i>완료</span>";
        break;
    case 'REJECT':
        $res['templateInspectionStatus'] = "<span class='request_state_box'><i class='request_state fail'></i>반려</span>";
        break;
}

// 템플릿 상태 한글로 변환
switch ($res['templateStatus']){
    case 'ACTIVE':
        $res['templateStatus'] = "<span class='request_state_box'><i class='request_state success'></i>정상</span>";
        break;
    case 'READY':
        $res['templateStatus'] = "<span class='request_state_box'><i class='request_state reserved'></i>대기</span>";
        break;
    case 'STOP':
        $res['templateInspectionStatus'] = "<span class='request_state_box'><i class='request_state fail'></i>정지</span>";
        break;
}

// 검수 상태 한글로 변환
switch ($res['comments'][0]['status']){
    case 'APR':
        $res['comments'][0]['status'] = "<span class='request_state_box'><i class='request_state success'></i>검수완료</span>";
        break;
    case 'REJ':
        $res['comments'][0]['status'] = "<span class='request_state_box'><i class='request_state fail'></i>검수반려</span>";
        break;
}
?>

<div class="tbl_head01 tbl_wrap">
    <h1 id="domain-title"><?php echo $res['templateName']?></h1>

    <h2 class="h2_frm">템플릿 상태</h2>
    <table>
        <thead>
        <tr>
            <th>템플릿 코드</th>
            <th>템플릿 이름</th>
            <th>템플릿 검수 상태</th>
            <th>템플릿 상태</th>
        </tr>
        </thead>

        <tbody>
        <tr>
            <td><?php echo $res['templateCode']?></td>
            <td><?php echo $res['templateName']?></td>
            <td><?php echo $res['templateInspectionStatus']?></td>
            <td><?php echo $res['templateStatus']?></td>
        </tr>
        </tbody>
    </table>
</div>

<div class="tbl_head01 tbl_wrap">
    <h2 class="h2_frm">템플릿 내용</h2>
    <table>
        <thead>
        <tr>
            <th>템플릿 내용</th>
        </tr>
        </thead>

        <tbody>
        <tr>
            <td><textarea rows="10" readonly><?php echo $res['content']?></textarea></td>
        </tr>
        </tbody>
    </table>
</div>

<?php if (count($res['buttons']) > 0) { ?>
<div class="tbl_head01 tbl_wrap">
    <h2 class="h2_frm">버튼정보</h2>
    <table>
        <thead>
        <tr>
            <th>타입</th>
            <th>버튼명</th>
            <th>버튼링크</th>
        </tr>
        </thead>

        <tbody>
        <?php
        // 버튼의 개수만큼 추가
        for ($i=0; $i<count($res['buttons']); $i++) {
            if (!$res['buttons'][$i])
                continue;

            $btn_type = '';
            switch ($res['buttons'][$i]['type']){
                case 'DS':
                    $btn_type = "배송조회";
                    break;
                case 'WL':
                    $btn_type = "웹링크";
                    break;
                case 'AL':
                    $btn_type = "앱링크";
                    break;
                case 'BK':
                    $btn_type = "봇키워드";
                    break;
                case 'MD':
                    $btn_type = "메시지전달";
                    break;
                case 'BC':
                    $btn_type = "상담톡전환";
                    break;
                case 'BT':
                    $btn_type = "봇전환";
                    break;
                case 'AC':
                    $btn_type = "채널추가";
                    break;
            }

            // 템플릿 버튼
            $button = "";
            switch ($res['buttons'][$i]['type']) {
                case 'WL':
                    if ($res['buttons'][$i]['linkMobile']) {
                        $button = $res['buttons'][$i]['linkMobile'];
                    }
                    if ($res['buttons'][$i]['linkPc']) {
                        $button .= "<br>".$res['buttons'][$i]['linkPc'];
                    }
                    break;
                case 'AL':
                    if ($res['buttons'][$i]['schemeAndroid']) {
                        $button .= $res['buttons'][$i]['schemeAndroid'];
                    }
                    if ($res['buttons'][$i]['schemeIos']) {
                        $button = "<br>".$res['buttons'][$i]['schemeIos'];
                    }
                    break;
            }
        ?>
        <tr>
            <td><?php echo $btn_type; ?></td>
            <td><?php echo $res['buttons'][$i]['name']; ?></td>
            <td><?php echo $button; ?></td>
        </tr>
        <?php
        }
        ?>
        </tbody>
    </table>
</div>
<?php } ?>

<div class="tbl_head01 tbl_wrap">
    <h2 class="h2_frm">검수이력</h2>
    <table>
        <thead>
        <tr>
            <th>검수 내용</th>
            <th>검수 상태</th>
            <th>검수 일시</th>
        </tr>
        </thead>

        <tbody>
        <tr>
        <td><textarea rows="10" readonly><?php echo $res['comments'][0]['content']?></textarea></td>
        <td><?php echo $res['comments'][0]['status']?></td>
        <td><?php echo $res['comments'][0]['createTime']?></td>
        </tr>
        </tbody>
    </table>
</div>
