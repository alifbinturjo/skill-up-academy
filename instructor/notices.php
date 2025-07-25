<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Notices</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
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
            <a class="nav-link " href="dashboard.php">Dashboard</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="courses.php">Courses</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active " href="notices.php">Notices</a>
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



  <div class="container mt-5 mb-5">
    <p class="text-center fs-1 mb-4">Notices</p>


    <div class="card shadow bg-transparent card-h mb-5">
      <div class="card-body">
        <h5 class="card-title">System Maintenance</h5>
        <p class="card-text">We will perform scheduled maintenance on the system tonight from 12:00 AM to 2:00 AM.
          During this time, access may be temporarily unavailable.</p>
        <p class="text-muted small">June 27, 2025 • 11:30 AM</p>
      </div>
    </div>


    <div class="card shadow bg-transparent card-h mb-5">
      <div class="card-body">
        <h5 class="card-title">New Course Added</h5>
        <p class="card-text">A new course titled "Advanced Machine Learning" has been added to the Computer Science
          department for the upcoming semester.</p>
        <p class="text-muted small">June 26, 2025 • 3:45 PM</p>
      </div>
    </div>


  </div>

</body>

</html>