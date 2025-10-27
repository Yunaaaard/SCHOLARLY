@php($title = 'Register')
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Scholarly Sign Up</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    * { font-family: 'Poppins', sans-serif !important; box-sizing: border-box; }
    html, body { height: 100%; margin: 0; padding: 0; overflow: hidden; }
    .container-fluid, .row, .col-md-7, .col-md-5 { margin: 0 !important; padding: 0 !important; }
    .container-fluid { width: 100vw; height: 100vh; }
    .toggle-eye i { cursor: pointer; transition: color 0.2s ease; }
    .toggle-eye i:hover { color: #6a56c8; }
    .left { background-color: #B5A6E8; display: flex; align-items: center; justify-content: center; height: 100vh; position: relative; }
    .left .logo { max-width: 70%; }
    .back-arrow { position: absolute; top: 20px; left: 20px; color: #fff; text-decoration: none; font-weight: 500; font-size: 1rem; }
    .back-arrow:hover { text-decoration: underline; }
    .right { background-color: #F5F6FB; display: flex; align-items: center; justify-content: center; flex-direction: column; padding: 2rem; height: 100vh; }
    .signup-form { width: 100%; max-width: 360px; }
    .signup-form h3 { text-align: center; font-weight: 600; margin-bottom: 10px; }
    .signup-form p.text-muted { text-align: center; margin-bottom: 25px; font-size: 0.95rem; }
    .input-group { background: #fff; border-radius: 8px; box-shadow: 0px 2px 4px rgba(0,0,0,0.05); overflow: hidden; }
    .input-group-text { background: transparent; border: none; padding-left: 15px; }
    .form-control { border: none; box-shadow: none !important; padding: 0.9rem; font-size: 0.95rem; }
    .toggle-eye { position: absolute; right: 16px; top: 50%; transform: translateY(-50%); color: #8E79E0; cursor: pointer; }
    .btn { background-color: #8E79E0; color: white; font-weight: 600; border-radius: 25px; padding: 0.7rem; margin-top: 10px; }
    .btn:hover { background-color: #7b68ce; }
    .form-check-label { font-size: 0.9rem; color: #555; }
    .form-check-label a { color: #7b68ce; text-decoration: none; font-weight: 500; }
    .form-check-label a:hover { text-decoration: underline; }
    .divider { display: flex; align-items: center; text-align: center; margin: 20px 0; }
    .divider::before, .divider::after { content: ""; flex: 1; border-bottom: 1px solid #ccc; }
    .divider:not(:empty)::before { margin-right: .75em; }
    .divider:not(:empty)::after { margin-left: .75em; }
    .social-login a { margin: 0 10px; }
    .social-login img { width: 30px; }
    .signup-text { text-align: center; font-size: 0.9rem; margin-top: 10px; }
    .signup-text a { color: #7b68ce; text-decoration: none; font-weight: 500; }
    .signup-text a:hover { text-decoration: underline; }
    @media (max-width: 768px) { .left, .right { width: 100%; height: 50vh; } .left { order: 1; } .right { order: 2; } }
  </style>
</head>
<body>
  <div class="container-fluid">
    <div class="row g-0">
      <div class="col-md-7 left">
        <a href="{{ route('login') }}" class="back-arrow">&#8592; Back</a>
        <img src="{{ asset('assets/images/Group 44.png') }}" alt="Scholarly Logo" class="logo img-fluid">
      </div>
      <div class="col-md-5 right">
        <form class="signup-form" action="{{ route('register.post') }}" method="POST">
          @csrf
          <h3>Create Account</h3>
          <p class="text-muted">Fill in the details to get started.</p>

          <div class="input-group mb-3">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" class="form-control" name="username" placeholder="Username" value="{{ old('username') }}" required>
          </div>

          <div class="input-group mb-3">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" class="form-control" name="email" placeholder="Email" value="{{ old('email') }}" required>
          </div>

          <div class="input-group mb-3">
            <span class="input-group-text"><i class="bi bi-telephone"></i></span>
            <input type="text" class="form-control" name="contact" placeholder="Contact Number" value="{{ old('contact') }}" required>
          </div>

          <div class="input-group mb-3 position-relative">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control password" name="password" placeholder="Password" required>
            <span class="toggle-eye"><i class="bi bi-eye"></i></span>
          </div>

          <div class="input-group mb-3 position-relative">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control password" name="password_confirmation" placeholder="Confirm Password" required>
            <span class="toggle-eye"><i class="bi bi-eye"></i></span>
          </div>

          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
            <label class="form-check-label" for="terms">
              I agree to <a href="#">Terms and Conditions</a>.
            </label>
          </div>

          @if ($errors->any())
            <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
          @endif

          <button type="submit" class="btn w-100">SIGN UP</button>
          <p class="signup-text">Already have an account? <a href="{{ route('login') }}">Sign in here</a></p>

          <div class="divider"><span>OR</span></div>
          <div class="social-login text-center">
            <a href="#"><img src="{{ asset('assets/images/ic_outline-facebook.png') }}" alt="Facebook"></a>
            <a href="#"><img src="{{ asset('assets/images/ri_google-fill.png') }}" alt="Google"></a>
          </div>
        </form>
      </div>
    </div>
  </div>
  <script>
    document.querySelectorAll('.toggle-eye').forEach(btn => {
      btn.addEventListener('click', () => {
        const input = btn.parentElement.querySelector('.password');
        const icon = btn.querySelector('i');
        if (input.type === 'password') { input.type = 'text'; icon.classList.replace('bi-eye', 'bi-eye-slash'); }
        else { input.type = 'password'; icon.classList.replace('bi-eye-slash', 'bi-eye'); }
      });
    });
  </script>
</body>
</html>
