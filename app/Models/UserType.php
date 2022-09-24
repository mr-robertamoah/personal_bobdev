<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserType extends Model
{
    use HasFactory;

    const TYPES = [
        'SUPERADMIN','ADMIN','PARENT','STUDENT','DONOR','FACILITATOR',
    ];
    const USABLETYPES = [
        'SUPERADMIN' => 'super admin',
        'ADMIN' => 'admin',
        'PARENT' => 'parent',
        'STUDENT' => 'student',
        'DONOR' => 'donor',
        'FACILITATOR' => 'facilitator',
    ];
    const SUPERADMIN = 'SUPERADMIN';
    const ADMIN = 'ADMIN';
    const PARENT = 'PARENT';
    const STUDENT = 'STUDENT';
    const DONOR = 'DONOR';
    const FACILITATOR = 'FACILITATOR';

    protected $fillable = [
        'name', 'description', 'user_id'
    ];

    public function usableName(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                return static::USABLETYPES[$attributes['name']];
            }
        );
    }

    public function users()
    {
        return $this->belongsToMany(
            related: User::class,
            table: 'user_user_type'
        )->withTimestamps();
    }

    public static function withName(string $name): Model
    {
        return static::where('name', $name)->first();
    }
}
