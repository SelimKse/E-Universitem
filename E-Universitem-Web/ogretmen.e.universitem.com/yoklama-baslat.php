<?php
include "function/connect_db.php";

session_start();

if (!isset($_SESSION['ogretmen_data']['ogretmen_no'])) {
    header("Location: https://e-universitem.com/auth/ogretmen/giris.php");
    die();
}

$ogretmen_no = $_SESSION['ogretmen_data']['ogretmen_no'];
$ogretmen_adi = $_SESSION['ogretmen_data']['adi'];
$ogretmen_soyadi = $_SESSION['ogretmen_data']['soyadi'];
$ogretmen_email = $_SESSION['ogretmen_data']['email'];

// ogretmenin verilerini al
$sql = "SELECT * FROM ogretmenler WHERE ogretmen_no = :ogretmen_no";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':ogretmen_no', $ogretmen_no, PDO::PARAM_INT);
$stmt->execute();

// Sonuçları alın
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// ogretmenin verdigi bir ders var mı kontrol et
if ($row && isset($row['verdigi_dersler'])) {
    $ders_adlari = json_decode($row['verdigi_dersler'], true); // ['Matematik', 'Fizik', 'Kimya']
}

// eğer ders yoksa swal ile uyarı ver
if (empty($ders_adlari)) {
    echo "<script>
        alert('Verdiğiniz bir ders bulunmadığı için yoklama başlatamazsınız.');
        window.location.href = '/verdigin-dersler.php';
    </script>";
    die();
}

// Ders detaylarını çek
if (!empty($ders_adlari)) {
    $placeholders = implode(',', array_fill(0, count($ders_adlari), '?'));
    $sql = "SELECT ders_adi AS name, ders_id AS value FROM dersler WHERE ders_adi IN ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($ders_adlari);
    $dersler = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Öğretmen Paneli</title>
    <link rel="stylesheet" href="/public/css/yoklama-baslat.css">
    <script src="https://kit.fontawesome.com/3c6e7d6957.js" crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sen:wght@400..800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@5/dark.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <link rel="shortcut icon" href="/assets/icon-beyaz.png" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script>
</head>

<body>

    <div class="loader-div">
        <div class="loader"></div>
    </div>

    <header>
        <div class="header-menu">
            <button class="menu-button" id="menu-button" onclick="toogleMenu()">
                <span class="material-symbols-outlined"> menu </span>
            </button>
        </div>
        <div class="header-logo">
            <img id="header-logo" src="/assets/logo-beyaz.png" alt="logo" />
        </div>
        <div class="header-buttons">
            <button class="logout-button" onclick="logout()">
                <span class="material-symbols-outlined"> logout </span>
            </button>
        </div>
    </header>

    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="/assets/icon-beyaz.png" alt="logo" />
            <h2>Öğretmen Menü</h2>
            <button>
                <span class="material-symbols-outlined" onclick="toogleMenu()"> close </span>
            </button>
        </div>
        <ul class="sidebar-links">
            <h4>
                <span>Bilgi İşlem</span>
                <div class="menu-separator"></div>
            </h4>
            <li>
                <a href="/">
                    <span class="material-symbols-outlined"> home </span>Ana Sayfa</a>
            </li>
            <li>
                <a href="/Verilen Dersler.php">
                    <span class="material-symbols-outlined"> script </span>Verilen Dersler</a>
            </li>
            <h4>
                <span>Yoklama</span>
                <div class="menu-separator"></div>
            </h4>
            <li>
                <a href="/aldigin-yoklamalar.php"><span class="material-symbols-outlined"> overview </span>Aldığın
                    Yoklamalar</a>
            </li>
            <li>
                <a href="/yoklama-baslat.php"><span class="material-symbols-outlined"> add_task </span>Yoklama
                    Başlat</a>
            </li>
            <h4>
                <span>Hesap</span>
                <div class="menu-separator"></div>
            </h4>
            <li>
                <a href="/hesabim.php"><span class="material-symbols-outlined"> account_circle </span>Hesabım</a>
            </li>
            <li>
                <a href="/ayarlar.php"><span class="material-symbols-outlined"> settings </span>Ayarlar</a>
            </li>
            <li>
                <a href="/cikis-yap.php"><span class="material-symbols-outlined"> logout </span>Çıkış Yap</a>
            </li>
        </ul>
        <div class="user-account">
            <div class="user-profile">
                <img src="/assets/ogretmen.png" alt="ogretmen Profil" />
                <div class="user-detail">
                    <h3><?php echo htmlspecialchars($ogretmen_adi . " " . $ogretmen_soyadi); ?></h3>
                    <span><?php echo htmlspecialchars($ogretmen_email); ?></span>
                </div>
            </div>
        </div>
    </aside>

    <div class="container">
        <div class="content">
            <div class="header">
                <h1>Yoklama Başlat</h1>
            </div>
            <div class="main-content">
                <div class="yoklama-baslat-content">
                    <div class="yoklama-baslat-content-body">
                        <div class="yoklama-ders">
                            <label for="ders_sec">Ders:</label>
                            <select name="ders_sec" id="yoklama-ders" required>
                                <?php if (!empty($dersler)): ?>
                                    <option value=null>Lütfen Ders Seçiniz!</option>
                                    <?php foreach ($dersler as $ders): ?>
                                        <option value="<?php echo $ders['ders_id']; ?>">
                                            <?php echo $ders['ders_adi']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="">Veri bulunamadı</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="yoklama-sinif">
                            <label for="sinif_sec">Sınıf:</label>
                            <select name="sinif_sec" id="yoklama-sinif" required>
                                <?php if (!empty($siniflar)): ?>
                                    <option value="null">Lütfen Sınıf Seçiniz!</option>
                                    <?php foreach ($siniflar as $sinif): ?>
                                        <option value="<?php echo $sinif['sinif_id']; ?>">
                                            <?php echo $sinif['sinif_adi']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="">Veri bulunamadı</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="yoklama-ogretmen">
                            <label for="yoklama-ogretmen-adi">Öğretmen:</label>
                            <input type="text" id="ogretmen"
                                value="<?php echo htmlspecialchars($ogretmen_adi . " " . $ogretmen_soyadi); ?>"
                                data-ogretmen_no="<?php echo htmlspecialchars($ogretmen_no); ?>" readonly />
                        </div>
                        <div class="yoklama-baslangic-tarihi">
                            <label for="yoklama-baslangic-tarihi">Başlangıç Tarihi:</label>
                            <input type="datetime-local" id="baslangic-tarihi" required />
                        </div>
                    </div>
                    <div class="yoklama-baslat-content-footer">
                        <button class="yoklama-baslat-button" onclick="yoklamaBaslat(event)">Yoklama Başlat</button>
                    </div>
                </div>

            </div>
        </div>
        <div class="yoklama-bilgi-popup">
            <div class="yoklama-bilgi-popup-content">
                <div class="popup-header">
                    <h2>Yoklama Bilgileri</h2>
                    <button class="popup-close" onclick="showPopup()">
                        <span class="material-symbols-outlined"> close </span>
                    </button>
                </div>
                <div class="popup-body">
                    <div class="popup-content">
                        <div class="yoklama-qr">
                            <div id="qrcode">
                                <img class="qr-code-img" alt="QR Kod" />
                            </div>
                        </div>
                        <div class="row-content">
                            <div class="row">
                                <span>Ders:</span>
                                <span id="yoklama-ders-adi"></span>
                            </div>
                            <div class="row">
                                <span>Sınıf:</span>
                                <span id="yoklama-sinif-adi"></span>
                            </div>
                            <div class="row">
                                <span>Öğretmen:</span>
                                <span id="yoklama-ogretmen-adi"></span>
                            </div>
                            <div class="row">
                                <span>Başlangıç Tarihi:</span>
                                <span id="yoklama-baslangic-tarihi"></span>
                            </div>
                            <div class="row">
                                <span>Bitiş Tarihi:</span>
                                <span id="yoklama-bitis-tarihi"></span>
                                <span id="yoklama-bitis-kalan-sure">Kalan Süre: <span id="timer">10:00</span></span>
                            </div>
                            <div class="row">
                                <span>Yoklama Kodu:</span>
                                <span id="yoklama-kodu"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

<script src="/public/js/yoklama-baslat.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fontfaceobserver/2.1.0/fontfaceobserver.standalone.js"></script>

</html>