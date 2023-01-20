<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use HasFactory,
    SoftDeletes;

    protected $fillable = ['action', 'data'];

    public function decodedData(): Attribute
    {
        return Attribute::make(
            get: function($value, $attribute) {
                if (array_key_exists('data', $attribute)) {
                    return json_decode($attribute['data']);
                }
            }
        );
    }

    public function performedby()
    {
        return $this->morphTo();
    }

    public function performedon()
    {
        return $this->morphTo();
    }
}
