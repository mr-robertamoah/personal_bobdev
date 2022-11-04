<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    use HasFactory;

    const MINVALUE = 1;

    protected $fillable = [
        'name', 'value', 'description', 'level_collection_id', 'user'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function levelCollection()
    {
        return $this->belongsTo(LevelCollection::class);
    }

    public function scopeWhereName($query, $name)
    {
        return $query->where('name', $name);
    }
}
