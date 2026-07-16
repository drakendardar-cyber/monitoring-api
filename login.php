<?php
include "koneksi.php";

// UBAH DARI $_GET MENJADI $_POST
if(isset($_POST['username']) && isset($_POST['password'])){

    $username = $_POST['username'];
    $password = $_POST['password'];

    // cek username dan password
    $sql = "SELECT * FROM users 
            WHERE username = :username 
            AND password = :password";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':username' => $username,
        ':password' => $password
    ]);

    $data = $stmt->fetch();

    if($data){
        echo "berhasil";
    }else{
        echo "gagal";
    }
}
?>
