<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    /**
     * Toplu atanabilir özellikler
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'user_message',
        'ai_response',
        'ai_analysis',
        'message_type',
        'processed_data',
        'model_used',
        'is_successful',
        'error_message',
    ];

    /**
     * JSON olarak işlenecek özellikler
     *
     * @var array<string>
     */
    protected $casts = [
        'ai_analysis' => 'array',
        'processed_data' => 'array',
        'is_successful' => 'boolean',
    ];

    /**
     * Mesajın sahibi olan kullanıcı
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
