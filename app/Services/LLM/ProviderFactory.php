<?php

namespace App\Services\LLM;

use App\Services\LLM\Providers\ProviderInterface;
use App\Services\LLM\Providers\GeminiProvider;
use App\Services\LLM\Providers\OpenAIProvider;
use App\Services\LLM\Providers\OpenRouterProvider;
use Illuminate\Support\Facades\Config;
use Exception;

class ProviderFactory
{
    /**
     * LLM sağlayıcının bir örneğini oluşturur
     *
     * @param string|null $provider Sağlayıcı adı (null ise varsayılan sağlayıcı kullanılır)
     * @return ProviderInterface
     * @throws Exception Sağlayıcı bulunamadığında veya kullanılamadığında
     */
    public static function create(?string $provider = null): ProviderInterface
    {
        // Sağlayıcı belirtilmemişse konfigürasyondan al
        if (!$provider) {
            $provider = Config::get('llm.default_provider', 'openrouter');
        }
        
        // Sağlayıcı sınıfını belirle
        $providerClass = match (strtolower($provider)) {
            'gemini' => GeminiProvider::class,
            'openai' => OpenAIProvider::class,
            'openrouter' => OpenRouterProvider::class,
            default => throw new Exception("Desteklenmeyen LLM sağlayıcısı: $provider")
        };
        
        // Sağlayıcı örneğini oluştur
        $instance = new $providerClass();
        
        // Sağlayıcının kullanılabilir olup olmadığını kontrol et
        if (!$instance->isAvailable()) {
            // Varsayılan olarak ilk kullanılabilir sağlayıcıyı bul
            foreach (['gemini', 'openai', 'openrouter'] as $fallbackProvider) {
                if ($fallbackProvider === strtolower($provider)) {
                    continue; // Zaten denenmiş olan sağlayıcıyı atla
                }
                
                $fallbackClass = match ($fallbackProvider) {
                    'gemini' => GeminiProvider::class,
                    'openai' => OpenAIProvider::class,
                    'openrouter' => OpenRouterProvider::class,
                    default => null
                };
                
                if ($fallbackClass) {
                    $fallbackInstance = new $fallbackClass();
                    if ($fallbackInstance->isAvailable()) {
                        return $fallbackInstance;
                    }
                }
            }
            
            throw new Exception("Kullanılabilir LLM sağlayıcısı bulunamadı. Lütfen API anahtarlarını kontrol edin.");
        }
        
        return $instance;
    }
    
    /**
     * Kullanılabilir tüm LLM sağlayıcılarını döndürür
     *
     * @return array [sağlayıcı_adı => durum] şeklinde bir dizi
     */
    public static function getAvailableProviders(): array
    {
        $providers = [
            'gemini' => false,
            'openai' => false,
            'openrouter' => false
        ];
        
        foreach ($providers as $name => $status) {
            try {
                $instance = self::create($name);
                $providers[$name] = $instance->isAvailable();
            } catch (Exception $e) {
                $providers[$name] = false;
            }
        }
        
        return $providers;
    }
} 