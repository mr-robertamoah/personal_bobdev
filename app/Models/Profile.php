<?php

namespace App\Models;

use App\Traits\CanAddImagesTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory,
        CanAddImagesTrait;

    const PROFILEABLECLASSES = [
        User::class,
        Company::class,
    ];

    protected $fillable = [
        'about',
        'settings'
    ];

    protected $casts = [
        'settings' => 'array'
    ];

    public function profileable()
    {
        return $this->morphTo();
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function emails()
    {
        return $this->contacts()
            ->where('type', Contact::EMAIL)
            ->get();
    }

    public function phones()
    {
        return $this->contacts()
            ->where('type', Contact::PHONE)
            ->get();
    }
}
