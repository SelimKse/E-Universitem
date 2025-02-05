<?php
session_start();
include '../connect_db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // JSON formatında gelen veriyi al
    $data = json_decode(file_get_contents("php://input"));

    // Form verilerini al
    $sinif_id = $data->sinif_id;

    // Yanıt için bir dizi oluştur
    $response = [];

    // Sınıf ID boş mu?
    if (empty($sinif_id)) {
        $response['status'] = 'error';
        $response['message'] = 'Sınıf ID boş bırakılamaz!';
    } else {
        // Önce sınıfın var olup olmadığını kontrol et
        $checkSql = "SELECT * FROM siniflar WHERE sinif_id = :sinif_id";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindParam(':sinif_id', $sinif_id, PDO::PARAM_INT);
        $checkStmt->execute();

        if ($checkStmt->rowCount() === 0) {
            // Belirtilen ID'ye sahip sınıf yoksa
            $response['status'] = 'error';
            $response['message'] = 'Bu ID ile eşleşen bir sınıf bulunamadı!';
        } else {
            // SQL sorgusu: sınıfı sil
            $sql = "DELETE FROM siniflar WHERE sinif_id = :sinif_id";

            try {
                $stmt = $pdo->prepare($sql);
                // Parametreyi bağla
                $stmt->bindParam(':sinif_id', $sinif_id, PDO::PARAM_INT);

                // Sorguyu çalıştır
                $stmt->execute();

                $response['status'] = 'success';
                $response['message'] = 'Sınıf başarıyla silindi.';
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
