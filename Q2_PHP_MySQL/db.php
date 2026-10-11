<?php
/**
 * Database Connection & Configuration File
 * College Admission Portal - Question 2 (PHP & MySQL)
 */

// Enable error reporting for development/debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Local XAMPP MySQL Configuration Settings
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'student_portal');
define('DB_PORT', 3306);

// Shared File Upload Directory Configuration
// Points to the project root: storage/uploads/
define('UPLOAD_DIR', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR);

// Automatically create the upload directory if it does not exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

try {
    // Establish connection using MySQLi
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    
    // Set charset to utf8mb4 for universal character support
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    // Graceful error display if XAMPP MySQL is stopped or DB is not yet imported
    $errorMsg = $e->getMessage();
    die("
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <title>Database Connection Error</title>
        <style>
            body { font-family: system-ui, sans-serif; background: #f4f6fb; padding: 40px 20px; display: flex; justify-content: center; }
            .error-box { max-width: 580px; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); border-left: 6px solid #dc2626; }
            h2 { color: #dc2626; margin-top: 0; }
            p { color: #334155; line-height: 1.6; }
            code { background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-weight: bold; }
            ol { color: #475569; padding-left: 20px; }
            li { margin-bottom: 8px; }
        </style>
    </head>
    <body>
        <div class='error-box'>
            <h2>Database Connection Failed</h2>
            <p>Could not connect to the <strong>student_portal</strong> database via MySQLi.</p>
            <p><strong>Error details:</strong> <code>" . htmlspecialchars($errorMsg) . "</code></p>
            <h3>Troubleshooting Checklist for XAMPP:</h3>
            <ol>
                <li>Open <strong>XAMPP Control Panel</strong> and verify that the <strong>MySQL</strong> service is started (green).</li>
                <li>Open <code>http://localhost/phpmyadmin/</code> in your browser.</li>
                <li>Import the SQL file located at: <code>database/student_portal.sql</code>.</li>
                <li>Refresh this page once the database is imported.</li>
            </ol>
        </div>
    </body>
    </html>
    ");
}

/**
 * Helper function to sanitize text input for safe HTML output
 * Prevents Cross-Site Scripting (XSS)
 */
function sanitize($data) {
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}
?>
