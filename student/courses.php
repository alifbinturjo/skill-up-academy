<?php
include '../auth/cnct.php';
session_start();

$_SESSION['role'] = "Student";
$_SESSION['u_id'] = 2;

if (!isset($_SESSION['role']) && $_SESSION['role'] !== "Student") {
    session_unset();
    session_destroy();
    $conn->close();
    header("Location: ../index.php");
    exit();
}
$u_id = $_SESSION['u_id'];

/*
// Use logged-in instructor's user ID
if (!isset($_SESSION['id'])) {
    $_SESSION['id'] = 1; // for testing; replace with actual session management in production
}
$uid = $_SESSION['id']; */

$sql = "
    SELECT DISTINCT c.c_id, c.title, c.description, c.domain, c.duration, u.name AS instructor
    FROM enrolls e
    JOIN courses c ON e.c_id = c.c_id
    JOIN instructors i ON c.u_id = i.u_id
    JOIN users u ON i.u_id = u.u_id
    WHERE e.u_id = ?
    ORDER BY c.c_id DESC
";

// user id -> enrolls -> how many course -> retrive course everything -> 

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $u_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>

<body>

    <?php
    $stmt_n = $conn->prepare("SELECT n_status FROM instructors WHERE u_id = ?");
    $stmt_n->bind_param("i", $u_id);

    try {
        $stmt_n->execute();
        $stmt_n->bind_result($n_status);
        $stmt_n->fetch();
        $stmt_n->close();
    } catch (Exception $e) {
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
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Courses</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="notices.php">Notices
                            <?php if ($n_status === "unread"): ?>
                                <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                                    <span class="visually-hidden">New</span>
                                </span>
                            <?php endif; ?>
                        </a>
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

    <div class="container mt-5">
        <p class="text-center fs-1 mb-4">Courses</p>
        <div class="row">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm p-4" style="background-color: rgba(169, 169, 169, 0.356);">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="badge bg-primary"><?= htmlspecialchars($row['domain']) ?></span>
                                <span class="text-muted"><?= htmlspecialchars($row['duration']) ?> Weeks</span>
                            </div>
                            <hr class="my-2">
                            <p class="card-text"><?= htmlspecialchars($row['description']) ?></p>
                            <p class="text-muted"><small>Instructor: <?= htmlspecialchars($row['instructor']) ?></small></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="#" class="btn btn-md btn-outline-dark">View Announcements</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>