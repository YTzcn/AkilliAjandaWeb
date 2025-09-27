# AkilliAjandaWeb Proje Raporu

## 1. Tanıtım

### Projenin Amacı ve Kapsamı
AkilliAjandaWeb, kullanıcıların görev ve toplantılarını dijital ortamda kolayca yönetebilmesini sağlamak, zamanı daha verimli planlamalarına yardımcı olmak ve üretkenliği artırmak amacıyla geliştirilmiştir. Yoğun gündeme sahip kullanıcılar için planlama sürecini kolaylaştırmak, unutulan görevlerin önüne geçmek ve yapay zekâ desteğiyle görev oluşturma/düzenlemeyi kolaylaştırmak temel hedeflerdendir. Proje, modern web teknolojileri ve Laravel framework'ü kullanılarak geliştirilmiştir. Hem web hem de mobil platformlarda çalışacak şekilde tasarlanmıştır.

- **Kapsam:**
  - Etkinlik ve görev yönetimi (doğal dil ile ekleme/düzenleme)
  - Google Takvim ile iki yönlü senkronizasyon
  - Bildirim ve mesajlaşma
  - Kullanıcı yönetimi ve profil işlemleri
  - Yapay zeka destekli asistan (LLM)
  - Gelişmiş arayüz ve kullanıcı deneyimi (web ve mobil)
  - **Mobil uygulama özel:**
    - React Native ile modern, responsive ve platform bağımsız arayüz (iOS/Android)
    - Gerçek zamanlı bildirimler (Pusher, Firebase)
    - Güvenli oturum ve veri yönetimi (JWT, OAuth2)
    - Mobilde Google Takvim ile çift yönlü entegrasyon

### Sorun ve Hedef Analizi
Günümüzde iş yükü ve zaman yönetimi ihtiyacı artmakta, dijital takvim ve görev yönetim araçlarına olan ihtiyaç da yükselmektedir. Mevcut sistemler genellikle manuel işlem ağırlıklı çalışmakta, görev/etkinlik ekleme, silme, güncelleme işlemleri elle yapılmakta ve bu da zaman ve dikkat gerektirmektedir. Ayrıca mevcut sistemler doğal dil ile girilen ifadeleri yorumlayıp işlem yapamamaktadır.

- **Sorunlar:**
  - Farklı takvim servisleriyle entegrasyon eksikliği
  - Manuel işlem yükü ve unutulan görevler
  - Doğal dil desteği ve yapay zekâ entegrasyonunun olmaması
  - Bildirim ve görev yönetiminin yetersizliği
- **Hedefler:**
  - Görev/etkinlik işlemlerini doğal dil ile yapabilme (Agentic AI)
  - Kullanıcı üretkenliğini artırmak, zamanı verimli kullanmak
  - Google Calendar'a alternatif, daha kullanıcı dostu ve kişiselleştirilebilir bir deneyim sunmak
  - Web ve mobilde entegre, çift yönlü takvim/görev yönetimi

---

## 2. Planlama

### Gant Diyagramı (Burada Gant diyagramı eklenecek)
- Proje Analizi: 1 hafta
- Tasarım: 1 hafta
- Geliştirme: 3 hafta
- Test: 1 hafta
- Dağıtım ve Bakım: Sürekli

### Ekip Yapısı (Burada ekip şeması eklenecek)
- Proje Yöneticisi: Proje planlaması ve koordinasyon
- Backend Geliştirici: API, servis ve veri tabanı geliştirme
- Frontend Geliştirici: Arayüz ve kullanıcı deneyimi
- Test Uzmanı: Otomatik ve manuel testler
- Dokümantasyon Sorumlusu: Teknik ve kullanıcı dokümantasyonu
- **Mobil uygulama özel:**
  - 2 React Native geliştirici, 1 backend geliştirici, 1 test uzmanı
  - Mobil test cihazları (iOS/Android)

### Kaynaklar
- **İnsan:** 5 kişi
- **Donanım:** Geliştirme bilgisayarları, test cihazları
- **Yazılım:** Laravel, PostgreSQL, Google API
- **Ek:** Google Cloud Console, GitHub, Composer, NPM, React Native (mobil)

---

## 3. Çözümleme

### Kullanıcı Hikayeleri (User Story)
1. Görevlerimi doğal bir dille yazıp sisteme eklemek, silmek, güncellemek istiyorum; böylece görev eklerken zaman kaybetmeden kolayca planlama yapabilirim.
2. Yapay zekâ destekli asistanı kullanarak haftalık görev özetimi almak istiyorum; böylece önceliklerimi netleştirip zamanımı daha etkili yönetebilirim.
3. Mevcut takvimimi akıllı asistan aracılığıyla kolayca düzenleyebilmek ve bu düzenlemeleri mevcut takvimimle eşitleyebilmek istiyorum; böylece tüm takvim bilgilerim güncel ve senkronize kalabilsin.

### Fonksiyonel Gereksinimler
- Kullanıcı yönetimi ve kimlik doğrulama (JWT/token, e-posta doğrulama, şifre sıfırlama)
- Etkinlik ve görev yönetimi (CRUD, detay görüntüleme, öncelik, tamamlanma)
- Cihaz yönetimi (bildirim token kaydı/silme)
- Google Takvim entegrasyonu (OAuth, bağlantı durumu, içe/dışa aktarma, senkronizasyon, bağlantı kaldırma)
- Akıllı asistana prompt gönderme ve doğal dil ile takvim/görev yönetimi
- API endpoint'lerinde toplu veri çekme, filtreleme, anlamlı hata mesajları
- Arka planda çalışan servisler (bildirim, senkronizasyon)
- Web ve mobil istemciler için API desteği
- **Mobil uygulama özel:**
  - Gerçek zamanlı bildirimler (Pusher, Firebase)
  - Mobilde Google Takvim ile çift yönlü senkronizasyon
  - Responsive ve sade arayüz, offline/online senkronizasyon yönetimi

### Fonksiyonel Olmayan Gereksinimler
- Kimlik doğrulama işlemlerinde rate limit/throttle
- Yetkisiz erişimlerin engellenmesi (middleware)
- Hataların loglanması
- Açık ve anlaşılır kod yapısı, fonksiyon isimlendirmeleri
- Servis ve repository katmanları ile modüler yapı
- API ve web arayüzlerinin ayrı yönetimi
- Çoklu platform desteği (web ve mobil)
- **Mobil uygulama özel:**
  - Güvenli depolama, offline veri yönetimi, hızlı başlatma

### Mevcut Sistem ve Eksiklikler
- Sadece tek yönlü veri aktarımı
- Kısıtlı bildirim ve görev yönetimi
- Harici takvimlerle zayıf entegrasyon
- Doğal dil ve yapay zekâ desteği yok

### AkilliAjanda'nın Çözümü
- İki yönlü Google Takvim entegrasyonu
- Gelişmiş görev ve etkinlik yönetimi (doğal dil ile)
- Bildirim, mesajlaşma ve LLM desteği
- Web ve mobilde entegre, modern arayüz
- **Mobil uygulama özel:**
  - Anlık bildirimler ve gerçek zamanlı güncellemeler
  - Mobilde sade ve hızlı kullanıcı deneyimi
  - Google Takvim ile tam uyumlu, offline/online çalışma

### Use Case Diyagramı (Burada Use Case diyagramı eklenecek)
- Kullanıcı: Etkinlik ekler, düzenler, siler, Google ile senkronize eder, görev yönetir, mesaj gönderir, bildirim alır, doğal dil ile asistanı kullanır.
- Sistem: Google ile OAuth, etkinlik/görev CRUD, bildirim, mesajlaşma, LLM entegrasyonu, mobil API desteği.
- **Mobil uygulama özel:**
  - Mobilde login, register, dashboard, calendar, task, profile, settings, notification ekranları

### Sınıf Diyagramı (Burada Sınıf diyagramı eklenecek)
- User, Event, Task, Message, Notification, UserDevices modelleri
- GoogleCalendarService, CalendarSyncService, MessageService, TaskService, NotificationService
- Repository katmanı (ör. EventRepository, TaskRepository)
- **Mobil uygulama özel:**
  - AuthService, TaskService, EventService, GoogleCalendarService, PusherService, CalendarStore (MobX)

### Veri Modeli ve ERD (Burada ERD diyagramı eklenecek)
- User (id, name, email, password, google_token, ...)
- Event (id, user_id, title, start_date, end_date, google_event_id, synced_to_google, ...)
- Task (id, user_id, title, due_date, status, priority, ...)
- Message, Notification, UserDevices tabloları
- İlişkiler: User 1-N Event, User 1-N Task, User 1-N Message, User 1-N Notification
- **Mobil uygulama özel:**
  - Firebase/Firestore veya backend ile ilişkili SQL/NoSQL veri modeli

### UML Diyagramları (Burada UML diyagramları eklenecek)
- Sınıf, use case, aktivite ve iş akış diyagramları kodda ve dokümantasyonda mantıksal olarak mevcut.
- İş akışları: Etkinlik ekleme, Google ile senkronizasyon, görev tamamlama, bildirim gönderme, doğal dil ile görev yönetimi
- **Mobil uygulama özel:**
  - Mobilde login, görev ekleme, bildirim alma, Google ile senkronizasyon iş akışları

### Arayüzler
- Dashboard: Genel bakış, özetler
- Profil: Kullanıcı bilgileri, Google bağlantı yönetimi, şifre değiştirme, hesap silme
- Takvim Senkronizasyonu: Google ile bağlantı, içe/dışa aktarım, bağlantı durumu
- Mesajlar: Mesaj gönderme, filtreleme
- Bildirimler: Okuma, silme, istatistikler
- Görevler: CRUD, durum ve öncelik yönetimi
- Akıllı Asistan: Doğal dil ile görev/etkinlik yönetimi, haftalık özet
- Mobil arayüz: React Native ile görev ve takvim yönetimi, asistan, bildirimler
- **Mobil uygulama özel:**
  - Responsive ve sade arayüz, erişilebilirlik, offline/online çalışma, canlı bildirimler

---

## 4. Tasarım

### Mimari Akış (Burada mimari akış diyagramı eklenecek)
- Katmanlar: Controller, Service, Repository, Model, View (Blade), Mobil API
- Entegrasyon: Google Takvim için özel servis ve controller, LLM/AI servisleri, mobil API
- Web ve mobil istemciler için ayrı endpoint ve arayüzler
- **Mobil uygulama özel:**
  - Katmanlı mimari (Presentation, Business Logic, Data, Integration)
  - React Native bileşenleri, MobX store, Axios ile API iletişimi
  - Google Takvim ve bildirim servisleriyle modüler entegrasyon

### Arabirimler ve Testler
- Her modül için blade şablonları (ör. calendar/sync.blade.php)
- Form validasyonları, hata yönetimi, kullanıcıya anlık geri bildirim
- Otomatik testler için phpunit.xml, manuel test senaryoları
- Test kapsamı: Kullanıcı işlemleri, Google entegrasyonu, bildirimler, mesajlar, doğal dil ile görev yönetimi
- **Mobil uygulama özel:**
  - Jest, React Native Testing Library ile birim ve entegrasyon testleri
  - Kullanıcı kabul testleri (UAT), erişilebilirlik ve kullanılabilirlik testleri

### Modüller
- Kullanıcı yönetimi
- Etkinlik yönetimi (CRUD, Google senkronizasyonu, doğal dil ile ekleme)
- Görev yönetimi (CRUD, durum/öncelik, doğal dil ile ekleme)
- Mesajlaşma ve bildirimler
- Takvim entegrasyonu (Google, ileride Notion)
- Akıllı asistan (LLM tabanlı, haftalık özet, doğal dil işleme)
- Mobil uygulama desteği (React Native)
- **Mobil uygulama özel:**
  - AuthService, TaskService, EventService, GoogleCalendarService, PusherService, CalendarStore

---

## 5. Gerçekleştirme

### Kullanılan Teknolojiler
- Backend: Laravel, PHP 8+, Eloquent ORM
- Frontend: Blade, Bootstrap
- Veritabanı: Postgresql
- Entegrasyon: Google API (Calendar), LLM (Gemini,openai,openrouter), Firebase, Pusher
- Mobil: React Native, Axios, Firebase, Pusher
- Yardımcılar: Composer, NPM, Git, Swagger
- **Mobil uygulama özel:**
  - TypeScript/JavaScript, Node.js (backend), MobX, VSCode, Postman

### Neden Bu Teknolojiler?
- Laravel: Modern, güvenli, sürdürülebilir, topluluk desteği yüksek
- Eloquent: Kolay veri modelleme ve ilişkiler
- Google API: Güçlü ve yaygın takvim entegrasyonu
- React Native: Çapraz platform mobil desteği
- LLM/AI: Doğal dil işleme ve akıllı asistan
- **Mobil uygulama özel:**
  - Platform bağımsızlık, hızlı geliştirme, kolay entegrasyon, geniş topluluk

### Veritabanı Mimarisi
- Migration dosyaları ile şema yönetimi
- Eloquent ile ilişkisel veri modeli
- Token ve hassas veriler için güvenli saklama
- **Mobil uygulama özel:**
  - Firebase/Firestore veya backend ile ilişkili SQL/NoSQL veri modeli

### Standartlar ve Kod Gözden Geçirme
- PSR standartları, fonksiyon ve sınıf açıklamaları
- Exception handling ve loglama
- Kod gözden geçirme süreçleri (pull request, code review)
- **Mobil uygulama özel:**
  - Airbnb/Google JS/TS standartları, Prettier, ESLint, otomatik testler

---

## 6. Test

### Test Planı (Burada test planı diyagramı/akışı eklenecek)
- Birim testler: Model, servis ve controller seviyesinde
- Manuel testler: Formlar, arayüz, Google entegrasyonu, mobil API
- Doğrulama: Başarı/başarısız mesajları, loglar, kullanıcı geri bildirimi
- **Mobil uygulama özel:**
  - Jest, React Native Testing Library ile birim ve entegrasyon testleri
  - Kullanıcı kabul testleri (UAT), test ortamı kurulumu, hata takibi

### Kullanılan Test Araçları
- PHPUnit: Otomatik testler
- Laravel test yardımcıları
- Manuel testler için test senaryoları
- **Mobil uygulama özel:**
  - Jest, React Native Testing Library, CI/CD pipeline'ında otomatik testler

### Test Kapsamı ve Süreçleri
- Kullanıcı kayıt/giriş işlemleri
- Etkinlik ve görev CRUD
- Google Takvim bağlantısı ve senkronizasyonu
- Bildirim ve mesajlaşma
- Doğal dil ile görev/etkinlik yönetimi
- Hata yönetimi ve loglama
- **Mobil uygulama özel:**
  - Mobilde offline/online senkronizasyon, bildirim testleri, kullanıcı hikayeleri bazında testler

---
    
## 8. Sonuç

### Değerlendirme
- Proje, modern ajanda uygulamalarının ötesinde, iki yönlü takvim entegrasyonu, doğal dil ile görev yönetimi ve modüler mimarisiyle öne çıkıyor.
- Güçlü yönler: Güvenlik, sürdürülebilirlik, genişletilebilirlik, kullanıcı dostu arayüz, açık kaynak kod, mobil ve web desteği, yapay zekâ entegrasyonu
- Eksikler: Notion entegrasyonu ve Google Tasks tam entegrasyonu henüz yok, otomatik test kapsamı artırılabilir
- **Mobil uygulama özel:**
  - Hızlı, güvenli, entegre ve kullanıcı odaklı mobil deneyim
  - Anlık bildirimler, offline/online çalışma, sade ve erişilebilir arayüz
  - Kısıtlar: Sadece mobil platformlar, Google hesabı gerekliliği

### Avantajlar ve Dezavantajlar
- Avantajlar:
  - Modüler yapı ve servis/repository pattern
  - Güçlü entegrasyon ve API desteği
  - Açık kaynak, kolay bakım ve genişletilebilirlik
  - Modern ve kullanıcı dostu arayüz (web ve mobil)
  - Doğal dil ile görev/etkinlik yönetimi
  - **Mobil uygulama özel:**
    - Gerçek zamanlı bildirimler, offline/online senkronizasyon, hızlı ve sade arayüz
- Dezavantajlar:
  - Bazı entegrasyonlar eksik (Notion, Google Tasks)
  - Daha fazla otomatik test ve CI/CD entegrasyonu eklenebilir
  - **Mobil uygulama özel:**
    - Yüksek entegrasyon bağımlılığı, internet gereksinimi

### Gelecek Geliştirmeler
- Notion entegrasyonu
- Google Tasks tam desteği
- Daha fazla otomasyon, kullanıcı geri bildirimi ve test
- Mobil uygulama fonksiyonlarının genişletilmesi
- **Mobil uygulama özel:**
  - Diğer takvim servisleriyle entegrasyon, offline mod, gelişmiş bildirim seçenekleri

---


## Ek: Risk Analizi, Güvenlik, DevOps, Yasal/Eti̇k, API Yönetimi

### Risk Analizi (Burada risk matrisi/grafiği eklenecek)
- Google API değişiklikleri ve erişim kısıtlamaları
- Kullanıcı veri güvenliği ve gizliliği
- Entegrasyon hataları ve veri kaybı
- Sürüm güncellemeleriyle uyumsuzluk
- Mobil ve webde farklı platform sorunları
- **Mobil uygulama özel:**
  - Google entegrasyonunda yetki kaybı, offline/online veri tutarsızlığı, mobil cihaz uyumluluğu

### Güvenlik Önlemleri
- Token ve hassas verilerin güvenli saklanması
- Middleware ile erişim kontrolü
- Loglama ve hata yönetimi
- Kullanıcı verisi gizliliği ve GDPR uyumluluğu
- **Mobil uygulama özel:**
  - JWT ile güvenli oturum, OAuth2 ile Google entegrasyonu, şifrelerin hashlenmesi, rol tabanlı erişim kontrolü

### Sürdürülebilirlik
- Modüler kod yapısı ve servis/repository pattern
- Açık kaynak lisans ve topluluk desteği
- Kolay bakım ve güncelleme süreçleri
- Web ve mobilde ortak API kullanımı
- **Mobil uygulama özel:**
  - Kodun modüler ve dokümante olması, otomatik testler ve CI/CD ile sürekli entegrasyon

### DevOps ve CI/CD
- Sürüm kontrolü (Git)
- CI/CD entegrasyonu için altyapı hazır 
- **Mobil uygulama özel:**
  - GitHub Actions, Bitrise, Fastlane, otomatik test ve dağıtım pipeline'ı

### Yasal ve Etik Hususlar
- Kullanıcı verisi gizliliği ve açık rıza
- Açık kaynak lisans kullanımı
- Yasal yükümlülükler ve etik kurallar
- **Mobil uygulama özel:**
  - KVKK/GDPR uyumluluğu, kullanıcıdan açık rıza alınması, üçüncü parti kütüphane lisansları

### API Yönetimi
- RESTful API mimarisi
- JSON veri formatı
- Dış sistemlerle kolay entegrasyon
- API anahtarı ve erişim yönetimi
- Web ve mobil istemciler için ayrı endpoint yönetimi
- **Mobil uygulama özel:**
  - Modüler API altyapısı, güvenli anahtar yönetimi, gelecekte eklenebilecek entegrasyonlar için esneklik

### Kullanıcı Deneyimi (UX)
- Modern ve responsive arayüz (web ve mobil)
- Hata ve başarı mesajları
- Kullanıcı geri bildirim mekanizmaları 
- Doğal dil ile görev/etkinlik yönetimi ve haftalık özet
- **Mobil uygulama özel:**
  - Kullanıcıya anlık bildirimler, erişilebilirlik (renk kontrastı, büyük butonlar), uygulama içi değerlendirme ve A/B testleri

---

Bu rapor, AkilliAjandaWeb projesinin isterler.txt, sunum.txt ve SRS_SRD_AkilliAjandaMobil.md gereksinimlerine göre detaylı analizini sunar. Grafik, diyagram ve tabloları eklemek için uygun alanlar belirtilmiştir. Daha fazla teknik detay veya örnek istenirse ayrıca eklenebilir. 