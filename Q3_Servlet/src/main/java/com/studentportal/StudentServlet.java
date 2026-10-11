package com.studentportal;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;

import java.io.IOException;
import java.util.List;

/**
 * Controller servlet for the Student Admission & Profile Management view.
 * Verifies session authentication and loads student records via StudentDAO.
 */
@WebServlet(name = "StudentServlet", urlPatterns = {"/students", "/profile"})
public class StudentServlet extends HttpServlet {

    private final StudentDAO studentDAO = new StudentDAO();

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        // Session validation check
        HttpSession session = request.getSession(false);
        if (session == null || session.getAttribute("adminUser") == null) {
            response.sendRedirect(request.getContextPath() + "/login?error=auth");
            return;
        }

        // Retrieve student profiles using JDBC StudentDAO
        List<Student> studentList = studentDAO.getAllStudentsWithFiles();
        request.setAttribute("students", studentList);
        request.setAttribute("totalCount", studentList.size());

        // Forward to profile.jsp for presentation
        request.getRequestDispatcher("/profile.jsp").forward(request, response);
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        doGet(request, response);
    }
}
