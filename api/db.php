<?php
// Establish PDO connection
$host = "localhost";
$dbname = "wwwfleee_fleeescape_db";
$username = "wwwfleee_dbuser";
$password = "fleeescapeDB@123"; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}
?>