<?php
include'../auth/cnct.php';
session_start();

if(!isset($_SESSION['role']) && $_SESSION['role']!=="Admin"){
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
    <title>Admin Dashboard</title>
    <link rel="prefetch" href="../image-assets/common/fav.webp" as="image">
    <link rel="icon" href="../image-assets/common/fav.webp" type="image/webp">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    
    <link rel="preload" href="../style.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="../style.css"></noscript>
    <style>
        .welcome-card {
            background: linear-gradient(135deg, #5e72e4 0%, #2d3b8b 100%);
            border-radius: 10px;
            color: white;
        }
        .stat-card {
            transition: all 0.3s ease;
            border-radius: 10px;
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            height: 100%;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1.5rem 0 rgba(58, 59, 69, 0.2);
        }
        .stat-icon {
            font-size: 1.75rem;
            opacity: 0.8;
        }
        .badge-admin {
            background-color: rgba(255, 255, 255, 0.2);
            font-size: 0.9rem;
            font-weight: 500;
        }
        .arrow-btn {
            width: 36px;
            height: 36px;
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
$stmt_name = $conn->prepare("SELECT name FROM users WHERE u_id = ?");
$stmt_name->bind_param("i", $u_id);

$stmt_level = $conn->prepare("SELECT level FROM admins WHERE u_id = ?");
$stmt_level->bind_param("i", $u_id);

$stmt_offered = $conn->prepare("SELECT COUNT(*) FROM courses WHERE status = 'offered'");
$stmt_started = $conn->prepare("SELECT COUNT(*) FROM courses WHERE status = 'started'");

$stmt_notices = $conn->prepare("SELECT COUNT(*) FROM admin_notices");

$stmt_junior = $conn->prepare("SELECT COUNT(*) FROM instructors WHERE title = 'junior'");
$stmt_instructor = $conn->prepare("SELECT COUNT(*) FROM instructors WHERE title = 'instructor'");
$stmt_senior = $conn->prepare("SELECT COUNT(*) FROM instructors WHERE title = 'senior'");

$stmt_l0 = $conn->prepare("SELECT COUNT(*) FROM admins WHERE level = 0");
$stmt_l1 = $conn->prepare("SELECT COUNT(*) FROM admins WHERE level = 1");

$stmt_students = $conn->prepare("SELECT COUNT(*) FROM students");

try{
  
  $stmt_name->execute();
  $stmt_name->bind_result($name);
  $stmt_name->fetch();
  $stmt_name->close();

  
  $stmt_level->execute();
  $stmt_level->bind_result($level);
  $stmt_level->fetch();
  $stmt_level->close();

  
  $stmt_offered->execute();
  $stmt_offered->bind_result($offered);
  $stmt_offered->fetch();
  $stmt_offered->close();

  $stmt_started->execute();
  $stmt_started->bind_result($started);
  $stmt_started->fetch();
  $stmt_started->close();

  
  $stmt_notices->execute();
  $stmt_notices->bind_result($notice_count);
  $stmt_notices->fetch();
  $stmt_notices->close();

  
  $stmt_junior->execute();
  $stmt_junior->bind_result($junior);
  $stmt_junior->fetch();
  $stmt_junior->close();

  $stmt_instructor->execute();
  $stmt_instructor->bind_result($instructor);
  $stmt_instructor->fetch();
  $stmt_instructor->close();

  $stmt_senior->execute();
  $stmt_senior->bind_result($senior);
  $stmt_senior->fetch();
  $stmt_senior->close();

  
  $stmt_l0->execute();
  $stmt_l0->bind_result($l0);
  $stmt_l0->fetch();
  $stmt_l0->close();

  $stmt_l1->execute();
  $stmt_l1->bind_result($l1);
  $stmt_l1->fetch();
  $stmt_l1->close();

  
  $stmt_students->execute();
  $stmt_students->bind_result($total_students);
  $stmt_students->fetch();
  $stmt_students->close();

  $conn->close();
}
catch(Exception $e){
  $stmt_name->close();
  $stmt_level->close();
  $stmt_offered->close();
  $stmt_started->close();
  $stmt_notices->close();
  $stmt_junior->close();
  $stmt_instructor->close();
  $stmt_senior->close();
  $stmt_l0->close();
  $stmt_l1->close();
  $stmt_students->close();

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
                            <span class="badge badge-admin py-2 px-3">
                                <i class="bi bi-shield-lock me-1"></i><?php echo $_SESSION['role']; ?>
                            </span>
                            <span class="badge badge-admin py-2 px-3">
                                <i class="bi bi-star me-1"></i>Level <?php echo $level ?>
                            </span>
                        </div>
                    </div>
                    <a href="profile.php" class="btn btn-light btn-lg px-4">
                        <i class="bi bi-person-circle me-2"></i>Profile
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4">
        <!-- Courses Card -->
        <div class="col-md-4 col-lg-2">
            <div class="stat-card bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="stat-icon text-primary">
                        <i class="bi bi-book"></i>
                    </div>
                    <a href="courses.php" class="btn btn-primary arrow-btn rounded-circle">
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <h5 class="fw-bold mb-1">Courses</h5>
                <p class="mb-1"><small>Offered: <?php echo $offered ?></small></p>
                <p class="mb-0"><small>Ongoing: <?php echo $started ?></small></p>
            </div>
        </div>

        <!-- Notices Card -->
        <div class="col-md-4 col-lg-2">
            <div class="stat-card bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="stat-icon text-secondary">
                        <i class="bi bi-megaphone"></i>
                    </div>
                    <a href="post-notices.php" class="btn btn-secondary arrow-btn rounded-circle">
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <h5 class="fw-bold mb-1">Notices</h5>
                <p class="mb-0"><small>Posted: <?php echo $notice_count ?></small></p>
            </div>
        </div>

        <!-- Instructors Card -->
        <div class="col-md-4 col-lg-2">
            <div class="stat-card bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="stat-icon text-success">
                        <i class="bi bi-person-lines-fill"></i>
                    </div>
                    <a href="instructors.php" class="btn btn-success arrow-btn rounded-circle">
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <h5 class="fw-bold mb-1">Instructors</h5>
                <p class="mb-1"><small>Junior: <?php echo $junior ?></small></p>
                <p class="mb-1"><small>Instructor: <?php echo $instructor ?></small></p>
                <p class="mb-0"><small>Senior: <?php echo $senior ?></small></p>
            </div>
        </div>

        <!-- Admins Card -->
        <div class="col-md-4 col-lg-2">
            <div class="stat-card bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="stat-icon text-info">
                        <i class="bi bi-person-gear"></i>
                    </div>
                    <a href="admins.php" class="btn btn-info arrow-btn rounded-circle">
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <h5 class="fw-bold mb-1">Admins</h5>
                <p class="mb-1"><small>Level 0: <?php echo $l0 ?></small></p>
                <p class="mb-0"><small>Level 1: <?php echo $l1 ?></small></p>
            </div>
        </div>

        <!-- Students Card -->
        <div class="col-md-4 col-lg-2">
            <div class="stat-card bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="stat-icon text-danger">
                        <i class="bi bi-people"></i>
                    </div>
                    <a href="students.php" class="btn btn-danger arrow-btn rounded-circle">
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <h5 class="fw-bold mb-1">Students</h5>
                <p class="mb-0"><small>Total: <?php echo $total_students ?></small></p>
            </div>
        </div>
        
        <!-- Quick Actions Card -->
        <div class="col-md-4 col-lg-2">
            <div class="stat-card bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="stat-icon text-warning">
                        <i class="bi bi-lightning"></i>
                    </div>
                </div>
                <h5 class="fw-bold mb-3">Quick Actions</h5>
                <div class="d-grid gap-2">
                    <a href="courses.php" class="btn btn-sm btn-outline-primary">Add Course</a>
                    <a href="instructors.php" class="btn btn-sm btn-outline-success">Add Instructors</a>
                    <a href="post-notices.php" class="btn btn-sm btn-outline-secondary">Post Notice</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>