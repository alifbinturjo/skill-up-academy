<?php
session_start();
include '../auth/cnct.php';

// Check if user is logged in as an Admin
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

// Handle profile update (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['change_password'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $level = (int)$_POST['level'];

    // Validate fields
    if (empty($name)) {
        $errors[] = "Name is required";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // If no errors, proceed to update the profile
    if (empty($errors)) {
        // Update user data in the `users` table
        $stmt = $conn->prepare("UPDATE users SET name=?, email=?, contact=? WHERE u_id=?");
        $stmt->bind_param("sssi", $name, $email, $contact, $u_id);
        $stmt->execute();
        $stmt->close();

        // Update admin level in the `admins` table
        $stmt = $conn->prepare("UPDATE admins SET level=? WHERE u_id=?");
        $stmt->bind_param("ii", $level, $u_id);
        $stmt->execute();
        $stmt->close();

        // Set success message in session
        $success = true;
    }
}

// Fetch profile data
$sql = "SELECT u.name, u.email, u.contact, a.level
        FROM users u
        JOIN admins a ON u.u_id = a.u_id
        WHERE u.u_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $u_id);
$stmt->execute();
$stmt->bind_result($name, $email, $contact, $level);
$stmt->fetch();
$stmt->close();

$image = '../image-assets/common/Profile.webp';
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
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="../index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
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
        <?php elseif ($success): ?>
            <div class="alert alert-success">Profile updated successfully.</div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4 text-center mb-4">
                <img src="<?= htmlspecialchars($image) ?>" class="rounded-circle shadow-sm" alt="Admin Photo"
                    style="width: 170px; height: 170px;">
                <h4 class="mt-3"><?= htmlspecialchars($name) ?></h4>
                <p class="text-muted">Admin</p>
                <span class="badge bg-primary">Level <?= htmlspecialchars($level) ?></span>
            </div>

            <div class="col-md-8 mt-4">
                <h5>Contact Information</h5>
                <div class="d-flex flex-column gap-3 mt-3">
                    <div>
                        <i class="fas fa-envelope me-2 text-muted"></i>
                        <a href="mailto:<?= htmlspecialchars($email) ?>" class="text-decoration-none"><?= htmlspecialchars($email) ?></a>
                    </div>
                    <div>
                        <i class="fas fa-phone me-2 text-muted"></i>
                        <span>+880<?= htmlspecialchars($contact) ?></span>
                    </div>
                </div>
                <hr class="divider my-2">
            </div>

            <div class="row text-center">
                <div class="col-md-12 mt-3 mb-4">
                    <button type="button" class="btn w-50 btn-outline-dark" data-bs-toggle="modal"
                        data-bs-target="#editProfileModal">
                        Edit Profile
                    </button>
                    <button type="button" class="btn w-50 btn-outline-danger mt-2" data-bs-toggle="modal"
                        data-bs-target="#changePasswordModal">
                        Change Password
                    </button>
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
                            <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name"
                                    class="form-control" value="<?= htmlspecialchars($name) ?>"></div>
                            <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email"
                                    class="form-control" value="<?= htmlspecialchars($email) ?>"></div>
                            <div class="mb-3">
                                <label class="form-label">Phone Number (Starts with 1)</label>
                                <input type="text" name="contact" class="form-control" value="<?= htmlspecialchars($contact) ?>"
                                    placeholder="e.g., 01xxxxxxxxx">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Admin Level</label>
                                <select class="form-select" name="level" required>
                                    <option value="1" <?= $level == 1 ? 'selected' : '' ?>>1</option>
                                    <option value="2" <?= $level == 2 ? 'selected' : '' ?>>2</option>
                                    <option value="3" <?= $level == 3 ? 'selected' : '' ?>>3</option>
                                    <option value="4" <?= $level == 4 ? 'selected' : '' ?>>4</option>
                                </select>
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
