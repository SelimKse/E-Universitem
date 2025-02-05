<?php
session_start();
include '../connect_db.php';

// Ders bilgilerini ve eklenecek Öğretmen numarasını almak
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

            // Eğer Öğretmen zaten listede yoksa ekle
            if (!in_array($ogretmen_no, $ogretmenler_array)) {
                $ogretmenler_array[] = $ogretmen_no; // Yeni Öğretmen numarasını ekle

                // Güncellenmiş Öğretmen listesi
                $yeni_ogretmenler = json_encode($ogretmenler_array); // Array'i JSON formatına çevir

                // Dersler tablosunu güncelle
                $update_sql = "UPDATE dersler SET ogretmenler = :ogretmenler WHERE ders_id = :ders_id";
                $update_stmt = $pdo->prepare($update_sql);
                $update_stmt->bindParam(':ogretmenler', $yeni_ogretmenler);
                $update_stmt->bindParam(':ders_id', $ders_id, PDO::PARAM_INT);

                if ($update_stmt->execute()) {
                    $response['status'] = 'success';
                    $response['message'] = 'Öğretmen başarıyla eklendi.';
                } else {
                    $response['status'] = 'error';
                    $response['message'] = 'Ders güncellenemedi.';
                }
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Öğretmen zaten bu derste mevcut.';
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