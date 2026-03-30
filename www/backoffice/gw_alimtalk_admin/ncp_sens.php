<?php
class NCP_SENS
{
    public function __construct()
    {

    }

    // 인증 시그니쳐 생성 : API 호출시 매번 다른 인증 값 필요
    public function get_signature($timestamp, $method, $url)
    {
        $space		= " ";
        $accessKey  = $this->subAccessKey;
        $secretKey	= $this->subSecretKey;
        $sign		= $method.$space.$url.PHP_EOL.$timestamp.PHP_EOL.$accessKey;
        $signature  = base64_encode( hash_hmac( "sha256", $sign, $secretKey, true) );

        return $signature;
    }

    // 타임스탬프 : 인증 시그니쳐 생성시 사용
    public function get_timestamp()
    {
        /* 기존 코드 오류
        return round( microtime( true ) * 1000 );
        */

        list($microtime,$timestamp) = explode(' ',microtime());
        $time = $timestamp.substr($microtime, 2, 3);

        return $time;
    }

    // 인증 요청 헤더 : 인증시 사용
    public function get_header($method, $url)
    {
        $timestamp = $this->get_timestamp();

        $header = array(
            'Content-Type: application/json; charset=utf-8',
            'x-ncp-apigw-timestamp: '.$timestamp,
            'x-ncp-iam-access-key: '.$this->subAccessKey,
            'x-ncp-apigw-signature-v2: '.$this->get_signature($timestamp, $method, $url)
        );

        return $header;
    }

    // GET 방식으로 요청 : API 요청시 사용
    public function get($url, $data = array())
    {
        if ($data)	$url = $url.'?'.http_build_query($data, '', '&');

        $opts = array(
            CURLOPT_URL => $this->host . $url,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => $this->get_header('GET', $url)
        );

        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $res = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $success_code = 200;

        if($status_code == $success_code) {
            return $res;
        } else {
            return false;
        }
    }

    // POST 방식으로 요청 : API 요청시 사용
    public function post($url, $data = array())
    {
        $data = json_encode($data);

        $opts = array(
            CURLOPT_URL => $this->host . $url,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => $this->get_header('POST', $url)
        );

        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $res = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        $success_code = 202;

        if($status_code == $success_code) {
            return $res;
        } else {
            return false;
        }
    }

    // DELETE 방식으로 요청 : API 요청시 사용
    public function delete($url)
    {
        $opts = array(
            CURLOPT_URL => $this->host.$url,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => $this->get_header('DELETE', $url)
        );

        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $res = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $success_code = 204;

        if($status_code == $success_code) {
            $res['status_code'] = $status_code;
            $res = json_encode($res);
            return $res;
        } else {
            return false;
        }
    }

    // 템플릿 조회 (신규 추가 2021-04-09)
    public function get_templates($template_code='', $template_name='')
    {
        $url	= $this->biz_msg_alimtalk_url."/templates";
        $data	= array('channelId'=>$this->plusFriendId);

        if ($template_code) {
            $data['templateCode'] = $template_code;
        }

        if ($template_name) {
            $data['templateName'] = $template_name;
        }

        $res	= $this->get($url, $data);
        $res	= json_decode($res, true);

        return $res;
    }

    // 예약 메시지 취소시 사용
    public function reserved_canceled($data = array())
    {
        global $g5;

        $url = $this->biz_msg_alimtalk_url."/reservations/";

        $count = count($data['chk']);

        for ($i=0; $i<$count; $i++)
        {
            // 실제 번호를 넘김
            $k = $data['chk'][$i];

            // 예약 내역정보
            $sql = " select * from {$g5['nc_alimtalk_history']} where nc_reserved = '1' and nc_message_id = '' and nc_id = '{$data['nc_id'][$k]}' ";
            $row = sql_fetch($sql);

            if(!$row['nc_id'] || !$row['nc_request_id'])
                continue;

            // 예약 발송 취소
            $res = $this->delete($url, $row['nc_request_id']);
            $res = json_decode($res, true);
            if ( $res['status_code'] == '204' ) {
                $sql = " update {$g5['nc_alimtalk_history']} set nc_reserved = '3' where nc_id = '{$row['nc_id']}' " ;
                sql_query($sql);
            }
        }

        return true;
    }

    /* 자동으로 대체문자 여부를 감지하여 내용을 넣어주기 때문에 사용중단.
    // Failover SMS 서비스 사용여부 (대체문자)
    public function set_failover_config()
    {
        $failover = array();

        // 카카오톡 채널 정보를 가져온다.
        $res = $this->get_channels();

        $res = $res[0];
        if ($res['useSmsFailover'] == 1) {
            $failover['type'] = "";
            $failover['from'] = "";
            $failover['subject'] = "";
            $failover['content'] = "";
        }

        return $failover;
    }
    */
}
