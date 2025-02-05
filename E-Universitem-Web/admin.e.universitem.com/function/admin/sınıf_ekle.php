<?php
session_start();
include '../connect_db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // JSON formatında gelen veriyi al
    $data = json_decode(file_get_contents("php://input"));

    // Form verilerini al
    $sınıf_adı = $data->sınıf_adı ?? null;

    // Yanıt için bir dizi oluştur
    $response = [];

    // Sınıf adı boş mu?
    if (empty($sınıf_adı)) {
        $response['status'] = 'error';
        $response['message'] = 'Sınıf adı boş bırakılamaz!';
    } else {
        // Önce sınıfın var olup olmadığını kontrol et
        $checkSql = "SELECT * FROM siniflar WHERE sinif_adi = :sinif_adi";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindParam(':sinif_adi', $sınıf_adı);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            // Aynı isimde bir sınıf varsa
            $response['status'] = 'error';
            $response['message'] = 'Bu isimde bir sınıf zaten mevcut!';
        } else {
            // SQL sorgusu
            $sql = "INSERT INTO siniflar (sinif_adi, olusturulma_tarihi) VALUES (:sinif_adi, NOW())";

            try {
                $stmt = $pdo->prepare($sql);
                // Parametreyi bağla
                $stmt->bindParam(':sinif_adi', $sınıf_adı);

                // Sorguyu çalıştır
                $stmt->execute();

                $response['status'] = 'success';
                $response['message'] = 'Sınıf başarıyla eklendi.';
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
