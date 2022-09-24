<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'storage_name',
        'path',
        'mime',
        'size'
    ];

    public function addedby() 
    {
        return $this->morphTo();
    }
}
