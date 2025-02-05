<?php
// Veritabanı bağlantısı
include './connect_db.php';

try {
    // Süresi dolmuş ve aktiflik durumu 1 olan yoklamaları kontrol edip aktifliği 0 yap
    $sql = "UPDATE yoklamalar 
            SET aktiflik = 0 
            WHERE bitis_tarihi <= NOW() AND aktiflik = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    // Güncellenen satır sayısını kontrol edin
    $updatedRows = $stmt->rowCount();
    echo $updatedRows . " yoklama pasif hale getirildi.\n";
} catch (PDOException $e) {
    // Hata mesajı yazdır
    echo "Hata: " . $e->getMessage();
}
?>