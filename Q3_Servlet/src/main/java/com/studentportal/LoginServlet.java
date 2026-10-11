package com.studentportal;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;

import java.io.IOException;

/**
 * Handles administrator authentication, session creation, and session fixation defense.
 */
@WebServlet(name = "LoginServlet", urlPatterns = {"/login"})
public class LoginServlet extends HttpServlet {

    private final AdminDAO adminDAO = new AdminDAO();

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        // If administrator is already authenticated, redirect straight to students
        HttpSession session = request.getSession(false);
        if (session != null && session.getAttribute("adminUser") != null) {
            response.sendRedirect(request.getContextPath() + "/students");
            return;
        }

        // Forward to login page
        request.getRequestDispatcher("/login.jsp").forward(request, response);
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        String username = request.getParameter("username");
        String password = request.getParameter("password");

        if (username == null || username.trim().isEmpty() || password == null || password.trim().isEmpty()) {
            request.setAttribute("errorMessage", "Both username and password are required.");
            request.getRequestDispatcher("/login.jsp").forward(request, response);
            return;
        }

        Admin admin = adminDAO.authenticate(username, password);

        if (admin != null) {
            // Defend against session fixation: Invalidate any pre-existing session
            HttpSession oldSession = request.getSession(false);
            if (oldSession != null) {
                oldSession.invalidate();
            }

            // Create new fresh authenticated session
            HttpSession newSession = request.getSession(true);
            newSession.setAttribute("adminUser", admin.getUsername());
            newSession.setAttribute("adminName", admin.getFullName());
            newSession.setMaxInactiveInterval(30 * 60); // 30-minute session timeout

            // Redirect to protected student profile management
            response.sendRedirect(request.getContextPath() + "/students");
        } else {
            request.setAttribute("errorMessage", "Invalid administrator credentials. Please try again.");
            request.setAttribute("enteredUsername", username);
            request.getRequestDispatcher("/login.jsp").forward(request, response);
        }
    }
}
