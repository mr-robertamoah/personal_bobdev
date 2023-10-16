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

    function scopeWhereIsParticipant($query, $model)
    {
        return $query->where(function ($q) use ($model) {
            $q->where('participant_type', $model::class)
                ->where('participant_id', $model->id);
        });
    }

    function scopeWhereOfficial($query)
    {
        return $query->whereParticipationType(ProjectParticipantEnum::facilitator->value);
    }

    function scopeWhereSponsor($query)
    {
        return $query->whereParticipationType(ProjectParticipantEnum::sponsor->value);
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
