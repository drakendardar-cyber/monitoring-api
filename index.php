<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Menghubungkan ke Environment Variables Database bawaan Railway (Bebas Spasi Gaib)
$host = getenv('MYSQLHOST') ?: 'localhost';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';
$db   = getenv('MYSQLDATABASE') ?: '';
$port = getenv('MYSQLPORT') ?: '3306';

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die(json_encode(["pass" => "koneksi_db_gagal"]));
}

$uri = $_SERVER['REQUEST_URI'];
$username = '';

if (preg_match('/user\/(.*?)\.json/', $uri, $matches)) {
    $username = $matches[1];
} 

if (empty($username)) {
    $username = $_GET['username'] ?? $_POST['username'] ?? '';
}

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

$action = "login"; 
if ($_SERVER['REQUEST_METHOD'] === 'PUT' || !empty($input) || isset($_POST['jsonData'])) {
    $action = "register";
}

// ==========================================
// A. LOGIKA REGISTER (DARI WEB1.PUT TEXT)
// ==========================================
if ($action === "register") {
    if (isset($_POST['jsonData'])) {
        $input = json_decode($_POST['jsonData'], true);
    }

    $nama_lengkap = $input['namaLengkap'] ?? '';
    $email        = $input['email'] ?? '';
    $password     = $input['pass'] ?? '';

    if (empty($username) || empty($password)) {
        echo json_encode(["pass" => "data_kosong_x_x_x"]);
        $conn->close();
        exit();
    }

    $stmtCheck = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmtCheck->bind_param("ss", $username, $email);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result();

    if ($resCheck->num_rows > 0) {
        echo json_encode(["pass" => "data_sudah_terdaftar_x_x_x"]);
        $stmtCheck->close();
        $conn->close();
        exit();
    }
    $stmtCheck->close();

    $stmtInsert = $conn->prepare("INSERT INTO users (nama_lengkap, email, username, password) VALUES (?, ?, ?, ?)");
    $stmtInsert->bind_param("ssss", $nama_lengkap, $email, $username, $password);

    if ($stmtInsert->execute()) {
        echo json_encode(["status" => "sukses", "message" => "berhasil"]);
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
