<?php
require_once __DIR__ . '/auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ../Pages/login_secure.php'); exit; }
if (!csrf_is_valid()) { http_response_code(400); exit('Invalid form token. Please return to the dashboard and try again.'); }
try {
    if (!empty($_COOKIE[STUDENTHUB_REMEMBER_COOKIE])) {
        $parts = explode(':', (string)$_COOKIE[STUDENTHUB_REMEMBER_COOKIE], 2);
        if (count($parts) === 2 && preg_match('/^[a-f0-9]{24}$/', $parts[0])) {
            $conn = auth_db(); $stmt = $conn->prepare('DELETE FROM remember_tokens WHERE selector = ?');
            $stmt->bind_param('s', $parts[0]); $stmt->execute(); $stmt->close(); $conn->close();
        }
    }
} catch (Throwable $e) { error_log('StudentHub logout token cleanup failed: ' . $e->getMessage()); }
clear_remember_cookie();
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', ['expires' => time()-42000, 'path' => $p['path'], 'domain' => $p['domain'], 'secure' => $p['secure'], 'httponly' => $p['httponly'], 'samesite' => 'Lax']);
}
session_destroy();
header('Location: ../Pages/login_secure.php?message=logged_out'); exit;
