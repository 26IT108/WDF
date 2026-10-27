<?php
require_once __DIR__ . '/../php/auth.php';
if (!empty($_SESSION['user_id'])) { header('Location: dashboard.php'); exit; }
$message = '';
if (($_GET['message'] ?? '') === 'login_required') $message = 'Please log in to access that page.';
elseif (($_GET['message'] ?? '') === 'logged_out') $message = 'You have been logged out securely.';
elseif (($_GET['message'] ?? '') === 'timeout') $message = 'Your session expired after inactivity. Please log in again.';
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Secure Login - StudentHub</title><link rel="stylesheet" href="../css/style.css"><style>.auth-card{max-width:560px;margin:30px auto;padding:28px;background:#fff;border-radius:10px;box-shadow:0 4px 14px #00000012}.auth-card label{font-weight:600}.auth-card input[type=email],.auth-card input[type=password]{width:100%;padding:11px;border:1px solid #bbb;border-radius:5px}.auth-message{padding:12px;background:#edf7ed;border-left:4px solid #1b5e20;margin:15px 0}.auth-card button{background:#1b5e20;color:white;border:0;padding:11px 22px;border-radius:5px;cursor:pointer}</style></head><body>
<header><div class="logo-area"><img src="../images/logo.png" alt="StudentHub Logo"><div><h1>StudentHub</h1><p>Your Student Portal</p></div></div></header>
<nav aria-label="Main Navigation"><a href="index.html">Home</a><a href="login_secure.php">Login</a><a href="register_secure.php">Register</a><a href="dashboard.php">Dashboard</a><a href="events.html">Events</a><a href="faq.html">FAQ</a><a href="contact.html">Contact</a></nav>
<main><section class="auth-card"><h2>Secure Login</h2><p>Sign in to access your StudentHub account.</p><?php if ($message !== ''): ?><div class="auth-message" role="status"><?= h($message) ?></div><?php endif; ?>
<?php if (isset($_GET['error'])): ?><div class="auth-message" role="alert">Invalid email/username or password. Please try again.</div><?php endif; ?>
<form action="../php/login_process.php" method="post"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><p><label for="identity">Email or username</label><input type="text" id="identity" name="identity" maxlength="150" autocomplete="username" required></p><p><label for="password">Password</label><input type="password" id="password" name="password" autocomplete="current-password" required></p><p><input type="checkbox" id="remember" name="remember" value="1"><label for="remember"> Remember me for 30 days on this device</label></p><button type="submit">Login</button></form><p>Don't have an account? <a href="register_secure.php">Register here</a></p></section></main><footer><p>&copy; 2026 StudentHub. All rights reserved.</p></footer></body></html>
