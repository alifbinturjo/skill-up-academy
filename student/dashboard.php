<?php
include'../auth/cnct.php';
session_start();

if(!isset($_SESSION['role'])&&$_SESSION['role']!=="Student"){
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
            <a class="nav-link active" href="">Dashboard</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="courses.html">Courses</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="notices.html">Notices
              <?php if ($n_status==="unread"): ?>
      <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
        <span class="visually-hidden">New</span>
      </span>
    <?php endif; ?>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="profile.html">Profile</a>
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
$stmt_platform=$conn->prepare("SELECT COUNT(*) FROM admin_notices WHERE audience = 'student'");
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

  <div class="container">

    <div class="mb-5 mt-5">
      <p class="lead fs-1">Hi <?php echo $name ?></p>
    </div>

    <div class="row">
      <div class="col-md-12">
        <div class="card h-100 shadow border-0 p-4 bg-primary text-center text-light">
          <div class="container ">
            <div class="row">
              <div class="col-md-6">
                <p class="lead fs-4">Type: <?php echo $_SESSION['role']; ?></p>
              </div>
              <div class="col-md-6">
                <a href="profile.html" class="btn btn-outline-light">Go to profile</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row mt-5">
      <div class="col-md-6">
        <div class="card card-h h-100 shadow-sm border-0 p-4 bg-success text-center text-light">
          <p class="fs-3 lead"><strong>Courses</strong></p>
          <p class="fs-4 lead">Taken: <?php echo $taken ?></p>
          <p class="fs-4 lead">Previous: <?php echo $past ?></p>
          <div class="text-center">
            <a href="courses.html" class="btn btn-outline-light w-50">View</a>
          </div>

        </div>
      </div>
      <div class="col-md-6">
        <div class="card card-h h-100 shadow-sm border-0 p-4 bg-info text-center">
          <p class="fs-3 lead">Notices</p>
          <p class="fs-4 lead">Platform: <?php echo $platform ?></p>
          <p class="fs-4 lead">Courses: <?php echo $courses ?></p>
          <div class="text-center">
            <a href="notices.html" class="btn btn-outline-dark w-50">View</a>
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