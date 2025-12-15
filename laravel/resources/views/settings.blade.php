@php($title = 'Settings')
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Scholarly Settings</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ time() }}">
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
      <h2 class="fw-bold mb-4">Settings</h2>
      <div class="settings-container">
        <div class="mb-4 pb-3 border-bottom">
          <h5 class="fw-bold">Account Information</h5>
          <form id="accountForm">
            @csrf
            <div class="mb-3">
              <label class="form-label">Username</label>
              <input id="setUsername" type="text" class="form-control" value="">
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input id="setEmail" type="email" class="form-control" value="">
            </div>
            <div class="mb-3">
              <label class="form-label">Contact Number</label>
              <input id="setContact" type="text" class="form-control" value="">
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </form>
        </div>
        <div class="mb-4 pb-3 border-bottom">
          <h5 class="fw-bold">Change Password</h5>
          <p class="text-muted small mb-3">Click the button below to change your password securely.</p>
          <button type="button" class="btn btn-primary" id="changePasswordBtn">
            <i class="bi bi-shield-lock me-2"></i>Change Password
          </button>
        </div>
      </div>
    </main>
  </div>

  <!-- Change Password Modal -->
  <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold" id="changePasswordModalLabel">
            <i class="bi bi-shield-lock me-2 text-primary"></i>Change Password
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="passwordForm">
          <div class="modal-body py-4">
            @csrf
            <div class="alert alert-info d-flex align-items-center" role="alert">
              <i class="bi bi-info-circle me-2"></i>
              <small>You need to verify your current password before changing it.</small>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Current Password <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-key"></i></span>
                <input type="password" class="form-control" id="currentPassword" placeholder="Enter your current password" required>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">New Password <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" class="form-control" id="newPassword" placeholder="Enter new password" required>
              </div>
              <small class="text-muted">Password must be at least 8 characters long.</small>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Confirm New Password <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                <input type="password" class="form-control" id="confirmPassword" placeholder="Confirm new password" required>
              </div>
            </div>
            <div id="passwordError" class="alert alert-danger d-none" role="alert"></div>
            <div id="passwordSuccess" class="alert alert-success d-none" role="alert"></div>
          </div>
          <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-circle me-1"></i>Update Password
            </button>
          </div>
        </form>
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
    
    document.addEventListener('DOMContentLoaded', function(){
      fetch('{{ url('api/user-profile') }}', { credentials: 'same-origin', cache: 'no-store' })
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(data => {
          const u = data.username || ''; const e = data.email || ''; const c = data.contact || '';
          const elUser = document.getElementById('setUsername');
          const elEmail = document.getElementById('setEmail');
          const elContact = document.getElementById('setContact');
          const elSidebar = document.getElementById('sidebarName');
          if (elUser) elUser.value = u; if (elEmail) elEmail.value = e; if (elContact) elContact.value = c;
          // Update sidebar name with full name if available
          if (elSidebar) {
            if (data.first_name && data.last_name) {
              elSidebar.textContent = data.first_name + ' ' + data.last_name;
            } else if (u) {
              elSidebar.textContent = u;
            }
            // Fade in the name after it's been set
            elSidebar.style.opacity = '1';
          }
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
        }).catch(()=>{});

      const accountForm = document.getElementById('accountForm');
      if (accountForm) {
        accountForm.addEventListener('submit', function(ev){
          ev.preventDefault();
          const username = document.getElementById('setUsername').value.trim();
          const email = document.getElementById('setEmail').value.trim();
          const contact = document.getElementById('setContact').value.trim();
          const fd = new FormData();
          fd.append('_token', '{{ csrf_token() }}');
          fd.append('username', username); fd.append('email', email); fd.append('contact', contact);
          fetch('{{ url('api/user-profile') }}', { method: 'POST', credentials: 'same-origin', body: fd })
            .then(r => r.json().then(j => ({ ok: r.ok, body: j })))
            .then(res => {
              const alertDiv = document.createElement('div');
              alertDiv.className = 'alert mt-3 ' + (res.ok ? 'alert-success' : 'alert-danger');
              alertDiv.textContent = res.ok ? 'Profile updated successfully.' : (res.body && res.body.error ? res.body.error : 'Update failed');
              accountForm.appendChild(alertDiv); setTimeout(()=> alertDiv.remove(), 3000);
              if (res.ok && username) { const elSidebar = document.getElementById('sidebarName'); if (elSidebar) elSidebar.textContent = username; }
            }).catch(()=>{
              const alertDiv = document.createElement('div'); alertDiv.className = 'alert mt-3 alert-danger'; alertDiv.textContent = 'Network error. Please try again.'; accountForm.appendChild(alertDiv); setTimeout(()=> alertDiv.remove(), 3000);
            });
        });
      }

      // Change Password Modal trigger
      const changePasswordBtn = document.getElementById('changePasswordBtn');
      if (changePasswordBtn) {
        changePasswordBtn.addEventListener('click', function() {
          const changePasswordModal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
          changePasswordModal.show();
        });
      }

      // Password Form submission
      const passwordForm = document.getElementById('passwordForm');
      if (passwordForm) {
        passwordForm.addEventListener('submit', function(ev){
          ev.preventDefault();
          const currentPassword = document.getElementById('currentPassword').value.trim();
          const newPassword = document.getElementById('newPassword').value.trim();
          const confirmPassword = document.getElementById('confirmPassword').value.trim();
          
          // Hide previous alerts
          document.getElementById('passwordError').classList.add('d-none');
          document.getElementById('passwordSuccess').classList.add('d-none');
          
          // Validation
          if (!currentPassword) return showPasswordModalAlert('Please enter your current password.', 'error');
          if (!newPassword) return showPasswordModalAlert('Please enter a new password.', 'error');
          if (newPassword !== confirmPassword) return showPasswordModalAlert('New passwords do not match.', 'error');
          if (newPassword.length < 8) return showPasswordModalAlert('Password must be at least 8 characters long.', 'error');
          if (currentPassword === newPassword) return showPasswordModalAlert('New password must be different from current password.', 'error');
          
          const fd = new FormData();
          fd.append('_token', '{{ csrf_token() }}');
          fd.append('current_password', currentPassword);
          fd.append('password', newPassword);
          fd.append('username', document.getElementById('setUsername').value.trim());
          fd.append('email', document.getElementById('setEmail').value.trim());
          fd.append('contact', document.getElementById('setContact').value.trim());
          fd.append('action', 'change_password');
          
          fetch('{{ url('api/user-profile') }}', { method: 'POST', credentials: 'same-origin', body: fd })
            .then(r => r.json().then(j => ({ ok: r.ok, body: j })))
            .then(res => { 
              if (res.ok && res.body.success) { 
                showPasswordModalAlert('Password updated successfully! The modal will close shortly.', 'success'); 
                passwordForm.reset();
                setTimeout(() => {
                  bootstrap.Modal.getInstance(document.getElementById('changePasswordModal')).hide();
                }, 2000);
              } else { 
                showPasswordModalAlert(res.body && res.body.error ? res.body.error : 'Password update failed', 'error'); 
              } 
            })
            .catch(()=> showPasswordModalAlert('Network error. Please try again.', 'error'));
        });
      }

      function showPasswordModalAlert(message, type) {
        const errorDiv = document.getElementById('passwordError');
        const successDiv = document.getElementById('passwordSuccess');
        
        if (type === 'error') {
          errorDiv.textContent = message;
          errorDiv.classList.remove('d-none');
          successDiv.classList.add('d-none');
        } else {
          successDiv.textContent = message;
          successDiv.classList.remove('d-none');
          errorDiv.classList.add('d-none');
        }
      }
    });
  </script>
</body>
</html>
