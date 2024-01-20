<?php

namespace Duardaum\LaravelRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{

    use SoftDeletes, HasFactory;

    protected $table = 'messages';

    protected $fillable = [
        'content',
        'created_at',
        'updated_at',
    ];

}