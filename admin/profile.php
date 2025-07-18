<?php
include '../auth/cnct.php';
session_start();

// Mock session user ID for testing (remove in production)
if (!isset($_SESSION['id'])) {
    $_SESSION['id'] = 2; // Replace with actual login session user ID
}
$uid = $_SESSION['id'];
if(!isset($_SESSION['role'])&&$_SESSION['role']!=="admin"){
  session_unset();
  session_destroy();
  $conn->close();
  header("Location: ../index.php");
  exit();
}
// Handle profile update (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $contact = $_POST['contact'] ?? '';

    // Update user profile in 'users' table (including name)
    $stmt = $conn->prepare("UPDATE users SET name=?, email=?, contact=? WHERE u_id=?");
    $stmt->bind_param("sssi", $name, $email, $contact, $uid);
    $stmt->execute();
    $stmt->close();
}

// Fetch profile data
$sql = "SELECT u.name, u.email, u.contact
        FROM users u
        WHERE u.u_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $uid);
$stmt->execute();
$stmt->bind_result($name, $email, $contact);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Admin Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-blur sticky-top shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="">SkillUp Academy</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="courses.php">Courses</a></li>
                <li class="nav-item"><a class="nav-link" href="instructors.php">Instructors</a></li>
                <li class="nav-item"><a class="nav-link" href="admins.php">Admins</a></li>
                <li class="nav-item"><a class="nav-link" href="students.php">Students</a></li>
                <li class="nav-item"><a class="nav-link" href="post-notices.php">Notices</a></li>
                <li class="nav-item"><a class="nav-link active" href="">Profile</a></li>
                <li class="nav-item"><a class="nav-link" href="../auth/logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <p class="text-center mb-4 fs-1">Admin Profile</p>
    
    <div class="row">
        <div class="col-md-4 text-center mb-4">
            <img src="../image-assets/common/profile.webp" class="rounded-circle shadow-sm" alt="Admin Photo" style="width: 170px; height: 170px;">
            <h4 class="mt-3"><?= htmlspecialchars($name) ?></h4>  <!-- Display Admin Name -->
            <p class="text-muted">Admin</p>
        </div>

        <div class="col-md-8 mt-4">
            <h5>Contact</h5>
            <!-- Email as a Button -->
            <a href="mailto:<?= htmlspecialchars($email) ?>" class="btn btn-primary btn-lg mb-3" role="button">
                <i class="fas fa-envelope"></i> Email: <?= htmlspecialchars($email) ?>
            </a>
            <!-- Phone as a Button -->
            <a href="tel:<?= htmlspecialchars($contact) ?>" class="btn btn-success btn-lg mb-3" role="button">
                <i class="fas fa-phone"></i> Phone: <?= htmlspecialchars($contact) ?>
            </a>
            <hr class="divider my-2">
        </div>

        <div class="row text-center">
            <div class="col-md-12 mt-3 mb-4">
                <button type="button" class="btn w-50 btn-outline-dark" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                    Edit Profile
                </button>
            </div>
        </div>

        <!-- Edit Modal -->
        <div class="modal fade" id="editProfileModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <form class="modal-content" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Profile</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name) ?>"></div>  <!-- Admin Name -->
                        <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>"></div>
                        <div class="mb-3"><label class="form-label">Contact</label><input type="text" name="contact" class="form-control" value="<?= htmlspecialchars($contact) ?>"></div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

