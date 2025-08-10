<?php
session_start();
include'../auth/cnct.php';


if(!isset($_SESSION['role'])&&$_SESSION['role']!=="Instructor"){
  session_unset();
  session_destroy();
  $conn->close();
  header("Location: ../index.php");
  exit();
}
/*$_POST['cid']=1;
if(!isset($_POST['c_id'])){
  $conn->close();
  header("Location: courses.html");
  exit();
}*/
$c_id=$_GET['c_id'];
$u_id=$_SESSION['u_id'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Students</title>
  <link rel="prefetch" href="../image-assets/common/fav.webp" as="image">
    <link rel="icon" href="../image-assets/common/fav.webp" type="image/webp">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="preload" href="../style.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="../style.css"></noscript>

<link rel="prefetch" href="../image-assets/common/fav.webp" as="image">
    <link rel="icon" href="../image-assets/common/fav.webp" type="image/webp">

</head>

<body>

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
            <a class="nav-link" href="dashboard.php">Dashboard</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="../auth/logout.php">Logout</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>


    <p class="fs-1 text-center">Students</p>

    
  <?php

  $stmt_list = $conn->prepare("
  SELECT users.name, users.email
FROM enrolls 
JOIN students ON enrolls.u_id = students.u_id 
JOIN users ON users.u_id = students.u_id 
WHERE enrolls.c_id = ?

");
$stmt_list->bind_param("i", $c_id);
try{
  $stmt_list->execute();
  $stmt_list->bind_result($name,$email);
  
}
catch(Exception $e){
  $stmt_list->close();
  $conn->close();
  header("Location: courses.php");
  exit();
}
?>
<div class="container mt-5">
  

  <div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Email</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $i = 1;
        $found = false;
        while ($stmt_list->fetch()):
          $found = true;
        ?>
        <tr>
          <td><?php echo $i++; ?></td>
          <td><?php echo htmlspecialchars($name); ?></td>
          <td><?php echo htmlspecialchars($email); ?></td>
        </tr>
        <?php endwhile; ?>

        <?php if (!$found): ?>
        <tr>
          <td colspan="3" class="text-center">No students enrolled in this course.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php
$stmt_list->close();
$conn->close();
?>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q"
    crossorigin="anonymous"></script>
</body>

</html>