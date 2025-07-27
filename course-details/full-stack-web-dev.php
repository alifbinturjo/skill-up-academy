<?php
include '../auth/cnct.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Full Stack Web Development</title>
    <link rel="prefetch" href="../image-assets/common/fav.webp" as="image">
    <link rel="icon" href="../image-assets/common/fav.webp" type="image/webp">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../style.css">
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
          <a class="nav-link" href="../courses.php">Courses</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../instructors.php">Instructors</a>
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
<?php
$c_id = 1;

$stmt = $conn->prepare("
    SELECT 
        c.title AS course_title,
        c.domain,
        c.duration,
        c.description,
        c.u_id,
        c.amount,
        i.title AS instructor_title,
        u.name AS instructor_name
    FROM 
        courses c
    JOIN 
        instructors i ON c.u_id = i.u_id
    JOIN 
        users u ON c.u_id = u.u_id
    WHERE 
        c.c_id = ?
");


    $stmt->bind_param("i", $c_id);
    $stmt->execute();
    $stmt->bind_result($courseTitle, $domain, $duration, $description, $uid, $amount, $instructorTitle, $instructorName);

    $stmt->fetch();
        

    $stmt->close();


?>
<div class="container py-5">
    <h1 class="mb-3"><?php echo $courseTitle ?></h1>

    <div class="mb-4">
        <span class="badge bg-primary"><?php echo $domain ?></span>
        <span class="ms-3 text-muted">Duration: <?php echo $duration ?> weeks</span>

        <div class="mt-3 text-center">
          <img src="../image-assets/course-images/full-stack-web-dev.webp" alt="Full stack web development" class="img-fluid rounded shadow">
        </div>
    </div>

    <div class="mb-4">
        <h4>Description</h4>
        <p><?php echo $description ?></p>
    </div>

    <div class="mb-4">
        <h4>Requirements</h4>
        <ul>
        <li>Basic understanding of computers</li>
        <li>Internet access</li>
        <li>Willingness to learn</li>
      </ul>
    </div>

    <div class="mb-4">
      <h4>Course Outline</h4>
      <div class="accordion" id="courseOutline">
        <div class="accordion-item bg-transparent">
          <h2 class="accordion-header " id="headingOne">
            <button class="accordion-button bg-transparent lead" type="button" data-bs-toggle="collapse" data-bs-target="#module1">
              Module 1: Introduction to Web Development
            </button>
          </h2>
          <div id="module1" class="accordion-collapse collapse show" data-bs-parent="#courseOutline">
            <div class="accordion-body">
              Learn the basics of how websites work and how they are built.
            </div>
          </div>
        </div>

        <div class="accordion-item bg-transparent">
          <h2 class="accordion-header" id="headingTwo">
            <button class="accordion-button collapsed bg-transparent lead" type="button" data-bs-toggle="collapse" data-bs-target="#module2">
              Module 2: HTML & CSS Basics
            </button>
          </h2>
          <div id="module2" class="accordion-collapse collapse" data-bs-parent="#courseOutline">
            <div class="accordion-body">
              Build beautiful web pages using HTML and CSS.
            </div>
          </div>
        </div>

        

      </div>
    </div>

    <div class="mb-4">
      <h4>Instructor</h4>
      <div class="d-flex align-items-center">
        <div>
          <p class="lead"><?php echo $instructorName ?></p>
          <p class="text-muted"><?php echo $instructorTitle ?></p>
        </div>
      </div>
    </div>
    <div class="text-center">
      <a href="../auth/billing.html" class="btn card-h shadow btn-success w-50">
      <strong class="fs-5">Buy now</strong>
      <p class="lead">BDT <strong><?php echo $amount ?></strong></p>
    </a>
    </div>
    

</div>


    
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