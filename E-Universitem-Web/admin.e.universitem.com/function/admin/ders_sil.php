<?php
session_start();
include '../connect_db.php';

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
        // Önce dersin var olup olmadığını kontrol et
        $checkSql = "SELECT * FROM dersler WHERE ders_id = :ders_id";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindParam(':ders_id', $ders_id, PDO::PARAM_INT);
        $checkStmt->execute();

        if ($checkStmt->rowCount() === 0) {
            // Belirtilen ID'ye sahip ders yoksa
            $response['status'] = 'error';
            $response['message'] = 'Bu ID ile eşleşen bir ders bulunamadı!';
        } else {
            // SQL sorgusu: dersi sil
            $sql = "DELETE FROM dersler WHERE ders_id = :ders_id";

            try {
                $stmt = $pdo->prepare($sql);
                // Parametreyi bağla
                $stmt->bindParam(':ders_id', $ders_id, PDO::PARAM_INT);

                // Sorguyu çalıştır
                $stmt->execute();

                $response['status'] = 'success';
                $response['message'] = 'Ders başarıyla silindi.';
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
