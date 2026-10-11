package com.studentportal;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;

/**
 * Data Access Object for Student Admission Records and Associated Files.
 * Contains all SQL queries so that JSP files remain clean of database code.
 */
public class StudentDAO {

    /**
     * Retrieves all students along with their uploaded files.
     * Uses an ordered LinkedHashMap to preserve student order and associate files.
     * @return List of Student objects with populated files
     */
    public List<Student> getAllStudentsWithFiles() {
        Map<Integer, Student> studentMap = new LinkedHashMap<>();

        // First retrieve all students ordered by ID descending
        String studentSql = "SELECT id, name, roll_number, email, course, created_at FROM students ORDER BY id DESC";

        try (Connection conn = DBConnection.getConnection();
             PreparedStatement ps = conn.prepareStatement(studentSql);
             ResultSet rs = ps.executeQuery()) {

            while (rs.next()) {
                Student s = new Student(
                        rs.getInt("id"),
                        rs.getString("name"),
                        rs.getString("roll_number"),
                        rs.getString("email"),
                        rs.getString("course"),
                        rs.getTimestamp("created_at")
                );
                studentMap.put(s.getId(), s);
            }
        } catch (SQLException e) {
            System.err.println("[StudentDAO] Error loading students: " + e.getMessage());
        }

        // If students exist, retrieve all their associated uploaded files
        if (!studentMap.isEmpty()) {
            String filesSql = "SELECT id, student_id, file_name, stored_name, file_path, file_type, upload_date "
                    + "FROM student_files ORDER BY id DESC";

            try (Connection conn = DBConnection.getConnection();
                 PreparedStatement ps = conn.prepareStatement(filesSql);
                 ResultSet rs = ps.executeQuery()) {

                while (rs.next()) {
                    int studentId = rs.getInt("student_id");
                    Student student = studentMap.get(studentId);
                    if (student != null) {
                        StudentFile file = new StudentFile(
                                rs.getInt("id"),
                                studentId,
                                rs.getString("file_name"),
                                rs.getString("stored_name"),
                                rs.getString("file_path"),
                                rs.getString("file_type"),
                                rs.getTimestamp("upload_date")
                        );
                        student.addFile(file);
                    }
                }
            } catch (SQLException e) {
                System.err.println("[StudentDAO] Error loading student files: " + e.getMessage());
            }
        }

        return new ArrayList<>(studentMap.values());
    }

    /**
     * Retrieves a single uploaded file record by its primary key ID.
     * @param fileId Primary key ID in student_files
     * @return StudentFile instance or null if not found
     */
    public StudentFile getFileById(int fileId) {
        String sql = "SELECT id, student_id, file_name, stored_name, file_path, file_type, upload_date "
                + "FROM student_files WHERE id = ?";

        try (Connection conn = DBConnection.getConnection();
             PreparedStatement ps = conn.prepareStatement(sql)) {

            ps.setInt(1, fileId);

            try (ResultSet rs = ps.executeQuery()) {
                if (rs.next()) {
                    return new StudentFile(
                            rs.getInt("id"),
                            rs.getInt("student_id"),
                            rs.getString("file_name"),
                            rs.getString("stored_name"),
                            rs.getString("file_path"),
                            rs.getString("file_type"),
                            rs.getTimestamp("upload_date")
                    );
                }
            }
        } catch (SQLException e) {
            System.err.println("[StudentDAO] Error finding file #" + fileId + ": " + e.getMessage());
        }

        return null;
    }

    /**
     * Counts the total number of students currently registered.
     */
    public int getTotalStudentCount() {
        String sql = "SELECT COUNT(*) FROM students";
        try (Connection conn = DBConnection.getConnection();
             PreparedStatement ps = conn.prepareStatement(sql);
             ResultSet rs = ps.executeQuery()) {
            if (rs.next()) {
                return rs.getInt(1);
            }
        } catch (SQLException e) {
            System.err.println("[StudentDAO] Error getting student count: " + e.getMessage());
        }
        return 0;
    }
}
