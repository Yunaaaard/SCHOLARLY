@php($title = 'Settings')
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Scholarly Settings</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body>
  <div class="dashboard d-flex">
    <aside class="sidebar d-flex flex-column align-items-center p-3">
      <img src="{{ asset('assets/images/Group 44.png') }}" alt="Scholarly Logo" class="logo mb-4 sidebar-logo">
      <div class="profile text-center mb-4">
        <img src="{{ asset('assets/Images/profile.png') }}" alt="Profile" class="profile-img mb-2 big-circle">
        <h2 id="sidebarName" class="h5 fw-bold">{{ session('username') }}</h2>
        <p class="small mb-2">STUDENT</p>
        <a class="btn btn-primary rounded-pill" href="{{ url('/profile/edit') }}">Edit Profile</a>
      </div>
      <hr class="w-100">
      <nav class="nav flex-column w-100">
        <a href="{{ route('dashboard') }}" class="nav-link d-flex align-items-center gap-2 text-white"><i class="bi bi-house-door"></i> Home</a>
        <a href="{{ url('/settings') }}" class="nav-link d-flex align-items-center gap-2 text-white"><i class="bi bi-gear"></i> Settings</a>
        <a href="{{ url('/bookmarks') }}" class="nav-link d-flex align-items-center gap-2 text-white"><i class="bi bi-bookmark"></i> Bookmarks</a>
        <a href="{{ route('logout') }}" class="nav-link d-flex align-items-center gap-2 text-white"><i class="bi bi-box-arrow-right"></i> Logout</a>
      </nav>
      <button class="sidebar-toggle" id="sidebarToggle">
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
          <form id="passwordForm">
            @csrf
            <div class="mb-3">
              <label class="form-label">New Password</label>
              <input type="password" class="form-control" id="newPassword" placeholder="Enter new password">
            </div>
            <div class="mb-3">
              <label class="form-label">Confirm New Password</label>
              <input type="password" class="form-control" id="confirmPassword" placeholder="Confirm new password">
            </div>
            <button type="submit" class="btn btn-primary">Update Password</button>
          </form>
        </div>
      </div>
    </main>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function(){
      fetch('{{ url('api/user-profile') }}', { credentials: 'same-origin', cache: 'no-store' })
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(data => {
          const u = data.username || ''; const e = data.email || ''; const c = data.contact || '';
          const elUser = document.getElementById('setUsername');
          const elEmail = document.getElementById('setEmail');
          const elContact = document.getElementById('setContact');
          const elSidebar = document.getElementById('sidebarName');
          if (elUser) elUser.value = u; if (elEmail) elEmail.value = e; if (elContact) elContact.value = c; if (elSidebar && u) elSidebar.textContent = u;
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

      const passwordForm = document.getElementById('passwordForm');
      if (passwordForm) {
        passwordForm.addEventListener('submit', function(ev){
          ev.preventDefault();
          const newPassword = document.getElementById('newPassword').value.trim();
          const confirmPassword = document.getElementById('confirmPassword').value.trim();
          if (!newPassword) return showPasswordAlert('Please enter a new password.', 'danger');
          if (newPassword !== confirmPassword) return showPasswordAlert('Passwords do not match.', 'danger');
          if (newPassword.length < 6) return showPasswordAlert('Password must be at least 6 characters long.', 'danger');
          const fd = new FormData();
          fd.append('_token', '{{ csrf_token() }}');
          fd.append('password', newPassword);
          fd.append('username', document.getElementById('setUsername').value.trim());
          fd.append('email', document.getElementById('setEmail').value.trim());
          fd.append('contact', document.getElementById('setContact').value.trim());
          fd.append('action', 'change_password');
          fetch('{{ url('api/user-profile') }}', { method: 'POST', credentials: 'same-origin', body: fd })
            .then(r => r.json().then(j => ({ ok: r.ok, body: j })))
            .then(res => { if (res.ok && res.body.success) { showPasswordAlert('Password updated successfully.', 'success'); passwordForm.reset(); } else { showPasswordAlert(res.body && res.body.error ? res.body.error : 'Password update failed', 'danger'); } })
            .catch(()=> showPasswordAlert('Network error. Please try again.', 'danger'));
        });
      }

      function showPasswordAlert(message, type) {
        const oldAlert = document.querySelector('#passwordForm .alert'); if (oldAlert) oldAlert.remove();
        const alertDiv = document.createElement('div'); alertDiv.className = 'alert mt-3 alert-' + type; alertDiv.textContent = message; passwordForm.appendChild(alertDiv); setTimeout(()=> alertDiv.remove(), 3000);
      }
    });
  </script>
</body>
</html>
