<?php
// Hubungkan dengan file koneksi Anda
include 'koneksi.php';


// MENGGUNAKAN $_REQUEST agar bisa dites lewat browser (GET) maupun aplikasi Kodular (POST)
$username  = $_REQUEST['username'] ?? '';
$password  = $_REQUEST['password'] ?? '';
$nama      = $_REQUEST['nama'] ?? '';
$email     = $_REQUEST['email'] ?? '';

// 1. Validasi: Memastikan tidak ada kolom inputan yang kosong saat mendaftar
if ($username == "" || $password == "" || $nama == "" || $email == "") {
    echo "Tidak Boleh Ada Yang Kosong!";
} else {
    try {
        // 2. Cek apakah username ATAU email sudah terdaftar di database
        $query_cek = "SELECT COUNT(*) FROM users WHERE username = :username OR email = :email";
        $stmt_cek = $conn->prepare($query_cek);
        $stmt_cek->execute([
            ':username' => $username,
            ':email'    => $email
        ]);
        $user_exist = $stmt_cek->fetchColumn();

        if ($user_exist > 0) {
            echo "Username atau Email yang anda gunakan sudah terdaftar!";
        } else {
            // 3. Mengamankan password dengan Hash Bcrypt
            $password_hashed = password_hash($password, PASSWORD_DEFAULT);

            // 4. Perintah SQL untuk menyimpan data ke tabel 'users'
            $query_simpan = "INSERT INTO users (username, password, nama, email) 
                             VALUES (:username, :password, :nama, :email)";
            
            $stmt_simpan = $conn->prepare($query_simpan);
            $eksekusi = $stmt_simpan->execute([
                ':username' => $username,
                ':password' => $password, // Menyimpan versi aman ter-hash
                ':nama'     => $nama,
                ':email'    => $email
            ]);

            if ($eksekusi) {
                echo "Selamat anda sudah berhasil melakukan pendaftaran!";
            } else {
                echo "Maaf pendaftaran anda gagal dilakukan!";
            }
        }
    } catch (PDOException $e) {
        // Menampilkan pesan jika terjadi error pada database
        echo "Terjadi kesalahan database: " . $e->getMessage();
    }
}
?>
