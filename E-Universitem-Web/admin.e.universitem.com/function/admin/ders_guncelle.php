<?php
session_start();
include '../connect_db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // JSON formatında gelen veriyi al
    $data = json_decode(file_get_contents("php://input"));

    // Form verilerini al
    $ders_id = $data->ders_id ?? null; // Ders ID
    $ders_adi = $data->ders_adi ?? null; // Ders Adı

    // Yanıt için bir dizi oluştur
    $response = [];

    // Ders ID ve adı boş mu?
    if (empty($ders_id) || empty($ders_adi)) {
        $response['status'] = 'error';
        $response['message'] = 'Ders ID ve adı boş bırakılamaz!';
    } else {
        // Önce dersin var olup olmadığını kontrol et
        $checkSql = "SELECT * FROM dersler WHERE ders_id = :ders_id";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindParam(':ders_id', $ders_id);
        $checkStmt->execute();

        if ($checkStmt->rowCount() == 0) {
            // Ders bulunamazsa
            $response['status'] = 'error';
            $response['message'] = 'Ders bulunamadı!';
        } else {
            // Ders güncelleme SQL sorgusu
            $sql = "UPDATE dersler SET ders_adi = :ders_adi WHERE ders_id = :ders_id";

            try {
                $stmt = $pdo->prepare($sql);
                // Parametreleri bağla
                $stmt->bindParam(':ders_adi', $ders_adi);
                $stmt->bindParam(':ders_id', $ders_id);

                // Sorguyu çalıştır
                $stmt->execute();

                $response['status'] = 'success';
                $response['message'] = 'Ders başarıyla güncellendi.';
            } catch (PDOException $e) {
                // Hata durumunda hata mesajını döndür
                $response['status'] = 'error';
                $response['message'] = 'Veritabanı hatası: ' . $e->getMessage();
            }
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
