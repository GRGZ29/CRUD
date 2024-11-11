<?php
// db.php: Database connection file

$host = 'localhost';   // Database host
$dbname = 'crud_app';  // Database name
$username = 'root';    // Database username
$password = '';        // Database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}
?>
