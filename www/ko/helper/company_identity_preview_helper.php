<?php
// 비회원에게 목록 상위 기업의 기본 식별 정보를 제한적으로 공개하기 위한 세션 기반 권한 헬퍼.

if (!function_exists('company_identity_preview_now')) {
    function company_identity_preview_now()
    {
        return time();
    }
}

if (!function_exists('company_identity_preview_session_key')) {
    function company_identity_preview_session_key()
    {
        return 'ss_company_identity_preview_grants';
    }
}

if (!function_exists('company_identity_preview_load_grants')) {
    function company_identity_preview_load_grants()
    {
        $raw = (string)get_session(company_identity_preview_session_key());
        $grants = json_decode($raw, true);
        if (!is_array($grants)) return array();

        $now = company_identity_preview_now();
        foreach ($grants as $token => $grant) {
            if (!is_array($grant) || (int)($grant['expires_at'] ?? 0) < $now) {
                unset($grants[$token]);
            }
        }
        return $grants;
    }
}

if (!function_exists('company_identity_preview_save_grants')) {
    function company_identity_preview_save_grants($grants)
    {
        set_session(company_identity_preview_session_key(), json_encode($grants, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}

if (!function_exists('company_identity_preview_issue')) {
    function company_identity_preview_issue($scope, $kedcd, $ttl_seconds = 1800)
    {
        $scope = preg_replace('/[^a-z0-9_-]/i', '', (string)$scope);
        $kedcd = preg_replace('/\D/', '', (string)$kedcd);
        if ($scope === '' || !preg_match('/^\d{10}$/', $kedcd)) return '';

        $grants = company_identity_preview_load_grants();
        if (count($grants) >= 100) {
            uasort($grants, function ($a, $b) {
                return (int)($a['expires_at'] ?? 0) <=> (int)($b['expires_at'] ?? 0);
            });
            $grants = array_slice($grants, -99, null, true);
        }

        try {
            $token = bin2hex(random_bytes(24));
        } catch (Exception $e) {
            $token = hash('sha256', uniqid('identity_preview_', true));
        }
        $grants[$token] = array(
            'scope' => $scope,
            'kedcd' => $kedcd,
            'expires_at' => company_identity_preview_now() + max(60, (int)$ttl_seconds),
        );
        company_identity_preview_save_grants($grants);
        return $token;
    }
}

if (!function_exists('company_identity_preview_is_valid')) {
    function company_identity_preview_is_valid($scope, $kedcd, $token)
    {
        $scope = preg_replace('/[^a-z0-9_-]/i', '', (string)$scope);
        $kedcd = preg_replace('/\D/', '', (string)$kedcd);
        $token = preg_replace('/[^a-f0-9]/i', '', (string)$token);
        if ($scope === '' || !preg_match('/^\d{10}$/', $kedcd) || $token === '') return false;

        $grants = company_identity_preview_load_grants();
        $grant = $grants[$token] ?? null;
        company_identity_preview_save_grants($grants);
        return is_array($grant)
            && hash_equals((string)($grant['scope'] ?? ''), $scope)
            && hash_equals((string)($grant['kedcd'] ?? ''), $kedcd)
            && (int)($grant['expires_at'] ?? 0) >= company_identity_preview_now();
    }
}
