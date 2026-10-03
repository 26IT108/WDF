let form = document.getElementById("registerForm");

let nameInput = document.getElementById("name");
let emailInput = document.getElementById("email");
let studentIdInput = document.getElementById("studentId");
let mobileInput = document.getElementById("mobile");
let courseInput = document.getElementById("course");
let yearInput = document.getElementById("year");
let passwordInput = document.getElementById("password");
let confirmPasswordInput = document.getElementById("confirmPassword");
let termsInput = document.getElementById("terms");


// Name validation
function checkName() {

    let name = nameInput.value.trim();
    let error = document.getElementById("nameError");

    let namePattern = /^[A-Za-z ]{2,50}$/;

    if (name === "") {
        error.textContent = "Name is required.";
        return false;
    }

    if (!namePattern.test(name)) {
        error.textContent = "Enter a valid name.";
        return false;
    }

    error.textContent = "";
    return true;
}


// Email validation
function checkEmail() {

    let email = emailInput.value.trim();
    let error = document.getElementById("emailError");

    let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (email === "") {
        error.textContent = "Email is required.";
        return false;
    }

    if (!emailPattern.test(email)) {
        error.textContent = "Enter a valid email.";
        return false;
    }

    error.textContent = "";
    return true;
}


// Student ID validation
function checkStudentId() {

    let studentId = studentIdInput.value.trim();
    let error = document.getElementById("studentIdError");

    if (studentId === "") {
        error.textContent = "Student ID is required.";
        return false;
    }

    error.textContent = "";
    return true;
}


// Mobile validation
function checkMobile() {

    let mobile = mobileInput.value.trim();
    let error = document.getElementById("mobileError");

    let mobilePattern = /^[6-9][0-9]{9}$/;

    if (mobile === "") {
        error.textContent = "Mobile number is required.";
        return false;
    }

    if (!mobilePattern.test(mobile)) {
        error.textContent = "Enter a valid 10 digit mobile number.";
        return false;
    }

    error.textContent = "";
    return true;
}


// Course validation
function checkCourse() {

    let error = document.getElementById("courseError");

    if (courseInput.value === "") {
        error.textContent = "Please select a course.";
        return false;
    }

    error.textContent = "";
    return true;
}


// Year validation
function checkYear() {

    let error = document.getElementById("yearError");

    if (yearInput.value === "") {
        error.textContent = "Please select your year.";
        return false;
    }

    error.textContent = "";
    return true;
}


// Gender validation
function checkGender() {

    let error = document.getElementById("genderError");

    let gender = document.querySelector(
        'input[name="gender"]:checked'
    );

    if (!gender) {
        error.textContent = "Please select your gender.";
        return false;
    }

    error.textContent = "";
    return true;
}


// Password validation
function checkPassword() {

    let password = passwordInput.value;
    let error = document.getElementById("passwordError");

    let passwordPattern =
        /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/;

    if (password === "") {
        error.textContent = "Password is required.";
        return false;
    }

    if (!passwordPattern.test(password)) {

        error.textContent =
            "Password must contain 8 characters, uppercase, lowercase, number and special character.";

        return false;
    }

    error.textContent = "";
    return true;
}


// Confirm password validation
function checkConfirmPassword() {

    let error = document.getElementById("confirmPasswordError");

    if (confirmPasswordInput.value === "") {
        error.textContent = "Please confirm your password.";
        return false;
    }

    if (confirmPasswordInput.value !== passwordInput.value) {
        error.textContent = "Passwords do not match.";
        return false;
    }

    error.textContent = "";
    return true;
}


// Terms validation
function checkTerms() {

    let error = document.getElementById("termsError");

    if (!termsInput.checked) {
        error.textContent =
            "You must agree to the Terms and Conditions.";

        return false;
    }

    error.textContent = "";
    return true;
}


// Password strength
function checkPasswordStrength() {

    let password = passwordInput.value;

    let strengthText =
        document.getElementById("passwordStrength");

    let strengthFill =
        document.getElementById("strengthFill");

    let score = 0;

    if (password.length >= 8) {
        score++;
    }

    if (/[A-Z]/.test(password)) {
        score++;
    }

    if (/[a-z]/.test(password)) {
        score++;
    }

    if (/[0-9]/.test(password)) {
        score++;
    }

    if (/[@$!%*?&]/.test(password)) {
        score++;
    }


    if (password === "") {

        strengthText.textContent = "";
        strengthFill.style.width = "0%";

    } else if (score <= 2) {

        strengthText.textContent = "Weak Password";
        strengthFill.style.width = "30%";

    } else if (score <= 4) {

        strengthText.textContent = "Medium Password";
        strengthFill.style.width = "65%";

    } else {

        strengthText.textContent = "Strong Password";
        strengthFill.style.width = "100%";
    }
}


// Real-time validation

nameInput.addEventListener("input", checkName);

emailInput.addEventListener("input", checkEmail);

studentIdInput.addEventListener("input", checkStudentId);

mobileInput.addEventListener("input", checkMobile);

courseInput.addEventListener("change", checkCourse);

yearInput.addEventListener("change", checkYear);

passwordInput.addEventListener("input", function() {

    checkPassword();

    checkPasswordStrength();

});

confirmPasswordInput.addEventListener(
    "input",
    checkConfirmPassword
);

termsInput.addEventListener("change", checkTerms);


// Form submit
form.addEventListener("submit", function(event) {

    let validName = checkName();
    let validEmail = checkEmail();
    let validStudentId = checkStudentId();
    let validMobile = checkMobile();
    let validCourse = checkCourse();
    let validYear = checkYear();
    let validGender = checkGender();
    let validPassword = checkPassword();
    let validConfirmPassword = checkConfirmPassword();
    let validTerms = checkTerms();


    if (
        !validName ||
        !validEmail ||
        !validStudentId ||
        !validMobile ||
        !validCourse ||
        !validYear ||
        !validGender ||
        !validPassword ||
        !validConfirmPassword ||
        !validTerms
    ) {

        event.preventDefault();

        document.getElementById("successMessage").textContent = "";

        return;
    }


    // Practical 7:
    // If JavaScript validation is successful, the form is
    // allowed to submit the data to the PHP file.

    document.getElementById("successMessage").textContent =
        "Submitting registration...";

});