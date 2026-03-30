<?php
class NCP_SENS_AT extends NCP_SENS
{
	protected $host; // SENS API 요청 URL
    protected $version; // SENS API 버전 V2
	protected $plusFriendId; // 카카오톡 채널명 [ @를 포함한 플러스친구 아이디 ]
    protected $biz_msg_serviceId;
    protected $subAccessKey;
    protected $subSecretKey;
    protected $biz_msg_alimtalk_url;

	public $templateCode; // 템플릿 코드
	public $countryCode; // 수신자 국가번호
	public $from; // 발신번호
	public $to; // 수신자번호
	public $messages		= array(); // 메시지 정보 [ 최대 100개 동시 발송 ]
	public $buttons			= array(); // 버튼 내용
	public $failover_config	= array(); // Failover 설정
	public $content; // 메시지 내용

	public $reserveTime; // 예약 일시 [ 메시지 발송 예약 일시 (yyyy-MM-dd HH:mm) ]
	public $reserveTimeZone = 'Asia/Seoul'; // 예약 일시 타임존 [ (기본: Asia/Seoul) * TZ database name 값 사용 ]
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
    public $user_link;

    public function __construct()
    {
        $this->host					    = NCP_SENS_HOST;
        $this->version				    = NCP_SENS_VERSION;
        $this->plusFriendId			    = NCP_KAKAO_PLUS_FRIEND_ID;
        $this->biz_msg_serviceId	    = NCP_BIZ_MESSAGE_SERVICE_ID;
        $this->subAccessKey			    = NCP_SUB_ACCESS_KEY_ID;
        $this->subSecretKey			    = NCP_SUB_SECRET_KEY;
        $this->countryCode			    = '82';
        $this->biz_msg_alimtalk_url	    = "/alimtalk/".$this->version."/services/".urlencode($this->biz_msg_serviceId);
    }

    // 카카오톡 채널 조회 (신규 추가 2021-04-09)
    public function get_channels()
    {
        $url	= $this->biz_msg_alimtalk_url."/channels";
        $res	= $this->get($url);
        $res	= json_decode($res, true);

        return $res;
    }

    // 발송 요청 조회
    public function get_request($request_id)
    {
        $url	= $this->biz_msg_alimtalk_url."/messages";
        $data	= array('requestId'=>$request_id);
        $res	= $this->get($url, $data);

        return $res;
    }

    // 발송 결과 조회
    public function get_result($message_id)
    {
        $url	= $this->biz_msg_alimtalk_url."/messages/".$message_id;
        $res	= $this->get($url, '');

        return $res;
    }

    // 예약 메시지 상태 조회 (신규 추가 2021-04-29)
    public function get_reserved_status($request_id)
    {
        $url	= $this->biz_msg_alimtalk_url."/reservations/".$request_id."/reserve-status";
        $res	= $this->get($url, '');

        return $res;
    }

    // 예약 메시지 동기화
    public function reserved_sync()
    {
        global $g5;

        $url	= $this->biz_msg_alimtalk_url."/messages";

        // 전송내역 중 예약중인 내역만 가져옴
        $sql = " select * from {$g5['nc_alimtalk_history']} where nc_message_id = '' and nc_reserved = 1 and nc_reserved_datetime <= '".G5_TIME_YMDHIS."' " ;
        $result = sql_query($sql);

        for ($i=0; $row=sql_fetch_array($result); $i++) {
            $data	= array('requestId'=>$row['nc_request_id']);
            $res	= $this->get($url, $data);
            $res	= json_decode($res, true);

            $return = $res['messages'][0];
            $return['statusName'] = $res['statusName'];

            // 예약 메시지의 경우 messageId 여부를 판단 후 update
            if ( $row['nc_reserved'] == 1 && !$row['nc_message_id'] && $return['messageId'] ) {
                $sql = " update {$g5['nc_alimtalk_history']} set nc_reserved = '2', nc_message_id = '{$return['messageId']}' where nc_id = '{$row['nc_id']}' " ;
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
            $sql = " select * from {$g5['nc_alimtalk_history']} where nc_reserved = '1' and nc_message_id = '' and nc_id = '{$data['nc_id'][$k]}' ";
            $row = sql_fetch($sql);

            if(!$row['nc_id'] || !$row['nc_request_id'])
                continue;

            // 예약 발송 취소
            $url = $this->biz_msg_alimtalk_url."/reservations/".$row['nc_request_id'];
            $res = $this->delete($url);
            $res = json_decode($res, true);
            if ( $res['status_code'] == '204' ) {
                $sql = " update {$g5['nc_alimtalk_history']} set nc_reserved = '3' where nc_id = '{$row['nc_id']}' " ;
                sql_query($sql);
            }
        }

        return true;
    }

    // 메시지 내용 세팅
    public function set_messages()
    {
        if (!$this->templateCode) {
            return false;
        }

        // 단일 템플릿을 가져온다.
        $res = $this->get_templates($this->templateCode);
        $res = $res[0];

        $this->buttons = $this->set_buttons($res['buttons']); // 해당 버튼을 세팅

        // #{필드1}, #{필드2}, #{필드3}, #{필드4}, #{필드5}, #{필드6}, #{필드7}, #{필드8}, #{필드9}, #{필드10}
        $content = $res['content'];

        $content = str_replace('#{필드1}',	$this->user_field1,		$content);
        $content = str_replace('#{필드2}',	$this->user_field2,		$content);
        $content = str_replace('#{필드3}',	$this->user_field3,		$content);
        $content = str_replace('#{필드4}',	$this->user_field4,		$content);
        $content = str_replace('#{필드5}',	$this->user_field5,		$content);
        $content = str_replace('#{필드6}',	$this->user_field6,		$content);
        $content = str_replace('#{필드7}',	$this->user_field7,		$content);
        $content = str_replace('#{필드8}',	$this->user_field8,		$content);
        $content = str_replace('#{필드9}',	$this->user_field9,		$content);
        $content = str_replace('#{필드10}',  $this->user_field10,	$content);

        //$content = addslashes($content); 쌍따옴표의 경우 템플릿 불일치로 우선 제거.

        return $content;
    }

    // 메시지 버튼 세팅
    public function set_buttons($data)
    {
        $buttons = array();

        for ($i=0; $i<count($data); $i++) {

            if (!$data[$i])
                continue;

            $buttons[$i]['type']      = $data[$i]['type'];
            $buttons[$i]['name']      = $data[$i]['name'];
            switch ($data[$i]['type']) {
                case 'WL':
                    // 모바일 링크
                    $mobile_link = $data[$i]['linkMobile'];
                    $mobile_link = str_replace('#{링크}', $this->user_link, $mobile_link);

                    // PC 링크
                    $pc_link = $data[$i]['linkPc'];
                    $pc_link = str_replace('#{링크}', $this->user_link, $pc_link);

                    $buttons[$i]['linkMobile'] = $mobile_link;
                    $buttons[$i]['linkPc'] = $pc_link;
                    break;
                case 'AL':
                    $buttons[$i]['schemeAndroid'] = $data[$i]['schemeAndroid'];
                    $buttons[$i]['schemeIos'] = $data[$i]['schemeIos'];
                    break;
            }

        }

        return $buttons;
    }

    // 메시지 발송
    public function send()
    {
        $this->to = preg_replace("/[^0-9]*/s", "", $this->to);

        $url				= $this->biz_msg_alimtalk_url.'/messages';
        $this->content		= $this->set_messages();

        if (!$this->content) {
            return false;
        }

        // 발송 데이터 양식
        $this->messages = array(
            'plusFriendId'	=> $this->plusFriendId,
            'templateCode'  => $this->templateCode,
            'messages' => array(
                array(
                    'countryCode'	=> $this->countryCode,
                    'to'			=> $this->to,
                    'content'		=> $this->content,
                    'buttons'		=> $this->buttons,
                )
            ),
        );

        // 예약 발송 여부에 따라서 배열을 추가
        if ($this->reserveTime) {
            $this->messages['reserveTime']      = $this->reserveTime;
            $this->messages['reserveTimeZone']  = $this->reserveTimeZone;
        }

        $data = $this->messages;
        //print_r2($data);exit;
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
        $url = $this->biz_msg_alimtalk_url.'/messages';

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
            $sql = "insert into {$g5['nc_alimtalk_history']}
					set nc_to					= '".$this->to."',
						nc_template_code		= '".$this->templateCode."',
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
                $sql = "insert into {$g5['nc_alimtalk_history']}
						set nc_to				= '{$req['messages'][$key]['to']}',
							nc_template_code	= '{$req['messages'][$key]['templateCode']}',
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
