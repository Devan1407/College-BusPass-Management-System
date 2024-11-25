<?php
session_start();
include('../includes/db.php');

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $renewal_id = $_POST['renewal_id'];
    $action = $_POST['action'];

    if ($action == 'approve') {
        // First, fetch the renewal start and end dates from bus_pass_renewals
        $fetch_dates_sql = "SELECT renewal_start_date, renewal_end_date, bus_booking_id 
                            FROM bus_pass_renewals 
                            WHERE id = ?";
        $stmt = $conn->prepare($fetch_dates_sql);
        $stmt->bind_param('i', $renewal_id);
        $stmt->execute();
        $stmt->bind_result($renewal_start_date, $renewal_end_date, $bus_booking_id);
        $stmt->fetch();
        $stmt->close();

        if ($renewal_start_date && $renewal_end_date) {
            // Update the bus_bookings table with the new renewal dates
            $update_sql = "UPDATE bus_bookings 
                           SET booking_start_date = ?, booking_end_date = ?
                           WHERE id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param('ssi', $renewal_start_date, $renewal_end_date, $bus_booking_id);
            if ($stmt->execute()) {
                // After updating the booking dates, mark the renewal request as approved
                $approve_sql = "UPDATE bus_pass_renewals SET is_approved = 1 WHERE id = ?";
                $stmt = $conn->prepare($approve_sql);
                $stmt->bind_param('i', $renewal_id);
                $stmt->execute();
                
                // Redirect to the admin dashboard after approval
                echo "<script>alert('Renewal request approved successfully!'); window.location.href='admin_dashboard.php';</script>";
            } else {
                echo "<script>alert('Error updating booking: " . $stmt->error . "'); window.history.back();</script>";
            }
            $stmt->close();
        } else {
            echo "<script>alert('Error fetching renewal dates.'); window.history.back();</script>";
        }
    } elseif ($action == 'reject') {
        // Delete the renewal request if rejected
        $delete_sql = "DELETE FROM bus_pass_renewals WHERE id = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param('i', $renewal_id);
        $stmt->execute();

        // Redirect to the admin dashboard after rejection
        echo "<script>alert('Renewal request rejected successfully!'); window.location.href='admin_dashboard.php';</script>";
    }
}

// Fetch all renewal requests
$sql = "SELECT bus_pass_renewals.id AS renewal_id, students.name, buses.bus_number, routes.route_name, 
               bus_bookings.booking_end_date, bus_pass_renewals.renewal_start_date, bus_pass_renewals.renewal_end_date, 
               bus_pass_renewals.is_approved 
        FROM bus_pass_renewals
        INNER JOIN bus_bookings ON bus_pass_renewals.bus_booking_id = bus_bookings.id
        INNER JOIN buses ON bus_bookings.bus_id = buses.id
        INNER JOIN routes ON buses.route_id = routes.id
        INNER JOIN students ON bus_pass_renewals.student_id = students.id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Renewal Requests</title>
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
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .table thead th {
            background-color: #007bff;
            color: white;
            border: none;
        }
        .table tbody tr {
            border-bottom: 1px solid #dee2e6;
        }
        .badge {
            font-size: 90%;
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
        .no-data {
            font-size: 18px;
            color: #6c757d;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Bus Pass Renewal Requests</h2>

        <?php if ($result->num_rows > 0): ?>
            <table class="table table-bordered mt-4">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Bus Number</th>
                        <th>Route</th>
                        <th>Current End Date</th>
                        <th>Renewal Start Date</th>
                        <th>Renewal End Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['bus_number']) ?></td>
                            <td><?= htmlspecialchars($row['route_name']) ?></td>
                            <td><?= htmlspecialchars($row['booking_end_date']) ?></td>
                            <td><?= htmlspecialchars($row['renewal_start_date']) ?></td>
                            <td><?= htmlspecialchars($row['renewal_end_date']) ?></td>
                            <td>
                                <?php if ($row['is_approved'] == 1): ?>
                                    <span class="badge bg-success">Approved</span>
                                <?php else: ?>
                                    <form method="post" action="" style="display:inline-block;">
                                        <input type="hidden" name="renewal_id" value="<?= htmlspecialchars($row['renewal_id']) ?>">
                                        <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                                        <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-data">No renewal requests found.</p>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
