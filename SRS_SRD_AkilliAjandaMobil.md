# Akıllı Ajanda Mobil Uygulaması SRS + SRD Dökümanı

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

### 2.1. Gant Diyagramı
- **Buraya projenin iş zaman çizelgesi (Gant diyagramı) eklenecek.**
- Örnek aşamalar: Analiz, Tasarım, Geliştirme, Test, Dağıtım, Bakım

### 2.2. Ekip Yapısı
- **Buraya ekip şeması eklenecek.**
- Proje Yöneticisi: Proje planlaması ve koordinasyon
- Yazılım Geliştirici(ler): Mobil uygulama ve backend geliştirme
- Test Sorumlusu: Test planı, manuel ve otomasyon testleri
- Analist: Gereksinim toplama, dokümantasyon

### 2.3. Kaynaklar
- İnsan Kaynakları: 2 React Native geliştirici, 1 backend geliştirici, 1 test uzmanı
- Donanım Kaynakları: Geliştirme bilgisayarları (Mac/Windows), iOS ve Android test cihazları
- Yazılım Kaynakları: React Native, Node.js, Google API, Pusher, Firebase, Git, VSCode, Postman

### 2.4. Alt Planlama Başlıkları
- Analiz, tasarım, geliştirme, test ve dağıtım için ayrı Gant diyagramları ve ekip/zaman/kaynak planı **(Buraya eklenecek)**

---

## 3. Çözümleme

### 3.1. Mevcut Sistem Analizi
- **Buraya mevcut sistemin use case diyagramı ve işleyiş senaryosu eklenecek.**
- Mevcut sistemlerde genellikle sadece temel etkinlik yönetimi ve sınırlı entegrasyon bulunur. Bildirimler ve gerçek zamanlı güncellemeler eksiktir.
- Eksik yönler: Zayıf entegrasyon, yetersiz bildirim, karmaşık arayüz, veri kaybı riski.
- Önerilen sistem, bu eksiklikleri güçlü entegrasyon, anlık bildirim, sade arayüz ve güvenli veri yönetimi ile çözer.

### 3.2. Önerilen Sistem
- **İşlevsel Model:**
  - Use case diyagramları **(Buraya eklenecek)**
  - Her bir use case için metinsel senaryolar (ör. "Kullanıcı yeni etkinlik ekler, sistem etkinliği takvime kaydeder ve Google Takvim ile senkronize eder.")
- **Bilgi Sistemleri/Nesneler:**
  - Sınıf diyagramları **(Buraya eklenecek)**
  - Sınıflar: User, Event, Task, Notification, Calendar, GoogleIntegration, vb.
  - Her sınıfın amacı ve senaryolarla ilişkisi (ör. Event sınıfı, etkinliklerin tüm özelliklerini ve ilişkili görevleri yönetir.)
  - Veri modeli: Kullanıcı, etkinlik, görev, bildirim ve entegrasyon tabloları
- **UML Diyagramları:**
  - Sınıf, Aktivite, Durum, Sıralama, Bileşen, Dağıtım, Paket diyagramları **(Buraya eklenecek)**
- **Arayüzler:**
  - Login, Register, Dashboard, Calendar, Task, Profile, Settings, Notification ekranları
  - Her arayüzün kısa tanımı ve kullanıcıya sağladığı işlevler
  - Arayüzlerin maliyet kestirim dokümanında kullanılıp kullanılmadığı belirtilecek

---

## 4. Tasarım

### 4.1. Mimari Akış Diyagramı
- **Buraya sistemin tasarım mimarisi akış diyagramı eklenecek.**
- Mimari: Katmanlı mimari (Presentation, Business Logic, Data, Integration)
- Seçim nedeni: Modülerlik, sürdürülebilirlik, kolay test edilebilirlik
- Katmanlar arası iletişim: API servisleri, MobX store, React Navigation

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
- Modüller arası entegrasyon ve test işlemlerinin akış diyagramı **(Buraya eklenecek)**
- Ortak alt sistemler: Bildirim altyapısı, kullanıcı yönetimi

---

## 5. Gerçekleştirme

### 5.1. Teknoloji ve Araçlar
- Programlama dilleri: TypeScript, JavaScript
- Kullanılan araçlar: React Native (mobil), Node.js (backend), Firebase (bildirim), Pusher (gerçek zamanlı), Google API (entegrasyon), Git (sürüm kontrol)
- Seçim nedenleri: Platform bağımsızlık, geniş topluluk, hızlı geliştirme, kolay entegrasyon

### 5.2. Veri Tabanı Yönetimi
- Kullanılan veri tabanı: (ör. Firebase/Firestore veya backend ile ilişkili bir SQL/NoSQL veritabanı)
- Mimari: Kullanıcı, etkinlik, görev, bildirim ve entegrasyon tabloları
- ERD/varlık ilişki diyagramı **(Buraya eklenecek)**
- Veri modeli ile ilişkisi: Her kullanıcıya ait etkinlik ve görevler, bildirimler ve entegrasyon kayıtları

### 5.3. Standartlar ve Kod Gözden Geçirme
- Kodlama standartları: Airbnb/Google JS/TS standartları, Prettier, ESLint
- Kod gözden geçirme: Pull request, code review, otomatik testler

### 5.4. Olağan Dışı Durumlar
- API hataları, bağlantı kopması, Google entegrasyonunda yetki kaybı gibi durumlar için hata yönetimi ve kullanıcıya bilgilendirme
- Otomatik yeniden deneme ve hata loglama

---

## 6. Test

### 6.1. Test Planı ve Gant Diyagramı
- Doğrulama ve geçerleme işlemlerinin iş zaman planı **(Buraya eklenecek)**
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
- Benzer sistemlerle tablo halinde karşılaştırma **(Buraya tablo eklenecek)**
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
- İş Zaman Çizelgesi (Gant diyagramı)
- UML Diyagramlarının tamamı
- ERD Diyagramları (Varlık ilişki diyagramları)
- Veri Sözlüğü (ör. Event: Etkinlik, Task: Görev, User: Kullanıcı, ...)
- Veri akış diyagramları
- İş akış diyagramları
- Veri tabanı diyagramları
- Rich Picture
- Context Diyagramı
- Mimari yapıları gösteren diyagramlar
- Alt sistemleri gösteren diyagramlar

---

## 11. Ek Değerlendirme Başlıkları

### 11.1. Risk Analizi ve Yönetimi
- Proje sırasında karşılaşılabilecek riskler: Google API değişiklikleri, veri kaybı, güvenlik açıkları, kullanıcı kabulü eksikliği
- Yönetim stratejileri: Yedekleme, çoklu entegrasyon, düzenli güncelleme, kullanıcı eğitimi
- Olasılık ve etki matrisli risk tablosu **(Buraya eklenecek)**

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

> **Not:** Tüm diyagram, tablo ve görseller ilgili başlık altına eklenecektir. Eksik olanlar için yer tutucu bırakılmıştır. Görsel ve diyagramları ekledikten sonra doküman tamamlanacaktır. 