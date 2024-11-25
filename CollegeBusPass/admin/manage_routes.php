<?php
session_start();
include('../includes/db.php');

// Fetch all routes
$sql = "SELECT * FROM routes";
$result = $conn->query($sql);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['route_name'])) {
    $route_name = $_POST['route_name'];
    $from_destination = $_POST['from_destination'];
    $to_destination = $_POST['to_destination'];
    $total_distance = $_POST['total_distance'];

    $sql = "INSERT INTO routes (route_name, from_destination, to_destination, total_distance) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $route_name, $from_destination, $to_destination, $total_distance);

    if ($stmt->execute()) {
        echo "<script>
                alert('Route added successfully!');
                window.location.href = 'manage_routes.php'; // Redirect to the manage routes page
              </script>";
    } else {
        echo "<script>
                alert('Error adding route.');
              </script>";
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Routes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: black;
        }
        .container {
            margin-top: 40px;
        }
        .card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 40px;
        }
        .table {
            background-color: white;
        }
        h2 {
            color: dodgerblue;
            font-weight: bold;
        }
        h3 {
            color: white;
            margin-top: 30px;
            font-weight: 600;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .form-control {
            border-radius: 5px;
        }
        .table-bordered th, .table-bordered td {
            vertical-align: middle;
        }
        .table th {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Card for Route Management -->
        <div class="card">
            <h2>Manage Routes</h2>
            <p>Use the form below to add a new route. View the list of existing routes in the table below.</p>

            <!-- Add Route Form -->
            <form method="post" action="manage_routes.php">
                <div class="mb-3">
                    <label for="route_name" class="form-label">Route Name</label>
                    <input type="text" class="form-control" id="route_name" name="route_name" placeholder="Enter route name" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="from_destination" class="form-label">From</label>
                        <input type="text" class="form-control" id="from_destination" name="from_destination" placeholder="Starting point" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="to_destination" class="form-label">To</label>
                        <input type="text" class="form-control" id="to_destination" name="to_destination" placeholder="Ending point" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="total_distance" class="form-label">Total Distance (in km)</label>
                    <input type="number" step="0.01" class="form-control" id="total_distance" name="total_distance" placeholder="e.g., 15.5" required>
                </div>
                <button type="submit" class="btn btn-primary">Add Route</button>
            </form>
        </div>

        <!-- Table of Existing Routes -->
        <h3>Existing Routes</h3>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Route Name</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Total Distance (km)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= $row['route_name'] ?></td>
                                <td><?= $row['from_destination'] ?></td>
                                <td><?= $row['to_destination'] ?></td>
                                <td><?= $row['total_distance'] ?> km</td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No routes found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
