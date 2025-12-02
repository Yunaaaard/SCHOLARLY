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
    .search-input { max-width: 280px; }
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
        <a href="{{ route('logout') }}" class="nav-link d-flex align-items-center gap-2 text-white">
          <i class="bi bi-box-arrow-right"></i> Logout
        </a>
      </nav>
    </aside>

    <main class="main-content flex-grow-1 p-4">
      <h4 class="fw-bold mb-3">Student Management</h4>

      <form class="search-container position-relative d-inline-block mb-4" method="GET" action="{{ route('admin.students') }}">
        <svg xmlns="http://www.w3.org/2000/svg" 
             viewBox="0 0 24 24" 
             fill="none" 
             stroke="#777" 
             stroke-width="2" 
             stroke-linecap="round" 
             stroke-linejoin="round" 
             class="search-icon position-absolute" style="top: 50%; left: 12px; transform: translateY(-50%); width: 18px; height: 18px;">
          <circle cx="11" cy="11" r="8" />
          <line x1="21" y1="21" x2="16.65" y2="16.65" />
        </svg>
        <input 
          type="text" 
          name="q" 
          value="{{ $query }}" 
          placeholder="Search students..." 
          class="search-input ps-5 form-control"
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
                  <td>{{ $student->id }}</td>
                  <td>{{ $student->username }}</td>
                  <td>{{ $student->email }}</td>
                  <td>{{ $student->contact }}</td>
                  <td class="text-center">
                    <button class="btn btn-sm btn-primary me-2 view-btn" data-id="{{ $student->id }}"><i class="bi bi-eye"></i> View</button>
                    <button class="btn btn-sm btn-danger delete-btn" data-id="{{ $student->id }}"><i class="bi bi-trash"></i> Delete</button>
                  </td>
                </tr>
              @endforeach
            @else
              <tr>
                <td colspan="5" class="text-center text-muted">No students found.</td>
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
      <div class="modal-content text-center p-3">
        <i class="bi bi-exclamation-triangle text-danger" style="font-size:2rem;"></i>
        <h5 class="fw-bold mt-2">Confirm Deletion</h5>
        <p>Are you sure you want to delete this student?</p>
        <div class="d-flex justify-content-center gap-3">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <form id="deleteForm" method="POST" action="{{ route('admin.students.delete') }}">
            @csrf
            <input type="hidden" name="id" id="deleteId">
            <button type="submit" class="btn btn-danger">Delete</button>
          </form>
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
                : '<li class="text-muted">No scholarships applied</li>';
              
              studentDetails.innerHTML = `
                <p><strong>Name:</strong> ${data.username}</p>
                <p><strong>Email:</strong> ${data.email}</p>
                <p><strong>Contact:</strong> ${data.contact || 'N/A'}</p>
                <h6 class="fw-bold mt-3">Scholarships Applied:</h6>
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

