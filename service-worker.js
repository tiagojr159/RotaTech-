const CACHE_NAME = "rotatech-arcoverde-v18";

const ASSETS_TO_CACHE = [
  "manifest.php?v=1.0.8",
  "logomarca.png",
  "icon.png",
  "noite_arcoverde.jpeg",
  "assets/css/style.css?v=1.0.17",
  "assets/js/app.js?v=1.0.10",
  "assets/img/logo-rotatech.svg",
  "assets/img/logo-saojoao.svg"
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

  const requestUrl = new URL(event.request.url);
  const isSameOrigin = requestUrl.origin === self.location.origin;
  const isPhpRequest = requestUrl.pathname.endsWith(".php");
  const isNavigationRequest = event.request.mode === "navigate";

  if (isNavigationRequest || (isSameOrigin && isPhpRequest)) {
    event.respondWith(fetch(event.request));
    return;
  }

  event.respondWith(
    caches.match(event.request).then((cached) => {
      if (cached) return cached;
      return fetch(event.request)
        .then((response) => {
          if (!response.ok || response.type !== "basic") {
            return response;
          }

          const copy = response.clone();
          caches.open(CACHE_NAME).then((cache) => cache.put(event.request, copy));
          return response;
        });
    })
  );
});
