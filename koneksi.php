<?php
// koneksi.php

// Konfigurasi Database - MEMBACA OTOMATIS DARI INTERNAL RAILWAY
$host     = getenv('MYSQLHOST') ?: "mysql.railway.internal"; 
$port     = getenv('MYSQLPORT') ?: "3306"; 
$dbname   = getenv('MYSQLDATABASE') ?: "railway";                               
$username = getenv('MYSQLUSER') ?: "root";                                  
$password = getenv('MYSQLPASSWORD') ?: "qbwgyLhREkhOHVWIXnqCWrAxkTrynrsJ"; 

// Membuat koneksi
try {
    $conn = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    // Mode error PDO
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Mode fetch default
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Jika gagal koneksi
    die("Koneksi database gagal: " . $e->getMessage());
}
?>
