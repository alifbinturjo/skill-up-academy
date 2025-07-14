<?php
session_start();
include '../auth/cnct.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'instructor') {
    $_SESSION['message'] = "Unauthorized access.";
    $_SESSION['message_type'] = "danger";
    header("Location: ../auth/login.php");
    exit;
}

function clean($data) {
    return htmlspecialchars(trim($data));
}

// AJAX Email Validation
if (isset($_POST['action']) && $_POST['action'] === 'validate_email') {
    $email = $_POST['email'];
    $stmt = $conn->prepare("SELECT u_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        echo json_encode(["exists" => false]);
        exit;
    }

    $user = $res->fetch_assoc();
    $u_id = $user['u_id'];

    $stmt = $conn->prepare("SELECT u_id FROM instructors WHERE u_id = ?");
    $stmt->bind_param("i", $u_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        echo json_encode(["exists" => true, "already_instructor" => true, "valid" => false]);
        exit;
    }

    echo json_encode(["exists" => true, "valid" => true]);
    exit;
}

// Add Instructor
if (isset($_POST['action']) && $_POST['action'] === 'add_instructor') {
    $email = clean($_POST['email']);
    $domain = clean($_POST['domain']);
    $title = clean($_POST['title']);

    if (!$email || !$domain || !$title) {
        $_SESSION['message'] = "Please fill all required fields.";
        $_SESSION['message_type'] = "danger";
        header("Location: instructors.php");
        exit;
    }

    $stmt = $conn->prepare("SELECT u_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $_SESSION['message'] = "User email is not registered.";
        $_SESSION['message_type'] = "danger";
        $stmt->close();
        header("Location: instructors.php");
        exit;
    }
    $stmt->bind_result($u_id);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO instructors (u_id, domain, title) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $u_id, $domain, $title);
    $executed = $stmt->execute();

    if ($executed) {
        // Delete from students table after successful insertion into instructors
        $stmt_del = $conn->prepare("DELETE FROM students WHERE u_id = ?");
        $stmt_del->bind_param("i", $u_id);
        $stmt_del->execute();
        $stmt_del->close();
    }

    $_SESSION['message'] = $executed ? "Instructor added successfully." : "Failed to add instructor.";
    $_SESSION['message_type'] = $executed ? "success" : "danger";
    $stmt->close();
    header("Location: instructors.php");
    exit;
}

// Delete Instructor
if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM instructors WHERE u_id = ?");
    $stmt->bind_param("i", $del_id);
    $executed = $stmt->execute();
    $_SESSION['message'] = $executed ? "Instructor removed successfully." : "Failed to remove instructor.";
    $_SESSION['message_type'] = $executed ? "success" : "danger";
    $stmt->close();
    header("Location: instructors.php");
    exit;
}

// Edit Instructor
if (isset($_POST['action']) && $_POST['action'] === 'edit_instructor') {
    $u_id = (int)$_POST['u_id'];
    $domain = clean($_POST['domain']);
    $title = clean($_POST['title']);

    if (empty($u_id) || !is_numeric($u_id)) {
        $_SESSION['message'] = "Invalid instructor ID";
        $_SESSION['message_type'] = "danger";
        header("Location: instructors.php");
        exit;
    }

    $stmt = $conn->prepare("UPDATE instructors SET domain = ?, title = ? WHERE u_id = ?");
    $stmt->bind_param("ssi", $domain, $title, $u_id);
    $executed = $stmt->execute();
    $_SESSION['message'] = $executed ? "Instructor updated successfully." : "Failed to update instructor.";
    $_SESSION['message_type'] = $executed ? "success" : "danger";
    $stmt->close();
    header("Location: instructors.php");
    exit;
}

// Fetch Instructors
$filter_name = $_GET['filter_name'] ?? '';
$filter_domain = $_GET['filter_domain'] ?? '';
$filter_title = $_GET['filter_title'] ?? '';
$sort_by = $_GET['sort_by'] ?? 'name_asc';

$sql = "SELECT i.u_id, u.email, u.name, u.contact, i.domain, i.title
        FROM instructors i
        JOIN users u ON i.u_id = u.u_id
        WHERE 1=1 ";
$params = [];
$types = "";

if ($filter_name) {
    $sql .= " AND u.name LIKE ? ";
    $params[] = "%$filter_name%";
    $types .= "s";
}
if ($filter_domain) {
    $sql .= " AND i.domain = ? ";
    $params[] = $filter_domain;
    $types .= "s";
}
if ($filter_title) {
    $sql .= " AND i.title = ? ";
    $params[] = $filter_title;
    $types .= "s";
}

$allowed_sorts = [
    'name_asc' => 'u.name ASC',
    'name_desc' => 'u.name DESC',
    'domain_asc' => 'i.domain ASC',
    'domain_desc' => 'i.domain DESC',
    'title_asc' => 'i.title ASC',
    'title_desc' => 'i.title DESC'
];
$order_by = $allowed_sorts[$sort_by] ?? 'u.name ASC';
$sql .= " ORDER BY $order_by";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$instructors = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>instructors - SkillUp Academy</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../style.css" />
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
        <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="courses.php">Courses</a></li>
        <li class="nav-item"><a class="nav-link active" href="instructors.php">Instructors</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Admins</a></li>
        <li class="nav-item"><a class="nav-link" href="students.php">Students</a></li>
        <li class="nav-item"><a class="nav-link" href="post-notices.php">Notices</a></li>
        <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container-fluid mt-3 mb-5 px-3">
  <div class="row justify-content-center">
    <div class="col-12">
      <div class="card bg-transparent card-h shadow-lg border-0">
        <div class="card-body">
          <p class="text-center fs-1 mb-4">Instructors</p>
          
          <!-- Add Instructor Button -->
          <div class="d-grid">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInstructorModal">
              Add Instructors
            </button>
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

<!-- Filters -->
<div class="card bg-transparent shadow-lg border-0 mt-4">
  <div class="card-body bg-transparent p-4">
    <h4 class="text-center mb-4">Filter & Sort Instructors</h4>
    <form method="GET" class="row g-3 align-items-end">

      <div class="col-md-3 ps-2">
        <label for="filter-name" class="form-label small">Name</label>
        <input
          type="text"
          class="form-control form-control-sm bg-transparent border border-dark text-dark"
          id="filter-name"
          name="filter_name"
          placeholder="Enter name"
          value="<?= htmlspecialchars($filter_name) ?>"
        />
      </div>

      <div class="col-md-3">
        <label class="form-label small">Domain</label>
        <select name="filter_domain" class="form-select form-select-sm bg-transparent border border-dark text-dark">
          <option value="">All Domains</option>
          <option value="programming" <?= $filter_domain==='programming'?'selected':'' ?>>Programming</option>
          <option value="problem-solving" <?= $filter_domain==='problem-solving'?'selected':'' ?>>Problem Solving</option>
          <option value="web-development" <?= $filter_domain==='web-development'?'selected':'' ?>>Web Development</option>
          <option value="android-development" <?= $filter_domain==='android-development'?'selected':'' ?>>Android Development</option>
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label small">Title</label>
        <select name="filter_title" class="form-select form-select-sm bg-transparent border border-dark text-dark">
          <option value="">All Titles</option>
          <option value="junior" <?= $filter_title==='junior'?'selected':'' ?>>Junior</option>
          <option value="instructor" <?= $filter_title==='instructor'?'selected':'' ?>>Instructor</option>
          <option value="senior" <?= $filter_title==='senior'?'selected':'' ?>>Senior</option>
        </select>
      </div>

      <div class="col-md-3 pe-2">
        <label class="form-label small">Sort By</label>
        <select name="sort_by" class="form-select form-select-sm bg-transparent border border-dark text-dark">
          <option value="name_asc" <?= $sort_by==='name_asc'?'selected':'' ?>>Name (A-Z)</option>
          <option value="name_desc" <?= $sort_by==='name_desc'?'selected':'' ?>>Name (Z-A)</option>
          <option value="domain_asc" <?= $sort_by==='domain_asc'?'selected':'' ?>>Domain (A-Z)</option>
          <option value="domain_desc" <?= $sort_by==='domain_desc'?'selected':'' ?>>Domain (Z-A)</option>
          <option value="title_asc" <?= $sort_by==='title_asc'?'selected':'' ?>>Title (A-Z)</option>
          <option value="title_desc" <?= $sort_by==='title_desc'?'selected':'' ?>>Title (Z-A)</option>
        </select>
      </div>

      <div class="col-12 text-center mt-2">
        <button class="btn btn-success btn-sm me-2">Apply</button>
        <a href="instructors.php" class="btn btn-warning btn-sm">Reset</a>
      </div>
    </form>
  </div>
</div>

<!-- Instructors Table -->
<div class="card bg-transparent shadow-lg border-0 mt-4">
  <div class="card-body bg-transparent p-4">
    <h4 class="mb-4 text-center text-dark">List of Instructors</h4>
    <div class="table-responsive">
      <table class="table table-bordered border-dark text-center align-middle mb-0">
        <thead class="text-dark">
          <tr class="bg-transparent">
            <th class="bg-transparent border-dark">Name</th>
            <th class="bg-transparent border-dark">Email</th>
            <th class="bg-transparent border-dark">Domain</th>
            <th class="bg-transparent border-dark">Title</th>
            <th class="bg-transparent border-dark">Actions</th>
          </tr>
        </thead>
        <tbody class="text-dark">
          <?php if (count($instructors) === 0): ?>
            <tr>
              <td colspan="5" class="bg-transparent border-dark">No instructors found.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($instructors as $ins): ?>
              <tr class="bg-transparent">
                <td class="bg-transparent border-dark"><?= htmlspecialchars($ins['name']) ?></td>
                <td class="bg-transparent border-dark"><?= htmlspecialchars($ins['email']) ?></td>
                <td class="bg-transparent border-dark"><?= htmlspecialchars($ins['domain']) ?></td>
                <td class="bg-transparent border-dark"><?= htmlspecialchars($ins['title']) ?></td>
                <td class="bg-transparent border-dark">
                  <button class="btn btn-sm btn-warning me-2"
                    data-bs-toggle="modal"
                    data-bs-target="#editModal"
                    data-u_id="<?= $ins['u_id'] ?>"
                    data-domain="<?= htmlspecialchars($ins['domain']) ?>"
                    data-title="<?= htmlspecialchars($ins['title']) ?>">
                    Edit
                  </button>
                  <button class="btn btn-sm btn-info"
                    data-bs-toggle="modal"
                    data-bs-target="#contactModal"
                    data-name="<?= htmlspecialchars($ins['name']) ?>"
                    data-email="<?= htmlspecialchars($ins['email']) ?>"
                    data-contact="<?= htmlspecialchars($ins['contact'] ?? 'Not provided') ?>">
                    Contact
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Add Instructor Modal -->
<div class="modal fade" id="addInstructorModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" onsubmit="return validateAddForm()" class="modal-content">
      <input type="hidden" name="action" value="add_instructor">
      <div class="modal-header">
        <h5 class="modal-title">Add Instructor</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Email <span class="text-danger">*</span></label>
          <input type="email" class="form-control" name="email" required oninput="checkEmail()">
          <div id="emailFeedback" class="form-text"></div>
        </div>
        <div class="mb-3">
          <label class="form-label">Domain</label>
          <select class="form-select" name="domain" required>
            <option value="programming">Programming</option>
            <option value="problem-solving">Problem Solving</option>
            <option value="web-development">Web Development</option>
            <option value="android-development">Android Development</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Title</label>
          <select class="form-select" name="title" required>
            <option value="junior">Junior Instructor</option>
            <option value="instructor">Instructor</option>
            <option value="senior">Senior Instructor</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" id="addInstructorSubmit" class="btn btn-success btn-sm" disabled>Add Instructor</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Instructor Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <input type="hidden" name="action" value="edit_instructor">
      <input type="hidden" name="u_id" id="edit_u_id">
      <div class="modal-header">
        <h5 class="modal-title">Edit Instructor</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Domain</label>
          <select class="form-select" id="edit_domain" name="domain" required>
            <option value="programming">Programming</option>
            <option value="problem-solving">Problem Solving</option>
            <option value="web-development">Web Development</option>
            <option value="android-development">Android Development</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Title</label>
          <select class="form-select" id="edit_title" name="title" required>
            <option value="junior">Junior Instructor</option>
            <option value="instructor">Instructor</option>
            <option value="senior">Senior Instructor</option>
          </select>
        </div>
      </div>
      <div class="modal-footer d-flex justify-content-between">
        <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete()">Remove</button>
        <div>
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary btn-sm ms-2">Save</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Contact Modal -->
<div class="modal fade" id="contactModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="contactModalTitle">Contact Information</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <p><strong>Email:</strong> <span id="contactEmail">Not provided</span></p>
        </div>
        <div class="mb-3">
          <p><strong>Contact No:</strong> <span id="contactPhone">Not provided</span></p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Email validation
  function checkEmail() {
    const email = document.querySelector("#addInstructorModal input[name='email']").value.trim();
    const feedback = document.getElementById("emailFeedback");
    const submitBtn = document.getElementById("addInstructorSubmit");

    if (!email) {
      feedback.textContent = "";
      submitBtn.disabled = true;
      return;
    }

    fetch("instructors.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded"
      },
      body: `action=validate_email&email=${encodeURIComponent(email)}`
    })
    .then(res => res.json())
    .then(data => {
      if (data.exists && !data.already_instructor) {
        feedback.textContent = "✅ Valid user";
        feedback.className = "form-text text-success";
        submitBtn.disabled = false;
      } else if (data.already_instructor) {
        feedback.textContent = "❌ User is already an instructor";
        feedback.className = "form-text text-danger";
        submitBtn.disabled = true;
      } else {
        feedback.textContent = "❌ Email not registered";
        feedback.className = "form-text text-danger";
        submitBtn.disabled = true;
      }
    })
    .catch(() => {
      feedback.textContent = "❌ Validation error";
      feedback.className = "form-text text-danger";
      submitBtn.disabled = true;
    });
  }

  // Form validation
  function validateAddForm() {
    const email = document.querySelector("#addInstructorModal input[name='email']").value.trim();
    if (!email) {
      document.getElementById("emailFeedback").textContent = "Please enter an email";
      return false;
    }
    return true;
  }

  // Delete confirmation
  function confirmDelete() {
    const u_id = document.getElementById("edit_u_id").value;
    if (confirm("Are you sure you want to remove this instructor?")) {
      window.location.href = `instructors.php?delete_id=${u_id}`;
    }
  }

  // Edit Modal Logic
  const editModal = document.getElementById('editModal');
  if (editModal) {
    editModal.addEventListener('show.bs.modal', event => {
      const button = event.relatedTarget;
      const u_id = button.getAttribute('data-u_id');
      const domain = button.getAttribute('data-domain');
      const title = button.getAttribute('data-title');

      document.getElementById('edit_u_id').value = u_id;
      document.getElementById('edit_domain').value = domain;
      document.getElementById('edit_title').value = title;
    });
  }

  // Contact Modal Logic
  const contactModal = document.getElementById('contactModal');
  if (contactModal) {
    contactModal.addEventListener('show.bs.modal', event => {
      const button = event.relatedTarget;
      const name = button.getAttribute('data-name');
      const email = button.getAttribute('data-email');
      const contact = button.getAttribute('data-contact');

      document.getElementById('contactEmail').textContent = email || 'Not provided';
      document.getElementById('contactPhone').textContent = contact || 'Not provided';
      document.getElementById('contactModalTitle').textContent = `Contact ${name}`;
    });
  }

  // Add Modal Reset
  const addModal = document.getElementById('addInstructorModal');
  if (addModal) {
    addModal.addEventListener('hidden.bs.modal', () => {
      addModal.querySelector('form').reset();
      document.getElementById('emailFeedback').textContent = '';
      document.getElementById('addInstructorSubmit').disabled = true;
    });
  }
</script>

</body>
</html>