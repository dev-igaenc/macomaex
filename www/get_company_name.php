<?php
include_once('./_common.php');
include_once(G5_PATH.'/extend/user.extend.php');

if (!function_exists('macomaex_company_list_ai_guard_now')) {
    // 추천 목록 진입 세션 만료 시각 계산용 현재 시간
    function macomaex_company_list_ai_guard_now()
    {
        return defined('G5_SERVER_TIME') ? (int)G5_SERVER_TIME : time();
    }
}

if (!function_exists('macomaex_company_list_ai_entry_map_session_key')) {
    // 추천 목록 승인 목록을 저장할 세션 키
    function macomaex_company_list_ai_entry_map_session_key()
    {
        return 'ss_company_list_ai_entry_map';
    }
}

if (!function_exists('macomaex_company_list_ai_load_entry_map')) {
    // 만료되지 않은 추천 목록 승인 목록만 세션에서 꺼냄
    function macomaex_company_list_ai_load_entry_map()
    {
        $raw = (string)get_session(macomaex_company_list_ai_entry_map_session_key());
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) return array();

        $now = macomaex_company_list_ai_guard_now();
        $clean = array();
        foreach ($decoded as $bizno => $entry) {
            $bizno = preg_replace('/\D/', '', (string)$bizno);
            $token = trim((string)($entry['token'] ?? ''));
            $expires = (int)($entry['expires'] ?? 0);
            $updated_at = (int)($entry['updated_at'] ?? 0);

            if (strlen($bizno) !== 10 || $token === '' || $expires < $now) continue;

            $clean[$bizno] = array(
                'token' => $token,
                'expires' => $expires,
                'updated_at' => $updated_at,
            );
        }

        return $clean;
    }
}

if (!function_exists('macomaex_company_list_ai_save_entry_map')) {
    // 최근 승인 목록만 남기고 세션에 다시 저장
    function macomaex_company_list_ai_save_entry_map($entry_map)
    {
        if (!is_array($entry_map) || empty($entry_map)) {
            set_session(macomaex_company_list_ai_entry_map_session_key(), '');
            return;
        }

        uasort($entry_map, function ($a, $b) {
            return (int)($b['updated_at'] ?? 0) <=> (int)($a['updated_at'] ?? 0);
        });

        $trimmed = array_slice($entry_map, 0, 20, true);
        set_session(
            macomaex_company_list_ai_entry_map_session_key(),
            json_encode($trimmed, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }
}

if (!function_exists('macomaex_company_list_ai_grant_entry')) {
    // 재배포 확인 2026-07-06-2: 추천 목록 진입 세션 발급
    // 정상 검색 플로우를 거친 사용자에게만 목록 진입 세션 부여
    function macomaex_company_list_ai_grant_entry($bizno, $bizno_token = '')
    {
        $bizno = preg_replace('/\D/', '', (string)$bizno);
        if (strlen($bizno) !== 10) return;

        $bizno_token = trim((string)$bizno_token);
        if ($bizno_token === '') {
            $bizno_token = (string)simple_crypt($bizno, 'e');
        }
        if ($bizno_token === '') return;

        $now = macomaex_company_list_ai_guard_now();
        $entry_map = macomaex_company_list_ai_load_entry_map();
        // 추천 목록은 최근 정상 진입 목록만 짧게 세션에 유지
        $entry_map[$bizno] = array(
            'token' => $bizno_token,
            'expires' => $now + 1800,
            'updated_at' => $now,
        );
        macomaex_company_list_ai_save_entry_map($entry_map);

        // 하위호환용 마지막 진입값도 함께 유지
        set_session('ss_company_list_ai_bizno', $bizno);
        set_session('ss_company_list_ai_bizno_token', $bizno_token);
        set_session('ss_company_list_ai_expires', (string)($now + 1800));
    }
}

$comnum = isset($_POST['comnum']) ? trim($_POST['comnum']) : '';
$comnum = str_replace("-", "", $comnum);

if (!$comnum) {
    echo json_encode(array('result' => 'fail', 'message' => '사업자번호가 없습니다.'));
    exit;
}
if (!preg_match('/^[0-9]{10}$/', $comnum)) {
    echo json_encode(array('result' => 'fail', 'message' => '사업자번호 형식이 올바르지 않습니다.'));
    exit;
}

$sql = "SELECT
            A.KEDCD,
            A.ENP_NM,
            A.ENP_NM_TRD,
            A.BZC_CD,
            A.ENP_TYP,
            COALESCE(k11.k_code_name, '') as BZC_NM
        FROM company A
        LEFT JOIN kssc_11 k11 ON k11.k_code = A.BZC_CD
        WHERE A.BZNO = '{$comnum}'";
$comp = sql_fetch($sql);

// 개발 시연용 기업은 외부 KODATA 응답을 기다리지 않고 로컬 company row를 사용한다.
$demo_enabled = strtolower(trim((string)macomaex_env('DEMO_RECOMMENDATION_ENABLED', '0')));
if (in_array($demo_enabled, array('1', 'true', 'yes', 'on'), true)
    && in_array($comnum, array('9000000070', '9000000071'), true)
    && !empty($comp['KEDCD'])) {
    $company_name = $comp['ENP_NM_TRD'] ? $comp['ENP_NM_TRD'] : $comp['ENP_NM'];
    $bizno_token = simple_crypt($comnum, 'e');
    macomaex_company_list_ai_grant_entry($comnum, $bizno_token);
    echo json_encode(array(
        'result' => 'success',
        'name' => $company_name,
        'bzc_name' => $comp['BZC_NM'] ? $comp['BZC_NM'] : '기타 업종',
        'enp_type' => $comp['ENP_TYP'] === '1' ? '법인기업' : '기업',
        'enp_type_code' => $comp['ENP_TYP'],
        'bzc_cd' => $comp['BZC_CD'],
        'original_bzc_cd' => $comp['BZC_CD'],
        'source_bzc_missing' => $comp['BZC_CD'] ? 0 : 1,
        'source_bzc_resolved' => 0,
        'source_bzc_resolve_source' => 'demo_company',
        'keyword_scope' => $comp['BZC_CD'] ? 'related' : 'all',
        'fallback_reason' => '',
        'bizno' => $comnum,
        'bizno_token' => $bizno_token,
        'kedcd' => $comp['KEDCD'],
        'source' => 'demo_company',
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

$resolved_company = macomaex_kodata_resolve_company($comnum, array(
    'context' => 'get_company_name',
));
$cominfo_json = !empty($resolved_company['json']) ? $resolved_company['json'] : '';
$fail_code = '';
$fail_message = '';
if (empty($resolved_company['success'])) {
    $fail_code = (string)($resolved_company['error_code'] ?? 'company_not_found');
    $fail_message = (string)($resolved_company['message'] ?? '기업 정보를 찾지 못했습니다.');
}

if (!empty($cominfo_json)) {
    $arr = json_decode($cominfo_json, true);
    $node = array();
    if (!empty($arr['CONTENTS'])) {
        $contents = $arr['CONTENTS'];
        $node = isset($contents['ENPintr0000']) ? $contents['ENPintr0000'] : (isset($contents[0]['ENPintr0000']) ? $contents[0]['ENPintr0000'] : array());
    }

    $company_name = isset($node['enp_nm_trd']) ? $node['enp_nm_trd'] : (isset($node['enp_nm']) ? $node['enp_nm'] : '');
    $kedcd = isset($node['kedcd']) ? $node['kedcd'] : '';
    // KODATA가 성공해도 업종코드를 빈 태그({})로 줄 수 있어 원본/보강값을 분리한다.
    $original_bzc_cd = isset($node['bzc_cd']) ? (is_scalar($node['bzc_cd']) ? trim((string)$node['bzc_cd']) : '') : '';
    $bzc_cd = $original_bzc_cd;
    $bzc_resolved = 0;
    $bzc_resolve_source = $bzc_cd !== '' ? (string)($resolved_company['source'] ?? '') : '';
    $keyword_scope = 'related';
    $fallback_reason = '';
    if ($bzc_cd === '') {
        // 원본 업종코드가 없으면 기존 company 테이블 값을 우선 사용하고, 없을 때만 전체 업종 검색으로 보낸다.
        $company_bzc_row = sql_fetch("SELECT BZC_CD FROM company WHERE BZNO = '{$comnum}' AND BZC_CD<>'' LIMIT 1");
        $company_bzc_cd = isset($company_bzc_row['BZC_CD']) ? strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string)$company_bzc_row['BZC_CD']), 0, 10)) : '';
        if ($company_bzc_cd !== '') {
            $bzc_cd = $company_bzc_cd;
            $bzc_resolved = 1;
            $bzc_resolve_source = 'company';
            $fallback_reason = 'company_bzc_found';
        } else {
            $keyword_scope = 'all';
            $bzc_resolve_source = 'none';
            $fallback_reason = 'missing_bzc_cd';
        }
    }
    $bzc_name = isset($node['bzc_nm']) ? $node['bzc_nm'] : '';
    $enp_typ = isset($node['enp_typ']) ? $node['enp_typ'] : '';

    if (!$bzc_name && $bzc_cd) {
        $bzc_row = sql_fetch("
            SELECT COALESCE(k11.k_code_name, '') AS k_code_name
            FROM (SELECT '{$bzc_cd}' AS k_code) src
            LEFT JOIN kssc_11 k11 ON k11.k_code = src.k_code
        ");
        $bzc_name = isset($bzc_row['k_code_name']) ? $bzc_row['k_code_name'] : '';
    }

    $enp_type_text = '기업';
    if ($enp_typ == '1') {
        $enp_type_text = '법인기업';
    } else if ($enp_typ == '2') {
        $enp_type_text = '개인기업';
    }

    if ($company_name) {
        // 추천 버튼에서 곧바로 목록으로 넘어갈 수 있게 세션,토큰 준비
        $bizno_token = simple_crypt($comnum, 'e');
        macomaex_company_list_ai_grant_entry($comnum, $bizno_token);
        echo json_encode(array(
            'result' => 'success',
            'name' => $company_name,
            'bzc_name' => $bzc_name ? $bzc_name : '기타 업종',
            'enp_type' => $enp_type_text,
            'enp_type_code' => $enp_typ,
            'bzc_cd' => $bzc_cd,
            'original_bzc_cd' => $original_bzc_cd,
            'source_bzc_missing' => $original_bzc_cd === '' ? 1 : 0,
            'source_bzc_resolved' => $bzc_resolved,
            'source_bzc_resolve_source' => $bzc_resolve_source,
            'keyword_scope' => $keyword_scope,
            'fallback_reason' => $fallback_reason,
            'bizno' => $comnum,
            'bizno_token' => $bizno_token,
            'kedcd' => $kedcd,
            'cominfo' => $cominfo_json,
            'source' => (string)($resolved_company['source'] ?? '')
        ), JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// KODATA 조회가 실패한 경우에만 company 테이블을 최종 fallback으로 사용한다.
if ($comp) {
    $company_name = $comp['ENP_NM_TRD'] ? $comp['ENP_NM_TRD'] : $comp['ENP_NM'];
    $bzc_name = $comp['BZC_NM'] ? $comp['BZC_NM'] : '기타 업종';

    $enp_type_text = '기업';
    if ($comp['ENP_TYP'] == '1') {
        $enp_type_text = '법인기업';
    } else if ($comp['ENP_TYP'] == '2') {
        $enp_type_text = '개인기업';
    }

    // fallback 응답이어도 목록 진입 플로우는 동일하게 유지
    $bizno_token = simple_crypt($comnum, 'e');
    macomaex_company_list_ai_grant_entry($comnum, $bizno_token);
    echo json_encode(array(
        'result' => 'success',
        'name' => $company_name,
        'bzc_name' => $bzc_name,
        'enp_type' => $enp_type_text,
        'enp_type_code' => $comp['ENP_TYP'],
        'bzc_cd' => $comp['BZC_CD'],
        'original_bzc_cd' => $comp['BZC_CD'],
        'source_bzc_missing' => $comp['BZC_CD'] ? 0 : 1,
        'source_bzc_resolved' => 0,
        'source_bzc_resolve_source' => $comp['BZC_CD'] ? 'company' : 'none',
        'keyword_scope' => $comp['BZC_CD'] ? 'related' : 'all',
        'fallback_reason' => $comp['BZC_CD'] ? '' : 'missing_bzc_cd',
        'bizno' => $comnum,
        'bizno_token' => $bizno_token,
        'kedcd' => $comp['KEDCD'],
        'source' => 'db'
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($fail_message !== '') {
    echo json_encode(array(
        'result' => 'fail',
        'code' => $fail_code,
        'message' => $fail_message,
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(array('result' => 'fail', 'code' => 'not_found', 'message' => '등록된 기업 정보가 없습니다.'), JSON_UNESCAPED_UNICODE);
?>
