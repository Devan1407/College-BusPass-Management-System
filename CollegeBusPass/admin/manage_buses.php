<?php
session_start();
include('../includes/db.php');

// Fetch all routes for the dropdown
$sqlRoutes = "SELECT id, route_name FROM routes";
$resultRoutes = $conn->query($sqlRoutes);

// Fetch all buses
$sqlBuses = "SELECT buses.id, buses.bus_number, buses.capacity, routes.route_name 
             FROM buses 
             JOIN routes ON buses.route_id = routes.id";
$resultBuses = $conn->query($sqlBuses);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['bus_number'])) {
    $bus_number = $_POST['bus_number'];
    $capacity = $_POST['capacity'];
    $route_id = $_POST['route_id'];

    $sql = "INSERT INTO buses (bus_number, capacity, route_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $bus_number, $capacity, $route_id);

    if ($stmt->execute()) {
        echo "<script>alert('Bus added successfully!'); window.location.href = 'manage_buses.php';</script>";
    } else {
        echo "<script>alert('Error adding bus: " . $stmt->error . "');</script>";
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
    <title>Manage Buses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: black;
        }
        .container {
            margin-top: 50px;
        }
        .card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        h2, h3 {
            font-weight: 600;
            color: orange;
        }
        .form-control, .form-select {
            border-radius: 5px;
        }
        .table {
            margin-top: 20px;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
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
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h2>Manage Buses</h2>
            <p class="text-muted">Use the form below to add a new bus. You can also view and manage existing buses.</p>

            <!-- Add Bus Form -->
            <form method="post" action="manage_buses.php">
                <div class="mb-3">
                    <label for="bus_number" class="form-label">Bus Number</label>
                    <input type="text" class="form-control" id="bus_number" name="bus_number" placeholder="Enter bus number" required>
                </div>
                <div class="mb-3">
                    <label for="capacity" class="form-label">Capacity</label>
                    <input type="number" class="form-control" id="capacity" name="capacity" placeholder="Enter bus capacity" required>
                </div>
                <div class="mb-3">
                    <label for="route_id" class="form-label">Select Route</label>
                    <select class="form-select" id="route_id" name="route_id" required>
                        <option value="">Select Route</option>
                        <?php if ($resultRoutes->num_rows > 0): ?>
                            <?php while ($route = $resultRoutes->fetch_assoc()): ?>
                                <option value="<?= $route['id'] ?>"><?= $route['route_name'] ?></option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Add Bus</button>
            </form>
        </div>

        <!-- List of Buses -->
        <h3 class="mt-5">Existing Buses</h3>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Bus Number</th>
                    <th>Capacity</th>
                    <th>Route</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resultBuses->num_rows > 0): ?>
                    <?php while ($bus = $resultBuses->fetch_assoc()): ?>
                        <tr>
                            <td><?= $bus['id'] ?></td>
                            <td><?= $bus['bus_number'] ?></td>
                            <td><?= $bus['capacity'] ?></td>
                            <td><?= $bus['route_name'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">No buses found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
