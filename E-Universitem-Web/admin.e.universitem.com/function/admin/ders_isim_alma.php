<?php
include '../connect_db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Gelen JSON verisini al ve doğru şekilde diziye dönüştür
    $data = json_decode(file_get_contents("php://input"), true);  // true parametresi, diziyi doğru döndürecektir

    // Gelen veriyi kontrol et
    if (!isset($data['dersler']) || !is_array($data['dersler'])) {
        $response['status'] = 'error';
        $response['message'] = 'Geçerli bir ders listesi gönderilmelidir.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 'dersler' dizisini al
    $dersIsimleri = $data['dersler'];

    // Ders isimleri boşsa hata döndür
    if (empty($dersIsimleri) || !is_array($dersIsimleri)) {
        $response['status'] = 'error';
        $response['message'] = 'Geçerli bir ders listesi gönderilmelidir.';
    } else {
        try {
            // Ders isimlerine göre ders ID'lerini alacak sorgu
            $placeholders = implode(',', array_fill(0, count($dersIsimleri), '?'));  // Dinamik parametreler için placeholders
            $sql = "SELECT ders_id, ders_adi FROM dersler WHERE ders_adi IN ($placeholders)";
            $stmt = $pdo->prepare($sql);

            // SQL sorgusunu ders isimleriyle çalıştır
            $stmt->execute($dersIsimleri);

            // Dersler verisini al
            $dersler = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($dersler) {
                // Dersler bulunduysa başarılı yanıt döndür
                $response['status'] = 'success';
                $response['data'] = $dersler; // Derslerin id ve isimlerini döndür
            } else {
                // Ders bulunamazsa hata mesajı döndür
                $response['status'] = 'error';
                $response['message'] = 'Hiçbir ders bulunamadı.';
            }
        } catch (PDOException $e) {
            // Veritabanı hatası oluşursa hata mesajı döndür
            $response['status'] = 'error';
            $response['message'] = 'Veritabanı hatası: ' . $e->getMessage();
        }
    }

    // Yanıtı JSON formatında döndür
    header('Content-Type: application/json');
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
} else {
    // Geçersiz istek yöntemi için hata mesajı döndür
    $response = [
        'status' => 'error',
        'message' => 'Geçersiz istek yöntemi.'
    ];
    header('Content-Type: application/json');
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}
?>