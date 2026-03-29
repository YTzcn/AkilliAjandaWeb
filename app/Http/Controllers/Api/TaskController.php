<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CalendarTaskRequest;
use App\Http\Requests\Api\TaskRequest;
use App\Models\Task;
use App\Services\TaskService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Görevler",
 *     description="Görev yönetimi ile ilgili API endpointleri"
 * )
 */
class TaskController extends Controller
{
    protected $service;

    public function __construct(TaskService $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *     path="/tasks",
     *     summary="Tüm görevleri listeler",
     *     description="Kullanıcıya ait tüm görevleri listeler. Takvim görünümü için start ve end parametreleri eklenebilir.",
     *     operationId="getTasks",
     *     tags={"Görevler"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="start",
     *         in="query",
     *         description="Başlangıç tarihi (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end",
     *         in="query",
     *         description="Bitiş tarihi (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Task")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Yetkilendirme hatası"
     *     )
     * )
     * 
     * Tüm görevleri listeler veya takvim formatında görevleri döndürür
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        if ($request->has('start') && $request->has('end')) {
            $tasks = $this->service->getCalendarTasks([
                'start' => $request->input('start'),
                'end' => $request->input('end'),
            ]);
        } else {
            $filters = $request->only([
                'status', 'priority', 'is_completed', 'due_from', 'due_to',
                'category_id', 'sort', 'dir',
            ]);
            $filters = array_filter(
                $filters,
                static fn ($v) => $v !== null && $v !== ''
            );
            $tasks = $filters === []
                ? $this->service->getAllTasks()
                : $this->service->getFilteredTasks($filters);
        }

        return response()->json($tasks);
    }
    /**
     * @OA\Get(
     *     path="/tasks/{task}",
     *     summary="Belirli bir görevi gösterir",
     *     description="Belirli bir görevin detaylarını gösterir",
     *     operationId="getTask",
     *     tags={"Görevler"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         description="Görev ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı",
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Görev bulunamadı"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Yetkilendirme hatası"
     *     )
     * )
     * 
     * Belirli bir görevi getirir
     * 
     * @param Task $task
     * @return JsonResponse
     */
    public function show(Task $task): JsonResponse
    {
        return response()->json($task);
    }

    /**
     * @OA\Post(
     *     path="/tasks",
     *     summary="Yeni bir görev oluşturur",
     *     description="Kullanıcı için yeni bir görev oluşturur",
     *     operationId="createTask",
     *     tags={"Görevler"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Görev bilgileri",
     *         @OA\JsonContent(
     *             required={"title", "due_date"},
     *             @OA\Property(property="title", type="string", example="Toplantı hazırlığı", description="Görev başlığı"),
     *             @OA\Property(property="description", type="string", example="Haftalık toplantı için sunum hazırlığı", description="Görev açıklaması"),
     *             @OA\Property(property="due_date", type="string", format="date-time", example="2023-12-31 14:00:00", description="Son tarih"),
     *             @OA\Property(property="priority", type="integer", example=2, description="Öncelik seviyesi (1: Düşük, 2: Orta, 3: Yüksek)"),
     *             @OA\Property(property="status", type="string", example="pending", description="Durum (pending, in-progress, completed)"),
     *             @OA\Property(property="is_completed", type="boolean", example=false, description="Tamamlanma durumu"),
     *             @OA\Property(property="category_ids", type="array", @OA\Items(type="integer"), example={1, 2}, description="Kategori ID'leri")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Başarıyla oluşturuldu",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Görev başarıyla oluşturuldu."),
     *             @OA\Property(property="task", ref="#/components/schemas/Task")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Doğrulama hatası",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Doğrulama hatası"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Yetkilendirme hatası"
     *     )
     * )
     * 
     * Yeni bir görev oluşturur
     * 
     * @param TaskRequest $request
     * @return JsonResponse
     */
    public function store(TaskRequest $request): JsonResponse
    {
        $task = $this->service->createTask($request->validated());

        return response()->json([
            'message' => 'Görev başarıyla oluşturuldu.',
            'task' => $task
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/tasks/{task}",
     *     summary="Bir görevi günceller",
     *     description="Mevcut bir görevi günceller",
     *     operationId="updateTask",
     *     tags={"Görevler"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         description="Görev ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Görev bilgileri",
     *         @OA\JsonContent(
     *             required={"title", "due_date"},
     *             @OA\Property(property="title", type="string", example="Toplantı hazırlığı (Güncellendi)", description="Görev başlığı"),
     *             @OA\Property(property="description", type="string", example="Haftalık toplantı için sunum hazırlığı ve doküman dağıtımı", description="Görev açıklaması"),
     *             @OA\Property(property="due_date", type="string", format="date-time", example="2023-12-31 16:00:00", description="Son tarih"),
     *             @OA\Property(property="priority", type="integer", example=3, description="Öncelik seviyesi (1: Düşük, 2: Orta, 3: Yüksek)"),
     *             @OA\Property(property="status", type="string", example="in-progress", description="Durum (pending, in-progress, completed)"),
     *             @OA\Property(property="is_completed", type="boolean", example=false, description="Tamamlanma durumu"),
     *             @OA\Property(property="category_ids", type="array", @OA\Items(type="integer"), example={1, 3}, description="Kategori ID'leri")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Başarıyla güncellendi",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Görev başarıyla güncellendi."),
     *             @OA\Property(property="task", ref="#/components/schemas/Task")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Görev bulunamadı"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Doğrulama hatası",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Doğrulama hatası"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Yetkilendirme hatası"
     *     )
     * )
     * 
     * Mevcut bir görevi günceller
     * 
     * @param TaskRequest $request
     * @param Task $task
     * @return JsonResponse
     */
    public function update(TaskRequest $request, Task $task): JsonResponse
    {
        $task = $this->service->updateTask($task->id, $request->validated());

        return response()->json([
            'message' => 'Görev başarıyla güncellendi.',
            'task' => $task
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/tasks/{task}",
     *     summary="Bir görevi siler",
     *     description="Belirli bir görevi siler",
     *     operationId="deleteTask",
     *     tags={"Görevler"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         description="Görev ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Başarıyla silindi",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Görev başarıyla silindi.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Görev bulunamadı"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Yetkilendirme hatası"
     *     )
     * )
     * 
     * Bir görevi siler
     * 
     * @param Task $task
     * @return JsonResponse
     */
    public function destroy(Task $task): JsonResponse
    {
        $this->service->deleteTask($task->id);

        return response()->json([
            'message' => 'Görev başarıyla silindi.'
        ]);
    }

} 