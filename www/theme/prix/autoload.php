<?php
spl_autoload_register('BootpayAutoload');



use Bootpay\Rest\BootpayApi;





$bootpay = BootpayApi::setConfig(

    '[[62eb5222cf9f6d001c7c189b]]',

    '[[bwTqyfRMwhHdDhJ+uRbQG5i4T/0nw7yLegEbKIDHZqo=]]'

);



$response = $bootpay->requestAccessToken();





// Token이 발행되면 그 이후에 verify 처리 한다.

if ($response->status === 200) {



$token = $response->data->token; //ACCES TOKEN KEY



}



//

//

//





// Token이 발행되면 그 이후에 verify 처리 한다.

if ($response->status === 200) {

$result = $bootpay->verify($receiptId);

//var_dump($result);

// 원래 주문했던 금액이 일치하는가?

// 그리고 결제 상태가 완료 상태인가?

if ($result->data->price === $order_pcost && $result->data->status === 1) {

// TODO: 이곳이 상품 지급 혹은 결제 완료 처리를 하는 로직으로 사용하면 됩니다.





}

}


?>