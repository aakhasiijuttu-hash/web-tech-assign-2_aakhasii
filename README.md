# Student Admission and Profile Management Portal

A comprehensive, multi-tier college Web Technology project integrating frontend client validation, PHP/MySQL administrative management, and enterprise Java Jakarta Servlets & JSP with JDBC.

All three assignment questions are organized in **one unified repository**, sharing a common database schema and central file storage.

---

## Project Structure

```
web assign-2 ak/
├── Q1_Registration/             # Question 1: Client-Side Student Registration
│   ├── index.html               # Registration form with semantic HTML5
│   ├── style.css                # Blue & purple gradient responsive UI
│   ├── script.js                # Form validation logic (regex, file size, errors)
│   └── README.md                # Documentation for Q1
│
├── Q2_PHP_MySQL/                # Question 2: PHP & MySQL Administrator Panel
│   ├── db.php                   # MySQLi database connection & config
│   ├── index.php                # Dashboard with student listing & search
│   ├── add_student.php          # Student creation with server-side validation
│   ├── edit_student.php         # Student editing with duplicate roll check
│   ├── delete_student.php       # Safe POST deletion with file cleanup
│   ├── view_student.php         # Student profile with file preview grid
│   ├── upload.php               # Server-validated file upload handler (2MB max)
│   ├── file_viewer.php          # Secure streaming proxy (avoids path traversal)
│   └── style.css                # Dashboard styling
│
├── Q3_Servlet/                  # Question 3: Java Jakarta Servlets & JSP
│   ├── pom.xml                  # Maven build configuration
│   ├── build.bat                # Automated compile & packaging script
│   ├── README.md                # Detailed setup & execution guide for Q3
│   ├── src/
│   │   ├── main/
│   │   │   ├── java/com/studentportal/
│   │   │   │   ├── DBConnection.java       # JDBC connection & config loader
│   │   │   │   ├── PasswordUtil.java       # SHA-256 secure password hashing
│   │   │   │   ├── Student.java            # Student model bean
│   │   │   │   ├── StudentFile.java        # Uploaded file model bean
│   │   │   │   ├── Admin.java              # Administrator model bean
│   │   │   │   ├── StudentDAO.java         # JDBC PreparedStatement DAO
│   │   │   │   ├── AdminDAO.java           # Administrator auth DAO
│   │   │   │   ├── LoginServlet.java       # Login controller & session handler
│   │   │   │   ├── LogoutServlet.java      # Session invalidation controller
│   │   │   │   ├── StudentServlet.java     # Protected profile controller
│   │   │   │   ├── ImageServlet.java       # Secure image streaming servlet
│   │   │   │   ├── DocumentServlet.java    # Secure PDF document servlet
│   │   │   │   └── AdminUserGenerator.java # Console admin creation utility
│   │   │   ├── resources/
│   │   │   │   └── db.properties           # Database & upload path config
│   │   │   └── webapp/
│   │   │       ├── login.jsp               # Administrator login page
│   │   │       ├── profile.jsp             # Student profile & media viewer
│   │   │       ├── css/style.css           # Blue/purple theme stylesheet
│   │   │       └── WEB-INF/web.xml         # Jakarta Servlet 6.0 deployment descriptor
│   └── target/
│       └── student-portal.war              # Tested, pre-packaged WAR file
│
├── database/
│   └── student_portal.sql       # MySQL setup script (students, student_files, admins)
│
├── storage/
│   └── uploads/                 # Shared storage for student documents/photos
│       └── .htaccess            # Script execution prevention
│
└── README.md                    # Root project documentation
```

---

## Architecture & Integration

```
  +-------------------------------------------------------------------------+
  |                               DATABASE                                  |
  |                        MySQL (`student_portal`)                         |
  |             - students   - student_files   - admins                     |
  +------------------------------------+------------------------------------+
                                       |
                   +-------------------+-------------------+
                   |                                       |
         [Question 2: PHP]                       [Question 3: Java]
         `Q2_PHP_MySQL/`                         `Q3_Servlet/`
         - MySQLi Prepared Statements            - JDBC Prepared Statements
         - Admin CRUD Dashboard                  - Jakarta Servlets & JSP
         - File Upload Handler                   - Session Authentication
                   |                                       |
                   +-------------------+-------------------+
                                       |
                               Reads & Writes
                                       v
                     +-----------------------------------+
                     |          SHARED STORAGE           |
                     |         `storage/uploads/`        |
                     |  - Student Photos (JPG/PNG)       |
                     |  - Documents & Certificates (PDF) |
                     +-----------------------------------+
```

---

## Quick Start Guide

### Step 1: Database Setup (XAMPP)
1. Open **XAMPP Control Panel** and start **Apache** and **MySQL**.
2. Open your browser and navigate to `http://localhost/phpmyadmin/`.
3. Click the **Import** tab.
4. Select `database/student_portal.sql` and click **Import**.
5. This creates the database `student_portal` with the required tables:
   - `students`
   - `student_files`
   - `admins` (seeded with username: `admin`, password: `Admin@123`)

---

### Step 2: Question 1 — Student Registration Form
- **Technologies**: HTML5, CSS3, JavaScript (Vanilla).
- **Run**: Open `Q1_Registration/index.html` directly in any web browser.
- **Features**:
  - Validates student full name, roll number, email address, course selection, and password strength.
  - Verifies confirmation password match.
  - Validates document file selection (JPG, JPEG, PNG, PDF &le; 2 MB).
  - Prevents page reload on demonstration.

---

### Step 3: Question 2 — PHP Administrator Panel
- **Technologies**: PHP 8.x, MySQLi, HTML, CSS.
- **Run**:
  Access via local XAMPP Apache:
  ```
  http://localhost/web-assign-2/Q2_PHP_MySQL/
  ```
- **Features**:
  - Full CRUD operations on student records with search functionality.
  - Duplicate roll number detection.
  - Upload student verification images and PDF certificates (max 2 MB) saved to `storage/uploads/`.
  - Secure file viewer (`file_viewer.php`) preventing directory traversal attacks.

---

### Step 4: Question 3 — Java Servlets & JSP Management Portal
- **Technologies**: Java 17+, Jakarta Servlets 6.0, JSP, JDBC, Tomcat 10.1.
- **Run**:
  1. Build the WAR file:
     ```powershell
     cd Q3_Servlet
     .\build.bat
     # OR with Maven:
     mvn clean package
     ```
  2. Deploy `Q3_Servlet/target/student-portal.war` to Apache Tomcat 10.1's `webapps/` folder.
  3. Start Tomcat (`bin\startup.bat`).
  4. Access in your browser:
     ```
     http://localhost:8080/student-portal/
     ```
  5. Log in using default credentials:
     - **Username**: `admin`
     - **Password**: `Admin@123`
- **Features**:
  - SHA-256 hashed administrator authentication with session fixation protection.
  - Protected endpoints for profiles, image streaming (`/image?id=...`), and document streaming (`/document?id=...`).
  - Reads from the shared `storage/uploads/` directory populated by Question 2.
  - Clean conditional empty states for students without documents.
