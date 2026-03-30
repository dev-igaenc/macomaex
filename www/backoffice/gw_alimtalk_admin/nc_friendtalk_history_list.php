<?php
$sub_menu = "999500";
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'r');

if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_date) ) $fr_date = '';
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_date) ) $to_date = '';

$sql_common = " from {$g5['nc_friendtalk_history']} ";

// 조건검색
if ($fr_date && $to_date) {
    $where[] = " nc_datetime between '$fr_date 00:00:00' and '$to_date 23:59:59' ";
}

if ($where) {
    $sql_search = ' where '.implode(' and ', $where);
}

if (!$sst) {
    $sst = "nc_id";
    $sod = "desc";
}

$sql_order = " order by {$sst} {$sod} ";

$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$g5['title'] = '친구톡 전송내역';
include_once(G5_ADMIN_PATH.'/admin.head.php');

$sql = " select * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$colspan = 15;

// 스크립트 & 스타일시트
add_stylesheet('<link rel="stylesheet" href="'.G5_ADMIN_URL.'/gw_alimtalk_admin/css/style.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/remodal/remodal.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/remodal/remodal-default-theme.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_ADMIN_URL.'/gw_alimtalk_admin/css/jquery.datetimepicker.css">', 10);
add_javascript('<script src="'.G5_ADMIN_URL.'/gw_alimtalk_admin/js/jquery.datetimepicker.min.js"></script>', 10);
add_javascript('<script src="'.G5_JS_URL.'/remodal/remodal.js"></script>', 10);
?>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총전송수 </span><span class="ov_num"> <?php echo number_format($total_count) ?>건 </span></span>
</div>

<form class="local_sch03 local_sch">

    <div class="sch_last">
        <strong>기간선택</strong>
        <input type="text" id="fr_date"  name="fr_date" value="<?php echo $fr_date; ?>" class="frm_input" size="10" maxlength="10"> ~
        <input type="text" id="to_date"  name="to_date" value="<?php echo $to_date; ?>" class="frm_input" size="10" maxlength="10">
        <button type="button" onclick="javascript:set_date('오늘');">오늘</button>
        <button type="button" onclick="javascript:set_date('어제');">어제</button>
        <button type="button" onclick="javascript:set_date('이번주');">이번주</button>
        <button type="button" onclick="javascript:set_date('이번달');">이번달</button>
        <button type="button" onclick="javascript:set_date('지난주');">지난주</button>
        <button type="button" onclick="javascript:set_date('지난달');">지난달</button>
        <button type="button" onclick="javascript:set_date('전체');">전체</button>
        <input type="submit" value="검색" class="btn_submit">
    </div>

</form>

<div class="local_desc01 local_desc">
    <ol>
        <li>친구톡 전송내역을 확인 할 수 있습니다. (<strong>발송시간</strong> 및 <strong>예약발송</strong> 시간을 기준으로 정렬됩니다.)</li>
        <li>우측상단의 <strong>"예약 메시지 동기화"</strong> 버튼을 클릭하시면 예약메시지가 갱신됩니다. (<strong>네이버 클라우드 플랫폼과 실시간 연동</strong>)</li>
        <li>대체문자 발송의 경우 친구톡이 발송되지 않았을 경우에만 발송됩니다.</li>
        <li>특정한 사유로 인해 발송 자체가 되지 않았다면, 전송목록에 내역이 없을 수 있습니다.</li>
    </ol>
</div>

<form name="fboardlist" id="fboardlist" action="./nc_friendtalk_history_list_update.php" onsubmit="return fboardlist_submit(this);" method="post">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="<?php echo $token ?>">

    <div class="tbl_head01 tbl_wrap">
        <table>
            <caption><?php echo $g5['title']; ?> 목록</caption>
            <thead>
            <tr>
                <th scope="col">
                    <label for="chkall" class="sound_only">게시판 전체</label>
                    <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
                </th>
                <th scope="col"><?php echo subject_sort_link('a.gr_id') ?>번호</a></th>
                <th scope="col">요청일시</th>
                <th scope="col">완료일시</th>
                <th scope="col">요청상태</th>
                <th scope="col">발송상태</th>
                <th scope="col">대체문자 발송상태</th>
                <th scope="col">수신번호</th>
                <th scope="col">대체문자 발송 여부</th>
                <th scope="col">결과보기</th>
            </tr>
            </thead>
            <tbody>
            <?php
            for ($i=0; $row=sql_fetch_array($result); $i++) {
                $bg = 'bg'.($i%2);
                $num = ($rows * ($page-1)) + $i+1;
                ?>

                <tr class="paygate-info-js" data-bo_table="<?php echo $row['bo_table'];?>" data-nc_id="<?php echo $row['nc_id'];?>" data-nc_message_id="<?php echo $row['nc_message_id'];?>" data-nc_request_id="<?php echo $row['nc_request_id'];?>">
                    <td class="td_chk">
                        <input type="hidden" name="nc_id[<?php echo $i ?>]" value="<?php echo $row['nc_id'] ?>" id="nc_id_<?php echo $i ?>">
                        <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['nc_id']) ?></label>
                        <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
                    </td>
                    <td class="td_numsmall"><?php echo $num ?></td>
                    <td class="td_datetime balance-requestTime loading"></td>
                    <td class="td_datetime balance-completeTime loading"></td>
                    <td class="td_num balance-statusName loading"></td>
                    <td class="td_datetime balance-messageStatusName loading"></td>
                    <td class="td_datetime balance-failoverStatusName loading"></td>
                    <td class="td_datetime balance-to loading"></td>
                    <td class="td_num balance-useSmsFailoverText loading"></td>
                    <td class="td_mng">
                        <button type="button" data-remodal-target="modal_result" data-message_id="<?php echo $row['nc_message_id'];?>" class="btn btn_02 btn_result_view">결과보기</button>
                    </td>
                </tr>

            <?php
            }
            if ($i == 0)
                echo '<tr><td colspan="'.$colspan.'" class="empty_table">전송내역이 없습니다.</td></tr>';
            ?>
            </tbody>
        </table>
    </div>

    <div class="server_rewrite_info">
        <div class="is_rewrite remodal alimtalk_modal" data-remodal-id="modal_result" role="dialog">
            <button type="button" class="connect-close" data-remodal-action="close">
                <i class="fa fa-close"></i>
                <span class="txt">닫기</span>
            </button>

            <header class="alimtalk_result_header">
                <h4>결과보기</h4>
                <div class="alimtalk_success_info"></div>
            </header>
            <div class="alimtalk_result_info"></div>
        </div>
    </div>

    <div class="btn_fixed_top">
        <input type="button" value="네이버 클라우드 플랫폼 바로가기" onclick="window.open('https://console.ncloud.com/sens/kakao-friendtalk-message')" class="btn btn_03">
        <input type="button" value="예약 메시지 동기화" onclick="sync_reserve_message()" class="btn btn_01" id="reserve_message">
        <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_02">
    </div>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, $_SERVER['SCRIPT_NAME'].'?'.$qstr.'&amp;fr_date='.$fr_date.'&amp;to_date='.$to_date.'&amp;page='); ?>

<script>
jQuery(function($){
    $(".btn_result_view").on("click", function(e){
        e.preventDefault();

        var ajax_url = "<?php echo G5_ADMIN_URL.'/gw_alimtalk_admin/nc_friendtalk_history_modal.php' ?>";
        var message_id = $(this).data("message_id");

        if( ! message_id ){
            alert('잘못된 요청! message_id 값이 없습니다.');
            return false;
        }

        $.ajax({
            url: ajax_url,
            type: 'POST',
            data: {
                'message_id': message_id,
            },
            dataType: 'json',
            async: false,
            success: function(data) {
                $('.alimtalk_success_info').html("Request Id: " + data.messageId);
                $('.alimtalk_result_info').html(data.html);
            },
            error: function(){
                alert('통신에러발생! 다시 시도해주세요.');
            }
        });

        return;
    });
});
</script>

<script>
function sync_reserve_message() {
    $.ajax({
        url: g5_url+'/adm/gw_alimtalk_admin/nc_friendtalk_sync_message.php',
        dataType: 'html',
        type:'post',
        success:function(req) {
            location.reload();
        }
    });
}

function initInquireBalance(targetName, statusName, messageStatusName, failoverStatusName, requestTime, completeTime, plusFriendId, to, useSmsFailoverText, messageId) {

    paygateInfoName = targetName || '.paygate-info-js';
    statusName = statusName || '.balance-statusName';
    messageStatusName = messageStatusName || '.balance-messageStatusName';
    failoverStatusName = failoverStatusName || '.balance-failoverStatusName';
    requestTime = requestTime || '.balance-requestTime';
    completeTime = completeTime || '.balance-completeTime';
    to = to || '.balance-to';
    useSmsFailoverText = useSmsFailoverText || '.balance-useSmsFailoverText';
    messageId = messageId || '.balance-messageId';

    var paygateInfos = $(paygateInfoName),
        order = 0,
        len = paygateInfos.length ? paygateInfos.length-1 : 0,
        nc_id = null,
        nc_message_id = null,
        nc_request_id = null,
        target = null;

    if(len !== 0 && !len) return false;

    var updateBalance = function(target, status, messageStatus, failoverStatus, requestDatetime, completeDatetime, to_phn, useSmsFailover, messageId_num) {
        var balance = target.find(statusName);
        balance.removeClass('loading');
        balance.append(status);

        var balance = target.find(messageStatusName);
        balance.removeClass('loading');
        balance.append(messageStatus);

        var balance = target.find(failoverStatusName);
        balance.removeClass('loading');
        balance.append(failoverStatus);

        var balance = target.find(requestTime);
        balance.removeClass('loading');
        balance.append(requestDatetime);

        var balance = target.find(completeTime);
        balance.removeClass('loading');
        balance.append(completeDatetime);

        var balance = target.find(to);
        balance.removeClass('loading');
        balance.append(to_phn);

        var balance = target.find(useSmsFailoverText);
        balance.removeClass('loading');
        balance.append(useSmsFailover);

        var balance = target.find(messageId);
        balance.removeClass('loading');
        balance.append(messageId_num);
    }

    var errorBalance = function(target, status, messageStatus, failoverStatus, requestDatetime, completeDatetime, to_phn, useSmsFailover, messageId_num) {
        var balance = target.find(statusName);
        balance
            .removeClass('loading')
            .addClass('error')
            .append(status);

        var balance = target.find(messageStatusName);
        balance.removeClass('loading');
        balance.append(messageStatus);

        var balance = target.find(failoverStatusName);
        balance.removeClass('loading');
        balance.append(failoverStatus);

        var balance = target.find(requestTime);
        balance.removeClass('loading');
        balance.append(requestDatetime);

        var balance = target.find(completeTime);
        balance.removeClass('loading');
        balance.append(completeDatetime);

        var balance = target.find(to);
        balance.removeClass('loading');
        balance.append(to_phn);

        var balance = target.find(useSmsFailoverText);
        balance.removeClass('loading');
        balance.append(useSmsFailover);

        var balance = target.find(messageId);
        balance.removeClass('loading');
        balance.append(messageId_num);
    }

    var func = function(order, isSequence) {
        order = order || 0;
        isSequence = (isSequence === false || isSequence === true)  ?  isSequence : true; /* boolean 값 없을 시 기본값 true */

        if(order > len) return false;

        target = paygateInfos.eq(order);
        nc_id = target.data('nc_id');
        nc_message_id = target.data('nc_message_id');
        nc_request_id = target.data('nc_request_id');

        if(!nc_request_id) {
            updateBalance(target, '-');
            isSequence ? func(++order, isSequence) : null;
            return false;
        }

        ajaxData({
            url: accountApi().paygate,
            data: {
                nc_id : nc_id,
                nc_message_id : nc_message_id,
                nc_request_id : nc_request_id,
            },
            error: function(xhr, ajaxOptions, thrownError) {
                /* 500 이상일때 load 멈추고 재시도 버튼 보여 주기 */
                console.log('BIZ_MESSAGE', xhr);
            }
        }, function(data){
            //console.log(data);

            var result = data;
            if(data.statusName === 'success') {
                //console.log(result.completeTime);
                updateBalance(target, result.statusName_ko, result.messageStatusName_ko, result.failoverStatusName_ko, result.requestTime, result.completeTime, result.to, result.useSmsFailover, result.messageId);
            } else {
                errorBalance(target, result.statusName_ko, result.messageStatusName_ko, result.failoverStatusName_ko, result.requestTime, result.completeTime, result.to, result.useSmsFailover, result.messageId);
            }
            isSequence ? func(++order, isSequence) : null;

        });

    }

    return func;
}

function accountApi(method) {
    return {
        paygate: g5_url+"/adm/gw_alimtalk_admin/nc_friendtalk_get_messages.php"
    };
}

function ajaxData(_regObj, resultCallBack, recursionLimit, recursionCounter) {

    recursionLimit = recursionLimit || 0;
    recursionCounter = recursionCounter || 0;

    if(!_regObj || !_regObj.url) {
        console.log("호출할 주소가 없습니다.");
        return;
    }

    var _dafaultAjaxOption = {
        method: "post",
        dataType: "json",
        success: function(data) {
            if(resultCallBack) {
                resultCallBack(data);
            } else {
                console.log("=== success ===");
                console.log("callback", data);
            }

        },
        complete: function(data) {
            //console.log("=== complete ===");
        },
        error: function(xhr, ajaxOptions, thrownError) {
            if(recursionLimit > 0 && recursionCounter < recursionLimit) {
                ++recursionCounter;
                console.log(recursionCounter, recursionLimit);
                return (function() {
                    setTimeout(function(){
                        ajaxData(_regObj, resultCallBack, recursionLimit, recursionCounter);
                    }, 1500);
                }());

            } else {
                console.log("=== error ===");
                console.log(xhr, ajaxOptions, thrownError);
            }
        }
    }

    $.extend(_dafaultAjaxOption, _regObj);
    $.ajax(_dafaultAjaxOption);
}

var sequenceStart = 0,
    sequenceBalance = initInquireBalance(
        '.paygate-info-js',
        '.balance-statusName',
        '.balance-messageStatusName',
        '.balance-failoverStatusName',
        '.balance-requestTime',
        '.balance-completeTime',
        '.balance-plusFriendId',
        '.balance-to',
        '.balance-useSmsFailoverText',
        '.balance-messageId'
    );
sequenceBalance(sequenceStart);

// datetimepicker
$("#fr_date").datetimepicker({dayOfWeekStart:1, format:"Y-m-d", timepicker:!1});
$("#to_date").datetimepicker({dayOfWeekStart:1, format:"Y-m-d", timepicker:!1});

function set_date(today)
{
    <?php
    $date_term = date('w', G5_SERVER_TIME);
    $week_term = $date_term + 7;
    $last_term = strtotime(date('Y-m-01', G5_SERVER_TIME));
    ?>
    if (today == "오늘") {
        document.getElementById("fr_date").value = "<?php echo G5_TIME_YMD; ?>";
        document.getElementById("to_date").value = "<?php echo G5_TIME_YMD; ?>";
    } else if (today == "어제") {
        document.getElementById("fr_date").value = "<?php echo date('Y-m-d', G5_SERVER_TIME - 86400); ?>";
        document.getElementById("to_date").value = "<?php echo date('Y-m-d', G5_SERVER_TIME - 86400); ?>";
    } else if (today == "이번주") {
        document.getElementById("fr_date").value = "<?php echo date('Y-m-d', strtotime('-'.$date_term.' days', G5_SERVER_TIME)); ?>";
        document.getElementById("to_date").value = "<?php echo date('Y-m-d', G5_SERVER_TIME); ?>";
    } else if (today == "이번달") {
        document.getElementById("fr_date").value = "<?php echo date('Y-m-01', G5_SERVER_TIME); ?>";
        document.getElementById("to_date").value = "<?php echo date('Y-m-d', G5_SERVER_TIME); ?>";
    } else if (today == "지난주") {
        document.getElementById("fr_date").value = "<?php echo date('Y-m-d', strtotime('-'.$week_term.' days', G5_SERVER_TIME)); ?>";
        document.getElementById("to_date").value = "<?php echo date('Y-m-d', strtotime('-'.($week_term - 6).' days', G5_SERVER_TIME)); ?>";
    } else if (today == "지난달") {
        document.getElementById("fr_date").value = "<?php echo date('Y-m-01', strtotime('-1 Month', $last_term)); ?>";
        document.getElementById("to_date").value = "<?php echo date('Y-m-t', strtotime('-1 Month', $last_term)); ?>";
    } else if (today == "전체") {
        document.getElementById("fr_date").value = "";
        document.getElementById("to_date").value = "";
    }
}
</script>

<script>
function fboardlist_submit(f)
{
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택삭제") {
        if(!confirm("선택한 자료를 정말 삭제하시겠습니까?")) {
            return false;
        }
    }

    return true;
}
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
