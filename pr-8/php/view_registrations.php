<?php

// Practical 7 - View Stored Registrations

$csvFile = __DIR__ . "/../data/registrations.csv";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Registered Students</title>
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
    <section class="form-container registration-table-section">
        <h2>Registered Students</h2>

        <?php if (!file_exists($csvFile)) { ?>
            <p>No registration records found.</p>
        <?php } else {
            $file = fopen($csvFile, "r");

            if ($file === false) {
                echo "<p>Unable to open registration file.</p>";
            } else {
        ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Student ID</th>
                        <th>Mobile</th>
                        <th>Course</th>
                        <th>Year</th>
                        <th>Gender</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                // Skip CSV header
                fgetcsv($file);

                while (($row = fgetcsv($file)) !== false) {
                    if (count($row) < 8) {
                        continue;
                    }
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row[0], ENT_QUOTES, "UTF-8"); ?></td>
                        <td><?php echo htmlspecialchars($row[1], ENT_QUOTES, "UTF-8"); ?></td>
                        <td><?php echo htmlspecialchars($row[2], ENT_QUOTES, "UTF-8"); ?></td>
                        <td><?php echo htmlspecialchars($row[3], ENT_QUOTES, "UTF-8"); ?></td>
                        <td><?php echo htmlspecialchars($row[5], ENT_QUOTES, "UTF-8"); ?></td>
                        <td><?php echo htmlspecialchars($row[6], ENT_QUOTES, "UTF-8"); ?></td>
                        <td><?php echo htmlspecialchars($row[7], ENT_QUOTES, "UTF-8"); ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>

        <?php
                fclose($file);
            }
        }
        ?>

        <p class="registration-back-link">
            <a href="../Pages/register.html">Back to Registration</a>
        </p>
    </section>
</main>

<footer>
    <p>&copy; 2026 StudentHub. All rights reserved.</p>
</footer>

</body>
</html>
