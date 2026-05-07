<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'user_id',      // actual DB column
        'student_id',   // alias kept in sync
        'score',
        'status',
        'submitted_at',
        'attempted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'attempted_at' => 'datetime',
    ];

    // Keep both user_id and student_id in sync automatically
    protected static function booted(): void
    {
        static::saving(function (self $attempt) {
            if ($attempt->student_id && !$attempt->user_id) {
                $attempt->user_id = $attempt->student_id;
            }
            if ($attempt->user_id && !$attempt->student_id) {
                $attempt->student_id = $attempt->user_id;
            }
            // Keep attempted_at in sync with submitted_at
            if ($attempt->submitted_at && !$attempt->attempted_at) {
                $attempt->attempted_at = $attempt->submitted_at;
            }
        });
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function student(): BelongsTo
    {
        // Works via both student_id and user_id since they're kept in sync
        return $this->belongsTo(User::class, 'user_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class);
    }
}
