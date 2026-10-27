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
// ==============================
// PRACTICAL 6 - JSON AND FETCH API
// ==============================

// ------------------------------
// EVENTS
// ------------------------------

let eventsData = [];
let eventCurrentPage = 1;
let eventItemsPerPage = 5;

let eventSearch = document.getElementById("eventSearch");
let eventCategory = document.getElementById("eventCategory");
let eventSort = document.getElementById("eventSort");
let eventContainer = document.getElementById("eventList");
let eventLoading = document.getElementById("eventLoading");
let eventError = document.getElementById("eventError");
let eventPrevious = document.getElementById("eventPrev");
let eventNext = document.getElementById("eventNext");
let eventPageText = document.getElementById("eventPage");

function showEvents() {

    if (!eventContainer) {
        return;
    }

    let searchText = eventSearch.value.toLowerCase();
    let selectedCategory = eventCategory.value;

    let filteredEvents = eventsData.filter(function(event) {

        let matchesSearch =
            event.title.toLowerCase().includes(searchText);

        let matchesCategory =
            selectedCategory === "all" ||
            event.category === selectedCategory;

        return matchesSearch && matchesCategory;
    });

    if (eventSort.value === "az") {
        filteredEvents.sort(function(a, b) {
            return a.title.localeCompare(b.title);
        });
    }

    if (eventSort.value === "za") {
        filteredEvents.sort(function(a, b) {
            return b.title.localeCompare(a.title);
        });
    }

    if (eventSort.value === "dateAsc") {
        filteredEvents.sort(function(a, b) {
            return new Date(a.date) - new Date(b.date);
        });
    }

    if (eventSort.value === "dateDesc") {
        filteredEvents.sort(function(a, b) {
            return new Date(b.date) - new Date(a.date);
        });
    }

    let totalPages =
        Math.ceil(filteredEvents.length / eventItemsPerPage);

    if (totalPages === 0) {
        totalPages = 1;
    }

    if (eventCurrentPage > totalPages) {
        eventCurrentPage = totalPages;
    }

    let start =
        (eventCurrentPage - 1) * eventItemsPerPage;

    let pageEvents =
        filteredEvents.slice(start, start + eventItemsPerPage);

    eventContainer.innerHTML = "";

    if (pageEvents.length === 0) {
        eventContainer.innerHTML =
            "<p>No events found.</p>";
    }

    pageEvents.forEach(function(event) {

        let article = document.createElement("article");

        article.innerHTML =
            "<h3>" + event.title + "</h3>" +
            "<p><strong>Date:</strong> " + event.date + "</p>" +
            "<p><strong>Location:</strong> " + event.location + "</p>" +
            "<p><strong>Category:</strong> " + event.category + "</p>";

        eventContainer.appendChild(article);
    });

    eventPageText.textContent =
        "Page " + eventCurrentPage + " of " + totalPages;

    eventPrevious.disabled = eventCurrentPage === 1;
    eventNext.disabled = eventCurrentPage === totalPages;
}

if (eventContainer) {

    fetch("../data/events.json")
        .then(function(response) {

            if (!response.ok) {
                throw new Error("Events data could not be loaded.");
            }

            return response.json();
        })
        .then(function(data) {

            eventsData = data;
            eventLoading.style.display = "none";
            showEvents();

        })
        .catch(function(error) {

            eventLoading.style.display = "none";
            eventError.textContent =
                "Unable to load events. Please try again.";

            console.log(error);
        });

    eventSearch.addEventListener("input", function() {
        eventCurrentPage = 1;
        showEvents();
    });

    eventCategory.addEventListener("change", function() {
        eventCurrentPage = 1;
        showEvents();
    });

    eventSort.addEventListener("change", function() {
        eventCurrentPage = 1;
        showEvents();
    });

    eventPrevious.addEventListener("click", function() {

        if (eventCurrentPage > 1) {
            eventCurrentPage--;
            showEvents();
        }

    });

    eventNext.addEventListener("click", function() {

        eventCurrentPage++;
        showEvents();

    });
}


// ------------------------------
// STUDENTS
// ------------------------------

let studentsData = [];
let studentCurrentPage = 1;
let studentItemsPerPage = 5;

let studentSearch = document.getElementById("studentSearch");
let studentCourse = document.getElementById("studentCourse");
let studentYear = document.getElementById("studentYear");
let studentSort = document.getElementById("studentSort");
let studentContainer = document.getElementById("studentList");
let studentLoading = document.getElementById("studentLoading");
let studentError = document.getElementById("studentError");
let studentPrevious = document.getElementById("studentPrev");
let studentNext = document.getElementById("studentNext");
let studentPageText = document.getElementById("studentPage");

function showStudents() {

    if (!studentContainer) {
        return;
    }

    let searchText = studentSearch.value.toLowerCase();
    let selectedCourse = studentCourse.value;
    let selectedYear = studentYear.value;

    let filteredStudents = studentsData.filter(function(student) {

        let matchesSearch =
            student.name.toLowerCase().includes(searchText);

        let matchesCourse =
            selectedCourse === "all" ||
            student.course === selectedCourse;

        let matchesYear =
            selectedYear === "all" ||
            student.year.toString() === selectedYear;

        return matchesSearch && matchesCourse && matchesYear;
    });

    if (studentSort.value === "az") {
        filteredStudents.sort(function(a, b) {
            return a.name.localeCompare(b.name);
        });
    }

    if (studentSort.value === "za") {
        filteredStudents.sort(function(a, b) {
            return b.name.localeCompare(a.name);
        });
    }

    if (studentSort.value === "yearAsc") {
        filteredStudents.sort(function(a, b) {
            return a.year - b.year;
        });
    }

    if (studentSort.value === "yearDesc") {
        filteredStudents.sort(function(a, b) {
            return b.year - a.year;
        });
    }

    let totalPages =
        Math.ceil(filteredStudents.length / studentItemsPerPage);

    if (totalPages === 0) {
        totalPages = 1;
    }

    if (studentCurrentPage > totalPages) {
        studentCurrentPage = totalPages;
    }

    let start =
        (studentCurrentPage - 1) * studentItemsPerPage;

    let pageStudents =
        filteredStudents.slice(start, start + studentItemsPerPage);

    studentContainer.innerHTML = "";

    if (pageStudents.length === 0) {
        studentContainer.innerHTML =
            "<p>No students found.</p>";
    }

    pageStudents.forEach(function(student) {

        let article = document.createElement("article");

        article.innerHTML =
            "<h3>" + student.name + "</h3>" +
            "<p><strong>Email:</strong> " + student.email + "</p>" +
            "<p><strong>Course:</strong> " + student.course + "</p>" +
            "<p><strong>Year:</strong> " + student.year + "</p>";

        studentContainer.appendChild(article);
    });

    studentPageText.textContent =
        "Page " + studentCurrentPage + " of " + totalPages;

    studentPrevious.disabled = studentCurrentPage === 1;
    studentNext.disabled = studentCurrentPage === totalPages;
}

if (studentContainer) {

    fetch("../data/students.json")
        .then(function(response) {

            if (!response.ok) {
                throw new Error("Students data could not be loaded.");
            }

            return response.json();
        })
        .then(function(data) {

            studentsData = data;
            studentLoading.style.display = "none";
            showStudents();

        })
        .catch(function(error) {

            studentLoading.style.display = "none";
            studentError.textContent =
                "Unable to load students. Please try again.";

            console.log(error);
        });

    studentSearch.addEventListener("input", function() {
        studentCurrentPage = 1;
        showStudents();
    });

    studentCourse.addEventListener("change", function() {
        studentCurrentPage = 1;
        showStudents();
    });

    studentYear.addEventListener("change", function() {
        studentCurrentPage = 1;
        showStudents();
    });

    studentSort.addEventListener("change", function() {
        studentCurrentPage = 1;
        showStudents();
    });

    studentPrevious.addEventListener("click", function() {

        if (studentCurrentPage > 1) {
            studentCurrentPage--;
            showStudents();
        }

    });

    studentNext.addEventListener("click", function() {

        studentCurrentPage++;
        showStudents();

    });
}


// ------------------------------
// FAQ
// ------------------------------

let faqData = [];
let faqCurrentPage = 1;
let faqItemsPerPage = 5;

let faqSearch = document.getElementById("faqSearch");
let faqSort = document.getElementById("faqSort");
let faqContainer = document.getElementById("faqList");
let faqLoading = document.getElementById("faqLoading");
let faqError = document.getElementById("faqError");
let faqPrevious = document.getElementById("faqPrev");
let faqNext = document.getElementById("faqNext");
let faqPageText = document.getElementById("faqPage");

function showFAQs() {

    if (!faqContainer) {
        return;
    }

    let searchText = faqSearch.value.toLowerCase();

    let filteredFAQs = faqData.filter(function(faq) {

        return faq.question.toLowerCase().includes(searchText);
    });

    if (faqSort.value === "az") {
        filteredFAQs.sort(function(a, b) {
            return a.question.localeCompare(b.question);
        });
    }

    if (faqSort.value === "za") {
        filteredFAQs.sort(function(a, b) {
            return b.question.localeCompare(a.question);
        });
    }

    let totalPages =
        Math.ceil(filteredFAQs.length / faqItemsPerPage);

    if (totalPages === 0) {
        totalPages = 1;
    }

    if (faqCurrentPage > totalPages) {
        faqCurrentPage = totalPages;
    }

    let start =
        (faqCurrentPage - 1) * faqItemsPerPage;

    let pageFAQs =
        filteredFAQs.slice(start, start + faqItemsPerPage);

    faqContainer.innerHTML = "";

    if (pageFAQs.length === 0) {
        faqContainer.innerHTML =
            "<p>No questions found.</p>";
    }

    pageFAQs.forEach(function(faq) {

        let article = document.createElement("article");

        article.innerHTML =
            "<h3>" + faq.question + "</h3>" +
            "<p>" + faq.answer + "</p>";

        faqContainer.appendChild(article);
    });

    faqPageText.textContent =
        "Page " + faqCurrentPage + " of " + totalPages;

    faqPrevious.disabled = faqCurrentPage === 1;
    faqNext.disabled = faqCurrentPage === totalPages;
}

if (faqContainer) {

    fetch("../data/faqs.json")
        .then(function(response) {

            if (!response.ok) {
                throw new Error("FAQ data could not be loaded.");
            }

            return response.json();
        })
        .then(function(data) {

            faqData = data;
            faqLoading.style.display = "none";
            showFAQs();

        })
        .catch(function(error) {

            faqLoading.style.display = "none";
            faqError.textContent =
                "Unable to load FAQs. Please try again.";

            console.log(error);
        });

    faqSearch.addEventListener("input", function() {
        faqCurrentPage = 1;
        showFAQs();
    });

    faqSort.addEventListener("change", function() {
        faqCurrentPage = 1;
        showFAQs();
    });

    faqPrevious.addEventListener("click", function() {

        if (faqCurrentPage > 1) {
            faqCurrentPage--;
            showFAQs();
        }

    });

    faqNext.addEventListener("click", function() {

        faqCurrentPage++;
        showFAQs();

    });
}
