<?php
// 이 파일은 새로운 파일 생성시 반드시 포함되어야 함
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$g5_debug['php']['begin_time'] = $begin_time = get_microtime();

if (!isset($g5['title'])) {
	$g5['title'] = $config['cf_title'];
	$g5_head_title = $g5['title'];
}else {
	// 상태바에 표시될 제목
	$g5_head_title = implode(' | ', array_filter(array($g5['title'], $config['cf_title'])));
}

if($board['bo_5'])$gr_subject = $board['bo_5']; // 그룹명
if($board['bo_6'])$gr_text = $board['bo_6']; // 페이지 설명

if($board['bo_subject'])$bo_subject = $board['bo_subject']; // 게시판명
if($board['bo_4'])$bo_text = $board['bo_4']; // 페이지 설명


if($board['bo_7']) $depth_4 = $board['bo_7']; else $depth_4 = 0; // 4뎁스 메뉴
if($board['bo_8']) $depth_3 = $board['bo_8']; else $depth_3 = 0; // 3뎁스 메뉴
if($board['bo_9']) $depth_2 = $board['bo_9']; else $depth_2 = 0; // 2뎁스 메뉴
if($board['bo_10']) $depth_1 = $board['bo_10']; else $depth_1 = 0; // 1뎁스 메뉴

$g5['title'] = strip_tags($g5['title']);
$g5_head_title = strip_tags($g5_head_title);

// 현재 접속자
// 게시판 제목에 ' 포함되면 오류 발생
$g5['lo_location'] = addslashes($g5['title']);
if (!$g5['lo_location'])
    $g5['lo_location'] = addslashes(clean_xss_tags($_SERVER['REQUEST_URI']));
$g5['lo_url'] = addslashes(clean_xss_tags($_SERVER['REQUEST_URI']));
if (strstr($g5['lo_url'], '/'.G5_ADMIN_DIR.'/') || $is_admin == 'super') $g5['lo_url'] = '';

/*
// 만료된 페이지로 사용하시는 경우
header("Cache-Control: no-cache"); // HTTP/1.1
header("Expires: 0"); // rfc2616 - Section 14.21
header("Pragma: no-cache"); // HTTP/1.0
*/
?>
<!doctype html>
<html lang="<?php if (preg_match("/_en|\/en\//i", $_SERVER['REQUEST_URI'])){ ?>en<?php }else if (preg_match('/_cn|\/cn\//i', $_SERVER['REQUEST_URI'])){ ?>zh<?php }else if (preg_match('/_jp|\/jp\//i', $_SERVER['REQUEST_URI'])){ ?>ja<?php }else{ ?>ko<?php } ?>" <?php echo defined('_INDEX_') ? "class='main'" : "class='sub'"; ?>>
<head>

<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
<meta name="HandheldFriendly" content="true"><!-- // * true : 모바일용(화면 크기 조정 기능) * false : Desktop에 최적화된 html 사용 -->
<meta http-equiv="imagetoolbar" content="no">
<meta name="format-detection" content="telephone=no, address=no, email=no, date=no" />

<!-- <link rel="shortcut icon" type="image/x-icon" href="/img/favicon.ico" /> -->

<meta name="naver-site-verification" content="d2dcaf0af8141b76850e35ec6e91446f7a94f8ec" />

<meta name="robots" content="index,follow" />

<?php
/*
if (G5_IS_MOBILE) {
	echo '<meta name="HandheldFriendly" content="true">'.PHP_EOL;
	echo '<meta name="format-detection" content="telephone=no">'.PHP_EOL;
} else {
	echo '<meta http-equiv="imagetoolbar" content="no">'.PHP_EOL;
	echo '<meta http-equiv="X-UA-Compatible" content="IE=edge">'.PHP_EOL;
}
*/
if($config['cf_add_meta'])
	echo $config['cf_add_meta'].PHP_EOL;

    $seo_meta=get_seo_data("default");
	echo $seo_meta;
?>

<!-- <link href="https://cdn.jsdelivr.net/gh/sunn-us/SUIT/fonts/variable/woff2/SUIT-Variable.css" rel="stylesheet"> -->

<title><?php echo $g5_head_title; ?></title>
<link href="/theme/prix/font/SUIT-Variable.css" rel="stylesheet">
<link href="/theme/prix/mont/font.css" rel="stylesheet">
<link href="/theme/prix/css/main.css" rel="stylesheet">
<link href="/theme/prix/css/sub.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>



<link rel="stylesheet" href="<?php echo G5_THEME_CSS_URL; ?>/front.css?ver=<?php echo G5_CSS_VER; ?>">
<?php if (G5_IS_MOBILE) {?>
<link rel="stylesheet" href="<?php echo G5_THEME_CSS_URL; ?>/mobile.css?ver=<?php echo G5_CSS_VER; ?>">
<link rel="stylesheet" href="<?php echo G5_THEME_CSS_URL; ?>/layout_m.css?ver=<?php echo G5_CSS_VER; ?>">
<link rel="stylesheet" href="<?php echo G5_THEME_CSS_URL; ?>/front_m.css?ver=<?php echo G5_CSS_VER; ?>">
<link rel="stylesheet" href="<?php echo G5_THEME_CSS_URL; ?>/contents_m.css?ver=<?php echo G5_CSS_VER; ?>">
<?php }else{?>
<link rel="stylesheet" href="<?php echo G5_THEME_CSS_URL; ?>/default.css">
<link rel="stylesheet" href="<?php echo G5_THEME_CSS_URL; ?>/layout.css?ver=<?php echo G5_CSS_VER; ?>">
<link rel="stylesheet" href="<?php echo G5_THEME_CSS_URL; ?>/contents.css?ver=<?php echo G5_CSS_VER; ?>">
<?php }?>


<!--[if lte IE 8]>
<script src="<?php echo G5_JS_URL ?>/html5.js"></script>
<![endif]-->
<script>
// 자바스크립트에서 사용하는 전역변수 선언
var g5_url       = "<?php echo G5_URL ?>";
var g5_bbs_url   = "<?php echo G5_BBS_URL ?>";
var g5_is_member = "<?php echo isset($is_member)?$is_member:''; ?>";
var g5_is_admin  = "<?php echo isset($is_admin)?$is_admin:''; ?>";
var g5_is_mobile = "<?php echo G5_IS_MOBILE ?>";
var g5_bo_table  = "<?php echo isset($bo_table)?$bo_table:''; ?>";
var g5_sca       = "<?php echo isset($sca)?$sca:''; ?>";
var g5_editor    = "<?php echo ($config['cf_editor'] && $board['bo_use_dhtml_editor'])?$config['cf_editor']:''; ?>";
var g5_cookie_domain = "<?php echo G5_COOKIE_DOMAIN ?>";
var siteTitle = "<?php echo $config['cf_title']; ?>";

history.scrollRestoration = "auto";
</script>

<script type="text/javascript" src="<?php echo G5_THEME_JS_URL ?>/jquery.lastest.js"></script>
<script type="text/javascript" src="<?php echo G5_THEME_JS_URL ?>/jquery.ui.lastest.js"></script>
<script type="text/javascript" src="<?php echo G5_THEME_JS_URL; ?>/TweenMax.min.js"></script>
<script type="text/javascript" src="<?php echo G5_THEME_JS_URL; ?>/BezierPlugin.min.js"></script>
<script type="text/javascript" src="<?php echo G5_THEME_JS_URL; ?>/jquery.throttledresize.js"></script>
<script type="text/javascript" src="<?php echo G5_THEME_JS_URL; ?>/jquery.rwdImageMaps.min.js"></script>
<script type="text/javascript" src="<?php echo G5_THEME_JS_URL; ?>/prefixfree.min.js"></script>
<script type="text/javascript" src="<?php echo G5_THEME_JS_URL; ?>/ofi.js"></script>
<script type="text/javascript" src="<?php echo G5_THEME_JS_URL; ?>/picturefill.min.js"></script>
<script type="text/javascript" src="<?php echo G5_THEME_JS_URL; ?>/jquery-input-file-text.js"></script>

<link rel="stylesheet" href="<?php echo G5_THEME_CSS_URL; ?>/aos.css">
<script type="text/javascript" src="<?php echo G5_THEME_JS_URL ?>/aos.js"></script>

<link rel="stylesheet" href="<?php echo G5_THEME_CSS_URL; ?>/slick.css">
<script type="text/javascript" src="<?php echo G5_THEME_JS_URL; ?>/slick.js"></script>

<link rel="stylesheet" href="<?php echo G5_THEME_CSS_URL; ?>/jquery.fancybox.css">
<script type="text/javascript" src="<?php echo G5_THEME_JS_URL; ?>/jquery.fancybox.js"></script>

<script type="text/javascript" src="<?php echo G5_THEME_JS_URL ?>/publisher.js?ver=<?php echo G5_JS_VER; ?>"></script>

<script type="text/javascript" src="/build/jquery.waypoints.min.js"></script>

<?php
//add_javascript('<script src="'.G5_JS_URL.'/jquery-1.12.4.min.js"></script>', 0);
//add_javascript('<script src="'.G5_JS_URL.'/jquery-migrate-1.4.1.min.js"></script>', 0);
//add_javascript('<script src="'.G5_JS_URL.'/jquery.menu.js?ver='.G5_JS_VER.'"></script>', 0);
add_javascript('<script src="'.G5_JS_URL.'/common.js?ver='.G5_JS_VER.'"></script>', 0);
//add_javascript('<script src="'.G5_JS_URL.'/wrest.js?ver='.G5_JS_VER.'"></script>', 0);
add_javascript('<script src="'.G5_JS_URL.'/placeholders.min.js"></script>', 0);
$font_awesome_css_url = G5_JS_URL.'/font-awesome/fonts/css/font-awesome.min.css';
if (!is_file(G5_PATH.'/js/font-awesome/fonts/css/font-awesome.min.css')) {
	$font_awesome_css_url = G5_JS_URL.'/font-awesome/css/font-awesome.min.css';
}
add_stylesheet('<link rel="stylesheet" href="'.$font_awesome_css_url.'">', 0);

if(G5_IS_MOBILE) {
	add_javascript('<script src="'.G5_JS_URL.'/modernizr.custom.70111.js"></script>', 1); // overflow scroll 감지
}
if(!defined('G5_IS_ADMIN')) {
	$cf_add_script = isset($config['cf_add_script']) ? (string) $config['cf_add_script'] : '';
	if (!is_file(G5_THEME_PATH.'/js/company-detail-actions.js')) {
		$cf_add_script = preg_replace('#<script[^>]*company-detail-actions\.js[^>]*>\s*</script>#i', '', $cf_add_script);
	}
	echo $cf_add_script;
}
?>
<link rel="stylesheet" href="<?php echo G5_THEME_CSS_URL; ?>/jquery.alerts.css">
<script type="text/javascript" src="<?php echo G5_THEME_JS_URL; ?>/jquery.alerts.js"></script>


<!-- Google Tag Manager 2025-02-07 -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-PRWD7J9P');</script>
<!-- End Google Tag Manager -->

</head>
<body<?php echo isset($g5['body_script']) ? $g5['body_script'] : ''; ?>>

<!-- Google Tag Manager (noscript) 2025-02-07-->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PRWD7J9P"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->


<div id="progressBar"></div>
<div id="wrap" class="<?php if (!defined("_INDEX_")) { ?> sub<?php } ?>">

<?php
if ($is_member) { // 회원이라면 로그인 중이라는 메세지를 출력해준다.
	$sr_admin_msg = '';
	if ($is_admin == 'super') $sr_admin_msg = "최고관리자 ";
	else if ($is_admin == 'group') $sr_admin_msg = "그룹관리자 ";
	else if ($is_admin == 'board') $sr_admin_msg = "게시판관리자 ";

	echo '<div id="hd_login_msg">'.$sr_admin_msg.get_text($member['mb_nick']).'님 로그인 중 ';
	echo '<a href="'.G5_BBS_URL.'/logout.php">로그아웃</a></div>';
}

if (preg_match("/_en|\/en\//i", $_SERVER['REQUEST_URI'])){
	//echo "영";
	include_once(G5_THEME_PATH.'/menu_en.php');
}else if (preg_match('/_cn|\/cn\//i', $_SERVER['REQUEST_URI'])){
	//echo "중";
	include_once(G5_THEME_PATH.'/menu_cn.php');
}else if (preg_match('/_jp|\/jp\//i', $_SERVER['REQUEST_URI'])){
	//echo "일";
	include_once(G5_THEME_PATH.'/menu_jp.php');
}else{
	//echo "국";
	include_once(G5_THEME_PATH.'/menu.php');
}
