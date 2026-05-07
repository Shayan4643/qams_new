<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'user_name',
        'email',
        'phone',
        'password',
        'role',
        'active',
        'status',
        // Student-specific
        'admission_number',
        'father_name',
        'profile_picture',
        'class_id',
        // Teacher-specific
        'job_history',
        'education',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        
        'password' => 'hashed',
    ];

    public function taughtSubjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'teacher_subjects', 'teacher_id', 'subject_id')
            ->withTimestamps();
    }

    public function enrolledSubjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'student_subjects', 'student_id', 'subject_id')
            ->withTimestamps();
    }

    public function schoolClass(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'teacher_id');
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class, 'teacher_id');
    }

    public function quizAttempts(): HasMany
    {
        // actual DB column is user_id in quiz_attempts
        return $this->hasMany(QuizAttempt::class, 'user_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'teacher_id');
    }

    public function assignmentSubmissions(): HasMany
    {
        // actual DB column is user_id in assignment_submissions
        return $this->hasMany(AssignmentSubmission::class, 'user_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
