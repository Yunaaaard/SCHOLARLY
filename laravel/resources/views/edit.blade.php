@php($title = 'Edit Profile')
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Profile - Scholarly</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
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
      <img src="{{ asset('assets/images/Group 44.png') }}" alt="Scholarly Logo" class="logo mb-4 sidebar-logo">
      <div class="profile text-center mb-4">
        <img src="{{ $profile_picture ?? asset('assets/Images/profile.png') }}" alt="Profile" class="profile-img mb-2 big-circle" onerror="this.src='{{ asset('assets/Images/profile.png') }}'">
        <h2 id="sidebarName" class="h5 fw-bold" style="opacity: 0; transition: opacity 0.2s;">{{ session('username') }}</h2>
        <p class="small mb-2">STUDENT</p>
        <a class="btn btn-primary rounded-pill" href="{{ url('/profile/edit') }}">Edit Profile</a>
      </div>
      <hr class="w-100">
      <nav class="nav flex-column w-100">
        <a href="{{ route('dashboard') }}" class="nav-link d-flex align-items-center gap-2 text-white"><i class="bi bi-house-door"></i> Home</a>
        <a href="{{ url('/settings') }}" class="nav-link d-flex align-items-center gap-2 text-white"><i class="bi bi-gear"></i> Settings</a>
        <a href="{{ url('/bookmarks') }}" class="nav-link d-flex align-items-center gap-2 text-white"><i class="bi bi-bookmark"></i> Bookmarks</a>
        <a href="#" id="logoutBtn" class="nav-link d-flex align-items-center gap-2 text-white"><i class="bi bi-box-arrow-right"></i> Logout</a>
      </nav>
      <button class="sidebar-toggle d-none d-md-flex" id="sidebarToggle">
        <img src="{{ asset('assets/Images/left_arrow.png') }}" alt="Toggle Sidebar" class="arrow-icon">
      </button>
    </aside>

    <main class="main-content flex-grow-1 p-4">
      <h2 class="fw-bold mb-4">Edit Profile</h2>
      <form class="w-75" id="editForm" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
          <label for="profilePic" class="form-label">Profile Picture</label>
          <input type="file" class="form-control" id="profilePic" accept="image/*">
          <div class="form-text">Accepted formats: JPG, PNG, GIF. Max size: 5MB</div>
        </div>
        <div class="mb-3">
          <label for="firstName" class="form-label">First Name</label>
          <input type="text" class="form-control" id="firstName" value="">
        </div>
        <div class="mb-3">
          <label for="lastName" class="form-label">Last Name</label>
          <input type="text" class="form-control" id="lastName" value="">
        </div>
        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control" id="email" value="" readonly>
        </div>
        <div class="mb-3">
          <label for="contact" class="form-label">Contact</label>
          <input type="text" class="form-control" id="contact" value="">
        </div>
        <button type="submit" class="btn btn-primary rounded-pill">Save Changes</button>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary rounded-pill ms-2">Cancel</a>
      </form>
    </main>
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
          <p class="text-muted small">You will need to login again to access your account.</p>
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
      
      // Desktop sidebar toggle
      const toggle = document.querySelector('#sidebarToggle');
      if (toggle && sidebar) {
        toggle.addEventListener('click', (e) => {
          e.preventDefault();
          e.stopPropagation();
          sidebar.classList.toggle('collapsed');
        });
      }
    })();
    
    document.addEventListener('DOMContentLoaded', function () {
      fetch('{{ url('api/user-profile') }}', { credentials: 'same-origin' })
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(data => {
          if (data.first_name) { document.getElementById('firstName').value = data.first_name; }
          if (data.last_name) { document.getElementById('lastName').value = data.last_name; }
          const sidebarName = document.getElementById('sidebarName');
          if (data.first_name || data.last_name) {
            const fullName = (data.first_name || '') + ' ' + (data.last_name || '');
            sidebarName.textContent = fullName.trim() || data.username;
          } else if (data.username) {
            sidebarName.textContent = data.username;
          }
          // Fade in the name after it's been set
          sidebarName.style.opacity = '1';
          if (data.email) { document.getElementById('email').value = data.email; }
          document.getElementById('contact').value = data.contact || '';
          // Load profile picture
          if (data.profile_picture) {
            const imgEl = document.querySelector('.profile-img');
            if (imgEl) {
              const profilePicUrl = data.profile_picture.startsWith('http') 
                ? data.profile_picture 
                : '{{ url("/") }}/' + data.profile_picture;
              imgEl.src = profilePicUrl;
            }
          }
        }).catch(() => {});

      const form = document.getElementById('editForm');
      form.addEventListener('submit', async function (ev) {
        ev.preventDefault();
        const firstName = document.getElementById('firstName').value.trim();
        const lastName = document.getElementById('lastName').value.trim();
        const email = document.getElementById('email').value.trim();
        const contact = document.getElementById('contact').value.trim();
        const profilePic = document.getElementById('profilePic').files[0];
        if (!email) { return showAlert('Email is required.', 'danger'); }
        const fd = new FormData();
        fd.append('_token', '{{ csrf_token() }}');
        if (firstName) fd.append('first_name', firstName);
        if (lastName) fd.append('last_name', lastName);
        fd.append('email', email); fd.append('contact', contact); if (profilePic) fd.append('profilePic', profilePic);
        try {
          const res = await fetch('{{ url('api/user-profile') }}', { method: 'POST', credentials: 'same-origin', body: fd });
          const data = await res.json();
          if (res.ok && data.success) { 
            showAlert('Profile updated successfully.', 'success');
            if (firstName && lastName) {
              document.getElementById('sidebarName').textContent = firstName + ' ' + lastName;
            }
            // Reload profile picture if it was updated
            if (profilePic) {
              fetch('{{ url('api/user-profile') }}', { credentials: 'same-origin', cache: 'no-store' })
                .then(r => r.ok ? r.json() : Promise.reject())
                .then(profileData => {
                  if (profileData.profile_picture) {
                    const imgEl = document.querySelector('.profile-img');
                    if (imgEl) {
                      const profilePicUrl = profileData.profile_picture.startsWith('http') 
                        ? profileData.profile_picture 
                        : '{{ url("/") }}/' + profileData.profile_picture;
                      imgEl.src = profilePicUrl + '?t=' + Date.now(); // Add timestamp to force refresh
                    }
                  }
                }).catch(() => {});
            }
          }
          else { showAlert(data.error || 'Update failed.', 'danger'); }
        } catch {
          showAlert('Network error. Please try again.', 'danger');
        }
      });

      function showAlert(message, type) {
        const oldAlert = document.querySelector('.alert'); if (oldAlert) oldAlert.remove();
        const alertDiv = document.createElement('div'); alertDiv.className = `alert alert-${type} mt-3`; alertDiv.textContent = message; document.getElementById('editForm').appendChild(alertDiv); setTimeout(() => alertDiv.remove(), 3000);
      }
    });
  </script>
</body>
</html>
