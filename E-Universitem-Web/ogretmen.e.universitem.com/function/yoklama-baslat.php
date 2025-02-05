<?php
// Veritabanı bağlantısı
include '../connect_db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"));

    // Gelen verileri al ve kontrol et
    $ders_id = $data->ders ?? null;
    $sinif_id = $data->sinif ?? null;
    $ogretmen_id = $data->ogretmen ?? null;
    $baslangic_tarihi = $data->baslangic ?? null;
    $bitis_tarihi = $data->bitis ?? null;

    $response = [];

    if (empty($ders_id) || empty($sinif_id) || empty($ogretmen_id) || empty($baslangic_tarihi) || empty($bitis_tarihi)) {
        $response['status'] = 'error';
        $response['message'] = 'Eksik veri gönderildi!';
    } else {
        try {
            // Aynı sınıfta ve derste aktif yoklama kontrolü
            $checkActiveSql = "
                SELECT * 
                FROM yoklamalar 
                WHERE ders_id = :ders_id 
                AND sinif_id = :sinif_id 
                AND aktiflik = 1";
            $checkActiveStmt = $pdo->prepare($checkActiveSql);
            $checkActiveStmt->bindParam(':ders_id', $ders_id, PDO::PARAM_INT);
            $checkActiveStmt->bindParam(':sinif_id', $sinif_id, PDO::PARAM_INT);
            $checkActiveStmt->execute();

            if ($checkActiveStmt->rowCount() > 0) {
                $response['status'] = 'error';
                $response['message'] = 'Aynı sınıf ve derste yoklama mevcut!';
            } else {
                // Yoklama ekleme sorgusu
                $sql = "
                    INSERT INTO yoklamalar 
                    (ders_id, sinif_id, ogretmen_no, baslatilma_tarihi, bitis_tarihi, aktiflik, ozel_kod) 
                    VALUES (:ders_id, :sinif_id, :ogretmen_no, :baslangic_tarihi, :bitis_tarihi, :aktiflik, :ozel_kod)";
                $stmt = $pdo->prepare($sql);

                // Parametre bağlama
                $stmt->bindParam(':ders_id', $ders_id, PDO::PARAM_INT);
                $stmt->bindParam(':sinif_id', $sinif_id, PDO::PARAM_INT);
                $stmt->bindParam(':ogretmen_no', $ogretmen_id, PDO::PARAM_INT);
                $stmt->bindParam(':baslangic_tarihi', $baslangic_tarihi, PDO::PARAM_STR);
                $stmt->bindParam(':bitis_tarihi', $bitis_tarihi, PDO::PARAM_STR);
                $stmt->bindValue(':aktiflik', 1, PDO::PARAM_INT);

                // Özel kod oluştur
                $ozel_kod = substr(md5(uniqid(mt_rand(), true)), 0, 8);
                $stmt->bindParam(':ozel_kod', $ozel_kod, PDO::PARAM_STR);

                // Sorguyu çalıştır
                $stmt->execute();

                $response['status'] = 'success';
                $response['message'] = 'Yoklama başarıyla başlatıldı.';
                $response['data'] = [
                    'yoklama_id' => $pdo->lastInsertId(),
                    'yoklama_kodu' => $ozel_kod
                ];
            }
        } catch (PDOException $e) {
            $response['status'] = 'error';
            $response['message'] = 'Veritabanı hatası: ' . $e->getMessage();
        }
    }

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
?>