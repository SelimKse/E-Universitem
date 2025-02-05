<?php
session_start();
include '../connect_db.php';

// Ders bilgilerini getiren kod, ders id'sine göre
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // JSON formatında gelen veriyi al
    $data = json_decode(file_get_contents("php://input"));

    // Form verilerini al
    $ders_id = $data->ders_id;

    // Yanıt için bir dizi oluştur
    $response = [];

    // Ders ID boş mu?
    if (empty($ders_id)) {
        $response['status'] = 'error';
        $response['message'] = 'Ders ID boş bırakılamaz!';
    } else {
        // Ders bilgilerini getir
        $sql = "SELECT * FROM dersler WHERE ders_id = :ders_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':ders_id', $ders_id, PDO::PARAM_INT);
        $stmt->execute();

        $ders = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ders) {
            // Eğer ders bulunduysa, öğrenci bilgilerini al
            // Öğrenciler sütunu JSON formatında saklanıyorsa, burada json_decode ile array'e çevirip kullanacağız.
            $ogrenci_ids = json_decode($ders['ogrenciler'], true); // JSON verisini diziye çevir (true parametresi ile)

            if (is_array($ogrenci_ids) && count($ogrenci_ids) > 0) {
                // Öğrenci bilgilerini getir
                $placeholders = implode(',', array_fill(0, count($ogrenci_ids), '?')); // Parametre yerleri
                $ogrenci_query = $pdo->prepare("SELECT ogrenci_no, ogrenci_adi, ogrenci_soyadi FROM ogrenciler WHERE ogrenci_no IN ($placeholders)");
                $ogrenci_query->execute($ogrenci_ids); // Öğrenci ID'leri ile sorguyu çalıştır

                // Öğrenci bilgilerini al
                $ogrenciler = $ogrenci_query->fetchAll(PDO::FETCH_ASSOC);

                $response['status'] = 'success';
                $response['ders'] = $ders;
                $response['ogrenciler'] = $ogrenciler; // İlgili öğrencileri döndür
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Dersin öğrenci listesi boş!';
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Ders bulunamadı!';
        }
    }

    // Yanıtı JSON olarak döndür
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    $response = [
        'status' => 'error',
        'message' => 'Geçersiz istek.'
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
}
