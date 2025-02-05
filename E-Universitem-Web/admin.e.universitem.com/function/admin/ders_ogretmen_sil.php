<?php
session_start();
include '../connect_db.php';

// Ders bilgilerini ve silinecek Öğretmen numarasını almak
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // JSON formatında gelen veriyi al
    $data = json_decode(file_get_contents("php://input"));

    // Form verilerini al
    $ders_id = $data->ders_id;
    $ogretmen_no = (string) $data->ogretmen_no; // Öğretmen numarasını string'e dönüştür

    // Yanıt için bir dizi oluştur
    $response = [];

    // Ders ID ve Öğretmen No boş mu?
    if (empty($ders_id) || empty($ogretmen_no)) {
        $response['status'] = 'error';
        $response['message'] = 'Ders ID ve Öğretmen No boş bırakılamaz!';
    } else {
        // Ders bilgilerini getir
        $sql = "SELECT * FROM dersler WHERE ders_id = :ders_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':ders_id', $ders_id, PDO::PARAM_INT);
        $stmt->execute();

        $ders = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ders) {
            // Eğer ders bulunduysa, Öğretmen bilgilerini al
            $ogretmenler = $ders['ogretmenler']; // Öğretmen numaraları string halinde

            // Eğer $ogretmenler NULL veya boşsa, boş bir dizi oluştur
            $ogretmenler_array = !empty($ogretmenler) ? json_decode($ogretmenler) : [];

            // Her elemanı string yap (güvenlik için mevcut diziyi kontrol et)
            $ogretmenler_array = array_map('strval', $ogretmenler_array);

            // Eğer Öğretmen listede varsa, sil
            if (($key = array_search($ogretmen_no, $ogretmenler_array)) !== false) {
                unset($ogretmenler_array[$key]); // Öğretmen numarasını diziden çıkar

                // Array'deki boş elemanları temizle ve diziyi yeniden sıralama
                $ogretmenler_array = array_values($ogretmenler_array);

                // Eğer dizi boş ise NULL olarak güncelle
                if (empty($ogretmenler_array)) {
                    $yeni_ogretmenler = NULL; // Eğer Öğretmen listesi boşsa NULL yapıyoruz
                } else {
                    // Güncellenmiş Öğretmen listesi
                    $yeni_ogretmenler = json_encode($ogretmenler_array); // Array'i JSON formatına çevir
                }

                // Dersler tablosunu güncelle
                $update_sql = "UPDATE dersler SET ogretmenler = :ogretmenler WHERE ders_id = :ders_id";
                $update_stmt = $pdo->prepare($update_sql);
                $update_stmt->bindParam(':ogretmenler', $yeni_ogretmenler, PDO::PARAM_STR);
                $update_stmt->bindParam(':ders_id', $ders_id, PDO::PARAM_INT);

                if ($update_stmt->execute()) {
                    $response['status'] = 'success';
                    $response['message'] = 'Öğretmen başarıyla silindi.';
                } else {
                    $response['status'] = 'error';
                    $response['message'] = 'Ders güncellenemedi.';
                }
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Öğretmen bu derste bulunamadı.';
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