// Firebase CDN'lerini head kısmına ekleyin
// <script src="https://www.gstatic.com/firebasejs/10.8.0/firebase-app-compat.js"></script>
// <script src="https://www.gstatic.com/firebasejs/10.8.0/firebase-messaging-compat.js"></script>

const firebaseConfig = {
    apiKey: "AIzaSyA-LYjbCZ96OrGf4hphK5LddAkfLIxNmj4",
    authDomain: "akilliajanda-ff6c6.firebaseapp.com",
    projectId: "akilliajanda-ff6c6",
    storageBucket: "akilliajanda-ff6c6.firebasestorage.app",
    messagingSenderId: "975843729349",
    appId: "1:975843729349:web:05c484e42aa75aab44b5ac",
    measurementId: "G-K3MWCHCVM6"
  };

firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();

async function requestNotificationPermission() {
    try {
        const permission = await Notification.requestPermission();
        if (permission === 'granted') {
            await getAndSendToken();
        } else {
            console.log('Bildirim izni reddedildi');
        }
    } catch (error) {
        console.error('Bildirim izni hatası:', error);
    }
}

async function getAndSendToken() {
    try {
        const currentToken = await messaging.getToken({
            vapidKey: 'BEWO8fTseISKO7UFzaPtJ3Ovypb6Eknz8CVkpKz6i-It_TGVuL0IkbB3aOzMzoHv-gF1MYiT2Di2K4CBQ-s3WnE'
        });
        
        if (currentToken) {
            await sendTokenToServer(currentToken);
        } else {
            console.log('Token alınamadı');
        }
    } catch (err) {
        console.log('Token alma hatası:', err);
    }
}

async function sendTokenToServer(token) {
    try {
        const response = await fetch('/api/save-device-token', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ device_token: token })
        });
        
        const data = await response.json();
        console.log('Token kaydedildi:', data);
    } catch (error) {
        console.error('Token kaydetme hatası:', error);
    }
}

// Ön planda bildirim alma
messaging.onMessage((payload) => {
    console.log('Bildirim alındı:', payload);
    const notification = new Notification(payload.notification.title, {
        body: payload.notification.body,
        icon: '/favicon.ico'
    });
});

// Sayfanız yüklendiğinde çağırın
document.addEventListener('DOMContentLoaded', () => {
    requestNotificationPermission();
}); 