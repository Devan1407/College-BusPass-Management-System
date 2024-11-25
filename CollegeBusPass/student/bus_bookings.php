<?php
session_start();
include('../includes/db.php');

// Check if the student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header("Location: login.php");
    exit();
}

// Get the student ID from session
$student_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bus_id = $_POST['bus_id'];
    $booking_start_date = $_POST['booking_start_date'];
    $booking_end_date = $_POST['booking_end_date'];

    // Validate that the end date is after the start date
    if (strtotime($booking_start_date) > strtotime($booking_end_date)) {
        echo "<div class='alert alert-danger'>End date must be after start date.</div>";
        exit();
    }

    // Calculate the number of days between the booking start date and end date
    $days_diff = (strtotime($booking_end_date) - strtotime($booking_start_date)) / (60 * 60 * 24) + 1;

    // Fetch the total distance of the route associated with the selected bus
    $distance_sql = "SELECT routes.total_distance 
                     FROM routes 
                     INNER JOIN buses ON routes.id = buses.route_id 
                     WHERE buses.id = ?";
    $stmt = $conn->prepare($distance_sql);
    $stmt->bind_param("i", $bus_id);
    $stmt->execute();
    $stmt->bind_result($total_distance);
    $stmt->fetch();
    $stmt->close();

    // Calculate the total cost
    $cost_per_km = 3; // Cost per kilometer
    $total_cost = $total_distance * $cost_per_km * $days_diff;

    // Insert booking into bus_bookings table
    $sql = "INSERT INTO bus_bookings (student_id, bus_id, booking_start_date, booking_end_date, total_cost) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissd", $student_id, $bus_id, $booking_start_date, $booking_end_date, $total_cost);

    if ($stmt->execute()) {
        echo "<script>alert('Bus booked successfully! Total cost: ₹" . $total_cost . "'); window.location.href='student_dashboard.php';</script>";
    } else {
        echo "<div class='alert alert-danger'>Error booking bus: " . $stmt->error . "</div>";
    }

    $stmt->close();
    $conn->close();
    exit();
}

// Fetch available buses and routes for the form
$bus_sql = "SELECT buses.id, buses.bus_number, routes.route_name, routes.total_distance 
            FROM buses 
            INNER JOIN routes ON buses.route_id = routes.id";
$bus_result = $conn->query($bus_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Bus Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .form-container {
            background-color: white;
            border-radius: 8px;
            padding: 30px;
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
            margin-top: 50px;
        }
        .container {
            min-height: calc(100vh - 60px); /* Ensure content fits the screen */
        }
    </style>
</head>
<body>
    <div class="container d-flex flex-column justify-content-center align-items-center">
        <div class="col-md-6">
            <div class="form-container">
                <h2 class="text-center mb-4">Book Bus Service</h2>

                <form method="post" action="">
                    <div class="mb-3">
                        <label for="bus_id" class="form-label">Select Bus Route</label>
                        <select class="form-select" id="bus_id" name="bus_id" required>
                            <option value="">Select a Bus Route</option>
                            <?php while ($row = $bus_result->fetch_assoc()): ?>
                                <option value="<?= $row['id'] ?>" data-distance="<?= $row['total_distance'] ?>">
                                    <?= htmlspecialchars($row['bus_number']) ?> - <?= htmlspecialchars($row['route_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="booking_start_date" class="form-label">Booking Start Date</label>
                            <input type="date" class="form-control" id="booking_start_date" name="booking_start_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="booking_end_date" class="form-label">Booking End Date</label>
                            <input type="date" class="form-control" id="booking_end_date" name="booking_end_date" required>
                        </div>
                    </div>

                    <!-- Display the total cost dynamically -->
                    <div class="mb-3">
                        <label class="form-label">Total Cost: ₹<span id="total_cost">0</span></label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Book Bus Service</button>
                </form>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 Bus Pass Management System. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JavaScript to dynamically calculate and display the total cost
        document.getElementById('bus_id').addEventListener('change', calculateTotalCost);
        document.getElementById('booking_start_date').addEventListener('change', calculateTotalCost);
        document.getElementById('booking_end_date').addEventListener('change', calculateTotalCost);

        function calculateTotalCost() {
            const busId = document.getElementById('bus_id');
            const startDate = new Date(document.getElementById('booking_start_date').value);
            const endDate = new Date(document.getElementById('booking_end_date').value);
            const costPerKm = 3; // Cost per kilometer

            if (busId.value && startDate && endDate && startDate <= endDate) {
                const daysDiff = Math.floor((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
                const distance = parseFloat(busId.options[busId.selectedIndex].getAttribute('data-distance'));
                const totalCost = distance * costPerKm * daysDiff;
                
                document.getElementById('total_cost').textContent = totalCost.toFixed(2);
            } else {
                document.getElementById('total_cost').textContent = '0';
            }
        }
    </script>
</body>
</html>
