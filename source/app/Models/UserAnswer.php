<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'entry_id',
        'group_question_id',
        'answer_text',
        'points_earned',
        'is_correct',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    /**
     * Get the entry that owns the answer.
     */
    public function entry()
    {
        return $this->belongsTo(Entry::class);
    }

    /**
     * Get the group question that this answer is for.
     */
    public function groupQuestion()
    {
        return $this->belongsTo(GroupQuestion::class);
    }
}
