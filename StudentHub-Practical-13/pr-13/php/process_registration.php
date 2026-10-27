<?php

// Practical 7 - PHP Form Processing
// StudentHub Registration

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request.");
}

// Get form data
$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$studentId = trim($_POST["studentId"] ?? "");
$mobile = trim($_POST["mobile"] ?? "");
$password = $_POST["password"] ?? "";
$confirmPassword = $_POST["confirmPassword"] ?? "";
$course = trim($_POST["course"] ?? "");
$year = trim($_POST["year"] ?? "");
$gender = trim($_POST["gender"] ?? "");
$terms = isset($_POST["terms"]);

$errors = array();

// Server-side validation
if ($name === "") {
    $errors[] = "Name is required.";
} elseif (!preg_match("/^[A-Za-z ]{2,50}$/", $name)) {
    $errors[] = "Enter a valid name.";
}

if ($email === "") {
    $errors[] = "Email is required.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Please enter a valid email address.";
}

if ($studentId === "") {
    $errors[] = "Student ID is required.";
}

if ($mobile === "") {
    $errors[] = "Mobile number is required.";
} elseif (!preg_match("/^[6-9][0-9]{9}$/", $mobile)) {
    $errors[] = "Please enter a valid 10-digit mobile number.";
}

if ($password === "") {
    $errors[] = "Password is required.";
} elseif (
    strlen($password) < 8 ||
    !preg_match("/[A-Z]/", $password) ||
    !preg_match("/[a-z]/", $password) ||
    !preg_match("/[0-9]/", $password) ||
    !preg_match("/[@$!%*?&]/", $password)
) {
    $errors[] = "Password must contain at least 8 characters, one uppercase letter, one lowercase letter, one number and one special character.";
}

if ($confirmPassword === "") {
    $errors[] = "Please confirm your password.";
} elseif ($password !== $confirmPassword) {
    $errors[] = "Passwords do not match.";
}

if ($course === "") {
    $errors[] = "Please select a course.";
}

if ($year === "") {
    $errors[] = "Please select your year.";
}

if ($gender === "") {
    $errors[] = "Please select your gender.";
}

if (!$terms) {
    $errors[] = "You must accept the Terms and Conditions.";
}

// Show validation errors
if (count($errors) > 0) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Registration Error</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <main class="form-container">
        <h2>Registration Failed</h2>
        <p>Please correct the following errors:</p>
        <ul class="error-list">
            <?php foreach ($errors as $error) { ?>
                <li><?php echo htmlspecialchars($error, ENT_QUOTES, "UTF-8"); ?></li>
            <?php } ?>
        </ul>
        <a href="../Pages/register.html">Go Back to Registration</a>
    </main>
</body>
</html>
<?php
    exit;
}

// Hash password before storing it
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Store registration data in CSV
$csvFile = __DIR__ . "/../data/registrations.csv";

if (!file_exists($csvFile)) {
    $file = fopen($csvFile, "w");

    if ($file === false) {
        die("Unable to create registration storage file.");
    }

    fputcsv($file, array(
        "Name",
        "Email",
        "Student ID",
        "Mobile",
        "Password",
        "Course",
        "Year",
        "Gender"
    ));

    fclose($file);
}

$file = fopen($csvFile, "a");

if ($file === false) {
    die("Unable to open registration storage file.");
}

fputcsv($file, array(
    $name,
    $email,
    $studentId,
    $mobile,
    $hashedPassword,
    $course,
    $year,
    $gender
));

fclose($file);

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
    <main class="form-container">
        <h2>Registration Successful!</h2>
        <p>Your StudentHub registration has been submitted successfully.</p>
        <p>Your registration information has been stored in the system.</p>
        <p><a href="../Pages/register.html">Back to Registration</a></p>
        <p><a href="view_registrations.php">View Registered Students</a></p>
    </main>
</body>
</html>
