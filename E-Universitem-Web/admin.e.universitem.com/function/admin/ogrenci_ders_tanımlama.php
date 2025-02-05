<?php
session_start();
include '../connect_db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Gelen JSON verisini al
    $data = json_decode(file_get_contents("php://input"));

    $ogrenci_no = $data->ogrenci_no ?? null;
    $ders_idleri = $data->ders_id ?? null; // Ders ID'leri boşsa null olur

    $response = [];

    // Gerekli alanların kontrolü
    if (empty($ogrenci_no)) {
        $response['status'] = 'error';
        $response['message'] = 'Öğrenci numarası boş bırakılamaz!';
    } else {
        try {
            // Öğrencinin varlığını kontrol et
            $checkStudentSql = "SELECT aldigi_dersler FROM ogrenciler WHERE ogrenci_no = :ogrenci_no";
            $checkStudentStmt = $pdo->prepare($checkStudentSql);
            $checkStudentStmt->bindParam(':ogrenci_no', $ogrenci_no);
            $checkStudentStmt->execute();

            $studentData = $checkStudentStmt->fetch(PDO::FETCH_ASSOC);

            if (!$studentData) {
                $response['status'] = 'error';
                $response['message'] = 'Bu öğrenci numarasına ait kayıt bulunamadı!';
            } else {
                if (empty($ders_idleri)) {
                    // Eğer ders ID'leri boşsa, aldigi_dersler sütununu null olarak güncelle
                    $updateSql = "UPDATE ogrenciler SET aldigi_dersler = NULL WHERE ogrenci_no = :ogrenci_no";
                    $updateStmt = $pdo->prepare($updateSql);
                    $updateStmt->bindParam(':ogrenci_no', $ogrenci_no);
                    $updateStmt->execute();

                    $response['status'] = 'success';
                    $response['message'] = 'Dersler başarıyla güncellendi!';
                } else {
                    // Mevcut dersler
                    $existingDersler = json_decode($studentData['aldigi_dersler'], true) ?? [];

                    // Gönderilen ders ID'lerine ait ders adlarını al
                    $placeholders = implode(',', array_fill(0, count($ders_idleri), '?'));
                    $getDerslerSql = "SELECT ders_adi FROM dersler WHERE ders_id IN ($placeholders)";
                    $getDerslerStmt = $pdo->prepare($getDerslerSql);
                    $getDerslerStmt->execute($ders_idleri);
                    $newDersler = $getDerslerStmt->fetchAll(PDO::FETCH_COLUMN);

                    // Ders adlarını karşılaştır
                    if (!array_diff($newDersler, $existingDersler) && !array_diff($existingDersler, $newDersler)) {
                        $response['status'] = 'info';
                        $response['message'] = 'Değişiklik yapılmadı, dersler zaten aynı.';
                    } else {
                        // Ders adlarını JSON olarak kaydet
                        $newDerslerJson = json_encode($newDersler, JSON_UNESCAPED_UNICODE);

                        // Öğrenciye ders adlarını güncelle
                        $updateSql = "UPDATE ogrenciler SET aldigi_dersler = :dersler WHERE ogrenci_no = :ogrenci_no";
                        $updateStmt = $pdo->prepare($updateSql);
                        $updateStmt->bindParam(':dersler', $newDerslerJson);
                        $updateStmt->bindParam(':ogrenci_no', $ogrenci_no);
                        $updateStmt->execute();

                        $response['status'] = 'success';
                        $response['message'] = 'Dersler başarıyla güncellendi!';
                    }
                }
            }
        } catch (PDOException $e) {
            $response['status'] = 'error';
            $response['message'] = 'Veritabanı hatası: ' . $e->getMessage();
        }
    }

    // Yanıtı JSON olarak döndür
    header('Content-Type: application/json');
    echo json_encode($response);

} else {
    $response = [
        'status' => 'error',
        'message' => 'Geçersiz istek yöntemi!'
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
}
