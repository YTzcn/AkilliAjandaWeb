# Akıllı Ajanda Mobil Uygulaması SRS + SRD Raporu

---

## 1. Tanıtım

### 1.1. Proje Amacı ve Özeti
Akıllı Ajanda Mobil Uygulaması, kullanıcıların etkinlik ve görevlerini kolayca yönetebileceği, Google Takvim ile entegre olabilen, bildirim ve gerçek zamanlı güncellemeler sunan bir mobil ajanda uygulamasıdır. Proje, kullanıcıların günlük yaşamlarını daha verimli planlamalarını ve organize olmalarını hedefler. Kullanıcılar, etkinlik ve görevlerini tek bir platformda yönetebilir, hatırlatıcılar ve bildirimler ile zaman yönetimini optimize edebilir.

### 1.2. Kapsam
- Etkinlik ve görev yönetimi (oluşturma, güncelleme, silme, tamamlama)
- Google Takvim entegrasyonu (çift yönlü senkronizasyon, içe/dışa aktarma)
- Gerçek zamanlı bildirimler (Pusher ile anlık güncellemeler)
- Modern ve kullanıcı dostu arayüz (React Native ile responsive tasarım)
- Profil ve hesap yönetimi (kayıt, giriş, şifre sıfırlama, profil güncelleme)
- Güvenli oturum ve veri yönetimi (JWT, OAuth2, güvenli depolama)

### 1.3. Sorun ve Hedef Analizi
- **Sorun:** Mevcut ajanda uygulamalarında entegrasyon, bildirim ve kullanıcı deneyimi eksiklikleri bulunmaktadır. Kullanıcılar, farklı platformlar arasında veri kaybı, senkronizasyon sorunları ve karmaşık arayüzlerle karşılaşmaktadır.
- **Hedef:** Kullanıcıların tüm ajanda ihtiyaçlarını tek bir uygulamada, entegre ve kolay kullanılabilir şekilde karşılamak. Google Takvim ile tam uyumlu, hızlı, güvenli ve erişilebilir bir ajanda deneyimi sunmak.

### 1.4. Vision & Scope
- Kullanıcıların tüm takvim ve görev yönetimi ihtiyaçlarını karşılamak
- Mobil platformlarda (iOS/Android) erişilebilirlik
- Güvenli ve sürdürülebilir bir yazılım altyapısı
- Geliştirilebilir ve modüler mimari

---

## 2. Planlama

### 2.1. Kaynaklar
- Donanım Kaynakları: Geliştirme bilgisayarları (Mac/Windows), iOS ve Android test cihazları
- Yazılım Kaynakları: React Native, Node.js, Google API, Pusher, Firebase, Git, VSCode, Postman

---

## 3. Çözümleme

### 3.1. Mevcut Sistem Analizi
- Mevcut sistemlerde genellikle sadece temel etkinlik yönetimi ve sınırlı entegrasyon bulunur. Bildirimler ve gerçek zamanlı güncellemeler eksiktir.
- Eksik yönler: Zayıf entegrasyon, yetersiz bildirim, karmaşık arayüz, veri kaybı riski.
- Önerilen sistem, bu eksiklikleri güçlü entegrasyon, anlık bildirim, sade arayüz ve güvenli veri yönetimi ile çözer.

### 3.2. Önerilen Sistem
- **İşlevsel Model:**
  - Kullanıcı ile sistem arasındaki temel etkileşimler; "Kayıt Ol / Giriş Yap", "Etkinlik Oluşturma / Güncelleme / Silme", "Görev Oluşturma / Tamamlama", "Google Takvim ile Senkronizasyon Başlatma", "Bildirimleri Görüntüleme" gibi use case'ler üzerinden metinsel olarak tanımlanır.
  - Her bir use case için, kullanıcının başlattığı aksiyon, sistemin verdiği cevap ve olası hata durumları (ör. "Google bağlantısı yoksa kullanıcı bilgilendirilir ve işlem kuyruğa alınır.") adım adım senaryo halinde açıklanır.
- **Bilgi Sistemleri/Nesneler:**
  - Sınıflar: User, Event, Task, Notification, Calendar, GoogleIntegration, vb. Her sınıf, tek bir iş sorumluluğunu üstlenecek şekilde tasarlanır (ör. Event, bir etkinliğe ait tüm alanları ve ilişkili görevleri temsil eder).
  - Her sınıfın amacı ve senaryolarla ilişkisi (ör. Task, son tarih, öncelik ve tamamlanma durumunu tutarak görev yönetimini sağlar; Notification, ilgili kullanıcı ve olayla ilişkilendirilmiş bildirim kayıtlarını temsil eder) metinsel olarak detaylandırılır.
  - Veri modeli: Kullanıcı, etkinlik, görev, bildirim ve entegrasyon tabloları arasında User–Event ve User–Task ilişkileri bire çok, kullanıcı–bildirim ilişkisi yine bire çok olacak şekilde kurgulanır; böylece veriler hem tutarlı hem de sorgulanabilir biçimde saklanır.
- **UML Diyagramları:**
  - Sınıf, Aktivite, Durum, Sıralama, Bileşen, Dağıtım ve Paket diyagramlarında gösterilecek akışlar, metinsel olarak; tipik bir istek döngüsünde mobil istemcinin API'ye çağrı yapması, ilgili servislerin çalışması, veritabanı ve harici servislerle (Google, Firebase, Pusher) etkileşim ve sonucun kullanıcıya dönmesi şeklinde adım adım tarif edilir.
- **Arayüzler:**
  - Login ekranı, kullanıcının e-posta ve şifresiyle sisteme güvenli şekilde giriş yapmasını sağlar; hatalı girişlerde anlamlı hata mesajları gösterilir.
  - Register ekranı, yeni kullanıcıların ad, e-posta ve şifre bilgileriyle hesap oluşturmasına ve gerekirse e-posta doğrulama sürecinin başlatılmasına imkân verir.
  - Dashboard ekranı, kullanıcının güncel gün/hafta özetini, yaklaşan etkinlik ve görevleri ile önemli bildirimleri tek bakışta görebileceği ana kontrol paneli olarak çalışır.
  - Calendar ekranı, takvim görünümü (gün, hafta, ay) üzerinde etkinliklerin görüntülenmesini, yeni etkinlik ekleme ve mevcut etkinlikleri düzenleme/silme işlemlerini destekler.
  - Task ekranı, görevlerin listelenmesi, tarih/öncelik/durum gibi kriterlere göre filtrelenmesi ve yeni görev ekleme, tamamlama veya silme işlemlerinin yapılmasını sağlar.
  - Profile ekranı, kullanıcı bilgilerinin (ad, e-posta, profil resmi vb.) görüntülenmesi ve güncellenmesi ile bildirim tercihleri gibi kişisel ayarların yönetilmesine olanak tanır.
  - Settings ekranı, uygulama genel ayarlarının, tema ve dil seçeneklerinin ve entegrasyon bağlantılarının (örneğin Google Takvim) yönetildiği bölümdür.
  - Notification ekranı, alınan bildirimlerin (yaklaşan görevler, yeni etkinlikler, entegrasyon uyarıları vb.) listelenmesini, okunma durumlarının güncellenmesini ve detayların görüntülenmesini sağlar.

---

## 4. Tasarım

### 4.1. Mimari Akış Diyagramı
- Mimari: Katmanlı mimari (Presentation, Business Logic, Data, Integration). Kullanıcı, React Native arayüzü üzerinden etkileşime geçer; istekler HTTP üzerinden backend API'lerine iletilir, iş kuralları business logic katmanında uygulanır ve veriler data katmanında saklanır.
- Seçim nedeni: Modülerlik, sürdürülebilirlik, kolay test edilebilirlik. Her katmanın sorumluluğu ayrıldığı için hem hata ayıklama hem de yeni özellik ekleme süreçleri daha öngörülebilir ve yönetilebilir hale gelir.
- Katmanlar arası iletişim: Mobil istemci, Axios ile RESTful API çağrıları yapar; backend tarafında kimlik doğrulama, görev, etkinlik ve entegrasyon servisleri çalışır; bu servisler veritabanına ve Google/Firebase/Pusher gibi harici sistemlere bağlanır, sonuçlar JSON cevaplar olarak tekrar mobil uygulamaya döner. Ekranlar arası geçiş React Navigation ile yönetilir, uygulama durumu (takvim ve görev listeleri gibi) ise merkezi bir store yapısıyla tutulur.

### 4.2. Arabirimler ve Modüller
- **Arabirimler:**
  - Kullanıcı arayüzü: React Native bileşenleri, özelleştirilebilir tema
  - API arabirimi: Axios ile RESTful API iletişimi
  - Google Takvim arabirimi: OAuth2 ile güvenli bağlantı, etkinlik senkronizasyonu
- **Modüller:**
  - AuthService: Kimlik doğrulama, oturum yönetimi
  - TaskService: Görev CRUD işlemleri, öncelik ve tamamlanma
  - EventService: Etkinlik CRUD işlemleri, takvim yönetimi
  - GoogleCalendarService: Google ile bağlantı, etkinlik senkronizasyonu
  - PusherService: Gerçek zamanlı bildirimler
  - CalendarStore: Etkinlik ve görevlerin global yönetimi (MobX)
- Her modülün kullanıcı profilleriyle ilişkisi ve test kriterleri
- Modüller arası entegrasyon ve test işlemlerinin akışı metinsel olarak; örneğin bir görev oluşturma senaryosunda, mobil arayüzün TaskService üzerinden API'ye istek atması, backend'de ilgili kontrol ve veritabanı işlemlerinin yapılması, ardından PusherService ile gerçek zamanlı bildirim tetiklenmesi ve son olarak CalendarStore'un güncellenmesi şeklinde adım adım açıklanır. Bu akış, birim testler (her servis fonksiyonu için), entegrasyon testleri (API + veritabanı) ve uçtan uca testler (kullanıcı senaryosu) ile doğrulanır.
- Ortak alt sistemler: Bildirim altyapısı, kullanıcı yönetimi

---

## 5. Gerçekleştirme

### 5.1. Teknoloji ve Araçlar
- Programlama dilleri: TypeScript, JavaScript
- Kullanılan araçlar: React Native (mobil), Node.js (backend), Firebase (bildirim), Pusher (gerçek zamanlı), Google API (entegrasyon), Git (sürüm kontrol)
- Seçim nedenleri: Platform bağımsızlık, geniş topluluk, hızlı geliştirme, kolay entegrasyon

### 5.2. Veri Tabanı Yönetimi
- Kullanılan veri tabanı: Backend ile ilişkili ilişkisel bir veritabanı (örneğin PostgreSQL) tercih edilir; böylece kullanıcı, etkinlik, görev ve bildirim gibi birbiriyle ilişkili veriler güvenli ve tutarlı şekilde saklanır.
- Mimari: Kullanıcı, etkinlik, görev, bildirim ve entegrasyon tabloları arasında User–Event ve User–Task ilişkileri bire çok, kullanıcı–bildirim ilişkisi yine bire çok olacak şekilde kurgulanır; entegrasyon tabloları ise ilgili kullanıcı ve harici servis ile ilişkilendirilir.
- ERD/varlık ilişki diyagramı yerine; metinsel olarak "Bir kullanıcı birçok etkinliğe ve göreve sahip olabilir, her etkinlik yalnızca bir kullanıcıya aittir, her görev bir kullanıcıya ve isteğe bağlı olarak bir etkinliğe bağlı olabilir, her bildirim tek bir kullanıcıya ve belirli bir olaya (ör. görev yaklaşıyor) bağlıdır." biçiminde ilişkiler tanımlanır.
- Veri modeli ile ilişkisi: Bu yapı sayesinde "belirli bir kullanıcının yaklaşan tüm görevlerini getir", "bir etkinliğe bağlı tüm görevleri listele" veya "okunmamış bildirimleri göster" gibi sorgular performanslı ve tutarlı bir biçimde gerçekleştirilebilir.

### 5.3. Standartlar ve Kod Gözden Geçirme
- Kodlama standartları: Airbnb/Google JS/TS standartları, Prettier, ESLint
- Kod gözden geçirme: Pull request, code review, otomatik testler

### 5.4. Olağan Dışı Durumlar
- API hataları, bağlantı kopması, Google entegrasyonunda yetki kaybı gibi durumlar için hata yönetimi ve kullanıcıya bilgilendirme
- Otomatik yeniden deneme ve hata loglama

---

## 6. Test

### 6.1. Test Planı ve Gant Diyagramı
- Doğrulama ve geçerleme işlemlerinin iş zaman planı; analiz, tasarım, geliştirme, test ve yayın öncesi kullanıcı kabul aşamaları şeklinde fazlara ayrılır. Her faz için başlangıç ve bitiş tarihleri, sorumlu ekip ve beklenen çıktılar (analiz raporu, prototip, test raporu vb.) metinsel olarak tanımlanır.
- Test aşamaları: Birim test, entegrasyon testleri, kullanıcı kabul testi (UAT)

### 6.2. Test Yöntemleri ve Araçları
- Birim testler: Jest, React Native Testing Library
- Entegrasyon testleri: Manuel ve otomasyon test senaryoları
- Kullanıcı testleri: Kullanıcı hikayeleri bazında senaryolar
- Test araçlarının işleyişi: Otomatik testler CI/CD pipeline'ında çalıştırılır

### 6.3. Test Süreci
- Test ortamı kurulumu, test verisi oluşturma, hata takibi ve raporlama
- Doğrulama ve geçerleme işlemlerinin nasıl yapılacağı, testten geçmeyen modüllerin yeniden geliştirilmesi

---

## 7. Bakım

### 7.1. Kurulum ve Destek
- Uygulamanın App Store/Google Play'e yüklenmesi
- Kullanıcıya kurulum sonrası destek için e-posta ve canlı destek kanalları
- Kurulum ve entegrasyon aşamalarında yapılacaklar: Sürüm güncellemeleri, veri migrasyonu, entegrasyon anahtarlarının yönetimi

### 7.2. Sürdürülebilirlik ve Uzun Vadeli Bakım
- Kodun modüler ve dokümante olması
- Otomatik testler ve CI/CD ile sürekli entegrasyon
- Geriye dönük uyumluluk ve veri yedekleme stratejileri
- Açık kaynak kütüphanelerin güncel tutulması

---

## 8. Sonuç

### 8.1. Değerlendirme
- Uygulama, mevcut ajanda uygulamalarına göre daha kapsamlı entegrasyon, anlık bildirim ve kullanıcı dostu arayüz sunar.
- Google Takvim ile çift yönlü senkronizasyon, görev ve etkinliklerin tek ekranda yönetimi, güvenli oturum ve veri yönetimi ile öne çıkar.
- Avantajlar: Hızlı, güvenli, entegre, kullanıcı odaklı
- Dezavantajlar: Yüksek entegrasyon bağımlılığı, internet gereksinimi
- Kısıtlar: Sadece mobil platformlar, Google hesabı gerekliliği
- Benzer sistemlerle karşılaştırmada; tipik ajanda uygulamalarına kıyasla bu sistemin Google Takvim ile daha derin entegrasyon sunduğu, görev ve etkinlikleri tek bir birleşik görünümde topladığı, gerçek zamanlı bildirimler ve çoklu cihaz senkronizasyonu ile öne çıktığı metinsel olarak açıklanır. Bunun yanında, sadece tek bir ekosisteme (Google) bağlı kalmanın ve sürekli internet bağlantısı gereksiniminin dezavantaj olduğu, gelecekte diğer takvim servisleri ve çevrimdışı mod desteği ile bu farkların azaltılabileceği belirtilir.
- Dezavantajların gelecekte nasıl iyileştirilebileceği: Diğer takvim servisleriyle entegrasyon, offline mod, gelişmiş bildirim seçenekleri

---

## 9. Kaynaklar
- Geliştirme süreçlerinde yararlanılan tüm kaynaklar numaralandırılarak yazılacak.
- Doküman içinde kaynak gösterimi yapılacak.
- Örnek: 
  1. Baykara M., "Bilgi Sistemleri ve Güvenliği Ders Notları", JISA, ss. 89-101, 2023.
  2. React Native Resmi Dokümantasyonu
  3. Google Calendar API Dökümantasyonu
  4. Pusher Resmi Dokümantasyonu
  5. ...

---

## 10. Ekler ve Zorunlu Unsurlar
- İş Zaman Çizelgesi, UML, ERD ve diğer görsel diyagramlara konu olan içerikler bu dokümanda metinsel olarak açıklanmıştır; ihtiyaç halinde bu açıklamalar temel alınarak ayrı bir ek dökümanda diyagram biçiminde üretilecektir.
- Veri Sözlüğü (ör. Event: Etkinlik, Task: Görev, User: Kullanıcı, ...) proje boyunca terimlerin tutarlı kullanılmasını sağlamak üzere ayrı bir ek dosyada detaylandırılabilir.

---

## 11. Ek Değerlendirme Başlıkları

### 11.1. Risk Analizi ve Yönetimi
- Proje sırasında karşılaşılabilecek riskler: Google API değişiklikleri, veri kaybı, güvenlik açıkları, kullanıcı kabulü eksikliği
- Yönetim stratejileri: Yedekleme, çoklu entegrasyon, düzenli güncelleme, kullanıcı eğitimi
- Olasılık ve etki matrisli risk değerlendirmesi diyagram yerine metinsel olarak yapılır; örneğin Google API değişiklikleri "orta olasılık – yüksek etki", veri kaybı "düşük olasılık – çok yüksek etki", kullanıcı kabulü eksikliği ise "orta olasılık – orta etki" kategorisinde değerlendirilir ve her biri için alınacak önlemler (yedekleme, alternatif entegrasyon, UX iyileştirmeleri vb.) belirtilir.

### 11.2. Güvenlik Önlemleri
- JWT ile güvenli oturum yönetimi
- Şifrelerin hashlenerek saklanması
- OAuth2 ile Google entegrasyonu ve token yönetimi
- API anahtarlarının güvenli saklanması (env dosyaları)
- Kullanıcı verilerinin şifrelenmesi ve gizlilik politikası
- Yetkisiz erişimlerin önlenmesi için rol tabanlı erişim kontrolü

### 11.3. Kullanıcı Deneyimi (UX) Değerlendirmesi
- Kullanıcı arayüzü için kullanılabilirlik testleri (ör. kullanıcıya görev ekletme, etkinlik silme senaryosu)
- Kullanıcı geri bildirimleri: Anket, uygulama içi değerlendirme, A/B testleri
- Erişilebilirlik: Renk kontrastı, büyük butonlar, sesli bildirimler
- Kullanıcıdan alınan geri bildirimlerle sürekli iyileştirme

### 11.4. DevOps ve CI/CD
- Otomasyon araçları: GitHub Actions, Bitrise, Fastlane
- Sürüm kontrol: Git, branch yönetimi, kod gözden geçirme
- Otomatik test ve dağıtım pipeline'ı
- Sürüm notları ve rollback stratejileri

### 11.5. Yasal ve Etik Hususlar
- Kullanıcı verilerinin gizliliği ve KVKK/GDPR uyumluluğu
- Kullanıcıdan açık rıza alınması (Google entegrasyonu, bildirimler)
- Açık kaynak ve üçüncü parti kütüphanelerin lisanslarının kontrolü
- Uygulamanın etik kullanımına dair kullanıcı sözleşmesi

### 11.6. Müşteri ve Paydaş Geri Bildirim Süreci
- Geri bildirim toplama: Uygulama içi formlar, e-posta, sosyal medya
- Geri bildirimlerin değerlendirilmesi ve sürümlere yansıtılması
- Paydaşlarla düzenli toplantılar ve demo sunumları

### 11.7. Sistem Entegrasyonu ve API Yönetimi
- Dış sistemlerle entegrasyon: Google Calendar API, Firebase, Pusher
- API tasarım prensipleri: RESTful, JSON veri formatı, hata yönetimi
- API anahtarlarının güvenli yönetimi
- Gelecekte eklenebilecek diğer entegrasyonlar için modüler API altyapısı

---

## 12. İş Paketleri ve Zaman Planı

Uygulamanın bundan sonraki geliştirme süreci, bugünden itibaren 11 haftaya yayılan, her biri yaklaşık bir haftalık küçük ve tamamlanabilir iş paketlerine ayrılmıştır. Aşağıdaki tabloda her haftanın tarih aralığı, iş paketi ve kısa açıklaması verilmiştir.

| Hafta | Tarih Aralığı                | İş Paketi Başlığı                               | Kısa Açıklama |
|-------|------------------------------|-------------------------------------------------|--------------|
| 1     | 25.02.2026 – 02.03.2026      | Temel görev filtreleme ve sıralama             | Görev listesinin tarih, öncelik ve duruma göre filtrelenebilmesi ve sıralanabilmesi. |
| 2     | 03.03.2026 – 09.03.2026      | Haftalık özet ve dashboard iyileştirmeleri     | Dashboard ekranına haftalık görev/etkinlik özeti ve basit istatistik kartlarının eklenmesi. |
| 3     | 10.03.2026 – 16.03.2026      | Görev ve etkinliklere kategori/etiket sistemi  | Görev ve etkinliklere kategori ya da etiket atama, listelemede kategoriye göre filtreleme. |
| 4     | 17.03.2026 – 23.03.2026      | Gelişmiş arama (metin tabanlı)                 | Başlık ve açıklamaya göre hızlı arama çubuğu ile görev/etkinlik bulma özelliği. |
| 5     | 24.03.2026 – 30.03.2026      | Basit raporlama ve dışa aktarma                | Belirli tarih aralığındaki görev ve etkinlikleri özetleyen basit rapor ve PDF/CSV dışa aktarma. |
| 6     | 31.03.2026 – 06.04.2026      | Bildirim tercihleri ve sessiz zamanlar         | Kullanıcının bildirim türlerini (push, uygulama içi) ve sessiz saat aralıklarını ayarlayabilmesi. |
| 7     | 07.04.2026 – 13.04.2026      | Karanlık mod ve tema ayarları                  | Açık/koyu tema desteği ve ayarlar ekranından tema seçimi. |
| 8     | 14.04.2026 – 20.04.2026      | Basit kullanıcı tercihleri ve görünüm ayarları | Liste yoğunluğu, tarih gösterimi ve varsayılan görünüm (gün/hafta/ay) gibi basit kullanıcı tercihlerinin eklenmesi. |
| 9     | 21.04.2026 – 27.04.2026      | Onboarding ve yardım içerikleri                | İlk kez giriş yapan kullanıcılar için kısa tanıtım ekranları ve yardım/SSS sayfası. |
| 10    | 28.04.2026 – 04.05.2026      | Performans ve kullanılabilirlik iyileştirmeleri | Liste performansının artırılması, küçük UX düzenlemeleri ve geri bildirimlere göre iyileştirmeler. |
| 11    | 05.05.2026 – 11.05.2026      | Hata izleme, bug tespiti ve düzeltmeleri       | Geliştirme sürecinde bilinen ve yeni tespit edilen hataların listelenmesi, önceliklendirilmesi, çözülmesi ve uygulamanın genel stabilitesinin gözden geçirilmesi. |

