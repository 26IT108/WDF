<?php
require_once __DIR__ . '/../php/auth.php';
require_login();
if (($_SESSION['role'] ?? 'student') === 'admin') { header('Location: admin_dashboard.php'); }
else { header('Location: student_dashboard.php'); }
exit;
