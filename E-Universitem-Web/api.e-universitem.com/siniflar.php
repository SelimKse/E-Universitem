<?php
header('Content-Type: application/json; charset=UTF-8'); // UTF-8 ile JSON başlığı ekliyoruz
require_once 'database.php';

$request = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($request, PHP_URL_PATH); // Sadece URL'nin path kısmını al
parse_str($_SERVER['QUERY_STRING'], $queryParams);

if ($requestPath == '/siniflar') {
    if (checkApiKey($queryParams) === false) {
        return;
    }
    getClass();
} else if ($requestPath == '/siniflar/olustur') {
    if (checkApiKey($queryParams) === false) {
        return;
    }
    createClass();
} else if ($requestPath == '/siniflar/guncelle') {
    if (checkApiKey($queryParams) === false) {
        return;
    }
    updateClass();
} else if ($requestPath == '/siniflar/sil') {
    if (checkApiKey($queryParams) === false) {
        return;
    }
    deleteClass();
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
            'message' => 'API Key eksik'
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        header('HTTP/1.1 401 Unauthorized');
        return false;
    }

    // API anahtarını kontrol et
    if (!verifyApiKey($apiKey)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Geçersiz API Key'
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        header('HTTP/1.1 401 Unauthorized');
        return false;
    }

    return true;
}

function getClass()
{
    global $pdo;

    try {
        // GET parametrelerini al
        $sinif_id = $_GET['sinif_id'] ?? null;
        $sinif_adi = $_GET['sinif_adi'] ?? null;

        // Eğer GET parametreleri yoksa, body'den al
        if (!$sinif_id && !$sinif_adi) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            $sinif_id = $data['sinif_id'] ?? null;
            $sinif_adi = $data['sinif_adi'] ?? null;
        }

        // SQL sorgusunu başlat
        $sql = "SELECT * FROM siniflar";
        $conditions = [];
        $parameters = [];

        // Yalnızca geçerli parametreleri işleme al
        if (!empty($sinif_id)) {
            $conditions[] = "sinif_id = :sinif_id";
            $parameters[':sinif_id'] = $sinif_id;
        }

        if (!empty($sinif_adi)) {
            $conditions[] = "sinif_adi = :sinif_adi";
            $parameters[':sinif_adi'] = $sinif_adi;
        }

        // Eğer herhangi bir koşul varsa WHERE ekle
        if ($conditions) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        // SQL sorgusunu hazırla ve çalıştır
        $stmt = $pdo->prepare($sql);
        $stmt->execute($parameters);

        // Sonuçları al
        $siniflar = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($siniflar)) {
            // Eğer sonuç boşsa hata mesajı döndür
            echo json_encode([
                'status' => 'error',
                'message' => 'Sınıf bulunamadı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            header('HTTP/1.1 404 Not Found');
        } else {
            // Sonuç varsa JSON formatında döndür
            echo json_encode([
                'status' => 'success',
                'data' => $siniflar
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


function createClass()
{
    global $pdo;

    try {
        // GET parametrelerini al
        $sinif_adi = $_GET['sinif_adi'] ?? null;

        // Eğer GET parametreleri yoksa, body'den al
        if (!$sinif_adi) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            $sinif_adi = $data['sinif_adi'] ?? null;
        }

        $errors = [];

        // Sınıf adı kontrolü
        if (empty($sinif_adi)) {
            $errors[] = 'Sınıf adı boş bırakılamaz.';
        } elseif (!preg_match('/^[a-zA-ZÇĞİÖŞÜçğöşüı\s]+$/u', $sinif_adi)) {
            $errors[] = 'Sınıf adı sadece yazılardan oluşmalı! Boşluk içerebilir.';
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
            INSERT INTO siniflar (sinif_adi, olusturulma_tarihi)
            VALUES (:sinif_adi, :olusturulma_tarihi)
        ");

        $stmt->execute([
            ':sinif_adi' => $sinif_adi,
            ':olusturulma_tarihi' => date('Y-m-d H:i:s')
        ]);

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Sınıf başarıyla eklendi.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(200); // HTTP 200 OK
            return;
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Sınıf eklenirken bir hata oluştu.'
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

function updateClass()
{
    global $pdo;

    try {
        // GET parametrelerini al
        $sinif_id = $_GET['sinif_id'] ?? null;
        $sinif_adi = $_GET['sinif_adi'] ?? null;

        // Eğer GET parametreleri yoksa, body'den al
        if (!$sinif_id || !$sinif_adi) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            $sinif_id = $data['sinif_id'] ?? null;
            $sinif_adi = $data['sinif_adi'] ?? null;
        }

        $errors = [];

        // ID kontrolü
        if (empty($sinif_id)) {
            $errors[] = 'Sınıf ID boş bırakılamaz.';
        } elseif (!ctype_digit($sinif_id)) {
            $errors[] = 'Sınıf ID geçerli bir sayı olmalıdır.';
        }

        // Sınıf adı kontrolü
        if (empty($sinif_adi)) {
            $errors[] = 'Sınıf adı boş bırakılamaz.';
        } elseif (!preg_match('/^[a-zA-ZÇĞİÖŞÜçğöşüı\s]+$/u', $sinif_adi)) {
            $errors[] = 'Sınıf adı sadece yazılardan oluşmalı! Boşluk içerebilir.';
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

        // Veritabanında sınıfın varlığını kontrol et
        $checkStmt = $pdo->prepare("SELECT * FROM siniflar WHERE sinif_id = :sinif_id");
        $checkStmt->execute([':sinif_id' => $sinif_id]);

        if ($checkStmt->rowCount() === 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Güncellenecek sınıf bulunamadı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(404); // HTTP 404 Not Found
            return;
        }

        // Veritabanında güncelleme işlemi
        $stmt = $pdo->prepare("
            UPDATE siniflar
            SET sinif_adi = :sinif_adi
            WHERE sinif_id = :sinif_id
        ");

        $stmt->execute([
            ':sinif_adi' => $sinif_adi,
            ':sinif_id' => $sinif_id
        ]);

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Sınıf başarıyla güncellendi.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(200); // HTTP 200 OK
            return;
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Sınıf güncellenirken bir hata oluştu.'
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

function deleteClass()
{
    global $pdo;

    try {
        // GET parametrelerini al
        $sinif_id = $_GET['sinif_id'] ?? null;

        // Eğer GET parametreleri yoksa, body'den al
        if (!$sinif_id) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            $sinif_id = $data['sinif_id'] ?? null;
        }

        $errors = [];

        // ID kontrolü
        if (empty($sinif_id)) {
            $errors[] = 'Sınıf ID boş bırakılamaz.';
        } elseif (!ctype_digit($sinif_id)) {
            $errors[] = 'Sınıf ID geçerli bir sayı olmalıdır.';
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

        // Veritabanında sınıfın varlığını kontrol et
        $checkStmt = $pdo->prepare("SELECT * FROM siniflar WHERE sinif_id = :sinif_id");
        $checkStmt->execute([':sinif_id' => $sinif_id]);

        if ($checkStmt->rowCount() === 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Silinecek sınıf bulunamadı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(404); // HTTP 404 Not Found
            return;
        }

        // Veritabanında silme işlemi
        $stmt = $pdo->prepare("
            DELETE FROM siniflar
            WHERE sinif_id = :sinif_id
        ");

        $stmt->execute([
            ':sinif_id' => $sinif_id
        ]);

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Sınıf başarıyla silindi.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(200); // HTTP 200 OK
            return;
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Sınıf silinirken bir hata oluştu.'
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

