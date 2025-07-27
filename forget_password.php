<?php
// Include PHPMailer's autoloader
require 'vendor/autoload.php';  // Ensure PHPMailer is installed via Composer

// Use PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';


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
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'mail.skillup.mynsu.xyz'; // Your mail server
    $mail->SMTPAuth = true;
    $mail->Username = 'auth@skillup.mynsu.xyz'; // Your email
    $mail->Password = ';*MZB-k]6D@z';     // Use the real password
    $mail->Port = 587; // From cPanel (SMTP Port)

    // Use TLS, since SSL is not required, but encryption is
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

    // Sender and recipient
    $mail->setFrom('auth@skillup.mynsu.xyz', 'SkillUp Academy');
    $mail->addAddress($email); // Recipient email

    // Content
    $mail->isHTML(false); // Use true if you're sending HTML emails
    $mail->Subject = 'Your Temporary Password';
    $mail->Body = 'Your temporary password is: ' . $randomPassword;

    $mail->send();
    $_SESSION['success'] = 'A temporary password has been sent to your email.';
} catch (Exception $e) {
    $_SESSION['error'] = 'Mailer Error: ' . $mail->ErrorInfo;
}
    } else {
        $_SESSION['error'] = 'Email not found in our system.';
    }

    // Redirect to login page after processing
    header('Location: ../auth/login.php'); 
    exit();
}
?>



