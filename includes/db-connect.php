<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "admin";
$port = 3306;
$conn = mysqli_connect(
    $host,
    $user,
    $password,
    $database,
    $port
);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

?>
