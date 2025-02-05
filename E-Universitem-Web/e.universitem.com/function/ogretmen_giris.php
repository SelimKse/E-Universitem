<?php

session_start();

// Veritabanı bağlantısını dahil et
include 'connect_db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // JSON girişini al
    $data = json_decode(file_get_contents("php://input"));

    $ogretmen_email = filter_var($data->email, FILTER_SANITIZE_EMAIL);
    $ogretmen_password = $data->password;
    $beni_hatirla = isset($data->beniHatirla) ? $data->beniHatirla : false;

    // SQL sorgusu - Öğretmen bilgilerini kontrol et
    $sql = "SELECT * FROM ogretmenler WHERE ogretmen_eposta = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $ogretmen_email);
    $stmt->execute();

    $ogretmen = $stmt->fetch(PDO::FETCH_ASSOC);

    $response = [];

    if ($ogretmen) {
        if (password_verify($ogretmen_password, $ogretmen['ogretmen_sifre'])) {
            // Oturum bilgilerini ayarla
            $_SESSION['username'] = $ogretmen['ogretmen_adi'];
            $_SESSION['email'] = $ogretmen['ogretmen_eposta'];
            $_SESSION['id'] = $ogretmen['ogretmen_no'];

            // Rastgele bir token oluştur
            $token = bin2hex(random_bytes(32));
            $expire_time = date('Y-m-d H:i:s', time() + 3600); // 1 saat geçerli

            // IP ve Tarayıcı bilgisi
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $browser_info = $_SERVER['HTTP_USER_AGENT'];

            // Token'ı tokens tablosuna kaydet
            $token_sql = "INSERT INTO tokens (user_id, user_type, token, ip_adresi, tarayici_bilgisi, gecerlilik_suresi) 
                          VALUES (:user_id, :user_type, :token, :ip_adresi, :tarayici_bilgisi, :gecerlilik_suresi)";
            $token_stmt = $pdo->prepare($token_sql);
            $token_stmt->execute([
                ':user_id' => $ogretmen['ogretmen_no'],  // Öğretmen ID'sini kullan
                ':user_type' => 'ogretmen',  // Kullanıcı tipi olarak 'ogretmen' belirle
                ':token' => $token,
                ':ip_adresi' => $ip_address,
                ':tarayici_bilgisi' => $browser_info,
                ':gecerlilik_suresi' => $expire_time
            ]);

            // Cookie süresini ayarla
            $cookie_lifetime = $beni_hatirla ? time() + 3600 : 0; // Beni Hatırla işaretliyse 1 saat, değilse tarayıcı kapanınca

            // Token'ı cookie olarak ayarla
            setcookie("auth_token", $token, $cookie_lifetime, "/", ".e-universitem.com", true, true);

            // Başarılı yanıt
            $response = [
                'status' => 'success',
                'message' => 'Giriş başarılı!',
                'user' => [
                    'username' => $ogretmen['ogretmen_adi'],
                    'email' => $ogretmen['ogretmen_eposta'],
                    'id' => $ogretmen['ogretmen_no']
                ],
                'token' => $token,
            ];
        } else {
            // Şifre hatalıysa hata döndür
            $response = [
                'status' => 'error',
                'message' => 'Şifre hatalı!'
            ];
        }
    } else {
        // Kullanıcı bulunamadıysa hata döndür
        $response = [
            'status' => 'error',
            'message' => 'Öğretmen bulunamadı!'
        ];
    }

    // JSON yanıtını döndür
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
