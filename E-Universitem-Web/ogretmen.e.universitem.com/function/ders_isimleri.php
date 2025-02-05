<?php

include 'connect_db.php';

// ders_isimleri_endpoint.php
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

// Gelen ders_id'leri kontrol et
if (!isset($data['dersIds']) || !is_array($data['dersIds']) || empty($data['dersIds'])) {
    echo json_encode(['error' => 'Geçersiz veya boş ders ID\'leri']);
    exit;
}

$dersIds = $data['dersIds'];

try {
    // Ders id'leri için placeholders ('?' işaretleri)
    $placeholders = str_repeat('?,', count($dersIds) - 1) . '?';

    // Ders id'lerine göre dersleri çek
    $sql = "SELECT ders_id, ders_adi FROM dersler WHERE ders_id IN ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($dersIds);

    // Dersleri al
    $dersler = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($dersler); // JSON formatında döndür
} catch (PDOException $e) {
    echo json_encode(['error' => 'Veritabanı hatası: ' . $e->getMessage()]);
}
?>