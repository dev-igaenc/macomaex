<?php
include_once('./_common.php');

header('Content-Type: application/json; charset=utf-8');

$daily_update_hour = 18;
$state_file = G5_DATA_PATH . '/live_stats.json';

$config = [
    'match_request' => ['base' => 12480, 'min' => 0, 'max' => 6, 'seed' => 200],
    'match_accept' => ['base' => 8560, 'min' => 0, 'max' => 3, 'seed' => 120],
    'members' => ['base' => 2700, 'min' => 0, 'max' => 4, 'seed' => 80],
    'request_rate' => ['base' => 28.37, 'monthly' => 0.01],
    'accept_rate' => ['base' => 31.10, 'monthly' => 0.0125]
];

function safe_rand($min, $max) {
    return mt_rand((int)$min, (int)$max);
}

function init_state($config) {
    $now = time();
    return [
        'updated_at' => $now,
        'values' => [
            'match_request' => $config['match_request']['base'] + safe_rand(0, $config['match_request']['seed']),
            'match_accept' => $config['match_accept']['base'] + safe_rand(0, $config['match_accept']['seed']),
            'members' => $config['members']['base'] + safe_rand(0, $config['members']['seed']),
        ],
        'rates' => [
            'request_rate' => $config['request_rate']['base'],
            'accept_rate' => $config['accept_rate']['base'],
        ],
        'history' => [
            'match_request' => [],
            'match_accept' => [],
            'members' => [],
        ],
    ];
}

function push_history(&$state, $limit = 12) {
    $state['history']['match_request'][] = $state['values']['match_request'];
    $state['history']['match_accept'][] = $state['values']['match_accept'];
    $state['history']['members'][] = $state['values']['members'];

    if (count($state['history']['match_request']) > $limit) array_shift($state['history']['match_request']);
    if (count($state['history']['match_accept']) > $limit) array_shift($state['history']['match_accept']);
    if (count($state['history']['members']) > $limit) array_shift($state['history']['members']);
}

function apply_ticks(&$state, $ticks, $config) {
    for ($i = 0; $i < $ticks; $i++) {
        $state['values']['match_request'] += safe_rand($config['match_request']['min'], $config['match_request']['max']);
        $state['values']['match_accept'] += safe_rand($config['match_accept']['min'], $config['match_accept']['max']);
        $state['values']['members'] += safe_rand($config['members']['min'], $config['members']['max']);
        push_history($state);
    }

    $daily_request = pow(1 + $config['request_rate']['monthly'], 1 / 30) - 1;
    $daily_accept = pow(1 + $config['accept_rate']['monthly'], 1 / 30) - 1;
    $request_rate = $state['rates']['request_rate'] ?? $config['request_rate']['base'];
    $accept_rate = $state['rates']['accept_rate'] ?? $config['accept_rate']['base'];

    $request_rate = $request_rate * pow(1 + $daily_request, $ticks);
    $accept_rate = $accept_rate * pow(1 + $daily_accept, $ticks);

    $state['rates']['request_rate'] = round($request_rate, 2);
    $state['rates']['accept_rate'] = round($accept_rate, 2);
}

function load_state($file) {
    if (!is_file($file)) return null;
    $raw = @file_get_contents($file);
    if (!$raw) return null;
    $data = json_decode($raw, true);
    if (!is_array($data) || !isset($data['values']) || !isset($data['history'])) return null;
    return $data;
}

function save_state($file, $state) {
    @file_put_contents($file, json_encode($state, JSON_UNESCAPED_UNICODE), LOCK_EX);
}

$state = load_state($state_file);
if (!$state) {
    $state = init_state($config);
    for ($i = 0; $i < 12; $i++) {
        push_history($state);
        $state['values']['match_request'] += safe_rand(0, 1);
        $state['values']['match_accept'] += safe_rand(0, 1);
        $state['values']['members'] += safe_rand(0, 1);
    }
    save_state($state_file, $state);
}
if (!isset($state['rates'])) {
    $state['rates'] = [
        'request_rate' => $config['request_rate']['base'],
        'accept_rate' => $config['accept_rate']['base'],
    ];
    save_state($state_file, $state);
}

$tz = new DateTimeZone('Asia/Seoul');
$now = new DateTime('now', $tz);
$last = new DateTime('@' . $state['updated_at']);
$last->setTimezone($tz);

$next = clone $last;
$next->setTime($daily_update_hour, 0, 0);
if ($last >= $next) {
    $next->modify('+1 day');
}

$ticks = 0;
$last_applied = null;
while ($next <= $now) {
    $ticks++;
    $last_applied = clone $next;
    $next->modify('+1 day');
}

if ($ticks > 0) {
    apply_ticks($state, $ticks, $config);
    $state['updated_at'] = $last_applied->getTimestamp();
    save_state($state_file, $state);
}

$state['updated_at_iso'] = date('c', $state['updated_at']);
echo json_encode($state, JSON_UNESCAPED_UNICODE);

