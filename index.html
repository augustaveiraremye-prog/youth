<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Gatsinde Youth – Connect & Chat</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/style.css">
</head>
<body class="auth-page">

<?php
session_start();
if (isset($_SESSION['user'])) {
    header('Location: chat.php');
    exit;
}

$error = '';
$mode = isset($_GET['mode']) && $_GET['mode'] === 'signup' ? 'signup' : 'login';

// Handle file-based user storage for demo
$users_file = __DIR__ . '/includes/users.json';
if (!file_exists($users_file)) file_put_contents($users_file, json_encode([]));
$users = json_decode(file_get_contents($users_file), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $fullname = trim($_POST['fullname'] ?? '');

    if ($mode === 'signup') {
        if (empty($username) || empty($password) || empty($fullname)) {
            $error = 'All fields are required.';
        } elseif (isset($users[$username])) {
            $error = 'Username already taken.';
        } else {
            $users[$username] = [
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'fullname' => $fullname,
                'avatar'   => 'https://ui-avatars.com/api/?name=' . urlencode($fullname) . '&background=random&color=fff&size=128',
                'joined'   => date('Y-m-d'),
            ];
            file_put_contents($users_file, json_encode($users));
            $_SESSION['user'] = $username;
            $_SESSION['fullname'] = $fullname;
            $_SESSION['avatar'] = $users[$username]['avatar'];
            header('Location: chat.php');
            exit;
        }
    } else {
        if (isset($users[$username]) && password_verify($password, $users[$username]['password'])) {
            $_SESSION['user'] = $username;
            $_SESSION['fullname'] = $users[$username]['fullname'];
            $_SESSION['avatar'] = $users[$username]['avatar'];
            header('Location: chat.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>

<div class="auth-container">
  <!-- Left Panel: Brand -->
  <div class="auth-brand">
    <div class="brand-glow"></div>
    <div class="brand-content">
      <div class="brand-logo">
        <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
          <circle cx="24" cy="24" r="24" fill="url(#grad)"/>
          <path d="M14 24C14 18.477 18.477 14 24 14s10 4.477 10 10-4.477 10-10 10" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
          <circle cx="24" cy="24" r="4" fill="white"/>
          <defs>
            <linearGradient id="grad" x1="0" y1="0" x2="48" y2="48">
              <stop offset="0%" stop-color="#F5785A"/>
              <stop offset="50%" stop-color="#C13584"/>
              <stop offset="100%" stop-color="#833AB4"/>
            </linearGradient>
          </defs>
        </svg>
      </div>
      <h1 class="brand-name">Gatsinde<br/><span>Youth</span></h1>
      <p class="brand-tagline">Where young voices connect,<br/>share, and grow together.</p>
      <div class="brand-features">
        <div class="feature-pill">💬 Real-time Chat</div>
        <div class="feature-pill">👥 Community</div>
        <div class="feature-pill">🌍 Rwanda</div>
      </div>
    </div>
    <div class="brand-deco">
      <div class="deco-circle c1"></div>
      <div class="deco-circle c2"></div>
      <div class="deco-circle c3"></div>
    </div>
  </div>

  <!-- Right Panel: Form -->
  <div class="auth-form-panel">
    <div class="auth-card">
      <div class="card-logo-sm">
        <svg width="36" height="36" viewBox="0 0 48 48" fill="none">
          <circle cx="24" cy="24" r="24" fill="url(#grad2)"/>
          <circle cx="24" cy="24" r="4" fill="white"/>
          <path d="M14 24C14 18.477 18.477 14 24 14s10 4.477 10 10-4.477 10-10 10" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
          <defs>
            <linearGradient id="grad2" x1="0" y1="0" x2="48" y2="48">
              <stop offset="0%" stop-color="#F5785A"/>
              <stop offset="100%" stop-color="#833AB4"/>
            </linearGradient>
          </defs>
        </svg>
        <span>Gatsinde Youth</span>
      </div>

      <h2 class="form-title">
        <?= $mode === 'signup' ? 'Create your account' : 'Welcome back' ?>
      </h2>
      <p class="form-subtitle">
        <?= $mode === 'signup' ? 'Join the Gatsinde Youth community' : 'Sign in to continue chatting' ?>
      </p>

      <?php if ($error): ?>
        <div class="alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" class="auth-form">
        <?php if ($mode === 'signup'): ?>
        <div class="form-group">
          <label>Full Name</label>
          <input type="text" name="fullname" placeholder="Your full name" required />
        </div>
        <?php endif; ?>
        <div class="form-group">
          <label>Username</label>
          <input type="text" name="username" placeholder="@yourusername" required />
        </div>
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" placeholder="••••••••" required />
        </div>
        <button type="submit" class="btn-primary">
          <?= $mode === 'signup' ? 'Create Account' : 'Sign In' ?>
        </button>
      </form>

      <div class="auth-switch">
        <?php if ($mode === 'login'): ?>
          Don't have an account? <a href="?mode=signup">Sign up</a>
        <?php else: ?>
          Already have an account? <a href="?mode=login">Log in</a>
        <?php endif; ?>
      </div>

      <?php if ($mode === 'login'): ?>
      <div class="demo-hint">
        <p>🚀 <strong>Quick demo:</strong> Register an account to start chatting!</p>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

</body>
</html>
