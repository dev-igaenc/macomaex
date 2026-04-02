<?
	$protocol = stripos($_SERVER['SERVER_PROTOCOL'], 'https') === true ? 'https://' : 'http://';

	$NO_STATIC_TITLE											= $SITEINFO_TITLE;
	$NO_META_KEYWORDS									= $SITEINFO_META_KEYWORDS;
	$NO_META_DESCRIPTION								= $SITEINFO_META_DESCRIPTION;
	$NO_META_URL												= $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$NO_META_TWITTER_CARD								= "Summary";
	$NO_META_TWITTER_URL								= $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$NO_META_TWITTER_TITLE								= $NO_STATIC_TITLE;
	$NO_META_TWITTER_DESCRIPTION				= $NO_META_DESCRIPTION;
	$NO_META_TWITTER_IMAGE							= $NO_IS_SUBDIR."/uploads/meta/".$SITEINFO_META_THUMB;
	$NO_META_OG_URL											= $protocol.$_SERVER['HTTP_HOST'];
	$NO_META_OG_TYPE										= "website";
	$NO_META_OG_IMAGE										= $NO_META_OG_URL."/uploads/meta/".$SITEINFO_META_THUMB;
	$NO_META_OG_SITE_NAME								= $NO_STATIC_TITLE;
	$NO_META_OG_LOCALE									= "ko";
	$NO_META_OG_TITLE										= $NO_STATIC_TITLE;
	$NO_META_OG_DESCRIPTION							= $NO_META_DESCRIPTION;
	$NO_META_OG_COUNTRY_NAME						= "";
	$NO_META_ITEMPROP_NAME							= $NO_STATIC_TITLE;
	$NO_META_ITEMPROP_IMAGE							= "";							
	$NO_META_ITEMPROP_URL								= $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$NO_META_ITEMPROP_DESCRIPTION				= $NO_META_DESCRIPTION;
	$NO_META_ITEMPROP_KEYWORD					= $NO_META_KEYWORDS;
	$NO_META_SHORTCUT_ICON							= $NO_IS_SUBDIR."/uploads/meta/".$SITEINFO_META_FAVICON_ICO;
	$NO_META_APPLE_THOUCH_ICON					= "";

?>
<title><?=$NO_STATIC_TITLE?><?=$NO_STATIC_SUBTITLE?></title>
<meta charset="utf-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="autocomplete" content="off" />
<meta name="keywords" content="<?=$NO_META_KEYWORDS?>">
<meta name="description" content="<?=$NO_META_DESCRIPTION?>">
<meta name="image" content="<?=$NO_META_OG_IMAGE?>">
<meta name="robots" content="index, follow" />
<meta property="og:locale" content="ko_KR" />
<meta property="og:url" content="<?=$NO_META_OG_URL?>">
<meta property="og:image" content="<?=$NO_META_OG_IMAGE?>">
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?=$NO_META_OG_SITE_NAME?>">
<meta property="og:title" content="<?=$NO_META_OG_TITLE?><?=$NO_STATIC_SUBTITLE?>">
<meta property="og:description" content="<?=$NO_META_OG_DESCRIPTION?>">
<meta name="google-site-verification" content="RBXSREGX0LG7wfC2N8YiRqnithHKEwbNrkQvD3SxW98" />
<meta name="naver-site-verification" content="6241b7995f757d0d461f12df945423b9ec0c033c" />
<link rel="shortcut icon" href="<?=$NO_META_SHORTCUT_ICON?>">