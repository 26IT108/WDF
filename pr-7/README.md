# StudentHub

## Practical 1 - Project Initiation and Requirement Analysis

StudentHub is a student portal website designed to provide students with important academic and college-related information in one place.

The project is being developed step-by-step during the semester. In Practical 1, the basic project structure, requirements, navigation flow, sitemap, wireframe and HTML pages are prepared.

---

## 1. Project Objective

The main objective of StudentHub is to create a simple student portal where students can:

* View their dashboard
* Check attendance
* View assignments
* View upcoming events
* Manage their profile
* Read frequently asked questions
* Contact the college
* Login and register

---

## 2. User Role

### Student

The student can:

* Register an account
* Login to the portal
* View dashboard
* Check attendance
* View assignments
* View events
* View profile
* Read FAQs
* Contact the college

---

## 3. Main Pages

The project contains the following 10 pages:

1. Home
2. Login
3. Register
4. Dashboard
5. Attendance
6. Assignments
7. Events
8. FAQ
9. Profile
10. Contact

---

## 4. Navigation Flow

The pages are connected using normal HTML links.

Basic flow:

```text
                    Home
                     |
        ---------------------------
        |           |             |
      Login      Register      Dashboard
                                  |
             --------------------------------
             |        |       |      |      |
        Attendance  Assignment Events Profile FAQ
                                            |
                                          Contact
```

Users can move between the different pages using the navigation links.

---

## 5. Project Folder Structure

```text
StudentHub/
│
├── Pages/
│   ├── index.html
│   ├── login.html
│   ├── register.html
│   ├── dashboard.html
│   ├── attendance.html
│   ├── assignment.html
│   ├── events.html
│   ├── faq.html
│   ├── profile.html
│   └── contact.html
│
├── images/
│   ├── sitemap.jpeg
│   └── wire frames.png
│
└── README.md
```

---

## 6. Technologies Used

The following technologies are used in Practical 1:

* HTML5
* Visual Studio Code
* Web Browser
* Draw.io / Figma for sitemap and wireframe

CSS and JavaScript will be added in the upcoming practicals.

---

## 7. Requirements

### Functional Requirements

The StudentHub system should allow students to:

* Register on the portal
* Login to their account
* View student information
* View attendance
* View assignments
* View college events
* View profile information
* Read FAQs
* Contact the college

### Non-Functional Requirements

The website should:

* Be simple to use
* Have clear navigation
* Have readable content
* Keep all pages properly connected
* Have an organized project structure

---

## 8. Sitemap

The sitemap shows the navigation structure of the StudentHub website.

The main page is the Home page. From Home, users can access Login, Register and Dashboard. The Dashboard provides access to the main student features.

---

## 9. Wireframe

A low-fidelity wireframe was prepared to show the basic layout and placement of elements on the StudentHub pages.

The wireframe focuses on:

* Page title
* Navigation links
* Main content
* Forms
* Tables
* Buttons
* Footer/content areas

---

## 10. Practical 1 Scope

In this practical, the following work has been completed:

* Project idea identified
* Student role identified
* Requirements analyzed
* 10 pages planned
* Sitemap prepared
* Wireframe prepared
* Project folder structure created
* Basic HTML pages created
* Navigation links added
* README file created

---

## 11. Future Development

The StudentHub project will be improved in the upcoming practicals.

### Practical 2

Semantic HTML5 structure and accessibility will be added.

### Practical 3

CSS, Flexbox, Grid and responsive design will be added.

### Practical 4

JavaScript will be added for interactive features such as:

* FAQ buttons
* Modal popup
* Event interaction
* Notifications
* Hamburger menu
* Light/Dark mode

---

## 12. Conclusion

Practical 1 provides the basic foundation for the StudentHub project. The requirements, user role, navigation flow, sitemap, wireframe and project structure have been prepared.

The basic HTML pages are connected to each other and provide the foundation for adding semantic HTML, responsive design and JavaScript functionality in the upcoming practicals.


## Practical 6 - JSON and Fetch API

Practical 6 is implemented on the existing StudentHub project.

### Added
- JSON datasets in the `data` folder
- Fetch API to load JSON data
- Event search, category filter, sorting and pagination
- Student search, course/year filter, sorting and pagination
- FAQ search, sorting and pagination
- Loading and error messages
- Students page added to the existing navigation

### Important
Run the project using VS Code Live Server (or another local web server). Fetch API may not work correctly when HTML files are opened directly using `file://`.

## Practical 7 - PHP Form Processing and CSV Storage

Practical 7 adds server-side processing to the existing StudentHub registration form.

### Added
- PHP form processing using POST
- Server-side validation for registration data
- Password hashing using `password_hash()`
- Registration data stored in `data/registrations.csv`
- PHP page to view registered students in a table
- Student ID included in registration storage
- Existing Practical 5 JavaScript validation kept on the client side

### PHP Files

```text
php/
├── process_registration.php
└── view_registrations.php
```

### Registration Flow

```text
Register Form
     |
     v
JavaScript Validation
     |
     v
process_registration.php
     |
     +---- Invalid data ----> Error message
     |
     +---- Valid data ------> Password Hashing
                                  |
                                  v
                         registrations.csv
                                  |
                                  v
                       view_registrations.php
```

### Running Practical 7

PHP must be run through a PHP-enabled local web server. The simplest option is the PHP built-in server.

Open the project folder in a terminal and run:

```text
php -S localhost:8000
```

Then open:

```text
http://localhost:8000/Pages/register.html
```

After a successful registration, the information is stored in `data/registrations.csv` and can be viewed from the **View Registered Students** link.

### Important

The password is not stored as plain text. A password hash is stored in the CSV file instead.
