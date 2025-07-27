<?php
session_start(); // Start the session to store user data
include 'cnct.php';

// Function to handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect data from the form
    $name = $_POST['name'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];

    // Validate the form data
    if (empty($name) || empty($email) || empty($contact) || empty($password) || empty($confirmPassword) || empty($dob) || empty($gender)) {
        $_SESSION['error'] = 'Please fill in all the fields.';
        header('Location: signup.php');
        exit();
    }

    if ($password !== $confirmPassword) {
        $_SESSION['error'] = 'Passwords do not match.';
        header('Location: signup.php');
        exit();
    }

    // Validate password strength (at least 8 characters, includes uppercase, lowercase, number, and special character)
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
        $_SESSION['error'] = 'Password must be at least 8 characters long and include uppercase, lowercase, number, and special character.';
        header('Location: signup.php');
        exit();
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if the email or contact number already exists
    $emailCheckQuery = "SELECT * FROM users WHERE email = ?";

    if ($stmt = $conn->prepare($emailCheckQuery)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $emailResult = $stmt->get_result();
        $stmt->close();

        if ($emailResult->num_rows > 0) {
            $_SESSION['error'] = 'This email is already registered.';
            header('Location: signup.php');
            exit();
        }
    } else {
        die("Error preparing email check query: " . $conn->error);
    }

    // Insert user data into the `users` table
    $insertUserQuery = "INSERT INTO users (name, email, contact, dob, gender) VALUES (?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($insertUserQuery)) {
        $stmt->bind_param("sssss", $name, $email, $contact, $dob, $gender);

        if ($stmt->execute()) {
            // Get the inserted user's ID
            $userId = $stmt->insert_id;

            // Insert credentials data into the `credentials` table
            $insertCredentialsQuery = "INSERT INTO credentials (u_id, password) VALUES (?, ?)";

            if ($stmt = $conn->prepare($insertCredentialsQuery)) {
                $stmt->bind_param("is", $userId, $hashedPassword);

                if ($stmt->execute()) {
                    // Insert student record into the `students` table
                    $insertStudentQuery = "INSERT INTO students (u_id, n_status) VALUES (?, 1)"; // 1 for active status

                    if ($stmt = $conn->prepare($insertStudentQuery)) {
                        $stmt->bind_param("i", $userId);

                        if ($stmt->execute()) {
                            $_SESSION['success'] = 'Registration successful!';
                            header('Location: login.php'); // Redirect to login page after successful registration
                            exit();
                        } else {
                            $_SESSION['error'] = 'Failed to insert student data.';
                            header('Location: signup.php');
                            exit();
                        }
                    } else {
                        die("Error preparing student insertion query: " . $conn->error);
                    }
                } else {
                    $_SESSION['error'] = 'Failed to insert credentials.';
                    header('Location: signup.php');
                    exit();
                }
            } else {
                die("Error preparing credentials insertion query: " . $conn->error);
            }
        } else {
            $_SESSION['error'] = 'Failed to register user.';
            header('Location: signup.php');
            exit();
        }
    } else {
        die("Error preparing user insertion query: " . $conn->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Signup - SkillUp Academy</title>
    <!-- Preload Critical Resources -->
    <link rel="preload" href="../style.css" as="style">

    <!-- Minified Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="prefetch" href="../image-assets/common/fav.webp" as="image">
    <link rel="icon" href="../image-assets/common/fav.webp" type="image/webp">
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
            <a class="navbar-brand fw-bold" href="../index.php">SkillUp Academy</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="signup.php">Signup</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <p class="text-center mb-4 fs-1">Sign Up</p>

        <?php
        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
            unset($_SESSION['success']);
        }
        ?>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <form action="signup.php" method="POST">
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="John Doe" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Email</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="example@email.com" required>
                    </div>

                    <div class="mb-3">
                        <label for="contact" class="form-label fw-semibold">Contact Number</label>
                        <input type="text" class="form-control" id="contact" name="contact" placeholder="e.g., 01xxxxxxxxx" required>
                    </div>

                    <div class="mb-3">
                        <label for="dob" class="form-label fw-semibold">Date of Birth</label>
                        <input type="date" class="form-control" id="dob" name="dob" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Gender</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="gender" id="genderMale" value="Male" required>
                            <label class="form-check-label" for="genderMale">Male</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="gender" id="genderFemale" value="Female" required>
                            <label class="form-check-label" for="genderFemale">Female</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Create a password" required>
                    </div>

                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label fw-semibold">Confirm Password</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Re-enter password" required>
                    </div>

                    <button type="submit" class="btn btn-dark w-100">Register</button>
                </form>

                <div class="text-center mt-3 mb-5">
                    <small>Already have an account? <a href="login.php">Login</a></small> <!-- Login link -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
