<?php
if($_POST['pay_status']=="Successful"){
    $merTxnId= $_POST['mer_txnid'];
    
}

$store_id = "aamarpaytest";  // You have to use your Store ID / MerchantID here
$signature_key="dbb74894e82415a2f7ff0ec3a97e4183"; // Your have to use your signature key here ,it will be provided by aamarPay
$url = "https://sandbox.aamarpay.com/api/v1/trxcheck/request.php?request_id=$merTxnId&store_id=$store_id&signature_key=$signature_key&type=json"; //sandbox
//$url = "https://secure.aamarpay.com/api/v1/trxcheck/request.php?request_id=$merTxnId&store_id=$store_id&signature_key=$signature_key&type=json"; //live url
        
$curl_handle=curl_init();
curl_setopt($curl_handle,CURLOPT_URL,$url);

curl_setopt($curl_handle, CURLOPT_VERBOSE, true);
curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
$buffer = curl_exec($curl_handle);
curl_close($curl_handle);
$a = (array)json_decode($buffer);
echo "<pre>";
print_r($a);
echo "</pre>";

?>