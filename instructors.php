<?php
include 'auth/cnct.php';
session_start();

// Pagination setup
$limit = 6;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Filter setup (GET instead of POST)
$filter = isset($_GET['domain']) ? $_GET['domain'] : "";

// Total count query
$count_sql = "SELECT COUNT(*) FROM instructors";
$params = [];
$types = "";

if (!empty($filter)) {
    $count_sql .= " WHERE domain = ?";
    $types .= "s";
    $params[] = $filter;
}

$stmt = $conn->prepare($count_sql);
if (!empty($filter)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$stmt->bind_result($total);
$stmt->fetch();
$stmt->close();

$total_pages = ceil($total / $limit);

// Instructor fetch
$instructors = [];
$sql = "SELECT users.name, instructors.domain, instructors.bio, instructors.image 
        FROM instructors 
        JOIN users ON users.u_id = instructors.u_id";

if (!empty($filter)) {
    $sql .= " WHERE instructors.domain = ?";
}
$sql .= " LIMIT ?, ?";

$stmt = $conn->prepare($sql);
if (!empty($filter)) {
    $stmt->bind_param("sii", $filter, $offset, $limit);
} else {
    $stmt->bind_param("ii", $offset, $limit);
}

$stmt->execute();
$stmt->bind_result($name, $domain, $bio, $image);
while ($stmt->fetch()) {
    $instructors[] = [
        'name' => $name,
        'domain' => $domain,
        'bio' => $bio,
        'image' => $image ?? 'image-assets/Instructors/default.webp'
    ];
}
$stmt->close();

// Distinct domains
$domains = [];
$result = $conn->query("SELECT DISTINCT domain FROM instructors");
while ($row = $result->fetch_assoc()) {
    $domains[] = $row['domain'];
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Instructors</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="preload" href="style.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="style.css">
    </noscript>
    
    <link rel="prefetch" href="image-assets/common/fav.webp" as="image">
    <link rel="icon" href="image-assets/common/fav.webp" type="image/webp">

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
            <a class="navbar-brand fw-bold" href="index.php">SkillUp Academy</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>

                    <?php
                    if (isset($_SESSION['role'])) {
                        echo '<li class="nav-item">';
                        if ($_SESSION['role'] === "Student")
                            echo '<a class="nav-link" href="student/dashboard.php">Dashboard</a> </li>';
                        else if ($_SESSION['role'] === "Instructor")
                            echo '<a class="nav-link" href="instructor/dashboard.php">Dashboard</a> </li>';
                        else
                            echo '<a class="nav-link" href="admin/dashboard.php">Dashboard</a> </li>';

                        echo '<li class="nav-item">
                  <a class="nav-link" href="auth/logout.php">Logout</a>
                  </li>';
                    } else {
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

    <div class="container mt-5 mb-5 min-vh-100">
        <h1 class="text-center mb-4">Instructors</h1>

        <!-- Filter -->
        <form method="get" class="mb-4">
            <div class="row justify-content-center">
                <div class="col-md-4">
                    <select name="domain" class="form-select" onchange="this.form.submit()">
                        <option value="">All Domains</option>
                        <?php foreach ($domains as $d): ?>
                            <option value="<?= htmlspecialchars($d) ?>" <?= ($filter == $d ? 'selected' : '') ?>>
                                <?= ucfirst($d) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </form>

        <div class="row">
            <?php if (empty($instructors)): ?>
                <div class="col text-center">
                    <p class="text-muted">No instructors found.</p>
                </div>
            <?php endif; ?>
            <?php foreach ($instructors as $inst): ?>
                <div class="col-md-4 mb-4">
                    <div class="card card-h h-100 shadow-sm border-1 p-4" style="background-color: rgba(169, 169, 169, 0.356);">
                        <div class="card-body text-center">
                            <img src="<?= htmlspecialchars($inst['image']) ?>" class="rounded-circle mb-3"
                                alt="<?= htmlspecialchars($inst['name']) ?>" loading="lazy">
                            <h5 class="card-title"><?= htmlspecialchars($inst['name']) ?></h5>
                            <span class="badge bg-warning text-dark"><?= htmlspecialchars($inst['domain']) ?></span>
                            <hr class="divider my-2">
                            <p class="card-text"><?= htmlspecialchars($inst['bio']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination (simplified like courses page) -->
        <?php if ($total_pages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= ($page == $i ? 'active' : '') ?>">
                            <a class="page-link" href="?page=<?= $i ?>&domain=<?= urlencode($filter) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
    </script>
</body>

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

</html>