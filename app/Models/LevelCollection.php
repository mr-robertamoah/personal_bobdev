<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LevelCollection extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'value', 
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function levels()
    {
        return $this->hasMany(Level::class);
    }

    public function hasLevels(): bool
    {
        return $this->levels()->exists();
    }

    public function doesNotHaveLevels(): bool
    {
        return !$this->hasLevels();
    }

    public function hasLevelWithName($name): bool
    {
        return $this->whereHasLevelWithName($name)->exists();
    }

    public function doesNotHaveLevelWithName($name): bool
    {
        return !$this->hasLevelWithName($name);
    }

    public function scopeWhereHasLevelWithName($query, $name)
    {
        return $query->whereHas('levels', function ($query) use ($name) {
            $query->whereName($name);
        });
    }
}
