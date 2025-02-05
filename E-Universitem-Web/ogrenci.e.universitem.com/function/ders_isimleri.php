<?php
// ders_isimleri_endpoint.php
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

// Gelen ders_id'leri kontrol et
if (!isset($data['dersIds']) || !is_array($data['dersIds'])) {
    echo json_encode(['error' => 'Geçersiz veri']);
    exit;
}

$dersIds = $data['dersIds'];

try {
    $pdo = new PDO("mysql:host=localhost;dbname=veritabani_adi", "kullanici_adi", "sifre");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
    echo json_encode(['error' => 'Veritabanı hatası']);
}
?>