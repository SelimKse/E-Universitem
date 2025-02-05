<?php
header('Content-Type: application/json; charset=UTF-8'); // UTF-8 ile JSON başlığı ekliyoruz
require_once 'database.php';

$request = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($request, PHP_URL_PATH); // Sadece URL'nin path kısmını al
parse_str($_SERVER['QUERY_STRING'], $queryParams);

if ($requestPath == '/login/ogrenci/giris') {
    if (checkApiKey($queryParams) === false) {
        return;
    }
    studentLogin();
} else if ($requestPath == '/login/ogretmen/giris') {
    if (checkApiKey($queryParams) === false) {
        return;
    }
    teacherLogin();
} else {
    header('HTTP/1.1 404 Not Found');
    echo json_encode([
        'status' => 'error',
        'message' => 'Geçersiz endpoint'
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

function studentLogin()
{
    global $pdo;

    // Sadece POST isteklerini kabul et
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('HTTP/1.1 405 Method Not Allowed');
        echo json_encode([
            'status' => 'error',
            'message' => 'Sadece POST istekleri kabul edilir.'
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        return;
    }

    // Giriş verilerini al
    $data = json_decode(file_get_contents('php://input'), true);

    // Öğrenci numarası ve şifre kontrolü
    if (empty($data['ogrenci_no']) || empty($data['ogrenci_sifre'])) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode([
            'status' => 'error',
            'message' => 'Öğrenci numarası ve şifre boş olamaz.'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return;
    }

    // Öğrenci verisini al
    $stmt = $pdo->prepare("SELECT * FROM ogrenciler WHERE ogrenci_no = :ogrenci_no");
    $stmt->execute([':ogrenci_no' => $data['ogrenci_no']]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    // Öğrenci numarası bulunamadı veya şifre yanlış
    if ($student === false || !password_verify($data['ogrenci_sifre'], $student['ogrenci_sifre'])) {
        header('HTTP/1.1 401 Unauthorized');
        echo json_encode([
            'status' => 'error',
            'message' => 'Öğrenci numarası veya şifre hatalı.'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return;
    }

    // Başarılı giriş
    echo json_encode([
        'status' => 'success',
        'data' => $student
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}


function teacherLogin()
{
    global $pdo;

    // Sadece POST isteklerini kabul et
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('HTTP/1.1 405 Method Not Allowed');
        echo json_encode([
            'status' => 'error',
            'message' => 'Sadece POST istekleri kabul edilir.'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return;
    }

    // Giriş verilerini al
    $data = json_decode(file_get_contents('php://input'), true);

    // Kullanıcı adı (eposta) ve şifre kontrolü
    if (empty($data['ogretmen_eposta']) || empty($data['ogretmen_sifre'])) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode([
            'status' => 'error',
            'message' => 'Kullanıcı adı ve şifre boş olamaz.'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return;
    }

    // Öğretmeni veritabanından al
    $stmt = $pdo->prepare("SELECT * FROM ogretmenler WHERE ogretmen_eposta = :ogretmen_eposta");
    $stmt->execute([':ogretmen_eposta' => $data['ogretmen_eposta']]);
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);

    // Eğer öğretmen bulunamazsa veya şifre yanlışsa
    if ($teacher === false || !password_verify($data['ogretmen_sifre'], $teacher['ogretmen_sifre'])) {
        header('HTTP/1.1 401 Unauthorized');
        echo json_encode([
            'status' => 'error',
            'message' => 'Kullanıcı adı veya şifre hatalı.'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return;
    }

    // Başarılı giriş
    echo json_encode([
        'status' => 'success',
        'data' => $teacher
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

