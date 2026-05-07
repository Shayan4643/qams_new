<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'name',
        'code',
        'credit_hours',
        'description',
        'active',
    ];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'teacher_subjects', 'subject_id', 'teacher_id')
            ->withTimestamps();
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'subject_id');
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class, 'subject_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'subject_id');
    }

    public function quizAttempts(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(QuizAttempt::class, Quiz::class);
    }

    public function assignmentSubmissions(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(AssignmentSubmission::class, Assignment::class);
    }
}
