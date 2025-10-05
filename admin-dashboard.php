<?php
session_start();

// Require admin session
if (empty($_SESSION['is_admin'])) {
    header('Location: login.html');
    exit();
}

require_once 'config.php';
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
  die('Database connection failed: ' . $conn->connect_error);
}

// Ensure scholarships table exists
$conn->query("CREATE TABLE IF NOT EXISTS scholarships (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  sponsor VARCHAR(255) NOT NULL,
  category ENUM('Company','School','Organization') NOT NULL,
  start_date DATE NULL,
  end_date DATE NULL,
  phone VARCHAR(30) NULL,
  email VARCHAR(100) NULL,
  image_path VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Ensure applications table exists
$conn->query("CREATE TABLE IF NOT EXISTS applications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  scholarship_id INT UNSIGNED NOT NULL,
  status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_application (user_id, scholarship_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE id IN (1, 2)");
$userCount = $result->fetch_assoc()['count'];
if ($userCount < 2) {
  $userSql = "INSERT IGNORE INTO users (id, username, email, contact, password) VALUES 
              (1, 'Jane Smith', 'jane.smith@example.com', '098-765-4321', '$2y$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')";
  $conn->query($userSql);
}

$result = $conn->query("SELECT COUNT(*) as count FROM applications");
$count = $result->fetch_assoc()['count'];
if ($count == 0) {
  $result = $conn->query("SELECT id FROM scholarships");
  if ($result->num_rows > 0) {
    while ($scholarship = $result->fetch_assoc()) {
      $scholarshipId = $scholarship['id'];
      
      $insertSql = "INSERT IGNORE INTO applications (user_id, scholarship_id, status) VALUES 
                    (1, ?, 'pending'),
                    (2, ?, 'pending')";
      $stmt = $conn->prepare($insertSql);
      $stmt->bind_param('ii', $scholarshipId, $scholarshipId);
      $stmt->execute();
      $stmt->close();
    }
  }
}

$q = trim($_GET['q'] ?? '');
$sql = "SELECT s.id, s.title, s.sponsor, s.category, s.start_date, s.end_date, s.image_path, 
               COUNT(a.id) as application_count
        FROM scholarships s
        LEFT JOIN applications a ON s.id = a.scholarship_id
        GROUP BY s.id, s.title, s.sponsor, s.category, s.start_date, s.end_date, s.image_path";
if ($q !== '') {
  $sql .= " HAVING s.title LIKE ? OR s.sponsor LIKE ? OR s.category LIKE ?";
  $stmt = $conn->prepare($sql);
  $like = "%$q%";
  $stmt->bind_param('sss', $like, $like, $like);
  $stmt->execute();
  $scholarships = $stmt->get_result();
} else {
  $scholarships = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Scholarly Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <style>
    body {
      font-family: 'Poppins', sans-serif !important;
    }
  </style>
</head>
<body>
  <div class="dashboard d-flex">
    <aside class="sidebar d-flex flex-column align-items-center p-3">
      <img src="assets/images/Group 44.png" alt="Scholarly Logo" class="logo mb-4">
      <div class="profile text-center mb-4">
        <img src="assets/Images/Admin.png" alt="Profile" class="profile-img mb-2">
        <h2 class="h5 fw-bold">Admin</h2>
        <p class="small mb-2">ADMIN</p>
      </div>
      <hr class="w-100">

      <nav class="nav flex-column w-100">
        <a href="admin-dashboard.php" class="nav-link d-flex align-items-center gap-2 text-white">
          <img src="assets/Images/material-symbols_school.png"> Scholarships
        </a>
        <a href="add-scholarship.php" class="nav-link d-flex align-items-center gap-2 text-white">
          <img src="assets/Images/material-add_symbols_school.png">Add Scholarships
        </a>
        <a href="studentmanagementboard.php" class="nav-link d-flex align-items-center gap-2 text-white">
          <img src="assets/Images/ph_student-bold.png">Student Management
        </a>
        <a href="logout.php" class="nav-link d-flex align-items-center gap-2 text-white" 
   data-bs-toggle="modal" data-bs-target="#logoutModal">
  <i class="bi bi-box-arrow-right"></i> Logout
</a>
      </nav>
    </aside>

    <main class="main-content flex-grow-1 p-4">
      <div class="controls d-flex justify-content-between align-items-center mb-4">
        <form class="w-50" method="GET" action="admin-dashboard.php">
          <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" class="form-control" placeholder="Search available scholarships">
        </form>
      </div>

      <div class="d-flex flex-column gap-3">
        <?php if ($scholarships && $scholarships->num_rows > 0): ?>
          <?php while ($s = $scholarships->fetch_assoc()): ?>
            <div class="card p-3 d-flex flex-row justify-content-between align-items-center shadow-sm">
              <div class="card-left d-flex align-items-center gap-3">
                <?php $img = (!empty($s['image_path']) ? $s['image_path'] : 'assets/Images/image.png'); ?>
                <img src="<?php echo htmlspecialchars($img); ?>" alt="logo" width="65">
                <div>
                  <h3 class="h6 fw-bold mb-1"><?php echo htmlspecialchars($s['title']); ?></h3>
                  <p class="small mb-0"><?php echo htmlspecialchars($s['sponsor']); ?> • <?php echo htmlspecialchars($s['category']); ?></p>
                  <p class="small text-muted mb-0">
                    <?php echo htmlspecialchars($s['start_date'] ?? ''); ?>
                    <?php echo $s['start_date'] ? '–' : ''; ?>
                    <?php echo htmlspecialchars($s['end_date'] ?? ''); ?>
                  </p>
                </div>
              </div>
              <div class="d-flex gap-2 align-items-center">
                <span class="badge bg-primary"><?php echo $s['application_count']; ?> applications</span>
                <button class="btn btn-sm btn-outline-primary" onclick="viewApplications(<?php echo $s['id']; ?>)">View Applications</button>
                <button class="btn btn-sm btn-outline-success" onclick="editScholarship(<?php echo $s['id']; ?>)">Edit</button>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="alert alert-info mb-0">No scholarships found.</div>
        <?php endif; ?>
      </div>
    </main>
  </div>

  <!-- Applications Modal -->
  <div id="applicationsModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Scholarship Applications</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="applicationsList">
            <div class="text-center">
              <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Scholarship Modal -->
  <div id="editScholarshipModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Scholarship</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="editScholarshipForm" enctype="multipart/form-data">
          <div class="modal-body">
            <input type="hidden" id="editScholarshipId" name="id">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="editTitle" class="form-label">Title</label>
                <input type="text" class="form-control" id="editTitle" name="title" required>
              </div>
              <div class="col-md-6 mb-3">
                <label for="editSponsor" class="form-label">Sponsor</label>
                <input type="text" class="form-control" id="editSponsor" name="sponsor" required>
              </div>
            </div>
            <div class="mb-3">
              <label for="editDescription" class="form-label">Description</label>
              <textarea class="form-control" id="editDescription" name="description" rows="3" required></textarea>
            </div>
            <div class="row">
              <div class="col-md-4 mb-3">
                <label for="editCategory" class="form-label">Category</label>
                <select class="form-select" id="editCategory" name="category" required>
                  <option value="">Select Category</option>
                  <option value="Company">Company</option>
                  <option value="School">School</option>
                  <option value="Organization">Organization</option>
                </select>
              </div>
              <div class="col-md-4 mb-3">
                <label for="editStartDate" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="editStartDate" name="start_date">
              </div>
              <div class="col-md-4 mb-3">
                <label for="editEndDate" class="form-label">End Date</label>
                <input type="date" class="form-control" id="editEndDate" name="end_date">
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="editEmail" class="form-label">Email</label>
                <input type="email" class="form-control" id="editEmail" name="email">
              </div>
              <div class="col-md-6 mb-3">
                <label for="editPhone" class="form-label">Phone</label>
                <input type="text" class="form-control" id="editPhone" name="phone">
              </div>
            </div>
            <div class="mb-3">
              <label for="editImage" class="form-label">Image (optional)</label>
              <input type="file" class="form-control" id="editImage" name="image" accept="image/*">
              <div class="form-text">Leave empty to keep current image</div>
            </div>
          </div>
          <div class="modal-footer d-flex justify-content-between">
  <button type="button" id="deleteScholarshipBtn" class="btn btn-danger">Delete Scholarship</button>
  <button type="submit" class="btn btn-primary">Update Scholarship</button>
        </div>
        </form>
      </div>
    </div>
  </div>

  <?php if (isset($stmt) && $stmt instanceof mysqli_stmt) { $stmt->close(); } ?>
  <?php if (isset($scholarships) && $scholarships instanceof mysqli_result) { $scholarships->free(); } ?>
  <?php $conn->close(); ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function viewApplications(scholarshipId) {
      var modal = new bootstrap.Modal(document.getElementById('applicationsModal'));
      modal.show();
      
      console.log('Loading applications for scholarship ID:', scholarshipId);
      fetch('api-applications.php?scholarship_id=' + scholarshipId, { credentials: 'same-origin' })
        .then(function(r) { 
          console.log('Response status:', r.status);
          console.log('Response headers:', r.headers);
          if (!r.ok) {
            throw new Error('HTTP ' + r.status + ': ' + r.statusText);
          }
          return r.json(); 
        })
        .then(function(applications) {
          console.log('Applications response:', applications);
          if (applications && applications.error) {
            throw new Error(applications.error);
          }
          var container = document.getElementById('applicationsList');
          
          if (!applications || applications.length === 0) {
            container.innerHTML = '<div class="alert alert-info">No applications found for this scholarship.</div>';
            return;
          }
          
          var html = '<div class="table-responsive"><table class="table table-striped">';
          html += '<thead><tr><th>Name</th><th>Email</th><th>Contact</th><th>Applied Date</th><th>Status</th><th>Actions</th></tr></thead>';
          html += '<tbody>';
          
          applications.forEach(function(app) {
            html += '<tr>';
            html += '<td>' + (app.username || 'N/A') + '</td>';
            html += '<td>' + (app.email || 'N/A') + '</td>';
            html += '<td>' + (app.contact || 'N/A') + '</td>';
            html += '<td>' + new Date(app.applied_at).toLocaleDateString() + '</td>';
            html += '<td><span class="badge bg-' + (app.status === 'pending' ? 'warning' : app.status === 'approved' ? 'success' : 'danger') + '">' + app.status + '</span></td>';
            html += '<td>';
            if (app.status === 'pending') {
  html += `
    <div class="d-flex justify-content-center gap-2">
      <button class="btn btn-success btn-sm flex-fill" onclick="updateApplicationStatus(${app.id}, 'approved')">
        <i class="bi bi-check-circle"></i> Approve
      </button>
      <button class="btn btn-danger btn-sm flex-fill" onclick="updateApplicationStatus(${app.id}, 'rejected')">
        <i class="bi bi-x-circle"></i> Reject
      </button>
    </div>
  `;
} else {
  html += '<span class="text-muted">No actions</span>';
}
            html += '</td>';
            html += '</tr>';
          });
          
          html += '</tbody></table></div>';
          container.innerHTML = html;
        })
        .catch(function(error) {
          console.error('Error loading applications:', error);
          document.getElementById('applicationsList').innerHTML = '<div class="alert alert-danger">Failed to load applications: ' + error.message + '</div>';
        });
    }

    // Delete scholarship
document.getElementById('deleteScholarshipBtn').addEventListener('click', function () {
  const id = document.getElementById('editScholarshipId').value;

  if (!id) {
    alert('No scholarship selected to delete.');
    return;
  }

  if (!confirm('Are you sure you want to permanently delete this scholarship?')) {
    return;
  }

  const formData = new FormData();
  formData.append('action', 'delete');
  formData.append('id', id);

  fetch('api-edit-scholarship.php', {
    method: 'POST',
    body: formData,
    credentials: 'same-origin'
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      alert('Scholarship deleted successfully!');
      bootstrap.Modal.getInstance(document.getElementById('editScholarshipModal')).hide();
      location.reload();
    } else {
      alert('Failed to delete scholarship: ' + (data.error || 'Unknown error'));
    }
  })
  .catch(() => {
    alert('Failed to delete scholarship.');
  });
});


    function updateApplicationStatus(applicationId, status) {
      var formData = new FormData();
      formData.append('action', 'update_status');
      formData.append('application_id', applicationId);
      formData.append('status', status);
      
      fetch('api-applications.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
      })
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if (data.success) {
          alert('Application ' + status + ' successfully!');
          var currentScholarshipId = document.querySelector('#applicationsModal .modal-title').dataset.scholarshipId;
          if (currentScholarshipId) {
            viewApplications(currentScholarshipId);
          }
        } else {
          alert('Failed to update application: ' + (data.error || 'Unknown error'));
        }
      })
      .catch(function() {
        alert('Failed to update application status');
      });
    }

    function editScholarship(scholarshipId) {
      fetch('api-edit-scholarship.php?id=' + scholarshipId, { credentials: 'same-origin' })
        .then(function(r) { return r.json(); })
        .then(function(scholarship) {
          document.getElementById('editScholarshipId').value = scholarship.id;
          document.getElementById('editTitle').value = scholarship.title || '';
          document.getElementById('editSponsor').value = scholarship.sponsor || '';
          document.getElementById('editDescription').value = scholarship.description || '';
          document.getElementById('editCategory').value = scholarship.category || '';
          document.getElementById('editStartDate').value = scholarship.start_date || '';
          document.getElementById('editEndDate').value = scholarship.end_date || '';
          document.getElementById('editEmail').value = scholarship.email || '';
          document.getElementById('editPhone').value = scholarship.phone || '';
          
          var modal = new bootstrap.Modal(document.getElementById('editScholarshipModal'));
          modal.show();
        })
        .catch(function() {
          alert('Failed to load scholarship data');
        });
    }

    document.getElementById('editScholarshipForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      var formData = new FormData(this);
      
      fetch('api-edit-scholarship.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
      })
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if (data.success) {
          alert('Scholarship updated successfully!');
          bootstrap.Modal.getInstance(document.getElementById('editScholarshipModal')).hide();
          location.reload();
        } else {
          alert('Failed to update scholarship: ' + (data.error || 'Unknown error'));
        }
      })
      .catch(function() {
        alert('Failed to update scholarship');
      });
    });
  </script>
<div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content logout-modal">
      <div class="modal-body text-center py-4">
        <i class="bi bi-box-arrow-right text-danger mb-3" style="font-size:3rem;"></i>
        <h5 class="fw-bold text-dark mb-2">Are you sure you want to log out?</h5>
        <p class="text-muted small">You will be redirected to the login page.</p>
        <div class="d-flex justify-content-center gap-3 mt-3">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <a href="logout.php" class="btn btn-danger px-4">Logout</a>
        </div>
      </div>
    </div>
  </div>
</div>
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

</body>
</html>