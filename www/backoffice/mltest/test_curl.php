<?php
$targets = [
    'EC2' => 'https://15.164.22.161/health',
    'Google' => 'https://www.google.com',
];
foreach ($targets as $name => $url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>8, CURLOPT_SSL_VERIFYPEER=>false, CURLOPT_SSL_VERIFYHOST=>false]);
    $r = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo "$name: HTTP $code | " . ($err ?: 'OK') . " | " . substr($r, 0, 60) . "\n";
}
