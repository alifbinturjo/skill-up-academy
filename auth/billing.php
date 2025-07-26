<?php
session_start();
include 'cnct.php';

// Get cart items from cookie
$cart_ids = isset($_COOKIE['cart']) && $_COOKIE['cart'] !== '' ? explode(',', $_COOKIE['cart']) : [];
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

// Payment handling (simulated for now)
$statusMsg = '';
if (isset($_POST['pay_now'])) {
    if (!empty($courses) && $total > 0) {
        // Simulate payment success or fail
        $paymentSuccess = true; // Change this based on actual payment API response

        if ($paymentSuccess) {
            $u_id = $_SESSION['u_id'] ?? 1; // Replace 1 with logged-in user ID
            $stmt = $conn->prepare("INSERT IGNORE INTO enrolls (u_id, c_id, rating) VALUES (?, ?, NULL)");

            foreach ($courses as $course) {
                $stmt->bind_param('ii', $u_id, $course['c_id']);
                $stmt->execute();
            }
            $stmt->close();

            // Clear cart cookie
            setcookie('cart', '', time() - 3600, '/');
            $statusMsg = "<div class='alert alert-success text-center'>Payment Successful! Courses have been enrolled.</div>";
            $courses = [];
            $total = 0;
        } else {
            $statusMsg = "<div class='alert alert-danger text-center'>Payment Failed! Please try again.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Billing | SkillUp Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../style.css" />
</head>
<body>

<div class="container mt-5">
    <h2 class="mb-4">Your Cart</h2>
    <?= $statusMsg ?>

    <?php if (empty($courses)): ?>
        <p class="text-muted">Your cart is empty.</p>
    <?php else: ?>
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Course Title</th>
                    <th>Price (BDT)</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="cart-table-body">
                <?php foreach ($courses as $course): ?>
                    <tr data-id="<?= $course['c_id'] ?>">
                        <td><?= htmlspecialchars($course['title']) ?></td>
                        <td class="course-price"><?= $course['amount'] ?></td>
                        <td>
                            <button type="button" class="btn btn-outline-danger btn-sm remove-btn" aria-label="Remove course">&times;</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr class="table-dark fw-bold">
                    <td>Total</td>
                    <td id="total-price">BDT<?= $total ?></td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <form method="POST" class="text-end">
            <button class="btn btn-success" name="pay_now" type="submit">Proceed to Payment</button>
        </form>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const removeButtons = document.querySelectorAll('.remove-btn');
    const totalPriceEl = document.getElementById('total-price');
    const cartBody = document.getElementById('cart-table-body');

    removeButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            const row = e.target.closest('tr');
            const priceEl = row.querySelector('.course-price');
            const price = parseFloat(priceEl.textContent);

            // Remove this course row
            row.remove();

            // Update total price
            let currentTotal = parseFloat(totalPriceEl.textContent.replace(/[^\d.]/g, ''));
            currentTotal -= price;
            totalPriceEl.textContent = 'BDT' + currentTotal.toFixed(2);

            // Update cookie cart
            const removedId = row.getAttribute('data-id');
            let cart = getCookie('cart');
            if (cart) {
                let cartArr = cart.split(',');
                cartArr = cartArr.filter(id => id !== removedId);
                setCookie('cart', cartArr.join(','), 7);
            }

            // If no courses left, show empty message
            if (cartBody.querySelectorAll('tr').length === 1) {
                cartBody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">Your cart is empty.</td></tr>';
                document.querySelector('form').style.display = 'none';
            }
        });
    });

    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
    }
    function setCookie(name, value, days) {
        const d = new Date();
        d.setTime(d.getTime() + (days*24*60*60*1000));
        const expires = "expires="+ d.toUTCString();
        document.cookie = name + "=" + value + ";" + expires + ";path=/";
    }
});
</script>
</body>
</html>
