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

window.addEventListener("load", () => {
  checkFontLoaded();
  changeTheme();
});

function checkFontLoaded() {
  const font = new FontFaceObserver("Material Symbols Outlined");
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
          retryLoad(); // Hata durumunda tekrar dene
        });
    }, 500); // 500 ms sonra yeniden dene
  }

  font
    .load()
    .then(hideLoader)
    .catch(() => {
      retryLoad();
    });
}

function changeTheme() {
  const theme = localStorage.getItem("theme") || "dark"; // Varsayılan olarak 'dark' temasını kullan
  const root = document.querySelector("html");

  if (theme === "dark") {
    root.classList.add("dark");
    root.classList.remove("light");
    localStorage.setItem("theme", "dark"); // Varsayılan olarak 'dark' ayarla
    document.getElementById("header-logo").src = "../../assets/logo-beyaz.png";
  } else {
    root.classList.add("light");
    root.classList.remove("dark");
    localStorage.setItem("theme", "light"); // Varsayılan olarak 'light' ayarla
    document.getElementById("header-logo").src = "../../assets/logo-siyah.png";
  }
}

function toggleTheme() {
  const theme = localStorage.getItem("theme") === "dark" ? "light" : "dark";
  console.log(theme);
  localStorage.setItem("theme", theme);
  changeTheme(); // Tema değişikliğini hemen uygulamak için çağır
}

// ------------------------------------------------------------------------------------------ \\

let sortDirections = {};

function sortList(type, content) {
  const container = document.getElementById(content); // Hedef listeyi al
  const items = Array.from(container.querySelectorAll(`.${content.includes("top") ? "top-lesson-item" : "last-lesson-item"}`));

  // Eğer öğe yoksa işlemi sonlandır
  if (!items.length) return;

  // Sıralama yönünü belirle
  if (!sortDirections[content]) sortDirections[content] = {};
  sortDirections[content][type] = !sortDirections[content][type];

  // Liste öğelerini sıralama
  items.sort((a, b) => {
    let aValue, bValue;

    if (type === "name") {
      // Ders adı için sıralama
      aValue = a.querySelector(".top-lesson-name, .last-lesson-name").textContent.trim().toLowerCase();
      bValue = b.querySelector(".top-lesson-name, .last-lesson-name").textContent.trim().toLowerCase();
    } else if (type === "count") {
      // Katılım sayısı için sıralama
      aValue = parseInt(a.querySelector(".top-lesson-count").textContent.trim());
      bValue = parseInt(b.querySelector(".top-lesson-count").textContent.trim());
    } else if (type === "date") {
      // Tarih için sıralama
      aValue = new Date(a.querySelector(".last-lesson-date").textContent.trim());
      bValue = new Date(b.querySelector(".last-lesson-date").textContent.trim());
    }

    return sortDirections[content][type] ? (aValue > bValue ? 1 : -1) : aValue < bValue ? 1 : -1;
  });

  // Sıralanmış öğeleri listeye tekrar ekle
  items.forEach(item => container.appendChild(item));

  // Okları güncelle
  updateArrows(type, content);
}

function updateArrows(type, content) {
  console.log(type);
  // İlgili header'daki ok işaretlerini sıfırla
  const headers = document.querySelectorAll(`.${content.includes("top") ? "top-lesson-header" : "last-lesson-header"} .sort-arrow`);
  headers.forEach(header => (header.textContent = "")); // Önce okları temizle

  // Seçilen tipe göre oka yön ekle
  const arrow = document.querySelector(`.${content.includes("top") ? "top-lesson-header" : "last-lesson-header"} .header-${type} .sort-arrow`);
  console.log(arrow);
  if (arrow) {
    arrow.textContent = sortDirections[content][type] ? "↑" : "↓"; // Yön belirle
  }
}

// ------------------------------------------------------------------------------------------ \\

function toogleMenu() {
  const menu = document.querySelector(".sidebar");
  menu.classList.toggle("active");
}

function logout() {
  window.location.href = "/cikis-yap.php";
}

async function veriler() {
  const session = await fetch("../../function/get-session.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
  });

  const sessionResponse = await session.json();
  if (!sessionResponse.status) {
    window.location.href = "https://e-universitem.com/auth/ogrenci/giris.php";
    return;
  }

  const request = await fetch("../../function/yoklamalar.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      ogrenci_no: sessionResponse.data.ogrenci_no,
    }),
  });

  const response = await request.json();

  // gelen yoklama verilerinin baslatilma tarihine gore islemler yapilacak

  // bugun girilen yoklamalar
  const bugun = response.data.filter(item => new Date(item.baslatilma_tarihi).toDateString() === new Date().toDateString()) || 0;

  // haftalik yoklamalar
  const haftalik = response.data.filter(item => new Date(item.baslatilma_tarihi).getDay() === new Date().getDay()) || 0;

  // aylik yoklamalar
  const aylik = response.data.filter(item => new Date(item.baslatilma_tarihi).getMonth() === new Date().getMonth()) || 0;

  // toplam yoklama sayisi
  const toplam = response.data.length || 0;

  // dynamic olarak yoklama verilerini goster
  document.getElementById("gunluk-yoklama-sayi").textContent = bugun.length;
  document.getElementById("haftalik-yoklama-sayi").textContent = haftalik.length;
  document.getElementById("aylik-yoklama-sayi").textContent = aylik.length;
  document.getElementById("toplam-yoklama-sayi").textContent = toplam;

  // yoklamalar arasında ders_id si en fazla olan 5 dersin ders_idlerini döndür ve en çok bulunan dersten kaç tane olduğunu döndür ders_id ve kaç tane olduğu lazım obje olarak döndür
  // response.data içinde yoklamalar verisi var
  const yoklamalar = response.data; // [{ ders_id: 1 }, { ders_id: 2 }, ...]

  const dersFrekanslari = yoklamalar
    .map(item => item.ders_id) // Ders ID'leri alın
    .reduce((acc, curr) => {
      // Her ders ID'sinin frekansını hesaplayın
      acc[curr] = (acc[curr] || 0) + 1; // Eğer acc içinde var ise artır, yoksa 1 olarak başlat
      return acc;
    }, {});

  // Frekansı en yüksek 5 ders_id'yi almak için
  const top5Dersler = Object.entries(dersFrekanslari) // [{ ders_id: frekans }, ...]
    .sort((a, b) => b[1] - a[1]) // Frekansa göre büyükten küçüğe sırala
    .slice(0, 5) // İlk 5 öğeyi al
    .map(item => ({ ders_id: item[0], count: item[1] })); // [{ ders_id: 1, count: 3 }, ...]

  // Ders isimlerini almak için API çağrısı
  const dersIds = top5Dersler.map(item => item.ders_id);

  // API'ye ders adlarını almak için istek yapalım
  const dersIsimleriResponse = await fetch("../../function/ders_isimleri.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ dersIds: dersIds }),
  });

  const dersIsimleri = await dersIsimleriResponse.json();

  // Ders adlarını ve count değerlerini eşleştir
  const dersAdlariVeCount = top5Dersler.map(item => {
    const dersAdi = dersIsimleri.find(ders => ders.ders_id == item.ders_id)?.ders_adi || "Bilinmiyor";
    return { ders_adi: dersAdi, count: item.count };
  });

  // Sonuçları göster
  if (dersAdlariVeCount.length == 0) {
    document.getElementById("top-lesson-list").innerHTML = `
      <div class="top-lesson-item">
        <div class="top-lesson-name">Ders bulunamadı</div>
      </div>
    `;
  }

  dersAdlariVeCount.forEach((item, index) => {
    const item_div = document.getElementById(`top-lesson-list`);
    const div = document.createElement("div");
    div.innerHTML = `
      <div class="top-lesson-item">
        <div class="top-lesson-name">${item.ders_adi}</div>
        <div class="top-lesson-count">${item.count}</div>
      </div>
    `;
    item_div.appendChild(div);
  });

  // EN SON KATILDIĞI 5 YOKLAMAYI GÖSTER
  const sonYoklamalar = response.data.sort((a, b) => new Date(b.baslatilma_tarihi) - new Date(a.baslatilma_tarihi)).slice(0, 5);

  if (sonYoklamalar.length == 0) {
    document.getElementById("last-lesson-list").innerHTML = `
      <div class="last-lesson-item">
        <div class="last-lesson-name">Yoklama bulunamadı</div>
      </div>
    `;
  }

  sonYoklamalar.forEach((item, index) => {
    const item_div = document.getElementById(`last-lesson-list`);
    const div = document.createElement("div");
    div.innerHTML = `
      <div class="last-lesson-item">
        <div class="last-lesson-name">${item.ders_adi}</div>
        <div class="last-lesson-date">${new Date(item.baslatilma_tarihi).toLocaleString()}</div>
      </div>
    `;
    item_div.appendChild(div);
  });

  // Bildirimleri göster
  const bildirimler = await fetch("../../function/bildirimler.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      ogrenci_no: sessionResponse.data.ogrenci_no,
    }),
  });

  const bildirimlerResponse = await bildirimler.json();
  console.log(bildirimlerResponse);

  if (bildirimlerResponse.data.length == 0) {
    document.getElementById("bildirim-list").innerHTML = `
      <div class="bildirim-item">
        <div class="bildirim-content">Bildirim bulunamadı</div>
      </div>
    `;
  }

  bildirimlerResponse.data.forEach((item, index) => {
    const item_div = document.getElementById(`notification-list`);
    const div = document.createElement("div");
    div.innerHTML = `
      <div class="notification-item">
        <div class="notification-content">${item.bildirim}</div>
      </div>
    `;
    item_div.appendChild(div);
  });
}

veriler();
