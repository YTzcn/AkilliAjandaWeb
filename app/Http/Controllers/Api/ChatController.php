<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\LLM\LLMService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * @var LLMService
     */
    protected LLMService $llmService;

    /**
     * ChatController constructor.
     *
     * @param LLMService $llmService
     */
    public function __construct(LLMService $llmService)
    {
        $this->llmService = $llmService;
    }

    /**
     * Kullanıcı mesajını işler ve yanıt döner
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function send(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'message' => 'required|string|max:1000'
            ]);

            $response = $this->llmService->processUserMessage($request->message);

            return response()->json([
                'status' => 'success',
                'response' => $response,
                'type' => 'Yapay Zeka Asistan',
                'is_successful' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mesaj işlenirken bir hata oluştu: ' . $e->getMessage(),
                'type' => 'error',
                'is_successful' => false
            ], 500);
        }
    }
} 