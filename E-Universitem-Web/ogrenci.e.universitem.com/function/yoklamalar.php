<?php

session_start();

// Veritabanı bağlantısını dahil et
include 'connect_db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $data = json_decode(file_get_contents("php://input"));

    $ogrenci_no = $data->ogrenci_no;

    $response = [];

    try {
        // sistemdeki tüm yoklamaları çek
        $sql = "SELECT * FROM yoklamalar";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        $yoklamalar = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // yoklamalar arasında katilan_ogrenciler sütunundan ogrenci_no'yu ara
        $yoklama = array_filter($yoklamalar, function ($yoklama) use ($ogrenci_no) {
            return in_array($ogrenci_no, json_decode($yoklama['katilan_ogrenciler']));
        });

        // var olan yoklamaları döndür
        $response = [
            'status' => 'success',
            'data' => $yoklama,
        ];

    } catch (PDOException $e) {
        $response['status'] = 'error';
        $response['message'] = $e->getMessage();
    }

    // Yoklama bilgilerini döndür
    header('Content-Type: application/json');
    echo json_encode($response);

    exit;

} else {
    $response = [
        'status' => 'error',
        'message' => 'Geçersiz istek!'
    ];

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}