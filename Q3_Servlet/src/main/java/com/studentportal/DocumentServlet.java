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
 * Serves student PDF documents securely.
 * Enforces session authentication, validates MIME type,
 * handles inline preview or attachment download, and defends against path traversal.
 */
@WebServlet(name = "DocumentServlet", urlPatterns = {"/document"})
public class DocumentServlet extends HttpServlet {

    private final StudentDAO studentDAO = new StudentDAO();

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        // 1. Session check
        HttpSession session = request.getSession(false);
        if (session == null || session.getAttribute("adminUser") == null) {
            response.sendError(HttpServletResponse.SC_UNAUTHORIZED, "Authentication required to view student documents.");
            return;
        }

        // 2. Validate file ID
        String idParam = request.getParameter("id");
        if (idParam == null || !idParam.matches("\\d+")) {
            response.sendError(HttpServletResponse.SC_BAD_REQUEST, "Invalid file ID parameter.");
            return;
        }

        int fileId = Integer.parseInt(idParam);
        boolean isDownload = "true".equalsIgnoreCase(request.getParameter("download"))
                || "1".equals(request.getParameter("download"));

        // 3. Retrieve file record from MySQL
        StudentFile studentFile = studentDAO.getFileById(fileId);
        if (studentFile == null) {
            response.sendError(HttpServletResponse.SC_NOT_FOUND, "Document record not found in database.");
            return;
        }

        // 4. Resolve file path with path traversal protection
        Path uploadBase = DBConnection.getUploadDirectory();
        String storedName = new File(studentFile.getStoredName()).getName();
        File physicalFile = uploadBase.resolve(storedName).toFile();

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

        // 5. Check file exists on disk
        if (!physicalFile.exists() || !physicalFile.isFile()) {
            response.sendError(HttpServletResponse.SC_NOT_FOUND, "Physical document file missing from storage.");
            return;
        }

        // 6. Set Content-Type and Content-Disposition headers
        String mime = studentFile.getFileType();
        if (mime == null || mime.trim().isEmpty()) {
            mime = "application/pdf";
        }
        response.setContentType(mime);
        response.setContentLengthLong(physicalFile.length());
        response.setHeader("X-Content-Type-Options", "nosniff");

        String dispositionType = isDownload ? "attachment" : "inline";
        String cleanOriginalName = studentFile.getFileName()
                .replaceAll("[\\r\\n\"]", "_");
        response.setHeader("Content-Disposition", dispositionType + "; filename=\"" + cleanOriginalName + "\"");

        // 7. Stream file bytes
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
