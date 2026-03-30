<?php
if (!defined('_GNUBOARD_')) exit;

// ML API 설정 (절대 외부 노출 금지)
define('ML_API_URL', macomaex_env('ML_API_URL', ''));
define('ML_API_KEY', macomaex_env('ML_API_KEY', ''));

// 허용 IP (비워두면 IP 제한 없음, Referer 체크만 적용)
define('API_ALLOWED_IPS', '');  // 예: '127.0.0.1,192.168.0.100'

// Rate Limit (같은 IP에서 분당 최대 요청 수)
define('API_RATE_LIMIT', 5);
