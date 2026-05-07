<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'user_id',          // actual DB column
        'student_id',       // alias kept in sync
        'file_path',
        'submission_text',
        'marks',
        'status',
        'feedback',         // actual DB column
        'teacher_feedback', // alias kept in sync
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    // Keep both user_id/student_id and feedback/teacher_feedback in sync
    protected static function booted(): void
    {
        static::saving(function (self $sub) {
            if ($sub->student_id && !$sub->user_id) {
                $sub->user_id = $sub->student_id;
            }
            if ($sub->user_id && !$sub->student_id) {
                $sub->student_id = $sub->user_id;
            }
            if ($sub->teacher_feedback && !$sub->feedback) {
                $sub->feedback = $sub->teacher_feedback;
            }
            if ($sub->feedback && !$sub->teacher_feedback) {
                $sub->teacher_feedback = $sub->feedback;
            }
        });
    }

    // Accessor: always return teacher_feedback, fallback to feedback column
    public function getTeacherFeedbackAttribute($value): ?string
    {
        return $value ?? $this->attributes['feedback'] ?? null;
    }

    // Accessor: always return student via user_id
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
