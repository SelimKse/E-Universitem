<?php
session_start();
include '../connect_db.php';

// Ders bilgilerini ve silinecek öğrenci numarasını almak
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

            // Eğer öğrenci listede varsa, sil
            if (($key = array_search($ogrenci_no, $ogrenciler_array)) !== false) {
                unset($ogrenciler_array[$key]); // Öğrenci numarasını diziden çıkar

                // Array'deki boş elemanları temizle ve diziyi yeniden sıralama
                $ogrenciler_array = array_values($ogrenciler_array);

                // Eğer dizi boş ise NULL olarak güncelle
                if (empty($ogrenciler_array)) {
                    $yeni_ogrenciler = NULL; // Eğer öğrenci listesi boşsa NULL yapıyoruz
                } else {
                    // Güncellenmiş öğrenci listesi
                    $yeni_ogrenciler = json_encode($ogrenciler_array); // Array'i JSON formatına çevir
                }

                // Dersler tablosunu güncelle
                $update_sql = "UPDATE dersler SET ogrenciler = :ogrenciler WHERE ders_id = :ders_id";
                $update_stmt = $pdo->prepare($update_sql);
                $update_stmt->bindParam(':ogrenciler', $yeni_ogrenciler, PDO::PARAM_STR);
                $update_stmt->bindParam(':ders_id', $ders_id, PDO::PARAM_INT);

                if ($update_stmt->execute()) {
                    $response['status'] = 'success';
                    $response['message'] = 'Öğrenci başarıyla silindi.';
                } else {
                    $response['status'] = 'error';
                    $response['message'] = 'Ders güncellenemedi.';
                }
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Öğrenci bu derste bulunamadı.';
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