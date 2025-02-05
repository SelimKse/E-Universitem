<?php
session_start();
include '../connect_db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"));
    $ogretmen_no = $data->ogretmen_no;

    $response = [];

    $sql = "SELECT * FROM ogretmenler WHERE ogretmen_no = :ogretmen_no";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':ogretmen_no', $ogretmen_no, PDO::PARAM_INT);
    $stmt->execute();

    $ogretmen = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ogretmen) {
        $response['status'] = 'success';
        $response['data'] = $ogretmen ? $ogretmen : []; // Listeyi döndür
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Öğretmen bulunamadı!';
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