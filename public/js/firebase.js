// Firebase CDN'lerini head kısmına ekleyin
// <script src="https://www.gstatic.com/firebasejs/10.8.0/firebase-app-compat.js"></script>
// <script src="https://www.gstatic.com/firebasejs/10.8.0/firebase-messaging-compat.js"></script>

const firebaseConfig = {
    apiKey: "AIzaSyA-LYjbCZ96OrGf4hphK5LddAkfLIxNmj4",
    authDomain: "akilliajanda-ff6c6.firebaseapp.com",
    projectId: "akilliajanda-ff6c6",
    storageBucket: "akilliajanda-ff6c6.firebasestorage.app",
    messagingSenderId: "975843729349",
    appId: "1:975843729349:web:05c484e42aa75aab44b5ac"
};

firebase.initializeApp(firebaseConfig);

let swRegistration = null;

// Service Worker'ı kaydet
async function registerServiceWorker() {
    try {
        if ('serviceWorker' in navigator) {
            swRegistration = await navigator.serviceWorker.register('/firebase-messaging-sw.js');
            await navigator.serviceWorker.ready;
            return swRegistration;
        }
        throw new Error('Service Worker desteklenmiyor');
    } catch (error) {
        console.error('Service Worker kaydı başarısız:', error);
        throw error;
    }
}

// Messaging nesnesini al
const messaging = firebase.messaging();

async function requestNotificationPermission() {
    try {
        // Bildirim iznini kontrol et
        if (!('Notification' in window)) {
            return;
        }

        // Mevcut izin durumunu kontrol et
        if (Notification.permission === 'denied') {
            await Swal.fire({
                title: 'Bildirim İzni Gerekli',
                text: 'Bildirimlere izin vermek için tarayıcı ayarlarından izinleri değiştirmeniz gerekiyor.',
                icon: 'warning',
                confirmButtonText: 'Tamam',
                confirmButtonColor: '#3b5998'
            });
            return;
        }

        // Önce Service Worker'ı kaydet
        await registerServiceWorker();

        if (Notification.permission === 'default') {
            const result = await Swal.fire({
                title: 'Bildirim İzni',
                text: 'AkilliAjanda size önemli hatırlatmalar göndermek istiyor. Bildirimlere izin vermek ister misiniz?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'İzin Ver',
                cancelButtonText: 'Reddet',
                confirmButtonColor: '#3b5998',
                cancelButtonColor: '#d33'
            });

            if (result.isConfirmed) {
                const permission = await Notification.requestPermission();
                if (permission === 'granted') {
                    await getAndSendToken();
                    await Swal.fire({
                        title: 'Başarılı!',
                        text: 'Artık bildirimleri alabileceksiniz.',
                        icon: 'success',
                        confirmButtonColor: '#3b5998'
                    });
                }
            } else {
                await Swal.fire({
                    title: 'Bildirimler Kapalı',
                    text: 'Bildirimleri daha sonra ayarlardan aktif edebilirsiniz.',
                    icon: 'info',
                    confirmButtonColor: '#3b5998'
                });
            }
        } else if (Notification.permission === 'granted') {
            await getAndSendToken();
        }
    } catch (error) {
        console.error('Bildirim sistemi hatası:', error);
        await Swal.fire({
            title: 'Hata!',
            text: 'Bildirim izni alınırken bir hata oluştu.',
            icon: 'error',
            confirmButtonColor: '#3b5998'
        });
    }
}

async function getAndSendToken() {
    try {
        if (!swRegistration) {
            throw new Error('Service Worker kaydı bulunamadı');
        }

        const currentToken = await messaging.getToken({
            vapidKey: 'BEWO8fTseISKO7UFzaPtJ3Ovypb6Eknz8CVkpKz6i-It_TGVuL0IkbB3aOzMzoHv-gF1MYiT2Di2K4CBQ-s3WnE',
            serviceWorkerRegistration: swRegistration
        });
        
        if (currentToken) {
            await sendTokenToServer(currentToken);
        } else {
            throw new Error('FCM token alınamadı');
        }
    } catch (err) {
        console.error('Token alma hatası:', err);
        await Swal.fire({
            title: 'Hata!',
            text: 'Bildirim sistemi için gerekli token alınamadı.',
            icon: 'error',
            confirmButtonColor: '#3b5998'
        });
    }
}

async function sendTokenToServer(token) {
    try {
        const response = await fetch('/save-device-token', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ device_token: token })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        await response.json();
    } catch (error) {
        console.error('Token kaydetme hatası:', error);
        await Swal.fire({
            title: 'Hata!',
            text: 'Bildirim sistemi için token kaydedilemedi.',
            icon: 'error',
            confirmButtonColor: '#3b5998'
        });
    }
}

// Ön planda bildirim alma
messaging.onMessage((payload) => {
    Swal.fire({
        title: payload.notification.title,
        text: payload.notification.body,
        icon: 'info',
        confirmButtonColor: '#3b5998',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 5000,
        timerProgressBar: true
    });
});

// Token silme fonksiyonu
async function deleteToken() {
    try {
        // Mevcut token'ı al
        const currentToken = await messaging.getToken();
        
        if (!currentToken) {
            return true;
        }

        // FCM token'ı sil
        await messaging.deleteToken();

        // Backend'den token'ı sil
        const response = await fetch('/delete-device-token', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ device_token: currentToken })
        });

        if (!response.ok) {
            throw new Error('Token silinemedi');
        }

        return true;
    } catch (error) {
        console.error('Token silme hatası:', error);
        return false;
    }
}

// Sayfa yüklendiğinde çalıştır
document.addEventListener('DOMContentLoaded', () => {
    requestNotificationPermission();

    // Çıkış formunu bul ve submit olayını yakala
    const logoutForm = document.getElementById('logout-form');
    if (logoutForm) {
        logoutForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Önce token'ı sil
            await deleteToken();
            
            // Sonra formu gönder
            logoutForm.submit();
        });
    }
}); 