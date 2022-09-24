<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use HasFactory,
    SoftDeletes;

    protected $fillable = ['action'];

    public function performedby()
    {
        return $this->morphTo();
    }

    public function performedon()
    {
        return $this->morphTo();
    }
}
