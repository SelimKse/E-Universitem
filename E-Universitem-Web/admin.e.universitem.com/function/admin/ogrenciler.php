<?php
session_start();
include '../connect_db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"));
    $ogrenci_no = $data->ogrenci_no;

    $response = [];

    $sql = "SELECT * FROM ogrenciler WHERE ogrenci_no = :ogrenci_no";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':ogrenci_no', $ogrenci_no, PDO::PARAM_INT);
    $stmt->execute();

    $ogrenci = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ogrenci) {
        $response['status'] = 'success';
        $response['data'] = $ogrenci ? $ogrenci : []; // Listeyi döndür
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Öğrenci bulunamadı!';
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