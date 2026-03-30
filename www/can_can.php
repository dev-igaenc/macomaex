<?
include_once('./_common.php');
include_once(G5_LIB_PATH.'/connect.lib.php');
include_once(G5_LIB_PATH.'/outlogin.lib.php');


								//무료가 아닐경우 실제 결제 취소 처리
								//step1. 요청을 위한 파라미터 설정
								$pinfo=sql_fetch("select * from g5_membership_paybill where order_no='{$chk['order_no']}'");
								$key = _key;
								$mid = _mid;
								$type = "refund";
								$timestamp = date("YmdHis");
								$clientIp = _clientIp;
								
								$postdata = array();
								$postdata["mid"] = $mid;
								$postdata["type"] = $type;
								$postdata["timestamp"] = $timestamp;
								$postdata["clientIp"] = $clientIp;
								//// Data 상세
								$detail = array();
								$detail["tid"] = "INIAPICARDCBBmacomaB20250221153214862176";
								$detail["msg"] = "사용자 취소";

								$postdata["data"] = $detail;
								$details = str_replace('\\/', '/', json_encode($detail, JSON_UNESCAPED_UNICODE));
								//// Hash Encryption
								$plainTxt = $key.$mid.$type.$timestamp.$details;
								$hashData = hash("sha512", $plainTxt);
								$postdata["hashData"] = $hashData;
								$post_data = json_encode($postdata, JSON_UNESCAPED_UNICODE);
								//step2. 요청전문 POST 전송
								$url = "https://iniapi.inicis.com/v2/pg/refund";
								$ch = curl_init();
								curl_setopt($ch, CURLOPT_URL, $url);
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
								curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
								curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
								curl_setopt($ch, CURLOPT_POST, 1);
								curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=utf-8'));
								curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
								 
								$response = curl_exec($ch);
								$pay_result=json_decode($response);
								curl_close($ch);
								print_r($response);
?>