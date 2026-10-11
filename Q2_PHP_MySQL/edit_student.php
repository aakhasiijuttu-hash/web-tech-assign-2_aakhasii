<?php
/**
 * Edit Student Profile
 * College Admission Portal - Question 2
 */

require_once __DIR__ . '/db.php';

// Validate student ID parameter
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    header("Location: index.php?err=Invalid student ID.");
    exit;
}

$studentId = intval($_GET['id']);
$errors = [];

// Allowed Courses Array matching Question 1
$courses = [
    "Computer Science and Engineering",
    "Information Technology",
    "Artificial Intelligence and Data Science",
    "Electronics and Communication Engineering",
    "Mechanical Engineering"
];

// Fetch current record
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
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

$name = $student['name'];
$rollNumber = $student['roll_number'];
$email = $student['email'];
$course = $student['course'];

// Handle POST update request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $rollNumber = trim($_POST['roll_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $course = trim($_POST['course'] ?? '');

    // Server-Side Validations
    if ($name === '') {
        $errors[] = "Student name is required.";
    } elseif (strlen($name) < 2) {
        $errors[] = "Student name must be at least 2 characters.";
    }

    if ($rollNumber === '') {
        $errors[] = "Roll number is required.";
    }

    if ($email === '') {
        $errors[] = "Email address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please provide a valid email address.";
    }

    if ($course === '' || !in_array($course, $courses)) {
        $errors[] = "Please select a valid course.";
    }

    // Check duplicate roll number on other students
    if (empty($errors)) {
        $dupStmt = $conn->prepare("SELECT id FROM students WHERE roll_number = ? AND id != ?");
        $dupStmt->bind_param("si", $rollNumber, $studentId);
        $dupStmt->execute();
        $dupStmt->store_result();

        if ($dupStmt->num_rows > 0) {
            $errors[] = "The roll number '<strong>" . sanitize($rollNumber) . "</strong>' belongs to another registered student.";
        }
        $dupStmt->close();
    }

    // Perform UPDATE with prepared statement
    if (empty($errors)) {
        $updateStmt = $conn->prepare("UPDATE students SET name = ?, roll_number = ?, email = ?, course = ? WHERE id = ?");
        $updateStmt->bind_param("ssssi", $name, $rollNumber, $email, $course, $studentId);

        if ($updateStmt->execute()) {
            $updateStmt->close();
            header("Location: index.php?msg=updated");
            exit;
        } else {
            $errors[] = "Failed to update record: " . $conn->error;
            $updateStmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student - Student Admission Portal</title>
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
            <a href="view_student.php?id=<?php echo urlencode($studentId); ?>" class="btn btn-secondary btn-sm">View Profile</a>
            <a href="index.php" class="btn btn-secondary btn-sm">&larr; Back to Students</a>
        </nav>
    </header>

    <main class="container" style="max-width: 680px;">
        <div class="card">
            <div class="card-header">
                <div>
                    <h1 class="card-title">Edit Student Record</h1>
                    <p class="card-desc">Update details for student <strong>#<?php echo sanitize($studentId); ?></strong> (<?php echo sanitize($student['roll_number']); ?>)</p>
                </div>
            </div>

            <!-- Error Feedback -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger" style="flex-direction: column; align-items: flex-start;">
                    <strong>Validation errors occurred:</strong>
                    <ul style="margin-top: 6px; padding-left: 20px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="edit_student.php?id=<?php echo urlencode($studentId); ?>" method="POST">
                <!-- Name -->
                <div class="form-group">
                    <label for="name" class="form-label">Student Full Name <span style="color: red;">*</span></label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        class="form-control" 
                        value="<?php echo sanitize($name); ?>" 
                        required
                    >
                </div>

                <!-- Roll Number -->
                <div class="form-group">
                    <label for="roll_number" class="form-label">Roll Number / Register Number <span style="color: red;">*</span></label>
                    <input 
                        type="text" 
                        id="roll_number" 
                        name="roll_number" 
                        class="form-control" 
                        value="<?php echo sanitize($rollNumber); ?>" 
                        required
                    >
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email" class="form-label">Email Address <span style="color: red;">*</span></label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-control" 
                        value="<?php echo sanitize($email); ?>" 
                        required
                    >
                </div>

                <!-- Course Selection -->
                <div class="form-group">
                    <label for="course" class="form-label">Course / Department <span style="color: red;">*</span></label>
                    <select id="course" name="course" class="form-control" required>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?php echo sanitize($c); ?>" <?php echo ($course === $c) ? 'selected' : ''; ?>>
                                <?php echo sanitize($c); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Actions -->
                <div style="display: flex; gap: 12px; margin-top: 24px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Update Student Details</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </main>

    <footer class="footer">
        Student Admission Portal &bull; Web Technology Assignment Question 2 &bull; PHP & MySQL
    </footer>

</body>
</html>
