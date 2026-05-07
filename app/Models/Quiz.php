<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quiz extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'teacher_id',
        'subject_id',
        'title',
        'description',
        'deadline',
        'published',
        'is_published',
        'results_published',
    ];

    protected $casts = [
        'deadline'     => 'datetime',
        'is_published' => 'boolean',
        'results_published' => 'boolean',
    ];

    // Keep published (string 'yes'/'no') and is_published (bool) in sync
    protected static function booted(): void
    {
        static::saving(function (self $quiz) {
            if (isset($quiz->published)) {
                $quiz->is_published = $quiz->published === 'yes' ? 1 : 0;
            } elseif (isset($quiz->is_published)) {
                $quiz->published = $quiz->is_published ? 'yes' : 'no';
            }
        });
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'quiz_question', 'quiz_id', 'question_id')
            ->withTimestamps();
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }
}
