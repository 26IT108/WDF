<?php
require_once __DIR__ . '/../php/auth.php';
require_role('admin');
require_once __DIR__ . '/../php/db_connect_mysqli.php';

// Ensure the dashboard's first visit and important admin navigation are traceable.
audit_log('admin_dashboard_view', 'Admin opened the dashboard');
function dash_count(mysqli $conn, string $sql): int {
    try { $result = $conn->query($sql); $row = $result->fetch_row(); return (int)($row[0] ?? 0); }
    catch (Throwable $e) { error_log('Dashboard statistic query failed: ' . $e->getMessage()); return 0; }
}
$userCount = dash_count($conn, "SELECT COUNT(*) FROM users");
$studentCount = dash_count($conn, "SELECT COUNT(*) FROM students");
$eventCount = dash_count($conn, "SELECT COUNT(*) FROM events");
$adminCount = dash_count($conn, "SELECT COUNT(*) FROM users WHERE role = 'admin'");
$latestUsers = [];
$recentLogs = [];
try { $latestUsers = $conn->query('SELECT id, full_name, email, role, created_at FROM users ORDER BY created_at DESC, id DESC LIMIT 5')->fetch_all(MYSQLI_ASSOC); } catch (Throwable $e) { error_log('Latest registrations query failed: ' . $e->getMessage()); }
try { $recentLogs = $conn->query('SELECT al.action, al.details, al.created_at, u.full_name FROM audit_logs al LEFT JOIN users u ON u.id = al.user_id ORDER BY al.id DESC LIMIT 8')->fetch_all(MYSQLI_ASSOC); } catch (Throwable $e) { error_log('Audit log query failed: ' . $e->getMessage()); }
$conn->close();
function de(mixed $value): string { return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Admin Dashboard - StudentHub</title><link rel="stylesheet" href="../css/style.css"><style>
.dashboard-card{max-width:1120px;margin:30px auto;padding:28px;background:#fff;border-radius:10px;box-shadow:0 4px 14px #00000012}.dashboard-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:15px;margin:22px 0}.dashboard-tile{padding:18px;border:1px solid #d9e8d9;border-radius:8px;background:#f7fbf7}.stat-number{font-size:2rem;font-weight:700;color:#1b5e20;margin:8px 0}.dashboard-card button,.btn{background:#1b5e20;color:white;border:0;padding:10px 18px;border-radius:5px;cursor:pointer;text-decoration:none;display:inline-block}.section-panel{margin-top:24px;padding:18px;border:1px solid #e2e9e2;border-radius:8px;overflow-x:auto}table{width:100%;border-collapse:collapse;min-width:580px}th,td{text-align:left;padding:10px;border-bottom:1px solid #e5eae5}th{background:#f0f7f0}.role-pill{display:inline-block;background:#e8f5e9;color:#1b5e20;padding:3px 8px;border-radius:20px;font-size:.85rem}.muted{color:#607060}.dashboard-nav{display:flex;gap:14px;flex-wrap:wrap}@media(max-width:600px){.dashboard-card{margin:15px;padding:18px}}
</style></head><body>
<header><div class="logo-area"><img src="../images/logo.png" alt="StudentHub Logo"><div><h1>StudentHub</h1><p>Your Student Portal</p></div></div></header>
<nav aria-label="Main Navigation" class="dashboard-nav"><a href="index.html">Home</a><a href="dashboard.php">Dashboard</a><a href="admin_dashboard.php" aria-current="page">Admin</a><a href="students_manage.php">Students</a><a href="events_manage.php">Events</a><a href="events_api_crud.php">Events API</a><a href="faq.html">FAQ</a></nav>
<main><section class="dashboard-card"><h2>Admin Dashboard</h2><p>Live overview of the StudentHub portal and recent activity.</p><p>Welcome, <strong><?= de($_SESSION['full_name'] ?? 'Admin') ?></strong>! <span class="role-pill">Administrator</span></p>
<div class="dashboard-grid"><div class="dashboard-tile"><h3>Total Users</h3><p class="stat-number"><?= $userCount ?></p><p class="muted"><?= $adminCount ?> admin account(s)</p></div><div class="dashboard-tile"><h3>Students</h3><p class="stat-number"><?= $studentCount ?></p><a href="students_manage.php">Manage students</a></div><div class="dashboard-tile"><h3>Events</h3><p class="stat-number"><?= $eventCount ?></p><a href="events_manage.php">Manage events</a></div><div class="dashboard-tile"><h3>Admin Tools</h3><p>Review records and event activity.</p><a href="events_api_crud.php">Open Events API CRUD</a></div></div>
<div class="dashboard-grid"><div class="dashboard-tile"><h3>Student Management</h3><p>Add, search, edit, filter, and delete student records.</p><a href="students_manage.php">Manage students</a></div><div class="dashboard-tile"><h3>Event Management</h3><p>Manage event details, status, and posters.</p><a href="events_manage.php">Manage events</a> · <a href="events.html">View events</a></div><div class="dashboard-tile"><h3>FAQs</h3><p>Find portal help and answers.</p><a href="faq.html">Read FAQs</a></div><div class="dashboard-tile"><h3>Profile</h3><p>View the profile page.</p><a href="profile.html">Open profile</a></div></div>
<section class="section-panel"><h3>Latest Registrations</h3><table><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Registered</th></tr></thead><tbody><?php if (!$latestUsers): ?><tr><td colspan="4">No registrations found.</td></tr><?php else: foreach ($latestUsers as $user): ?><tr><td><?= de($user['full_name']) ?></td><td><?= de($user['email']) ?></td><td><?= de($user['role']) ?></td><td><?= de($user['created_at']) ?></td></tr><?php endforeach; endif; ?></tbody></table></section>
<section class="section-panel"><h3>Recent Audit Activity</h3><p class="muted">Important admin actions and dashboard activity.</p><table><thead><tr><th>Who</th><th>Action</th><th>Details</th><th>Time</th></tr></thead><tbody><?php if (!$recentLogs): ?><tr><td colspan="4">No audit entries yet. Actions will appear here after the audit log migration is applied.</td></tr><?php else: foreach ($recentLogs as $log): ?><tr><td><?= de($log['full_name'] ?? 'Deleted user') ?></td><td><?= de($log['action']) ?></td><td><?= de($log['details']) ?></td><td><?= de($log['created_at']) ?></td></tr><?php endforeach; endif; ?></tbody></table></section>
<form action="../php/logout.php" method="post" style="margin-top:24px"><input type="hidden" name="csrf_token" value="<?= de(csrf_token()) ?>"><button type="submit">Logout</button></form></section></main><footer><p>&copy; 2026 StudentHub. All rights reserved.</p></footer></body></html>
