<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Registration - StudentHub</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .secure-note { background: rgba(44, 130, 201, .09); border-left: 4px solid #2c82c9; padding: 12px 14px; margin: 16px 0; border-radius: 5px; }
        .form-container .message { padding: 12px 14px; border-radius: 5px; margin: 15px 0; }
        .form-container .message.error-message { background: #fff0f0; color: #9b1c1c; border: 1px solid #f2b8b8; }
        .form-container .message.success-message { background: #edf9f0; color: #176b31; border: 1px solid #b8e5c2; }
        .form-container .field-hint { display: block; margin-top: 4px; font-size: .88rem; opacity: .8; }
    </style>
</head>
<body>
<header>
    <div class="logo-area">
        <img src="../images/logo.png" alt="StudentHub Logo">
        <div><h1>StudentHub</h1><p>Your Student Portal</p></div>
    </div>
</header>
<nav>
    <a href="index.html">Home</a>
    <a href="login.html">Login</a>
    <a href="register_secure.php" aria-current="page">Secure Register</a>
    <a href="dashboard.html">Dashboard</a>
    <a href="attendance.html">Attendance</a>
    <a href="assignment.html">Assignment</a>
    <a href="events.html">Events</a>
    <a href="students.html">Students</a>
    <a href="profile.html">Profile</a>
    <a href="faq.html">FAQ</a>
    <a href="contact.html">Contact</a>
</nav>
<main>
    <section class="form-container">
        <h2>Secure Student Registration</h2>
        <p>Create your StudentHub account securely.</p>
        <div class="secure-note">Your password is hashed before it is stored. Your original password is never saved in the database.</div>
        <form id="secureRegisterForm" action="../php/process_secure_registration.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
            <label for="name">Full Name:</label>
            <input type="text" id="name" name="name" placeholder="Enter your full name" minlength="2" maxlength="100" autocomplete="name" required>

            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="Choose a username" minlength="3" maxlength="30" pattern="[A-Za-z0-9_]+" autocomplete="username" required>
            <small class="field-hint">Use 3–30 letters, numbers, or underscores.</small>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="example@email.com" maxlength="150" autocomplete="email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="At least 8 characters" minlength="8" autocomplete="new-password" required>
            <small class="field-hint">Use at least 8 characters.</small>

            <label for="confirmPassword">Confirm Password:</label>
            <input type="password" id="confirmPassword" name="confirm_password" placeholder="Enter password again" minlength="8" autocomplete="new-password" required>

            <div class="terms">
                <label><input type="checkbox" id="terms" name="terms" value="1" required> I agree to the Terms and Conditions</label>
            </div>
            <button type="submit">Create Secure Account</button>
        </form>
        <p class="registration-view-link"><a href="register.html">Return to Practical 8 registration</a></p>
    </section>
</main>
<footer><p>&copy; 2026 StudentHub. All rights reserved.</p></footer>
<script>
document.getElementById('secureRegisterForm').addEventListener('submit', function (event) {
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirmPassword');
    if (password.value !== confirmPassword.value) {
        event.preventDefault();
        confirmPassword.setCustomValidity('Passwords do not match.');
        confirmPassword.reportValidity();
    } else {
        confirmPassword.setCustomValidity('');
    }
});
document.getElementById('confirmPassword').addEventListener('input', function () { this.setCustomValidity(''); });
</script>
</body>
</html>
