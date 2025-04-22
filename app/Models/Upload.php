<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    //
    protected $table = 'upload';
    protected $fillable = [
        'filename',
        's3_path',
    ];
}
