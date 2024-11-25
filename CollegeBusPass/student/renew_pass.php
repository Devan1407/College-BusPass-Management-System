<?php
session_start();
include('../includes/db.php');

// Check if the student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header("Location: login.php");
    exit();
}

// Get student ID from session
$student_id = $_SESSION['user_id'];

// Fetch the student's active bookings
$sql = "SELECT bus_bookings.id, routes.route_name, buses.bus_number, bus_bookings.booking_end_date 
        FROM bus_bookings
        INNER JOIN buses ON bus_bookings.bus_id = buses.id
        INNER JOIN routes ON buses.route_id = routes.id
        WHERE bus_bookings.student_id = ? AND bus_bookings.booking_end_date >= CURDATE()";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$bookings = $stmt->get_result();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bus_booking_id = $_POST['bus_booking_id'];
    $renewal_start_date = $_POST['renewal_start_date'];
    $renewal_end_date = $_POST['renewal_end_date'];

    // Validate that the end date is after the start date
    if (strtotime($renewal_start_date) > strtotime($renewal_end_date)) {
        echo "<script>alert('End date must be after the start date.'); window.history.back();</script>";
        exit();
    }

    // Insert the renewal request into the bus_pass_renewals table
    $insert_sql = "INSERT INTO bus_pass_renewals (student_id, bus_booking_id, renewal_start_date, renewal_end_date) 
                   VALUES (?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("iiss", $student_id, $bus_booking_id, $renewal_start_date, $renewal_end_date);

    if ($insert_stmt->execute()) {
        echo "<script>alert('Bus pass renewal request submitted successfully!'); window.location.href='student_dashboard.php';</script>";
    } else {
        echo "<script>alert('Error submitting renewal request: " . $insert_stmt->error . "'); window.history.back();</script>";
    }

    $insert_stmt->close();
    $conn->close();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Renew Bus Pass</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .form-container {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
        }
        .form-label {
            font-weight: bold;
        }
        footer {
            background-color: #343a40;
            color: #fff;
            padding: 10px 0;
            text-align: center;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="form-container">
                    <h2 class="text-center mb-4">Renew Your Bus Pass</h2>

                    <?php if ($bookings->num_rows > 0): ?>
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="bus_booking_id" class="form-label">Select Booking</label>
                            <select class="form-select" id="bus_booking_id" name="bus_booking_id" required onchange="updateDates()">
                                <?php while ($row = $bookings->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($row['id']) ?>" data-end-date="<?= htmlspecialchars($row['booking_end_date']) ?>">
                                        <?= htmlspecialchars($row['bus_number']) ?> - <?= htmlspecialchars($row['route_name']) ?> (End Date: <?= htmlspecialchars($row['booking_end_date']) ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="renewal_start_date" class="form-label">Renewal Start Date</label>
                                <input type="date" class="form-control" id="renewal_start_date" name="renewal_start_date" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="renewal_end_date" class="form-label">Renewal End Date</label>
                                <input type="date" class="form-control" id="renewal_end_date" name="renewal_end_date" readonly>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Request Renewal</button>
                    </form>
                    <?php else: ?>
                        <div class="alert alert-warning text-center">
                            No active bookings found to renew.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 Bus Pass Management System. All rights reserved.</p>
    </footer>

    <script>
    function updateDates() {
        // Get the selected booking's end date
        var bookingSelect = document.getElementById('bus_booking_id');
        var selectedOption = bookingSelect.options[bookingSelect.selectedIndex];
        var endDate = new Date(selectedOption.getAttribute('data-end-date'));

        // Set renewal start date to the day after the booking end date
        var renewalStartDate = new Date(endDate);
        renewalStartDate.setDate(renewalStartDate.getDate() + 1);

        // Set renewal end date to one month after the renewal start date
        var renewalEndDate = new Date(renewalStartDate);
        renewalEndDate.setMonth(renewalEndDate.getMonth() + 1);

        // Format dates as yyyy-mm-dd
        var formattedStartDate = renewalStartDate.toISOString().split('T')[0];
        var formattedEndDate = renewalEndDate.toISOString().split('T')[0];

        // Update the input fields
        document.getElementById('renewal_start_date').value = formattedStartDate;
        document.getElementById('renewal_end_date').value = formattedEndDate;
    }

    // Update dates on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateDates();
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
