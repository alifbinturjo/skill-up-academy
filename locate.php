<?php
session_start();
include 'auth/cnct.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Locate</title>
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
          <a class="nav-link" href="index.php">Home</a>
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

<div class="container my-5">
  <h3 class="text-center fw-bold mb-4">Locate Us on Google Map</h3>
  <div id="map" style="height: 400px; width: 100%;" class="rounded shadow-sm card-h"></div>
</div>

<script>
  function initMap() {
    const location = { lat: 23.81584955664102, lng: 90.42555230160781 }; // Example: Dhaka, Bangladesh

    const map = new google.maps.Map(document.getElementById("map"), {
      zoom: 15,
      center: location,
    });

    const marker = new google.maps.Marker({
      position: location,
      map: map,
      title: "SkillUp Academy",
    });
  }
</script>

<script async defer
  src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBoMVQ-Z7I1-KWQl9muBsoCa6xjlhHWDho&callback=initMap">
</script>


    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
</body>
<footer class="bg-dark text-white pt-5 pb-4">
  <div class="container text-md-left">
    <div class="row text-center text-md-left">

           <div class="col-md-6 col-lg-6 col-xl-6 mx-auto mt-3">
        <h5 class="mb-4 fw-bold">SkillUp Academy</h5>
        <p>Empowering learners with the skills they need to succeed in the digital world.</p>
        <a href="policies.php" class="text-white text-decoration-none">Academy policies &rarr;</a>
      </div>

      <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
        <h5 class="mb-4 fw-bold">Contact</h5>
        <p><i class="bi bi-envelope me-2"></i> support@skillup.mynsu.xyz</p>
        <p><i class="bi bi-phone me-2"></i> 01745630304</p>
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