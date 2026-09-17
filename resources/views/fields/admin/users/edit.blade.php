@include('fields.parts.header')

<div class="container my-4">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2 class="mb-1"><i class="bi bi-pencil-square"></i> Edit User</h2>
          <p class="text-muted mb-0">Update account details, role, and field permissions for {{ $user->name }}.</p>
        </div>
        <a href="/admin/users" class="btn btn-outline-secondary">
          <i class="bi bi-arrow-left"></i> Back to Users
        </a>
      </div>

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

      <div class="card shadow-sm">
        <div class="card-body p-4">
          <form action="/admin/users/{{ $user->id }}" method="POST">
            @csrf

            <div class="mb-3">
              <label for="name" class="form-label font-weight-bold">Full Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required>
            </div>

            <div class="mb-3">
              <label for="email" class="form-label font-weight-bold">Email Address <span class="text-danger">*</span></label>
              <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}" required>
            </div>

            <div class="mb-4 form-check form-switch">
              <input class="form-check-input" type="checkbox" id="is_admin" name="is_admin" value="1" 
                @if(old('is_admin', $user->is_admin)) checked @endif 
                @if(Auth::id() === $user->id) disabled @endif>
              <label class="form-check-label font-weight-bold" for="is_admin">
                Grant Superadmin Privileges
              </label>
              @if(Auth::id() === $user->id)
                <div class="form-text text-muted">You cannot revoke your own superadmin privileges.</div>
              @else
                <div class="form-text">Superadmins have unrestricted access to all fields, reports, and administrative management tools.</div>
              @endif
            </div>

            <hr class="my-4">

            <h5 class="mb-3"><i class="bi bi-geo-alt"></i> Field Assignments</h5>
            <p class="text-muted small mb-3">Manage which athletic fields this user is permitted to assess and manage.</p>

            <div class="mb-3">
              <label for="permission_level" class="form-label font-weight-bold">Permission Level</label>
              <select name="permission_level" id="permission_level" class="form-select mb-3">
                <option value="standard" @if(old('permission_level', $userPermission) === 'standard') selected @endif>Standard (Submit Reports & View)</option>
                <option value="admin" @if(old('permission_level', $userPermission) === 'admin') selected @endif>Field Admin (Full Field Control)</option>
              </select>
            </div>

            <div class="card border mb-4">
              <div class="card-header bg-light py-2">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="selectAllFields" onclick="document.querySelectorAll('.field-checkbox').forEach(cb => cb.checked = this.checked)">
                  <label class="form-check-label font-weight-bold" for="selectAllFields">Select All Fields</label>
                </div>
              </div>
              <div class="card-body p-3" style="max-height: 250px; overflow-y: auto;">
                @forelse ($fields as $field)
                  <div class="form-check mb-2">
                    <input class="form-check-input field-checkbox" type="checkbox" name="fields[]" value="{{ $field->id }}" id="field_{{ $field->id }}" 
                      @if(is_array(old('fields')) ? in_array($field->id, old('fields')) : in_array($field->id, $userFieldIds)) checked @endif>
                    <label class="form-check-label" for="field_{{ $field->id }}">
                      <strong>{{ $field->name }}</strong>
                      @if($field->city || $field->state)
                        <span class="text-muted small">({{ $field->city ?? '' }}{{ ($field->city && $field->state) ? ', ' : '' }}{{ $field->state ?? '' }})</span>
                      @endif
                    </label>
                  </div>
                @empty
                  <p class="text-muted mb-0">No fields created yet.</p>
                @endforelse
              </div>
            </div>

            <div class="d-flex justify-content-between">
              <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('sendResetForm').submit();">
                <i class="bi bi-key"></i> Send Password Reset Link
              </button>
              <div class="d-flex gap-2">
                <a href="/admin/users" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                  <i class="bi bi-check-lg"></i> Save Changes
                </button>
              </div>
            </div>
          </form>

          <form id="sendResetForm" action="/admin/users/{{ $user->id }}/send-reset" method="POST" style="display: none;">
            @csrf
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

@include('fields.parts.footer')
