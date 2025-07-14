<?php
include'../auth/cnct.php';
session_start();

if(!isset($_SESSION['role'])&&$_SESSION['role']!=="Admin"){
  session_unset();
  session_destroy();
  $conn->close();
  header("Location: ../dashboard.php");
  exit();
}
$u_id=$_SESSION['u_id'];

if (isset($_POST['search'])) {
    $search = '%' . trim($_POST['search']) . '%';

    $stmt = $conn->prepare("
        SELECT users.name, users.email
        FROM students 
        JOIN users ON students.u_id = users.u_id
        WHERE users.name LIKE ? OR users.email LIKE ?
        LIMIT 20
    ");
    $stmt->bind_param("ss", $search, $search);
    $stmt->execute();
    $stmt->bind_result($name, $email);

    echo '<div class="table-responsive"><table class="table table-bordered table-hover mt-3">
            <thead class="table-dark"><tr><th>#</th><th>Name</th><th>Email</th></tr></thead><tbody>';
    $i = 1;
    $found = false;
    while ($stmt->fetch()) {
        $found = true;
        echo "<tr><td>$i</td><td>" . htmlspecialchars($name) . "</td><td>" . htmlspecialchars($email) . "</td></tr>";
        $i++;
    }
    if (!$found) {
        echo '<tr><td colspan="3" class="text-center">No results found</td></tr>';
    }
    echo '</tbody></table></div>';

    $stmt->close();
    $conn->close();
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../style.css">
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
          <a class="nav-link" href="dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="courses.php">Courses</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="instructors.php">Instructors</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="admins.php">Admins</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="">Students</a>
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

<div class="container">
    <p class="fs-1 text-center">Students</p>

    <div class="row shadow p-2">
        <div class="col-md-12">
            <input type="text" name="text" id="text" class="form-control" placeholder="Name or email...">
<div id="result" class="mt-2"></div>

        </div>
    </div>
</div>


    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {
  $('#text').on('input', function () {
    let query = $(this).val();
    if (query.trim() === "") {
      $('#result').html("");
      return;
    }

    $.post("students.php", { search: query }, function (data) {
      $('#result').html(data);
    });
  });
});
</script>

  </body>
</html>