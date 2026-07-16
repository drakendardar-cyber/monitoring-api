<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Menghubungkan ke Environment Variables Database bawaan Railway
$host = getenv('MYSQLHOST') ?: 'localhost';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';
$db   = getenv('MYSQLDATABASE') ?: '';
$port = getenv('MYSQLPORT') ?: '3306';

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die(json_encode(["pass" => "koneksi_db_gagal"]));
}

// Tangkap kiriman data dari Google Apps Script
$action   = $_POST['action'] ?? '';
$username = $_POST['username'] ?? $_GET['username'] ?? '';

// ==========================================
// A. LOGIKA REGISTER (DARI WEB1.PUT TEXT)
// ==========================================
if ($action === "register") {
    $rawJson = $_POST['jsonData'] ?? '';
    $input = json_decode($rawJson, true);

    $nama_lengkap = $input['namaLengkap'] ?? '';
    $email        = $input['email'] ?? '';
    $password     = $input['pass'] ?? '';

    // Cek username atau email kembar
    $stmtCheck = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmtCheck->bind_param("ss", $username, $email);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result();

    if ($resCheck->num_rows > 0) {
        // Balasan teks acak agar validasi password di Kodular gagal (User/Email sudah ada)
        echo json_encode(["pass" => "data_sudah_terdaftar_x_x_x"]);
        $stmtCheck->close();
        $conn->close();
        exit();
    }
    $stmtCheck->close();

    // Simpan teks biasa (Plaintext) tanpa hash
    $stmtInsert = $conn->prepare("INSERT INTO users (nama_lengkap, email, username, password) VALUES (?, ?, ?, ?)");
    $stmtInsert->bind_param("ssss", $nama_lengkap, $email, $username, $password);

    if ($stmtInsert->execute()) {
        // Mengembalikan status sukses untuk dibaca oleh GAS
        echo json_encode(["status" => "sukses"]);
    } else {
        echo json_encode(["pass" => "gagal_simpan_database"]);
    }
    $stmtInsert->close();
}

// ==========================================
// B. LOGIKA LOGIN (DARI WEB1.GET)
// ==========================================
if ($action === "login") {
    $stmt = $conn->prepare("SELECT id, nama_lengkap, email, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Kembalikan objek JSON dengan key pass plaintext untuk dicocokkan di Kodular
        $response = [
            "id" => $row['id'],
            "namaLengkap" => $row['nama_lengkap'],
            "email" => $row['email'],
            "pass" => $row['password'] 
        ];
        echo json_encode($response);
    } else {
        echo json_encode(["pass" => "user_tidak_ditemukan_x_x_x"]);
    }
    $stmt->close();
}

$conn->close();
?>
