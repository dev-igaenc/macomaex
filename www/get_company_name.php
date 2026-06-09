<?php
include_once('./_common.php');
include_once(G5_PATH.'/extend/user.extend.php');

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
            (SELECT k_code_name FROM kssc WHERE k_code = A.BZC_CD) as BZC_NM
        FROM company A
        WHERE A.BZNO = '{$comnum}'";
$comp = sql_fetch($sql);

$log_chk = sql_fetch("
    select *
    from company_api_log
    where c_bizno='{$comnum}'
      and c_content!=''
      and c_datetime >= date_sub(now(), interval 1 month)
    order by c_datetime desc
    limit 1
");
$cominfo_json = !empty($log_chk['c_content']) ? $log_chk['c_content'] : '';
$fail_code = '';
$fail_message = '';

// 한 달 이내 KODATA 캐시가 없으면 최신 기업정보를 직접 조회한다.
if (empty($cominfo_json)) {
    $TOKEN_URL = "https://218.55.37.214:6059/enpinfo?userid=igaenconl&process=S&bzno={$comnum}&pidagryn=Y&jmno=ig001&format=XML&Length=10000";
    $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)';
    $cookie_nm = './cookie.txt';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $TOKEN_URL);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_nm);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_nm);
    curl_setopt($ch, CURLOPT_USERAGENT, $agent);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $html_ori = curl_exec($ch);
    $curl_error = curl_error($ch);
    $curl_errno = curl_errno($ch);
    curl_close($ch);

    if ($html_ori === false) {
        macomaex_kodata_log('curl_failed', array(
            'context' => 'get_company_name',
            'comnum' => $comnum,
            'curl_errno' => $curl_errno,
            'curl_error' => $curl_error,
        ));
        $fail_code = ($curl_errno === 28) ? 'timeout' : 'curl_error';
        $fail_message = ($curl_errno === 28)
            ? '기업 정보 조회 시간이 초과되었습니다.'
            : '기업 정보 조회 중 통신 오류가 발생했습니다.';
    } else {
        $parsed = macomaex_parse_kodata_xml($html_ori, array(
            'context' => 'get_company_name',
            'comnum' => $comnum,
        ));

        if (!empty($parsed['success'])) {
            $xml = $parsed['xml'];
            if ((string)$xml->HEADER->errcd === '00') {
                $json = $parsed['json'];
                $kedcd = '';
                foreach ($xml->CONTENTS as $row) {
                    $kedcd = (string)$row->ENPintr0000->kedcd;
                }
                sql_query("insert company_api_log set c_bizno='{$comnum}', c_kedcd='{$kedcd}', c_content='" . addslashes($json) . "', c_datetime=now(), c_ip='{$_SERVER['REMOTE_ADDR']}'");
                $cominfo_json = $json;
            }
        }
    }
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
    $bzc_cd = isset($node['bzc_cd']) ? $node['bzc_cd'] : '';
    $bzc_name = isset($node['bzc_nm']) ? $node['bzc_nm'] : '';
    $enp_typ = isset($node['enp_typ']) ? $node['enp_typ'] : '';

    if (!$bzc_name && $bzc_cd) {
        $bzc_row = sql_fetch("SELECT k_code_name FROM kssc WHERE k_code = '{$bzc_cd}'");
        $bzc_name = isset($bzc_row['k_code_name']) ? $bzc_row['k_code_name'] : '';
    }

    $enp_type_text = '기업';
    if ($enp_typ == '1') {
        $enp_type_text = '법인기업';
    } else if ($enp_typ == '2') {
        $enp_type_text = '개인기업';
    }

    if ($company_name) {
        echo json_encode(array(
            'result' => 'success',
            'name' => $company_name,
            'bzc_name' => $bzc_name ? $bzc_name : '기타 업종',
            'enp_type' => $enp_type_text,
            'enp_type_code' => $enp_typ,
            'bzc_cd' => $bzc_cd,
            'bizno' => $comnum,
            'bizno_token' => simple_crypt($comnum, 'e'),
            'kedcd' => $kedcd,
            'cominfo' => $cominfo_json,
            'source' => !empty($log_chk['c_content']) ? 'kodata_cache_1m' : 'kodata_live'
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

    echo json_encode(array(
        'result' => 'success',
        'name' => $company_name,
        'bzc_name' => $bzc_name,
        'enp_type' => $enp_type_text,
        'enp_type_code' => $comp['ENP_TYP'],
        'bzc_cd' => $comp['BZC_CD'],
        'bizno' => $comnum,
        'bizno_token' => simple_crypt($comnum, 'e'),
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
