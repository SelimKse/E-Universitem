<?php

session_start();

// Veritabanı bağlantısını dahil et
include 'connect_db.php';

// Gelen POST isteğini kontrol et
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // JSON formatında gelen veriyi al
    $data = json_decode(file_get_contents("php://input"));

    // Form verilerini al
    $admin_username = $data->username;
    $admin_password = $data->password;

    // SQL sorgusu - Admin bilgilerini kontrol et
    $sql = "SELECT * FROM admins WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':username', $admin_username);
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    // Yanıt için bir dizi oluştur
    $response = [];

    // Admin bulundu mu?
    if ($admin) {
        // Şifreyi doğrula
        if (password_verify($admin_password, $admin['password'])) {
            // Şifre doğru, yanıtı ayarla
            $response['status'] = 'success';
            $_SESSION['username'] = $admin['username'];
        } else {
            // Şifre yanlış
            $response['status'] = 'error';
            $response['message'] = 'Şifre hatalı!';
        }
    } else {
        // Kullanıcı adı yanlış
        $response['status'] = 'error';
        $response['message'] = 'Kullanıcı Bulunamadı!';
    }

    // Yanıtı JSON olarak döndür
    echo json_encode($response);
}
