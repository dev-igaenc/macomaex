<?php
include_once('./_common.php');

// 로그인중인 경우 회원가입 할 수 없습니다.
if ($is_member) {
    goto_url(G5_URL);
}

if (!function_exists('macomaex_normalize_return_url')) {
    function macomaex_normalize_return_url($raw_url) {
        $raw_url = trim((string)$raw_url);
        if ($raw_url === '') return '';
        if (preg_match('/^\s*javascript:/i', $raw_url)) return '';

        $parts = @parse_url($raw_url);
        if ($parts === false) return '';

        // Absolute URL: only allow same host.
        if (isset($parts['scheme']) || isset($parts['host'])) {
            $host = isset($parts['host']) ? strtolower((string)$parts['host']) : '';
            $my_host = isset($_SERVER['HTTP_HOST']) ? strtolower((string)$_SERVER['HTTP_HOST']) : '';
            if ($host === '' || $my_host === '' || $host !== $my_host) return '';
            $path = isset($parts['path']) ? (string)$parts['path'] : '/';
            $query = isset($parts['query']) ? '?'.$parts['query'] : '';
            $url = $path . $query;
        } else {
            // Relative path only
            if ($raw_url[0] !== '/') return '';
            $url = $raw_url;
        }

        // Prevent loops back to register pages.
        if (strpos($url, '/bbs/register.php') !== false || strpos($url, '/bbs/register_result.php') !== false) return '';
        return $url;
    }
}

// 세션을 지웁니다.
set_session("ss_mb_reg", "");

// 회원가입 완료 후 "이전 페이지로" 이동을 위한 진입 URL 저장
$return_candidate = '';
if (isset($_REQUEST['url']) && $_REQUEST['url'] !== '') {
    $return_candidate = (string)$_REQUEST['url'];
} elseif (isset($_SERVER['HTTP_REFERER'])) {
    $return_candidate = (string)$_SERVER['HTTP_REFERER'];
}
$normalized_return_url = macomaex_normalize_return_url($return_candidate);
if ($normalized_return_url !== '') {
    set_session('ss_reg_prev_url', $normalized_return_url);
}

$board['bo_10']="10";
$board['bo_9']="2";
$board['bo_5'] = "멤버쉽";
$board['bo_subject'] = "회원가입";
//$board['bo_4'] = $config['cf_title']."의 혜택을 확인하세요.";
$g5['title'] = '회원 가입';

include_once('./_head.php');

$register_action_url = G5_BBS_URL.'/register_form_update.php';
include_once($member_skin_path.'/register.skin.php');
include_once('./_tail.php');
