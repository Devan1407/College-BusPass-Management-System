<?php
session_start();
include('../includes/db.php');

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch all students
$students_sql = "SELECT id, name FROM students";
$students = $conn->query($students_sql);

// Handle notification submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_notification'])) {
    $student_id = $_POST['student_id'];
    $message = $_POST['message'];

    // Insert notification into the database
    $stmt = $conn->prepare("INSERT INTO notifications (student_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $student_id, $message);
    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Notification sent successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Failed to send notification.</div>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('../images/mac.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            height: 100vh;
            color: white;
        }

        .container {
            background-color: rgba(0, 0, 0, 0.5);
            padding: 20px;
            border-radius: 8px;
        }

        .image-container {
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .image-container img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }

        .dashboard-text {
            color: black;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Bus Pass Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="manage_routes.php">Manage Routes</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage_buses.php">Manage Buses</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage_registrations.php">Manage Registrations</a></li>
                    <li class="nav-item"><a class="nav-link" href="approve_renewal.php">Manage Renewals</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_reports.php">View Reports</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1>Welcome to the Admin Dashboard</h1>
        <p>Select an option from the menu to manage the system.</p>

        <div class="image-container mt-5">
            <div class="dashboard-text">
                <h2>Manage your bus routes, registrations, and reports all in one place.</h2>
                <p>This dashboard allows you to keep track of everything related to the college bus pass system. Click on the options above to get started.</p>
            </div>
        </div>

        <!-- Notification Form -->
        <div class="mt-5 p-4 bg-light text-dark rounded">
            <h2>Send Notification to Student</h2>
            <form method="post">
                <div class="mb-3">
                    <label for="student_id" class="form-label">Select Student</label>
                    <select id="student_id" name="student_id" class="form-select" required>
                        <?php while ($row = $students->fetch_assoc()): ?>
                            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="message" class="form-label">Notification Message</label>
                    <textarea id="message" name="message" class="form-control" rows="3" placeholder="Enter notification message here..." required></textarea>
                </div>
                <button type="submit" name="send_notification" class="btn btn-primary">Send Notification</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>