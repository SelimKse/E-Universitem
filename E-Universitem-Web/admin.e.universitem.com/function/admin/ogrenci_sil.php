<?php
session_start();
include '../connect_db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // JSON formatında gelen veriyi al
    $data = json_decode(file_get_contents("php://input"));

    // Form verilerini al
    $ogrenciNo = $data->ogrenciNo;

    // Yanıt için bir dizi oluştur
    $response = [];

    // Ders ID boş mu?
    if (empty($ogrenciNo)) {
        $response['status'] = 'error';
        $response['message'] = 'Öğrenci ID boş bırakılamaz!';
    } else {
        // Önce dersin var olup olmadığını kontrol et
        $checkSql = "SELECT * FROM ogrenciler WHERE ogrenci_no = :ogrenciNo";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindParam(':ogrenciNo', $ogrenciNo, PDO::PARAM_INT);
        $checkStmt->execute();

        if ($checkStmt->rowCount() === 0) {
            // Belirtilen ID'ye sahip ders yoksa
            $response['status'] = 'error';
            $response['message'] = 'Bu ID ile eşleşen bir öğrenci bulunamadı!';
        } else {
            // SQL sorgusu: dersi sil
            $sql = "DELETE FROM ogrenciler WHERE ogrenci_no = :ogrenciNo";

            try {
                $stmt = $pdo->prepare($sql);
                // Parametreyi bağla
                $stmt->bindParam(':ogrenciNo', $ogrenciNo, PDO::PARAM_INT);

                // Sorguyu çalıştır
                $stmt->execute();

                $response['status'] = 'success';
                $response['message'] = 'Öğrenci başarıyla silindi.';
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
