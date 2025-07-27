<?php
session_start();
include '../auth/cnct.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== "Admin") {
    session_unset();
    session_destroy();
    $conn->close();
    header("Location: ../index.php");
    exit();
}

$u_id = $_SESSION['u_id'];
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['change_password'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contact = trim($_POST['contact'] ?? '');

    if (empty($name)) $errors[] = "Name is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
    if (!preg_match('/^1[0-9]{9}$/', $contact)) $errors[] = "Phone number must be 11 digits and start with 1.";

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE users SET name=?, email=?, contact=? WHERE u_id=?");
        $stmt->bind_param("sssi", $name, $email, $contact, $u_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['message'] = "Profile updated successfully!";
        $_SESSION['message_type'] = "success";
        header("Location: profile.php");
        exit();
    } else {
        $_SESSION['errors'] = $errors;
        header('Location: profile.php');
        exit();
    }
}

$sql = "SELECT u.name, u.email, u.contact, a.level FROM users u JOIN admins a ON u.u_id = a.u_id WHERE u.u_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $u_id);
$stmt->execute();
$stmt->bind_result($name, $email, $contact, $level);
$stmt->fetch();
$stmt->close();

$image = '../image-assets/common/profile.webp';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($current) || empty($new) || empty($confirm)) {
        $errors[] = "All password fields are required.";
    } elseif ($new !== $confirm) {
        $errors[] = "New passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT pass FROM credentials WHERE u_id = ?");
        $stmt->bind_param("i", $u_id);
        $stmt->execute();
        $stmt->bind_result($hashed_password);
        $stmt->fetch();
        $stmt->close();

        if (!password_verify($current, $hashed_password)) {
            $errors[] = "Current password is incorrect.";
        } else {
            $new_hashed = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE credentials SET pass = ? WHERE u_id = ?");
            $stmt->bind_param("si", $new_hashed, $u_id);
            $stmt->execute();
            $stmt->close();

            $_SESSION['message'] = "Password updated successfully!";
            $_SESSION['message_type'] = "success";
            header("Location: profile.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Admin Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="prefetch" href="../image-assets/common/fav.webp" as="image">
    <link rel="icon" href="../image-assets/common/fav.webp" type="image/webp">
</head>
<body>
<script>
    window.addEventListener('pageshow', function (event) {
        if (event.persisted) {
            window.location.reload();
        }
    });
</script>
<nav class="navbar navbar-expand-lg navbar-blur sticky-top shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="">SkillUp Academy</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link active" href="#">Profile</a></li>
                <li class="nav-item"><a class="nav-link" href="../auth/logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="container mt-5">
    <p class="text-center mb-4 fs-1">Admin Profile</p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $e): ?>
                <p class="mb-0"><?= htmlspecialchars($e) ?></p>
            <?php endforeach; ?>
        </div>
    <?php elseif ($success || (isset($_SESSION['message']) && $_SESSION['message_type'] === 'success')): ?>
        <div class="alert alert-success">Profile updated successfully.</div>
        <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4 text-center mb-4">
            <img src="<?= htmlspecialchars($image) ?>" class="rounded-circle shadow-sm" alt="Admin Photo" style="width: 170px; height: 170px;">
            <h4 class="mt-3"><?= htmlspecialchars($name) ?></h4>
            <p class="text-muted">Admin</p>
            <p>Admin Level: <?= htmlspecialchars($level) ?></p>
        </div>

        <div class="col-md-8 mt-4">
            <h5>Contacts</h5>
            <div class="d-flex flex-column gap-3 mt-3">
                <div><i class="fas fa-envelope me-2 text-muted"></i><a href="mailto:<?= htmlspecialchars($email) ?>" class="text-decoration-none"><?= htmlspecialchars($email) ?></a></div>
                <div><i class="fas fa-phone me-2 text-muted"></i><span>+880<?= htmlspecialchars($contact) ?></span></div>
            </div>
        </div>
    </div>

    <div class="row text-center">
        <div class="col-md-12 mt-3 mb-4">
            <button type="button" class="btn w-50 btn-outline-dark" data-bs-toggle="modal" data-bs-target="#editProfileModal">Edit Profile</button>
            <button type="button" class="btn w-50 btn-outline-danger mt-2" data-bs-toggle="modal" data-bs-target="#changePasswordModal">Change Password</button>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form class="modal-content" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name) ?>"></div>
                    <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>"></div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number (Starts with 01)</label>
                        <input type="text" name="contact" class="form-control" value="<?= htmlspecialchars($contact) ?>" placeholder="e.g., 01xxxxxxxxx">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <form class="modal-content" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Change Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="change_password" class="btn btn-danger">Change Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


