<?php
session_start();
include '../auth/cnct.php';

if(!isset($_SESSION['role']) || $_SESSION['role']!=="Admin"){
  session_unset();
  session_destroy();
  $conn->close();
  header("Location: ../index.php");
  exit();
}

function clean($data) {
    return htmlspecialchars(trim($data));
}

// Domain mapping for display
$domainMap = [
    'web' => 'Web Development',
    'data' => 'Data Science',
    'ai' => 'AI & ML'
];

// Add Course
if (isset($_POST['action']) && $_POST['action'] === 'add_course') {
    $title = clean($_POST['title']);
    $amount = (int)$_POST['amount'];
    $description = clean($_POST['description']);
    $domain = clean($_POST['domain']);
    $duration = (int)$_POST['duration'];
    $u_id = (int)$_POST['instructor'];
    $status = 'active'; // Default status

    if (!$title || !$amount || !$description || !$domain || !$duration || !$u_id) {
        $_SESSION['message'] = "Please fill all required fields.";
        $_SESSION['message_type'] = "danger";
        header("Location: courses.php");
        exit;
    }

    $start_date = $_POST['start_date'];

$stmt = $conn->prepare("INSERT INTO courses (title, amount, description, domain, duration, u_id, start_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sississs", $title, $amount, $description, $domain, $duration, $u_id, $start_date, $status);

    $executed = $stmt->execute();
    $_SESSION['message'] = $executed ? "Course added successfully!" : "Failed to add course.";
    $_SESSION['message_type'] = $executed ? "success" : "danger";
    $stmt->close();
    header("Location: courses.php");
    exit;
}

// Delete Course
if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM courses WHERE c_id = ?");
    $stmt->bind_param("i", $del_id);
    $executed = $stmt->execute();
    $_SESSION['message'] = $executed ? "Course removed successfully!" : "Failed to remove course.";
    $_SESSION['message_type'] = $executed ? "success" : "danger";
    $stmt->close();
    header("Location: courses.php");
    exit;
}

// Edit Course
if (isset($_POST['action']) && $_POST['action'] === 'edit_course') {
    $c_id = (int)$_POST['c_id'];
    $title = clean($_POST['title']);
    $amount = (int)$_POST['amount'];
    $description = clean($_POST['description']);
    $domain = clean($_POST['domain']);
    $duration = (int)$_POST['duration'];
    $u_id = (int)$_POST['instructor'];

    if (empty($c_id) || !$title || !$amount || !$description || !$domain || !$duration || !$u_id) {
        $_SESSION['message'] = "Please fill all required fields.";
        $_SESSION['message_type'] = "danger";
        header("Location: courses.php");
        exit;
    }

    $start_date = $_POST['start_date'];
$stmt = $conn->prepare("UPDATE courses SET title = ?, amount = ?, description = ?, domain = ?, duration = ?, u_id = ?, start_date = ? WHERE c_id = ?");
$stmt->bind_param("sissiiisi", $title, $amount, $description, $domain, $duration, $u_id, $start_date, $c_id);

    $executed = $stmt->execute();
    $_SESSION['message'] = $executed ? "Course updated successfully!" : "Failed to update course.";
    $_SESSION['message_type'] = $executed ? "success" : "danger";
    $stmt->close();
    header("Location: courses.php");
    exit;
}

// Fetch Courses
$sql = "SELECT c.c_id, c.title, c.amount, c.description, c.domain, c.duration, c.u_id, c.start_date, u.name as instructor_name 
        FROM courses c
        LEFT JOIN instructors i ON c.u_id = i.u_id
        LEFT JOIN users u ON i.u_id = u.u_id
        ORDER BY c.title ASC";
$result = $conn->query($sql);
$courses = $result->fetch_all(MYSQLI_ASSOC);

// Fetch Instructors for dropdown
$instructor_sql = "SELECT i.u_id, u.name 
                   FROM instructors i
                   JOIN users u ON i.u_id = u.u_id
                   ORDER BY u.name ASC";
$instructor_result = $conn->query($instructor_sql);
$instructors = $instructor_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Courses</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../style.css">
</head>

<body>

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
            <a class="nav-link active" href="">Dashboard</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="../auth/logout.php">Logout</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container-fluid mt-3 mb-5 px-3">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card bg-transparent card-h shadow-lg border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-center align-items-center mb-4">
                        <div class="text-center">
                            <p class="fs-1 mb-2">Courses</p>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                                Add Course
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>

  <?php if (isset($_SESSION['message'])): ?>
    <div class="position-fixed bottom-0 start-50 translate-middle-x mb-3" style="z-index: 1080;">
      <div class="toast align-items-center text-bg-<?= $_SESSION['message_type'] ?> show" role="alert">
        <div class="d-flex">
          <div class="toast-body"><?= $_SESSION['message'] ?></div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
      </div>
    </div>
    <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
  <?php endif; ?>

  <!-- Courses Table -->
  <div class="card bg-transparent shadow-lg border-0 mt-4">
    <div class="card-body bg-transparent">
      <h4 class="mb-4 text-center">List of Courses</h4>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="bg-transparent text-dark text-start">
            <tr>
              <th class="bg-transparent ps-4">Course</th>
              <th class="bg-transparent">Domain</th>
              <th class="bg-transparent">Duration</th>
              <th class="bg-transparent">Instructor</th>
              <th class="bg-transparent">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($courses) === 0): ?>
              <tr class="bg-transparent text-dark">
                <td colspan="5" class="bg-transparent text-center">No courses found.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($courses as $course): ?>
                <tr class="bg-transparent text-dark">
                  <td class="bg-transparent">
                    <div class="d-flex justify-content-between align-items-center px-3 py-2 border border-dark rounded">
                      <span class="ps-2"><?= htmlspecialchars($course['title']) ?></span>
                    </div>
                  </td>
                  <td class="bg-transparent"><?= htmlspecialchars($domainMap[$course['domain']] ?? $course['domain']) ?></td>
                  <td class="bg-transparent"><?= htmlspecialchars($course['duration']) ?> weeks</td>
                  <td class="bg-transparent"><?= htmlspecialchars($course['instructor_name'] ?? 'Not assigned') ?></td>
                  <td class="bg-transparent">
                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal"
                      data-bs-target="#editModal<?= $course['c_id'] ?>">Edit</button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Add Course Modal -->
  <div class="modal fade" id="addCourseModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content border border-dark">
        <div class="modal-header border-0">
          <h5 class="modal-title">Add New Course</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form method="POST" action="courses.php">
            <input type="hidden" name="action" value="add_course">
            <div class="mb-2">
              <label class="form-label">Course Title <span class="text-danger">*</span></label>
              <input type="text" class="form-control border-dark" name="title" required>
            </div>
            <div class="mb-2">
              <label class="form-label">Description <span class="text-danger">*</span></label>
              <textarea class="form-control border-dark" name="description" rows="3" required></textarea>
            </div>
            <div class="mb-2">
              <label class="form-label">Domain <span class="text-danger">*</span></label>
              <select class="form-select border-dark" name="domain" required>
                <option value="" selected disabled>Select Domain</option>
                <option value="web">Web Development</option>
                <option value="data">Data Science</option>
                <option value="ai">AI & ML</option>
              </select>
            </div>
            <div class="mb-2">
  <label class="form-label">Start Date <span class="text-danger">*</span></label>
  <input type="date" class="form-control border-dark" name="start_date" required>
</div>

            <div class="mb-2">
              <label class="form-label">Duration (weeks) <span class="text-danger">*</span></label>
              <select class="form-select border-dark" name="duration" required>
                <option value="" selected disabled>Select duration</option>
                <option value="3">3 weeks</option>
                <option value="5">5 weeks</option>
                <option value="8">8 weeks</option>
                <option value="10">10 weeks</option>
                <option value="12">12 weeks</option>
                <option value="15">15 weeks</option>
                <option value="18">18 weeks</option>
              </select>
            </div>
            <div class="mb-2">
              <label class="form-label">Price (BDT) <span class="text-danger">*</span></label>
              <input type="number" class="form-control border-dark" name="amount" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Instructor <span class="text-danger">*</span></label>
              <select class="form-select border-dark" name="instructor" required>
                <option value="" selected disabled>Select Instructor</option>
                <?php foreach ($instructors as $instructor): ?>
                  <option value="<?= $instructor['u_id'] ?>"><?= htmlspecialchars($instructor['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="d-flex justify-content-between">
              <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-success btn-sm">Add Course</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Course Modals -->
  <?php foreach ($courses as $course): ?>
    <div class="modal fade" id="editModal<?= $course['c_id'] ?>" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content border border-dark">
          <div class="modal-header border-0">
            <h5 class="modal-title">Edit Course</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form method="POST" action="courses.php">
              <input type="hidden" name="action" value="edit_course">
              <input type="hidden" name="c_id" value="<?= $course['c_id'] ?>">
              <div class="mb-2">
                <label class="form-label">Course Title</label>
                <input type="text" class="form-control border-dark" name="title" value="<?= htmlspecialchars($course['title']) ?>" required>
              </div>
              <div class="mb-2">
                <label class="form-label">Description</label>
                <textarea class="form-control border-dark" name="description" rows="3" required><?= htmlspecialchars($course['description']) ?></textarea>
              </div>
              <div class="mb-2">
                <label class="form-label">Domain</label>
                <select class="form-select border-dark" name="domain" required>
                  <option value="web" <?= $course['domain'] === 'web' ? 'selected' : '' ?>>Web Development</option>
                  <option value="data" <?= $course['domain'] === 'data' ? 'selected' : '' ?>>Data Science</option>
                  <option value="ai" <?= $course['domain'] === 'ai' ? 'selected' : '' ?>>AI & ML</option>
                </select>
              </div>
              <div class="mb-2">
                 <label class="form-label">Start Date</label>
                    <input type="date" class="form-control border-dark" name="start_date" value="<?= $course['start_date'] ?>" required>
                 </div>

              <div class="mb-2">
                <label class="form-label">Duration (weeks)</label>
                <select class="form-select border-dark" name="duration" required>
                  <option value="3" <?= $course['duration'] == 3 ? 'selected' : '' ?>>3 weeks</option>
                  <option value="5" <?= $course['duration'] == 5 ? 'selected' : '' ?>>5 weeks</option>
                  <option value="8" <?= $course['duration'] == 8 ? 'selected' : '' ?>>8 weeks</option>
                  <option value="10" <?= $course['duration'] == 10 ? 'selected' : '' ?>>10 weeks</option>
                  <option value="12" <?= $course['duration'] == 12 ? 'selected' : '' ?>>12 weeks</option>
                  <option value="15" <?= $course['duration'] == 15 ? 'selected' : '' ?>>15 weeks</option>
                  <option value="18" <?= $course['duration'] == 18 ? 'selected' : '' ?>>18 weeks</option>
                </select>
              </div>
              <div class="mb-2">
                <label class="form-label">Price (BDT)</label>
                <input type="number" class="form-control border-dark" name="amount" value="<?= htmlspecialchars($course['amount']) ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Instructor</label>
                <select class="form-select border-dark" name="instructor" required>
                  <?php foreach ($instructors as $instructor): ?>
                    <option value="<?= $instructor['u_id'] ?>" <?= $course['u_id'] == $instructor['u_id'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($instructor['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $course['c_id'] ?>)">Remove</button>
                <button type="submit" class="btn btn-success btn-sm">Save Changes</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function confirmDelete(courseId) {
      if (confirm("Are you sure you want to remove this course?")) {
        window.location.href = `courses.php?delete_id=${courseId}`;
      }
    }
  </script>
</body>
</html>