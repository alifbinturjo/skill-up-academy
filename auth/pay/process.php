<?php
session_start();
if(!isset($_POST['cus_name'])){
    echo "Direct access restricted";
    exit();
}

$fullName=$_POST['cus_name'];
$email=$_POST['cus_email'];
$phone_number=$_POST['cus_phone'];
$currency=$_POST['currency'];
$amount=$_POST['amount'];
$store_id = "aamarpaytest";  // You have to use your Store ID / MerchantID here
$signature_key="dbb74894e82415a2f7ff0ec3a97e4183"; // Your have to use your signature key here ,it will be provided by aamarPay

$tran_id = "test".rand(1111111,9999999); // Transection id need to be unique for each successful transection.
$url = 'https://sandbox.aamarpay.com/jsonpost.php'; //sandbox
// $url = 'https://secure.aamarpay.com/jsonpost.php'; //live url
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => $url,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
    "store_id": "'.$store_id.'",
    "tran_id": "'.$tran_id.'",
    "success_url": "https://skillup.mynsu.xyz/auth/pay/success.php",
    "fail_url": "https://skillup.mynsu.xyz/auth/pay/success.php",
    "cancel_url": "https://skillup.mynsu.xyz/auth/pay/success.php",
    "amount": "'.$amount.'",
    "currency": "'.$currency.'",
    "signature_key": "'.$signature_key.'",
    "desc": "Merchant Registration Payment",
    "cus_name": "'.$fullName.'",
    "cus_email": "'.$email.'",
    "cus_add1": "House B-158 Road 22",
    "cus_add2": "Mohakhali DOHS",
    "cus_city": "Dhaka",
    "cus_state": "Dhaka",
    "cus_postcode": "1206",
    "cus_country": "Bangladesh",
    "cus_phone": "'.$phone_number.'",
    "type": "json"
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json'
  ),
));

$response = curl_exec($curl);

curl_close($curl);

$responseObj = json_decode($response);

if(isset($responseObj->payment_url) && !empty($responseObj->payment_url)) {

  $paymentUrl = $responseObj->payment_url;
  return header('Location: '. $paymentUrl);
  exit();
    
}else{
    echo $response;
}

?>