<?php
include_once('./_common.php');
include_once(G5_ADMIN_PATH.'/gw_alimtalk_admin/ncp_sens.php');
include_once(G5_ADMIN_PATH.'/gw_alimtalk_admin/ncp_sens_at.php');
include_once(G5_ADMIN_PATH.'/gw_alimtalk_admin/ncp_sens_ft.php');

#############################################################################################
// DB 테이블 설정 (특별한 사유가 없는 한 수정하지 마세요.)
$g5['nc_alimtalk_config'] = G5_TABLE_PREFIX.'nc_alimtalk_config'; // 카카오 알림톡 설정 테이블
$g5['nc_alimtalk_history'] = G5_TABLE_PREFIX.'nc_alimtalk_history'; // 카카오 알림톡 템플릿 발송 내역 테이블
$g5['nc_friendtalk_history'] = G5_TABLE_PREFIX.'nc_friendtalk_history'; // 카카오 친구톡 발송 내역 테이블
$g5['nc_friendtalk_image'] = G5_TABLE_PREFIX.'nc_friendtalk_image'; // 카카오 친구톡 이미지 테이블
#############################################################################################

$nc_config = array();
$nc_config = sql_fetch(" select * from {$g5['nc_alimtalk_config']} "); // 카카오 알림톡 기본환경설정

#############################################################################################
// API 사용자 설정 (관리자 설정 페이지에서 입력한 정보로 설정 됩니다.)
define('NCP_SENS_HOST', 'https://sens.apigw.ntruss.com'); // SENS API 요청 URL
define('NCP_SENS_VERSION', 'v2'); // SENS API 버전 V2
define('NCP_KAKAO_PLUS_FRIEND_ID', $nc_config['nc_plus_friend_id']); // @를 포함한 플러스친구 아이디
define('NCP_BIZ_MESSAGE_SERVICE_ID', $nc_config['nc_biz_msg_service_id']); // ncp: 로 시작하는 API키
define('NCP_SUB_ACCESS_KEY_ID', $nc_config['nc_sub_access_key_id']); // 서브계정에서 생성한 접큰키 아이디
define('NCP_SUB_SECRET_KEY', $nc_config['nc_sub_secret_key']); // // 서브계정에서 생성한 시크릿 키
#############################################################################################

$alimtalk_table = true;

// 카카오 알림톡 설정 테이블이 존재한다면 설치할 수 없다.
// 카카오 알림톡 설정 테이블이 있는가?
if(!sql_query(" DESC {$g5['nc_alimtalk_config']} ", false) || !sql_query(" DESC {$g5['nc_alimtalk_history']} ", false) || !sql_query(" DESC {$g5['nc_friendtalk_history']} ", false) || !sql_query(" DESC {$g5['nc_friendtalk_image']} ", false)) {
	$alimtalk_table = false;
}
