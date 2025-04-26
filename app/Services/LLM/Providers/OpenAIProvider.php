<?php

namespace App\Services\LLM\Providers;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class OpenAIProvider implements ProviderInterface
{
    /**
     * Kullanılacak model adı
     * 
     * @var string
     */
    protected string $model = 'gpt-4o';
    
    /**
     * OpenAI API anahtarı
     * 
     * @var string|null
     */
    protected ?string $apiKey = null;
    
    /**
     * API taban URL'i
     * 
     * @var string
     */
    protected string $baseUrl = 'https://api.openai.com/v1';
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->apiKey = Config::get('services.openai.api_key');
    }
    
    /**
     * Kullanıcı mesajını işler ve analiz eder
     * 
     * @param string $message Kullanıcı mesajı
     * @return array İşlenmiş analiz sonucu
     */
    public function processMessage(string $message): array
    {
        try {
            if (!$this->apiKey) {
                throw new Exception('OpenAI API anahtarı bulunamadı');
            }
            
            $currentDate = Carbon::now()->format('Y-m-d H:i:s');
            $response = $this->callOpenAI([
                "model" => $this->model,
                "messages" => [
                    [
                        "role" => "system",
                        "content" => "Sen bir Akıllı Ajanda Uygulamasının Asistanısın. Kullanıcılar seninle konuşarak ajanda üzerindeki işlemlerini yapabilirler."
                    ],
                    [
                        "role" => "user",
                        "content" => "ÖNEMLİ: Şu anki gerçek tarih ve saat: " . $currentDate . " Bu tarihi referans alarak işlem yap.\n" .
                            "Tarih ile ilgili tüm kararlarında bugün olarak yukarıdaki tarihi kabul et ve buna göre hesaplama yap.\n" .
                            "Kullanıcı mesajı: " . $message . "\n" .
                            "Lütfen aşağıdaki adımları izle:\n" .
                            "1. Kullanıcının mesajının içeriğini analiz et\n" .
                            "2. Kullanıcının ne yapmak istediğini belirle: takvim sorgulama, etkinlik ekleme, görev güncelleme, görev ekleme veya özet bilgi isteme\n" .
                            "3. Kullanıcının mesajındaki tarihleri, kişileri, etkinlik/görev detaylarını belirle\n" .
                            "4. İçerik türünü belirle: Kullanıcı etkinlikleri mi, görevleri mi yoksa her ikisini birden mi sorguluyor\n" .
                            "5. Görevler için öncelik ve durum değerlerini kullanıcının ifade tarzından çıkar:\n" .
                            "   - Öncelik: 1 (düşük), 2 (orta), 3 (yüksek/acil)\n" .
                            "   - İfadede \"önemli\", \"acil\", \"hemen\", \"kritik\" gibi kelimeler varsa öncelik 3 (yüksek) olmalı\n" .
                            "   - İfadede \"önemsiz\", \"acil değil\", \"vakit buldukça\" gibi ifadeler varsa öncelik 1 (düşük) olmalı\n" .
                            "   - Belirgin bir vurgu yoksa öncelik 2 (orta) kullan\n" .
                            "   - Durum: \"beklemede\" (pending), \"devam_ediyor\" (in_progress), \"tamamlandı\" (completed), \"iptal\" (cancelled)\n" .
                            "   - İfadede \"başla\", \"başlayacağım\", \"üzerinde çalışıyorum\" gibi ifadeler varsa durum \"devam_ediyor\" olmalı\n" .
                            "   - İfadede \"tamamlandı\", \"bitti\", \"hallettim\" gibi kelimeler varsa durum \"tamamlandı\" olmalı\n" .
                            "   - İfadede \"iptal\", \"vazgeçtim\", \"yapılmayacak\" varsa durum \"iptal\" olmalı\n" .
                            "   - Belirgin bir durum belirtilmemişse varsayılan olarak \"beklemede\" kullan\n" .
                            "6. Yanıtını tam olarak aşağıdaki JSON formatında ver (başka bir metin veya açıklama olmadan):\n" .
                            "{\n" .
                            "  \"type\": \"işlem_tipi\", // takvim_sorgulama, yeni_etkinlik, yeni_görev, gorev_guncelleme, ozet_bilgi\n" .
                            "  \"data\": {\n" .
                            "    \"title\": \"Etkinlik/Görev başlığı\", // Etkinlik veya görev başlığı\n" .
                            "    \"start_date\": \"YYYY-MM-DD HH:MM:SS\", // Başlangıç tarihi ve saati (etkinlikler için), her zaman tam tarih saat kullan\n" .
                            "    \"end_date\": \"YYYY-MM-DD HH:MM:SS\", // Bitiş tarihi ve saati (etkinlikler için), her zaman tam tarih saat kullan\n" .
                            "    \"due_date\": \"YYYY-MM-DD HH:MM:SS\", // Bitiş tarihi (görevler için), her zaman tam tarih saat kullan\n" .
                            "    \"description\": \"Açıklama\", // Varsa açıklama\n" .
                            "    \"location\": \"Konum\", // Varsa konum (etkinlikler için)\n" .
                            "    \"task_id\": \"id\", // Görev güncellemesi için ID\n" .
                            "    \"all_day\": false, // Tüm gün etkinliği mi? (etkinlikler için)\n" .
                            "    \"status\": \"beklemede\", // Görevin durumu (görevler için): beklemede (pending), devam_ediyor (in_progress), tamamlandı (completed), iptal (cancelled)\n" .
                            "    \"priority\": 2, // Görevin önceliği (görevler için): 1 (düşük), 2 (orta), 3 (yüksek/acil)\n" .
                            "    \"is_completed\": false, // Görev tamamlandı mı? (görevler için) - status=completed ise true, değilse false\n" .
                            "    \"content_type\": \"both\", // Sorgu türü: etkinlikler, görevler veya her ikisi\n" .
                            "    \"user_id\": 1 // Etkinlik veya görev sahibi ID (normalde burası yok sayılacak, sistem otomatik atayacak)\n" .
                            "  }\n" .
                            "}\n" .
                            "ÖNEMLİ NOTLAR:\n" .
                            "1. \"bugün\" ifadesini görürsen MUTLAKA " . $currentDate . " tarihini kullan, kendi kafandan tarih uydurma\n" .
                            "2. \"yarın\" ifadesini görürsen MUTLAKA " . Carbon::tomorrow()->format('Y-m-d H:i:s') . " tarihini kullan\n" .
                            "3. \"dün\" ifadesini görürsen MUTLAKA " . Carbon::yesterday()->format('Y-m-d H:i:s') . " tarihini kullan\n" .
                            "4. Tarihleri yazarken her zaman tam tarih ve saat formatı (YYYY-MM-DD HH:MM:SS) kullan\n" .
                            "5. Tarihler HER ZAMAN gerçek şu anki tarihten (YUKARIDA VERİLEN " . $currentDate . " TARİHİNDEN) hesaplanmalıdır\n" .
                            "6. Kendi bildiğin tarih yerine MUTLAKA yukarıdaki güncel tarihi kullan\n" .
                            "7. Görev önceliği (priority) ve durumu (status) kullanıcının ifade tonundan MUTLAKA çıkarılmalıdır\n" .
                            "8. Yanıtını sadece JSON formatında ver. Başka açıklama ekleme, yorum yapma veya metinle cevap verme. JSON yanıtı kod bloğu (```) içinde de verme. JSON dışında hiçbir karakter olmamalıdır."
                    ]
                ],
                "response_format" => [
                    "type" => "json_object"
                ]
            ]);
            
            if (!isset($response['choices'][0]['message']['content'])) {
                throw new Exception("API geçerli bir yanıt döndürmedi");
            }

            $content = $response['choices'][0]['message']['content'];
            $analysis = json_decode($content, true);
            
            if (!is_array($analysis) || !isset($analysis['type'])) {
                throw new Exception("AI yanıtı düzgün parse edilemedi: " . $content);
            }
            
            // Data kontrolü - bazı durumlarda data alanı boş gelebilir
            if (!isset($analysis['data']) || !is_array($analysis['data'])) {
                $analysis['data'] = [];
            }
            
            return $analysis;
        } catch (Exception $e) {
            throw new Exception('OpenAI mesaj işleme hatası: ' . $e->getMessage());
        }
    }
    
    /**
     * Soru bazlı içerik oluşturur
     * 
     * @param array $prompt İstek için prompt
     * @return string Model tarafından üretilen yanıt
     */
    public function generateContent(array $prompt): string
    {
        try {
            if (!$this->apiKey) {
                throw new Exception('OpenAI API anahtarı bulunamadı');
            }
            
            $messages = [];
            
            // Sistem mesajı
            $messages[] = [
                "role" => "system",
                "content" => "Sen bir Akıllı Ajanda Uygulamasının Asistanısın."
            ];
            
            // Kullanıcı mesajı
            $messages[] = [
                "role" => "user",
                "content" => implode("\n", $prompt)
            ];
            
            $response = $this->callOpenAI([
                "model" => $this->model,
                "messages" => $messages
            ]);
            
            if (!isset($response['choices'][0]['message']['content'])) {
                throw new Exception("API geçerli bir yanıt döndürmedi");
            }
            
            return $response['choices'][0]['message']['content'];
        } catch (Exception $e) {
            throw new Exception('OpenAI içerik oluşturma hatası: ' . $e->getMessage());
        }
    }
    
    /**
     * OpenAI API'sine HTTP isteği gönderir
     *
     * @param array $data İstek gövdesi
     * @return array Yanıt
     */
    private function callOpenAI(array $data): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/chat/completions', $data);
        
        if ($response->failed()) {
            throw new Exception('OpenAI API hatası: ' . $response->body());
        }
        
        return $response->json();
    }
    
    /**
     * Modelin mevcut durumda kullanılabilir olup olmadığını kontrol eder
     * 
     * @return bool
     */
    public function isAvailable(): bool
    {
        try {
            if (!$this->apiKey) {
                return false;
            }
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->get($this->baseUrl . '/models');
            
            return $response->successful();
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Varsayılan model adını döndürür
     * 
     * @return string
     */
    public function getDefaultModel(): string
    {
        return $this->model;
    }
    
    /**
     * Kullanılacak modeli ayarlar
     * 
     * @param string $model
     * @return self
     */
    public function setModel(string $model): self
    {
        $this->model = $model;
        return $this;
    }
    
    /**
     * Kullanılabilir modelleri listeler
     * 
     * @return array
     */
    public function listModels(): array
    {
        try {
            if (!$this->apiKey) {
                return [];
            }
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->get($this->baseUrl . '/models');
            
            if ($response->failed()) {
                return [];
            }
            
            $models = [];
            $data = $response->json();
            
            if (isset($data['data']) && is_array($data['data'])) {
                foreach ($data['data'] as $model) {
                    if (isset($model['id'])) {
                        $models[] = $model['id'];
                    }
                }
            }
            
            return $models;
        } catch (Exception $e) {
            return [];
        }
    }
} 