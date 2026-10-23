importScripts('https://www.gstatic.com/firebasejs/9.1.1/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.1.1/firebase-messaging-compat.js');

self.addEventListener('push', function(event) {
    if (!event.data) return;
    try {
        const data = event.data.json();
        const title = data.notification ? data.notification.title : (data.data ? data.data.title : 'Njoftim i Ri');
        const body = data.notification ? data.notification.body : (data.data ? data.data.body : '');

        const options = {
            body: body,
            icon: '/favicon.ico',
            badge: '/favicon.ico',
            data: data.data || {}
        };

        event.waitUntil(
            self.registration.showNotification(title, options)
        );
    } catch (e) {
        console.error('Service Worker push error:', e);
    }
});

self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    event.waitUntil(
        clients.openWindow('/admin/bookings')
    );
});
