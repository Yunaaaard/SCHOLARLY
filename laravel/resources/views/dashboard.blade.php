@php($title = 'Dashboard')
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Scholarly Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
        <img src="{{ asset('assets/Images/profile.png') }}" alt="" class="profile-img mb-2 big-circle">
        <h2 id="sidebarName" class="h5 fw-bold">{{ session('username') }}</h2>
        <p class="small mb-2">STUDENT</p>
        <a class="btn btn-primary rounded-pill" href="{{ url('/profile/edit') }}">Edit Profile</a>
      </div>
      <hr class="w-100">
      <nav class="nav flex-column w-100">
        <a href="{{ route('dashboard') }}" class="nav-link d-flex align-items-center gap-2 text-white">
          <i class="bi bi-house-door"></i> Home
        </a>
        <a href="{{ url('/settings') }}" class="nav-link d-flex align-items-center gap-2 text-white">
          <i class="bi bi-gear"></i> Settings
        </a>
        <a href="{{ url('/bookmarks') }}" class="nav-link d-flex align-items-center gap-2 text-white">
          <i class="bi bi-bookmark"></i> Bookmarks
        </a>
        <a href="{{ route('logout') }}" class="nav-link d-flex align-items-center gap-2 text-white">
          <i class="bi bi-box-arrow-right"></i> Logout
        </a>
      </nav>
      <button class="sidebar-toggle d-none d-md-flex" id="sidebarToggle">
        <img src="{{ asset('assets/Images/left_arrow.png') }}" alt="Toggle Sidebar" class="arrow-icon">
      </button>
    </aside>

    <main class="main-content flex-grow-1 p-4">
      <div class="controls d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center mb-4 gap-3">
        <div class="search-container position-relative w-100">
          <i class="bi bi-search position-absolute" style="top: 50%; left: 12px; transform: translateY(-50%); color: #6c757d;"></i>
          <input type="text" class="form-control search-input ps-5" placeholder="Search available scholarships">
        </div>
        <div class="filter-sort d-flex gap-2 w-100 w-md-auto">
          <button class="btn btn-outline-secondary filter-btn d-flex align-items-center gap-1 flex-fill flex-md-initial">
            <i class="bi bi-funnel"></i> <span class="d-none d-sm-inline">FILTER</span>
          </button>
          <button class="btn btn-outline-secondary sort-btn d-flex align-items-center gap-1 flex-fill flex-md-initial">
            <i class="bi bi-arrow-down-up"></i> <span class="d-none d-sm-inline">SORT</span>
          </button>
        </div>
      </div>
      <div id="scholarshipCards" class="cards d-flex flex-column gap-3"></div>
    </main>
  </div>

  <!-- Scholarship Detail Modal -->
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
              <div class="benefits-section mb-4">
                <h5 class="section-title mb-3"><span class="benefits-icon">🎓</span> Benefits</h5>
                <ul id="modalBenefits" class="benefits-list"></ul>
              </div>
              <div class="requirements-section mb-4">
                <h5 class="section-title mb-3"><span class="requirements-icon">✅</span> Application Requirements</h5>
                <ul id="modalRequirements" class="requirements-list"></ul>
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
    (function() {
fetch('{{ url('api/user-profile') }}', { credentials: 'same-origin' })
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(data => {
          if (data.username) document.getElementById('sidebarName').textContent = data.username;
          if (data.profile_picture) {
            const imgEl = document.querySelector('.profile-img');
            if (imgEl) imgEl.src = data.profile_picture;
          }
        }).catch(() => {});

fetch('{{ url('api/scholarships') }}', { credentials: 'same-origin' })
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(items => {
          const wrap = document.getElementById('scholarshipCards');
          if (!Array.isArray(items) || !wrap) return;
          let allItems = items.slice(0);
          let sortAsc = true;
          let currentCategory = '';
          function clearWrap() { while (wrap.firstChild) wrap.removeChild(wrap.firstChild); }
          function render(list) {
            clearWrap();
            if (!list || list.length === 0) {
              const info = document.createElement('div');
              info.className = 'alert alert-info mb-0';
              info.textContent = 'No scholarships available.';
              wrap.appendChild(info); return;
            }
            list.forEach(s => {
              const card = document.createElement('div'); card.className = 'card position-relative';
              const left = document.createElement('div'); left.className = 'card-left';
const img = document.createElement('img'); img.alt = 'logo'; img.src = (s.image_path && s.image_path.length) ? ('{{ url('../../') }}/' + s.image_path) : '{{ asset('assets/Images/image.png') }}'; left.appendChild(img);
              const middle = document.createElement('div'); middle.className = 'card-middle';
              const title = document.createElement('h3'); title.className = 'card-title'; title.textContent = s.title || '';
              const sponsor = document.createElement('p'); sponsor.className = 'card-sponsor'; sponsor.textContent = s.sponsor || '';
              middle.appendChild(title); middle.appendChild(sponsor);
              const right = document.createElement('div'); right.className = 'card-right';
              const bookmarkIcon = document.createElement('button'); bookmarkIcon.className = 'bookmark-icon'; if (s.bookmarked) { bookmarkIcon.classList.add('bookmarked'); }
              bookmarkIcon.onclick = function(){ toggleBookmark(s.id, bookmarkIcon); };
              const btn = document.createElement('button'); btn.className = 'btn btn-primary'; btn.textContent = 'VIEW'; btn.onclick = function(){ showScholarshipDetails(s.id); };
              right.appendChild(bookmarkIcon); right.appendChild(btn);
              card.appendChild(left); card.appendChild(middle); card.appendChild(right); wrap.appendChild(card);
            });
          }
          function applyFilters() {
            const q = (document.querySelector('.search-input')?.value || '').toLowerCase();
            const filtered = allItems.filter(it => {
              const t = (it.title || '').toLowerCase();
              const sp = (it.sponsor || '').toLowerCase();
              const catOk = currentCategory ? (it.category === currentCategory) : true;
              const qOk = !q || t.includes(q) || sp.includes(q);
              return catOk && qOk;
            });
            filtered.sort((a,b)=>{
              const at=(a.title||'').toLowerCase(); const bt=(b.title||'').toLowerCase();
              if (at<bt) return sortAsc?-1:1; if (at>bt) return sortAsc?1:-1; return 0;
            });
            return filtered;
          }
          render(applyFilters());
          const searchEl = document.querySelector('.search-input');
          if (searchEl) searchEl.addEventListener('input', ()=>{ render(applyFilters()); });
          const sortBtn = document.querySelector('.sort-btn');
          if (sortBtn) sortBtn.addEventListener('click', ()=>{ sortAsc=!sortAsc; render(applyFilters()); });
          const filterBtn = document.querySelector('.filter-btn');
          if (filterBtn) {
            const container = document.querySelector('.filter-sort');
            const select = document.createElement('select'); select.className='form-select'; select.style.maxWidth='180px'; select.style.display='none';
            const optAll=document.createElement('option'); optAll.value=''; optAll.textContent='All Categories'; select.appendChild(optAll);
            const cats=[...new Set(allItems.map(it=>it.category||'').filter(Boolean))];
            cats.forEach(c=>{ const o=document.createElement('option'); o.value=c; o.textContent=c; select.appendChild(o); });
            container.insertBefore(select, container.firstChild);
            filterBtn.addEventListener('click', ()=>{ select.style.display = (select.style.display==='none')?'block':'none'; });
            select.addEventListener('change', ()=>{ currentCategory = select.value || ''; render(applyFilters()); });
          }
        }).catch(()=>{});
    })();

    function toggleBookmark(scholarshipId, iconElement) {
      fetch('{{ url('api/bookmarks/toggle') }}', {
        method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ scholarship_id: scholarshipId, action: 'toggle' })
      }).then(r=>r.json()).then(data=>{
        if (data.success) { if (data.bookmarked) iconElement.classList.add('bookmarked'); else iconElement.classList.remove('bookmarked'); }
      }).catch(()=>{});
    }

    // Desktop sidebar toggle
    const sidebar = document.querySelector('.sidebar');
    const toggle = document.querySelector('#sidebarToggle');
    if (toggle) toggle.addEventListener('click', ()=>{ sidebar.classList.toggle('collapsed'); });
    
    // Mobile menu toggle
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
      
      // Close sidebar when clicking nav links on mobile
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

    let currentScholarshipId = null;
    function showScholarshipDetails(scholarshipId) {
      currentScholarshipId = scholarshipId;
      fetch('{{ url('api/scholarships') }}/' + scholarshipId, { credentials: 'same-origin' })
        .then(r=> r.ok ? r.json() : Promise.reject())
        .then(data=>{
          document.getElementById('modalLogo').src = data.image_path ? ('{{ url('../../') }}/' + data.image_path) : '{{ asset('assets/Images/image.png') }}';
          document.getElementById('modalTitle').textContent = data.title || '';
          document.getElementById('modalSponsor').textContent = data.sponsor || '';
          const eligibilityList = document.getElementById('modalEligibility');
          eligibilityList.innerHTML = '';
          if (data.description) { const li=document.createElement('li'); li.innerHTML = data.description; eligibilityList.appendChild(li); }
          document.querySelector('.benefits-section').style.display = 'none';
          document.querySelector('.requirements-section').style.display = 'none';
          let dateRange = 'N/A';
          if (data.start_date && data.end_date) dateRange = data.start_date + ' - ' + data.end_date;
          else if (data.start_date) dateRange = 'From ' + data.start_date;
          else if (data.end_date) dateRange = 'Until ' + data.end_date;
          document.getElementById('modalDate').textContent = dateRange;
          document.getElementById('modalContact').textContent = data.email || data.phone || 'N/A';
          const applyBtn = document.getElementById('applyBtn'); if (applyBtn) { applyBtn.style.display = 'none'; }
          const modal = new bootstrap.Modal(document.getElementById('scholarshipModal'));
          modal.show();
        }).catch(()=>{ alert('Failed to load scholarship details'); });
    }

    function submitApplication(scholarshipId) {
      const applyBtn = document.getElementById('applyBtn');
      applyBtn.disabled = true; applyBtn.textContent = 'APPLYING...';
      const formData = new FormData(); formData.append('scholarship_id', scholarshipId);
      fetch('{{ url('api/applications') }}', { method: 'POST', body: formData, credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
        .then(r=>r.json()).then(data=>{
          if (data.success) { applyBtn.textContent='APPLIED ✓'; applyBtn.style.backgroundColor='#28a745'; alert('Application submitted successfully!'); }
          else { applyBtn.textContent='APPLY'; applyBtn.disabled=false; alert(data.error || 'Failed to submit application'); }
        }).catch(()=>{ applyBtn.textContent='APPLY'; applyBtn.disabled=false; alert('Failed to submit application'); });
    }
  </script>
</body>
</html>
