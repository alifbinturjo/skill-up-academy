<?php
include'../auth/cnct.php';
session_start();

if(!isset($_SESSION['role'])&&$_SESSION['role']!=="Admin"){
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
          <a class="nav-link active" href="#">Dashboard</a>
        </li>
         <li class="nav-item">
          <a class="nav-link" href="admins.php">Admins</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="instructors.php">Instructors</a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="courses.php">Courses</a>
        </li>
        
       
        <li class="nav-item">
          <a class="nav-link" href="students.php">Students</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="post-notices.php">Notices</a>
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
<div class="container">

    <div class="mb-5 mt-5">
        <p class="lead fs-1">Hi <?php echo $name ?></p>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card h-100 shadow border-0 p-4 bg-primary text-center text-light">
            <div class="container ">
                <div class="row">
                    <div class="col-md-4">
                        <p class="lead fs-4">Type: <?php echo $_SESSION['role'] ?></p>
                    </div>
                    <div class="col-md-4">
                        <p class="lead fs-4">Level: <?php echo $level ?></p>
                    </div>
                    <div class="col-md-4">
                        <a href="profile.php" class="btn btn-outline-light">Go to profile</a>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>

    <div class="row mt-5 justify-content-center">
        <div class="col-md-2">
            <div class="card h-100 card-h shadow-sm border-0 p-4 bg-light text-center text-primary">
                <p class="fs-4 lead"><strong>Courses  <i class="bi bi-book"></i></strong></p>
                <p class="fs-5 lead">Offered: <?php echo $offered ?></p>
                <p class="fs-5 lead">Ongoing: <?php echo $started ?></p>
                <div class="text-center mt-5">
                  <a href="courses.php" class="btn btn-outline-primary btn-sm w-50">&rarr;</a>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card h-100 card-h shadow-sm border-0 p-4 bg-light text-center text-secondary">
                <p class="fs-4 lead"><strong>Notices  <i class="bi bi-megaphone"></i></strong></p>
                <p class="fs-5 lead">Posted: <?php echo $notice_count ?></p>
                <div class="text-center mt-5">
                  <a href="post-notices.php" class="mt-5 btn btn-outline-secondary btn-sm w-50">&rarr;</a>
                </div>
            </div>
        </div>
        
    

    
        
        <div class="col-md-2">
            <div class="card h-100 card-h shadow-sm border-0 p-4 bg-light text-center text-success">
                <p class="fs-4 lead"><strong>Instructors  <i class="bi bi-person-lines-fill"></i></strong></p>
                <p class="fs-5 lead">Junior instructor: <?php echo $junior ?></p>
                <p class="fs-5 lead">Instructor: <?php echo $instructor ?></p>
                <p class="fs-5 lead">Senior instructor: <?php echo $senior ?></p>
                <div class="text-center">
                  <a href="instructors.php" class="btn btn-outline-success btn-sm w-50">&rarr;</a>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card h-100 card-h shadow-sm border-0 p-4 bg-light text-center text-info">
                <p class="fs-4 lead"><strong>Admins  <i class="bi bi-person-gear"></i></strong></p>
                <p class="fs-5 lead">L0: <?php echo $l0 ?></p>
                <p class="fs-5 lead">L1: <?php echo $l1 ?></p>
                <div class="text-center mt-2">
                  <a href="admins.php" class="mt-5 btn btn-outline-info btn-sm w-50">&rarr;</a>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card h-100 card-h shadow-sm border-0 p-4 bg-light text-center text-danger">
                <p class="fs-4 lead"><strong>Students  <i class="bi bi-people"></i></strong></p>
                <p class="fs-5 lead">Total: <?php echo $total_students ?></p>
                <div class="text-center mt-5">
                  <a href="students.php" class="btn btn-outline-danger btn-sm mt-5 w-50">&rarr;</a>
                </div>
            </div>
        </div>
        
</div>



    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
</body>
</html>