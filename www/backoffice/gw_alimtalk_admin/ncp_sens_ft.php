<?php
class NCP_SENS_FT extends NCP_SENS
{
    protected $host; // SENS API 요청 URL
    protected $version; // SENS API 버전 V2
    protected $plusFriendId; // 카카오톡 채널명 [ @를 포함한 플러스친구 아이디 ]
    protected $biz_msg_serviceId;
    protected $subAccessKey;
    protected $subSecretKey;
    protected $biz_msg_friendtalk_url;

    public $countryCode; // 수신자 국가번호
    public $from; // 발신번호
    public $to; // 수신자번호
    public $messages		= array(); // 메시지 정보 [ 최대 100개 동시 발송 ]
    public $buttons			= array(); // 버튼 내용
    public $failover_config	= array(); // Failover 설정
    public $content; // 메시지 내용

    public $reserveTime; // 예약 일시 [ 메시지 발송 예약 일시 (yyyy-MM-dd HH:mm) ]
    public $reserveTimeZone; // 예약 일시 타임존 [ (기본: Asia/Seoul) * TZ database name 값 사용 ]
    public $scheduleCode; // 스케줄 코드 [ 등록하려는 스케줄 코드 ]

    public $user_field1; // 여분필드 1~10
    public $user_field2;
    public $user_field3;
    public $user_field4;
    public $user_field5;
    public $user_field6;
    public $user_field7;
    public $user_field8;
    public $user_field9;
    public $user_field10;

    public function __construct()
    {
        $this->host					    = NCP_SENS_HOST;
        $this->version				    = NCP_SENS_VERSION;
        $this->plusFriendId			    = NCP_KAKAO_PLUS_FRIEND_ID;
        $this->biz_msg_serviceId	    = NCP_BIZ_MESSAGE_SERVICE_ID;
        $this->subAccessKey			    = NCP_SUB_ACCESS_KEY_ID;
        $this->subSecretKey			    = NCP_SUB_SECRET_KEY;
        $this->countryCode			    = '82';
        $this->biz_msg_friendtalk_url	= "/friendtalk/".$this->version."/services/".urlencode($this->biz_msg_serviceId);
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

    public function get_header_file($method, $url, $content_type=false)
    {
        $timestamp = $this->get_timestamp();

        $header = array(
            'x-ncp-apigw-timestamp: '.$timestamp,
            'x-ncp-iam-access-key: '.$this->subAccessKey,
            'x-ncp-apigw-signature-v2: '.$this->get_signature($timestamp, $method, $url)
        );

        if ($content_type == true) {
            $header[] ='Content-Type: multipart/form-data';
        }

        return $header;
    }

	/**
	* For safe multipart POST request for PHP5.3 ~ PHP 5.4.
	*
	* @param resource $ch cURL resource
	* @param array $assoc "name => value"
	* @param array $files "name => path"
	* @return bool
	*/
	function curl_custom_postfields($ch, array $assoc = array(), array $files = array()) {

		// invalid characters for "name" and "filename"
		static $disallow = array("\0", "\"", "\r", "\n");

		// build normal parameters
		foreach ($assoc as $k => $v) {
			$k = str_replace($disallow, "_", $k);
			$body[] = implode("\r\n", array(
				"Content-Disposition: form-data; name=\"{$k}\"",
				"",
				filter_var($v),
			));
		}

		// build file parameters
		foreach ($files as $k => $f) {
			$v = $f['tmp_name'];
			switch (true) {
				case false === $v = realpath(filter_var($v)):
				case !is_file($v):
				case !is_readable($v):
					//continue; // or return false, throw new InvalidArgumentException
					break;
			}
			$data = file_get_contents($v);
			$mimetype = mime_content_type($v);
			//$v = call_user_func("end", explode(DIRECTORY_SEPARATOR, $f['name'])); // 2021-10-26 수정
			$v = basename($f['name']);
			$k = str_replace($disallow, "_", $k);
			$v = str_replace($disallow, "_", $v);
			$body[] = implode("\r\n", array(
				"Content-Disposition: form-data; name=\"{$k}\"; filename=\"{$v}\"",
				sprintf("Content-Type: %s", $mimetype),
				"",
				$data,
			));
		}

		// generate safe boundary
		do {
			$boundary = "---------------------" . md5(mt_rand() . microtime());
		} while (preg_grep("/{$boundary}/", $body));

		// add boundary for each parameters
		array_walk($body, function (&$part) use ($boundary) {
			$part = "--{$boundary}\r\n{$part}";
		});

		// add final boundary
		$body[] = "--{$boundary}--";
		$body[] = "";

		// set options
		return array(
			CURLOPT_POSTFIELDS => implode("\r\n", $body),
			CURLOPT_HTTPHEADER => array(
				"Content-Type: multipart/form-data; boundary={$boundary}", // change Content-Type
			),
		);
	}

    // POST 방식으로 요청 : API 요청시 사용
    public function post_file($url, $data = array(), $files = array())
    {
        global $g5;

		$headers = $this->get_header_file('POST', $url);

        $ch = curl_init();

		$custom_opts = $this->curl_custom_postfields($ch, $data, $files);

        $opts = array(
            CURLOPT_URL => $this->host . $url,
			CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POSTFIELDS => $custom_opts[CURLOPT_POSTFIELDS],
			CURLOPT_HTTPHEADER => array_merge($headers, $custom_opts[CURLOPT_HTTPHEADER]),
		);

        curl_setopt_array($ch, $opts);
        $res = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        $res = json_decode($res, true);

		if (!$res['isWide']) {
			$res['isWide'] = 0;
		}

        if($status_code == 200) {
            $sql = " insert into {$g5['nc_friendtalk_image']}
						set nc_plus_friend_id	= '{$this->plusFriendId}',
							nc_image_id		    = '{$res['imageId']}',
							nc_image_name		= '{$res['imageName']}',
							nc_image_url		= '{$res['imageUrl']}',
							nc_image_is_wide	= '{$res['isWide']}',
							nc_datetime			= '{$res['createTime']}' ";
            sql_query($sql);
        }

        return array_merge($res, array(
            'code' =>  $status_code,
            'error' => $error,
        ));
        //return $res;
    }

    // DELETE 방식으로 요청 : API 요청시 사용
    public function delete_file($url)
    {
        $opts = array(
            CURLOPT_URL => $this->host.$url,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => $this->get_header_file('DELETE', $url, true)
        );

        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $status_code;
    }

    // 카카오톡 채널 조회 (신규 추가 2021-04-09)
    public function get_channels()
    {
        $url	= $this->biz_msg_friendtalk_url."/channels";
        $res	= $this->get($url);
        $res	= json_decode($res, true);

        return $res;
    }

    // 친구톡 이미지 업로드 (신규 추가 2021-04-27)
    public function images_upload($file, $wide=false)
    {
        $file_name = $file['name'];

        $url	= $this->biz_msg_friendtalk_url."/images";
        $data	= array(
            'plusFriendId'  =>  $this->plusFriendId,
            'imageName'     =>  $file_name,
            'isWide'        =>  $wide,
        );

		$files = array(
            'imageFile'     =>  $file,
		);

        $res	= $this->post_file($url, $data, $files);

        return $res;
    }

    // 친구톡 이미지 조회 (신규 추가 2021-04-27)
    public function get_image($image_id)
    {
        $url	= $this->biz_msg_friendtalk_url."/images/".$image_id;
        $res	= $this->get($url);
        $res	= json_decode($res, true);

        return $res;
    }

    // 친구톡 이미지 삭제 (신규 추가 2021-04-27)
    public function delete_image($image_id)
    {
        global $g5;

        $url	     = $this->biz_msg_friendtalk_url."/images/".$image_id;
        $status_code = $this->delete_file($url);

        if ($status_code == 204) {
            $sql = " delete from {$g5['nc_friendtalk_image']} where nc_image_id = '{$image_id}' ";
            sql_query($sql);

            return true;
        } else {
            return false;
        }
    }

    // 발송 요청 조회
    public function get_request($request_id)
    {
        $url	= $this->biz_msg_friendtalk_url."/messages";
        $data	= array('requestId'=>$request_id);
        $res	= $this->get($url, $data);

        return $res;
    }

    // 발송 결과 조회
    public function get_result($message_id)
    {
        $url	= $this->biz_msg_friendtalk_url."/messages/".$message_id;
        $res	= $this->get($url, '');

        return $res;
    }


    // 예약 메시지 동기화
    public function reserved_sync()
    {
        global $g5;

        $url	= $this->biz_msg_friendtalk_url."/messages";

        // 전송내역 중 예약중인 내역만 가져옴
        $sql = " select * from {$g5['nc_friendtalk_history']} where nc_message_id = '' and nc_reserved = 1 and nc_reserved_datetime <= '".G5_TIME_YMDHIS."' " ;
        $result = sql_query($sql);

        for ($i=0; $row=sql_fetch_array($result); $i++) {
            $data	= array('requestId'=>$row['nc_request_id']);
            $res	= $this->get($url, $data);
            $res	= json_decode($res, true);

            $return = $res['messages'][0];
            $return['statusName'] = $res['statusName'];

            // 예약 메시지의 경우 messageId 여부를 판단 후 update
            if ( $row['nc_reserved'] == 1 && !$row['nc_message_id'] && $return['messageId'] ) {
                $sql = " update {$g5['nc_friendtalk_history']} set nc_reserved = '2', nc_message_id = '{$return['messageId']}' where nc_id = '{$row['nc_id']}' " ;
                sql_query($sql);
            }
        }

        return true;
    }

    // 예약 메시지 취소시 사용
    public function reserved_canceled($data = array())
    {
        global $g5;

        $count = count($data['chk']);

        for ($i=0; $i<$count; $i++)
        {
            // 실제 번호를 넘김
            $k = $data['chk'][$i];

            // 예약 내역정보
            $sql = " select * from {$g5['nc_friendtalk_history']} where nc_reserved = '1' and nc_message_id = '' and nc_id = '{$data['nc_id'][$k]}' ";
            $row = sql_fetch($sql);

            if(!$row['nc_id'] || !$row['nc_request_id'])
                continue;

            // 예약 발송 취소
            $url = $this->biz_msg_friendtalk_url."/reservations/".$row['nc_request_id'];
            $res = $this->delete($url);
            $res = json_decode($res, true);
            if ( $res['status_code'] == '204' ) {
                $sql = " update {$g5['nc_friendtalk_history']} set nc_reserved = '3' where nc_id = '{$row['nc_id']}' " ;
                sql_query($sql);
            }
        }

        return true;
    }

    // 메시지 내용 세팅
    public function set_messages()
    {

    }

    // 메시지 버튼 세팅
    public function set_buttons($data)
    {

    }

    // 메시지 발송
    public function send()
    {
        $this->to = preg_replace("/[^0-9]*/s", "", $this->to);

        $url				= $this->biz_msg_friendtalk_url.'/messages';
        $this->content		= 'testest';

        if (!$this->content) {
            return false;
        }

        // 발송 데이터 양식
		/*
        $this->messages = array(
            'plusFriendId'	=> $this->plusFriendId,
            'messages' => array(
                array(
                    'isAd'	        => 'true',
                    'countryCode'	=> $this->countryCode,
                    'to'			=> $this->to,
                    'content'		=> $this->content,
                    'buttons'		=> array(array('type' => 'WL', 'name' => '버튼', 'linkMobile' => 'http://gnuwiz.com', 'linkPc' => 'http://gnuwiz.com')),
                )
            ),
        );
		*/
        $this->messages = array(
            'plusFriendId'	=> $this->plusFriendId,
            'messages' => array(
                array(
                    'isAd'	        => 'true',
                    'countryCode'	=> $this->countryCode,
                    'to'			=> $this->to,
                    'content'		=> $this->content,
                )
            ),
        );

        // 대체 문자 발송 여부 확인
        /*
        $this->failover_config = $this->set_failover_config();
        if ($this->failover_config) {
            $this->messages['messages'][0]['failoverConfig'] = $this->failover_config;
        }
        */

        // 예약 발송 여부에 따라서 배열을 추가
        if ($this->reserveTime) {
            $this->messages['reserveTime']      = $this->reserveTime;
            $this->messages['reserveTimeZone']  = 'Asia/Seoul';
        }

        $data = $this->messages;

        $res = $this->post($url, $data);
        $res = json_decode($res, true);

        // 만약, 서버에서 전달받은 상태가 성공이라면
        if( $res['requestId'] ) {
            $this->write_history($res); // 발송 내역 저장
        }

        return $res;
    }

    // 멀티 메시지 발송 (신규 추가 21-04-29)
    // 템플릿 발송시 필요한 모든 내용들은 미리 생성 후 발송만 하는 메서드
    public function multi_send($data)
    {
        $url = $this->biz_msg_friendtalk_url.'/messages';

        // 발송 데이터 양식
        $this->messages = $data;

        // 히스토리 테이블에 저장하기 위해 변수로 선언해줌.
        $this->templateCode = $this->messages['templateCode'];

        // 예약 발송 여부에 따라서 배열을 추가
        if ($this->messages['reserveTime']) {
            $this->messages['reserveTimeZone']  = $this->reserveTimeZone;

            // 히스토리 테이블에 저장하기 위해 변수로 선언해줌.
            $this->reserveTime = $this->messages['reserveTime'];
        }

        $data = $this->messages;
        //print_r2($data);exit;
        $res = $this->post($url, $data);
        $res = json_decode($res, true);
        //print_r2($res);exit;
        // 만약, 서버에서 전달받은 상태가 성공이라면
        if( $res['requestId'] ) {
            $this->write_history($res); // 발송 내역 저장
        }

        return $res;
    }

    // 발송 내역 기록 남김
    public function write_history($res)
    {
        global $g5;

        $nc_datetime = date('Y-m-d H:i:s', strtotime($res['requestTime'])); // 요청일시 가공

        // 예약 메시지의 경우 아직 messages 배열이 없어서 따로 insert 함
        if ( $this->reserveTime ) {
            $sql = "insert into {$g5['nc_friendtalk_history']}
					set nc_to					= '".$this->to."',
						nc_message_id			= '',
						nc_request_id			= '{$res['requestId']}',
						nc_reserved				= '1',
						nc_reserved_datetime	= '".$this->reserveTime."',
						nc_datetime				= '".$nc_datetime."' ";
            sql_query($sql);
        } else {
            $req = $this->get_request($res['requestId']);
            $req = json_decode($req, true);

            foreach ( $req['messages'] as $key => $val ) {
                $sql = "insert into {$g5['nc_friendtalk_history']}
						set nc_to				= '{$req['messages'][$key]['to']}',
							nc_message_id		= '{$req['messages'][$key]['messageId']}',
							nc_request_id		= '{$res['requestId']}',
							nc_datetime			= '".$nc_datetime."' ";
                sql_query($sql);

                // 네이버 수신을 받고 난 후 처리
                do {
                    $result = $this->get_result($req['messages'][$key]['messageId']);
                    $result = json_decode($result, true);

                    // if(isset($result['messageStatusCode'])) { 번호가 000-0000-0000같은 특이 번호 발송 시 무한루프로 인해 로직 변경
                    if(isset($result['requestStatusName'])) {
                        break;
                    }
                }
                while(1);
            }
        }

        return;
    }

}
