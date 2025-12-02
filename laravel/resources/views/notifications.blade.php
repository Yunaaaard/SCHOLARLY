@php($title = 'Notifications')
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Notifications - Scholarly</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
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
        <h2 class="h5 fw-bold sidebarName">{{ session('username') }}</h2>
        <p class="small mb-2">STUDENT</p>
        <a href="{{ url('/profile/edit') }}" class="btn btn-sm btn-outline-light">EDIT PROFILE</a>
      </div>
      <hr class="w-100">
      <nav class="nav flex-column w-100">
        <a href="{{ route('dashboard') }}" class="nav-link d-flex align-items-center gap-2 text-white"><i class="bi bi-house-door"></i> Home</a>
        <a href="{{ url('/settings') }}" class="nav-link d-flex align-items-center gap-2 text-white"><i class="bi bi-gear"></i> Settings</a>
        <a href="{{ url('/bookmarks') }}" class="nav-link d-flex align-items-center gap-2 text-white"><i class="bi bi-bookmark"></i> Bookmarks</a>
        <a href="{{ url('/notifications') }}" class="nav-link d-flex align-items-center gap-2 text-white active"><i class="bi bi-bell"></i> Notifications</a>
        <a href="{{ route('logout') }}" class="nav-link d-flex align-items-center gap-2 text-white"><i class="bi bi-box-arrow-right"></i> Logout</a>
      </nav>
      <button class="sidebar-toggle d-none d-md-flex" id="sidebarToggle">
        <img src="{{ asset('assets/Images/left_arrow.png') }}" alt="Toggle Sidebar" class="arrow-icon">
      </button>
    </aside>
    <main class="main-content flex-grow-1 p-4">
      <h2 class="fw-bold mb-4">Notifications</h2>
      <div id="notificationsList" class="d-flex flex-column gap-3">
        <div class="text-center">
          <div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>
        </div>
      </div>
    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
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
      
      const toggle = document.querySelector('#sidebarToggle');
      if (toggle) toggle.addEventListener('click', ()=>{ sidebar.classList.toggle('collapsed'); });
    })();
    
    (function() {
      // Load profile picture
      fetch('{{ url('api/user-profile') }}', { credentials: 'same-origin' })
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(data => {
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
      
      fetch('{{ url('api/notifications') }}', { credentials: 'same-origin' })
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(notifications => {
          const container = document.getElementById('notificationsList');
          if (!notifications || notifications.length === 0) { container.innerHTML = '<div class="alert alert-info">No notifications yet.</div>'; return; }
          let html = '';
          notifications.forEach(n => {
            const badgeClass = n.status === 'approved' ? 'success' : (n.status === 'rejected' ? 'danger' : 'warning');
            const icon = n.status === 'approved' ? '✅' : (n.status === 'rejected' ? '❌' : '⏳');
            html += '<div class="card p-3">';
            html += '<div class="d-flex align-items-center gap-3">';
            html += '<span class="fs-4">' + icon + '</span>';
            html += '<div class="flex-grow-1">';
            html += '<h5 class="mb-1">' + (n.scholarship_title || '') + '</h5>';
            html += '<p class="mb-1 text-muted">' + (n.scholarship_sponsor || '') + '</p>';
            html += '<small class="text-muted">Applied on ' + new Date(n.applied_at).toLocaleDateString() + '</small>';
            html += '</div>';
            html += '<span class="badge bg-' + badgeClass + '">' + n.status + '</span>';
            html += '</div>';
            html += '</div>';
          });
          container.innerHTML = html;
        })
        .catch(() => { document.getElementById('notificationsList').innerHTML = '<div class="alert alert-danger">Failed to load notifications.</div>'; });
    })();
  </script>
</body>
</html>
