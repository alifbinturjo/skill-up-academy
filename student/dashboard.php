<?php
include'../auth/cnct.php';
session_start();

if(!isset($_SESSION['role'])&&isset($_SESSION['u_id'])&&$_SESSION['role']!=="Student"){
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
  <title>Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../style.css">
  <style>
    .welcome-card {
      background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
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
<?php
$stmt_n = $conn->prepare("SELECT n_status FROM students WHERE u_id = ?");
$stmt_n->bind_param("i", $u_id);

try{
$stmt_n->execute();
$stmt_n->bind_result($n_status);
$stmt_n->fetch();
$stmt_n->close();
}
catch(Exception $e){
  $stmt_n->close();
  $conn->close();
  header("Location: ../auth/logout.php");
  exit();
}
?>
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
$stmt_name=$conn->prepare("SELECT name FROM users WHERE u_id = ?");
$stmt_name->bind_param("i", $u_id);
$stmt_taken=$conn->prepare("SELECT COUNT(*) FROM enrolls 
  JOIN courses ON enrolls.c_id = courses.c_id 
  WHERE enrolls.u_id = ? AND (courses.status IS NULL OR courses.status != 'ended')");
$stmt_taken->bind_param("i", $u_id);
$stmt_past=$conn->prepare("SELECT COUNT(*) FROM enrolls 
  JOIN courses ON enrolls.c_id = courses.c_id 
  WHERE enrolls.u_id = ? AND courses.status = 'ended'");
$stmt_past->bind_param("i", $u_id);
$stmt_platform=$conn->prepare("SELECT COUNT(*) FROM admin_notices WHERE audience = 'student' or audience='everyone'");
$stmt_courses=$conn->prepare("SELECT COUNT(DISTINCT instructors_notices.n_id) 
  FROM instructors_notices
  JOIN enrolls ON instructors_notices.c_id = enrolls.c_id
  WHERE enrolls.u_id = ?");
$stmt_courses->bind_param("i", $u_id);

try{
  $stmt_name->execute();
  $stmt_name->bind_result($name);
  $stmt_name->fetch();
  $stmt_name->close();

  $stmt_taken->execute();
  $stmt_taken->bind_result($taken);
  $stmt_taken->fetch();
  $stmt_taken->close();

  $stmt_past->execute();
  $stmt_past->bind_result($past);
  $stmt_past->fetch();
  $stmt_past->close();

  $stmt_platform->execute();
  $stmt_platform->bind_result($platform);
  $stmt_platform->fetch();
  $stmt_platform->close();

  $stmt_courses->execute();
  $stmt_courses->bind_result($courses);
  $stmt_courses->fetch();
  $stmt_courses->close();

  $conn->close();
}
catch(Exception $e){
  $stmt_name->close();
  $stmt_courses->close();
  $stmt_past->close();
  $stmt_platform->close();
  $stmt_taken->close();

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
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h1 class="display-5 fw-bold mb-3">Welcome, <?php echo $name ?>!</h1>
              <p class="lead mb-0">You're logged in as a <strong><?php echo $_SESSION['role']; ?></strong></p>
            </div>
            <a href="profile.php" class="btn btn-light btn-lg px-4">
              <i class="bi bi-person-circle me-2"></i>View Profile
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Info Cards Section -->
    <div class="row g-4">
      <!-- Courses Card -->
      <div class="col-md-6">
        <div class="info-card h-100 p-4 bg-white">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="fw-bold text-primary">
              <i class="bi bi-book me-2"></i>Your Courses
            </h3>
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
                <h2 class="fw-bold"><?php echo $past ?></h2>
                <p class="text-muted mb-0">Completed</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Notices Card -->
      <!-- In the Notices Card section of student dashboard -->
<div class="col-md-6">
  <div class="info-card h-100 p-4 bg-white">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h3 class="fw-bold text-secondary">
          <i class="bi bi-megaphone me-2"></i>Your Notices
          <?php if ($n_status === "unread"): ?>
            <span class="badge bg-danger ms-2">New</span>
          <?php endif; ?>
        </h3>
      </div>
      <a href="notices.php" class="btn btn-secondary arrow-btn rounded-circle">
        <i class="bi bi-arrow-right"></i>
      </a>
    </div>
    <div class="row text-center">
      <div class="col-6">
        <div class="p-3">
          <h2 class="fw-bold"><?php echo $platform ?></h2>
          <p class="text-muted mb-0">Platform</p>
        </div>
      </div>
      <div class="col-6">
        <div class="p-3">
          <h2 class="fw-bold"><?php echo $courses ?></h2>
          <p class="text-muted mb-0">Course</p>
        </div>
      </div>
    </div>
  </div>
</div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q"
    crossorigin="anonymous"></script>
</body>
</html>