<?php
$sub_menu = "990400";
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = '카카오 알림톡 설정';

if (!isset($fr_date) || !is_string($fr_date)) $fr_date = '';
if (!isset($to_date) || !is_string($to_date)) $to_date = '';
if (!isset($sst) || !is_string($sst)) $sst = '';
if (!isset($sod) || !is_string($sod)) $sod = '';
if (!isset($page) || !is_numeric($page)) $page = 1;
$page = (int)$page > 0 ? (int)$page : 1;
$where = array();
$sql_search = '';

$sql_common = " from {$g5['nc_alimtalk_history']} ";

// 조건검색
$where[] = " nc_reserved = '1' ";
$where[] = " nc_message_id = '' ";

if (!empty($where)) {
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

$rows = isset($config['cf_page_rows']) ? (int)$config['cf_page_rows'] : 0;
$rows = $rows > 0 ? $rows : 30;
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$g5['title'] = '알림톡 예약취소';
include_once(G5_ADMIN_PATH.'/admin.head.php');

$sql = " select * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$colspan = 15;

// 스크립트 & 스타일시트
add_stylesheet('<link rel="stylesheet" href="'.G5_ADMIN_URL.'/gw_alimtalk_admin/css/style.css">', 0);
?>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총전송수 </span><span class="ov_num"> <?php echo number_format($total_count) ?>건 </span></span>
</div>

<div class="local_desc01 local_desc">
    <ol>
        <li>알림톡 예약 발송을 취소 할 수 있습니다. <strong>예약중(미발송) 메시지만 확인, 취소</strong> 가능합니다. (<strong>예약발송</strong> 시간을 기준으로 정렬됩니다.)</li>
		<li>우측상단의 <strong>"예약 메시지 동기화"</strong> 버튼을 클릭하시면 예약메시지가 갱신됩니다. (<strong>네이버 클라우드 플랫폼과 실시간 연동</strong>)</li>
        <li>우측상단의 <strong>"예약 취소"</strong> 버튼을 클릭하시면 예약 발송이 취소됩니다. (<strong>네이버 클라우드 플랫폼과 실시간 연동</strong>)</li>
		<li>취소는 예약 발송 시간이 <strong>최소 20분이 남은 상태의 메시지만 취소 가능합니다.</strong></li>
		<li>특정한 사유로 인해 예약 발송 자체가 되지 않았다면, 예약목록에 내역이 없을 수 있습니다.</li>
    </ol>
</div>

<form name="fboardlist" id="fboardlist" action="./nc_alimtalk_reserved_list_update.php" onsubmit="return fboardlist_submit(this);" method="post">
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
        <th scope="col">템플릿코드</th>
        <th scope="col">수신번호</th>
        <th scope="col">요청 ID</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        $bg = 'bg'.($i%2);
    ?>

	<tr class="paygate-info-js" data-nc_id="<?php echo $row['nc_id'];?>" data-nc_message_id="<?php echo $row['nc_message_id'];?>" data-nc_request_id="<?php echo $row['nc_request_id'];?>">
		<td class="td_chk">
			<input type="hidden" name="nc_id[<?php echo $i ?>]" value="<?php echo $row['nc_id'] ?>" id="nc_id_<?php echo $i ?>">
            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['nc_id']) ?></label>
            <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
        </td>
        <td class="td_numsmall"><?php echo ($rows * ($page-1)) + $i+1;?></td>
        <td class="td_datetime balance-requestTime loading"></td>
        <td class="td_datetime balance-completeTime loading"></td>
        <td class="td_num balance-statusName loading"></td>
		<td class="td_datetime balance-messageStatusName loading"></td>
		<td class="td_num balance-templateCode loading"></td>
		<td class="td_datetime balance-to loading"></td>
		<td class="td_boolean balance-messageId loading"></td>
    </tr>

    <?php
    }
    if ($i == 0)
        echo '<tr><td colspan="'.$colspan.'" class="empty_table">예약내역이 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <input type="button" value="네이버 클라우드 플랫폼 바로가기" onclick="window.open('https://console.ncloud.com/sens/kakao-alimtalk-reservation')" class="btn btn_03">
    <input type="button" value="예약 메시지 동기화" onclick="sync_reserve_message()" class="btn btn_01" id="reserve_message">
	<input type="submit" name="act_button" value="예약취소" onclick="document.pressed=this.value" class="btn btn_02">
</div>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, $_SERVER['SCRIPT_NAME'].'?'.$qstr.'&amp;fr_date='.$fr_date.'&amp;to_date='.$to_date.'&amp;page='); ?>

<script>
function sync_reserve_message() {
	$.ajax({
		url: g5_url+'/adm/gw_alimtalk_admin/nc_alimtalk_sync_message.php',
		dataType: 'html',
		type:'post',
		success:function(req) {
			location.reload();
		}
	});
}

function initInquireBalance(targetName, statusName, messageStatusName, requestTime, completeTime, plusFriendId, templateCode, to, messageId) {
	
	paygateInfoName = targetName || '.paygate-info-js';
	statusName = statusName || '.balance-statusName';
	messageStatusName = messageStatusName || '.balance-messageStatusName';
	requestTime = requestTime || '.balance-requestTime';
	completeTime = completeTime || '.balance-completeTime';
	templateCode = templateCode || '.balance-templateCode';
	to = to || '.balance-to';
	messageId = messageId || '.balance-messageId';

	var paygateInfos = $(paygateInfoName),
		order = 0, 
		len = paygateInfos.length ? paygateInfos.length-1 : 0,
		nc_id = null,
		nc_message_id = null,
		nc_request_id = null,
		target = null;
	
	if(len !== 0 && !len) return false;
	
	var updateBalance = function(target, status, messageStatus, requestDatetime, completeDatetime, template, to_phn, messageId_num) {
		var balance = target.find(statusName);
		balance.removeClass('loading');
		balance.append(status);

		var balance = target.find(messageStatusName);
		balance.removeClass('loading');
		balance.append(messageStatus);

		var balance = target.find(requestTime);
		balance.removeClass('loading');
		balance.append(requestDatetime);

		var balance = target.find(completeTime);
		balance.removeClass('loading');
		balance.append(completeDatetime);

		var balance = target.find(templateCode);
		balance.removeClass('loading');
		balance.append(template);

		var balance = target.find(to);
		balance.removeClass('loading');
		balance.append(to_phn);

		var balance = target.find(messageId);
		balance.removeClass('loading');
		balance.append(messageId_num);
	}

	var errorBalance = function(target, status, messageStatus, requestDatetime, completeDatetime, template, to_phn, messageId_num) {
		var balance = target.find(statusName);
		balance
			.removeClass('loading')
			.addClass('error')
			.append(status);

		var balance = target.find(messageStatusName);
		balance.removeClass('loading');
		balance.append(messageStatus);

		var balance = target.find(requestTime);
		balance.removeClass('loading');
		balance.append(requestDatetime);

		var balance = target.find(completeTime);
		balance.removeClass('loading');
		balance.append(completeDatetime);

		var balance = target.find(templateCode);
		balance.removeClass('loading');
		balance.append(template);

		var balance = target.find(to);
		balance.removeClass('loading');
		balance.append(to_phn);

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
			url: accountApi('ncp_api').paygate,
			data: {
					nc_id : nc_id,
					nc_message_id : nc_message_id,
					nc_request_id : nc_request_id,
			},
			error: function(xhr, ajaxOptions, thrownError) {
				/* 500 이상일때 load 멈추고 재시도 버튼 보여 주기 */
				console.log('ncp_api', xhr);
			}
		}, function(data){
			//console.log(data);
			
			var result = data;
			if(data.statusName === 'success') {
				//console.log(result.completeTime);
				updateBalance(target, result.statusName_ko, result.messageStatusName_ko, result.requestTime, result.completeTime, result.templateCode, result.to, result.messageId);
			} else {
				errorBalance(target, result.statusName_ko, result.messageStatusName_ko, result.requestTime, result.completeTime, result.templateCode, result.to, result.messageId);
			}
			isSequence ? func(++order, isSequence) : null;

		});
	
	}

	return func;
}

function accountApi(method) {
	return {
		paygate: g5_url+"/adm/gw_alimtalk_admin/nc_alimtalk_get_messages.php?method="+method
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
			'.balance-requestTime', 
			'.balance-completeTime', 
			'.balance-plusFriendId',
			'.balance-templateCode',
			'.balance-to',
			'.balance-messageId'
		);
	sequenceBalance(sequenceStart);
</script>

<script>
function fboardlist_submit(f)
{
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

	if(document.pressed == "선택취소") {
        if(!confirm("선택한 예약 메시지를 정말 취소하시겠습니까?")) {
            return false;
        }
    }

    return true;
}
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
