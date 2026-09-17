@include('fields.parts.header')

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
          <h4 class="mb-0"><i class="bi bi-shield-lock"></i> Set Your Password</h4>
        </div>
        <div class="card-body p-4">
          <p class="text-muted mb-4">
            Welcome to TGM Suite! Please enter your account email and choose a secure password (minimum 6 characters) to set up your credentials.
          </p>

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

          <form action="/reset-password" method="POST">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="mb-3">
              <label for="email" class="form-label font-weight-bold">Email Address</label>
              <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $email) }}" required autofocus placeholder="name@example.com">
            </div>

            <div class="mb-3">
              <label for="password" class="form-label font-weight-bold">New Password</label>
              <input type="password" class="form-control" id="password" name="password" required minlength="6" placeholder="At least 6 characters">
            </div>

            <div class="mb-3">
              <label for="password_confirmation" class="form-label font-weight-bold">Confirm New Password</label>
              <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required minlength="6" placeholder="Repeat new password">
            </div>

            <div class="d-grid gap-2">
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-check2-circle"></i> Set Password & Log In
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

@include('fields.parts.footer')
