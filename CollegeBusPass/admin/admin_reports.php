<?php
session_start();
include('../includes/db.php');

// Fetch all booking and renewal details
$sql = "SELECT 
            students.name AS student_name, 
            students.course_name, 
            students.semester, 
            buses.bus_number, 
            routes.route_name, 
            bus_bookings.booking_start_date, 
            bus_bookings.booking_end_date, 
            IFNULL(bus_pass_renewals.renewal_start_date, 'N/A') AS renewal_start_date, 
            IFNULL(bus_pass_renewals.renewal_end_date, 'N/A') AS renewal_end_date, 
            IF(bus_pass_renewals.is_approved = 1, 'Approved', IF(bus_pass_renewals.is_approved = 0, 'Rejected', 'Pending')) AS renewal_status 
        FROM bus_bookings
        INNER JOIN buses ON bus_bookings.bus_id = buses.id
        INNER JOIN routes ON buses.route_id = routes.id
        INNER JOIN students ON bus_bookings.student_id = students.id
        LEFT JOIN bus_pass_renewals ON bus_bookings.id = bus_pass_renewals.bus_booking_id";

$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Booking and Renewal Report</title>
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
        .status-approved {
            background-color: #28a745;
            color: white;
        }
        .status-rejected {
            background-color: #dc3545;
            color: white;
        }
        .status-pending {
            background-color: #ffc107;
            color: black;
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
        <h2>Booking and Renewal Report</h2>

        <!-- List of Booking and Renewal details -->
        <table class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Course</th>
                    <th>Semester</th>
                    <th>Bus Number</th>
                    <th>Route</th>
                    <th>Booking Start Date</th>
                    <th>Booking End Date</th>
                    <th>Renewal Start Date</th>
                    <th>Renewal End Date</th>
                    <th>Renewal Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['student_name']) ?></td>
                            <td><?= htmlspecialchars($row['course_name']) ?></td>
                            <td><?= htmlspecialchars($row['semester']) ?></td>
                            <td><?= htmlspecialchars($row['bus_number']) ?></td>
                            <td><?= htmlspecialchars($row['route_name']) ?></td>
                            <td><?= htmlspecialchars($row['booking_start_date']) ?></td>
                            <td><?= htmlspecialchars($row['booking_end_date']) ?></td>
                            <td><?= htmlspecialchars($row['renewal_start_date']) ?></td>
                            <td><?= htmlspecialchars($row['renewal_end_date']) ?></td>
                            <td>
                                <?php if ($row['renewal_status'] == 'Approved'): ?>
                                    <span class="badge status-approved">Approved</span>
                                <?php elseif ($row['renewal_status'] == 'Rejected'): ?>
                                    <span class="badge status-rejected">Rejected</span>
                                <?php else: ?>
                                    <span class="badge status-pending">Pending</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" class="no-data">No booking and renewal details found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

