<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    /** @use HasFactory<\Database\Factories\DocumentFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'embedding',
        'original_text',
    ];

    protected $connection = 'pgsql';

   /* protected $casts = [
        'embedding' => 'array',
    ];*/
}
