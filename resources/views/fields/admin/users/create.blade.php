@include('fields.parts.header')

<div class="container my-4">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2 class="mb-1"><i class="bi bi-person-plus"></i> Add New User</h2>
          <p class="text-muted mb-0">Create an account and send an invitation link to set their initial password.</p>
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
          <div class="alert alert-info d-flex align-items-center mb-4">
            <i class="bi bi-info-circle-fill fs-4 me-3"></i>
            <div>
              <strong>First-Time Password Setup:</strong> You do not need to set a password for this user. Upon submission, TGM Suite will generate an account activation link and email it to the user so they can set their own secure password.
            </div>
          </div>

          <form action="/admin/users" method="POST">
            @csrf

            <div class="mb-3">
              <label for="name" class="form-label font-weight-bold">Full Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required placeholder="e.g. Jane Doe">
            </div>

            <div class="mb-3">
              <label for="email" class="form-label font-weight-bold">Email Address <span class="text-danger">*</span></label>
              <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required placeholder="e.g. jdoe@uconn.edu">
            </div>

            <div class="mb-4 form-check form-switch">
              <input class="form-check-input" type="checkbox" id="is_admin" name="is_admin" value="1" @if(old('is_admin')) checked @endif>
              <label class="form-check-label font-weight-bold" for="is_admin">
                Grant Superadmin Privileges
              </label>
              <div class="form-text">Superadmins have unrestricted access to all fields, reports, and administrative management tools.</div>
            </div>

            <hr class="my-4">

            <h5 class="mb-3"><i class="bi bi-geo-alt"></i> Field Assignments</h5>
            <p class="text-muted small mb-3">Assign athletic fields this user will be authorized to assess and manage.</p>

            <div class="mb-3">
              <label for="permission_level" class="form-label font-weight-bold">Default Permission Level</label>
              <select name="permission_level" id="permission_level" class="form-select mb-3">
                <option value="standard" @if(old('permission_level') === 'standard') selected @endif>Standard (Submit Reports & View)</option>
                <option value="admin" @if(old('permission_level') === 'admin') selected @endif>Field Admin (Full Field Control)</option>
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
                    <input class="form-check-input field-checkbox" type="checkbox" name="fields[]" value="{{ $field->id }}" id="field_{{ $field->id }}" @if(is_array(old('fields')) && in_array($field->id, old('fields'))) checked @endif>
                    <label class="form-check-label" for="field_{{ $field->id }}">
                      <strong>{{ $field->name }}</strong>
                      @if($field->city || $field->state)
                        <span class="text-muted small">({{ $field->city ?? '' }}{{ ($field->city && $field->state) ? ', ' : '' }}{{ $field->state ?? '' }})</span>
                      @endif
                    </label>
                  </div>
                @empty
                  <p class="text-muted mb-0">No fields have been created yet.</p>
                @endforelse
              </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
              <a href="/admin/users" class="btn btn-outline-secondary">Cancel</a>
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-send"></i> Create User & Send Invitation
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

@include('fields.parts.footer')
