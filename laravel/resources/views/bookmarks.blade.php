@php($title = 'Bookmarks')
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Scholarly Bookmarks</title>
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
      <div class="controls d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center mb-4 gap-3">
        <h2 class="fw-bold mb-0">Bookmarks</h2>
        <div class="filter-sort-compact d-flex gap-2">
          <div class="dropdown">
            <button class="btn-filter-sort dropdown-toggle d-flex align-items-center gap-2" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-funnel-fill"></i>
              <span class="filter-text">Filter</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="filterDropdown" id="filterDropdownMenu">
              <li><a class="dropdown-item active" href="#" data-category=""><i class="bi bi-check2 me-2"></i>All Categories</a></li>
            </ul>
          </div>
          <button class="btn-filter-sort sort-btn d-flex align-items-center gap-2" type="button">
            <i class="bi bi-sort-alpha-down sort-icon"></i>
            <span>Sort</span>
          </button>
        </div>
      </div>
      <div id="bookmarkCards" class="cards d-flex flex-column gap-3"></div>
    </main>
  </div>

  <div id="scholarshipModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
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
      // Load profile picture and name
      fetch('{{ url('api/user-profile') }}', { credentials: 'same-origin' })
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(data => {
          // Update name
          const sidebarName = document.getElementById('sidebarName');
          if (sidebarName) {
            if (data.first_name && data.last_name) {
              sidebarName.textContent = data.first_name + ' ' + data.last_name;
            } else if (data.username) {
              sidebarName.textContent = data.username;
            }
            // Fade in the name after it's been set
            sidebarName.style.opacity = '1';
          }
          // Update profile picture
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
      
      // Bookmarks functionality
      (()=>{
        let allItems = [];
        let currentCategory = '';
        let sortAsc = true;
        const wrap = document.getElementById('bookmarkCards');
        
        function applyFilters() {
          let filtered = allItems.filter(it => !currentCategory || (it.category || '') === currentCategory);
          filtered.sort((a,b)=> {
            const titleA = (a.title || '').toLowerCase();
            const titleB = (b.title || '').toLowerCase();
            return sortAsc ? titleA.localeCompare(titleB) : titleB.localeCompare(titleA);
          });
          return filtered;
        }
        
        function render(items) {
          wrap.innerHTML = '';
          if (!items || items.length === 0) {
            const info = document.createElement('div');
            info.className = 'alert alert-info text-center py-5';
            info.innerHTML = "<h4>No bookmarks found</h4><p class='mb-0'>Try changing your filters or bookmark some scholarships!</p>";
            wrap.appendChild(info);
            return;
          }
          items.forEach(s => {
            const card = document.createElement('div'); 
            card.className = 'card';
            
            const left = document.createElement('div'); 
            left.className = 'card-left';
            const img = document.createElement('img'); 
            img.src = s.image_path && s.image_path.length ? ('{{ url('../../') }}/' + s.image_path) : "{{ asset('assets/Images/image.png') }}"; 
            img.alt = 'logo';
            left.appendChild(img);
            
            const middle = document.createElement('div'); 
            middle.className = 'card-middle';
            const title = document.createElement('h5'); 
            title.className = 'card-title';
            title.textContent = s.title || ''; 
            const sponsor = document.createElement('p'); 
            sponsor.className = 'card-sponsor'; 
            sponsor.textContent = s.sponsor || ''; 
            middle.appendChild(title); 
            middle.appendChild(sponsor);
            
            const right = document.createElement('div'); 
            right.className = 'card-right';
            const bookmarkIcon = document.createElement('button'); 
            bookmarkIcon.className = 'bookmark-icon bookmarked';
            const icon = document.createElement('i'); 
            icon.className = 'bi bi-bookmark-fill';
            bookmarkIcon.appendChild(icon);
            bookmarkIcon.onclick = function(){ toggleBookmark(s.id, bookmarkIcon); };
            const btn = document.createElement('button'); 
            btn.className = 'btn btn-primary'; 
            btn.textContent = 'VIEW'; 
            btn.onclick = function(){ showScholarshipDetails(s.id); };
            right.appendChild(bookmarkIcon); 
            right.appendChild(btn);
            
            card.appendChild(left); 
            card.appendChild(middle); 
            card.appendChild(right); 
            wrap.appendChild(card);
          });
        }
        
        fetch('{{ url('api/bookmarks') }}', { credentials: 'same-origin' })
          .then(r => r.ok ? r.json() : Promise.reject())
          .then(items => {
            if (!Array.isArray(items) || items.length === 0) {
              const info = document.createElement('div');
              info.className = 'alert alert-info text-center py-5';
              info.innerHTML = "<h4>No bookmarks yet</h4><p class='mb-0'>Start bookmarking scholarships to see them here!</p>";
              wrap.appendChild(info);
              return;
            }
            allItems = items;
            render(applyFilters());
            
            // Setup sort button
            const sortBtn = document.querySelector('.sort-btn');
            if (sortBtn) {
              sortBtn.addEventListener('click', ()=>{ 
                sortAsc=!sortAsc; 
                // Toggle sort icon
                const icon = sortBtn.querySelector('.sort-icon');
                if (icon) {
                  icon.className = sortAsc ? 'bi bi-sort-alpha-down sort-icon' : 'bi bi-sort-alpha-up sort-icon';
                }
                sortBtn.classList.toggle('sorting-desc', !sortAsc);
                render(applyFilters()); 
              });
            }
            
            // Setup filter dropdown
            const filterDropdownMenu = document.getElementById('filterDropdownMenu');
            if (filterDropdownMenu) {
              const cats = [...new Set(allItems.map(it => it.category || '').filter(Boolean))];
              cats.forEach(c => {
                const li = document.createElement('li');
                const a = document.createElement('a');
                a.className = 'dropdown-item';
                a.href = '#';
                a.innerHTML = '<i class="bi bi-check2 me-2"></i>' + c;
                a.setAttribute('data-category', c);
                a.addEventListener('click', function(e) {
                  e.preventDefault();
                  currentCategory = c;
                  // Update active state
                  filterDropdownMenu.querySelectorAll('.dropdown-item').forEach(item => {
                    item.classList.remove('active');
                  });
                  a.classList.add('active');
                  // Update button text
                  const filterBtn = document.querySelector('.btn-filter-sort.dropdown-toggle');
                  if (filterBtn) {
                    const span = filterBtn.querySelector('.filter-text');
                    if (span) {
                      span.textContent = c;
                    }
                  }
                  // Close dropdown
                  const dropdown = bootstrap.Dropdown.getInstance(document.getElementById('filterDropdown'));
                  if (dropdown) dropdown.hide();
                  render(applyFilters());
                });
                li.appendChild(a);
                filterDropdownMenu.appendChild(li);
              });
              // Add click handler for "All Categories"
              const allCategoriesItem = filterDropdownMenu.querySelector('[data-category=""]');
              if (allCategoriesItem) {
                allCategoriesItem.addEventListener('click', function(e) {
                  e.preventDefault();
                  currentCategory = '';
                  // Update active state
                  filterDropdownMenu.querySelectorAll('.dropdown-item').forEach(item => {
                    item.classList.remove('active');
                  });
                  allCategoriesItem.classList.add('active');
                  // Update button text back to "Filter"
                  const filterBtn = document.querySelector('.btn-filter-sort.dropdown-toggle');
                  if (filterBtn) {
                    const span = filterBtn.querySelector('.filter-text');
                    if (span) {
                      span.textContent = 'Filter';
                    }
                  }
                  // Close dropdown
                  const dropdown = bootstrap.Dropdown.getInstance(document.getElementById('filterDropdown'));
                  if (dropdown) dropdown.hide();
                  render(applyFilters());
                });
              }
            }
          }).catch(err => { console.error('Bookmark load failed:', err); wrap.innerHTML = `<div class="alert alert-danger text-center">Failed to load bookmarks.</div>`; });
      })();
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
          new bootstrap.Modal(document.getElementById('scholarshipModal')).show();
        }).catch(()=> alert('Failed to load scholarship details'));
    }
  </script>
</body>
</html>
