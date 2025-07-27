<?php
session_start();
include '../auth/cnct.php';

// Only allow instructors
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Instructor') {
  session_unset();
  session_destroy();
  header("Location: ../index.php");
  exit;
}

// Fetch admin notices for instructors or everyone
$admin_notices = $conn->query("
  SELECT title, message, date 
  FROM admin_notices 
  WHERE audience = 'instructor' OR audience = 'everyone'
  ORDER BY date DESC
");

// Fetch all instructor notices
$instructors_notices = $conn->query("
  SELECT title, message, date 
  FROM instructors_notices 
  ORDER BY date DESC
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Notices</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" />
  <link rel="stylesheet" href="../style.css" />
</head>

<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-blur sticky-top shadow-sm">
    <div class="container-fluid">
      <a class="navbar-brand fw-bold" href="#">SkillUp Academy</a>
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
            <a class="nav-link" href="courses.php">Courses</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="notices.php">Notices</a>
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

  <!-- Notices Section -->
  <div class="container mt-5 mb-5">
    <p class="text-center fs-1 mb-4">Notices</p>

    <!-- Admin Notices -->
    <?php
    if ($admin_notices && $admin_notices->num_rows > 0) {
      while ($row = $admin_notices->fetch_assoc()) {
        echo '
        <div class="card shadow bg-transparent card-h mb-4">
          <div class="card-body">
            <h5 class="card-title">' . htmlspecialchars($row["title"]) . ' <span class="badge bg-primary">Admin</span></h5>
            <p class="card-text">' . htmlspecialchars($row["message"]) . '</p>
            <p class="text-muted small">' . htmlspecialchars($row["date"]) . '</p>
          </div>
        </div>';
      }
    }
    ?>

    <!-- Instructor Notices -->
    <?php
    if ($instructors_notices && $instructors_notices->num_rows > 0) {
      while ($row = $instructors_notices->fetch_assoc()) {
        echo '
        <div class="card shadow bg-transparent card-h mb-4">
          <div class="card-body">
            <h5 class="card-title">' . htmlspecialchars($row["title"]) . ' <span class="badge bg-success">Instructor</span></h5>
            <p class="card-text">' . htmlspecialchars($row["message"]) . '</p>
            <p class="text-muted small">' . htmlspecialchars($row["date"]) . '</p>
          </div>
        </div>';
      }
    } else {
      echo '<p class="text-center text-muted">No instructor notices found.</p>';
    }
    ?>

  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
