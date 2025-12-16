@php($title = 'Student Management')
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Management - Scholarly</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
  <style>
    * { font-family: 'Poppins', sans-serif !important; }
    
    /* Search Bar Styling */
    .search-container {
      position: relative;
      max-width: 400px;
      margin-bottom: 1.5rem;
    }
    
    .search-input {
      width: 100%;
      padding: 0.75rem 1rem 0.75rem 3rem;
      border: 2px solid #e2e8f0;
      border-radius: 50px;
      font-size: 0.9375rem;
      transition: all 0.3s ease;
      background: white;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
    }
    
    .search-input:focus {
      outline: none;
      border-color: #667eea;
      box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1), 0 4px 12px rgba(0, 0, 0, 0.08);
      background: white;
    }
    
    .search-input::placeholder {
      color: #a0aec0;
    }
    
    .search-icon {
      position: absolute;
      left: 1rem;
      top: 50%;
      transform: translateY(-50%);
      width: 20px;
      height: 20px;
      pointer-events: none;
      transition: stroke 0.3s ease;
    }
    
    .search-container:focus-within .search-icon {
      stroke: #667eea;
    }
    
    /* Improved Table Styles */
    .table-responsive {
      background: white;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
      overflow: hidden;
    }
    
    .table {
      margin-bottom: 0;
    }
    
    .table thead {
      background: #f8f9fa;
      border-bottom: 2px solid #dee2e6;
    }
    
    .table thead th {
      color: #000000;
      font-weight: 600;
      font-size: 0.875rem;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      padding: 1rem;
      border: none;
      white-space: nowrap;
    }
    
    .table tbody td {
      padding: 1rem;
      vertical-align: middle;
      color: #333;
      font-size: 0.9375rem;
      border-bottom: 1px solid #f0f0f0;
    }
    
    .table tbody tr {
      transition: all 0.2s ease;
    }
    
    .table tbody tr:hover {
      background-color: #f8f9ff;
      transform: scale(1.01);
      box-shadow: 0 2px 8px rgba(102, 126, 234, 0.1);
    }
    
    .table tbody tr:last-child td {
      border-bottom: none;
    }
    
    .student-id {
      font-weight: 600;
      color: #667eea;
    }
    
    .student-name {
      font-weight: 500;
      color: #2d3748;
    }
    
    .student-email {
      color: #718096;
      font-size: 0.875rem;
    }
    
    .student-contact {
      color: #4a5568;
    }
    
    .btn-action {
      padding: 0.5rem 1rem;
      border-radius: 8px;
      font-size: 0.875rem;
      font-weight: 500;
      transition: all 0.3s ease;
      border: none;
    }
    
    .btn-view {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
    }
    
    .btn-view:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
      color: white;
    }
    
    .btn-delete {
      background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
      color: white;
    }
    
    .btn-delete:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(245, 87, 108, 0.4);
      color: white;
    }
    
    .btn-action i {
      font-size: 0.875rem;
      margin-right: 0.25rem;
    }
    
    /* Modal Improvements */
    .modal-content {
      border-radius: 16px;
      border: none;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    }
    
    .modal-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border-top-left-radius: 16px;
      border-top-right-radius: 16px;
      border-bottom: none;
      padding: 1.5rem 2rem;
    }
    
    .modal-header .modal-title {
      font-size: 1.25rem;
      font-weight: 600;
      color: white;
    }
    
    .modal-header .btn-close {
      filter: brightness(0) invert(1);
      opacity: 0.8;
    }
    
    .modal-header .btn-close:hover {
      opacity: 1;
    }
    
    .modal-body {
      padding: 2rem;
      line-height: 1.8;
    }
    
    .modal-body p {
      margin-bottom: 1rem;
      font-size: 0.9375rem;
    }
    
    .modal-body p strong {
      color: #667eea;
      font-weight: 600;
      min-width: 120px;
      display: inline-block;
    }
    
    .modal-body h6 {
      margin-top: 1.5rem;
      margin-bottom: 1rem;
      color: #2d3748;
      font-weight: 600;
    }
    
    .modal-body ul {
      list-style: none;
      padding-left: 0;
    }
    
    .modal-body ul li {
      padding: 0.5rem 1rem;
      margin-bottom: 0.5rem;
      background: #f8f9ff;
      border-radius: 8px;
      border-left: 3px solid #667eea;
    }
    
    .modal-body ul li.text-muted {
      background: #f7fafc;
      border-left-color: #cbd5e0;
      color: #718096;
    }
    
    .modal-footer {
      border-top: 1px solid #e2e8f0;
      padding: 1.25rem 2rem;
      background: #f8f9fa;
      border-bottom-left-radius: 16px;
      border-bottom-right-radius: 16px;
    }
    
    /* Logout Modal - Different Style */
    #logoutModal .modal-content {
      border-radius: 20px;
      border: none;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }
    
    #logoutModal .modal-header {
      background: white;
      color: #2d3748;
      border-bottom: 1px solid #e2e8f0;
      padding: 1.5rem;
    }
    
    #logoutModal .modal-header .modal-title {
      color: #2d3748;
      font-size: 1.25rem;
    }
    
    #logoutModal .modal-header .btn-close {
      filter: none;
      opacity: 0.5;
    }
    
    #logoutModal .modal-body {
      background: white;
      padding: 2rem;
    }
    
    #logoutModal .modal-footer {
      background: white;
      border-top: 1px solid #e2e8f0;
      padding: 1.5rem;
    }
    
    @media (max-width: 768px) {
      .table thead th,
      .table tbody td {
        padding: 0.75rem 0.5rem;
        font-size: 0.8125rem;
      }
      
      .btn-action {
        padding: 0.4rem 0.75rem;
        font-size: 0.8125rem;
      }
      
      .modal-body {
        padding: 1.5rem;
      }
      
      .modal-header {
        padding: 1.25rem 1.5rem;
      }
    }
  </style>
</head>
<body>
  <div class="dashboard d-flex">
    <!-- Mobile Menu Toggle Button -->
    <button class="sidebar-toggle d-md-none" id="mobileMenuToggle">
      <i class="bi bi-list" style="font-size: 24px; color: white;"></i>
    </button>
    
    <!-- Overlay for mobile -->
    <div class="sidebar-overlay d-md-none" id="sidebarOverlay"></div>
    
    <aside class="sidebar d-flex flex-column align-items-center p-3" id="sidebar">
      <img src="{{ asset('assets/images/Group 44.png') }}" alt="Scholarly Logo" class="logo mb-4">
      <div class="profile text-center mb-4">
        <img src="{{ asset('assets/Images/Admin.png') }}" alt="Profile" class="profile-img mb-2">
        <h2 class="h5 fw-bold">Admin</h2>
        <p class="small mb-2">ADMIN</p>
      </div>
      <hr class="w-100">
      <nav class="nav flex-column w-100">
        <a href="{{ route('admin') }}" class="nav-link d-flex align-items-center gap-2 text-white">
          <img src="{{ asset('assets/Images/material-symbols_school.png') }}" alt=""> Scholarships
        </a>
        <a href="{{ route('admin.add-scholarship') }}" class="nav-link d-flex align-items-center gap-2 text-white">
          <img src="{{ asset('assets/Images/material-add_symbols_school.png') }}" alt=""> Add Scholarship
        </a>
        <a href="{{ route('admin.students') }}" class="nav-link active d-flex align-items-center gap-2 text-white">
          <img src="{{ asset('assets/Images/ph_student-bold.png') }}" alt=""> Student Management
        </a>
        <a href="#" id="logoutBtn" class="nav-link d-flex align-items-center gap-2 text-white">
          <i class="bi bi-box-arrow-right"></i> Logout
        </a>
      </nav>
    </aside>

    <main class="main-content flex-grow-1 p-4">
      <h4 class="fw-bold mb-3">Student Management</h4>

      <form class="search-container" method="GET" action="{{ route('admin.students') }}">
        <svg xmlns="http://www.w3.org/2000/svg" 
             viewBox="0 0 24 24" 
             fill="none" 
             stroke="#a0aec0" 
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
          value="{{ $query }}" 
          placeholder="Search by name, email, or contact..." 
          class="search-input"
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
            @if($students->count() > 0)
              @foreach($students as $student)
                <tr>
                  <td class="student-id">#{{ $student->id }}</td>
                  <td class="student-name">{{ $student->first_name }} {{ $student->last_name }}</td>
                  <td class="student-email">{{ $student->email }}</td>
                  <td class="student-contact">{{ $student->contact ?? 'N/A' }}</td>
                  <td class="text-center">
                    <button class="btn btn-action btn-view me-2 view-btn" data-id="{{ $student->id }}">
                      <i class="bi bi-eye-fill"></i> View
                    </button>
                    <button class="btn btn-action btn-delete delete-btn" data-id="{{ $student->id }}">
                      <i class="bi bi-trash-fill"></i> Delete
                    </button>
                  </td>
                </tr>
              @endforeach
            @else
              <tr>
                <td colspan="5" class="text-center text-muted py-5">
                  <i class="bi bi-inbox" style="font-size: 3rem; color: #cbd5e0;"></i>
                  <p class="mt-2 mb-0">No students found.</p>
                </td>
              </tr>
            @endif
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
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
          <h5 class="modal-title fw-bold">Confirm Deletion</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center" style="padding: 2.5rem 2rem;">
          <i class="bi bi-exclamation-triangle text-danger" style="font-size: 4rem;"></i>
          <h5 class="fw-bold mt-3 mb-3">Are you sure?</h5>
          <p class="text-muted mb-0">This action cannot be undone. The student and all related data will be permanently deleted.</p>
        </div>
        <div class="modal-footer" style="justify-content: center; gap: 1rem;">
          <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
          <form id="deleteForm" method="POST" action="{{ route('admin.students.delete') }}" style="margin: 0;">
            @csrf
            <input type="hidden" name="id" id="deleteId">
            <button type="submit" class="btn btn-danger px-4">Delete Student</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Logout Confirmation Modal -->
  <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold" id="logoutModalLabel">
            <i class="bi bi-box-arrow-right me-2 text-warning"></i>Logout Confirmation
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center py-4">
          <div class="mb-3">
            <i class="bi bi-exclamation-circle text-warning" style="font-size: 3rem;"></i>
          </div>
          <p class="fs-5 mb-2">Are you sure you want to logout?</p>
          <p class="text-muted small">You will need to login again to access the admin panel.</p>
        </div>
        <div class="modal-footer border-0 pt-0 justify-content-center gap-2">
          <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1"></i>Cancel
          </button>
          <a href="{{ route('logout') }}" class="btn btn-danger px-4">
            <i class="bi bi-box-arrow-right me-1"></i>Yes, Logout
          </a>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Logout confirmation
    document.getElementById('logoutBtn')?.addEventListener('click', function(e) {
      e.preventDefault();
      const logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'));
      logoutModal.show();
    });

    const viewButtons = document.querySelectorAll('.view-btn');
    const deleteButtons = document.querySelectorAll('.delete-btn');
    const studentDetails = document.getElementById('studentDetails');
    const viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

    viewButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        fetch(`{{ route('admin.students.details') }}?id=${id}`, { 
          credentials: 'same-origin',
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          }
        })
          .then(res => res.json())
          .then(data => {
            if (data.error) {
              studentDetails.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
            } else {
              let scholarships = data.scholarships && data.scholarships.length > 0
                ? data.scholarships.map(s => `<li>${s}</li>`).join('')
                : '<li class="text-muted">No scholarships bookmarked</li>';
              
              studentDetails.innerHTML = `
                <p><strong>First Name:</strong> ${data.first_name || 'N/A'}</p>
                <p><strong>Last Name:</strong> ${data.last_name || 'N/A'}</p>
                <p><strong>Username:</strong> ${data.username}</p>
                <p><strong>Email:</strong> ${data.email}</p>
                <p><strong>Contact:</strong> ${data.contact || 'N/A'}</p>
                <h6 class="fw-bold mt-3">Scholarships Bookmarked:</h6>
                <ul>${scholarships}</ul>
              `;
            }
            viewModal.show();
          })
          .catch(err => {
            studentDetails.innerHTML = `<div class="alert alert-danger">Failed to load student details.</div>`;
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

    document.getElementById('deleteForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const form = this;
      const formData = new FormData(form);
      
      fetch(form.action, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            deleteModal.hide();
            location.reload();
          } else {
            alert('Failed to delete student: ' + (data.error || 'Unknown error'));
          }
        })
        .catch(err => {
          alert('Failed to delete student');
        });
    });

    // Mobile menu functionality
    (function() {
      const sidebar = document.querySelector('.sidebar');
      const mobileToggle = document.getElementById('mobileMenuToggle');
      const sidebarOverlay = document.getElementById('sidebarOverlay');
      
      // Initialize: ensure overlay is hidden on page load
      if (sidebarOverlay) {
        sidebarOverlay.classList.remove('active');
        sidebarOverlay.style.display = 'none';
        sidebarOverlay.style.pointerEvents = 'none';
      }
      if (sidebar) {
        sidebar.classList.remove('active');
      }
      
      function openMobileMenu() {
        if (sidebar && sidebarOverlay) {
          sidebar.classList.add('active');
          sidebarOverlay.classList.add('active');
          sidebarOverlay.style.display = 'block';
          sidebarOverlay.style.pointerEvents = 'auto';
          document.body.style.overflow = 'hidden';
          if (mobileToggle) {
            mobileToggle.style.display = 'none';
          }
        }
      }
      
      function closeMobileMenu() {
        if (sidebar && sidebarOverlay) {
          sidebar.classList.remove('active');
          sidebarOverlay.classList.remove('active');
          sidebarOverlay.style.display = 'none';
          sidebarOverlay.style.pointerEvents = 'none';
          document.body.style.overflow = '';
          if (mobileToggle) {
            mobileToggle.style.display = 'flex';
          }
        }
      }
      
      if (mobileToggle && sidebarOverlay) {
        mobileToggle.addEventListener('click', function(e) {
          e.stopPropagation();
          e.preventDefault();
          if (sidebar && sidebar.classList.contains('active')) {
            closeMobileMenu();
          } else {
            openMobileMenu();
          }
        });
        
        sidebarOverlay.addEventListener('click', function(e) {
          e.stopPropagation();
          closeMobileMenu();
        });
        
        if (sidebar) {
          const navLinks = sidebar.querySelectorAll('.nav-link');
          navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
              // Allow navigation to proceed immediately, close menu asynchronously
              if (window.innerWidth <= 768) {
                setTimeout(() => {
                  closeMobileMenu();
                }, 0);
              }
            });
          });
        }
      }
    })();
  </script>
</body>
</html>

