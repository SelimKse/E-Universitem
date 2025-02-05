<?php
session_start();
include '../connect_db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // JSON formatında gelen veriyi al
    $data = json_decode(file_get_contents("php://input"));

    // Form verilerini al
    $sinif_id = $data->sinif_id ?? null; // Sınıf ID
    $sinif_adi = $data->sinif_adi ?? null; // Sınıf Adı

    // Yanıt için bir dizi oluştur
    $response = [];

    // Sınıf ID ve adı boş mu?
    if (empty($sinif_id) || empty($sinif_adi)) {
        $response['status'] = 'error';
        $response['message'] = 'Sınıf ID veya adı boş bırakılamaz!';
    } else {
        // Önce sınıfın var olup olmadığını kontrol et
        $checkSql = "SELECT * FROM siniflar WHERE sinif_id = :sinif_id";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindParam(':sinif_id', $sinif_id);
        $checkStmt->execute();

        if ($checkStmt->rowCount() == 0) {
            // Sınıf bulunamazsa
            $response['status'] = 'error';
            $response['message'] = 'Ders bulunamadı!';
        } else {
            // Sınıf güncelleme SQL sorgusu
            $sql = "UPDATE siniflar SET sinif_adi = :sinif_adi WHERE sinif_id = :sinif_id";

            try {
                $stmt = $pdo->prepare($sql);
                // Parametreleri bağla
                $stmt->bindParam(':sinif_adi', $sinif_adi);
                $stmt->bindParam(':sinif_id', $sinif_id);

                // Sorguyu çalıştır
                $stmt->execute();

                $response['status'] = 'success';
                $response['message'] = 'Sınıf başarıyla güncellendi.';
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
