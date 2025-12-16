<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = [
    
        'session_id',
        'question',
        'answer',
        'summary',

    ];
}
