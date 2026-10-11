package com.studentportal;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;

import java.io.File;
import java.io.FileInputStream;
import java.io.IOException;
import java.io.OutputStream;
import java.nio.file.Path;

/**
 * Serves student images securely by ID.
 * Enforces session authentication, validates MIME type,
 * and defends against path traversal.
 */
@WebServlet(name = "ImageServlet", urlPatterns = {"/image"})
public class ImageServlet extends HttpServlet {

    private final StudentDAO studentDAO = new StudentDAO();

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        // 1. Session authentication verification
        HttpSession session = request.getSession(false);
        if (session == null || session.getAttribute("adminUser") == null) {
            response.sendError(HttpServletResponse.SC_UNAUTHORIZED, "Authentication required to view student images.");
            return;
        }

        // 2. Validate requested file ID
        String idParam = request.getParameter("id");
        if (idParam == null || !idParam.matches("\\d+")) {
            response.sendError(HttpServletResponse.SC_BAD_REQUEST, "Invalid file ID parameter.");
            return;
        }

        int fileId = Integer.parseInt(idParam);

        // 3. Retrieve file metadata from MySQL
        StudentFile studentFile = studentDAO.getFileById(fileId);
        if (studentFile == null) {
            response.sendError(HttpServletResponse.SC_NOT_FOUND, "File record not found in database.");
            return;
        }

        // 4. Confirm that the file is an allowed image format
        String mimeType = studentFile.getFileType();
        if (mimeType == null || !mimeType.startsWith("image/")) {
            response.sendError(HttpServletResponse.SC_FORBIDDEN, "Requested file is not an allowed image format.");
            return;
        }

        // 5. Resolve storage location and prevent path traversal
        Path uploadBase = DBConnection.getUploadDirectory();
        String storedName = new File(studentFile.getStoredName()).getName(); // strip any traversal tokens
        File physicalFile = uploadBase.resolve(storedName).toFile();

        // Path traversal defense check
        try {
            String canonicalBase = uploadBase.toFile().getCanonicalPath();
            String canonicalFile = physicalFile.getCanonicalPath();
            if (!canonicalFile.startsWith(canonicalBase)) {
                response.sendError(HttpServletResponse.SC_FORBIDDEN, "Access denied: Path traversal detected.");
                return;
            }
        } catch (IOException e) {
            response.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR, "Error verifying file path.");
            return;
        }

        // 6. Check physical file exists on disk
        if (!physicalFile.exists() || !physicalFile.isFile()) {
            response.sendError(HttpServletResponse.SC_NOT_FOUND, "Physical image file missing from storage.");
            return;
        }

        // 7. Set headers and stream image
        response.setContentType(mimeType);
        response.setContentLengthLong(physicalFile.length());
        response.setHeader("X-Content-Type-Options", "nosniff");
        response.setHeader("Cache-Control", "private, max-age=86400");

        try (FileInputStream in = new FileInputStream(physicalFile);
             OutputStream out = response.getOutputStream()) {

            byte[] buffer = new byte[8192];
            int bytesRead;
            while ((bytesRead = in.read(buffer)) != -1) {
                out.write(buffer, 0, bytesRead);
            }
            out.flush();
        }
    }
}
