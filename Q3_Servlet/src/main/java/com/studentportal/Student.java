package com.studentportal;

import java.sql.Timestamp;
import java.util.ArrayList;
import java.util.List;

/**
 * Model representing a student record in the students table.
 */
public class Student {
    private int id;
    private String name;
    private String rollNumber;
    private String email;
    private String course;
    private Timestamp createdAt;
    private List<StudentFile> files = new ArrayList<>();

    public Student() {
    }

    public Student(int id, String name, String rollNumber, String email, String course, Timestamp createdAt) {
        this.id = id;
        this.name = name;
        this.rollNumber = rollNumber;
        this.email = email;
        this.course = course;
        this.createdAt = createdAt;
    }

    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public String getName() { return name; }
    public void setName(String name) { this.name = name; }

    public String getRollNumber() { return rollNumber; }
    public void setRollNumber(String rollNumber) { this.rollNumber = rollNumber; }

    public String getEmail() { return email; }
    public void setEmail(String email) { this.email = email; }

    public String getCourse() { return course; }
    public void setCourse(String course) { this.course = course; }

    public Timestamp getCreatedAt() { return createdAt; }
    public void setCreatedAt(Timestamp createdAt) { this.createdAt = createdAt; }

    public List<StudentFile> getFiles() { return files; }
    public void setFiles(List<StudentFile> files) { this.files = files; }

    public void addFile(StudentFile file) {
        this.files.add(file);
    }

    public boolean hasFiles() {
        return files != null && !files.isEmpty();
    }
}
