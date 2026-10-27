<?php
// Practical 13: JSON API for asynchronous event CRUD.
require_once __DIR__ . '/../php/auth.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function api_reply(int $status, array $payload): never {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (empty($_SESSION['user_id'])) {
    api_reply(401, ['success' => false, 'message' => 'Please sign in to continue.']);
}
if (($_SESSION['role'] ?? '') !== 'admin') {
    api_reply(403, ['success' => false, 'message' => 'Admin access is required.']);
}

try {
    require_once __DIR__ . '/../php/db_connect_mysqli.php';
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

    if ($method === 'GET') {
        $search = trim((string)($_GET['search'] ?? ''));
        $status = trim((string)($_GET['status'] ?? ''));
        $allowedStatuses = ['upcoming', 'completed', 'cancelled'];
        $sql = 'SELECT id, title, description, category, event_date, status, poster_path, created_at FROM events WHERE 1=1';
        $params = [];
        $types = '';
        if ($search !== '') {
            $sql .= ' AND (title LIKE ? OR description LIKE ? OR category LIKE ?)';
            $like = '%' . $search . '%';
            $params = [$like, $like, $like];
            $types = 'sss';
        }
        if ($status !== '' && in_array($status, $allowedStatuses, true)) {
            $sql .= ' AND status = ?';
            $params[] = $status;
            $types .= 's';
        }
        $sql .= ' ORDER BY event_date DESC, id DESC';
        $stmt = $conn->prepare($sql);
        if ($types !== '') $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $conn->close();
        api_reply(200, ['success' => true, 'message' => 'Events loaded.', 'data' => $events]);
    }

    if ($method !== 'POST') {
        header('Allow: GET, POST');
        api_reply(405, ['success' => false, 'message' => 'Method not allowed. Use GET or POST.']);
    }

    $sentToken = (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    $savedToken = (string)($_SESSION['csrf_token'] ?? '');
    if ($sentToken === '' || $savedToken === '' || !hash_equals($savedToken, $sentToken)) {
        api_reply(419, ['success' => false, 'message' => 'Security token expired. Refresh the page and try again.']);
    }
    $raw = file_get_contents('php://input');
    $input = json_decode($raw ?: '', true);
    if (!is_array($input)) api_reply(400, ['success' => false, 'message' => 'Request body must be valid JSON.']);

    $action = (string)($input['action'] ?? '');
    $id = filter_var($input['id'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
    if ($action === 'delete') {
        if ($id < 1) api_reply(422, ['success' => false, 'message' => 'A valid event ID is required.']);
        $stmt = $conn->prepare('SELECT poster_path FROM events WHERE id = ?');
        $stmt->bind_param('i', $id); $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc(); $stmt->close();
        if (!$existing) api_reply(404, ['success' => false, 'message' => 'Event not found.']);
        $stmt = $conn->prepare('DELETE FROM events WHERE id = ?');
        $stmt->bind_param('i', $id); $stmt->execute(); $deleted = $stmt->affected_rows; $stmt->close();
        if ($deleted && !empty($existing['poster_path'])) {
            $posterName = basename((string)$existing['poster_path']);
            $posterPath = __DIR__ . '/../uploads/event-posters/' . $posterName;
            if ($posterName !== '' && $posterName === $existing['poster_path'] && is_file($posterPath)) @unlink($posterPath);
        }
        $conn->close();
        api_reply($deleted ? 200 : 500, ['success' => (bool)$deleted, 'message' => $deleted ? 'Event deleted successfully.' : 'Unable to delete event.']);
    }

    if (!in_array($action, ['create', 'update'], true)) {
        api_reply(422, ['success' => false, 'message' => 'Choose create, update, or delete.']);
    }
    $title = trim((string)($input['title'] ?? ''));
    $description = trim((string)($input['description'] ?? ''));
    $category = trim((string)($input['category'] ?? ''));
    $eventDateInput = trim((string)($input['event_date'] ?? ''));
    $status = trim((string)($input['status'] ?? 'upcoming'));
    $categories = ['Technical', 'Workshop', 'Sports', 'Cultural', 'Career', 'Other'];
    $statuses = ['upcoming', 'completed', 'cancelled'];

    if ($title === '' || mb_strlen($title) > 150) api_reply(422, ['success' => false, 'message' => 'Enter a title of 1–150 characters.']);
    if ($description === '' || mb_strlen($description) > 5000) api_reply(422, ['success' => false, 'message' => 'Enter a description of 1–5000 characters.']);
    if (!in_array($category, $categories, true)) api_reply(422, ['success' => false, 'message' => 'Select a valid category.']);
    if (!in_array($status, $statuses, true)) api_reply(422, ['success' => false, 'message' => 'Select a valid event status.']);
    $date = DateTime::createFromFormat('Y-m-d\TH:i', $eventDateInput) ?: DateTime::createFromFormat('Y-m-d H:i:s', $eventDateInput);
    $dateErrors = DateTime::getLastErrors();
    if (!$date || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))) {
        api_reply(422, ['success' => false, 'message' => 'Enter a valid event date and time.']);
    }
    $eventDate = $date->format('Y-m-d H:i:s');

    if ($action === 'create') {
        $posterPath = null;
        $adminId = (int)$_SESSION['user_id'];
        $stmt = $conn->prepare('INSERT INTO events (title, description, category, event_date, status, poster_path, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('ssssssi', $title, $description, $category, $eventDate, $status, $posterPath, $adminId);
        $stmt->execute(); $newId = $stmt->insert_id; $stmt->close(); $conn->close();
        api_reply(201, ['success' => true, 'message' => 'Event added successfully.', 'id' => $newId]);
    }

    if ($id < 1) api_reply(422, ['success' => false, 'message' => 'A valid event ID is required to update.']);
    $check = $conn->prepare('SELECT id FROM events WHERE id = ?');
    $check->bind_param('i', $id); $check->execute(); $found = $check->get_result()->fetch_assoc(); $check->close();
    if (!$found) api_reply(404, ['success' => false, 'message' => 'Event not found. Refresh the list and try again.']);
    $stmt = $conn->prepare('UPDATE events SET title = ?, description = ?, category = ?, event_date = ?, status = ? WHERE id = ?');
    $stmt->bind_param('sssssi', $title, $description, $category, $eventDate, $status, $id);
    $stmt->execute(); $stmt->close(); $conn->close();
    api_reply(200, ['success' => true, 'message' => 'Event updated successfully.']);
} catch (Throwable $e) {
    error_log('StudentHub Practical 13 API error: ' . $e->getMessage());
    api_reply(500, ['success' => false, 'message' => 'A server error occurred. Check the database setup and try again.']);
}
