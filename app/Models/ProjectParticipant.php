<?php

namespace App\Models;

use App\Enums\ProjectParticipantEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectParticipant extends Model
{
    use HasFactory;

    protected $table = 'project_participant';

    protected $fillable = ['participating_as', 'project_id'];

    public static $validParticipantClasses = [User::class, Company::class];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function participant()
    {
        return $this->morphTo();
    }

    function scopeWhereParticipant($query, $model)
    {
        return $query->where('participant_type', $model::class)
            ->where('participant_id', $model->id);
    }

    function scopeWhereParticipationType($query, $type)
    {
        if (is_null($type)) {
            return $query;
        }

        if (strtolower($type) == 'learner')
        {
            $type = ProjectParticipantEnum::learner->value;
        }
        return $query->where('participating_as', strtoupper($type));
    }
}
