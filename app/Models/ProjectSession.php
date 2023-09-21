<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'type', 'period', 'start_date', 'end_date'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}