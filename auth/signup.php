<?php
  include 'cnct.php';
  session_start(); // Start the session to store user data

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

      $stmt = $conn->prepare($emailCheckQuery);
      $stmt->bind_param("s", $email);
      $stmt->execute();
      $emailResult = $stmt->get_result();
      $stmt->close();

      if ($emailResult->num_rows > 0) {
          $_SESSION['error'] = 'This email is already registered.';
          header('Location: signup.php');
          exit();
      }

      // Insert user data into the `users` table
      $insertUserQuery = "INSERT INTO users (name, email, contact, dob, gender) VALUES (?, ?, ?, ?, ?)";
      $stmt = $conn->prepare($insertUserQuery);
      $stmt->bind_param("sssss", $name, $email, $contact, $dob, $gender);
      if ($stmt->execute()) {
          // Get the inserted user's ID
          $userId = $stmt->insert_id;

          // Insert user credentials into the `credentials` table
          $insertCredentialsQuery = "INSERT INTO credentials (u_id, pass) VALUES (?, ?)";
          $stmt = $conn->prepare($insertCredentialsQuery);
          $stmt->bind_param("is", $userId, $hashedPassword);
          if ($stmt->execute()) {
              // Insert student record into the `students` table
              $insertStudentQuery = "INSERT INTO students (u_id, n_status) VALUES (?, 1)"; // 1 for active status
              $stmt = $conn->prepare($insertStudentQuery);
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
              $_SESSION['error'] = 'Failed to insert credentials.';
              header('Location: signup.php');
              exit();
          }
      } else {
          $_SESSION['error'] = 'Failed to register user.';
          header('Location: signup.php');
          exit();
      }
  }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - SkillUp Academy</title>

    <!-- Preload Critical Resources -->
    <link rel="preload" href="../style.css" as="style">

    <!-- Minified Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="../style.css">

    <!-- Bootstrap Icons for Eye Toggle -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
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
            <a class="navbar-brand fw-bold" href="../index.php">SkillUp Academy</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="../index.php">Home</a></li>
                    <?php
                    if (isset($_SESSION['role'])) {
                        echo '<li class="nav-item">';
                        if ($_SESSION['role'] === "Student")
                            echo '<a class="nav-link" href="student/dashboard.php">Dashboard</a> </li>';
                        else if ($_SESSION['role'] === "Instructor")
                            echo '<a class="nav-link" href="instructor/dashboard.php">Dashboard</a> </li>';
                        else
                            echo '<a class="nav-link" href="admin/dashboard.php">Dashboard</a> </li>';
                        echo '<li class="nav-item"><a class="nav-link" href="auth/logout.php">Logout</a></li>';
                    } else {
                        echo '<a class="nav-link " href="login.php">Login</a> </li><li class="nav-item"><a class="nav-link active " href="signup.php">Signup</a></li>';
                    }
                    ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Signup Form -->
    <div class="container mt-4">
        <p class="text-center mb-4 fs-1">Sign Up</p>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <form action="signup.php" method="POST">

                    <?php
                    // Show error or success messages
                    if (isset($_SESSION['error'])) {
                        echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
                        unset($_SESSION['error']);
                    }
                    if (isset($_SESSION['success'])) {
                        echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
                        unset($_SESSION['success']);
                    }
                    ?>

                    <div class="mb-3">
                        <label for="Name" class="form-label fw-semibold"> Name</label>
                        <input type="text" class="form-control" id="Name" name="name" placeholder="John Doe" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Email</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="example@email.com" required>
                    </div>

                    <div class="mb-3 position-relative">
                        <label for="password" class="form-label fw-semibold">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Create a password" required>
                        <i class="bi bi-eye-slash position-absolute end-0 pe-3" id="togglePassword"
                            style="top: 70%; transform: translateY(-50%); cursor: pointer;"></i>
                    </div>

                    <div class="mb-3 position-relative">
                        <label for="confirmPassword" class="form-label fw-semibold">Confirm Password</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Re-enter password" required>
                        <i class="bi bi-eye-slash position-absolute end-0 pe-3" id="toggleConfirmPassword"
                            style="top: 70%; transform: translateY(-50%); cursor: pointer;"></i>
                    </div>

                    <div class="mb-3">
                        <label for="dob" class="form-label fw-semibold">Date of Birth</label>
                        <input type="date" class="form-control" id="dob" name="dob" required>
                    </div>

                    <div class="mb-3">
                        <label for="contact" class="form-label fw-semibold">Contact Number</label>
                        <div class="input-group">
                            <span class="input-group-text">+880</span>
                            <input type="tel" class="form-control" id="contact" name="contact" placeholder="1XXXXXXXXX" pattern="[1][0-9]{9}" required>
                        </div>
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

                    <button type="submit" class="btn btn-dark w-100">Register</button>
                </form>

                <div class="text-center mt-3 mb-5">
                    <small>Already have an account? <a href="login.php">Login</a></small> <!-- Login link -->
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

        document.getElementById("toggleConfirmPassword").addEventListener("click", function () {
            const confirmPasswordField = document.getElementById("confirmPassword");
            const icon = this;
            if (confirmPasswordField.type === "password") {
                confirmPasswordField.type = "text";
                icon.classList.remove("bi-eye-slash");
                icon.classList.add("bi-eye");
            } else {
                confirmPasswordField.type = "password";
                icon.classList.remove("bi-eye");
                icon.classList.add("bi-eye-slash");
            }
        });
    </script>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" defer></script>
</body>

<footer class="bg-dark text-white pt-5 pb-4">
    <div class="container text-md-left">
        <div class="row text-center text-md-left">
            <div class="col-md-6 col-lg-6 col-xl-6 mx-auto mt-3">
                <h5 class="mb-4 fw-bold">SkillUp Academy</h5>
                <p>Empowering learners with the skills they need to succeed in the digital world.</p>
                <a href="../policies.php" class="text-white text-decoration-none">Academy policies &rarr;</a>
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
