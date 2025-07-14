<?php
include 'auth/cnct.php';
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkillUp Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
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
          <a class="nav-link active" href="">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="courses.php">Courses</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="instructors.php">Instructors</a>
        </li>
        
        <?php
          if(isset($_SESSION['role'])){
            echo'<li class="nav-item">';
            if($_SESSION['role']==="Student")
              echo '<a class="nav-link" href="student/dashboard.php">Dashboard</a> </li>';
            else if($_SESSION['role']==="Instructor")
              echo '<a class="nav-link" href="instructor/dashboard.php">Dashboard</a> </li>';
            else
              echo '<a class="nav-link" href="admin/dashboard.php">Dashboard</a> </li>';

            echo'<li class="nav-item">
                  <a class="nav-link" href="auth/logout.php">Logout</a>
                  </li>';
          }
          else{
            echo '<a class="nav-link" href="auth/login.php">Login</a> </li>
                  <li class="nav-item">
                  <a class="nav-link" href="auth/signup.php">Signup</a>
                  </li>';
          }
        ?>
      </ul>
    </div>
  </div>
</nav>

<section class="vh-100 hero-section d-flex align-items-center">
  <div class="container card-h shadow-lg p-5 rounded">
    <div class="hero-text">
      <h1 class="fw-bold" id="hero-text"></h1>
      <p class="lead">Learn new skills and boost your career with us...</p>
      <a href="auth/signup.html" class="btn btn-outline-dark">Get Started</a>
      <span>Or</span>
      <a href="#explore" class="btn btn-outline-dark">Explore</a>
    </div>
  </div>
</section>

<?php

$stmt_student = $conn->prepare("SELECT COUNT(*) FROM students");
$stmt_instructor = $conn->prepare("SELECT COUNT(*) FROM instructors");
$stmt_domain = $conn->prepare("SELECT COUNT(DISTINCT domain) FROM courses");

try{
$stmt_student->execute();
$stmt_student->bind_result($studentCount);
$stmt_student->fetch();
$stmt_student->close();

$stmt_instructor->execute();
$stmt_instructor->bind_result($instructorCount);
$stmt_instructor->fetch();
$stmt_instructor->close();

$stmt_domain->execute();
$stmt_domain->bind_result($domainCount);
$stmt_domain->fetch();
$stmt_domain->close();

$conn->close();
}
catch(Exception $e){
  $stmt_student->close();
  $stmt_instructor->close();
  $stmt_domain->close();
  $conn->close();
  header("Location: ops.php");
  exit();
}

?>

<section id="explore" class="py-5">
  <div class="container">
    <div class="mb-5">
      <h2 class="fw-bold">Explore SkillUp Academy</h2>
      <p class="lead">Discover what makes us unique in skill development.</p>
  </div>

  <div class="row justify-content-center text-center mb-5">
      <div class="col-md-4">
        <div class="card card-h h-100 shadow border-0 p-4 bg-transparent">
          <h3 class="fw-bold counter" data-target="<?php echo $studentCount  ?>">0</h3>
        <p>Students Trained</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card card-h h-100 shadow border-0 p-4 bg-transparent">
          <h3 class="fw-bold counter" data-target="<?php echo $instructorCount  ?>">0</h3>
        <p>Expert Instructors</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card card-h h-100 shadow border-0 p-4 bg-transparent">
          <h3 class="fw-bold counter" data-target="<?php echo $domainCount  ?>">0</h3>
        <p>Domains</p>
        </div>
      </div>
    </div>

    <div class="mb-5">
      <h3 class="mb-3 text-center fw-bold">Featured Domains</h4>
      <div class="row justify-content-center text-center mb-5">
      <div class="col-md-3 mb-3">
        <div class="card card-h h-100 shadow-sm border-1 p-4 bg-transparent">
          <h5 class="fw-bold">Programming</h5>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="card card-h h-100 shadow-sm border-1 p-4 bg-transparent">
          <h5 class="fw-bold">Problem solving</h5>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="card card-h h-100 shadow-sm border-1 p-4 bg-transparent">
          <h5 class="fw-bold">Web development</h5>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="card card-h h-100 shadow-sm border-1 p-4 bg-transparent">
          <h5 class="fw-bold">Android development</h5>
        </div>
      </div>
    </div>
    </div>

    <div class="mb-5">
      <h3 class="mb-3 fw-bold text-center">How we faciliate</h4>
      <div class="row justify-content-center mb-5">
      <div class="col-md-12 mb-2">
        <div class="card card-h h-100 shadow-sm border-0 p-4 bg-transparent">
          <h5>Interactive Live Classes</h5>
        </div>
      </div>
      <div class="col-md-12 mb-2">
        <div class="card card-h h-100 shadow-sm border-0 p-4 bg-transparent">
          <h5>Real-world Projects</h5>
        </div>
      </div>
      <div class="col-md-12 mb-2">
        <div class="card card-h h-100 shadow-sm border-0 p-4 bg-transparent">
          <h5>Job Placement Assistance</h5>
        </div>
      </div>
      <div class="col-md-12 mb-2">
        <div class="card card-h h-100 shadow-sm border-0 p-4 bg-transparent">
          <h5>Mentorship from Industry Experts</h5>
        </div>
      </div>
      <div class="col-md-12 mb-2">
        <div class="card card-h h-100 shadow-sm border-0 p-4 bg-transparent">
          <h5>24/7 Access to Course Materials</h5>
        </div>
      </div>
    </div>
    </div>

    <div class="mb-5 text-center">
      <a href="courses.php" class="btn btn-outline-dark">View Courses</a>
      <a href="instructors.php" class="btn btn-outline-dark ms-5">View Instructors</a>
    </div>
</section>
    <script src="common/common.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
</body>
<footer class="bg-dark text-white pt-5 pb-4">
  <div class="container text-md-left">
    <div class="row text-center text-md-left">

      <div class="col-md-6 col-lg-6 col-xl-6 mx-auto mt-3">
        <h5 class="mb-4 fw-bold">SkillUp Academy</h5>
        <p>Empowering learners with the skills they need to succeed in the digital world.</p>
        <a href="policies.html" class="text-white text-decoration-none">Academy policies &rarr;</a>
      </div>

      <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
        <h5 class="mb-4 fw-bold">Contact</h5>
        <p><i class="bi bi-envelope me-2"></i> support@skillup.com</p>
        <p><i class="bi bi-phone me-2"></i> +880 1234-567890</p>
        <p><i class="bi bi-geo-alt me-2"></i> Dhaka, Bangladesh</p>
      </div>

      <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
        <h5 class="mb-4 fw-bold">Follow Us</h5>
        <a href="#" class="text-white me-3"><i class="bi bi-facebook"></i></a>
        <a href="#" class="text-white me-3"><i class="bi bi-twitter"></i></a>
        <a href="#" class="text-white me-3"><i class="bi bi-linkedin"></i></a>
        <a href="#" class="text-white"><i class="bi bi-youtube"></i></a>
      </div>

    </div>

     <hr class="my-3">

    <div class="text-center">
      <p class="mb-0">&copy; 2025 SkillUp Academy. All rights reserved.</p>
    </div>

  </div>
</footer>
</html>