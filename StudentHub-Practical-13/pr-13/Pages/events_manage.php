<?php
require_once __DIR__ . '/../php/auth.php';
require_role('admin');
require_once __DIR__ . '/../php/db_connect_mysqli.php';

const EVENT_UPLOAD_DIR = __DIR__ . '/../uploads/event-posters/';
const EVENT_UPLOAD_WEB = '../uploads/event-posters/';
const EVENT_MAX_FILE_SIZE = 2 * 1024 * 1024; // 2 MB

$message = (string)($_GET['message'] ?? '');
$messageType = (($_GET['type'] ?? 'success') === 'error') ? 'error' : 'success';
$editEvent = null;

function event_redirect(string $message, string $type = 'success'): never {
    header('Location: events_manage.php?message=' . rawurlencode($message) . '&type=' . rawurlencode($type));
    exit;
}
function event_e(mixed $value): string { return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8'); }
function delete_event_poster(?string $relativePath): void {
    if (!$relativePath) return;
    $basename = basename($relativePath);
    if ($basename !== '' && $basename === $relativePath && is_file(EVENT_UPLOAD_DIR . $basename)) {
        @unlink(EVENT_UPLOAD_DIR . $basename);
    }
}
function save_event_poster(array $file): ?string {
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Poster upload failed. Please choose the file again.');
    if ((int)$file['size'] < 1 || (int)$file['size'] > EVENT_MAX_FILE_SIZE) throw new RuntimeException('Poster must be smaller than or equal to 2 MB.');
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($allowed[$mime])) throw new RuntimeException('Invalid poster format. Upload a JPG, PNG, or WEBP image.');
    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) throw new RuntimeException('The uploaded poster is not a valid image.');
    if (!is_dir(EVENT_UPLOAD_DIR) && !mkdir(EVENT_UPLOAD_DIR, 0755, true) && !is_dir(EVENT_UPLOAD_DIR)) throw new RuntimeException('Poster upload folder could not be created.');
    $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], EVENT_UPLOAD_DIR . $filename)) throw new RuntimeException('Unable to save the poster. Check folder permissions.');
    return $filename;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid()) event_redirect('Your security token expired. Please try again.', 'error');
    $action = (string)($_POST['action'] ?? '');
    $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT) ?: 0;

    if ($action === 'delete') {
        if ($id < 1) event_redirect('Invalid event record.', 'error');
        $stmt = $conn->prepare('SELECT poster_path FROM events WHERE id = ?');
        $stmt->bind_param('i', $id); $stmt->execute(); $old = $stmt->get_result()->fetch_assoc(); $stmt->close();
        if (!$old) event_redirect('Event was not found.', 'error');
        $stmt = $conn->prepare('DELETE FROM events WHERE id = ?');
        $stmt->bind_param('i', $id); $stmt->execute(); $deleted = $stmt->affected_rows; $stmt->close();
        if ($deleted) { delete_event_poster($old['poster_path'] ?? null); event_redirect('Event deleted successfully.'); }
        event_redirect('Unable to delete event.', 'error');
    }

    if ($action === 'save') {
        $title = trim((string)($_POST['title'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $category = trim((string)($_POST['category'] ?? ''));
        $eventDate = trim((string)($_POST['event_date'] ?? ''));
        $status = trim((string)($_POST['status'] ?? 'upcoming'));
        $allowedCategories = ['Technical', 'Workshop', 'Sports', 'Cultural', 'Career', 'Other'];
        $allowedStatuses = ['upcoming', 'completed', 'cancelled'];
        if ($title === '' || mb_strlen($title) > 150) event_redirect('Enter an event title (up to 150 characters).', 'error');
        if ($description === '' || mb_strlen($description) > 5000) event_redirect('Enter an event description (up to 5000 characters).', 'error');
        if (!in_array($category, $allowedCategories, true)) event_redirect('Select a valid event category.', 'error');
        $date = DateTime::createFromFormat('Y-m-d\TH:i', $eventDate) ?: DateTime::createFromFormat('Y-m-d H:i:s', $eventDate);
        if (!$date || $date->format('Y-m-d H:i:s') === '') event_redirect('Select a valid event date and time.', 'error');
        $eventDate = $date->format('Y-m-d H:i:s');
        if (!in_array($status, $allowedStatuses, true)) event_redirect('Select a valid event status.', 'error');

        $stmt = $conn->prepare('SELECT poster_path FROM events WHERE id = ?');
        $stmt->bind_param('i', $id); $stmt->execute(); $existing = $id > 0 ? $stmt->get_result()->fetch_assoc() : null; $stmt->close();
        if ($id > 0 && !$existing) event_redirect('Event was not found. Refresh the page and try again.', 'error');
        $newPoster = null;
        try { $newPoster = save_event_poster($_FILES['poster'] ?? []); }
        catch (RuntimeException $e) { event_redirect($e->getMessage(), 'error'); }
        $posterPath = $newPoster ?? ($existing['poster_path'] ?? null);
        try {
            if ($id > 0) {
                $stmt = $conn->prepare('UPDATE events SET title = ?, description = ?, category = ?, event_date = ?, status = ?, poster_path = ? WHERE id = ?');
                $stmt->bind_param('ssssssi', $title, $description, $category, $eventDate, $status, $posterPath, $id);
                $stmt->execute(); $stmt->close();
                if ($newPoster !== null) delete_event_poster($existing['poster_path'] ?? null);
                event_redirect('Event updated successfully.');
            } else {
                $stmt = $conn->prepare('INSERT INTO events (title, description, category, event_date, status, poster_path, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $adminId = (int)($_SESSION['user_id'] ?? 0);
                $stmt->bind_param('ssssssi', $title, $description, $category, $eventDate, $status, $posterPath, $adminId);
                $stmt->execute(); $stmt->close();
                event_redirect('Event added successfully.');
            }
        } catch (Throwable $e) {
            if ($newPoster !== null) delete_event_poster($newPoster);
            error_log('StudentHub event save failed: ' . $e->getMessage());
            event_redirect('Unable to save the event. Check that the Practical 12 database migration has been applied.', 'error');
        }
    }
    event_redirect('Unknown action. Please try again.', 'error');
}

if (isset($_GET['edit'])) {
    $editId = filter_var($_GET['edit'], FILTER_VALIDATE_INT);
    if ($editId && $editId > 0) {
        $stmt = $conn->prepare('SELECT * FROM events WHERE id = ?'); $stmt->bind_param('i', $editId); $stmt->execute();
        $editEvent = $stmt->get_result()->fetch_assoc() ?: null; $stmt->close();
        if (!$editEvent) { $message = 'Event was not found.'; $messageType = 'error'; }
    }
}
$search = trim((string)($_GET['search'] ?? ''));
$statusFilter = trim((string)($_GET['status'] ?? ''));
$sql = 'SELECT id, title, description, category, event_date, status, poster_path, created_at FROM events WHERE 1=1';
$params = []; $types = '';
if ($search !== '') { $sql .= ' AND (title LIKE ? OR description LIKE ? OR category LIKE ?)'; $like = '%' . $search . '%'; $params = [$like, $like, $like]; $types = 'sss'; }
if (in_array($statusFilter, ['upcoming', 'completed', 'cancelled'], true)) { $sql .= ' AND status = ?'; $params[] = $statusFilter; $types .= 's'; }
$sql .= ' ORDER BY event_date DESC, id DESC';
$stmt = $conn->prepare($sql); if ($types !== '') $stmt->bind_param($types, ...$params); $stmt->execute(); $events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close(); $conn->close();
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Event Management - StudentHub</title><link rel="stylesheet" href="../css/style.css">
<style>
.management-wrap{max-width:1180px;margin:30px auto;padding:0 18px}.management-panel{background:#fff;border-radius:12px;padding:24px;margin-bottom:22px;box-shadow:0 4px 14px #00000012}.management-panel h2,.management-panel h3{color:#1b5e20;margin-top:0}.management-form{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:14px}.management-form label{display:block;font-weight:600;margin-bottom:5px}.management-form input,.management-form select,.management-form textarea,.search-form input,.search-form select{width:100%;padding:10px;border:1px solid #cbd5cb;border-radius:6px;font:inherit;box-sizing:border-box}.management-form textarea{min-height:110px;resize:vertical}.management-actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-top:16px}.btn{display:inline-block;border:0;border-radius:6px;padding:10px 16px;background:#1b5e20;color:#fff;text-decoration:none;font:inherit;cursor:pointer}.btn-secondary{background:#455a64}.btn-danger{background:#b3261e}.btn-small{padding:6px 10px;font-size:.9rem}.notice{padding:12px 15px;border-radius:7px;margin-bottom:18px}.notice.success{background:#e8f5e9;color:#1b5e20}.notice.error{background:#ffebee;color:#b71c1c}.search-form{display:grid;grid-template-columns:minmax(200px,1fr) 200px auto auto;gap:10px;align-items:center}.table-scroll{overflow-x:auto}table{width:100%;border-collapse:collapse;min-width:800px}th,td{text-align:left;padding:11px 10px;border-bottom:1px solid #e4e9e4;vertical-align:top}th{background:#f0f7f0;color:#204b24}td form{display:inline}.poster-thumb{width:76px;height:58px;object-fit:cover;border-radius:5px}.status-pill{display:inline-block;padding:4px 8px;border-radius:20px;background:#edf5ed;font-size:.85rem;text-transform:capitalize}.help-text{color:#607060;font-size:.9rem}.empty-state{padding:22px;text-align:center;color:#607060}@media(max-width:650px){.management-panel{padding:16px}.search-form{grid-template-columns:1fr}}
</style></head><body>
<header><div class="logo-area"><img src="../images/logo.png" alt="StudentHub Logo"><div><h1>StudentHub</h1><p>Your Student Portal</p></div></div></header>
<nav aria-label="Main Navigation"><a href="index.html">Home</a><a href="dashboard.php">Dashboard</a><a href="admin_dashboard.php">Admin</a><a href="events.html">Events</a><a href="faq.html">FAQ</a></nav>
<main class="management-wrap"><section class="management-panel"><h2>Event Management</h2><p>Add, update, view, and delete college events. Posters must be JPG, PNG, or WEBP and no larger than 2 MB.</p>
<?php if ($message !== ''): ?><div class="notice <?= event_e($messageType) ?>" role="status"><?= event_e($message) ?></div><?php endif; ?>
<h3><?= $editEvent ? 'Edit Event' : 'Add New Event' ?></h3>
<form method="post" action="events_manage.php<?= $editEvent ? '?edit=' . (int)$editEvent['id'] : '' ?>" class="management-form" enctype="multipart/form-data">
<input type="hidden" name="csrf_token" value="<?= event_e(csrf_token()) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int)($editEvent['id'] ?? 0) ?>">
<div><label for="title">Event title *</label><input id="title" name="title" maxlength="150" required value="<?= event_e($editEvent['title'] ?? '') ?>"></div>
<div><label for="category">Category *</label><select id="category" name="category" required><option value="">Select category</option><?php foreach (['Technical','Workshop','Sports','Cultural','Career','Other'] as $category): ?><option value="<?= event_e($category) ?>" <?= ($editEvent['category'] ?? '') === $category ? 'selected' : '' ?>><?= event_e($category) ?></option><?php endforeach; ?></select></div>
<div><label for="event_date">Date and time *</label><input id="event_date" type="datetime-local" name="event_date" required value="<?= isset($editEvent['event_date']) ? event_e(date('Y-m-d\TH:i', strtotime($editEvent['event_date']))) : '' ?>"></div>
<div><label for="status">Status *</label><select id="status" name="status" required><?php foreach (['upcoming','completed','cancelled'] as $status): ?><option value="<?= $status ?>" <?= ($editEvent['status'] ?? 'upcoming') === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option><?php endforeach; ?></select></div>
<div style="grid-column:1/-1"><label for="description">Description *</label><textarea id="description" name="description" maxlength="5000" required><?= event_e($editEvent['description'] ?? '') ?></textarea></div>
<div><label for="poster">Poster image <?= $editEvent ? '(optional)' : '(optional)' ?></label><input id="poster" type="file" name="poster" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"><p class="help-text">JPG, PNG or WEBP · maximum 2 MB. Leave empty to keep the current poster.</p></div>
<?php if (!empty($editEvent['poster_path'])): ?><div><label>Current poster</label><p><img class="poster-thumb" src="<?= EVENT_UPLOAD_WEB . rawurlencode(basename($editEvent['poster_path'])) ?>" alt="Current poster"></p></div><?php endif; ?>
<div class="management-actions" style="grid-column:1/-1"><button class="btn" type="submit"><?= $editEvent ? 'Update Event' : 'Add Event' ?></button><?php if ($editEvent): ?><a class="btn btn-secondary" href="events_manage.php">Cancel edit</a><?php endif; ?></div>
</form></section>
<section class="management-panel"><h3>Search and Filter Events</h3><form class="search-form" method="get" action="events_manage.php"><input type="search" name="search" placeholder="Search title, description, category" value="<?= event_e($search) ?>" aria-label="Search events"><select name="status" aria-label="Filter by status"><option value="">All statuses</option><?php foreach (['upcoming','completed','cancelled'] as $status): ?><option value="<?= $status ?>" <?= $statusFilter === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option><?php endforeach; ?></select><button class="btn" type="submit">Search / Filter</button><a class="btn btn-secondary" href="events_manage.php">Reset</a></form></section>
<section class="management-panel"><h3>Event Records (<?= count($events) ?>)</h3><div class="table-scroll"><table><thead><tr><th>ID</th><th>Poster</th><th>Event</th><th>Category</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php if (!$events): ?><tr><td colspan="7" class="empty-state">No events found. Add an event or change your search filters.</td></tr><?php else: foreach ($events as $event): ?><tr><td><?= (int)$event['id'] ?></td><td><?php if (!empty($event['poster_path'])): ?><img class="poster-thumb" src="<?= EVENT_UPLOAD_WEB . rawurlencode(basename($event['poster_path'])) ?>" alt="Poster for <?= event_e($event['title']) ?>"><?php else: ?>—<?php endif; ?></td><td><strong><?= event_e($event['title']) ?></strong><br><?= nl2br(event_e(mb_strimwidth($event['description'], 0, 140, '…'))) ?></td><td><?= event_e($event['category']) ?></td><td><?= event_e(date('d M Y, h:i A', strtotime($event['event_date']))) ?></td><td><span class="status-pill"><?= event_e($event['status']) ?></span></td><td><a class="btn btn-small" href="events_manage.php?edit=<?= (int)$event['id'] ?>">Edit</a> <form method="post" action="events_manage.php" onsubmit="return confirm('Delete this event and its poster permanently?');"><input type="hidden" name="csrf_token" value="<?= event_e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$event['id'] ?>"><button class="btn btn-danger btn-small" type="submit">Delete</button></form></td></tr><?php endforeach; endif; ?>
</tbody></table></div></section><form action="../php/logout.php" method="post" style="text-align:right;margin:0 0 24px"><input type="hidden" name="csrf_token" value="<?= event_e(csrf_token()) ?>"><button class="btn btn-secondary" type="submit">Logout</button></form></main><footer><p>&copy; 2026 StudentHub. All rights reserved.</p></footer></body></html>
