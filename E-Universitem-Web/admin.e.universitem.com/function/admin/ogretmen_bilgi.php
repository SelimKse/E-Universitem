<?php
session_start();
include '../connect_db.php';

// öğretmen bilgilerini getiren kod öğretmen id'sine göre
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // JSON formatında gelen veriyi al
    $data = json_decode(file_get_contents("php://input"));

    // Form verilerini al
    $ogretmen_no = $data->ogretmen_no;

    // Yanıt için bir dizi oluştur
    $response = [];

    // öğretmen ID boş mu?
    if (empty($ogretmen_no)) {
        $response['status'] = 'error';
        $response['message'] = 'Öğretmen ID boş bırakılamaz!';
    } else {
        // öğretmen bilgilerini getir
        $sql = "SELECT * FROM ogretmenler WHERE ogretmen_no = :ogretmen_no";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':ogretmen_no', $ogretmen_no, PDO::PARAM_INT);
        $stmt->execute();

        $ogretmen = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ogretmen) {
            $response['status'] = 'success';
            $response['ogretmen'] = $ogretmen;
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Öğretmen bulunamadı!';
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
