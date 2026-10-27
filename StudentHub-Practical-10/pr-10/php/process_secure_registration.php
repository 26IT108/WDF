<?php
session_start();

function showResult(string $title, array $messages, bool $success = false): void
{
    $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $class = $success ? 'success-message' : 'error-message';
    ?>
    <!DOCTYPE html>
    <html lang="en"><head>
        <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= $safeTitle ?> - StudentHub</title>
        <link rel="stylesheet" href="../css/style.css">
        <style>.form-container .message{padding:12px 14px;border-radius:5px;margin:15px 0}.form-container .error-message{background:#fff0f0;color:#9b1c1c;border:1px solid #f2b8b8}.form-container .success-message{background:#edf9f0;color:#176b31;border:1px solid #b8e5c2}</style>
    </head><body>
    <header><div class="logo-area"><img src="../images/logo.png" alt="StudentHub Logo"><div><h1>StudentHub</h1><p>Your Student Portal</p></div></div></header>
    <main><section class="form-container"><h2><?= $safeTitle ?></h2><div class="message <?= $class ?>"><ul>
    <?php foreach ($messages as $message): ?><li><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?>
    </ul></div><p><a href="../Pages/register_secure.php">Back to Secure Registration</a></p><p><a href="index.html">StudentHub Home</a></p></section></main>
    <footer><p>&copy; 2026 StudentHub. All rights reserved.</p></footer></body></html>
    <?php
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method not allowed. Please submit the registration form.');
}

$postedToken = (string)($_POST['csrf_token'] ?? '');
$sessionToken = (string)($_SESSION['csrf_token'] ?? '');
if ($sessionToken === '' || !hash_equals($sessionToken, $postedToken)) {
    http_response_code(400);
    showResult('Registration Failed', ['Your form session expired or was invalid. Please reload the form and try again.']);
    exit;
}

$name = trim((string)($_POST['name'] ?? ''));
$username = trim((string)($_POST['username'] ?? ''));
$email = strtolower(trim((string)($_POST['email'] ?? '')));
$password = (string)($_POST['password'] ?? '');
$confirmPassword = (string)($_POST['confirm_password'] ?? '');
$termsAccepted = isset($_POST['terms']);
$errors = [];

if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
    $errors[] = 'Full name must contain between 2 and 100 characters.';
}
if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
    $errors[] = 'Username must be 3–30 characters and contain only letters, numbers, or underscores.';
}
if (strlen($email) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address (maximum 150 characters).';
}
if (strlen($password) < 8 || strlen($password) > 4096) {
    $errors[] = 'Password must contain at least 8 characters.';
}
if ($password !== $confirmPassword) {
    $errors[] = 'Passwords do not match.';
}
if (!$termsAccepted) {
    $errors[] = 'Please accept the Terms and Conditions.';
}
if ($errors) {
    http_response_code(422);
    showResult('Registration Failed', $errors);
    exit;
}

require __DIR__ . '/db_connect_mysqli.php';

try {
    // Prepared statement checks either duplicate identifier without exposing SQL input.
    $check = $conn->prepare('SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1');
    $check->bind_param('ss', $email, $username);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        $check->close();
        http_response_code(409);
        showResult('Registration Failed', ['That email address or username is already registered. Please use a different one.']);
        exit;
    }
    $check->close();

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $insert = $conn->prepare('INSERT INTO users (full_name, username, email, password_hash) VALUES (?, ?, ?, ?)');
    $insert->bind_param('ssss', $name, $username, $email, $passwordHash);
    $insert->execute();
    $insert->close();
    unset($_SESSION['csrf_token']);
    showResult('Registration Successful!', ['Your account has been created securely. Your password was hashed before storage.'], true);
} catch (mysqli_sql_exception $exception) {
    error_log('StudentHub registration error: ' . $exception->getMessage());
    // Unique indexes are the final protection against simultaneous duplicate submissions.
    if ((int)$exception->getCode() === 1062) {
        http_response_code(409);
        showResult('Registration Failed', ['That email address or username is already registered.']);
    } else {
        http_response_code(500);
        showResult('Registration Error', ['We could not complete registration right now. Check the database setup and try again.']);
    }
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
