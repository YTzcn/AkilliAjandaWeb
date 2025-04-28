<?php

namespace App\Services\LLM\Providers;

interface ProviderInterface
{
    /**
     * Kullanıcı mesajını işler ve analiz eder
     * 
     * @param string $message Kullanıcı mesajı
     * @return array İşlenmiş analiz sonucu
     */
    public function processMessage(string $message): array;
    
    /**
     * Soru bazlı içerik oluşturur
     * 
     * @param array $prompt İstek için prompt
     * @return string Model tarafından üretilen yanıt
     */
    public function generateContent(array $prompt): string;
    
    /**
     * Modelin mevcut durumda kullanılabilir olup olmadığını kontrol eder
     * 
     * @return bool
     */
    public function isAvailable(): bool;
    
    /**
     * Varsayılan model adını döndürür
     * 
     * @return string
     */
    public function getDefaultModel(): string;
    
    /**
     * Kullanılacak modeli ayarlar
     * 
     * @param string $model
     * @return self
     */
    public function setModel(string $model): self;
    
    /**
     * Kullanılabilir modelleri listeler
     * 
     * @return array
     */
    public function listModels(): array;
}