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
     * Kullanıcı mesajını veya ReAct prompt'unu işler ve analiz eder
     * 
     * @param string $prompt Kullanıcı mesajı veya tam ReAct prompt'u
     * @return array İşlenmiş analiz sonucu
     */
    public function processMessage(string $prompt): array
    {
        try {
            if (!$this->apiKey) {
                throw new Exception('OpenAI API anahtarı bulunamadı');
            }
            
            $currentDate = Carbon::now()->format('Y-m-d H:i:s');
            
            // Eğer gelen string zaten bir ReAct prompt'u ise doğrudan kullan, 
            // değilse (eski yapıdan geliyorsa) sarmala.
            if (str_contains($prompt, 'Thought:') || str_contains($prompt, 'Action:')) {
                $messages = [
                    [
                        "role" => "system",
                        "content" => "Sen bir Akıllı Ajanda Uygulamasının Asistanısın. ReAct (Reasoning and Acting) prensibiyle çalışmalısın."
                    ],
                    [
                        "role" => "user",
                        "content" => $prompt
                    ]
                ];
            } else {
                $messages = [
                    [
                        "role" => "system",
                        "content" => "Sen bir Akıllı Ajanda Uygulamasının Asistanısın. Kullanıcılar seninle konuşarak ajanda üzerindeki işlemlerini yapabilirler."
                    ],
                    [
                        "role" => "user",
                        "content" => "ÖNEMLİ: Şu anki gerçek tarih ve saat: " . $currentDate . " Bu tarihi referans alarak işlem yap.\n" .
                            "Tarih ile ilgili tüm kararlarında bugün olarak yukarıdaki tarihi kabul et ve buna göre hesaplama yap.\n" .
                            "Kullanıcı mesajı: " . $prompt . "\n" .
                            "Lütfen yanıtını ReAct formatında (thought, action, action_input) bir JSON olarak ver. Nihai cevap için action 'final_answer' ve action_input içinde 'message' kullan."
                    ]
                ];
            }

            $response = $this->callOpenAI([
                "model" => $this->model,
                "messages" => $messages,
                "response_format" => [
                    "type" => "json_object"
                ]
            ]);
            
            if (!isset($response['choices'][0]['message']['content'])) {
                throw new Exception("API geçerli bir yanıt döndürmedi");
            }

            $content = $response['choices'][0]['message']['content'];
            $analysis = json_decode($content, true);
            
            if (!is_array($analysis)) {
                // Eğer JSON değilse, düz metni final_answer olarak paketle
                return [
                    'thought' => 'Düz metin yanıt alındı.',
                    'action' => 'final_answer',
                    'action_input' => ['message' => $content]
                ];
            }
            
            // ReAct formatı için gerekli alanları kontrol et ve dönüştür
            if (isset($analysis['type']) && !isset($analysis['action'])) {
                $analysis['action'] = $analysis['type'];
                $analysis['action_input'] = $analysis['data'] ?? [];
                $analysis['thought'] = $analysis['thought'] ?? 'İşlem gerçekleştiriliyor.';
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