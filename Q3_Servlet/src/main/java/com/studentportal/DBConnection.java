package com.studentportal;

import java.io.File;
import java.io.InputStream;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;
import java.util.Properties;

/**
 * Reusable Database Connection Provider and Shared Storage Resolver.
 * Connects to MySQL using JDBC and loads credentials
 * from db.properties or environment variables.
 */
public class DBConnection {

    private static String jdbcUrl;
    private static String dbUser;
    private static String dbPassword;
    private static Path uploadDirectory;

    static {
        loadConfiguration();
    }

    private static void loadConfiguration() {
        Properties props = new Properties();

        // 1. Load db.properties from the classpath
        try (InputStream is = DBConnection.class
                .getClassLoader()
                .getResourceAsStream("db.properties")) {

            if (is != null) {
                props.load(is);
            }

        } catch (Exception e) {
            System.err.println(
                "[DBConnection] Warning: Could not read db.properties: "
                + e.getMessage()
            );
        }

        // 2. Read environment variables
        String envUrl = System.getenv("STUDENT_PORTAL_DB_URL");
        String envUser = System.getenv("STUDENT_PORTAL_DB_USER");
        String envPass = System.getenv("STUDENT_PORTAL_DB_PASSWORD");
        String envUpload = System.getenv("STUDENT_PORTAL_UPLOAD_DIR");

        String host = props.getProperty("db.host", "localhost");
        String port = props.getProperty("db.port", "3306");
        String dbName = props.getProperty("db.name", "student_portal");

        String defaultUrl = "jdbc:mysql://" + host + ":" + port + "/"
                + dbName
                + "?useSSL=false&allowPublicKeyRetrieval=true"
                + "&characterEncoding=UTF-8&serverTimezone=UTC";

        jdbcUrl = (envUrl != null && !envUrl.trim().isEmpty())
                ? envUrl.trim()
                : props.getProperty("db.url", defaultUrl);

        dbUser = (envUser != null)
                ? envUser.trim()
                : props.getProperty("db.user", "root");

        dbPassword = (envPass != null)
                ? envPass
                : props.getProperty("db.password", "");

        // 3. Configure the shared uploads directory
        String configuredUpload = (envUpload != null
                && !envUpload.trim().isEmpty())
                ? envUpload.trim()
                : props.getProperty("upload.dir", "");

        if (!configuredUpload.trim().isEmpty()) {
            uploadDirectory = Paths.get(configuredUpload)
                    .toAbsolutePath()
                    .normalize();
        } else {
            uploadDirectory = resolveDefaultUploadDirectory();
        }

        // 4. Create the uploads directory if necessary
        try {
            File dir = uploadDirectory.toFile();

            if (!dir.exists() && !dir.mkdirs()) {
                System.err.println(
                    "[DBConnection] Warning: Could not create upload directory: "
                    + dir.getAbsolutePath()
                );
            }

            System.out.println(
                "[DBConnection] Upload directory: "
                + uploadDirectory.toAbsolutePath()
            );

        } catch (Exception e) {
            System.err.println(
                "[DBConnection] Warning: Failed to create upload directory: "
                + e.getMessage()
            );
        }

        // 5. Register the MySQL JDBC driver
        try {
            Class.forName("com.mysql.cj.jdbc.Driver");

        } catch (ClassNotFoundException e) {
            throw new RuntimeException(
                "MySQL JDBC Driver not found in the classpath!", e
            );
        }
    }

    /**
     * Finds storage/uploads in the current directory
     * or one of its parent directories.
     */
    private static Path resolveDefaultUploadDirectory() {

        Path current = Paths.get("")
                .toAbsolutePath()
                .normalize();

        Path directory = current;

        for (int i = 0; i < 6 && directory != null; i++) {

            Path candidate = directory.resolve("storage/uploads");

            if (candidate.toFile().isDirectory()) {
                System.out.println(
                    "[DBConnection] Upload directory found: "
                    + candidate
                );

                return candidate.normalize();
            }

            directory = directory.getParent();
        }

        // Fallback if the directory cannot be found
        Path fallback = current.resolve("storage/uploads")
                .normalize();

        System.out.println(
            "[DBConnection] Upload directory fallback: "
            + fallback
        );

        return fallback;
    }

    /**
     * Obtains a new JDBC database connection.
     *
     * @return Connection instance
     * @throws SQLException if a database connection error occurs
     */
    public static Connection getConnection() throws SQLException {
        return DriverManager.getConnection(
            jdbcUrl,
            dbUser,
            dbPassword
        );
    }

    /**
     * Returns the resolved storage/uploads directory.
     *
     * @return Upload directory path
     */
    public static Path getUploadDirectory() {
        return uploadDirectory;
    }
}