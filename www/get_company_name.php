<?php
include_once('./_common.php');

$comnum = isset($_POST['comnum']) ? trim($_POST['comnum']) : '';
$comnum = str_replace("-", "", $comnum);

if (!$comnum) {
    echo json_encode(array('result' => 'fail', 'message' => '사업자번호가 없습니다.'));
    exit;
}

$sql = "SELECT
            A.ENP_NM,
            A.ENP_NM_TRD,
            A.BZC_CD,
            A.ENP_TYP,
            (SELECT k_code_name FROM kssc WHERE k_code = A.BZC_CD) as BZC_NM
        FROM company A
        WHERE A.BZNO = '{$comnum}'";
$comp = sql_fetch($sql);

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
        'source' => 'db'
    ), JSON_UNESCAPED_UNICODE);
} else {
    $log_chk = sql_fetch("
        SELECT * FROM company_api_log
        WHERE c_bizno = '{$comnum}'
          AND DATE(c_datetime) = '" . date('Y-m-d') . "'
    ");

    $cominfo_json = '';

    if (!empty($log_chk['c_content'])) {
        $cominfo_json = $log_chk['c_content'];
    } else {
        $TOKEN_URL = "https://218.55.37.214:6059/enpinfo?userid=igaenconl&process=S&bzno={$comnum}&pidagryn=Y&jmno=ig001&format=XML&Length=10000";
        $cookie_nm = './cookie.txt';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $TOKEN_URL);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSLVERSION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_nm);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_nm);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $html_ori = curl_exec($ch);
        curl_close($ch);

        if ($html_ori) {
            $xml = @simplexml_load_string($html_ori, null, LIBXML_NOCDATA);
            if ($xml && (string)$xml->HEADER->errcd === '00') {
                $json = json_encode($xml, JSON_UNESCAPED_UNICODE);
                $kedcd = '';
                foreach ($xml->CONTENTS as $row) {
                    $kedcd = (string)$row->ENPintr0000->kedcd;
                }
                sql_query("
                    INSERT INTO company_api_log
                    SET c_bizno    = '{$comnum}',
                        c_kedcd    = '{$kedcd}',
                        c_content  = '" . addslashes($json) . "',
                        c_datetime = NOW(),
                        c_ip       = '{$_SERVER['REMOTE_ADDR']}'
                ");
                $cominfo_json = $json;
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
                'cominfo' => $cominfo_json,
                'source' => 'api'
            ), JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    echo json_encode(array('result' => 'fail', 'message' => '등록된 기업 정보가 없습니다.'), JSON_UNESCAPED_UNICODE);
}
?>
