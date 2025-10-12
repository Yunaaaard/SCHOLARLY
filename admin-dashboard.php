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
  category ENUM('Company','School','Organization','Government','Foundation','Non-Profit','Individual','Other') NOT NULL,
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
$category = trim($_GET['category'] ?? '');
$sort = trim($_GET['sort'] ?? '');

$baseSql = "SELECT s.id, s.title, s.sponsor, s.category, s.start_date, s.end_date, s.image_path,
                   COUNT(b.id) as application_count
            FROM scholarships s
            LEFT JOIN bookmarks b ON s.id = b.scholarship_id";

$where = [];
$bindTypes = '';
$bindValues = [];

if ($q !== '') {
  $where[] = "(s.title LIKE ? OR s.sponsor LIKE ? OR s.category LIKE ?)";
  $bindTypes .= 'sss';
  $like = "%$q%";
  $bindValues[] = $like; $bindValues[] = $like; $bindValues[] = $like;
}
if ($category !== '') {
  $where[] = "s.category = ?";
  $bindTypes .= 's';
  $bindValues[] = $category;
}

$sql = $baseSql;
if (!empty($where)) {
  $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= " GROUP BY s.id, s.title, s.sponsor, s.category, s.start_date, s.end_date, s.image_path";

switch ($sort) {
  case 'title_asc': $sql .= " ORDER BY s.title ASC"; break;
  case 'title_desc': $sql .= " ORDER BY s.title DESC"; break;
  case 'start_new': $sql .= " ORDER BY s.start_date DESC"; break;
  case 'end_new': $sql .= " ORDER BY s.end_date DESC"; break;
  case 'apps_high': $sql .= " ORDER BY application_count DESC"; break;
  default: $sql .= " ORDER BY s.id DESC"; break;
}

if ($bindTypes !== '') {
  $stmt = $conn->prepare($sql);
  $stmt->bind_param($bindTypes, ...$bindValues);
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
    
    /* Rich text editor styles */
    .rich-text-editor {
      background: white;
      border: 1px solid #ced4da;
      border-radius: 0.375rem;
    }
    
    .rich-text-editor:focus-within {
      border-color: #86b7fe;
      box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    
    .toolbar button {
      border: 1px solid #dee2e6;
      background: white;
    }
    
    .toolbar button:hover {
      background: #f8f9fa;
    }
    
    .description-content {
      font-family: inherit;
      line-height: 1.5;
    }
    
    .description-content:empty:before {
      content: attr(data-placeholder);
      color: #6c757d;
      font-style: italic;
    }
    
    .description-content ul, .description-content ol {
      margin: 0.5rem 0;
      padding-left: 1.5rem;
    }
    
    .description-content p {
      margin: 0.5rem 0;
    }
    
    .description-content strong {
      font-weight: bold;
    }
    
    .description-content em {
      font-style: italic;
    }
    
    .description-content u {
      text-decoration: underline;
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
        <form class="d-flex gap-2 w-100" method="GET" action="admin-dashboard.php">
          <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" class="form-control" placeholder="Search by title, sponsor or category">
          <select name="category" class="form-select" style="max-width: 200px;">
            <option value="">All Categories</option>
            <option value="Company" <?php echo $category==='Company'?'selected':''; ?>>Company</option>
            <option value="School" <?php echo $category==='School'?'selected':''; ?>>School</option>
            <option value="Organization" <?php echo $category==='Organization'?'selected':''; ?>>Organization</option>
          </select>
          <select name="sort" class="form-select" style="max-width: 200px;">
            <option value="">Sort: Default</option>
            <option value="title_asc" <?php echo $sort==='title_asc'?'selected':''; ?>>Title A-Z</option>
            <option value="title_desc" <?php echo $sort==='title_desc'?'selected':''; ?>>Title Z-A</option>
            <option value="start_new" <?php echo $sort==='start_new'?'selected':''; ?>>Newest Start Date</option>
            <option value="end_new" <?php echo $sort==='end_new'?'selected':''; ?>>Newest End Date</option>
            <option value="apps_high" <?php echo $sort==='apps_high'?'selected':''; ?>>Most Applications</option>
          </select>
          <button class="btn btn-primary" type="submit">Apply</button>
          <a class="btn btn-outline-secondary" href="admin-dashboard.php">Reset</a>
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
                <span class="badge bg-primary"><?php echo $s['application_count']; ?> bookmarks</span>
                <button class="btn btn-sm btn-outline-primary" onclick="viewApplications(<?php echo $s['id']; ?>)">View Bookmarks</button>
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
                <input type="text" class="form-control" id="editSponsor" name="sponsor">
              </div>
            </div>
            <div class="mb-3">
              <label for="editDescription" class="form-label">Description</label>
              <div class="rich-text-editor border rounded p-2" style="min-height: 200px; background: white;">
                <div class="toolbar mb-2 border-bottom pb-2">
                  <button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="formatTextEdit('bold')" title="Bold">
                    <i class="bi bi-type-bold"></i>
                  </button>
                  <button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="formatTextEdit('italic')" title="Italic">
                    <i class="bi bi-type-italic"></i>
                  </button>
                  <button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="formatTextEdit('underline')" title="Underline">
                    <i class="bi bi-type-underline"></i>
                  </button>
                  <button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="insertLineBreakEdit()" title="Line Break">
                    <i class="bi bi-text-paragraph"></i>
                  </button>
                  <button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="insertBulletListEdit()" title="Bullet List">
                    <i class="bi bi-list-ul"></i>
                  </button>
                  <button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="insertNumberedListEdit()" title="Numbered List">
                    <i class="bi bi-list-ol"></i>
                  </button>
                </div>
                <div contenteditable="true" id="editDescriptionEditor" class="description-content" style="min-height: 150px; outline: none;" data-placeholder="Provide a detailed description with formatting..."></div>
              </div>
              <textarea id="editDescription" name="description" style="display: none;" required></textarea>
            </div>
            <div class="row">
              <div class="col-md-4 mb-3">
                <label for="editCategory" class="form-label">Category</label>
                <select class="form-select" id="editCategory" name="category" required>
                  <option value="">Select Category</option>
                  <option value="Company">Company</option>
                  <option value="School">School</option>
                  <option value="Organization">Organization</option>
                  <option value="Government">Government</option>
                  <option value="Foundation">Foundation</option>
                  <option value="Non-Profit">Non-Profit</option>
                  <option value="Individual">Individual</option>
                  <option value="Other">Other</option>
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
            container.innerHTML = '<div class="alert alert-info">No bookmarks found for this scholarship.</div>';
            return;
          }
          
          var html = '<div class="table-responsive"><table class="table table-striped">';
          html += '<thead><tr><th>Name</th><th>Email</th><th>Contact</th><th>Bookmarked Date</th><th>Status</th><th>Actions</th></tr></thead>';
          html += '<tbody>';
          
          applications.forEach(function(app) {
            html += '<tr>';
            html += '<td>' + (app.username || 'N/A') + '</td>';
            html += '<td>' + (app.email || 'N/A') + '</td>';
            html += '<td>' + (app.contact || 'N/A') + '</td>';
            html += '<td>' + new Date(app.applied_at).toLocaleDateString() + '</td>';
            html += '<td><span class="badge bg-info">' + app.status + '</span></td>';
            html += '<td>';
            // No actions needed for bookmarks
            html += '<span class="text-muted">-</span>';
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
          document.getElementById('editDescriptionEditor').innerHTML = scholarship.description || '';
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

    // Rich text editor functions for edit modal
    function formatTextEdit(command) {
      document.execCommand(command, false, null);
      document.getElementById('editDescriptionEditor').focus();
    }

    function insertLineBreakEdit() {
      document.execCommand('insertHTML', false, '<br><br>');
      document.getElementById('editDescriptionEditor').focus();
    }

    function insertBulletListEdit() {
      document.execCommand('insertUnorderedList', false, null);
      document.getElementById('editDescriptionEditor').focus();
    }

    function insertNumberedListEdit() {
      document.execCommand('insertOrderedList', false, null);
      document.getElementById('editDescriptionEditor').focus();
    }

    // Sync edit editor content with hidden textarea
    function syncEditDescription() {
      const editor = document.getElementById('editDescriptionEditor');
      const textarea = document.getElementById('editDescription');
      textarea.value = editor.innerHTML;
    }

    // Initialize edit editor
    document.addEventListener('DOMContentLoaded', function() {
      const editor = document.getElementById('editDescriptionEditor');
      const textarea = document.getElementById('editDescription');
      
      if (editor) {
        editor.addEventListener('input', function() {
          syncEditDescription();
        });

        // Sync on form submit
        document.getElementById('editScholarshipForm').addEventListener('submit', function() {
          syncEditDescription();
        });
      }
    });

    document.getElementById('editScholarshipForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      // Sync editor content before submit
      syncEditDescription();
      
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