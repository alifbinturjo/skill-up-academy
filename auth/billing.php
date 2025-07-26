<?php
session_start();
require 'cnct.php';

/**
 * =========================
 *  CONFIG — CHANGE THESE
 * =========================
 */
const PAYMENT_API_CREATE_URL = 'https://friend-api.example.com/create-payment'; // Your friend's API URL
const PAYMENT_API_STATUS_URL = 'https://friend-api.example.com/payment-status/'; // Append payment_id
const PAYMENT_API_TOKEN      = 'YOUR_SERVER_SIDE_SECRET_TOKEN'; // Replace with your API token
const SUCCESS_URL            = 'https://your-domain.com/billing.php?action=return';
const FAIL_URL               = 'https://your-domain.com/billing.php?action=return';

$action = $_GET['action'] ?? ($_POST['action'] ?? 'show');

switch ($action) {
    case 'pay':
        initPayment($conn);
        break;
    case 'return':
        handleReturn($conn);
        break;
    default:
        showCart($conn);
        break;
}

// ======================
//  Show Cart
// ======================
function showCart(mysqli $conn)
{
    $cart_ids = isset($_COOKIE['cart']) && strlen($_COOKIE['cart']) ? explode(',', $_COOKIE['cart']) : [];
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
            $total += (float)$row['amount'];
        }
        $stmt->close();
    }

    $_SESSION['cart_course_ids'] = array_column($courses, 'c_id');
    $_SESSION['total_amount']    = $total;
    $_SESSION['currency']        = 'BDT';
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
                    <tr data-id="<?= (int)$course['c_id'] ?>">
                        <td><?= htmlspecialchars($course['title']) ?></td>
                        <td class="course-price"><?= number_format((float)$course['amount'], 2) ?></td>
                        <td>
                            <button type="button" class="btn btn-outline-danger btn-sm remove-btn" aria-label="Remove course">&times;</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr class="table-dark fw-bold">
                    <td>Total</td>
                    <td id="total-price">৳<?= number_format($total, 2) ?></td>
                    <td></td>
                </tr>
                </tbody>
            </table>

            <form action="billing.php" method="post" class="text-end">
                <input type="hidden" name="action" value="pay">
                <button class="btn btn-success" type="submit" id="proceed-btn">Proceed to Payment</button>
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

                    row.remove();

                    let currentTotal = parseFloat(totalPriceEl.textContent.replace(/[^\d.]/g, ''));
                    currentTotal -= price;
                    totalPriceEl.textContent = '৳' + currentTotal.toFixed(2);

                    const removedId = row.getAttribute('data-id');
                    let cart = getCookie('cart');
                    if (cart) {
                        let cartArr = cart.split(',');
                        cartArr = cartArr.filter(id => id !== removedId);
                        setCookie('cart', cartArr.join(','), 7);
                    }

                    if (cartBody.querySelectorAll('tr').length === 1) {
                        cartBody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">Your cart is empty.</td></tr>';
                        document.getElementById('proceed-btn').style.display = 'none';
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
    <?php
    exit;
}

// ======================
//  Start Payment
// ======================
function initPayment(mysqli $conn)
{
    if (!isset($_SESSION['total_amount'], $_SESSION['cart_course_ids'])) {
        header('Location: billing.php');
        exit;
    }

    $u_id = $_SESSION['u_id'] ?? null;
    if (!$u_id) {
        header('Location: login.php');
        exit;
    }

    $total_amount = (float)$_SESSION['total_amount'];
    $course_ids   = $_SESSION['cart_course_ids'];

    $payload = [
        'amount'      => $total_amount,
        'currency'    => 'BDT',
        'metadata'    => [
            'user_id'    => $u_id,
            'course_ids' => $course_ids
        ],
        'success_url' => SUCCESS_URL,
        'fail_url'    => FAIL_URL
    ];

    $ch = curl_init(PAYMENT_API_CREATE_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . PAYMENT_API_TOKEN
        ],
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpCode >= 400) {
        die('Payment initialization failed. Please try again.');
    }

    $data = json_decode($response, true);
    if (!isset($data['payment_id'], $data['payment_url'])) {
        die('Invalid response from payment API');
    }

    $_SESSION['payment_id'] = $data['payment_id'];

    header('Location: ' . $data['payment_url']);
    exit;
}

// ======================
//  Return URL
// ======================
function handleReturn(mysqli $conn)
{
    if (!isset($_SESSION['payment_id'], $_SESSION['u_id'], $_SESSION['cart_course_ids'])) {
        echo "<h2 class='text-danger text-center mt-5'>Session expired or invalid. Please try again.</h2>";
        echo "<p class='text-center'><a href='billing.php' class='btn btn-secondary'>Back to Cart</a></p>";
        exit;
    }

    $payment_id = $_SESSION['payment_id'];
    $u_id       = (int)$_SESSION['u_id'];
    $course_ids = $_SESSION['cart_course_ids'];

    $ch = curl_init(PAYMENT_API_STATUS_URL . urlencode($payment_id));
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . PAYMENT_API_TOKEN
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpCode >= 400) {
        echo "<h2 class='text-danger text-center mt-5'>Could not verify payment. Please contact support.</h2>";
        exit;
    }

    $data = json_decode($response, true);
    if (($data['status'] ?? '') !== 'success') {
        echo "<h2 class='text-danger text-center mt-5'>Payment Failed/Cancelled</h2>";
        unset($_SESSION['payment_id']);
        echo "<p class='text-center'><a href='billing.php' class='btn btn-secondary'>Back to Cart</a></p>";
        exit;
    }

    // Insert enrollments
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT IGNORE INTO enrolls (u_id, c_id, rating) VALUES (?, ?, NULL)");
        foreach ($course_ids as $c_id) {
            $c_id = (int)$c_id;
            $stmt->bind_param('ii', $u_id, $c_id);
            $stmt->execute();
        }
        $stmt->close();
        $conn->commit();

        setcookie('cart', '', time() - 3600, '/');
        unset($_SESSION['cart_course_ids'], $_SESSION['total_amount'], $_SESSION['payment_id']);

        echo "<h2 class='text-success text-center mt-5'>Payment Successful! You are enrolled.</h2>";
        echo "<p class='text-center'><a href='my-courses.php' class='btn btn-primary'>Go to My Courses</a></p>";

    } catch (Exception $e) {
        $conn->rollback();
        echo "<h2 class='text-danger text-center mt-5'>Payment succeeded, but enrollment failed. Please contact support.</h2>";
    }
    exit;
}
?>
