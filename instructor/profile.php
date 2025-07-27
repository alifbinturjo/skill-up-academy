<?php
session_start();
include '../auth/cnct.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== "Instructor") {
    session_unset();
    session_destroy();
    $conn->close();
    header("Location: ../index.php");
    exit();
}

$u_id = $_SESSION['u_id'];
$errors = [];
$success = false;

// Handle profile update (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['change_password'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $contact = trim($_POST['contact'] ?? '');

    $skills = implode(', ', array_filter([
        $_POST['skill1'] ?? '',
        $_POST['skill2'] ?? '',
        $_POST['skill3'] ?? ''
    ]));

    if (empty($name)) {
        $errors[] = "Name is required";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE users SET name=?, email=?, contact=? WHERE u_id=?");
        $stmt->bind_param("sssi", $name, $email, $contact, $u_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("UPDATE instructors SET bio=?, skills=? WHERE u_id=?");
        $stmt->bind_param("ssi", $bio, $skills, $u_id);
        $stmt->execute();
        $stmt->close();

        // Refetch updated data
        $success = true;
    }
}

// Fetch profile data
$sql = "SELECT u.name, u.email, u.contact, i.title, i.bio, i.skills, i.domain, i.image
        FROM users u
        JOIN instructors i ON u.u_id = i.u_id
        WHERE u.u_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $u_id);
$stmt->execute();
$stmt->bind_result($name, $email, $contact, $title, $bio, $skills, $domain, $image);
$stmt->fetch();
$stmt->close();

$skillsArray = explode(',', $skills ?? '');
$image = $image ? ('../' . $image) : '../image-assets/Instructors/default.webp';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | SkillUp Academy</title>
    <link rel="prefetch" href="../image-assets/common/fav.webp" as="image">
    <link rel="icon" href="../image-assets/common/fav.webp" type="image/webp">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <link rel="preload" href="../style.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="../style.css">
    </noscript>
</head>

<body>
    <script>
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (empty($current) || empty($new) || empty($confirm)) {
            $errors[] = "All password fields are required.";
        } elseif ($new !== $confirm) {
            $errors[] = "New passwords do not match.";
        } else {
            // Check current password
            $stmt = $conn->prepare("SELECT pass FROM credentials WHERE u_id = ?");
            $stmt->bind_param("i", $u_id);
            $stmt->execute();
            $stmt->bind_result($hashed_password);
            $stmt->fetch();
            $stmt->close();

            if (!password_verify($current, $hashed_password)) {
                $errors[] = "Current password is incorrect.";
            } else {
                // Update password
                $new_hashed = password_hash($new, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE credentials SET pass = ? WHERE u_id = ?");
                $stmt->bind_param("si", $new_hashed, $u_id);
                $stmt->execute();
                $stmt->close();
                $success = true;
            }
        }
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
                        <a class="nav-link" href="../index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../auth/logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card card-h shadow-lg border-0 rounded">
                        <div class="card-body p-5">
                            <div class="text-center mb-5">
                                <h1 class="fw-bold">Profile</h1>
                                <p class="lead text-muted">Manage your account information and settings</p>
                            </div>

                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger alert-dismissible fade show mb-4">
                                    <?php foreach ($errors as $e): ?>
                                        <p class="mb-0"><?= htmlspecialchars($e) ?></p>
                                    <?php endforeach; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php elseif ($success): ?>
                                <div class="alert alert-success alert-dismissible fade show mb-4">
                                    Profile updated successfully.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <div class="row align-items-center mb-5">
                                <div class="col-md-4 text-center">
                                    <img src="<?= htmlspecialchars($image) ?>" loading="lazy" class="rounded-circle shadow mb-4" width="150" height="150" alt="Profile Image">
                                    <h3 class="fw-bold"><?= htmlspecialchars($name) ?></h3>
                                    <p class="text-muted"><?= htmlspecialchars($title) ?></p>
                                    <span class="badge bg-primary"><?= htmlspecialchars($domain) ?></span>
                                </div>
                                <div class="col-md-8">
                                    <div class="mb-4">
                                        <h4 class="fw-bold">About</h4>
                                        <p class="text-muted"><?= nl2br(htmlspecialchars($bio)) ?></p>
                                    </div>

                                    <div class="mb-4">
                                        <h4 class="fw-bold">Skills</h4>
                                        <div class="d-flex flex-wrap gap-2">
                                            <?php foreach ($skillsArray as $skill): ?>
                                                <span class="badge bg-dark text-light p-2"><?= htmlspecialchars($skill) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <h4 class="fw-bold">Contact Information</h4>
                                        <div class="d-flex flex-column gap-2">
                                            <div>
                                                <i class="bi bi-envelope me-2 text-muted"></i>
                                                <a href="mailto:<?= htmlspecialchars($email) ?>" class="text-decoration-none"><?= htmlspecialchars($email) ?></a>
                                            </div>
                                            <div>
                                                <i class="bi bi-telephone me-2 text-muted"></i>
                                                <span>+880<?= htmlspecialchars(substr($contact, 0)) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-3 d-md-flex justify-content-md-center mt-4">
                                <button type="button" class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                                    <i class="bi bi-pencil-square me-2"></i>Edit Profile
                                </button>
                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                    <i class="bi bi-key me-2"></i>Change Password
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Edit Profile</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Name</label>
                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($_POST['name'] ?? $name) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? $email) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="contact" class="form-control" value="<?= htmlspecialchars($_POST['contact'] ?? $contact) ?>" placeholder="e.g., 017XXXXXXXX" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Domain</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($domain) ?>" disabled>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Bio</label>
                                <textarea name="bio" class="form-control" rows="4"><?= htmlspecialchars($_POST['bio'] ?? $bio) ?></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Skills (Up to 3)</label>
                                <div class="row g-2">
                                    <?php for ($i = 0; $i < 3; $i++): ?>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" name="skill<?= $i + 1 ?>" value="<?= htmlspecialchars($_POST["skill" . ($i + 1)] ?? $skillsArray[$i] ?? '') ?>" placeholder="Skill <?= $i + 1 ?>">
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Change Password</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="change_password" class="btn btn-danger">Change Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
</body>

</html>