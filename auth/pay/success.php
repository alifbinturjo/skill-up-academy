<?php
session_start();

$merTxnId = $_POST['mer_txnid'] ?? '';

if (!$merTxnId) {
    header("Location: ../billing.php?status=missing");
    exit;
}

$store_id = "aamarpaytest";
$signature_key = "dbb74894e82415a2f7ff0ec3a97e4183";
$url = "https://sandbox.aamarpay.com/api/v1/trxcheck/request.php?request_id=$merTxnId&store_id=$store_id&signature_key=$signature_key&type=json";

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($curl);
curl_close($curl);

$data = json_decode($response, true);
$pay_status = strtolower($data['pay_status'] ?? '');
$_SESSION['u_id']=$data['cus_name'];
$_SESSION['role']='Student';

if ($pay_status === "successful") {
    header("Location: ../billing.php?status=success");
    exit;
} else {
    header("Location: ../billing.php?status=fail");
    exit;
}
?>
