<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @OA\Schema(
 *     schema="Task",
 *     title="Task",
 *     description="Görev modeli",
 *     @OA\Property(property="id", type="integer", format="int64", example=1, description="Görev ID"),
 *     @OA\Property(property="user_id", type="integer", format="int64", example=1, description="Kullanıcı ID"),
 *     @OA\Property(property="title", type="string", example="Toplantı hazırlığı", description="Görev başlığı"),
 *     @OA\Property(property="description", type="string", example="Haftalık toplantı için sunum hazırlığı", description="Görev açıklaması"),
 *     @OA\Property(property="due_date", type="string", format="date-time", example="2023-12-31 14:00:00", description="Son tarih"),
 *     @OA\Property(property="status", type="string", example="pending", description="Görev durumu (pending, in-progress, completed)"),
 *     @OA\Property(property="priority", type="integer", example=2, description="Öncelik seviyesi (1: Düşük, 2: Orta, 3: Yüksek)"),
 *     @OA\Property(property="is_completed", type="boolean", example=false, description="Tamamlanma durumu"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01 12:00:00", description="Oluşturulma tarihi"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-01 12:00:00", description="Güncellenme tarihi")
 * )
 */
class Task extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'due_date',
        'status',
        'priority',
        'is_completed'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'datetime',
        'is_completed' => 'boolean',
        'priority' => 'integer'
    ];

    /**
     * Get the user that owns the task.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }
} 