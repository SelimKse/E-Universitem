<?php
session_start();
include '../connect_db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"));
    $ders_id = $data->ders_id;

    $response = [];

    $sql = "SELECT * FROM dersler WHERE ders_id = :ders_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':ders_id', $ders_id, PDO::PARAM_INT);
    $stmt->execute();

    $ders = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ders) {
        $response['status'] = 'success';
        $response['data'] = $ders ? $ders : []; // Listeyi döndür
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Ders bulunamadı!';
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