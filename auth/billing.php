<?php
session_start();
include 'cnct.php';

$statusMsg = "";
$u_id = $_SESSION['u_id'] ?? 0;

// Retrieve and validate cart items from cookie
$cart_items = [];
if (isset($_COOKIE['cart']) && $_COOKIE['cart'] !== '') {
    $cart_items = array_unique(array_filter(array_map('intval', explode(',', $_COOKIE['cart']))));
}

// Get all courses owned by user (for price adjustment)
$owned_courses = [];
if ($u_id) {
    $stmt = $conn->prepare("SELECT c_id FROM enrolls WHERE u_id = ?");
    $stmt->bind_param('i', $u_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $owned_courses[] = $row['c_id'];
    }
    $stmt->close();
}

// If user is logged in, remove owned courses from cart
if ($u_id && !empty($cart_items)) {
    $placeholders = implode(',', array_fill(0, count($cart_items), '?'));
    $stmt = $conn->prepare("SELECT c_id FROM enrolls WHERE u_id = ? AND c_id IN ($placeholders)");
    $types = str_repeat('i', count($cart_items));
    $stmt->bind_param('i' . $types, $u_id, ...$cart_items);
    $stmt->execute();
    $result = $stmt->get_result();

    $already_owned = [];
    while ($row = $result->fetch_assoc()) {
        $already_owned[] = $row['c_id'];
    }
    $stmt->close();

    // Filter out owned courses
    if (!empty($already_owned)) {
        $cart_items = array_diff($cart_items, $already_owned);
        if (count($already_owned) > 0) {
            if (empty($cart_items)) {
                setcookie('cart', '', time() - 3600, '/', '', false, true);
                unset($_COOKIE['cart']);
            } else {
                setcookie('cart', implode(',', $cart_items), time() + (7 * 24 * 60 * 60), '/', '', false, true);
                $_COOKIE['cart'] = implode(',', $cart_items);
            }
        }
    }
}

//   Handle item removal and delete cookie if cart is empty
if (isset($_GET['remove'])) {
    $remove_id = (int)$_GET['remove'];
    if (($key = array_search($remove_id, $cart_items)) !== false) {
        unset($cart_items[$key]);
        $cart_items = array_values($cart_items); // Reindex array

        if (empty($cart_items)) {
            setcookie('cart', '', time() - 3600, '/', '', false, true);
            unset($_COOKIE['cart']);
        } else {
            setcookie('cart', implode(',', $cart_items), time() + (7 * 24 * 60 * 60), '/', '', false, true);
            $_COOKIE['cart'] = implode(',', $cart_items);
        }

        header("Location: billing.php");
        exit();
    }
}

// Fetch course details for items in cart
$courses = [];
$total = 0;

if (!empty($cart_items)) {
    $placeholders = implode(',', array_fill(0, count($cart_items), '?'));
    $stmt = $conn->prepare("SELECT c_id, title, amount FROM courses WHERE c_id IN ($placeholders)");
    $types = str_repeat('i', count($cart_items));
    $stmt->bind_param($types, ...$cart_items);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $is_owned = in_array($row['c_id'], $owned_courses);
        $courses[] = [
            'c_id' => $row['c_id'],
            'title' => $row['title'],
            'amount' => $is_owned ? 0 : $row['amount'],
            'is_owned' => $is_owned
        ];
        $total += $is_owned ? 0 : $row['amount'];
    }
    $stmt->close();
}

// Handle payment status messages
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'success') {
        if (!empty($cart_items) && $u_id) {
            $stmt = $conn->prepare("INSERT IGNORE INTO enrolls (u_id, c_id, rating) VALUES (?, ?, NULL)");
            foreach ($cart_items as $c_id) {
                $stmt->bind_param('ii', $u_id, $c_id);
                $stmt->execute();
            }
            $stmt->close();
        }

        // Clear cart after successful payment
        setcookie('cart', '', time() - 3600, '/', '', false, true);
        unset($_COOKIE['cart']);

        $statusMsg = "<div class='alert alert-success text-center'>✅ Payment Successful! Courses have been enrolled.</div>";
        $courses = [];
        $total = 0;
        $cart_items = [];
    } elseif ($_GET['status'] === 'fail') {
        $statusMsg = "<div class='alert alert-danger text-center'>❌ Payment Failed! Please try again.</div>";
    } elseif ($_GET['status'] === 'missing') {
        $statusMsg = "<div class='alert alert-warning text-center'>⚠️ Payment response missing or invalid.</div>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Billing | SkillUp Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../style.css" />
<link rel="prefetch" href="../image-assets/common/fav.webp" as="image">
    <link rel="icon" href="../image-assets/common/fav.webp" type="image/webp">

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
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="cart-table-body">
                <?php foreach ($courses as $course): ?>
                    <tr>
                        <td><?= htmlspecialchars($course['title']) ?></td>
                        <td class="course-price"><?= $course['is_owned'] ? '0.00 (Owned)' : $course['amount'] ?></td>
                        <td><?= $course['is_owned'] ? '<span class="badge bg-info">Already Owned</span>' : '<span class="badge bg-success">New Purchase</span>' ?></td>
                        <td>
                            <a href="billing.php?remove=<?= $course['c_id'] ?>" class="btn btn-outline-danger btn-sm" aria-label="Remove course">&times;</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr class="table-dark fw-bold">
                    <td>Total</td>
                    <td id="total-price">BDT<?= number_format($total, 2) ?></td>
                    <td colspan="2"></td>
                </tr>
            </tbody>
        </table>

        <form method="POST" action="pay/index.php" class="text-end" id="payment-form">
            <input type="hidden" name="amount" value="<?= $total ?>">
            <input type="hidden" name="currency" value="BDT">
            <input type="hidden" name="cus_name" value="<?= htmlspecialchars($_SESSION['u_id'] ?? '') ?>">
            <input type="hidden" name="cus_email" value="xyz@gmail.com">
            <input type="hidden" name="cus_phone" value="00000000000">
            
            <button class="btn btn-success" name="pay_now" type="submit" <?= $total == 0 ? 'disabled' : '' ?>>Proceed to Payment</button>
        </form>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
