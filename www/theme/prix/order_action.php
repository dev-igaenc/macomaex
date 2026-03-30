<?php

/*
 * Access Token 요청 예제입니다.
 */
require_once('./autoload.php');
spl_autoload_register('BootpayAutoload');

use Bootpay\Rest\BootpayApi;

$bootpay = BootpayApi::setConfig(
    '62eb5222cf9f6d001c7c189b',
    'bwTqyfRMwhHdDhJ+uRbQG5i4T/0nw7yLegEbKIDHZqo='
);

$response = $bootpay->requestAccessToken();

if ($response->status === 200) {
    print $response->data->token . "\n";
    print $response->data->server_time . "\n";
    print $response->data->expired_at . "\n";
} else {
    var_dump($response);
}

?>