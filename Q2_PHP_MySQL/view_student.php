<?php
/**
 * View Student Profile & Uploaded Documents
 * Displays student records, image previews, and PDF download links.
 * College Admission Portal - Question 2
 */

require_once __DIR__ . '/db.php';

// Validate student ID
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    header("Location: index.php?err=Invalid student ID.");
    exit;
}

$studentId = intval($_GET['id']);

// Fetch student details using prepared statement
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$studentResult = $stmt->get_result();

if ($studentResult->num_rows === 0) {
    $stmt->close();
    header("Location: index.php?err=Student record not found.");
    exit;
}

$student = $studentResult->fetch_assoc();
$stmt->close();

// Fetch student uploaded files
$fileStmt = $conn->prepare("SELECT * FROM student_files WHERE student_id = ? ORDER BY id DESC");
$fileStmt->bind_param("i", $studentId);
$fileStmt->execute();
$filesResult = $fileStmt->get_result();
$files = $filesResult->fetch_all(MYSQLI_ASSOC);
$fileStmt->close();

$uploadSuccess = isset($_GET['upload']) && $_GET['upload'] === 'success';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo sanitize($student['name']); ?> - Student Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Header Navigation -->
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
            <a href="upload.php?student_id=<?php echo urlencode($studentId); ?>" class="btn btn-primary btn-sm">&#8682; Upload Document</a>
            <a href="edit_student.php?id=<?php echo urlencode($studentId); ?>" class="btn btn-secondary btn-sm">Edit Profile</a>
        </nav>
    </header>

    <main class="container">

        <!-- Upload Success Feedback -->
        <?php if ($uploadSuccess): ?>
            <div class="alert alert-success">
                <span>&#10004;</span> File uploaded and saved successfully!
            </div>
        <?php endif; ?>

        <!-- Student Profile Card -->
        <section class="card">
            <div class="student-profile-header">
                <div class="student-avatar-placeholder">
                    <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
                </div>
                <div>
                    <h1 class="card-title"><?php echo sanitize($student['name']); ?></h1>
                    <div style="display: flex; gap: 8px; align-items: center; margin-top: 6px; flex-wrap: wrap;">
                        <span class="badge-id">Roll: <?php echo sanitize($student['roll_number']); ?></span>
                        <span class="badge badge-course"><?php echo sanitize($student['course']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Profile Attributes Grid -->
            <div class="student-info-grid">
                <div class="info-item">
                    <div class="info-label">Student ID</div>
                    <div class="info-value">#<?php echo sanitize($student['id']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email Address</div>
                    <div class="info-value" style="font-size: 0.92rem; word-break: break-all;">
                        <?php echo sanitize($student['email']); ?>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Department / Course</div>
                    <div class="info-value"><?php echo sanitize($student['course']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Admission Date</div>
                    <div class="info-value"><?php echo date('M d, Y', strtotime($student['created_at'])); ?></div>
                </div>
            </div>
        </section>

        <!-- Uploaded Documents & Media Section -->
        <section class="card">
            <div class="card-header">
                <div>
                    <h2 class="card-title" style="font-size: 1.25rem;">Uploaded Documents & Photos (<?php echo count($files); ?>)</h2>
                    <p class="card-desc">Submitted verification proofs, certificates, and identification files.</p>
                </div>
                <a href="upload.php?student_id=<?php echo urlencode($studentId); ?>" class="btn btn-primary btn-sm">
                    + Upload New File
                </a>
            </div>

            <?php if (empty($files)): ?>
                <div style="text-align: center; padding: 40px 20px; background: #f8fafc; border-radius: var(--radius-md); border: 1px dashed var(--border-color);">
                    <div style="font-size: 2.2rem; margin-bottom: 8px;">&#128194;</div>
                    <h3 style="color: #334155; font-size: 1.05rem;">No files uploaded yet</h3>
                    <p style="color: var(--text-muted); font-size: 0.88rem; margin: 4px 0 16px;">
                        Upload the student's ID proof, photograph, or admission certificates.
                    </p>
                    <a href="upload.php?student_id=<?php echo urlencode($studentId); ?>" class="btn btn-primary btn-sm">
                        Upload Document Now
                    </a>
                </div>
            <?php else: ?>
                <div class="file-grid">
                    <?php foreach ($files as $file): ?>
                        <?php 
                            $isImage = strpos($file['file_type'], 'image/') === 0;
                            $fileViewerUrl = "file_viewer.php?id=" . urlencode($file['id']);
                            $downloadUrl = "file_viewer.php?id=" . urlencode($file['id']) . "&download=1";
                        ?>
                        <div class="file-card">
                            <div class="file-preview">
                                <?php if ($isImage): ?>
                                    <img src="<?php echo $fileViewerUrl; ?>" alt="<?php echo sanitize($file['file_name']); ?>" loading="lazy">
                                <?php else: ?>
                                    <div class="file-preview-icon" title="PDF Document">&#128196;</div>
                                <?php endif; ?>
                            </div>
                            <div class="file-details">
                                <div class="file-title" title="<?php echo sanitize($file['file_name']); ?>">
                                    <?php echo sanitize($file['file_name']); ?>
                                </div>
                                <div class="file-meta">
                                    Type: <strong><?php echo $isImage ? 'Image' : 'PDF Document'; ?></strong>
                                </div>
                                <div class="file-meta">
                                    Uploaded: <?php echo date('M d, Y', strtotime($file['upload_date'])); ?>
                                </div>
                                <div class="file-actions">
                                    <a href="<?php echo $fileViewerUrl; ?>" target="_blank" class="btn btn-view btn-sm" style="flex: 1;">
                                        Preview
                                    </a>
                                    <a href="<?php echo $downloadUrl; ?>" class="btn btn-secondary btn-sm" style="flex: 1;">
                                        Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    </main>

    <footer class="footer">
        Student Admission Portal &bull; Web Technology Assignment Question 2 &bull; PHP & MySQL
    </footer>

</body>
</html>
