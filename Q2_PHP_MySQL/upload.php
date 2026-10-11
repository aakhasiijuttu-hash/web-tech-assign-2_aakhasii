<?php
/**
 * Student Document / Image Upload Handler
 * Validates file constraints on the server, creates unique filenames,
 * inserts metadata into MySQL, and cleans up orphaned files on failure.
 * College Admission Portal - Question 2
 */

require_once __DIR__ . '/db.php';

$errors = [];
$studentId = isset($_REQUEST['student_id']) ? intval($_REQUEST['student_id']) : 0;

// Validate that student exists in database
if ($studentId <= 0) {
    header("Location: index.php?err=Invalid student ID.");
    exit;
}

$stmt = $conn->prepare("SELECT id, name, roll_number FROM students WHERE id = ?");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$studentResult = $stmt->get_result();

if ($studentResult->num_rows === 0) {
    $stmt->close();
    header("Location: index.php?err=Student not found.");
    exit;
}

$student = $studentResult->fetch_assoc();
$stmt->close();

// Process POST upload submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['student_file']) || $_FILES['student_file']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = "Please select a file to upload.";
    } else {
        $file = $_FILES['student_file'];

        // 1. Check for standard PHP upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $errors[] = "The uploaded file exceeds the allowed upload limit.";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $errors[] = "The file was only partially uploaded. Please try again.";
                    break;
                default:
                    $errors[] = "An unexpected upload error occurred (Code: " . $file['error'] . ").";
                    break;
            }
        }

        // 2. Enforce 2 MB maximum file size (2 * 1024 * 1024 bytes)
        $maxSizeBytes = 2 * 1024 * 1024;
        if (empty($errors) && $file['size'] > $maxSizeBytes) {
            $errors[] = "File size (" . round($file['size'] / (1024 * 1024), 2) . " MB) exceeds the 2 MB limit.";
        }

        // 3. Strict File Extension Validation
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
        $originalName = basename($file['name']);
        $fileExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (empty($errors) && !in_array($fileExt, $allowedExtensions, true)) {
            $errors[] = "Invalid file type. Only JPG, JPEG, PNG, and PDF files are allowed.";
        }

        // 4. Server-Side MIME Type Validation (inspects actual file content)
        $allowedMimeMap = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'pdf'  => 'application/pdf'
        ];

        if (empty($errors)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $detectedMime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            $expectedMime = $allowedMimeMap[$fileExt] ?? '';

            if ($detectedMime !== $expectedMime && !($fileExt === 'jpg' && $detectedMime === 'image/jpeg')) {
                $errors[] = "File content does not match its extension. Detected MIME: " . sanitize($detectedMime);
            }
        }

        // 5. Generate secure, unique filename to prevent collisions and overwrite attacks
        if (empty($errors)) {
            $uniquePrefix = 'doc_' . $studentId . '_' . time() . '_' . bin2hex(random_bytes(6));
            $storedName = $uniquePrefix . '.' . $fileExt;
            $destination = UPLOAD_DIR . $storedName;
            $relativeDbPath = 'storage/uploads/' . $storedName;

            // Move uploaded file to storage/uploads/
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                // Insert metadata into student_files table using prepared statement
                $insertStmt = $conn->prepare("
                    INSERT INTO student_files (student_id, file_name, stored_name, file_path, file_type) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $insertStmt->bind_param("issss", $studentId, $originalName, $storedName, $relativeDbPath, $detectedMime);

                if ($insertStmt->execute()) {
                    $insertStmt->close();
                    header("Location: view_student.php?id=" . urlencode($studentId) . "&upload=success");
                    exit;
                } else {
                    // Database insert failed: clean up orphaned physical file
                    if (file_exists($destination)) {
                        @unlink($destination);
                    }
                    $errors[] = "Database insert failed. Cleaned up temporary upload. Error: " . $conn->error;
                    $insertStmt->close();
                }
            } else {
                $errors[] = "Failed to move uploaded file to storage directory. Please check folder permissions.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Document - Student Admission Portal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header class="navbar">
        <a href="index.php" class="brand">
            <div class="brand-icon">SP</div>
            <div>
                <div class="brand-title">Student Admission Portal</div>
                <small style="color: var(--text-muted); font-size: 0.75rem;">Administrator Control Panel</small>
            </div>
        </a>
        <nav class="nav-links">
            <a href="view_student.php?id=<?php echo urlencode($studentId); ?>" class="btn btn-secondary btn-sm">&larr; Back to Student Profile</a>
        </nav>
    </header>

    <main class="container" style="max-width: 640px;">
        <div class="card">
            <div class="card-header">
                <div>
                    <h1 class="card-title">Upload Student Document</h1>
                    <p class="card-desc">
                        Uploading verification file for <strong><?php echo sanitize($student['name']); ?></strong> (<?php echo sanitize($student['roll_number']); ?>)
                    </p>
                </div>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger" style="flex-direction: column; align-items: flex-start;">
                    <strong>Upload Failed:</strong>
                    <ul style="margin-top: 6px; padding-left: 20px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- File Upload Form -->
            <form action="upload.php?student_id=<?php echo urlencode($studentId); ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="student_id" value="<?php echo sanitize($studentId); ?>">

                <div class="file-upload-box">
                    <p style="font-weight: 600; color: #334155; margin-bottom: 8px;">Select a file from your computer</p>
                    <input 
                        type="file" 
                        name="student_file" 
                        id="student_file" 
                        class="form-control" 
                        accept=".jpg,.jpeg,.png,.pdf" 
                        required
                    >
                    <p class="form-helper" style="margin-top: 10px;">
                        Accepted formats: <strong>JPG, JPEG, PNG, PDF</strong> &bull; Max allowed size: <strong>2 MB</strong>
                    </p>
                </div>

                <div style="display: flex; gap: 12px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Upload & Save Document</button>
                    <a href="view_student.php?id=<?php echo urlencode($studentId); ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </main>

    <footer class="footer">
        Student Admission Portal &bull; Web Technology Assignment Question 2 &bull; PHP & MySQL
    </footer>

</body>
</html>
