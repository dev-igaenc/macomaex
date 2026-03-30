<?php
class B2bRecommender {
    private $api_url = '';
    private $api_key = '';
    private $timeout = 3;

    public function __construct() {
        if (function_exists('macomaex_env')) {
            $this->api_url = macomaex_env('ML_API_URL', '');
            $this->api_key = macomaex_env('ML_API_KEY', '');
            return;
        }

        $scanner = defined('INI_SCANNER_RAW') ? INI_SCANNER_RAW : INI_SCANNER_NORMAL;
        $values = @parse_ini_file(dirname(dirname(__FILE__)) . '/.env', false, $scanner);

        if (is_array($values)) {
            $this->api_url = isset($values['ML_API_URL']) ? $values['ML_API_URL'] : '';
            $this->api_key = isset($values['ML_API_KEY']) ? $values['ML_API_KEY'] : '';
        }
    }

    /**
     * ML 추천 API 호출
     */
    public function recommend($kedcd, $candidates, $top_n = 20) {
        $payload = json_encode([
            'kedcd' => $kedcd,
            'candidates' => $candidates,
            'top_n' => $top_n
        ]);

        $ch = curl_init($this->api_url . '/recommend');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-API-Key: ' . $this->api_key
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 1,
        ]);

        $raw = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if (!$err && $http_code === 200 && $raw) {
            $result = json_decode($raw, true);
            if ($result && $result['status'] === 'ok') {
                return $result;
            }
        }

        return [
            'status' => 'error',
            'sellers' => [],
            'buyers' => [],
            'error' => $err ?: 'HTTP ' . $http_code
        ];
    }

    public function health() {
        $ch = curl_init($this->api_url . '/health');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 2,
        ]);
        $raw = curl_exec($ch);
        curl_close($ch);
        return json_decode($raw, true);
    }
}
