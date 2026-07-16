<?php
include 'koneksi.php';
// index.php - Entry point for the application
// Route requests to the appropriate PHP file

$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);

switch ($path) {
    case '/daftar':
    case '/daftar.php':
        require 'daftar.php';
        break;
    default:
        echo "API is running.";
        break;
}
?>
