<?php
session_start();
require_once '../auth/cnct.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Student') {
  $_SESSION['error'] = "Unauthorized access. Please log in as a student.";
  header("Location: ../auth/login.php");
  exit();
}

$student_id = $_SESSION['u_id'];
$notices = [];
$courses = [];

// 1. Get student's enrolled course IDs from student_courses table
$course_query = $conn->prepare("SELECT c_id FROM student_courses WHERE u_id = ?");
$course_query->bind_param("i", $student_id);
$course_query->execute();
$course_result = $course_query->get_result();

while ($row = $course_result->fetch_assoc()) {
  $courses[] = (int)$row['c_id'];
}

// 2. Get admin notices for students
$admin_query = "SELECT title, message, date, 'Admin' AS source FROM admin_notices 
                WHERE audience IN ('student', 'everyone')";
$admin_result = $conn->query($admin_query);
if ($admin_result && $admin_result->num_rows > 0) {
  while ($row = $admin_result->fetch_assoc()) {
    $row['course_name'] = ''; // Admin notices aren't tied to a course
    $notices[] = $row;
  }
}

// 3. Get instructor notices for student's courses (if any)
if (!empty($courses)) {
  $in_clause = implode(",", array_map('intval', $courses)); // safe list of ids
  $instructor_query = "
      SELECT instructors_notices.*, 'Instructor' AS source, courses.title AS course_name 
      FROM instructors_notices 
      JOIN courses ON instructors_notices.c_id = courses.c_id
      WHERE instructors_notices.c_id IN ($in_clause)";
  $instructor_result = $conn->query($instructor_query);

  if ($instructor_result && $instructor_result->num_rows > 0) {
    while ($row = $instructor_result->fetch_assoc()) {
      $notices[] = $row;
    }
  }
}

// Sort all notices by date DESC
usort($notices, function ($a, $b) {
  return strtotime($b['date']) <=> strtotime($a['date']);
});

// Red dot notification
$hasNewNotice = isset($_SESSION['new_notice']);
unset($_SESSION['new_notice']);
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
</head>

<body>

  <nav class="navbar navbar-expand-lg navbar-blur sticky-top shadow-sm">
    <div class="container-fluid">
      <a class="navbar-brand fw-bold" href="#">SkillUp Academy</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

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
        </ul>
      </div>
    </div>
  </nav>

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
                <?= date('F j, Y', strtotime($notice['date'])) ?>
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
</body>

</html>
