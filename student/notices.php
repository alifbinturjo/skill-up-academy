<<<<<<< HEAD
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notices</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../style.css">
=======
<?php
require_once '../auth/cnct.php';
session_start();

// For testing without login (remove in production)
if (!isset($_SESSION['user_type'])) {
    $_SESSION['user_type'] = 'student'; 
}
if (!isset($_SESSION['course_id'])) {
    $_SESSION['course_id'] = 1; // Simulated course ID
}

$notices = [];

// 1. Admin notices visible to students
$admin_query = "SELECT title, message, date, 'Admin' AS source FROM admin_notices 
                WHERE audience IN ('student', 'all')";
$admin_result = $conn->query($admin_query);
if ($admin_result && $admin_result->num_rows > 0) {
    while ($row = $admin_result->fetch_assoc()) {
        $row['course_name'] = '';  // Admin notices not linked to any course
        $notices[] = $row;
    }
}

// 2. Instructor notices for student's course with course name join
$instructor_query = "
    SELECT instructors_notices.*, 'Instructor' AS source, courses.title AS course_name 
    FROM instructors_notices 
    JOIN courses ON instructors_notices.c_id = courses.c_id
    WHERE instructors_notices.c_id = {$_SESSION['course_id']}
";
$instructor_result = $conn->query($instructor_query);
if ($instructor_result && $instructor_result->num_rows > 0) {
    while ($row = $instructor_result->fetch_assoc()) {
        $notices[] = $row;
    }
}

// Sort all notices by date DESC
usort($notices, function($a, $b) {
    return strtotime($b['date']) <=> strtotime($a['date']);
});

// Check for red dot notification
$hasNewNotice = isset($_SESSION['new_notice']);
unset($_SESSION['new_notice']); // Reset after viewing
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Notices - SkillUp Academy</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" />
  <link rel="stylesheet" href="../style.css" />
>>>>>>> fbe117958de6045b5583d0919310a59a8d8f1ca9
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-blur sticky-top shadow-sm">
  <div class="container-fluid">
<<<<<<< HEAD
    <a class="navbar-brand fw-bold" href="">SkillUp Academy</a>
=======
    <a class="navbar-brand fw-bold" href="#">SkillUp Academy</a>
>>>>>>> fbe117958de6045b5583d0919310a59a8d8f1ca9
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
<<<<<<< HEAD
  
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link " href="dashboard.html">Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="courses.html">Courses</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active " href="">Notices</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="profile.html">Profile</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="">Logout</a>
        </li>
=======

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="courses.php">Courses</a></li>
        <li class="nav-item">
          <a class="nav-link active position-relative" href="notices.php">
            Notices
            <?php if ($hasNewNotice): ?>
              <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                <span class="visually-hidden">New notice</span>
              </span>
            <?php endif; ?>
          </a>
        </li>
        <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
        <li class="nav-item"><a class="nav-link" href="../auth/logout.php">Logout</a></li>
>>>>>>> fbe117958de6045b5583d0919310a59a8d8f1ca9
      </ul>
    </div>
  </div>
</nav>

<<<<<<< HEAD


  <div class="container mt-5 mb-5">
    <p class="text-center fs-1 mb-4">Notices</p>

    <!-- Notification Card -->
<div class="card shadow bg-transparent card-h mb-5">
  <div class="card-body">
    <h5 class="card-title">System Maintenance</h5>
    <p class="card-text">We will perform scheduled maintenance on the system tonight from 12:00 AM to 2:00 AM. During this time, access may be temporarily unavailable.</p>
    <p class="text-muted small">June 27, 2025 • 11:30 AM</p>
  </div>
</div>

<!-- Duplicate Notification Card -->
<div class="card shadow bg-transparent card-h mb-5">
  <div class="card-body">
    <h5 class="card-title">New Course Added</h5>
    <p class="card-text">A new course titled "Advanced Machine Learning" has been added to the Computer Science department for the upcoming semester.</p>
    <p class="text-muted small">June 26, 2025 • 3:45 PM</p>
  </div>
</div>


  </div>

=======
<div class="container mt-5 mb-5">
  <h1 class="text-center mb-4">Notices</h1>

  <?php if (empty($notices)): ?>
    <div class="alert alert-info text-center">
      No notices available at this time.
    </div>
  <?php else: ?>
    <?php foreach ($notices as $notice): ?>
      <div class="card shadow bg-transparent mb-4">
        <div class="card-body">
          <h5 class="card-title"><?= htmlspecialchars($notice['title']) ?></h5>
          <p class="card-text"><?= nl2br(htmlspecialchars($notice['message'])) ?></p>
          <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted">
              <?= date('F j, Y \a\t g:i A', strtotime($notice['date'])) ?>
            </small>
            <?php if ($notice['source'] === 'Instructor'): ?>
              <span class="badge bg-info text-dark"><?= htmlspecialchars($notice['course_name']) ?></span>
            <?php else: ?>
              <span class="badge bg-secondary"><?= htmlspecialchars($notice['source']) ?></span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
>>>>>>> fbe117958de6045b5583d0919310a59a8d8f1ca9
</body>
</html>
