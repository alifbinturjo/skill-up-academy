<?php
include 'auth/cnct.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Policies </title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
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

  <!-- Navbar -->
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

</div>


 <!-- Main Content -->
<div class="container py-5">
  <div class="row">
    <div class="col-12">
      <p class="text-center mb-4 fs-1">Policies</p>

      <div class="card p-4 card-h bg-secondary text-light shadow-lg" style="max-width: 100%; width: 100%;">
        <div class="card-body">
          <h5 class="fw-bold">Introduction</h5>
          <p class="fs-6">Welcome to SkillUp Academy. By accessing our website, you agree to the following terms and conditions. These terms are essential for the proper functioning of the website and the safety and security of our users. Please read them carefully before proceeding.</p>

          <h5 class="fw-bold">1. Use of Services</h5>
          <p class="fs-6">You must agree to comply with the terms for using our educational services, which include course registration, learning materials, and communication with instructors. You may access the platform only for lawful purposes and must not misuse the services provided.</p>
          <p class="fs-6">We provide educational services to enhance your skills and knowledge. You must not use our platform to conduct illegal activities, engage in harassment, or attempt to gain unauthorized access to our systems or other users’ accounts.</p>

          <h5 class="fw-bold">2. Account Registration</h5>
          <p class="fs-6">To use our platform, you must register for an account. You must provide accurate information, and you are responsible for maintaining the security of your account. Please ensure the information you provide is truthful, complete, and updated regularly. Failure to do so could lead to account suspension or termination.</p>
          <p class="fs-6">Your account may be used only by you and must not be shared or used by others without permission. You are responsible for all activities that occur under your account and should immediately report any suspicious activity to us.</p>

          <h5 class="fw-bold">3. Payment and Fees</h5>
          <p class="fs-6">All users must make payments through our secure gateway when enrolling in a paid course. Please refer to our payment page for details on fee structures, payment methods, and any applicable taxes or charges. We reserve the right to change the fees for courses, but we will notify you in advance of any changes.</p>
          <p class="fs-6">If you fail to make the required payments, your access to the courses and services may be restricted until payment is received. We offer refunds for certain situations as specified in our refund policy, which you can review on our website.</p>

          <h5 class="fw-bold">4. User Conduct</h5>
          <p class="fs-6">Users are expected to maintain a respectful and professional conduct when interacting with other learners and instructors on the platform. You should treat others with respect and follow the guidelines for appropriate behavior set forth in our community guidelines.</p>
          <p class="fs-6">We encourage collaborative learning and meaningful discussions but will not tolerate discriminatory remarks, harassment, or any other conduct that may harm the learning environment. Violation of this code of conduct may result in account suspension or termination.</p>

          <h5 class="fw-bold">5. Privacy Policy</h5>
          <p class="fs-6">Your privacy is important to us. We are committed to protecting your personal information as outlined in our Privacy Policy. We collect personal data only when necessary to provide our services, and we do not share or sell this data without your consent.</p>
          <p class="fs-6">Please read our Privacy Policy carefully to understand how we collect, use, and store your personal data. We take appropriate measures to protect your data, but we cannot guarantee absolute security. You should also take steps to protect your account and personal information.</p>

          <h5 class="fw-bold">6. Changes to Terms</h5>
          <p class="fs-6">We may update these terms at any time. All changes will be posted on this page, and your continued use of the services signifies acceptance of the new terms. We advise you to regularly check this page to stay informed of any updates.</p>
          <p class="fs-6">Changes may be made to accommodate new services, legal requirements, or to enhance user experience. If you do not agree with the new terms, you may stop using the services, and we may terminate your account if necessary.</p>

          <h5 class="fw-bold">7. Governing Law</h5>
          <p class="fs-6">These terms are governed by the laws of Bangladesh. Any disputes will be resolved in the courts of Dhaka, Bangladesh. By agreeing to these terms, you submit to the jurisdiction of the courts in Dhaka for any legal matters that may arise.</p>
          <p class="fs-6">If you have any concerns or need assistance, please contact us before seeking legal action. We prefer to resolve disputes amicably through communication and cooperation.</p>
        </div>
      </div>
    </div>
  </div>
</div>

  <!-- Footer -->
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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>