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
      <h2 class="fw-bold mb-4">Edit Profile</h2>
      <form class="w-75" id="editForm" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
          <label for="profilePic" class="form-label">Profile Picture</label>
          <input type="file" class="form-control" id="profilePic" accept="image/*">
          <div class="form-text">Accepted formats: JPG, PNG, GIF. Max size: 5MB</div>
        </div>
        <div class="mb-3">
          <label for="fullName" class="form-label">Full Name</label>
          <input type="text" class="form-control" id="fullName" value="" required>
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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      fetch('{{ url('api/user-profile') }}', { credentials: 'same-origin' })
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(data => {
          if (data.username) { document.getElementById('fullName').value = data.username; document.getElementById('sidebarName').textContent = data.username; }
          if (data.email) { document.getElementById('email').value = data.email; }
          document.getElementById('contact').value = data.contact || '';
        }).catch(() => {});

      const form = document.getElementById('editForm');
      form.addEventListener('submit', async function (ev) {
        ev.preventDefault();
        const username = document.getElementById('fullName').value.trim();
        const email = document.getElementById('email').value.trim();
        const contact = document.getElementById('contact').value.trim();
        const profilePic = document.getElementById('profilePic').files[0];
        if (!username || !email) { return showAlert('Please fill in required fields.', 'danger'); }
        const fd = new FormData();
        fd.append('_token', '{{ csrf_token() }}');
        fd.append('username', username); fd.append('email', email); fd.append('contact', contact); if (profilePic) fd.append('profilePic', profilePic);
        try {
          const res = await fetch('{{ url('api/user-profile') }}', { method: 'POST', credentials: 'same-origin', body: fd });
          const data = await res.json();
          if (res.ok && data.success) { showAlert('Profile updated successfully.', 'success'); document.getElementById('sidebarName').textContent = username; }
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
