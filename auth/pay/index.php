<?php
session_start();
// pay/index.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and assign posted values
    $amount = $_POST['amount'] ?? '';
    $currency = $_POST['currency'] ?? '';
    $cus_name = $_POST['cus_name'] ?? '';
    $cus_email = $_POST['cus_email'] ?? '';
    $cus_phone = $_POST['cus_phone'] ?? '';
} else {
    // Redirect or show error if accessed directly
    header("Location: ../courses.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Redirecting to Payment</title>
</head>
<body>
    <form id="payForm" action="process.php" method="post" style="display:none;">
        <input type="hidden" name="amount" value="<?= htmlspecialchars($amount) ?>">
        <input type="hidden" name="currency" value="<?= htmlspecialchars($currency) ?>">
        <input type="hidden" name="cus_name" value="<?= htmlspecialchars($cus_name) ?>">
        <input type="hidden" name="cus_email" value="<?= htmlspecialchars($cus_email) ?>">
        <input type="hidden" name="cus_phone" value="<?= htmlspecialchars($cus_phone) ?>">
    </form>

    <script>
        document.getElementById('payForm').submit();
    </script>
</body>
</html>
