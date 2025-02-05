<?php
session_start();
header('Content-Type: application/json');

try {
    if (!isset($_SESSION['ogretmen_data']['ogretmen_no'])) {
        throw new Exception("Oturum bilgisi bulunamadı!");
    }

    $response = [
        'status' => 'success',
        'data' => $_SESSION['ogretmen_data'],
    ];
} catch (Exception $e) {
    $response = [
        'status' => 'error',
        'message' => $e->getMessage(),
    ];
}

echo json_encode($response);
exit;

?>