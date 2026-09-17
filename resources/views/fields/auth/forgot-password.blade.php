@include('fields.parts.header')

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
          <h4 class="mb-0"><i class="bi bi-key"></i> Forgot Password</h4>
        </div>
        <div class="card-body p-4">
          <p class="text-muted mb-4">
            Forgot your password? No problem. Enter your email address below, and we will email you a password reset link that will allow you to set a new one.
          </p>

          @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              <i class="bi bi-check-circle"></i> {{ session('status') }}
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

          <form action="/forgot-password" method="POST">
            @csrf
            <div class="mb-3">
              <label for="email" class="form-label font-weight-bold">Email Address</label>
              <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="name@example.com">
            </div>

            <div class="d-grid gap-2">
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-envelope"></i> Email Password Reset Link
              </button>
            </div>

            <div class="text-center mt-3">
              <a href="/login" class="text-decoration-none">
                <i class="bi bi-arrow-left"></i> Back to Login
              </a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

@include('fields.parts.footer')
