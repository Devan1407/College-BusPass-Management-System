<?php
session_start();
include('../includes/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Fetch student data along with approval status
    $sql = "SELECT students.id, students.password, registrations.is_approved 
            FROM students 
            INNER JOIN registrations ON students.id = registrations.student_id 
            WHERE students.username = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $hashed_password, $is_approved);
            $stmt->fetch();

            // Check if the student's registration is approved
            if ($is_approved == 1) {
                // Verify the password
                if (password_verify($password, $hashed_password)) {
                    $_SESSION['user_id'] = $id;
                    $_SESSION['username'] = $username;
                    $_SESSION['user_type'] = 'student';
                    header("Location: student_dashboard.php");
                    exit();
                } else {
                    $error_message = "Invalid password!";
                }
            } else {
                $error_message = "Your registration has not been approved yet. Please wait for admin approval.";
            }
        } else {
            $error_message = "No user found with this username!";
        }

        $stmt->close();
    } else {
        $error_message = "SQL error: " . $conn->error;
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('https://source.unsplash.com/1600x900/?education,login');
            background-size: cover;
            background-position: center;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-container h2 {
            text-align: center;
            color: #007bff;
            margin-bottom: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .form-control {
            border-radius: 25px;
            padding: 0.75rem 1.5rem;
        }
        .btn-custom {
            background-color: #007bff;
            color: #fff;
            border-radius: 25px;
            padding: 0.75rem;
            width: 100%;
            font-size: 1.1rem;
        }
        .btn-custom:hover {
            background-color: #0056b3;
        }
        .alert-custom {
            margin-top: 15px;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <h2>Student Login</h2>

        <!-- Error Message Display -->
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-custom">
                <?= $error_message ?>
            </div>
        <?php endif; ?>

        <form method="post" action="login.php">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-custom">Login</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
