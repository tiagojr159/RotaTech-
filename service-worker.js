const CACHE_NAME = "rotatech-arcoverde-v3";
const OFFLINE_URL = "index.php";

const ASSETS_TO_CACHE = [
  "index.php",
  "login.php",
  "home.php",
  "programacao.php",
  "restaurantes.php",
  "album.php",
  "roteiro.php",
  "grupos.php",
  "perfil.php",
  "assets/css/style.css",
  "assets/js/app.js",
  "assets/img/logo-rotatech.svg",
  "assets/img/logo-saojoao.svg",
  "assets/img/hero-fogueira.svg"
];

self.addEventListener("install", (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(ASSETS_TO_CACHE)).then(() => self.skipWaiting())
  );
});

self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(
        keys.map((key) => {
          if (key !== CACHE_NAME) return caches.delete(key);
          return null;
        })
      )
    ).then(() => self.clients.claim())
  );
});

self.addEventListener("fetch", (event) => {
  if (event.request.method !== "GET") return;

  event.respondWith(
    caches.match(event.request).then((cached) => {
      if (cached) return cached;
      return fetch(event.request)
        .then((response) => {
          const copy = response.clone();
          caches.open(CACHE_NAME).then((cache) => cache.put(event.request, copy));
          return response;
        })
        .catch(() => caches.match(OFFLINE_URL));
    })
  );
});
