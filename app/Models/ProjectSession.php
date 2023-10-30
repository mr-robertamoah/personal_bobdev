<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'type', 'period', 'start_time', 'end_time',
        'start_date', 'end_date', 'description', 'day_of_week',
        "project_id"
    ];

    protected $dates = ["start_date", "end_date", "start_time", "end_time"];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}