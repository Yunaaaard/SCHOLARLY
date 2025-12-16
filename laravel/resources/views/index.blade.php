@php
// This Blade view was generated from legacy public/legacy/index.html
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Scholarly - Home</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/home.css') }}">
</head>
<body>
  <div style="min-height: 100vh; display: flex; flex-direction: column; background-image: url('{{ asset('assets/images/HOME PAGE.png') }}'); background-size: cover; background-position: center;">
    <div class="navbar">
      <div class="nav-left">
        <img src="{{ asset('assets/images/Group 44.png') }}" alt="Scholarly Logo"/>
      </div>
      <div class="nav-right">
        <a href="/" class="nav-btn">Home</a>
        <a href="{{ route('login') }}" class="nav-link">Login</a>
        <a href="{{ route('register') }}" class="nav-link">Register</a>
      </div>
    </div>

    <div class="hero-wrapper">
      <div class="hero-text">
        <div class="hero-title">
          WELCOME TO SCHOL<span style="color:#7575C3;">ARLY</span>!
        </div>
        <div class="hero-subtitle">
          Empowering Learning, <span>One at a Time</span>.
        </div>
        <a href="{{ route('register') }}" class="get-started">GET STARTED</a>
      </div>
    </div>

    <footer class="footer" style="text-align:center; padding:1rem; background:rgba(0,0,0,0.5); color:#fff; margin-top:auto;">
      © 2025 Scholarly. All rights reserved.
    </footer>
  </div>
</body>
</html>
