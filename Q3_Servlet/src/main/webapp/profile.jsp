<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ page import="java.util.List" %>
<%@ page import="com.studentportal.Student" %>
<%@ page import="com.studentportal.StudentFile" %>
<%
    // Ensure active authenticated session
    String adminUser = (String) session.getAttribute("adminUser");
    String adminName = (String) session.getAttribute("adminName");
    if (adminUser == null) {
        response.sendRedirect("login?error=auth");
        return;
    }

    // Retrieve student list from request attribute (populated by StudentServlet)
    @SuppressWarnings("unchecked")
    List<Student> students = (List<Student>) request.getAttribute("students");
    Integer totalCount = (Integer) request.getAttribute("totalCount");
    if (totalCount == null && students != null) {
        totalCount = students.size();
    }
%>
<%!
    // Safe HTML Escaping Helper to prevent XSS
    private String escapeHtml(String input) {
        if (input == null) return "";
        return input.replace("&", "&amp;")
                    .replace("<", "&lt;")
                    .replace(">", "&gt;")
                    .replace("\"", "&quot;")
                    .replace("'", "&#x27;");
    }
%>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profiles - Admission Management Portal</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Top Navigation Header -->
    <header class="navbar">
        <a href="students" class="brand">
            <div class="brand-icon">SP</div>
            <div>
                <div class="brand-title">Student Admission Portal</div>
                <small style="color: var(--text-muted); font-size: 0.75rem;">Jakarta Servlets &bull; Question 3</small>
            </div>
        </a>

        <div class="nav-links">
            <div class="admin-badge">
                <span>&#128100;</span>
                <span><%= escapeHtml(adminName != null ? adminName : adminUser) %> (Admin)</span>
            </div>
            <a href="../Q2_PHP_MySQL/index.php" class="btn btn-secondary btn-sm" target="_blank">Q2 PHP Panel &rarr;</a>
            <a href="logout" class="btn btn-danger btn-sm">Log Out</a>
        </div>
    </header>

    <!-- Main Container -->
    <main class="container">

        <!-- Informational Banner -->
        <div class="alert" style="background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af;">
            <span>&#9432;</span>
            <div>
                <strong>Connected to Shared Database & Storage</strong>:
                Displaying enrolled students and their uploaded verification documents from <code>student_portal</code> MySQL database and <code>storage/uploads/</code>.
            </div>
        </div>

        <!-- Section Header Card -->
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header" style="margin-bottom: 0;">
                <div>
                    <h1 class="card-title">Enrolled Student Profiles (<%= totalCount != null ? totalCount : 0 %>)</h1>
                    <p class="card-subtitle">Student credentials, departments, photographs, and submitted documentation.</p>
                </div>
                <a href="students" class="btn btn-primary btn-sm">&#8635; Refresh Records</a>
            </div>
        </div>

        <!-- Students Grid View -->
        <% if (students == null || students.isEmpty()) { %>
            <div class="card" style="text-align: center; padding: 48px;">
                <div style="font-size: 2.5rem; margin-bottom: 10px;">&#128194;</div>
                <h3>No Student Records Found</h3>
                <p style="color: var(--text-muted); margin-top: 6px;">
                    No student registrations exist in the MySQL database yet. Register a student via Question 1 or Question 2.
                </p>
                <div style="margin-top: 16px;">
                    <a href="../Q1_Registration/index.html" class="btn btn-primary" target="_blank">Register Student (Q1)</a>
                    <a href="../Q2_PHP_MySQL/add_student.php" class="btn btn-secondary" target="_blank">Add Student (Q2)</a>
                </div>
            </div>
        <% } else { %>
            <div class="students-grid">
                <% for (Student s : students) { %>
                    <article class="student-card">
                        <!-- Student Header / Summary -->
                        <div class="student-head">
                            <div class="student-identity">
                                <div class="student-avatar">
                                    <%= s.getName() != null && !s.getName().isEmpty() ? s.getName().substring(0, 1).toUpperCase() : "S" %>
                                </div>
                                <div>
                                    <h2 class="student-name"><%= escapeHtml(s.getName()) %></h2>
                                    <div class="student-roll">Roll No: <strong><%= escapeHtml(s.getRollNumber()) %></strong></div>
                                </div>
                            </div>

                            <div style="display: flex; gap: 8px; align-items: center;">
                                <span class="badge-id">ID #<%= s.getId() %></span>
                                <span class="badge badge-course"><%= escapeHtml(s.getCourse()) %></span>
                            </div>
                        </div>

                        <!-- Metadata Grid -->
                        <div class="student-meta-grid">
                            <div class="meta-box">
                                <div class="meta-label">Email Address</div>
                                <div class="meta-val"><%= escapeHtml(s.getEmail()) %></div>
                            </div>
                            <div class="meta-box">
                                <div class="meta-label">Department / Course</div>
                                <div class="meta-val"><%= escapeHtml(s.getCourse()) %></div>
                            </div>
                            <div class="meta-box">
                                <div class="meta-label">Registration Date</div>
                                <div class="meta-val">
                                    <%= s.getCreatedAt() != null ? s.getCreatedAt().toString().substring(0, 10) : "N/A" %>
                                </div>
                            </div>
                            <div class="meta-box">
                                <div class="meta-label">Total Files</div>
                                <div class="meta-val" style="color: var(--primary-blue);">
                                    <%= s.getFiles() != null ? s.getFiles().size() : 0 %> File(s) Attached
                                </div>
                            </div>
                        </div>

                        <!-- Uploaded Files Section -->
                        <div class="student-files-section">
                            <div class="files-title">
                                <span>&#128196;</span> Submitted Verification Documents & Photos
                            </div>

                            <% if (!s.hasFiles()) { %>
                                <!-- Conditional Empty-State Message for students without files -->
                                <div class="empty-files">
                                    <em>No documents or images uploaded yet for this student.</em>
                                </div>
                            <% } else { %>
                                <div class="files-grid">
                                    <% for (StudentFile file : s.getFiles()) { %>
                                        <div class="file-item-card">
                                            <div class="file-thumb">
                                                <% if (file.isImage()) { %>
                                                    <!-- Image preview served through ImageServlet -->
                                                    <img src="image?id=<%= file.getId() %>" 
                                                         alt="Photo: <%= escapeHtml(file.getFileName()) %>" 
                                                         loading="lazy">
                                                <% } else { %>
                                                    <!-- Document icon for PDF -->
                                                    <div class="file-thumb-icon" title="PDF Document">&#128196;</div>
                                                <% } %>
                                            </div>

                                            <div class="file-info">
                                                <div class="file-name-text" title="<%= escapeHtml(file.getFileName()) %>">
                                                    <%= escapeHtml(file.getFileName()) %>
                                                </div>
                                                <div style="font-size: 0.72rem; color: var(--text-muted);">
                                                    <%= file.isImage() ? "Image File" : "PDF Document" %>
                                                </div>

                                                <div style="display: flex; gap: 4px; margin-top: 6px;">
                                                    <% if (file.isImage()) { %>
                                                        <a href="image?id=<%= file.getId() %>" target="_blank" class="btn btn-secondary btn-sm" style="flex: 1;">
                                                            View
                                                        </a>
                                                    <% } else { %>
                                                        <!-- PDF preview and download served through DocumentServlet -->
                                                        <a href="document?id=<%= file.getId() %>" target="_blank" class="btn btn-secondary btn-sm" style="flex: 1;">
                                                            View
                                                        </a>
                                                        <a href="document?id=<%= file.getId() %>&download=true" class="btn btn-secondary btn-sm" style="flex: 1;">
                                                            Save
                                                        </a>
                                                    <% } %>
                                                </div>
                                            </div>
                                        </div>
                                    <% } %>
                                </div>
                            <% } %>
                        </div>
                    </article>
                <% } %>
            </div>
        <% } %>

    </main>

    <!-- Footer -->
    <footer class="footer">
        Student Admission Portal &bull; Web Technology Assignment Question 3 &bull; Jakarta Servlets & JSP
    </footer>

</body>
</html>
