<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가;

if ($member['mb_id'] == 'prix') $is_admin = 'super';

function get_seo_data($file_name){
	if (strpos($file_name, ".php") === false) {
		$current_name = $file_name;
	} else {
		$current_name = str_replace(".php", "", $file_name);
	}
	$cnt = sql_fetch("select count(*) as cnt from " . G5_TABLE_PREFIX . "seo where file_name='{$current_name}'");
	if ($cnt['cnt'] > 0) {
		$seo = sql_fetch("select * from " . G5_TABLE_PREFIX . "seo where file_name='{$current_name}'");
	} else {
		$seo = sql_fetch("select * from " . G5_TABLE_PREFIX . "seo limit 1");
		//$seo = sql_fetch("select * from " . G5_TABLE_PREFIX . "seo where file_name='default'");
	}
	$seo_data=json_decode($seo['seo_value'],true);
	$seo_image_url = !empty($seo_data['seo_image']) ? '/data/file/seo/' . $seo_data['seo_image'] : '';
	$seo_favicon_url = !empty($seo_data['seo_favicon']) ? '/data/file/seo/' . $seo_data['seo_favicon'] : '';
	$seo_html = <<<HTML
<link rel="canonical" href="{$seo_data['seo_url']}" />
<meta name="title" content="{$seo_data['seo_title']}" />
<meta name="keywords" content="{$seo_data['seo_keyword']}"/>
<meta name="description" content="{$seo_data['seo_description']}"/>
<meta property="og:type" content="website">
<meta property="og:title" content="{$seo_data['seo_title']}">
<meta property="og:url" content="{$seo_data['seo_url']}">
<meta property="og:image" content="{$seo_image_url}">
<meta property="og:description" content="{$seo_data['seo_description']}"/>
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="{$seo_data['seo_title']}">
<meta name="twitter:url" content="{$seo_data['seo_url']}">
<meta name="twitter:image" content="{$seo_image_url}">
<meta name="twitter:description" content="{$seo_data['seo_description']}"/>
<link rel="shortcut icon" type="image/x-icon" href="{$seo_favicon_url}" />
HTML;
	return $seo_html;
}

//변수설정
$enp_typt["1"]="법인기업";
$enp_typt["2"]="개인기업";
$enp_fcdt["01"]="주식회사";
$enp_fcdt["02"]="합명회사";
$enp_fcdt["03"]="합자회사";
$enp_fcdt["04"]="유한회사";
$enp_fcdt["05"]="비영리재단법인";
$enp_fcdt["06"]="비영리사단법인";
$enp_fcdt["07"]="조합";
$enp_fcdt["08"]="외국법인";
$enp_fcdt["09"]="의료법인";
$enp_fcdt["10"]="학교법인";
$enp_fcdt["11"]="종교법인";
$enp_fcdt["12"]="학술장학법인";
$enp_fcdt["13"]="사회복지법인";
$enp_fcdt["14"]="기타공익법인";
$enp_fcdt["16"]="개인기업";
$enp_fcdt["17"]="개인동업기업";
$enp_fcdt["18"]="법인격없는 단체";
$enp_fcdt["19"]="유한책임회사";
$enp_fcdt["99"]="기타법인";

$ENP_SZE['01']="대기업";
$ENP_SZE['02']="중기업";
$ENP_SZE['03']="소기업";
$ENP_SZE['04']="한시성중소기업";
$ENP_SZE['05']="중견기업";
$ENP_SZE['06']="소상공인";
$ENP_SZE['07']="보호대상 중견기업";
$ENP_SZE['08']="판단제외대상";

$kind_cd['제조사']="M";
$kind_cd['유통사']="D";
$kind_cd['기타']="C";

//S:유지, C:취소, E:종료
$ms_status_text['S']="결제완료";
$ms_status_text['C']="구독취소";
$ms_status_text['E']="구독종료";
$ms_status_text['T']="취소예정";
$ms_status_cd['유지']="S";
$ms_status_cd['취소환불']="C";
$ms_status_cd['만료종료']="E";
$ms_status_cd['만료정지']="T";
