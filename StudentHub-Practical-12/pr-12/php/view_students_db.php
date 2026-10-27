<?php

require "db_connect.php";

$sql = "SELECT
            students.id,
            students.name,
            students.email,
            students.mobile,
            courses.course_name,
            students.year,
            students.gender
        FROM students
        INNER JOIN courses
        ON students.course_id = courses.course_id";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Database Students</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<header>
    <div class="logo-area">
        <img src="../images/logo.png" alt="StudentHub Logo">
        <div>
            <h1>StudentHub</h1>
            <p>Your Student Portal</p>
        </div>
    </div>
</header>

<nav>
    <a href="../Pages/index.html">Home</a>
    <a href="../Pages/login.html">Login</a>
    <a href="../Pages/register.html">Register</a>
    <a href="../Pages/dashboard.html">Dashboard</a>
    <a href="../Pages/attendance.html">Attendance</a>
    <a href="../Pages/assignment.html">Assignment</a>
    <a href="../Pages/events.html">Events</a>
    <a href="../Pages/students.html">Students</a>
    <a href="../Pages/profile.html">Profile</a>
    <a href="../Pages/faq.html">FAQ</a>
    <a href="../Pages/contact.html">Contact</a>
</nav>

<main>
    <section class="registration-table-section">
        <h2>Registered Students - MySQL</h2>

        <?php if (count($students) === 0) { ?>
            <p>No student records found.</p>
        <?php } else { ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>Course</th>
                            <th>Year</th>
                            <th>Gender</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($students as $student) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student["id"], ENT_QUOTES, "UTF-8"); ?></td>
                            <td><?php echo htmlspecialchars($student["name"], ENT_QUOTES, "UTF-8"); ?></td>
                            <td><?php echo htmlspecialchars($student["email"], ENT_QUOTES, "UTF-8"); ?></td>
                            <td><?php echo htmlspecialchars($student["mobile"], ENT_QUOTES, "UTF-8"); ?></td>
                            <td><?php echo htmlspecialchars($student["course_name"], ENT_QUOTES, "UTF-8"); ?></td>
                            <td><?php echo htmlspecialchars($student["year"], ENT_QUOTES, "UTF-8"); ?></td>
                            <td><?php echo htmlspecialchars($student["gender"], ENT_QUOTES, "UTF-8"); ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>

        <p class="registration-back-link">
            <a href="../Pages/register_db.html">Back to Database Registration</a>
        </p>
    </section>
</main>

<footer>
    <p>&copy; 2026 StudentHub. All rights reserved.</p>
</footer>

</body>
</html>
