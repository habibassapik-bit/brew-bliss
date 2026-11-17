<?php

namespace App\Models;

use App\Enums\FeedbackStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('feedbacks')]
#[Fillable(['user_id', 'category', 'subject', 'message', 'attachment', 'is_anonymous', 'status', 'admin_reply', 'replied_by', 'replied_at'])]
class Feedback extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nama tabel eksplisit karena "feedback" diperlakukan uncountable oleh pluralizer.
     */
    protected $table = 'feedbacks';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => FeedbackStatus::class,
            'is_anonymous' => 'boolean',
            'replied_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function repliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replied_by');
    }
}
