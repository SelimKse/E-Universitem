<?php
session_start();
include '../connect_db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"));
    $sinif_id = $data->sinif_id;

    $response = [];

    $sql = "SELECT * FROM siniflar WHERE sinif_id = :sinif_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':sinif_id', $sinif_id, PDO::PARAM_INT);
    $stmt->execute();

    $sınıf = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($sınıf) {
        $response['status'] = 'success';
        $response['data'] = $sınıf ? $sınıf : []; // Listeyi döndür
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Sınıf bulunamadı!';
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