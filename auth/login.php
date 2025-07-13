<?php
include 'cnct.php';  // Include the database connection file
session_start();  // Start the session to store user data

// Handle the login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
    $email = $_POST['email'];
    $password = $_POST['pass'];  // Make sure it's 'pass' as per your input name

    // Validate form data
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = 'Please enter both email and password.';
        header('Location: login.php');
        exit();
    }

    // Check if the email exists in the database
    $emailCheckQuery = "SELECT u.u_id, u.email, c.pass AS password_hash FROM users u
                        JOIN credentials c ON u.u_id = c.u_id
                        WHERE u.email = ?";
    $stmt = $conn->prepare($emailCheckQuery);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($userId, $dbEmail, $storedPassword);  // Bind the results
    $stmt->fetch();
    $stmt->close();

    // Check if the email was found
    if (!$dbEmail) {
        $_SESSION['error'] = 'No user found with that email.';
        header('Location: login.php');
        exit();
    }

    // Verify the entered password matches the stored password (use password_verify for hashed passwords)
    if (!password_verify($password, $storedPassword)) {
        $_SESSION['error'] = 'Incorrect password.';
        header('Location: login.php');
        exit();
    }

    // If the email and password are correct, determine the user role (Student, Admin, Instructor)
    $roleCheckQuery = "SELECT * FROM students WHERE u_id = ?";
    $stmt = $conn->prepare($roleCheckQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $studentResult = $stmt->get_result();

    $role = 'unknown';  // Default role is 'unknown'
    if ($studentResult->num_rows > 0) {
        $role = 'Student';  // If found in students table, role is 'student'
    } else {
        // Check if the user is an admin
        $roleCheckQuery = "SELECT * FROM admins WHERE u_id = ?";
        $stmt = $conn->prepare($roleCheckQuery);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $adminResult = $stmt->get_result();

        if ($adminResult->num_rows > 0) {
            $role = 'Admin';  // If found in admins table, role is 'admin'
        } else {
            // Check if the user is an instructor
            $roleCheckQuery = "SELECT * FROM instructors WHERE u_id = ?";
            $stmt = $conn->prepare($roleCheckQuery);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $instructorResult = $stmt->get_result();

            if ($instructorResult->num_rows > 0) {
                $role = 'Instructor';  // If found in instructors table, role is 'instructor'
            }
        }
    }

    // Store session data
    $_SESSION['u_id'] = $userId;
    $_SESSION['role'] = $role;
    $_SESSION['email'] = $dbEmail;

    // Redirect based on role
if ($role == 'Student') {
    header('Location:/skill-up-academy/student/dashboard.php');  // Correct path for student dashboard
} elseif ($role == 'Admin') {
    header('Location: /skill-up-academy/admin/dashboard.php');    // Correct path for admin dashboard
} elseif ($role == 'Instructor') {
    header('Location:/skill-up-academy/instructor/dashboard.php'); // Correct path for instructor dashboard
} else {
    $_SESSION['error'] = 'User role is undefined.';
    header('Location: /auth/login.php');  // Redirect back to login page if no role is found
    exit();
}
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SkillUp Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../style.css">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-blur sticky-top shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="../index.php">SkillUp Academy</a>
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
                        <a class="nav-link" href="signup.php">Signup</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="login.php">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Login Form -->
    <div class="container mt-5">
        <p class="text-center mb-4 fs-1">Login</p>
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <form action="login.php" method="POST">
                    <!-- Error message display -->
                    <?php
                    if (isset($_SESSION['error'])) {
                        echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
                        unset($_SESSION['error']);
                    }
                    ?>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Email</label>
                        <input type="email" class="form-control form-control-lg" id="email" name="email" placeholder="example@email.com" required>
                    </div>

                    <div class="mb-3 position-relative">
                        <label for="password" class="form-label fw-semibold">Password</label>
                        <input type="password" class="form-control form-control-lg" id="password" name="pass" placeholder="Your password" required>
                        <i class="bi bi-eye-slash position-absolute end-0 pe-3" id="togglePassword" style="top: 70%; transform: translateY(-50%); cursor: pointer;"></i>
                    </div>

                    <button type="submit" class="btn btn-dark w-100 py-2 fs-5">Login</button>
                </form>

                <div class="text-center mt-3 mb-5">
                    <small>Don't have an account? <a href="signup.php">Signup</a></small>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.getElementById("togglePassword").addEventListener("click", function () {
            const passwordField = document.getElementById("password");
            const icon = this;
            if (passwordField.type === "password") {
                passwordField.type = "text";
                icon.classList.remove("bi-eye-slash");
                icon.classList.add("bi-eye");
            } else {
                passwordField.type = "password";
                icon.classList.remove("bi-eye");
                icon.classList.add("bi-eye-slash");
            }
        });
    </script>

</body>

<footer class="bg-dark text-white pt-5 pb-4 mt-5">
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


