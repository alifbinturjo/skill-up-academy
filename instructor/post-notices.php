<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Notice</title>
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
            <a class="nav-link " href="dashboard.php">Dashboard</a>
          </li>
          <li class="nav-item">
            <a class="nav-link " href="courses.php">Courses</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="notices.php">Notices</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="profile.php">Profile</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="">Logout</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>


  <div class="container-fluid mt-3 mb-5 px-3">
    <div class="row justify-content-center">
      <div class="col-12">


        <div class="card bg-transparent shadow card-h border-0 mb-4">
          <div class="card-body">
            <p class="text-center mb-4 fs-1">Post Notice</p>
            <h5 class="mb-3">Create Notice</h5>

            <div class="mb-3">
              <label class="form-label">Notice Title</label>
              <input type="text" class="form-control bg-transparent border border-dark" id="notificationTitle"
                placeholder="Enter notification title" />
            </div>

            <div class="mb-3">
              <label class="form-label">Description</label>
              <textarea class="form-control bg-transparent border border-dark" id="notificationDescription" rows="5"
                placeholder="Enter notification description"></textarea>
            </div>

            <div class="text-center mt-2">
              <button onclick="showDanger()" class="btn btn-primary btn-sm">Post Notice</button>
            </div>
          </div>
        </div>


        <div class="position-fixed bottom-0 start-50 translate-middle-x p-3 mb-3" style="z-index: 11;">
          <div id="adminToast" class="toast align-items-center text-white bg-success border-0" role="alert"
            aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
              <div class="toast-body">
                Notice posted successfully!
              </div>
            </div>
          </div>
        </div>

        <script>
          function showDanger() {
            const toastEl = document.getElementById('adminToast');
            const toast = new bootstrap.Toast(toastEl, { delay: 2000 });
            toast.show();
          }
        </script>


        <div class="card bg-transparent shadow-sm border-0">
          <div class="card-body">
            <p class="text-center fs-4 mb-4">Recent Notices</p>
            <div class="list-group" id="notificationList">

              <div
                class="list-group-item d-flex justify-content-between align-items-center bg-transparent border border-dark">
                <div>
                  <h6 class="mb-1">Semester Final Exam Schedule</h6>
                  <small class="text-muted">Posted on: May 15, 2023 at 10:30 AM</small>
                </div>
                <div class="text-center mt-2">
                  <button type="button" class="btn btn-danger btn-sm"
                    onclick="showToast('Item removed!', 'danger')">Remove</button>
                </div>
              </div>

              <div
                class="list-group-item d-flex justify-content-between align-items-center bg-transparent border border-dark mt-2">
                <div>
                  <h6 class="mb-1">Library Closure Notice</h6>
                  <small class="text-muted">Posted on: May 10, 2023 at 2:15 PM</small>
                </div>
                <div class="text-center mt-2">
                  <button type="button" class="btn btn-danger btn-sm"
                    onclick="showToast('Item removed!', 'danger')">Remove</button>
                </div>
              </div>

              <div class="position-fixed bottom-0 start-50 translate-middle-x p-3 mb-3" style="z-index: 11;">
                <div id="actionToast" class="toast align-items-center text-white border-0" role="alert"
                  aria-live="assertive" aria-atomic="true">
                  <div class="d-flex">
                    <div class="toast-body" id="toastMessage"></div>
                  </div>
                </div>
              </div>

              <script>
                function showToast(message, type) {
                  const toastEl = document.getElementById('actionToast');
                  const toastMsg = document.getElementById('toastMessage');

                  toastMsg.textContent = message;

                  toastEl.className = 'toast align-items-center text-white border-0'; // Reset classes
                  toastEl.classList.add(
                    type === 'danger' ? 'bg-danger' :
                      type === 'success' ? 'bg-success' :
                        'bg-primary'
                  );

                  const toast = new bootstrap.Toast(toastEl, { delay: 2000 });
                  toast.show();
                }
              </script>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>