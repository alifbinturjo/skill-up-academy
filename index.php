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
    <link rel="prefetch" href="image-assets/common/fav.webp" as="image">
    <link rel="icon" href="image-assets/common/fav.webp" type="image/webp">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    
<link rel="preload" href="style.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="style.css"></noscript>

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
          <p>More than</p>
          <h3 class="fw-bold counter" data-target="<?php echo $studentCount  ?>">0</h3>
        <p>Students Trained</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card card-h h-100 shadow border-0 p-4 bg-transparent">
          <p>More than</p>
          <h3 class="fw-bold counter" data-target="<?php echo $instructorCount  ?>">0</h3>
        <p>Expert Instructors</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card card-h h-100 shadow border-0 p-4 bg-transparent">
          <p>More than</p>
          <h3 class="fw-bold counter" data-target="<?php echo $domainCount  ?>">0</h3>
        <p>Domains</p>
        </div>
      </div>
    </div>

    

    <?php

$stmt = $conn->prepare("SELECT domain, COUNT(*) as total_courses FROM courses GROUP BY domain ORDER BY total_courses DESC LIMIT 5");
try{
$stmt->execute();
}
catch(Exception $e){
  $stmt->close();
  $conn->close();
  session_destroy();
  header("Location: ops.php");
  exit();
}

$result = $stmt->get_result();
?>

<div class="mb-5">
  <h3 class="mb-3 text-center fw-bold">Featured Domains</h3>
  <div class="d-flex flex-wrap justify-content-center gap-2">
    <?php while ($row = $result->fetch_assoc()) { ?>
      <span class="card-h badge bg-primary fs-6 fw-semibold px-3 py-3 m-2">
        <?php echo htmlspecialchars(ucfirst($row['domain'])); ?>
      </span>
    <?php } ?>
    <?php $stmt->close(); ?>
  </div>
</div>


<?php

$stmt = $conn->prepare("
    SELECT c.*, u.name AS instructor, COALESCE(AVG(e.rating), 0) AS avg_rating
    FROM courses c
    JOIN users u ON c.u_id = u.u_id
    LEFT JOIN enrolls e ON c.c_id = e.c_id
    WHERE c.status = 'offered'
    GROUP BY c.c_id
    ORDER BY avg_rating DESC
    LIMIT 3
");

try {
    $stmt->execute();
} catch(Exception $e) {
    $stmt->close();
    $conn->close();
    session_destroy();
    header("Location: ops.php");
    exit();
}

$result = $stmt->get_result();
$courses = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div class="mb-5">
    <h3 class="mb-3 fw-bold text-center">Featured Offered Courses</h3>
    <div class="row justify-content-center">
        <?php foreach ($courses as $course): ?>
            <div class="col-md-4 mb-4">
                <div class="card card-h h-100 shadow-sm border-1 p-4" style="background-color: rgba(169, 169, 169, 0.356);">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($course['title']) ?></h5>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="badge bg-primary text-light"><?= htmlspecialchars(ucfirst($course['domain'])) ?></span>
                            <span class="text-muted"><?= htmlspecialchars(ceil($course['duration'])) ?> Weeks</span>
                        </div>
                        <hr class="divider my-2">
                        <p class="card-text"><?= htmlspecialchars($course['description']) ?></p>
                        <p class="text-muted"><small>Instructor: <?= htmlspecialchars($course['instructor']) ?></small></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="course-details/full-stack-web-dev.php" class="btn btn-md btn-outline-dark">View Details</a>
                            <span class="badge bg-success">Offered</span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <div class="col md-12 text-center">
          <a href="courses.php" class="text-dark text-decoration-none fw-bold ">View all courses &rarr;</a>
        </div>
    </div>
</div>

<?php
$stmt = $conn->prepare("
    SELECT 
      i.u_id, 
      u.name, 
      i.image, 
      i.bio, 
      i.domain,
      COALESCE(AVG(e.rating), 0) AS avg_rating
    FROM instructors i
    JOIN users u ON i.u_id = u.u_id
    LEFT JOIN courses c ON i.u_id = c.u_id
    LEFT JOIN enrolls e ON c.c_id = e.c_id
    GROUP BY i.u_id
    ORDER BY avg_rating DESC
    LIMIT 3
");

try {
    $stmt->execute();
    $result = $stmt->get_result();
    $instructors = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    if (isset($stmt)) $stmt->close();
    $conn->close();
    session_destroy();
    header("Location: ops.php");
    exit();
}
?>


<div class="mb-5">
  <h3 class="mb-3 text-center fw-bold">Top Rated Instructors</h3>
  <div class="row justify-content-center text-center mb-5">
    <?php foreach ($instructors as $inst): ?>
      <div class="col-md-4 mb-4">
        <div class="card card-h h-100 shadow-sm border-1 p-4" style="background-color: rgba(169, 169, 169, 0.356);">
          <div class="card-body text-center">
            <img src="<?= htmlspecialchars($inst['image'] ?: 'default-teacher.png') ?>" class="rounded-circle mb-3" width="100" height="100" alt="<?= htmlspecialchars($inst['name']) ?> " loading="lazy">
            <h5 class="card-title"><?= htmlspecialchars($inst['name']) ?></h5>
            <p class="text-muted"><?= htmlspecialchars(ucwords(str_replace('-', ' ', $inst['domain']))) ?></p>
            <hr class="divider my-2">
            <p class="card-text"><?= htmlspecialchars($inst['bio'] ?: 'No bio available.') ?></p>
            <p><strong>Average Rating:</strong> <?= number_format($inst['avg_rating'], 1) ?><span class="badge bg-warning mx-2 text-dark">Top rated</span></p>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
    <div class="col md-12 text-center">
          <a href="instructors.php" class="text-dark text-decoration-none fw-bold ">View all instructors &rarr;</a>
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

</section>
    
    <!-- Preload JS -->
<link rel="preload" href="common/common.js" as="script">
<script src="common/common.js" defer></script>

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
        <a href="locate.php" class="text-white text-decoration-none"><i class="bi bi-geo-alt me-2"></i>Dhaka, Bangladesh &rarr;</a>
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