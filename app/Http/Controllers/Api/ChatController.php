<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\LLM\LLMService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Sohbet",
 *     description="Yapay zeka sohbet asistanı işlemleri"
 * )
 */
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
     * @OA\Post(
     *     path="/api/chat/send",
     *     summary="Yapay zeka asistanına mesaj gönder",
     *     description="Kullanıcı mesajını işler ve yapay zeka asistanından yanıt döner",
     *     tags={"Sohbet"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"message"},
     *             @OA\Property(property="message", type="string", example="Merhaba, bugün için yapılacaklar listemi oluşturabilir misin?"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="response", type="string", example="Merhaba! Tabii ki, işte sizin için bir yapılacaklar listesi önerisi..."),
     *             @OA\Property(property="type", type="string", example="Yapay Zeka Asistan"),
     *             @OA\Property(property="is_successful", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Hata",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Mesaj işlenirken bir hata oluştu"),
     *             @OA\Property(property="type", type="string", example="error"),
     *             @OA\Property(property="is_successful", type="boolean", example=false)
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function send(Request $request): JsonResponse
    {
        set_time_limit(120);
        try {
            $request->validate([
                'message' => 'required|string|max:1000'
            ]);

            \Log::debug('Chat request:', [
                'message' => $request->message,
                'user_id' => auth()->id()
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