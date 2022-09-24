<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    const Types = ['EMAIL', 'PHONE'];
    const EMAIL = 'EMAIL';
    const PHONE = 'PHONE';

    protected $fillable = [
        'type',
        'contact'
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
