<?php
session_start();
include '../connect_db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // JSON formatında gelen veriyi al
    $data = json_decode(file_get_contents("php://input"));

    // Form verilerini al
    $ders_adı = $data->ders_adı ?? null;

    // Yanıt için bir dizi oluştur
    $response = [];

    // Ders adı boş mu?
    if (empty($ders_adı)) {
        $response['status'] = 'error';
        $response['message'] = 'Ders adı boş bırakılamaz!';
    } else {
        // Önce dersin var olup olmadığını kontrol et
        $checkSql = "SELECT * FROM dersler WHERE ders_adi = :ders_adi";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindParam(':ders_adi', $ders_adı);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            // Aynı isimde bir ders varsa
            $response['status'] = 'error';
            $response['message'] = 'Bu isimde bir ders zaten mevcut!';
        } else {
            // SQL sorgusu
            $sql = "INSERT INTO dersler (ders_adi, olusturulma_tarihi) VALUES (:ders_adi, NOW())";

            try {
                $stmt = $pdo->prepare($sql);
                // Parametreyi bağla
                $stmt->bindParam(':ders_adi', $ders_adı);

                // Sorguyu çalıştır
                $stmt->execute();

                $response['status'] = 'success';
                $response['message'] = 'Ders başarıyla eklendi.';
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
