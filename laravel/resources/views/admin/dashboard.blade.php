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
    <aside class="sidebar d-flex flex-column align-items-center p-3">
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
        <a href="#" class="nav-link d-flex align-items-center gap-2 text-white">
          <img src="{{ asset('assets/Images/material-add_symbols_school.png') }}">Add Scholarships
        </a>
        <a href="{{ route('logout') }}" class="nav-link d-flex align-items-center gap-2 text-white">
          <i class="bi bi-box-arrow-right"></i> Logout
        </a>
      </nav>
    </aside>

    <main class="main-content flex-grow-1 p-4">
      <div class="controls d-flex justify-content-between align-items-center mb-4">
        <form class="d-flex gap-2 w-100" method="GET" action="{{ route('admin') }}">
          <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search by title, sponsor or category">
          <select name="category" class="form-select" style="max-width: 200px;">
            <option value="">All Categories</option>
            <option value="Company" @selected(request('category')==='Company')>Company</option>
            <option value="School" @selected(request('category')==='School')>School</option>
            <option value="Organization" @selected(request('category')==='Organization')>Organization</option>
          </select>
          <select id="sort" name="sort" class="form-select" style="max-width: 200px;">
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
      fetch(apiBase + '/scholarships', { credentials: 'same-origin' })
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

    function editScholarship(scholarshipId) {
      fetch(`${apiBase}/admin/edit-scholarship?id=${scholarshipId}`, { credentials: 'same-origin' })
        .then(r => r.json())
        .then(s => {
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
        .catch(() => alert('Failed to load scholarship data'));
    }

    document.getElementById('editScholarshipForm').addEventListener('submit', function(e){
      e.preventDefault();
      const form = e.target;
      const id = document.getElementById('editScholarshipId').value;
      document.getElementById('editDescription').value = document.getElementById('editDescriptionEditor').innerHTML;
      const fd = new FormData(form);
      fd.append('_token', '{{ csrf_token() }}');
      fetch(`${apiBase}/admin/edit-scholarship`, { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(r => r.json())
        .then(data => {
          if (data.success) { alert('Scholarship updated successfully'); bootstrap.Modal.getInstance(document.getElementById('editScholarshipModal')).hide(); loadScholarships(); }
          else { alert('Failed to update scholarship: ' + (data.error || (data.errors && data.errors.join(', ')) || 'Unknown error')); }
        })
        .catch(() => alert('Failed to update scholarship'));
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

    // init
    loadScholarships();
  </script>
</body>
</html>
