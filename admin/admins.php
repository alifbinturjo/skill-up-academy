<?php
session_start();
include '../auth/cnct.php';

if(!isset($_SESSION['role'])&&$_SESSION['role']!=="Admin"){
  session_unset();
  session_destroy();
  $conn->close();
  header("Location: ../index.php");
  exit();
}

// Handle AJAX request for email validation
if (isset($_POST['check_email'])) {
    $email = $_POST['check_email'];

    // Check if email exists in users table
  $stmt = $conn->prepare("
    SELECT u.u_id, a.u_id AS is_admin
    FROM users u
    LEFT JOIN admins a ON u.u_id = a.u_id
    WHERE u.email = ?
");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();

if ($res && $res->num_rows > 0) {
    $user = $res->fetch_assoc();
    if ($user['is_admin']) {
        echo json_encode(["status" => "exists", "message" => "This user is already an admin."]);
    } else {
        echo json_encode(["status" => "valid", "message" => "User exists and can be added."]);
    }
    exit;
} else {
    echo json_encode(["status" => "not_found", "message" => "User email is not registered."]);
    exit;
}

}

// Initialize filter and sort variables for the listing
$filter_name = isset($_GET['filter_name']) ? trim($_GET['filter_name']) : '';
$filter_level = isset($_GET['filter_level']) ? (int)$_GET['filter_level'] : 0;
$sort_option = isset($_GET['sort_option']) ? $_GET['sort_option'] : 'name-asc';

// ADD / UPDATE / REMOVE ADMIN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_admin'])) {
        $email = $_POST['email'];
        $level = (int)$_POST['level'];

        $stmt = $conn->prepare("SELECT u_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $check_result = $stmt->get_result();

        if ($check_result && $check_result->num_rows > 0) {
            $user = $check_result->fetch_assoc();
            $u_id = $user['u_id'];
            $stmt->close();

            $stmt = $conn->prepare("SELECT * FROM admins WHERE u_id = ?");
            $stmt->bind_param("i", $u_id);
            $stmt->execute();
            $check_admin = $stmt->get_result();

            if ($check_admin && $check_admin->num_rows > 0) {
                $_SESSION['message'] = "User is already an admin.";
                $_SESSION['message_type'] = "warning";
                $stmt->close();
            } else {
                $stmt->close();
                $stmt = $conn->prepare("INSERT INTO admins (u_id, level) VALUES (?, ?)");
                $stmt->bind_param("ii", $u_id, $level);
                if ($stmt->execute()) {

                    // Remove user from students table when promoted to admin
                    $stmt_delete = $conn->prepare("DELETE FROM students WHERE u_id = ?");
                    $stmt_delete->bind_param("i", $u_id);
                    $stmt_delete->execute();
                    $stmt_delete->close();

                    $_SESSION['message'] = "Admin added successfully!";
                    $_SESSION['message_type'] = "success";
                } else {
                    $_SESSION['message'] = "Error adding admin: " . $stmt->error;
                    $_SESSION['message_type'] = "danger";
                }
                $stmt->close();
            }
        } else {
            $_SESSION['message'] = "No user found with that email.";
            $_SESSION['message_type'] = "danger";
            $stmt->close();
        }

        header("Location: admins.php");
        exit();

        
    } elseif (isset($_POST['update_admin'])) {
        $u_id = (int)$_POST['u_id'];
        $level = (int)$_POST['level'];
        $stmt = $conn->prepare("UPDATE admins SET level = ? WHERE u_id = ?");
        $stmt->bind_param("ii", $level, $u_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Admin updated successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error updating admin: " . $stmt->error;
            $_SESSION['message_type'] = "danger";
        }
        $stmt->close();
        header("Location: admins.php");
        exit();
    } elseif (isset($_POST['remove_admin'])) {
        $u_id = (int)$_POST['u_id'];
        $stmt = $conn->prepare("DELETE FROM admins WHERE u_id = ?");
        $stmt->bind_param("i", $u_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Admin removed successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error removing admin: " . $stmt->error;
            $_SESSION['message_type'] = "danger";
        }
        $stmt->close();
        header("Location: admins.php");
        exit();
    }
}

// Prepare query to fetch admins with filters and sorting
$sql = "SELECT a.u_id, a.level, u.name, u.email, u.contact
        FROM admins a
        JOIN users u ON a.u_id = u.u_id
        WHERE 1=1 ";

$params = [];
$types = "";

// Filtering
if ($filter_name !== '') {
    $sql .= " AND u.name LIKE ? ";
    $params[] = "%$filter_name%";
    $types .= "s";
}

if ($filter_level > 0) {
    $sql .= " AND a.level = ? ";
    $params[] = $filter_level;
    $types .= "i";
}

// Sorting
switch ($sort_option) {
    case 'name-desc':
        $sql .= " ORDER BY u.name DESC ";
        break;
    case 'level-asc':
        $sql .= " ORDER BY a.level ASC ";
        break;
    case 'level-desc':
        $sql .= " ORDER BY a.level DESC ";
        break;
    case 'name-asc':
    default:
        $sql .= " ORDER BY u.name ASC ";
        break;
}

$stmt = $conn->prepare($sql);

if ($types) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$admins = [];
while ($row = $result->fetch_assoc()) {
    $admins[] = $row;
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admins - SkillUp Academy</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../style.css" />
<link rel="prefetch" href="../image-assets/common/fav.webp" as="image">
    <link rel="icon" href="../mage-assets/common/fav.webp" type="image/webp">

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

<!-- SESSION MESSAGE TOAST -->
<?php if (isset($_SESSION['message'])): ?>
  <div
    id="sessionToast"
    class="toast align-items-center text-bg-<?php echo $_SESSION['message_type']; ?> border-0 position-fixed bottom-0 start-50 translate-middle-x mb-3"
    role="alert"
    aria-live="assertive"
    aria-atomic="true"
    style="z-index: 1080; min-width: 250px;"
  >
    <div class="d-flex">
      <div class="toast-body text-center w-100">
        <?php echo htmlspecialchars($_SESSION['message']); ?>
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const toastEl = document.getElementById('sessionToast');
      if (toastEl) {
        const toast = new bootstrap.Toast(toastEl, { delay: 1000 });
        toast.show();
      }
    });
  </script>

  <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
<?php endif; ?>

<div class="container-fluid mt-3 mb-5 px-3">
  <div class="row justify-content-center">
    <div class="col-12">
      <div class="card bg-transparent card-h shadow-lg border-0">
        <div class="card-body">
          <p class="text-center fs-1 mb-4">Admins</p>
          
          <!-- Add Admin Button -->
          <div class="d-grid">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdminModal">
              Add Admin
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Add Admin Modal -->
<div class="modal fade" id="addAdminModal" tabindex="-1" aria-labelledby="addAdminModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border border-dark">
      <div class="modal-header">
        <h5 class="modal-title" id="addAdminModalLabel">Add Existing User as Admin</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="admins.php" id="addAdminForm">
        <div class="modal-body">
          <div class="mb-3">
            <label for="email" class="form-label small">User Email <span class="text-danger">*</span></label>
            <input
              type="email"
              class="form-control form-control-sm border border-dark"
              id="email"
              name="email"
              required
              autocomplete="off"
            />
            <div id="emailFeedback" class="form-text mt-1"></div>
          </div>
          <div class="mb-3">
            <label for="level" class="form-label small">Admin Level <span class="text-danger">*</span></label>
            <select
              class="form-select form-select-sm border border-dark"
              id="level"
              name="level"
              required
            >
              <option value="" selected disabled>Select Level</option>
              <option value="1">1</option>
              <option value="2">2</option>
              <option value="3">3</option>
              <option value="4">4</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-success btn-sm" name="add_admin" id="addAdminSubmit">Add Admin</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Filter Section -->
<div class="card bg-transparent shadow-lg border-0 mt-4">
  <div class="card-body bg-transparent p-4">
    <h4 class="mb-4 text-center">Filter & Sort Admins</h4>
    <form method="GET" action="admins.php">
      <div class="row g-3 align-items-center">
        <div class="col-md-4">
          <label for="filter-name" class="form-label small">Name</label>
          <input
            type="text"
            class="form-control form-control-sm bg-transparent border border-dark text-dark"
            id="filter-name"
            name="filter_name"
            placeholder="Enter name"
            value="<?php echo htmlspecialchars($filter_name); ?>"
          />
        </div>
        <div class="col-md-4">
          <label for="filter-level" class="form-label small">Level</label>
          <select
            class="form-select form-select-sm bg-transparent border border-dark text-dark"
            id="filter-level"
            name="filter_level"
          >
            <option value="0" <?php echo $filter_level == 0 ? 'selected' : ''; ?>>All Levels</option>
            <option value="1" <?php echo $filter_level == 1 ? 'selected' : ''; ?>>1</option>
            <option value="2" <?php echo $filter_level == 2 ? 'selected' : ''; ?>>2</option>
            <option value="3" <?php echo $filter_level == 3 ? 'selected' : ''; ?>>3</option>
            <option value="4" <?php echo $filter_level == 4 ? 'selected' : ''; ?>>4</option>
          </select>
        </div>
        <div class="col-md-4">
          <label for="sort-option" class="form-label small">Sort By</label>
          <select
            class="form-select form-select-sm bg-transparent border border-dark text-dark"
            id="sort-option"
            name="sort_option"
          >
            <option value="name-asc" <?php echo $sort_option == 'name-asc' ? 'selected' : ''; ?>>Name (A-Z)</option>
            <option value="name-desc" <?php echo $sort_option == 'name-desc' ? 'selected' : ''; ?>>Name (Z-A)</option>
            <option value="level-asc" <?php echo $sort_option == 'level-asc' ? 'selected' : ''; ?>>Level (1-4)</option>
            <option value="level-desc" <?php echo $sort_option == 'level-desc' ? 'selected' : ''; ?>>Level (4-1)</option>
          </select>
        </div>
      </div>
      <div class="text-center mt-3">
        <button type="submit" class="btn btn-success btn-sm me-2">Apply</button>
        <a href="admins.php" class="btn btn-warning btn-sm">Reset</a>
      </div>
    </form>
  </div>
</div>

<!-- Admins Table -->
<div class="card bg-transparent shadow-lg border-0 mt-4">
  <div class="card-body bg-transparent">
    <h4 class="mb-4 text-center">List of Admins</h4>
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle text-center border-dark mb-0">
        <thead class="bg-transparent text-dark">
          <tr>
            <th class="bg-transparent">Name</th>
            <th class="bg-transparent">Email</th>
            <th class="bg-transparent">Level</th>
            <th class="bg-transparent">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($admins) === 0): ?>
            <tr><td colspan="4">No admins found.</td></tr>
          <?php endif; ?>

          <?php foreach ($admins as $admin): ?>
            <tr class="bg-transparent">
              <td class="bg-transparent"><?php echo htmlspecialchars($admin['name']); ?></td>
              <td class="bg-transparent"><?php echo htmlspecialchars($admin['email']); ?></td>
              <td class="bg-transparent"><?php echo (int)$admin['level']; ?></td>
              <td class="bg-transparent">
                <button
                  class="btn btn-sm btn-warning me-2"
                  data-bs-toggle="modal"
                  data-bs-target="#editModal<?php echo $admin['u_id']; ?>"
                >Edit</button>
                <button
                  class="btn btn-sm btn-primary"
                  data-bs-toggle="modal"
                  data-bs-target="#contactModal<?php echo $admin['u_id']; ?>"
                >Contact</button>
              </td>
            </tr>

            <!-- Edit Modal -->
            <div class="modal fade" id="editModal<?php echo $admin['u_id']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $admin['u_id']; ?>" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content border border-dark">
                  <div class="modal-header border-0">
                    <h5 class="modal-title" id="editModalLabel<?php echo $admin['u_id']; ?>">Edit Admin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <form method="POST" action="admins.php">
                    <input type="hidden" name="u_id" value="<?php echo $admin['u_id']; ?>">
                    <div class="modal-body">
                      <div class="mb-3">
                        <label class="form-label">Level</label>
                        <select class="form-select border-dark" name="level" required>
                          <?php for ($i = 1; $i <= 4; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo $admin['level'] == $i ? 'selected' : ''; ?>>
                              <?php echo $i; ?>
                            </option>
                          <?php endfor; ?>
                        </select>
                      </div>
                      <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-danger btn-sm" name="remove_admin" onclick="return confirm('Are you sure to remove this admin?');">Remove</button>
                        <button type="submit" class="btn btn-success btn-sm" name="update_admin">Save Changes</button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <!-- Contact Modal -->
            <div class="modal fade" id="contactModal<?php echo $admin['u_id']; ?>" tabindex="-1" aria-labelledby="contactModalLabel<?php echo $admin['u_id']; ?>" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content border border-dark">
                  <div class="modal-header border-0">
                    <h5 class="modal-title" id="contactModalLabel<?php echo $admin['u_id']; ?>">Contact Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($admin['email']); ?></p>
                    <p><strong>Contact No:</strong> <?php echo htmlspecialchars($admin['contact']); ?></p>
                  </div>
                </div>
              </div>
            </div>

          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Live AJAX validation for email input in Add Admin modal
document.addEventListener("DOMContentLoaded", () => {
  const emailInput = document.getElementById("email");
  const emailFeedback = document.getElementById("emailFeedback");
  const addAdminSubmit = document.getElementById("addAdminSubmit");

  emailInput.addEventListener("input", () => {
    const email = emailInput.value.trim();
    if (!email) {
      emailFeedback.innerHTML = "";
      addAdminSubmit.disabled = false;
      return;
    }

    fetch("admins.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "check_email=" + encodeURIComponent(email)
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === "valid") {
        emailFeedback.innerHTML = `<span class="text-success">✅ ${data.message}</span>`;
        addAdminSubmit.disabled = false;
      } else if (data.status === "exists") {
        emailFeedback.innerHTML = `<span class="text-warning">⚠️ ${data.message}</span>`;
        addAdminSubmit.disabled = true;
      } else {
        emailFeedback.innerHTML = `<span class="text-danger">❌ ${data.message}</span>`;
        addAdminSubmit.disabled = true;
      }
    })
    .catch(() => {
      emailFeedback.innerHTML = `<span class="text-danger">⚠️ Error checking email</span>`;
      addAdminSubmit.disabled = false;
    });
  });
});
</script>

</body>
</html>

<?php
$conn->close();
?>
