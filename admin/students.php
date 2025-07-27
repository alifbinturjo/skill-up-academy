<?php
session_start();
include '../auth/cnct.php';

// ✅ Authorization check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "Admin") {
    session_unset();
    session_destroy();
    $conn->close();
    header("Location: ../dashboard.php");
    exit();
}

$u_id = $_SESSION['u_id'];

// ✅ Only handle AJAX POST for search
if (isset($_POST['search'])) {
    $search = trim($_POST['search']);
    $cacheKey = "students_search:" . strtolower($search);

    $useRedis = false;
    $redis = new Redis();

    // ✅ Try connecting to Redis
    try {
        if (@$redis->connect('127.0.0.1', 6379)) {
            $useRedis = true;
        }
    } catch (Exception $e) {
        $useRedis = false;
    }

    if ($useRedis && $redis->exists($cacheKey)) {
        // 🔁 Return cached HTML
        echo $redis->get($cacheKey);
    } else {
        $like = '%' . $search . '%';

        $stmt = $conn->prepare("
            SELECT users.name, users.email
            FROM students 
            JOIN users ON students.u_id = users.u_id
            WHERE users.name LIKE ? OR users.email LIKE ?
            LIMIT 20
        ");
        $stmt->bind_param("ss", $like, $like);
        $stmt->execute();
        $stmt->bind_result($name, $email);

        // ✅ Generate HTML table
        ob_start();
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

        $html = ob_get_clean(); // 🧠 store HTML output

        // ✅ Cache HTML in Redis (if available)
        if ($useRedis) {
            $redis->setex($cacheKey, 300, $html); // cache for 5 mins
        }

        echo $html;
        $stmt->close();
    }

    $conn->close();
    exit();
}
?>
