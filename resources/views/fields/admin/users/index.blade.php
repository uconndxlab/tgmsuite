@include('fields.parts.header')

<div class="container my-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h2 class="mb-1"><i class="bi bi-people"></i> User Management</h2>
      <p class="text-muted mb-0">Manage system users, assign fields, and issue account invitations.</p>
    </div>
    <div>
      <a href="/admin/submissions" class="btn btn-outline-secondary me-2">
        <i class="bi bi-file-earmark-text"></i> View Submissions
      </a>
      <a href="/admin/users/create" class="btn btn-primary">
        <i class="bi bi-person-plus"></i> Add New User
      </a>
    </div>
  </div>

  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="bi bi-check-circle"></i> {{ session('success') }}
      @if (session('activation_link'))
        <div class="mt-2 p-2 bg-light border rounded text-break">
          <strong>Activation / Password Set Link:</strong><br/>
          <code>{{ session('activation_link') }}</code>
        </div>
      @endif
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <!-- Search & Filter Card -->
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <form method="GET" action="/admin/users" class="row g-3">
        <div class="col-md-5">
          <input type="text" class="form-control" name="search" placeholder="Search by name or email..." value="{{ request('search') }}">
        </div>
        <div class="col-md-4">
          <select name="role" class="form-select">
            <option value="">All Roles</option>
            <option value="admin" @if(request('role') === 'admin') selected @endif>Superadmins</option>
            <option value="user" @if(request('role') === 'user') selected @endif>Regular Users</option>
          </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
          <button type="submit" class="btn btn-secondary flex-grow-1">
            <i class="bi bi-search"></i> Filter
          </button>
          @if(request('search') || request('role'))
            <a href="/admin/users" class="btn btn-outline-secondary">Reset</a>
          @endif
        </div>
      </form>
    </div>
  </div>

  <!-- Users Table -->
  <div class="card shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Role</th>
              <th>Assigned Fields</th>
              <th>Joined Date</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($users as $user)
              <tr>
                <td class="font-weight-bold">
                  {{ $user->name }}
                  @if (Auth::id() === $user->id)
                    <span class="badge bg-secondary ms-1">You</span>
                  @endif
                </td>
                <td>{{ $user->email }}</td>
                <td>
                  @if ($user->is_admin)
                    <span class="badge bg-danger"><i class="bi bi-shield-check"></i> Superadmin</span>
                  @else
                    <span class="badge bg-info text-dark">Field Manager</span>
                  @endif
                </td>
                <td>
                  <span class="badge bg-light text-dark border">
                    {{ $user->fields_count }} {{ Str::plural('field', $user->fields_count) }}
                  </span>
                </td>
                <td>{{ $user->created_at ? $user->created_at->format('M j, Y') : 'N/A' }}</td>
                <td class="text-end">
                  <div class="btn-group btn-group-sm" role="group">
                    <a href="/admin/users/{{ $user->id }}/edit" class="btn btn-outline-primary" title="Edit User">
                      <i class="bi bi-pencil"></i> Edit
                    </a>
                    <form action="/admin/users/{{ $user->id }}/send-reset" method="POST" class="d-inline" onsubmit="return confirm('Send a password setup link to {{ $user->email }}?');">
                      @csrf
                      <button type="submit" class="btn btn-outline-secondary" title="Send Password Setup Link">
                        <i class="bi bi-key"></i> Send Setup Link
                      </button>
                    </form>
                    @if (Auth::id() !== $user->id)
                      <form action="/admin/users/{{ $user->id }}/delete" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete user {{ $user->name }}?');">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger" title="Delete User">
                          <i class="bi bi-trash"></i>
                        </button>
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center py-4 text-muted">
                  No users found matching your criteria.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    @if ($users->hasPages())
      <div class="card-footer bg-white py-3">
        {{ $users->links() }}
      </div>
    @endif
  </div>
</div>

@include('fields.parts.footer')
