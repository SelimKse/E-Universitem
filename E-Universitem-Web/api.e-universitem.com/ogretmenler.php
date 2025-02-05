<?php
header('Content-Type: application/json; charset=UTF-8'); // UTF-8 ile JSON başlığı ekliyoruz
require_once 'database.php';

$request = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($request, PHP_URL_PATH); // Sadece URL'nin path kısmını al
parse_str($_SERVER['QUERY_STRING'], $queryParams);

// API isteğine göre işlem yap
if ($requestPath === '/ogretmenler') { // Eğer /ogretmenler endpoint'i kullanıldıysa
    if (checkApiKey($queryParams) === false) {
        return;
    }

    getTeacher(); // Kullanıcıları getir
} else if ($requestPath === '/ogretmenler/olustur') { // Eğer /ogretmenler endpoint'i kullanıldıysa
    if (checkApiKey($queryParams) === false) {
        return;
    }

    createTeacher(); // Öğretmen ekleme fonksiyonunu çağır
} else if ($requestPath === '/ogretmenler/guncelle') { // Eğer /ogretmenler endpoint'i kullanıldıysa
    if (checkApiKey($queryParams) === false) {
        return;
    }

    updateTeacher(); // Öğretmen güncelleme fonksiyonunu çağır
} else if ($requestPath === '/ogretmenler/sil') { // Eğer /ogretmenler endpoint'i kullanıldıysa 
    if (checkApiKey($queryParams) === false) {
        return;
    }

    deleteTeacher(); // Öğretmen silme fonksiyonunu çağır
} else if ($requestPath === '/ogretmenler/dersler') { // Eğer /ogretmenler endpoint'i kullanıldıysa
    if (checkApiKey($queryParams) === false) {
        return;
    }

    getTeacherCourses(); // Öğretmen derslerini getir
} else if ($requestPath === '/ogretmenler/dersler/ekle') {
    if (checkApiKey($queryParams) === false) {
        return;
    }

    addTeacherCourse(); // Öğretmen ders ekleme fonksiyonunu çağır
} else if ($requestPath === "/ogretmenler/dersler/guncelle") {
    if (checkApiKey($queryParams) === false) {
        return;
    }

    updateTeacherCourse(); // Öğretmen ders güncelleme fonksiyonunu çağır
} else if ($requestPath === "/ogretmenler/dersler/sil") {
    if (checkApiKey($queryParams) === false) {
        return;
    }

    deleteTeacherCourse(); // Öğretmen ders silme fonksiyonunu çağır
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
            'message' => 'API Key belirtilmedi.'
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

function getTeacher()
{
    global $pdo;

    try {
        // GET parametrelerini al
        $ogretmen_no = $_GET['ogretmen_no'] ?? null;
        $ogretmen_adi = $_GET['ogretmen_adi'] ?? null;
        $ogretmen_soyadi = $_GET['ogretmen_soyadi'] ?? null;
        $ogretmen_eposta = $_GET['ogretmen_eposta'] ?? null;
        $ogretmen_telefon = $_GET['ogretmen_telefon'] ?? null;

        // Eğer GET parametreleri yoksa, body'den al
        if (!$ogretmen_no && !$ogretmen_adi && !$ogretmen_soyadi && !$ogretmen_eposta && !$ogretmen_telefon) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            $ogretmen_no = $data['ogretmen_no'] ?? null;
            $ogretmen_adi = $data['ogretmen_adi'] ?? null;
            $ogretmen_soyadi = $data['ogretmen_soyadi'] ?? null;
            $ogretmen_eposta = $data['ogretmen_eposta'] ?? null;
            $ogretmen_telefon = $data['ogretmen_telefon'] ?? null;
        }

        // SQL sorgusunu başlat
        $sql = "SELECT * FROM ogretmenler";
        $conditions = [];
        $parameters = [];

        // Yalnızca geçerli parametreleri işleme al
        if (!empty($ogretmen_no)) {
            $conditions[] = "ogretmen_no = :ogretmen_no";
            $parameters[':ogretmen_no'] = $ogretmen_no;
        }

        if (!empty($ogretmen_adi)) {
            $conditions[] = "ogretmen_adi = :ogretmen_adi";
            $parameters[':ogretmen_adi'] = $ogretmen_adi;
        }

        if (!empty($ogretmen_soyadi)) {
            $conditions[] = "ogretmen_soyadi = :ogretmen_soyadi";
            $parameters[':ogretmen_soyadi'] = $ogretmen_soyadi;
        }

        if (!empty($ogretmen_eposta)) {
            $conditions[] = "ogretmen_eposta = :ogretmen_eposta";
            $parameters[':ogretmen_eposta'] = $ogretmen_eposta;
        }

        if (!empty($ogretmen_telefon)) {
            $conditions[] = "ogretmen_telefon = :ogretmen_telefon";
            $parameters[':ogretmen_telefon'] = $ogretmen_telefon;
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
                'message' => 'Öğretmen bulunamadı.'
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

function createTeacher()
{
    global $pdo;

    try {
        // Query parametreleri al
        $ogretmen_no = $_GET['ogretmen_no'] ?? null;
        $ogretmen_adi = $_GET['ogretmen_adi'] ?? null;
        $ogretmen_soyadi = $_GET['ogretmen_soyadi'] ?? null;
        $ogretmen_eposta = $_GET['ogretmen_eposta'] ?? null;
        $ogretmen_sifre = $_GET['ogretmen_sifre'] ?? null;
        $ogretmen_telefon = $_GET['ogretmen_telefon'] ?? null;

        // Eğer query parametreleri yoksa, body'den al
        if (!$ogretmen_no || !$ogretmen_adi || !$ogretmen_soyadi || !$ogretmen_eposta || !$ogretmen_sifre || !$ogretmen_telefon) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            // Body'den gelen veriler
            $ogretmen_no = $ogretmen_no ?? $data['ogretmen_no'] ?? null;
            $ogretmen_adi = $ogretmen_adi ?? $data['ogretmen_adi'] ?? null;
            $ogretmen_soyadi = $ogretmen_soyadi ?? $data['ogretmen_soyadi'] ?? null;
            $ogretmen_eposta = $ogretmen_eposta ?? $data['ogretmen_eposta'] ?? null;
            $ogretmen_sifre = $ogretmen_sifre ?? $data['ogretmen_sifre'] ?? null;
            $ogretmen_telefon = $ogretmen_telefon ?? $data['ogretmen_telefon'] ?? null;
        }

        $errors = [];

        // Öğretmen numarası kontrolü ve benzersizliği
        if (empty($ogretmen_no)) {
            $errors[] = 'Öğretmen numarası boş bırakılamaz.';
        } elseif (!ctype_digit($ogretmen_no) || strlen($ogretmen_no) !== 9) {
            $errors[] = 'Öğretmen numarası 9 haneli ve sadece rakamlardan oluşmalıdır.';
        } else {
            // Öğretmen numarası benzersizlik kontrolü
            $checkStudentNoStmt = $pdo->prepare("SELECT COUNT(*) FROM ogretmenler WHERE ogretmen_no = :ogretmen_no");
            $checkStudentNoStmt->execute([':ogretmen_no' => $ogretmen_no]);
            if ($checkStudentNoStmt->fetchColumn() > 0) {
                $errors[] = 'Bu Öğretmen numarası zaten kullanımda!';
            }
        }

        // Öğretmen adı kontrolü
        if (empty($ogretmen_adi)) {
            $errors[] = 'Öğretmen adı boş bırakılamaz.';
        } elseif (!preg_match('/^[a-zA-ZÇĞİÖŞÜçğöşüı\s]+$/u', $ogretmen_adi)) {
            $errors[] = 'Öğretmen adı sadece yazılardan oluşmalı! Boşluk içerebilir.';
        }

        // Öğretmen soyadı kontrolü
        if (empty($ogretmen_soyadi)) {
            $errors[] = 'Öğretmen soyadı boş bırakılamaz.';
        } elseif (!preg_match('/^[a-zA-ZÇĞİÖŞÜçğöşüı\s]+$/u', $ogretmen_soyadi)) {
            $errors[] = 'Öğretmen soyadı sadece yazılardan oluşmalı! Boşluk içerebilir.';
        }

        // E-posta kontrolü
        if (empty($ogretmen_eposta)) {
            $errors[] = 'E-posta adresi boş bırakılamaz.';
        } elseif (!filter_var($ogretmen_eposta, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Geçersiz e-posta adresi!';
        } else {
            // E-posta benzersizlik kontrolü
            $checkEmailStmt = $pdo->prepare("SELECT COUNT(*) FROM ogretmenler WHERE ogretmen_eposta = :email");
            $checkEmailStmt->execute([':email' => $ogretmen_eposta]);
            if ($checkEmailStmt->fetchColumn() > 0) {
                $errors[] = 'Bu e-posta adresi zaten kullanımda!';
            }
        }

        // Şifre kontrolü
        if (empty($ogretmen_sifre)) {
            $errors[] = 'Şifre boş bırakılamaz.';
        } elseif (!preg_match('/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&.])[A-Za-z\d@$!%*?&.]{8,}/', $ogretmen_sifre)) {
            $errors[] = 'Şifre en az 8 karakter uzunluğunda olmalı ve en az bir büyük harf, bir küçük harf, bir rakam ve bir özel karakter içermelidir.';
        }

        // Telefon numarası kontrolü ve benzersizliği
        if (empty($ogretmen_telefon)) {
            $errors[] = 'Telefon numarası boş bırakılamaz.';
        } elseif (!preg_match('/^5\d{9}$/', $ogretmen_telefon)) {
            $errors[] = 'Geçersiz telefon numarası!';
        } else {
            // Telefon numarası benzersizlik kontrolü
            $checkPhoneStmt = $pdo->prepare("SELECT COUNT(*) FROM ogretmenler WHERE ogretmen_telefon = :telefon");
            $checkPhoneStmt->execute([':telefon' => $ogretmen_telefon]);
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
            INSERT INTO ogretmenler (ogretmen_no, ogretmen_adi, ogretmen_soyadi, ogretmen_eposta, ogretmen_sifre, ogretmen_telefon, kayit_tarihi)
            VALUES (:ogretmen_no, :ogretmen_adi, :ogretmen_soyadi, :ogretmen_eposta, :ogretmen_sifre, :ogretmen_telefon, NOW())
        ");

        $stmt->execute([
            ':ogretmen_no' => $ogretmen_no,
            ':ogretmen_adi' => $ogretmen_adi,
            ':ogretmen_soyadi' => $ogretmen_soyadi,
            ':ogretmen_eposta' => $ogretmen_eposta,
            ':ogretmen_sifre' => password_hash($ogretmen_sifre, PASSWORD_DEFAULT), // Şifreyi hashle
            ':ogretmen_telefon' => $ogretmen_telefon
        ]);

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Öğretmen başarıyla eklendi.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(200); // HTTP 200 OK
            return;
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğretmen eklenirken bir hata oluştu.'
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


function updateTeacher()
{
    global $pdo;

    try {
        // Query parametreleri al
        $ogretmen_no = $_GET['ogretmen_no'] ?? null;
        $ogretmen_adi = $_GET['ogretmen_adi'] ?? null;
        $ogretmen_soyadi = $_GET['ogretmen_soyadi'] ?? null;
        $ogretmen_eposta = $_GET['ogretmen_eposta'] ?? null;
        $ogretmen_sifre = $_GET['ogretmen_sifre'] ?? null;
        $ogretmen_telefon = $_GET['ogretmen_telefon'] ?? null;
        $eski_ogretmen_no = $_GET['eski_ogretmen_no'] ?? null;

        // Eğer parametreler URL'de yoksa, body'den al
        if (!$ogretmen_no || !$ogretmen_adi || !$ogretmen_soyadi || !$ogretmen_eposta || !$ogretmen_telefon || !$eski_ogretmen_no) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            $ogretmen_no = $ogretmen_no ?? $data['ogretmen_no'] ?? null;
            $ogretmen_adi = $ogretmen_adi ?? $data['ogretmen_adi'] ?? null;
            $ogretmen_soyadi = $ogretmen_soyadi ?? $data['ogretmen_soyadi'] ?? null;
            $ogretmen_eposta = $ogretmen_eposta ?? $data['ogretmen_eposta'] ?? null;
            $ogretmen_sifre = $ogretmen_sifre ?? $data['ogretmen_sifre'] ?? null;
            $ogretmen_telefon = $ogretmen_telefon ?? $data['ogretmen_telefon'] ?? null;
            $eski_ogretmen_no = $eski_ogretmen_no ?? $data['eski_ogretmen_no'] ?? null;
        }

        $errors = [];
        $changes = [];
        $updateFields = [];
        $updateParams = [];

        // Eski Öğretmen numarası kontrolü
        if (empty($eski_ogretmen_no)) {
            $errors[] = 'Eski Öğretmen numarası belirtilmeli.';
        } elseif (!ctype_digit($eski_ogretmen_no) || strlen($eski_ogretmen_no) !== 9) {
            $errors[] = 'Eski Öğretmen numarası 9 haneli ve sadece rakamlardan oluşmalıdır.';
        }

        // Eğer eski Öğretmen numarası hatalıysa, hata mesajı döndür
        if (!empty($errors)) {
            echo json_encode([
                'status' => 'error',
                'errors' => $errors
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400);
            return;
        }

        // Eski verileri getir
        $stmt = $pdo->prepare("SELECT * FROM ogretmenler WHERE ogretmen_no = :eski_ogretmen_no");
        $stmt->execute([':eski_ogretmen_no' => $eski_ogretmen_no]);
        $existingStudent = $stmt->fetch(PDO::FETCH_ASSOC);

        // Eğer eski Öğretmen numarasıyla eşleşen bir kayıt yoksa
        if (!$existingStudent) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Eski Öğretmen numarasıyla eşleşen bir kayıt bulunamadı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(404);
            return;
        }

        // Öğretmen numarası kontrolü (sadece rakam, 9 haneli)
        if (!empty($ogretmen_no)) {
            if (!ctype_digit($ogretmen_no) || strlen($ogretmen_no) !== 9) {
                $errors[] = 'Öğretmen numarası sadece 9 haneli rakamlardan oluşmalıdır.';
            } elseif ($existingStudent['ogretmen_no'] !== $ogretmen_no) {
                $updateFields[] = "ogretmen_no = :ogretmen_no";
                $updateParams[':ogretmen_no'] = $ogretmen_no;
                $changes[] = 'Öğretmen Numarası';
            }
        }

        // Öğretmen adı kontrolü (sadece harf, rakam içeremez)
        if (!empty($ogretmen_adi)) {
            if (!preg_match('/^[a-zA-ZÇĞİÖŞÜçğöşüı\s]+$/u', $ogretmen_adi)) {
                $errors[] = 'Öğretmen adı sadece harflerden oluşmalı ve rakam içermemelidir.';
            } elseif ($existingStudent['ogretmen_adi'] !== $ogretmen_adi) {
                $updateFields[] = "ogretmen_adi = :ogretmen_adi";
                $updateParams[':ogretmen_adi'] = $ogretmen_adi;
                $changes[] = 'Adı';
            }
        }

        // Öğretmen soyadı kontrolü (sadece harf, rakam içeremez)
        if (!empty($ogretmen_soyadi)) {
            if (!preg_match('/^[a-zA-ZÇĞİÖŞÜçğöşüı\s]+$/u', $ogretmen_soyadi)) {
                $errors[] = 'Öğretmen soyadı sadece harflerden oluşmalı ve rakam içermemelidir.';
            } elseif ($existingStudent['ogretmen_soyadi'] !== $ogretmen_soyadi) {
                $updateFields[] = "ogretmen_soyadi = :ogretmen_soyadi";
                $updateParams[':ogretmen_soyadi'] = $ogretmen_soyadi;
                $changes[] = 'Soyadı';
            }
        }


        // E-posta kontrolü
        if (!empty($ogretmen_eposta)) {
            // E-posta formatı geçerliliğini kontrol et
            if (!filter_var($ogretmen_eposta, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Geçersiz e-posta adresi formatı.';
            } elseif ($existingStudent['ogretmen_eposta'] !== $ogretmen_eposta) {
                // Eğer e-posta değişmişse, güncelle
                $updateFields[] = "ogretmen_eposta = :ogretmen_eposta";
                $updateParams[':ogretmen_eposta'] = $ogretmen_eposta;
                $changes[] = 'E-posta';
            }
        }


        // Telefon numarası kontrolü ve değişiklik
        if (!empty($ogretmen_telefon)) {
            // Telefon numarasının geçerliliğini kontrol et
            if (!preg_match('/^5\d{9}$/', $ogretmen_telefon)) {
                $errors[] = 'Telefon numarası 5 ile başlamalı ve sadece 10 rakamdan oluşmalıdır.';
            } elseif ($existingStudent['ogretmen_telefon'] !== $ogretmen_telefon) {
                // Eğer telefon numarası değişmişse, güncelle
                $updateFields[] = "ogretmen_telefon = :ogretmen_telefon";
                $updateParams[':ogretmen_telefon'] = $ogretmen_telefon;
                $changes[] = 'Telefon';
            }
        }

        // Şifre kontrolü ve değişiklik
        if (!empty($ogretmen_sifre)) {
            // Şifre karmaşıklığı kontrolü
            if (!preg_match('/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&.])[A-Za-z\d@$!%*?&.]{8,}/', $ogretmen_sifre)) {
                // Eğer şifre belirtilen kurallara uymazsa hata mesajı döndür
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Şifre en az 8 karakter uzunluğunda olmalı ve en az bir büyük harf, bir küçük harf, bir rakam ve bir özel karakter içermelidir.'
                ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                http_response_code(400);  // Bad Request
                return;
            }

            // Şifre doğrulaması
            if (!password_verify($ogretmen_sifre, $existingStudent['ogretmen_sifre'])) {
                $hashedPassword = password_hash($ogretmen_sifre, PASSWORD_DEFAULT);
                $updateFields[] = "ogretmen_sifre = :ogretmen_sifre";
                $updateParams[':ogretmen_sifre'] = $hashedPassword;
                $changes[] = 'Şifre';
            }
        }

        // Eğer hata varsa döndür
        if (!empty($errors)) {
            echo json_encode([
                'status' => 'error',
                'errors' => $errors
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
        $updateParams[':eski_ogretmen_no'] = $eski_ogretmen_no;
        $query = "UPDATE ogretmenler SET " . implode(', ', $updateFields) . " WHERE ogretmen_no = :eski_ogretmen_no";
        $stmt = $pdo->prepare($query);
        $stmt->execute($updateParams);

        // Sonuç döndür
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Öğretmen başarıyla güncellendi.',
                'changes' => $changes
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(200);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğretmen güncellenirken bir hata oluştu.'
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



function deleteTeacher()
{
    global $pdo;

    try {
        // Önce URL parametrelerini kontrol et
        $ogretmen_no = $_GET['ogretmen_no'] ?? null;

        // Eğer URL parametresi yoksa, body'den al
        if (!$ogretmen_no) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);  // JSON olarak gelen veriyi çözümle
            $ogretmen_no = $data['ogretmen_no'] ?? null;  // JSON içinden Öğretmen numarasını al
        }

        // Öğretmen numarası eksikse hata döndür
        if (empty($ogretmen_no)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğretmen numarası belirtilmeli.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400);  // Bad Request
            return;
        } elseif (!ctype_digit($ogretmen_no) || strlen($ogretmen_no) !== 9) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğretmen numarası 9 haneli ve sadece rakamlardan oluşmalıdır.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400);  // Bad Request
            return;
        }
        // Öğretmen numarasını kullanarak veritabanından silme işlemi yap
        $stmt = $pdo->prepare("DELETE FROM ogretmenler WHERE ogretmen_no = :ogretmen_no");
        $stmt->execute([':ogretmen_no' => $ogretmen_no]);

        // Eğer silme işlemi başarılı olduysa
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Öğretmen başarıyla silindi.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(200);  // OK
        } else {
            // Eğer Öğretmen numarasıyla eşleşen bir kayıt bulunamazsa
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğretmen bulunamadı.'
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

function getTeacherCourses()
{
    global $pdo;

    try {
        $ogretmen_no = $_GET['ogretmen_no'] ?? null;

        // Eğer URL parametresi yoksa, body'den al
        if (!$ogretmen_no) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);  // JSON olarak gelen veriyi çözümle
            $ogretmen_no = $data['ogretmen_no'] ?? null;  // JSON içinden Öğretmen numarasını al
        }

        if (empty($ogretmen_no)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğretmen numarası belirtilmeli.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400);  // Bad Request
            return;
        } elseif (!ctype_digit($ogretmen_no) || strlen($ogretmen_no) !== 9) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğretmen numarası 9 haneli ve sadece rakamlardan oluşmalıdır.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400);  // Bad Request
            return;
        }

        $stmt = $pdo->prepare('SELECT * FROM ogretmenler WHERE ogretmen_no = :ogretmen_no');
        $stmt->bindParam(':ogretmen_no', $ogretmen_no, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($result)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğretmen bulunamadı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400);
            return;
        }

        $chechStudentCoursesStmt = $pdo->prepare('SELECT * FROM ogretmenler WHERE ogretmen_no = :ogretmen_no AND verdigi_dersler IS NOT NULL');
        $chechStudentCoursesStmt->bindParam(':ogretmen_no', $ogretmen_no, PDO::PARAM_STR);
        $chechStudentCoursesStmt->execute();
        $result = $chechStudentCoursesStmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($result)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğretmenin verdiği dersler bulunamadı.'
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

function addTeacherCourse()
{
    global $pdo;

    try {
        // URL'den gelen verileri al
        $ogretmen_no = $_GET['ogretmen_no'] ?? null;
        $dersler = $_GET['dersler'] ?? null;

        // Eğer URL parametresi yoksa, body'den al
        if (!$ogretmen_no) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);  // JSON olarak gelen veriyi çözümle
            $ogretmen_no = $data['ogretmen_no'] ?? null;  // JSON içinden Öğretmen numarasını al
            $dersler = $data['dersler'] ?? null;  // JSON içinden dersleri al
        }

        // Öğretmen numarasının kontrolü
        if (empty($ogretmen_no)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğretmen numarası belirtilmeli.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400);  // Bad Request
            return;
        } elseif (!ctype_digit($ogretmen_no) || strlen($ogretmen_no) !== 9) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğretmen numarası 9 haneli ve sadece rakamlardan oluşmalıdır.'
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
                    'message' => 'Ders adı boş olamaz.'
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

        // Öğretmen numarasına göre Öğretmen kontrolü yap
        $stmt = $pdo->prepare('SELECT * FROM ogretmenler WHERE ogretmen_no = :ogretmen_no');
        $stmt->bindParam(':ogretmen_no', $ogretmen_no, PDO::PARAM_STR);
        $stmt->execute();
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğretmen bulunamadı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(404);  // Not Found
            return;
        }

        // Mevcut dersleri al
        $currentCourses = $student['verdigi_dersler'];  // Mevcut dersler
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
                    'message' => 'Dersler zaten aynı. Değişiklik yapılmadı.'
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
        $stmt = $pdo->prepare("UPDATE ogretmenler SET verdigi_dersler = :verdigi_dersler WHERE ogretmen_no = :ogretmen_no");
        $stmt->bindParam(':verdigi_dersler', $updatedCoursesStr);
        $stmt->bindParam(':ogretmen_no', $ogretmen_no);
        $stmt->execute();

        echo json_encode([
            'status' => 'success',
            'message' => 'Dersler başarıyla güncellendi.',
            'data' => $updatedCoursesStr
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

function updateTeacherCourse()
{
    global $pdo;

    try {
        // URL'den gelen verileri al
        $ogretmen_no = $_GET['ogretmen_no'] ?? null;
        $dersler = $_GET['dersler'] ?? null;

        // Eğer URL parametresi yoksa, body'den al
        if (!$ogretmen_no) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);  // JSON olarak gelen veriyi çözümle
            $ogretmen_no = $data['ogretmen_no'] ?? null;  // JSON içinden Öğretmen numarasını al
            $dersler = $data['dersler'] ?? null;  // JSON içinden dersleri al
        }

        // Öğretmen numarasının kontrolü
        if (empty($ogretmen_no)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğretmen numarası belirtilmeli.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400);  // Bad Request
            return;
        } elseif (!ctype_digit($ogretmen_no) || strlen($ogretmen_no) !== 9) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğretmen numarası 9 haneli ve sadece rakamlardan oluşmalıdır.'
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
                    'message' => 'Ders adı boş olamaz.'
                ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                http_response_code(400);  // Bad Request
                return;
            }
        }

        // Öğretmen numarasına göre Öğretmen kontrolü yap
        $stmt = $pdo->prepare('SELECT * FROM ogretmenler WHERE ogretmen_no = :ogretmen_no');
        $stmt->bindParam(':ogretmen_no', $ogretmen_no, PDO::PARAM_STR);
        $stmt->execute();
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğretmen bulunamadı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(404);  // Not Found
            return;
        }

        // Mevcut dersleri al
        $currentCourses = $student['verdigi_dersler'];  // Mevcut dersler
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
                    'message' => 'Dersler zaten aynı. Değişiklik yapılmadı.'
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
        $stmt = $pdo->prepare("UPDATE ogretmenler SET verdigi_dersler = :verdigi_dersler WHERE ogretmen_no = :ogretmen_no");
        $stmt->bindParam(':verdigi_dersler', $updatedCoursesStr);
        $stmt->bindParam(':ogretmen_no', $ogretmen_no);
        $stmt->execute();

        echo json_encode([
            'status' => 'success',
            'message' => 'Dersler başarıyla güncellendi.',
            'data' => $updatedCoursesStr
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

function deleteTeacherCourse()
{
    global $pdo;

    try {
        $ogretmen_no = $_GET['ogretmen_no'] ?? null;
        $dersler = $_GET['dersler'] ?? null;  // Dersler parametresi (string veya array)

        // Eğer URL parametresi yoksa, body'den al
        if (!$ogretmen_no) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);  // JSON olarak gelen veriyi çözümle
            $ogretmen_no = $data['ogretmen_no'] ?? null;  // JSON içinden Öğretmen numarasını al
            $dersler = $data['dersler'] ?? null;  // JSON içinden dersleri al
        }

        // Öğretmen numarasının kontrolü
        if (empty($ogretmen_no)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğretmen numarası belirtilmeli.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(400);  // Bad Request
            return;
        } elseif (!ctype_digit($ogretmen_no) || strlen($ogretmen_no) !== 9) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğretmen numarası 9 haneli ve sadece rakamlardan oluşmalıdır.'
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
                    'message' => 'Ders adı boş olamaz.'
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

        // Öğretmen numarasına göre Öğretmen kontrolü yap
        $stmt = $pdo->prepare('SELECT * FROM ogretmenler WHERE ogretmen_no = :ogretmen_no');
        $stmt->bindParam(':ogretmen_no', $ogretmen_no, PDO::PARAM_STR);
        $stmt->execute();
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğretmen bulunamadı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(404);  // Not Found
            return;
        }

        // Mevcut dersleri al
        $currentCourses = $student['verdigi_dersler'];  // Mevcut dersler
        if (!empty($currentCourses)) {
            // Mevcut dersleri JSON formatından array'e dönüştür
            $currentCoursesArray = json_decode($currentCourses, true);  // JSON'dan array'e dönüştür

            // Girilen derslerin Öğretmene ait olup olmadığını kontrol et
            foreach ($derslerArray as $ders) {
                if (!in_array($ders, $currentCoursesArray)) {
                    echo json_encode([
                        'status' => 'error',
                        'message' => "$ders dersi öğretmene ait değil."
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
            // Öğretmen zaten ders almamışsa
            echo json_encode([
                'status' => 'error',
                'message' => 'Öğretmenin verdiği dersler bulunamadı.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            http_response_code(404);  // Not Found
            return;
        }

        // Dersleri güncelle
        $stmt = $pdo->prepare("UPDATE ogretmenler SET verdigi_dersler = :verdigi_dersler WHERE ogretmen_no = :ogretmen_no");
        $stmt->bindParam(':verdigi_dersler', $updatedCoursesStr);
        $stmt->bindParam(':ogretmen_no', $ogretmen_no);
        $stmt->execute();

        echo json_encode([
            'status' => 'success',
            'message' => 'Dersler başarıyla güncellendi.',
            'data' => $updatedCoursesStr
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

