<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$host = getenv('MYSQLHOST') ?: 'localhost';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';
$db   = getenv('MYSQLDATABASE') ?: '';
$port = getenv('MYSQLPORT') ?: '3306';

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die(json_encode(["pass" => "koneksi_db_gagal"]));
}

$action   = $_POST['action'] ?? 'login';
$rawQuery = $_POST['rawQuery'] ?? '';
$username = '';

if (!empty($rawQuery)) {
    $decodedQuery = urldecode($rawQuery);
    if (preg_match('/user\/(.*?)\.json/', $decodedQuery, $matches)) {
        $username = $matches[1];
    }
}

if ($action === "register") {
    $rawJson = $_POST['jsonData'] ?? '';
    $input = json_decode($rawJson, true);

    $nama_lengkap = $input['namaLengkap'] ?? '';
    $email        = $input['email'] ?? '';
    $password     = $input['pass'] ?? '';

    $stmtCheck = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmtCheck->bind_param("ss", $username, $email);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result();

    if ($resCheck->num_rows > 0) {
        echo json_encode(["pass" => "data_sudah_terdaftar_x_x_x"]);
        exit();
    }

    $stmtInsert = $conn->prepare("INSERT INTO users (nama_lengkap, email, username, password) VALUES (?, ?, ?, ?)");
    $stmtInsert->bind_param("ssss", $nama_lengkap, $email, $username, $password);

    if ($stmtInsert->execute()) {
        echo json_encode(["status" => "sukses"]);
    } else {
        echo json_encode(["pass" => "gagal_simpan_database"]);
    }
}

if ($action === "login") {
    $stmt = $conn->prepare("SELECT id, nama_lengkap, email, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            "id" => $row['id'],
            "namaLengkap" => $row['nama_lengkap'],
            "email" => $row['email'],
            "pass" => $row['password'] 
        ]);
    } else {
        echo json_encode(["pass" => "user_tidak_ditemukan_x_x_x"]);
    }
}
$conn->close();
?>
