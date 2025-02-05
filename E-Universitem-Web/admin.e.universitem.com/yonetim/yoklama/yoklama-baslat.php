<?php
session_start();
include '../../function/connect_db.php';

if (!isset($_SESSION['username'])) {
    // Oturum yoksa bir JavaScript yönlendirmesi oluştur
    echo '<script>
            alert("Lütfen önce giriş yapın!");
            window.location.href = "/"; // Giriş sayfası
          </script>';
    exit(); // Kodun devamını çalıştırma
}

// Dersleri çekmek için SQL sorgusu
$sql = "SELECT * FROM dersler";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$dersler = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Sınıfları çekmek için SQL sorgusu
$sql = "SELECT * FROM siniflar";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$siniflar = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Öğretmenleri çekmek için SQL sorgusu
$sql = "SELECT * FROM ogretmenler";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$ogretmenler = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>


<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yoklama Başlat - Yönetim Paneli</title>
    <link rel="stylesheet" href="../../public/css/yoklama/yoklama-baslat.css">
    <script src="https://kit.fontawesome.com/3c6e7d6957.js" crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sen:wght@400..800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@5/dark.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
</head>

<body>
    <div class="loader-div">
        <div class="loader"></div>
    </div>

    <header>
        <nav>
            <div class="mobileMenu">
                <i class="fas fa-bars" onclick="toggleMenu()"></i>
            </div>
            <h4>Yönetim Paneli</h4>
            <div class="menu">
                <i class="fa-solid fa-sun" id="themeButton" onclick="toggleTheme(event)"></i>
                <i class="fa-solid fa-arrow-right-from-bracket" onclick="logout(event)"></i>
            </div>
        </nav>
    </header>

    <aside class="sidebar">
        <div class="sidebar-header">
            <button onclick="toggleMenu()">
                <i class="fas fa-times"></i>
            </button>
            <img src="../../assets/img/dashboard.png" alt="Admin">
            <h3>Yönetim Paneli</h3>
        </div>
        <ul class="sidebar-links">
            <h4>
                <span>Bilgi İşlem</span>
                <div class="menu-seperator"></div>
            </h4>
            <li>
                <a href="/dashboard.php">
                    <i class="fas fa-home"></i>
                    <span>Ana Sayfa</span>
                </a>
            </li>
            <li>
                <a href="/ogrenciler.php">
                    <i class="fa-solid fa-users"></i>
                    <span>Öğrenciler</span>
                </a>
            </li>

            <li>
                <a href="/ogretmenler.php">
                    <i class="fa-solid fa-user-tie"></i>
                    <span>Öğretmenler</span>
                </a>
            </li>
            <li>
                <a href="/sınıflar.php">
                    <i class="fa-solid fa-school"></i>
                    <span>Sınıflar</span>
                </a>
            </li>
            <li>
                <a href="/dersler.php">
                    <i class="fa-solid fa-person-chalkboard"></i>
                    <span>Dersler</span>
                </a>
            </li>
            <h4>
                <span>İşlemler</span>
                <div class="menu-seperator"></div>
            </h4>
            <li>
                <a href="" onclick="toggleAccordion(event, 'ogrenciler-menu')">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Öğrenci İşlemleri</span>
                    <i class="arrow fa-solid fa-chevron-down"></i> <!-- Küçük ok -->
                </a>
                <ul id="ogrenciler-menu" class="accordion-menu">
                    <li><a href="/yonetim/ogrenci/ogrenci-ekle.php">Yeni Öğrenci Ekle</a></li>
                    <li><a href="/yonetim/ogrenci/ders-tanımla.php">Ders Tanımla</a></li>
                </ul>
            </li>
            <li>
                <a href="#" onclick="toggleAccordion(event, 'ogretmenler-menu')">
                    <i class="fa-solid fa-user-tie"></i>
                    <span>Öğretmen İşlemleri</span>
                    <i class="arrow fa-solid fa-chevron-down"></i> <!-- Küçük ok -->
                </a>
                <ul id="ogretmenler-menu" class="accordion-menu">
                    <li><a href="/yonetim/ogretmen/ogretmen-ekle.php">Yeni Öğretmen Ekle</a></li>
                    <li><a href="/yonetim/ogretmen/ders-tanımla.php">Ders Tanımla</a></li>
                </ul>
            </li>
            <li>
                <a href="#" onclick="toggleAccordion(event, 'sınıflar-menu')">
                    <i class="fa-solid fa-school"></i>
                    <span>Sınıf İşlemleri</span>
                    <i class="arrow fa-solid fa-chevron-down"></i> <!-- Küçük ok -->
                </a>
                <ul id="sınıflar-menu" class="accordion-menu">
                    <li><a href="/yonetim/sınıf/sınıf-ekle.php">Yeni Sınıf Ekle</a></li>
                </ul>
            </li>
            <li>
                <a href="#" onclick="toggleAccordion(event, 'dersler-menu')">
                    <i class="fa-solid fa-person-chalkboard"></i>
                    <span>Ders İşlemleri</span>
                    <i class="arrow fa-solid fa-chevron-down"></i> <!-- Küçük ok -->
                </a>
                <ul id="dersler-menu" class="accordion-menu">
                    <li><a href="/yonetim/ders/ders-ekle.php">Yeni Ders Ekle</a></li>
                    <li><a href="/yonetim/ders/ogretmen-tanımla.php">Öğretmen Tanımla</a></li>
                    <li><a href="/yonetim/ders/ogrenci-tanımla.php">Öğrenci Tanımla</a></li>
                </ul>
            </li>

            <h4>
                <span>Yoklama</span>
                <div class="menu-seperator"></div>
            </h4>
            <li>
                <a href="/yonetim/yoklama/yoklamalar.php">
                    <i class="fa-solid fa-person-chalkboard"></i>
                    <span>Yoklamalar</span>
                </a>
            </li>
            <li>
                <a href="/yonetim/yoklama/aktif-yoklamalar.php">
                    <i class="fa-solid fa-square-poll-vertical"></i>
                    <span>Aktif Yoklamalar</span>
                </a>
            </li>
            <li>
                <a href="/yonetim/yoklama/yoklama-baslat.php">
                    <i class="fa-solid fa-file-circle-plus"></i>
                    <span>Yoklama Başlat</span>
                </a>
            </li>
        </ul>
    </aside>

    <div class="container">
        <div class="box">
            <div class="buttons">
                <button type="reset" id="cancelButton" class="button cancel"
                    onclick="yoklamaReset(event)">Vazgeç</button>
                <button type="submit" id="baslatButton" class="button submit"
                    onclick="yoklamaBaslat(event)">Başlat</button>
            </div>
            <div class="formBox">
                <h2>Yoklama Başlat</h2>
                <form method="POST" id="yoklamaBaslatForm" onsubmit="return yoklamaBaslat(event);">
                    <div class="inputBox">
                        <div class="ders">
                            <select name="ders_sec" id="ders_sec" required>
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
                        <div class="sınıf">
                            <select name="sinif_sec" id="sinif_sec" required>
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
                        <div class="ogretmen">
                            <select name="ogretmen_sec" id="ogretmen_sec" required>
                                <?php if (!empty($ogretmenler)): ?>
                                    <option value="null">Lütfen Öğretmen Seçiniz!</option>
                                    <?php foreach ($ogretmenler as $ogretmen): ?>
                                        <option value="<?php echo $ogretmen['ogretmen_no']; ?>">
                                            <?php echo $ogretmen['ogretmen_adi'] . " " . $ogretmen['ogretmen_soyadi']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="">Veri bulunamadı</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="baslamaTarihi">
                            <input type="datetime-local" name="baslama_tarihi" id="baslama_tarihi" required>
                        </div>
                    </div>
                </form>
                <div class="rules">
                    <div class="text">
                        <ul>
                            <h3>Yoklama Başlatma Kuralları</h3>
                            <p>Yoklama başlatmak için ders seçimi yapılması zorunludur. Bu alan boş bırakılamaz.</p>
                            <p>Yoklama tarihi ve saati girilmelidir. Bu bilgiler eksiksiz olmalıdır.</p>
                            <p>Ders adı ve diğer bilgiler yalnızca harfler (A-Z, a-z), sayılar ve boşluk içerebilir;
                                özel karakterler kullanılamaz.</p>
                            <p>Yoklama tarihi, geçmiş bir tarih olamaz. Bugün veya gelecekteki bir tarih seçilmelidir.
                            </p>
                            <p>Yönetici yoklama başlatırken bir onay mesajı veya hata mesajı gösterilecektir.</p>
                            <p>Yönetici, "Vazgeç" butonuna tıkladığında yapılan işlemler kaydedilmeyecektir.</p>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>

<script src="../../public/js/yoklama/yoklama-baslat.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fontfaceobserver/2.1.0/fontfaceobserver.standalone.js"></script>
<script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script>

</html>