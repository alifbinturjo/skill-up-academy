<?php
require_once '../auth/cnct.php';
session_start();

// -------------------------------
// Instructor-only access restriction
// -------------------------------
if (!isset($_SESSION['user_id'], $_SESSION['user_type']) || $_SESSION['user_type'] !== 'instructor') {
    $_SESSION['error'] = "Unauthorized access. Please login as instructor.";
    header("Location: ../auth/login.php");
    exit();
}

// Verify instructor exists in database (optional but recommended)
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT u_id FROM instructors WHERE u_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Instructor not found: force logout
    $_SESSION['error'] = "Access denied. Instructor not found.";
    session_destroy();
    header("Location: ../auth/login.php");
    exit();
}
$stmt->close();

// For testing only: preset course_id (remove or update in production)
if (!isset($_SESSION['course_id'])) {
    $_SESSION['course_id'] = 1;
}

// Handle form submission - post new notice
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title'] ?? '');
    $message = $conn->real_escape_string($_POST['message'] ?? '');

    $sql = "INSERT INTO instructors_notices (title, message, date, u_id, c_id) 
            VALUES ('$title', '$message', CURDATE(), {$_SESSION['user_id']}, {$_SESSION['course_id']})";

    if ($conn->query($sql)) {
        $_SESSION['success'] = "Notice posted successfully!";
        $_SESSION['new_notice'] = true;
        header("Location: post-notices.php");
        exit;
    } else {
        $_SESSION['error'] = "Error: " . $conn->error;
        header("Location: post-notices.php");
        exit;
    }
}

// Handle notice deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $sql = "DELETE FROM instructors_notices WHERE n_id = $id AND u_id = {$_SESSION['user_id']}";

    if ($conn->query($sql)) {
        $_SESSION['success'] = "Notice deleted!";
    } else {
        $_SESSION['error'] = "Delete failed!";
    }
    header("Location: post-notices.php");
    exit;
}

// Fetch notices for this instructor, newest first
$result = $conn->query("SELECT * FROM instructors_notices WHERE u_id = {$_SESSION['user_id']} ORDER BY date DESC, n_id DESC");
$notices = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Check if new notice flag is set (for red dot)
$hasNewNotice = isset($_SESSION['new_notice']) && $_SESSION['new_notice'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Instructor Notices</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="../style.css" />
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-blur sticky-top shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="#">SkillUp Academy</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="dashboard.html">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="courses.html">Courses</a></li>
        <li class="nav-item position-relative">
          <a class="nav-link <?= $hasNewNotice ? 'fw-bold' : '' ?>" href="post-notices.php">
            Notices
            <?php if ($hasNewNotice): ?>
              <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
            <?php endif; ?>
          </a>
        </li>
        <li class="nav-item"><a class="nav-link" href="profile.html">Profile</a></li>
        <li class="nav-item"><a class="nav-link" href="../auth/logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container-fluid mt-4 px-3 mb-5">
  <!-- Header Card -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="card bg-transparent border-0 shadow-sm">
        <div class="card-body text-center py-4">
          <h3 class="mb-3">Notices</h3>
          <div class="d-flex justify-content-center">
            <button type="button" class="btn btn-primary px-4 py-2" style="width: 100%;" data-bs-toggle="modal" data-bs-target="#noticeModal">
              <i class="bi bi-plus-circle me-1"></i>Create Notice
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Create Notice Modal -->
  <div class="modal fade" id="noticeModal" tabindex="-1" aria-labelledby="noticeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form method="POST" action="">
          <div class="modal-header">
            <h5 class="modal-title" id="noticeModalLabel">Create New Notice</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Notice Title</label>
              <input type="text" class="form-control" name="title" required />
            </div>
            <div class="mb-3">
              <label class="form-label">Description</label>
              <textarea class="form-control" name="message" rows="5" required></textarea>
            </div>
          </div>
          <div class="modal-footer justify-content-center">
            <button type="submit" class="btn btn-primary px-4">Post Notice</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Notices List Card -->
  <div class="row">
    <div class="col-12">
      <div class="card bg-transparent border-0 shadow-sm">
        <div class="card-body">
          <h5 class="text-center mb-4">Recent Notices</h5>
          <div class="list-group">
            <?php if (empty($notices)): ?>
              <div class="list-group-item text-center text-muted">No notices found</div>
            <?php else: ?>
              <?php foreach ($notices as $notice): ?>
                <div class="list-group-item d-flex justify-content-between align-items-center mb-2">
                  <div>
                    <h6 class="fw-bold"><?= htmlspecialchars($notice['title']) ?></h6>
                    <p><?= nl2br(htmlspecialchars($notice['message'])) ?></p>
                    <small class="text-muted">Posted on <?= htmlspecialchars($notice['date']) ?></small>
                  </div>
                  <a href="?delete=<?= $notice['n_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this notice?');">Remove</a>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Toast Message -->
<?php if (isset($_SESSION['success']) || isset($_SESSION['error'])): ?>
  <div class="toast-container position-fixed bottom-0 start-50 translate-middle-x p-3 mb-4" style="z-index: 9999;">
    <div class="toast align-items-center text-white bg-<?= isset($_SESSION['success']) ? 'success' : 'danger' ?> border-0 show"
         role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body">
          <?= htmlspecialchars($_SESSION['success'] ?? $_SESSION['error']); ?>
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
  </div>
  <script>
    setTimeout(() => {
      const toastEl = document.querySelector('.toast');
      const toast = bootstrap.Toast.getOrCreateInstance(toastEl);
      toast.hide();
    }, 4000);
  </script>
  <?php 
  // Clear session messages and new notice flag after showing
  unset($_SESSION['success'], $_SESSION['error'], $_SESSION['new_notice']); 
  ?>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
