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

function toogleMenu() {
  const menu = document.querySelector(".sidebar");
  menu.classList.toggle("active");
}

// ------------------------------------------------------------------------------------------ \\
