<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionSimilarity extends Model
{
    use HasFactory;

    protected $table = 'question_similarities';

    protected $fillable = [
        'question_id',
        'similar_question_id',
        'similarity_score',
        'handled',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    public function similarQuestion()
    {
        return $this->belongsTo(Question::class, 'similar_question_id');
    }
}
