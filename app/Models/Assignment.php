<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'teacher_id',
        'subject_id',
        'title',
        'description',
        'file_path',
        'attachment_path',
        'deadline',
        'published',
        'is_published',
    ];

    protected $casts = [
        'deadline'     => 'datetime',
        'is_published' => 'boolean',
    ];

    // Keep published (string 'yes'/'no') and is_published (bool) in sync
    protected static function booted(): void
    {
        static::saving(function (self $assignment) {
            if (isset($assignment->published)) {
                $assignment->is_published = $assignment->published === 'yes' ? 1 : 0;
            } elseif (isset($assignment->is_published)) {
                $assignment->published = $assignment->is_published ? 'yes' : 'no';
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

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }
}
