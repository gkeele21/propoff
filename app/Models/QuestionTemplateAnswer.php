<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionTemplateAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_template_id',
        'answer_text',
        'display_order',
    ];

    public function questionTemplate()
    {
        return $this->belongsTo(QuestionTemplate::class);
    }
}
