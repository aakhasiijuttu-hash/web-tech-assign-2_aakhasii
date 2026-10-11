<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator Login - Student Admission Portal</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <div class="badge" style="background: rgba(124, 58, 237, 0.1); color: var(--primary-purple);">
                    Java Servlets &bull; Question 3
                </div>
                <h1 class="login-title">Administrator Login</h1>
                <p style="color: var(--text-muted); font-size: 0.88rem; margin-top: 4px;">
                    Student Admission & Profile Management Portal
                </p>
            </div>

            <!-- Error Alerts -->
            <%
                String error = (String) request.getAttribute("errorMessage");
                String errorParam = request.getParameter("error");
                String logoutParam = request.getParameter("logout");

                if (error != null && !error.isEmpty()) {
            %>
                <div class="alert alert-danger">
                    <span>&#9888;</span>
                    <div><%= error %></div>
                </div>
            <%
                } else if ("auth".equals(errorParam)) {
            %>
                <div class="alert alert-danger">
                    <span>&#9888;</span>
                    <div>Session expired or unauthenticated. Please log in first.</div>
                </div>
            <%
                }
            %>

            <!-- Logout Success Alert -->
            <% if ("true".equals(logoutParam)) { %>
                <div class="alert alert-success">
                    <span>&#10004;</span>
                    <div>You have been logged out successfully.</div>
                </div>
            <% } %>

            <!-- Login Form (POST to LoginServlet) -->
            <form action="login" method="POST">
                <div class="form-group">
                    <label for="username" class="form-label">Administrator Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-control" 
                        placeholder="e.g. admin" 
                        value="<%= request.getAttribute("enteredUsername") != null ? request.getAttribute("enteredUsername") : "" %>" 
                        required 
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control" 
                        placeholder="Enter password" 
                        required
                    >
                </div>

                <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 10px 12px; margin-bottom: 20px; font-size: 0.8rem; color: #475569;">
                    <strong>Default Credentials:</strong><br>
                    Username: <code>admin</code> &bull; Password: <code>Admin@123</code>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 0.95rem;">
                    Log In to Management Portal
                </button>
            </form>

            <div style="text-align: center; margin-top: 24px; border-top: 1px solid var(--border-color); padding-top: 16px; font-size: 0.82rem; color: var(--text-muted);">
                <a href="../Q1_Registration/index.html" target="_blank" style="color: var(--primary-blue); text-decoration: none;">&larr; Q1 Form</a>
                &bull;
                <a href="../Q2_PHP_MySQL/index.php" target="_blank" style="color: var(--primary-purple); text-decoration: none;">Q2 PHP Panel &rarr;</a>
            </div>
        </div>
    </div>

</body>
</html>
