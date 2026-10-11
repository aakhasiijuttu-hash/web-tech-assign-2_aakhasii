package com.studentportal;

import java.sql.Timestamp;

/**
 * Model representing an administrator account in the admins table.
 */
public class Admin {
    private int id;
    private String username;
    private String passwordHash;
    private String fullName;
    private Timestamp createdAt;

    public Admin() {
    }

    public Admin(int id, String username, String passwordHash, String fullName, Timestamp createdAt) {
        this.id = id;
        this.username = username;
        this.passwordHash = passwordHash;
        this.fullName = fullName;
        this.createdAt = createdAt;
    }

    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public String getUsername() { return username; }
    public void setUsername(String username) { this.username = username; }

    public String getPasswordHash() { return passwordHash; }
    public void setPasswordHash(String passwordHash) { this.passwordHash = passwordHash; }

    public String getFullName() { return fullName; }
    public void setFullName(String fullName) { this.fullName = fullName; }

    public Timestamp getCreatedAt() { return createdAt; }
    public void setCreatedAt(Timestamp createdAt) { this.createdAt = createdAt; }
}
