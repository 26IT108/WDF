// ==============================
// DARK / LIGHT MODE
// ==============================

let themeButton = document.getElementById("themeButton");

if (themeButton) {

    themeButton.addEventListener("click", function () {

        document.body.classList.toggle("dark-mode");

        if (document.body.classList.contains("dark-mode")) {
            localStorage.setItem("theme", "dark");
            themeButton.textContent = "☀️ Light Mode";
        } else {
            localStorage.setItem("theme", "light");
            themeButton.textContent = "🌙 Dark Mode";
        }

    });

}

// Load saved theme

let savedTheme = localStorage.getItem("theme");

if (savedTheme === "dark") {
    document.body.classList.add("dark-mode");

    if (themeButton) {
        themeButton.textContent = "☀️ Light Mode";
    }
}


// ==============================
// FAQ
// ==============================
let faqButtons = document.querySelectorAll(".faq-button");

faqButtons.forEach(function(button) {

    button.addEventListener("click", function() {

        let answer = button.nextElementSibling;

        if (answer && answer.classList.contains("faq-answer")) {
            const isOpen = answer.style.display === "block";

            faqButtons.forEach(function(btn) {
                const item = btn.nextElementSibling;

                if (item && item.classList.contains("faq-answer")) {
                    item.style.display = "none";
                }
            });

            if (!isOpen) {
                answer.style.display = "block";
            }
        }

    });

});





// ////////////////////
// let faqButtons = document.querySelectorAll(".faq-button");

// faqButtons.forEach(function(button) {

//     button.addEventListener("click", function() {

//         let answer = button.nextElementSibling;

//         if (answer.style.display === "block") {
//             answer.style.display = "none";
//         } else {
//             answer.style.display = "block";
//         }

//     });

// });


// ==============================
// NOTIFICATION
// ==============================

let notificationButton =
    document.getElementById("notificationButton");

let notification =
    document.getElementById("notification");

if (notificationButton && notification) {

    notificationButton.addEventListener("click", function() {

        notification.style.display = "block";

    });

}


// ==============================
// MODAL
// ==============================

let modalButton = document.getElementById("modalButton");

let modal = document.getElementById("modal");

let closeButton = document.getElementById("closeButton");

if (modalButton && modal) {

    modalButton.addEventListener("click", function() {

        modal.style.display = "block";

    });

}

if (closeButton && modal) {

    closeButton.addEventListener("click", function() {

        modal.style.display = "none";

    });

}


// ==============================s
// HAMBURGER MENU
// ==============================

let menuButton = document.getElementById("menuButton");

let navigation = document.getElementById("navigation");

if (menuButton && navigation) {

    menuButton.addEventListener("click", function() {

        navigation.classList.toggle("show-menu");

    });

}