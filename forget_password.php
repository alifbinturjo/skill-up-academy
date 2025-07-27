<?php
// Include PHPMailer's autoloader
require 'vendor/autoload.php';  // Ensure PHPMailer is installed via Composer

// Use PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Start session and handle POST request
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['emailReset'];  // Get email from the form

    // Validate the email
    if (empty($email)) {
        $_SESSION['error'] = 'Please enter your email.';
        header('Location: auth/login.php');  // Redirect back to login page
        exit();
    }

    // Include database connection (correct path)
    include 'auth/cnct.php'; 

    // Check if the email exists in the database
    $query = "SELECT u_id, email FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($userId, $dbEmail);
    $stmt->fetch();
    $stmt->close();

    if ($dbEmail) {
        // Generate a random temporary password
        $randomPassword = bin2hex(random_bytes(4));  // Generates 8-character password (4 bytes)

        // Hash the temporary password before saving it to the database
        $hashedPassword = password_hash($randomPassword, PASSWORD_DEFAULT);

        // Update the credentials table with the new temporary password
        $updateQuery = "UPDATE credentials SET pass = ? WHERE u_id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("si", $hashedPassword, $userId);
        $updateStmt->execute();
        $updateStmt->close();

        // Now send the temporary password to the user's email using PHPMailer
        $mail = new PHPMailer(true);  // Create PHPMailer instance
        $mail->SMTPDebug = 2;  // Enable verbose debug output
        $mail->Debugoutput = 'html';  // Output in HTML format (or 'text' for plain text)

        try {
            // Set up PHPMailer to use SMTP
            $mail->isSMTP();
            $mail->Host = 'mail.skillup.mynsu.xyz';  // Gmail SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'auth@skillup.mynsu.xyz';  // Replace with your Gmail email address
            $mail->Password = ';*MZB-k]6D@z';     // Use the generated app password (if using 2FA)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // TLS encryption
            $mail->Port = 587;  // SMTP port for TLS

            // Set email sender and recipient
            $mail->setFrom('auth@skillup.mynsu.xyz', 'SkillUp Academy');
            $mail->addAddress($email);  // User's email

            // Set email subject and body
            $mail->Subject = 'Your Temporary Password';
            $mail->Body = 'Your temporary password is: ' . $randomPassword;  // The temporary password generated

            // Send the email
            $mail->send();
            $_SESSION['success'] = 'A temporary password has been sent to your email.';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Failed to send reset email: ' . $mail->ErrorInfo;
        }
    } else {
        $_SESSION['error'] = 'Email not found in our system.';
    }

    // Redirect to login page after processing
    header('Location: auth/login.php'); 
    exit();
}
?>



