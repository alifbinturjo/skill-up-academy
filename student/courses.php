<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coruses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
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
                        <a class="nav-link " href="dashboard.html">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="">Courses</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="notices.html">Notices</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.html">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <p class="text-center mb-4 fs-1">Courses</p>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card card-h h-100 shadow-sm border-1 p-4"
                    style="background-color: rgba(169, 169, 169, 0.356);">
                    <div class="card-body">
                        <h5 class="card-title">Complete Web Development Bootcamp</h5>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="badge bg-primary">Web Development</span>
                            <span class="text-muted">8 Weeks</span>
                        </div>
                        <hr class="divider my-2">
                        <p class="card-text">Learn full-stack web development with HTML, CSS, JavaScript, React,
                            Node.js, and MongoDB.</p>
                        <p class="text-muted"><small>Instructor: John Doe</small></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="notices.html" class="btn btn-md btn-outline-dark">View Announcements</a>
                        </div>
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