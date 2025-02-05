<?php
// Veritabanı bağlantısı için gerekli bilgileri girin
$host = "localhost";   // Veritabanı sunucusu (genellikle localhost)
$dbname = "ogrenci_yoklama";  // Veritabanı adı
$username = "root";  // Veritabanı kullanıcı adı
$password = "";  // Veritabanı şifresi

try {
    // PDO ile veritabanına bağlanıyoruz
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Hata durumunda Exception fırlatmasını sağlıyoruz
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Bağlantı hatası varsa ekrana hata mesajı basıyoruz
    die("Veritabanı bağlantısı başarısız: " . $e->getMessage());
}
