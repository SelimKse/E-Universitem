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
});

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
  console.log(theme);
  localStorage.setItem("theme", theme);
  changeTheme(); // Tema değişikliğini hemen uygulamak için çağır
}

// ------------------------------------------------------------------------------------------ \\

function toggleMenu() {
  const menu = document.getElementsByClassName("sidebar")[0];
  menu.classList.toggle("active");
}

// ------------------------------------------------------------------------------------------ \\

async function yoklamaBaslat(event) {
  event.preventDefault();

  const ders = document.getElementById("ders_sec").value;
  const sınıf = document.getElementById("sinif_sec").value;
  const ogretmen = document.getElementById("ogretmen_sec").value;
  const tarih = document.getElementById("baslama_tarihi").value;

  if (ders === "null" || sınıf === "null" || ogretmen === "null" || tarih === "") {
    Toast.fire({
      icon: "error",
      title: "Lütfen tüm alanları doldurun!",
    });
    return;
  }

  // tarihe 10 dk ekle
  const date = new Date(tarih);
  date.setMinutes(date.getMinutes() + 10);
  date.setHours(date.getHours() + 3);
  const bitis_tarihi = date.toISOString().slice(0, 19).replace("T", " ");

  const response = await fetch("../../function/admin/yoklama_baslat.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      ders: ders,
      sinif: sınıf,
      ogretmen: ogretmen,
      baslangic: tarih,
      bitis: bitis_tarihi,
    }),
  });

  const result = await response.json();

  if (result.status === "success") {
    Toast.fire({
      icon: "success",
      title: "Yoklama başarıyla başlatıldı!",
    });

    yoklamaQR();
  } else {
    Toast.fire({
      icon: "error",
      title: result.message,
    });
  }
}

function yoklamaReset(event) {
  const form = document.getElementById("yoklamaBaslatForm");
  form.reset();
}

// ------------------------------------------------------------------------------------------ \\

async function yoklamaQR(link) {
  function showQRCode() {
    // SweetAlert içinde QR kodu göstermek
    Swal.fire({
      title: "QR Kodunuz",
      html: '<div id="qrcode"></div>', // QR kodu için boş bir div ekliyoruz
      didOpen: () => {
        const qrCodeDiv = document.getElementById("qrcode");
        qrCodeDiv.style.textAlign = "center";
        qrCodeDiv.style.display = "flex";
        qrCodeDiv.style.justifyContent = "center";
        qrCodeDiv.style.alignItems = "center";
        qrCodeDiv.style.margin = "20px";
        new QRCode(link, {
          text: qrData, // Sabit değişkenden gelen metin
          width: 200,
          height: 200,
        });
      },
      confirmButtonText: "Kapat",
    });
  }

  showQRCode();
}
