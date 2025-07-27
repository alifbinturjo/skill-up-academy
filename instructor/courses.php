<?php
include '../auth/cnct.php';
session_start();

/* For Checking
$_SESSION['role'] = "Instructor";
$_SESSION['u_id'] = 2; */

if (!isset($_SESSION['role']) && $_SESSION['role'] !== "Instructor") {
    session_unset();
    session_destroy();
    $conn->close();
    header("Location: ../index.php");
    exit();
}

$u_id = $_SESSION['u_id'];


// Fetch instructor's courses
$stmt = $conn->prepare("SELECT c_id, title, description, domain, duration FROM courses WHERE u_id = ?");
$stmt->bind_param("i", $u_id);
$stmt->execute();
$result = $stmt->get_result();

$courses = [];
while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses For Instructor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="preload" href="../style.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="../style.css">
    </noscript>
    
    <link rel="prefetch" href="../image-assets/common/fav.webp" as="image">
    <link rel="icon" href="../image-assets/common/fav.webp" type="image/webp">

</head>

<body>
    <script>
        window.addEventListener('pageshow', function(event) {
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
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../auth/logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <p class="text-center mb-4 fs-1">Courses</p>
        <div class="row">
            <?php if (count($courses) === 0): ?>
                <p class="text-center">No courses assigned.</p>
            <?php else: ?>
                <?php foreach ($courses as $course): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card card-h h-100 shadow-sm border-1 p-4" style="background-color: rgba(169, 169, 169, 0.356);">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($course['title']) ?></h5>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="badge bg-primary"><?= htmlspecialchars($course['domain']) ?></span>
                                    <span class="text-muted"><?= htmlspecialchars($course['duration']) ?> Weeks</span>
                                </div>
                                <hr class="divider my-2">
                                <p class="card-text"><?= htmlspecialchars($course['description']) ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="students.php?c_id=<?= $course['c_id'] ?>" class="btn btn-sm btn-outline-dark">View Students</a>
                                    <a href="post-notices.php?c_id=<?= $course['c_id'] ?>" class="btn ms-2 btn-sm btn-outline-dark">Create Announcements</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>