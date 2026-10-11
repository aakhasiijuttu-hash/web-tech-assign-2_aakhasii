# Student Admission and Profile Management Portal - Question 3

## 1. Overview
This module completes **Question 3** of the college Web Technology assignment. It provides an authenticated Administrator portal to view admitted student profiles, preview submitted verification photographs, and view/download student documents.

It is built strictly using modern **Jakarta Servlets (Tomcat 10.1+)**, **JSP**, **JDBC PreparedStatement**, and integrates seamlessly with the existing MySQL database (`student_portal`) and shared upload storage directory (`storage/uploads/`) created in Question 2.

---

## 2. Technologies Used
- **Language**: Java (JDK 17 or higher; tested on JDK 24)
- **Web Layer**: Jakarta Servlet API 6.0 (`jakarta.servlet.*`), Jakarta JSP API 3.1
- **Database Access**: Java Database Connectivity (JDBC) with `PreparedStatement`
- **Database**: MySQL 8.x / MariaDB (`student_portal`)
- **Driver**: MySQL Connector/J (`com.mysql.cj.jdbc.Driver`)
- **Application Server**: Apache Tomcat 10.1.x (requires Jakarta EE 10 namespace)
- **Build Tool**: Apache Maven (`pom.xml`)

---

## 3. Prerequisites & Environment Setup

### A. Java Development Kit (JDK)
1. Verify JDK is installed:
   ```bash
   java -version
   javac -version
   ```
2. If not installed, download **JDK 17 or 21** from [Oracle](https://www.oracle.com/java/technologies/downloads/) or [Eclipse Temurin](https://adoptium.net/).
3. Set `JAVA_HOME` environment variable to your JDK path (e.g., `C:\Program Files\Java\jdk-17`) and add `%JAVA_HOME%\bin` to your `Path`.

### B. Apache Maven
1. Download Apache Maven from [maven.apache.org](https://maven.apache.org/download.cgi).
2. Extract to `C:\Program Files\apache-maven` (or any directory).
3. Add `C:\Program Files\apache-maven\bin` to your system `Path`.
4. Verify by running:
   ```bash
   mvn -version
   ```

### C. Apache Tomcat 10.1 Setup
> [!IMPORTANT]
> Tomcat 10.1 uses the **`jakarta.servlet.*`** package namespace. Do not use older Tomcat 9 or Tomcat 8 versions (which use `javax.servlet`).

1. Download **Apache Tomcat 10.1** (Core zip or installer) from [tomcat.apache.org](https://tomcat.apache.org/download-10.cgi).
2. Extract to `C:\apache-tomcat-10.1` (or your preferred folder).
3. Ensure MySQL Connector/J jar (`mysql-connector-j-8.3.0.jar`) is placed in your Tomcat's `lib/` folder if you deploy unpacked applications, or include it in `WEB-INF/lib` (handled automatically by Maven).

---

## 4. Database Setup & Administrator Account

Question 3 reuses the same database created in Question 2:
1. Ensure Apache and MySQL are running in **XAMPP Control Panel**.
2. Open `http://localhost/phpmyadmin/` in your browser.
3. Import the updated script:
   ```
   database/student_portal.sql
   ```
   This creates:
   - `students` table
   - `student_files` table
   - `admins` table

### Default Administrator Account
- **Username**: `admin`
- **Password**: `Admin@123`
- **Stored In Database As**: `e86f78a8a3caf0b60d8e74e5942aa6d86dc150cd3c03338aef25b7d2d7e3acc7` (Secure SHA-256 Hash)

### Creating Custom Administrator Accounts
To create or reset admin credentials without committing plaintext passwords, compile and run the console utility:
```bash
# From Q3_Servlet/ directory:
javac -d target/classes -cp "target/classes" src/main/java/com/studentportal/PasswordUtil.java src/main/java/com/studentportal/AdminDAO.java src/main/java/com/studentportal/AdminUserGenerator.java
java -cp "target/classes;C:/path/to/mysql-connector-j.jar" com.studentportal.AdminUserGenerator
```
Alternatively, generate the SHA-256 hash using PowerShell:
```powershell
$bytes = [System.Text.Encoding]::UTF8.GetBytes("YourNewPassword");
$hash = [System.Security.Cryptography.SHA256]::Create().ComputeHash($bytes);
-join ($hash | ForEach-Object { $_.ToString("x2") })
```
Then run this SQL statement in phpMyAdmin:
```sql
INSERT INTO `admins` (`username`, `password_hash`, `full_name`) 
VALUES ('newadmin', '<GENERATED_HASH>', 'Staff Member');
```

---

## 5. Configuration & Shared File Storage

### A. Database Configuration (`db.properties`)
Located at `src/main/resources/db.properties`. Configured for local XAMPP by default:
```properties
db.host=localhost
db.port=3306
db.name=student_portal
db.user=root
db.password=
```
You can also override these without touching code using environment variables:
- `STUDENT_PORTAL_DB_URL`
- `STUDENT_PORTAL_DB_USER`
- `STUDENT_PORTAL_DB_PASSWORD`

### B. Shared File Upload Storage
Question 2 saves uploaded documents and photos to:
```
<PROJECT_ROOT>/storage/uploads/
```
`DBConnection.java` automatically resolves this relative directory. If your Tomcat server runs in a custom directory, you can explicitly set the Windows path:
- In `src/main/resources/db.properties`:
  ```properties
  upload.dir=C:/Users/ponpr/Desktop/web assign-2 ak/storage/uploads/
  ```
- Or via environment variable:
  ```powershell
  [System.Environment]::SetEnvironmentVariable("STUDENT_PORTAL_UPLOAD_DIR", "C:\Users\ponpr\Desktop\web assign-2 ak\storage\uploads", "User")
  ```

---

## 6. Build and Deployment

### Building with Maven
Open PowerShell in the `Q3_Servlet` folder:
```powershell
cd "c:\Users\ponpr\Desktop\web assign-2 ak\Q3_Servlet"
mvn clean package
```
This produces the deployable web archive:
```
target/student-portal.war
```

### Deploying to Apache Tomcat 10.1
1. Copy `target/student-portal.war` into Tomcat's `webapps/` folder:
   ```powershell
   Copy-Item target/student-portal.war "C:\apache-tomcat-10.1\webapps\"
   ```
2. Start Tomcat:
   ```powershell
   & "C:\apache-tomcat-10.1\bin\startup.bat"
   ```
3. Open your browser at:
   ```
   http://localhost:8080/student-portal/
   ```

---

## 7. How to Test the Application

| Step | Action | Expected Result |
| :--- | :--- | :--- |
| **1. Unauthenticated Access** | Open `http://localhost:8080/student-portal/students` without logging in. | Redirects immediately to `login.jsp?error=auth` with *"Session expired or unauthenticated."* |
| **2. Invalid Login** | Enter `admin` and an incorrect password like `wrongpass`. | Displays red alert: *"Invalid administrator credentials. Please try again."* |
| **3. Successful Login** | Enter `admin` and `Admin@123`. | Authenticates via SHA-256 hash in MySQL, regenerates session ID, and redirects to the **Student Profiles** dashboard. |
| **4. Profile & Data Loop** | Observe the student list on `profile.jsp`. | Retrieves and displays student name, roll number, department, registration date, and all associated verification documents via `StudentDAO`. |
| **5. Empty Files Condition** | Observe students with no uploaded documents. | Displays clean conditional banner: *"No documents or images uploaded yet for this student."* |
| **6. Image Streaming** | Check students with uploaded JPG/PNG files. | Image is securely previewed via `<img src="image?id=X">`. Clicking **View** opens image safely in a new tab. |
| **7. PDF Document Handling** | For students with uploaded PDF certificates. | PDF document icon is rendered. Clicking **View** previews the PDF inline; clicking **Save** downloads it with proper filename headers. |
| **8. Path Traversal & Security** | Try accessing `image?id=99999` or non-image IDs. | Returns HTTP 404/403 errors and prevents unauthorized server directory access. |
| **9. Administrator Logout** | Click **Log Out** in the top navigation bar. | Invalidates session and redirects to `login.jsp?logout=true` with confirmation notice. Back-button access to `/students` is blocked. |
