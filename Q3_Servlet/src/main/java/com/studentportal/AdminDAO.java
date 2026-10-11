package com.studentportal;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

/**
 * Data Access Object for Administrator authentication and management.
 * Strictly uses PreparedStatement for SQL operations.
 */
public class AdminDAO {

    /**
     * Authenticates administrator by checking username and hashed password.
     * @param username Input username
     * @param plainPassword Input plaintext password
     * @return Admin object if authenticated, null otherwise
     */
    public Admin authenticate(String username, String plainPassword) {
        if (username == null || plainPassword == null) {
            return null;
        }

        String sql = "SELECT id, username, password_hash, full_name, created_at FROM admins WHERE username = ?";

        try (Connection conn = DBConnection.getConnection();
             PreparedStatement ps = conn.prepareStatement(sql)) {

            ps.setString(1, username.trim());

            try (ResultSet rs = ps.executeQuery()) {
                if (rs.next()) {
                    String storedHash = rs.getString("password_hash");
                    if (PasswordUtil.verifyPassword(plainPassword, storedHash)) {
                        return new Admin(
                                rs.getInt("id"),
                                rs.getString("username"),
                                storedHash,
                                rs.getString("full_name"),
                                rs.getTimestamp("created_at")
                        );
                    }
                }
            }
        } catch (SQLException e) {
            System.err.println("[AdminDAO] Error during authentication: " + e.getMessage());
        }

        return null;
    }

    /**
     * Inserts a new administrator with a hashed password.
     */
    public boolean createAdmin(String username, String plainPassword, String fullName) {
        String hash = PasswordUtil.hashPassword(plainPassword);
        String sql = "INSERT INTO admins (username, password_hash, full_name) VALUES (?, ?, ?)";

        try (Connection conn = DBConnection.getConnection();
             PreparedStatement ps = conn.prepareStatement(sql)) {

            ps.setString(1, username.trim());
            ps.setString(2, hash);
            ps.setString(3, fullName.trim());

            return ps.executeUpdate() > 0;
        } catch (SQLException e) {
            System.err.println("[AdminDAO] Error creating admin: " + e.getMessage());
            return false;
        }
    }
}
