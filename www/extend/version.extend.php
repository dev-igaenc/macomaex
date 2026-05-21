<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 정적 자산 버전 정책
// 1) APP_ASSET_VERSION 환경변수
// 2) data/asset.version 파일
// 3) fallback (version.php 수정시각)
$asset_version = getenv('APP_ASSET_VERSION');

if (!$asset_version) {
    $asset_version_file = (defined('G5_DATA_PATH') ? G5_DATA_PATH : dirname(__DIR__).'/data') . '/asset.version';
    if (is_readable($asset_version_file)) {
        $asset_version = trim((string) file_get_contents($asset_version_file));
    }
}

if (!$asset_version) {
    $version_file_mtime = @filemtime(dirname(__DIR__) . '/version.php');
    $asset_version = $version_file_mtime ? date('YmdHis', $version_file_mtime) : date('Ymd');
}

define('G5_JS_VER', $asset_version);
define('G5_CSS_VER', $asset_version);
