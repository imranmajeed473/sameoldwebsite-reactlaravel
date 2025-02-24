<?php

namespace App\Models;

use App\Enums\CommentStatus as CommentStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $comment_id
 * @property ?int $user_id
 * @property CommentStatusEnum $status
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $updated_at
 * @property-read Comment $comment
 * @property-read ?User $user
 */
class CommentStatus extends Model
{
    /** @use HasFactory<\Database\Factories\CommentStatusFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CommentStatusEnum::class,
        ];
    }

    /**
     * Gets the comment that owns this status.
     */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    /**
     * Gets the user who set the status.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
