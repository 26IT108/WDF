<?php

require "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request.");
}

$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$mobile = trim($_POST["mobile"] ?? "");
$course_id = $_POST["course_id"] ?? "";
$year = $_POST["year"] ?? "";
$gender = $_POST["gender"] ?? "";

$errors = array();

if ($name === "") {
    $errors[] = "Name is required.";
} elseif (!preg_match("/^[A-Za-z ]{2,100}$/", $name)) {
    $errors[] = "Enter a valid name.";
}

if ($email === "") {
    $errors[] = "Email is required.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Please enter a valid email address.";
}

if ($mobile === "") {
    $errors[] = "Mobile number is required.";
} elseif (!preg_match("/^[6-9][0-9]{9}$/", $mobile)) {
    $errors[] = "Please enter a valid 10-digit mobile number.";
}

if ($course_id === "" || !filter_var($course_id, FILTER_VALIDATE_INT)) {
    $errors[] = "Please select a course.";
}

if ($year === "" || !filter_var($year, FILTER_VALIDATE_INT) || $year < 1 || $year > 4) {
    $errors[] = "Please select a valid year.";
}

if ($gender === "") {
    $errors[] = "Please select your gender.";
}

if (count($errors) > 0) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>StudentHub - Database Registration Error</title>
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
        <main>
            <section class="form-container">
                <h2>Registration Failed</h2>
                <p>Please correct the following errors:</p>
                <ul class="error-list">
                    <?php foreach ($errors as $error) { ?>
                        <li><?php echo htmlspecialchars($error, ENT_QUOTES, "UTF-8"); ?></li>
                    <?php } ?>
                </ul>
                <p><a href="../Pages/register_db.html">Go Back to Database Registration</a></p>
            </section>
        </main>
        <footer>
            <p>&copy; 2026 StudentHub. All rights reserved.</p>
        </footer>
    </body>
    </html>
    <?php
    exit;
}

$sql = "INSERT INTO students
        (name, email, mobile, course_id, year, gender)
        VALUES
        (:name, :email, :mobile, :course_id, :year, :gender)";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":name" => $name,
    ":email" => $email,
    ":mobile" => $mobile,
    ":course_id" => $course_id,
    ":year" => $year,
    ":gender" => $gender
]);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Registration Successful</title>
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

<main>
    <section class="form-container">
        <h2>Registration Successful!</h2>
        <p>Student data has been saved to MySQL.</p>
        <p><a href="../Pages/register_db.html">Register Another Student</a></p>
        <p><a href="view_students_db.php">View Students from Database</a></p>
    </section>
</main>

<footer>
    <p>&copy; 2026 StudentHub. All rights reserved.</p>
</footer>

</body>
</html>
