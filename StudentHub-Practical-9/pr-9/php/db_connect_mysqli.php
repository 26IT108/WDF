<?php
// Practical 9: MySQLi database connection for XAMPP defaults.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$host = 'localhost';
$dbname = 'studenthub';
$dbuser = 'root';
$dbpassword = '';

try {
    $conn = new mysqli($host, $dbuser, $dbpassword, $dbname);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $exception) {
    error_log('StudentHub MySQLi connection error: ' . $exception->getMessage());
    http_response_code(500);
    exit('Database connection failed. Start MySQL in XAMPP and import the Practical 9 database script.');
}
