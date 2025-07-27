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

        // ✅ FIXED LINE: Changed column name from `pass` to `password`
        $insertCredentialsQuery = "INSERT INTO credentials (u_id, password) VALUES (?, ?)";
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

