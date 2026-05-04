<?php

namespace App\Services\LLM\Providers;

use Gemini\Laravel\Facades\Gemini;
use Carbon\Carbon;
use Exception;

class GeminiProvider implements ProviderInterface
{
    /**
     * Kullanılacak model adı
     * 
     * @var string
     */
    protected string $model = 'gemini-1.5-pro';
    
    /**
     * Kullanıcı mesajını veya ReAct prompt'unu işler ve analiz eder
     * 
     * @param string $prompt Kullanıcı mesajı veya tam ReAct prompt'u
     * @return array İşlenmiş analiz sonucu
     */
    public function processMessage(string $prompt): array
    {
        try {
            // Mevcut model kontrolü
            $this->checkAndSetBestModel();
            
            // Gemini API kullanarak mesajı analiz et
            // Eğer gelen string zaten bir ReAct prompt'u ise doğrudan kullan, 
            // değilse (eski yapıdan geliyorsa) sarmala.
            $finalPrompt = $prompt;
            
            if (!str_contains($prompt, 'Thought:') && !str_contains($prompt, 'Action:')) {
                $currentDate = Carbon::now()->format('Y-m-d H:i:s');
                $finalPrompt = "Sen bir Akıllı Ajanda Uygulamasının Asistanısın. ReAct (Reasoning and Acting) prensibiyle çalışmalısın.\n" .
                    "Şu anki tarih: " . $currentDate . "\n" .
                    "Kullanıcı Mesajı: " . $prompt . "\n\n" .
                    "Lütfen şu formatta yanıt ver:\n" .
                    "{\n" .
                    "  \"thought\": \"Düşüncen...\",\n" .
                    "  \"action\": \"arac_adi\",\n" .
                    "  \"action_input\": { ... }\n" .
                    "}\n" .
                    "Eğer nihai cevaba ulaştıysan action 'final_answer' olmalı ve action_input içinde 'message' anahtarı ile yanıtını vermelisin.";
            }

            $response = Gemini::generativeModel($this->model)->generateContent($finalPrompt);

            // API yanıtını güvenli bir şekilde parse et
            $responseText = $response->text();
            
            // JSON yanıtını temizle - markdown kod bloklarını kaldır
            $cleanedResponse = $this->cleanJsonResponse($responseText);
            
            // JSON'ı parse et
            $analysis = json_decode($cleanedResponse, true);
            
            // JSON doğru parse edildi mi kontrol et
            if (!is_array($analysis)) {
                // Eğer JSON değilse, düz metni final_answer olarak paketle
                return [
                    'thought' => 'Düz metin yanıt alındı.',
                    'action' => 'final_answer',
                    'action_input' => ['message' => $responseText]
                ];
            }
            
            // ReAct formatı için gerekli alanları kontrol et (thought, action, action_input)
            // Eğer eski formatta (type, data) geldiyse dönüştür
            if (isset($analysis['type']) && !isset($analysis['action'])) {
                $analysis['action'] = $analysis['type'];
                $analysis['action_input'] = $analysis['data'] ?? [];
                $analysis['thought'] = $analysis['thought'] ?? 'İşlem gerçekleştiriliyor.';
            }

            return $analysis;
        } catch (Exception $e) {
            throw new Exception('Gemini mesaj işleme hatası: ' . $e->getMessage());
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
            $response = Gemini::generativeModel($this->model)->generateContent($prompt);
            return $response->text();
        } catch (Exception $e) {
            throw new Exception('Gemini içerik oluşturma hatası: ' . $e->getMessage());
        }
    }
    
    /**
     * JSON yanıtını temizler - kod blokları, fazla boşluklar vs. kaldırılır
     *
     * @param string $response
     * @return string
     */
    private function cleanJsonResponse(string $response): string
    {
        // Markdown JSON kod bloklarını temizle (```json ... ``` veya ```{...}```)
        $pattern = '/```(?:json)?\s*(.*?)```/s';
        if (preg_match($pattern, $response, $matches)) {
            return trim($matches[1]);
        }
        
        // Eğer kod bloğu formatında değilse, ilk '{' ve son '}' arasındaki metni al
        if (preg_match('/\{.*\}/s', $response, $matches)) {
            return trim($matches[0]);
        }
        
        // Hiçbir eşleşme bulunamazsa orijinal metni döndür
        return trim($response);
    }
    
    /**
     * Modelin mevcut durumda kullanılabilir olup olmadığını kontrol eder
     * 
     * @return bool
     */
    public function isAvailable(): bool
    {
        try {
            $models = $this->listModels();
            return !empty($models);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Kullanılabilir en iyi Gemini modelini kontrol eder ve seçer
     * 
     * @return void
     */
    private function checkAndSetBestModel(): void
    {
        try {
            $models = $this->listModels();
            
            // Pro model kontrolü yap
            foreach ($models as $model) {
                // Son model adını kullan
                $modelName = is_array($model) ? ($model['name'] ?? '') : $model;
                
                if (is_string($modelName) && str_contains($modelName, 'gemini-1.5-pro')) {
                    $this->model = $modelName;
                    break;
                }
            }
        } catch (Exception $e) {
            // Hata durumunda varsayılan modeli kullan
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
            $modelResponse = Gemini::models()->list();
            
            if (isset($modelResponse->models)) {
                $models = [];
                foreach ($modelResponse->models as $model) {
                    $models[] = str_replace('models/', '', $model->name ?? '');
                }
                return $models;
            }
            
            return [];
        } catch (Exception $e) {
            return [];
        }
    }
} 