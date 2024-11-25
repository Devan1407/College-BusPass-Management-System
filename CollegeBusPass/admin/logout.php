<?php
session_start();
session_unset(); // Unset all session variables
session_destroy(); // Destroy the session
// Redirect to the home page
header("Location: ../index.php"); // Assuming your home page is `index.php` in the root directory
exit();
?>
