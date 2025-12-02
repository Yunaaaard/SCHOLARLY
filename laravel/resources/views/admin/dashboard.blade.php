@php($title = 'Admin Dashboard')
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Scholarly Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <style>
    body { font-family: 'Poppins', sans-serif !important; }
    .description-content:empty:before { content: attr(data-placeholder); color: #6c757d; font-style: italic; }
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
          <img src="{{ asset('assets/Images/material-symbols_school.png') }}"> Scholarships
        </a>
        <a href="{{ route('admin.add-scholarship') }}" class="nav-link d-flex align-items-center gap-2 text-white">
          <img src="{{ asset('assets/Images/material-add_symbols_school.png') }}"> Add Scholarships
        </a>
        <a href="{{ route('admin.students') }}" class="nav-link d-flex align-items-center gap-2 text-white">
          <img src="{{ asset('assets/Images/ph_student-bold.png') }}"> Student Management
        </a>
        <a href="{{ route('logout') }}" class="nav-link d-flex align-items-center gap-2 text-white">
          <i class="bi bi-box-arrow-right"></i> Logout
        </a>
      </nav>
    </aside>

    <main class="main-content flex-grow-1 p-4">
      <div class="controls d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center mb-4 gap-3">
        <form class="d-flex flex-column flex-md-row gap-2 w-100" method="GET" action="{{ route('admin') }}">
          <input type="text" name="q" value="{{ request('q') }}" class="form-control flex-fill" placeholder="Search by title, sponsor or category">
          <select name="category" class="form-select" style="max-width: 100%; min-width: 150px;">
            <option value="">All Categories</option>
            <option value="Company" @selected(request('category')==='Company')>Company</option>
            <option value="School" @selected(request('category')==='School')>School</option>
            <option value="Organization" @selected(request('category')==='Organization')>Organization</option>
          </select>
          <select id="sort" name="sort" class="form-select" style="max-width: 100%; min-width: 150px;">
            <option value="">Sort: Default</option>
            <option value="title_asc" @selected(request('sort')==='title_asc')>Title A-Z</option>
            <option value="title_desc" @selected(request('sort')==='title_desc')>Title Z-A</option>
            <option value="start_new" @selected(request('sort')==='start_new')>Newest Start Date</option>
            <option value="end_new" @selected(request('sort')==='end_new')>Newest End Date</option>
            <option value="apps_high" @selected(request('sort')==='apps_high')>Most Applications</option>
          </select>
          <button class="btn btn-primary" type="submit">Apply</button>
          <a class="btn btn-outline-secondary" href="{{ route('admin') }}">Reset</a>
        </form>
      </div>

      <div id="scholarshipList" class="d-flex flex-column gap-3"></div>
    </main>
  </div>

  <!-- Applications Modal -->
  <div id="applicationsModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Scholarship Applications</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="applicationsList">
            <div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Scholarship Modal -->
  <div id="editScholarshipModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Scholarship</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="editScholarshipForm" enctype="multipart/form-data">
          <div class="modal-body">
            <input type="hidden" id="editScholarshipId" name="id">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="editTitle" class="form-label">Title</label>
                <input type="text" class="form-control" id="editTitle" name="title" required>
              </div>
              <div class="col-md-6 mb-3">
                <label for="editSponsor" class="form-label">Sponsor</label>
                <input type="text" class="form-control" id="editSponsor" name="sponsor">
              </div>
            </div>
            <div class="mb-3">
              <label for="editDescription" class="form-label">Description</label>
              <div class="rich-text-editor border rounded p-2" style="min-height: 200px; background: white;">
                <div class="toolbar mb-2 border-bottom pb-2">
                  <button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="formatEditText('bold')" title="Bold">
                    <i class="bi bi-type-bold"></i>
                  </button>
                  <button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="formatEditText('italic')" title="Italic">
                    <i class="bi bi-type-italic"></i>
                  </button>
                  <button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="formatEditText('underline')" title="Underline">
                    <i class="bi bi-type-underline"></i>
                  </button>
                  <button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="insertEditLineBreak()" title="Line Break">
                    <i class="bi bi-text-paragraph"></i>
                  </button>
                  <button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="insertEditBulletList()" title="Bullet List">
                    <i class="bi bi-list-ul"></i>
                  </button>
                  <button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="insertEditNumberedList()" title="Numbered List">
                    <i class="bi bi-list-ol"></i>
                  </button>
                </div>
                <div contenteditable="true" id="editDescriptionEditor" class="description-content" style="min-height: 150px; outline: none;" data-placeholder="Provide a detailed description with formatting..."></div>
              </div>
              <textarea id="editDescription" name="description" style="display: none;" required></textarea>
            </div>
            <div class="row">
              <div class="col-md-4 mb-3">
                <label for="editCategory" class="form-label">Category</label>
                <select class="form-select" id="editCategory" name="category" required>
                  <option value="">Select Category</option>
                  <option value="Company">Company</option>
                  <option value="School">School</option>
                  <option value="Organization">Organization</option>
                  <option value="Government">Government</option>
                  <option value="Foundation">Foundation</option>
                  <option value="Non-Profit">Non-Profit</option>
                  <option value="Individual">Individual</option>
                  <option value="Other">Other</option>
                </select>
              </div>
              <div class="col-md-4 mb-3">
                <label for="editStartDate" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="editStartDate" name="start_date">
              </div>
              <div class="col-md-4 mb-3">
                <label for="editEndDate" class="form-label">End Date</label>
                <input type="date" class="form-control" id="editEndDate" name="end_date">
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="editEmail" class="form-label">Email</label>
                <input type="email" class="form-control" id="editEmail" name="email">
              </div>
              <div class="col-md-6 mb-3">
                <label for="editPhone" class="form-label">Phone</label>
                <input type="text" class="form-control" id="editPhone" name="phone">
              </div>
            </div>
            <div class="mb-3">
              <label for="editImage" class="form-label">Image (optional)</label>
              <input type="file" class="form-control" id="editImage" name="image" accept="image/*">
              <div class="form-text">Leave empty to keep current image</div>
            </div>
          </div>
          <div class="modal-footer d-flex justify-content-between">
            <button type="button" id="deleteScholarshipBtn" class="btn btn-danger">Delete Scholarship</button>
            <button type="submit" class="btn btn-primary">Update Scholarship</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const listEl = document.getElementById('scholarshipList');
    const apiBase = '{{ url('api') }}';

    function loadScholarships() {
      // Get filter parameters from URL
      const urlParams = new URLSearchParams(window.location.search);
      const q = urlParams.get('q') || '';
      const category = urlParams.get('category') || '';
      const sort = urlParams.get('sort') || '';
      
      // Build query string
      const params = new URLSearchParams();
      if (q) params.append('q', q);
      if (category) params.append('category', category);
      if (sort) params.append('sort', sort);
      
      const queryString = params.toString();
      const url = apiBase + '/scholarships' + (queryString ? '?' + queryString : '');
      
      fetch(url, { credentials: 'same-origin' })
        .then(r => r.json())
        .then(items => {
          listEl.innerHTML = '';
          if (!Array.isArray(items) || !items.length) {
            listEl.innerHTML = '<div class="alert alert-info mb-0">No scholarships found.</div>';
            return;
          }
          items.forEach(s => {
            const card = document.createElement('div');
            card.className = 'card p-3 d-flex flex-row justify-content-between align-items-center shadow-sm';
            const left = document.createElement('div'); left.className = 'd-flex align-items-center gap-3';
            const img = document.createElement('img'); img.width = 65; img.src = s.image_path ? ('{{ url('../../') }}/' + s.image_path) : '{{ asset('assets/Images/image.png') }}'; left.appendChild(img);
            const meta = document.createElement('div');
            meta.innerHTML = `<h3 class="h6 fw-bold mb-1">${s.title||''}</h3><p class="small mb-0">${s.sponsor||''} • ${s.category||''}</p>`;
            left.appendChild(meta);
            const right = document.createElement('div'); right.className = 'd-flex gap-2 align-items-center';
            const viewBtn = document.createElement('button'); viewBtn.className = 'btn btn-sm btn-outline-primary'; viewBtn.textContent = 'View Bookmarks'; viewBtn.onclick = () => viewApplications(s.id);
            const editBtn = document.createElement('button'); editBtn.className = 'btn btn-sm btn-outline-success'; editBtn.textContent = 'Edit'; editBtn.onclick = () => editScholarship(s.id);
            right.appendChild(viewBtn); right.appendChild(editBtn);
            card.appendChild(left); card.appendChild(right);
            listEl.appendChild(card);
          });
        });
    }

    function viewApplications(scholarshipId) {
      const modal = new bootstrap.Modal(document.getElementById('applicationsModal'));
      modal.show();
      fetch(`${apiBase}/applications?scholarship_id=${scholarshipId}`, { credentials: 'same-origin' })
        .then(r => r.json())
        .then(applications => {
          const container = document.getElementById('applicationsList');
          if (!applications || !applications.length) { container.innerHTML = '<div class="alert alert-info">No bookmarks found for this scholarship.</div>'; return; }
          let html = '<div class="table-responsive"><table class="table table-striped"><thead><tr><th>Name</th><th>Email</th><th>Contact</th><th>Bookmarked Date</th><th>Status</th></tr></thead><tbody>';
          applications.forEach(app => {
            html += `<tr><td>${app.username||'N/A'}</td><td>${app.email||'N/A'}</td><td>${app.contact||'N/A'}</td><td>${new Date(app.applied_at).toLocaleDateString()}</td><td><span class="badge bg-info">${app.status}</span></td></tr>`;
          });
          html += '</tbody></table></div>';
          container.innerHTML = html;
        })
        .catch(() => { document.getElementById('applicationsList').innerHTML = '<div class="alert alert-danger">Failed to load applications</div>'; });
    }

    // Rich text editor functions for edit modal
    function formatEditText(command) {
      document.execCommand(command, false, null);
      document.getElementById('editDescriptionEditor').focus();
    }

    function insertEditLineBreak() {
      document.execCommand('insertHTML', false, '<br><br>');
      document.getElementById('editDescriptionEditor').focus();
    }

    function insertEditBulletList() {
      document.execCommand('insertUnorderedList', false, null);
      document.getElementById('editDescriptionEditor').focus();
    }

    function insertEditNumberedList() {
      document.execCommand('insertOrderedList', false, null);
      document.getElementById('editDescriptionEditor').focus();
    }

    function editScholarship(scholarshipId) {
      fetch(`${apiBase}/admin/edit-scholarship?id=${scholarshipId}`, { credentials: 'same-origin' })
        .then(r => r.json())
        .then(s => {
          if (s.error) {
            alert('Failed to load scholarship: ' + s.error);
            return;
          }
          document.getElementById('editScholarshipId').value = s.id;
          document.getElementById('editTitle').value = s.title || '';
          document.getElementById('editSponsor').value = s.sponsor || '';
          document.getElementById('editDescriptionEditor').innerHTML = s.description || '';
          document.getElementById('editDescription').value = s.description || '';
          document.getElementById('editCategory').value = s.category || '';
          document.getElementById('editStartDate').value = s.start_date || '';
          document.getElementById('editEndDate').value = s.end_date || '';
          document.getElementById('editEmail').value = s.email || '';
          document.getElementById('editPhone').value = s.phone || '';
          new bootstrap.Modal(document.getElementById('editScholarshipModal')).show();
        })
        .catch(err => {
          console.error('Error:', err);
          alert('Failed to load scholarship data');
        });
    }

    document.getElementById('editScholarshipForm').addEventListener('submit', function(e){
      e.preventDefault();
      const form = e.target;
      const id = document.getElementById('editScholarshipId').value;
      
      // Sync rich text editor content
      const editorContent = document.getElementById('editDescriptionEditor').innerHTML;
      document.getElementById('editDescription').value = editorContent;
      
      const fd = new FormData(form);
      fd.append('_token', '{{ csrf_token() }}');
      fd.append('id', id);
      
      // Ensure description is set
      if (!fd.get('description')) {
        fd.set('description', editorContent);
      }
      
      fetch(`${apiBase}/admin/edit-scholarship`, { 
        method: 'POST', 
        body: fd, 
        credentials: 'same-origin',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
      })
        .then(r => r.json())
        .then(data => {
          if (data.success) { 
            alert('Scholarship updated successfully'); 
            bootstrap.Modal.getInstance(document.getElementById('editScholarshipModal')).hide(); 
            loadScholarships(); 
          } else { 
            const errorMsg = data.error || (data.errors && Array.isArray(data.errors) ? data.errors.join(', ') : JSON.stringify(data.errors)) || 'Unknown error';
            alert('Failed to update scholarship: ' + errorMsg); 
          }
        })
        .catch(err => {
          console.error('Error:', err);
          alert('Failed to update scholarship: ' + err.message);
        });
    });

    document.getElementById('deleteScholarshipBtn').addEventListener('click', function(){
      const id = document.getElementById('editScholarshipId').value;
      if (!id) return alert('No scholarship selected');
      if (!confirm('Delete this scholarship?')) return;
      const fd = new FormData(); fd.append('action','delete'); fd.append('id', id); fd.append('_token', '{{ csrf_token() }}');
      fetch(`${apiBase}/admin/edit-scholarship`, { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(r => r.json())
        .then(data => { if (data.success) { alert('Deleted'); bootstrap.Modal.getInstance(document.getElementById('editScholarshipModal')).hide(); loadScholarships(); } else { alert('Failed: ' + (data.error||'Unknown')); } })
        .catch(() => alert('Failed to delete scholarship'));
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
        
        // Close menu when clicking on main content area
        const mainContent = document.querySelector('.main-content');
        if (mainContent) {
          mainContent.addEventListener('click', function(e) {
            if (window.innerWidth <= 768 && sidebar && sidebar.classList.contains('active')) {
              // Only close if clicking directly on main content, not on a child element that should be clickable
              if (e.target === mainContent || e.target.closest('.main-content') === mainContent) {
                closeMobileMenu();
              }
            }
          });
        }
      }
    })();

    // Load scholarships on page load
    loadScholarships();
  </script>
</body>
</html>
