<?php
session_start();
include('../includes/db.php');

// Check if the student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'student') {
    header("Location: login.php");
    exit();
}

// Fetch student details including profile image
$student_id = $_SESSION['user_id'];
$sql_student = "SELECT name, profile_image FROM students WHERE id = ?";
$stmt_student = $conn->prepare($sql_student);
$stmt_student->bind_param("i", $student_id);
$stmt_student->execute();
$result_student = $stmt_student->get_result();
$student = $result_student->fetch_assoc();

$stmt_student->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
        background-color: #f8f9fa;
        margin: 0;
        padding: 0;
        font-family: 'Arial', sans-serif;
    }
    .navbar {
        margin-bottom: 0;
        padding: 10px 20px;
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        position: sticky;
        top: 0;
        z-index: 1000;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-bottom: 2px solid #0056b3;
    }
    .navbar-brand {
        font-size: 1.75rem;
        font-weight: bold;
        color: white;
        letter-spacing: 1px;
    }
    .navbar .nav-link {
        color: white !important;
        transition: color 0.3s ease-in-out;
        font-weight: 500;
        margin: 0 10px;
        position: relative;
    }
    .navbar .nav-link:hover {
        color: #ffc107 !important;
    }
    .navbar .nav-link::after {
        content: '';
        display: block;
        width: 0;
        height: 2px;
        background: #ffc107;
        transition: width 0.3s;
        position: absolute;
        bottom: -5px;
        left: 0;
    }
    .navbar .nav-link:hover::after {
        width: 100%;
    }
    .profile-img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #fff;
        margin-left: 10px;
    }
    footer {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        padding: 30px;
        text-align: center;
        font-size: 0.9rem;
        letter-spacing: 1px;
        box-shadow: 0 -4px 6px rgba(0, 0, 0, 0.1);
        position: relative;
        border-top: 5px solid #0056b3;
    }
    footer p {
        margin: 0;
        font-size: 0.85rem;
        font-weight: 400;
    }
    footer a {
        color: #ffc107;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s ease-in-out;
    }
    footer a:hover {
        color: #ffd966;
    }
    .hero-section {
        position: relative;
        background: url('../images/MACFAST1.jpg') no-repeat center center/cover;
        height: 95vh;
        width: 100%;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        margin-bottom: 0;
        border-radius: 0;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    }
    .hero-section::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to bottom, rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.8));
        z-index: 1;
    }
    .hero-section h1 {
        font-size: 2.5rem;
        font-weight: bold;
        text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.5);
        position: relative;
        z-index: 2;
        padding: 30px 20px;
    }
    .dashboard-section {
        padding-top: 50px;
        text-align: center;
    }
    .card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease-in-out;
        padding: 20px;
        background-color: white;
        margin: 15px;
    }
    .card:hover {
        transform: translateY(-10px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
    }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Bus Pass Management</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="#">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="bus_bookings.php">Bookings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="renew_pass.php">Bus Pass Renewal</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="edit_profile.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="edit_profile.php">
                            <img src="<?php echo $student['profile_image'] ? '../uploads/'.$student['profile_image'] : 'default-avatar.png'; ?>" 
                                 class="profile-img" alt="Profile Image">
                            <?php echo htmlspecialchars($student['name']); ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>


    <!-- Hero Section -->
    <div class="hero-section">
        <h1>Welcome to Your Dashboard</h1>
    </div>

    <!-- Notifications Section -->
    <div class="container mt-4">
        <?php
        // Retrieve unread notifications
        $sql = "SELECT message, created_at FROM notifications WHERE student_id = ? AND is_read = 0";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Display notifications
        while ($notification = $result->fetch_assoc()):
        ?>
            <div class="alert alert-info">
                <?= htmlspecialchars($notification['message']) ?> - <small><?= htmlspecialchars($notification['created_at']) ?></small>
            </div>
        <?php endwhile; ?>
        <?php
        // Mark notifications as read after displaying
        $update_sql = "UPDATE notifications SET is_read = 1 WHERE student_id = ? AND is_read = 0";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        ?>
    </div>
    <!-- Dashboard Cards Section -->
    <!-- <div class="container my-5"> -->
        <!-- (Your main content goes here) -->
    <!-- </div> -->

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 Bus Pass Management System. All rights reserved.</p>
    </footer>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
