<?php
session_start();
set_time_limit(120);
ignore_user_abort(true);

// ── 인증 ──────────────────────────────────────────────────
if (!isset($_SESSION['mltest_auth'])) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => '인증이 필요합니다.']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

require_once __DIR__ . '/api_config.php';
require_once __DIR__ . '/prompts.php';

$apiKey = GEMINI_API_KEY;
if (!$apiKey || strlen($apiKey) < 22) {
    http_response_code(500);
    echo json_encode(['error' => '서버 .env에 GEMINI_API_KEY가 설정되지 않았습니다.']);
    exit;
}

// ── 요청 파싱 ─────────────────────────────────────────────
$body = json_decode(file_get_contents('php://input'), true);
if (!is_array($body)) {
    http_response_code(400);
    echo json_encode(['error' => '잘못된 요청 형식입니다.']);
    exit;
}

$ALLOWED_MODELS = [
    'gemini-3.1-pro-preview',
    'gemini-3-flash-preview',
    'gemini-3.1-flash-lite-preview',
    'gemini-2.5-pro',
    'gemini-2.5-pro-thinking',
    'gemini-2.5-flash',
    'gemini-2.5-flash-thinking',
    'gemini-2.5-flash-lite',
];
$ALLOWED_TIERS = ['basic', 'enhanced', 'premium', 'trade'];

$modelKey    = $body['model'] ?? '';
$promptTier  = $body['tier']  ?? 'basic';
$companyData = $body['data']  ?? [];

if (!in_array($modelKey, $ALLOWED_MODELS, true)) {
    http_response_code(400);
    echo json_encode(['error' => '지원하지 않는 모델: ' . htmlspecialchars($modelKey)]);
    exit;
}
if (!in_array($promptTier, $ALLOWED_TIERS, true)) {
    http_response_code(400);
    echo json_encode(['error' => '지원하지 않는 프롬프트 티어']);
    exit;
}
if (empty($companyData) || empty($companyData['source_enp_nm'])) {
    http_response_code(400);
    echo json_encode(['error' => '기업 데이터가 없습니다.']);
    exit;
}
if (empty($companyData['buy_top1']) && empty($companyData['sell_top1'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ML 추천 결과가 없습니다. 먼저 추천 조회를 완료하세요.']);
    exit;
}

// ── 모델 설정 ─────────────────────────────────────────────
$MODEL_CFG = [
    'gemini-3.1-pro-preview'       => ['model_id' => 'gemini-3.1-pro-preview',       'thinking' => false, 'label' => 'Gemini 3.1 Pro Preview',       'search' => false, 'search_tool' => null],
    'gemini-3-flash-preview'       => ['model_id' => 'gemini-3-flash-preview',        'thinking' => false, 'label' => 'Gemini 3 Flash Preview',        'search' => false, 'search_tool' => null],
    'gemini-3.1-flash-lite-preview'=> ['model_id' => 'gemini-3.1-flash-lite-preview', 'thinking' => false, 'label' => 'Gemini 3.1 Flash Lite Preview', 'search' => false, 'search_tool' => null],
    'gemini-2.5-pro'               => ['model_id' => 'gemini-2.5-pro',               'thinking' => false, 'label' => 'Gemini 2.5 Pro',               'search' => false, 'search_tool' => null],
    'gemini-2.5-pro-thinking'      => ['model_id' => 'gemini-2.5-pro',               'thinking' => true,  'label' => 'Gemini 2.5 Pro (Thinking)',    'search' => false, 'search_tool' => null],
    'gemini-2.5-flash'             => ['model_id' => 'gemini-2.5-flash',             'thinking' => false, 'label' => 'Gemini 2.5 Flash',             'search' => false, 'search_tool' => null],
    'gemini-2.5-flash-thinking'    => ['model_id' => 'gemini-2.5-flash',             'thinking' => true,  'label' => 'Gemini 2.5 Flash (Thinking)',  'search' => false, 'search_tool' => null],
    'gemini-2.5-flash-lite'        => ['model_id' => 'gemini-2.5-flash-lite',        'thinking' => false, 'label' => 'Gemini 2.5 Flash Lite',        'search' => false, 'search_tool' => null],
];
$cfg = $MODEL_CFG[$modelKey];

// ── 프롬프트 빌드 ─────────────────────────────────────────
$prompt = get_prompt($promptTier, $companyData);

// ── maxOutputTokens 티어별 설정 ───────────────────────────
$maxTokens = match($promptTier) {
    'basic'    => 4500,
    'enhanced' => 6500,
    'trade'    => 6000,
    default    => 8192,
};

// ── Gemini API 요청 구성 ──────────────────────────────────
$requestBody = [
    'system_instruction' => ['parts' => [['text' => $prompt['system_instruction']]]],
    'contents'           => [['role' => 'user', 'parts' => [['text' => $prompt['user_content']]]]],
    'generationConfig'   => [
        'temperature'     => $prompt['temperature'] ?? 0.3,
        'maxOutputTokens' => $maxTokens,
    ],
];

if ($cfg['thinking']) {
    $requestBody['generationConfig']['thinkingConfig'] = [
        'thinkingBudget'  => -1,
        'includeThoughts' => false,
    ];
} elseif (str_contains($cfg['model_id'], 'flash') || $cfg['model_id'] === 'gemini-2.5-pro') {
    // Flash 계열 + 2.5 Pro 비-Thinking: adaptive thinking 명시적 비활성화
    $requestBody['generationConfig']['thinkingConfig'] = ['thinkingBudget' => 0];
}

if ($cfg['search'] && $cfg['search_tool']) {
    if ($cfg['search_tool'] === 'google_search') {
        $requestBody['tools'] = [['google_search' => (object)[]]];
    } elseif ($cfg['search_tool'] === 'google_search_retrieval') {
        $requestBody['tools'] = [['google_search_retrieval' => ['dynamic_retrieval_config' => ['mode' => 'MODE_DYNAMIC']]]];
    }
}

$apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/'
        . urlencode($cfg['model_id'])
        . ':generateContent?key='
        . urlencode($apiKey);

// ── API 호출 (503/429 재시도: 최대 3회, 1s→2s→4s) ─────────
$startMs    = (int)(microtime(true) * 1000);
$payload    = json_encode($requestBody, JSON_UNESCAPED_UNICODE);
$maxRetries = 3;
$raw = $code = $curlErr = null;

for ($attempt = 0; $attempt <= $maxRetries; $attempt++) {
    if ($attempt > 0) sleep(1 << ($attempt - 1));

    $ch = curl_init($apiUrl);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 25,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);
    $raw     = curl_exec($ch);
    $code    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if (!$curlErr && $raw && !in_array($code, [429, 503], true)) break;
}

$elapsedMs = (int)(microtime(true) * 1000) - $startMs;

if ($curlErr || !$raw) {
    $isTimeout = str_contains($curlErr, 'timed out') || str_contains($curlErr, 'Operation timed');
    http_response_code(502);
    echo json_encode(['error' => $isTimeout
        ? 'Gemini 응답 시간 초과 (25초). Flash + Basic 티어를 사용하세요.'
        : 'Gemini API 연결 실패: ' . $curlErr]);
    exit;
}

$resp = json_decode($raw, true);

if ($code !== 200) {
    $errMsg = $resp['error']['message'] ?? '알 수 없는 오류';
    $hint   = in_array($code, [429, 503]) ? ' (잠시 후 다시 시도해 주세요)' : '';
    http_response_code($code);
    echo json_encode(['error' => "Gemini API 오류 ({$code}): {$errMsg}{$hint}"]);
    exit;
}

// ── 결과 추출 (thought 파트 제외, 여러 파트 합산) ──────────
$reportText = '';
$_parts = $resp['candidates'][0]['content']['parts'] ?? [];
foreach ($_parts as $_part) {
    if (isset($_part['text']) && empty($_part['thought'])) {
        $reportText .= $_part['text'];
    }
}
// fallback: thought 파트뿐이면 전체 텍스트 합산
if ($reportText === '') {
    foreach ($_parts as $_part) {
        if (isset($_part['text'])) $reportText .= $_part['text'];
    }
}

if ($reportText === '') {
    $finishReason = $resp['candidates'][0]['finishReason'] ?? 'UNKNOWN';
    $dbg = array_map(fn($p) => [
        'keys'      => array_keys($p),
        'thought'   => $p['thought']  ?? null,
        'text_len'  => isset($p['text']) ? mb_strlen($p['text']) : null,
        'text_head' => isset($p['text']) ? mb_substr($p['text'], 0, 80) : null,
    ], $_parts);
    echo json_encode([
        'error'       => "보고서 텍스트를 추출하지 못했습니다. (finishReason: {$finishReason})",
        'debug_parts' => $dbg,
        'debug_keys'  => array_keys($resp['candidates'][0] ?? []),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ── 후처리: ** 제거 (테스트 환경 — 기업명 마스킹 없음) ──
$reportText = str_replace('__', '', $reportText);
// (추론) 제거
$reportText = preg_replace('/\s*\(추론\)/', '', $reportText);

// ── 비용 산정 ─────────────────────────────────────────────
$usage        = $resp['usageMetadata'] ?? [];
$inputTokens  = (int)($usage['promptTokenCount']     ?? 0);
$outputTokens = (int)($usage['candidatesTokenCount'] ?? 0);
$thinkTokens  = (int)($usage['thoughtsTokenCount']   ?? 0);
$totalTokens  = (int)($usage['totalTokenCount']      ?? 0);

$PRICING = [
    'gemini-3.1-pro-preview'        => ['in' => 2.00, 'out' => 12.00, 'think' => 12.00, 'confirmed' => true],
    'gemini-3-flash-preview'        => ['in' => 0.50, 'out' =>  3.00, 'think' =>  3.00, 'confirmed' => true],
    'gemini-3.1-flash-lite-preview' => ['in' => 0.25, 'out' =>  1.50, 'think' =>  1.50, 'confirmed' => true],
    'gemini-2.5-pro'                => ['in' => 1.25, 'out' => 10.00, 'think' => 10.00, 'confirmed' => true],
    'gemini-2.5-flash'               => ['in' => 0.30, 'out' =>  2.50, 'think' =>  2.50, 'confirmed' => true],
];
$pr            = $PRICING[$cfg['model_id']] ?? ['in' => 0, 'out' => 0, 'think' => 0, 'confirmed' => false];
$costUsd       = ($inputTokens / 1e6 * $pr['in'])
               + ($outputTokens / 1e6 * $pr['out'])
               + ($thinkTokens  / 1e6 * $pr['think']);
$costConfirmed = $pr['confirmed'];

$searchUsed = $cfg['search'] && !empty($resp['candidates'][0]['groundingMetadata']['webSearchQueries']);

echo json_encode([
    'report'         => $reportText,
    'model_label'    => $cfg['label'],
    'model_key'      => $modelKey,
    'thinking'       => $cfg['thinking'],
    'search'         => $cfg['search'],
    'search_used'    => $searchUsed,
    'prompt_tier'    => $promptTier,
    'elapsed_ms'     => $elapsedMs,
    'input_tokens'   => $inputTokens,
    'output_tokens'  => $outputTokens,
    'think_tokens'   => $thinkTokens,
    'total_tokens'   => $totalTokens,
    'cost_usd'       => round($costUsd, 6),
    'cost_krw'       => round($costUsd * 1500),
    'cost_confirmed' => $costConfirmed,
    'report_chars'   => mb_strlen($reportText),
], JSON_UNESCAPED_UNICODE);
