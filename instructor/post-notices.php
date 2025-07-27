<?php
session_start();
require_once '../auth/cnct.php';


// -------------------------------
// Instructor-only access restriction
// -------------------------------
if (!isset($_SESSION['u_id'], $_SESSION['role']) || $_SESSION['role'] !== 'Instructor') {
    $_SESSION['error'] = "Unauthorized access. Please login as instructor.";
    header("Location: ../auth/login.php");
    exit();
}

// Verify instructor exists in database
$user_id = $_SESSION['u_id'];
$stmt = $conn->prepare("SELECT u_id FROM instructors WHERE u_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Access denied. Instructor not found.";
    session_destroy();
    header("Location: ../auth/login.php");
    exit();
}
$stmt->close();

// Fetch distinct course IDs for filter dropdown
$courses = [];
$courseResult = $conn->query("SELECT DISTINCT c_id FROM instructors_notices ORDER BY c_id");
if ($courseResult) {
    $courses = $courseResult->fetch_all(MYSQLI_ASSOC);
}

// Determine selected course filter
$selectedCourse = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

// Handle form submission - post new notice
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title'] ?? '');
    $message = $conn->real_escape_string($_POST['message'] ?? '');
    $courseId = (int)($_POST['c_id'] ?? 1);

    $sql = "INSERT INTO instructors_notices (title, message, date, u_id, c_id) 
            VALUES ('$title', '$message', CURDATE(), {$_SESSION['u_id']}, $courseId)";

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
    $sql = "DELETE FROM instructors_notices WHERE n_id = $id";

    if ($conn->query($sql)) {
        $_SESSION['success'] = "Notice deleted!";
    } else {
        $_SESSION['error'] = "Delete failed!";
    }
    header("Location: post-notices.php");
    exit;
}

// Fetch notices (filtered by course if selected)
$query = "SELECT * FROM instructors_notices";
if ($selectedCourse > 0) {
    $query .= " WHERE c_id = $selectedCourse";
}
$query .= " ORDER BY date DESC, n_id DESC";
$result = $conn->query($query);
$notices = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Check if new notice flag is set
$hasNewNotice = isset($_SESSION['new_notice']) && $_SESSION['new_notice'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Notice</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
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
            <a class="nav-link" href="../index.php">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="">Dashboard</a>
          </li>
          <li class="nav-item">
<<<<<<< HEAD
=======
            <a class="nav-link active" href="notices.php">Notices</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="profile.php">Profile</a>
          </li>
          <li class="nav-item">
>>>>>>> fa605e02d6adde23530be8ca38e3745691c2c5de
            <a class="nav-link" href="../auth/logout.php">Logout</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container-fluid mt-3 mb-5 px-3">
    <div class="row justify-content-center">
      <div class="col-12">

        <!-- Post Notice Card -->
        <div class="card bg-transparent shadow card-h border-0 mb-4">
          <div class="card-body">
            <p class="text-center mb-4 fs-1">Post Notice</p>
            <h5 class="mb-3">Create Notice</h5>
            <form method="POST" action="">
              <div class="mb-3">
                <label class="form-label">Notice Title</label>
                <input type="text" class="form-control bg-transparent border border-dark" name="title"
                  placeholder="Enter notification title" required />
              </div>
              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control bg-transparent border border-dark" name="message" rows="5"
                  placeholder="Enter notification description" required></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Select Course</label>
                <select class="form-control bg-transparent border border-dark" name="c_id" required>
                  <?php foreach ($courses as $course): ?>
                    <option value="<?= $course['c_id'] ?>"><?= 'Course ID: ' . $course['c_id'] ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="text-center mt-2">
                <button type="submit" class="btn btn-primary btn-sm">Post Notice</button>
              </div>
            </form>
          </div>
        </div>

        <!-- Filter Section -->
        <div class="card bg-transparent shadow-sm border-0 mb-4">
          <div class="card-body d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Recent Notices</h5>
            <form method="GET" action="" class="d-flex">
              <select name="course_id" class="form-select me-2" onchange="this.form.submit()">
                <option value="0">All Courses</option>
                <?php foreach ($courses as $course): ?>
                  <option value="<?= $course['c_id'] ?>" <?= ($selectedCourse == $course['c_id']) ? 'selected' : '' ?>>
                    Course ID: <?= $course['c_id'] ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <noscript><button type="submit" class="btn btn-primary">Filter</button></noscript>
            </form>
          </div>
        </div>

        <!-- Recent Notices -->
        <div class="card bg-transparent shadow-sm border-0">
          <div class="card-body">
            <div class="list-group" id="notificationList">
              <?php if (empty($notices)): ?>
                <div class="list-group-item text-center text-muted">No notices found</div>
              <?php else: ?>
                <?php foreach ($notices as $notice): ?>
                  <div
                    class="list-group-item d-flex justify-content-between align-items-center bg-transparent border border-dark mt-2">
                    <div>
                      <h6 class="mb-1"><?= htmlspecialchars($notice['title']) ?> (Course: <?= $notice['c_id'] ?>)</h6>
                      <p class="mb-1"><?= nl2br(htmlspecialchars($notice['message'])) ?></p>
                      <small class="text-muted">Posted on: <?= htmlspecialchars($notice['date']) ?></small>
                    </div>
                    <a href="?delete=<?= $notice['n_id'] ?>" class="btn btn-danger btn-sm"
                      onclick="return confirm('Are you sure you want to delete this notice?');">Remove</a>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Toast Messages -->
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
          <?php unset($_SESSION['success'], $_SESSION['error'], $_SESSION['new_notice']); ?>
        <?php endif; ?>

      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
