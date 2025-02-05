<?php
include 'connect_db.php';

// Bildirimler tablosundan öğrenciye ait en yeni 5 bildirimi almak
header('Content-Type: application/json');

// Gelen veriyi al (ogrenci_no)
$data = json_decode(file_get_contents('php://input'), true);

// Öğrenci numarasını kontrol et
if (!isset($data['ogrenci_no'])) {
    echo json_encode(['error' => 'ogrenci_no parametresi eksik']);
    exit;
}

$ogrenci_no = $data['ogrenci_no'];
$response = [];

try {
    // Son 5 bildirimi almak için SQL sorgusu
    $sql = "
        SELECT id, bildirim_metni, tarih 
        FROM bildirimler 
        WHERE user_id = :user_id 
        ORDER BY tarih DESC 
        LIMIT 5
    ";

    // Sorguyu hazırlama
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $ogrenci_no, PDO::PARAM_INT);
    $stmt->execute();

    // Bildirimleri al
    $bildirimler = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Bildirimleri döndür
    $response = [
        'status' => 'success',
        'data' => $bildirimler,
    ];

} catch (PDOException $e) {
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>