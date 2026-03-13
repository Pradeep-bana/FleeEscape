self.addEventListener('install', (event) => {
  console.log('Service Worker installed for FleeEscape!');
});

self.addEventListener('fetch', (event) => {
  // Optional: Add caching here if needed
});
