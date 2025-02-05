const Toast = Swal.mixin({
  toast: true,
  position: "top-end",
  showConfirmButton: false,
  timer: 3000,
  timerProgressBar: true,
  didOpen: toast => {
    toast.onmouseenter = Swal.stopTimer;
    toast.onmouseleave = Swal.resumeTimer;
  },
});

async function logout(event) {
  const response = await fetch("../../function/logout.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
  });

  const request = await response.json();

  if (request.status === "success") {
    Toast.fire({
      icon: "success",
      title: "Çıkış Başarılı!",
    });
  } else {
    Toast.fire({
      icon: "success",
      title: "Çıkış Başarılı!",
    });
  }

  setTimeout(() => {
    window.location.href = "/";
  }, 3000);
}

var sidebar = document.querySelector(".sidebar");

// Sidebar'a hover yapıldığında accordion menülerin açılmasını sağlayan fonksiyon
function toggleAccordion(event, id) {
  event.preventDefault();
  var accordionMenu = document.getElementById(id);
  var arrow = event.target.querySelector(".arrow") || event.target.parentElement.querySelector(".arrow") || event.target.children[2]; // Ok öğesini seç

  // Menü açık mı kontrol et
  var isOpen = accordionMenu.style.maxHeight;

  if (isOpen) {
    accordionMenu.style.maxHeight = null; // Menü kapat
    arrow.classList.remove("rotate"); // Oku eski haline çevir
  } else {
    accordionMenu.style.maxHeight = accordionMenu.scrollHeight + "px"; // Menü aç
    arrow.classList.add("rotate"); // Oku döndür
  }

  // Diğer menüleri kapat ve okları eski haline getir
  var menus = document.querySelectorAll(".accordion-menu");
  menus.forEach(menu => {
    if (menu.id !== id) {
      menu.style.maxHeight = null; // Kapalı menüyü kapat
      var otherArrow = menu.previousElementSibling.querySelector(".arrow"); // Ok öğesini seç
      if (otherArrow) {
        otherArrow.classList.remove("rotate"); // Diğer okları eski haline çevir
      }
    }
  });
}

// Sidebar üzerine gelindiğinde 'active' sınıfını ekleyin
sidebar.addEventListener("mouseenter", function () {
  sidebar.classList.add("active");
});

// Sidebar'dan ayrıldığında menüleri kapatın ve 'active' sınıfını kaldırın
sidebar.addEventListener("mouseleave", function () {
  sidebar.classList.remove("active");

  // Tüm accordion menüleri kapat
  var menus = document.querySelectorAll(".accordion-menu");
  menus.forEach(menu => {
    menu.style.maxHeight = null;
    var arrow = menu.previousElementSibling.querySelector(".arrow");
    if (arrow) {
      arrow.classList.remove("rotate"); // Okları eski haline çevir
    }
  });
});
// Sayfa tamamen yüklendiğinde font kontrolü yap

function checkFontLoaded() {
  const font = new FontFaceObserver("Font Awesome 6 Free");
  const loader = document.querySelector(".loader-div");

  function hideLoader() {
    loader.style.display = "none";
  }

  function retryLoad() {
    setTimeout(() => {
      font
        .load()
        .then(hideLoader)
        .catch(() => {
          console.error("İconlar yüklenirken tekrar bir sorun oluştu.");
          retryLoad(); // Hata durumunda tekrar dene
        });
    }, 500); // 500 ms sonra yeniden dene
  }

  font
    .load()
    .then(hideLoader)
    .catch(() => {
      console.error("İconlar yüklenirken bir sorun oluştu.");
      retryLoad();
    });
}

window.addEventListener("load", () => {
  checkFontLoaded();
  changeTheme();

  const ogrencilerList = document.getElementById("ogrenciler");
  const ogrenciler = ogrencilerList.getElementsByTagName("li");

  // Eğer öğrenci yoksa, "Öğrenci bulunamadı" mesajını içeren bir <li> ekle
  if (ogrenciler.length === 0) {
    const noResultsItem = document.createElement("li");
    noResultsItem.id = "noResults"; // Kimlik ataması yap
    noResultsItem.textContent = "Öğrenci bulunamadı"; // Mesajı ayarla
    noResultsItem.style.display = "list-item"; // Görünür yap
    ogrencilerList.appendChild(noResultsItem); // Listeye ekle

    const searchInput = document.getElementById("search");
    searchInput.setAttribute("disabled", "disabled"); // Arama girişini devre dışı bırak
  }
});

// ------------------------------------------------------------------------------------------ \\

// Search input element
var searchInput = document.getElementById("search");

searchInput.addEventListener("keyup", function () {
  const filter = this.value.toLowerCase();
  const ogrencilerList = document.getElementById("ogrenciler");
  const ogrenciler = ogrencilerList.getElementsByTagName("li");

  let found = false;
  let noResultsItem = document.getElementById("noResults");

  if (!noResultsItem) {
    noResultsItem = document.createElement("li");
    noResultsItem.id = "noResults";
    noResultsItem.textContent = "Öğrenci bulunamadı";
    noResultsItem.style.display = "none";
    ogrencilerList.appendChild(noResultsItem);
  }

  for (let i = 0; i < ogrenciler.length; i++) {
    const ogrenciText = ogrenciler[i].textContent.toLowerCase(); // Öğrencinin tüm içeriği
    if (ogrenciText.includes(filter)) {
      ogrenciler[i].style.display = "";
      found = true;
    } else {
      ogrenciler[i].style.display = "none";
    }
  }

  if (!found) {
    noResultsItem.style.display = "list-item";
  } else {
    noResultsItem.style.display = "none";
  }
});
// ------------------------------------------------------------------------------------------ \\
// İcon İşlemleri
var öğrenci_no;

function editOgrenci(event, id) {
  const popup = document.getElementsByClassName("editBox")[0];
  popup.classList.add("active");
  öğrenci_no = id;
}

function closeEditBox(event) {
  const popup = document.getElementsByClassName("editBox")[0];
  popup.classList.remove("active");
}

async function showOgrenci(event, id) {
  const response = await fetch("../../../function/admin/ogrenci_bilgi.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ ogrenci_no: id }),
  });

  const result = await response.json();

  const ogrenciDetayBox = document.getElementsByClassName("ogrenciDetay")[0];
  ogrenciDetayBox.classList.add("active");

  const ogrenci_id = result.ogrenci.id;
  const ogrenci_no = result.ogrenci.ogrenci_no;
  const ogrenci_adi = result.ogrenci.ogrenci_adi;
  const ogrenci_soyadi = result.ogrenci.ogrenci_soyadi;
  const ogrenci_eposta = result.ogrenci.ogrenci_eposta;
  const ogrenci_telefon = result.ogrenci.ogrenci_telefon;
  const ogrenci_kayit_tarihi = result.ogrenci.kayit_tarihi;
  const ogrenci_dersler = result.ogrenci.aldigi_dersler;

  document.getElementById("ogrenciNo").textContent = ogrenci_no;
  document.getElementById("ogrenciAdi").textContent = ogrenci_adi;
  document.getElementById("ogrenciSoyad").textContent = ogrenci_soyadi;
  document.getElementById("ogrenciEmail").textContent = ogrenci_eposta;
  document.getElementById("ogrenciTelefon").textContent = ogrenci_telefon;
  document.getElementById("kayitTarihi").textContent = ogrenci_kayit_tarihi;

  // Öğrencilerin dersleri aslında array ama string olarak sakladık bunu arraya çevirip dersleri sırayla eklememiz gerekiyor listeye
  if (ogrenci_dersler !== null) {
    let derslerArray = JSON.parse(ogrenci_dersler);
    let derslerString = derslerArray.join(", ");

    document.getElementById("aldigiDersler").textContent = derslerString;
  } else {
    document.getElementById("aldigiDersler").textContent = "Henüz ders tanımlanmamış.";
  }
}

function closeOgrenciDetay(event) {
  const ogrenciDetayBox = document.getElementsByClassName("ogrenciDetay")[0];
  ogrenciDetayBox.classList.remove("active");
}

function editOgrenciDersleri() {
  window.location.href = "/yonetim/ogrenci/ders-tanımla.php";
}

async function deleteOgrenci(event, id) {
  Swal.fire({
    title: "Öğrenci Sil",
    text: "Öğrenciyi silmek istediğinizden emin misiniz?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Sil",
    cancelButtonText: "İptal",
  }).then(async result => {
    if (result.isConfirmed) {
      const response = await fetch("../../../function/admin/ogrenci_sil.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ ogrenciNo: id }),
      });

      const result = await response.json();

      if (result.status === "success") {
        Toast.fire({
          icon: "success",
          title: "Öğrenci başarıyla silindi!",
        });

        // Öğrenci listesini güncelle
        var ogrencilerList = document.getElementById("ogrenciler");
        var ogrenciler = ogrencilerList.getElementsByTagName("li");
        var öğrenci = event.target.closest("li");

        ogrencilerList.removeChild(öğrenci);

        ogrencilerList = document.getElementById("ogrenciler");
        ogrenciler = ogrencilerList.getElementsByTagName("li");

        // Eğer hiç öğrenci kalmadıysa "Öğrenci bulunamadı" mesajını göster
        if (ogrenciler.length === 0 || ogrenciler.length === null) {
          const noResultsItem = document.createElement("li");
          noResultsItem.id = "noResults"; // Kimlik ataması yap
          noResultsItem.textContent = "Öğrenci bulunamadı"; // Mesajı ayarla
          noResultsItem.style.display = "list-item"; // Görünür yap
          ogrencilerList.appendChild(noResultsItem); // Listeye ekle

          const searchInput = document.getElementById("search");
          searchInput.setAttribute("disabled", "disabled"); // Arama girişini devre dışı bırak
        }
      } else {
        Toast.fire({
          icon: "error",
          title: `${result.message}`,
        });
      }
    }
  });
}

async function updateOgrenci() {
  const form = document.getElementById("ogrenciEditForm");

  if (form.checkValidity()) {
    // Form verilerini seç
    const formData = {
      ogrenciNo: öğrenci_no,
    };

    // Sadece dolu olan alanları formData'ya ekle
    const fields = ["ogrenciAdı", "ogrenciSoyadı", "ogrenciEposta", "ogrenciSifre", "ogrenciPhoneNo"];
    let isAnyFieldFilled = false; // En az bir alanın dolu olup olmadığını kontrol etmek için

    fields.forEach(field => {
      const element = document.getElementById(field);
      if (element && element.value.trim() !== "") {
        formData[field] = element.value.trim();
        isAnyFieldFilled = true; // En az bir alan dolu olduğunda true yap
      }
    });

    // Hiçbir alan doldurulmadıysa uyarı göster ve işlemi durdur
    if (!isAnyFieldFilled) {
      Toast.fire({
        icon: "warning",
        title: "En az bir alanı doldurun!",
      });
      return;
    }

    // Sunucuya gönder
    const response = await fetch("../../../function/admin/ogrenci_güncelle.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(formData),
    });

    const result = await response.json();

    if (result.status === "success") {
      Toast.fire({
        icon: "success",
        title: "Öğrenci başarıyla güncellendi!",
      });

      // Öğrenci listesini güncelle
      var ogrencilerList = document.getElementById("ogrenciler");
      var ogrenciler = ogrencilerList.getElementsByTagName("li");

      for (let i = 0; i < ogrenciler.length; i++) {
        if (ogrenciler[i].no === öğrenci_no) {
          const spanElement = ogrenciler[i].getElementsByTagName("span")[0];
          spanElement.textContent = formData.ogrenciAdı + " " + formData.ogrenciSoyadı;
          break;
        }
      }

      closeEditBox();

      form.reset();
    } else {
      Toast.fire({
        icon: "error",
        title: `${result.message}`,
      });
    }
  } else {
    Toast.fire({
      icon: "warning",
      title: "Lütfen tüm alanları doldurun!",
    });
    form.reportValidity();
  }
}

async function geciciSifreGonder(event) {
  // Geçiçi şifre oluştur
  const temporaryPassword = Math.random().toString(36).slice(-8);

  // Şifreyi güncelle
  const response2 = await fetch("../../../function/admin/ogrenci_güncelle.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ ogrenciNo: öğrenci_no, ogrenciSifre: temporaryPassword }),
  });

  const result2 = await response2.json();

  if (result2.status === "success") {
    Swal.fire({
      icon: "info",
      title: "Geçici şifreniz: " + temporaryPassword,
      confirmButtonText: "Onayla",
      showCancelButton: false,
    });
  } else {
    Toast.fire({
      icon: "error",
      title: `${result2.message}`,
    });
  }
}
// ------------------------------------------------------------------------------------------ \\

function changeTheme() {
  const theme = localStorage.getItem("theme");
  const root = document.querySelector("html");
  const themeButton = document.getElementById("themeButton");

  if (theme === "dark") {
    root.classList.add("dark");
    root.classList.remove("light");
    themeButton.classList.add("fa-sun");
    themeButton.classList.remove("fa-moon");
    localStorage.setItem("theme", "dark"); // Varsayılan olarak 'dark' ayarla
  } else {
    root.classList.add("light");
    root.classList.remove("dark");
    themeButton.classList.add("fa-moon");
    themeButton.classList.remove("fa-sun");
    localStorage.setItem("theme", "light"); // Varsayılan olarak 'light' ayarla
  }
}

function toggleTheme() {
  const theme = localStorage.getItem("theme") === "dark" ? "light" : "dark";
  localStorage.setItem("theme", theme);
  changeTheme(); // Tema değişikliğini hemen uygulamak için çağır
}

function toggleMenu() {
  const menu = document.getElementsByClassName("sidebar")[0];
  menu.classList.toggle("active");
}

// ------------------------------------------------------------------------------------------ \\
