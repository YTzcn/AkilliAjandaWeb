<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LLMController;
use Gemini\Laravel\Facades\Gemini;

// Kullanıcı bilgisi
Route::middleware('ensure.auth')->get('/user', function (Request $request) {
    return $request->user();
});

// LLM işlemleri
Route::prefix('llm')->group(function () {
    // Kullanıcı mesajını işler
    Route::post('/process', [LLMController::class, 'processMessage']);
    
    // Kullanılabilir sağlayıcıları listeler
    Route::get('/providers', [LLMController::class, 'listProviders']);
    
    // Kullanılabilir modelleri listeler
    Route::get('/models', [LLMController::class, 'listModels']);
});

// Gemini modelleri listesi
Route::get('/models', function(){
    try {
        $response = Gemini::models()->list();
        return response()->json([
            'success' => true,
            'models' => $response->models ?? []
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'error_trace' => app()->environment('production') ? null : $e->getTraceAsString()
        ], 500);
    }
});

// Gemini model testi - doğrudan bir prompt göndererek test etmek için
Route::post('/test-model', function(Request $request){
    try {
        $request->validate([
            'prompt' => 'required|string',
            'model' => 'nullable|string'
        ]);
        
        $model = $request->input('model', 'gemini-1.5-pro');
        $response = Gemini::generativeModel($model)->generateContent($request->input('prompt'));
        
        return response()->json([
            'success' => true,
            'response' => $response->text()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'error_trace' => app()->environment('production') ? null : $e->getTraceAsString()
        ], 500);
    }
});
