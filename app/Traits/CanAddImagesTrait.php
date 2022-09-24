<?php

namespace App\Traits;

use App\Models\Image;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait CanAddImagesTrait
{
    function images()
    {
        return $this->morphMany(Image::class, 'addedby');
    }

    function latestImage()
    {
        return $this
            ->morphMany(Image::class, 'addedby')
            ->latestOfMany();
    }

    public function latestUrl(): Attribute
    {
        return new Attribute(
            get: function () {
                
                if ($this->images()->doesntExist()) {
                    return asset("storage/default.webp");
                }

                $latestImage = $this->latestImage();
                    
                return asset("storage/{$latestImage->path}/{$latestImage->storage_name}");
            }
        );
        
    }
}