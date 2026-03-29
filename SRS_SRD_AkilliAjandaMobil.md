## Akıllı Ajanda Web Uygulaması 
---

## 1. Tanıtım

### 1.1. Proje Amacı ve Özeti
Akıllı Ajanda Web Uygulaması, kullanıcıların etkinlik ve görevlerini tek bir yerden yönetebildiği, Google Calendar ile entegre çalışabilen, gerçek zamanlı güncellemeler ve bildirimler sunan, yapay zeka destekli bir ajanda ve görev yönetim sistemidir. Kullanıcılar, web arayüzü üzerinden etkinlik ve görev oluşturabilir, güncelleyebilir, silebilir; takvimlerini görüntüleyebilir, özet raporlar alabilir ve LLM tabanlı sohbet asistanı üzerinden doğal dil ile ajandalarını yönetebilir.

### 1.2. Kapsam
- Etkinlik ve görev yönetimi (oluşturma, güncelleme, silme, tamamlama)
- Google Calendar entegrasyonu (iki yönlü senkronizasyon, içe/dışa aktarma)
- Gerçek zamanlı güncellemeler (Pusher ile kanal bazlı bildirimler)
- Yapay zeka destekli sohbet asistanı (LLM ile doğal dil komutları)
- Bildirim sistemi (in-app, push altyapısına hazırlık)
- Kullanıcı ve profil yönetimi (kayıt, giriş, e-posta doğrulama, şifre sıfırlama)

### 1.3. Sorun ve Hedef Analizi
- **Sorun:** Farklı takvim ve görev uygulamaları arasında dağılmış veriler, senkronizasyon problemleri, zayıf entegrasyonlar ve kullanıcıya yük olan karmaşık ara yüzler.
- **Hedef:** Tüm takvim ve görev işlemlerini tek bir web uygulamasında toplamak, Google Calendar ile tam uyumlu, gerçek zamanlı, güvenli ve kullanıcı dostu bir ajanda deneyimi sunmak; yapay zeka desteğiyle doğal dilde ajanda yönetimini mümkün kılmak.

### 1.4. Vision & Scope
- Kullanıcıların günlük, haftalık ve aylık planlarını web üzerinden tek ekrandan yönetebilmesi
- Mevcut harici servislerle (özellikle Google Calendar) güçlü entegrasyon
- Genişletilebilir, modüler ve sürdürülebilir bir backend mimarisi
- İleride mobil ve diğer istemcilere hizmet verebilecek ölçeklenebilir API katmanı

---

## 2. Planlama

### 2.1. Kaynaklar
- **Sunucu tarafı:** PHP 8+, Laravel 12, PostgreSQL veritabanı
- **İstemci tarafı:** Blade template’ler, Tailwind CSS, Alpine.js, gerekli yerlerde Bootstrap bileşenleri
- **Yardımcı servisler:** Pusher (broadcasting), Firebase (push altyapısı için), Google API (Calendar), OpenAI / Gemini / OpenRouter (LLM)
- **Geliştirme araçları:** Git, Composer, NPM, Vite, Postman, IDE (Cursor/VSCode)

---

## 3. Çözümleme

### 3.1. Mevcut Sistem Analizi
- Geleneksel ajanda ve takvim uygulamalarında genellikle tek yönlü entegrasyon, sınırlı görev yönetimi ve zayıf gerçek zamanlı bildirim yetenekleri bulunur.
- Kullanıcılar, Google Calendar, görev listeleri ve not uygulamaları gibi farklı araçlar arasında veri taşımak zorunda kalır; bu da veri kaybına ve yönetim zorluğuna yol açar.
- Önerilen web sistemi, bu eksiklikleri tek merkezde toplanmış görev/etkinlik yönetimi, güçlü Google Calendar entegrasyonu, gerçek zamanlı güncellemeler ve LLM tabanlı akıllı asistan ile gidermeyi hedefler.

### 3.2. Önerilen Sistem
- **İşlevsel Model:**
  - Kullanıcı ile sistem arasındaki temel etkileşimler; "Kayıt Ol / Giriş Yap", "Etkinlik Oluşturma / Güncelleme / Silme", "Görev Oluşturma / Tamamlama", "Takvim Görüntüleme", "Google Calendar ile Senkronizasyon", "Sohbet Asistanına Komut Verme" gibi use case’ler üzerinden tanımlanır.
  - Her bir use case için, kullanıcının başlattığı aksiyon (örneğin yeni etkinlik ekleme), sistemin bunu veritabanı ve Google Calendar üzerinde nasıl işlediği ve hata durumlarında (örneğin Google erişim hatası) kullanıcıya nasıl geri bildirim verildiği metinsel senaryolarla açıklanır.
- **Bilgi Sistemleri / Nesneler:**
  - Temel modeller: User, Event, Task, Notification, Message (LLM mesajları), UserDevice, Category vb.
  - User, sisteme giriş yapan hesapları temsil eder; Event, takvimdeki zaman bazlı etkinliklerdir; Task, son tarih ve öncelik bilgisi olan görevlerdir; Notification, kullanıcıya gösterilen sistem bildirimlerini; Message, kullanıcı–LLM etkileşim kayıtlarını; Category ise görevleri kategorize etmeyi sağlar.
  - User ile Event, Task, Notification ve Message arasında bire çok ilişkiler tanımlanır; görev–kategori ilişkisi için çoktan çoğa bir yapı (category_task pivot tablosu) öngörülür.
- **UML Açıklamaları:**
  - Tipik bir istek akışında, istemciden (web tarayıcısı) gelen HTTP isteği Laravel routing katmanına gelir, ilgili Controller’a yönlendirilir; Controller iş kurallarını ilgili Service sınıflarına devreder; servisler Eloquent modelleri üzerinden PostgreSQL’e erişir ve gerektiğinde Google Calendar veya LLM sağlayıcılarına API çağrısı yapar; sonuç, Blade şablonları veya JSON API cevapları ile istemciye döner.

---

## 4. Tasarım

### 4.1. Mimari
- Uygulama, katmanlı bir mimari yaklaşımıyla tasarlanır:
  - **Sunum katmanı:** Blade view’lar, Tailwind/Bootstrap ile stillendirilmiş arayüzler, Alpine.js ile hafif etkileşimler.
  - **Uygulama katmanı:** Laravel Controller’ları, Request/Response nesneleri, doğrulama katmanı.
  - **İş mantığı katmanı:** Service sınıfları (EventService, TaskService, LLMService, GoogleCalendarService, NotificationService vb.).
  - **Veri katmanı:** Eloquent modeller, repository benzeri sorgu katmanı, PostgreSQL veritabanı.
  - **Entegrasyon katmanı:** Google API istemcileri, LLM sağlayıcı adapter’ları, Pusher kanalları.
- Katmanlar arası geçişler net arayüzlerle sınırlandırılır; Controller’lar sadece koordinasyon yapar, ağır iş mantığı servislerde tutulur.

### 4.2. Web Arayüzleri
- **Dashboard:** Günlük/haftalık özet, yaklaşan etkinlik ve görevler, kritik bildirimler ve kısa LLM önerileri gösterilir.
- **Takvim (Calendar) Sayfası:** Gün/hafta/ay görünümleri, etkinliklerin görsel olarak gösterimi, tıklayarak etkinlik detayı ve düzenleme imkânı.
- **Görev (Tasks) Sayfası:** Görev listesi, durum (bekliyor, devam ediyor, tamamlandı), öncelik ve tarih filtreleri; hızlı tamamlama ve düzenleme aksiyonları.
- **Sohbet (Chat) Sayfası:** LLM ile etkileşim alanı; kullanıcı doğal dilde komut verir (ör. "yarın 15.00’e toplantı ekle"), sistem sonucu gösterip gerekli kayıtları oluşturur.
- **Profil ve Ayarlar:** Kullanıcı bilgileri, parola değişimi, bildirim tercihleri, Google Calendar bağlantısını yönetme.
- **Bildirimler:** Okunmamış bildirimlerin listesi, tek tek veya toplu okundu işaretleme, önemli uyarıların vurgulanması.

---

## 5. Gerçekleştirme

### 5.1. Teknolojiler
- **Backend:** Laravel 12, PHP 8+, Eloquent ORM, Laravel Sanctum (API auth), Queue & Jobs (arka plan işlemleri).
- **Veritabanı:** PostgreSQL, ilişkisel şema (users, events, tasks, notifications, messages, user_devices, categories, category_task vb.).
- **Frontend:** Blade, Tailwind CSS, Alpine.js, gerektiği yerlerde Bootstrap bileşenleri.
- **Gerçek Zamanlı:** Pusher kanalları ile broadcast event’leri (ör. görev/etkinlik güncelleme).
- **LLM:** LLMService aracılığıyla Gemini, OpenAI ve OpenRouter sağlayıcıları; sağlayıcılar arasında geçiş için yapılandırılabilir bir provider mantığı.
- **Diğer:** L5-Swagger ile API dokümantasyonu, Google Calendar entegrasyonu için `spatie/laravel-google-calendar` ve Google API istemcileri.

### 5.2. Veri Tabanı Tasarımı
- Users tablosu, kimlik, iletişim ve Google entegrasyon durumunu tutar (ör. google_token, google_calendar_connected).
- Events tablosu, user_id bağlantısı ile kullanıcıya bağlı etkinlikleri, başlık, açıklama, başlangıç/bitiş tarihleri, all_day ve google_event_id gibi alanları barındırır.
- Tasks tablosu, user_id ile kullanıcıya bağlı görevleri, due_date, priority, status ve isteğe bağlı Google senkronizasyon alanlarını içerir.
- Notifications, Messages ve UserDevices tabloları, bildirim, LLM mesaj geçmişi ve cihaz token’larını yönetir.
- Category ve category_task ile görevlerin kategorize edilmesi sağlanır.

### 5.3. LLM ve Entegrasyon Tasarımı
- LLMService, gelen kullanıcı mesajını alır, uygun sağlayıcıyı (Gemini/OpenAI/OpenRouter) seçer, isteği ilgili adapter üzerinden API’ye iletir ve yanıtı sistemin anlayacağı yapılara dönüştürür (örneğin "yeni görev oluştur" komutu için görev alanları).
- GoogleCalendarService, kullanıcı adına Google Calendar’da etkinlik oluşturma/güncelleme/silme ve mevcut etkinlikleri içeri aktarma işlemlerini yürütür; bağlantı durumu ve hata yönetimi için kullanıcıya bildirim gönderir.

---

## 6. Test

### 6.1. Test Planı
- Birim testler: Servis katmanındaki temel fonksiyonlar, model ilişkileri ve yardımcı sınıflar test edilir.
- Entegrasyon testleri: API endpoint’leri, Google Calendar ve LLM entegrasyonlarının beklenen şekilde çalışması doğrulanır.
- Kullanıcı kabul testleri (UAT): Tipik kullanıcı senaryoları (görev/etkinlik yönetimi, Google bağlantısı, sohbet asistanı kullanımı) adım adım test edilir.

### 6.2. Test Araçları
- PHPUnit ve Laravel test araçları, Postman koleksiyonları, gerektiğinde browser tabanlı manuel testler.

---

## 7. Bakım ve İşletim

- Kodun modüler yapıda tutulması, servislerin ayrı ayrı geliştirilebilir ve test edilebilir olması.
- CI/CD hattında otomatik test, build ve deploy adımlarının çalıştırılması.
- Loglama ve monitoring (Laravel log’ları, isteğe bağlı harici izleme servisleri) ile sorun tespiti.

---

## 8. Sonuç ve Değerlendirme

- Akıllı Ajanda Web Uygulaması, ajanda, görev, entegrasyon ve yapay zeka özelliklerini tek bir çatı altında toplayarak mevcut çözümlere göre daha entegre ve esnek bir deneyim sunar.
- Güçlü entegrasyonlar (özellikle Google Calendar), ilişkisel PostgreSQL veritabanı ve modüler Laravel mimarisi sayesinde hem teknik olarak sürdürülebilir hem de yeni özelliklere açık bir yapı hedeflenmektedir.

---

## 9. İş Paketleri ve Zaman Planı

Uygulamanın bundan sonraki geliştirme süreci, bugünden itibaren 11 haftaya yayılan, her biri yaklaşık bir haftalık küçük ve tamamlanabilir iş paketlerine ayrılmıştır. Aşağıdaki tabloda her haftanın tarih aralığı, iş paketi ve kısa açıklaması verilmiştir.

| Hafta | Tarih Aralığı                | İş Paketi Başlığı                               | Kısa Açıklama |
|-------|------------------------------|-------------------------------------------------|--------------|
| 1     | 10.03.2026 – 16.03.2026      | Temel görev filtreleme ve sıralama             | Görev listesinin tarih, öncelik ve duruma göre filtrelenebilmesi ve sıralanabilmesi. |
| 2     | 17.03.2026 – 23.03.2026      | Haftalık özet ve dashboard iyileştirmeleri     | Dashboard ekranına haftalık görev/etkinlik özeti ve basit istatistik kartlarının eklenmesi. |
| 3     | 24.03.2026 – 30.03.2026      | Görev ve etkinliklere kategori/etiket sistemi  | Görev ve etkinliklere kategori ya da etiket atama, listelemede kategoriye göre filtreleme. |
| 4     | 31.03.2026 – 06.04.2026      | Gelişmiş arama (metin tabanlı)                 | Başlık ve açıklamaya göre hızlı arama çubuğu ile görev/etkinlik bulma özelliği. |
| 5     | 07.04.2026 – 13.04.2026      | Basit raporlama ve dışa aktarma                | Belirli tarih aralığındaki görev ve etkinlikleri özetleyen basit rapor ve PDF/CSV dışa aktarma. |
| 6     | 14.04.2026 – 20.04.2026      | Bildirim tercihleri ve sessiz zamanlar         | Kullanıcının bildirim türlerini (in-app, e-posta/push altyapısına hazırlık) ve sessiz saat aralıklarını ayarlayabilmesi. |
| 7     | 21.04.2026 – 27.04.2026      | Karanlık mod ve tema ayarları                  | Web arayüzünde açık/koyu tema desteği ve kullanıcı bazlı tema seçimi. |
| 8     | 28.04.2026 – 04.05.2026      | Basit kullanıcı tercihleri ve görünüm ayarları | Liste yoğunluğu, tarih gösterimi ve varsayılan takvim görünümü (gün/hafta/ay) gibi basit kullanıcı tercihlerinin eklenmesi. |
| 9     | 05.05.2026 – 11.05.2026      | Onboarding ve yardım içerikleri                | İlk kez giriş yapan kullanıcılar için kısa tanıtım ekranları ve yardım/SSS sayfası. |
| 10    | 12.05.2026 – 18.05.2026      | Performans ve kullanılabilirlik iyileştirmeleri | Liste ve takvim sayfalarının performansının artırılması, küçük UX düzenlemeleri ve geri bildirimlere göre iyileştirmeler. |
| 11    | 19.05.2026 – 25.05.2026      | Hata izleme, bug tespiti ve düzeltmeleri       | Geliştirme sürecinde bilinen ve yeni tespit edilen hataların listelenmesi, önceliklendirilmesi, çözülmesi ve uygulamanın genel stabilitesinin gözden geçirilmesi. |

