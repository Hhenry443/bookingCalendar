<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database connection parameters
$host = 'localhost'; // Change if necessary
$db = 'RolsaTesting'; // Database name
$user = 'henry'; // Database username
$pass = 'root'; // Database password
$charset = 'utf8mb4';

// Set up the DSN (Data Source Name)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";


try {
    // Create a PDO instance
    $pdo = new PDO($dsn, $user, $pass);
    // Set error mode to exception to catch errors
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get the booking month and year from URL, else get default
    $month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
    $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

    // Prepare the statement to fetch all bookings. We will then filter them
    $stmt = $pdo->prepare("SELECT * FROM tbl_booking");
    $stmt->execute();

    // Fetch all of the bookings
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$bookings) {
        $error_message = "No Bookings found.";
    }

    
    // Filter bookings by the specified month and year
    $filteredBookings = array_filter($bookings, function ($booking) use ($month, $year) {
        $bookingDate = new DateTime($booking['booking_date']); // Assuming 'booking_date' is the column name
        return $bookingDate->format('n') == $month && $bookingDate->format('Y') == $year;
    });

} catch (PDOException $e) {
    $error_message = "Connection failed: " . $e->getMessage();
}
