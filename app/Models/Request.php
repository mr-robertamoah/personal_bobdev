<?php

namespace App\Models;

use App\Enums\RequestStateEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;

    protected $fillable = ['state', 'purpose', 'type'];

    public function from()
    {
        return $this->morphTo();
    }

    public function to()
    {
        return $this->morphTo();
    }

    public function for()
    {
        return $this->morphTo();
    }

    public function isFor(Model $model): bool
    {
        return $this->for_type == $model::class &&
            $this->for_id == $model->id;
    }

    public function isNotFor(Model $model): bool
    {
        return !$this->isFor($model);
    }

    public function isFromClass(string $class): bool
    {
        return $this->from_type == $class;
    }

    public function isToClass(string $class): bool
    {
        return $this->to_type == $class;
    }

    public function isForClass(?string $class): bool
    {
        return $this->for_type == $class;
    }

    public function isFromCompany(): bool
    {
        return $this->isFromClass(Company::class);
    }

    public function isToCompany(): bool
    {
        return $this->isToClass(Company::class);
    }

    public function isForCompany(): bool
    {
        return $this->isForClass(Company::class);
    }

    public function isForUser(): bool
    {
        return $this->isForClass(User::class) ||
            $this->isForClass(null);
    }

    public function isNotForUser(): bool
    {
        return !$this->isForUser();
    }

    public function isForProject(): bool
    {
        return $this->isForClass(Project::class);
    }

    public function scopeWhereState($query, $state)
    {
        return $query
            ->where('state', strtoupper($state));
    }

    public function scopeWherePending($query)
    {
        return $query
            ->whereState(RequestStateEnum::pending->value);
    }

    public function scopeWhereType($query, $type)
    {
        return $query
            ->where('type', strtoupper($type));
    }

    public function scopeWherePurpose($query, $purpose)
    {
        return $query
            ->where('purpose', strtoupper($purpose));
    }
}
