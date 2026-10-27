<?php
require_once __DIR__ . '/auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ../Pages/login_secure.php'); exit; }
if (!csrf_is_valid()) { http_response_code(400); exit('Invalid form token. Return to login and try again.'); }
$identity = trim((string)($_POST['identity'] ?? ''));
$password = (string)($_POST['password'] ?? '');
if ($identity === '' || $password === '' || strlen($identity) > 150 || strlen($password) > 4096) {
    header('Location: ../Pages/login_secure.php?error=1'); exit;
}
try {
    $conn = auth_db();
    $stmt = $conn->prepare('SELECT id, full_name, username, email, password_hash, role FROM users WHERE email = ? OR username = ? LIMIT 1');
    $stmt->bind_param('ss', $identity, $identity); $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc(); $stmt->close();
    if (!$user || !password_verify($password, $user['password_hash'])) {
        // Keep login errors generic to avoid revealing whether an account exists.
        usleep(250000); $conn->close(); header('Location: ../Pages/login_secure.php?error=1'); exit;
    }
    if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $update = $conn->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $update->bind_param('si', $newHash, $user['id']); $update->execute(); $update->close();
    }
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = in_array($user['role'], ['admin','student'], true) ? $user['role'] : 'student';
    $_SESSION['last_activity'] = time();
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    if (!empty($_POST['remember'])) issue_remember_token($conn, (int)$user['id']);
    else clear_remember_cookie();
    $conn->close();
    header('Location: ../Pages/dashboard.php'); exit;
} catch (Throwable $e) {
    error_log('StudentHub login error: ' . $e->getMessage());
    http_response_code(500); exit('Login is temporarily unavailable. Check that MySQL is running and the Practical 10 schema is imported.');
}
