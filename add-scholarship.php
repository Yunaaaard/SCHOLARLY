<?php
session_start();
if (empty($_SESSION['is_admin'])) {
    header('Location: login.html');
    exit();
}
require_once 'config.php';

$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD);
    if ($conn->connect_error) {
        $errors[] = 'Connection failed: ' . $conn->connect_error;
    } else {
        $conn->query("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`");
        $conn->select_db(DB_NAME);

        $createTableSql = "CREATE TABLE IF NOT EXISTS scholarships (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $conn->query($createTableSql);

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $sponsor = trim($_POST['sponsor'] ?? '');
        $category = $_POST['category'] ?? '';
        $start_date = $_POST['start_date'] ?? null;
        $end_date = $_POST['end_date'] ?? null;
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if ($title === '') { $errors[] = 'Scholarship title is required.'; }
        if ($description === '') { $errors[] = 'Description is required.'; }
        if (!in_array($category, ['Company','School','Organization','Government','Foundation','Non-Profit','Individual','Other'], true)) { $errors[] = 'Category is required.'; }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Invalid email format.'; }
        if ($start_date && $end_date && strtotime($end_date) < strtotime($start_date)) { $errors[] = 'End date cannot be before start date.'; }

        $imagePath = null;
        if (!empty($_FILES['image']['name'])) {
            if (!is_uploaded_file($_FILES['image']['tmp_name'])) {
                $errors[] = 'Invalid uploaded file.';
            } else {
                $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
                $mime = mime_content_type($_FILES['image']['tmp_name']);
                if (!isset($allowed[$mime])) {
                    $errors[] = 'Only JPG and PNG images are allowed.';
                } else {
                    $ext = $allowed[$mime];
                    $uploadsDir = __DIR__ . '/uploads';
                    if (!is_dir($uploadsDir)) {
                        mkdir($uploadsDir, 0777, true);
                    }
                    $safeName = preg_replace('/[^A-Za-z0-9-_]/', '_', pathinfo($_FILES['image']['name'], PATHINFO_FILENAME));
                    $fileName = $safeName . '_' . time() . '.' . $ext;
                    $dest = $uploadsDir . '/' . $fileName;
                    if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                        $errors[] = 'Failed to upload image.';
                    } else {
                        $imagePath = 'uploads/' . $fileName;
                    }
                }
            }
        }

        if (empty($errors)) {
            $sql = "INSERT INTO scholarships (title, description, sponsor, category, start_date, end_date, phone, email, image_path)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                $errors[] = 'Prepare failed: ' . $conn->error;
            } else {
                $stmt->bind_param(
                    'sssssssss',
                    $title,
                    $description,
                    $sponsor,
                    $category,
                    $start_date,
                    $end_date,
                    $phone,
                    $email,
                    $imagePath
                );
                if ($stmt->execute()) {
                    $success = true;
                } else {
                    $errors[] = 'Failed to add scholarship: ' . $stmt->error;
                }
                $stmt->close();
            }
        }

        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Add Scholarship Scholarly</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <style>
    .modal-success-body { display:flex; flex-direction:column; align-items:center; gap:12px; padding:28px; }
    .check-svg { width:92px; height:92px; }
    .check-circle { stroke:#28a745; stroke-width:3; stroke-linecap:round; stroke-linejoin:round; fill:none; stroke-dasharray:180; stroke-dashoffset:180; animation:drawCircle .45s ease forwards; }
    .check-tick { stroke:#28a745; stroke-width:5; stroke-linecap:round; stroke-linejoin:round; fill:none; stroke-dasharray:60; stroke-dashoffset:60; animation:drawTick .35s .35s ease forwards; }
    @keyframes drawCircle { to { stroke-dashoffset:0; } }
    @keyframes drawTick { to { stroke-dashoffset:0; } }
    
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
        <a href="add-scholarship.php" class="nav-link active d-flex align-items-center gap-2">
          <img src="assets/Images/material-add_symbols_school.png"> Add Scholarship
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
      <div class="add-scholarship-heading d-flex align-items-center mb-4">
        <img src="assets/images/addlogo.png" alt="Add Scholarship Icon" class="me-2">
        <h2 class="mb-0">ADD SCHOLARSHIP</h2>
      </div>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?>
        </div>
      <?php endif; ?>

      <form action="add-scholarship.php" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
          <label for="title" class="form-label fw-semibold">Scholarship Title</label>
          <input type="text" class="form-control" id="title" name="title" placeholder="University name, Company name, etc." required>
        </div>
        <div class="mb-3">
          <label for="description" class="form-label fw-semibold">Description</label>
          <div class="rich-text-editor border rounded p-2" style="min-height: 200px; background: white;">
            <div class="toolbar mb-2 border-bottom pb-2">
              <button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="formatText('bold')" title="Bold">
                <i class="bi bi-type-bold"></i>
              </button>
              <button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="formatText('italic')" title="Italic">
                <i class="bi bi-type-italic"></i>
              </button>
              <button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="formatText('underline')" title="Underline">
                <i class="bi bi-type-underline"></i>
              </button>
              <button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="insertLineBreak()" title="Line Break">
                <i class="bi bi-text-paragraph"></i>
              </button>
              <button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="insertBulletList()" title="Bullet List">
                <i class="bi bi-list-ul"></i>
              </button>
              <button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="insertNumberedList()" title="Numbered List">
                <i class="bi bi-list-ol"></i>
              </button>
            </div>
            <div contenteditable="true" id="descriptionEditor" class="description-content" style="min-height: 150px; outline: none;" data-placeholder="Provide a detailed description with formatting..."></div>
          </div>
          <textarea id="description" name="description" style="display: none;" required></textarea>
        </div>
        <div class="mb-3">
          <label for="sponsor" class="form-label fw-semibold">Sponsor</label>
          <input type="text" class="form-control" id="sponsor" name="sponsor" placeholder="Sponsor or Organization Name (Optional)">
        </div>

        <div class="mb-3">
          <span class="form-label fw-semibold d-block mb-1">Category</span>
          <div class="row">
            <div class="col-md-6">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="category" id="company" value="Company" required>
                <label class="form-check-label" for="company">Company</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="category" id="school" value="School">
                <label class="form-check-label" for="school">School</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="category" id="organization" value="Organization">
                <label class="form-check-label" for="organization">Organization</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="category" id="government" value="Government">
                <label class="form-check-label" for="government">Government</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="category" id="foundation" value="Foundation">
                <label class="form-check-label" for="foundation">Foundation</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="category" id="nonprofit" value="Non-Profit">
                <label class="form-check-label" for="nonprofit">Non-Profit</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="category" id="individual" value="Individual">
                <label class="form-check-label" for="individual">Individual</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="category" id="other" value="Other">
                <label class="form-check-label" for="other">Other</label>
              </div>
            </div>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Start Date</label>
            <input type="date" class="form-control" name="start_date">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">End Date</label>
            <input type="date" class="form-control" name="end_date">
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Phone Number</label>
            <input type="tel" class="form-control" name="phone" placeholder="e.g. 09xxxxxxxxx">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Email</label>
            <input type="email" class="form-control" name="email" placeholder="example@email.com">
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label fw-semibold d-block mb-2">Upload Image</label>
          <div class="upload-box text-center position-relative">
            <div class="upload-icon mb-2" id="uploadIcon">
              <img src="assets/images/galary.png" alt="Upload Icon">
            </div>
            <p class="mb-1 text-muted" id="uploadText">Drop your image here or <span class="browse-link text-primary">browse</span></p>
            <small class="text-secondary">Supports: JPG, PNG</small>
            <input type="file" class="upload-input position-absolute top-0 start-0 w-100 h-100 opacity-0" accept="image/*" name="image" id="imageInput">
            <div id="previewContainer" class="mt-3 d-none text-center">
              <p class="text-success fw-semibold mb-1">✅ Image ready for upload:</p>
              <img id="previewImage" src="#" alt="Preview" class="img-fluid rounded border" style="max-height: 150px;">
            </div>
          </div>
        </div>

        <button type="submit" class="btn btn-primary px-5">Submit</button>
      </form>
    </main>
  </div>

  <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-body modal-success-body">
          <svg class="check-svg" viewBox="0 0 52 52" aria-hidden="true">
            <circle class="check-circle" cx="26" cy="26" r="24"></circle>
            <path class="check-tick" d="M15 27 L23 34 L38 18"></path>
          </svg>
          <h4 class="fw-bold text-success mb-0">Success!</h4>
          <p class="text-muted mb-0">Scholarship has been added.</p>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content text-center">
        <div class="modal-body">
          <i class="bi bi-box-arrow-right text-danger mb-3" style="font-size:3rem;"></i>
          <h5>Are you sure you want to log out?</h5>
          <p class="text-muted small">You will be redirected to the login page.</p>
          <div class="d-flex justify-content-center gap-3 mt-3">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <a href="logout.php" class="btn btn-danger">Logout</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Rich text editor functionality
    function formatText(command) {
      document.execCommand(command, false, null);
      document.getElementById('descriptionEditor').focus();
    }

    function insertLineBreak() {
      document.execCommand('insertHTML', false, '<br><br>');
      document.getElementById('descriptionEditor').focus();
    }

    function insertBulletList() {
      document.execCommand('insertUnorderedList', false, null);
      document.getElementById('descriptionEditor').focus();
    }

    function insertNumberedList() {
      document.execCommand('insertOrderedList', false, null);
      document.getElementById('descriptionEditor').focus();
    }

    // Sync editor content with hidden textarea
    function syncDescription() {
      const editor = document.getElementById('descriptionEditor');
      const textarea = document.getElementById('description');
      textarea.value = editor.innerHTML;
    }

    // Initialize editor
    document.addEventListener('DOMContentLoaded', function() {
      const editor = document.getElementById('descriptionEditor');
      const textarea = document.getElementById('description');
      
      // Add placeholder functionality
      editor.addEventListener('input', function() {
        syncDescription();
        if (this.innerHTML.trim() === '') {
          this.innerHTML = '';
        }
      });

      editor.addEventListener('focus', function() {
        if (this.innerHTML.trim() === '') {
          this.innerHTML = '';
        }
      });

      editor.addEventListener('blur', function() {
        if (this.innerHTML.trim() === '') {
          this.innerHTML = '';
        }
      });

      // Sync on form submit
      document.querySelector('form').addEventListener('submit', function() {
        syncDescription();
      });
    });

    const imageInput = document.getElementById('imageInput');
    const previewContainer = document.getElementById('previewContainer');
    const previewImage = document.getElementById('previewImage');
    const uploadText = document.getElementById('uploadText');
    const uploadIcon = document.getElementById('uploadIcon');

    imageInput.addEventListener('change', function () {
      const file = this.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
          previewImage.src = e.target.result;
          previewContainer.classList.remove('d-none');
          uploadText.innerHTML = "<span class='text-success fw-semibold'>Image selected successfully!</span>";
          uploadIcon.classList.add('d-none');
        };
        reader.readAsDataURL(file);
      } else {
        previewContainer.classList.add('d-none');
        uploadText.innerHTML = "Drop your image here or <span class='browse-link text-primary'>browse</span>";
        uploadIcon.classList.remove('d-none');
      }
    });

    document.addEventListener('DOMContentLoaded', function () {
      const showSuccess = <?php echo json_encode($success); ?>;
      if (showSuccess) {
        const modalEl = document.getElementById('successModal');
        const myModal = new bootstrap.Modal(modalEl);
        myModal.show();
        setTimeout(function () {
          myModal.hide();
          window.location.href = 'admin-dashboard.php';
        }, 1000);
      }
    });
  </script>
</body>
</html>