<?php
session_start();
include('../includes/db.php');

// Approve or reject a registration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $registration_id = $_POST['registration_id'];
    $action = $_POST['action'];

    if ($action == 'approve') {
        $sql = "UPDATE registrations SET is_approved = 1 WHERE id = ?";
    } elseif ($action == 'reject') {
        $sql = "UPDATE registrations SET is_approved = 0 WHERE id = ?";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $registration_id);

    if ($stmt->execute()) {
        echo "<script>alert('Action completed successfully.');</script>";
    } else {
        echo "<script>alert('Error processing request.');</script>";
    }

    $stmt->close();
}

// Fetch all registrations
$sql = "SELECT 
            registrations.id as reg_id, 
            students.name as student_name, 
            students.course_name, 
            students.semester, 
            registrations.is_approved 
         FROM registrations
        INNER JOIN students ON registrations.student_id = students.id";
        
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Registrations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: black;
        }
        .container {
            margin-top: 50px;
        }
        h2 {
            font-weight: 600;
            color: white;
        }
        .table {
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .table thead th {
            background-color: #007bff;
            color: white;
            border: none;
        }
        .table tbody tr {
            border-bottom: 1px solid #dee2e6;
        }
        .table tbody tr:last-child {
            border-bottom: none;
        }
        .btn-success, .btn-danger {
            border-radius: 5px;
        }
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }
        .btn-success:hover {
            background-color: #218838;
        }
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .alert {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Manage Student Registrations</h2>

        <!-- List of Registrations -->
        <table class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Course</th>
                    <th>Semester</th>
                    <th>Approval Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['student_name']) ?></td>
                            <td><?= htmlspecialchars($row['course_name']) ?></td>
                            <td><?= htmlspecialchars($row['semester']) ?></td>
                            <td>
                                <?php 
                                    if ($row['is_approved'] == 1) {
                                        echo '<span class="badge bg-success">Approved</span>';
                                    } elseif ($row['is_approved'] == 0) {
                                        echo '<span class="badge bg-danger">Rejected</span>';
                                    } else {
                                        echo '<span class="badge bg-secondary">Pending</span>';
                                    }
                                ?>
                            </td>
                            <td>
                                <form method="post" action="manage_registrations.php" style="display:inline-block;">
                                    <input type="hidden" name="registration_id" value="<?= $row['reg_id'] ?>">
                                    <button type="submit" name="action" value="approve" class="btn btn-success btn-sm" <?= $row['is_approved'] == 1 ? 'disabled' : '' ?>>Approve</button>
                                </form>
                                <form method="post" action="manage_registrations.php" style="display:inline-block;">
                                    <input type="hidden" name="registration_id" value="<?= $row['reg_id'] ?>">
                                    <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm" <?= $row['is_approved'] === 0 ? 'disabled' : '' ?>>Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No registrations found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
