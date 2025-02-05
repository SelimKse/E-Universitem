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

  const ogretmenlerList = document.getElementById("ogretmenler");
  const ogretmenler = ogretmenlerList.getElementsByTagName("li");

  // Eğer öğrenci yoksa, "Öğrenci bulunamadı" mesajını içeren bir <li> ekle
  if (ogretmenler.length === 0) {
    const noResultsItem = document.createElement("li");
    noResultsItem.id = "noResults"; // Kimlik ataması yap
    noResultsItem.textContent = "Öğretmen bulunamadı"; // Mesajı ayarla
    noResultsItem.style.display = "list-item"; // Görünür yap
    ogretmenlerList.appendChild(noResultsItem); // Listeye ekle

    const searchInput = document.getElementById("search");
    searchInput.setAttribute("disabled", "disabled"); // Arama girişini devre dışı bırak
  }
});

// ------------------------------------------------------------------------------------------ \\

// Search input element
var searchInput = document.getElementById("search");

searchInput.addEventListener("keyup", function () {
  const filter = this.value.toLowerCase(); // Arama metnini küçük harfe çevir
  const ogretmenlerList = document.getElementById("ogretmenler");
  const ogretmenler = ogretmenlerList.getElementsByTagName("li");
  let found = false; // Her aramada sonuç olup olmadığını kontrol etmek için bir değişken

  // Eğer daha önce oluşturulmuşsa "Öğrenci bulunamadı" li'sini bulalım
  let noResultsItem = document.getElementById("noResults");

  // Eğer yoksa, yeni bir li oluşturalım
  if (!noResultsItem) {
    noResultsItem = document.createElement("li");
    noResultsItem.id = "noResults";
    noResultsItem.textContent = "Öğrenci bulunamadı";
    noResultsItem.style.display = "none"; // Başlangıçta gizli
    noResultsItem.style.color = "#000"; // Mesaj stilini ayarlayabilirsiniz
    noResultsItem.style.textAlign = "left"; // Ortalayın
    ogretmenlerList.appendChild(noResultsItem); // li öğesini öğrenci listesinin sonuna ekle
  }

  for (let i = 0; i < ogretmenler.length; i++) {
    const spanElement = ogretmenler[i].getElementsByTagName("span")[0];
    const ogretmenAdi = spanElement ? spanElement.textContent || spanElement.innerText : ""; // Öğrenci ismini al veya boş bırak

    // Eğer öğrenci ismi arama metnini içeriyorsa
    if (ogretmenAdi.toLowerCase().includes(filter)) {
      ogretmenler[i].style.display = ""; // Görüntüle
      found = true; // En az bir sonuç bulundu
    } else {
      ogretmenler[i].style.display = "none"; // Gizle
    }
  }

  // Hiçbir sonuç bulunamadıysa "Öğrenci bulunamadı" li öğesini göster, aksi halde gizle
  if (!found) {
    noResultsItem.style.display = "list-item";
  } else {
    noResultsItem.style.display = "none";
  }
});
// ------------------------------------------------------------------------------------------ \\
// İcon İşlemleri
var öğretmen_id;

function editOgretmen(event, id) {
  const popup = document.getElementsByClassName("editBox")[0];
  popup.classList.add("active");
  öğretmen_id = id;
}

async function deleteOgretmen(event, id) {
  Swal.fire({
    title: "Öğretmen Sil",
    text: "Öğretmeni silmek istediğinizden emin misiniz?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Sil",
    cancelButtonText: "İptal",
  }).then(async result => {
    if (result.isConfirmed) {
      const response = await fetch("../../../function/admin/ogretmen_sil.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ ogretmen_id: id }),
      });

      const result = await response.json();

      if (result.status === "success") {
        Toast.fire({
          icon: "success",
          title: "Öğretmen başarıyla silindi!",
        });

        // Öğretmen listesini güncelle
        var ogretmenlerList = document.getElementById("ogretmenler");
        var ogretmenler = ogretmenlerList.getElementsByTagName("li");
        var ogretmen = event.target.closest("li");

        ogretmenlerList.removeChild(ogretmen);

        ogretmenlerList = document.getElementById("ogretmenler");
        ogretmenler = ogretmenlerList.getElementsByTagName("li");

        // Eğer hiç öğrenci kalmadıysa "Öğrenci bulunamadı" mesajını göster
        if (ogretmenler.length === 0 || ogretmenler.length === null) {
          const noResultsItem = document.createElement("li");
          noResultsItem.id = "noResults"; // Kimlik ataması yap
          noResultsItem.textContent = "Öğretmen bulunamadı"; // Mesajı ayarla
          noResultsItem.style.display = "list-item"; // Görünür yap
          ogretmenlerList.appendChild(noResultsItem); // Listeye ekle

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

async function closeEditBox(event) {
  const popup = document.getElementsByClassName("editBox")[0];
  popup.classList.remove("active");
}

async function showOgretmen(event, id) {
  const response = await fetch("../../../function/admin/ogretmen_bilgi.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ ogretmen_no: id }),
  });

  const result = await response.json();

  const ogretmenDetayBox = document.getElementsByClassName("ogretmenDetay")[0];
  ogretmenDetayBox.classList.add("active");

  const ogretmen_no = result.ogretmen.ogretmen_no;
  const ogretmen_adi = result.ogretmen.ogretmen_adi;
  const ogretmen_soyadi = result.ogretmen.ogretmen_soyadi;
  const ogretmen_eposta = result.ogretmen.ogretmen_eposta;
  const ogretmen_telefon = result.ogretmen.ogretmen_telefon;
  const ogretmen_kayit_tarihi = result.ogretmen.kayit_tarihi;
  const ogretmen_dersler = result.ogretmen.verdigi_dersler;

  document.getElementById("ogretmenNo").textContent = ogretmen_no;
  document.getElementById("ogretmenAdi").textContent = ogretmen_adi;
  document.getElementById("ogretmenSoyad").textContent = ogretmen_soyadi;
  document.getElementById("ogretmenEmail").textContent = ogretmen_eposta;
  document.getElementById("ogretmenTelefon").textContent = ogretmen_telefon;
  document.getElementById("kayitTarihi").textContent = ogretmen_kayit_tarihi;

  // Öğrencilerin dersleri aslında array ama string olarak sakladık bunu arraya çevirip dersleri sırayla eklememiz gerekiyor listeye
  if (ogretmen_dersler != null) {
    let derslerArray = JSON.parse(ogretmen_dersler);
    let derslerString = derslerArray.join(", ");

    document.getElementById("verdigiDersler").textContent = derslerString;
  } else {
    document.getElementById("verdigiDersler").textContent = "Ders bilgisi bulunamadı.";
  }
}

function closeOgretmenDetay(event) {
  const ogretmenDetayBox = document.getElementsByClassName("ogretmenDetay")[0];
  ogretmenDetayBox.classList.remove("active");
}

async function updateOgretmen() {
  const form = document.getElementById("ogretmenEditForm");

  if (form.checkValidity()) {
    // Form verilerini seç
    const formData = {
      ogretmenId: öğretmen_id,
    };

    // Sadece dolu olan alanları formData'ya ekle
    const fields = ["ogretmenAdı", "ogretmenSoyadı", "ogretmenEposta", "ogretmenSifre", "ogretmenPhoneNo"];
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
    const response = await fetch("../../../function/admin/ogretmen_güncelle.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(formData),
    });

    const result = await response.json();
    console.log(result);

    if (result.status === "success") {
      Toast.fire({
        icon: "success",
        title: "Öğretmen başarıyla güncellendi!",
      });

      // Öğrenci listesini güncelle
      var ogretmenlerList = document.getElementById("ogretmenler");
      var ogretmenler = ogretmenlerList.getElementsByTagName("li");

      for (let i = 0; i < ogretmenler.length; i++) {
        if (ogretmenler[i].id === öğrenci_id) {
          const spanElement = ogretmenler[i].getElementsByTagName("span")[0];
          spanElement.textContent = formData.ogretmenAdı + " " + formData.ogretmenSoyadı;
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

function editogretmenDersleri() {
  window.location.href = "/yonetim/ogretmen/ders-tanımla.php";
}

async function geciciSifreGonder(event) {
  // Geçiçi şifre oluştur
  const temporaryPassword = Math.random().toString(36).slice(-8);

  // Şifreyi güncelle
  const response2 = await fetch("../../../function/admin/ogretmen_güncelle.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ ogretmenNo: öğretmen_id, ogretmenSifre: temporaryPassword }),
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
