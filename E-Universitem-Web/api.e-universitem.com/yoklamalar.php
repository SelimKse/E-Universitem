<?php
header('Content-Type: application/json; charset=UTF-8'); // UTF-8 ile JSON başlığı ekliyoruz
require_once 'database.php';

$request = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($request, PHP_URL_PATH); // Sadece URL'nin path kısmını al
parse_str($_SERVER['QUERY_STRING'], $queryParams);

if ($requestPath == '/yoklamalar') {
    if (checkApiKey($queryParams) === false) {
        return;
    }
    getYoklamalar();
} else if ($requestPath == '/yoklamalar/olustur') {
    if (checkApiKey($queryParams) === false) {
        return;
    }
    createYoklamalar();
} else if ($requestPath == '/yoklamalar/guncelle') {
    if (checkApiKey($queryParams) === false) {
        return;
    }
    updateYoklamalar();
} else if ($requestPath == '/yoklamalar/sil') {
    if (checkApiKey($queryParams) === false) {
        return;
    }
    deleteYoklamalar();
} else {
    header('HTTP/1.1 404 Not Found');
    echo json_encode([
        'status' => 'error',
        'message' => 'Geçersiz istek'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit();
}

function verifyApiKey($apiKey)
{
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM api_keys WHERE api_key = :api_key AND is_active = TRUE");
    $stmt->execute([':api_key' => $apiKey]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result !== false; // Geçerli ise true döner
}

function checkApiKey($queryParams)
{
    // API Key'in nereden alınacağını kontrol et
    $apiKey = null;

    // Eğer query parametrelerinde api_key varsa, onu al
    if (!empty($queryParams['api_key'])) {
        $apiKey = $queryParams['api_key'];
    } else {
        // Eğer query parametrelerinde api_key yoksa, header'dan alalım
        $headers = apache_request_headers();
        if (!empty($headers['X-Api-Key'])) {
            $apiKey = $headers['X-Api-Key'];
        }
    }

    // Eğer api_key hala yoksa, hata döndür
    if (empty($apiKey)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'API Key bulunamadı.'
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        header('HTTP/1.1 401 Unauthorized');
        return false;
    }

    // API anahtarını kontrol et
    if (!verifyApiKey($apiKey)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Geçersiz Api Key.'
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        header('HTTP/1.1 401 Unauthorized');
        return false;
    }

    return true;
}

function getYoklamalar()
{
    global $pdo;

    try {
        // GET parametrelerini al
        $yoklama_id = $_GET['yoklama_id'] ?? null;
        $aktiflik = $_GET['aktiflik'] ?? null;

        // Eğer GET parametresi yoksa, body'den al
        if (!$yoklama_id && !$aktiflik) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            $yoklama_id = $data['yoklama_id'] ?? null;
            $aktiflik = $data['aktiflik'] ?? null;
        }

        // SQL sorgusunu başlat
        $sql = "SELECT * FROM yoklamalar";
        $conditions = [];
        $parameters = [];

        // Yoklama ID varsa, koşul ekle
        if (!empty($yoklama_id)) {
            $conditions[] = "yoklama_id = :yoklama_id";
            $parameters[':yoklama_id'] = $yoklama_id;
        }

        // Aktiflik parametresi varsa, koşul ekle
        if (!empty($aktiflik)) {
            $conditions[] = "aktiflik = :aktiflik";
            $parameters[':aktiflik'] = $aktiflik;
        }

        // Eğer herhangi bir koşul varsa WHERE ekle
        if ($conditions) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        // SQL sorgusunu hazırla ve çalıştır
        $stmt = $pdo->prepare($sql);
        $stmt->execute($parameters);

        // Sonuçları al
        $yoklamalar = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($yoklamalar)) {
            // Eğer sonuç boşsa hata mesajı döndür
            echo json_encode([
                'status' => 'error',
                'message' => 'Yoklama bulunamadı'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            header('HTTP/1.1 404 Not Found');
        } else {
            // Sonuç varsa JSON formatında döndür
            echo json_encode([
                'status' => 'success',
                'data' => $yoklamalar
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            header('HTTP/1.1 200 OK');
        }
    } catch (Exception $e) {
        // Hata durumunda JSON formatında döndür
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        header('HTTP/1.1 500 Internal Server Error');
    }
}

function createYoklamalar()
{
    global $pdo;

    try {
        // Parametreleri al
        $ders_id = $_POST['ders_id'] ?? null;
        $sinif_id = $_POST['sinif_id'] ?? null;
        $ogretmen_id = $_POST['ogretmen_id'] ?? null;
        $katilan_ogrenciler = $_POST['katilan_ogrenciler'] ?? [];
        $baslatilma_tarihi = $_POST['baslatilma_tarihi'] ?? date('Y-m-d H:i:s'); // Varsayılan olarak şu anki tarih
        $bitis_tarihi = $_POST['bitis_tarihi'] ?? null;
        $aktiflik = $_POST['aktiflik'] ?? true;

        // Hata mesajlarını tutacak dizi
        $errors = [];

        // Gerekli parametrelerin kontrolü
        if (empty($ders_id)) {
            $errors[] = 'Ders ID boş bırakılamaz.';
        }

        if (empty($sinif_id)) {
            $errors[] = 'Sınıf ID boş bırakılamaz.';
        }

        if (empty($ogretmen_id)) {
            $errors[] = 'Öğretmen ID boş bırakılamaz.';
        }

        // Ders, sınıf ve öğretmen veritabanı kontrolleri
        $checkDers = $pdo->prepare("SELECT * FROM dersler WHERE ders_id = :ders_id");
        $checkDers->execute([':ders_id' => $ders_id]);
        if ($checkDers->rowCount() === 0) {
            $errors[] = 'Geçersiz Ders ID.';
        }

        $checkSinif = $pdo->prepare("SELECT * FROM siniflar WHERE sinif_id = :sinif_id");
        $checkSinif->execute([':sinif_id' => $sinif_id]);
        if ($checkSinif->rowCount() === 0) {
            $errors[] = 'Geçersiz Sınıf ID.';
        }

        $checkOgretmen = $pdo->prepare("SELECT * FROM ogretmenler WHERE ogretmen_id = :ogretmen_id");
        $checkOgretmen->execute([':ogretmen_id' => $ogretmen_id]);
        if ($checkOgretmen->rowCount() === 0) {
            $errors[] = 'Geçersiz Öğretmen ID.';
        }

        // Eğer hata varsa, hata mesajlarını döndür
        if (!empty($errors)) {
            echo json_encode([
                'status' => 'error',
                'message' => $errors
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400); // HTTP 400 Bad Request
            return;
        }

        // Bitiş tarihi yoksa, 15 dakika sonra varsayılan tarih oluştur
        if (empty($bitis_tarihi)) {
            $bitis_tarihi = date('Y-m-d H:i:s', strtotime('+15 minutes', strtotime($baslatilma_tarihi)));
        }

        // Yoklama ekleme işlemi
        $stmt = $pdo->prepare("
            INSERT INTO yoklamalar (ders_id, sinif_id, ogretmen_id, katilan_ogrenciler, baslatilma_tarihi, bitis_tarihi, aktiflik)
            VALUES (:ders_id, :sinif_id, :ogretmen_id, :katilan_ogrenciler, :baslatilma_tarihi, :bitis_tarihi, :aktiflik)
        ");

        // Katılan öğrenciler array olduğu için JSON olarak kaydedebiliriz
        $stmt->execute([
            ':ders_id' => $ders_id,
            ':sinif_id' => $sinif_id,
            ':ogretmen_id' => $ogretmen_id,
            ':katilan_ogrenciler' => json_encode($katilan_ogrenciler), // JSON formatında kaydediyoruz
            ':baslatilma_tarihi' => $baslatilma_tarihi,
            ':bitis_tarihi' => $bitis_tarihi,
            ':aktiflik' => $aktiflik
        ]);

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Yoklama başarıyla oluşturuldu.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(200); // HTTP 200 OK
            return;
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Yoklama oluşturulurken bir hata oluştu.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(500); // HTTP 500 Internal Server Error
            return;
        }

    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        http_response_code(500); // HTTP 500 Internal Server Error
    }
}


function updateYoklamalar()
{
    global $pdo;

    try {
        // Parametreleri al
        $yoklama_id = $_POST['yoklama_id'] ?? null;
        $ders_id = $_POST['ders_id'] ?? null;
        $sinif_id = $_POST['sinif_id'] ?? null;
        $ogretmen_id = $_POST['ogretmen_id'] ?? null;
        $katilan_ogrenciler = $_POST['katilan_ogrenciler'] ?? [];
        $baslatilma_tarihi = $_POST['baslatilma_tarihi'] ?? null;
        $bitis_tarihi = $_POST['bitis_tarihi'] ?? null;
        $aktiflik = $_POST['aktiflik'] ?? null;

        // Hata mesajlarını tutacak dizi
        $errors = [];

        // Yoklama ID kontrolü
        if (empty($yoklama_id)) {
            $errors[] = 'Yoklama ID boş bırakılamaz.';
        }

        // Geçerli yoklama var mı kontrol et
        $checkYoklama = $pdo->prepare("SELECT * FROM yoklamalar WHERE yoklama_id = :yoklama_id");
        $checkYoklama->execute([':yoklama_id' => $yoklama_id]);
        if ($checkYoklama->rowCount() === 0) {
            $errors[] = 'Geçersiz Yoklama ID.';
        }

        // Ders, sınıf ve öğretmen veritabanı kontrolleri
        if (!empty($ders_id)) {
            $checkDers = $pdo->prepare("SELECT * FROM dersler WHERE ders_id = :ders_id");
            $checkDers->execute([':ders_id' => $ders_id]);
            if ($checkDers->rowCount() === 0) {
                $errors[] = 'Geçersiz Ders ID.';
            }
        }

        if (!empty($sinif_id)) {
            $checkSinif = $pdo->prepare("SELECT * FROM siniflar WHERE sinif_id = :sinif_id");
            $checkSinif->execute([':sinif_id' => $sinif_id]);
            if ($checkSinif->rowCount() === 0) {
                $errors[] = 'Geçersiz Sınıf ID.';
            }
        }

        if (!empty($ogretmen_id)) {
            $checkOgretmen = $pdo->prepare("SELECT * FROM ogretmenler WHERE ogretmen_id = :ogretmen_id");
            $checkOgretmen->execute([':ogretmen_id' => $ogretmen_id]);
            if ($checkOgretmen->rowCount() === 0) {
                $errors[] = 'Geçersiz Öğretmen ID.';
            }
        }

        // Eğer hata varsa, hata mesajlarını döndür
        if (!empty($errors)) {
            echo json_encode([
                'status' => 'error',
                'message' => $errors
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400); // HTTP 400 Bad Request
            return;
        }

        // Eğer bitiş tarihi verilmemişse, 15 dakika sonra varsayılan tarih oluştur
        if (empty($bitis_tarihi)) {
            $bitis_tarihi = date('Y-m-d H:i:s', strtotime('+15 minutes', strtotime($baslatilma_tarihi)));
        }

        // Yoklama güncelleme işlemi
        $updateStmt = "UPDATE yoklamalar SET ";
        $params = [];

        if ($ders_id) {
            $updateStmt .= "ders_id = :ders_id, ";
            $params[':ders_id'] = $ders_id;
        }

        if ($sinif_id) {
            $updateStmt .= "sinif_id = :sinif_id, ";
            $params[':sinif_id'] = $sinif_id;
        }

        if ($ogretmen_id) {
            $updateStmt .= "ogretmen_id = :ogretmen_id, ";
            $params[':ogretmen_id'] = $ogretmen_id;
        }

        if (!empty($katilan_ogrenciler)) {
            $updateStmt .= "katilan_ogrenciler = :katilan_ogrenciler, ";
            $params[':katilan_ogrenciler'] = json_encode($katilan_ogrenciler); // JSON formatında kaydediyoruz
        }

        if ($baslatilma_tarihi) {
            $updateStmt .= "baslatilma_tarihi = :baslatilma_tarihi, ";
            $params[':baslatilma_tarihi'] = $baslatilma_tarihi;
        }

        if ($bitis_tarihi) {
            $updateStmt .= "bitis_tarihi = :bitis_tarihi, ";
            $params[':bitis_tarihi'] = $bitis_tarihi;
        }

        if ($aktiflik !== null) {
            $updateStmt .= "aktiflik = :aktiflik, ";
            $params[':aktiflik'] = $aktiflik;
        }

        // Kendi kodunun sonundaki virgülü kaldır
        $updateStmt = rtrim($updateStmt, ", ");

        // Yoklama ID'sini WHERE koşuluna ekle
        $updateStmt .= " WHERE yoklama_id = :yoklama_id";
        $params[':yoklama_id'] = $yoklama_id;

        // Güncellemeyi gerçekleştir
        $stmt = $pdo->prepare($updateStmt);
        $stmt->execute($params);

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Yoklama başarıyla güncellendi.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(200); // HTTP 200 OK
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Yoklamada bir değişiklik yapılmadı!'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400); // HTTP 400 Bad Request
        }

    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        http_response_code(500); // HTTP 500 Internal Server Error
    }
}


function deleteYoklamalar()
{
    global $pdo;

    try {
        // Parametreyi al
        $yoklama_id = $_GET['yoklama_id'] ?? null;

        // Eğer yoklama ID'si yoksa, body'den al
        if (!$yoklama_id) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            $yoklama_id = $data['yoklama_id'] ?? null;
        }

        // Hata mesajlarını tutacak dizi
        $errors = [];

        // Yoklama ID kontrolü
        if (empty($yoklama_id)) {
            $errors[] = 'Yoklama ID boş bırakılamaz.';
        }

        // Geçerli yoklama var mı kontrol et
        $checkYoklama = $pdo->prepare("SELECT * FROM yoklamalar WHERE yoklama_id = :yoklama_id");
        $checkYoklama->execute([':yoklama_id' => $yoklama_id]);
        if ($checkYoklama->rowCount() === 0) {
            $errors[] = 'Geçersiz Yoklama ID.';
        }

        // Eğer hata varsa, hata mesajlarını döndür
        if (!empty($errors)) {
            echo json_encode([
                'status' => 'error',
                'message' => $errors
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400); // HTTP 400 Bad Request
            return;
        }

        // Yoklamayı silme işlemi
        $stmt = $pdo->prepare("DELETE FROM yoklamalar WHERE yoklama_id = :yoklama_id");
        $stmt->execute([':yoklama_id' => $yoklama_id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Yoklama başarıyla silindi.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(200); // HTTP 200 OK
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Yoklama silinirken bir hata oluştu.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(500); // HTTP 500 Internal Server Error
        }

    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        http_response_code(500); // HTTP 500 Internal Server Error
    }
}
