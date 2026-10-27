<?php
// Practical 10: shared secure session, timeout, CSRF, and remember-me helpers.
if (session_status() !== PHP_SESSION_ACTIVE) {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

const STUDENTHUB_SESSION_TIMEOUT = 1200; // 20 minutes of inactivity
const STUDENTHUB_REMEMBER_COOKIE = 'studenthub_remember';

function auth_db(): mysqli {
    require_once __DIR__ . '/db_connect_mysqli.php';
    return $conn;
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_is_valid(): bool {
    $sent = (string)($_POST['csrf_token'] ?? '');
    $saved = (string)($_SESSION['csrf_token'] ?? '');
    return $sent !== '' && $saved !== '' && hash_equals($saved, $sent);
}

function clear_remember_cookie(): void {
    setcookie(STUDENTHUB_REMEMBER_COOKIE, '', [
        'expires' => time() - 3600, 'path' => '/',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true, 'samesite' => 'Lax'
    ]);
}

function issue_remember_token(mysqli $conn, int $userId): void {
    $selector = bin2hex(random_bytes(12));
    $validator = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $validator);
    $expires = date('Y-m-d H:i:s', time() + 60 * 60 * 24 * 30);
    $stmt = $conn->prepare('INSERT INTO remember_tokens (user_id, selector, token_hash, expires_at) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('isss', $userId, $selector, $tokenHash, $expires);
    $stmt->execute();
    $stmt->close();
    setcookie(STUDENTHUB_REMEMBER_COOKIE, $selector . ':' . $validator, [
        'expires' => time() + 60 * 60 * 24 * 30,
        'path' => '/',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

function restore_remembered_session(): void {
    if (!empty($_SESSION['user_id']) || empty($_COOKIE[STUDENTHUB_REMEMBER_COOKIE])) return;
    $parts = explode(':', (string)$_COOKIE[STUDENTHUB_REMEMBER_COOKIE], 2);
    if (count($parts) !== 2 || !preg_match('/^[a-f0-9]{24}$/', $parts[0]) || !preg_match('/^[a-f0-9]{64}$/', $parts[1])) {
        clear_remember_cookie(); return;
    }
    try {
        $conn = auth_db();
        $stmt = $conn->prepare('SELECT rt.user_id, rt.token_hash, u.full_name, u.username, u.email, u.role FROM remember_tokens rt JOIN users u ON u.id = rt.user_id WHERE rt.selector = ? AND rt.expires_at > NOW() LIMIT 1');
        $stmt->bind_param('s', $parts[0]);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        if ($row && hash_equals($row['token_hash'], hash('sha256', $parts[1]))) {
            // Rotate the persistent token on every successful restoration.
            $del = $conn->prepare('DELETE FROM remember_tokens WHERE selector = ?');
            $del->bind_param('s', $parts[0]); $del->execute(); $del->close();
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$row['user_id'];
            $_SESSION['full_name'] = $row['full_name'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['last_activity'] = time();
            issue_remember_token($conn, (int)$row['user_id']);
        } else {
            clear_remember_cookie();
        }
        $conn->close();
    } catch (Throwable $e) {
        error_log('StudentHub remember-me restore failed: ' . $e->getMessage());
        clear_remember_cookie();
    }
}

// Enforce inactivity timeout and attempt remember-me restoration on each request.
if (isset($_SESSION['user_id'])) {
    $last = (int)($_SESSION['last_activity'] ?? time());
    if (time() - $last > STUDENTHUB_SESSION_TIMEOUT) {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', ['expires' => time()-42000, 'path' => $p['path'], 'domain' => $p['domain'], 'secure' => $p['secure'], 'httponly' => $p['httponly'], 'samesite' => 'Lax']);
        }
        session_destroy();
        session_start();
        session_regenerate_id(true);
        $_SESSION['session_timed_out'] = true;
    } else {
        $_SESSION['last_activity'] = time();
    }
}
restore_remembered_session();

function require_login(): void {
    if (empty($_SESSION['user_id'])) {
        if (!empty($_SESSION['session_timed_out'])) {
            unset($_SESSION['session_timed_out']);
            header('Location: login_secure.php?message=timeout'); exit;
        }
        header('Location: login_secure.php?message=login_required'); exit;
    }
}
function require_role(string $role): void {
    require_login();
    if (($_SESSION['role'] ?? '') !== $role) {
        http_response_code(403);
        header('Location: dashboard.php?message=forbidden'); exit;
    }
}
function h(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
