<?php

namespace App\Http\Controllers;

use App\Services\LLM\LLMService;
use App\Services\LLM\ProviderFactory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LLMController extends Controller
{
    protected LLMService $llmService;

    public function __construct(LLMService $llmService)
    {
        $this->llmService = $llmService;
    }

    /**
     * Kullanıcı mesajını işler
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function processMessage(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string',
            'provider' => 'nullable|string'
        ]);

        try {
            // Eğer belirli bir sağlayıcı belirtilmişse ona geçiş yap
            if ($request->has('provider')) {
                $this->llmService->setProvider($request->provider);
            }
            
            $response = $this->llmService->processUserMessage($request->message);
            
            return response()->json([
                'success' => true,
                'response' => $response,
                'provider' => get_class($this->llmService->getProvider())
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Mesajınız işlenirken bir hata oluştu.',
                'details' => $e->getMessage(),
                'error_trace' => app()->environment('production') ? null : $e->getTraceAsString()
            ], 500);
        }
    }
    
    /**
     * Kullanılabilir sağlayıcıları listeler
     * 
     * @return JsonResponse
     */
    public function listProviders(): JsonResponse
    {
        try {
            $providers = ProviderFactory::getAvailableProviders();
            
            return response()->json([
                'success' => true,
                'providers' => $providers,
                'current' => get_class($this->llmService->getProvider())
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Sağlayıcılar listelenirken bir hata oluştu.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Belirli bir sağlayıcı için kullanılabilir modelleri listeler
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function listModels(Request $request): JsonResponse
    {
        $request->validate([
            'provider' => 'nullable|string'
        ]);
        
        try {
            // Eğer belirli bir sağlayıcı belirtilmişse ona geçiş yap
            if ($request->has('provider')) {
                $this->llmService->setProvider($request->provider);
            }
            
            $models = $this->llmService->getProvider()->listModels();
            
            return response()->json([
                'success' => true,
                'provider' => get_class($this->llmService->getProvider()),
                'models' => $models,
                'default_model' => $this->llmService->getProvider()->getDefaultModel()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Modeller listelenirken bir hata oluştu.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
} 