<?php
session_start();
require_once '../auth/cnct.php';

// Ensure only admin can access
if (!isset($_SESSION['u_id']) || $_SESSION['role'] !== 'Admin') {
    $_SESSION['error'] = "Unauthorized access.";
    header("Location: ../auth/login.php");
    exit();
}

      // Check if Admin
            $admin_check = $conn->prepare("SELECT * FROM admins WHERE u_id = ?");
            $admin_check->bind_param("i", $u_id);
            $admin_check->execute();
            $admin_res = $admin_check->get_result();
            if ($admin_res->num_rows > 0) {
                $role = 'Admin';
                $_SESSION['role'] = $role;
                header("Location: ../admin/post-notices.php");
                exit();
            }

// Handle new notice submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title'] ?? '');
    $message = $conn->real_escape_string($_POST['message'] ?? '');
    $audience = $conn->real_escape_string($_POST['audience'] ?? 'all');

    try {
        $stmt = $conn->prepare("INSERT INTO admin_notices (title, message, date, u_id, audience) 
                                VALUES (?, ?, CURDATE(), ?, ?)");
        $stmt->bind_param("ssis", $title, $message, $_SESSION['u_id'], $audience);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Notice posted successfully!";
        } else {
            throw new Exception("Failed to post: " . $stmt->error);
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }

    // Redirect to avoid form re-submission on refresh
    header("Location: post-notices.php");
    exit();
}

// Optional: Handle delete notice
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $conn->prepare("DELETE FROM admin_notices WHERE n_id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Notice deleted successfully!";
        } else {
            throw new Exception("Failed to delete: " . $stmt->error);
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }

    header("Location: post-notices.php");
    exit();
}

// Load notices
$notices = [];
$res = $conn->query("SELECT * FROM admin_notices ORDER BY date DESC, n_id DESC");
if ($res) {
    $notices = $res->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Notices</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../style.css">
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
          <a class="nav-link" href="dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="admins.php">Admins</a>
          </li>
        <li class="nav-item">
          <a class="nav-link" href="courses.php">Courses</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="instructors.php">Instructors</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="students.php">Students</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="#">Notices</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="profile.php">Profile</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../auth/logout.php">Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container-fluid mt-3 mb-5 px-3">
  <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
  <?php endif; ?>
  
  <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
  <?php endif; ?>

  <div class="row justify-content-center">
    <div class="col-12">
    
 <!-- Header Card (Transparent and wider button) -->
<div class="card shadow-sm mb-4" style="background-color: transparent; border: 1px solid #ccc;">
  <div class="card-body text-center py-3">
    <h3 class="card-title mb-3">Notices</h3>
    <button type="button" class="btn btn-primary btn-sm px-4 py-2" style="min-width: 160px;" data-bs-toggle="modal" data-bs-target="#noticeModal">
      <i class="bi bi-plus-circle me-1"></i>Create Notice
    </button>
  </div>
</div>

      <!-- Notice Modal -->
      <div class="modal fade" id="noticeModal" tabindex="-1" aria-labelledby="noticeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="noticeModalLabel">Create New Notice</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form method="POST" action="post-notices.php">
                <div class="mb-3">
                  <label class="form-label">Notice Title</label>
                  <input type="text" class="form-control" name="title" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Description</label>
                  <textarea class="form-control" name="message" rows="5" required></textarea>
                </div>
                <div class="mb-3">
                  <label class="form-label">Audience</label>
                  <select class="form-select" name="audience">
                    <option value="all">Everyone</option>
                    <option value="student">Students Only</option>
                    <option value="instructor">Instructors Only</option>
                    <option value="admin">Admins Only</option>
                  </select>
                </div>
                <div class="text-center mt-4">
                  <button type="submit" class="btn btn-primary px-4">Post Notice</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Notices Card (90% width) -->
      <div class="row justify-content-center">
        <div class="col-12 ">
         <div class="card shadow-sm" style="background-color: transparent; border: 1px solid #ccc;">

            <div class="card-body">
              <h5 class="card-title text-center mb-4">Recent Notices</h5>
              <div class="list-group">
                <?php if (empty($notices)): ?>
                  <div class="list-group-item">
                    <p class="text-center text-muted">No notices found</p>
                  </div>
                <?php else: ?>
                  <?php foreach ($notices as $notice): ?>
                  <div class="list-group-item d-flex justify-content-between align-items-center mb-2">
                    <div class="flex-grow-1">
                      <h6 class="mb-1 fw-bold"><?= htmlspecialchars($notice['title']) ?></h6>
                      <p class="mb-2"><?= htmlspecialchars($notice['message']) ?></p>
                      <small class="text-muted">
                        <i class="bi bi-calendar me-1"></i><?= date('F j, Y', strtotime($notice['date'])) ?>
                        | <i class="bi bi-people me-1"></i><?= ucfirst($notice['audience']) ?>
                        | <i class="bi bi-person me-1"></i>Admin #<?= htmlspecialchars($notice['u_id']) ?>
                      </small>
                    </div>
                    <div class="ms-3">
                      <a href="post-notices.php?delete=<?= $notice['n_id'] ?>" 
                         class="btn btn-outline-danger btn-sm"
                         onclick="return confirm('Are you sure you want to delete this notice?')">
                         <i class="bi bi-trash"></i>
                      </a>
                    </div>
                  </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>



</body>
</html>