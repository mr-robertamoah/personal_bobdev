<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Administrator extends Model
{
    use HasFactory;

    const LEVELS = ['SUPERADMIN', 'SUPERVISOR', 'ADMIN', 'MEMBER'];
    const SUPERADMIN = 'SUPERADMIN';
    const SUPERVISOR = 'SUPERVISOR';
    const ADMIN = 'ADMIN';
    const MEMBER = 'MEMBER';
    const CONTRIBUTOR = 'CONTRIBUTOR';
    const MORPHNAME = 'adminable';

    protected $fillable = ['levels'];

    public function adminable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
