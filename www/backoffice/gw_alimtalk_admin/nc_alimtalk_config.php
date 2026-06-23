<?php
$sub_menu = "990100";
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = '알림톡 설정';

include_once (G5_ADMIN_PATH.'/admin.head.php');

$pg_anchor = '<ul class="anchor">
    <li><a href="#nc_alimtalk_config">네이버 클라우드 플랫폼 [Biz Message 계정 등록]</a></li>
    <li><a href="#nc_sms_config">네이버 클라우드 플랫폼 [SMS 대체 발송]</a></li>
    <li><a href="#nc_template_config">네이버 클라우드 플랫폼 [템플릿 목록]</a></li>
</ul>';

$colspan = 6;
$rows = isset($config['cf_page_rows']) ? (int)$config['cf_page_rows'] : 0;
$page = (isset($page) && is_numeric($page) && (int)$page > 0) ? (int)$page : 1;

$gw_msg = new NCP_SENS_AT();

add_stylesheet('<link rel="stylesheet" href="'.G5_ADMIN_URL.'/gw_alimtalk_admin/css/style.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_ADMIN_URL.'/gw_alimtalk_admin/css/alimtalk.css">', 0);
?>

<div class="local_desc01 local_desc">
    <ol>
        <li>알림톡 계정 정보를 <strong>등록</strong>합니다.</li>
        <li><strong>네이버 클라우드 플랫폼</strong>에 템플릿을 등록합니다. (템플릿코드, 템플릿명은 중복 될 수 없습니다.)</li>
		<li><strong>네이버 클라우드 플랫폼</strong>에 템플릿을 등록하면 하단의 템플릿 목록에서 확인 할 수 있습니다.</li>
        <li>템플릿은 <strong>네이버 클라우드 플랫폼</strong>에서 무제한으로 등록할 수 있습니다.</li>
    </ol>
</div>

<div class="local_desc01 local_desc">
    <p>카카오 알림톡 설정을 하는 메뉴입니다.</p>
    <p><strong>주의!</strong> 알림톡설정 작업 후 반드시 <strong>우측상단의 확인</strong> 버튼을 누르셔야 저장됩니다.</p>
</div>

<form name="fconfigform" id="fconfigform" method="post" onsubmit="return fconfigform_submit(this);">
<input type="hidden" name="token" value="" id="token">

<section id="nc_alimtalk_config">
    <h2 class="h2_frm">네이버 클라우드 플랫폼 [Biz Message 계정 등록]</h2>
    <?php echo $pg_anchor ?>
    <div class="local_desc02 local_desc">
        <p>네이버 클라우드 플랫폼(Biz Message)에 가입된 API 키를 추가하거나 변경할 수 있습니다.</p>
    </div>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>네이버 클라우드 플랫폼 [Biz Message 계정 등록]</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
		<tr>
            <th scope="row"><label for="nc_biz_msg_admin_hp">관리자용 알림톡 회신 번호</label></th>
            <td colspan="3">
				<?php echo help("관리자용 알림톡을 회신받을 휴대폰 번호를 입력하세요. 관리자용 기능 사용시에만 발송됩니다."); ?>
                <input type="text" name="nc_biz_msg_admin_hp" value="<?php echo $nc_config['nc_biz_msg_admin_hp'] ?>" id="nc_biz_msg_admin_hp" class="frm_input" size="50" maxlength="15">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="nc_plus_friend_id">카카오톡 채널 아이디</label></th>
            <td>
                <?php
                // 해당 계정에 등록된 카카오채널을 가져온다.
                $res = $gw_msg->get_channels();
                ?>

                <?php if ($res) { ?>
                    <?php echo help("발송 할 카카오톡 채널 아이디를 선택/저장합니다."); ?>
                    <?php echo help("네이버 클라우드 플랫폼에 반드시 카카오채널이 등록 되어있어야 합니다."); ?>
                    <select id="nc_plus_friend_id" name="nc_plus_friend_id" class="">
                        <option value=''>선택하세요</option>
                        <?php foreach ($res as $key => $val) { ?>
                        <option value="<?php echo $res[$key]['channelId'] ?>" <?php echo get_selected($res[$key]['channelId'], $nc_config['nc_plus_friend_id']); ?>><?php echo $res[$key]['channelId'] ?></option>
                        <?php } ?>
                    </select>
                <?php } else { ?>
                    <?php echo help("서비스 ID, 서브 계정 Access Key Id, 서브 계정 Secret Key를 입력해주세요."); ?>
                    <?php echo help("이후 알림톡을 발송 할 카카오톡 채널을 선택 할 수 있습니다."); ?>
                <?php } ?>
                <a href="https://console.ncloud.com/sens/kakao-talk-channel" target="_blank" class="btn_frmline">카카오채널 등록하기</a>
            </td>
            <th scope="row"><label for="nc_biz_msg_service_id">서비스 ID</label></th>
            <td>
				<?php echo help("ncp:kkobizmsg:kr: 로 시작하는 서비스 ID를 입력합니다."); ?>
                <input type="text" name="nc_biz_msg_service_id" value="<?php echo $nc_config['nc_biz_msg_service_id'] ?>" id="nc_biz_msg_service_id" class="frm_input" size="50"> <a href="https://console.ncloud.com/sens/project" target="_blank" class="btn_frmline">서비스 ID 등록하기</a>
            </td>
        </tr>
		<tr>
            <th scope="row"><label for="nc_sub_access_key_id">서브 계정 Access Key Id</label></th>
            <td>
				<?php echo help("서브 계정 Access Key Id를 입력합니다."); ?>
                <input type="text" name="nc_sub_access_key_id" value="<?php echo $nc_config['nc_sub_access_key_id'] ?>" id="nc_sub_access_key_id" class="frm_input" size="50"> <a href="https://console.ncloud.com/iam/users" target="_blank" class="btn_frmline">서브 계정 Access Key Id 등록하기</a>
            </td>
            <th scope="row"><label for="nc_sub_secret_key">서브 계정 Secret Key</label></th>
            <td>
				<?php echo help("서브 계정 Secret Key를 입력합니다."); ?>
                <input type="text" name="nc_sub_secret_key" value="<?php echo $nc_config['nc_sub_secret_key'] ?>" id="nc_sub_secret_key" class="frm_input" size="50"> <a href="https://console.ncloud.com/iam/users" target="_blank" class="btn_frmline">서브 계정 Secret Key 등록하기</a>
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>

<section id="nc_sms_config">
    <h2 class="h2_frm">네이버 클라우드 플랫폼 [SMS 대체 발송]</h2>
    <?php echo $pg_anchor ?>
    <div class="local_desc02 local_desc">
        <p>네이버 클라우드 플랫폼(Biz Message)에 <strong>Failover SMS 서비스</strong>를 연동 한 계정은 자동으로 <strong>대체문자</strong> 발송이 가능합니다.</p>
        <p><strong>네이버 클라우드 플랫폼(Biz Message) -> KakaoTalk Channel -> Failover 설정에서 사용으로 설정 되어 있어야 합니다.</strong></p>
    </div>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>네이버 클라우드 플랫폼 [SMS 대체 발송]</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>

		<tr>
            <th scope="row"><label for="nc_sms">Failover SMS 서비스</label></th>
            <td colspan="3">
				<?php echo help("알림톡 발송실패시 SMS/LMS 문자로 대체발송 가능 한 서비스입니다."); ?>
				<?php echo help("문자 대체발송을 원하시는 경우에는 네이버 클라우드 플랫폼에 로그인 후 SMS 서비스 계정을 발급 받으셔야 합니다.
				(기본으로는 보내지 않음 상태입니다.)"); ?>
				<?php echo help("SMS 또는 LMS 기본: 문자내용의 길이에 따라 자동 적용 (90 bytes 이하 SMS, 초과 LMS)"); ?>
                <?php echo help("SMS 무료 50건(월), LMS 무료 10건(월)"); ?>
				<a href="https://console.ncloud.com/sens/project" target="_blank" class="btn_frmline">SMS 대체발송 설정 바로가기</a>
            </td>
        </tr>

        </tbody>
        </table>
    </div>
</section>

<div class="btn_fixed_top">
	<input type="submit" value="확인" class="btn btn-info" accesskey="s">
</div>

</form>


<section id="nc_template_config">
    <h2 class="h2_frm">네이버 클라우드 플랫폼 [템플릿 목록]</h2>
    <?php echo $pg_anchor ?>
    <div class="local_desc02 local_desc">
        <p>네이버 클라우드 플랫폼에 등록 한 템플릿을 확인 할 수 있습니다.</p>
        <p>
            <strong>템플릿은 반드시 네이버 클라우드 플랫폼에 등록이 되어 있어야 합니다.</strong>
            <a href="https://console.ncloud.com/sens/biz/alim-talk/template" target="_blank" class="btn_frmline">템플릿 등록 바로가기</a>
        </p>
    </div>

    <?php
    // 템플릿 목록을 가져온다.
    $res = $gw_msg->get_templates();
    ?>

    <div class="tbl_head01 tbl_wrap">
        <table>
            <caption><?php echo $g5['title']; ?></caption>
            <thead>
            <tr>
                <th scope="col">번호</th>
                <th scope="col">카카오톡 채널</th>
                <th scope="col">템플릿코드</th>
                <th scope="col">템플릿명</th>
                <th scope="col">템플릿 등록일</th>
                <th scope="col">상세보기</th>
            </tr>
            </thead>
            <tbody>
            <?php
			$cnt = 0;
			if (is_array($res)) {
				$cnt = count($res);
			}

            for ($i=0; $i<$cnt; $i++) {
                // 선택된 카카오톡 채널의 템플릿만 보여줌.
                if ($res[$i]['channelId'] != $nc_config['nc_plus_friend_id']) continue;

                $res[$i]['createTime'] = date('Y-m-d H:i:s', strtotime($res[$i]['createTime']));

                $bg = 'bg'.($i%2);
                ?>

                <tr class="<?php echo $bg; ?>">
                    <td class="td_num_c"><?php echo ($rows * ($page-1)) + $i+1; ?></td>
                    <td class="td_mngsmall"><?php echo $res[$i]['channelId'] ?></td>
                    <td class="td_mngsmall"><?php echo $res[$i]['templateCode'] ?></td>
                    <td class="td_mngsmall"><?php echo $res[$i]['templateName'] ?></td>
                    <td class="td_mngsmall"><?php echo $res[$i]['createTime'] ?></td>
                    <td class="td_mng td_mng_s">
                        <button type="button" class="btn btn-primary" onclick="tmpdetail('<?php echo $res[$i]['templateCode'] ?>')">상세보기</button>
                    </td>
                </tr>

            <?php
            }
            if ($i == 0)
                echo "<tr><td colspan=\"".$colspan."\" class=\"empty_table\">등록된 템플릿이 없거나, 네이버 클라우드 플랫폼과 통신이 원할하지 않습니다.</br>화면을 새로고침 하시거나, 잠시 후 다시 시도하세요.</td></tr>";
            ?>
            </tbody>
        </table>
    </div>
</section>

<!-- 템플릿 불러오기 모달 TemplateUpload Modal -->
<div class="basicModal" id="template_Upload">
    <div class="basicModal_content">
        <h3 class="modal_title">템플릿 상세보기</h3>
        <!-- basicModal_body -->
        <div class="basicModal_body select_body">
            <div class="widget-content">
                <div class="tbl_head03 tbl_wrap" id="template_table"></div>
            </div>

            <div class="btn_area text_center">
                <button class="btn_submit btn btn-secondary" onclick="tmpcancel()">닫기</button>
            </div>
        </div>
        <!-- //basicModal_body -->
    </div>
    <!-- //basicModal_content -->
</div>
<!-- //템플릿 불러오기 모달 TemplateUpload Modal -->

<script>
function fconfigform_submit(f)
{
    f.action = "./nc_alimtalk_config_update.php";
    return true;
}

function tmpdetail(templateCode) {
    $.ajax({
        url:"./nc_alimtalk_template_detail.php",
        method:"POST",
        data:{template_code : templateCode},
        success:function(data){
            $('#template_table').html(data);
            $("#template_Upload").css("display", "block");
        }
    });
}

function tmpcancel() {
    $("#template_Upload").css("display", "none");
}
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
