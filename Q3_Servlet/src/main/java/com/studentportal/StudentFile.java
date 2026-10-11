package com.studentportal;

import java.sql.Timestamp;

/**
 * Model representing an uploaded student document/image in student_files table.
 */
public class StudentFile {
    private int id;
    private int studentId;
    private String fileName;
    private String storedName;
    private String filePath;
    private String fileType;
    private Timestamp uploadDate;

    public StudentFile() {
    }

    public StudentFile(int id, int studentId, String fileName, String storedName, String filePath, String fileType, Timestamp uploadDate) {
        this.id = id;
        this.studentId = studentId;
        this.fileName = fileName;
        this.storedName = storedName;
        this.filePath = filePath;
        this.fileType = fileType;
        this.uploadDate = uploadDate;
    }

    public boolean isImage() {
        return fileType != null && fileType.startsWith("image/");
    }

    public boolean isPdf() {
        return fileType != null && fileType.equalsIgnoreCase("application/pdf");
    }

    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public int getStudentId() { return studentId; }
    public void setStudentId(int studentId) { this.studentId = studentId; }

    public String getFileName() { return fileName; }
    public void setFileName(String fileName) { this.fileName = fileName; }

    public String getStoredName() { return storedName; }
    public void setStoredName(String storedName) { this.storedName = storedName; }

    public String getFilePath() { return filePath; }
    public void setFilePath(String filePath) { this.filePath = filePath; }

    public String getFileType() { return fileType; }
    public void setFileType(String fileType) { this.fileType = fileType; }

    public Timestamp getUploadDate() { return uploadDate; }
    public void setUploadDate(Timestamp uploadDate) { this.uploadDate = uploadDate; }
}
