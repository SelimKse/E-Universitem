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
            // Eğer ders bulunduysa, Öğretmen bilgilerini al
            // Öğretmenler sütunu JSON formatında saklanıyorsa, burada json_decode ile array'e çevirip kullanacağız.
            $ogretmen_ids = json_decode($ders['ogretmenler'], true); // JSON verisini diziye çevir (true parametresi ile)

            if (is_array($ogretmen_ids) && count($ogretmen_ids) > 0) {
                // Öğretmen bilgilerini getir
                $placeholders = implode(',', array_fill(0, count($ogretmen_ids), '?')); // Parametre yerleri
                $ogretmen_query = $pdo->prepare("SELECT ogretmen_no, ogretmen_adi, ogretmen_soyadi FROM ogretmenler WHERE ogretmen_no IN ($placeholders)");
                $ogretmen_query->execute($ogretmen_ids); // Öğretmen ID'leri ile sorguyu çalıştır

                // Öğretmen bilgilerini al
                $ogretmenler = $ogretmen_query->fetchAll(PDO::FETCH_ASSOC);

                $response['status'] = 'success';
                $response['ders'] = $ders;
                $response['ogretmenler'] = $ogretmenler; // İlgili Öğretmenleri döndür
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Dersin Öğretmen listesi boş!';
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
