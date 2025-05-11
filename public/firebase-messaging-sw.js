// Give the service worker access to Firebase Messaging.
// Note that you can only use Firebase Messaging here. Other Firebase libraries
// are not available in the service worker.
importScripts('https://www.gstatic.com/firebasejs/10.8.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.8.0/firebase-messaging-compat.js');

// Initialize the Firebase app in the service worker by passing in
// your app's Firebase config object.
firebase.initializeApp({
    apiKey: "AIzaSyA-LYjbCZ96OrGf4hphK5LddAkfLIxNmj4",
    authDomain: "akilliajanda-ff6c6.firebaseapp.com",
    projectId: "akilliajanda-ff6c6",
    storageBucket: "akilliajanda-ff6c6.firebasestorage.app",
    messagingSenderId: "975843729349",
    appId: "1:975843729349:web:05c484e42aa75aab44b5ac"
});

// Retrieve an instance of Firebase Messaging so that it can handle background messages.
const messaging = firebase.messaging();

// Handle background messages
messaging.onBackgroundMessage((payload) => {
    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: '/favicon.ico',
        badge: '/favicon.ico',
        tag: 'notification-1'
    };

    return self.registration.showNotification(notificationTitle, notificationOptions);
}); 