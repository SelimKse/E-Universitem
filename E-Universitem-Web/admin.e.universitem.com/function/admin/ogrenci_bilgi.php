<?php
session_start();
include '../connect_db.php';

// Öğrenci bilgilerini getiren kod öğrenci id'sine göre
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // JSON formatında gelen veriyi al
    $data = json_decode(file_get_contents("php://input"));

    // Form verilerini al
    $ogrenci_no = $data->ogrenci_no;

    // Yanıt için bir dizi oluştur
    $response = [];

    // Öğrenci ID boş mu?
    if (empty($ogrenci_no)) {
        $response['status'] = 'error';
        $response['message'] = 'Öğrenci ID boş bırakılamaz!';
    } else {
        // Öğrenci bilgilerini getir
        $sql = "SELECT * FROM ogrenciler WHERE ogrenci_no = :ogrenci_no";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':ogrenci_no', $ogrenci_no, PDO::PARAM_INT);
        $stmt->execute();

        $ogrenci = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ogrenci) {
            $response['status'] = 'success';
            $response['ogrenci'] = $ogrenci;
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Öğrenci bulunamadı!';
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
