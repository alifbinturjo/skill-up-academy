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

// Payment status handling (after redirect from success.php)
$statusMsg = '';
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'success') {
        $u_id = $_SESSION['u_id'];

        if (!empty($cart_ids)) {
            $stmt = $conn->prepare("INSERT IGNORE INTO enrolls (u_id, c_id, rating) VALUES (?, ?, NULL)");
            foreach ($cart_ids as $c_id) {
                $stmt->bind_param('ii', $u_id, $c_id);
                $stmt->execute();
            }
            $stmt->close();
        }

        // Clear cart after success
        setcookie('cart', '', time() - 3600, '/');
        $statusMsg = "<div class='alert alert-success text-center'>✅ Payment Successful! Courses have been enrolled.</div>";
        $courses = [];
        $total = 0;
    } elseif ($_GET['status'] === 'fail') {
        $statusMsg = "<div class='alert alert-danger text-center'>❌ Payment Failed! Please try again.</div>";
    } elseif ($_GET['status'] === 'missing') {
        $statusMsg = "<div class='alert alert-warning text-center'>⚠️ Payment response missing or invalid.</div>";
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
    <nav class="navbar navbar-expand-lg navbar-blur sticky-top shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="">SkillUp Academy</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
  
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link active" href="../index.php">Home</a>
        </li>
        
        <?php
          if(isset($_SESSION['role'])){
            echo'<li class="nav-item">';
            if($_SESSION['role']==="Student")
              echo '<a class="nav-link" href="../student/dashboard.php">Dashboard</a> </li>';
            else if($_SESSION['role']==="Instructor")
              echo '<a class="nav-link" href="../instructor/dashboard.php">Dashboard</a> </li>';
            else
              echo '<a class="nav-link" href="../admin/dashboard.php">Dashboard</a> </li>';

            echo'<li class="nav-item">
                  <a class="nav-link" href="logout.php">Logout</a>
                  </li>';
          }
          else{
            echo '<a class="nav-link" href="login.php">Login</a> </li>
                  <li class="nav-item">
                  <a class="nav-link" href="signup.php">Signup</a>
                  </li>';
          }
        ?>
      </ul>
    </div>
  </div>
</nav>

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

        <form method="POST" action="pay/index.php" class="text-end">
    <input type="hidden" name="amount" value="<?= $total ?>">
    <input type="hidden" name="currency" value="BDT">
    <input type="hidden" name="cus_name" value="<?= htmlspecialchars($_SESSION['u_id']) ?>">
    <input type="hidden" name="cus_email" value="xyz@gmail.com">
    <input type="hidden" name="cus_phone" value="00000000000">
    
    <button class="btn btn-success" name="pay_now" type="submit">Proceed to Payment</button>
</form>

    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" defer></script>
<script defer>
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
