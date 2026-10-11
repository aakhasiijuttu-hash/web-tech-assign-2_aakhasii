<?php
/**
 * Delete Student Record
 * Uses POST method for actual deletion to prevent accidental CSRF/GET deletion.
 * Cleans up both database records and physical uploaded files on disk.
 * College Admission Portal - Question 2
 */

require_once __DIR__ . '/db.php';

// Handle POST request (actual deletion execution)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['student_id']) || !filter_var($_POST['student_id'], FILTER_VALIDATE_INT)) {
        header("Location: index.php?err=Invalid student ID for deletion.");
        exit;
    }

    $studentId = intval($_POST['student_id']);

    // 1. Fetch all associated physical files before deleting database record
    $fileStmt = $conn->prepare("SELECT stored_name FROM student_files WHERE student_id = ?");
    $fileStmt->bind_param("i", $studentId);
    $fileStmt->execute();
    $fileResult = $fileStmt->get_result();

    while ($row = $fileResult->fetch_assoc()) {
        $diskPath = UPLOAD_DIR . basename($row['stored_name']);
        if (file_exists($diskPath) && is_file($diskPath)) {
            @unlink($diskPath); // Delete physical file from disk
        }
    }
    $fileStmt->close();

    // 2. Delete student record (ON DELETE CASCADE in MySQL will also delete student_files records)
    $delStmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $delStmt->bind_param("i", $studentId);

    if ($delStmt->execute()) {
        $delStmt->close();
        header("Location: index.php?msg=deleted");
        exit;
    } else {
        $error = "Failed to delete student: " . $conn->error;
        $delStmt->close();
        header("Location: index.php?err=" . urlencode($error));
        exit;
    }
}

// If accessed via GET, display confirmation dialog
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    header("Location: index.php?err=Invalid student ID.");
    exit;
}

$studentId = intval($_GET['id']);

// Fetch student record and file count for confirmation display
$stmt = $conn->prepare("
    SELECT s.*, COUNT(f.id) AS file_count 
    FROM students s 
    LEFT JOIN student_files f ON s.id = f.student_id 
    WHERE s.id = ? 
    GROUP BY s.id
");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    header("Location: index.php?err=Student record not found.");
    exit;
}

$student = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Deletion - Student Admission Portal</title>
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
            <a href="index.php" class="btn btn-secondary btn-sm">&larr; Back to Students</a>
        </nav>
    </header>

    <main class="container" style="max-width: 580px;">
        <div class="card" style="border-top: 5px solid #dc2626;">
            <div class="card-header">
                <div>
                    <h1 class="card-title" style="color: #dc2626;">Confirm Deletion</h1>
                    <p class="card-desc">This action is permanent and cannot be undone.</p>
                </div>
            </div>

            <div class="alert alert-danger">
                <span>&#9888;</span>
                <div>
                    Are you sure you want to permanently delete student:
                    <br>
                    <strong><?php echo sanitize($student['name']); ?></strong> (Roll No: <strong><?php echo sanitize($student['roll_number']); ?></strong>)?
                </div>
            </div>

            <div style="background: #f8fafc; padding: 16px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); margin-bottom: 24px;">
                <p><strong>Department:</strong> <?php echo sanitize($student['course']); ?></p>
                <p><strong>Email:</strong> <?php echo sanitize($student['email']); ?></p>
                <p><strong>Associated Files:</strong> <?php echo intval($student['file_count']); ?> uploaded document(s) will also be deleted from disk and database.</p>
            </div>

            <!-- POST Form for Secure Deletion -->
            <form action="delete_student.php" method="POST">
                <input type="hidden" name="student_id" value="<?php echo sanitize($studentId); ?>">
                
                <div style="display: flex; gap: 12px;">
                    <button type="submit" class="btn btn-delete" style="flex: 1; padding: 12px; font-weight: 700;">
                        Yes, Confirm & Delete Record
                    </button>
                    <a href="index.php" class="btn btn-secondary" style="padding: 12px 24px;">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </main>

    <footer class="footer">
        Student Admission Portal &bull; Web Technology Assignment Question 2 &bull; PHP & MySQL
    </footer>

</body>
</html>
