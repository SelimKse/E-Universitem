<?php
header('Content-Type: application/json; charset=UTF-8'); // UTF-8 ile JSON başlığı ekliyoruz
require_once 'database.php';

$request = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($request, PHP_URL_PATH); // Sadece URL'nin path kısmını al
parse_str($_SERVER['QUERY_STRING'], $queryParams);

if ($requestPath == '/dersler') {
    if (checkApiKey($queryParams) === false) {
        return;
    }
    getDersler();
} else if ($requestPath == '/dersler/olustur') {
    if (checkApiKey($queryParams) === false) {
        return;
    }
    createDers();
} else if ($requestPath == '/dersler/guncelle') {
    if (checkApiKey($queryParams) === false) {
        return;
    }
    updateDers();
} else if ($requestPath == '/dersler/sil') {
    if (checkApiKey($queryParams) === false) {
        return;
    }
    deleteDers();
} else if ($requestPath == '/dersler/ogrenciler') {
    if (checkApiKey($queryParams) === false) {
        return;
    }
    getOgrenciler();
} else if ($requestPath == '/dersler/ogrenciler/ekle') {
    if (checkApiKey($queryParams) === false) {
        return;
    }
    addOgrenciler();
} else if ($requestPath == '/dersler/ogrenciler/guncelle') {
    if (checkApiKey($queryParams) === false) {
        return;
    }
    updateOgrenciler();
} else if ($requestPath == '/dersler/ogrenciler/sil') {
    if (checkApiKey($queryParams) === false) {
        return;
    }
    deleteOgrenciler();
} else if ($requestPath == '/dersler/ogretmenler') {
    if (checkApiKey($queryParams) === false) {
        return;
    }
    getTeacher();
} else if ($requestPath == '/dersler/ogretmenler/ekle') {
    if (checkApiKey($queryParams) === false) {
        return;
    }
    addTeacher();
} else if ($requestPath == '/dersler/ogretmenler/guncelle') {
    if (checkApiKey($queryParams) === false) {
        return;
    }
    updateTeacher();
} else if ($requestPath == '/dersler/ogretmenler/sil') {
    if (checkApiKey($queryParams) === false) {
        return;
    }
    deleteTeacher();
} else {
    header('HTTP/1.1 404 Not Found');
    echo json_encode([
        'status' => 'error',
        'message' => 'Geçersiz endpoint.'
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
            'message' => 'API Key eksik!'
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        header('HTTP/1.1 401 Unauthorized');
        return false;
    }

    // API anahtarını kontrol et
    if (!verifyApiKey($apiKey)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Geçersiz API Key!'
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        header('HTTP/1.1 401 Unauthorized');
        return false;
    }

    return true;
}

function getDersler()
{
    global $pdo;

    try {
        // GET parametrelerini al
        $ders_id = $_GET['ders_id'] ?? null;
        $ders_adi = $_GET['ders_adi'] ?? null;

        // Eğer GET parametreleri yoksa, body'den al
        if (!$ders_id && !$ders_adi) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            $ders_id = $data['ders_id'] ?? null;
            $ders_adi = $data['ders_adi'] ?? null;
        }

        // SQL sorgusunu başlat
        $sql = "SELECT * FROM dersler";
        $conditions = [];
        $parameters = [];

        // Yalnızca geçerli parametreleri işleme al
        if (!empty($ders_id)) {
            $conditions[] = "ders_id = :ders_id";
            $parameters[':ders_id'] = $ders_id;
        }

        if (!empty($ders_adi)) {
            $conditions[] = "ders_adi = :ders_adi";
            $parameters[':ders_adi'] = $ders_adi;
        }


        // Eğer herhangi bir koşul varsa WHERE ekle
        if ($conditions) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        // SQL sorgusunu hazırla ve çalıştır
        $stmt = $pdo->prepare($sql);
        $stmt->execute($parameters);

        // Sonuçları al
        $dersler = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($dersler)) {
            // Eğer sonuç boşsa hata mesajı döndür
            echo json_encode([
                'status' => 'error',
                'message' => 'Ders bulunamadı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            header('HTTP/1.1 404 Not Found');
        } else {
            // Sonuç varsa JSON formatında döndür
            echo json_encode([
                'status' => 'success',
                'data' => $dersler
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

function createDers()
{
    global $pdo;

    try {
        // GET parametrelerini al
        $ders_adi = $_GET['ders_adi'] ?? null;

        // Eğer GET parametreleri yoksa, body'den al
        if (!$ders_adi) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            $ders_adi = $data['ders_adi'] ?? null;
        }

        $errors = [];

        // Ders adı kontrolü
        if (empty($ders_adi)) {
            $errors[] = 'Ders adı boş bırakılamaz.';
        } elseif (!preg_match('/^[a-zA-ZÇĞİÖŞÜçğöşüı\s]+$/u', $ders_adi)) {
            $errors[] = 'Ders adı sadece yazılardan oluşmalı! Boşluk içerebilir.';
        }

        // Eğer hata varsa döndür
        if (!empty($errors)) {
            echo json_encode([
                'status' => 'error',
                'errors' => $errors
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400); // HTTP 400 Bad Request
            return;
        }

        // Veritabanına ekleme işlemi
        $stmt = $pdo->prepare("
            INSERT INTO dersler (ders_adi, olusturulma_tarihi)
            VALUES (:ders_adi, :olusturulma_tarihi)
        ");

        $stmt->execute([
            ':ders_adi' => $ders_adi,
            ':olusturulma_tarihi' => date('Y-m-d H:i:s')
        ]);

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Ders başarıyla eklendi.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(200); // HTTP 200 OK
            return;
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Ders eklenirken bir hata oluştu.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(500);
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

function updateDers()
{
    global $pdo;

    try {
        // GET parametrelerini al
        $ders_id = $_GET['ders_id'] ?? null;
        $ders_adi = $_GET['ders_adi'] ?? null;

        // Eğer GET parametreleri yoksa, body'den al
        if (!$ders_id || !$ders_adi) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            $ders_id = $data['ders_id'] ?? null;
            $ders_adi = $data['ders_adi'] ?? null;
        }

        $errors = [];

        // ID kontrolü
        if (empty($ders_id)) {
            $errors[] = 'Ders ID boş bırakılamaz.';
        } elseif (!ctype_digit($ders_id)) {
            $errors[] = 'Ders ID geçerli bir sayı olmalıdır.';
        }

        // Ders adı kontrolü
        if (empty($ders_adi)) {
            $errors[] = 'Ders adı boş bırakılamaz.';
        } elseif (!preg_match('/^[a-zA-ZÇĞİÖŞÜçğöşüı\s]+$/u', $ders_adi)) {
            $errors[] = 'Ders adı sadece yazılardan oluşmalı! Boşluk içerebilir.';
        }

        // Eğer hata varsa döndür
        if (!empty($errors)) {
            echo json_encode([
                'status' => 'error',
                'errors' => $errors
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400); // HTTP 400 Bad Request
            return;
        }

        // Veritabanında dersin varlığını kontrol et
        $checkStmt = $pdo->prepare("SELECT * FROM dersler WHERE ders_id = :ders_id");
        $checkStmt->execute([':ders_id' => $ders_id]);

        if ($checkStmt->rowCount() === 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Güncellenecek ders bulunamadı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(404); // HTTP 404 Not Found
            return;
        }

        // Veritabanında güncelleme işlemi
        $stmt = $pdo->prepare("
            UPDATE dersler
            SET ders_adi = :ders_adi
            WHERE ders_id = :ders_id
        ");

        $stmt->execute([
            ':ders_adi' => $ders_adi,
            ':ders_id' => $ders_id
        ]);

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Ders başarıyla güncellendi.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(200); // HTTP 200 OK
            return;
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Ders güncellenirken bir hata oluştu.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400); // HTTP 400 Bad Request
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



function deleteDers()
{
    global $pdo;

    try {
        // GET parametrelerini al
        $ders_id = $_GET['ders_id'] ?? null;

        // Eğer GET parametreleri yoksa, body'den al
        if (!$ders_id) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            $ders_id = $data['ders_id'] ?? null;
        }

        $errors = [];

        // ID kontrolü
        if (empty($ders_id)) {
            $errors[] = 'Ders ID boş bırakılamaz.';
        } elseif (!ctype_digit($ders_id)) {
            $errors[] = 'Ders ID geçerli bir sayı olmalıdır.';
        }

        // Eğer hata varsa döndür
        if (!empty($errors)) {
            echo json_encode([
                'status' => 'error',
                'errors' => $errors
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400); // HTTP 400 Bad Request
            return;
        }

        // Veritabanında dersin varlığını kontrol et
        $checkStmt = $pdo->prepare("SELECT * FROM dersler WHERE id = :ders_id");
        $checkStmt->execute([':ders_id' => $ders_id]);

        if ($checkStmt->rowCount() === 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Silinecek ders bulunamadı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(404); // HTTP 404 Not Found
            return;
        }

        // Veritabanında silme işlemi
        $stmt = $pdo->prepare("
            DELETE FROM dersler
            WHERE id = :ders_id
        ");

        $stmt->execute([
            ':ders_id' => $ders_id
        ]);

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Ders başarıyla silindi.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(200); // HTTP 200 OK
            return;
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Ders silinirken bir hata oluştu.'
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

function getOgrenciler()
{
    global $pdo;

    try {
        // GET parametrelerini al
        $ders_id = $_GET['ders_id'] ?? null;

        // Eğer GET parametreleri yoksa, body'den al
        if (!$ders_id) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            $ders_id = $data['ders_id'] ?? null;
        }

        $errors = [];

        // ID kontrolü
        if (empty($ders_id)) {
            $errors[] = 'Ders ID boş bırakılamaz.';
        } elseif (!ctype_digit($ders_id)) {
            $errors[] = 'Ders ID geçerli bir 9 haneli sayı olmalıdır.';
        }

        // Eğer hata varsa döndür
        if (!empty($errors)) {
            echo json_encode([
                'status' => 'error',
                'errors' => $errors
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400); // HTTP 400 Bad Request
            return;
        }

        $stmt = $pdo->prepare("SELECT ogrenciler FROM dersler WHERE ders_id = :ders_id");
        $stmt->execute([':ders_id' => $ders_id]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result || empty($result['ogrenciler'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Belirtilen ders ID\'si için öğrenci bulunamadı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(404); // HTTP 404 Not Found
            return;
        }

        // Öğrenci ID'lerini JSON olarak çöz
        $ogrenci_ids = json_decode($result['ogrenciler'], true);

        if (!is_array($ogrenci_ids) || empty($ogrenci_ids)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Geçersiz öğrenci listesi.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(500); // HTTP 500 Internal Server Error
            return;
        }

        // Öğrenci bilgilerini almak için sorgu hazırla
        $placeholders = implode(',', array_fill(0, count($ogrenci_ids), '?'));
        $stmt = $pdo->prepare("
            SELECT ogrenci_no, CONCAT(ogrenci_adi, ' ', ogrenci_soyadi) AS name
            FROM ogrenciler
            WHERE ders_id IN ($placeholders)
        ");
        $stmt->execute($ogrenci_ids);

        $ogrenciler = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($ogrenciler)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğrenci bilgileri bulunamadı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(404); // HTTP 404 Not Found
            return;
        }

        echo json_encode([
            'status' => 'success',
            'data' => $ogrenciler
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        http_response_code(200); // HTTP 200 OK

    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        http_response_code(500); // HTTP 500 Internal Server Error
    }
}


function addOgrenciler()
{
    global $pdo;

    try {
        // GET parametrelerini al
        $ders_id = $_GET['ders_id'] ?? null;
        $ogrenciler_input = $_GET['ogrenciler'] ?? null;

        // Eğer GET parametreleri yoksa, body'den al
        if (!$ders_id || !$ogrenciler_input) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            $ders_id = $data['ders_id'] ?? null;
            $ogrenciler_input = $data['ogrenciler'] ?? null;
        }

        // Hatalar listesi
        $errors = [];

        // Ders ID kontrolü
        if (empty($ders_id)) {
            $errors[] = 'Ders ID boş bırakılamaz.';
        } elseif (!ctype_digit($ders_id)) {
            $errors[] = 'Ders ID geçerli bir sayı olmalıdır.';
        }

        // Öğrenci listesi kontrolü
        if (empty($ogrenciler_input)) {
            $errors[] = 'Öğrenci listesi boş bırakılamaz.';
        }

        if (!empty($errors)) {
            echo json_encode([
                'status' => 'error',
                'errors' => $errors
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400); // HTTP 400 Bad Request
            return;
        }

        // Öğrenci ID'lerini temizle ve filtrele
        $ogrenci_ids = is_string($ogrenciler_input)
            ? array_map('trim', explode(',', $ogrenciler_input))
            : (is_array($ogrenciler_input) ? $ogrenciler_input : []);

        // Yalnızca rakamları al ve geçerli ID'leri filtrele
        $ogrenci_ids = array_filter(array_map(function ($id) {
            $id = preg_replace('/\D/', '', $id); // Sadece rakamları bırak
            return !empty($id) ? $id : null;
        }, $ogrenci_ids));

        if (empty($ogrenci_ids)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Geçersiz öğrenci listesi sağlandı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400); // HTTP 400 Bad Request
            return;
        }

        // Veritabanında öğrenci ID'lerini kontrol et
        $placeholders = implode(',', array_fill(0, count($ogrenci_ids), '?'));
        $stmt = $pdo->prepare("SELECT ogrenci_no FROM ogrenciler WHERE ogrenci_no IN ($placeholders)");
        $stmt->execute($ogrenci_ids);

        $valid_ids = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $valid_ids[] = (string) $row['ogrenci_no']; // Tüm ID'leri string olarak ekle
        }

        // Geçersiz öğrenci ID'lerini bul
        $invalid_ids = array_diff($ogrenci_ids, $valid_ids);

        if (!empty($invalid_ids)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Sistemde bulunamayan öğrenci numaraları: ' . implode(', ', $invalid_ids)
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(404); // HTTP 404 Not Found
            return;
        }

        // Mevcut dersin öğrencilerini kontrol et
        $stmt = $pdo->prepare("SELECT ogrenciler FROM dersler WHERE ders_id = ?");
        $stmt->execute([$ders_id]);
        $current_ders = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$current_ders) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Belirtilen Ders ID bulunamadı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(404); // HTTP 404 Not Found
            return;
        }

        $current_ogrenciler = json_decode($current_ders['ogrenciler'], true) ?? [];

        // Yeni öğrencileri belirle
        $new_students = array_diff($valid_ids, $current_ogrenciler);

        if (empty($new_students)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Yeni öğrenci eklenmedi.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(200); // HTTP 200 OK
            return;
        }

        // Yeni ve mevcut öğrencileri birleştir
        $updated_ogrenciler = array_unique(array_merge($current_ogrenciler, $new_students));

        // Öğrenci listesi JSON formatına dönüştürülerek saklanır
        $updated_ogrenciler_json = json_encode(array_map('strval', $updated_ogrenciler), JSON_UNESCAPED_UNICODE);

        // Veritabanını güncelle
        $stmt = $pdo->prepare("UPDATE dersler SET ogrenciler = :ogrenciler WHERE ders_id = :ders_id");
        $stmt->execute([
            ':ogrenciler' => $updated_ogrenciler_json,
            ':ders_id' => $ders_id
        ]);

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Ders öğrenci listesi başarıyla güncellendi.',
                'guncel_ogrenciler' => $updated_ogrenciler
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(200); // HTTP 200 OK
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Ders zaten güncel.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(200); // HTTP 200 OK
        }

    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        http_response_code(500); // HTTP 500 Internal Server Error
    }
}



function updateOgrenciler()
{
    global $pdo;

    try {
        // GET parametrelerini al
        $ders_id = $_GET['ders_id'] ?? null;
        $ogrenciler_input = $_GET['ogrenciler'] ?? null;

        // Eğer GET parametreleri yoksa, body'den al
        if (!$ders_id || !$ogrenciler_input) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            $ders_id = $data['ders_id'] ?? null;
            $ogrenciler_input = $data['ogrenciler'] ?? null;
        }

        // Hatalar listesi
        $errors = [];

        // Ders ID kontrolü
        if (empty($ders_id)) {
            $errors[] = 'Ders ID boş bırakılamaz.';
        } elseif (!ctype_digit($ders_id)) {
            $errors[] = 'Ders ID geçerli bir sayı olmalıdır.';
        }

        // Öğrenci listesi kontrolü
        if (empty($ogrenciler_input)) {
            $errors[] = 'Öğrenci listesi boş bırakılamaz.';
        }

        if (!empty($errors)) {
            echo json_encode([
                'status' => 'error',
                'errors' => $errors
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400); // HTTP 400 Bad Request
            return;
        }

        // Öğrenci ID'lerini temizle ve filtrele
        $ogrenci_ids = is_string($ogrenciler_input)
            ? array_map('trim', explode(',', $ogrenciler_input))
            : (is_array($ogrenciler_input) ? $ogrenciler_input : []);

        // Yalnızca rakamları al ve geçerli ID'leri filtrele
        $ogrenci_ids = array_filter(array_map(function ($id) {
            $id = preg_replace('/\D/', '', $id); // Sadece rakamları bırak
            return !empty($id) ? $id : null;
        }, $ogrenci_ids));

        if (empty($ogrenci_ids)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Geçersiz öğrenci listesi sağlandı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400); // HTTP 400 Bad Request
            return;
        }

        // Veritabanında öğrenci ID'lerini kontrol et
        $placeholders = implode(',', array_fill(0, count($ogrenci_ids), '?'));
        $stmt = $pdo->prepare("SELECT ogrenci_no FROM ogrenciler WHERE ogrenci_no IN ($placeholders)");
        $stmt->execute($ogrenci_ids);

        $valid_ids = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $valid_ids[] = (string) $row['ogrenci_no']; // Tüm ID'leri string olarak ekle
        }

        // Geçersiz öğrenci ID'lerini bul
        $invalid_ids = array_diff($ogrenci_ids, $valid_ids);

        if (!empty($invalid_ids)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Sistemde bulunamayan öğrenci numaraları: ' . implode(', ', $invalid_ids)
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(404); // HTTP 404 Not Found
            return;
        }

        // Mevcut dersin kontrolü
        $stmt = $pdo->prepare("SELECT ders_id FROM dersler WHERE ders_id = ?");
        $stmt->execute([$ders_id]);
        $current_ders = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$current_ders) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Belirtilen Ders ID bulunamadı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(404); // HTTP 404 Not Found
            return;
        }

        // Yeni öğrenci listesi JSON formatına dönüştürülür
        $updated_ogrenciler_json = json_encode($valid_ids, JSON_UNESCAPED_UNICODE);

        // Veritabanını güncelle
        $stmt = $pdo->prepare("UPDATE dersler SET ogrenciler = :ogrenciler WHERE ders_id = :ders_id");
        $stmt->execute([
            ':ogrenciler' => $updated_ogrenciler_json,
            ':ders_id' => $ders_id
        ]);

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Ders öğrenci listesi başarıyla güncellendi.',
                'guncel_ogrenciler' => $valid_ids
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(200); // HTTP 200 OK
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Ders zaten güncel.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(200); // HTTP 200 OK
        }

    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        http_response_code(500); // HTTP 500 Internal Server Error
    }
}


function deleteOgrenciler()
{
    global $pdo;

    try {
        // GET parametrelerini al
        $ders_id = $_GET['ders_id'] ?? null;
        $ogrenciler_input = $_GET['ogrenciler'] ?? null;

        // Eğer GET parametreleri yoksa, body'den al
        if (!$ders_id || !$ogrenciler_input) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            $ders_id = $data['ders_id'] ?? null;
            $ogrenciler_input = $data['ogrenciler'] ?? null;
        }

        // Hatalar listesi
        $errors = [];

        // Ders ID kontrolü
        if (empty($ders_id)) {
            $errors[] = 'Ders ID boş bırakılamaz.';
        } elseif (!ctype_digit($ders_id)) {
            $errors[] = 'Ders ID geçerli bir sayı olmalıdır.';
        }

        // Öğrenci listesi kontrolü
        if (empty($ogrenciler_input)) {
            $errors[] = 'Öğrenci listesi boş bırakılamaz.';
        }

        if (!empty($errors)) {
            echo json_encode([
                'status' => 'error',
                'errors' => $errors
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400); // HTTP 400 Bad Request
            return;
        }

        // Öğrenci ID'lerini temizle ve filtrele
        $ogrenci_ids = is_string($ogrenciler_input)
            ? array_map('trim', explode(',', $ogrenciler_input))
            : (is_array($ogrenciler_input) ? $ogrenciler_input : []);

        // Yalnızca rakamları al ve geçerli ID'leri filtrele
        $ogrenci_ids = array_filter(array_map(function ($id) {
            $id = preg_replace('/\D/', '', $id); // Sadece rakamları bırak
            return !empty($id) ? $id : null;
        }, $ogrenci_ids));

        if (empty($ogrenci_ids)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Geçersiz öğrenci listesi sağlandı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400); // HTTP 400 Bad Request
            return;
        }

        // Veritabanında öğrenci ID'lerini kontrol et
        $placeholders = implode(',', array_fill(0, count($ogrenci_ids), '?'));
        $stmt = $pdo->prepare("SELECT ogrenci_no FROM ogrenciler WHERE ogrenci_no IN ($placeholders)");
        $stmt->execute($ogrenci_ids);

        $valid_ids = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $valid_ids[] = (string) $row['ogrenci_no']; // Tüm ID'leri string olarak ekle
        }

        // Geçersiz öğrenci ID'lerini bul
        $invalid_ids = array_diff($ogrenci_ids, $valid_ids);

        if (!empty($invalid_ids)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Sistemde bulunamayan öğrenci numaraları: ' . implode(', ', $invalid_ids)
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(404); // HTTP 404 Not Found
            return;
        }

        // Mevcut dersin öğrencilerini kontrol et
        $stmt = $pdo->prepare("SELECT ogrenciler FROM dersler WHERE ders_id = ?");
        $stmt->execute([$ders_id]);
        $current_ders = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$current_ders) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Belirtilen Ders ID bulunamadı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(404); // HTTP 404 Not Found
            return;
        }

        // Mevcut öğrenci listesini al
        $current_ogrenciler = json_decode($current_ders['ogrenciler'], true) ?? [];

        // Silinecek öğrenciler
        $updated_ogrenciler = array_diff($current_ogrenciler, $valid_ids);

        if (count($updated_ogrenciler) === count($current_ogrenciler)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Silinecek öğrenci bulunamadı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(200); // HTTP 200 OK
            return;
        }

        // Eğer öğrenci listesi boşsa, null olarak kaydet
        $updated_ogrenciler_json = empty($updated_ogrenciler) ? NULL : json_encode(array_values($updated_ogrenciler), JSON_UNESCAPED_UNICODE);

        // Veritabanını güncelle
        $stmt = $pdo->prepare("UPDATE dersler SET ogrenciler = :ogrenciler WHERE ders_id = :ders_id");
        $stmt->execute([
            ':ogrenciler' => $updated_ogrenciler_json,
            ':ders_id' => $ders_id
        ]);

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Ders öğrenci listesi başarıyla güncellendi.',
                'guncel_ogrenciler' => $updated_ogrenciler
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(200); // HTTP 200 OK
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Ders zaten güncel.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(200); // HTTP 200 OK
        }

    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        http_response_code(500); // HTTP 500 Internal Server Error
    }
}



function getTeacher()
{
    global $pdo;

    try {
        // GET parametrelerini al
        $ders_id = $_GET['ders_id'] ?? null;

        // Eğer GET parametreleri yoksa, body'den al
        if (!$ders_id) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            $ders_id = $data['ders_id'] ?? null;
        }

        $errors = [];

        // Ders ID kontrolü
        if (empty($ders_id)) {
            $errors[] = 'Ders ID boş bırakılamaz.';
        } elseif (!ctype_digit($ders_id)) {
            $errors[] = 'Ders ID geçerli bir sayı olmalıdır.';
        }

        // Eğer hata varsa döndür
        if (!empty($errors)) {
            echo json_encode([
                'status' => 'error',
                'errors' => $errors
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400); // HTTP 400 Bad Request
            return;
        }

        // Dersin öğretmenlerini almak için sorgu
        $stmt = $pdo->prepare("SELECT ogretmenler FROM dersler WHERE ders_id = :ders_id");
        $stmt->execute([':ders_id' => $ders_id]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result || empty($result['ogretmenler'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Belirtilen ders ID\'si için öğretmen bulunamadı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(404); // HTTP 404 Not Found
            return;
        }

        // Öğretmen ID'lerini JSON olarak çöz
        $ogretmen_ids = json_decode($result['ogretmenler'], true);

        if (!is_array($ogretmen_ids) || empty($ogretmen_ids)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Geçersiz öğretmen listesi.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(500); // HTTP 500 Internal Server Error
            return;
        }

        // Öğretmen bilgilerini almak için sorgu hazırla
        $placeholders = implode(',', array_fill(0, count($ogretmen_ids), '?'));
        $stmt = $pdo->prepare("
            SELECT ogretmen_no, CONCAT(ogretmen_adi, ' ', ogretmen_soyadi) AS name
            FROM ogretmenler
            WHERE ogretmen_no IN ($placeholders)
        ");
        $stmt->execute($ogretmen_ids);

        $ogretmenler = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($ogretmenler)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğretmen bilgileri bulunamadı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(404); // HTTP 404 Not Found
            return;
        }

        echo json_encode([
            'status' => 'success',
            'data' => $ogretmenler
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        http_response_code(200); // HTTP 200 OK

    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        http_response_code(500); // HTTP 500 Internal Server Error
    }
}

function addTeacher()
{
    global $pdo;

    try {
        // GET parametrelerini al
        $ders_id = $_GET['ders_id'] ?? null;
        $ogretmenler_input = $_GET['ogretmenler'] ?? null;

        // Eğer GET parametreleri yoksa, body'den al
        if (!$ders_id || !$ogretmenler_input) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            $ders_id = $data['ders_id'] ?? null;
            $ogretmenler_input = $data['ogretmenler'] ?? null;
        }

        // Hatalar listesi
        $errors = [];

        // Ders ID kontrolü
        if (empty($ders_id)) {
            $errors[] = 'Ders ID boş bırakılamaz.';
        } elseif (!ctype_digit($ders_id)) {
            $errors[] = 'Ders ID geçerli bir sayı olmalıdır.';
        }

        // Öğretmen listesi kontrolü
        if (empty($ogretmenler_input)) {
            $errors[] = 'Öğretmen listesi boş bırakılamaz.';
        }

        if (!empty($errors)) {
            echo json_encode([
                'status' => 'error',
                'errors' => $errors
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400); // HTTP 400 Bad Request
            return;
        }

        // Öğretmen ID'lerini temizle ve filtrele
        $ogretmen_ids = is_string($ogretmenler_input)
            ? array_map('trim', explode(',', $ogretmenler_input))
            : (is_array($ogretmenler_input) ? $ogretmenler_input : []);

        // Yalnızca rakamları al ve geçerli ID'leri filtrele
        $ogretmen_ids = array_filter(array_map(function ($id) {
            $id = preg_replace('/\D/', '', $id); // Sadece rakamları bırak
            return !empty($id) ? $id : null;
        }, $ogretmen_ids));

        if (empty($ogretmen_ids)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Geçersiz öğretmen listesi sağlandı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400); // HTTP 400 Bad Request
            return;
        }

        // Veritabanında öğretmen ID'lerini kontrol et
        $placeholders = implode(',', array_fill(0, count($ogretmen_ids), '?'));
        $stmt = $pdo->prepare("SELECT ogretmen_no FROM ogretmenler WHERE ogretmen_no IN ($placeholders)");
        $stmt->execute($ogretmen_ids);

        $valid_ids = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $valid_ids[] = (string) $row['ogretmen_no']; // Tüm ID'leri string olarak ekle
        }

        // Geçersiz öğretmen ID'lerini bul
        $invalid_ids = array_diff($ogretmen_ids, $valid_ids);

        if (!empty($invalid_ids)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Sistemde bulunamayan öğretmen numaraları: ' . implode(', ', $invalid_ids)
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(404); // HTTP 404 Not Found
            return;
        }

        // Mevcut dersin öğretmenlerini kontrol et
        $stmt = $pdo->prepare("SELECT ogretmenler FROM dersler WHERE ders_id = ?");
        $stmt->execute([$ders_id]);
        $current_ders = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$current_ders) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Belirtilen Ders ID bulunamadı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(404); // HTTP 404 Not Found
            return;
        }

        $current_ogretmenler = json_decode($current_ders['ogretmenler'], true) ?? [];

        // Yeni öğretmenleri belirle
        $new_teachers = array_diff($valid_ids, $current_ogretmenler);

        if (empty($new_teachers)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Yeni öğretmen eklenmedi.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(200); // HTTP 200 OK
            return;
        }

        // Yeni ve mevcut öğretmenleri birleştir
        $updated_ogretmenler = array_unique(array_merge($current_ogretmenler, $new_teachers));

        // Öğretmen listesi JSON formatına dönüştürülerek saklanır
        $updated_ogretmenler_json = json_encode(array_map('strval', $updated_ogretmenler), JSON_UNESCAPED_UNICODE);

        // Veritabanını güncelle
        $stmt = $pdo->prepare("UPDATE dersler SET ogretmenler = :ogretmenler WHERE ders_id = :ders_id");
        $stmt->execute([
            ':ogretmenler' => $updated_ogretmenler_json,
            ':ders_id' => $ders_id
        ]);

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Ders öğretmen listesi başarıyla güncellendi.',
                'guncel_ogretmenler' => $updated_ogretmenler
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(200); // HTTP 200 OK
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Ders zaten güncel.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(200); // HTTP 200 OK
        }

    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        http_response_code(500); // HTTP 500 Internal Server Error
    }
}


function updateTeacher()
{
    global $pdo;

    try {
        // GET parametrelerini al
        $ders_id = $_GET['ders_id'] ?? null;
        $ogretmenler_input = $_GET['ogretmenler'] ?? null;

        // Eğer GET parametreleri yoksa, body'den al
        if (!$ders_id || !$ogretmenler_input) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            $ders_id = $data['ders_id'] ?? null;
            $ogretmenler_input = $data['ogretmenler'] ?? null;
        }

        // Hatalar listesi
        $errors = [];

        // Ders ID kontrolü
        if (empty($ders_id)) {
            $errors[] = 'Ders ID boş bırakılamaz.';
        } elseif (!ctype_digit($ders_id)) {
            $errors[] = 'Ders ID geçerli bir sayı olmalıdır.';
        }

        // Öğretmen listesi kontrolü
        if (empty($ogretmenler_input)) {
            $errors[] = 'Öğretmen listesi boş bırakılamaz.';
        }

        if (!empty($errors)) {
            echo json_encode([
                'status' => 'error',
                'errors' => $errors
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400); // HTTP 400 Bad Request
            return;
        }

        // Öğretmen ID'lerini temizle ve filtrele
        $ogretmen_ids = is_string($ogretmenler_input)
            ? array_map('trim', explode(',', $ogretmenler_input))
            : (is_array($ogretmenler_input) ? $ogretmenler_input : []);

        // Yalnızca rakamları al ve geçerli ID'leri filtrele
        $ogretmen_ids = array_filter(array_map(function ($id) {
            $id = preg_replace('/\D/', '', $id); // Sadece rakamları bırak
            return !empty($id) ? $id : null;
        }, $ogretmen_ids));

        if (empty($ogretmen_ids)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Geçersiz öğretmen listesi sağlandı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400); // HTTP 400 Bad Request
            return;
        }

        // Veritabanında öğretmen ID'lerini kontrol et
        $placeholders = implode(',', array_fill(0, count($ogretmen_ids), '?'));
        $stmt = $pdo->prepare("SELECT ogretmen_no FROM ogretmenler WHERE ogretmen_no IN ($placeholders)");
        $stmt->execute($ogretmen_ids);

        $valid_ids = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $valid_ids[] = (string) $row['ogretmen_no']; // Tüm ID'leri string olarak ekle
        }

        // Geçersiz öğretmen ID'lerini bul
        $invalid_ids = array_diff($ogretmen_ids, $valid_ids);

        if (!empty($invalid_ids)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Sistemde bulunamayan öğretmen numaraları: ' . implode(', ', $invalid_ids)
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(404); // HTTP 404 Not Found
            return;
        }

        // Mevcut dersin kontrolü
        $stmt = $pdo->prepare("SELECT ders_id FROM dersler WHERE ders_id = ?");
        $stmt->execute([$ders_id]);
        $current_ders = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$current_ders) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Belirtilen Ders ID bulunamadı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(404); // HTTP 404 Not Found
            return;
        }

        // Yeni öğretmen listesi JSON formatına dönüştürülür
        $updated_ogretmenler_json = json_encode($valid_ids, JSON_UNESCAPED_UNICODE);

        // Veritabanını güncelle
        $stmt = $pdo->prepare("UPDATE dersler SET ogretmenler = :ogretmenler WHERE ders_id = :ders_id");
        $stmt->execute([
            ':ogretmenler' => $updated_ogretmenler_json,
            ':ders_id' => $ders_id
        ]);

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Ders öğretmen listesi başarıyla güncellendi.',
                'guncel_ogretmenler' => $valid_ids
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(200); // HTTP 200 OK
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Ders zaten güncel.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(200); // HTTP 200 OK
        }

    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        http_response_code(500); // HTTP 500 Internal Server Error
    }
}

function deleteTeacher()
{
    global $pdo;

    try {
        // GET parametrelerini al
        $ders_id = $_GET['ders_id'] ?? null;
        $ogretmenler_input = $_GET['ogretmenler'] ?? null;

        // Eğer GET parametreleri yoksa, body'den al
        if (!$ders_id || !$ogretmenler_input) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            $ders_id = $data['ders_id'] ?? null;
            $ogretmenler_input = $data['ogretmenler'] ?? null;
        }

        // Hatalar listesi
        $errors = [];

        // Ders ID kontrolü
        if (empty($ders_id)) {
            $errors[] = 'Ders ID boş bırakılamaz.';
        } elseif (!ctype_digit($ders_id)) {
            $errors[] = 'Ders ID geçerli bir sayı olmalıdır.';
        }

        // Öğretmen listesi kontrolü
        if (empty($ogretmenler_input)) {
            $errors[] = 'Öğretmen listesi boş bırakılamaz.';
        }

        if (!empty($errors)) {
            echo json_encode([
                'status' => 'error',
                'errors' => $errors
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400); // HTTP 400 Bad Request
            return;
        }

        // Öğretmen ID'lerini temizle ve filtrele
        $ogretmen_ids = is_string($ogretmenler_input)
            ? array_map('trim', explode(',', $ogretmenler_input))
            : (is_array($ogretmenler_input) ? $ogretmenler_input : []);

        // Yalnızca rakamları al ve geçerli ID'leri filtrele
        $ogretmen_ids = array_filter(array_map(function ($id) {
            $id = preg_replace('/\D/', '', $id); // Sadece rakamları bırak
            return !empty($id) ? $id : null;
        }, $ogretmen_ids));

        if (empty($ogretmen_ids)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Geçersiz öğretmen listesi sağlandı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400); // HTTP 400 Bad Request
            return;
        }

        // Veritabanında öğretmen ID'lerini kontrol et
        $placeholders = implode(',', array_fill(0, count($ogretmen_ids), '?'));
        $stmt = $pdo->prepare("SELECT ogretmen_no FROM ogretmenler WHERE ogretmen_no IN ($placeholders)");
        $stmt->execute($ogretmen_ids);

        $valid_ids = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $valid_ids[] = (string) $row['ogretmen_no']; // Tüm ID'leri string olarak ekle
        }

        // Geçersiz öğretmen ID'lerini bul
        $invalid_ids = array_diff($ogretmen_ids, $valid_ids);

        if (!empty($invalid_ids)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Sistemde bulunamayan öğretmen numaraları: ' . implode(', ', $invalid_ids)
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(404); // HTTP 404 Not Found
            return;
        }

        // Mevcut dersin öğretmenlerini kontrol et
        $stmt = $pdo->prepare("SELECT ogretmenler FROM dersler WHERE ders_id = ?");
        $stmt->execute([$ders_id]);
        $current_ders = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$current_ders) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Belirtilen Ders ID bulunamadı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(404); // HTTP 404 Not Found
            return;
        }

        // Mevcut öğretmen listesini al
        $current_ogretmenler = json_decode($current_ders['ogretmenler'], true) ?? [];

        // Silinecek öğretmenleri
        $updated_ogretmenler = array_diff($current_ogretmenler, $valid_ids);

        if (count($updated_ogretmenler) === count($current_ogretmenler)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Silinecek öğretmen bulunamadı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(200); // HTTP 200 OK
            return;
        }

        // Eğer öğretmen listesi boşsa, null olarak kaydet
        $updated_ogretmenler_json = empty($updated_ogretmenler) ? NULL : json_encode(array_values($updated_ogretmenler), JSON_UNESCAPED_UNICODE);

        // Veritabanını güncelle
        $stmt = $pdo->prepare("UPDATE dersler SET ogretmenler = :ogretmenler WHERE ders_id = :ders_id");
        $stmt->execute([
            ':ogretmenler' => $updated_ogretmenler_json,
            ':ders_id' => $ders_id
        ]);

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Ders öğretmen listesi başarıyla güncellendi.',
                'guncel_ogretmenler' => $updated_ogretmenler
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(200); // HTTP 200 OK
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Ders zaten güncel.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(200); // HTTP 200 OK
        }

    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        http_response_code(500); // HTTP 500 Internal Server Error
    }
}
