<?php
include('../includes/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $name = $_POST['name'];
    $course_name = $_POST['course_name'];
    $semester = $_POST['semester'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    if ($password == $confirm_password) {
        // Check if an image file is uploaded
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = $_FILES['profile_image']['type'];

            if (in_array($file_type, $allowed_types)) {
                $upload_dir = '../uploads/';
                $file_name = basename($_FILES['profile_image']['name']);
                $target_file = $upload_dir . time() . '_' . $file_name; // Add timestamp to avoid duplicates

                // Move uploaded file to server directory
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
                    // File upload successful
                } else {
                    echo "Failed to upload image!";
                    exit();
                }
            } else {
                echo "Invalid file type. Only JPG, PNG, and GIF are allowed.";
                exit();
            }
        } else {
            // No image uploaded
            $target_file = null;
        }

        $conn->begin_transaction();

        try {
            // Insert into students table
            $student_sql = "INSERT INTO students (username, name, course_name, semester, password, profile_image) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($student_sql);
            $stmt->bind_param("sssdss", $username, $name, $course_name, $semester, $hashed_password, $target_file);

            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }

            // Get the last inserted student ID
            $student_id = $stmt->insert_id;

            // Insert into registrations table
            $registration_sql = "INSERT INTO registrations (student_id) VALUES (?)";
            $stmt = $conn->prepare($registration_sql);
            $stmt->bind_param("i", $student_id);

            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }

            $conn->commit();
            echo "Registration successful!";
            header("Location: login.php");
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            echo "Error: " . $e->getMessage();
        }

        $stmt->close();
    } else {
        echo "Passwords do not match!";
    }

    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2 class="mt-5">Student Registration</h2>
        <form method="post" action="register.php" enctype="multipart/form-data">
    <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" class="form-control" id="username" name="username" required>
    </div>
    <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <input type="text" class="form-control" id="name" name="name" required>
    </div>
    <div class="mb-3">
        <label for="course_name" class="form-label">Course Name</label>
        <input type="text" class="form-control" id="course_name" name="course_name" required>
    </div>
    <div class="mb-3">
        <label for="semester" class="form-label">Semester</label>
        <input type="number" class="form-control" id="semester" name="semester" required>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required>
    </div>
    <div class="mb-3">
        <label for="confirm_password" class="form-label">Confirm Password</label>
        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
    </div>
    <!-- Image Upload Field -->
    <div class="mb-3">
        <label for="profile_image" class="form-label">Upload Profile Image</label>
        <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
    </div>
    <button type="submit" class="btn btn-primary">Register</button>
</form>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>