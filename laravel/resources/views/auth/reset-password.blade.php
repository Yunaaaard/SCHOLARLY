@php($title = 'Reset Password')
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Scholarly - Reset Password</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * { font-family: 'Poppins', sans-serif !important; box-sizing: border-box; }
    html, body { height: 100%; margin: 0; padding: 0; overflow: hidden; }
    .container-fluid, .row, .col-md-8, .col-md-4 { margin: 0 !important; padding: 0 !important; }
    .container-fluid { width: 100vw; height: 100vh; }
    .left { background-color: #B5A6E8; display: flex; align-items: center; justify-content: center; height: 100vh; position: relative; padding: 0; margin: 0; }
    .left .logo { max-width: 70%; }
    .right { background-color: #F5F6FB; display: flex; align-items: center; justify-content: center; flex-direction: column; padding: 2rem; height: 100vh; }
    .reset-form { width: 100%; max-width: 360px; }
    .input-group { background: #fff; border-radius: 8px; box-shadow: 0px 2px 4px rgba(0,0,0,0.05); overflow: hidden; }
    .input-group-text { background: transparent; border: none; padding-left: 15px; }
    .form-control { border: none; box-shadow: none !important; padding: 0.9rem; }
    .btn { background-color: #8E79E0; color: white; font-weight: 600; border-radius: 25px; padding: 0.7rem; margin-top: 10px; }
    .btn:hover { background-color: #7b68ce; }
    .back-link { text-align: center; margin-top: 15px; }
    .back-link a { font-size: 0.9rem; color: #7b68ce; text-decoration: none; }
    .back-link a:hover { text-decoration: underline; }
    .back-arrow { position: absolute; top: 20px; left: 20px; color: #fff; text-decoration: none; font-weight: 500; font-size: 1rem; z-index: 10; }
    .back-arrow:hover { text-decoration: underline; }
    @media (max-width: 768px) { 
      .left, .right { width: 100%; height: auto; min-height: 50vh; } 
      .left { order: 1; padding: 2rem 1rem; } 
      .right { order: 2; padding: 2rem 1rem; height: auto; } 
      .container-fluid { height: auto; overflow-y: auto; }
      html, body { overflow: auto; height: auto; }
      .left .logo { max-width: 50%; }
      .back-arrow { top: 15px; left: 15px; font-size: 0.9rem; }
    }
    @media (max-width: 480px) {
      .reset-form { max-width: 100%; }
      .right { padding: 1.5rem 1rem; }
      .left { padding: 1.5rem 1rem; }
      .btn { padding: 0.6rem; font-size: 0.9rem; }
    }
  </style>
</head>
<body>
  <div class="container-fluid">
    <div class="row g-0">
      <div class="col-md-8 left">
        <a href="{{ route('index') }}" class="back-arrow">&#8592; Back</a>
        <img src="{{ asset('assets/images/Group 44.png') }}" alt="Scholarly Logo" class="logo img-fluid">
      </div>
      <div class="col-md-4 right">
        <form class="reset-form" method="POST" action="{{ route('reset-password.post') }}">
          @csrf
          <input type="hidden" name="token" value="{{ $token }}">
          <h4 class="mb-3 text-center" style="color: #333; font-weight: 600;">Reset Password</h4>
          <div class="input-group mb-3">
            <span class="input-group-text">
              <img src="{{ asset('assets/images/weui_lock-outlined.png') }}" alt="Password" width="20">
            </span>
            <input name="password" type="password" class="form-control" placeholder="New Password" required minlength="6">
          </div>
          <div class="input-group mb-3">
            <span class="input-group-text">
              <img src="{{ asset('assets/images/weui_lock-outlined.png') }}" alt="Password" width="20">
            </span>
            <input name="password_confirmation" type="password" class="form-control" placeholder="Confirm New Password" required minlength="6">
          </div>
          @if ($errors->any())
            <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
          @endif
          @if (session('error'))
            <div class="alert alert-danger py-2">{{ session('error') }}</div>
          @endif
          <button type="submit" class="btn w-100">RESET PASSWORD</button>
          <div class="back-link">
            <a href="{{ route('login') }}">Back to Login</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>
</html>

