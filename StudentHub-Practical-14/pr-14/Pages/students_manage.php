<?php
require_once __DIR__ . '/../php/auth.php';
require_role('admin');
require_once __DIR__ . '/../php/db_connect_mysqli.php';

$message = '';
$messageType = 'success';
$editStudent = null;

function student_redirect(string $message, string $type = 'success'): never {
    header('Location: students_manage.php?message=' . rawurlencode($message) . '&type=' . rawurlencode($type));
    exit;
}

if (isset($_GET['message'])) {
    $message = (string)$_GET['message'];
    $messageType = (($_GET['type'] ?? 'success') === 'error') ? 'error' : 'success';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid()) {
        student_redirect('Your session token was invalid. Please try again.', 'error');
    }

    $action = (string)($_POST['action'] ?? '');
    if ($action === 'delete') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$id || $id < 1) student_redirect('Invalid student record.', 'error');
        $stmt = $conn->prepare('DELETE FROM students WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $deleted = $stmt->affected_rows;
        $stmt->close();
        if ($deleted) audit_log('student_delete', 'Deleted student record ID ' . (int)$id);
        student_redirect($deleted ? 'Student record deleted successfully.' : 'Student record was not found.', $deleted ? 'success' : 'error');
    }

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim((string)($_POST['name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $mobile = trim((string)($_POST['mobile'] ?? ''));
        $courseIdRaw = trim((string)($_POST['course_id'] ?? ''));
        $yearRaw = trim((string)($_POST['year'] ?? ''));
        $gender = trim((string)($_POST['gender'] ?? ''));

        if ($name === '' || mb_strlen($name) > 100 || !preg_match("/^[\p{L}\p{M} .'-]+$/u", $name)) {
            student_redirect('Enter a valid student name (letters, spaces, apostrophes, dots, or hyphens).', 'error');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
            student_redirect('Enter a valid email address.', 'error');
        }
        if (!preg_match('/^[0-9+() .-]{7,15}$/', $mobile)) {
            student_redirect('Enter a valid mobile number (7–15 characters).', 'error');
        }
        if ($courseIdRaw === '' || !ctype_digit($courseIdRaw)) {
            student_redirect('Please select a course.', 'error');
        }
        $courseId = (int)$courseIdRaw;
        if ($yearRaw === '' || !ctype_digit($yearRaw) || (int)$yearRaw < 1 || (int)$yearRaw > 6) {
            student_redirect('Select a valid study year (1–6).', 'error');
        }
        $year = (int)$yearRaw;
        $allowedGenders = ['Female', 'Male', 'Other', 'Prefer not to say'];
        if (!in_array($gender, $allowedGenders, true)) {
            student_redirect('Please select a valid gender option.', 'error');
        }

        $courseCheck = $conn->prepare('SELECT course_id FROM courses WHERE course_id = ?');
        $courseCheck->bind_param('i', $courseId);
        $courseCheck->execute();
        $courseExists = $courseCheck->get_result()->num_rows > 0;
        $courseCheck->close();
        if (!$courseExists) student_redirect('The selected course does not exist.', 'error');

        $duplicate = $conn->prepare('SELECT id FROM students WHERE email = ? AND id <> ? LIMIT 1');
        $duplicate->bind_param('si', $email, $id);
        $duplicate->execute();
        $hasDuplicate = $duplicate->get_result()->num_rows > 0;
        $duplicate->close();
        if ($hasDuplicate) student_redirect('A student with this email already exists.', 'error');

        if ($id > 0) {
            $stmt = $conn->prepare('UPDATE students SET name = ?, email = ?, mobile = ?, course_id = ?, year = ?, gender = ? WHERE id = ?');
            $stmt->bind_param('sssiisi', $name, $email, $mobile, $courseId, $year, $gender, $id);
            $stmt->execute();
            $changed = $stmt->affected_rows;
            $stmt->close();
            audit_log('student_update', 'Updated student record ID ' . $id);
            student_redirect($changed >= 0 ? 'Student record updated successfully.' : 'Unable to update student record.', $changed >= 0 ? 'success' : 'error');
        } else {
            $stmt = $conn->prepare('INSERT INTO students (name, email, mobile, course_id, year, gender) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('sssiis', $name, $email, $mobile, $courseId, $year, $gender);
            $stmt->execute();
            $newStudentId = (int)$stmt->insert_id;
            $stmt->close();
            audit_log('student_create', 'Created student record ID ' . $newStudentId);
            student_redirect('Student added successfully.');
        }
    }
    student_redirect('Unknown action. Please try again.', 'error');
}

if (isset($_GET['edit'])) {
    $editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
    if ($editId && $editId > 0) {
        $stmt = $conn->prepare('SELECT id, name, email, mobile, course_id, year, gender FROM students WHERE id = ?');
        $stmt->bind_param('i', $editId);
        $stmt->execute();
        $editStudent = $stmt->get_result()->fetch_assoc() ?: null;
        $stmt->close();
        if (!$editStudent) { $message = 'Student record not found.'; $messageType = 'error'; }
    }
}

$courses = $conn->query('SELECT course_id, course_name FROM courses ORDER BY course_name')->fetch_all(MYSQLI_ASSOC);
$search = trim((string)($_GET['search'] ?? ''));
$courseFilter = trim((string)($_GET['course'] ?? ''));
$sql = 'SELECT s.id, s.name, s.email, s.mobile, s.year, s.gender, s.course_id, c.course_name FROM students s LEFT JOIN courses c ON c.course_id = s.course_id WHERE 1=1';
$types = '';
$params = [];
if ($search !== '') {
    $sql .= ' AND (s.name LIKE ? OR s.email LIKE ? OR c.course_name LIKE ?)';
    $like = '%' . $search . '%';
    $types .= 'sss'; array_push($params, $like, $like, $like);
}
if ($courseFilter !== '' && ctype_digit($courseFilter)) {
    $sql .= ' AND s.course_id = ?'; $types .= 'i'; $params[] = (int)$courseFilter;
}
$sql .= ' ORDER BY s.id DESC';
$stmt = $conn->prepare($sql);
if ($types !== '') $stmt->bind_param($types, ...$params);
$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
function e(mixed $value): string { return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Management - StudentHub</title><link rel="stylesheet" href="../css/style.css">
<style>
.management-wrap{max-width:1180px;margin:30px auto;padding:0 18px}.management-panel{background:#fff;border-radius:12px;padding:24px;margin-bottom:22px;box-shadow:0 4px 14px #00000012}.management-panel h2,.management-panel h3{color:#1b5e20;margin-top:0}.management-form{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:14px}.management-form label{display:block;font-weight:600;margin-bottom:5px}.management-form input,.management-form select,.search-form input,.search-form select{width:100%;padding:10px;border:1px solid #cbd5cb;border-radius:6px;font:inherit}.management-actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-top:16px}.btn{display:inline-block;border:0;border-radius:6px;padding:10px 16px;background:#1b5e20;color:#fff;text-decoration:none;font:inherit;cursor:pointer}.btn-secondary{background:#455a64}.btn-danger{background:#b3261e}.btn-small{padding:6px 10px;font-size:.9rem}.notice{padding:12px 15px;border-radius:7px;margin-bottom:18px}.notice.success{background:#e8f5e9;color:#1b5e20}.notice.error{background:#ffebee;color:#b71c1c}.search-form{display:grid;grid-template-columns:minmax(200px,1fr) minmax(180px,260px) auto auto;gap:10px;align-items:center}.table-scroll{overflow-x:auto}table{width:100%;border-collapse:collapse;min-width:760px}th,td{text-align:left;padding:11px 10px;border-bottom:1px solid #e4e9e4}th{background:#f0f7f0;color:#204b24}td form{display:inline}.empty-state{padding:22px;text-align:center;color:#607060}@media(max-width:650px){.management-panel{padding:16px}.search-form{grid-template-columns:1fr}}
</style>
</head>
<body>
<header><div class="logo-area"><img src="../images/logo.png" alt="StudentHub Logo"><div><h1>StudentHub</h1><p>Your Student Portal</p></div></div></header>
<nav aria-label="Main Navigation"><a href="index.html">Home</a><a href="dashboard.php">Dashboard</a><a href="admin_dashboard.php">Admin</a><a href="events.html">Events</a><a href="faq.html">FAQ</a></nav>
<main class="management-wrap">
<section class="management-panel"><h2>Student Management</h2><p>Add, view, update, delete, search, and filter student records.</p>
<?php if ($message !== ''): ?><div class="notice <?= e($messageType) ?>" role="status"><?= e($message) ?></div><?php endif; ?>
<h3><?= $editStudent ? 'Edit Student' : 'Add New Student' ?></h3>
<form method="post" action="students_manage.php" class="management-form">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= e($editStudent['id'] ?? 0) ?>">
<div><label for="name">Full name *</label><input id="name" name="name" maxlength="100" required pattern="[A-Za-zÀ-ÿ .'-]+" value="<?= e($editStudent['name'] ?? '') ?>"></div>
<div><label for="email">Email *</label><input id="email" type="email" name="email" maxlength="150" required value="<?= e($editStudent['email'] ?? '') ?>"></div>
<div><label for="mobile">Mobile *</label><input id="mobile" name="mobile" maxlength="15" minlength="7" pattern="[0-9+() .-]{7,15}" required value="<?= e($editStudent['mobile'] ?? '') ?>"></div>
<div><label for="course_id">Course *</label><select id="course_id" name="course_id" required><option value="">Select course</option><?php foreach ($courses as $course): ?><option value="<?= (int)$course['course_id'] ?>" <?= (string)($editStudent['course_id'] ?? '') === (string)$course['course_id'] ? 'selected' : '' ?>><?= e($course['course_name']) ?></option><?php endforeach; ?></select></div>
<div><label for="year">Study year *</label><select id="year" name="year" required><option value="">Select year</option><?php for ($y=1;$y<=6;$y++): ?><option value="<?= $y ?>" <?= (string)($editStudent['year'] ?? '') === (string)$y ? 'selected' : '' ?>>Year <?= $y ?></option><?php endfor; ?></select></div>
<div><label for="gender">Gender *</label><select id="gender" name="gender" required><option value="">Select option</option><?php foreach (['Female','Male','Other','Prefer not to say'] as $g): ?><option value="<?= e($g) ?>" <?= ($editStudent['gender'] ?? '') === $g ? 'selected' : '' ?>><?= e($g) ?></option><?php endforeach; ?></select></div>
</div>
<div class="management-actions"><button class="btn" type="submit"><?= $editStudent ? 'Save Changes' : 'Add Student' ?></button><?php if ($editStudent): ?><a class="btn btn-secondary" href="students_manage.php">Cancel Edit</a><?php endif; ?></div>
</form>
</section>
<section class="management-panel"><h3>Search and Filter Students</h3><form class="search-form" method="get" action="students_manage.php"><input type="search" name="search" placeholder="Search name, email, or course" value="<?= e($search) ?>" aria-label="Search name email or course"><select name="course" aria-label="Filter by course"><option value="">All courses</option><?php foreach ($courses as $course): ?><option value="<?= (int)$course['course_id'] ?>" <?= $courseFilter === (string)$course['course_id'] ? 'selected' : '' ?>><?= e($course['course_name']) ?></option><?php endforeach; ?></select><button class="btn" type="submit">Search / Filter</button><a class="btn btn-secondary" href="students_manage.php">Reset</a></form></section>
<section class="management-panel"><h3>Student Records (<?= count($students) ?>)</h3><div class="table-scroll"><table><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Mobile</th><th>Course</th><th>Year</th><th>Gender</th><th>Actions</th></tr></thead><tbody>
<?php if (!$students): ?><tr><td colspan="8" class="empty-state">No student records found. Add a student or change your search filters.</td></tr><?php else: foreach ($students as $student): ?><tr><td><?= (int)$student['id'] ?></td><td><?= e($student['name']) ?></td><td><?= e($student['email']) ?></td><td><?= e($student['mobile']) ?></td><td><?= e($student['course_name'] ?? '—') ?></td><td><?= e($student['year'] ?? '—') ?></td><td><?= e($student['gender'] ?? '—') ?></td><td><a class="btn btn-small" href="students_manage.php?edit=<?= (int)$student['id'] ?>">Edit</a><form method="post" action="students_manage.php" onsubmit="return confirm('Are you sure you want to permanently delete this student record?');"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$student['id'] ?>"><button class="btn btn-danger btn-small" type="submit">Delete</button></form></td></tr><?php endforeach; endif; ?>
</tbody></table></div></section>
<form action="../php/logout.php" method="post" style="text-align:right;margin:0 0 24px"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><button class="btn btn-secondary" type="submit">Logout</button></form>
</main><footer><p>&copy; 2026 StudentHub. All rights reserved.</p></footer>
</body></html>
