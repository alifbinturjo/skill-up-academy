<?php
include 'cnct.php';
session_start();

$cart_ids = isset($_COOKIE['cart']) ? explode(',', $_COOKIE['cart']) : [];

$courses = [];
$total = 0;

if (!empty($cart_ids)) {
    $placeholders = implode(',', array_fill(0, count($cart_ids), '?'));
    $types = str_repeat('i', count($cart_ids));

    $sql = "SELECT c_id, title, amount FROM courses WHERE c_id IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$cart_ids);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
        $total += $row['amount'];
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Billing | SkillUp Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../style.css" />
</head>
<body>

<div class="container mt-5">
    <h2 class="mb-4">Your Cart</h2>

    <?php if (empty($courses)): ?>
        <p class="text-muted">Your cart is empty.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Course Title</th>
                    <th>Price (৳)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $course): ?>
                    <tr>
                        <td><?= htmlspecialchars($course['title']) ?></td>
                        <td><?= $course['amount'] ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="table-dark fw-bold">
                    <td>Total</td>
                    <td>৳<?= $total ?></td>
                </tr>
            </tbody>
        </table>

        <div class="text-end">
            <a href="pay_now.php" class="btn btn-success">Proceed to Payment</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
