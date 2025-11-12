<?php
session_start();
if (empty($_SESSION['is_admin'])) {
  header('Location: login.html');
  exit();
}

require_once 'config.php';
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
  die('Database connection failed: ' . $conn->connect_error);
}

$q = trim($_GET['q'] ?? '');
$sql = "SELECT id, username, email, contact FROM users";
if ($q !== '') {
  $sql .= " WHERE username LIKE ? OR email LIKE ? OR contact LIKE ?";
  $stmt = $conn->prepare($sql);
  $like = "%$q%";
  $stmt->bind_param('sss', $like, $like, $like);
  $stmt->execute();
  $result = $stmt->get_result();
} else {
  $result = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Scholarly – Student Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <style>
    * { font-family: 'Poppins', sans-serif !important; }
    .search-input { max-width: 280px; }
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
          <img src="assets/Images/material-symbols_school.png" alt=""> Scholarships
        </a>
        <a href="add-scholarship.php" class="nav-link d-flex align-items-center gap-2 text-white">
          <img src="assets/Images/material-add_symbols_school.png" alt=""> Add Scholarship
        </a>
        <a href="studentmanagementboard.php" class="nav-link active d-flex align-items-center gap-2 text-white">
          <img src="assets/Images/ph_student-bold.png" alt=""> Student Management
        </a>
        <a href="logout.php" class="nav-link d-flex align-items-center gap-2 text-white" 
   data-bs-toggle="modal" data-bs-target="#logoutModal">
  <i class="bi bi-box-arrow-right"></i> Logout
</a>
      </nav>
    </aside>

    <main class="main-content flex-grow-1 p-4">
      <h4 class="fw-bold mb-3">Student Management</h4>

      <form class="search-container position-relative d-inline-block" method="GET" action="">
  <svg xmlns="http://www.w3.org/2000/svg" 
       viewBox="0 0 24 24" 
       fill="none" 
       stroke="#777" 
       stroke-width="2" 
       stroke-linecap="round" 
       stroke-linejoin="round" 
       class="search-icon">
    <circle cx="11" cy="11" r="8" />
    <line x1="21" y1="21" x2="16.65" y2="16.65" />
  </svg>

  <input 
    type="text" 
    name="q" 
    value="<?php echo htmlspecialchars($q ?? ''); ?>" 
    placeholder="Search students..." 
    class="search-input ps-5"
  >
</form>



      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th scope="col">STUDENT ID</th>
              <th scope="col">STUDENT NAME</th>
              <th scope="col">EMAIL</th>
              <th scope="col">CONTACT NUMBER</th>
              <th scope="col" class="text-center">ACTIONS</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
              <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                  <td><?php echo htmlspecialchars($row['id']); ?></td>
                  <td><?php echo htmlspecialchars($row['username']); ?></td>
                  <td><?php echo htmlspecialchars($row['email']); ?></td>
                  <td><?php echo htmlspecialchars($row['contact']); ?></td>
                  <td class="text-center">
                    <button class="btn btn-sm btn-primary me-2 view-btn" data-id="<?php echo $row['id']; ?>"><i class="bi bi-eye"></i> View</button>
                    <button class="btn btn-sm btn-danger delete-btn" data-id="<?php echo $row['id']; ?>"><i class="bi bi-trash"></i> Delete</button>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" class="text-center text-muted">No students found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>

  <!-- View Modal -->
  <div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title fw-bold">Student Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div id="studentDetails"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Delete Modal -->
  <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content text-center p-3">
        <i class="bi bi-exclamation-triangle text-danger" style="font-size:2rem;"></i>
        <h5 class="fw-bold mt-2">Confirm Deletion</h5>
        <p>Are you sure you want to delete this student?</p>
        <div class="d-flex justify-content-center gap-3">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <form id="deleteForm" method="POST" action="delete_student.php">
            <input type="hidden" name="id" id="deleteId">
            <button type="submit" class="btn btn-danger">Delete</button>
          </form>
        </div>
      </div>
    </div>
  </div>

<!-- Logout Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content logout-modal text-center p-4">
      <i class="bi bi-box-arrow-right text-danger mb-3" style="font-size:3rem;"></i>
      <h5 class="fw-bold text-dark mb-2">Are you sure you want to log out?</h5>
      <p class="text-muted small">You will be redirected to the login page after logging out.</p>
      <div class="d-flex justify-content-center gap-3 mt-3">
        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
        <a href="logout.php" class="btn btn-danger px-4">Logout</a>
      </div>
    </div>
  </div>
</div>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const viewButtons = document.querySelectorAll('.view-btn');
    const deleteButtons = document.querySelectorAll('.delete-btn');
    const studentDetails = document.getElementById('studentDetails');
    const viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

    viewButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        fetch(`./get_student_details.php?id=${id}`)
          .then(res => res.json())
          .then(data => {
            if (data.error) {
              studentDetails.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
            } else {
              let scholarships = data.scholarships.length > 0
                ? data.scholarships.map(s => `<li>${s.title}</li>`).join('')
                : '<li class="text-muted">No scholarships applied</li>';
              
              studentDetails.innerHTML = `
                <p><strong>Name:</strong> ${data.username}</p>
                <p><strong>Email:</strong> ${data.email}</p>
                <p><strong>Contact:</strong> ${data.contact}</p>
                <h6 class="fw-bold mt-3">Scholarships Applied:</h6>
                <ul>${scholarships}</ul>
              `;
            }
            viewModal.show();
          });
      });
    });

    deleteButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        document.getElementById('deleteId').value = btn.dataset.id;
        deleteModal.show();
      });
    });
  </script>
</body>
</html>
<?php
if (isset($stmt) && $stmt instanceof mysqli_stmt) { $stmt->close(); }
if (isset($result) && $result instanceof mysqli_result) { $result->free(); }
$conn->close();
?>