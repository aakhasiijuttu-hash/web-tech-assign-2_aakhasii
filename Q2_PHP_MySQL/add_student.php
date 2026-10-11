<?php
/**
 * Create New Student Record
 * College Admission Portal - Question 2
 */

require_once __DIR__ . '/db.php';

$errors = [];
$name = '';
$rollNumber = '';
$email = '';
$course = '';

// Allowed Courses Array matching Question 1
$courses = [
    "Computer Science and Engineering",
    "Information Technology",
    "Artificial Intelligence and Data Science",
    "Electronics and Communication Engineering",
    "Mechanical Engineering"
];

// Handle POST request
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
        $errors[] = "Roll number / Register number is required.";
    }

    if ($email === '') {
        $errors[] = "Email address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please provide a valid email address.";
    }

    if ($course === '' || !in_array($course, $courses)) {
        $errors[] = "Please select a valid course / department.";
    }

    // Check duplicate roll number using prepared statement
    if (empty($errors)) {
        $checkStmt = $conn->prepare("SELECT id FROM students WHERE roll_number = ?");
        $checkStmt->bind_param("s", $rollNumber);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $errors[] = "A student with Roll Number '<strong>" . sanitize($rollNumber) . "</strong>' is already registered.";
        }
        $checkStmt->close();
    }

    // Insert record if no validation errors
    if (empty($errors)) {
        $insertStmt = $conn->prepare("INSERT INTO students (name, roll_number, email, course) VALUES (?, ?, ?, ?)");
        $insertStmt->bind_param("ssss", $name, $rollNumber, $email, $course);

        if ($insertStmt->execute()) {
            $newStudentId = $insertStmt->insert_id;
            $insertStmt->close();
            // Redirect to dashboard with success parameter
            header("Location: index.php?msg=added");
            exit;
        } else {
            $errors[] = "Database insertion failed: " . $conn->error;
            $insertStmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student - Student Admission Portal</title>
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
        </nav>
    </header>

    <main class="container" style="max-width: 680px;">
        <div class="card">
            <div class="card-header">
                <div>
                    <h1 class="card-title">Add New Student</h1>
                    <p class="card-desc">Enter admission credentials to register a student record.</p>
                </div>
            </div>

            <!-- Server Error Notifications -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger" style="flex-direction: column; align-items: flex-start;">
                    <strong>Please resolve the following errors:</strong>
                    <ul style="margin-top: 6px; padding-left: 20px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Student Creation Form -->
            <form action="add_student.php" method="POST">
                <!-- Name -->
                <div class="form-group">
                    <label for="name" class="form-label">Student Full Name <span style="color: red;">*</span></label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        class="form-control" 
                        placeholder="e.g. Priyadharshini" 
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
                        placeholder="e.g. 21CS045" 
                        value="<?php echo sanitize($rollNumber); ?>" 
                        required
                    >
                    <div class="form-helper">Roll number must be unique for each student.</div>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email" class="form-label">Email Address <span style="color: red;">*</span></label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-control" 
                        placeholder="e.g. student@college.edu" 
                        value="<?php echo sanitize($email); ?>" 
                        required
                    >
                </div>

                <!-- Course Dropdown -->
                <div class="form-group">
                    <label for="course" class="form-label">Course / Department <span style="color: red;">*</span></label>
                    <select id="course" name="course" class="form-control" required>
                        <option value="">-- Select Department --</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?php echo sanitize($c); ?>" <?php echo ($course === $c) ? 'selected' : ''; ?>>
                                <?php echo sanitize($c); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Actions -->
                <div style="display: flex; gap: 12px; margin-top: 24px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Save Student Record</button>
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
