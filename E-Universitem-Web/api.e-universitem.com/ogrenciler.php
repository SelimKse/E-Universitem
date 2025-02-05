<?php
header('Content-Type: application/json; charset=UTF-8'); // UTF-8 ile JSON başlığı ekliyoruz
require_once 'database.php';

$request = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($request, PHP_URL_PATH); // Sadece URL'nin path kısmını al
parse_str($_SERVER['QUERY_STRING'], $queryParams);

// API isteğine göre işlem yap
if ($requestPath === '/ogrenciler') { // Eğer /users endpoint'i kullanıldıysa
    if (checkApiKey($queryParams) === false) {
        return;
    }

    getStudent(); // Kullanıcıları getir
} else if ($requestPath === '/ogrenciler/olustur') { // Eğer /users endpoint'i kullanıldıysa
    if (checkApiKey($queryParams) === false) {
        return;
    }

    createStudent(); // Öğrenci ekleme fonksiyonunu çağır
} else if ($requestPath === '/ogrenciler/guncelle') { // Eğer /users endpoint'i kullanıldıysa
    if (checkApiKey($queryParams) === false) {
        return;
    }

    updateStudent(); // Öğrenci güncelleme fonksiyonunu çağır
} else if ($requestPath === '/ogrenciler/sil') { // Eğer /users endpoint'i kullanıldıysa 
    if (checkApiKey($queryParams) === false) {
        return;
    }

    deleteStudent(); // Öğrenci silme fonksiyonunu çağır
} else if ($requestPath === '/ogrenciler/dersler') { // Eğer /users endpoint'i kullanıldıysa
    if (checkApiKey($queryParams) === false) {
        return;
    }

    getStudentCourses(); // Öğrenci derslerini getir
} else if ($requestPath === '/ogrenciler/dersler/ekle') {
    if (checkApiKey($queryParams) === false) {
        return;
    }

    addStudentCourse(); // Öğrenci ders ekleme fonksiyonunu çağır
} else if ($requestPath === "/ogrenciler/dersler/guncelle") {
    if (checkApiKey($queryParams) === false) {
        return;
    }

    updateStudentCourse(); // Öğrenci ders güncelleme fonksiyonunu çağır
} else if ($requestPath === "/ogrenciler/dersler/sil") {
    if (checkApiKey($queryParams) === false) {
        return;
    }

    deleteStudentCourse(); // Öğrenci ders silme fonksiyonunu çağır
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

function getStudent()
{
    global $pdo;

    try {
        // GET parametrelerini al
        $ogrenci_no = $_GET['ogrenci_no'] ?? null;
        $ogrenci_adi = $_GET['ogrenci_adi'] ?? null;
        $ogrenci_soyadi = $_GET['ogrenci_soyadi'] ?? null;
        $ogrenci_eposta = $_GET['ogrenci_eposta'] ?? null;
        $ogrenci_telefon = $_GET['ogrenci_telefon'] ?? null;

        // Eğer GET parametreleri yoksa, body'den al
        if (!$ogrenci_no && !$ogrenci_adi && !$ogrenci_soyadi && !$ogrenci_eposta && !$ogrenci_telefon) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            $ogrenci_no = $data['ogrenci_no'] ?? null;
            $ogrenci_adi = $data['ogrenci_adi'] ?? null;
            $ogrenci_soyadi = $data['ogrenci_soyadi'] ?? null;
            $ogrenci_eposta = $data['ogrenci_eposta'] ?? null;
            $ogrenci_telefon = $data['ogrenci_telefon'] ?? null;
        }

        // SQL sorgusunu başlat
        $sql = "SELECT * FROM ogrenciler";
        $conditions = [];
        $parameters = [];

        // Yalnızca geçerli parametreleri işleme al
        if (!empty($ogrenci_no)) {
            $conditions[] = "ogrenci_no = :ogrenci_no";
            $parameters[':ogrenci_no'] = $ogrenci_no;
        }

        if (!empty($ogrenci_adi)) {
            $conditions[] = "ogrenci_adi = :ogrenci_adi";
            $parameters[':ogrenci_adi'] = $ogrenci_adi;
        }

        if (!empty($ogrenci_soyadi)) {
            $conditions[] = "ogrenci_soyadi = :ogrenci_soyadi";
            $parameters[':ogrenci_soyadi'] = $ogrenci_soyadi;
        }

        if (!empty($ogrenci_eposta)) {
            $conditions[] = "ogrenci_eposta = :ogrenci_eposta";
            $parameters[':ogrenci_eposta'] = $ogrenci_eposta;
        }

        if (!empty($ogrenci_telefon)) {
            $conditions[] = "ogrenci_telefon = :ogrenci_telefon";
            $parameters[':ogrenci_telefon'] = $ogrenci_telefon;
        }

        // Eğer herhangi bir koşul varsa WHERE ekle
        if ($conditions) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        // SQL sorgusunu hazırla ve çalıştır
        $stmt = $pdo->prepare($sql);
        $stmt->execute($parameters);

        // Sonuçları al
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($users)) {
            // Eğer sonuç boşsa hata mesajı döndür
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğrenci bulunamadı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            header('HTTP/1.1 404 Not Found');
        } else {
            // Sonuç varsa JSON formatında döndür
            echo json_encode([
                'status' => 'success',
                'data' => $users
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

function createStudent()
{
    global $pdo;

    try {
        // Query parametreleri al
        $ogrenci_no = $_GET['ogrenci_no'] ?? null;
        $ogrenci_adi = $_GET['ogrenci_adi'] ?? null;
        $ogrenci_soyadi = $_GET['ogrenci_soyadi'] ?? null;
        $ogrenci_eposta = $_GET['ogrenci_eposta'] ?? null;
        $ogrenci_sifre = $_GET['ogrenci_sifre'] ?? null;
        $ogrenci_telefon = $_GET['ogrenci_telefon'] ?? null;

        // Eğer query parametreleri yoksa, body'den al
        if (!$ogrenci_no || !$ogrenci_adi || !$ogrenci_soyadi || !$ogrenci_eposta || !$ogrenci_sifre || !$ogrenci_telefon) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            // Body'den gelen veriler
            $ogrenci_no = $ogrenci_no ?? $data['ogrenci_no'] ?? null;
            $ogrenci_adi = $ogrenci_adi ?? $data['ogrenci_adi'] ?? null;
            $ogrenci_soyadi = $ogrenci_soyadi ?? $data['ogrenci_soyadi'] ?? null;
            $ogrenci_eposta = $ogrenci_eposta ?? $data['ogrenci_eposta'] ?? null;
            $ogrenci_sifre = $ogrenci_sifre ?? $data['ogrenci_sifre'] ?? null;
            $ogrenci_telefon = $ogrenci_telefon ?? $data['ogrenci_telefon'] ?? null;
        }

        $errors = [];

        // Öğrenci numarası kontrolü ve benzersizliği
        if (empty($ogrenci_no)) {
            $errors[] = 'Öğrenci numarası boş bırakılamaz.';
        } elseif (!ctype_digit($ogrenci_no) || strlen($ogrenci_no) !== 9) {
            $errors[] = 'Öğrenci numarası 9 haneli ve sadece rakamlardan oluşmalıdır.';
        } else {
            // Öğrenci numarası benzersizlik kontrolü
            $checkStudentNoStmt = $pdo->prepare("SELECT COUNT(*) FROM ogrenciler WHERE ogrenci_no = :ogrenci_no");
            $checkStudentNoStmt->execute([':ogrenci_no' => $ogrenci_no]);
            if ($checkStudentNoStmt->fetchColumn() > 0) {
                $errors[] = 'Bu öğrenci numarası zaten kullanımda!';
            }
        }

        // Öğrenci adı kontrolü
        if (empty($ogrenci_adi)) {
            $errors[] = 'Öğrenci adı boş bırakılamaz.';
        } elseif (!preg_match('/^[a-zA-ZÇĞİÖŞÜçğöşüı\s]+$/u', $ogrenci_adi)) {
            $errors[] = 'Öğrenci adı sadece yazılardan oluşmalı! Boşluk içerebilir.';
        }

        // Öğrenci soyadı kontrolü
        if (empty($ogrenci_soyadi)) {
            $errors[] = 'Öğrenci soyadı boş bırakılamaz.';
        } elseif (!preg_match('/^[a-zA-ZÇĞİÖŞÜçğöşüı\s]+$/u', $ogrenci_soyadi)) {
            $errors[] = 'Öğrenci soyadı sadece yazılardan oluşmalı! Boşluk içerebilir.';
        }

        // E-posta kontrolü
        if (empty($ogrenci_eposta)) {
            $errors[] = 'E-posta adresi boş bırakılamaz.';
        } elseif (!filter_var($ogrenci_eposta, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Geçersiz e-posta adresi!';
        } else {
            // E-posta benzersizlik kontrolü
            $checkEmailStmt = $pdo->prepare("SELECT COUNT(*) FROM ogrenciler WHERE ogrenci_eposta = :email");
            $checkEmailStmt->execute([':email' => $ogrenci_eposta]);
            if ($checkEmailStmt->fetchColumn() > 0) {
                $errors[] = 'Bu e-posta adresi zaten kullanımda!';
            }
        }

        // Şifre kontrolü
        if (empty($ogrenci_sifre)) {
            $errors[] = 'Şifre boş bırakılamaz.';
        } elseif (!preg_match('/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&.])[A-Za-z\d@$!%*?&.]{8,}/', $ogrenci_sifre)) {
            $errors[] = 'Şifre en az 8 karakter uzunluğunda olmalı ve en az bir büyük harf, bir küçük harf, bir rakam ve bir özel karakter içermelidir.';
        }

        // Telefon numarası kontrolü ve benzersizliği
        if (empty($ogrenci_telefon)) {
            $errors[] = 'Telefon numarası boş bırakılamaz.';
        } elseif (!preg_match('/^5\d{9}$/', $ogrenci_telefon)) {
            $errors[] = 'Geçersiz telefon numarası!';
        } else {
            // Telefon numarası benzersizlik kontrolü
            $checkPhoneStmt = $pdo->prepare("SELECT COUNT(*) FROM ogrenciler WHERE ogrenci_telefon = :telefon");
            $checkPhoneStmt->execute([':telefon' => $ogrenci_telefon]);
            if ($checkPhoneStmt->fetchColumn() > 0) {
                $errors[] = 'Bu telefon numarası zaten kullanımda!';
            }
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
            INSERT INTO ogrenciler (ogrenci_no, ogrenci_adi, ogrenci_soyadi, ogrenci_eposta, ogrenci_sifre, ogrenci_telefon, kayit_tarihi)
            VALUES (:ogrenci_no, :ogrenci_adi, :ogrenci_soyadi, :ogrenci_eposta, :ogrenci_sifre, :ogrenci_telefon, NOW())
        ");

        $stmt->execute([
            ':ogrenci_no' => $ogrenci_no,
            ':ogrenci_adi' => $ogrenci_adi,
            ':ogrenci_soyadi' => $ogrenci_soyadi,
            ':ogrenci_eposta' => $ogrenci_eposta,
            ':ogrenci_sifre' => password_hash($ogrenci_sifre, PASSWORD_DEFAULT), // Şifreyi hashle
            ':ogrenci_telefon' => $ogrenci_telefon
        ]);

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Öğrenci başarıyla eklendi.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(200); // HTTP 200 OK
            return;
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğrenci eklenirken bir hata oluştu.'
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


function updateStudent()
{
    global $pdo;

    try {
        // Query parametreleri al
        $ogrenci_no = $_GET['ogrenci_no'] ?? null;
        $ogrenci_adi = $_GET['ogrenci_adi'] ?? null;
        $ogrenci_soyadi = $_GET['ogrenci_soyadi'] ?? null;
        $ogrenci_eposta = $_GET['ogrenci_eposta'] ?? null;
        $ogrenci_sifre = $_GET['ogrenci_sifre'] ?? null;
        $ogrenci_telefon = $_GET['ogrenci_telefon'] ?? null;
        $eski_ogrenci_no = $_GET['eski_ogrenci_no'] ?? null;

        // Eğer parametreler URL'de yoksa, body'den al
        if (!$ogrenci_no || !$ogrenci_adi || !$ogrenci_soyadi || !$ogrenci_eposta || !$ogrenci_telefon || !$eski_ogrenci_no) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            $ogrenci_no = $ogrenci_no ?? $data['ogrenci_no'] ?? null;
            $ogrenci_adi = $ogrenci_adi ?? $data['ogrenci_adi'] ?? null;
            $ogrenci_soyadi = $ogrenci_soyadi ?? $data['ogrenci_soyadi'] ?? null;
            $ogrenci_eposta = $ogrenci_eposta ?? $data['ogrenci_eposta'] ?? null;
            $ogrenci_sifre = $ogrenci_sifre ?? $data['ogrenci_sifre'] ?? null;
            $ogrenci_telefon = $ogrenci_telefon ?? $data['ogrenci_telefon'] ?? null;
            $eski_ogrenci_no = $eski_ogrenci_no ?? $data['eski_ogrenci_no'] ?? null;
        }

        $errors = [];
        $changes = [];
        $updateFields = [];
        $updateParams = [];

        // Eski öğrenci numarası kontrolü
        if (empty($eski_ogrenci_no)) {
            $errors[] = 'Eski öğrenci numarası belirtilmeli.';
        } elseif (!ctype_digit($eski_ogrenci_no) || strlen($eski_ogrenci_no) !== 9) {
            $errors[] = 'Eski öğrenci numarası 9 haneli ve sadece rakamlardan oluşmalıdır.';
        }

        // Eğer eski öğrenci numarası hatalıysa, hata mesajı döndür
        if (!empty($errors)) {
            echo json_encode([
                'status' => 'error',
                'message' => $errors
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400);
            return;
        }

        // Eski verileri getir
        $stmt = $pdo->prepare("SELECT * FROM ogrenciler WHERE ogrenci_no = :eski_ogrenci_no");
        $stmt->execute([':eski_ogrenci_no' => $eski_ogrenci_no]);
        $existingStudent = $stmt->fetch(PDO::FETCH_ASSOC);

        // Eğer eski öğrenci numarasıyla eşleşen bir kayıt yoksa
        if (!$existingStudent) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Eski öğrenci numarasıyla eşleşen bir kayıt bulunamadı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(404);
            return;
        }

        // Öğrenci numarası kontrolü (sadece rakam, 9 haneli)
        if (!empty($ogrenci_no)) {
            if (!ctype_digit($ogrenci_no) || strlen($ogrenci_no) !== 9) {
                $errors[] = 'Öğrenci numarası sadece 9 haneli rakamlardan oluşmalıdır.';
            } elseif ($existingStudent['ogrenci_no'] !== $ogrenci_no) {
                $updateFields[] = "ogrenci_no = :ogrenci_no";
                $updateParams[':ogrenci_no'] = $ogrenci_no;
                $changes[] = 'Öğrenci Numarası';
            }
        }

        // Öğrenci adı kontrolü (sadece harf, rakam içeremez)
        if (!empty($ogrenci_adi)) {
            if (!preg_match('/^[a-zA-ZÇĞİÖŞÜçğöşüı\s]+$/u', $ogrenci_adi)) {
                $errors[] = 'Öğrenci adı sadece harflerden oluşmalı ve rakam içermemelidir.';
            } elseif ($existingStudent['ogrenci_adi'] !== $ogrenci_adi) {
                $updateFields[] = "ogrenci_adi = :ogrenci_adi";
                $updateParams[':ogrenci_adi'] = $ogrenci_adi;
                $changes[] = 'Adı';
            }
        }

        // Öğrenci soyadı kontrolü (sadece harf, rakam içeremez)
        if (!empty($ogrenci_soyadi)) {
            if (!preg_match('/^[a-zA-ZÇĞİÖŞÜçğöşüı\s]+$/u', $ogrenci_soyadi)) {
                $errors[] = 'Öğrenci soyadı sadece harflerden oluşmalı ve rakam içermemelidir.';
            } elseif ($existingStudent['ogrenci_soyadi'] !== $ogrenci_soyadi) {
                $updateFields[] = "ogrenci_soyadi = :ogrenci_soyadi";
                $updateParams[':ogrenci_soyadi'] = $ogrenci_soyadi;
                $changes[] = 'Soyadı';
            }
        }


        // E-posta kontrolü
        if (!empty($ogrenci_eposta)) {
            // E-posta formatı geçerliliğini kontrol et
            if (!filter_var($ogrenci_eposta, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Geçersiz e-posta adresi formatı.';
            } elseif ($existingStudent['ogrenci_eposta'] !== $ogrenci_eposta) {
                // Eğer e-posta değişmişse, güncelle
                $updateFields[] = "ogrenci_eposta = :ogrenci_eposta";
                $updateParams[':ogrenci_eposta'] = $ogrenci_eposta;
                $changes[] = 'E-posta';
            }
        }


        // Telefon numarası kontrolü ve değişiklik
        if (!empty($ogrenci_telefon)) {
            // Telefon numarasının geçerliliğini kontrol et
            if (!preg_match('/^5\d{9}$/', $ogrenci_telefon)) {
                $errors[] = 'Telefon numarası 5 ile başlamalı ve sadece 10 rakamdan oluşmalıdır.';
            } elseif ($existingStudent['ogrenci_telefon'] !== $ogrenci_telefon) {
                // Eğer telefon numarası değişmişse, güncelle
                $updateFields[] = "ogrenci_telefon = :ogrenci_telefon";
                $updateParams[':ogrenci_telefon'] = $ogrenci_telefon;
                $changes[] = 'Telefon';
            }
        }

        // Şifre kontrolü ve değişiklik
        if (!empty($ogrenci_sifre)) {
            // Şifre karmaşıklığı kontrolü
            if (!preg_match('/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&.])[A-Za-z\d@$!%*?&.]{8,}/', $ogrenci_sifre)) {
                // Eğer şifre belirtilen kurallara uymazsa hata mesajı döndür
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Şifre en az 8 karakter uzunluğunda olmalı ve en az bir büyük harf, bir küçük harf, bir rakam ve bir özel karakter içermelidir.'
                ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                http_response_code(400);  // Bad Request
                return;
            }

            // Şifre doğrulaması
            if (!password_verify($ogrenci_sifre, $existingStudent['ogrenci_sifre'])) {
                $hashedPassword = password_hash($ogrenci_sifre, PASSWORD_DEFAULT);
                $updateFields[] = "ogrenci_sifre = :ogrenci_sifre";
                $updateParams[':ogrenci_sifre'] = $hashedPassword;
                $changes[] = 'Şifre';
            }
        }

        // Eğer hata varsa döndür
        if (!empty($errors)) {
            echo json_encode([
                'status' => 'error',
                'message' => $errors
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400);
            return;
        }

        // Eğer güncellenecek bir alan yoksa
        if (empty($updateFields)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Herhangi bir değişiklik yapılmadı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400);
            return;
        }

        // Güncelleme sorgusunu oluştur
        $updateParams[':eski_ogrenci_no'] = $eski_ogrenci_no;
        $query = "UPDATE ogrenciler SET " . implode(', ', $updateFields) . " WHERE ogrenci_no = :eski_ogrenci_no";
        $stmt = $pdo->prepare($query);
        $stmt->execute($updateParams);

        // Sonuç döndür
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Öğrenci başarıyla güncellendi.',
                'changes' => $changes
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(200);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğrenci güncellenirken bir hata oluştu.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400);
        }
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        http_response_code(500);
    }
}



function deleteStudent()
{
    global $pdo;

    try {
        // Önce URL parametrelerini kontrol et
        $ogrenci_no = $_GET['ogrenci_no'] ?? null;

        // Eğer URL parametresi yoksa, body'den al
        if (!$ogrenci_no) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);  // JSON olarak gelen veriyi çözümle
            $ogrenci_no = $data['ogrenci_no'] ?? null;  // JSON içinden öğrenci numarasını al
        }

        // Öğrenci numarası eksikse hata döndür
        if (empty($ogrenci_no)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğrenci numarası belirtilmeli.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400);  // Bad Request
            return;
        } elseif (!ctype_digit($ogrenci_no) || strlen($ogrenci_no) !== 9) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğrenci numarası 9 haneli ve sadece rakamlardan oluşmalıdır.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400);  // Bad Request
            return;
        }
        // Öğrenci numarasını kullanarak veritabanından silme işlemi yap
        $stmt = $pdo->prepare("DELETE FROM ogrenciler WHERE ogrenci_no = :ogrenci_no");
        $stmt->execute([':ogrenci_no' => $ogrenci_no]);

        // Eğer silme işlemi başarılı olduysa
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Öğrenci başarıyla silindi.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(200);  // OK
        } else {
            // Eğer öğrenci numarasıyla eşleşen bir kayıt bulunamazsa
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğrenci bulunamadı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(404);  // Not Found
        }
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        http_response_code(500);  // Internal Server Error
    }
}

function getStudentCourses()
{
    global $pdo;

    try {
        $ogrenci_no = $_GET['ogrenci_no'] ?? null;

        // Eğer URL parametresi yoksa, body'den al
        if (!$ogrenci_no) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);  // JSON olarak gelen veriyi çözümle
            $ogrenci_no = $data['ogrenci_no'] ?? null;  // JSON içinden öğrenci numarasını al
        }

        if (empty($ogrenci_no)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğrenci numarası belirtilmeli.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400);  // Bad Request
            return;
        } elseif (!ctype_digit($ogrenci_no) || strlen($ogrenci_no) !== 9) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğrenci numarası 9 haneli ve sadece rakamlardan oluşmalıdır.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400);  // Bad Request
            return;
        }

        $stmt = $pdo->prepare('SELECT * FROM ogrenciler WHERE ogrenci_no = :ogrenci_no');
        $stmt->bindParam(':ogrenci_no', $ogrenci_no, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($result)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğrenci bulunamadı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400);
            return;
        }

        $chechStudentCoursesStmt = $pdo->prepare('SELECT * FROM ogrenciler WHERE ogrenci_no = :ogrenci_no AND aldigi_dersler IS NOT NULL');
        $chechStudentCoursesStmt->bindParam(':ogrenci_no', $ogrenci_no, PDO::PARAM_STR);
        $chechStudentCoursesStmt->execute();
        $result = $chechStudentCoursesStmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($result)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğrencinin aldığı dersler bulunamadı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400);
            return;
        }

        echo json_encode([
            'status' => 'success',
            'data' => $result
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        http_response_code(500);  // Internal Server Error
    }
}

function addStudentCourse()
{
    global $pdo;

    try {
        // URL'den gelen verileri al
        $ogrenci_no = $_GET['ogrenci_no'] ?? null;
        $dersler = $_GET['dersler'] ?? null;

        // Eğer URL parametresi yoksa, body'den al
        if (!$ogrenci_no) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);  // JSON olarak gelen veriyi çözümle
            $ogrenci_no = $data['ogrenci_no'] ?? null;  // JSON içinden öğrenci numarasını al
            $dersler = $data['dersler'] ?? null;  // JSON içinden dersleri al
        }

        // Öğrenci numarasının kontrolü
        if (empty($ogrenci_no)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğrenci numarası belirtilmeli.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400);  // Bad Request
            return;
        } elseif (!ctype_digit($ogrenci_no) || strlen($ogrenci_no) !== 9) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğrenci numarası 9 haneli ve sadece rakamlardan oluşmalıdır.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400);  // Bad Request
            return;
        }

        // Derslerin kontrolü
        if (empty($dersler)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Dersler belirtilmeli.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400);  // Bad Request
            return;
        }

        // Derslerin doğru formatta olup olmadığını kontrol et
        if (is_string($dersler)) {
            // Eğer dersler string olarak gelmişse, virgüllerle ayırarak array'e dönüştür
            $derslerArray = explode(',', $dersler);
        } elseif (is_array($dersler)) {
            // Eğer dersler zaten array olarak gelmişse, olduğu gibi al
            $derslerArray = $dersler;
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Dersler doğru formatta değil.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400);  // Bad Request
            return;
        }

        // Ders adlarının boş olup olmadığını kontrol et
        foreach ($derslerArray as $ders) {
            if (trim($ders) === "") {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Ders adları boş olamaz.'
                ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                http_response_code(400);  // Bad Request
                return;
            }
        }

        // Veritabanındaki ders isimlerini al
        $stmt = $pdo->prepare('SELECT ders_adi FROM dersler');
        $stmt->execute();
        $availableCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $availableCourseNames = array_column($availableCourses, 'ders_adi');  // Veritabanındaki ders isimlerini bir array'e al

        // Girilen derslerin sistemde var olup olmadığını kontrol et
        foreach ($derslerArray as $ders) {
            if (!in_array($ders, $availableCourseNames)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => "$ders dersi sistemde bulunamadı."
                ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                http_response_code(400);  // Bad Request
                return;
            }
        }

        // Öğrenci numarasına göre öğrenci kontrolü yap
        $stmt = $pdo->prepare('SELECT * FROM ogrenciler WHERE ogrenci_no = :ogrenci_no');
        $stmt->bindParam(':ogrenci_no', $ogrenci_no, PDO::PARAM_STR);
        $stmt->execute();
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğrenci bulunamadı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(404);  // Not Found
            return;
        }

        // Mevcut dersleri al
        $currentCourses = $student['aldigi_dersler'];  // Mevcut dersler
        if (!empty($currentCourses)) {
            // Mevcut dersleri JSON formatından array'e dönüştür
            $currentCoursesArray = json_decode($currentCourses, true);  // JSON'dan array'e dönüştür
            // Yeni derslerle mevcut dersleri karşılaştır
            $updatedCourses = array_merge($currentCoursesArray, $derslerArray);
            $updatedCourses = array_unique($updatedCourses);  // Tekrarlayan dersleri kaldır
            // Eğer dersler zaten aynıysa, işlem yapılmasın
            if ($updatedCourses === $currentCoursesArray) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Öğrencinin aldığı dersler zaten güncel.'
                ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                http_response_code(200);  // OK
                return;
            }
            // Array'i tekrar JSON formatına dönüştür
            $updatedCoursesStr = json_encode($updatedCourses, JSON_UNESCAPED_UNICODE); // Türkçe karakterleri düzgün sakla
        } else {
            // Eğer ders yoksa, sadece yeni dersleri JSON formatında kaydet
            $updatedCoursesStr = json_encode($derslerArray, JSON_UNESCAPED_UNICODE); // Türkçe karakterleri düzgün sakla
        }

        // Dersleri güncelle
        $stmt = $pdo->prepare("UPDATE ogrenciler SET aldigi_dersler = :aldigi_dersler WHERE ogrenci_no = :ogrenci_no");
        $stmt->bindParam(':aldigi_dersler', $updatedCoursesStr);
        $stmt->bindParam(':ogrenci_no', $ogrenci_no);
        $stmt->execute();

        echo json_encode([
            'status' => 'success',
            'message' => 'Öğrencinin aldığı dersler başarıyla güncellendi.',
            'updated_courses' => $updatedCoursesStr
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        http_response_code(200);  // OK
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        http_response_code(500);  // Internal Server Error
    }
}

function updateStudentCourse()
{
    global $pdo;

    try {
        // URL'den gelen verileri al
        $ogrenci_no = $_GET['ogrenci_no'] ?? null;
        $dersler = $_GET['dersler'] ?? null;

        // Eğer URL parametresi yoksa, body'den al
        if (!$ogrenci_no) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);  // JSON olarak gelen veriyi çözümle
            $ogrenci_no = $data['ogrenci_no'] ?? null;  // JSON içinden öğrenci numarasını al
            $dersler = $data['dersler'] ?? null;  // JSON içinden dersleri al
        }

        // Öğrenci numarasının kontrolü
        if (empty($ogrenci_no)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğrenci numarası belirtilmeli.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400);  // Bad Request
            return;
        } elseif (!ctype_digit($ogrenci_no) || strlen($ogrenci_no) !== 9) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğrenci numarası 9 haneli ve sadece rakamlardan oluşmalıdır.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400);  // Bad Request
            return;
        }

        // Derslerin kontrolü
        if (empty($dersler)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Dersler belirtilmeli.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400);  // Bad Request
            return;
        }

        // Derslerin doğru formatta olup olmadığını kontrol et
        if (is_string($dersler)) {
            // Eğer dersler string olarak gelmişse, virgüllerle ayırarak array'e dönüştür
            $derslerArray = explode(',', $dersler);
        } elseif (is_array($dersler)) {
            // Eğer dersler zaten array olarak gelmişse, olduğu gibi al
            $derslerArray = $dersler;
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Dersler doğru formatta değil.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400);  // Bad Request
            return;
        }

        // Ders adı boş olanları kontrol et
        foreach ($derslerArray as $ders) {
            if (trim($ders) === "") {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Ders adları boş olamaz.'
                ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                http_response_code(400);  // Bad Request
                return;
            }
        }

        // Öğrenci numarasına göre öğrenci kontrolü yap
        $stmt = $pdo->prepare('SELECT * FROM ogrenciler WHERE ogrenci_no = :ogrenci_no');
        $stmt->bindParam(':ogrenci_no', $ogrenci_no, PDO::PARAM_STR);
        $stmt->execute();
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğrenci bulunamadı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(404);  // Not Found
            return;
        }

        // Mevcut dersleri al
        $currentCourses = $student['aldigi_dersler'];  // Mevcut dersler
        if (!empty($currentCourses)) {
            // Mevcut dersleri JSON formatından array'e dönüştür
            $currentCoursesArray = json_decode($currentCourses, true);  // JSON'dan array'e dönüştür
            // Derslerin sıralaması önemli değilse ve karşılaştırmak istiyorsak sıralamaları düzeltelim
            sort($currentCoursesArray);
            sort($derslerArray);

            // Eğer dersler zaten aynıysa, işlem yapılmasın
            if ($currentCoursesArray === $derslerArray) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Öğrencinin aldığı dersler zaten güncel.'
                ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                http_response_code(200);  // OK
                return;
            }

            // Eğer dersler değişmişse, güncelle
            $updatedCoursesStr = json_encode($derslerArray, JSON_UNESCAPED_UNICODE); // Türkçe karakterleri düzgün sakla
        } else {
            // Eğer ders yoksa, sadece yeni dersleri JSON formatında kaydet
            $updatedCoursesStr = json_encode($derslerArray, JSON_UNESCAPED_UNICODE); // Türkçe karakterleri düzgün sakla
        }

        // Dersleri güncelle
        $stmt = $pdo->prepare("UPDATE ogrenciler SET aldigi_dersler = :aldigi_dersler WHERE ogrenci_no = :ogrenci_no");
        $stmt->bindParam(':aldigi_dersler', $updatedCoursesStr);
        $stmt->bindParam(':ogrenci_no', $ogrenci_no);
        $stmt->execute();

        echo json_encode([
            'status' => 'success',
            'message' => 'Öğrencinin aldığı dersler başarıyla güncellendi.',
            'updated_courses' => $updatedCoursesStr
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        http_response_code(200);  // OK
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        http_response_code(500);  // Internal Server Error
    }
}

function deleteStudentCourse()
{
    global $pdo;

    try {
        $ogrenci_no = $_GET['ogrenci_no'] ?? null;
        $dersler = $_GET['dersler'] ?? null;  // Dersler parametresi (string veya array)

        // Eğer URL parametresi yoksa, body'den al
        if (!$ogrenci_no) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);  // JSON olarak gelen veriyi çözümle
            $ogrenci_no = $data['ogrenci_no'] ?? null;  // JSON içinden öğrenci numarasını al
            $dersler = $data['dersler'] ?? null;  // JSON içinden dersleri al
        }

        // Öğrenci numarasının kontrolü
        if (empty($ogrenci_no)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğrenci numarası belirtilmeli.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400);  // Bad Request
            return;
        } elseif (!ctype_digit($ogrenci_no) || strlen($ogrenci_no) !== 9) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğrenci numarası 9 haneli ve sadece rakamlardan oluşmalıdır.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400);  // Bad Request
            return;
        }

        // Derslerin kontrolü
        if (empty($dersler)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Dersler belirtilmeli.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400);  // Bad Request
            return;
        }

        // Derslerin doğru formatta olup olmadığını kontrol et
        if (is_string($dersler)) {
            if ($dersler === 'all' || $dersler === 'hepsi') {
                // Eğer "all" veya "hepsi" girildiyse, tüm dersler silinecek
                $derslerArray = [];
            } else {
                // Eğer dersler string olarak gelmişse, virgüllerle ayırarak array'e dönüştür
                $derslerArray = explode(',', $dersler);
            }
        } elseif (is_array($dersler)) {
            // Eğer dersler zaten array olarak gelmişse, olduğu gibi al
            $derslerArray = $dersler;
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Dersler doğru formatta değil.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400);  // Bad Request
            return;
        }

        // Boş ders adı kontrolü
        foreach ($derslerArray as $ders) {
            if (empty($ders)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Ders adları boş olamaz.'
                ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                http_response_code(400);  // Bad Request
                return;
            }
        }

        // Veritabanındaki ders isimlerini al
        $stmt = $pdo->prepare('SELECT ders_adi FROM dersler');
        $stmt->execute();
        $availableCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $availableCourseNames = array_column($availableCourses, 'ders_adi');  // Veritabanındaki ders isimlerini bir array'e al

        // Ders isimlerinin doğruluğunu kontrol et
        foreach ($derslerArray as $ders) {
            if (!in_array($ders, $availableCourseNames)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => "$ders dersi sistemde bulunamadı."
                ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                http_response_code(400);  // Bad Request
                return;
            }
        }

        // Öğrenci numarasına göre öğrenci kontrolü yap
        $stmt = $pdo->prepare('SELECT * FROM ogrenciler WHERE ogrenci_no = :ogrenci_no');
        $stmt->bindParam(':ogrenci_no', $ogrenci_no, PDO::PARAM_STR);
        $stmt->execute();
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğrenci bulunamadı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(404);  // Not Found
            return;
        }

        // Mevcut dersleri al
        $currentCourses = $student['aldigi_dersler'];  // Mevcut dersler
        if (!empty($currentCourses)) {
            // Mevcut dersleri JSON formatından array'e dönüştür
            $currentCoursesArray = json_decode($currentCourses, true);  // JSON'dan array'e dönüştür

            // Girilen derslerin öğrenciye ait olup olmadığını kontrol et
            foreach ($derslerArray as $ders) {
                if (!in_array($ders, $currentCoursesArray)) {
                    echo json_encode([
                        'status' => 'error',
                        'message' => "$ders dersi öğrenciye ait değil."
                    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                    http_response_code(400);  // Bad Request
                    return;
                }
            }

            // Eğer "all" veya "hepsi" belirtilmemişse ve dersler varsa
            if ($derslerArray !== []) {
                // Silinecek dersleri mevcut derslerden çıkar
                $updatedCourses = array_diff($currentCoursesArray, $derslerArray);
                // Eğer dersler tamamen silinmişse, null yapalım
                if (empty($updatedCourses)) {
                    $updatedCoursesStr = null;
                } else {
                    // Array'i tekrar JSON formatına dönüştür
                    $updatedCoursesStr = json_encode(array_values($updatedCourses), JSON_UNESCAPED_UNICODE); // Türkçe karakterleri düzgün sakla
                }
            } else {
                // Eğer dersler boş bir array ise, tüm dersler silinecek
                $updatedCoursesStr = null;
            }
        } else {
            // Öğrenci zaten ders almamışsa
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğrenci zaten ders almamış.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(404);  // Not Found
            return;
        }

        // Dersleri güncelle
        $stmt = $pdo->prepare("UPDATE ogrenciler SET aldigi_dersler = :aldigi_dersler WHERE ogrenci_no = :ogrenci_no");
        $stmt->bindParam(':aldigi_dersler', $updatedCoursesStr);
        $stmt->bindParam(':ogrenci_no', $ogrenci_no);
        $stmt->execute();

        echo json_encode([
            'status' => 'success',
            'message' => 'Öğrencinin aldığı dersler başarıyla güncellendi.',
            'updated_courses' => $updatedCoursesStr
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        http_response_code(200);  // OK
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        http_response_code(500);  // Internal Server Error
    }
}

