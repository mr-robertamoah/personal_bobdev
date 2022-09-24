<?php

namespace App\Models;

use App\Traits\AddedByTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Url extends Model
{
    use HasFactory,
        AddedByTrait;

    protected $fillable = ['url', 'about'];
    
}
