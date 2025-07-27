<?php
session_start();
include '../auth/cnct.php';

// Handle AJAX rating submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'])) {
    header('Content-Type: application/json');

    if (!isset($_SESSION['u_id']) || $_SESSION['role'] !== "Student") {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $u_id = $_SESSION['u_id'];
    $c_id = (int) $_POST['c_id'];
    $rating = (int) $_POST['rating'];

    if ($rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'message' => 'Invalid rating']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE enrolls SET rating = ? WHERE u_id = ? AND c_id = ?");
    $stmt->bind_param("iii", $rating, $u_id, $c_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
    exit;
}

// Regular page rendering
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "Student") {
    session_unset();
    session_destroy();
    $conn->close();
    header("Location: ../index.php");
    exit();
}

$u_id = $_SESSION['u_id'];

$sql = "
    SELECT DISTINCT c.c_id, c.title, c.description, c.domain, c.duration, u.name AS instructor, e.rating
    FROM enrolls e
    JOIN courses c ON e.c_id = c.c_id
    JOIN instructors i ON c.u_id = i.u_id
    JOIN users u ON i.u_id = u.u_id
    WHERE e.u_id = ?
    ORDER BY c.c_id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $u_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Student Courses</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="preload" href="../style.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="../style.css">
    </noscript>
  
    <link rel="prefetch" href="../image-assets/common/fav.webp" as="image">
    <link rel="icon" href="../mage-assets/common/fav.webp" type="image/webp">

    <style>
        .rating-box button {
            width: 35px;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-blur sticky-top shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#">SkillUp Academy</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="../index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" href="#">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="../auth/logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <p class="text-center fs-1 mb-4">Courses</p>
        <div class="row">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm p-4" style="background-color: rgba(169, 169, 169, 0.2);">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="badge bg-primary"><?= htmlspecialchars($row['domain']) ?></span>
                                <span class="text-muted"><?= htmlspecialchars($row['duration']) ?> Weeks</span>
                            </div>
                            <hr>
                            <p class="card-text"><?= htmlspecialchars($row['description']) ?></p>
                            <p class="text-muted"><small>Instructor: <?= htmlspecialchars($row['instructor']) ?></small></p>

                            <?php if ($row['rating']): ?>
                                <div>
                                    <small>Your rating:</small>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="<?= $i <= $row['rating'] ? 'text-warning' : 'text-secondary' ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Rate/Update button -->
                            <div class="mt-2">
                                <button class="btn btn-sm btn-outline-dark show-rating" data-cid="<?= $row['c_id'] ?>">
                                    <?= $row['rating'] ? 'Update Rating' : 'Rate' ?>
                                </button>

                                <!-- Rating options (hidden initially) -->
                                <div class="rating-box mt-2 d-none" id="rating-box-<?= $row['c_id'] ?>">
                                    <small>Select:</small>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <button class="btn btn-sm btn-outline-primary rating-btn"
                                            data-cid="<?= $row['c_id'] ?>" data-rating="<?= $i ?>">
                                            <?= $i ?>
                                        </button>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <!-- View announcements -->
                            <div class="mt-3">
                                <a href="notices.php?c_id=<?= $row['c_id'] ?>" class="btn btn-sm btn-outline-dark">View Announcements</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="row text-center mt-1 mb-4">
            <div class="col-md-12">
                <a href="../courses.php" class="btn btn-success w-25">Buy Courses</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        // Toggle rating buttons when "Rate" clicked
        document.querySelectorAll(".show-rating").forEach(button => {
            button.addEventListener("click", function () {
                const cid = this.dataset.cid;
                const box = document.getElementById("rating-box-" + cid);
                box.classList.toggle("d-none");
            });
        });

        // Handle click on rating number (1-5)
        document.querySelectorAll(".rating-btn").forEach(button => {
            button.addEventListener("click", function () {
                const c_id = this.dataset.cid;
                const rating = this.dataset.rating;

                // Send rating via fetch POST
                fetch("", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `rating=${rating}&c_id=${c_id}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert("Rating submitted!");
                        location.reload(); // Refresh to show stars
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert("An error occurred.");
                });
            });
        });
    });
    </script>
</body>

</html>
