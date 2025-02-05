<?php
session_start();
include '../connect_db.php';

// Ders bilgilerini ve eklenecek öğrenci numarasını almak
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // JSON formatında gelen veriyi al
    $data = json_decode(file_get_contents("php://input"));

    // Form verilerini al
    $ders_id = $data->ders_id;
    $ogrenci_no = (string) $data->ogrenci_no; // Öğrenci numarasını string'e dönüştür

    // Yanıt için bir dizi oluştur
    $response = [];

    // Ders ID ve Öğrenci No boş mu?
    if (empty($ders_id) || empty($ogrenci_no)) {
        $response['status'] = 'error';
        $response['message'] = 'Ders ID ve Öğrenci No boş bırakılamaz!';
    } else {
        // Ders bilgilerini getir
        $sql = "SELECT * FROM dersler WHERE ders_id = :ders_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':ders_id', $ders_id, PDO::PARAM_INT);
        $stmt->execute();

        $ders = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ders) {
            // Eğer ders bulunduysa, öğrenci bilgilerini al
            $ogrenciler = $ders['ogrenciler']; // Öğrenci numaraları string halinde

            // Eğer $ogrenciler NULL veya boşsa, boş bir dizi oluştur
            $ogrenciler_array = !empty($ogrenciler) ? json_decode($ogrenciler) : [];

            // Her elemanı string yap (güvenlik için mevcut diziyi kontrol et)
            $ogrenciler_array = array_map('strval', $ogrenciler_array);

            // Eğer öğrenci zaten listede yoksa ekle
            if (!in_array($ogrenci_no, $ogrenciler_array)) {
                $ogrenciler_array[] = $ogrenci_no; // Yeni öğrenci numarasını ekle

                // Güncellenmiş öğrenci listesi
                $yeni_ogrenciler = json_encode($ogrenciler_array); // Array'i JSON formatına çevir

                // Dersler tablosunu güncelle
                $update_sql = "UPDATE dersler SET ogrenciler = :ogrenciler WHERE ders_id = :ders_id";
                $update_stmt = $pdo->prepare($update_sql);
                $update_stmt->bindParam(':ogrenciler', $yeni_ogrenciler);
                $update_stmt->bindParam(':ders_id', $ders_id, PDO::PARAM_INT);

                if ($update_stmt->execute()) {
                    $response['status'] = 'success';
                    $response['message'] = 'Öğrenci başarıyla eklendi.';
                } else {
                    $response['status'] = 'error';
                    $response['message'] = 'Ders güncellenemedi.';
                }
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Öğrenci zaten bu derste mevcut.';
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
?>