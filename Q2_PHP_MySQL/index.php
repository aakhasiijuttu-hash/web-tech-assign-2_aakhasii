<?php
/**
 * Admin Dashboard - Student Records List
 * College Admission Portal - Question 2
 */

require_once __DIR__ . '/db.php';

// Handle optional search query
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

// Retrieve student records using prepared statements
if ($searchTerm !== '') {
    $searchWildcard = "%" . $searchTerm . "%";
    $stmt = $conn->prepare("
        SELECT s.*, COUNT(f.id) AS file_count 
        FROM students s 
        LEFT JOIN student_files f ON s.id = f.student_id 
        WHERE s.name LIKE ? OR s.roll_number LIKE ? OR s.course LIKE ? OR s.email LIKE ?
        GROUP BY s.id 
        ORDER BY s.id DESC
    ");
    $stmt->bind_param("ssss", $searchWildcard, $searchWildcard, $searchWildcard, $searchWildcard);
} else {
    $stmt = $conn->prepare("
        SELECT s.*, COUNT(f.id) AS file_count 
        FROM students s 
        LEFT JOIN student_files f ON s.id = f.student_id 
        GROUP BY s.id 
        ORDER BY s.id DESC
    ");
}

$stmt->execute();
$result = $stmt->get_result();
$students = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Feedback alert messages
$msg = $_GET['msg'] ?? '';
$err = $_GET['err'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Student Admission Portal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Navigation Header -->
    <header class="navbar">
        <a href="index.php" class="brand">
            <div class="brand-icon">SP</div>
            <div>
                <div class="brand-title">Student Admission Portal</div>
                <small style="color: var(--text-muted); font-size: 0.75rem;">Administrator Control Panel</small>
            </div>
        </a>
        <nav class="nav-links">
            <a href="index.php" class="btn btn-secondary btn-sm">All Students</a>
            <a href="add_student.php" class="btn btn-primary btn-sm">+ Add New Student</a>
            <a href="../Q1_Registration/index.html" class="btn btn-secondary btn-sm" target="_blank">Open Q1 Form &rarr;</a>
        </nav>
    </header>

    <!-- Main Content Container -->
    <main class="container">

        <!-- Informational banner explaining local demo context -->
        <div class="alert alert-info">
            <span>&#9432;</span>
            <div>
                <strong>Administrator Panel (Local College Demonstration)</strong>:
                Manage registered students, view admission details, and inspect uploaded verification documents.
            </div>
        </div>

        <!-- Dynamic Success / Error Notifications -->
        <?php if ($msg === 'added'): ?>
            <div class="alert alert-success">
                <span>&#10004;</span> Student record has been added successfully!
            </div>
        <?php elseif ($msg === 'updated'): ?>
            <div class="alert alert-success">
                <span>&#10004;</span> Student details have been updated successfully!
            </div>
        <?php elseif ($msg === 'deleted'): ?>
            <div class="alert alert-success">
                <span>&#10004;</span> Student record and associated files were deleted successfully!
            </div>
        <?php endif; ?>

        <?php if (!empty($err)): ?>
            <div class="alert alert-danger">
                <span>&#9888;</span> <?php echo sanitize($err); ?>
            </div>
        <?php endif; ?>

        <!-- Table Card -->
        <section class="card">
            <div class="card-header">
                <div>
                    <h1 class="card-title">Enrolled Students (<?php echo count($students); ?>)</h1>
                    <p class="card-desc">Review, search, view documents, or update student profiles.</p>
                </div>

                <!-- Search Functionality -->
                <form action="index.php" method="GET" class="search-bar-form">
                    <input 
                        type="text" 
                        name="search" 
                        class="search-input" 
                        placeholder="Search by name, roll no, course..." 
                        value="<?php echo sanitize($searchTerm); ?>"
                    >
                    <button type="submit" class="btn btn-primary btn-sm">Search</button>
                    <?php if ($searchTerm !== ''): ?>
                        <a href="index.php" class="btn btn-secondary btn-sm">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Student Records Table -->
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Roll Number</th>
                            <th>Student Name</th>
                            <th>Email Address</th>
                            <th>Course</th>
                            <th>Files</th>
                            <th>Registered On</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 36px; color: var(--text-muted);">
                                    <?php if ($searchTerm !== ''): ?>
                                        No students found matching "<strong><?php echo sanitize($searchTerm); ?></strong>".
                                        <br><br><a href="index.php" class="btn btn-secondary btn-sm">Reset Search Filter</a>
                                    <?php else: ?>
                                        No student records found in the database.
                                        <br><br><a href="add_student.php" class="btn btn-primary btn-sm">+ Add First Student</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($students as $row): ?>
                                <tr>
                                    <td><span class="badge-id">#<?php echo sanitize($row['id']); ?></span></td>
                                    <td><strong><?php echo sanitize($row['roll_number']); ?></strong></td>
                                    <td><?php echo sanitize($row['name']); ?></td>
                                    <td><?php echo sanitize($row['email']); ?></td>
                                    <td>
                                        <span class="badge badge-course"><?php echo sanitize($row['course']); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge" style="background:#e0e7ff; color:#3730a3;">
                                            &#128196; <?php echo intval($row['file_count']); ?> file(s)
                                        </span>
                                    </td>
                                    <td style="color: var(--text-muted); font-size: 0.84rem;">
                                        <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                                    </td>
                                    <td style="text-align: right;">
                                        <div style="display: inline-flex; gap: 6px;">
                                            <a href="view_student.php?id=<?php echo urlencode($row['id']); ?>" class="btn btn-sm btn-view" title="View Profile & Uploads">
                                                View
                                            </a>
                                            <a href="edit_student.php?id=<?php echo urlencode($row['id']); ?>" class="btn btn-sm btn-edit" title="Edit Student">
                                                Edit
                                            </a>
                                            <a href="delete_student.php?id=<?php echo urlencode($row['id']); ?>" class="btn btn-sm btn-delete" title="Delete Student">
                                                Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        Student Admission Portal &bull; Web Technology Assignment Question 2 &bull; PHP & MySQL
    </footer>

</body>
</html>
