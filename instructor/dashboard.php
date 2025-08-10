<?php
session_start();
include'../auth/cnct.php';


if(!isset($_SESSION['role']) && $_SESSION['role']!=="Instructor"){
  session_unset();
  session_destroy();
  $conn->close();
  header("Location: ../index.php");
  exit();
}
$u_id=$_SESSION['u_id'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Instructor Dashboard</title>
  <link rel="prefetch" href="../image-assets/common/fav.webp" as="image">
    <link rel="icon" href="../image-assets/common/fav.webp" type="image/webp">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  
  <link rel="preload" href="../style.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="../style.css"></noscript>

<link rel="prefetch" href="../image-assets/common/fav.webp" as="image">
    <link rel="icon" href="../image-assets/common/fav.webp" type="image/webp">

  <style>
    .welcome-card {
      background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
      border-radius: 10px;
      color: white;
    }
    .info-card {
      transition: all 0.3s ease;
      border-radius: 10px;
      border: none;
      box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
    }
    .info-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 0.5rem 1.5rem 0 rgba(58, 59, 69, 0.2);
    }
    .arrow-btn {
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .badge-instructor {
      background-color: rgba(255, 255, 255, 0.2);
      font-size: 0.9rem;
      font-weight: 500;
    }
    .course-table th {
      background-color: #f8f9fa;
      font-weight: 600;
    }
    .new-badge {
      font-size: 0.75rem;
      vertical-align: middle;
    }
  </style>
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
          <li class="nav-item">
            <a class="nav-link" href="../index.php">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="">Dashboard</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="../auth/logout.php">Logout</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <?php
$stmt_name = $conn->prepare("SELECT users.name, instructors.title FROM users JOIN instructors ON users.u_id = instructors.u_id WHERE users.u_id = ?");
$stmt_name->bind_param("i", $u_id);

$stmt_taken = $conn->prepare("SELECT COUNT(*) FROM courses WHERE u_id = ? AND (status IS NULL OR status != 'ended')");
$stmt_taken->bind_param("i", $u_id);

$stmt_previous = $conn->prepare("SELECT COUNT(*) FROM courses WHERE u_id = ? AND status = 'ended'");
$stmt_previous->bind_param("i", $u_id);

$stmt_notices = $conn->prepare("SELECT COUNT(*) FROM admin_notices WHERE audience = 'instructor' or audience='everyone'");

// Get recent courses
$stmt_recent_courses = $conn->prepare("
    SELECT c_id, title, domain, start_date 
    FROM courses 
    WHERE u_id = ? 
    ORDER BY start_date DESC 
    LIMIT 3
");
$stmt_recent_courses->bind_param("i", $u_id);

try {
    $stmt_name->execute();
    $stmt_name->bind_result($name, $title);
    $stmt_name->fetch();
    $stmt_name->close();

    $stmt_taken->execute();
    $stmt_taken->bind_result($taken);
    $stmt_taken->fetch();
    $stmt_taken->close();

    $stmt_previous->execute();
    $stmt_previous->bind_result($previous);
    $stmt_previous->fetch();
    $stmt_previous->close();

    $stmt_notices->execute();
    $stmt_notices->bind_result($platform);
    $stmt_notices->fetch();
    $stmt_notices->close();

    $stmt_recent_courses->execute();
    $recent_courses = $stmt_recent_courses->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_recent_courses->close();

    $conn->close();
} catch(Exception $e) {
    $stmt_name->close();
    $stmt_taken->close();
    $stmt_previous->close();
    $stmt_notices->close();
    $stmt_recent_courses->close();
    $conn->close();
    header("Location: ../auth/logout.php");
    exit();
}
  ?>

  <div class="container py-5">
    <!-- Welcome Section -->
    <div class="row mb-5">
      <div class="col-12">
        <div class="welcome-card p-4 shadow">
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
            <div class="mb-3 mb-md-0">
              <h1 class="display-5 fw-bold mb-2">Welcome, <?php echo $name ?>!</h1>
              <div class="d-flex flex-wrap gap-2">
                <span class="badge badge-instructor py-2 px-3">
                  <?php echo $_SESSION['role']; ?>
                </span>
                <span class="badge badge-instructor py-2 px-3">
                  <?php echo "Title: " .ucfirst($title) ?>
                </span>
              </div>
            </div>
            <a href="profile.php" class="btn btn-light btn-lg px-4">
              View Profile
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Info Cards Section -->
    <div class="row g-4 mb-5">
      <!-- Courses Card -->
      <div class="col-md-6">
        <div class="info-card h-100 p-4 bg-white">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="fw-bold">Your Courses</h3>
            <a href="courses.php" class="btn btn-primary arrow-btn rounded-circle">
              <i class="bi bi-arrow-right"></i>
            </a>
          </div>
          <div class="row text-center">
            <div class="col-6">
              <div class="p-3">
                <h2 class="fw-bold"><?php echo $taken ?></h2>
                <p class="text-muted mb-0">Active</p>
              </div>
            </div>
            <div class="col-6">
              <div class="p-3">
                <h2 class="fw-bold"><?php echo $previous ?></h2>
                <p class="text-muted mb-0">Completed</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Notices Card -->
      <div class="col-md-6">
        <div class="info-card h-100 p-4 bg-white">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="fw-bold">
              Platform Notices
              
            </h3>
            <a href="notices.php" class="btn btn-secondary arrow-btn rounded-circle">
              <i class="bi bi-arrow-right"></i>
            </a>
          </div>
          <div class="text-center py-4">
            <h2 class="fw-bold"><?php echo $platform ?></h2>
            <p class="text-muted mb-0">New announcements</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Courses Section -->
    <div class="row">
      <div class="col-12">
        <div class="info-card p-4 bg-white">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold">Recent Courses</h3>
            <a href="courses.php" class="btn btn-outline-primary">
              View All
            </a>
          </div>
          
          <?php if (!empty($recent_courses)): ?>
            <div class="table-responsive">
              <table class="table table-hover course-table">
                <thead>
                  <tr>
                    <th width="40%">Course Title</th>
                    <th width="30%">Domain</th>
                    <th width="30%">Start Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($recent_courses as $course): ?>
                    <tr>
                      <td><?php echo $course['title'] ?></td>
                      <td>
                        <span class="badge bg-primary bg-opacity-10 text-primary">
                          <?php echo ucfirst(str_replace('-', ' ', $course['domain'])) ?>
                        </span>
                      </td>
                      <td><?php echo date('M d, Y', strtotime($course['start_date'])) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <div class="text-center py-4">
              <i class="bi bi-book text-muted" style="font-size: 3rem;"></i>
              <p class="text-muted mt-3">You haven't created any courses yet</p>
              <a href="courses.php" class="btn btn-primary">Create Course</a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>