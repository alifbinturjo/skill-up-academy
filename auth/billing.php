<?php
include 'cnct.php';
session_start();
//student
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
                    <th></th> <!-- For remove button -->
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

        <div class="text-end">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#paymentModal" id="proceed-btn">Proceed to Payment</button>
        </div>
    <?php endif; ?>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-light">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="paymentModalLabel">Choose Payment Method</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="container">
          <div class="row g-3 text-center">
            <div class="col-6 col-md-3">
              <button class="btn btn-outline-primary w-100 py-3 d-flex flex-column align-items-center justify-content-center h-100">
                <img src="../image-assets/billings/bikash.webp" alt="bKash" class="rounded" style="width:48px; height:48px; object-fit: contain; margin-bottom: 0.5rem;">
                <span>bKash</span>
              </button>
            </div>
            <div class="col-6 col-md-3">
              <button class="btn btn-outline-primary w-100 py-3 d-flex flex-column align-items-center justify-content-center h-100">
                <img src="../image-assets/billings/nagad.webp" alt="Nagad" class="rounded" style="width:48px; height:48px; object-fit: contain; margin-bottom: 0.5rem;">
                <span>Nagad</span>
              </button>
            </div>
            <div class="col-6 col-md-3">
              <button class="btn btn-outline-primary w-100 py-3 d-flex flex-column align-items-center justify-content-center h-100">
                <img src="../image-assets/billings/rocket.webp" alt="Rocket" class="rounded" style="width:48px; height:48px; object-fit: contain; margin-bottom: 0.5rem;">
                <span>Rocket</span>
              </button>
            </div>
            <div class="col-6 col-md-3">
              <button class="btn btn-outline-primary w-100 py-3 d-flex flex-column align-items-center justify-content-center h-100">
                <img src="../image-assets/billings/gpay.webp" alt="Google Pay" class="rounded" style="width:48px; height:48px; object-fit: contain; margin-bottom: 0.5rem;">
                <span>Google Pay</span>
              </button>
            </div>
            <div class="col-6 col-md-3">
              <button class="btn btn-outline-primary w-100 py-3 d-flex flex-column align-items-center justify-content-center h-100">
                <img src="../image-assets/billings/visa.webp" alt="Visa" class="rounded" style="width:48px; height:48px; object-fit: contain; margin-bottom: 0.5rem;">
                <span>Visa</span>
              </button>
            </div>
            <div class="col-6 col-md-3">
              <button class="btn btn-outline-primary w-100 py-3 d-flex flex-column align-items-center justify-content-center h-100">
                <img src="../image-assets/billings/master.webp" alt="MasterCard" class="rounded" style="width:48px; height:48px; object-fit: contain; margin-bottom: 0.5rem;">
                <span>MasterCard</span>
              </button>
            </div>
            <div class="col-6 col-md-3">
              <button class="btn btn-outline-primary w-100 py-3 d-flex flex-column align-items-center justify-content-center h-100">
                <img src="../image-assets/billings/credit.webp" alt="Credit Card" class="rounded" style="width:48px; height:48px; object-fit: contain; margin-bottom: 0.5rem;">
                <span>Credit Card</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="text-center pt-2 pb-3">
        <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
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
            let currentTotal = parseFloat(totalPriceEl.textContent.replace('৳', ''));
            currentTotal -= price;
            totalPriceEl.textContent = '৳' + currentTotal.toFixed(2);

            // Update cookie cart
            const removedId = row.getAttribute('data-id');
            let cart = getCookie('cart');
            if (cart) {
                let cartArr = cart.split(',');
                cartArr = cartArr.filter(id => id !== removedId);
                setCookie('cart', cartArr.join(','), 7);
            }

            // If no courses left, show empty message and hide table + proceed button
            if (cartBody.querySelectorAll('tr').length === 1) { // only total row left
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
