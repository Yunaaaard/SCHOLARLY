@php($title = 'Bookmarks')
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Scholarly Bookmarks</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body>
  <div class="dashboard d-flex">
    <!-- Mobile Menu Toggle Button -->
    <button class="sidebar-toggle d-md-none" id="mobileMenuToggle" style="display: none;">
      <i class="bi bi-list" style="font-size: 24px; color: white;"></i>
    </button>
    
    <!-- Overlay for mobile -->
    <div class="sidebar-overlay d-md-none" id="sidebarOverlay" style="display: none;"></div>
    
    <aside class="sidebar d-flex flex-column align-items-center p-3" id="sidebar">
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
      <button class="sidebar-toggle d-none d-md-flex" id="sidebarToggle">
        <img src="{{ asset('assets/Images/left_arrow.png') }}" alt="Toggle Sidebar" class="arrow-icon">
      </button>
    </aside>
    <main class="main-content flex-grow-1 p-4">
      <h2 class="fw-bold mb-4">Bookmarks</h2>
      <div id="bookmarkCards" class="cards d-flex flex-column gap-3"></div>
    </main>
  </div>

  <div id="scholarshipModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="scholarship-detail">
            <div class="scholarship-header d-flex align-items-center gap-3 mb-4">
              <img id="modalLogo" src="" alt="Logo" class="scholarship-logo">
              <div>
                <h2 id="modalTitle" class="scholarship-title mb-1"></h2>
                <p id="modalSponsor" class="scholarship-sponsor mb-0"></p>
              </div>
            </div>
            <div class="scholarship-content">
              <div class="eligibility-section mb-4">
                <h5 class="section-title text-primary mb-3">Eligibility</h5>
                <ul id="modalEligibility" class="eligibility-list"></ul>
              </div>
              <div class="scholarship-meta d-flex justify-content-between align-items-center mb-4">
                <div><strong>DATE OF EFFECT:</strong> <span id="modalDate"></span></div>
                <div><strong>CONTACT & DETAILS:</strong> <span id="modalContact"></span></div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary btn-apply" id="applyBtn">APPLY</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Mobile menu functionality
    (function() {
      const sidebar = document.querySelector('.sidebar');
      const mobileToggle = document.getElementById('mobileMenuToggle');
      const sidebarOverlay = document.getElementById('sidebarOverlay');
      
      if (mobileToggle && sidebarOverlay) {
        mobileToggle.addEventListener('click', function() {
          sidebar.classList.add('active');
          sidebarOverlay.style.display = 'block';
          document.body.style.overflow = 'hidden';
        });
        
        sidebarOverlay.addEventListener('click', function() {
          sidebar.classList.remove('active');
          sidebarOverlay.style.display = 'none';
          document.body.style.overflow = '';
        });
        
        const navLinks = sidebar.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
          link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
              sidebar.classList.remove('active');
              sidebarOverlay.style.display = 'none';
              document.body.style.overflow = '';
            }
          });
        });
      }
      
      const toggle = document.querySelector('#sidebarToggle');
      if (toggle) toggle.addEventListener('click', ()=>{ sidebar.classList.toggle('collapsed'); });
    })();
    
    document.addEventListener('DOMContentLoaded', function () {
      const wrap = document.getElementById('bookmarkCards');
      fetch('{{ url('api/bookmarks') }}', { credentials: 'same-origin' })
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(items => {
          if (!Array.isArray(items) || items.length === 0) {
            const info = document.createElement('div');
            info.className = 'alert alert-info text-center py-5';
            info.innerHTML = "<h4>No bookmarks yet</h4><p class='mb-0'>Start bookmarking scholarships to see them here!</p>";
            wrap.appendChild(info); return;
          }
          items.forEach(s => {
            const card = document.createElement('div'); card.className = 'card position-relative p-3 d-flex flex-row justify-content-between align-items-center';
            const img = document.createElement('img'); img.src = s.image_path && s.image_path.length ? ('{{ url('../../') }}/' + s.image_path) : "{{ asset('assets/Images/image.png') }}"; img.alt = 'logo'; img.style.width = '80px'; img.style.height = '80px'; img.style.objectFit = 'cover';
            const middle = document.createElement('div'); const title = document.createElement('h5'); title.textContent = s.title || ''; const sponsor = document.createElement('p'); sponsor.className = 'text-muted mb-0'; sponsor.textContent = s.sponsor || ''; middle.appendChild(title); middle.appendChild(sponsor);
            const right = document.createElement('div'); const bookmarkIcon = document.createElement('button'); bookmarkIcon.className = 'bookmark-icon bookmarked me-2'; bookmarkIcon.onclick = function(){ toggleBookmark(s.id, bookmarkIcon); };
            const btn = document.createElement('button'); btn.className = 'btn btn-primary btn-sm'; btn.textContent = 'VIEW'; btn.onclick = function(){ showScholarshipDetails(s.id); };
            right.appendChild(bookmarkIcon); right.appendChild(btn);
            card.appendChild(img); card.appendChild(middle); card.appendChild(right); wrap.appendChild(card);
          });
        }).catch(err => { console.error('Bookmark load failed:', err); wrap.innerHTML = `<div class="alert alert-danger text-center">Failed to load bookmarks.</div>`; });
    });

    function toggleBookmark(scholarshipId, iconElement) {
      fetch('{{ url('api/bookmarks/toggle') }}', {
        method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ scholarship_id: scholarshipId, action: 'toggle' })
      }).then(r => r.json()).then(data => { if (data.success && !data.bookmarked) { iconElement.closest('.card').remove(); } }).catch(console.error);
    }

    function showScholarshipDetails(scholarshipId) {
      fetch('{{ url('api/scholarships') }}/' + scholarshipId, { credentials: 'same-origin' })
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(data => {
          document.getElementById('modalLogo').src = data.image_path ? ('{{ url('../../') }}/' + data.image_path) : '{{ asset('assets/Images/image.png') }}';
          document.getElementById('modalTitle').textContent = data.title || '';
          document.getElementById('modalSponsor').textContent = data.sponsor || '';
          document.getElementById('modalEligibility').innerHTML = data.description ? `<li>${data.description}</li>` : '<li>No description available</li>';
          const date = (data.start_date && data.end_date) ? `${data.start_date} - ${data.end_date}` : 'N/A';
          document.getElementById('modalDate').textContent = date;
          document.getElementById('modalContact').textContent = data.email || data.phone || 'N/A';
          const applyBtn = document.getElementById('applyBtn'); if (applyBtn) applyBtn.style.display = 'none';
          new bootstrap.Modal(document.getElementById('scholarshipModal')).show();
        }).catch(()=> alert('Failed to load scholarship details'));
    }
  </script>
</body>
</html>
