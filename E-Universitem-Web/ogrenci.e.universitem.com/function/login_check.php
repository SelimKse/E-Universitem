<?php
include "../function/connect_db.php";

session_start();

// URL'den gelen verileri alıyoruz
if (!isset($_GET["token"]) || empty($_GET["token"])) {
    $response = [
        'status' => 'error',
        'message' => 'Giriş verileri eksik veya hatalı! Lütfen tekrar deneyin.',
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit; // İşlemi sonlandır
}

$response = [];


$token = $_GET["token"];

// // Token'i veritabanında arıyoruz
try {
    $sql = "SELECT * FROM tokens WHERE token = :token";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':token', $token);
    $stmt->execute();
} catch (PDOException $e) {
    $response = [
        'status' => 'error',
        'message' => 'Token sorgulanırken bir hata oluştu!' + $e->getMessage(),
    ];
    exit; // İşlemi sonlandır
}

$token_data = $stmt->fetch(PDO::FETCH_ASSOC);

if ($token_data) {
    // Token bulunduysa, kullanıcıyı bul
    $user_id = $token_data['user_id'];
    $user_type = $token_data['user_type'];

    if ($user_type == 'ogrenci') {
        // Öğrenci tablosundan öğrenciyi bul
        $sql = "SELECT * FROM ogrenciler WHERE ogrenci_no = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // IP ve Tarayıcı bilgisi kontrolü
        if ($token_data['ip_adresi'] == $_SERVER['REMOTE_ADDR'] && $token_data['tarayici_bilgisi'] == $_SERVER['HTTP_USER_AGENT']) {
            // Geçerlilik süresi kontrolü
            if (strtotime($token_data['gecerlilik_suresi']) > time()) {
                // Oturum bilgilerini ayarla
                $_SESSION['ogrenci_data'] = $user;

                // Token'i veritabanından sil
                try {
                    $sql = "DELETE FROM tokens WHERE token = :token";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':token', $token);
                    $stmt->execute();
                } catch (PDOException $e) {
                    $response = [
                        'status' => 'error',
                        'message' => 'Token silinirken bir hata oluştu!' + $e->getMessage(),
                    ];
                    exit; // İşlemi sonlandır
                }

                // Başarılı yanıtı bas
                $response = [
                    'status' => 'success',
                    'message' => 'Giriş başarılı! Yönlendiriliyorsunuz...',
                    'data' => $user,
                ];

                //json formatında yanıtı bas
                header('Content-Type: application/json');
                echo json_encode($response);
                exit; // İşlemi sonlandır

            } else {
                // Token süresi dolmuşsa hata mesajı bas
                $response = [
                    'status' => 'error',
                    'message' => 'Giriş bağlantısı süresi dolmuş!',
                ];
            }
        } else {
            // IP ve Tarayıcı bilgisi uyuşmazsa hata mesajı bas
            $response = [
                'status' => 'error',
                'message' => 'Güvenlik doğrulaması başarısız!',
            ];
        }
    } else {
        // Kullanıcı tipi öğrenci değilse hata mesajı bas
        $response = [
            'status' => 'error',
            'message' => 'Kullanıcı tipi hatalı!',
        ];
    }
} else {
    // Token bulunamadıysa hata mesajı bas
    $response = [
        'status' => 'error',
        'message' => 'Giriş verileri bulunamadı. Lütfen tekrar deneyin.',
    ];
}

// JSON formatında yanıtı bas
header('Content-Type: application/json');
echo json_encode($response);
?>